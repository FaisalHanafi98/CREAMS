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
        'session_code',
        'session_date',
        'date',
        'start_time',
        'end_time',
        'duration_minutes',
        'duration',
        'venue',
        'location',
        'room_number',
        'max_participants',
        'max_capacity',
        'enrolled_count',
        'status',
        'notes',
        'materials_prepared',
        'attendance_marked',
        'actual_start',
        'actual_end',
        'session_report'
    ];

    protected $casts = [
        'session_date' => 'datetime',
        'date' => 'datetime',
        'actual_start' => 'datetime',
        'actual_end' => 'datetime',
        'attendance_marked' => 'boolean'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($session) {
            // Generate unique session code
            $session->session_code = 'SES' . date('Ymd') . strtoupper(substr(uniqid(), -4));
        });
    }

    // Relationships
    public function activity()
    {
        return $this->belongsTo(Activity::class);
    }

    public function teacher()
    {
        return $this->belongsTo(Users::class, 'teacher_id');
    }

    public function enrollments()
    {
        return $this->hasMany(SessionEnrollment::class, 'session_id');
    }

    public function trainees()
    {
        return $this->belongsToMany(Trainee::class, 'session_enrollments', 'session_id', 'trainee_id')
            ->withPivot(['attendance_status', 'participation_score', 'progress_notes'])
            ->withTimestamps();
    }

    // Scopes
    public function scopeUpcoming($query)
    {
        return $query->where('session_date', '>=', now())
            ->where('status', 'scheduled')
            ->orderBy('session_date');
    }

    public function scopeToday($query)
    {
        return $query->whereDate('session_date', today());
    }

    public function scopeByTeacher($query, $teacherId)
    {
        return $query->where('teacher_id', $teacherId);
    }

    // Accessors
    public function getIsFullAttribute()
    {
        return $this->enrolled_count >= $this->max_participants;
    }

    public function getAvailableSlotsAttribute()
    {
        return max(0, $this->max_participants - $this->enrolled_count);
    }

    public function getFormattedScheduleAttribute()
    {
        if (!$this->session_date || !$this->start_time || !$this->end_time) {
            return 'Schedule not set';
        }
        
        try {
            return Carbon::parse($this->session_date)->format('M d, Y') . ' at ' . 
                   Carbon::parse($this->start_time)->format('g:i A') . ' - ' . 
                   Carbon::parse($this->end_time)->format('g:i A');
        } catch (\Exception $e) {
            return 'Invalid schedule format';
        }
    }

    // Methods
    public function canEnroll()
    {
        return $this->status === 'scheduled' && !$this->is_full && $this->session_date > now();
    }

    public function markAttendance($traineeId, $status)
    {
        return $this->enrollments()
            ->where('trainee_id', $traineeId)
            ->update([
                'attendance_status' => $status,
                'checked_in_at' => $status === 'present' ? now() : null
            ]);
    }
}