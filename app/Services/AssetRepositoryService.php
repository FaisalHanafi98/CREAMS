<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\AssetMovement;
use App\Models\AssetMaintenance;
use App\Models\Centre;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Exception;

/**
 * Centralized Asset Management Repository Service
 * Provides comprehensive asset management functionality across all centres
 */
class AssetRepositoryService
{
    /**
     * Get comprehensive asset statistics across all centres
     *
     * @param string|null $centreId Filter by specific centre
     * @return array
     */
    public function getGlobalAssetStatistics($centreId = null): array
    {
        try {
            $query = Asset::query();
            
            if ($centreId) {
                $query->where('centre_id', $centreId);
            }

            $totalAssets = $query->count();
            $availableAssets = $query->clone()->byStatus('available')->count();
            $inUseAssets = $query->clone()->byStatus('in_use')->count();
            $maintenanceAssets = $query->clone()->byStatus('maintenance')->count();
            $disposedAssets = $query->clone()->byStatus('disposed')->count();

            // Advanced statistics
            $warrantyExpiring = $query->clone()->warrantyExpiring(30)->count();
            $highValueAssets = $query->clone()->where('current_value', '>', 10000)->count();
            $oldAssets = $query->clone()->where('purchase_date', '<', Carbon::now()->subYears(5))->count();

            // Category breakdown
            $categoryBreakdown = $query->clone()
                ->select('category_id', DB::raw('count(*) as count'))
                ->with('category:id,name')
                ->groupBy('category_id')
                ->get()
                ->pluck('count', 'category.name')
                ->toArray();

            // Centre distribution
            $centreDistribution = [];
            if (!$centreId) {
                $centreDistribution = $query->clone()
                    ->select('centre_id', DB::raw('count(*) as count'))
                    ->with('centre:centre_id,centre_name')
                    ->groupBy('centre_id')
                    ->get()
                    ->pluck('count', 'centre.centre_name')
                    ->toArray();
            }

            return [
                'overview' => [
                    'total_assets' => $totalAssets,
                    'available' => $availableAssets,
                    'in_use' => $inUseAssets,
                    'maintenance' => $maintenanceAssets,
                    'disposed' => $disposedAssets,
                    'utilization_rate' => $totalAssets > 0 ? round(($inUseAssets / $totalAssets) * 100, 2) : 0,
                ],
                'alerts' => [
                    'warranty_expiring' => $warrantyExpiring,
                    'requires_maintenance' => AssetMaintenance::getRequiringAttentionCount($centreId),
                    'high_value_assets' => $highValueAssets,
                    'aging_assets' => $oldAssets,
                ],
                'breakdown' => [
                    'by_category' => $categoryBreakdown,
                    'by_centre' => $centreDistribution,
                ],
                'financial' => [
                    'total_value' => $query->clone()->sum('current_value'),
                    'purchase_value' => $query->clone()->sum('purchase_price'),
                    'depreciation_total' => $query->clone()->sum(DB::raw('purchase_price - current_value')),
                ]
            ];
        } catch (Exception $e) {
            Log::error('Error getting global asset statistics', [
                'error' => $e->getMessage(),
                'centre_id' => $centreId
            ]);
            return $this->getEmptyStatistics();
        }
    }

    /**
     * Transfer asset between centres
     *
     * @param int $assetId
     * @param string $fromCentreId
     * @param string $toCentreId
     * @param int $performedBy
     * @param string|null $reason
     * @return bool
     */
    public function transferAssetBetweenCentres(int $assetId, string $fromCentreId, string $toCentreId, int $performedBy, ?string $reason = null): bool
    {
        try {
            DB::beginTransaction();

            $asset = Asset::findOrFail($assetId);
            
            // Validate transfer
            if ($asset->centre_id !== $fromCentreId) {
                throw new Exception('Asset does not belong to the source centre');
            }

            if ($asset->status === 'in_use') {
                throw new Exception('Cannot transfer asset that is currently in use');
            }

            // Update asset centre
            $asset->update([
                'centre_id' => $toCentreId,
                'location' => 'Transferred to ' . Centre::find($toCentreId)->centre_name,
                'assigned_to' => null,
                'assigned_date' => null,
            ]);

            // Record movement
            AssetMovement::create([
                'asset_id' => $assetId,
                'movement_type' => 'centre_transfer',
                'from_centre_id' => $fromCentreId,
                'to_centre_id' => $toCentreId,
                'movement_date' => Carbon::now(),
                'performed_by' => $performedBy,
                'reason' => $reason ?? 'Inter-centre transfer',
                'status' => 'completed'
            ]);

            DB::commit();
            
            Log::info('Asset transferred between centres', [
                'asset_id' => $assetId,
                'from_centre' => $fromCentreId,
                'to_centre' => $toCentreId,
                'performed_by' => $performedBy
            ]);

            return true;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error transferring asset between centres', [
                'asset_id' => $assetId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Get asset utilization report
     *
     * @param string|null $centreId
     * @param Carbon|null $startDate
     * @param Carbon|null $endDate
     * @return array
     */
    public function getAssetUtilizationReport($centreId = null, Carbon $startDate = null, Carbon $endDate = null): array
    {
        try {
            $startDate = $startDate ?? Carbon::now()->startOfMonth();
            $endDate = $endDate ?? Carbon::now()->endOfMonth();

            $query = AssetMovement::with(['asset', 'asset.category'])
                ->whereBetween('movement_date', [$startDate, $endDate]);

            if ($centreId) {
                $query->whereHas('asset', function($q) use ($centreId) {
                    $q->where('centre_id', $centreId);
                });
            }

            $movements = $query->get();

            // Calculate utilization metrics
            $totalMovements = $movements->count();
            $assignmentMovements = $movements->where('movement_type', 'assignment')->count();
            $returnMovements = $movements->where('movement_type', 'return')->count();
            $maintenanceMovements = $movements->where('movement_type', 'maintenance')->count();

            // Most utilized assets
            $mostUtilized = $movements
                ->groupBy('asset_id')
                ->map(function($assetMovements) {
                    return [
                        'asset' => $assetMovements->first()->asset,
                        'movement_count' => $assetMovements->count(),
                        'last_movement' => $assetMovements->sortByDesc('movement_date')->first()
                    ];
                })
                ->sortByDesc('movement_count')
                ->take(10)
                ->values();

            // Category utilization
            $categoryUtilization = $movements
                ->groupBy('asset.category.name')
                ->map(function($categoryMovements, $categoryName) {
                    return [
                        'category' => $categoryName,
                        'movement_count' => $categoryMovements->count(),
                        'unique_assets' => $categoryMovements->pluck('asset_id')->unique()->count()
                    ];
                })
                ->sortByDesc('movement_count')
                ->values();

            return [
                'period' => [
                    'start_date' => $startDate->format('Y-m-d'),
                    'end_date' => $endDate->format('Y-m-d'),
                    'days' => $startDate->diffInDays($endDate) + 1
                ],
                'summary' => [
                    'total_movements' => $totalMovements,
                    'assignments' => $assignmentMovements,
                    'returns' => $returnMovements,
                    'maintenance' => $maintenanceMovements,
                    'avg_movements_per_day' => round($totalMovements / ($startDate->diffInDays($endDate) + 1), 2)
                ],
                'most_utilized_assets' => $mostUtilized,
                'category_utilization' => $categoryUtilization
            ];
        } catch (Exception $e) {
            Log::error('Error generating asset utilization report', [
                'error' => $e->getMessage(),
                'centre_id' => $centreId
            ]);
            return [];
        }
    }

    /**
     * Get asset maintenance schedule and alerts
     *
     * @param string|null $centreId
     * @return array
     */
    public function getMaintenanceSchedule($centreId = null): array
    {
        try {
            $query = AssetMaintenance::with(['asset', 'asset.centre', 'scheduledBy', 'performedBy']);

            if ($centreId) {
                $query->whereHas('asset', function($q) use ($centreId) {
                    $q->where('centre_id', $centreId);
                });
            }

            // Overdue maintenance
            $overdue = $query->clone()
                ->where('scheduled_date', '<', Carbon::now())
                ->where('status', 'scheduled')
                ->orderBy('scheduled_date', 'asc')
                ->get();

            // Upcoming maintenance (next 30 days)
            $upcoming = $query->clone()
                ->whereBetween('scheduled_date', [Carbon::now(), Carbon::now()->addDays(30)])
                ->where('status', 'scheduled')
                ->orderBy('scheduled_date', 'asc')
                ->get();

            // Recent completed maintenance
            $recentCompleted = $query->clone()
                ->where('status', 'completed')
                ->where('completed_date', '>=', Carbon::now()->subDays(30))
                ->orderBy('completed_date', 'desc')
                ->limit(10)
                ->get();

            // Maintenance frequency analysis
            $maintenanceFrequency = Asset::with('maintenanceRecords')
                ->when($centreId, function($q) use ($centreId) {
                    $q->where('centre_id', $centreId);
                })
                ->get()
                ->map(function($asset) {
                    $completedMaintenance = $asset->maintenanceRecords->where('status', 'completed');
                    return [
                        'asset' => $asset,
                        'maintenance_count' => $completedMaintenance->count(),
                        'avg_interval_days' => $this->calculateAverageMaintenanceInterval($completedMaintenance),
                        'last_maintenance' => $completedMaintenance->sortByDesc('completed_date')->first()
                    ];
                })
                ->sortByDesc('maintenance_count')
                ->take(10);

            return [
                'alerts' => [
                    'overdue_count' => $overdue->count(),
                    'upcoming_count' => $upcoming->count(),
                    'critical_assets' => $this->getCriticalMaintenanceAssets($centreId)
                ],
                'schedules' => [
                    'overdue' => $overdue,
                    'upcoming' => $upcoming,
                    'recent_completed' => $recentCompleted
                ],
                'analysis' => [
                    'high_maintenance_assets' => $maintenanceFrequency,
                    'maintenance_cost_trend' => $this->getMaintenanceCostTrend($centreId)
                ]
            ];
        } catch (Exception $e) {
            Log::error('Error getting maintenance schedule', [
                'error' => $e->getMessage(),
                'centre_id' => $centreId
            ]);
            return [];
        }
    }

    /**
     * Generate comprehensive asset audit report
     *
     * @param string|null $centreId
     * @return array
     */
    public function generateAuditReport($centreId = null): array
    {
        try {
            $query = Asset::with(['category', 'centre', 'assignedTo', 'latestMaintenance']);

            if ($centreId) {
                $query->where('centre_id', $centreId);
            }

            $assets = $query->get();

            // Audit findings
            $findings = [
                'missing_serial_numbers' => $assets->whereNull('serial_number')->count(),
                'missing_purchase_dates' => $assets->whereNull('purchase_date')->count(),
                'expired_warranties' => $assets->where('warranty_expiry', '<', Carbon::now())->count(),
                'unassigned_assets' => $assets->where('status', 'available')->whereNull('assigned_to')->count(),
                'high_value_untracked' => $assets->where('current_value', '>', 5000)->whereNull('assigned_to')->count(),
            ];

            // Asset condition analysis
            $conditionAnalysis = $assets->groupBy('condition')->map->count();

            // Age analysis
            $ageAnalysis = [
                'new_assets' => $assets->where('purchase_date', '>=', Carbon::now()->subYear())->count(),
                'aging_assets' => $assets->where('purchase_date', '<', Carbon::now()->subYears(3))->count(),
                'legacy_assets' => $assets->where('purchase_date', '<', Carbon::now()->subYears(7))->count(),
            ];

            // Value analysis
            $totalCurrentValue = $assets->sum('current_value');
            $totalPurchaseValue = $assets->sum('purchase_price');
            $depreciationRate = $totalPurchaseValue > 0 ? 
                round((($totalPurchaseValue - $totalCurrentValue) / $totalPurchaseValue) * 100, 2) : 0;

            return [
                'audit_summary' => [
                    'total_assets_audited' => $assets->count(),
                    'audit_date' => Carbon::now(),
                    'centres_covered' => $centreId ? 1 : $assets->pluck('centre_id')->unique()->count()
                ],
                'data_quality' => $findings,
                'asset_analysis' => [
                    'condition_breakdown' => $conditionAnalysis,
                    'age_analysis' => $ageAnalysis,
                    'value_analysis' => [
                        'total_current_value' => $totalCurrentValue,
                        'total_purchase_value' => $totalPurchaseValue,
                        'depreciation_rate' => $depreciationRate
                    ]
                ],
                'recommendations' => $this->generateAuditRecommendations($findings, $assets)
            ];
        } catch (Exception $e) {
            Log::error('Error generating audit report', [
                'error' => $e->getMessage(),
                'centre_id' => $centreId
            ]);
            return [];
        }
    }

    /**
     * Private helper methods
     */
    private function getEmptyStatistics(): array
    {
        return [
            'overview' => array_fill_keys(['total_assets', 'available', 'in_use', 'maintenance', 'disposed', 'utilization_rate'], 0),
            'alerts' => array_fill_keys(['warranty_expiring', 'requires_maintenance', 'high_value_assets', 'aging_assets'], 0),
            'breakdown' => ['by_category' => [], 'by_centre' => []],
            'financial' => array_fill_keys(['total_value', 'purchase_value', 'depreciation_total'], 0)
        ];
    }

    private function calculateAverageMaintenanceInterval($maintenanceRecords): int
    {
        if ($maintenanceRecords->count() < 2) {
            return 0;
        }

        $intervals = [];
        $sorted = $maintenanceRecords->sortBy('completed_date');
        
        for ($i = 1; $i < $sorted->count(); $i++) {
            $intervals[] = $sorted->values()[$i]->completed_date->diffInDays($sorted->values()[$i-1]->completed_date);
        }

        return $intervals ? round(array_sum($intervals) / count($intervals)) : 0;
    }

    private function getCriticalMaintenanceAssets($centreId): int
    {
        return Asset::when($centreId, function($q) use ($centreId) {
                $q->where('centre_id', $centreId);
            })
            ->where('condition', 'poor')
            ->orWhere(function($q) {
                $q->whereHas('latestMaintenance', function($maintenance) {
                    $maintenance->where('completed_date', '<', Carbon::now()->subMonths(6));
                });
            })
            ->count();
    }

    private function getMaintenanceCostTrend($centreId): array
    {
        $months = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $cost = AssetMaintenance::when($centreId, function($q) use ($centreId) {
                    $q->whereHas('asset', function($asset) use ($centreId) {
                        $asset->where('centre_id', $centreId);
                    });
                })
                ->whereMonth('completed_date', $month->month)
                ->whereYear('completed_date', $month->year)
                ->sum('cost');
            
            $months[] = [
                'month' => $month->format('M Y'),
                'cost' => $cost
            ];
        }
        return $months;
    }

    private function generateAuditRecommendations(array $findings, $assets): array
    {
        $recommendations = [];

        if ($findings['missing_serial_numbers'] > 0) {
            $recommendations[] = "Update {$findings['missing_serial_numbers']} assets with missing serial numbers";
        }

        if ($findings['high_value_untracked'] > 0) {
            $recommendations[] = "Assign tracking/responsible personnel to {$findings['high_value_untracked']} high-value untracked assets";
        }

        if ($findings['expired_warranties'] > 0) {
            $recommendations[] = "Review warranty status for {$findings['expired_warranties']} assets with expired warranties";
        }

        // Add more recommendations based on specific findings
        $poorConditionAssets = $assets->where('condition', 'poor')->count();
        if ($poorConditionAssets > 0) {
            $recommendations[] = "Schedule maintenance or replacement for {$poorConditionAssets} assets in poor condition";
        }

        return $recommendations;
    }
}