<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Enhanced Asset Type Model
 * 
 * This model represents asset types/categories with enhanced features
 * for maintenance scheduling, depreciation tracking, and specifications.
 */
class AssetType extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name', 'category', 'description', 'default_value', 
        'expected_lifespan', 'maintenance_schedule', 'maintenance_interval',
        'depreciation_method', 'depreciation_rate', 'vendor', 
        'image_path', 'specifications', 'required_certifications',
        'warranty_period', 'is_active'
    ];

    protected $casts = [
        'default_value' => 'decimal:2',
        'expected_lifespan' => 'integer', // in days
        'maintenance_interval' => 'integer', // in days
        'depreciation_rate' => 'decimal:4',
        'warranty_period' => 'integer', // in days
        'specifications' => 'array',
        'required_certifications' => 'array',
        'is_active' => 'boolean',
    ];

    protected $dates = ['deleted_at'];

    /**
     * Depreciation method constants
     */
    const DEPRECIATION_STRAIGHT_LINE = 'straight_line';
    const DEPRECIATION_DECLINING_BALANCE = 'declining_balance';
    const DEPRECIATION_UNITS_OF_PRODUCTION = 'units_of_production';

    /**
     * Get all depreciation methods
     */
    public static function getDepreciationMethods(): array
    {
        return [
            self::DEPRECIATION_STRAIGHT_LINE => 'Straight Line',
            self::DEPRECIATION_DECLINING_BALANCE => 'Declining Balance',
            self::DEPRECIATION_UNITS_OF_PRODUCTION => 'Units of Production',
        ];
    }

    // =============================================
    // RELATIONSHIPS
    // =============================================

    /**
     * Get all assets of this type
     */
    public function assets(): HasMany
    {
        return $this->hasMany(Asset::class);
    }

    /**
     * Get all asset items (for compatibility with existing system)
     */
    public function assetItems(): HasMany
    {
        return $this->hasMany(AssetItem::class);
    }

    // =============================================
    // QUERY SCOPES
    // =============================================

    /**
     * Scope a query to only include active asset types
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to filter by category
     */
    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope a query to search asset types
     */
    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'LIKE', "%{$search}%")
              ->orWhere('category', 'LIKE', "%{$search}%")
              ->orWhere('description', 'LIKE', "%{$search}%");
        });
    }

    /**
     * Scope a query to find types requiring regular maintenance
     */
    public function scopeRequiresMaintenance($query)
    {
        return $query->whereNotNull('maintenance_interval')
                     ->where('maintenance_interval', '>', 0);
    }

    // =============================================
    // ACCESSORS & MUTATORS
    // =============================================

    /**
     * Get the asset type's image URL
     */
    public function getImageUrlAttribute(): string
    {
        if ($this->image_path) {
            return asset('storage/' . $this->image_path);
        }
        return asset('images/default-asset-type.png');
    }

    /**
     * Get formatted default value
     */
    public function getFormattedDefaultValueAttribute(): string
    {
        return 'RM ' . number_format($this->default_value, 2);
    }

    /**
     * Get expected lifespan in years
     */
    public function getExpectedLifespanYearsAttribute(): float
    {
        return $this->expected_lifespan ? round($this->expected_lifespan / 365, 1) : 0;
    }

    /**
     * Get maintenance interval in months
     */
    public function getMaintenanceIntervalMonthsAttribute(): float
    {
        return $this->maintenance_interval ? round($this->maintenance_interval / 30, 1) : 0;
    }

    /**
     * Get warranty period in months
     */
    public function getWarrantyPeriodMonthsAttribute(): float
    {
        return $this->warranty_period ? round($this->warranty_period / 30, 1) : 0;
    }

    /**
     * Get depreciation method label
     */
    public function getDepreciationMethodLabelAttribute(): string
    {
        $methods = self::getDepreciationMethods();
        return $methods[$this->depreciation_method] ?? 'Not Set';
    }

    /**
     * Check if this asset type requires maintenance
     */
    public function getRequiresMaintenanceAttribute(): bool
    {
        return !is_null($this->maintenance_interval) && $this->maintenance_interval > 0;
    }

    /**
     * Check if this asset type has depreciation configured
     */
    public function getHasDepreciationAttribute(): bool
    {
        return !is_null($this->depreciation_method) && !is_null($this->depreciation_rate);
    }

    // =============================================
    // BUSINESS LOGIC METHODS
    // =============================================

    /**
     * Calculate current value based on depreciation
     */
    public function calculateDepreciatedValue(float $purchasePrice, \Carbon\Carbon $purchaseDate): float
    {
        if (!$this->has_depreciation) {
            return $purchasePrice;
        }

        $ageInDays = $purchaseDate->diffInDays(now());
        
        return match($this->depreciation_method) {
            self::DEPRECIATION_STRAIGHT_LINE => $this->calculateStraightLineDepreciation($purchasePrice, $ageInDays),
            self::DEPRECIATION_DECLINING_BALANCE => $this->calculateDecliningBalanceDepreciation($purchasePrice, $ageInDays),
            default => $purchasePrice,
        };
    }

    /**
     * Calculate next maintenance date
     */
    public function calculateNextMaintenanceDate(\Carbon\Carbon $lastMaintenanceDate = null): ?\Carbon\Carbon
    {
        if (!$this->requires_maintenance) {
            return null;
        }

        $baseDate = $lastMaintenanceDate ?? now();
        return $baseDate->copy()->addDays($this->maintenance_interval);
    }

    /**
     * Get asset statistics for this type
     */
    public function getStatistics(): array
    {
        return [
            'total_assets' => $this->assets()->count(),
            'active_assets' => $this->assets()->where('status', '!=', 'retired')->count(),
            'total_value' => 0, // current_value column not available
            'average_age' => $this->assets()->whereNotNull('purchase_date')->get()->avg(function ($asset) {
                return $asset->age_in_days;
            }),
            'maintenance_due_count' => $this->assets()->needingMaintenance()->count(),
        ];
    }

    /**
     * Activate this asset type
     */
    public function activate(): bool
    {
        $this->is_active = true;
        return $this->save();
    }

    /**
     * Deactivate this asset type
     */
    public function deactivate(): bool
    {
        $this->is_active = false;
        return $this->save();
    }

    /**
     * Clone this asset type with new name
     */
    public function duplicate(string $newName): self
    {
        $attributes = $this->attributesToArray();
        unset($attributes['id'], $attributes['created_at'], $attributes['updated_at']);
        
        $attributes['name'] = $newName;
        
        return self::create($attributes);
    }

    // =============================================
    // DEPRECIATION CALCULATIONS
    // =============================================

    /**
     * Calculate straight-line depreciation
     */
    private function calculateStraightLineDepreciation(float $purchasePrice, int $ageInDays): float
    {
        if (!$this->expected_lifespan || $this->expected_lifespan <= 0) {
            return $purchasePrice;
        }

        $depreciationPerDay = $purchasePrice / $this->expected_lifespan;
        $totalDepreciation = min($depreciationPerDay * $ageInDays, $purchasePrice);
        
        return max(0, $purchasePrice - $totalDepreciation);
    }

    /**
     * Calculate declining balance depreciation
     */
    private function calculateDecliningBalanceDepreciation(float $purchasePrice, int $ageInDays): float
    {
        if (!$this->depreciation_rate) {
            return $purchasePrice;
        }

        $yearsOld = $ageInDays / 365;
        $currentValue = $purchasePrice * pow((1 - $this->depreciation_rate), $yearsOld);
        
        return max(0, $currentValue);
    }

    // =============================================
    // STATIC UTILITY METHODS
    // =============================================

    /**
     * Get all categories
     */
    public static function getCategories(): array
    {
        return self::distinct()->pluck('category')->filter()->sort()->values()->toArray();
    }

    /**
     * Get asset types by category
     */
    public static function getByCategory(): array
    {
        return self::active()
                   ->get()
                   ->groupBy('category')
                   ->map(function ($types) {
                       return $types->sortBy('name');
                   })
                   ->toArray();
    }

    /**
     * Find asset type by name
     */
    public static function findByName(string $name): ?self
    {
        return self::where('name', $name)->first();
    }

    /**
     * Get popular asset types (by usage)
     */
    public static function getPopular(int $limit = 10): \Illuminate\Database\Eloquent\Collection
    {
        return self::withCount('assets')
                   ->active()
                   ->orderBy('assets_count', 'desc')
                   ->limit($limit)
                   ->get();
    }

    /**
     * Get asset types requiring attention
     */
    public static function getRequiringAttention(): array
    {
        return [
            'no_assets' => self::active()->doesntHave('assets')->get(),
            'high_maintenance' => self::requiresMaintenance()
                                     ->whereHas('assets', function ($query) {
                                         $query->needingMaintenance();
                                     })
                                     ->withCount(['assets' => function ($query) {
                                         $query->needingMaintenance();
                                     }])
                                     ->get(),
            'inactive_with_assets' => self::where('is_active', false)
                                          ->has('assets')
                                          ->withCount('assets')
                                          ->get(),
        ];
    }
}