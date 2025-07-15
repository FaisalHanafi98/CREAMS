<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Assets extends Model
{
    use HasFactory;

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'asset_id';

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
        'asset_id',
        'asset_name',
        'asset_type',
        'asset_quantity',
        'asset_last_updated',
        'asset_note',
        'centre_name',
        'asset_avatar',
        'asset_price',
        'asset_brand',
        'assigned_to_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'asset_last_updated' => 'datetime',
        'asset_price' => 'decimal:2',
        'asset_quantity' => 'integer',
    ];

    /**
     * The model's default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'asset_avatar' => 'default-avatar.png',
    ];

    /**
     * Get the centre that the asset belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function centre()
    {
        return $this->belongsTo(Centres::class, 'centre_name', 'centre_name');
    }

    /**
     * Get the user that the asset is assigned to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function assignedTo()
    {
        return $this->belongsTo(Users::class, 'assigned_to_id');
    }

    /**
     * Get the categories for this asset.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function categories()
    {
        return $this->belongsToMany(AssetCategories::class, 'asset_category', 'asset_id', 'category_id');
    }

    /**
     * Scope a query to only include assets of a specific type.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $type
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('asset_type', $type);
    }

    /**
     * Scope a query to only include assets assigned to a specific user.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $userId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAssignedTo($query, $userId)
    {
        return $query->where('assigned_to_id', $userId);
    }

    /**
     * Scope a query to only include assets belonging to a specific centre.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $centreName
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByCentre($query, $centreName)
    {
        return $query->where('centre_name', $centreName);
    }

    /**
     * Get assets with low quantity (below threshold).
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int  $threshold
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeLowQuantity($query, $threshold = 5)
    {
        return $query->where('asset_quantity', '<', $threshold);
    }

    /**
     * Assign this asset to a user.
     *
     * @param  string  $userId
     * @return bool
     */
    public function assignTo($userId)
    {
        $this->assigned_to_id = $userId;
        return $this->save();
    }

    /**
     * Update the quantity of this asset.
     *
     * @param  int  $change
     * @return bool
     */
    public function updateQuantity($change)
    {
        $this->asset_quantity += $change;
        return $this->save();
    }

    /**
     * Check if this asset is available (has quantity > 0).
     *
     * @return bool
     */
    public function isAvailable()
    {
        return $this->asset_quantity > 0;
    }

    /**
     * Generate a report of all assets by type.
     *
     * @return array
     */
    public static function reportByType()
    {
        return self::selectRaw('asset_type, COUNT(*) as count, SUM(asset_quantity) as total_quantity, SUM(asset_price) as total_value')
            ->groupBy('asset_type')
            ->get();
    }

    /**
     * Generate a report of all assets by centre.
     *
     * @return array
     */
    public static function reportByCentre()
    {
        return self::selectRaw('centre_name, COUNT(*) as count, SUM(asset_quantity) as total_quantity, SUM(asset_price) as total_value')
            ->groupBy('centre_name')
            ->get();
    }
}