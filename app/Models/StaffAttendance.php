<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class StaffAttendance extends Model
{
    use HasFactory;

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
        'attendance_time' => 'datetime:H:i:s',
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
            ->orderBy('attendance_time', 'desc')
            ->get();
    }

    /**
     * Check if user already checked in today
     */
    public static function hasCheckedInToday($userId)
    {
        return self::where('user_id', $userId)
            ->whereDate('attendance_date', Carbon::today())
            ->where('attendance_type', 'check_in')
            ->exists();
    }

    /**
     * Check if user already checked out today
     */
    public static function hasCheckedOutToday($userId)
    {
        return self::where('user_id', $userId)
            ->whereDate('attendance_date', Carbon::today())
            ->where('attendance_type', 'check_out')
            ->exists();
    }

    /**
     * Mark attendance for a user
     */
    public static function markAttendance($userId, $markedByUserId, $markedByEmail, $centreId, $status = 'present', $type = 'check_in', $remarks = null)
    {
        return self::create([
            'user_id' => $userId,
            'marked_by_user_id' => $markedByUserId,
            'marked_by_email' => $markedByEmail,
            'attendance_date' => Carbon::today(),
            'attendance_time' => Carbon::now()->format('H:i:s'),
            'centre_id' => $centreId,
            'status' => $status,
            'attendance_type' => $type,
            'remarks' => $remarks
        ]);
    }

    /**
     * Get attendance statistics for date range
     */
    public static function getAttendanceStats($userId, $startDate, $endDate)
    {
        $records = self::where('user_id', $userId)
            ->whereBetween('attendance_date', [$startDate, $endDate])
            ->get();

        $totalDays = Carbon::parse($startDate)->diffInDays(Carbon::parse($endDate)) + 1;
        $presentDays = $records->where('status', 'present')->count();
        $absentDays = $records->where('status', 'absent')->count();
        $lateDays = $records->where('status', 'late')->count();

        return [
            'total_days' => $totalDays,
            'present_days' => $presentDays,
            'absent_days' => $absentDays,
            'late_days' => $lateDays,
            'attendance_rate' => $totalDays > 0 ? round(($presentDays / $totalDays) * 100, 2) : 0
        ];
    }
}
