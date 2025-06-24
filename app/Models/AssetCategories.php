<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetCategories extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'assets_categories';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'category_id';

    /**
     * Indicates if the model's ID is auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'category_id',
        'category_name',
        'description',
        'icon',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the assets that belong to this category.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function assets()
    {
        return $this->belongsToMany(Assets::class, 'asset_category', 'category_id', 'asset_id');
    }

    /**
     * Scope a query to only include active categories.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Find a category by name.
     *
     * @param string $name
     * @return AssetCategories|null
     */
    public static function findByName($name)
    {
        return self::where('category_name', $name)->first();
    }

    /**
     * Check if a category with the given name exists.
     *
     * @param string $name
     * @return bool
     */
    public static function nameExists($name)
    {
        return self::where('category_name', $name)->exists();
    }

    /**
     * Get all assets for this category.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAssets()
    {
        return $this->assets()->get();
    }

    /**
     * Get the count of assets in this category.
     *
     * @return int
     */
    public function getAssetCount()
    {
        return $this->assets()->count();
    }

    /**
     * Get the total value of all assets in this category.
     *
     * @return float
     */
    public function getTotalValue()
    {
        return $this->assets()->sum('asset_price');
    }

    /**
     * Get the total quantity of all assets in this category.
     *
     * @return int
     */
    public function getTotalQuantity()
    {
        return $this->assets()->sum('asset_quantity');
    }

    /**
     * Set this category as active.
     *
     * @return bool
     */
    public function activate()
    {
        $this->is_active = true;
        return $this->save();
    }

    /**
     * Set this category as inactive.
     *
     * @return bool
     */
    public function deactivate()
    {
        $this->is_active = false;
        return $this->save();
    }
}