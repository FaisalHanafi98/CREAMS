<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'asset_type_id',
        'tag',
        'location',
        'value',
    ];

    public function assetType()
    {
        return $this->belongsTo(AssetType::class);
    }
}
