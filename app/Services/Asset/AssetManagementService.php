<?php

namespace App\Services\Asset;

use App\Models\Asset;
use App\Models\AssetType;
use App\Models\AssetLocation;
use App\Models\AssetMovement;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Carbon\Carbon;
use Exception;

class AssetManagementService
{
    private int $cacheTimeout = 300; // 5 minutes

    /**
     * Get comprehensive asset dashboard data
     */
    public function getDashboardData(?int $centreId = null): array
    {
        return Cache::remember("asset_dashboard_{$centreId}", $this->cacheTimeout, function () use ($centreId) {
            return [
                'statistics' => $this->getAssetStatistics($centreId),
                'distribution' => $this->getAssetDistribution($centreId),
                'maintenance_alerts' => $this->getMaintenanceAlerts($centreId),
                'financial_metrics' => $this->getFinancialMetrics($centreId),
                'recent_movements' => $this->getRecentMovements($centreId),
                'status_breakdown' => $this->getStatusBreakdown($centreId),
                'utilization_rates' => $this->getUtilizationRates($centreId),
            ];
        });
    }

    /**
     * Create new asset with comprehensive validation
     */
    public function createAsset(array $data): Asset
    {
        try {
            DB::beginTransaction();

            // Generate unique asset code
            $data['asset_code'] = $this->generateAssetCode($data['asset_type_id']);
            
            // Create asset
            $asset = Asset::create($data);
            
            // Generate QR code
            $this->generateQRCode($asset);
            
            // Create initial movement record
            if (isset($data['location_id'])) {
                $this->recordMovement($asset, null, $data['location_id'], 'Asset Created');
            }
            
            // Schedule initial maintenance if required
            if ($asset->assetType && $asset->assetType->maintenance_schedule) {
                $this->scheduleInitialMaintenance($asset);
            }

            DB::commit();
            
            Log::info('Asset created successfully', [
                'asset_id' => $asset->id,
                'asset_code' => $asset->asset_code,
                'created_by' => auth()->id() ?? session('id')
            ]);

            return $asset;

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Asset creation failed', [
                'error' => $e->getMessage(),
                'data' => $data
            ]);
            throw $e;
        }
    }

    /**
     * Update asset with change tracking
     */
    public function updateAsset(Asset $asset, array $data): Asset
    {
        try {
            $oldValues = $asset->toArray();
            
            $asset->update($data);
            
            // Track significant changes
            $this->trackAssetChanges($asset, $oldValues, $data);
            
            // Handle location change
            if (isset($data['location_id']) && $data['location_id'] !== $oldValues['location_id']) {
                $this->recordMovement(
                    $asset, 
                    $oldValues['location_id'], 
                    $data['location_id'], 
                    'Asset Updated'
                );
            }

            Log::info('Asset updated successfully', [
                'asset_id' => $asset->id,
                'changes' => array_diff_assoc($data, $oldValues),
                'updated_by' => auth()->id() ?? session('id')
            ]);

            return $asset->fresh();

        } catch (Exception $e) {
            Log::error('Asset update failed', [
                'asset_id' => $asset->id,
                'error' => $e->getMessage(),
                'data' => $data
            ]);
            throw $e;
        }
    }

    /**
     * Advanced asset search with filters
     */
    public function searchAssets(array $filters): LengthAwarePaginator
    {
        $query = Asset::with(['assetType', 'location', 'centre', 'assignedTo'])
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'LIKE', "%{$search}%")
                      ->orWhere('asset_code', 'LIKE', "%{$search}%")
                      ->orWhere('description', 'LIKE', "%{$search}%")
                      ->orWhereHas('assetType', function ($subQ) use ($search) {
                          $subQ->where('name', 'LIKE', "%{$search}%");
                      });
                });
            })
            ->when($filters['status'] ?? null, function ($query, $status) {
                $query->where('status', $status);
            })
            ->when($filters['centre_id'] ?? null, function ($query, $centreId) {
                $query->where('centre_id', $centreId);
            })
            ->when($filters['asset_type_id'] ?? null, function ($query, $typeId) {
                $query->where('asset_type_id', $typeId);
            })
            ->when($filters['location_id'] ?? null, function ($query, $locationId) {
                $query->where('location_id', $locationId);
            })
            ->when($filters['value_range'] ?? null, function ($query, $range) {
                // $query->whereBetween('current_value', [$range['min'], $range['max']]); // Column not available
            });

        return $query->paginate($filters['per_page'] ?? 15);
    }

    /**
     * Get asset statistics
     */
    private function getAssetStatistics(?int $centreId = null): array
    {
        $query = Asset::query();
        
        if ($centreId) {
            $query->where('centre_id', $centreId);
        }

        return [
            'total_assets' => $query->count(),
            'total_value' => 0, // current_value column not available
            'available_assets' => $query->where('status', 'available')->count(),
            'in_use_assets' => $query->where('status', 'in-use')->count(),
            'maintenance_assets' => $query->where('status', 'maintenance')->count(),
            'retired_assets' => $query->where('status', 'retired')->count(),
            'average_age' => $this->calculateAverageAge($query),
            'maintenance_cost_mtd' => $this->getMaintenanceCostMTD($centreId),
        ];
    }

    /**
     * Get asset distribution by type and location
     */
    private function getAssetDistribution(?int $centreId = null): array
    {
        $query = Asset::with(['assetType', 'location']);
        
        if ($centreId) {
            $query->where('centre_id', $centreId);
        }

        return [
            'by_type' => $query->selectRaw('asset_type_id, COUNT(*) as count')
                ->groupBy('asset_type_id')
                ->with('assetType:id,name')
                ->get()
                ->toArray(),
            'by_location' => $query->selectRaw('location_id, COUNT(*) as count')
                ->groupBy('location_id')
                ->with('location:id,name')
                ->get()
                ->toArray(),
            'by_status' => $query->selectRaw('status, COUNT(*) as count')
                ->groupBy('status')
                ->get()
                ->toArray(),
        ];
    }

    /**
     * Get maintenance alerts
     */
    private function getMaintenanceAlerts(?int $centreId = null): array
    {
        $query = Asset::with(['assetType']);
        
        if ($centreId) {
            $query->where('centre_id', $centreId);
        }

        $overdueAssets = $query->whereRaw('
            last_maintenance_date IS NOT NULL 
            AND DATE_ADD(last_maintenance_date, INTERVAL COALESCE(maintenance_interval, 365) DAY) < NOW()
        ')->get();

        $upcomingAssets = $query->whereRaw('
            last_maintenance_date IS NOT NULL 
            AND DATE_ADD(last_maintenance_date, INTERVAL COALESCE(maintenance_interval, 365) DAY) BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 30 DAY)
        ')->get();

        return [
            'overdue' => $overdueAssets->toArray(),
            'upcoming' => $upcomingAssets->toArray(),
            'overdue_count' => $overdueAssets->count(),
            'upcoming_count' => $upcomingAssets->count(),
        ];
    }

    /**
     * Get financial metrics
     */
    private function getFinancialMetrics(?int $centreId = null): array
    {
        $query = Asset::query();
        
        if ($centreId) {
            $query->where('centre_id', $centreId);
        }

        $totalPurchaseValue = $query->sum('purchase_price');
        $totalCurrentValue = 0; // current_value column not available

        return [
            'total_purchase_value' => $totalPurchaseValue,
            'total_current_value' => $totalCurrentValue,
            'total_depreciation' => $totalPurchaseValue - $totalCurrentValue,
            'depreciation_percentage' => $totalPurchaseValue > 0 ? 
                (($totalPurchaseValue - $totalCurrentValue) / $totalPurchaseValue) * 100 : 0,
            'maintenance_cost_mtd' => $this->getMaintenanceCostMTD($centreId),
            'average_asset_value' => 0, // current_value column not available
        ];
    }

    /**
     * Get recent asset movements
     */
    private function getRecentMovements(?int $centreId = null): array
    {
        $query = AssetMovement::with(['asset', 'fromLocation', 'toLocation', 'movedBy'])
            ->latest()
            ->limit(10);

        if ($centreId) {
            $query->whereHas('asset', function ($q) use ($centreId) {
                $q->where('centre_id', $centreId);
            });
        }

        return $query->get()->toArray();
    }

    /**
     * Get status breakdown
     */
    private function getStatusBreakdown(?int $centreId = null): array
    {
        $query = Asset::query();
        
        if ($centreId) {
            $query->where('centre_id', $centreId);
        }

        return $query->selectRaw('status, COUNT(*) as count, 0 as total_value') // current_value column not available
            ->groupBy('status')
            ->get()
            ->keyBy('status')
            ->toArray();
    }

    /**
     * Get utilization rates
     */
    private function getUtilizationRates(?int $centreId = null): array
    {
        $query = Asset::query();
        
        if ($centreId) {
            $query->where('centre_id', $centreId);
        }

        $totalAssets = $query->count();
        $inUseAssets = $query->where('status', 'in-use')->count();
        
        return [
            'overall_utilization' => $totalAssets > 0 ? ($inUseAssets / $totalAssets) * 100 : 0,
            'available_rate' => $totalAssets > 0 ? ($query->where('status', 'available')->count() / $totalAssets) * 100 : 0,
            'maintenance_rate' => $totalAssets > 0 ? ($query->where('status', 'maintenance')->count() / $totalAssets) * 100 : 0,
        ];
    }

    /**
     * Record asset movement
     */
    private function recordMovement(Asset $asset, ?int $fromLocationId, ?int $toLocationId, string $reason): void
    {
        AssetMovement::create([
            'asset_id' => $asset->id,
            'from_location_id' => $fromLocationId,
            'to_location_id' => $toLocationId,
            'moved_by_id' => auth()->id() ?? session('id'),
            'movement_reason' => $reason,
            'movement_date' => now(),
            'notes' => request('movement_notes'),
        ]);
    }

    /**
     * Generate unique asset code
     */
    private function generateAssetCode(int $assetTypeId): string
    {
        $assetType = AssetType::find($assetTypeId);
        $prefix = $assetType ? strtoupper(substr($assetType->name, 0, 3)) : 'AST';
        $year = date('Y');
        
        $sequence = Asset::where('asset_type_id', $assetTypeId)
            ->whereYear('created_at', $year)
            ->count() + 1;
        
        return sprintf('%s%s%04d', $prefix, $year, $sequence);
    }

    /**
     * Calculate average age of assets
     */
    private function calculateAverageAge($query): float
    {
        $assets = $query->whereNotNull('purchase_date')->get();
        
        if ($assets->isEmpty()) {
            return 0;
        }

        $totalDays = $assets->sum(function ($asset) {
            return Carbon::parse($asset->purchase_date)->diffInDays(now());
        });

        return $totalDays / $assets->count();
    }

    /**
     * Get maintenance cost for current month
     */
    private function getMaintenanceCostMTD(?int $centreId): float
    {
        // Placeholder - will be implemented when AssetMaintenance model is created
        return 0;
    }

    /**
     * Generate QR code for asset
     */
    private function generateQRCode(Asset $asset): void
    {
        // Placeholder for QR code generation
        // Will be implemented in future iteration
        $asset->update(['qr_code' => 'qr_' . $asset->asset_code . '.png']);
    }

    /**
     * Schedule initial maintenance
     */
    private function scheduleInitialMaintenance(Asset $asset): void
    {
        // Placeholder for maintenance scheduling
        // Will be implemented when AssetMaintenance model is created
    }

    /**
     * Track asset changes
     */
    private function trackAssetChanges(Asset $asset, array $oldValues, array $newValues): void
    {
        $changes = array_diff_assoc($newValues, $oldValues);
        
        if (!empty($changes)) {
            Log::info('Asset changes tracked', [
                'asset_id' => $asset->id,
                'asset_code' => $asset->asset_code,
                'changes' => $changes,
                'changed_by' => auth()->id() ?? session('id'),
                'timestamp' => now()
            ]);
        }
    }
}