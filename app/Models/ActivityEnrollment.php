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
        'enrollment_status',
        'enrollment_notes',
        'progress_percentage',
        'attendance_count',
        'completion_date',
        'completion_notes',
        'enrolled_by'
    ];

    protected $casts = [
        'enrollment_date' => 'date',
        'completion_date' => 'date',
        'progress_percentage' => 'decimal:2',
        'attendance_count' => 'integer'
    ];

    // Relationships
    public function activity()
    {
        return $this->belongsTo(Activity::class);
    }

    public function trainee()
    {
        return $this->belongsTo(Trainee::class);
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
        return $query->whereIn('enrollment_status', ['enrolled', 'active']);
    }

    public function scopeCompleted($query)
    {
        return $query->where('enrollment_status', 'completed');
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
        return match($this->enrollment_status) {
            'enrolled' => 'badge-primary',
            'active' => 'badge-success',
            'completed' => 'badge-info',
            'dropped' => 'badge-danger',
            'pending' => 'badge-warning',
            default => 'badge-secondary'
        };
    }

    public function getStatusDisplayAttribute()
    {
        return match($this->enrollment_status) {
            'enrolled' => 'Enrolled',
            'active' => 'Active',
            'completed' => 'Completed',
            'dropped' => 'Dropped',
            'pending' => 'Pending',
            default => 'Unknown'
        };
    }

    public function getDaysEnrolledAttribute()
    {
        $startDate = $this->enrollment_date;
        $endDate = $this->completion_date ?? Carbon::now();
        
        return $startDate->diffInDays($endDate);
    }

    // Helper methods
    public function isActive()
    {
        return in_array($this->enrollment_status, ['enrolled', 'active']);
    }

    public function isCompleted()
    {
        return $this->enrollment_status === 'completed';
    }

    public function canAttend()
    {
        return $this->isActive() && !$this->isOnHold();
    }

    public function isOnHold()
    {
        return $this->enrollment_status === 'pending';
    }

    public function markAttended()
    {
        $this->increment('attendance_count');
    }

    public function addProgressNote($note)
    {
        $timestamp = Carbon::now()->format('Y-m-d H:i');
        $currentNotes = $this->enrollment_notes ?? '';
        $newNote = "[{$timestamp}] {$note}";
        
        $this->update([
            'enrollment_notes' => $currentNotes ? $currentNotes . "\n" . $newNote : $newNote
        ]);
    }
}