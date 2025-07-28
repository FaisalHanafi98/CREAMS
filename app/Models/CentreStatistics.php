<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class CentreStatistics extends Model
{
    use HasFactory;

    protected $table = 'centre_statistics';

    protected $fillable = [
        'centre_id',
        'total_users',
        'total_trainees',
        'total_activities',
        'total_assets',
        'utilization_rate',
        'attendance_rate',
        'monthly_stats',
        'last_calculated'
    ];

    protected $casts = [
        'monthly_stats' => 'array',
        'utilization_rate' => 'decimal:2',
        'attendance_rate' => 'decimal:2',
        'last_calculated' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Get the centre that these statistics belong to
     */
    public function centre()
    {
        return $this->belongsTo(Centre::class, 'centre_id');
    }

    /**
     * Scope for recent statistics
     */
    public function scopeRecent($query, $days = 30)
    {
        return $query->where('last_calculated', '>=', Carbon::now()->subDays($days));
    }

    /**
     * Scope for statistics within date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('last_calculated', [$startDate, $endDate]);
    }

    /**
     * Get utilization status based on rate
     */
    public function getUtilizationStatusAttribute()
    {
        $rate = $this->utilization_rate;
        
        if ($rate >= 90) {
            return 'overcapacity';
        } elseif ($rate >= 75) {
            return 'high';
        } elseif ($rate >= 50) {
            return 'moderate';
        } elseif ($rate >= 25) {
            return 'low';
        } else {
            return 'very_low';
        }
    }

    /**
     * Get attendance status based on rate
     */
    public function getAttendanceStatusAttribute()
    {
        $rate = $this->attendance_rate;
        
        if ($rate >= 90) {
            return 'excellent';
        } elseif ($rate >= 80) {
            return 'good';
        } elseif ($rate >= 70) {
            return 'fair';
        } elseif ($rate >= 60) {
            return 'poor';
        } else {
            return 'critical';
        }
    }

    /**
     * Get formatted monthly statistics
     */
    public function getFormattedMonthlyStatsAttribute()
    {
        if (!$this->monthly_stats) {
            return [];
        }

        $stats = $this->monthly_stats;
        $formatted = [];

        foreach ($stats as $month => $data) {
            $formatted[] = [
                'month' => Carbon::createFromFormat('Y-m', $month)->format('F Y'),
                'month_key' => $month,
                'utilization_rate' => $data['utilization_rate'] ?? 0,
                'attendance_rate' => $data['attendance_rate'] ?? 0,
                'total_sessions' => $data['total_sessions'] ?? 0,
                'total_trainees' => $data['total_trainees'] ?? 0
            ];
        }

        return $formatted;
    }

    /**
     * Update monthly statistics
     */
    public function updateMonthlyStats($month, $stats)
    {
        $monthlyStats = $this->monthly_stats ?? [];
        $monthlyStats[$month] = $stats;
        
        // Keep only last 12 months
        $sortedMonths = array_keys($monthlyStats);
        sort($sortedMonths);
        
        if (count($sortedMonths) > 12) {
            $keepMonths = array_slice($sortedMonths, -12);
            $monthlyStats = array_intersect_key($monthlyStats, array_flip($keepMonths));
        }
        
        $this->update(['monthly_stats' => $monthlyStats]);
    }

    /**
     * Get performance trends (comparing with previous period)
     */
    public function getPerformanceTrendsAttribute()
    {
        $previousStats = static::where('centre_id', $this->centre_id)
            ->where('last_calculated', '<', $this->last_calculated)
            ->orderBy('last_calculated', 'desc')
            ->first();

        if (!$previousStats) {
            return null;
        }

        $utilizationTrend = $this->utilization_rate - $previousStats->utilization_rate;
        $attendanceTrend = $this->attendance_rate - $previousStats->attendance_rate;
        $usersTrend = $this->total_users - $previousStats->total_users;
        $traineesTrend = $this->total_trainees - $previousStats->total_trainees;

        return [
            'utilization_rate' => [
                'change' => round($utilizationTrend, 2),
                'direction' => $utilizationTrend > 0 ? 'up' : ($utilizationTrend < 0 ? 'down' : 'stable')
            ],
            'attendance_rate' => [
                'change' => round($attendanceTrend, 2),
                'direction' => $attendanceTrend > 0 ? 'up' : ($attendanceTrend < 0 ? 'down' : 'stable')
            ],
            'total_users' => [
                'change' => $usersTrend,
                'direction' => $usersTrend > 0 ? 'up' : ($usersTrend < 0 ? 'down' : 'stable')
            ],
            'total_trainees' => [
                'change' => $traineesTrend,
                'direction' => $traineesTrend > 0 ? 'up' : ($traineesTrend < 0 ? 'down' : 'stable')
            ]
        ];
    }

    /**
     * Calculate and store fresh statistics for a centre
     */
    public static function calculateAndStore($centreId)
    {
        $centre = Centre::find($centreId);
        if (!$centre) {
            return false;
        }

        // Calculate current month stats
        $currentMonth = Carbon::now()->format('Y-m');
        
        $stats = [
            'centre_id' => $centreId,
            'total_users' => User::where('centre_id', $centreId)->where('is_active', true)->count(),
            'total_trainees' => Trainee::where('centre_id', $centreId)->where('is_active', true)->count(),
            'total_activities' => Activity::where('centre_id', $centreId)->where('activity_status', 'scheduled')->count(),
            'total_assets' => Asset::where('centre_id', $centreId)->count(),
            'utilization_rate' => 0, // Will be calculated based on capacity
            'attendance_rate' => 0,  // Will be calculated based on recent sessions
            'last_calculated' => now()
        ];

        // Calculate utilization rate
        if ($centre->centre_capacity > 0) {
            $stats['utilization_rate'] = round(($stats['total_trainees'] / $centre->centre_capacity) * 100, 2);
        }

        // Calculate attendance rate (last 30 days)
        $thirtyDaysAgo = Carbon::now()->subDays(30);
        $attendanceData = \DB::table('session_enrollments')
            ->join('activity_sessions', 'session_enrollments.session_id', '=', 'activity_sessions.id')
            ->join('activities', 'activity_sessions.activity_id', '=', 'activities.id')
            ->where('activities.centre_id', $centreId)
            ->where('activity_sessions.session_date', '>=', $thirtyDaysAgo)
            ->selectRaw('
                COUNT(*) as total_enrollments,
                SUM(CASE WHEN session_enrollments.attendance_status IN ("present", "late") THEN 1 ELSE 0 END) as present_count
            ')
            ->first();

        if ($attendanceData && $attendanceData->total_enrollments > 0) {
            $stats['attendance_rate'] = round(($attendanceData->present_count / $attendanceData->total_enrollments) * 100, 2);
        }

        // Create or update statistics record
        $statisticsRecord = static::updateOrCreate(
            ['centre_id' => $centreId],
            $stats
        );

        // Update monthly stats
        $monthlyStats = [
            'utilization_rate' => $stats['utilization_rate'],
            'attendance_rate' => $stats['attendance_rate'],
            'total_sessions' => \DB::table('activity_sessions')
                ->join('activities', 'activity_sessions.activity_id', '=', 'activities.id')
                ->where('activities.centre_id', $centreId)
                ->whereRaw('DATE_FORMAT(activity_sessions.session_date, "%Y-%m") = ?', [$currentMonth])
                ->count(),
            'total_trainees' => $stats['total_trainees']
        ];

        $statisticsRecord->updateMonthlyStats($currentMonth, $monthlyStats);

        return $statisticsRecord;
    }
}