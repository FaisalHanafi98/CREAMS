<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    protected $table = 'activity_categories';

    protected $fillable = [
        'category_name',
        'category_slug',
        'category_status',
        'category_description',
        'category_icon',
        'category_color',
        'category_order',
    ];

    protected $casts = [
        'category_order' => 'integer',
    ];

    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class, 'category', 'category_name');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('category_status', 'active');
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('category_order');
    }

    public function getSlugAttribute(): string
    {
        return $this->category_slug;
    }
}
