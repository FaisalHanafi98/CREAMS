<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

/**
 * Enhanced Asset Model
 * 
 * This model represents the unified asset management system that consolidates
 * and enhances the existing asset functionality with comprehensive features.
 */
class Asset extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'assets_enhanced';

    protected $fillable = [
        'asset_code', 'name', 'description', 'asset_type_id',
        'centre_id', 'location_id', 'assigned_to_id',
        'brand', 'model', 'serial_number', 'purchase_date', 
        'purchase_price', 'current_value', 'status', 'warranty_date',
        'last_maintenance_date', 'next_maintenance_date', 'maintenance_interval',
        'image_path', 'qr_code', 'rfid_tag', 'barcode', 'notes'
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'warranty_date' => 'date',
        'last_maintenance_date' => 'date',
        'next_maintenance_date' => 'date',
        'purchase_price' => 'decimal:2',
        'current_value' => 'decimal:2',
        'maintenance_interval' => 'integer',
    ];

    protected $dates = ['deleted_at'];

    protected $appends = [
        'age_in_days', 'depreciation', 'depreciation_percentage',
        'is_under_warranty', 'needs_maintenance', 'image_url', 'qr_code_url'
    ];

    /**
     * Asset status constants
     */
    const STATUS_AVAILABLE = 'available';
    const STATUS_IN_USE = 'in-use';
    const STATUS_MAINTENANCE = 'maintenance';
    const STATUS_RETIRED = 'retired';
    const STATUS_DISPOSED = 'disposed';

    /**
     * Get all available statuses
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_AVAILABLE => 'Available',
            self::STATUS_IN_USE => 'In Use',
            self::STATUS_MAINTENANCE => 'Under Maintenance',
            self::STATUS_RETIRED => 'Retired',
            self::STATUS_DISPOSED => 'Disposed',
        ];
    }

    // =============================================
    // RELATIONSHIPS
    // =============================================

    /**
     * Get the asset type that owns the asset
     */
    public function assetType(): BelongsTo
    {
        return $this->belongsTo(AssetType::class);
    }

    /**
     * Get the centre that owns the asset
     */
    public function centre(): BelongsTo
    {
        return $this->belongsTo(Centres::class);
    }

    /**
     * Get the location where the asset is placed
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(AssetLocation::class);
    }

    /**
     * Get the user to whom the asset is assigned
     */
    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(Users::class, 'assigned_to_id');
    }

    /**
     * Get all movement records for this asset
     */
    public function movements(): HasMany
    {
        return $this->hasMany(AssetMovement::class)->latest('movement_date');
    }

    /**
     * Get all maintenance records for this asset
     */
    public function maintenanceRecords(): HasMany
    {
        return $this->hasMany(AssetMaintenance::class)->latest('scheduled_date');
    }

    /**
     * Get the latest movement record
     */
    public function latestMovement(): BelongsTo
    {
        return $this->belongsTo(AssetMovement::class, 'id', 'asset_id')
                    ->latest('movement_date');
    }

    /**
     * Get pending maintenance records
     */
    public function pendingMaintenance(): HasMany
    {
        return $this->hasMany(AssetMaintenance::class)
                    ->whereIn('status', ['scheduled', 'in-progress']);
    }

    // =============================================
    // QUERY SCOPES
    // =============================================

    /**
     * Scope a query to only include available assets
     */
    public function scopeAvailable($query)
    {
        return $query->where('status', self::STATUS_AVAILABLE);
    }

    /**
     * Scope a query to only include assets in use
     */
    public function scopeInUse($query)
    {
        return $query->where('status', self::STATUS_IN_USE);
    }

    /**
     * Scope a query to only include assets under maintenance
     */
    public function scopeInMaintenance($query)
    {
        return $query->where('status', self::STATUS_MAINTENANCE);
    }

    /**
     * Scope a query to only include retired assets
     */
    public function scopeRetired($query)
    {
        return $query->where('status', self::STATUS_RETIRED);
    }

    /**
     * Scope a query to filter by status
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to filter by centre
     */
    public function scopeForCentre($query, int $centreId)
    {
        return $query->where('centre_id', $centreId);
    }

    /**
     * Scope a query to filter by assigned user
     */
    public function scopeAssignedTo($query, int $userId)
    {
        return $query->where('assigned_to_id', $userId);
    }

    /**
     * Scope a query to filter by asset type
     */
    public function scopeOfType($query, int $assetTypeId)
    {
        return $query->where('asset_type_id', $assetTypeId);
    }

    /**
     * Scope a query to filter by location
     */
    public function scopeAtLocation($query, int $locationId)
    {
        return $query->where('location_id', $locationId);
    }

    /**
     * Scope a query to find assets needing maintenance
     */
    public function scopeNeedingMaintenance($query)
    {
        return $query->whereRaw('
            next_maintenance_date IS NOT NULL 
            AND next_maintenance_date <= NOW()
        ');
    }

    /**
     * Scope a query to find assets with expired warranties
     */
    public function scopeExpiredWarranty($query)
    {
        return $query->whereNotNull('warranty_date')
                     ->where('warranty_date', '<', now());
    }

    /**
     * Scope a query to search assets by text
     */
    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'LIKE', "%{$search}%")
              ->orWhere('asset_code', 'LIKE', "%{$search}%")
              ->orWhere('description', 'LIKE', "%{$search}%")
              ->orWhere('brand', 'LIKE', "%{$search}%")
              ->orWhere('model', 'LIKE', "%{$search}%")
              ->orWhere('serial_number', 'LIKE', "%{$search}%");
        });
    }

    // =============================================
    // ACCESSORS & MUTATORS
    // =============================================

    /**
     * Get the asset's age in days
     */
    public function getAgeInDaysAttribute(): int
    {
        return $this->purchase_date ? $this->purchase_date->diffInDays(now()) : 0;
    }

    /**
     * Get the asset's depreciation amount
     */
    public function getDepreciationAttribute(): float
    {
        if (!$this->purchase_price || !$this->current_value) {
            return 0;
        }
        return (float)($this->purchase_price - $this->current_value);
    }

    /**
     * Get the asset's depreciation percentage
     */
    public function getDepreciationPercentageAttribute(): float
    {
        if (!$this->purchase_price || $this->purchase_price == 0) {
            return 0;
        }
        return (($this->purchase_price - $this->current_value) / $this->purchase_price) * 100;
    }

    /**
     * Check if the asset is under warranty
     */
    public function getIsUnderWarrantyAttribute(): bool
    {
        return $this->warranty_date && $this->warranty_date->isFuture();
    }

    /**
     * Check if the asset needs maintenance
     */
    public function getNeedsMaintenanceAttribute(): bool
    {
        return $this->next_maintenance_date && $this->next_maintenance_date->isPast();
    }

    /**
     * Get the asset's image URL
     */
    public function getImageUrlAttribute(): string
    {
        if ($this->image_path) {
            return asset('storage/' . $this->image_path);
        }
        return asset('images/default-asset.png');
    }

    /**
     * Get the asset's QR code URL
     */
    public function getQrCodeUrlAttribute(): string
    {
        if ($this->qr_code) {
            return asset('storage/qrcodes/' . $this->qr_code);
        }
        return '';
    }

    /**
     * Get formatted purchase price
     */
    public function getFormattedPurchasePriceAttribute(): string
    {
        return 'RM ' . number_format($this->purchase_price, 2);
    }

    /**
     * Get formatted current value
     */
    public function getFormattedCurrentValueAttribute(): string
    {
        return 'RM ' . number_format($this->current_value, 2);
    }

    /**
     * Get status badge class for UI
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match($this->status) {
            self::STATUS_AVAILABLE => 'badge-success',
            self::STATUS_IN_USE => 'badge-primary',
            self::STATUS_MAINTENANCE => 'badge-warning',
            self::STATUS_RETIRED => 'badge-secondary',
            self::STATUS_DISPOSED => 'badge-danger',
            default => 'badge-secondary',
        };
    }

    // =============================================
    // BUSINESS LOGIC METHODS
    // =============================================

    /**
     * Assign asset to a user
     */
    public function assignTo(int $userId, string $reason = 'Asset Assignment'): bool
    {
        $this->assigned_to_id = $userId;
        $this->status = self::STATUS_IN_USE;
        
        $success = $this->save();
        
        if ($success) {
            // Log the assignment
            \Log::info('Asset assigned', [
                'asset_id' => $this->id,
                'asset_code' => $this->asset_code,
                'assigned_to' => $userId,
                'reason' => $reason
            ]);
        }
        
        return $success;
    }

    /**
     * Release asset from current assignment
     */
    public function release(string $reason = 'Asset Release'): bool
    {
        $oldAssignee = $this->assigned_to_id;
        
        $this->assigned_to_id = null;
        $this->status = self::STATUS_AVAILABLE;
        
        $success = $this->save();
        
        if ($success) {
            \Log::info('Asset released', [
                'asset_id' => $this->id,
                'asset_code' => $this->asset_code,
                'previously_assigned_to' => $oldAssignee,
                'reason' => $reason
            ]);
        }
        
        return $success;
    }

    /**
     * Move asset to a new location
     */
    public function moveTo(int $locationId, string $reason = 'Asset Movement'): bool
    {
        $oldLocationId = $this->location_id;
        $this->location_id = $locationId;
        
        $success = $this->save();
        
        if ($success) {
            // Create movement record
            AssetMovement::create([
                'asset_id' => $this->id,
                'from_location_id' => $oldLocationId,
                'to_location_id' => $locationId,
                'moved_by_id' => auth()->id() ?? session('id'),
                'movement_reason' => $reason,
                'movement_date' => now(),
            ]);
        }
        
        return $success;
    }

    /**
     * Update asset value (for depreciation tracking)
     */
    public function updateValue(float $newValue, string $reason = 'Value Update'): bool
    {
        $oldValue = $this->current_value;
        $this->current_value = $newValue;
        
        $success = $this->save();
        
        if ($success) {
            \Log::info('Asset value updated', [
                'asset_id' => $this->id,
                'asset_code' => $this->asset_code,
                'old_value' => $oldValue,
                'new_value' => $newValue,
                'reason' => $reason
            ]);
        }
        
        return $success;
    }

    /**
     * Schedule next maintenance
     */
    public function scheduleNextMaintenance(Carbon $date = null): bool
    {
        if (!$date && $this->maintenance_interval) {
            $date = now()->addDays($this->maintenance_interval);
        } elseif (!$date) {
            $date = now()->addYear(); // Default to 1 year if no interval set
        }
        
        $this->next_maintenance_date = $date;
        return $this->save();
    }

    /**
     * Mark asset as retired
     */
    public function retire(string $reason = 'Asset Retirement'): bool
    {
        $this->status = self::STATUS_RETIRED;
        $this->assigned_to_id = null;
        
        $success = $this->save();
        
        if ($success) {
            \Log::info('Asset retired', [
                'asset_id' => $this->id,
                'asset_code' => $this->asset_code,
                'reason' => $reason
            ]);
        }
        
        return $success;
    }

    /**
     * Check if asset can be assigned
     */
    public function canBeAssigned(): bool
    {
        return in_array($this->status, [self::STATUS_AVAILABLE]) && !$this->assigned_to_id;
    }

    /**
     * Check if asset is operational
     */
    public function isOperational(): bool
    {
        return in_array($this->status, [self::STATUS_AVAILABLE, self::STATUS_IN_USE]);
    }

    /**
     * Calculate estimated replacement date
     */
    public function getEstimatedReplacementDate(): ?Carbon
    {
        if (!$this->assetType || !$this->assetType->expected_lifespan) {
            return null;
        }
        
        return $this->purchase_date?->addDays($this->assetType->expected_lifespan);
    }

    // =============================================
    // STATIC UTILITY METHODS
    // =============================================

    /**
     * Generate asset statistics report
     */
    public static function getStatistics(?int $centreId = null): array
    {
        $query = self::query();
        
        if ($centreId) {
            $query->where('centre_id', $centreId);
        }

        return [
            'total_assets' => $query->count(),
            'total_value' => $query->sum('current_value'),
            'available_count' => $query->where('status', self::STATUS_AVAILABLE)->count(),
            'in_use_count' => $query->where('status', self::STATUS_IN_USE)->count(),
            'maintenance_count' => $query->where('status', self::STATUS_MAINTENANCE)->count(),
            'retired_count' => $query->where('status', self::STATUS_RETIRED)->count(),
            'average_age' => $query->whereNotNull('purchase_date')->get()->avg(function ($asset) {
                return $asset->age_in_days;
            }),
            'needs_maintenance_count' => $query->needingMaintenance()->count(),
            'expired_warranty_count' => $query->expiredWarranty()->count(),
        ];
    }

    /**
     * Get assets by status distribution
     */
    public static function getStatusDistribution(?int $centreId = null): array
    {
        $query = self::selectRaw('status, COUNT(*) as count, SUM(current_value) as total_value')
                     ->groupBy('status');
                     
        if ($centreId) {
            $query->where('centre_id', $centreId);
        }
        
        return $query->get()->keyBy('status')->toArray();
    }

    /**
     * Find assets requiring immediate attention
     */
    public static function getAssetsRequiringAttention(?int $centreId = null): array
    {
        $baseQuery = self::query();
        
        if ($centreId) {
            $baseQuery->where('centre_id', $centreId);
        }
        
        return [
            'needs_maintenance' => $baseQuery->needingMaintenance()->with(['assetType', 'location'])->get(),
            'expired_warranty' => $baseQuery->expiredWarranty()->with(['assetType', 'location'])->get(),
            'high_depreciation' => $baseQuery->whereRaw('
                purchase_price > 0 AND 
                ((purchase_price - current_value) / purchase_price) > 0.8
            ')->with(['assetType', 'location'])->get(),
        ];
    }
}