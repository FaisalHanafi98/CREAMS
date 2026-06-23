<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class ActivityEnrollment extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'activity_enrollments';

    protected $fillable = [
        'trainee_id',
        'activity_id',
        'enrollment_status',
        'enrollment_date',
        'enrollment_notes',
        'progress_percentage',
        'attendance_count',
        'completion_date',
        'completion_notes',
        'enrolled_by',
    ];

    protected $casts = [
        'enrollment_date' => 'date',
        'completion_date' => 'date',
        'progress_percentage' => 'decimal:2',
        'attendance_count' => 'integer',
    ];

    protected $appends = ['status_badge_class', 'participation_level'];

    /**
     * Get the trainee for this enrollment
     */
    public function trainee()
    {
        return $this->belongsTo(Trainee::class);
    }

    /**
     * Get the activity for this enrollment
     */
    public function activity()
    {
        return $this->belongsTo(Activity::class);
    }

    /**
     * Get the session for this enrollment
     */
    public function session()
    {
        return $this->belongsTo(ActivitySession::class, 'session_id');
    }

    /**
     * Get the staff member who enrolled this trainee
     */
    public function enrolledBy()
    {
        return $this->belongsTo(User::class, 'enrolled_by');
    }

    /**
     * Scope for enrolled trainees
     */
    public function scopeEnrolled($query)
    {
        return $query->where('enrollment_status', 'enrolled');
    }

    /**
     * Scope for completed enrollments
     */
    public function scopeCompleted($query)
    {
        return $query->where('enrollment_status', 'completed');
    }

    /**
     * Scope for dropped enrollments
     */
    public function scopeDropped($query)
    {
        return $query->where('enrollment_status', 'dropped');
    }

    /**
     * Scope for absent enrollments
     */
    public function scopeAbsent($query)
    {
        return $query->where('enrollment_status', 'absent');
    }

    /**
     * Scope for enrollments with marked attendance
     */
    public function scopeAttendanceMarked($query)
    {
        return $query->where('attendance_marked', true);
    }

    /**
     * Scope for enrollments by trainee
     */
    public function scopeByTrainee($query, $traineeId)
    {
        return $query->where('trainee_id', $traineeId);
    }

    /**
     * Scope for enrollments by activity
     */
    public function scopeByActivity($query, $activityId)
    {
        return $query->where('activity_id', $activityId);
    }

    /**
     * Scope for enrollments by session
     */
    public function scopeBySession($query, $sessionId)
    {
        return $query->where('session_id', $sessionId);
    }

    /**
     * Get status badge class for display
     */
    public function getStatusBadgeClassAttribute()
    {
        $classes = [
            'enrolled' => 'badge-primary',
            'completed' => 'badge-success',
            'dropped' => 'badge-warning',
            'absent' => 'badge-danger'
        ];

        return $classes[$this->enrollment_status] ?? 'badge-secondary';
    }

    /**
     * Get participation level based on score
     */
    public function getParticipationLevelAttribute()
    {
        if ($this->participation_score === null) {
            return 'Not Scored';
        }

        if ($this->participation_score >= 8) {
            return 'Excellent';
        } elseif ($this->participation_score >= 6) {
            return 'Good';
        } elseif ($this->participation_score >= 4) {
            return 'Average';
        } else {
            return 'Needs Improvement';
        }
    }

    /**
     * Check if enrollment can be marked as present
     */
    public function canMarkPresent()
    {
        return $this->enrollment_status === 'enrolled' && !$this->attendance_marked;
    }

    /**
     * Check if enrollment can be marked as absent
     */
    public function canMarkAbsent()
    {
        return $this->enrollment_status === 'enrolled' && !$this->attendance_marked;
    }

    /**
     * Mark attendance as present
     */
    public function markPresent($participationScore = null, $notes = null)
    {
        if (!$this->canMarkPresent()) {
            return false;
        }

        $this->update([
            'enrollment_status' => 'completed',
            'attendance_marked' => true,
            'participation_score' => $participationScore,
            'progress_notes' => $notes
        ]);

        return true;
    }

    /**
     * Mark attendance as absent
     */
    public function markAbsent($notes = null)
    {
        if (!$this->canMarkAbsent()) {
            return false;
        }

        $this->update([
            'enrollment_status' => 'absent',
            'attendance_marked' => true,
            'progress_notes' => $notes
        ]);

        return true;
    }

    /**
     * Check if trainee has completed the activity
     */
    public function isCompleted()
    {
        return $this->enrollment_status === 'completed' && $this->attendance_marked;
    }

    /**
     * Check if trainee is currently enrolled
     */
    public function isActive()
    {
        return $this->enrollment_status === 'enrolled';
    }

    /**
     * Boot method to add model event listeners
     */
    protected static function boot()
    {
        parent::boot();

        // Update current_participants count when enrollments change
        static::created(function ($enrollment) {
            static::updateActivitySessionsParticipantCount($enrollment->activity_id);
        });

        static::updated(function ($enrollment) {
            if ($enrollment->isDirty('enrollment_status')) {
                static::updateActivitySessionsParticipantCount($enrollment->activity_id);
            }
        });

        static::deleted(function ($enrollment) {
            static::updateActivitySessionsParticipantCount($enrollment->activity_id);
        });
    }

    /**
     * Update current_participants count for all sessions of an activity
     */
    protected static function updateActivitySessionsParticipantCount($activityId)
    {
        \DB::statement("
            UPDATE activity_occurrences
            SET current_participants = (
                SELECT COUNT(DISTINCT ae.trainee_id)
                FROM activity_enrollments ae
                WHERE ae.activity_id = activity_occurrences.activity_id
                AND ae.enrollment_status IN ('enrolled', 'pending')
            )
            WHERE activity_id = ?
        ", [$activityId]);
    }

    /**
     * Static method to get enrollment statistics for an activity
     */
    public static function getActivityStatistics($activityId)
    {
        $enrollments = static::where('activity_id', $activityId);

        return [
            'total' => $enrollments->count(),
            'enrolled' => $enrollments->where('enrollment_status', 'enrolled')->count(),
            'completed' => $enrollments->where('enrollment_status', 'completed')->count(),
            'dropped' => $enrollments->where('enrollment_status', 'dropped')->count(),
            'absent' => $enrollments->where('enrollment_status', 'absent')->count(),
            'attendance_marked' => $enrollments->where('attendance_marked', true)->count(),
            'average_participation' => $enrollments->whereNotNull('participation_score')->avg('participation_score')
        ];
    }
}