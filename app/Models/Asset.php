<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Asset Model - Simplified to match actual table structure
 */
class Asset extends Model
{
    use HasFactory;

    protected $table = 'assets';
    protected $primaryKey = 'asset_id';

    protected $fillable = [
        'asset_name', 'asset_type', 'asset_brand', 'asset_avatar',
        'asset_price', 'asset_quantity', 'asset_last_updated',
        'centre_name', 'asset_note'
    ];

    protected $casts = [
        'asset_price' => 'decimal:2',
        'asset_quantity' => 'integer',
        'asset_last_updated' => 'datetime',
    ];

    /**
     * Get the centre that owns the asset
     */
    public function centre(): BelongsTo
    {
        return $this->belongsTo(Centres::class, 'centre_name', 'centre_name');
    }

    /**
     * Get formatted asset price
     */
    public function getFormattedPriceAttribute(): string
    {
        return 'RM ' . number_format($this->asset_price, 2);
    }

    /**
     * Get asset image URL
     */
    public function getImageUrlAttribute(): string
    {
        if ($this->asset_avatar) {
            return asset('storage/assets/' . $this->asset_avatar);
        }
        return asset('images/default-asset.png');
    }

    /**
     * Scope to search assets
     */
    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('asset_name', 'LIKE', "%{$search}%")
              ->orWhere('asset_type', 'LIKE', "%{$search}%")
              ->orWhere('asset_brand', 'LIKE', "%{$search}%")
              ->orWhere('centre_name', 'LIKE', "%{$search}%");
        });
    }

    /**
     * Scope to filter by centre
     */
    public function scopeForCentre($query, string $centreName)
    {
        return $query->where('centre_name', $centreName);
    }

    /**
     * Scope to filter by type
     */
    public function scopeOfType($query, string $assetType)
    {
        return $query->where('asset_type', $assetType);
    }
}