<?php

namespace App\Models;

use App\Models\Scopes\CentreScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class StaffAttendance extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::addGlobalScope(new CentreScope);
    }

    protected $fillable = [
        'user_id',
        'marked_by_user_id',
        'marked_by_email',
        'attendance_date',
        'attendance_time',
        'centre_id',
        'status',
        'remarks',
        'attendance_type'
    ];

    protected $casts = [
        'attendance_date' => 'date',
        'attendance_time' => 'datetime:H:i:s'
    ];

    /**
     * Get the user this attendance record belongs to
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the user who marked this attendance
     */
    public function markedBy()
    {
        return $this->belongsTo(User::class, 'marked_by_user_id');
    }

    /**
     * Get the centre this attendance belongs to
     */
    public function centre()
    {
        return $this->belongsTo(Centre::class, 'centre_id', 'centre_id');
    }

    /**
     * Check if attendance was self-marked
     */
    public function isSelfMarked()
    {
        return $this->user_id === $this->marked_by_user_id;
    }

    /**
     * Scope for today's attendance
     */
    public function scopeToday($query)
    {
        return $query->whereDate('attendance_date', Carbon::today());
    }

    /**
     * Scope for specific centre
     */
    public function scopeForCentre($query, $centreId)
    {
        return $query->where('centre_id', $centreId);
    }

    /**
     * Scope for specific user
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope for date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('attendance_date', [$startDate, $endDate]);
    }

    /**
     * Get today's attendance for a user
     */
    public static function getTodayAttendance($userId)
    {
        return self::where('user_id', $userId)
            ->whereDate('attendance_date', Carbon::today())
            ->first();
    }

    /**
     * Check if user already marked attendance today
     */
    public static function hasMarkedAttendanceToday($userId)
    {
        return self::where('user_id', $userId)
            ->whereDate('attendance_date', Carbon::today())
            ->exists();
    }

    /**
     * Mark attendance for a user with the correct schema fields
     * Parameters match what StaffAttendanceController calls
     */
    public static function markAttendance($targetUserId, $currentUserId, $currentUserEmail, $centreId, $status, $attendanceType, $remarks = null)
    {
        $currentTime = Carbon::now();
        
        // If no specific status provided, determine based on time for check_in
        if ($status === 'present' && $attendanceType === 'check_in') {
            $centreOpeningTime = $currentTime->copy()->setTimeFromTimeString('09:00:00'); // Default 9 AM
            $lateThreshold = $centreOpeningTime->copy()->addMinutes(15); // 15 minutes after opening
            
            if ($currentTime->gt($lateThreshold)) {
                $status = 'late';
            }
        }
        
        return self::create([
            'user_id' => $targetUserId,
            'marked_by_user_id' => $currentUserId,
            'marked_by_email' => $currentUserEmail,
            'attendance_date' => Carbon::today(),
            'attendance_time' => $currentTime->format('H:i:s'),
            'centre_id' => $centreId,
            'status' => $status,
            'remarks' => $remarks,
            'attendance_type' => $attendanceType
        ]);
    }

    /**
     * Mark check out for a user
     */
    public static function markCheckOut($userId, $markedBy = null, $isSelfMarked = true)
    {
        $attendance = self::where('user_id', $userId)
            ->whereDate('attendance_date', Carbon::today())
            ->first();
            
        if ($attendance) {
            $attendance->update([
                'check_out_time' => Carbon::now()->format('H:i:s'),
                'marked_by' => $markedBy ?: $userId,
                'is_self_marked' => $isSelfMarked && $attendance->is_self_marked
            ]);
        }
        
        return $attendance;
    }

    /**
     * Get attendance statistics for date range
     */
    public static function getAttendanceStats($userId, $startDate, $endDate)
    {
        $records = self::where('user_id', $userId)
            ->whereBetween('attendance_date', [$startDate, $endDate])
            ->get();

        $totalWorkDays = self::getWorkDaysCount($startDate, $endDate);
        $presentDays = $records->where('status', 'present')->count();
        $lateDays = $records->where('status', 'late')->count();
        $excusedDays = $records->where('status', 'excused')->count();
        $absentDays = $totalWorkDays - $records->count();

        return [
            'total_work_days' => $totalWorkDays,
            'present_days' => $presentDays,
            'late_days' => $lateDays,
            'excused_days' => $excusedDays,
            'absent_days' => $absentDays,
            'attendance_rate' => $totalWorkDays > 0 ? round((($presentDays + $lateDays) / $totalWorkDays) * 100, 2) : 0,
            'punctuality_rate' => ($presentDays + $lateDays) > 0 ? round(($presentDays / ($presentDays + $lateDays)) * 100, 2) : 0
        ];
    }

    /**
     * Get work days count (excluding weekends)
     */
    private static function getWorkDaysCount($startDate, $endDate)
    {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        $workDays = 0;
        
        while ($start->lte($end)) {
            if ($start->isWeekday()) {
                $workDays++;
            }
            $start->addDay();
        }
        
        return $workDays;
    }
}
