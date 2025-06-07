<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Activities extends Model
{
    use HasFactory;

    protected $fillable = [
        'activity_name',
        'activity_code',
        'description',
        'category',
        'objectives',
        'materials_needed',
        'age_group',
        'difficulty_level',
        'is_active',
        'created_by'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function creator()
    {
        return $this->belongsTo(Users::class, 'created_by');
    }

    public function sessions()
    {
        return $this->hasMany(ActivitySession::class);
    }

    public function activeSessions()
    {
        return $this->hasMany(ActivitySession::class)->where('is_active', true);
    }

    public function getSessionsCountAttribute()
    {
        return $this->activeSessions()->count();
    }

    public function getEnrollmentCountAttribute()
    {
        return $this->activeSessions()->sum('current_enrollment');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }
}