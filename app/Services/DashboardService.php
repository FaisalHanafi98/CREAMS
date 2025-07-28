<?php

namespace App\Services;

use App\Models\User;
use App\Models\Trainee;
use App\Models\Activity;
use App\Models\ActivitySession;
use App\Models\Letter;
use App\Models\Asset;
use App\Models\AssetMaintenance;
use App\Models\Centre;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class DashboardService
{
    /**
     * Get role-specific dashboard data with aggressive caching
     */
    public function getDashboardData($userId, $role, $centreId): array
    {
        $cacheKey = "dashboard_{$role}_{$userId}_{$centreId}";
        
        return Cache::remember($cacheKey, 300, function() use ($userId, $role, $centreId) {
            try {
                switch ($role) {
                    case 'admin':
                        return $this->getAdminDashboard($centreId);
                    case 'supervisor':
                        return $this->getSupervisorDashboard($centreId);
                    case 'teacher':
                        return $this->getTeacherDashboard($userId, $centreId);
                    case 'ajk':
                        return $this->getAjkDashboard($centreId);
                    default:
                        return $this->getDefaultDashboard($centreId);
                }
            } catch (\Exception $e) {
                Log::error('Dashboard data retrieval failed', [
                    'user_id' => $userId,
                    'role' => $role,
                    'centre_id' => $centreId,
                    'error' => $e->getMessage()
                ]);
                
                return $this->getEmptyDashboard();
            }
        });
    }

    /**
     * Admin dashboard with comprehensive system overview
     */
    private function getAdminDashboard($centreId): array
    {
        // Use single queries with counts for performance
        $stats = $this->getAdminStats();
        
        return [
            'stats' => $stats,
            'charts' => [
                'user_growth' => $this->getUserGrowthData(6),
                'activity_distribution' => $this->getActivityDistribution(),
                'centre_performance' => $this->getCentrePerformance(),
                'monthly_statistics' => $this->getMonthlyStatistics(),
                'system_metrics' => $this->getSystemMetrics()
            ],
            'recent' => [
                'users' => $this->getRecentUsers(5),
                'trainees' => $this->getRecentTrainees(5),
                'activities' => $this->getRecentActivities(5),
                'letters' => $this->getRecentLetters(5)
            ],
            'alerts' => $this->getSystemAlerts(),
            'system_health' => $this->getSystemHealth()
        ];
    }

    /**
     * Supervisor dashboard with centre-specific data
     */
    private function getSupervisorDashboard($centreId): array
    {
        $stats = $this->getSupervisorStats($centreId);
        
        return [
            'stats' => $stats,
            'charts' => [
                'trainee_progress' => $this->getTraineeProgressData($centreId),
                'activity_completion' => $this->getActivityCompletionData($centreId),
                'staff_performance' => $this->getStaffPerformanceData($centreId),
                'monthly_overview' => $this->getMonthlyOverview($centreId)
            ],
            'schedule' => [
                'today' => $this->getTodaySchedule($centreId),
                'week' => $this->getWeekSchedule($centreId),
                'upcoming' => $this->getUpcomingActivities($centreId, 10)
            ],
            'team' => [
                'teachers' => $this->getCentreTeachers($centreId),
                'trainees' => $this->getCentreTrainees($centreId, 10),
                'performance' => $this->getTeamPerformance($centreId)
            ],
            'alerts' => $this->getCentreAlerts($centreId)
        ];
    }

    /**
     * Teacher dashboard with personalized data
     */
    private function getTeacherDashboard($userId, $centreId): array
    {
        $today = Carbon::today();
        $stats = $this->getTeacherStats($userId, $today);
        
        return [
            'stats' => $stats,
            'schedule' => [
                'today' => $this->getTeacherSchedule($userId, $today),
                'week' => $this->getTeacherWeekSchedule($userId),
                'upcoming' => $this->getTeacherUpcomingSessions($userId, 10)
            ],
            'performance' => [
                'attendance_rate' => $this->calculateTeacherAttendanceRate($userId),
                'completion_rate' => $this->calculateTeacherCompletionRate($userId),
                'trainee_progress' => $this->getTeacherTraineeProgress($userId),
                'monthly_summary' => $this->getTeacherMonthlySummary($userId)
            ],
            'trainees' => [
                'active' => $this->getTeacherActiveTrainees($userId),
                'recent_enrollments' => $this->getTeacherRecentEnrollments($userId, 5),
                'progress_alerts' => $this->getTraineeProgressAlerts($userId)
            ],
            'quick_actions' => $this->getTeacherQuickActions($userId)
        ];
    }

    /**
     * AJK dashboard with support-focused data
     */
    private function getAjkDashboard($centreId): array
    {
        return [
            'stats' => [
                'active_trainees' => Trainee::where('centre_id', $centreId)->where('is_active', true)->count(),
                'today_sessions' => ActivitySession::whereDate('session_date', Carbon::today())
                    ->whereHas('activity', function($q) use ($centreId) {
                        $q->where('centre_id', $centreId);
                    })->count(),
                'pending_tasks' => $this->getAjkPendingTasks($centreId),
                'maintenance_alerts' => AssetMaintenance::whereHas('asset', function($q) use ($centreId) {
                        $q->where('centre_id', $centreId);
                    })->where('status', 'scheduled')
                    ->where('scheduled_date', '<=', Carbon::now()->addDays(7))
                    ->count()
            ],
            'schedule' => [
                'today' => $this->getTodaySchedule($centreId),
                'week' => $this->getWeekSchedule($centreId)
            ],
            'support' => [
                'facilities' => $this->getFacilityStatus($centreId),
                'equipment' => $this->getEquipmentStatus($centreId),
                'maintenance' => $this->getMaintenanceSchedule($centreId)
            ],
            'notifications' => $this->getAjkNotifications($centreId)
        ];
    }

    /**
     * Get optimized admin statistics
     */
    private function getAdminStats(): array
    {
        return Cache::remember('admin_stats', 60, function() {
            return [
                'total_users' => User::count(),
                'total_trainees' => Trainee::count(),
                'total_activities' => Activity::count(),
                'total_sessions' => ActivitySession::count(),
                'active_centres' => Centre::where('is_active', true)->count(),
                'total_letters' => Letter::count(),
                'total_assets' => Asset::count(),
                'pending_maintenance' => AssetMaintenance::where('status', 'scheduled')->count(),
                'user_growth_rate' => $this->calculateGrowthRate('users', 30),
                'trainee_growth_rate' => $this->calculateGrowthRate('trainees', 30),
                'system_utilization' => $this->calculateSystemUtilization()
            ];
        });
    }

    /**
     * Get supervisor statistics for specific centre
     */
    private function getSupervisorStats($centreId): array
    {
        return Cache::remember("supervisor_stats_{$centreId}", 60, function() use ($centreId) {
            return [
                'centre_trainees' => Trainee::where('centre_id', $centreId)->where('is_active', true)->count(),
                'centre_teachers' => User::where('centre_id', $centreId)->where('role', 'teacher')->where('is_active', true)->count(),
                'centre_activities' => Activity::where('centre_id', $centreId)->where('is_active', true)->count(),
                'today_sessions' => ActivitySession::whereDate('session_date', Carbon::today())
                    ->whereHas('activity', function($q) use ($centreId) {
                        $q->where('centre_id', $centreId);
                    })->count(),
                'week_sessions' => ActivitySession::whereBetween('session_date', [
                        Carbon::now()->startOfWeek(),
                        Carbon::now()->endOfWeek()
                    ])->whereHas('activity', function($q) use ($centreId) {
                        $q->where('centre_id', $centreId);
                    })->count(),
                'completion_rate' => $this->calculateCentreCompletionRate($centreId),
                'attendance_rate' => $this->calculateCentreAttendanceRate($centreId)
            ];
        });
    }

    /**
     * Get teacher statistics
     */
    private function getTeacherStats($userId, $today): array
    {
        return Cache::remember("teacher_stats_{$userId}", 60, function() use ($userId, $today) {
            $startOfWeek = $today->copy()->startOfWeek();
            $endOfWeek = $today->copy()->endOfWeek();
            
            return [
                'today_sessions' => ActivitySession::where('teacher_id', $userId)
                    ->whereDate('session_date', $today)
                    ->count(),
                'week_sessions' => ActivitySession::where('teacher_id', $userId)
                    ->whereBetween('session_date', [$startOfWeek, $endOfWeek])
                    ->count(),
                'total_trainees' => DB::table('activity_enrollments')
                    ->join('activity_sessions', 'activity_enrollments.activity_id', '=', 'activity_sessions.activity_id')
                    ->where('activity_sessions.teacher_id', $userId)
                    ->where('activity_enrollments.enrollment_status', 'enrolled')
                    ->distinct('activity_enrollments.trainee_id')
                    ->count('activity_enrollments.trainee_id'),
                'pending_attendance' => ActivitySession::where('teacher_id', $userId)
                    ->where('status', 'scheduled')
                    ->where('session_date', '<', $today)
                    ->count(),
                'completed_sessions' => ActivitySession::where('teacher_id', $userId)
                    ->where('status', 'completed')
                    ->whereMonth('session_date', $today->month)
                    ->count(),
                'average_attendance' => $this->calculateTeacherAverageAttendance($userId)
            ];
        });
    }

    /**
     * Get user growth data for charts
     */
    private function getUserGrowthData($months = 6): array
    {
        return Cache::remember("user_growth_{$months}", 300, function() use ($months) {
            $data = User::selectRaw('DATE(created_at) as date, COUNT(*) as count')
                ->where('created_at', '>=', Carbon::now()->subMonths($months))
                ->groupBy('date')
                ->orderBy('date')
                ->get();
            
            return [
                'labels' => $data->pluck('date')->map(function($date) {
                    return Carbon::parse($date)->format('M d');
                })->toArray(),
                'data' => $data->pluck('count')->toArray()
            ];
        });
    }

    /**
     * Get activity distribution by category
     */
    private function getActivityDistribution(): array
    {
        return Cache::remember('activity_distribution', 300, function() {
            $activities = Activity::all();
            $distribution = [];
            
            foreach ($activities as $activity) {
                $category = $activity->category;
                if (!isset($distribution[$category])) {
                    $distribution[$category] = 0;
                }
                $distribution[$category]++;
            }
            
            return $distribution;
        });
    }

    /**
     * Get centre performance metrics
     */
    private function getCentrePerformance(): array
    {
        return Cache::remember('centre_performance', 300, function() {
            return DB::table('centres')
                ->leftJoin('trainees', 'centres.id', '=', 'trainees.centre_id')
                ->leftJoin('users', function($join) {
                    $join->on('centres.id', '=', 'users.centre_id')
                         ->where('users.role', '=', 'teacher');
                })
                ->selectRaw('centres.centre_name, 
                           COUNT(DISTINCT trainees.id) as trainee_count,
                           COUNT(DISTINCT users.id) as teacher_count')
                ->where('centres.is_active', true)
                ->groupBy('centres.id', 'centres.centre_name')
                ->get()
                ->toArray();
        });
    }

    /**
     * Get monthly statistics
     */
    private function getMonthlyStatistics(): array
    {
        return Cache::remember('monthly_statistics', 600, function() {
            $currentMonth = Carbon::now()->startOfMonth();
            $lastMonth = Carbon::now()->subMonth()->startOfMonth();
            
            return [
                'current_month' => [
                    'users' => User::where('created_at', '>=', $currentMonth)->count(),
                    'trainees' => Trainee::where('created_at', '>=', $currentMonth)->count(),
                    'sessions' => ActivitySession::where('created_at', '>=', $currentMonth)->count(),
                    'letters' => Letter::where('created_at', '>=', $currentMonth)->count()
                ],
                'last_month' => [
                    'users' => User::whereBetween('created_at', [$lastMonth, $currentMonth])->count(),
                    'trainees' => Trainee::whereBetween('created_at', [$lastMonth, $currentMonth])->count(),
                    'sessions' => ActivitySession::whereBetween('created_at', [$lastMonth, $currentMonth])->count(),
                    'letters' => Letter::whereBetween('created_at', [$lastMonth, $currentMonth])->count()
                ]
            ];
        });
    }

    /**
     * Get system health metrics
     */
    private function getSystemHealth(): array
    {
        return Cache::remember('system_health', 60, function() {
            return [
                'database' => $this->checkDatabaseHealth(),
                'storage' => $this->checkStorageHealth(),
                'cache' => $this->checkCacheHealth(),
                'queue' => $this->checkQueueHealth(),
                'last_backup' => $this->getLastBackupTime(),
                'response_time' => $this->getAverageResponseTime()
            ];
        });
    }

    /**
     * Get system alerts
     */
    private function getSystemAlerts(): array
    {
        $alerts = [];

        // Check for overdue maintenance
        $overdueCount = AssetMaintenance::where('scheduled_date', '<', Carbon::now())
            ->where('status', 'scheduled')
            ->count();
        
        if ($overdueCount > 0) {
            $alerts[] = [
                'type' => 'warning',
                'message' => "{$overdueCount} maintenance task(s) are overdue",
                'action' => route('assets.maintenance')
            ];
        }

        // Check for low storage
        $storageHealth = $this->checkStorageHealth();
        if ($storageHealth === 'critical') {
            $alerts[] = [
                'type' => 'danger',
                'message' => 'Storage space is critically low (>90% used)',
                'action' => '#'
            ];
        }

        // Check for inactive teachers
        $inactiveTeachers = User::where('role', 'teacher')
            ->where('user_last_accessed_at', '<', Carbon::now()->subDays(7))
            ->count();
        
        if ($inactiveTeachers > 0) {
            $alerts[] = [
                'type' => 'info',
                'message' => "{$inactiveTeachers} teacher(s) haven't logged in for over a week",
                'action' => route('teachers.index')
            ];
        }

        return $alerts;
    }

    /**
     * Check database health
     */
    private function checkDatabaseHealth(): string
    {
        try {
            DB::select('SELECT 1');
            
            // Check query performance
            $start = microtime(true);
            User::count();
            $queryTime = (microtime(true) - $start) * 1000;
            
            if ($queryTime > 1000) {
                return 'slow';
            }
            
            return 'healthy';
        } catch (\Exception $e) {
            return 'unhealthy';
        }
    }

    /**
     * Check storage health
     */
    private function checkStorageHealth(): string
    {
        try {
            $freeSpace = disk_free_space(storage_path());
            $totalSpace = disk_total_space(storage_path());
            
            if (!$freeSpace || !$totalSpace) {
                return 'unknown';
            }
            
            $usedPercentage = (($totalSpace - $freeSpace) / $totalSpace) * 100;
            
            if ($usedPercentage > 90) {
                return 'critical';
            } elseif ($usedPercentage > 70) {
                return 'warning';
            }
            
            return 'healthy';
        } catch (\Exception $e) {
            return 'unknown';
        }
    }

    /**
     * Check cache health
     */
    private function checkCacheHealth(): string
    {
        try {
            $testKey = 'health_check_' . time();
            Cache::put($testKey, 'test', 10);
            $result = Cache::get($testKey);
            Cache::forget($testKey);
            
            return $result === 'test' ? 'healthy' : 'unhealthy';
        } catch (\Exception $e) {
            return 'unhealthy';
        }
    }

    /**
     * Check queue health
     */
    private function checkQueueHealth(): string
    {
        try {
            // This is a basic check - in production you'd check queue size, failed jobs, etc.
            return 'healthy';
        } catch (\Exception $e) {
            return 'unhealthy';
        }
    }

    /**
     * Calculate growth rate for a given model
     */
    private function calculateGrowthRate($model, $days): float
    {
        $modelClass = match($model) {
            'users' => User::class,
            'trainees' => Trainee::class,
            default => User::class
        };

        $current = $modelClass::where('created_at', '>=', Carbon::now()->subDays($days))->count();
        $previous = $modelClass::whereBetween('created_at', [
            Carbon::now()->subDays($days * 2),
            Carbon::now()->subDays($days)
        ])->count();

        if ($previous === 0) {
            return $current > 0 ? 100 : 0;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }

    /**
     * Calculate system utilization
     */
    private function calculateSystemUtilization(): float
    {
        $totalCapacity = ActivitySession::whereDate('session_date', Carbon::today())->sum('max_participants');
        $totalEnrolled = DB::table('activity_enrollments')
            ->join('activity_sessions', 'activity_enrollments.activity_id', '=', 'activity_sessions.activity_id')
            ->whereDate('activity_sessions.session_date', Carbon::today())
            ->where('activity_enrollments.enrollment_status', 'enrolled')
            ->count();

        if ($totalCapacity === 0) {
            return 0;
        }

        return round(($totalEnrolled / $totalCapacity) * 100, 1);
    }

    /**
     * Get recent data with optimized queries
     */
    private function getRecentUsers($limit): \Illuminate\Database\Eloquent\Collection
    {
        return User::select(['id', 'name', 'email', 'role', 'created_at'])
            ->latest()
            ->limit($limit)
            ->get();
    }

    private function getRecentTrainees($limit): \Illuminate\Database\Eloquent\Collection
    {
        return Trainee::select(['id', 'trainee_first_name', 'trainee_last_name', 'trainee_condition', 'trainee_date_of_birth', 'created_at'])
            ->latest()
            ->limit($limit)
            ->get();
    }

    private function getRecentActivities($limit): \Illuminate\Database\Eloquent\Collection
    {
        return Activity::with(['sessions' => function($query) {
                $query->latest()->limit(1);
            }])
            ->select(['id', 'activity_name', 'activity_type', 'activity_description', 'created_at'])
            ->latest()
            ->limit($limit)
            ->get();
    }

    private function getRecentLetters($limit): \Illuminate\Database\Eloquent\Collection
    {
        return Letter::select(['id', 'letter_reference', 'letter_type', 'recipient_id', 'recipient_type', 'created_at'])
            ->latest()
            ->limit($limit)
            ->get();
    }

    /**
     * Get empty dashboard for error cases
     */
    private function getEmptyDashboard(): array
    {
        return [
            'stats' => [],
            'charts' => [],
            'recent' => [],
            'alerts' => [
                [
                    'type' => 'danger',
                    'message' => 'Unable to load dashboard data. Please refresh the page.',
                    'action' => '#'
                ]
            ]
        ];
    }

    /**
     * Clear dashboard cache for user
     */
    public function clearUserCache($userId, $role, $centreId): void
    {
        $cacheKey = "dashboard_{$role}_{$userId}_{$centreId}";
        Cache::forget($cacheKey);
    }

    /**
     * Clear all dashboard caches
     */
    public function clearAllCaches(): void
    {
        $keys = [
            'admin_stats',
            'user_growth_*',
            'activity_distribution',
            'centre_performance',
            'monthly_statistics',
            'system_health'
        ];

        foreach ($keys as $pattern) {
            if (str_contains($pattern, '*')) {
                // For wildcard patterns, you'd implement cache tag clearing
                // For now, clear specific known keys
                for ($i = 1; $i <= 12; $i++) {
                    Cache::forget(str_replace('*', $i, $pattern));
                }
            } else {
                Cache::forget($pattern);
            }
        }
    }

    // Additional helper methods would be implemented here...
    // For brevity, I'm showing the main structure
    
    private function getLastBackupTime(): ?string
    {
        // Implementation depends on backup strategy
        return null;
    }
    
    private function getAverageResponseTime(): float
    {
        // Implementation would track response times
        return 0.0;
    }
    
    // Placeholder methods for other dashboard types
    private function getTeacherSchedule($userId, $date) { return []; }
    private function getTeacherWeekSchedule($userId) { return []; }
    private function calculateTeacherAttendanceRate($userId) { return 0; }
    private function calculateTeacherCompletionRate($userId) { return 0; }
    private function getTeacherTraineeProgress($userId) { return []; }
    private function getTeacherMonthlySummary($userId) { return []; }
    private function getTeacherUpcomingSessions($userId, $limit) { return []; }
    private function getTeacherActiveTrainees($userId) { return []; }
    private function getTeacherRecentEnrollments($userId, $limit) { return []; }
    private function getTraineeProgressAlerts($userId) { return []; }
    private function getTeacherQuickActions($userId) { return []; }
    private function calculateTeacherAverageAttendance($userId) { return 0; }
    private function getDefaultDashboard($centreId) { return $this->getEmptyDashboard(); }
    private function getTodaySchedule($centreId) 
    { 
        return ActivitySession::with(['activity', 'teacher', 'enrollments.trainee'])
            ->whereDate('session_date', Carbon::today())
            ->whereHas('activity', function($q) use ($centreId) {
                $q->where('centre_id', $centreId);
            })
            ->orderBy('start_time')
            ->get();
    }
    private function getWeekSchedule($centreId) { return []; }
    private function getUpcomingActivities($centreId, $limit) { return []; }
    private function getCentreTeachers($centreId) { return []; }
    private function getCentreTrainees($centreId, $limit) { return []; }
    private function getTeamPerformance($centreId) { return []; }
    private function getCentreAlerts($centreId) { return []; }
    private function getTraineeProgressData($centreId) { return []; }
    private function getActivityCompletionData($centreId) { return []; }
    private function getStaffPerformanceData($centreId) { return []; }
    private function getMonthlyOverview($centreId) { return []; }
    private function calculateCentreCompletionRate($centreId) { return 0; }
    private function calculateCentreAttendanceRate($centreId) { return 0; }
    private function getAjkPendingTasks($centreId) { return 0; }
    private function getFacilityStatus($centreId) { return []; }
    private function getEquipmentStatus($centreId) { return []; }
    private function getMaintenanceSchedule($centreId) { return []; }
    private function getAjkNotifications($centreId) { return []; }
    private function getSystemMetrics() { return []; }
}