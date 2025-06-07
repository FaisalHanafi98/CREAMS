<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivitySessions extends Model
{
    use HasFactory;

    protected $fillable = [
        'activity_id',
        'teacher_id',
        'class_name',
        'day_of_week',
        'start_time',
        'end_time',
        'duration_hours',
        'location',
        'max_capacity',
        'current_enrollment',
        'semester',
        'is_active',
        'notes'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'duration_hours' => 'decimal:1',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
    ];

    public function activity()
    {
        return $this->belongsTo(Activity::class);
    }

    public function teacher()
    {
        return $this->belongsTo(Users::class, 'teacher_id');
    }

    public function attendance()
    {
        return $this->hasMany(ActivityAttendance::class, 'session_id');
    }

    public function isFullyBooked()
    {
        return $this->current_enrollment >= $this->max_capacity;
    }

    public function getAvailableSlots()
    {
        return max(0, $this->max_capacity - $this->current_enrollment);
    }

    public function getTodayAttendanceAttribute()
    {
        return $this->attendance()->whereDate('attendance_date', today())->get();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByTeacher($query, $teacherId)
    {
        return $query->where('teacher_id', $teacherId);
    }

    public function scopeByDay($query, $day)
    {
        return $query->where('day_of_week', $day);
    }
}