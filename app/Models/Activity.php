<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    use HasFactory;

    protected $table = 'activities';

    protected $fillable = [
        'activity_code',
        'name',
        'description',
        'activity_type',
        'difficulty_level',
        'max_participants',
        'min_participants',
        'duration_minutes',
        'required_materials',
        'learning_objectives',
        'is_active',
        'centre_id',
        'created_by',
        'instructor_id',
        'location',
        'sessions_per_week',
        'start_date',
        'start_time'
    ];

    protected $casts = [
        'required_materials' => 'array',
        'learning_objectives' => 'array',
        'is_active' => 'boolean'
    ];

    protected $appends = ['category_icon', 'category_color', 'formatted_duration'];

    /**
     * Get the centre that owns this activity
     */
    public function centre()
    {
        return $this->belongsTo(Centre::class, 'centre_id');
    }

    /**
     * Get the user who created this activity
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the instructor for this activity (alias for creator)
     */
    public function instructor()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get all sessions for this activity
     */
    public function sessions()
    {
        return $this->hasMany(ActivitySession::class);
    }

    /**
     * Get all enrollments for this activity
     */
    public function enrollments()
    {
        return $this->hasMany(ActivityEnrollment::class);
    }

    /**
     * Get only active enrollments for this activity
     */
    public function activeEnrollments()
    {
        return $this->hasMany(ActivityEnrollment::class)
            ->where('enrollment_status', 'enrolled');
    }

    /**
     * Get enrolled trainees (participants) for this activity
     */
    public function participants()
    {
        return $this->belongsToMany(Trainee::class, 'activity_enrollments', 'activity_id', 'trainee_id')
                    ->wherePivot('enrollment_status', 'enrolled')
                    ->withPivot(['enrollment_date', 'enrollment_status', 'progress_percentage', 'attendance_count'])
                    ->withTimestamps();
    }

    /**
     * Get upcoming sessions
     */
    public function upcomingSessions()
    {
        return $this->hasMany(ActivitySession::class)
            ->where('session_date', '>=', now()->toDateString())
            ->where('status', 'scheduled')
            ->orderBy('session_date')
            ->orderBy('start_time');
    }

    /**
     * Get completed sessions
     */
    public function completedSessions()
    {
        return $this->hasMany(ActivitySession::class)
            ->where('status', 'completed')
            ->orderBy('session_date', 'desc');
    }

    /**
     * Get active enrollments count
     */
    public function getActiveEnrollmentsCountAttribute()
    {
        return $this->enrollments()
            ->where('enrollment_status', 'enrolled')
            ->distinct('trainee_id')
            ->count('trainee_id');
    }

    /**
     * Get total sessions count
     */
    public function getTotalSessionsCountAttribute()
    {
        return $this->sessions()->count();
    }

    /**
     * Get completion rate
     */
    public function getCompletionRateAttribute()
    {
        $totalSessions = $this->sessions()->count();
        if ($totalSessions === 0) return 0;
        
        $completedSessions = $this->sessions()->where('status', 'completed')->count();
        return round(($completedSessions / $totalSessions) * 100, 1);
    }

    /**
     * Scope for active activities
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for activities by category
     */
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope for activities by centre
     */
    public function scopeByCentre($query, $centreId)
    {
        return $query->where('centre_id', $centreId);
    }

    /**
     * Scope for activities with upcoming sessions
     */
    public function scopeWithUpcomingSessions($query)
    {
        return $query->whereHas('sessions', function($q) {
            $q->where('session_date', '>=', now()->toDateString())
              ->where('status', 'scheduled');
        });
    }

    /**
     * Check if activity has capacity for more participants
     */
    public function hasCapacity()
    {
        return $this->active_enrollments_count < $this->max_participants;
    }

    /**
     * Get category icon
     */
    public function getCategoryIconAttribute()
    {
        $icons = [
            'Physical Therapy' => 'fas fa-running',
            'Occupational Therapy' => 'fas fa-hands-helping',
            'Speech Therapy' => 'fas fa-comments',
            'Behavioral Therapy' => 'fas fa-brain',
            'Sensory Integration' => 'fas fa-hand-paper',
            'Mathematics' => 'fas fa-calculator',
            'Literacy' => 'fas fa-book',
            'Science' => 'fas fa-flask',
            'Computer Skills' => 'fas fa-laptop',
            'Art & Creativity' => 'fas fa-palette',
            'Music Therapy' => 'fas fa-music',
            'Social Skills' => 'fas fa-users',
            'Life Skills' => 'fas fa-home',
            'Vocational Training' => 'fas fa-briefcase'
        ];

        return $icons[$this->activity_type] ?? 'fas fa-circle';
    }

    /**
     * Get category color
     */
    public function getCategoryColorAttribute()
    {
        $colors = [
            'Physical Therapy' => '#4CAF50',
            'Occupational Therapy' => '#2196F3',
            'Speech Therapy' => '#FF9800',
            'Behavioral Therapy' => '#9C27B0',
            'Sensory Integration' => '#00BCD4',
            'Mathematics' => '#F44336',
            'Literacy' => '#3F51B5',
            'Science' => '#009688',
            'Computer Skills' => '#607D8B',
            'Art & Creativity' => '#E91E63',
            'Music Therapy' => '#673AB7',
            'Social Skills' => '#795548',
            'Life Skills' => '#FF5722',
            'Vocational Training' => '#FFC107'
        ];

        return $colors[$this->activity_type] ?? '#6c757d';
    }

    /**
     * Format duration for display
     */
    public function getFormattedDurationAttribute()
    {
        $hours = floor($this->duration_minutes / 60);
        $minutes = $this->duration_minutes % 60;
        
        if ($hours > 0) {
            return $hours . 'h ' . ($minutes > 0 ? $minutes . 'm' : '');
        }
        
        return $minutes . 'm';
    }

    /**
     * Get activity name from database column
     */
    public function getNameAttribute()
    {
        return $this->attributes['activity_name'] ?? '';
    }

    /**
     * Set activity name to database column
     */
    public function setNameAttribute($value)
    {
        $this->attributes['activity_name'] = $value;
    }

    /**
     * Get activity description from database column
     */
    public function getDescriptionAttribute()
    {
        return $this->attributes['activity_description'] ?? '';
    }

    /**
     * Set activity description to database column
     */
    public function setDescriptionAttribute($value)
    {
        $this->attributes['activity_description'] = $value;
    }

    /**
     * Get therapy category based on activity name
     */
    public function getCategoryAttribute()
    {
        $name = strtolower($this->activity_name ?? '');
        
        if (strpos($name, 'speech') !== false || strpos($name, 'pertuturan') !== false) {
            return 'Speech Therapy';
        } elseif (strpos($name, 'occupational') !== false || strpos($name, 'okupasi') !== false) {
            return 'Occupational Therapy';
        } elseif (strpos($name, 'physiotherapy') !== false || strpos($name, 'fisioterapi') !== false) {
            return 'Physical Therapy';
        } elseif (strpos($name, 'behavioral') !== false || strpos($name, 'tingkah laku') !== false) {
            return 'Behavioral Therapy';
        } elseif (strpos($name, 'sensory') !== false || strpos($name, 'sensori') !== false) {
            return 'Sensory Integration';
        } elseif (strpos($name, 'social') !== false || strpos($name, 'sosial') !== false) {
            return 'Social Skills';
        } elseif (strpos($name, 'life') !== false || strpos($name, 'hidup') !== false) {
            return 'Life Skills';
        } elseif (strpos($name, 'art') !== false || strpos($name, 'seni') !== false) {
            return 'Art & Creativity';
        } elseif (strpos($name, 'music') !== false || strpos($name, 'muzik') !== false) {
            return 'Music Therapy';
        } elseif (strpos($name, 'academic') !== false || strpos($name, 'akademik') !== false) {
            return 'Academic Support';
        } elseif (strpos($name, 'literacy') !== false || strpos($name, 'literasi') !== false) {
            return 'Literacy';
        } elseif (strpos($name, 'computer') !== false || strpos($name, 'komputer') !== false) {
            return 'Computer Skills';
        } elseif (strpos($name, 'mathematics') !== false || strpos($name, 'matematik') !== false) {
            return 'Mathematics';
        } elseif (strpos($name, 'vocational') !== false || strpos($name, 'vokasional') !== false) {
            return 'Vocational Training';
        } else {
            return 'Other';
        }
    }
}