<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ActivityEnrollment extends Model
{
    use HasFactory;

    protected $fillable = [
        'activity_id',
        'trainee_id',
        'enrollment_date',
        'start_date',
        'completion_date',
        'status',
        'progress_notes',
        'attendance_rate',
        'sessions_attended',
        'total_sessions',
        'goals',
        'achievements',
        'enrolled_by'
    ];

    protected $casts = [
        'enrollment_date' => 'date',
        'start_date' => 'date',
        'completion_date' => 'date',
        'attendance_rate' => 'decimal:2',
        'sessions_attended' => 'integer',
        'total_sessions' => 'integer'
    ];

    // Relationships
    public function activity()
    {
        return $this->belongsTo(Activity::class);
    }

    public function trainee()
    {
        return $this->belongsTo(Trainees::class);
    }

    public function enrolledBy()
    {
        return $this->belongsTo(Users::class, 'enrolled_by');
    }

    /**
     * Get the session enrollments for this activity enrollment
     * This provides access to session-level data when needed
     */
    public function sessionEnrollments()
    {
        return $this->hasMany(SessionEnrollment::class, 'trainee_id', 'trainee_id')
                    ->whereHas('session', function($query) {
                        $query->where('activity_id', $this->activity_id);
                    });
    }

    /**
     * Get the latest session enrollment for this activity enrollment
     * This provides a "session" relationship for backward compatibility
     */
    public function session()
    {
        return $this->hasOneThrough(
            ActivitySession::class,
            SessionEnrollment::class,
            'trainee_id', // Foreign key on SessionEnrollment table
            'id', // Foreign key on ActivitySession table
            'trainee_id', // Local key on ActivityEnrollment table
            'session_id' // Local key on SessionEnrollment table
        )->where('activity_sessions.activity_id', $this->activity_id)
         ->latest('activity_sessions.scheduled_date');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->whereIn('status', ['enrolled', 'active']);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeForActivity($query, $activityId)
    {
        return $query->where('activity_id', $activityId);
    }

    public function scopeForTrainee($query, $traineeId)
    {
        return $query->where('trainee_id', $traineeId);
    }

    public function scopeRecent($query, $days = 30)
    {
        return $query->where('enrollment_date', '>=', Carbon::now()->subDays($days));
    }

    // Accessors
    public function getStatusBadgeClassAttribute()
    {
        return match($this->status) {
            'enrolled' => 'badge-primary',
            'active' => 'badge-success',
            'completed' => 'badge-info',
            'dropped' => 'badge-danger',
            'on_hold' => 'badge-warning',
            default => 'badge-secondary'
        };
    }

    public function getStatusDisplayAttribute()
    {
        return match($this->status) {
            'enrolled' => 'Enrolled',
            'active' => 'Active',
            'completed' => 'Completed',
            'dropped' => 'Dropped',
            'on_hold' => 'On Hold',
            default => 'Unknown'
        };
    }

    public function getDaysEnrolledAttribute()
    {
        $startDate = $this->start_date ?? $this->enrollment_date;
        $endDate = $this->completion_date ?? Carbon::now();
        
        return $startDate->diffInDays($endDate);
    }

    public function getProgressPercentageAttribute()
    {
        if ($this->total_sessions <= 0) {
            return 0;
        }
        
        return round(($this->sessions_attended / $this->total_sessions) * 100, 1);
    }

    // Helper methods
    public function isActive()
    {
        return in_array($this->status, ['enrolled', 'active']);
    }

    public function isCompleted()
    {
        return $this->status === 'completed';
    }

    public function canAttend()
    {
        return $this->isActive() && !$this->isOnHold();
    }

    public function isOnHold()
    {
        return $this->status === 'on_hold';
    }

    public function markAttended()
    {
        $this->increment('sessions_attended');
        $this->increment('total_sessions');
        $this->updateAttendanceRate();
    }

    public function markAbsent()
    {
        $this->increment('total_sessions');
        $this->updateAttendanceRate();
    }

    private function updateAttendanceRate()
    {
        if ($this->total_sessions > 0) {
            $rate = ($this->sessions_attended / $this->total_sessions) * 100;
            $this->update(['attendance_rate' => round($rate, 2)]);
        }
    }

    public function addProgressNote($note)
    {
        $timestamp = Carbon::now()->format('Y-m-d H:i');
        $currentNotes = $this->progress_notes ?? '';
        $newNote = "[{$timestamp}] {$note}";
        
        $this->update([
            'progress_notes' => $currentNotes ? $currentNotes . "\n" . $newNote : $newNote
        ]);
    }
}