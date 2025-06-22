<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ActivitySession extends Model
{
    use HasFactory;

    protected $fillable = [
        'activity_id',
        'teacher_id',
        'date',
        'start_time',
        'duration',
        'location',
        'max_capacity',
        'status'
    ];

    protected $casts = [
        'date' => 'date',
        'start_time' => 'datetime:H:i',
        'duration' => 'integer',
        'max_capacity' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Get the activity
     */
    public function activity()
    {
        return $this->belongsTo(Activity::class);
    }

    /**
     * Get the teacher
     */
    public function teacher()
    {
        return $this->belongsTo(Users::class, 'teacher_id');
    }

    /**
     * Get enrollments
     */
    public function enrollments()
    {
        return $this->hasMany(SessionEnrollment::class, 'session_id');
    }

    /**
     * Get attendance records
     */
    public function attendance()
    {
        return $this->hasMany(ActivityAttendance::class, 'session_id');
    }

    /**
     * Get end time attribute
     */
    public function getEndTimeAttribute()
    {
        return Carbon::parse($this->start_time)->addMinutes($this->duration)->format('H:i');
    }

    /**
     * Get enrollment count
     */
    public function getEnrollmentCountAttribute()
    {
        return $this->enrollments()->count();
    }

    /**
     * Check if session is full
     */
    public function getIsFullAttribute()
    {
        return $this->enrollment_count >= $this->max_capacity;
    }

    /**
     * Get available spots
     */
    public function getAvailableSpotsAttribute()
    {
        return max(0, $this->max_capacity - $this->enrollment_count);
    }

    /**
     * Scope for active sessions
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for upcoming sessions
     */
    public function scopeUpcoming($query)
    {
        return $query->where('date', '>=', Carbon::today())
                     ->orderBy('date')
                     ->orderBy('start_time');
    }

    /**
     * Scope for today's sessions
     */
    public function scopeToday($query)
    {
        return $query->whereDate('date', Carbon::today());
    }
}