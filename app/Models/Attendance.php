<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'trainee_attendances';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'trainee_id',
        'activity_id',
        'session_id',
        'attendance_date',
        'status',
        'notes',
        'marked_by_user_id',
        'marked_at'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'attendance_date' => 'date',
        'marked_at' => 'datetime'
    ];

    /**
     * Get the trainee that owns the attendance record.
     */
    public function trainee()
    {
        return $this->belongsTo(Trainee::class, 'trainee_id');
    }

    /**
     * Get the activity for this attendance record.
     */
    public function activity()
    {
        return $this->belongsTo(Activity::class, 'activity_id');
    }

    /**
     * Get the session for this attendance record.
     */
    public function session()
    {
        return $this->belongsTo(\App\Models\ActivitySession::class, 'session_id');
    }

    /**
     * Get the user who marked this attendance.
     */
    public function markedBy()
    {
        return $this->belongsTo(User::class, 'marked_by_user_id');
    }

    /**
     * Scope a query to only include attendance records for a specific date.
     */
    public function scopeForDate($query, $date)
    {
        return $query->whereDate('attendance_date', $date);
    }

    /**
     * Scope a query to only include attendance records for a specific trainee.
     */
    public function scopeForTrainee($query, $traineeId)
    {
        return $query->where('trainee_id', $traineeId);
    }

    /**
     * Scope a query to only include attendance records for a specific activity.
     */
    public function scopeForActivity($query, $activityId)
    {
        return $query->where('activity_id', $activityId);
    }

    /**
     * Get attendance records for a date range.
     */
    public static function getForDateRange($startDate, $endDate, $traineeId = null)
    {
        $query = self::whereBetween('attendance_date', [$startDate, $endDate]);
        
        if ($traineeId) {
            $query->where('trainee_id', $traineeId);
        }
        
        return $query->orderBy('attendance_date', 'desc')->get();
    }
    
    /**
     * Calculate attendance rate for a trainee within a date range.
     */
    public static function calculateAttendanceRate($traineeId, $startDate, $endDate)
    {
        // Get attendance records in the range
        $records = self::where('trainee_id', $traineeId)
            ->whereBetween('attendance_date', [$startDate, $endDate])
            ->get();
        
        // Count by status
        $presentCount = $records->where('status', 'present')->count();
        $absentCount = $records->where('status', 'absent')->count();
        $lateCount = $records->where('status', 'late')->count();
        $excusedCount = $records->where('status', 'excused')->count();
        
        $totalCount = $records->count();
        
        // Calculate percentage (considering late as half present)
        if ($totalCount > 0) {
            $percentage = round((($presentCount + ($lateCount * 0.5)) / $totalCount) * 100, 2);
        } else {
            $percentage = 0;
        }
        
        return [
            'present' => $presentCount,
            'absent' => $absentCount,
            'late' => $lateCount,
            'excused' => $excusedCount,
            'total' => $totalCount,
            'percentage' => $percentage
        ];
    }
}