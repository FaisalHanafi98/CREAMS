<?php

namespace App\Services\Dashboard;

use App\Models\Users;
use App\Models\Trainee;
use App\Models\Activity;
use App\Models\Centres;
use App\Models\ContactMessages;
use App\Models\Volunteers;
use App\Models\Asset;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use Exception;

class AdminDashboardService extends BaseDashboardService
{
    /**
     * Get dashboard data for admin users
     */
    public function getDashboardData(int $adminId): array
    {
        return Cache::remember("dashboard_admin_{$adminId}", $this->cacheTimeout, function () use ($adminId) {
            try {
                return [
                    'stats' => $this->getAdminStats(),
                    'charts' => $this->getAdminCharts(),
                    'notifications' => $this->getNotifications($adminId, 'admin'),
                    'recentActivities' => $this->getRecentActivities(5),
                    'upcomingEvents' => $this->getUpcomingEvents(5),
                    'systemHealth' => $this->getSystemHealth(),
                    'quickActions' => $this->getAdminQuickActions(),
                    'pendingApprovals' => $this->getPendingApprovals(),
                    'systemAlerts' => $this->getSystemAlerts(),
                    'userGrowth' => $this->getUserGrowthData(),
                    'centreStats' => $this->getCentreStatistics(),
                ];
            } catch (Exception $e) {
                Log::error('Error getting admin dashboard data', [
                    'admin_id' => $adminId,
                    'error' => $e->getMessage()
                ]);
                
                return $this->getFallbackAdminData($adminId);
            }
        });
    }

    /**
     * Get admin-specific statistics
     */
    private function getAdminStats(): array
    {
        try {
            $basicStats = $this->getBasicStats();
            
            // Add admin-specific stats
            $adminStats = [
                'pending_volunteers' => Volunteers::where('status', 'pending')->count(),
                'unread_messages' => ContactMessages::where('status', 'unread')->count(),
                'total_centres' => Centres::count(),
                'active_centres' => Centres::where('status', 'active')->count(),
                'system_users' => Users::whereIn('role', ['admin', 'supervisor', 'teacher'])->count(),
                'total_assets' => Asset::count(),
                'asset_value' => Asset::sum('value') ?? 0,
            ];

            // Calculate role distribution
            $roleDistribution = Users::select('role', DB::raw('count(*) as count'))
                ->groupBy('role')
                ->pluck('count', 'role')
                ->toArray();

            // Calculate recent growth (last 30 days)
            $recentGrowth = [
                'new_users' => Users::where('created_at', '>=', Carbon::now()->subDays(30))->count(),
                'new_trainees' => Trainee::where('created_at', '>=', Carbon::now()->subDays(30))->count(),
                'new_activities' => Activity::where('created_at', '>=', Carbon::now()->subDays(30))->count(),
            ];

            return array_merge($basicStats, $adminStats, [
                'role_distribution' => $roleDistribution,
                'recent_growth' => $recentGrowth,
            ]);
        } catch (Exception $e) {
            Log::error('Error getting admin stats', ['error' => $e->getMessage()]);
            return $this->getBasicStats();
        }
    }

    /**
     * Get admin-specific charts
     */
    private function getAdminCharts(): array
    {
        try {
            return [
                'user_registration' => $this->getChartData('user_registration', ['days' => 30]),
                'activity_participation' => $this->getChartData('activity_participation', ['limit' => 10]),
                'role_distribution' => $this->getRoleDistributionChart(),
                'centre_performance' => $this->getCentrePerformanceChart(),
                'monthly_overview' => $this->getMonthlyOverviewChart(),
            ];
        } catch (Exception $e) {
            Log::error('Error getting admin charts', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Get role distribution chart data
     */
    private function getRoleDistributionChart(): array
    {
        try {
            $data = Users::select('role', DB::raw('count(*) as count'))
                ->groupBy('role')
                ->get();

            return [
                'labels' => $data->pluck('role')->map(fn($role) => ucfirst($role))->toArray(),
                'datasets' => [[
                    'label' => 'Users by Role',
                    'data' => $data->pluck('count')->toArray(),
                    'backgroundColor' => [
                        'rgba(255, 99, 132, 0.8)',
                        'rgba(54, 162, 235, 0.8)',
                        'rgba(255, 205, 86, 0.8)',
                        'rgba(75, 192, 192, 0.8)',
                    ],
                ]]
            ];
        } catch (Exception $e) {
            Log::error('Error getting role distribution chart', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Get centre performance chart data
     */
    private function getCentrePerformanceChart(): array
    {
        try {
            $data = Centres::with(['users', 'trainees', 'activities'])
                ->get()
                ->map(function ($centre) {
                    return [
                        'name' => $centre->name,
                        'users' => $centre->users->count(),
                        'trainees' => $centre->trainees->count(),
                        'activities' => $centre->activities->count(),
                    ];
                });

            return [
                'labels' => $data->pluck('name')->toArray(),
                'datasets' => [
                    [
                        'label' => 'Users',
                        'data' => $data->pluck('users')->toArray(),
                        'backgroundColor' => 'rgba(75, 192, 192, 0.8)',
                    ],
                    [
                        'label' => 'Trainees',
                        'data' => $data->pluck('trainees')->toArray(),
                        'backgroundColor' => 'rgba(54, 162, 235, 0.8)',
                    ],
                    [
                        'label' => 'Activities',
                        'data' => $data->pluck('activities')->toArray(),
                        'backgroundColor' => 'rgba(255, 205, 86, 0.8)',
                    ],
                ]
            ];
        } catch (Exception $e) {
            Log::error('Error getting centre performance chart', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Get monthly overview chart data
     */
    private function getMonthlyOverviewChart(): array
    {
        try {
            $months = [];
            $userData = [];
            $traineeData = [];
            $activityData = [];

            for ($i = 11; $i >= 0; $i--) {
                $date = Carbon::now()->subMonths($i);
                $months[] = $date->format('M Y');

                $userData[] = Users::whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->count();

                $traineeData[] = Trainee::whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->count();

                $activityData[] = Activity::whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->count();
            }

            return [
                'labels' => $months,
                'datasets' => [
                    [
                        'label' => 'New Users',
                        'data' => $userData,
                        'borderColor' => 'rgb(75, 192, 192)',
                        'backgroundColor' => 'rgba(75, 192, 192, 0.2)',
                    ],
                    [
                        'label' => 'New Trainees',
                        'data' => $traineeData,
                        'borderColor' => 'rgb(54, 162, 235)',
                        'backgroundColor' => 'rgba(54, 162, 235, 0.2)',
                    ],
                    [
                        'label' => 'New Activities',
                        'data' => $activityData,
                        'borderColor' => 'rgb(255, 205, 86)',
                        'backgroundColor' => 'rgba(255, 205, 86, 0.2)',
                    ],
                ]
            ];
        } catch (Exception $e) {
            Log::error('Error getting monthly overview chart', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Get admin quick actions
     */
    private function getAdminQuickActions(): array
    {
        return [
            [
                'title' => 'Add New User',
                'icon' => 'fas fa-user-plus',
                'url' => '/admin/users/create',
                'color' => 'primary',
            ],
            [
                'title' => 'Create Centre',
                'icon' => 'fas fa-building',
                'url' => '/admin/centres/create',
                'color' => 'success',
            ],
            [
                'title' => 'System Reports',
                'icon' => 'fas fa-chart-bar',
                'url' => '/admin/reports',
                'color' => 'info',
            ],
            [
                'title' => 'Manage Assets',
                'icon' => 'fas fa-boxes',
                'url' => '/admin/assets',
                'color' => 'warning',
            ],
            [
                'title' => 'View Messages',
                'icon' => 'fas fa-envelope',
                'url' => '/admin/messages',
                'color' => 'secondary',
                'badge' => ContactMessages::where('status', 'unread')->count(),
            ],
            [
                'title' => 'Review Volunteers',
                'icon' => 'fas fa-hands-helping',
                'url' => '/admin/volunteers',
                'color' => 'danger',
                'badge' => Volunteers::where('status', 'pending')->count(),
            ],
        ];
    }

    /**
     * Get pending approvals for admin
     */
    private function getPendingApprovals(): array
    {
        try {
            return [
                'volunteers' => Volunteers::where('status', 'pending')
                    ->limit(5)
                    ->get()
                    ->map(function ($volunteer) {
                        return [
                            'id' => $volunteer->id,
                            'name' => $volunteer->name,
                            'email' => $volunteer->email,
                            'type' => 'volunteer',
                            'submitted_at' => $volunteer->created_at,
                        ];
                    })
                    ->toArray(),
                
                'centre_requests' => [], // Placeholder for future centre approval system
                'user_requests' => [], // Placeholder for future user approval system
            ];
        } catch (Exception $e) {
            Log::error('Error getting pending approvals', ['error' => $e->getMessage()]);
            return ['volunteers' => [], 'centre_requests' => [], 'user_requests' => []];
        }
    }

    /**
     * Get system alerts
     */
    private function getSystemAlerts(): array
    {
        try {
            $alerts = [];

            // Check system health
            $health = $this->getSystemHealth();
            if ($health['overall'] !== 'healthy') {
                $alerts[] = [
                    'type' => 'danger',
                    'title' => 'System Health Alert',
                    'message' => 'Some system components are not functioning properly.',
                    'action' => '/admin/system-health',
                ];
            }

            // Check for inactive centres
            $inactiveCentres = Centres::where('status', '!=', 'active')->count();
            if ($inactiveCentres > 0) {
                $alerts[] = [
                    'type' => 'warning',
                    'title' => 'Inactive Centres',
                    'message' => "{$inactiveCentres} centres are currently inactive.",
                    'action' => '/admin/centres',
                ];
            }

            // Check for old unread messages
            $oldMessages = ContactMessages::where('status', 'unread')
                ->where('created_at', '<', Carbon::now()->subDays(7))
                ->count();
            
            if ($oldMessages > 0) {
                $alerts[] = [
                    'type' => 'info',
                    'title' => 'Old Messages',
                    'message' => "{$oldMessages} messages have been unread for over a week.",
                    'action' => '/admin/messages',
                ];
            }

            return $alerts;
        } catch (Exception $e) {
            Log::error('Error getting system alerts', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Get user growth data
     */
    private function getUserGrowthData(): array
    {
        try {
            $last30Days = Carbon::now()->subDays(30);
            $last7Days = Carbon::now()->subDays(7);

            return [
                'total_growth' => [
                    'users' => Users::where('created_at', '>=', $last30Days)->count(),
                    'trainees' => Trainee::where('created_at', '>=', $last30Days)->count(),
                    'activities' => Activity::where('created_at', '>=', $last30Days)->count(),
                ],
                'weekly_growth' => [
                    'users' => Users::where('created_at', '>=', $last7Days)->count(),
                    'trainees' => Trainee::where('created_at', '>=', $last7Days)->count(),
                    'activities' => Activity::where('created_at', '>=', $last7Days)->count(),
                ],
                'growth_rate' => $this->calculateGrowthRate(),
            ];
        } catch (Exception $e) {
            Log::error('Error getting user growth data', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Calculate growth rate
     */
    private function calculateGrowthRate(): array
    {
        try {
            $currentMonth = Users::whereMonth('created_at', Carbon::now()->month)->count();
            $lastMonth = Users::whereMonth('created_at', Carbon::now()->subMonth()->month)->count();
            
            $userGrowthRate = $lastMonth > 0 ? (($currentMonth - $lastMonth) / $lastMonth) * 100 : 0;

            return [
                'users' => round($userGrowthRate, 2),
                // Add more growth rates as needed
            ];
        } catch (Exception $e) {
            Log::error('Error calculating growth rate', ['error' => $e->getMessage()]);
            return ['users' => 0];
        }
    }

    /**
     * Get centre statistics
     */
    private function getCentreStatistics(): array
    {
        try {
            return Centres::with(['users', 'trainees', 'activities'])
                ->get()
                ->map(function ($centre) {
                    return [
                        'id' => $centre->id,
                        'name' => $centre->name,
                        'location' => $centre->location,
                        'status' => $centre->status,
                        'users_count' => $centre->users->count(),
                        'trainees_count' => $centre->trainees->count(),
                        'activities_count' => $centre->activities->count(),
                        'capacity_utilization' => $this->calculateCapacityUtilization($centre),
                    ];
                })
                ->toArray();
        } catch (Exception $e) {
            Log::error('Error getting centre statistics', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Calculate capacity utilization for a centre
     */
    private function calculateCapacityUtilization($centre): float
    {
        try {
            $maxCapacity = $centre->capacity ?? 100; // Default capacity
            $currentTrainees = $centre->trainees->count();
            
            return $maxCapacity > 0 ? ($currentTrainees / $maxCapacity) * 100 : 0;
        } catch (Exception $e) {
            Log::error('Error calculating capacity utilization', ['error' => $e->getMessage()]);
            return 0;
        }
    }

    /**
     * Get fallback data when main query fails
     */
    private function getFallbackAdminData(int $adminId): array
    {
        return [
            'stats' => $this->getBasicStats(),
            'charts' => [],
            'notifications' => [],
            'recentActivities' => [],
            'upcomingEvents' => [],
            'systemHealth' => ['overall' => 'unknown'],
            'quickActions' => $this->getAdminQuickActions(),
            'pendingApprovals' => ['volunteers' => [], 'centre_requests' => [], 'user_requests' => []],
            'systemAlerts' => [],
            'userGrowth' => [],
            'centreStats' => [],
        ];
    }
}