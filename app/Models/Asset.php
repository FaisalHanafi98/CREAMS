<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

/**
 * Enhanced Asset Model with comprehensive features
 */
class Asset extends Model
{
    use HasFactory;

    protected $table = 'assets';

    protected $fillable = [
        'asset_code',
        'asset_name',
        'description',
        'category_id',
        'centre_id',
        'brand',
        'model',
        'serial_number',
        'purchase_price',
        'purchase_date',
        'warranty_months',
        'condition',
        'status',
        'location',
        'assigned_to',
        'assigned_date',
        'depreciation_rate',
        'current_value',
        'specifications',
        'images',
        'notes',
        'created_by'
    ];

    protected $casts = [
        'purchase_price' => 'decimal:2',
        'current_value' => 'decimal:2',
        'depreciation_rate' => 'decimal:2',
        'purchase_date' => 'date',
        'warranty_expiry' => 'date',
        'assigned_date' => 'date',
        'specifications' => 'array',
        'images' => 'array'
    ];

    protected $appends = [
        'formatted_value',
        'warranty_status',
        'depreciation_percentage',
        'primary_image_url'
    ];

    /**
     * Get the centre that owns the asset
     */
    public function centre(): BelongsTo
    {
        return $this->belongsTo(Centre::class, 'centre_id');
    }

    /**
     * Get the category of the asset
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(AssetCategory::class, 'category_id');
    }

    /**
     * Get the user assigned to this asset
     */
    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Get the user who created this asset
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get all maintenance records for this asset
     */
    public function maintenance(): HasMany
    {
        return $this->hasMany(AssetMaintenance::class, 'asset_id');
    }

    /**
     * Get all movement records for this asset
     */
    public function movements(): HasMany
    {
        return $this->hasMany(AssetMovement::class, 'asset_id');
    }

    /**
     * Get the latest maintenance record
     */
    public function latestMaintenance()
    {
        return $this->hasOne(AssetMaintenance::class, 'asset_id')->latest('scheduled_date');
    }

    /**
     * Get upcoming maintenance
     */
    public function upcomingMaintenance()
    {
        return $this->hasOne(AssetMaintenance::class, 'asset_id')
            ->where('status', 'scheduled')
            ->where('scheduled_date', '>=', Carbon::now())
            ->orderBy('scheduled_date');
    }

    /**
     * Get formatted current value
     */
    public function getFormattedValueAttribute(): string
    {
        return 'RM ' . number_format($this->current_value ?? $this->purchase_price ?? 0, 2);
    }

    /**
     * Get warranty status
     */
    public function getWarrantyStatusAttribute(): string
    {
        if (!$this->warranty_expiry) {
            return 'No Warranty';
        }

        $now = Carbon::now();
        $expiry = Carbon::parse($this->warranty_expiry);

        if ($expiry->isPast()) {
            return 'Expired';
        } elseif ($expiry->diffInDays($now) <= 30) {
            return 'Expiring Soon';
        } else {
            return 'Active';
        }
    }

    /**
     * Get depreciation percentage
     */
    public function getDepreciationPercentageAttribute(): float
    {
        if (!$this->purchase_price || $this->purchase_price == 0) {
            return 0;
        }

        $currentValue = $this->current_value ?? $this->purchase_price;
        $depreciation = $this->purchase_price - $currentValue;
        
        return round(($depreciation / $this->purchase_price) * 100, 2);
    }

    /**
     * Get primary image URL
     */
    public function getPrimaryImageUrlAttribute(): string
    {
        if ($this->images && count($this->images) > 0) {
            return asset('storage/' . $this->images[0]);
        }
        return asset('images/default-asset.png');
    }

    /**
     * Scope to search assets
     */
    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'LIKE', "%{$search}%")
              ->orWhere('asset_code', 'LIKE', "%{$search}%")
              ->orWhere('model', 'LIKE', "%{$search}%")
              ->orWhere('brand', 'LIKE', "%{$search}%")
              ->orWhere('serial_number', 'LIKE', "%{$search}%")
              ->orWhere('location', 'LIKE', "%{$search}%");
        });
    }

    /**
     * Scope to filter by centre
     */
    public function scopeForCentre($query, string $centreId)
    {
        return $query->where('centre_id', $centreId);
    }

    /**
     * Scope to filter by status
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to filter by condition
     */
    public function scopeByCondition($query, string $condition)
    {
        return $query->where('condition', $condition);
    }

    /**
     * Scope to filter by category
     */
    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    /**
     * Scope for available assets
     */
    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }

    /**
     * Scope for assets requiring maintenance
     */
    public function scopeRequiresMaintenance($query)
    {
        return $query->whereHas('upcomingMaintenance', function($q) {
            $q->where('scheduled_date', '<=', Carbon::now()->addDays(7));
        });
    }

    /**
     * Scope for assets with expiring warranty
     */
    public function scopeWarrantyExpiring($query, $days = 30)
    {
        return $query->whereNotNull('warranty_expiry')
            ->where('warranty_expiry', '<=', Carbon::now()->addDays($days))
            ->where('warranty_expiry', '>', Carbon::now());
    }

    /**
     * Check if asset is available for assignment
     */
    public function isAvailable(): bool
    {
        return $this->status === 'available';
    }

    /**
     * Check if asset is assigned
     */
    public function isAssigned(): bool
    {
        return $this->status === 'in_use' && !is_null($this->assigned_to);
    }

    /**
     * Check if warranty is active
     */
    public function hasActiveWarranty(): bool
    {
        return $this->warranty_expiry && Carbon::parse($this->warranty_expiry)->isFuture();
    }

    /**
     * Get status badge class for display
     */
    public function getStatusBadgeClassAttribute(): string
    {
        $classes = [
            'available' => 'badge-success',
            'in_use' => 'badge-primary',
            'maintenance' => 'badge-warning',
            'disposed' => 'badge-danger'
        ];

        return $classes[$this->status] ?? 'badge-secondary';
    }

    /**
     * Get condition badge class for display
     */
    public function getConditionBadgeClassAttribute(): string
    {
        $classes = [
            'new' => 'badge-success',
            'good' => 'badge-info',
            'fair' => 'badge-warning',
            'poor' => 'badge-danger',
            'broken' => 'badge-dark'
        ];

        return $classes[$this->condition] ?? 'badge-secondary';
    }

    /**
     * Calculate current depreciated value
     */
    public function calculateCurrentValue(): float
    {
        if (!$this->purchase_price || !$this->purchase_date) {
            return $this->purchase_price ?? 0;
        }

        $monthsOwned = Carbon::parse($this->purchase_date)->diffInMonths(Carbon::now());
        $yearlyDepreciation = $this->purchase_price * ($this->depreciation_rate / 100);
        $totalDepreciation = ($yearlyDepreciation / 12) * $monthsOwned;

        $currentValue = $this->purchase_price - $totalDepreciation;

        // Minimum value is 10% of purchase price
        $minimumValue = $this->purchase_price * 0.1;

        return max($currentValue, $minimumValue);
    }

    /**
     * Update current value based on depreciation
     */
    public function updateDepreciatedValue(): void
    {
        $this->update(['current_value' => $this->calculateCurrentValue()]);
    }
}