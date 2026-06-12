<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_name',
        'category_description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    /**
     * The table stores category_name, but views and controllers refer to
     * ->name. Bridge the schema/code naming drift in one place.
     */
    public function getNameAttribute(): ?string
    {
        return $this->category_name;
    }

    public function setNameAttribute($value): void
    {
        $this->attributes['category_name'] = $value;
    }

    /**
     * Get the parent category
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(AssetCategory::class, 'parent_id');
    }

    /**
     * Get child categories
     */
    public function children(): HasMany
    {
        return $this->hasMany(AssetCategory::class, 'parent_id');
    }

    /**
     * Get all assets in this category
     */
    public function assets(): HasMany
    {
        return $this->hasMany(Asset::class, 'category_id');
    }

    /**
     * Scope for active categories
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for parent categories only
     */
    public function scopeParents($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Get the full category path
     */
    public function getFullNameAttribute(): string
    {
        if ($this->parent) {
            return $this->parent->name . ' > ' . $this->name;
        }
        
        return $this->name;
    }

    /**
     * Get assets count in this category
     */
    public function getAssetsCountAttribute(): int
    {
        return $this->assets()->count();
    }

    /**
     * Check if category has children
     */
    public function hasChildren(): bool
    {
        return $this->children()->exists();
    }

    /**
     * Get category with icon and color for display
     */
    public function getDisplayDataAttribute(): array
    {
        return [
            'name' => $this->name,
            'icon' => $this->icon ?? 'fas fa-cube',
            'color' => $this->color ?? '#6c757d',
            'count' => $this->assets_count
        ];
    }
}