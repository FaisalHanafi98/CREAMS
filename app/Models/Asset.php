<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Scopes\CentreScope;
use Carbon\Carbon;

/**
 * Enhanced Asset Model with comprehensive features
 */
class Asset extends Model
{
    use HasFactory;

    protected $table = 'assets';

    protected $fillable = [
        'asset_tag',
        'asset_name',
        'asset_description',
        'category_id',
        'type_id',
        'centre_id',
        'location_id',
        'serial_number',
        'model_number',
        'manufacturer',
        'purchase_date',
        'purchase_price',
        'warranty_expiry',
        'condition',
        'status',
        'assigned_to_user',
        'notes',
        'images',
        'is_active'
    ];

    protected $casts = [
        'purchase_price' => 'decimal:2',
        'purchase_date' => 'date',
        'warranty_expiry' => 'date',
        'images' => 'array',
        'is_active' => 'boolean'
    ];

    protected $appends = [
        'formatted_value',
        'warranty_status',
        'depreciation_percentage',
        'primary_image_url'
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new CentreScope);
    }

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
     * Get the type of the asset
     */
    public function type(): BelongsTo
    {
        return $this->belongsTo(AssetParent::class, 'type_id');
    }

    /**
     * Get the location of the asset
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(AssetLocation::class, 'location_id');
    }

    /**
     * Get the user assigned to this asset
     */
    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to_user');
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
        } elseif ($now->diffInDays($expiry) <= 30) { // Carbon 3 diffs are signed
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
        // First check if we have a primary_image set
        if ($this->primary_image) {
            return asset('storage/' . $this->primary_image);
        }

        // Fallback to first image in images array
        if ($this->images && count($this->images) > 0) {
            return asset('storage/' . $this->images[0]);
        }

        // Default asset image
        return asset('images/default-asset.png');
    }

    /**
     * Scope to search assets
     */
    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('asset_name', 'LIKE', "%{$search}%")
                ->orWhere('asset_tag', 'LIKE', "%{$search}%")
                ->orWhere('model_number', 'LIKE', "%{$search}%")
                ->orWhere('manufacturer', 'LIKE', "%{$search}%")
                ->orWhere('serial_number', 'LIKE', "%{$search}%");
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
        return $query->whereHas('upcomingMaintenance', function ($q) {
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
