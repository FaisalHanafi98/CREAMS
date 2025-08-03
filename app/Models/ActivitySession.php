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
        'session_id',
        'session_code',
        'session_name',
        'session_description',
        'session_date',
        'scheduled_date',
        'start_time',
        'session_start_time',
        'end_time',
        'session_end_time',
        'venue',
        'room_number',
        'max_participants',
        'current_participants',
        'attendance_marked',
        'teacher_id',
        'supervisor_id',
        'status',
        'priority',
        'session_objectives',
        'notes',
        'session_notes',
        'session_materials',
        'recurring_pattern',
        'color_code',
        'encrypted_id'
    ];

    protected $casts = [
        'session_date' => 'date',
        'scheduled_date' => 'date',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'session_materials' => 'array',
        'recurring_pattern' => 'array',
        'attendance_marked' => 'boolean'
    ];

    protected $appends = ['formatted_time', 'duration_minutes', 'is_current'];

    /**
     * Boot method to sync scheduled_date with session_date
     */
    protected static function boot()
    {
        parent::boot();

        // Sync scheduled_date with session_date and generate session_code on create/update
        static::creating(function ($session) {
            // Generate unique session code
            if (!$session->session_code) {
                $session->session_code = static::generateSessionCode();
            }
            
            // Sync dates
            if (!$session->scheduled_date && $session->session_date) {
                $session->scheduled_date = $session->session_date;
            } elseif (!$session->session_date && $session->scheduled_date) {
                $session->session_date = $session->scheduled_date;
            }
        });

        static::updating(function ($session) {
            if ($session->isDirty('session_date') && !$session->isDirty('scheduled_date')) {
                $session->scheduled_date = $session->session_date;
            } elseif ($session->isDirty('scheduled_date') && !$session->isDirty('session_date')) {
                $session->session_date = $session->scheduled_date;
            }
        });
    }

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
        return $this->belongsTo(User::class, 'teacher_id');
    }

    /**
     * Get the supervisor for this session
     */
    public function supervisor()
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    /**
     * Get all enrollments for this session
     */
    public function enrollments()
    {
        return $this->hasMany(ActivityEnrollment::class, 'activity_id', 'activity_id');
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
     * Check for venue conflicts (same room and time)
     */
    public function hasVenueConflict($excludeSessionId = null)
    {
        if (!$this->venue) return false;

        $query = static::where('venue', $this->venue)
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
     * Check for supervisor conflicts (same supervisor double-booked)
     */
    public function hasSupervisorConflict($excludeSessionId = null)
    {
        if (!$this->supervisor_id) return false;

        $query = static::where('supervisor_id', $this->supervisor_id)
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
     * Check for all types of conflicts
     */
    public function hasAnyConflict($excludeSessionId = null)
    {
        return $this->hasTimeConflict($excludeSessionId) ||
               $this->hasVenueConflict($excludeSessionId) ||
               $this->hasSupervisorConflict($excludeSessionId);
    }

    /**
     * Get detailed conflict information
     */
    public function getConflictDetails($excludeSessionId = null)
    {
        $conflicts = [];

        if ($this->hasTimeConflict($excludeSessionId)) {
            $conflicts['teacher'] = 'Teacher is already assigned to another session at this time';
        }

        if ($this->hasVenueConflict($excludeSessionId)) {
            $conflicts['venue'] = 'Venue is already booked for another session at this time';
        }

        if ($this->hasSupervisorConflict($excludeSessionId)) {
            $conflicts['supervisor'] = 'Supervisor is already assigned to another session at this time';
        }

        return $conflicts;
    }

    /**
     * Generate unique session code
     */
    public static function generateSessionCode($activityId = null)
    {
        do {
            // Generate code format: SES-YYYYMM-XXXX (e.g., SES-202501-0001)
            $yearMonth = date('Ym');
            $sequence = static::where('session_code', 'like', "SES-{$yearMonth}-%")->count() + 1;
            $code = sprintf("SES-%s-%04d", $yearMonth, $sequence);
        } while (static::where('session_code', $code)->exists());

        return $code;
    }

    /**
     * Check if session is full
     */
    public function isFull()
    {
        return $this->current_participants >= $this->max_participants;
    }

    /**
     * Get available spots
     */
    public function getAvailableSpotsAttribute()
    {
        return max(0, $this->max_participants - $this->current_participants);
    }

    /**
     * Get capacity percentage
     */
    public function getCapacityPercentageAttribute()
    {
        if ($this->max_participants == 0) return 0;
        return round(($this->current_participants / $this->max_participants) * 100, 1);
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

    /**
     * Get room details for display
     */
    public function getRoomDetailsAttribute()
    {
        $details = $this->venue;
        if ($this->room_number) {
            $details .= " - Room {$this->room_number}";
        }
        return $details;
    }

    /**
     * Generate encrypted ID on creation
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($session) {
            if (!$session->encrypted_id) {
                $session->encrypted_id = encrypt($session->id ?: uniqid('session_'));
            }
            
            // Set default color if not provided
            if (!$session->color_code) {
                $session->color_code = '#3498db';
            }
        });
    }

    /**
     * Find session by encrypted ID
     */
    public static function findByEncryptedId($encryptedId)
    {
        try {
            $id = decrypt($encryptedId);
            return static::find($id);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get display date for calendar
     */
    public function getDisplayDateAttribute()
    {
        return $this->session_date ? $this->session_date->format('Y-m-d') : 
               ($this->scheduled_date ? $this->scheduled_date->format('Y-m-d') : null);
    }

    /**
     * Get calendar event data
     */
    public function getCalendarEventAttribute()
    {
        return [
            'id' => $this->encrypted_id,
            'title' => $this->activity->activity_name ?? 'Session',
            'start' => $this->display_date . 'T' . $this->start_time,
            'end' => $this->display_date . 'T' . $this->end_time,
            'color' => $this->color_code,
            'textColor' => '#ffffff',
            'description' => $this->session_description,
            'location' => $this->room_details,
            'status' => $this->status,
            'participants' => $this->current_participants . '/' . $this->max_participants
        ];
    }
}