<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * SessionEnrollment Model
 *
 * @property int $id
 * @property int $session_id
 * @property int $trainee_id
 * @property int|null $enrolled_by
 * @property string|null $enrollment_status
 * @property string|null $attendance_status
 * @property \Illuminate\Support\Carbon|null $enrolled_at
 * @property \Illuminate\Support\Carbon|null $checked_in_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @property-read ActivitySession $session
 * @property-read Trainee $trainee
 * @property-read User $enrolledBy
 */
class SessionEnrollment extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'trainee_id',
        'enrolled_at',
        'enrolled_by',
        'enrollment_status',
        'attendance_status',
        'checked_in_at',
        'participation_score',
        'progress_notes',
        'skills_demonstrated',
        'special_requirements',
        'requires_assistance'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'checked_in_at' => 'datetime',
        'skills_demonstrated' => 'array',
        'requires_assistance' => 'boolean'
    ];
    

    // Relationships
    public function session()
    {
        return $this->belongsTo(ActivitySession::class, 'session_id');
    }

    public function trainee()
    {
        return $this->belongsTo(Trainee::class);
    }

    public function enrolledBy()
    {
        return $this->belongsTo(User::class, 'enrolled_by');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('enrollment_status', 'enrolled');
    }

    public function scopePresent($query)
    {
        return $query->where('attendance_status', 'present');
    }

    // Methods
    public function markAttendance($status)
    {
        $this->attendance_status = $status;
        if ($status === 'present') {
            $this->checked_in_at = now();
        }
        return $this->save();
    }
}