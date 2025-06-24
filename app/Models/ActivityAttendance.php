<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityAttendance extends Model
{
    use HasFactory;

    protected $table = 'activity_attendance';

    protected $fillable = [
        'session_id',
        'trainee_id',
        'attendance_date',
        'status',
        'notes',
        'marked_by'
    ];

    protected $casts = [
        'attendance_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Get the session
     */
    public function session()
    {
        return $this->belongsTo(ActivitySession::class, 'session_id');
    }

    /**
     * Get the trainee
     */
    public function trainee()
    {
        return $this->belongsTo(Trainee::class, 'trainee_id');
    }

    /**
     * Get the user who marked attendance
     */
    public function markedBy()
    {
        return $this->belongsTo(Users::class, 'marked_by');
    }

    /**
     * Scope for present attendance
     */
    public function scopePresent($query)
    {
        return $query->where('status', 'present');
    }

    /**
     * Scope for absent attendance
     */
    public function scopeAbsent($query)
    {
        return $query->where('status', 'absent');
    }

    /**
     * Get status badge color
     */
    public function getStatusColorAttribute()
    {
        return [
            'present' => 'success',
            'absent' => 'danger',
            'late' => 'warning',
            'excused' => 'info'
        ][$this->status] ?? 'secondary';
    }
}