<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Asset Location Model
 * 
 * This model represents physical locations where assets can be placed,
 * supporting hierarchical location management and capacity tracking.
 */
class AssetLocation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name', 'description', 'centre_id', 'parent_location_id',
        'location_type', 'building', 'floor', 'room_number',
        'capacity', 'coordinates_lat', 'coordinates_lng',
        'access_level', 'contact_person', 'notes', 'is_active'
    ];

    protected $casts = [
        'capacity' => 'integer',
        'coordinates_lat' => 'decimal:8',
        'coordinates_lng' => 'decimal:8',
        'is_active' => 'boolean',
    ];

    protected $dates = ['deleted_at'];

    protected $appends = [
        'full_path', 'current_asset_count', 'available_capacity',
        'utilization_percentage', 'is_over_capacity'
    ];

    /**
     * Location type constants
     */
    const TYPE_BUILDING = 'building';
    const TYPE_FLOOR = 'floor';
    const TYPE_ROOM = 'room';
    const TYPE_STORAGE = 'storage';
    const TYPE_OUTDOOR = 'outdoor';
    const TYPE_VEHICLE = 'vehicle';
    const TYPE_WORKSHOP = 'workshop';
    const TYPE_OFFICE = 'office';

    /**
     * Access level constants
     */
    const ACCESS_PUBLIC = 'public';
    const ACCESS_RESTRICTED = 'restricted';
    const ACCESS_SECURE = 'secure';
    const ACCESS_AUTHORIZED_ONLY = 'authorized_only';

    /**
     * Get all location types
     */
    public static function getLocationTypes(): array
    {
        return [
            self::TYPE_BUILDING => 'Building',
            self::TYPE_FLOOR => 'Floor',
            self::TYPE_ROOM => 'Room',
            self::TYPE_STORAGE => 'Storage Area',
            self::TYPE_OUTDOOR => 'Outdoor Area',
            self::TYPE_VEHICLE => 'Vehicle',
            self::TYPE_WORKSHOP => 'Workshop',
            self::TYPE_OFFICE => 'Office',
        ];
    }

    /**
     * Get all access levels
     */
    public static function getAccessLevels(): array
    {
        return [
            self::ACCESS_PUBLIC => 'Public Access',
            self::ACCESS_RESTRICTED => 'Restricted Access',
            self::ACCESS_SECURE => 'Secure Access',
            self::ACCESS_AUTHORIZED_ONLY => 'Authorized Personnel Only',
        ];
    }

    // =============================================
    // RELATIONSHIPS
    // =============================================

    /**
     * Get the centre that owns this location
     */
    public function centre(): BelongsTo
    {
        return $this->belongsTo(Centres::class);
    }

    /**
     * Get the parent location (for hierarchical structure)
     */
    public function parentLocation(): BelongsTo
    {
        return $this->belongsTo(AssetLocation::class, 'parent_location_id');
    }

    /**
     * Get child locations
     */
    public function childLocations(): HasMany
    {
        return $this->hasMany(AssetLocation::class, 'parent_location_id');
    }

    /**
     * Get all assets currently at this location
     */
    public function assets(): HasMany
    {
        return $this->hasMany(Asset::class, 'location_id');
    }

    /**
     * Get asset movements to this location
     */
    public function incomingMovements(): HasMany
    {
        return $this->hasMany(AssetMovement::class, 'to_location_id')
                    ->latest('movement_date');
    }

    /**
     * Get asset movements from this location
     */
    public function outgoingMovements(): HasMany
    {
        return $this->hasMany(AssetMovement::class, 'from_location_id')
                    ->latest('movement_date');
    }

    /**
     * Get all movements related to this location
     */
    public function allMovements(): \Illuminate\Database\Eloquent\Collection
    {
        $incoming = $this->incomingMovements()->get();
        $outgoing = $this->outgoingMovements()->get();
        
        return $incoming->merge($outgoing)->sortByDesc('movement_date');
    }

    // =============================================
    // QUERY SCOPES
    // =============================================

    /**
     * Scope a query to only include active locations
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to filter by centre
     */
    public function scopeForCentre($query, int $centreId)
    {
        return $query->where('centre_id', $centreId);
    }

    /**
     * Scope a query to filter by location type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('location_type', $type);
    }

    /**
     * Scope a query to find root locations (no parent)
     */
    public function scopeRootLocations($query)
    {
        return $query->whereNull('parent_location_id');
    }

    /**
     * Scope a query to find locations with capacity
     */
    public function scopeWithCapacity($query)
    {
        return $query->whereNotNull('capacity')->where('capacity', '>', 0);
    }

    /**
     * Scope a query to find over-capacity locations
     */
    public function scopeOverCapacity($query)
    {
        return $query->whereHas('assets', function ($q) {
            $q->selectRaw('location_id, COUNT(*) as asset_count')
              ->groupBy('location_id')
              ->havingRaw('asset_count > capacity');
        });
    }

    /**
     * Scope a query to search locations
     */
    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'LIKE', "%{$search}%")
              ->orWhere('description', 'LIKE', "%{$search}%")
              ->orWhere('building', 'LIKE', "%{$search}%")
              ->orWhere('room_number', 'LIKE', "%{$search}%");
        });
    }

    // =============================================
    // ACCESSORS & MUTATORS
    // =============================================

    /**
     * Get the full hierarchical path of the location
     */
    public function getFullPathAttribute(): string
    {
        $path = [];
        $location = $this;
        
        while ($location) {
            array_unshift($path, $location->name);
            $location = $location->parentLocation;
        }
        
        return implode(' > ', $path);
    }

    /**
     * Get current asset count at this location
     */
    public function getCurrentAssetCountAttribute(): int
    {
        return $this->assets()->count();
    }

    /**
     * Get available capacity
     */
    public function getAvailableCapacityAttribute(): int
    {
        if (!$this->capacity) {
            return 0;
        }
        
        return max(0, $this->capacity - $this->current_asset_count);
    }

    /**
     * Get utilization percentage
     */
    public function getUtilizationPercentageAttribute(): float
    {
        if (!$this->capacity || $this->capacity <= 0) {
            return 0;
        }
        
        return min(100, ($this->current_asset_count / $this->capacity) * 100);
    }

    /**
     * Check if location is over capacity
     */
    public function getIsOverCapacityAttribute(): bool
    {
        if (!$this->capacity) {
            return false;
        }
        
        return $this->current_asset_count > $this->capacity;
    }

    /**
     * Get location type label
     */
    public function getLocationTypeLabelAttribute(): string
    {
        $types = self::getLocationTypes();
        return $types[$this->location_type] ?? 'Unknown';
    }

    /**
     * Get access level label
     */
    public function getAccessLevelLabelAttribute(): string
    {
        $levels = self::getAccessLevels();
        return $levels[$this->access_level] ?? 'Not Set';
    }

    /**
     * Get coordinates as array
     */
    public function getCoordinatesAttribute(): ?array
    {
        if ($this->coordinates_lat && $this->coordinates_lng) {
            return [
                'lat' => (float) $this->coordinates_lat,
                'lng' => (float) $this->coordinates_lng,
            ];
        }
        
        return null;
    }

    /**
     * Get status badge class for UI
     */
    public function getStatusBadgeClassAttribute(): string
    {
        if (!$this->is_active) {
            return 'badge-secondary';
        }
        
        if ($this->is_over_capacity) {
            return 'badge-danger';
        }
        
        if ($this->utilization_percentage > 80) {
            return 'badge-warning';
        }
        
        return 'badge-success';
    }

    // =============================================
    // BUSINESS LOGIC METHODS
    // =============================================

    /**
     * Check if location can accommodate more assets
     */
    public function canAccommodate(int $additionalAssets = 1): bool
    {
        if (!$this->capacity) {
            return true; // No capacity limit
        }
        
        return ($this->current_asset_count + $additionalAssets) <= $this->capacity;
    }

    /**
     * Get all descendant locations (recursive)
     */
    public function getAllDescendants(): \Illuminate\Database\Eloquent\Collection
    {
        $descendants = collect();
        
        foreach ($this->childLocations as $child) {
            $descendants->push($child);
            $descendants = $descendants->merge($child->getAllDescendants());
        }
        
        return $descendants;
    }

    /**
     * Get all ancestor locations (recursive)
     */
    public function getAllAncestors(): \Illuminate\Database\Eloquent\Collection
    {
        $ancestors = collect();
        $location = $this->parentLocation;
        
        while ($location) {
            $ancestors->push($location);
            $location = $location->parentLocation;
        }
        
        return $ancestors;
    }

    /**
     * Move all assets to another location
     */
    public function moveAllAssetsTo(AssetLocation $targetLocation, string $reason = 'Location Transfer'): bool
    {
        try {
            \DB::beginTransaction();
            
            $assets = $this->assets()->get();
            
            foreach ($assets as $asset) {
                $asset->moveTo($targetLocation->id, $reason);
            }
            
            \DB::commit();
            
            \Log::info('All assets moved from location', [
                'from_location_id' => $this->id,
                'to_location_id' => $targetLocation->id,
                'asset_count' => $assets->count(),
                'reason' => $reason
            ]);
            
            return true;
            
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Failed to move all assets from location', [
                'from_location_id' => $this->id,
                'to_location_id' => $targetLocation->id,
                'error' => $e->getMessage()
            ]);
            
            return false;
        }
    }

    /**
     * Activate this location
     */
    public function activate(): bool
    {
        $this->is_active = true;
        return $this->save();
    }

    /**
     * Deactivate this location
     */
    public function deactivate(): bool
    {
        $this->is_active = false;
        return $this->save();
    }

    /**
     * Get location statistics
     */
    public function getStatistics(): array
    {
        $assets = $this->assets;
        
        return [
            'total_assets' => $assets->count(),
            'asset_value' => $assets->sum('current_value'),
            'available_capacity' => $this->available_capacity,
            'utilization_percentage' => $this->utilization_percentage,
            'asset_types' => $assets->groupBy('asset_type_id')->count(),
            'movements_this_month' => $this->allMovements()
                                          ->filter(function ($movement) {
                                              return $movement->movement_date >= now()->startOfMonth();
                                          })
                                          ->count(),
        ];
    }

    // =============================================
    // STATIC UTILITY METHODS
    // =============================================

    /**
     * Get location hierarchy for a centre
     */
    public static function getHierarchyForCentre(int $centreId): \Illuminate\Database\Eloquent\Collection
    {
        return self::forCentre($centreId)
                   ->rootLocations()
                   ->with('childLocations.childLocations')
                   ->orderBy('name')
                   ->get();
    }

    /**
     * Get locations requiring attention
     */
    public static function getRequiringAttention(?int $centreId = null): array
    {
        $query = self::active();
        
        if ($centreId) {
            $query->forCentre($centreId);
        }
        
        return [
            'over_capacity' => $query->overCapacity()->with(['assets', 'centre'])->get(),
            'near_capacity' => $query->withCapacity()
                                    ->whereHas('assets', function ($q) {
                                        $q->selectRaw('location_id, COUNT(*) as asset_count')
                                          ->groupBy('location_id')
                                          ->havingRaw('asset_count >= (capacity * 0.9)');
                                    })
                                    ->with(['assets', 'centre'])
                                    ->get(),
            'no_assets' => $query->doesntHave('assets')->get(),
        ];
    }

    /**
     * Generate location tree structure
     */
    public static function getTreeStructure(?int $centreId = null): array
    {
        $query = self::active();
        
        if ($centreId) {
            $query->forCentre($centreId);
        }
        
        $locations = $query->with('childLocations')->get();
        
        return self::buildTree($locations->whereNull('parent_location_id'), $locations);
    }

    /**
     * Build tree structure recursively
     */
    private static function buildTree($parents, $allLocations): array
    {
        $tree = [];
        
        foreach ($parents as $parent) {
            $children = $allLocations->where('parent_location_id', $parent->id);
            
            $node = [
                'id' => $parent->id,
                'name' => $parent->name,
                'type' => $parent->location_type,
                'asset_count' => $parent->current_asset_count,
                'capacity' => $parent->capacity,
                'children' => self::buildTree($children, $allLocations),
            ];
            
            $tree[] = $node;
        }
        
        return $tree;
    }
}