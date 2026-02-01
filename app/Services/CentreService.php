<?php

namespace App\Services;

use App\Models\Centre;
use App\Models\User;
use App\Models\Trainee;
use App\Models\Activity;
use App\Models\Asset;
use App\Models\CentreStatistics;
use App\Models\CentreAuditLog;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CentreService
{
    /**
     * Cache duration in minutes
     */
    const CACHE_DURATION = 30;
    const LONG_CACHE_DURATION = 1440; // 24 hours
    
    /**
     * Get centres with optimized caching and filtering
     */
    public function getCentres($filters = [], $userId = null, $userRole = null)
    {
        $cacheKey = $this->generateCacheKey('centres_list', array_merge($filters, [
            'user_id' => $userId,
            'user_role' => $userRole
        ]));
        
        return Cache::remember($cacheKey, self::CACHE_DURATION, function () use ($filters, $userId, $userRole) {
            $query = Centre::query();
            
            // Apply role-based filtering
            if (!in_array($userRole, ['admin'])) {
                $user = User::find($userId);
                if ($user) {
                    $query->where('id', $user->centre_id);
                }
            }
            
            // Apply search filters
            if (!empty($filters['search'])) {
                $search = $filters['search'];
                $query->where(function ($q) use ($search) {
                    $q->where('centre_name', 'LIKE', "%{$search}%")
                      ->orWhere('centre_id', 'LIKE', "%{$search}%")
                      ->orWhere('centre_address', 'LIKE', "%{$search}%");
                });
            }
            
            // Apply status filter
            if (!empty($filters['status'])) {
                $query->where('centre_status', $filters['status']);
            }
            
            // Apply type filter
            if (!empty($filters['type'])) {
                $query->where('centre_type', $filters['type']);
            }
            
            // Apply active filter
            if (isset($filters['is_active'])) {
                $query->where('is_active', $filters['is_active']);
            }
            
            return $query->with([
                    'users' => function($q) {
                        $q->select('id', 'centre_id', 'name', 'role', 'is_active')
                          ->where('is_active', true);
                    }
                ])
                ->withCount([
                    'users as active_users_count' => function($q) {
                        $q->where('is_active', true);
                    },
                    'trainees as active_trainees_count' => function($q) {
                        $q->where('is_active', true);
                    }
                ])
                ->orderBy('centre_name')
                ->get();
        });
    }
    
    /**
     * Get centre details with comprehensive statistics
     */
    public function getCentreDetails($centreId, $userId = null, $userRole = null)
    {
        // Validate access
        if (!$this->canAccessCentre($centreId, $userId, $userRole)) {
            throw new \Exception('Access denied to centre data');
        }
        
        $cacheKey = "centre_details_{$centreId}";
        
        return Cache::remember($cacheKey, self::CACHE_DURATION, function () use ($centreId) {
            $centre = Centre::with([
                    'users' => function($q) {
                        $q->select('id', 'centre_id', 'name', 'role', 'is_active')
                          ->where('is_active', true)
                          ->orderBy('name');
                    },
                    'trainees' => function($q) {
                        $q->select('id', 'centre_id', 'trainee_first_name', 'trainee_last_name', 'unique_identifier', 'is_active')
                          ->where('is_active', true)
                          ->orderBy('trainee_first_name');
                    }
                ])
                ->findOrFail($centreId);
                
            // Get comprehensive statistics
            $centre->statistics = $this->getCentreStatistics($centreId);
            $centre->recent_activities = $this->getRecentActivities($centreId);
            
            return $centre;
        });
    }
    
    /**
     * Get centre statistics with intelligent caching
     */
    public function getCentreStatistics($centreId, $forceRefresh = false)
    {
        $cacheKey = "centre_stats_{$centreId}";
        
        if ($forceRefresh) {
            Cache::forget($cacheKey);
        }
        
        return Cache::remember($cacheKey, self::CACHE_DURATION, function () use ($centreId) {
            return $this->calculateRealTimeStatistics($centreId);
        });
    }
    
    /**
     * Calculate real-time statistics for a centre
     */
    private function calculateRealTimeStatistics($centreId)
    {
        $centre = Centre::find($centreId);
        if (!$centre) {
            return [];
        }
        
        $stats = [
            'centre_id' => $centreId,
            'centre_name' => $centre->centre_name,
            'centre_capacity' => $centre->centre_capacity ?? 0,
            'total_users' => User::where('centre_id', $centreId)->where('is_active', true)->count(),
            'total_trainees' => Trainee::where('centre_id', $centreId)->where('is_active', true)->count(),
            'total_activities' => Activity::where('centre_id', $centreId)->where('activity_status', 'scheduled')->count(),
            'total_assets' => Asset::where('centre_id', $centreId)->count(),
            'active_sessions_today' => $this->getTodayActiveSessions($centreId),
            'completed_sessions_today' => $this->getTodayCompletedSessions($centreId),
            'scheduled_sessions_upcoming' => $this->getUpcomingSessions($centreId),
            'utilization_rate' => 0,
            'attendance_rate_30d' => 0,
            'performance_trends' => null,
            'last_calculated' => now()->toDateTimeString()
        ];
        
        // Calculate utilization rate
        if ($stats['centre_capacity'] > 0) {
            $stats['utilization_rate'] = round(($stats['total_trainees'] / $stats['centre_capacity']) * 100, 2);
        }
        
        // Calculate 30-day attendance rate
        $stats['attendance_rate_30d'] = $this->calculateAttendanceRate($centreId, 30);
        
        // Get performance trends
        $stats['performance_trends'] = $this->getPerformanceTrends($centreId);
        
        // Update the statistics table asynchronously
        $this->updateStatisticsRecord($centreId, $stats);
        
        return $stats;
    }
    
    /**
     * Get today's active sessions for a centre
     */
    private function getTodayActiveSessions($centreId)
    {
        return DB::table('activity_occurrences')
            ->join('activities', 'activity_occurrences.activity_id', '=', 'activities.id')
            ->where('activities.centre_id', $centreId)
            ->where('activity_occurrences.session_date', now()->toDateString())
            ->where('activity_occurrences.session_status', 'scheduled')
            ->count();
    }
    
    /**
     * Get today's completed sessions for a centre
     */
    private function getTodayCompletedSessions($centreId)
    {
        return DB::table('activity_occurrences')
            ->join('activities', 'activity_occurrences.activity_id', '=', 'activities.id')
            ->where('activities.centre_id', $centreId)
            ->where('activity_occurrences.session_date', now()->toDateString())
            ->where('activity_occurrences.session_status', 'completed')
            ->count();
    }
    
    /**
     * Get upcoming scheduled sessions
     */
    private function getUpcomingSessions($centreId, $days = 7)
    {
        $endDate = now()->addDays($days)->toDateString();
        
        return DB::table('activity_occurrences')
            ->join('activities', 'activity_occurrences.activity_id', '=', 'activities.id')
            ->where('activities.centre_id', $centreId)
            ->where('activity_occurrences.session_date', '>', now()->toDateString())
            ->where('activity_occurrences.session_date', '<=', $endDate)
            ->where('activity_occurrences.session_status', 'scheduled')
            ->count();
    }
    
    /**
     * Calculate attendance rate for specified period
     */
    private function calculateAttendanceRate($centreId, $days = 30)
    {
        $startDate = Carbon::now()->subDays($days);
        
        $attendanceData = DB::table('session_attendance')
            ->join('activity_occurrences', 'session_attendance.session_id', '=', 'activity_occurrences.id')
            ->join('activities', 'activity_occurrences.activity_id', '=', 'activities.id')
            ->where('activities.centre_id', $centreId)
            ->where('activity_occurrences.session_date', '>=', $startDate)
            ->selectRaw('
                COUNT(*) as total_enrollments,
                SUM(CASE WHEN session_attendance.attendance_status IN ("present", "late") THEN 1 ELSE 0 END) as present_count
            ')
            ->first();
            
        if (!$attendanceData || $attendanceData->total_enrollments == 0) {
            return 0;
        }
        
        return round(($attendanceData->present_count / $attendanceData->total_enrollments) * 100, 2);
    }
    
    /**
     * Get performance trends compared to previous period
     */
    private function getPerformanceTrends($centreId)
    {
        $currentStats = CentreStatistics::where('centre_id', $centreId)
            ->orderBy('last_calculated', 'desc')
            ->first();
            
        if (!$currentStats) {
            return null;
        }
        
        $previousStats = CentreStatistics::where('centre_id', $centreId)
            ->where('last_calculated', '<', $currentStats->last_calculated)
            ->orderBy('last_calculated', 'desc')
            ->first();
            
        if (!$previousStats) {
            return null;
        }
        
        return [
            'utilization_rate' => [
                'change' => round($currentStats->utilization_rate - $previousStats->utilization_rate, 2),
                'direction' => $this->getTrendDirection($currentStats->utilization_rate, $previousStats->utilization_rate)
            ],
            'attendance_rate' => [
                'change' => round($currentStats->attendance_rate - $previousStats->attendance_rate, 2),
                'direction' => $this->getTrendDirection($currentStats->attendance_rate, $previousStats->attendance_rate)
            ],
            'total_users' => [
                'change' => $currentStats->total_users - $previousStats->total_users,
                'direction' => $this->getTrendDirection($currentStats->total_users, $previousStats->total_users)
            ],
            'total_trainees' => [
                'change' => $currentStats->total_trainees - $previousStats->total_trainees,
                'direction' => $this->getTrendDirection($currentStats->total_trainees, $previousStats->total_trainees)
            ]
        ];
    }
    
    /**
     * Get trend direction
     */
    private function getTrendDirection($current, $previous)
    {
        if ($current > $previous) {
            return 'up';
        } elseif ($current < $previous) {
            return 'down';
        } else {
            return 'stable';
        }
    }
    
    /**
     * Get recent activities for a centre
     */
    private function getRecentActivities($centreId, $limit = 10)
    {
        return Activity::where('centre_id', $centreId)
            ->where('activity_status', 'scheduled')
            ->select('id', 'activity_name', 'activity_type', 'created_at', 'activity_status')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($activity) {
                return [
                    'id' => $activity->id,
                    'name' => $activity->activity_name,
                    'type' => $activity->activity_type,
                    'date' => $activity->created_at->toDateString(),
                    'time' => $activity->created_at->format('H:i'),
                    'status' => $activity->activity_status
                ];
            });
    }
    
    /**
     * Update statistics record in database
     */
    private function updateStatisticsRecord($centreId, $stats)
    {
        try {
            CentreStatistics::updateOrCreate(
                ['centre_id' => $centreId],
                [
                    'total_users' => $stats['total_users'],
                    'total_trainees' => $stats['total_trainees'],
                    'total_activities' => $stats['total_activities'],
                    'total_assets' => $stats['total_assets'],
                    'utilization_rate' => $stats['utilization_rate'],
                    'attendance_rate' => $stats['attendance_rate_30d'],
                    'last_calculated' => now()
                ]
            );
        } catch (\Exception $e) {
            Log::warning('Failed to update centre statistics', [
                'centre_id' => $centreId,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Check if user can access centre data
     */
    private function canAccessCentre($centreId, $userId, $userRole)
    {
        if (in_array($userRole, ['admin'])) {
            return true;
        }
        
        if (!$userId) {
            return false;
        }
        
        $user = User::find($userId);
        return $user && $user->centre_id == $centreId;
    }
    
    /**
     * Clear centre-related caches
     */
    public function clearCentreCache($centreId = null)
    {
        if ($centreId) {
            $patterns = [
                "centre_stats_{$centreId}",
                "centre_details_{$centreId}",
                "centres_list*"
            ];
        } else {
            $patterns = [
                "centre_stats_*",
                "centre_details_*",
                "centres_list*"
            ];
        }
        
        foreach ($patterns as $pattern) {
            if (strpos($pattern, '*') !== false) {
                // For wildcard patterns, we'd need a more sophisticated cache clearing mechanism
                // For now, just clear the basic keys
                Cache::forget(str_replace('*', '', $pattern));
            } else {
                Cache::forget($pattern);
            }
        }
    }
    
    /**
     * Generate cache key with consistent hashing
     */
    private function generateCacheKey($prefix, $data = [])
    {
        ksort($data);
        $hash = md5(serialize($data));
        return "{$prefix}_{$hash}";
    }
    
    /**
     * Log centre action for audit trail
     */
    public function logCentreAction($centreId, $userId, $action, $oldValues = null, $newValues = null, $notes = null)
    {
        try {
            CentreAuditLog::create([
                'centre_id' => $centreId,
                'user_id' => $userId,
                'action' => $action,
                'old_values' => $oldValues ? json_encode($oldValues) : null,
                'new_values' => $newValues ? json_encode($newValues) : null,
                'notes' => $notes,
                'ip_address' => request()->ip()
            ]);
            
            Log::info('Centre action logged', [
                'centre_id' => $centreId,
                'user_id' => $userId,
                'action' => $action
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to log centre action', [
                'centre_id' => $centreId,
                'action' => $action,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Get centres summary for dashboard
     */
    public function getCentresSummary($userId = null, $userRole = null)
    {
        $cacheKey = "centres_summary_{$userId}_{$userRole}";
        
        return Cache::remember($cacheKey, self::CACHE_DURATION, function () use ($userId, $userRole) {
            $query = Centre::query();
            
            // Apply role-based filtering
            if (!in_array($userRole, ['admin'])) {
                $user = User::find($userId);
                if ($user) {
                    $query->where('id', $user->centre_id);
                }
            }
            
            $centres = $query->get();
            
            return [
                'total_centres' => $centres->count(),
                'active_centres' => $centres->where('is_active', true)->count(),
                'inactive_centres' => $centres->where('is_active', false)->count(),
                'maintenance_centres' => $centres->where('centre_status', 'maintenance')->count(),
                'total_capacity' => $centres->sum('centre_capacity'),
                'average_utilization' => $this->calculateAverageUtilization($centres),
                'performance_overview' => $this->getPerformanceOverview($centres)
            ];
        });
    }
    
    /**
     * Calculate average utilization across centres
     */
    private function calculateAverageUtilization($centres)
    {
        if ($centres->isEmpty()) {
            return 0;
        }
        
        $totalUtilization = 0;
        $centreCount = 0;
        
        foreach ($centres as $centre) {
            if ($centre->centre_capacity > 0) {
                $trainees = Trainee::where('centre_id', $centre->id)->where('is_active', true)->count();
                $utilization = ($trainees / $centre->centre_capacity) * 100;
                $totalUtilization += $utilization;
                $centreCount++;
            }
        }
        
        return $centreCount > 0 ? round($totalUtilization / $centreCount, 2) : 0;
    }
    
    /**
     * Get performance overview for all centres
     */
    private function getPerformanceOverview($centres)
    {
        $overview = [
            'high_performance' => 0,
            'medium_performance' => 0,
            'low_performance' => 0,
            'total_users' => 0,
            'total_trainees' => 0,
            'total_activities' => 0
        ];
        
        foreach ($centres as $centre) {
            $stats = $this->getCentreStatistics($centre->id);
            
            // Categorize performance based on utilization and attendance
            $utilizationRate = $stats['utilization_rate'] ?? 0;
            $attendanceRate = $stats['attendance_rate_30d'] ?? 0;
            $performanceScore = ($utilizationRate + $attendanceRate) / 2;
            
            if ($performanceScore >= 80) {
                $overview['high_performance']++;
            } elseif ($performanceScore >= 60) {
                $overview['medium_performance']++;
            } else {
                $overview['low_performance']++;
            }
            
            $overview['total_users'] += $stats['total_users'] ?? 0;
            $overview['total_trainees'] += $stats['total_trainees'] ?? 0;
            $overview['total_activities'] += $stats['total_activities'] ?? 0;
        }
        
        return $overview;
    }
}