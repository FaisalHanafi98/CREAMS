<?php

namespace App\Services\Dashboard;

use App\Models\Users;
use App\Models\Trainee;
use App\Models\Activity;
use App\Models\ActivitySession;
use App\Models\SessionEnrollment;
use App\Models\Centres;
use App\Models\Asset;
use App\Models\Event;
use App\Models\ContactMessages;
use App\Models\Volunteers;
use App\Models\ActivityAttendance;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use Exception;

abstract class BaseDashboardService
{
    protected int $cacheTimeout = 300; // 5 minutes

    /**
     * Get dashboard data for a specific role
     */
    abstract public function getDashboardData(int $userId): array;

    /**
     * Get basic statistics shared across roles
     */
    protected function getBasicStats(): array
    {
        return Cache::remember('dashboard_basic_stats', $this->cacheTimeout, function () {
            try {
                return [
                    'total_users' => Users::count(),
                    'total_trainees' => Trainee::count(),
                    'total_activities' => Activity::count(),
                    'total_centres' => Centres::count(),
                    'active_sessions' => ActivitySession::where('status', 'active')->count(),
                    'total_assets' => Asset::count(),
                ];
            } catch (Exception $e) {
                Log::error('Error getting basic stats', ['error' => $e->getMessage()]);
                return [
                    'total_users' => 0,
                    'total_trainees' => 0,
                    'total_activities' => 0,
                    'total_centres' => 0,
                    'active_sessions' => 0,
                    'total_assets' => 0,
                ];
            }
        });
    }

    /**
     * Get system health status
     */
    protected function getSystemHealth(): array
    {
        return Cache::remember('dashboard_system_health', $this->cacheTimeout, function () {
            try {
                $dbStatus = 'healthy';
                $cacheStatus = 'healthy';
                $storageStatus = 'healthy';

                // Test database connection
                try {
                    DB::connection()->getPdo();
                } catch (Exception $e) {
                    $dbStatus = 'unhealthy';
                    Log::error('Database health check failed', ['error' => $e->getMessage()]);
                }

                // Test cache (already using it, so if we get here it's working)
                try {
                    Cache::put('health_check', 'test', 1);
                    Cache::forget('health_check');
                } catch (Exception $e) {
                    $cacheStatus = 'unhealthy';
                    Log::error('Cache health check failed', ['error' => $e->getMessage()]);
                }

                // Test storage (check if storage directory is writable)
                try {
                    $storageStatus = is_writable(storage_path()) ? 'healthy' : 'unhealthy';
                } catch (Exception $e) {
                    $storageStatus = 'unhealthy';
                    Log::error('Storage health check failed', ['error' => $e->getMessage()]);
                }

                return [
                    'overall' => ($dbStatus === 'healthy' && $cacheStatus === 'healthy' && $storageStatus === 'healthy') ? 'healthy' : 'degraded',
                    'database' => $dbStatus,
                    'cache' => $cacheStatus,
                    'storage' => $storageStatus,
                    'last_check' => now()->toISOString(),
                ];
            } catch (Exception $e) {
                Log::error('System health check failed', ['error' => $e->getMessage()]);
                return [
                    'overall' => 'unknown',
                    'database' => 'unknown',
                    'cache' => 'unknown',
                    'storage' => 'unknown',
                    'last_check' => now()->toISOString(),
                ];
            }
        });
    }

    /**
     * Get recent activities for dashboard
     */
    protected function getRecentActivities(int $limit = 5): array
    {
        return Cache::remember("dashboard_recent_activities_{$limit}", $this->cacheTimeout, function () use ($limit) {
            try {
                return Activity::with(['centre'])
                    ->latest()
                    ->limit($limit)
                    ->get()
                    ->map(function ($activity) {
                        return [
                            'id' => $activity->id,
                            'name' => $activity->name,
                            'centre' => $activity->centre->name ?? 'Unknown Centre',
                            'created_at' => $activity->created_at,
                            'status' => $activity->status ?? 'active',
                        ];
                    })
                    ->toArray();
            } catch (Exception $e) {
                Log::error('Error getting recent activities', ['error' => $e->getMessage()]);
                return [];
            }
        });
    }

    /**
     * Get upcoming events
     */
    protected function getUpcomingEvents(int $limit = 5): array
    {
        return Cache::remember("dashboard_upcoming_events_{$limit}", $this->cacheTimeout, function () use ($limit) {
            try {
                return Event::where('event_date', '>=', now())
                    ->orderBy('event_date')
                    ->limit($limit)
                    ->get()
                    ->map(function ($event) {
                        return [
                            'id' => $event->id,
                            'title' => $event->title,
                            'event_date' => $event->event_date,
                            'location' => $event->location ?? 'TBD',
                            'status' => $event->status ?? 'scheduled',
                        ];
                    })
                    ->toArray();
            } catch (Exception $e) {
                Log::error('Error getting upcoming events', ['error' => $e->getMessage()]);
                return [];
            }
        });
    }

    /**
     * Get notifications for a user
     */
    protected function getNotifications(int $userId, string $role): array
    {
        return Cache::remember("dashboard_notifications_{$userId}_{$role}", $this->cacheTimeout, function () use ($userId, $role) {
            try {
                $notifications = [];

                // Add role-specific notifications
                switch ($role) {
                    case 'admin':
                        // Check for new contact messages
                        $newMessages = ContactMessages::where('status', 'unread')->count();
                        if ($newMessages > 0) {
                            $notifications[] = [
                                'type' => 'info',
                                'message' => "You have {$newMessages} unread contact messages",
                                'action' => '/admin/messages',
                                'created_at' => now(),
                            ];
                        }

                        // Check for new volunteer applications
                        $newVolunteers = Volunteers::where('status', 'pending')->count();
                        if ($newVolunteers > 0) {
                            $notifications[] = [
                                'type' => 'warning',
                                'message' => "{$newVolunteers} volunteer applications awaiting approval",
                                'action' => '/admin/volunteers',
                                'created_at' => now(),
                            ];
                        }
                        break;

                    case 'teacher':
                        // Check for today's sessions
                        $todaySessions = ActivitySession::whereDate('session_date', today())->count();
                        if ($todaySessions > 0) {
                            $notifications[] = [
                                'type' => 'info',
                                'message' => "You have {$todaySessions} sessions scheduled for today",
                                'action' => '/teacher/sessions',
                                'created_at' => now(),
                            ];
                        }
                        break;
                }

                return $notifications;
            } catch (Exception $e) {
                Log::error('Error getting notifications', ['error' => $e->getMessage()]);
                return [];
            }
        });
    }

    /**
     * Get chart data for dashboard
     */
    protected function getChartData(string $type, array $filters = []): array
    {
        $cacheKey = "dashboard_chart_{$type}_" . md5(serialize($filters));
        
        return Cache::remember($cacheKey, $this->cacheTimeout, function () use ($type, $filters) {
            try {
                switch ($type) {
                    case 'user_registration':
                        return $this->getUserRegistrationChartData($filters);
                    case 'activity_participation':
                        return $this->getActivityParticipationChartData($filters);
                    case 'attendance_trends':
                        return $this->getAttendanceTrendsChartData($filters);
                    default:
                        return [];
                }
            } catch (Exception $e) {
                Log::error("Error getting chart data for type: {$type}", ['error' => $e->getMessage()]);
                return [];
            }
        });
    }

    /**
     * Get user registration chart data
     */
    private function getUserRegistrationChartData(array $filters): array
    {
        $days = $filters['days'] ?? 30;
        $startDate = Carbon::now()->subDays($days);

        $data = Users::where('created_at', '>=', $startDate)
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return [
            'labels' => $data->pluck('date')->map(fn($date) => Carbon::parse($date)->format('M d'))->toArray(),
            'datasets' => [[
                'label' => 'New Users',
                'data' => $data->pluck('count')->toArray(),
                'borderColor' => 'rgb(75, 192, 192)',
                'backgroundColor' => 'rgba(75, 192, 192, 0.2)',
            ]]
        ];
    }

    /**
     * Get activity participation chart data
     */
    private function getActivityParticipationChartData(array $filters): array
    {
        $data = Activity::withCount('sessions')
            ->orderBy('sessions_count', 'desc')
            ->limit($filters['limit'] ?? 10)
            ->get();

        return [
            'labels' => $data->pluck('name')->toArray(),
            'datasets' => [[
                'label' => 'Sessions',
                'data' => $data->pluck('sessions_count')->toArray(),
                'backgroundColor' => [
                    'rgba(255, 99, 132, 0.8)',
                    'rgba(54, 162, 235, 0.8)',
                    'rgba(255, 205, 86, 0.8)',
                    'rgba(75, 192, 192, 0.8)',
                    'rgba(153, 102, 255, 0.8)',
                ],
            ]]
        ];
    }

    /**
     * Get attendance trends chart data
     */
    private function getAttendanceTrendsChartData(array $filters): array
    {
        $days = $filters['days'] ?? 30;
        $startDate = Carbon::now()->subDays($days);

        $data = ActivityAttendance::where('created_at', '>=', $startDate)
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(CASE WHEN status = "present" THEN 1 ELSE 0 END) as present'),
                DB::raw('SUM(CASE WHEN status = "absent" THEN 1 ELSE 0 END) as absent')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return [
            'labels' => $data->pluck('date')->map(fn($date) => Carbon::parse($date)->format('M d'))->toArray(),
            'datasets' => [
                [
                    'label' => 'Present',
                    'data' => $data->pluck('present')->toArray(),
                    'borderColor' => 'rgb(75, 192, 192)',
                    'backgroundColor' => 'rgba(75, 192, 192, 0.2)',
                ],
                [
                    'label' => 'Absent',
                    'data' => $data->pluck('absent')->toArray(),
                    'borderColor' => 'rgb(255, 99, 132)',
                    'backgroundColor' => 'rgba(255, 99, 132, 0.2)',
                ]
            ]
        ];
    }

    /**
     * Clear cache for specific user/role
     */
    public function clearUserCache(int $userId, string $role): void
    {
        $patterns = [
            "dashboard_notifications_{$userId}_{$role}",
            "dashboard_{$role}_{$userId}",
            "dashboard_stats_{$role}_{$userId}",
            "dashboard_charts_{$role}_{$userId}",
        ];

        foreach ($patterns as $pattern) {
            Cache::forget($pattern);
        }
    }

    /**
     * Clear all dashboard cache
     */
    public function clearAllCache(): void
    {
        $patterns = [
            'dashboard_basic_stats',
            'dashboard_system_health',
            'dashboard_recent_activities_*',
            'dashboard_upcoming_events_*',
            'dashboard_chart_*',
        ];

        foreach ($patterns as $pattern) {
            Cache::forget($pattern);
        }
    }
}