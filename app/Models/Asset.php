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
        'asset_id',
        'asset_name',
        'asset_description',
        'asset_type_id',
        'asset_model',
        'asset_brand',
        'asset_serial_number',
        'asset_value',
        'purchase_date',
        'supplier',
        'warranty_info',
        'asset_condition',
        'asset_status',
        'asset_location',
        'centre_id',
        'assigned_to',
        'maintenance_notes',
        'last_maintenance_date',
        'next_maintenance_date',
        'asset_image',
        'asset_attributes'
    ];

    protected $casts = [
        'asset_value' => 'decimal:2',
        'purchase_date' => 'date',
        'last_maintenance_date' => 'date',
        'next_maintenance_date' => 'date',
        'asset_attributes' => 'array',
    ];

    /**
     * Get the centre that owns the asset
     */
    public function centre(): BelongsTo
    {
        return $this->belongsTo(Centres::class, 'centre_id', 'centre_id');
    }

    /**
     * Get formatted asset value
     */
    public function getFormattedValueAttribute(): string
    {
        return 'RM ' . number_format($this->asset_value, 2);
    }

    /**
     * Get asset image URL
     */
    public function getImageUrlAttribute(): string
    {
        if ($this->asset_image) {
            return asset('storage/assets/' . $this->asset_image);
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
              ->orWhere('asset_model', 'LIKE', "%{$search}%")
              ->orWhere('asset_brand', 'LIKE', "%{$search}%")
              ->orWhere('asset_serial_number', 'LIKE', "%{$search}%")
              ->orWhere('asset_location', 'LIKE', "%{$search}%");
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
        return $query->where('asset_status', $status);
    }

    /**
     * Scope to filter by condition
     */
    public function scopeByCondition($query, string $condition)
    {
        return $query->where('asset_condition', $condition);
    }
}