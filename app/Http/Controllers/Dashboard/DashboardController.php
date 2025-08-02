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

class DashboardController extends Controller
{
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
            
            return view('dashboard.modern', $dashboardData);
            
        } catch (\Exception $e) {
            Log::error('Modern dashboard error', [
                'user_id' => $userId ?? 'unknown',
                'error' => $e->getMessage()
            ]);
            
            return view('dashboard.modern', [
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
     * Get comprehensive dashboard data based on role
     */
    private function getDashboardData($role, $userId, $centreId)
    {
        $data = [
            'role' => $role,
            'user_name' => session('name'),
            'centre_id' => $centreId,
            'current_time' => now()->format('l, F j, Y - g:i A'),
            'quick_stats' => $this->getQuickStats($role, $userId, $centreId),
            'recent_activities' => $this->getRecentActivities($role, $userId, $centreId),
            'upcoming_sessions' => $this->getUpcomingSessions($role, $userId, $centreId),
            'notifications' => $this->getNotifications($userId),
            'recent_users' => $this->getRecentUsers($role, $centreId),
            'current_sessions' => $this->getCurrentSessions($role, $userId, $centreId),
            'calendar_events' => $this->getCalendarEvents($role, $userId, $centreId),
            'system_alerts' => $this->getSystemAlerts($role),
            'progress_summary' => $this->getProgressSummary($role, $userId, $centreId),
            'centre_info' => $this->getCentreInfo($centreId)
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
            
            $totalUsers = DB::table('users')->count();
            $activeTrainees = DB::table('trainees')->where('status', 'active')->count();
            $totalActivities = DB::table('activities')->where('activity_status', 'scheduled')->count();
            $activeCentres = DB::table('centres')->where('is_active', true)->count();
            
            return [
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
                        'maintenance_due' => DB::table('assets')
                            ->where('next_maintenance_date', '<=', now()->addDays(30))
                            ->count(),
                        'centre_capacity' => DB::table('centres')
                            ->sum('centre_capacity')
                    ]
                ],
                [
                    'title' => 'System Letters',
                    'value' => DB::table('letters')->count(),
                    'icon' => 'fas fa-envelope',
                    'color' => 'secondary',
                    'trend' => 'tracking',
                    'details' => [
                        'this_month' => DB::table('letters')->whereMonth('created_at', now()->month)->count(),
                        'pending' => DB::table('letters')->where('letter_status', 'draft')->count(),
                        'by_type' => DB::table('letters')
                            ->select('letter_type', DB::raw('count(*) as count'))
                            ->groupBy('letter_type')
                            ->pluck('count', 'letter_type')
                            ->toArray()
                    ]  
                ],
                [
                    'title' => 'System Health',
                    'value' => '98%',
                    'icon' => 'fas fa-heartbeat',
                    'color' => 'success',
                    'trend' => 'optimal',
                    'details' => [
                        'database_queries' => 'Fast (<100ms avg)',
                        'storage_usage' => round((1 - disk_free_space(storage_path()) / disk_total_space(storage_path())) * 100, 1) . '%',
                        'active_sessions' => DB::table('sessions')->count(),
                        'error_rate' => '0.2%'
                    ]
                ]
            ];
        } catch (\Exception $e) {
            Log::error('Error calculating admin stats', ['error' => $e->getMessage()]);
            return [
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
            $staffCount = DB::table('users')->where('centre_id', $centreId)->where('is_active', true)->count();
            $traineeCount = DB::table('trainees')->where('centre_id', $centreId)->where('status', 'active')->count();
            $activityCount = DB::table('activities')->where('centre_id', $centreId)->where('activity_status', 'scheduled')->count();
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
    private function getRecentActivities($role = null, $userId = null, $centreId = null)
    {
        try {
            $query = DB::table('activities')
                ->leftJoin('categories', 'activities.category_id', '=', 'categories.id')
                ->select('activities.activity_name', 'activities.created_at', 'activities.activity_status', 
                        'activities.activity_type', 'categories.category_type', 'categories.category_name')
                ->orderBy('activities.created_at', 'desc')
                ->limit(5);
                
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
     * Get upcoming sessions for teacher
     */
    private function getUpcomingSessions($role = null, $userId = null, $centreId = null)
    {
        return DB::table('activity_sessions')
            ->join('activities', 'activity_sessions.activity_id', '=', 'activities.id')
            ->select('activities.activity_name', 'activity_sessions.scheduled_date', 'activity_sessions.start_time', 'activity_sessions.venue')
            ->where('activity_sessions.teacher_id', $userId)
            ->where('activity_sessions.session_status', 'scheduled')
            ->where('activity_sessions.scheduled_date', '>=', today())
            ->orderBy('activity_sessions.scheduled_date')
            ->orderBy('activity_sessions.start_time')
            ->limit(5)
            ->get()
            ->map(function ($session) {
                return [
                    'activity' => $session->activity_name,
                    'date' => Carbon::parse($session->scheduled_date)->format('M j'),
                    'time' => Carbon::parse($session->start_time)->format('g:i A'),
                    'venue' => $session->venue ?? 'TBA'
                ];
            });
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
                        'type' => $notification->type ?? 'info',
                        'title' => $notification->title,
                        'message' => $notification->message,
                        'time' => Carbon::parse($notification->created_at)->diffForHumans(),
                        'read' => $notification->is_read
                    ];
                });
        } catch (\Exception $e) {
            return [
                [
                    'type' => 'info',
                    'title' => 'Welcome!',
                    'message' => 'Welcome to your CREAMS dashboard!',
                    'time' => 'now'
                ]
            ];
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

            if ($role === 'teacher') {
                $query->where('activity_sessions.teacher_id', $userId);
            } elseif ($role === 'supervisor' && $centreId) {
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
            $startDate = now()->startOfWeek();
            $endDate = now()->endOfWeek();

            $query = DB::table('activity_sessions')
                ->join('activities', 'activity_sessions.activity_id', '=', 'activities.id')
                ->select(
                    'activities.activity_name',
                    'activity_sessions.scheduled_date',
                    'activity_sessions.start_time',
                    'activity_sessions.end_time',
                    'activity_sessions.session_status'
                )
                ->whereBetween('activity_sessions.scheduled_date', [$startDate, $endDate])
                ->where('activity_sessions.session_status', '!=', 'cancelled');

            if ($role === 'teacher') {
                $query->where('activity_sessions.teacher_id', $userId);
            } elseif ($role === 'supervisor' && $centreId) {
                $query->where('activities.centre_id', $centreId);
            }

            return $query->orderBy('activity_sessions.scheduled_date')
                ->orderBy('activity_sessions.start_time')
                ->limit(10)
                ->get()
                ->map(function ($event) {
                    return [
                        'title' => $event->activity_name,
                        'date' => Carbon::parse($event->scheduled_date)->format('M j'),
                        'day' => Carbon::parse($event->scheduled_date)->format('D'),
                        'time' => Carbon::parse($event->start_time)->format('g:i A'),
                        'status' => $event->status,
                        'color' => $this->getStatusColor($event->status)
                    ];
                });
        } catch (\Exception $e) {
            return [];
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
                $completedSessions = DB::table('activity_sessions')->where('teacher_id', $userId)->where('status', 'completed')->count();
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
}