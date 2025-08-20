<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityCategory extends Model
{
    use HasFactory;

    protected $table = 'activity_categories';

    protected $fillable = [
        'category_name',
        'category_description',
        'category_type',
        'category_color',
        'category_icon',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    /**
     * Get all activities in this category
     */
    public function activities()
    {
        return $this->hasMany(Activity::class, 'category_id');
    }

    /**
     * Get active activities in this category
     */
    public function activeActivities()
    {
        return $this->hasMany(Activity::class, 'category_id')
                    ->where('is_active', true);
    }

    /**
     * Scope for active categories
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope by category type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('category_type', $type);
    }

    /**
     * Get activities count
     */
    public function getActivitiesCountAttribute()
    {
        return $this->activities()->count();
    }

    /**
     * Get active activities count
     */
    public function getActiveActivitiesCountAttribute()
    {
        return $this->activeActivities()->count();
    }
}