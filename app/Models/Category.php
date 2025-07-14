<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'type',
        'icon',
        'color',
        'description',
        'is_active',
        'sort_order'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer'
    ];

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        // Auto-generate slug when creating
        static::creating(function ($category) {
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });

        // Update slug when name changes
        static::updating(function ($category) {
            if ($category->isDirty('name')) {
                $category->slug = Str::slug($category->name);
            }
        });
    }

    // Relationships
    public function activities()
    {
        return $this->hasMany(Activity::class);
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
        return $query->where('type', 'rehabilitation');
    }

    public function scopeAcademic($query)
    {
        return $query->where('type', 'academic');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
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
        return ucfirst($this->type);
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
        return $this->type === 'rehabilitation';
    }

    public function isAcademic()
    {
        return $this->type === 'academic';
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }
}