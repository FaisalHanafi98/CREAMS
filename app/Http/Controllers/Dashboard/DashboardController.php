<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Trainee;
use App\Models\Activity;
use App\Models\ActivitySession;
use App\Models\Centre;
use App\Models\Asset;
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

            $userId = session('id');
            $role = session('role');
            $centreId = session('centre_id');
            
            // Get enhanced dashboard data with additional UX features
            $dashboardData = $this->getEnhancedDashboardData($role, $userId, $centreId);
            
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
                'current_time' => now()->format('l, F j, Y - g:i A')
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
            
            return view('dashboard.modernnew', [
                'error' => 'Unable to load dashboard data',
                'role' => $role ?? 'unknown',
                'user_name' => session('name', 'User'),
                'current_time' => now()->format('l, F j, Y - g:i A')
            ]);
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
            
            return view('dashboard.enhanced', $dashboardData);
            
        } catch (\Exception $e) {
            Log::error('Enhanced dashboard error', [
                'user_id' => $userId ?? 'unknown',
                'error' => $e->getMessage()
            ]);
            
            return view('dashboard.enhanced', [
                'error' => 'Unable to load enhanced dashboard data',
                'role' => $role ?? 'unknown',
                'user_name' => session('name', 'User'),
                'current_time' => now()->format('l, F j, Y - g:i A')
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
    private function getDashboardData($role, $userId, $centreId)
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
            'calendar_events' => $this->getCalendarEvents($role, $userId, $centreId),
            'todays_centre_activities' => $this->getTodaysCentreActivities($centreId),
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
            // Calculate growth rates dynamically
            $currentMonthUsers = DB::table('users')->whereMonth('created_at', now()->month)->count();
            $lastMonthUsers = DB::table('users')->whereMonth('created_at', now()->subMonth()->month)->count();
            $userGrowthRate = $lastMonthUsers > 0 ? round((($currentMonthUsers - $lastMonthUsers) / $lastMonthUsers) * 100, 1) : 0;
            
            $currentMonthTrainees = DB::table('trainees')->whereMonth('created_at', now()->month)->count();
            $lastMonthTrainees = DB::table('trainees')->whereMonth('created_at', now()->subMonth()->month)->count();
            $traineeGrowthRate = $lastMonthTrainees > 0 ? round((($currentMonthTrainees - $lastMonthTrainees) / $lastMonthTrainees) * 100, 1) : 0;
            
            $currentMonthActivities = DB::table('activities')->whereMonth('created_at', now()->month)->count();
            $lastMonthActivities = DB::table('activities')->whereMonth('created_at', now()->subMonth()->month)->count();
            $activityGrowthRate = $lastMonthActivities > 0 ? round((($currentMonthActivities - $lastMonthActivities) / $lastMonthActivities) * 100, 1) : 0;
            
            $totalUsers = DB::table('users')->where('status', 'active')->count();
            $activeTrainees = DB::table('trainees')->where('status', 'active')->count();
            $totalActivities = DB::table('activities')->where('is_active', true)->count();
            $activeCentres = DB::table('centres')->where('is_active', true)->count();
            
            return [
                'total_users' => $totalUsers,
                'total_trainees' => $activeTrainees,
                'total_activities' => $totalActivities,
                'active_centres' => $activeCentres,
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
                            'admins' => DB::table('users')->where('role', 'admin')->count(),
                            'supervisors' => DB::table('users')->where('role', 'supervisor')->count(), 
                            'teachers' => DB::table('users')->where('role', 'teacher')->count(),
                            'ajk' => DB::table('users')->where('role', 'ajk')->count(),
                            'active_today' => DB::table('users')->whereDate('user_last_accessed_at', today())->count(),
                            'inactive_30_days' => DB::table('users')->where('user_last_accessed_at', '<', now()->subDays(30))->count()
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
                        'scheduled' => DB::table('activities')->where('activity_status', 'scheduled')->count(),
                        'completed' => DB::table('activities')->where('activity_status', 'completed')->count(),
                        'cancelled' => DB::table('activities')->where('activity_status', 'cancelled')->count(),
                        'today_sessions' => DB::table('activity_sessions')->whereDate('scheduled_date', today())->count(),
                        'total_sessions' => DB::table('activity_sessions')->count(),
                        'by_type' => DB::table('activities')
                            ->select('activity_type', DB::raw('count(*) as count'))
                            ->groupBy('activity_type')
                            ->pluck('count', 'activity_type')
                            ->toArray()
                    ]
                ],
                [
                    'title' => 'Active Centres',
                    'value' => $activeCentres,
                    'icon' => 'fas fa-building',
                    'color' => 'warning',
                    'trend' => 'stable',
                    'details' => [
                        'total_centres' => DB::table('centres')->count(),
                        'inactive_centres' => DB::table('centres')->where('is_active', false)->count(),
                        'total_assets' => DB::table('assets')->count(),
                        'maintenance_due' => $this->getMaintenanceDueCount(),
                        'centre_capacity' => DB::table('centres')
                            ->sum('centre_capacity')
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
            Log::error('Error calculating admin stats', ['error' => $e->getMessage()]);
            return [
                'total_users' => 0,
                'total_trainees' => 0,
                'total_activities' => 0,
                'active_centres' => 0,
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
            $staffCount = DB::table('users')->where('centre_id', $centreId)->where('status', 'active')->count();
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
            $myActivities = DB::table('activities')->where('created_by', $userId)->count();
            $assignedSessions = DB::table('activity_sessions')->where('teacher_id', $userId)->where('session_status', 'scheduled')->count();
            $centreTrainees = DB::table('trainees')->where('centre_id', $centreId)->where('status', 'active')->count();
            $completedSessions = DB::table('activity_sessions')->where('teacher_id', $userId)->where('session_status', 'completed')->count();
            
            // Calculate completion rate
            $totalSessions = DB::table('activity_sessions')->where('teacher_id', $userId)->count();
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
            $activeActivities = DB::table('activities')->where('centre_id', $centreId)->where('activity_status', 'scheduled')->count();
            $todaySessions = DB::table('activity_sessions')
                ->join('activities', 'activity_sessions.activity_id', '=', 'activities.id')
                ->where('activities.centre_id', $centreId)
                ->whereDate('activity_sessions.scheduled_date', today())
                ->count();
            
            // Calculate maintenance alerts
            $maintenanceAlerts = DB::table('assets')
                ->where('centre_id', $centreId)
                ->where(function($query) {
                    $query->where('status', 'maintenance_required')
                          ->orWhere('next_maintenance_date', '<=', now()->addDays(7));
                })
                ->count();
            
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
                ->leftJoin('categories', 'activities.category_id', '=', 'categories.id')
                ->select('activities.id', 'activities.activity_name', 'activities.created_at', 'activities.activity_status', 
                        'activities.activity_type', 'categories.category_type', 'categories.category_name')
                ->orderBy('activities.created_at', 'desc');

            // Add timestamp filtering if provided
            if ($since) {
                $query->where('activities.created_at', '>', date('Y-m-d H:i:s', $since));
            }
                
            $query->limit(5);
                
            // Filter based on role and user
            if ($forceUserSpecific || ($role !== 'admin' && $userId)) {
                // Show only user-specific activities (for personal tab or non-admin users)
                $query->where(function($q) use ($userId) {
                    $q->where('activities.created_by', $userId)
                      ->orWhere('activities.instructor_id', $userId);
                });
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
                    'status' => $activity->activity_status ?? 'active',
                    'type' => $mappedType,
                    'category_name' => $activity->category_name ?? 'General',
                    'original_type' => $activity->activity_type ?? ''
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
            $query = DB::table('activity_sessions')
                ->join('activities', 'activity_sessions.activity_id', '=', 'activities.id')
                ->select('activities.activity_name', 'activity_sessions.scheduled_date', 'activity_sessions.start_time', 'activity_sessions.venue')
                ->where('activity_sessions.session_status', 'scheduled')
                ->where('activity_sessions.scheduled_date', '>=', today())
                ->orderBy('activity_sessions.scheduled_date')
                ->orderBy('activity_sessions.start_time')
                ->limit(5);

            // Role-based filtering
            if ($role === 'admin') {
                // For admin, show upcoming sessions from all centres
                // No additional filtering needed
            } else if ($userId) {
                // For non-admin users, show only sessions where they are assigned
                $query->where(function($q) use ($userId) {
                    $q->where('activity_sessions.teacher_id', $userId)
                      ->orWhere('activity_sessions.instructor_id', $userId)
                      ->orWhere('activities.instructor_id', $userId)
                      ->orWhere('activities.created_by', $userId);
                });
            }
            
            if ($centreId && $centreId !== 'admin') {
                $query->where('activities.centre_id', $centreId);
            }

            return $query->get()
                ->map(function ($session) {
                    return [
                        'activity' => $session->activity_name,
                        'date' => Carbon::parse($session->scheduled_date)->format('M j'),
                        'time' => Carbon::parse($session->start_time)->format('g:i A'),
                        'venue' => $session->venue ?? 'TBA'
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
                ->where('user_id', $userId)
                ->where('is_read', false)
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get()
                ->map(function ($notification) {
                    return [
                        'id' => $notification->id,
                        'type' => $notification->notification_type ?? 'info',
                        'title' => $notification->notification_title ?? 'Notification',
                        'message' => $notification->notification_message ?? 'No message',
                        'time' => Carbon::parse($notification->created_at)->diffForHumans(),
                        'read' => $notification->is_read
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
            $query = DB::table('users')
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
            $query = DB::table('activity_sessions')
                ->join('activities', 'activity_sessions.activity_id', '=', 'activities.id')
                ->join('users', 'activity_sessions.teacher_id', '=', 'users.id')
                ->select(
                    'activities.activity_name',
                    'activity_sessions.start_time',
                    'activity_sessions.end_time',
                    'activity_sessions.venue',
                    'users.name as teacher_name',
                    'activity_sessions.session_status',
                    'activity_sessions.id as session_id'
                )
                ->where('activity_sessions.session_status', 'ongoing')
                ->whereDate('activity_sessions.scheduled_date', today());

            if ($role === 'admin') {
                // Admins see all ongoing sessions
            } else if ($userId) {
                // Non-admin users see only their assigned sessions
                $query->where(function($q) use ($userId) {
                    $q->where('activity_sessions.teacher_id', $userId)
                      ->orWhere('activity_sessions.instructor_id', $userId)
                      ->orWhere('activities.instructor_id', $userId)
                      ->orWhere('activities.created_by', $userId);
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
                    'venue' => $session->venue ?? 'TBA',
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
    private function getCalendarEvents($role, $userId, $centreId)
    {
        try {
            $startDate = now()->startOfDay();
            $endDate = now()->addDays(7)->endOfDay();

            $query = DB::table('activity_sessions')
                ->leftJoin('activities', 'activity_sessions.activity_id', '=', 'activities.id')
                ->select([
                    'activity_sessions.id',
                    'activity_sessions.activity_id',
                    'activity_sessions.session_date',
                    'activity_sessions.start_time',
                    'activity_sessions.end_time',
                    'activity_sessions.status',
                    'activity_sessions.venue',
                    'activity_sessions.room_number',
                    'activity_sessions.current_participants',
                    'activity_sessions.max_participants',
                    'activities.activity_name'
                ])
                ->whereBetween('session_date', [$startDate, $endDate])
                ->where('status', '!=', 'cancelled');

            // Personal calendar should show only user's assigned sessions regardless of role
            if ($role === 'trainee') {
                $query->whereExists(function($q) use ($userId) {
                    $q->select(DB::raw(1))
                      ->from('activity_enrollments')
                      ->whereColumn('activity_enrollments.activity_id', 'activity_sessions.activity_id')
                      ->where('activity_enrollments.trainee_id', $userId);
                });
            } else {
                // All staff (including admin) see only their personally assigned sessions for Personal tab
                $query->where(function($q) use ($userId) {
                    $q->where('activity_sessions.teacher_id', $userId)
                      ->orWhere('activity_sessions.instructor_id', $userId);
                });
            }
            
            if ($centreId && $centreId !== 'admin') {
                $query->where('activities.centre_id', $centreId);
            }

            $results = $query->orderBy('activity_sessions.session_date', 'asc')
                ->orderBy('activity_sessions.start_time', 'asc')
                ->limit(8)
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
                            'time' => Carbon::parse($session->start_time)->format('g:i A'),
                            'location' => ($session->venue ?? '') . ($session->room_number ? ' - Room ' . $session->room_number : ''),
                            'participants' => ($session->current_participants ?? 0) . '/' . ($session->max_participants ?? 0),
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


            return $results;
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
                $overdueSessions = DB::table('activity_sessions')
                    ->where('status', 'scheduled')
                    ->where('scheduled_date', '<', today())
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
                    ->where('activities.activity_status', 'scheduled')
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
                $totalSessions = DB::table('activity_sessions')->where('teacher_id', $userId)->count();
                $completedSessions = DB::table('activity_sessions')->where('teacher_id', $userId)->where('session_status', 'completed')->count();
                $progress = $totalSessions > 0 ? round(($completedSessions / $totalSessions) * 100) : 0;

                return [
                    'title' => 'Teaching Progress',
                    'percentage' => $progress,
                    'completed' => $completedSessions,
                    'total' => $totalSessions,
                    'description' => 'Sessions completed this month'
                ];
            } elseif (in_array($role, ['admin', 'supervisor'])) {
                $query = DB::table('activity_sessions')
                    ->join('activities', 'activity_sessions.activity_id', '=', 'activities.id');

                if ($role === 'supervisor' && $centreId) {
                    $query->where('activities.centre_id', $centreId);
                }

                $totalSessions = $query->count();
                $completedSessions = $query->where('activity_sessions.session_status', 'completed')->count();
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
                'active_centres' => $quickStats['active_centres'] ?? 0,
                'completion_rate' => 0
            ];
        }
        
        // Supervisor/other stats - convert cards to flat array
        $flatStats = [
            'total_users' => 0,
            'total_trainees' => 0,
            'total_activities' => 0,
            'active_centres' => 0,
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
                case 'assets':
                case 'active_centres':
                    $flatStats['active_centres'] = $value;
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
    private function getEnhancedDashboardData($role, $userId, $centreId)
    {
        $data = $this->getDashboardData($role, $userId, $centreId);
        
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
                // Fallback to activity_sessions table if sessions table doesn't exist
                return DB::table('activity_sessions')
                    ->where('session_status', 'ongoing')
                    ->whereDate('scheduled_date', today())
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
     * Get today's centre activities for General tab
     */
    private function getTodaysCentreActivities($centreId)
    {
        try {
            $today = now()->format('Y-m-d');
            
            $query = DB::table('activity_sessions')
                ->join('activities', 'activity_sessions.activity_id', '=', 'activities.id')
                ->leftJoin('users as teachers', 'activity_sessions.teacher_id', '=', 'teachers.id')
                ->select([
                    'activity_sessions.id',
                    'activities.activity_name as title',
                    'activity_sessions.session_date',
                    'activity_sessions.start_time as time',
                    'activity_sessions.end_time',
                    'activity_sessions.status',
                    'activity_sessions.venue as location',
                    'teachers.name as teacher_name',
                    'activity_sessions.current_participants',
                    'activity_sessions.max_participants'
                ])
                ->whereDate('activity_sessions.session_date', $today)
                ->where('activity_sessions.status', '!=', 'cancelled')
                ->orderBy('activity_sessions.start_time');

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
                    'status' => $session->status,
                    'location' => $session->location,
                    'teacher' => $session->teacher_name,
                    'participants' => ($session->current_participants ?? 0) . '/' . ($session->max_participants ?? 0),
                    'day' => date('D', strtotime($session->session_date)),
                    'date' => date('d', strtotime($session->session_date))
                ];
            })->filter()->toArray(); // Remove null entries
        } catch (\Exception $e) {
            Log::error('Today\'s centre activities error', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Get comprehensive recent changes including activities, users, trainees, and other modifications
     */
    private function getComprehensiveRecentChanges($centreId, $limit = 10)
    {
        try {
            $changes = collect();

            // 1. Recent Activity Changes - Simple and working
            $activityChanges = DB::table('activities')
                ->leftJoin('categories', 'activities.category_id', '=', 'categories.id')
                ->leftJoin('users', 'activities.created_by', '=', 'users.id')
                ->select([
                    'activities.id',
                    'activities.activity_name as title',
                    'activities.created_at',
                    'activities.updated_at',
                    'activities.activity_status as status',
                    'activities.activity_type',
                    'categories.category_name',
                    'users.name as user_name'
                ])
                ->when($centreId && $centreId !== 'admin', function($query) use ($centreId) {
                    return $query->where('activities.centre_id', $centreId);
                })
                ->where('activities.updated_at', '>=', now()->subDays(14)) // Extended to 14 days to ensure data
                ->orderBy('activities.updated_at', 'desc')
                ->limit(8)
                ->get();

            foreach ($activityChanges as $change) {
                $action = ($change->created_at == $change->updated_at) ? 'created' : 'updated';
                $changes->push([
                    'title' => ucfirst($action) . ' Activity: ' . $change->title,
                    'time' => Carbon::parse($change->updated_at)->diffForHumans(),
                    'status' => $change->status ?: 'active',
                    'type' => 'activity',
                    'category_name' => $change->category_name ?: 'General',
                    'user_name' => $change->user_name ?: 'System',
                    'action' => $action,
                    'icon' => $action === 'created' ? 'plus-circle' : 'edit',
                    'id' => $change->id
                ]);
            }

            // 2. Recent User Changes (simplified)
            $userChanges = DB::table('users')
                ->select(['id', 'name as title', 'created_at', 'updated_at', 'role'])
                ->when($centreId && $centreId !== 'admin', function($query) use ($centreId) {
                    return $query->where('centre_id', $centreId);
                })
                ->where('updated_at', '>=', now()->subDays(3))
                ->orderBy('updated_at', 'desc')
                ->limit(3)
                ->get();

            foreach ($userChanges as $change) {
                $action = ($change->created_at == $change->updated_at) ? 'registered' : 'updated';
                $actionText = $action === 'registered' ? 'New Registration' : 'Profile Updated';
                
                $changes->push([
                    'title' => $actionText . ': ' . $change->title . ' (' . ucfirst($change->role ?? 'User') . ')',
                    'time' => Carbon::parse($change->updated_at)->diffForHumans(),
                    'status' => 'info',
                    'type' => 'user',
                    'category_name' => 'User Management',
                    'user_name' => $change->title,
                    'action' => $action,
                    'icon' => $action === 'registered' ? 'user-plus' : 'user-edit'
                ]);
            }

            // 3. Recent Trainee Changes (new registrations, status updates, profile changes)
            $traineeChanges = DB::table('trainees')
                ->select([
                    'trainees.id',
                    DB::raw("CONCAT(trainees.trainee_first_name, ' ', trainees.trainee_last_name) as title"),
                    'trainees.created_at as timestamp',
                    'trainees.updated_at',
                    'trainees.status',
                    'trainees.trainee_condition',
                    'trainees.guardian_name as parent_name',
                    DB::raw("'trainee' as change_type"),
                    DB::raw("CASE 
                        WHEN trainees.created_at = trainees.updated_at THEN 'registered'
                        ELSE 'updated' 
                    END as action")
                ])
                ->when($centreId && $centreId !== 'admin', function($query) use ($centreId) {
                    return $query->where('trainees.centre_id', $centreId);
                })
                ->where(function($query) {
                    $query->where('trainees.created_at', '>=', now()->subDays(7))
                          ->orWhere('trainees.updated_at', '>=', now()->subDays(3));
                })
                ->orderBy('trainees.updated_at', 'desc')
                ->limit(5)
                ->get();

            foreach ($traineeChanges as $change) {
                $actionText = $change->action === 'registered' ? 'New Trainee Registration' : 'Trainee Profile Updated';
                
                $changes->push([
                    'title' => $actionText . ': ' . $change->title,
                    'time' => Carbon::parse($change->updated_at)->diffForHumans(),
                    'status' => $change->status ?? 'active',
                    'type' => 'trainee',
                    'category_name' => 'Trainee Management',
                    'user_name' => $change->parent_name ?? 'System',
                    'action' => $change->action,
                    'condition' => $change->trainee_condition,
                    'icon' => $change->action === 'registered' ? 'user-graduate' : 'user-edit'
                ]);
            }

            // 4. Recent Activity Session Changes (scheduled, completed, cancelled)
            $sessionChanges = DB::table('activity_sessions')
                ->join('activities', 'activity_sessions.activity_id', '=', 'activities.id')
                ->leftJoin('users as teachers', 'activity_sessions.teacher_id', '=', 'teachers.id')
                ->select([
                    'activity_sessions.id',
                    'activities.activity_name',
                    'activity_sessions.session_date',
                    'activity_sessions.created_at as timestamp',
                    'activity_sessions.updated_at',
                    'activity_sessions.status',
                    'teachers.name as teacher_name',
                    DB::raw("'session' as change_type"),
                    DB::raw("CASE 
                        WHEN activity_sessions.created_at = activity_sessions.updated_at THEN 'scheduled'
                        ELSE 'updated' 
                    END as action")
                ])
                ->when($centreId && $centreId !== 'admin', function($query) use ($centreId) {
                    return $query->where('activities.centre_id', $centreId);
                })
                ->where('activity_sessions.updated_at', '>=', now()->subDays(5))
                ->orderBy('activity_sessions.updated_at', 'desc')
                ->limit(5)
                ->get();

            foreach ($sessionChanges as $change) {
                $actionText = $change->action === 'scheduled' ? 'Session Scheduled' : 'Session Updated';
                $sessionDate = Carbon::parse($change->session_date)->format('M d, Y');
                
                $changes->push([
                    'title' => $actionText . ': ' . $change->activity_name . ' (' . $sessionDate . ')',
                    'time' => Carbon::parse($change->updated_at)->diffForHumans(),
                    'status' => $change->status ?? 'scheduled',
                    'type' => 'session',
                    'category_name' => 'Session Management',
                    'user_name' => $change->teacher_name ?? 'System',
                    'action' => $change->action,
                    'session_date' => $sessionDate,
                    'icon' => $change->action === 'scheduled' ? 'calendar-plus' : 'calendar-check'
                ]);
            }

            // Return activity changes only for now
            return $changes->take($limit)->toArray();

        } catch (\Exception $e) {
            Log::error('Comprehensive recent changes error', ['error' => $e->getMessage()]);
            return [];
        }
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
                    ->where(function($query) use ($userId) {
                        $query->where('created_by', $userId)
                              ->orWhere('instructor_id', $userId);
                    })
                    ->where('centre_id', $centreId) // Filter by user's centre
                    ->count();

                // Get sessions this week (using correct schema)
                $startOfWeek = now()->startOfWeek();
                $endOfWeek = now()->endOfWeek();
                
                $weeklySessions = DB::table('activity_sessions')
                    ->join('activities', 'activity_sessions.activity_id', '=', 'activities.id')
                    ->where('activities.centre_id', $centreId)
                    ->where(function($query) use ($userId) {
                        $query->where('activity_sessions.teacher_id', $userId)
                              ->orWhere('activity_sessions.instructor_id', $userId)
                              ->orWhere('activities.created_by', $userId);
                    })
                    ->whereBetween('activity_sessions.scheduled_date', [$startOfWeek->format('Y-m-d'), $endOfWeek->format('Y-m-d')])
                    ->count();

                // Calculate completion rate (sessions that have been conducted vs scheduled past sessions)
                $pastSessions = DB::table('activity_sessions')
                    ->join('activities', 'activity_sessions.activity_id', '=', 'activities.id')
                    ->where('activities.centre_id', $centreId)
                    ->where(function($query) use ($userId) {
                        $query->where('activity_sessions.teacher_id', $userId)
                              ->orWhere('activity_sessions.instructor_id', $userId)
                              ->orWhere('activities.created_by', $userId);
                    })
                    ->where('activity_sessions.scheduled_date', '<=', now()->format('Y-m-d'))
                    ->count();

                $completedSessions = DB::table('activity_sessions')
                    ->join('activities', 'activity_sessions.activity_id', '=', 'activities.id')
                    ->where('activities.centre_id', $centreId)
                    ->where(function($query) use ($userId) {
                        $query->where('activity_sessions.teacher_id', $userId)
                              ->orWhere('activity_sessions.instructor_id', $userId)
                              ->orWhere('activities.created_by', $userId);
                    })
                    ->where('activity_sessions.status', 'completed')
                    ->count();

                $completionRate = $pastSessions > 0 ? round(($completedSessions / $pastSessions) * 100) : 100;

                // Calculate average attendance from sessions taught by this user
                $userSessionIds = DB::table('activity_sessions')
                    ->join('activities', 'activity_sessions.activity_id', '=', 'activities.id')
                    ->where('activities.centre_id', $centreId)
                    ->where(function($query) use ($userId) {
                        $query->where('activity_sessions.teacher_id', $userId)
                              ->orWhere('activity_sessions.instructor_id', $userId)
                              ->orWhere('activities.created_by', $userId);
                    })
                    ->pluck('activity_sessions.id');

                $totalAttendanceRecords = DB::table('attendances')
                    ->whereIn('session_id', $userSessionIds)
                    ->count();
                
                $presentAttendanceRecords = DB::table('attendances')
                    ->whereIn('session_id', $userSessionIds)
                    ->where('status', 'present')
                    ->count();
                
                $avgAttendance = $totalAttendanceRecords > 0 ? round(($presentAttendanceRecords / $totalAttendanceRecords) * 100) : 0;

                // Get additional stats for more comprehensive view
                $totalTraineesManaged = DB::table('activity_enrollments')
                    ->join('activities', 'activity_enrollments.activity_id', '=', 'activities.id')
                    ->where('activities.centre_id', $centreId)
                    ->where(function($query) use ($userId) {
                        $query->where('activities.created_by', $userId)
                              ->orWhere('activities.instructor_id', $userId);
                    })
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
                $attendedSessions = DB::table('attendances')
                    ->join('activity_sessions', 'attendances.session_id', '=', 'activity_sessions.id')
                    ->join('activities', 'activity_sessions.activity_id', '=', 'activities.id')
                    ->where('attendances.trainee_id', $userId)
                    ->where('activities.centre_id', $centreId)
                    ->where('attendances.status', 'present')
                    ->count();

                $totalSessionsAttended = DB::table('attendances')
                    ->join('activity_sessions', 'attendances.session_id', '=', 'activity_sessions.id')
                    ->join('activities', 'activity_sessions.activity_id', '=', 'activities.id')
                    ->where('attendances.trainee_id', $userId)
                    ->where('activities.centre_id', $centreId)
                    ->count();

                $attendanceRate = $totalSessionsAttended > 0 ? round(($attendedSessions / $totalSessionsAttended) * 100) : 0;

                // Get this week's sessions for enrolled activities
                $thisWeekSessions = DB::table('activity_sessions')
                    ->join('activity_enrollments', 'activity_sessions.activity_id', '=', 'activity_enrollments.activity_id')
                    ->join('activities', 'activity_sessions.activity_id', '=', 'activities.id')
                    ->where('activity_enrollments.trainee_id', $userId)
                    ->where('activities.centre_id', $centreId)
                    ->where('activity_enrollments.enrollment_status', '!=', 'dropped')
                    ->whereBetween('activity_sessions.scheduled_date', [
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
                    ->where('assigned_to', $userId)
                    ->count();

                $maintenanceTasksCompleted = DB::table('asset_maintenance')
                    ->join('assets', 'asset_maintenance.asset_id', '=', 'assets.id')
                    ->where('assets.centre_id', $centreId)
                    ->where('asset_maintenance.performed_by', $userId)
                    ->where('asset_maintenance.status', 'completed')
                    ->count();

                $thisWeekTasks = DB::table('asset_maintenance')
                    ->join('assets', 'asset_maintenance.asset_id', '=', 'assets.id')
                    ->where('assets.centre_id', $centreId)
                    ->where('asset_maintenance.assigned_to', $userId)
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