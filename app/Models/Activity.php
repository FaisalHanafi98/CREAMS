<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Activity extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'activity_code',
        'activity_name',
        'description',
        'category',
        'activity_type',
        'objectives',
        'materials_needed',
        'skills_developed',
        'age_group',
        'difficulty_level',
        'min_participants',
        'max_participants',
        'duration_minutes',
        'location_type',
        'requires_equipment',
        'equipment_list',
        'is_active',
        'times_conducted',
        'average_rating',
        'created_by',
        'centre_id'
    ];

    protected $casts = [
        'skills_developed' => 'array',
        'equipment_list' => 'array',
        'is_active' => 'boolean',
        'requires_equipment' => 'boolean'
    ];

    // Relationships
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function centre()
    {
        return $this->belongsTo(Centres::class, 'centre_id', 'centre_id');
    }

    public function sessions()
    {
        return $this->hasMany(ActivitySession::class);
    }

    public function upcomingSessions()
    {
        return $this->sessions()
            ->where('scheduled_date', '>=', now())
            ->where('status', 'scheduled')
            ->orderBy('scheduled_date');
    }

    public function completedSessions()
    {
        return $this->sessions()->where('status', 'completed');
    }

    // New relationships for scheduling and enrollment
    public function schedules()
    {
        return $this->hasMany(ActivitySchedule::class);
    }

    public function activeSchedules()
    {
        return $this->schedules()->where('status', 'active');
    }

    public function enrollments()
    {
        return $this->hasMany(ActivityEnrollment::class);
    }

    public function activeEnrollments()
    {
        return $this->enrollments()->whereIn('status', ['enrolled', 'active']);
    }

    public function trainees()
    {
        return $this->belongsToMany(Trainees::class, 'activity_enrollments', 'activity_id', 'trainee_id')
                    ->withPivot(['enrollment_date', 'status', 'notes'])
                    ->withTimestamps();
    }

    public function teacher()
    {
        return $this->belongsTo(Users::class, 'created_by');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeForAgeGroup($query, $age)
    {
        return $query->where('age_group', 'LIKE', "%{$age}%");
    }

    // Accessors
    public function getFormattedDurationAttribute()
    {
        $hours = floor($this->duration_minutes / 60);
        $minutes = $this->duration_minutes % 60;
        
        if ($hours > 0) {
            return $hours . 'h ' . ($minutes > 0 ? $minutes . 'm' : '');
        }
        return $minutes . 'm';
    }

    public function getParticipantRangeAttribute()
    {
        if ($this->min_participants == $this->max_participants) {
            return $this->min_participants . ' participants';
        }
        return $this->min_participants . '-' . $this->max_participants . ' participants';
    }
}