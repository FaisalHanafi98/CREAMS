<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityAttendances extends Model
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
    ];

    public function session()
    {
        return $this->belongsTo(ActivitySessions::class, 'session_id');
    }

    public function trainee()
    {
        return $this->belongsTo(Trainees::class, 'trainee_id');
    }

    public function markedBy()
    {
        return $this->belongsTo(Users::class, 'marked_by');
    }

    public function isPresent()
    {
        return in_array($this->status, ['Present', 'Late']);
    }

    public function scopePresent($query)
    {
        return $query->whereIn('status', ['Present', 'Late']);
    }

    public function scopeAbsent($query)
    {
        return $query->where('status', 'Absent');
    }

    public function scopeByDate($query, $date)
    {
        return $query->whereDate('attendance_date', $date);
    }
}