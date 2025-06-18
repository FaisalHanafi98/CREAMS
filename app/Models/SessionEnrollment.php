<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SessionEnrollment extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'trainee_id',
        'enrolled_by',
        'enrollment_date',
        'status'
    ];

    protected $casts = [
        'enrollment_date' => 'datetime',
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
     * Get the user who enrolled the trainee
     */
    public function enrolledBy()
    {
        return $this->belongsTo(Users::class, 'enrolled_by');
    }

    /**
     * Get attendance for this enrollment
     */
    public function attendance()
    {
        return $this->hasMany(ActivityAttendance::class, 'trainee_id', 'trainee_id')
                    ->where('session_id', $this->session_id);
    }

    /**
     * Calculate attendance percentage
     */
    public function getAttendancePercentageAttribute()
    {
        $total = $this->attendance()->count();
        if ($total === 0) return 0;

        $present = $this->attendance()->where('status', 'present')->count();
        return round(($present / $total) * 100, 2);
    }

    /**
     * Scope for active enrollments
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}