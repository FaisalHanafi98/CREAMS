<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ActivityEnrollments extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'activity_session_id',
        'trainee_id',
        'enrolled_by',
        'enrollment_status',
        'enrollment_date',
        'start_date',
        'end_date',
        'enrollment_notes',
        'withdrawal_reason',
        'withdrawal_date',
        'progress_percentage',
        'individual_goals'
    ];

    protected $casts = [
        'enrollment_date' => 'date',
        'start_date' => 'date',
        'end_date' => 'date',
        'withdrawal_date' => 'date',
        'individual_goals' => 'array',
        'progress_percentage' => 'decimal:2'
    ];

    // Relationships
    public function activitySession()
    {
        return $this->belongsTo(ActivitySessions::class);
    }

    public function trainee()
    {
        return $this->belongsTo(Trainees::class);
    }

    public function enrolledBy()
    {
        return $this->belongsTo(Users::class, 'enrolled_by');
    }

    public function attendance()
    {
        return $this->hasMany(ActivityAttendances::class, 'trainee_id', 'trainee_id')
                    ->where('activity_session_id', $this->activity_session_id);
    }

    public function progressReports()
    {
        return $this->hasMany(TraineeProgress::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('enrollment_status', 'active');
    }

    public function scopeByTrainee($query, $traineeId)
    {
        return $query->where('trainee_id', $traineeId);
    }

    public function scopeBySession($query, $sessionId)
    {
        return $query->where('activity_session_id', $sessionId);
    }

    // Helper methods
    public function isActive()
    {
        return $this->enrollment_status === 'active';
    }

    public function canWithdraw()
    {
        return in_array($this->enrollment_status, ['active', 'pending']);
    }

    public function getAttendanceRate()
    {
        $totalSessions = $this->attendance()->count();
        $presentSessions = $this->attendance()->where('attendance_status', 'present')->count();
        
        return $totalSessions > 0 ? ($presentSessions / $totalSessions) * 100 : 0;
    }
}