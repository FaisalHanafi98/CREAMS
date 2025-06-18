<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Activity extends Model
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
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Get the user who created the activity
     */
    public function creator()
    {
        return $this->belongsTo(Users::class, 'created_by');
    }

    /**
     * Get all sessions for the activity
     */
    public function sessions()
    {
        return $this->hasMany(ActivitySession::class);
    }

    /**
     * Get active sessions
     */
    public function activeSessions()
    {
        return $this->hasMany(ActivitySession::class)->where('status', 'active');
    }

    /**
     * Scope for active activities
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for rehabilitation activities
     */
    public function scopeRehabilitation($query)
    {
        return $query->whereIn('category', [
            'Physical Therapy',
            'Occupational Therapy',
            'Speech & Language Therapy',
            'Sensory Integration',
            'Social Skills Training',
            'Daily Living Skills'
        ]);
    }

    /**
     * Scope for academic activities
     */
    public function scopeAcademic($query)
    {
        return $query->whereIn('category', [
            'Basic Mathematics',
            'Language & Literacy',
            'Science Exploration',
            'Art & Creativity',
            'Music Therapy',
            'Computer Skills'
        ]);
    }
}