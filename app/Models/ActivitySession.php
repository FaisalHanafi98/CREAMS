<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ActivitySession extends Model
{
    use HasFactory;

    protected $table = 'activity_sessions';

    protected $fillable = [
        'activity_id',
        'session_name',
        'session_date',
        'start_time',
        'end_time',
        'venue',
        'teacher_id',
        'status',
        'attendance_marked',
        'notes'
    ];

    protected $casts = [
        'session_date' => 'date',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'session_data' => 'array'
    ];

    protected $appends = ['formatted_time', 'duration_minutes', 'is_current'];

    /**
     * Get the activity this session belongs to
     */
    public function activity()
    {
        return $this->belongsTo(Activity::class);
    }

    /**
     * Get the teacher for this session
     */
    public function teacher()
    {
        return $this->belongsTo(Users::class, 'teacher_id');
    }

    /**
     * Get all enrollments for this session
     */
    public function enrollments()
    {
        return $this->hasMany(ActivityEnrollment::class, 'session_id');
    }

    /**
     * Get enrolled trainees
     */
    public function trainees()
    {
        return $this->belongsToMany(Trainee::class, 'activity_enrollments_new', 'session_id', 'trainee_id')
            ->withPivot(['enrollment_status', 'attendance_marked', 'participation_score', 'progress_notes'])
            ->withTimestamps();
    }

    /**
     * Get active enrollments only
     */
    public function activeEnrollments()
    {
        return $this->hasMany(ActivityEnrollment::class, 'session_id')
            ->where('enrollment_status', 'enrolled');
    }

    /**
     * Get formatted time display
     */
    public function getFormattedTimeAttribute()
    {
        return Carbon::parse($this->start_time)->format('H:i') . ' - ' . 
               Carbon::parse($this->end_time)->format('H:i');
    }

    /**
     * Get session duration in minutes
     */
    public function getDurationMinutesAttribute()
    {
        $start = Carbon::parse($this->start_time);
        $end = Carbon::parse($this->end_time);
        return $end->diffInMinutes($start);
    }

    /**
     * Check if session is currently running
     */
    public function getIsCurrentAttribute()
    {
        if ($this->session_date->isToday() && $this->status === 'ongoing') {
            return true;
        }

        if ($this->session_date->isToday() && $this->status === 'scheduled') {
            $now = Carbon::now();
            $start = Carbon::parse($this->session_date->format('Y-m-d') . ' ' . $this->start_time);
            $end = Carbon::parse($this->session_date->format('Y-m-d') . ' ' . $this->end_time);
            
            return $now->between($start, $end);
        }

        return false;
    }

    /**
     * Get current enrollment count
     */
    public function getCurrentEnrollmentCountAttribute()
    {
        return $this->enrollments()
            ->where('enrollment_status', 'enrolled')
            ->count();
    }

    /**
     * Get attendance completion percentage
     */
    public function getAttendanceCompletionAttribute()
    {
        $totalEnrollments = $this->enrollments()->count();
        if ($totalEnrollments === 0) return 0;

        $markedAttendance = $this->enrollments()
            ->where('attendance_marked', true)
            ->count();

        return round(($markedAttendance / $totalEnrollments) * 100, 1);
    }

    /**
     * Scope for today's sessions
     */
    public function scopeToday($query)
    {
        return $query->whereDate('session_date', Carbon::today());
    }

    /**
     * Scope for upcoming sessions
     */
    public function scopeUpcoming($query)
    {
        return $query->where('session_date', '>=', Carbon::today())
            ->where('status', 'scheduled');
    }

    /**
     * Scope for ongoing sessions
     */
    public function scopeOngoing($query)
    {
        return $query->where('status', 'ongoing');
    }

    /**
     * Scope for completed sessions
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope for sessions by teacher
     */
    public function scopeByTeacher($query, $teacherId)
    {
        return $query->where('teacher_id', $teacherId);
    }

    /**
     * Scope for sessions by date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('session_date', [$startDate, $endDate]);
    }

    /**
     * Check if session has capacity for more enrollments
     */
    public function hasCapacity()
    {
        return $this->current_enrollment_count < $this->capacity;
    }

    /**
     * Check if session can be started
     */
    public function canStart()
    {
        return $this->status === 'scheduled' && 
               $this->session_date->isToday() &&
               Carbon::now()->gte(Carbon::parse($this->session_date->format('Y-m-d') . ' ' . $this->start_time));
    }

    /**
     * Check if session can be completed
     */
    public function canComplete()
    {
        return $this->status === 'ongoing' || 
               ($this->status === 'scheduled' && $this->session_date->isPast());
    }

    /**
     * Start the session
     */
    public function start()
    {
        if ($this->canStart()) {
            $this->update(['status' => 'ongoing']);
            return true;
        }
        return false;
    }

    /**
     * Complete the session
     */
    public function complete()
    {
        if ($this->canComplete()) {
            $this->update(['status' => 'completed']);
            return true;
        }
        return false;
    }

    /**
     * Cancel the session
     */
    public function cancel($reason = null)
    {
        $sessionData = $this->session_data ?? [];
        $sessionData['cancelled_reason'] = $reason;
        $sessionData['cancelled_at'] = Carbon::now()->toISOString();

        $this->update([
            'status' => 'cancelled',
            'session_data' => $sessionData
        ]);

        return true;
    }

    /**
     * Check for time conflicts with other sessions for the same teacher
     */
    public function hasTimeConflict($excludeSessionId = null)
    {
        $query = static::where('teacher_id', $this->teacher_id)
            ->where('session_date', $this->session_date)
            ->where('status', '!=', 'cancelled')
            ->where(function($q) {
                $q->whereBetween('start_time', [$this->start_time, $this->end_time])
                  ->orWhereBetween('end_time', [$this->start_time, $this->end_time])
                  ->orWhere(function($q2) {
                      $q2->where('start_time', '<=', $this->start_time)
                         ->where('end_time', '>=', $this->end_time);
                  });
            });

        if ($excludeSessionId) {
            $query->where('id', '!=', $excludeSessionId);
        }

        return $query->exists();
    }

    /**
     * Get status badge class for display
     */
    public function getStatusBadgeClassAttribute()
    {
        $classes = [
            'scheduled' => 'badge-primary',
            'ongoing' => 'badge-warning',
            'completed' => 'badge-success',
            'cancelled' => 'badge-danger'
        ];

        return $classes[$this->status] ?? 'badge-secondary';
    }

    /**
     * Get status color for display
     */
    public function getStatusColorAttribute()
    {
        $colors = [
            'scheduled' => '#007bff',
            'ongoing' => '#ffc107',
            'completed' => '#28a745',
            'cancelled' => '#dc3545'
        ];

        return $colors[$this->status] ?? '#6c757d';
    }
}