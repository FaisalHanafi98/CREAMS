<?php

namespace App\Services\Dashboard;

use App\Models\Users;
use App\Models\Trainee;
use App\Models\Activity;
use App\Models\ActivitySession;
use App\Models\Centres;
use App\Models\Asset;
use App\Models\Events;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Route;
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
     * Get basic statistics - SAFE VERSION with only existing columns
     */
    protected function getBasicStats(): array
    {
        return Cache::remember('dashboard_basic_stats_safe', $this->cacheTimeout, function () {
            $stats = [
                'total_users' => 0,
                'total_trainees' => 0,
                'total_activities' => 0,
                'total_centres' => 0,
                'total_assets' => 0,
                'active_sessions' => 0,
            ];

            try {
                // Safe user count
                if (Schema::hasTable('users')) {
                    $stats['total_users'] = DB::table('users')->count();
                }
            } catch (Exception $e) {
                Log::error('Error counting users', ['error' => $e->getMessage()]);
            }

            try {
                // Safe trainee count
                if (Schema::hasTable('trainees')) {
                    $stats['total_trainees'] = DB::table('trainees')->count();
                }
            } catch (Exception $e) {
                Log::error('Error counting trainees', ['error' => $e->getMessage()]);
            }

            try {
                // Safe activity count
                if (Schema::hasTable('activities')) {
                    $stats['total_activities'] = DB::table('activities')->count();
                }
            } catch (Exception $e) {
                Log::error('Error counting activities', ['error' => $e->getMessage()]);
            }

            try {
                // Safe centre count
                if (Schema::hasTable('centres')) {
                    $stats['total_centres'] = DB::table('centres')->count();
                }
            } catch (Exception $e) {
                Log::error('Error counting centres', ['error' => $e->getMessage()]);
            }

            try {
                // Safe asset count (NO VALUE CALCULATION)
                if (Schema::hasTable('assets')) {
                    $stats['total_assets'] = DB::table('assets')->count();
                }
            } catch (Exception $e) {
                Log::error('Error counting assets', ['error' => $e->getMessage()]);
            }

            try {
                // Safe session count - only if table exists
                if (Schema::hasTable('activity_sessions')) {
                    $stats['active_sessions'] = DB::table('activity_sessions')->count();
                }
            } catch (Exception $e) {
                Log::error('Error counting sessions', ['error' => $e->getMessage()]);
            }

            Log::info('Dashboard stats generated safely', $stats);
            return $stats;
        });
    }

    /**
     * Get system health status - SAFE VERSION
     */
    public function getSystemHealth(): array
    {
        return Cache::remember('dashboard_system_health_safe', $this->cacheTimeout, function () {
            try {
                $dbStatus = 'healthy';
                $cacheStatus = 'healthy';
                $storageStatus = 'healthy';

                // Test database connection
                try {
                    DB::connection()->getPdo();
                    DB::table('users')->limit(1)->count(); // Test actual query
                } catch (Exception $e) {
                    $dbStatus = 'unhealthy';
                    Log::error('Database health check failed', ['error' => $e->getMessage()]);
                }

                // Test cache
                try {
                    Cache::put('health_check', 'test', 1);
                    Cache::forget('health_check');
                } catch (Exception $e) {
                    $cacheStatus = 'unhealthy';
                    Log::error('Cache health check failed', ['error' => $e->getMessage()]);
                }

                // Test storage
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
     * Get recent activities - SAFE VERSION
     */
    protected function getRecentActivities(int $limit = 5): array
    {
        return Cache::remember("dashboard_recent_activities_safe_{$limit}", $this->cacheTimeout, function () use ($limit) {
            try {
                if (!Schema::hasTable('activities')) {
                    return [];
                }

                $activities = DB::table('activities')
                    ->orderBy('created_at', 'desc')
                    ->limit($limit)
                    ->get();

                return $activities->map(function ($activity) {
                    return [
                        'id' => $activity->id ?? 0,
                        'name' => $activity->activity_name ?? 'Unknown Activity',
                        'centre' => $activity->centre_id ?? 'Unknown Centre',
                        'created_at' => $activity->created_at ?? now(),
                        'status' => 'active',
                    ];
                })->toArray();

            } catch (Exception $e) {
                Log::error('Error getting recent activities', ['error' => $e->getMessage()]);
                return [];
            }
        });
    }

    /**
     * Get upcoming events - SAFE VERSION
     */
    protected function getUpcomingEvents(int $limit = 5): array
    {
        return Cache::remember("dashboard_upcoming_events_safe_{$limit}", $this->cacheTimeout, function () use ($limit) {
            try {
                if (!Schema::hasTable('events') || !Schema::hasColumn('events', 'date')) {
                    return [];
                }

                $events = DB::table('events')
                    ->where('date', '>=', now()->toDateString())
                    ->orderBy('date')
                    ->limit($limit)
                    ->get();

                return $events->map(function ($event) {
                    return [
                        'id' => $event->id ?? 0,
                        'title' => $event->title ?? 'Unknown Event',
                        'event_date' => $event->date ?? now(),
                        'location' => $event->location ?? 'TBD',
                        'status' => $event->status ?? 'scheduled',
                    ];
                })->toArray();

            } catch (Exception $e) {
                Log::error('Error getting upcoming events', ['error' => $e->getMessage()]);
                return [];
            }
        });
    }

    /**
     * Get notifications - SAFE VERSION
     */
    protected function getNotifications(int $userId, string $role): array
    {
        return Cache::remember("dashboard_notifications_safe_{$userId}_{$role}", $this->cacheTimeout, function () use ($userId, $role) {
            try {
                $notifications = [];

                // Simple role-based notifications without complex queries
                switch ($role) {
                    case 'admin':
                        try {
                            if (Schema::hasTable('users')) {
                                $userCount = DB::table('users')->count();
                                $notifications[] = [
                                    'type' => 'info',
                                    'message' => "System has {$userCount} total users",
                                    'action' => '#',
                                    'created_at' => now(),
                                ];
                            }
                        } catch (Exception $e) {
                            Log::error('Error creating admin notifications', ['error' => $e->getMessage()]);
                        }
                        break;

                    case 'teacher':
                        try {
                            if (Schema::hasTable('activities')) {
                                $activityCount = DB::table('activities')->count();
                                $notifications[] = [
                                    'type' => 'info',
                                    'message' => "System has {$activityCount} activities available",
                                    'action' => '#',
                                    'created_at' => now(),
                                ];
                            }
                        } catch (Exception $e) {
                            Log::error('Error creating teacher notifications', ['error' => $e->getMessage()]);
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
     * Clear cache for specific user/role
     */
    public function clearUserCache(int $userId, string $role): void
    {
        $patterns = [
            "dashboard_notifications_safe_{$userId}_{$role}",
            "dashboard_{$role}_{$userId}",
            "dashboard_stats_safe_{$role}_{$userId}",
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
        Cache::forget('dashboard_basic_stats_safe');
        Cache::forget('dashboard_system_health_safe');
        
        // Clear pattern-based cache keys
        $patterns = [
            'dashboard_recent_activities_safe_*',
            'dashboard_upcoming_events_safe_*',
        ];

        foreach ($patterns as $pattern) {
            Cache::forget($pattern);
        }
    }
}