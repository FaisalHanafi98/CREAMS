<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'category',
        'location',
        'value',
        'vendor',
        'image_path',
    ];

    public function assetItems()
    {
        return $this->hasMany(AssetItem::class);
    }
}
