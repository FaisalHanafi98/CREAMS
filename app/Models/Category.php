<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Category extends Model
{
    use HasFactory;

    protected $table = 'activity_categories';

    protected $fillable = [
        'category_name',
        'category_description',
        'category_color',
        'category_icon',
        'category_type',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();
    }

    // Relationships
    public function activities()
    {
        return $this->hasMany(Activity::class, 'category_id', 'id');
    }

    public function activeActivities()
    {
        return $this->activities()->where('is_active', true);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeRehabilitation($query)
    {
        return $query->where('category_type', 'rehabilitation');
    }

    public function scopeAcademic($query)
    {
        return $query->where('category_type', 'academic');
    }

    public function scopeCreativeSocial($query)
    {
        return $query->where('category_type', 'creative_social');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('category_name');
    }

    // Accessors
    public function getActivityCountAttribute()
    {
        return $this->activities()->count();
    }

    public function getActiveActivityCountAttribute()
    {
        return $this->activeActivities()->count();
    }

    public function getTypeDisplayAttribute()
    {
        return ucfirst(str_replace('_', ' ', $this->category_type));
    }

    public function getIconClassAttribute()
    {
        return $this->icon ?: 'fas fa-tasks';
    }

    public function getColorCodeAttribute()
    {
        return $this->color ?: '#32bdea';
    }

    // Helper methods
    public function isRehabilitation()
    {
        return $this->category_type === 'rehabilitation';
    }

    public function isAcademic()
    {
        return $this->category_type === 'academic';
    }

    public function isCreativeSocial()
    {
        return $this->category_type === 'creative_social';
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }
}