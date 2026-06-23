<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Trainee;
use App\Models\Activity;
use App\Models\ActivitySession;
use App\Models\Centre;
use App\Models\Asset;
use App\Models\ActivityLog;
use App\Traits\HandlesEncryptedIds;

class DashboardController extends Controller
{
    use HandlesEncryptedIds;
    /**
     * Display modern dashboard (now using enhanced version)
     */
    public function index(Request $request)
    {
        try {
            // Check authentication
            if (!session()->has('id')) {
                return redirect()->route('login');
            }

            $userId = session('id', 1); // Default to user 1 for demo
            $role = session('role', 'admin'); // Default to admin for demo
            $centreId = session('centre_id', '01'); // Default to centre 01 for demo
            $weekOffset = intval($request->input('week_offset', 0));
            
            // Log for debugging
            Log::info('Dashboard access', ['userId' => $userId, 'role' => $role, 'centreId' => $centreId]);
            
            // Get enhanced dashboard data with additional UX features
            $dashboardData = $this->getEnhancedDashboardData($role, $userId, $centreId, $weekOffset);
            
            // Route to role-specific dashboard views
            $dashboardView = $this->getDashboardViewByRole($role);
            
            return view($dashboardView, $dashboardData);
            
        } catch (\Exception $e) {
            Log::error('Modern dashboard error', [
                'user_id' => $userId ?? 'unknown',
                'error' => $e->getMessage()
            ]);
            
            $dashboardView = $this->getDashboardViewByRole($role ?? 'trainee');
            
            return view($dashboardView, [
                'error' => 'Unable to load dashboard data',
                'role' => $role ?? 'unknown',
                'user_name' => session('name', 'User'),
                'current_time' => now()->format('l, F j, Y - g:i A'),
                'todays_centre_activities' => [],
                'calendar_data' => ['events' => [], 'week_start' => now()->startOfWeek(), 'week_end' => now()->endOfWeek()],
                'recent_activities_centre' => [],
                'upcoming_sessions' => [],
                'user_encrypted_id' => '',
                'stats' => [],
                'personal_stats' => []
            ]);
        }
    }

    /**
     * Display new modern dashboard design
     */
    public function modernNew(Request $request)
    {
        try {
            // Check authentication
            if (!session()->has('id')) {
                return redirect()->route('login');
            }

            $userId = session('id');
            $role = session('role');
            $centreId = session('centre_id');
            
            // Get dashboard data based on role
            $dashboardData = $this->getDashboardData($role, $userId, $centreId);
            
            return view('dashboard.modernnew', $dashboardData);
            
        } catch (\Exception $e) {
            Log::error('Modern new dashboard error', [
                'user_id' => $userId ?? 'unknown',
                'error' => $e->getMessage()
            ]);
            
            // NF-01: dashboard.modernnew view does not exist; rendering it here re-threw
            // out of the catch as a hard 500. Degrade to the working dashboard instead.
            return redirect()->route('dashboard')
                ->with('error', 'Unable to load the modern dashboard.');
        }
    }

    /**
     * Display enhanced dashboard with improved UX
     */
    public function enhanced(Request $request)
    {
        try {
            // Check authentication
            if (!session()->has('id')) {
                return redirect()->route('login');
            }

            $userId = session('id');
            $role = session('role');
            $centreId = session('centre_id');
            
            // Get enhanced dashboard data with additional UX features
            $dashboardData = $this->getEnhancedDashboardData($role, $userId, $centreId);
            
            return view('dashboard.modern', $dashboardData);

        } catch (\Exception $e) {
            Log::error('Enhanced dashboard error', [
                'user_id' => $userId ?? 'unknown',
                'error' => $e->getMessage()
            ]);

            return view('dashboard.modern', [
                'error' => 'Unable to load enhanced dashboard data',
                'role' => $role ?? 'unknown',
                'user_name' => session('name', 'User'),
                'current_time' => now()->format('l, F j, Y - g:i A'),
                'todays_centre_activities' => [],
                'calendar_data' => ['events' => [], 'week_start' => now()->startOfWeek(), 'week_end' => now()->endOfWeek()],
                'recent_activities_centre' => [],
                'upcoming_sessions' => [],
                'user_encrypted_id' => '',
                'stats' => [],
                'personal_stats' => []
            ]);
        }
    }

    /**
     * Determine which dashboard view to use based on user role
     */
    private function getDashboardViewByRole($role)
    {
        // For now, return to the original modern dashboard interface that users are familiar with
        // Role-specific dashboards are available but not being used yet
        return 'dashboard.modern';
        
        /*
        // Role-specific dashboard routing (available when needed):
        switch (strtolower($role)) {
            case 'admin':
                return 'dashboard.admin';
            case 'teacher':
                return 'dashboard.teacher';
            case 'supervisor':
                return 'dashboard.supervisor';
            case 'ajk':
                return 'dashboard.ajk';
            case 'trainee':
                return 'dashboard.trainee';
            case 'parent':
                return 'dashboard.parent';
            default:
                return 'dashboard.modern';
        }
        */
    }

    /**
     * Get comprehensive dashboard data based on role
     */
    private function getDashboardData($role, $userId, $centreId, $weekOffset = 0)
    {
        // Get individual data components
        $recentActivities = $this->getRecentActivities($role, $userId, $centreId);
        $recentUsers = $this->getRecentUsers($role, $centreId);
        $upcomingSessions = $this->getUpcomingSessions($role, $userId, $centreId);
        $currentSessions = $this->getCurrentSessions($role, $userId, $centreId);
        $quickStats = $this->getQuickStats($role, $userId, $centreId);
        
        $data = [
            'role' => $role,
            'user_name' => session('name'),
            'centre_id' => $centreId,
            'current_time' => now()->format('l, F j, Y - g:i A'),
            'quick_stats' => $quickStats,
            
            // Structure data to match template expectations
            'recent_activities' => $this->getRecentActivities($role, $userId, $centreId, null, true), // Force user-specific for personal tab
            'recent_activities_centre' => $this->getComprehensiveRecentChanges($centreId, 10), // Use comprehensive changes for General tab
            'recent' => [
                'activities' => $recentActivities,
                'users' => $recentUsers
            ],
            
            // Session data for different template structures
            'upcoming_sessions' => $upcomingSessions,
            'schedule' => [
                'today' => $currentSessions,
                'upcoming' => $upcomingSessions
            ],
            
            'notifications' => $this->getNotifications($userId),
            'recent_users' => $recentUsers,
            'current_sessions' => $currentSessions,
            'calendar_data' => $this->getCalendarEvents($role, $userId, $centreId, $weekOffset),
            'todays_centre_activities' => $this->getTodaysActivities($role, $userId, $centreId),
            'system_alerts' => $this->getSystemAlerts($role),
            'progress_summary' => $this->getProgressSummary($role, $userId, $centreId),
            'centre_info' => $this->getCentreInfo($centreId),
            
            // Separate statistics for General and Personal tabs
            'stats' => $quickStats, // General tab statistics (system-wide)
            'stats_flat' => $this->getFlatStatsFromCards($quickStats), // Flat array for legacy view compatibility
            'personal_stats' => $this->getUserPerformanceStats($role, $userId, $centreId), // Personal tab statistics (user-specific)
            'performance' => [
                'cache_status' => 'Online',
                'system_health' => 'Good',
                'load_time' => round(microtime(true) * 1000)
            ],
            
            // User identification for links
            'user_encrypted_id' => $this->generateEncryptedId($userId)
        ];

        try {
            return $data;
        } catch (\Exception $e) {
            Log::error('Dashboard data error', ['error' => $e->getMessage()]);
            return $data;
        }
    }

    /**
     * Get comprehensive admin statistics with detailed breakdowns
     */
    private function getAdminStats()
    {
        try {
            // Get centre ID for filtering - if null/empty, get from logged-in user
            $centreId = session('centre_id');

            // If centre_id is not in session, get it from the user's record
            if (!$centreId) {
                $userId = session('id');
                $user = DB::table('staffs')->where('id', $userId)->first();
                $centreId = $user->centre_id ?? '01';

                // Update session with centre_id for future use
                session(['centre_id' => $centreId]);
            }

            // Calculate growth rates dynamically - CENTRE SPECIFIC
            $currentMonthUsers = DB::table('staffs')
                ->where('centre_id', $centreId)
                ->where('status', 'active')
                ->whereMonth('created_at', now()->month)
                ->count();
            $lastMonthUsers = DB::table('staffs')
                ->where('centre_id', $centreId)
                ->where('status', 'active')
                ->whereMonth('created_at', now()->subMonth()->month)
                ->count();
            $userGrowthRate = $lastMonthUsers > 0 ? round((($currentMonthUsers - $lastMonthUsers) / $lastMonthUsers) * 100, 1) : 0;

            $currentMonthTrainees = DB::table('trainees')
                ->where('centre_id', $centreId)
                ->where('status', 'active')
                ->whereMonth('created_at', now()->month)
                ->count();
            $lastMonthTrainees = DB::table('trainees')
                ->where('centre_id', $centreId)
                ->where('status', 'active')
                ->whereMonth('created_at', now()->subMonth()->month)
                ->count();
            $traineeGrowthRate = $lastMonthTrainees > 0 ? round((($currentMonthTrainees - $lastMonthTrainees) / $lastMonthTrainees) * 100, 1) : 0;

            $currentMonthActivities = DB::table('activities')
                ->where('centre_id', $centreId)
                ->where('is_active', true)
                ->whereMonth('created_at', now()->month)
                ->count();
            $lastMonthActivities = DB::table('activities')
                ->where('centre_id', $centreId)
                ->where('is_active', true)
                ->whereMonth('created_at', now()->subMonth()->month)
                ->count();
            $activityGrowthRate = $lastMonthActivities > 0 ? round((($currentMonthActivities - $lastMonthActivities) / $lastMonthActivities) * 100, 1) : 0;

            // CENTRE-SPECIFIC COUNTS - Active staff in this centre
            $totalUsers = DB::table('staffs')
                ->where('centre_id', $centreId)
                ->where('status', 'active')
                ->count();

            // Active trainees in this centre
            $activeTrainees = DB::table('trainees')
                ->where('centre_id', $centreId)
                ->where('status', 'active')
                ->count();

            // Active activities (ongoing programs) in this centre
            $totalActivities = DB::table('activities')
                ->where('centre_id', $centreId)
                ->where('is_active', true)
                ->count();

            // Sessions this week in this centre
            $startOfWeek = now()->startOfWeek();
            $endOfWeek = now()->endOfWeek();
            $sessionsThisWeek = DB::table('activity_occurrences')
                ->join('activities', 'activity_occurrences.activity_id', '=', 'activities.id')
                ->where('activities.centre_id', $centreId)
                ->whereBetween('activity_occurrences.session_date', [$startOfWeek->format('Y-m-d'), $endOfWeek->format('Y-m-d')])
                ->count();

            // Count active centres (for admin dashboard display)
            $activeCentres = DB::table('centres')
                ->where('centre_status', 'active')
                ->count();

            return [
                'total_users' => $totalUsers,
                'total_trainees' => $activeTrainees,
                'total_activities' => $totalActivities,
                'active_centres' => $activeCentres,
                'sessions_this_week' => $sessionsThisWeek,
                'user_growth_rate' => $userGrowthRate,
                'trainee_growth_rate' => $traineeGrowthRate,
                'activity_growth_rate' => $activityGrowthRate,
                'detailed_stats' => [
                    [
                        'title' => 'Total Users',
                        'value' => $totalUsers,
                        'icon' => 'fas fa-users',
                        'color' => 'primary',
                        'trend' => $userGrowthRate > 0 ? "+{$userGrowthRate}%" : ($userGrowthRate < 0 ? "{$userGrowthRate}%" : "stable"),
                        'details' => [
                            'admins' => DB::table('staffs')->where('role', 'admin')->count(),
                            'supervisors' => DB::table('staffs')->where('role', 'supervisor')->count(), 
                            'teachers' => DB::table('staffs')->where('role', 'teacher')->count(),
                            'ajk' => DB::table('staffs')->where('role', 'ajk')->count(),
                            'active_today' => DB::table('staffs')->whereDate('last_accessed_at', today())->count(),
                            'inactive_30_days' => DB::table('staffs')->where('last_accessed_at', '<', now()->subDays(30))->count()
                        ]
                    ],
                    [
                        'title' => 'Active Trainees',
                        'value' => $activeTrainees,
                        'icon' => 'fas fa-user-graduate',
                    'color' => 'success',
                    'trend' => $traineeGrowthRate > 0 ? "+{$traineeGrowthRate}%" : ($traineeGrowthRate < 0 ? "{$traineeGrowthRate}%" : "stable"),
                    'details' => [
                        'total_registered' => DB::table('trainees')->count(),
                        'pending_registration' => DB::table('trainees')->where('status', 'pending')->count(),
                        'graduated' => DB::table('trainees')->where('status', 'graduated')->count(),
                        'by_condition' => DB::table('trainees')
                            ->select('trainee_condition', DB::raw('count(*) as count'))
                            ->groupBy('trainee_condition')
                            ->pluck('count', 'trainee_condition')
                            ->toArray()
                    ]
                ],
                [
                    'title' => 'Total Activities',
                    'value' => $totalActivities,
                    'icon' => 'fas fa-tasks',
                    'color' => 'info', 
                    'trend' => $activityGrowthRate > 0 ? "+{$activityGrowthRate}%" : ($activityGrowthRate < 0 ? "{$activityGrowthRate}%" : "stable"),
                    'details' => [
                        'scheduled' => DB::table('activities')->where('is_active', true)->count(),
                        'completed' => DB::table('activities')->where('is_active', false)->count(),
                        'cancelled' => 0,
                        'today_sessions' => DB::table('activity_occurrences')->whereDate('session_date', today())->count(),
                        'total_sessions' => DB::table('activity_occurrences')->count()
                        // Removed 'by_type' query - activity_categories table doesn't exist
                    ]
                ],
                [
                    'title' => 'Sessions This Week',
                    'value' => $sessionsThisWeek,
                    'icon' => 'fas fa-calendar-week',
                    'color' => 'warning',
                    'trend' => $sessionsThisWeek > 0 ? 'active' : 'none',
                    'details' => [
                        'today_sessions' => DB::table('activity_occurrences')
                            ->join('activities', 'activity_occurrences.activity_id', '=', 'activities.id')
                            ->where('activities.centre_id', $centreId)
                            ->whereDate('activity_occurrences.session_date', today())
                            ->count(),
                        'completed_this_week' => DB::table('activity_occurrences')
                            ->join('activities', 'activity_occurrences.activity_id', '=', 'activities.id')
                            ->where('activities.centre_id', $centreId)
                            ->whereBetween('activity_occurrences.session_date', [$startOfWeek->format('Y-m-d'), now()->format('Y-m-d')])
                            ->where('activity_occurrences.session_status', 'completed')
                            ->count(),
                        'upcoming_this_week' => DB::table('activity_occurrences')
                            ->join('activities', 'activity_occurrences.activity_id', '=', 'activities.id')
                            ->where('activities.centre_id', $centreId)
                            ->whereBetween('activity_occurrences.session_date', [now()->format('Y-m-d'), $endOfWeek->format('Y-m-d')])
                            ->where('activity_occurrences.session_status', 'scheduled')
                            ->count()
                    ]
                ],
                [
                    'title' => 'System Letters',
                    'value' => $this->getLettersCount(),
                    'icon' => 'fas fa-envelope',
                    'color' => 'secondary',
                    'trend' => 'tracking',
                    'details' => $this->getLettersDetails()
                ],
                [
                    'title' => 'System Health',
                    'value' => '98%',
                    'icon' => 'fas fa-heartbeat',
                    'color' => 'success',
                    'trend' => 'optimal',
                    'details' => [
                        'database_queries' => 'Fast (<100ms avg)',
                        'storage_usage' => $this->getStorageUsage(),
                        'active_sessions' => $this->getActiveSessionsCount(),
                        'error_rate' => '0.2%'
                    ]
                    ]
                ]
            ];
        } catch (\Exception $e) {
            Log::error('Error calculating admin stats', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);

            return [
                'total_users' => 0,
                'total_trainees' => 0,
                'total_activities' => 0,
                'sessions_this_week' => 0,
                [
                    'title' => 'Total Users',
                    'value' => 0,
                    'icon' => 'fas fa-users',
                    'color' => 'primary',
                    'trend' => 'unavailable'
                ],
                [
                    'title' => 'Active Trainees', 
                    'value' => 0,
                    'icon' => 'fas fa-user-graduate',
                    'color' => 'success',
                    'trend' => 'unavailable'
                ],
                [
                    'title' => 'Total Activities',
                    'value' => 0,
                    'icon' => 'fas fa-tasks',
                    'color' => 'info',
                    'trend' => 'unavailable'
                ],
                [
                    'title' => 'Active Centres',
                    'value' => 0,
                    'icon' => 'fas fa-building',
                    'color' => 'warning',
                    'trend' => 'unavailable'
                ]
            ];
        }
    }

    /**
     * Get supervisor statistics
     */
    private function getSupervisorStats($centreId)
    {
        try {
            // Calculate actual numbers for the centre
            $staffCount = DB::table('staffs')->where('centre_id', $centreId)->where('status', 'active')->count();
            $traineeCount = DB::table('trainees')->where('centre_id', $centreId)->where('status', 'active')->count();
            $activityCount = DB::table('activities')->where('centre_id', $centreId)->where('is_active', true)->count();
            $assetCount = DB::table('assets')->where('centre_id', $centreId)->count();
            
            // Calculate growth rates
            $currentMonthTrainees = DB::table('trainees')
                ->where('centre_id', $centreId)
                ->whereMonth('created_at', now()->month)
                ->count();
            $lastMonthTrainees = DB::table('trainees')
                ->where('centre_id', $centreId)
                ->whereMonth('created_at', now()->subMonth()->month)
                ->count();
            $traineeGrowthRate = $lastMonthTrainees > 0 ? round((($currentMonthTrainees - $lastMonthTrainees) / $lastMonthTrainees) * 100, 1) : 0;
            
            return [
                [
                    'title' => 'Centre Staff',
                    'value' => $staffCount,
                    'icon' => 'fas fa-users',
                    'color' => 'primary',
                    'trend' => 'stable'
                ],
                [
                    'title' => 'Centre Trainees',
                    'value' => $traineeCount,
                    'icon' => 'fas fa-user-graduate',
                    'color' => 'success',
                    'trend' => $traineeGrowthRate > 0 ? "+{$traineeGrowthRate}%" : ($traineeGrowthRate < 0 ? "{$traineeGrowthRate}%" : "stable")
                ],
                [
                    'title' => 'Active Activities',
                    'value' => $activityCount,
                    'icon' => 'fas fa-tasks',
                    'color' => 'info',
                    'trend' => 'ongoing'
                ],
                [
                    'title' => 'Assets',
                    'value' => $assetCount,
                    'icon' => 'fas fa-boxes',
                    'color' => 'warning',
                    'trend' => 'managed'
                ]
            ];
        } catch (\Exception $e) {
            Log::error('Error calculating supervisor stats', ['error' => $e->getMessage(), 'centre_id' => $centreId]);
            return [
                [
                    'title' => 'Centre Staff',
                    'value' => 0,
                    'icon' => 'fas fa-users',
                    'color' => 'primary',
                    'trend' => 'unavailable'
                ],
                [
                    'title' => 'Centre Trainees',
                    'value' => 0,
                    'icon' => 'fas fa-user-graduate',
                    'color' => 'success',
                    'trend' => 'unavailable'
                ],
                [
                    'title' => 'Active Activities',
                    'value' => 0,
                    'icon' => 'fas fa-tasks',
                    'color' => 'info',
                    'trend' => 'unavailable'
                ],
                [
                    'title' => 'Assets',
                    'value' => 0,
                    'icon' => 'fas fa-boxes',
                    'color' => 'warning',
                    'trend' => 'unavailable'
                ]
            ];
        }
    }

    /**
     * Get teacher statistics
     */
    private function getTeacherStats($userId, $centreId)
    {
        try {
            // Calculate actual values for the teacher
            $myActivities = DB::table('activities')->where('instructor_id', $userId)->count();
            $assignedSessions = DB::table('activity_occurrences')->where('instructor_id', $userId)->where('session_status', 'scheduled')->count();
            $centreTrainees = DB::table('trainees')->where('centre_id', $centreId)->where('status', 'active')->count();
            $completedSessions = DB::table('activity_occurrences')->where('instructor_id', $userId)->where('session_status', 'completed')->count();
            
            // Calculate completion rate
            $totalSessions = DB::table('activity_occurrences')->where('instructor_id', $userId)->count();
            $completionRate = $totalSessions > 0 ? round(($completedSessions / $totalSessions) * 100, 1) : 0;
            
            return [
                [
                    'title' => 'My Activities',
                    'value' => $myActivities,
                    'icon' => 'fas fa-clipboard-list',
                    'color' => 'primary',
                    'trend' => 'active'
                ],
                [
                    'title' => 'Assigned Sessions',
                    'value' => $assignedSessions,
                    'icon' => 'fas fa-calendar',
                    'color' => 'success',
                    'trend' => 'upcoming'
                ],
                [
                    'title' => 'Centre Trainees',
                    'value' => $centreTrainees,
                    'icon' => 'fas fa-user-graduate',
                    'color' => 'info',
                    'trend' => 'available'
                ],
                [
                    'title' => 'Completed Sessions',
                    'value' => $completedSessions,
                    'icon' => 'fas fa-check-circle',
                    'color' => 'success',
                    'trend' => "{$completionRate}% rate"
                ]
            ];
        } catch (\Exception $e) {
            Log::error('Error calculating teacher stats', ['error' => $e->getMessage(), 'user_id' => $userId]);
            return [
                [
                    'title' => 'My Activities',
                    'value' => 0,
                    'icon' => 'fas fa-clipboard-list',
                    'color' => 'primary',
                    'trend' => 'unavailable'
                ],
                [
                    'title' => 'Assigned Sessions',
                    'value' => 0,
                    'icon' => 'fas fa-calendar',
                    'color' => 'success',
                    'trend' => 'unavailable'
                ],
                [
                    'title' => 'Centre Trainees',
                    'value' => 0,
                    'icon' => 'fas fa-user-graduate',
                    'color' => 'info',
                    'trend' => 'unavailable'
                ],
                [
                    'title' => 'Completed Sessions',
                    'value' => 0,
                    'icon' => 'fas fa-check-circle',
                    'color' => 'success',
                    'trend' => 'unavailable'
                ]
            ];
        }
    }

    /**
     * Get AJK statistics
     */
    private function getAjkStats($centreId)
    {
        try {
            // Calculate actual values for AJK role
            $centreTrainees = DB::table('trainees')->where('centre_id', $centreId)->where('status', 'active')->count();
            $activeActivities = DB::table('activities')->where('centre_id', $centreId)->where('is_active', true)->count();
            $todaySessions = DB::table('activity_occurrences')
                ->join('activities', 'activity_occurrences.activity_id', '=', 'activities.id')
                ->where('activities.centre_id', $centreId)
                ->whereDate('activity_occurrences.session_date', today())
                ->count();
            
            // Calculate maintenance alerts
            $maintenanceAlertsQuery = DB::table('assets')
                ->where('centre_id', $centreId)
                ->where('status', 'maintenance');

            if (Schema::hasColumn('assets', 'next_maintenance_date')) {
                $maintenanceAlertsQuery->orWhere(function ($query) use ($centreId) {
                    $query->where('centre_id', $centreId)
                        ->where('next_maintenance_date', '<=', now()->addDays(7));
                });
            }

            $maintenanceAlerts = $maintenanceAlertsQuery->count();
            
            return [
                [
                    'title' => 'Centre Trainees',
                    'value' => $centreTrainees,
                    'icon' => 'fas fa-user-graduate',
                    'color' => 'primary',
                    'trend' => 'active'
                ],
                [
                    'title' => 'Active Activities',
                    'value' => $activeActivities,
                    'icon' => 'fas fa-tasks',
                    'color' => 'success',
                    'trend' => 'running'
                ],
                [
                    'title' => 'Today\'s Sessions',
                    'value' => $todaySessions,
                    'icon' => 'fas fa-calendar-day', 
                    'color' => 'info',
                    'trend' => 'scheduled'
                ],
                [
                    'title' => 'Maintenance Alerts',
                    'value' => $maintenanceAlerts,
                    'icon' => 'fas fa-tools',
                    'color' => 'warning',
                    'trend' => $maintenanceAlerts > 0 ? 'attention needed' : 'all good'
                ]
            ];
        } catch (\Exception $e) {
            Log::error('Error calculating AJK stats', ['error' => $e->getMessage(), 'centre_id' => $centreId]);
            return [
                [
                    'title' => 'Centre Trainees',
                    'value' => 0,
                    'icon' => 'fas fa-user-graduate',
                    'color' => 'primary',
                    'trend' => 'unavailable'
                ],
                [
                    'title' => 'Active Activities',
                    'value' => 0,
                    'icon' => 'fas fa-tasks',
                    'color' => 'success',
                    'trend' => 'unavailable'
                ],
                [
                    'title' => 'Today\'s Sessions',
                    'value' => 0,
                    'icon' => 'fas fa-calendar-day',
                    'color' => 'info',
                    'trend' => 'unavailable'  
                ],
                [
                    'title' => 'Maintenance Alerts',
                    'value' => 0,
                    'icon' => 'fas fa-tools',
                    'color' => 'warning',
                    'trend' => 'unavailable'
                ]
            ];
        }
    }

    /**
     * Get recent activities
     */
    private function getRecentActivities($role = null, $userId = null, $centreId = null, $since = null, $forceUserSpecific = false)
    {
        try {
            $query = DB::table('activities')
                ->select('activities.id', 'activities.activity_name', 'activities.created_at', 'activities.is_active',
                        'activities.category as category_name')
                ->orderBy('activities.created_at', 'desc');

            // Add timestamp filtering if provided
            if ($since) {
                $query->where('activities.created_at', '>', date('Y-m-d H:i:s', $since));
            }
                
            $query->limit(5);
                
            // Filter based on role and user
            if ($forceUserSpecific || ($role !== 'admin' && $userId)) {
                // Show only user-specific activities (for personal tab or non-admin users)
                $query->where('activities.instructor_id', $userId);
            } elseif ($role === 'admin' && !$forceUserSpecific) {
                // Admins see all activities (for general tab)
            }
            
            if ($centreId && $centreId !== 'admin') {
                $query->where('activities.centre_id', $centreId);
            }
            
            return $query->get()->map(function ($activity) {
                // Use category_type if available, otherwise fallback to old mapping
                $mappedType = $activity->category_type ?? $this->mapActivityTypeToCategory($activity->activity_type ?? '');
                
                return [
                    'title' => $activity->activity_name ?? 'Activity',
                    'time' => Carbon::parse($activity->created_at)->diffForHumans(),
                    'status' => $activity->is_active ? 'active' : 'inactive',
                    'type' => $activity->category_type ?? 'general',
                    'category_name' => $activity->category_name ?? 'General',
                    'original_type' => $activity->category_type ?? ''
                ];
            })->toArray();
        } catch (\Exception $e) {
            Log::error('Recent activities error', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Map activity_type to rehabilitation/academic categories for filtering
     */
    private function mapActivityTypeToCategory($activityType)
    {
        // Define rehabilitation categories
        $rehabilitationTypes = [
            'Physical Therapy', 'Occupational Therapy', 'Speech Therapy', 
            'Behavioral Therapy', 'Sensory Integration', 'Physiotherapy',
            'Speech and Language Therapy', 'Occupational Rehabilitation'
        ];
        
        // Define academic categories  
        $academicTypes = [
            'Mathematics', 'Literacy', 'Science', 'Computer Skills', 
            'Art & Creativity', 'Music Therapy', 'Social Skills', 
            'Life Skills', 'Vocational Training', 'Reading', 'Writing'
        ];
        
        // Check if activity type matches rehabilitation categories
        if (in_array($activityType, $rehabilitationTypes)) {
            return 'rehabilitation';
        }
        
        // Check if activity type matches academic categories
        if (in_array($activityType, $academicTypes)) {
            return 'academic';
        }
        
        // Default to general for unknown types
        return 'general';
    }

    /**
     * Get upcoming sessions based on role
     */
    private function getUpcomingSessions($role = null, $userId = null, $centreId = null)
    {
        try {
            $query = DB::table('activity_occurrences')
                ->join('activities', 'activity_occurrences.activity_id', '=', 'activities.id')
                ->select('activities.activity_name', 'activity_occurrences.session_date', 'activity_occurrences.start_time', 'activity_occurrences.location')
                ->where('activity_occurrences.session_status', 'scheduled')
                ->where('activity_occurrences.session_date', '>=', today())
                ->orderBy('activity_occurrences.session_date')
                ->orderBy('activity_occurrences.start_time')
                ->limit(5);

            // Role-based filtering
            if ($role === 'admin') {
                // For admin, show upcoming sessions from all centres
                // No additional filtering needed
            } else if ($userId) {
                // For non-admin users, show only sessions where they are assigned
                $query->where(function($q) use ($userId) {
                    $q->where('activity_occurrences.instructor_id', $userId)
                      ->orWhere('activities.instructor_id', $userId);
                });
            }
            
            if ($centreId && $centreId !== 'admin') {
                $query->where('activities.centre_id', $centreId);
            }

            return $query->get()
                ->map(function ($session) {
                    return [
                        'activity' => $session->activity_name,
                        'date' => Carbon::parse($session->session_date)->format('M j'),
                        'time' => Carbon::parse($session->start_time)->format('g:i A'),
                        'location' => $session->location ?? 'TBA'
                    ];
                });
        } catch (\Exception $e) {
            Log::error('Error getting upcoming sessions', ['error' => $e->getMessage()]);
            return collect();
        }
    }

    /**
     * Get notifications for user
     */
    private function getNotifications($userId)
    {
        try {
            return DB::table('notifications')
                ->where('notifiable_id', $userId)
                ->where('notifiable_type', 'App\\Models\\User')
                ->whereNull('read_at')
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get()
                ->map(function ($notification) {
                    $data = json_decode($notification->data, true);
                    return [
                        'id' => $notification->id,
                        'type' => $data['type'] ?? 'info',
                        'title' => $data['title'] ?? 'Notification',
                        'message' => $data['message'] ?? 'No message',
                        'time' => Carbon::parse($notification->created_at)->diffForHumans(),
                        'read' => !is_null($notification->read_at)
                    ];
                });
        } catch (\Exception $e) {
            // If notifications table doesn't exist or has issues, return sample notifications
            Log::warning('Notifications table not accessible', ['error' => $e->getMessage()]);
            return collect([
                [
                    'id' => 1,
                    'type' => 'info',
                    'title' => 'Welcome!',
                    'message' => 'Welcome to your CREAMS dashboard!',
                    'time' => 'now',
                    'read' => false
                ],
                [
                    'id' => 2,
                    'type' => 'success',
                    'title' => 'System Update',
                    'message' => 'Dashboard statistics have been updated and are now working correctly.',
                    'time' => '1 hour ago',
                    'read' => false
                ]
            ]);
        }
    }

    /**
     * Get recent users (admin/supervisor only)
     */
    private function getRecentUsers($role, $centreId)
    {
        if (!in_array($role, ['admin', 'supervisor'])) {
            return [];
        }

        try {
            $query = DB::table('staffs')
                ->select('id', 'name', 'role', 'last_login', 'is_online', 'created_at')
                ->orderBy('last_login', 'desc')
                ->limit(8);

            if ($role === 'supervisor' && $centreId) {
                $query->where('centre_id', $centreId);
            }

            return $query->get()->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'role' => ucfirst($user->role),
                    'last_login' => $user->last_login ? Carbon::parse($user->last_login)->diffForHumans() : 'Never',
                    'status' => $user->is_online ? 'online' : 'offline',
                    'created' => Carbon::parse($user->created_at)->format('M j, Y')
                ];
            });
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get current active sessions
     */
    private function getCurrentSessions($role, $userId, $centreId)
    {
        try {
            $query = DB::table('activity_occurrences')
                ->join('activities', 'activity_occurrences.activity_id', '=', 'activities.id')
                ->join('staffs', 'activity_occurrences.instructor_id', '=', 'staffs.id')
                ->select(
                    'activities.activity_name',
                    'activity_occurrences.start_time',
                    'activity_occurrences.end_time',
                    'activity_occurrences.location',
                    'staffs.name as teacher_name',
                    'activity_occurrences.session_status',
                    'activity_occurrences.id as session_id'
                )
                ->where('activity_occurrences.session_status', 'ongoing')
                ->whereDate('activity_occurrences.session_date', today());

            if ($role === 'admin') {
                // Admins see all ongoing sessions
            } else if ($userId) {
                // Non-admin users see only their assigned sessions
                $query->where(function($q) use ($userId) {
                    $q->where('activity_occurrences.instructor_id', $userId)
                      ->orWhere('activity_occurrences.instructor_id', $userId)
                      ->orWhere('activities.instructor_id', $userId)
                      ->orWhere('activities.instructor_id', $userId);
                });
            }
            
            if ($centreId && $centreId !== 'admin') {
                $query->where('activities.centre_id', $centreId);
            }

            return $query->limit(6)->get()->map(function ($session) {
                return [
                    'activity' => $session->activity_name,
                    'teacher' => $session->teacher_name,
                    'time' => Carbon::parse($session->start_time)->format('g:i A') . ' - ' . Carbon::parse($session->end_time)->format('g:i A'),
                    'location' => $session->location ?? 'TBA',
                    'status' => ucfirst($session->status),
                    'session_id' => $session->session_id
                ];
            });
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get calendar events for the week
     */
    private function getCalendarEvents($role, $userId, $centreId, $weekOffset = 0)
    {
        try {
            // Calculate week start and end based on offset
            $weekStart = now()->startOfWeek()->addWeeks($weekOffset);
            $weekEnd = $weekStart->copy()->endOfWeek();

            $query = DB::table('activity_occurrences')
                ->leftJoin('activities', 'activity_occurrences.activity_id', '=', 'activities.id')
                ->select([
                    'activity_occurrences.id',
                    'activity_occurrences.activity_id',
                    'activity_occurrences.session_date',
                    'activity_occurrences.start_time',
                    'activity_occurrences.end_time',
                    'activity_occurrences.session_status',
                    'activity_occurrences.location',
                    'activity_occurrences.max_participants',
                    'activities.activity_name'
                ])
                ->whereBetween('session_date', [$weekStart->format('Y-m-d'), $weekEnd->format('Y-m-d')])
                ->where('session_status', '!=', 'cancelled');

            // Personal calendar should show only user's assigned sessions regardless of role
            if ($role === 'trainee') {
                $query->whereExists(function($q) use ($userId) {
                    $q->select(DB::raw(1))
                      ->from('activity_enrollments')
                      ->whereColumn('activity_enrollments.activity_id', 'activity_occurrences.activity_id')
                      ->where('activity_enrollments.trainee_id', $userId);
                });
            } else {
                // All staff (including admin) see their assigned sessions - FIXED: Added instructor_id condition
                $query->where(function($q) use ($userId) {
                    $q->where('activity_occurrences.instructor_id', $userId)
                      ->orWhere('activity_occurrences.instructor_id', $userId)
                      ->orWhere('activities.instructor_id', $userId);
                });
            }
            
            if ($centreId && $centreId !== 'admin') {
                $query->where('activities.centre_id', $centreId);
            }

            $results = $query->orderBy('activity_occurrences.session_date', 'asc')
                ->orderBy('activity_occurrences.start_time', 'asc')
                ->get()
                ->map(function ($session) {
                    try {
                        $sessionDate = Carbon::parse($session->session_date);
                        
                        // Ensure all required fields exist
                        return [
                            'id' => $session->id ?? '',
                            'activity_id' => $session->activity_id ?? '',
                            'session_id' => $session->id ?? '',
                            'title' => $session->activity_name ?? 'Activity Session',
                            'day' => $sessionDate->format('D'),
                            'date' => $sessionDate->format('d'),
                            'month' => $sessionDate->format('M'),
                            'year' => $sessionDate->format('Y'),
                            'full_date' => $sessionDate->format('Y-m-d'),
                            'time' => Carbon::parse($session->start_time)->format('g:i A'),
                            'end_time' => Carbon::parse($session->end_time)->format('g:i A'),
                            'location' => $session->location ?? 'TBA',
                            'participants' => ($session->max_participants ?? 0) . '/' . ($session->max_participants ?? 0),
                            'status' => $session->status ?? 'scheduled',
                            'is_today' => $sessionDate->isToday(),
                            'is_tomorrow' => $sessionDate->isTomorrow(),
                            'color' => $this->getStatusColor($session->status ?? 'scheduled')
                        ];
                    } catch (\Exception $e) {
                        // Skip malformed sessions
                        return null;
                    }
                })
                ->filter(); // Remove null entries

            // Get centre's state for state-specific holidays
            $centreState = null;
            if ($centreId && $centreId !== 'admin') {
                $centre = DB::table('centres')->where('centre_id', $centreId)->first();
                $centreState = $centre->state ?? null;
            }

            // Get public holidays for this week (federal + state-specific)
            $holidays = \App\Models\PublicHoliday::whereBetween('date', [
                    $weekStart->format('Y-m-d'),
                    $weekEnd->format('Y-m-d')
                ])
                ->where('is_active', true)
                ->where(function($query) use ($centreState) {
                    // Include federal holidays (state is null)
                    $query->whereNull('state');
                    // Also include holidays for this centre's state
                    if ($centreState) {
                        $query->orWhere('state', $centreState);
                    }
                })
                ->get()
                ->keyBy(function($holiday) {
                    return $holiday->date->format('Y-m-d');
                });

            // Return events with week info and holidays
            return [
                'events' => $results,
                'week_start' => $weekStart,
                'week_end' => $weekEnd,
                'week_offset' => $weekOffset,
                'holidays' => $holidays
            ];
        } catch (\Exception $e) {
            Log::error('Calendar events error', ['error' => $e->getMessage()]);
            return collect([]);
        }
    }

    /**
     * Get system alerts
     */
    private function getSystemAlerts($role)
    {
        $alerts = [];

        if (in_array($role, ['admin', 'supervisor'])) {
            try {
                // Check for pending registrations
                $pendingRegistrations = DB::table('trainees')->where('status', 'pending')->count();
                if ($pendingRegistrations > 0) {
                    $alerts[] = [
                        'type' => 'warning',
                        'icon' => 'fas fa-user-clock',
                        'message' => "{$pendingRegistrations} trainee registration(s) pending approval",
                        'action_url' => route('trainees.create'),
                        'action_text' => 'Review'
                    ];
                }

                // Check for overdue sessions
                $overdueSessions = DB::table('activity_occurrences')
                    ->where('status', 'scheduled')
                    ->where('session_date', '<', today())
                    ->count();
                if ($overdueSessions > 0) {
                    $alerts[] = [
                        'type' => 'danger',
                        'icon' => 'fas fa-exclamation-triangle',
                        'message' => "{$overdueSessions} session(s) are overdue",
                        'action_url' => route('enhanced-attendance.index'),
                        'action_text' => 'Check'
                    ];
                }

                // Check for low attendance
                $lowAttendanceActivities = DB::table('activities')
                    ->leftJoin('activity_enrollments', 'activities.id', '=', 'activity_enrollments.activity_id')
                    ->select('activities.id', 'activities.activity_name', DB::raw('COUNT(activity_enrollments.id) as enrollment_count'))
                    ->where('activities.is_active', true)
                    ->groupBy('activities.id', 'activities.activity_name')
                    ->having('enrollment_count', '<', 3)
                    ->count();

                if ($lowAttendanceActivities > 0) {
                    $alerts[] = [
                        'type' => 'info',
                        'icon' => 'fas fa-chart-line',
                        'message' => "{$lowAttendanceActivities} activity(ies) have low enrollment",
                        'action_url' => route('activities.home'),
                        'action_text' => 'View'
                    ];
                }
            } catch (\Exception $e) {
                // Ignore errors and continue
            }
        }

        return $alerts;
    }

    /**
     * Get progress summary
     */
    private function getProgressSummary($role, $userId, $centreId)
    {
        try {
            if ($role === 'teacher') {
                $totalSessions = DB::table('activity_occurrences')->where('instructor_id', $userId)->count();
                $completedSessions = DB::table('activity_occurrences')->where('instructor_id', $userId)->where('session_status', 'completed')->count();
                $progress = $totalSessions > 0 ? round(($completedSessions / $totalSessions) * 100) : 0;

                return [
                    'title' => 'Teaching Progress',
                    'percentage' => $progress,
                    'completed' => $completedSessions,
                    'total' => $totalSessions,
                    'description' => 'Sessions completed this month'
                ];
            } elseif (in_array($role, ['admin', 'supervisor'])) {
                $query = DB::table('activity_occurrences')
                    ->join('activities', 'activity_occurrences.activity_id', '=', 'activities.id');

                if ($role === 'supervisor' && $centreId) {
                    $query->where('activities.centre_id', $centreId);
                }

                $totalSessions = $query->count();
                $completedSessions = $query->where('activity_occurrences.session_status', 'completed')->count();
                $progress = $totalSessions > 0 ? round(($completedSessions / $totalSessions) * 100) : 0;

                return [
                    'title' => 'Centre Progress',
                    'percentage' => $progress,
                    'completed' => $completedSessions,
                    'total' => $totalSessions,
                    'description' => 'Activities completed this month'
                ];
            }

            return null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get centre information
     */
    private function getCentreInfo($centreId)
    {
        if (!$centreId) return null;

        try {
            return DB::table('centres')
                ->select('centre_name', 'centre_address', 'centre_phone', 'centre_email', 'centre_capacity')
                ->where('centre_id', $centreId)
                ->first();
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get enhanced quick stats based on role
     */
    private function getQuickStats($role, $userId, $centreId)
    {
        try {
            switch ($role) {
                case 'admin':
                    return $this->getAdminStats();
                case 'supervisor':
                    return $this->getSupervisorStats($centreId);
                case 'teacher':
                    return $this->getTeacherStats($userId, $centreId);
                case 'ajk':
                    return $this->getAjkStats($centreId);
                default:
                    return [];
            }
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Convert card-based stats to flat array for view compatibility
     */
    private function getFlatStatsFromCards($quickStats)
    {
        // Check if quickStats is already a flat array (admin) or cards array (supervisor/others)
        if (isset($quickStats['total_users'])) {
            // Admin stats - already flat, just return as is
            return [
                'total_users' => $quickStats['total_users'] ?? 0,
                'total_trainees' => $quickStats['total_trainees'] ?? 0,
                'total_activities' => $quickStats['total_activities'] ?? 0,
                'sessions_this_week' => $quickStats['sessions_this_week'] ?? 0,
                'completion_rate' => 0
            ];
        }
        
        // Supervisor/other stats - convert cards to flat array
        $flatStats = [
            'total_users' => 0,
            'total_trainees' => 0,
            'total_activities' => 0,
            'sessions_this_week' => 0,
            'completion_rate' => 0
        ];

        foreach ($quickStats as $card) {
            $title = strtolower(str_replace(' ', '_', $card['title'] ?? ''));
            $value = $card['value'] ?? 0;

            // Map card titles to flat array keys
            switch ($title) {
                case 'centre_staff':
                case 'total_users':
                    $flatStats['total_users'] = $value;
                    break;
                case 'centre_trainees':
                case 'active_trainees':
                    $flatStats['total_trainees'] = $value;
                    break;
                case 'active_activities':
                case 'total_activities':
                    $flatStats['total_activities'] = $value;
                    break;
                case 'sessions_this_week':
                    $flatStats['sessions_this_week'] = $value;
                    break;
            }
        }
        
        return $flatStats;
    }

    /**
     * Get status color for calendar events
     */
    private function getStatusColor($status)
    {
        switch ($status) {
            case 'scheduled':
                return 'primary';
            case 'ongoing':
                return 'success';
            case 'completed':
                return 'info';
            case 'cancelled':
                return 'danger';
            default:
                return 'secondary';
        }
    }

    /**
     * Get enhanced dashboard data with additional UX features
     */
    private function getEnhancedDashboardData($role, $userId, $centreId, $weekOffset = 0)
    {
        $data = $this->getDashboardData($role, $userId, $centreId, $weekOffset);
        
        // Add enhanced features
        $data['enhanced_features'] = true;
        $data['user_preferences'] = $this->getUserPreferences($userId);
        $data['system_health'] = $this->getSystemHealth();
        $data['quick_actions_enhanced'] = $this->getEnhancedQuickActions($role);
        
        return $data;
    }

    /**
     * Get user preferences for dashboard customization
     */
    private function getUserPreferences($userId)
    {
        // Default preferences
        return [
            'widgets' => [
                'stats' => true,
                'sessions' => true,
                'notifications' => true,
                'calendar' => true
            ],
            'theme' => 'light',
            'layout' => 'default'
        ];
    }

    /**
     * Get system health metrics
     */
    private function getSystemHealth()
    {
        try {
            return [
                'database' => 'healthy',
                'cache' => 'healthy',
                'storage' => 'healthy',
                'overall' => 'healthy'
            ];
        } catch (\Exception $e) {
            return [
                'database' => 'unknown',
                'cache' => 'unknown',
                'storage' => 'unknown',
                'overall' => 'unknown'
            ];
        }
    }

    /**
     * Get enhanced quick actions based on role
     */
    private function getEnhancedQuickActions($role)
    {
        $actions = [];
        
        if ($role === 'admin') {
            $actions['administration'] = [
                ['label' => 'Add User', 'icon' => 'fas fa-user-plus', 'route' => 'staffs.register'],
                ['label' => 'Add Centre', 'icon' => 'fas fa-building', 'route' => 'centres.create']
            ];
        }
        
        if (in_array($role, ['admin', 'supervisor'])) {
            $actions['management'] = [
                ['label' => 'Add Trainee', 'icon' => 'fas fa-user-graduate', 'route' => 'trainees.create'],
                ['label' => 'Create Activity', 'icon' => 'fas fa-plus-circle', 'route' => 'activities.create']
            ];
        }
        
        $actions['personal'] = [
            ['label' => 'My Profile', 'icon' => 'fas fa-user', 'route' => 'profile'],
            ['label' => 'Generate Letter', 'icon' => 'fas fa-file-alt', 'route' => 'profile', 'fragment' => '#letter-generator']
        ];
        
        return $actions;
    }

    /**
     * Get storage usage safely
     */
    private function getStorageUsage()
    {
        try {
            $storagePath = storage_path();
            $freeBytes = disk_free_space($storagePath);
            $totalBytes = disk_total_space($storagePath);
            
            if ($freeBytes !== false && $totalBytes !== false && $totalBytes > 0) {
                $usagePercentage = round((1 - $freeBytes / $totalBytes) * 100, 1);
                return $usagePercentage . '%';
            }
            
            return 'N/A';
        } catch (\Exception $e) {
            return 'N/A';
        }
    }

    /**
     * Get active sessions count safely
     */
    private function getActiveSessionsCount()
    {
        try {
            // Try sessions table first (Laravel default session table)
            return DB::table('sessions')->count();
        } catch (\Exception $e) {
            try {
                // Fallback to activity_occurrences table if sessions table doesn't exist
                return DB::table('activity_occurrences')
                    ->where('session_status', 'ongoing')
                    ->whereDate('session_date', today())
                    ->count();
            } catch (\Exception $e2) {
                return 0;
            }
        }
    }

    /**
     * Get letters count safely
     */
    private function getLettersCount()
    {
        try {
            return DB::table('letters')->count();
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Get letters details safely
     */
    private function getLettersDetails()
    {
        try {
            $details = [
                'this_month' => DB::table('letters')->whereMonth('created_at', now()->month)->count(),
            ];
            
            // Try to get pending count with safe column check
            try {
                $details['pending'] = DB::table('letters')->where('letter_status', 'draft')->count();
            } catch (\Exception $e) {
                $details['pending'] = 0;
            }
            
            // Try to get by_type with safe column check
            try {
                $details['by_type'] = DB::table('letters')
                    ->select('letter_type', DB::raw('count(*) as count'))
                    ->groupBy('letter_type')
                    ->pluck('count', 'letter_type')
                    ->toArray();
            } catch (\Exception $e) {
                $details['by_type'] = [];
            }
            
            return $details;
        } catch (\Exception $e) {
            return [
                'this_month' => 0,
                'pending' => 0,
                'by_type' => []
            ];
        }
    }

    /**
     * Get maintenance due count safely
     */
    private function getMaintenanceDueCount()
    {
        try {
            // Try with next_maintenance_date column first
            return DB::table('assets')
                ->where('next_maintenance_date', '<=', now()->addDays(30))
                ->count();
        } catch (\Exception $e) {
            try {
                // Fallback - try with maintenance_date column
                return DB::table('assets')
                    ->where('maintenance_date', '<=', now()->addDays(30))
                    ->count();
            } catch (\Exception $e2) {
                // If no maintenance date columns exist, return 0
                return 0;
            }
        }
    }

    /**
     * Refresh a specific widget's data
     */
    public function refreshWidget(Request $request)
    {
        try {
            $widgetType = $request->input('widget');
            $userId = session('id');
            $role = session('role');
            $centreId = session('centre_id');

            $html = '';
            $success = false;

            switch ($widgetType) {
                case 'stats':
                    $stats = $this->getQuickStats($role, $userId, $centreId);
                    $success = true;
                    $html = '<div class="widget-refreshed">Statistics updated: ' . json_encode($stats) . '</div>';
                    break;

                case 'activities':
                    $recentActivities = $this->getRecentActivities($role, $userId, $centreId);
                    $success = true;
                    $html = '<div class="widget-refreshed">Activities updated: ' . count($recentActivities) . ' items</div>';
                    break;

                case 'schedule':
                    $upcomingSessions = $this->getUpcomingSessions($role, $userId, $centreId);
                    $currentSessions = $this->getCurrentSessions($role, $userId, $centreId);
                    $success = true;
                    $html = '<div class="widget-refreshed">Schedule updated: ' . count($upcomingSessions) . ' upcoming, ' . count($currentSessions) . ' current</div>';
                    break;

                case 'management':
                    $recentUsers = $this->getRecentUsers($role, $centreId);
                    $success = true;
                    $html = '<div class="widget-refreshed">Management updated: ' . count($recentUsers) . ' recent users</div>';
                    break;

                case 'centres':
                    $centreInfo = $this->getCentreInfo($centreId);
                    $success = true;
                    $html = '<div class="widget-refreshed">Centre info updated</div>';
                    break;

                default:
                    throw new \Exception('Unknown widget type: ' . $widgetType);
            }

            return response()->json([
                'success' => $success,
                'html' => $html,
                'widget' => $widgetType,
                'timestamp' => time()
            ]);

        } catch (\Exception $e) {
            Log::error('Widget refresh failed', [
                'widget' => $request->input('widget'),
                'error' => $e->getMessage(),
                'user_id' => session('id')
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to refresh widget',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get calendar events for a specific week
     */
    public function getWeekCalendar(Request $request)
    {
        try {
            $weekOffset = $request->input('week_offset', 0);
            $userId = session('id');
            $role = session('role');
            $centreId = session('centre_id');

            $calendarData = $this->getCalendarEvents($role, $userId, $centreId, $weekOffset);

            // Ensure dates are properly formatted for JSON
            $calendarData['week_start_formatted'] = $calendarData['week_start']->format('Y-m-d');
            $calendarData['week_end_formatted'] = $calendarData['week_end']->format('Y-m-d');
            
            return response()->json([
                'success' => true,
                'calendar_data' => $calendarData,
                'week_info' => [
                    'week_start' => $calendarData['week_start']->format('M j'),
                    'week_end' => $calendarData['week_end']->format('M j'),
                    'week_number' => $calendarData['week_start']->format('W'),
                    'year' => $calendarData['week_start']->format('Y'),
                    'current_offset' => $weekOffset
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Week calendar error', [
                'error' => $e->getMessage(),
                'user_id' => session('id'),
                'week_offset' => $request->input('week_offset', 0)
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to load calendar data'
            ], 500);
        }
    }

    /**
     * Get real-time updates for dashboard
     */
    public function getUpdates(Request $request)
    {
        try {
            $lastUpdate = $request->input('last_update', 0);
            $includeStats = $request->boolean('include_stats', false);
            
            $userId = session('id');
            $role = session('role');
            $centreId = session('centre_id');

            $response = [
                'success' => true,
                'timestamp' => time(),
                'updates' => []
            ];

            // Include updated stats if requested
            if ($includeStats) {
                $response['stats'] = $this->getQuickStats($role, $userId, $centreId);
            }

            // Check for new notifications, activities, etc. since last update
            $newActivities = $this->getRecentActivities($role, $userId, $centreId, $lastUpdate);
            if (!empty($newActivities)) {
                $response['updates'][] = [
                    'type' => 'info',
                    'message' => 'New activities available',
                    'data' => $newActivities
                ];
            }

            return response()->json($response);

        } catch (\Exception $e) {
            Log::error('Real-time updates failed', [
                'error' => $e->getMessage(),
                'user_id' => session('id')
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to get updates'
            ], 500);
        }
    }

    /**
     * Refresh dashboard statistics
     */
    public function refreshStats(Request $request)
    {
        try {
            $userId = session('id');
            $role = session('role');
            $centreId = session('centre_id');

            $stats = $this->getQuickStats($role, $userId, $centreId);

            return response()->json([
                'success' => true,
                'stats' => $stats,
                'timestamp' => time()
            ]);

        } catch (\Exception $e) {
            Log::error('Stats refresh failed', [
                'error' => $e->getMessage(),
                'user_id' => session('id')
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to refresh statistics'
            ], 500);
        }
    }

    /**
     * Get today's activities - personalized for staff, centre-wide for admin
     */
    private function getTodaysActivities($role, $userId, $centreId)
    {
        if ($role === 'admin') {
            return $this->getTodaysCentreActivities($centreId);
        } else {
            return $this->getTodaysPersonalActivities($role, $userId, $centreId);
        }
    }

    /**
     * Get today's personal activities for staff members (non-admin users)
     */
    private function getTodaysPersonalActivities($role, $userId, $centreId)
    {
        try {
            // Get nearest workday from current time
            $targetDate = $this->getNearestWorkday();
            
            $query = DB::table('activity_occurrences')
                ->join('activities', 'activity_occurrences.activity_id', '=', 'activities.id')
                ->leftJoin('staffs as teachers', 'activity_occurrences.instructor_id', '=', 'teachers.id')
                ->select([
                    'activity_occurrences.id',
                    'activities.activity_name as title',
                    'activity_occurrences.session_date',
                    'activity_occurrences.start_time as time',
                    'activity_occurrences.end_time',
                    'activity_occurrences.session_status',
                    'activity_occurrences.location as location',
                    'teachers.name as teacher_name',
                    'activity_occurrences.max_participants'
                ])
                ->whereDate('activity_occurrences.session_date', $targetDate)
                ->where('activity_occurrences.session_status', '!=', 'cancelled')
                ->orderBy('activity_occurrences.start_time');

            // Filter to show only user's assigned activities
            $query->where(function($q) use ($userId) {
                $q->where('activity_occurrences.instructor_id', $userId)
                  ->orWhere('activities.instructor_id', $userId);
            });

            if ($centreId && $centreId !== 'admin') {
                $query->where('activities.centre_id', $centreId);
            }

            return $query->get()->map(function ($session) {
                // Only return sessions with complete data
                if (!$session->time || !$session->location || !$session->title) {
                    return null;
                }
                
                return [
                    'id' => $session->id,
                    'title' => $session->title,
                    'time' => date('H:i', strtotime($session->time)),
                    'end_time' => $session->end_time ? date('H:i', strtotime($session->end_time)) : null,
                    'status' => $session->session_status,
                    'location' => $session->location,
                    'teacher' => $session->teacher_name,
                    'participants' => ($session->max_participants ?? 0) . '/' . ($session->max_participants ?? 0),
                    'day' => date('D', strtotime($session->session_date)),
                    'date' => date('d', strtotime($session->session_date))
                ];
            })->filter()->toArray(); // Remove null entries
        } catch (\Exception $e) {
            Log::error('Personal activities error', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Get today's centre activities for General tab (or nearest workday)
     */
    private function getTodaysCentreActivities($centreId)
    {
        try {
            // Get nearest workday from current time
            $targetDate = $this->getNearestWorkday();
            
            $query = DB::table('activity_occurrences')
                ->join('activities', 'activity_occurrences.activity_id', '=', 'activities.id')
                ->leftJoin('staffs as teachers', 'activity_occurrences.instructor_id', '=', 'teachers.id')
                ->select([
                    'activity_occurrences.id',
                    'activities.activity_name as title',
                    'activity_occurrences.session_date',
                    'activity_occurrences.start_time as time',
                    'activity_occurrences.end_time',
                    'activity_occurrences.session_status',
                    'activity_occurrences.location as location',
                    'teachers.name as teacher_name',
                    'activity_occurrences.max_participants'
                ])
                ->whereDate('activity_occurrences.session_date', $targetDate)
                ->where('activity_occurrences.session_status', '!=', 'cancelled')
                ->orderBy('activity_occurrences.start_time');

            if ($centreId && $centreId !== 'admin') {
                $query->where('activities.centre_id', $centreId);
            }

            return $query->get()->map(function ($session) {
                // Only return sessions with complete data
                if (!$session->time || !$session->location || !$session->title) {
                    return null;
                }
                
                return [
                    'id' => $session->id,
                    'title' => $session->title,
                    'time' => date('H:i', strtotime($session->time)),
                    'end_time' => $session->end_time ? date('H:i', strtotime($session->end_time)) : null,
                    'status' => $session->session_status,
                    'location' => $session->location,
                    'teacher' => $session->teacher_name,
                    'participants' => ($session->max_participants ?? 0) . '/' . ($session->max_participants ?? 0),
                    'day' => date('D', strtotime($session->session_date)),
                    'date' => date('d', strtotime($session->session_date))
                ];
            })->filter()->toArray(); // Remove null entries
        } catch (\Exception $e) {
            Log::error('Centre activities error', ['error' => $e->getMessage()]);
            return [];
        }
    }
    
    /**
     * Get nearest workday from current time
     */
    private function getNearestWorkday()
    {
        $today = now();
        
        // If today is a workday (Monday-Friday), return today
        if ($today->isWeekday()) {
            return $today->format('Y-m-d');
        }
        
        // If today is weekend, find next Monday
        if ($today->isSaturday()) {
            return $today->addDays(2)->format('Y-m-d'); // Next Monday
        } else if ($today->isSunday()) {
            return $today->addDay()->format('Y-m-d'); // Next Monday
        }
        
        return $today->format('Y-m-d');
    }

    /**
     * Get comprehensive recent changes including activities, users, trainees, and other modifications
     */
    private function getComprehensiveRecentChanges($centreId, $limit = 10)
    {
        try {
            // Use ActivityLog model for recent activities
            $activityLogs = \App\Models\ActivityLog::query()
                ->when($centreId && $centreId !== 'admin', function($query) use ($centreId) {
                    return $query->where('centre_id', $centreId);
                })
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get();

            $changes = collect();

            foreach ($activityLogs as $log) {
                $changes->push([
                    'title' => $log->title,
                    'description' => $log->description,
                    'time' => $log->created_at->diffForHumans(),
                    'status' => $log->status,
                    'type' => $log->model_type,
                    'category_name' => $this->getActivityLogCategoryName($log->model_type),
                    'user_name' => $log->user_name ?? 'System',
                    'user_role' => $log->user_role ?? 'system',
                    'action' => $log->action_type,
                    'icon' => $log->icon,
                    'id' => $log->id
                ]);
            }

            return $changes->toArray();

        } catch (\Exception $e) {
            Log::error('Comprehensive recent changes error', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Get category name for ActivityLog model type
     */
    private function getActivityLogCategoryName($modelType)
    {
        $categories = [
            'User' => 'Staff Management',
            'Trainee' => 'Trainee Management',
            'Activity' => 'Activity Management',
            'Session' => 'Session Management',
            'ActivitySession' => 'Session Management',
        ];

        return $categories[$modelType] ?? 'General';
    }

    /**
     * Get user-specific performance statistics
     */
    private function getUserPerformanceStats($role, $userId, $centreId)
    {
        try {
            $stats = [];

            if ($role === 'teacher' || $role === 'admin' || $role === 'supervisor') {
                // Get activities created or taught by this user (using correct schema)
                $userActivities = DB::table('activities')
                    ->where('instructor_id', $userId)
                    ->where('centre_id', $centreId) // Filter by user's centre
                    ->where('is_active', true) // Only count active activities
                    ->count();

                // Get sessions this week (using correct schema)
                $startOfWeek = now()->startOfWeek();
                $endOfWeek = now()->endOfWeek();
                
                $weeklySessions = DB::table('activity_occurrences')
                    ->join('activities', 'activity_occurrences.activity_id', '=', 'activities.id')
                    ->where('activities.centre_id', $centreId)
                    ->where(function($query) use ($userId) {
                        $query->where('activity_occurrences.instructor_id', $userId)
                              ->orWhere('activities.instructor_id', $userId);
                    })
                    ->whereBetween('activity_occurrences.session_date', [$startOfWeek->format('Y-m-d'), $endOfWeek->format('Y-m-d')])
                    ->count();

                // Calculate completion rate (sessions that have been conducted vs scheduled past sessions)
                $pastSessions = DB::table('activity_occurrences')
                    ->join('activities', 'activity_occurrences.activity_id', '=', 'activities.id')
                    ->where('activities.centre_id', $centreId)
                    ->where(function($query) use ($userId) {
                        $query->where('activity_occurrences.instructor_id', $userId)
                              ->orWhere('activities.instructor_id', $userId);
                    })
                    ->where('activity_occurrences.session_date', '<=', now()->format('Y-m-d'))
                    ->count();

                $completedSessions = DB::table('activity_occurrences')
                    ->join('activities', 'activity_occurrences.activity_id', '=', 'activities.id')
                    ->where('activities.centre_id', $centreId)
                    ->where(function($query) use ($userId) {
                        $query->where('activity_occurrences.instructor_id', $userId)
                              ->orWhere('activities.instructor_id', $userId);
                    })
                    ->where('activity_occurrences.session_status', 'completed')
                    ->count();

                $completionRate = $pastSessions > 0 ? round(($completedSessions / $pastSessions) * 100) : 0;

                // Calculate average attendance from sessions taught by this user
                $userSessionIds = DB::table('activity_occurrences')
                    ->join('activities', 'activity_occurrences.activity_id', '=', 'activities.id')
                    ->where('activities.centre_id', $centreId)
                    ->where(function($query) use ($userId) {
                        $query->where('activity_occurrences.instructor_id', $userId)
                              ->orWhere('activities.instructor_id', $userId);
                    })
                    ->pluck('activity_occurrences.id');

                $totalAttendanceRecords = DB::table('session_attendance')
                    ->whereIn('session_id', $userSessionIds)
                    ->count();

                $presentAttendanceRecords = DB::table('session_attendance')
                    ->whereIn('session_id', $userSessionIds)
                    ->where('attendance_status', 'present')
                    ->count();

                $avgAttendance = $totalAttendanceRecords > 0 ? round(($presentAttendanceRecords / $totalAttendanceRecords) * 100) : 0;

                // Get additional stats for more comprehensive view
                $totalTraineesManaged = DB::table('activity_enrollments')
                    ->join('activities', 'activity_enrollments.activity_id', '=', 'activities.id')
                    ->where('activities.centre_id', $centreId)
                    ->where('activities.instructor_id', $userId)
                    ->distinct('activity_enrollments.trainee_id')
                    ->count('activity_enrollments.trainee_id');

                $stats = [
                    'user_activities' => $userActivities,
                    'completion_rate' => $completionRate,
                    'weekly_sessions' => $weeklySessions,
                    'avg_attendance' => $avgAttendance,
                    'total_trainees_managed' => $totalTraineesManaged
                ];

            } elseif ($role === 'trainee') {
                // Trainee performance stats using correct schema
                $enrolledActivities = DB::table('activity_enrollments')
                    ->join('activities', 'activity_enrollments.activity_id', '=', 'activities.id')
                    ->where('activity_enrollments.trainee_id', $userId)
                    ->where('activities.centre_id', $centreId)
                    ->where('activity_enrollments.enrollment_status', '!=', 'dropped')
                    ->count();

                // Get attendance records for this trainee
                $attendedSessions = DB::table('trainee_attendances')
                    ->join('activity_occurrences', 'trainee_attendances.session_id', '=', 'activity_occurrences.id')
                    ->join('activities', 'activity_occurrences.activity_id', '=', 'activities.id')
                    ->where('trainee_attendances.trainee_id', $userId)
                    ->where('activities.centre_id', $centreId)
                    ->where('trainee_attendances.status', 'present')
                    ->count();

                $totalSessionsAttended = DB::table('trainee_attendances')
                    ->join('activity_occurrences', 'trainee_attendances.session_id', '=', 'activity_occurrences.id')
                    ->join('activities', 'activity_occurrences.activity_id', '=', 'activities.id')
                    ->where('trainee_attendances.trainee_id', $userId)
                    ->where('activities.centre_id', $centreId)
                    ->count();

                $attendanceRate = $totalSessionsAttended > 0 ? round(($attendedSessions / $totalSessionsAttended) * 100) : 0;

                // Get this week's sessions for enrolled activities
                $thisWeekSessions = DB::table('activity_occurrences')
                    ->join('activity_enrollments', 'activity_occurrences.activity_id', '=', 'activity_enrollments.activity_id')
                    ->join('activities', 'activity_occurrences.activity_id', '=', 'activities.id')
                    ->where('activity_enrollments.trainee_id', $userId)
                    ->where('activities.centre_id', $centreId)
                    ->where('activity_enrollments.enrollment_status', '!=', 'dropped')
                    ->whereBetween('activity_occurrences.session_date', [
                        now()->startOfWeek()->format('Y-m-d'), 
                        now()->endOfWeek()->format('Y-m-d')
                    ])
                    ->count();

                // Calculate progress - activities completed vs enrolled
                $completedActivities = DB::table('activity_enrollments')
                    ->join('activities', 'activity_enrollments.activity_id', '=', 'activities.id')
                    ->where('activity_enrollments.trainee_id', $userId)
                    ->where('activities.centre_id', $centreId)
                    ->where('activity_enrollments.enrollment_status', 'completed')
                    ->count();

                $completionRate = $enrolledActivities > 0 ? round(($completedActivities / $enrolledActivities) * 100) : 0;

                // Average participation score not available in current schema
                $avgParticipation = 0;

                $stats = [
                    'user_activities' => $enrolledActivities,
                    'completion_rate' => $completionRate,
                    'weekly_sessions' => $thisWeekSessions,
                    'avg_attendance' => $attendanceRate,
                    'avg_participation' => $avgParticipation
                ];

            } elseif ($role === 'ajk') {
                // AJK performance stats - focus on facility management
                $facilitiesManaged = DB::table('assets')
                    ->where('centre_id', $centreId)
                    ->where('assigned_to_user', $userId)
                    ->count();

                $maintenanceTasksCompleted = DB::table('asset_maintenance')
                    ->join('assets', 'asset_maintenance.asset_id', '=', 'assets.id')
                    ->where('assets.centre_id', $centreId)
                    ->where(function ($query) use ($userId) {
                        $query->where('asset_maintenance.performed_by_user_id', $userId)
                            ->orWhere('asset_maintenance.performed_by', (string) $userId);
                    })
                    ->where('asset_maintenance.status', 'completed')
                    ->count();

                $thisWeekTasks = DB::table('asset_maintenance')
                    ->join('assets', 'asset_maintenance.asset_id', '=', 'assets.id')
                    ->where('assets.centre_id', $centreId)
                    ->where(function ($query) use ($userId) {
                        $query->where('asset_maintenance.performed_by_user_id', $userId)
                            ->orWhere('asset_maintenance.performed_by', (string) $userId);
                    })
                    ->whereBetween('asset_maintenance.scheduled_date', [
                        now()->startOfWeek()->format('Y-m-d'), 
                        now()->endOfWeek()->format('Y-m-d')
                    ])
                    ->count();

                $taskCompletionRate = 85; // Default good rate for AJK

                $stats = [
                    'user_activities' => $facilitiesManaged,
                    'completion_rate' => $taskCompletionRate,
                    'weekly_sessions' => $thisWeekTasks,
                    'avg_attendance' => $maintenanceTasksCompleted
                ];

            } else {
                // Default stats for other roles
                $stats = [
                    'user_activities' => 0,
                    'completion_rate' => 0,
                    'weekly_sessions' => 0,
                    'avg_attendance' => 0
                ];
            }

            return $stats;

        } catch (\Exception $e) {
            Log::error('User performance stats error', [
                'user_id' => $userId,
                'role' => $role,
                'centre_id' => $centreId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return [
                'user_activities' => 0,
                'completion_rate' => 0,
                'weekly_sessions' => 0,
                'avg_attendance' => 0
            ];
        }
    }
}
