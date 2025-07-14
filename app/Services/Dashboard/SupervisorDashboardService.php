<?php

namespace App\Services\Dashboard;

use App\Models\Users;
use App\Models\Activity;
use App\Models\ActivitySession;
use App\Models\Centres;
use App\Models\Trainee;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use Exception;

class SupervisorDashboardService extends BaseDashboardService
{
    /**
     * Get dashboard data for supervisor users
     */
    public function getDashboardData(int $supervisorId): array
    {
        return Cache::remember("dashboard_supervisor_{$supervisorId}", $this->cacheTimeout, function () use ($supervisorId) {
            try {
                $supervisor = Users::find($supervisorId);
                if (!$supervisor) {
                    throw new Exception("Supervisor not found: {$supervisorId}");
                }

                return [
                    'stats' => $this->getSupervisorStats($supervisorId, $supervisor),
                    'charts' => $this->getSupervisorCharts($supervisorId),
                    'notifications' => $this->getNotifications($supervisorId, 'supervisor'),
                    'teacherPerformance' => $this->getTeacherPerformance($supervisorId),
                    'centreOverview' => $this->getCentreOverview($supervisorId),
                    'pendingApprovals' => $this->getPendingApprovals($supervisorId),
                    'recentActivities' => $this->getRecentActivities(10),
                    'quickActions' => $this->getSupervisorQuickActions($supervisorId),
                    'activityMetrics' => $this->getActivityMetrics($supervisorId),
                    'monthlyReport' => $this->getMonthlyReport($supervisorId),
                ];
            } catch (Exception $e) {
                Log::error('Error getting supervisor dashboard data', [
                    'supervisor_id' => $supervisorId,
                    'error' => $e->getMessage()
                ]);
                
                return $this->getFallbackSupervisorData($supervisorId);
            }
        });
    }

    /**
     * Get supervisor-specific statistics
     */
    private function getSupervisorStats(int $supervisorId, $supervisor): array
    {
        try {
            $centreId = $supervisor->centre_id;
            
            return [
                'total_teachers' => Users::where('role', 'teacher')
                    ->where('centre_id', $centreId)
                    ->count(),
                'total_activities' => Activity::where('centre_id', $centreId)->count(),
                'active_sessions' => ActivitySession::whereHas('activity', function ($query) use ($centreId) {
                    $query->where('centre_id', $centreId);
                })->where('status', 'active')->count(),
                'centre_trainees' => Trainee::where('centre_id', $centreId)->count(),
                'completed_sessions_this_month' => ActivitySession::whereHas('activity', function ($query) use ($centreId) {
                    $query->where('centre_id', $centreId);
                })->where('status', 'completed')
                  ->whereMonth('session_date', Carbon::now()->month)
                  ->count(),
                'pending_approvals' => $this->getPendingApprovalsCount($supervisorId),
                'centre_name' => $supervisor->centre->name ?? 'Unknown Centre',
                'centre_capacity' => $supervisor->centre->capacity ?? 0,
                'capacity_utilization' => $this->calculateCapacityUtilization($supervisor->centre),
            ];
        } catch (Exception $e) {
            Log::error('Error getting supervisor stats', ['supervisor_id' => $supervisorId, 'error' => $e->getMessage()]);
            return [
                'total_teachers' => 0,
                'total_activities' => 0,
                'active_sessions' => 0,
                'centre_trainees' => 0,
                'completed_sessions_this_month' => 0,
                'pending_approvals' => 0,
                'centre_name' => 'Unknown',
                'centre_capacity' => 0,
                'capacity_utilization' => 0,
            ];
        }
    }

    /**
     * Get supervisor-specific charts
     */
    private function getSupervisorCharts(int $supervisorId): array
    {
        try {
            return [
                'teacher_performance' => $this->getTeacherPerformanceChart($supervisorId),
                'activity_completion' => $this->getActivityCompletionChart($supervisorId),
                'monthly_sessions' => $this->getMonthlySessionsChart($supervisorId),
                'centre_utilization' => $this->getCentreUtilizationChart($supervisorId),
            ];
        } catch (Exception $e) {
            Log::error('Error getting supervisor charts', ['supervisor_id' => $supervisorId, 'error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Get teacher performance data
     */
    private function getTeacherPerformance(int $supervisorId): array
    {
        try {
            $supervisor = Users::find($supervisorId);
            $centreId = $supervisor->centre_id;
            
            return Users::where('role', 'teacher')
                ->where('centre_id', $centreId)
                ->with(['activities'])
                ->get()
                ->map(function ($teacher) {
                    return [
                        'id' => $teacher->id,
                        'name' => $teacher->full_name,
                        'activities_count' => $teacher->activities->count(),
                        'sessions_this_month' => $this->getTeacherSessionsThisMonth($teacher->id),
                        'attendance_rate' => $this->getTeacherAttendanceRate($teacher->id),
                        'performance_score' => $this->calculateTeacherPerformanceScore($teacher->id),
                        'last_activity' => $this->getLastActivityDate($teacher->id),
                    ];
                })
                ->toArray();
        } catch (Exception $e) {
            Log::error('Error getting teacher performance', ['supervisor_id' => $supervisorId, 'error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Get centre overview data
     */
    private function getCentreOverview(int $supervisorId): array
    {
        try {
            $supervisor = Users::find($supervisorId);
            $centre = $supervisor->centre;
            
            if (!$centre) {
                return [];
            }

            return [
                'centre_info' => [
                    'id' => $centre->id,
                    'name' => $centre->name,
                    'location' => $centre->location,
                    'capacity' => $centre->capacity,
                    'status' => $centre->status,
                    'established_date' => $centre->created_at,
                ],
                'resource_utilization' => [
                    'trainee_capacity' => $this->calculateCapacityUtilization($centre),
                    'teacher_workload' => $this->calculateTeacherWorkload($centre->id),
                    'activity_frequency' => $this->calculateActivityFrequency($centre->id),
                ],
                'recent_metrics' => [
                    'new_trainees_this_month' => Trainee::where('centre_id', $centre->id)
                        ->whereMonth('created_at', Carbon::now()->month)
                        ->count(),
                    'completed_sessions_this_week' => ActivitySession::whereHas('activity', function ($query) use ($centre) {
                        $query->where('centre_id', $centre->id);
                    })->where('status', 'completed')
                      ->whereBetween('session_date', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])
                      ->count(),
                ],
            ];
        } catch (Exception $e) {
            Log::error('Error getting centre overview', ['supervisor_id' => $supervisorId, 'error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Get pending approvals for supervisor
     */
    private function getPendingApprovals(int $supervisorId): array
    {
        try {
            $supervisor = Users::find($supervisorId);
            $centreId = $supervisor->centre_id;
            
            return [
                'teacher_requests' => [], // Placeholder for teacher approval system
                'activity_modifications' => [], // Placeholder for activity modification approvals
                'schedule_changes' => [], // Placeholder for schedule change approvals
                'trainee_transfers' => [], // Placeholder for trainee transfer requests
            ];
        } catch (Exception $e) {
            Log::error('Error getting pending approvals', ['supervisor_id' => $supervisorId, 'error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Get supervisor quick actions
     */
    private function getSupervisorQuickActions(int $supervisorId): array
    {
        try {
            $pendingCount = $this->getPendingApprovalsCount($supervisorId);
            
            return [
                [
                    'title' => 'Approve Requests',
                    'icon' => 'fas fa-check-double',
                    'url' => '/supervisor/approvals',
                    'color' => 'warning',
                    'badge' => $pendingCount > 0 ? $pendingCount : null,
                ],
                [
                    'title' => 'Manage Teachers',
                    'icon' => 'fas fa-users',
                    'url' => '/supervisor/teachers',
                    'color' => 'primary',
                ],
                [
                    'title' => 'Activity Overview',
                    'icon' => 'fas fa-tasks',
                    'url' => '/supervisor/activities',
                    'color' => 'info',
                ],
                [
                    'title' => 'Centre Reports',
                    'icon' => 'fas fa-chart-bar',
                    'url' => '/supervisor/reports',
                    'color' => 'success',
                ],
                [
                    'title' => 'Schedule Management',
                    'icon' => 'fas fa-calendar-alt',
                    'url' => '/supervisor/schedule',
                    'color' => 'secondary',
                ],
                [
                    'title' => 'Performance Review',
                    'icon' => 'fas fa-star',
                    'url' => '/supervisor/performance',
                    'color' => 'danger',
                ],
            ];
        } catch (Exception $e) {
            Log::error('Error getting supervisor quick actions', ['supervisor_id' => $supervisorId, 'error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Get activity metrics for supervisor
     */
    private function getActivityMetrics(int $supervisorId): array
    {
        try {
            $supervisor = Users::find($supervisorId);
            $centreId = $supervisor->centre_id;
            
            return Activity::where('centre_id', $centreId)
                ->with(['sessions', 'teacher'])
                ->get()
                ->map(function ($activity) {
                    return [
                        'id' => $activity->id,
                        'name' => $activity->name,
                        'teacher_name' => $activity->teacher->full_name ?? 'Unassigned',
                        'total_sessions' => $activity->sessions->count(),
                        'completed_sessions' => $activity->sessions->where('status', 'completed')->count(),
                        'success_rate' => $this->calculateActivitySuccessRate($activity->id),
                        'average_attendance' => $this->calculateActivityAttendanceRate($activity->id),
                        'last_session' => $activity->sessions->sortByDesc('session_date')->first()?->session_date,
                    ];
                })
                ->toArray();
        } catch (Exception $e) {
            Log::error('Error getting activity metrics', ['supervisor_id' => $supervisorId, 'error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Get monthly report for supervisor
     */
    private function getMonthlyReport(int $supervisorId): array
    {
        try {
            $supervisor = Users::find($supervisorId);
            $centreId = $supervisor->centre_id;
            $currentMonth = Carbon::now()->startOfMonth();
            
            return [
                'period' => $currentMonth->format('F Y'),
                'sessions_conducted' => ActivitySession::whereHas('activity', function ($query) use ($centreId) {
                    $query->where('centre_id', $centreId);
                })->where('status', 'completed')
                  ->where('session_date', '>=', $currentMonth)
                  ->count(),
                'total_attendance' => $this->getMonthlyAttendanceCount($centreId, $currentMonth),
                'new_trainees' => Trainee::where('centre_id', $centreId)
                    ->where('created_at', '>=', $currentMonth)
                    ->count(),
                'teacher_performance_summary' => $this->getTeacherPerformanceSummary($centreId, $currentMonth),
                'goals_achievement' => $this->calculateGoalsAchievement($centreId, $currentMonth),
            ];
        } catch (Exception $e) {
            Log::error('Error getting monthly report', ['supervisor_id' => $supervisorId, 'error' => $e->getMessage()]);
            return [];
        }
    }

    // Helper methods for charts
    private function getTeacherPerformanceChart(int $supervisorId): array
    {
        // Implementation for teacher performance chart
        return [];
    }

    private function getActivityCompletionChart(int $supervisorId): array
    {
        // Implementation for activity completion chart
        return [];
    }

    private function getMonthlySessionsChart(int $supervisorId): array
    {
        // Implementation for monthly sessions chart
        return [];
    }

    private function getCentreUtilizationChart(int $supervisorId): array
    {
        // Implementation for centre utilization chart
        return [];
    }

    // Helper calculation methods
    private function calculateCapacityUtilization($centre): float
    {
        try {
            if (!$centre || !$centre->capacity) {
                return 0;
            }
            
            $currentTrainees = Trainee::where('centre_id', $centre->id)->count();
            return ($currentTrainees / $centre->capacity) * 100;
        } catch (Exception $e) {
            return 0;
        }
    }

    private function calculateTeacherWorkload(int $centreId): float
    {
        try {
            $teachers = Users::where('role', 'teacher')->where('centre_id', $centreId)->count();
            $activities = Activity::where('centre_id', $centreId)->count();
            
            return $teachers > 0 ? $activities / $teachers : 0;
        } catch (Exception $e) {
            return 0;
        }
    }

    private function calculateActivityFrequency(int $centreId): float
    {
        try {
            $totalSessions = ActivitySession::whereHas('activity', function ($query) use ($centreId) {
                $query->where('centre_id', $centreId);
            })->whereMonth('session_date', Carbon::now()->month)->count();
            
            $totalActivities = Activity::where('centre_id', $centreId)->count();
            
            return $totalActivities > 0 ? $totalSessions / $totalActivities : 0;
        } catch (Exception $e) {
            return 0;
        }
    }

    private function getPendingApprovalsCount(int $supervisorId): int
    {
        // Placeholder - implement based on actual approval system
        return 0;
    }

    private function getTeacherSessionsThisMonth(int $teacherId): int
    {
        try {
            $teacherActivities = Activity::where('teacher_id', $teacherId)->pluck('id');
            return ActivitySession::whereIn('activity_id', $teacherActivities)
                ->whereMonth('session_date', Carbon::now()->month)
                ->count();
        } catch (Exception $e) {
            return 0;
        }
    }

    private function getTeacherAttendanceRate(int $teacherId): float
    {
        // Implementation for teacher's overall attendance rate
        return 0;
    }

    private function calculateTeacherPerformanceScore(int $teacherId): float
    {
        // Implementation for performance scoring algorithm
        return 0;
    }

    private function getLastActivityDate(int $teacherId): ?string
    {
        try {
            $teacherActivities = Activity::where('teacher_id', $teacherId)->pluck('id');
            $lastSession = ActivitySession::whereIn('activity_id', $teacherActivities)
                ->orderBy('session_date', 'desc')
                ->first();
            
            return $lastSession ? $lastSession->session_date : null;
        } catch (Exception $e) {
            return null;
        }
    }

    private function calculateActivitySuccessRate(int $activityId): float
    {
        // Implementation for activity success rate calculation
        return 0;
    }

    private function calculateActivityAttendanceRate(int $activityId): float
    {
        // Implementation for activity attendance rate calculation
        return 0;
    }

    private function getMonthlyAttendanceCount(int $centreId, Carbon $month): int
    {
        // Implementation for monthly attendance count
        return 0;
    }

    private function getTeacherPerformanceSummary(int $centreId, Carbon $month): array
    {
        // Implementation for teacher performance summary
        return [];
    }

    private function calculateGoalsAchievement(int $centreId, Carbon $month): array
    {
        // Implementation for goals achievement calculation
        return [];
    }

    /**
     * Get fallback data when main query fails
     */
    private function getFallbackSupervisorData(int $supervisorId): array
    {
        return [
            'stats' => [
                'total_teachers' => 0,
                'total_activities' => 0,
                'active_sessions' => 0,
                'centre_trainees' => 0,
                'completed_sessions_this_month' => 0,
                'pending_approvals' => 0,
                'centre_name' => 'Unknown',
                'centre_capacity' => 0,
                'capacity_utilization' => 0,
            ],
            'charts' => [],
            'notifications' => [],
            'teacherPerformance' => [],
            'centreOverview' => [],
            'pendingApprovals' => [],
            'recentActivities' => [],
            'quickActions' => $this->getSupervisorQuickActions($supervisorId),
            'activityMetrics' => [],
            'monthlyReport' => [],
        ];
    }
}