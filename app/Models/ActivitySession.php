<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class ActivitySession extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'activity_id',
        'teacher_id',
        'session_code',
        'scheduled_date',
        'start_time',
        'end_time',
        'duration_minutes',
        'venue',
        'room_number',
        'max_participants',
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
        'scheduled_date' => 'datetime',
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
        return $this->belongsTo(User::class, 'teacher_id');
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
        return $query->where('scheduled_date', '>=', now())
            ->where('status', 'scheduled')
            ->orderBy('scheduled_date');
    }

    public function scopeToday($query)
    {
        return $query->whereDate('scheduled_date', today());
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
        return Carbon::parse($this->scheduled_date)->format('M d, Y') . ' at ' . 
               Carbon::parse($this->start_time)->format('g:i A') . ' - ' . 
               Carbon::parse($this->end_time)->format('g:i A');
    }

    // Methods
    public function canEnroll()
    {
        return $this->status === 'scheduled' && !$this->is_full && $this->scheduled_date > now();
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