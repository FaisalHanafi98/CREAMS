<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\AssetMaintenance;
use App\Models\AssetMovement;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use Exception;

class AssetService
{
    /**
     * Get comprehensive asset statistics
     */
    public function getAssetStatistics(?int $centreId = null): array
    {
        $cacheKey = "asset_stats_" . ($centreId ?? 'all');
        
        return Cache::remember($cacheKey, 300, function () use ($centreId) {
            $query = Asset::query();
            
            if ($centreId) {
                $query->where('centre_id', $centreId);
            }
            
            $totalAssets = $query->count();
            
            return [
                'overview' => [
                    'total_assets' => $totalAssets,
                    'total_value' => $query->sum('current_value'),
                    'average_age' => $this->getAverageAssetAge($centreId),
                    'depreciation_rate' => $this->getAverageDepreciationRate($centreId)
                ],
                'by_status' => [
                    'available' => $query->clone()->byStatus('available')->count(),
                    'in_use' => $query->clone()->byStatus('in_use')->count(),
                    'maintenance' => $query->clone()->byStatus('maintenance')->count(),
                    'disposed' => $query->clone()->byStatus('disposed')->count(),
                ],
                'by_condition' => [
                    'new' => $query->clone()->byCondition('new')->count(),
                    'good' => $query->clone()->byCondition('good')->count(),
                    'fair' => $query->clone()->byCondition('fair')->count(),
                    'poor' => $query->clone()->byCondition('poor')->count(),
                    'broken' => $query->clone()->byCondition('broken')->count(),
                ],
                'by_category' => $this->getAssetsByCategory($centreId),
                'alerts' => [
                    'warranty_expiring' => $query->clone()->warrantyExpiring(30)->count(),
                    'requires_maintenance' => $query->clone()->requiresMaintenance()->count(),
                    'overdue_maintenance' => $this->getOverdueMaintenanceCount($centreId),
                    'high_value_unassigned' => $this->getHighValueUnassignedCount($centreId)
                ]
            ];
        });
    }

    /**
     * Process asset assignment
     */
    public function assignAsset(int $assetId, int $userId, ?string $location = null, ?string $reason = null): bool
    {
        try {
            $asset = Asset::findOrFail($assetId);
            $user = User::findOrFail($userId);

            // Validate assignment
            if ($asset->status !== 'available') {
                throw new Exception('Asset is not available for assignment');
            }

            if ($asset->condition === 'broken') {
                throw new Exception('Cannot assign broken asset');
            }

            // Check centre permissions
            if (session('role') !== 'admin' && $asset->centre_id != session('centre_id')) {
                throw new Exception('Cannot assign asset from different centre');
            }

            // Update asset
            $asset->update([
                'status' => 'in_use',
                'assigned_to' => $userId,
                'assigned_date' => now(),
                'location' => $location ?? $asset->location
            ]);

            // Create movement record
            AssetMovement::createAssignment(
                $assetId,
                $userId,
                $location,
                $reason ?: 'Asset assignment',
                session('id')
            );

            Log::info('Asset assigned successfully', [
                'asset_id' => $assetId,
                'user_id' => $userId,
                'assigned_by' => session('id')
            ]);

            // Clear cache
            $this->clearAssetCache($asset->centre_id);

            return true;

        } catch (Exception $e) {
            Log::error('Asset assignment failed', [
                'asset_id' => $assetId,
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }

    /**
     * Process asset return
     */
    public function returnAsset(int $assetId, ?string $condition = null, ?string $notes = null): bool
    {
        try {
            $asset = Asset::findOrFail($assetId);

            // Validate return
            if ($asset->status !== 'in_use' || !$asset->assigned_to) {
                throw new Exception('Asset is not currently assigned');
            }

            $oldAssignee = $asset->assigned_to;

            // Update asset
            $updateData = [
                'status' => 'available',
                'assigned_to' => null,
                'assigned_date' => null
            ];

            if ($condition) {
                $updateData['condition'] = $condition;
            }

            if ($notes) {
                $updateData['notes'] = $asset->notes ? $asset->notes . "\n" . $notes : $notes;
            }

            $asset->update($updateData);

            // Create movement record
            AssetMovement::createReturn(
                $assetId,
                $oldAssignee,
                $notes ?: 'Asset return',
                session('id')
            );

            Log::info('Asset returned successfully', [
                'asset_id' => $assetId,
                'returned_from' => $oldAssignee,
                'returned_by' => session('id')
            ]);

            // Clear cache
            $this->clearAssetCache($asset->centre_id);

            return true;

        } catch (Exception $e) {
            Log::error('Asset return failed', [
                'asset_id' => $assetId,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }

    /**
     * Transfer asset between users or locations
     */
    public function transferAsset(
        int $assetId,
        ?int $fromUserId = null,
        ?int $toUserId = null,
        ?string $fromLocation = null,
        ?string $toLocation = null,
        ?string $reason = null
    ): bool {
        try {
            $asset = Asset::findOrFail($assetId);

            // Validate transfer
            if ($asset->status === 'disposed') {
                throw new Exception('Cannot transfer disposed asset');
            }

            // Update asset
            $updateData = [];
            
            if ($toUserId) {
                $updateData['assigned_to'] = $toUserId;
                $updateData['assigned_date'] = now();
                $updateData['status'] = 'in_use';
            } elseif ($fromUserId && !$toUserId) {
                // Transfer from user to location only
                $updateData['assigned_to'] = null;
                $updateData['assigned_date'] = null;
                $updateData['status'] = 'available';
            }

            if ($toLocation) {
                $updateData['location'] = $toLocation;
            }

            $asset->update($updateData);

            // Create movement record
            AssetMovement::createTransfer(
                $assetId,
                $fromUserId,
                $toUserId,
                $fromLocation,
                $toLocation,
                $reason ?: 'Asset transfer',
                session('id')
            );

            Log::info('Asset transferred successfully', [
                'asset_id' => $assetId,
                'from_user' => $fromUserId,
                'to_user' => $toUserId,
                'transferred_by' => session('id')
            ]);

            // Clear cache
            $this->clearAssetCache($asset->centre_id);

            return true;

        } catch (Exception $e) {
            Log::error('Asset transfer failed', [
                'asset_id' => $assetId,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }

    /**
     * Schedule maintenance for asset
     */
    public function scheduleMaintenance(
        int $assetId,
        string $type,
        Carbon $scheduledDate,
        string $description,
        ?string $performedBy = null,
        ?string $notes = null
    ): AssetMaintenance {
        try {
            $asset = Asset::findOrFail($assetId);

            // Check for conflicting maintenance
            $existingMaintenance = AssetMaintenance::where('asset_id', $assetId)
                ->where('scheduled_date', $scheduledDate->toDateString())
                ->where('status', 'scheduled')
                ->exists();

            if ($existingMaintenance) {
                throw new Exception('Maintenance already scheduled for this date');
            }

            $maintenance = AssetMaintenance::create([
                'asset_id' => $assetId,
                'maintenance_type' => $type,
                'scheduled_date' => $scheduledDate,
                'status' => 'scheduled',
                'performed_by' => $performedBy,
                'description' => $description,
                'notes' => $notes,
            ]);

            Log::info('Maintenance scheduled', [
                'asset_id' => $assetId,
                'maintenance_id' => $maintenance->id,
                'scheduled_by' => session('id')
            ]);

            // Clear cache
            $this->clearAssetCache($asset->centre_id);

            return $maintenance;

        } catch (Exception $e) {
            Log::error('Maintenance scheduling failed', [
                'asset_id' => $assetId,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }

    /**
     * Update asset depreciation values
     */
    public function updateDepreciation(?int $centreId = null): int
    {
        try {
            $query = Asset::whereNotNull('purchase_date')
                ->whereNotNull('purchase_price')
                ->where('status', '!=', 'disposed');

            if ($centreId) {
                $query->where('centre_id', $centreId);
            }

            $assets = $query->get();
            $updatedCount = 0;

            foreach ($assets as $asset) {
                $oldValue = $asset->current_value;
                $asset->updateDepreciatedValue();
                
                if ($oldValue != $asset->current_value) {
                    $updatedCount++;
                }
            }

            Log::info('Depreciation updated', [
                'centre_id' => $centreId,
                'updated_count' => $updatedCount
            ]);

            // Clear cache
            $this->clearAssetCache($centreId);

            return $updatedCount;

        } catch (Exception $e) {
            Log::error('Depreciation update failed', [
                'centre_id' => $centreId,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }

    /**
     * Generate asset report
     */
    public function generateAssetReport(?int $centreId = null, ?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        $startDate = $startDate ?? now()->startOfMonth();
        $endDate = $endDate ?? now()->endOfMonth();

        $query = Asset::query();
        if ($centreId) {
            $query->where('centre_id', $centreId);
        }

        $assets = $query->with(['category', 'centre', 'assignedTo'])->get();

        return [
            'period' => [
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
            ],
            'summary' => [
                'total_assets' => $assets->count(),
                'total_value' => $assets->sum('current_value'),
                'total_depreciation' => $assets->sum(function($asset) {
                    return ($asset->purchase_price ?? 0) - ($asset->current_value ?? 0);
                }),
                'categories_count' => $assets->pluck('category_id')->unique()->count(),
                'centres_count' => $assets->pluck('centre_id')->unique()->count(),
            ],
            'by_status' => $assets->groupBy('status')->map->count(),
            'by_condition' => $assets->groupBy('condition')->map->count(),
            'by_category' => $assets->groupBy('category.name')->map->count(),
            'high_value_assets' => $assets->where('purchase_price', '>', 10000)->values(),
            'warranty_expiring' => $assets->filter(function($asset) {
                return $asset->warranty_expiry && 
                       $asset->warranty_expiry->between(now(), now()->addDays(30));
            })->values(),
            'maintenance_due' => $this->getMaintenanceDueAssets($centreId),
            'movement_summary' => AssetMovement::getStatistics($centreId, $startDate, $endDate)
        ];
    }

    /**
     * Get asset utilization data
     */
    public function getAssetUtilization(?int $centreId = null): array
    {
        $query = Asset::query();
        if ($centreId) {
            $query->where('centre_id', $centreId);
        }

        $totalAssets = $query->count();
        $inUseAssets = $query->clone()->byStatus('in_use')->count();
        $availableAssets = $query->clone()->byStatus('available')->count();
        $maintenanceAssets = $query->clone()->byStatus('maintenance')->count();

        $utilizationRate = $totalAssets > 0 ? ($inUseAssets / $totalAssets) * 100 : 0;

        return [
            'utilization_rate' => round($utilizationRate, 2),
            'total_assets' => $totalAssets,
            'in_use' => $inUseAssets,
            'available' => $availableAssets,
            'maintenance' => $maintenanceAssets,
            'efficiency_score' => $this->calculateEfficiencyScore($centreId),
            'recommendations' => $this->getUtilizationRecommendations($utilizationRate, $availableAssets)
        ];
    }

    /**
     * Get assets requiring attention
     */
    public function getAssetsRequiringAttention(?int $centreId = null): array
    {
        $query = Asset::query();
        if ($centreId) {
            $query->where('centre_id', $centreId);
        }

        return [
            'warranty_expiring' => $query->clone()->warrantyExpiring(30)
                ->with(['category', 'assignedTo'])
                ->get(),
            'maintenance_overdue' => $this->getOverdueMaintenanceAssets($centreId),
            'poor_condition' => $query->clone()->whereIn('condition', ['poor', 'broken'])
                ->with(['category', 'assignedTo'])
                ->get(),
            'high_value_unassigned' => $query->clone()
                ->where('purchase_price', '>', 5000)
                ->where('status', 'available')
                ->whereNull('assigned_to')
                ->with(['category'])
                ->get(),
            'long_term_assignments' => $this->getLongTermAssignments($centreId)
        ];
    }

    // =============================================
    // PRIVATE HELPER METHODS
    // =============================================

    private function getAverageAssetAge(?int $centreId = null): float
    {
        $query = Asset::whereNotNull('purchase_date');
        if ($centreId) {
            $query->where('centre_id', $centreId);
        }

        $assets = $query->get();
        if ($assets->isEmpty()) return 0;

        $totalAge = $assets->sum(function($asset) {
            return $asset->purchase_date->diffInMonths(now());
        });

        return round($totalAge / $assets->count(), 1);
    }

    private function getAverageDepreciationRate(?int $centreId = null): float
    {
        $query = Asset::whereNotNull('depreciation_rate');
        if ($centreId) {
            $query->where('centre_id', $centreId);
        }

        return round($query->avg('depreciation_rate') ?? 0, 2);
    }

    private function getAssetsByCategory(?int $centreId = null): array
    {
        $query = Asset::with('category');
        if ($centreId) {
            $query->where('centre_id', $centreId);
        }

        return $query->get()
            ->groupBy('category.name')
            ->map->count()
            ->toArray();
    }

    private function getOverdueMaintenanceCount(?int $centreId = null): int
    {
        $query = AssetMaintenance::where('scheduled_date', '<', now())
            ->whereIn('status', ['scheduled', 'in_progress']);

        if ($centreId) {
            $query->whereHas('asset', function($q) use ($centreId) {
                $q->where('centre_id', $centreId);
            });
        }

        return $query->count();
    }

    private function getHighValueUnassignedCount(?int $centreId = null): int
    {
        $query = Asset::where('purchase_price', '>', 5000)
            ->where('status', 'available')
            ->whereNull('assigned_to');

        if ($centreId) {
            $query->where('centre_id', $centreId);
        }

        return $query->count();
    }

    private function getOverdueMaintenanceAssets(?int $centreId = null): \Illuminate\Database\Eloquent\Collection
    {
        $query = AssetMaintenance::with(['asset.category', 'asset.assignedTo'])
            ->where('scheduled_date', '<', now())
            ->whereIn('status', ['scheduled', 'in_progress']);

        if ($centreId) {
            $query->whereHas('asset', function($q) use ($centreId) {
                $q->where('centre_id', $centreId);
            });
        }

        return $query->orderBy('scheduled_date')->get();
    }

    private function getMaintenanceDueAssets(?int $centreId = null): \Illuminate\Database\Eloquent\Collection
    {
        $query = AssetMaintenance::with(['asset.category'])
            ->where('scheduled_date', '<=', now()->addDays(7))
            ->where('status', 'scheduled');

        if ($centreId) {
            $query->whereHas('asset', function($q) use ($centreId) {
                $q->where('centre_id', $centreId);
            });
        }

        return $query->orderBy('scheduled_date')->get();
    }

    private function getLongTermAssignments(?int $centreId = null): \Illuminate\Database\Eloquent\Collection
    {
        $query = Asset::with(['category', 'assignedTo'])
            ->where('status', 'in_use')
            ->whereNotNull('assigned_to')
            ->where('assigned_date', '<', now()->subMonths(6));

        if ($centreId) {
            $query->where('centre_id', $centreId);
        }

        return $query->get();
    }

    private function calculateEfficiencyScore(?int $centreId = null): float
    {
        // Efficiency based on utilization, maintenance compliance, and condition
        $utilization = $this->getAssetUtilization($centreId);
        $maintenanceCompliance = $this->getMaintenanceCompliance($centreId);
        $conditionScore = $this->getConditionScore($centreId);

        return round(($utilization['utilization_rate'] * 0.4 + 
                     $maintenanceCompliance * 0.3 + 
                     $conditionScore * 0.3), 2);
    }

    private function getMaintenanceCompliance(?int $centreId = null): float
    {
        $totalMaintenance = AssetMaintenance::query();
        if ($centreId) {
            $totalMaintenance->whereHas('asset', function($q) use ($centreId) {
                $q->where('centre_id', $centreId);
            });
        }
        
        $total = $totalMaintenance->count();
        if ($total === 0) return 100;

        $completed = $totalMaintenance->clone()->where('status', 'completed')->count();
        return round(($completed / $total) * 100, 2);
    }

    private function getConditionScore(?int $centreId = null): float
    {
        $query = Asset::query();
        if ($centreId) {
            $query->where('centre_id', $centreId);
        }

        $conditionWeights = [
            'new' => 100,
            'good' => 80,
            'fair' => 60,
            'poor' => 40,
            'broken' => 20
        ];

        $assets = $query->get();
        if ($assets->isEmpty()) return 0;

        $totalScore = $assets->sum(function($asset) use ($conditionWeights) {
            return $conditionWeights[$asset->condition] ?? 0;
        });

        return round($totalScore / $assets->count(), 2);
    }

    private function getUtilizationRecommendations(float $utilizationRate, int $availableAssets): array
    {
        $recommendations = [];

        if ($utilizationRate < 60) {
            $recommendations[] = 'Low utilization rate detected. Consider promoting asset usage or disposing of unused assets.';
        }

        if ($availableAssets > 10) {
            $recommendations[] = 'High number of available assets. Review assignment procedures.';
        }

        if ($utilizationRate > 90) {
            $recommendations[] = 'High utilization rate. Consider acquiring additional assets to meet demand.';
        }

        return $recommendations;
    }

    private function clearAssetCache(?int $centreId = null): void
    {
        $cacheKey = "asset_stats_" . ($centreId ?? 'all');
        Cache::forget($cacheKey);
    }
}