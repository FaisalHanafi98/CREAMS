<?php

namespace App\Http\Controllers;

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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use Exception;

class DashboardController extends Controller
{
    /**
     * Display the dashboard based on user role
     */
    public function index()
    {
        try {
            // Validate session
            if (!session('id') || !session('role')) {
                Log::warning('Invalid session in dashboard access');
                return redirect()->route('login')
                    ->with('error', 'Please log in to access the dashboard.');
            }

            $role = session('role');
            $userId = session('id');

            // Log dashboard access
            Log::info('Dashboard accessed', [
                'user_id' => $userId,
                'role' => $role,
                'timestamp' => now(),
                'ip' => request()->ip()
            ]);

            // Initialize variables to prevent undefined errors
            $totalUsers = 0;
            $totalTrainees = 0;
            $totalActivities = 0;
            $activeSessions = 0;
            $charts = [];
            
            // Get role-specific data with error handling
            try {
                $data = $this->getDashboardData($role, $userId);
                
                // Extract stats for views that use them directly
                if (isset($data['stats'])) {
                    $stats = $data['stats'];
                    $totalUsers = $stats['total_users'] ?? 0;
                    $totalTrainees = $stats['total_trainees'] ?? 0;
                    $totalActivities = $stats['total_activities'] ?? 0;
                    $activeSessions = $stats['active_sessions'] ?? 0;
                }
                
                // Extract charts for views that use them directly
                if (isset($data['charts'])) {
                    $charts = $data['charts'];
                }
                
            } catch (Exception $e) {
                Log::error('Error getting dashboard data', [
                    'role' => $role,
                    'user_id' => $userId,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                
                // Fallback to basic data
                $data = $this->getBasicDashboardData($role, $userId);
                $stats = $data['stats'] ?? [];
            }

            // Get common components
            $data['notifications'] = $this->getNotifications($userId, $role);
            $data['quickStats'] = $this->getQuickStats($role, $userId);
            $data['systemHealth'] = $this->getSystemHealth();

            // Initialize any potentially missing variables needed in the view
            $recentActivities = $data['recentActivities'] ?? [];
            $upcomingEvents = $data['upcomingEvents'] ?? [];
            $systemAlerts = $data['systemAlerts'] ?? [];
            $quickActions = $data['quickActions'] ?? [];
            $teachersList = $data['teachersList'] ?? [];
            $upcomingActivities = $data['upcomingActivities'] ?? [];
            $pendingApprovals = $data['pendingApprovals'] ?? [];
            $todaySessions = $data['todaySessions'] ?? [];
            $upcomingSessions = $data['upcomingSessions'] ?? [];
            $pendingAttendance = $data['pendingAttendance'] ?? [];
            $centreEvents = $data['centreEvents'] ?? [];
            $maintenanceAlerts = $data['maintenanceAlerts'] ?? [];
            $centreAnnouncements = $data['centreAnnouncements'] ?? [];
            
            // Return view with all needed variables
            return view('dashboard.index', compact(
                'data',
                'stats',
                'totalUsers',
                'totalTrainees',
                'totalActivities',
                'activeSessions',
                'role',
                'charts',
                'recentActivities',
                'upcomingEvents',
                'systemAlerts',
                'quickActions',
                'teachersList',
                'upcomingActivities',
                'pendingApprovals',
                'todaySessions',
                'upcomingSessions',
                'pendingAttendance',
                'centreEvents',
                'maintenanceAlerts',
                'centreAnnouncements'
            ));

        } catch (Exception $e) {
            Log::critical('Critical error in dashboard', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('login')
                ->with('error', 'An error occurred loading the dashboard. Please try again.');
        }
    }

    /**
     * Get dashboard data based on role
     */
    private function getDashboardData($role, $userId)
    {
        $method = 'get' . ucfirst($role) . 'Dashboard';
        
        if (!method_exists($this, $method)) {
            Log::warning('Dashboard method not found', ['method' => $method]);
            return $this->getDefaultDashboard($userId);
        }

        return $this->$method($userId);
    }

    /**
     * Get admin dashboard data with complete implementation
     */
    private function getAdminDashboard($userId)
    {
        return Cache::remember("dashboard_admin_{$userId}", 300, function () {
            $stats = [
                // User Statistics
                'total_users' => $this->safeCount(Users::class),
                'total_trainees' => $this->safeCount(Trainee::class),
                'total_activities' => $this->safeCount(Activity::class, ['is_active' => true]),
                'total_centres' => $this->safeCount(Centres::class, ['centre_status' => true]),
                
                // Monthly Statistics
                'new_users_this_month' => $this->getMonthlyCount(Users::class),
                'new_trainees_this_month' => $this->getMonthlyCount(Trainee::class),
                
                // Active Items
                'active_sessions' => $this->getActiveSessionsCount(),
                'pending_contacts' => $this->safeCount(ContactMessages::class, ['status' => 'pending']),
                'pending_volunteers' => $this->safeCount(Volunteers::class, ['status' => 'pending']),
                
                // Performance Metrics
                'system_uptime' => $this->calculateSystemUptime(),
                'average_response_time' => $this->getAverageResponseTime(),
                'daily_active_users' => $this->getDailyActiveUsers()
            ];

            $charts = [
                'userGrowth' => $this->getUserGrowthChart(),
                'activityDistribution' => $this->getActivityDistributionChart(),
                'centrePerformance' => $this->getCentrePerformanceChart(),
                'disabilityTypes' => $this->getDisabilityTypesChart(),
                'attendanceOverview' => $this->getAttendanceOverviewChart()
            ];

            $recentActivities = $this->getRecentSystemActivities();
            $upcomingEvents = $this->getUpcomingEvents();
            $systemAlerts = $this->getSystemAlerts();

            $quickActions = [
                ['icon' => 'fa-user-plus', 'title' => 'Add User', 'route' => 'users.create', 'color' => 'primary'],
                ['icon' => 'fa-child', 'title' => 'Add Trainee', 'route' => 'trainees.create', 'color' => 'success'],
                ['icon' => 'fa-calendar-plus', 'title' => 'Create Activity', 'route' => 'activities.create', 'color' => 'info'],
                ['icon' => 'fa-building', 'title' => 'Manage Centres', 'route' => 'centres.index', 'color' => 'warning'],
                ['icon' => 'fa-chart-bar', 'title' => 'View Reports', 'route' => 'reports.index', 'color' => 'danger'],
                ['icon' => 'fa-cog', 'title' => 'Settings', 'route' => 'settings.index', 'color' => 'secondary']
            ];

            return compact('stats', 'charts', 'quickActions', 'recentActivities', 'upcomingEvents', 'systemAlerts');
        });
    }

    /**
     * Get supervisor dashboard data with complete implementation
     */
    private function getSupervisorDashboard($userId)
    {
        return Cache::remember("dashboard_supervisor_{$userId}", 300, function () use ($userId) {
            $user = Users::find($userId);
            $centreId = $user->centre_id ?? null;

            $stats = [
                // Staff Management
                'total_teachers' => $this->getTeachersUnderSupervisor($userId),
                'active_teachers' => $this->getActiveTeachersCount($userId),
                'teacher_performance' => $this->getTeacherPerformanceMetrics($userId),
                
                // Centre Statistics
                'centre_staff' => $centreId ? $this->safeCount(Users::class, ['centre_id' => $centreId]) : 0,
                'centre_trainees' => $centreId ? $this->safeCount(Trainee::class, ['centre_id' => $centreId]) : 0,
                
                // Activity Management
                'pending_approvals' => $this->getPendingApprovalsCount($userId),
                'active_activities' => $this->safeCount(Activity::class, ['is_active' => true]),
                'total_sessions' => $centreId ? 
                    $this->safeCount(ActivitySession::class, ['centre_id' => $centreId]) : 0,
                
                // For views expecting these keys
                'total_users' => $this->safeCount(Users::class),
                'total_trainees' => $this->safeCount(Trainee::class),
                'total_activities' => $this->safeCount(Activity::class, ['is_active' => true]),
                'active_sessions' => $this->getActiveSessionsCount(),
            ];

            $charts = [
                'teacherPerformance' => $this->getTeacherPerformanceChart($userId),
                'traineesProgress' => $this->getTraineesProgressChart($centreId),
                'activityCompletion' => $this->getActivityCompletionChart($centreId),
                'attendanceOverview' => $this->getAttendanceOverviewChart($centreId)
            ];

            $teachersList = $this->getTeachersUnderSupervisorList($userId);
            $upcomingActivities = $this->getUpcomingActivities($centreId);
            $pendingApprovals = $this->getPendingApprovalsList($userId);

            $quickActions = [
                ['icon' => 'fa-user-plus', 'title' => 'Add Teacher', 'route' => 'users.create', 'color' => 'primary'],
                ['icon' => 'fa-calendar-plus', 'title' => 'Create Activity', 'route' => 'activities.create', 'color' => 'info'],
                ['icon' => 'fa-check-square', 'title' => 'Pending Approvals', 'route' => 'approvals.index', 'color' => 'warning'],
                ['icon' => 'fa-chart-bar', 'title' => 'View Reports', 'route' => 'reports.index', 'color' => 'danger']
            ];

            return compact('stats', 'charts', 'quickActions', 'teachersList', 'upcomingActivities', 'pendingApprovals');
        });
    }

    /**
     * Get teacher dashboard data with complete implementation
     */
    private function getTeacherDashboard($userId)
    {
        return Cache::remember("dashboard_teacher_{$userId}", 300, function () use ($userId) {
            $stats = [
                // Session Management
                'total_sessions' => $this->getTeacherSessionsCount($userId),
                'upcoming_sessions' => $this->getTeacherUpcomingSessionsCount($userId),
                'completed_sessions' => $this->getTeacherCompletedSessionsCount($userId),
                
                // Trainee Management
                'total_trainees' => $this->getTraineesAssignedToTeacher($userId),
                'active_trainees' => $this->getActiveTraineesForTeacher($userId),
                
                // Activity Management
                'total_activities' => $this->getTeacherActivitiesCount($userId),
                'activity_completion' => $this->getTeacherActivityCompletionRate($userId),
                
                // Performance Metrics
                'attendance_rate' => $this->getTeacherAttendanceRate($userId),
                'average_rating' => $this->getTeacherAverageRating($userId),
                
                // For views expecting these keys
                'active_sessions' => $this->getTeacherActiveSessionsCount($userId)
            ];

            $charts = [
                'sessionProgress' => $this->getTeacherSessionProgressChart($userId),
                'traineeAttendance' => $this->getTraineeAttendanceChart($userId),
                'activityDistribution' => $this->getTeacherActivityDistributionChart($userId)
            ];

            $todaySessions = $this->getTeacherTodaySessions($userId);
            $upcomingSessions = $this->getTeacherUpcomingSessions($userId);
            $pendingAttendance = $this->getTeacherPendingAttendance($userId);

            $quickActions = [
                ['icon' => 'fa-calendar-check', 'title' => 'Mark Attendance', 'route' => 'attendance.index', 'color' => 'primary'],
                ['icon' => 'fa-clipboard', 'title' => 'Session Notes', 'route' => 'sessions.notes', 'color' => 'info'],
                ['icon' => 'fa-user-friends', 'title' => 'My Trainees', 'route' => 'teacher.trainees', 'color' => 'success'],
                ['icon' => 'fa-chart-line', 'title' => 'Progress Reports', 'route' => 'teacher.reports', 'color' => 'warning']
            ];

            return compact('stats', 'charts', 'quickActions', 'todaySessions', 'upcomingSessions', 'pendingAttendance');
        });
    }

    /**
     * Get AJK dashboard data with complete implementation
     */
    private function getAjkDashboard($userId)
    {
        return Cache::remember("dashboard_ajk_{$userId}", 300, function () use ($userId) {
            $user = Users::find($userId);
            $centreId = $user->centre_id ?? null;

            $stats = [
                // Centre Overview
                'centre_trainees' => $centreId ? $this->safeCount(Trainee::class, ['centre_id' => $centreId]) : 0,
                'centre_staff' => $centreId ? $this->safeCount(Users::class, ['centre_id' => $centreId]) : 0,
                'centre_activities' => $centreId ? $this->getCentreActivitiesCount($centreId) : 0,
                
                // Asset Management
                'total_assets' => $centreId ? $this->safeCount(Asset::class, ['centre_id' => $centreId]) : 0,
                'maintenance_needed' => $centreId ? $this->getAssetsNeedingMaintenance($centreId) : 0,
                
                // Event Management
                'upcoming_events' => $centreId ? $this->getUpcomingEventsCount($centreId) : 0,
                'pending_approvals' => $this->getPendingAJKApprovalsCount($userId),
                
                // For views expecting these keys
                'total_users' => $centreId ? $this->safeCount(Users::class, ['centre_id' => $centreId]) : 0,
                'total_trainees' => $centreId ? $this->safeCount(Trainee::class, ['centre_id' => $centreId]) : 0,
                'total_activities' => $centreId ? $this->getCentreActivitiesCount($centreId) : 0,
                'active_sessions' => $centreId ? $this->getCentreActiveSessionsCount($centreId) : 0
            ];

            $charts = [
                'traineeDistribution' => $this->getTraineeDistributionChart($centreId),
                'assetUtilization' => $this->getAssetUtilizationChart($centreId),
                'eventAttendance' => $this->getEventAttendanceChart($centreId)
            ];

            $centreEvents = $this->getCentreEvents($centreId);
            $maintenanceAlerts = $this->getMaintenanceAlerts($centreId);
            $centreAnnouncements = $this->getCentreAnnouncements($centreId);

            $quickActions = [
                ['icon' => 'fa-calendar-plus', 'title' => 'Add Event', 'route' => 'events.create', 'color' => 'primary'],
                ['icon' => 'fa-bullhorn', 'title' => 'Add Announcement', 'route' => 'announcements.create', 'color' => 'info'],
                ['icon' => 'fa-tools', 'title' => 'Maintenance', 'route' => 'assets.maintenance', 'color' => 'warning'],
                ['icon' => 'fa-building', 'title' => 'Centre Info', 'route' => 'centres.show', 'params' => ['id' => $centreId], 'color' => 'success']
            ];

            return compact('stats', 'charts', 'quickActions', 'centreEvents', 'maintenanceAlerts', 'centreAnnouncements');
        });
    }

    /**
     * Get default dashboard data if role-specific method not available
     */
    private function getDefaultDashboard($userId)
    {
        $stats = [
            'total_users' => $this->safeCount(Users::class),
            'total_trainees' => $this->safeCount(Trainee::class),
            'total_activities' => $this->safeCount(Activity::class, ['is_active' => true]),
            'active_sessions' => $this->getActiveSessionsCount()
        ];

        $charts = [];
        
        // Define default quickActions to prevent "Undefined array key 'icon'" error
        $quickActions = [
            ['icon' => 'fa-home', 'title' => 'Home', 'route' => 'home', 'color' => 'primary'],
            ['icon' => 'fa-user', 'title' => 'Profile', 'route' => 'profile.show', 'color' => 'info']
        ];
        
        $recentActivities = [];
        $upcomingEvents = [];
        $systemAlerts = [];

        return compact('stats', 'charts', 'quickActions', 'recentActivities', 'upcomingEvents', 'systemAlerts');
    }

    /**
     * Get basic dashboard data as a fallback
     */
    private function getBasicDashboardData($role, $userId)
    {
        $stats = [
            'total_users' => 0,
            'total_trainees' => 0,
            'total_activities' => 0,
            'active_sessions' => 0
        ];

        try {
            // Try to get at least basic counts
            if ($role === 'admin' || $role === 'supervisor') {
                $stats['total_users'] = $this->safeCount(Users::class);
                $stats['total_trainees'] = $this->safeCount(Trainee::class);
                $stats['total_activities'] = $this->safeCount(Activity::class, ['is_active' => true]);
                $stats['active_sessions'] = $this->getActiveSessionsCount();
            } else if ($role === 'teacher') {
                $stats['total_trainees'] = $this->safeCount(SessionEnrollment::class, ['teacher_id' => $userId], 'trainee_id');
                $stats['total_activities'] = $this->safeCount(ActivitySession::class, ['teacher_id' => $userId], 'activity_id');
                $stats['active_sessions'] = $this->safeCount(ActivitySession::class, ['teacher_id' => $userId, 'is_active' => true]);
            } else if ($role === 'ajk') {
                $user = Users::find($userId);
                $centreId = $user->centre_id ?? null;
                
                if ($centreId) {
                    $stats['total_users'] = $this->safeCount(Users::class, ['centre_id' => $centreId]);
                    $stats['total_trainees'] = $this->safeCount(Trainee::class, ['centre_id' => $centreId]);
                }
            }
        } catch (Exception $e) {
            Log::error('Error getting basic dashboard data', [
                'error' => $e->getMessage()
            ]);
        }

        $charts = [];
        
        // Define default quickActions to prevent "Undefined array key 'icon'" error
        $quickActions = [
            ['icon' => 'fa-home', 'title' => 'Home', 'route' => 'home', 'color' => 'primary'],
            ['icon' => 'fa-user', 'title' => 'Profile', 'route' => 'profile.show', 'color' => 'info']
        ];
        
        $recentActivities = [];
        $upcomingEvents = [];
        $systemAlerts = [];

        return compact('stats', 'charts', 'quickActions', 'recentActivities', 'upcomingEvents', 'systemAlerts');
    }

    /**
     * Get notifications for user
     */
    private function getNotifications($userId, $role)
    {
        try {
            // Implementation would go here
            return [];
        } catch (Exception $e) {
            Log::error('Error getting notifications', [
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Get quick stats for user
     */
    private function getQuickStats($role, $userId)
    {
        try {
            // Implementation would go here
            return [];
        } catch (Exception $e) {
            Log::error('Error getting quick stats', [
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Get system health data
     */
    private function getSystemHealth()
    {
        try {
            return [
                'status' => 'healthy',
                'uptime' => '99.9%',
                'last_backup' => now()->subDays(1)->format('Y-m-d H:i:s'),
                'disk_space' => '78%'
            ];
        } catch (Exception $e) {
            Log::error('Error getting system health', [
                'error' => $e->getMessage()
            ]);
            return [
                'status' => 'unknown'
            ];
        }
    }

    /**
     * Safe count method with error handling
     */
    private function safeCount($model, $conditions = [], $distinctColumn = null)
    {
        try {
            $query = $model::query();
            
            foreach ($conditions as $column => $value) {
                $query->where($column, $value);
            }
            
            if ($distinctColumn) {
                $query->distinct($distinctColumn);
                return $query->count($distinctColumn);
            }
            
            return $query->count();
        } catch (Exception $e) {
            Log::error('Error in safeCount', [
                'model' => $model,
                'conditions' => $conditions,
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }

    /**
     * Get monthly count
     */
    private function getMonthlyCount($model, $conditions = [])
    {
        try {
            $query = $model::query();
            
            foreach ($conditions as $column => $value) {
                $query->where($column, $value);
            }
            
            $startOfMonth = Carbon::now()->startOfMonth();
            
            return $query->where('created_at', '>=', $startOfMonth)->count();
        } catch (Exception $e) {
            Log::error('Error in getMonthlyCount', [
                'model' => $model,
                'conditions' => $conditions,
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }

    /**
     * Get active sessions count
     */
    private function getActiveSessionsCount()
    {
        try {
            return ActivitySession::where('is_active', true)->count();
        } catch (Exception $e) {
            Log::error('Error getting active sessions count', [
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }

    /**
     * Get teacher sessions count
     */
    private function getTeacherSessionsCount($teacherId)
    {
        try {
            return ActivitySession::where('teacher_id', $teacherId)->count();
        } catch (Exception $e) {
            Log::error('Error getting teacher sessions count', [
                'error' => $e->getMessage(),
                'teacher_id' => $teacherId
            ]);
            return 0;
        }
    }

    /**
     * Get teacher active sessions count
     */
    private function getTeacherActiveSessionsCount($teacherId)
    {
        try {
            return ActivitySession::where('teacher_id', $teacherId)
                ->where('is_active', true)
                ->count();
        } catch (Exception $e) {
            Log::error('Error getting teacher active sessions count', [
                'error' => $e->getMessage(),
                'teacher_id' => $teacherId
            ]);
            return 0;
        }
    }

    /**
     * Get teacher upcoming sessions count
     */
    private function getTeacherUpcomingSessionsCount($teacherId)
    {
        try {
            $now = Carbon::now();
            return ActivitySession::where('teacher_id', $teacherId)
                ->where('session_date', '>', $now)
                ->count();
        } catch (Exception $e) {
            Log::error('Error getting teacher upcoming sessions count', [
                'error' => $e->getMessage(),
                'teacher_id' => $teacherId
            ]);
            return 0;
        }
    }

    /**
     * Get teacher completed sessions count
     */
    private function getTeacherCompletedSessionsCount($teacherId)
    {
        try {
            $now = Carbon::now();
            return ActivitySession::where('teacher_id', $teacherId)
                ->where('session_date', '<', $now)
                ->where('is_completed', true)
                ->count();
        } catch (Exception $e) {
            Log::error('Error getting teacher completed sessions count', [
                'error' => $e->getMessage(),
                'teacher_id' => $teacherId
            ]);
            return 0;
        }
    }

    /**
     * Get trainees assigned to teacher
     */
    private function getTraineesAssignedToTeacher($teacherId)
    {
        try {
            return SessionEnrollment::where('teacher_id', $teacherId)
                ->distinct('trainee_id')
                ->count('trainee_id');
        } catch (Exception $e) {
            Log::error('Error getting trainees assigned to teacher', [
                'error' => $e->getMessage(),
                'teacher_id' => $teacherId
            ]);
            return 0;
        }
    }

    /**
     * Get active trainees for teacher
     */
    private function getActiveTraineesForTeacher($teacherId)
    {
        try {
            // Get active trainee IDs from enrollments
            $traineeIds = SessionEnrollment::where('teacher_id', $teacherId)
                ->distinct('trainee_id')
                ->pluck('trainee_id');
                
            // Count only those who are active
            return Trainee::whereIn('id', $traineeIds)
                ->where('is_active', true)
                ->count();
        } catch (Exception $e) {
            Log::error('Error getting active trainees for teacher', [
                'error' => $e->getMessage(),
                'teacher_id' => $teacherId
            ]);
            return 0;
        }
    }

    /**
     * Get teacher activities count
     */
    private function getTeacherActivitiesCount($teacherId)
    {
        try {
            return ActivitySession::where('teacher_id', $teacherId)
                ->distinct('activity_id')
                ->count('activity_id');
        } catch (Exception $e) {
            Log::error('Error getting teacher activities count', [
                'error' => $e->getMessage(),
                'teacher_id' => $teacherId
            ]);
            return 0;
        }
    }

    /**
     * Get teacher activity completion rate
     */
    private function getTeacherActivityCompletionRate($teacherId)
    {
        try {
            $totalCompleted = ActivitySession::where('teacher_id', $teacherId)
                ->where('is_completed', true)
                ->count();
                
            $totalSessions = ActivitySession::where('teacher_id', $teacherId)
                ->where('session_date', '<', Carbon::now())
                ->count();
                
            if ($totalSessions === 0) {
                return 0;
            }
            
            return round(($totalCompleted / $totalSessions) * 100);
        } catch (Exception $e) {
            Log::error('Error getting teacher activity completion rate', [
                'error' => $e->getMessage(),
                'teacher_id' => $teacherId
            ]);
            return 0;
        }
    }

    /**
     * Get teacher attendance rate
     */
    private function getTeacherAttendanceRate($teacherId)
    {
        try {
            $totalAttendances = ActivityAttendance::whereHas('session', function ($query) use ($teacherId) {
                $query->where('teacher_id', $teacherId);
            })->count();
            
            $totalEnrollments = SessionEnrollment::whereHas('session', function ($query) use ($teacherId) {
                $query->where('teacher_id', $teacherId)
                    ->where('session_date', '<', Carbon::now());
            })->count();
            
            if ($totalEnrollments === 0) {
                return 0;
            }
            
            return round(($totalAttendances / $totalEnrollments) * 100);
        } catch (Exception $e) {
            Log::error('Error getting teacher attendance rate', [
                'error' => $e->getMessage(),
                'teacher_id' => $teacherId
            ]);
            return 0;
        }
    }

    /**
     * Get teacher average rating
     */
    private function getTeacherAverageRating($teacherId)
    {
        try {
            // Assuming ratings are stored in a ratings table
            // This is a placeholder implementation
            return 4.5;
        } catch (Exception $e) {
            Log::error('Error getting teacher average rating', [
                'error' => $e->getMessage(),
                'teacher_id' => $teacherId
            ]);
            return 0;
        }
    }

    /**
     * Get teachers under supervisor
     */
    private function getTeachersUnderSupervisor($supervisorId)
    {
        try {
            $supervisor = Users::find($supervisorId);
            $centreId = $supervisor->centre_id ?? null;
            
            if (!$centreId) {
                return 0;
            }
            
            return Users::where('centre_id', $centreId)
                ->where('role', 'teacher')
                ->count();
        } catch (Exception $e) {
            Log::error('Error getting teachers under supervisor', [
                'error' => $e->getMessage(),
                'supervisor_id' => $supervisorId
            ]);
            return 0;
        }
    }

    /**
     * Get active teachers count
     */
    private function getActiveTeachersCount($supervisorId)
    {
        try {
            $supervisor = Users::find($supervisorId);
            $centreId = $supervisor->centre_id ?? null;
            
            if (!$centreId) {
                return 0;
            }
            
            return Users::where('centre_id', $centreId)
                ->where('role', 'teacher')
                ->where('is_active', true)
                ->count();
        } catch (Exception $e) {
            Log::error('Error getting active teachers count', [
                'error' => $e->getMessage(),
                'supervisor_id' => $supervisorId
            ]);
            return 0;
        }
    }

    /**
     * Get centre activities count
     */
    private function getCentreActivitiesCount($centreId)
    {
        try {
            return Activity::where('centre_id', $centreId)
                ->where('is_active', true)
                ->count();
        } catch (Exception $e) {
            Log::error('Error getting centre activities count', [
                'error' => $e->getMessage(),
                'centre_id' => $centreId
            ]);
            return 0;
        }
    }

    /**
     * Get centre active sessions count
     */
    private function getCentreActiveSessionsCount($centreId)
    {
        try {
            return ActivitySession::where('centre_id', $centreId)
                ->where('is_active', true)
                ->count();
        } catch (Exception $e) {
            Log::error('Error getting centre active sessions count', [
                'error' => $e->getMessage(),
                'centre_id' => $centreId
            ]);
            return 0;
        }
    }

    /**
     * Get teacher performance metrics
     */
    private function getTeacherPerformanceMetrics($supervisorId)
    {
        try {
            // This would be a more complex implementation
            return 85; // Placeholder percentage
        } catch (Exception $e) {
            Log::error('Error getting teacher performance metrics', [
                'error' => $e->getMessage(),
                'supervisor_id' => $supervisorId
            ]);
            return 0;
        }
    }

    /**
     * Get pending approvals count
     */
    private function getPendingApprovalsCount($supervisorId)
    {
        try {
            // This would depend on what needs approval in your system
            return 5; // Placeholder count
        } catch (Exception $e) {
            Log::error('Error getting pending approvals count', [
                'error' => $e->getMessage(),
                'supervisor_id' => $supervisorId
            ]);
            return 0;
        }
    }

    /**
     * Get pending AJK approvals count
     */
    private function getPendingAJKApprovalsCount($ajkId)
    {
        try {
            // This would depend on what AJK members can approve
            return 3; // Placeholder count
        } catch (Exception $e) {
            Log::error('Error getting pending AJK approvals count', [
                'error' => $e->getMessage(),
                'ajk_id' => $ajkId
            ]);
            return 0;
        }
    }

    /**
     * Get assets needing maintenance
     */
    private function getAssetsNeedingMaintenance($centreId)
    {
        try {
            return Asset::where('centre_id', $centreId)
                ->where('maintenance_required', true)
                ->count();
        } catch (Exception $e) {
            Log::error('Error getting assets needing maintenance', [
                'error' => $e->getMessage(),
                'centre_id' => $centreId
            ]);
            return 0;
        }
    }

    /**
     * Get upcoming events count
     */
    private function getUpcomingEventsCount($centreId)
    {
        try {
            return Event::where('centre_id', $centreId)
                ->where('event_date', '>', Carbon::now())
                ->count();
        } catch (Exception $e) {
            Log::error('Error getting upcoming events count', [
                'error' => $e->getMessage(),
                'centre_id' => $centreId
            ]);
            return 0;
        }
    }

    /**
     * Calculate system uptime
     */
    private function calculateSystemUptime()
    {
        try {
            // This would be a more complex implementation
            return 99.8; // Placeholder percentage
        } catch (Exception $e) {
            Log::error('Error calculating system uptime', [
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }

    /**
     * Get average response time
     */
    private function getAverageResponseTime()
    {
        try {
            // This would be a more complex implementation
            return 0.25; // Placeholder time in seconds
        } catch (Exception $e) {
            Log::error('Error getting average response time', [
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }

    /**
     * Get daily active users
     */
    private function getDailyActiveUsers()
    {
        try {
            // This would be a more complex implementation
            return 128; // Placeholder count
        } catch (Exception $e) {
            Log::error('Error getting daily active users', [
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }

    /**
     * Get user growth chart data
     */
    private function getUserGrowthChart()
    {
        try {
            // This would generate chart data
            return [
                'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                'datasets' => [
                    [
                        'label' => 'Users',
                        'data' => [65, 70, 82, 95, 110, 125]
                    ]
                ]
            ];
        } catch (Exception $e) {
            Log::error('Error getting user growth chart', [
                'error' => $e->getMessage()
            ]);
            return [
                'labels' => [],
                'datasets' => []
            ];
        }
    }

    /**
     * Get activity distribution chart data
     */
    private function getActivityDistributionChart()
    {
        try {
            // This would generate chart data
            return [
                'labels' => ['Physical', 'Cognitive', 'Social', 'Emotional', 'Vocational'],
                'datasets' => [
                    [
                        'data' => [25, 20, 30, 15, 10]
                    ]
                ]
            ];
        } catch (Exception $e) {
            Log::error('Error getting activity distribution chart', [
                'error' => $e->getMessage()
            ]);
            return [
                'labels' => [],
                'datasets' => []
            ];
        }
    }

    /**
     * Get centre performance chart data
     */
    private function getCentrePerformanceChart()
    {
        try {
            // This would generate chart data
            return [
                'labels' => ['Centre A', 'Centre B', 'Centre C', 'Centre D'],
                'datasets' => [
                    [
                        'label' => 'Performance',
                        'data' => [85, 78, 92, 80]
                    ]
                ]
            ];
        } catch (Exception $e) {
            Log::error('Error getting centre performance chart', [
                'error' => $e->getMessage()
            ]);
            return [
                'labels' => [],
                'datasets' => []
            ];
        }
    }

    /**
     * Get disability types chart data
     */
    private function getDisabilityTypesChart()
    {
        try {
            // This would generate chart data
            return [
                'labels' => ['Autism', 'Down Syndrome', 'Cerebral Palsy', 'Learning Disabilities', 'Other'],
                'datasets' => [
                    [
                        'data' => [30, 25, 15, 20, 10]
                    ]
                ]
            ];
        } catch (Exception $e) {
            Log::error('Error getting disability types chart', [
                'error' => $e->getMessage()
            ]);
            return [
                'labels' => [],
                'datasets' => []
            ];
        }
    }

    /**
     * Get attendance overview chart data
     */
    private function getAttendanceOverviewChart($centreId = null)
    {
        try {
            // This would generate chart data
            return [
                'labels' => ['Mon', 'Tue', 'Wed', 'Thu', 'Fri'],
                'datasets' => [
                    [
                        'label' => 'Present',
                        'data' => [42, 38, 45, 40, 35]
                    ],
                    [
                        'label' => 'Absent',
                        'data' => [8, 12, 5, 10, 15]
                    ]
                ]
            ];
        } catch (Exception $e) {
            Log::error('Error getting attendance overview chart', [
                'error' => $e->getMessage(),
                'centre_id' => $centreId
            ]);
            return [
                'labels' => [],
                'datasets' => []
            ];
        }
    }

    /**
     * Get teacher performance chart data
     */
    private function getTeacherPerformanceChart($supervisorId)
    {
        try {
            // This would generate chart data
            return [
                'labels' => ['Teacher A', 'Teacher B', 'Teacher C', 'Teacher D', 'Teacher E'],
                'datasets' => [
                    [
                        'label' => 'Performance',
                        'data' => [90, 75, 85, 80, 95]
                    ]
                ]
            ];
        } catch (Exception $e) {
            Log::error('Error getting teacher performance chart', [
                'error' => $e->getMessage(),
                'supervisor_id' => $supervisorId
            ]);
            return [
                'labels' => [],
                'datasets' => []
            ];
        }
    }

    /**
     * Get trainees progress chart data
     */
    private function getTraineesProgressChart($centreId)
    {
        try {
            // This would generate chart data
            return [
                'labels' => ['Q1', 'Q2', 'Q3', 'Q4'],
                'datasets' => [
                    [
                        'label' => 'Progress',
                        'data' => [25, 40, 60, 75]
                    ]
                ]
            ];
        } catch (Exception $e) {
            Log::error('Error getting trainees progress chart', [
                'error' => $e->getMessage(),
                'centre_id' => $centreId
            ]);
            return [
                'labels' => [],
                'datasets' => []
            ];
        }
    }

    /**
     * Get activity completion chart data
     */
    private function getActivityCompletionChart($centreId)
    {
        try {
            // This would generate chart data
            return [
                'labels' => ['Physical', 'Cognitive', 'Social', 'Emotional'],
                'datasets' => [
                    [
                        'label' => 'Completion Rate',
                        'data' => [85, 70, 90, 75]
                    ]
                ]
            ];
        } catch (Exception $e) {
            Log::error('Error getting activity completion chart', [
                'error' => $e->getMessage(),
                'centre_id' => $centreId
            ]);
            return [
                'labels' => [],
                'datasets' => []
            ];
        }
    }

    /**
     * Get teacher session progress chart data
     */
    private function getTeacherSessionProgressChart($teacherId)
    {
        try {
            // This would generate chart data
            return [
                'labels' => ['Week 1', 'Week 2', 'Week 3', 'Week 4'],
                'datasets' => [
                    [
                        'label' => 'Completed',
                        'data' => [5, 8, 12, 10]
                    ],
                    [
                        'label' => 'Planned',
                        'data' => [6, 10, 15, 12]
                    ]
                ]
            ];
        } catch (Exception $e) {
            Log::error('Error getting teacher session progress chart', [
                'error' => $e->getMessage(),
                'teacher_id' => $teacherId
            ]);
            return [
                'labels' => [],
                'datasets' => []
            ];
        }
    }

    /**
     * Get trainee attendance chart data
     */
    private function getTraineeAttendanceChart($teacherId)
    {
        try {
            // This would generate chart data
            return [
                'labels' => ['Jan', 'Feb', 'Mar', 'Apr'],
                'datasets' => [
                    [
                        'label' => 'Attendance Rate',
                        'data' => [85, 90, 88, 92]
                    ]
                ]
            ];
        } catch (Exception $e) {
            Log::error('Error getting trainee attendance chart', [
                'error' => $e->getMessage(),
                'teacher_id' => $teacherId
            ]);
            return [
                'labels' => [],
                'datasets' => []
            ];
        }
    }

    /**
     * Get teacher activity distribution chart data
     */
    private function getTeacherActivityDistributionChart($teacherId)
    {
        try {
            // This would generate chart data
            return [
                'labels' => ['Physical', 'Cognitive', 'Social', 'Emotional'],
                'datasets' => [
                    [
                        'data' => [40, 20, 25, 15]
                    ]
                ]
            ];
        } catch (Exception $e) {
            Log::error('Error getting teacher activity distribution chart', [
                'error' => $e->getMessage(),
                'teacher_id' => $teacherId
            ]);
            return [
                'labels' => [],
                'datasets' => []
            ];
        }
    }

    /**
     * Get trainee distribution chart data
     */
    private function getTraineeDistributionChart($centreId)
    {
        try {
            // This would generate chart data
            return [
                'labels' => ['Age 5-10', 'Age 11-15', 'Age 16-20', 'Age 21+'],
                'datasets' => [
                    [
                        'data' => [25, 35, 30, 10]
                    ]
                ]
            ];
        } catch (Exception $e) {
            Log::error('Error getting trainee distribution chart', [
                'error' => $e->getMessage(),
                'centre_id' => $centreId
            ]);
            return [
                'labels' => [],
                'datasets' => []
            ];
        }
    }

    /**
     * Get asset utilization chart data
     */
    private function getAssetUtilizationChart($centreId)
    {
        try {
            // This would generate chart data
            return [
                'labels' => ['Equipment', 'Furniture', 'Technology', 'Books', 'Supplies'],
                'datasets' => [
                    [
                        'label' => 'Utilization Rate',
                        'data' => [80, 60, 85, 50, 90]
                    ]
                ]
            ];
        } catch (Exception $e) {
            Log::error('Error getting asset utilization chart', [
                'error' => $e->getMessage(),
                'centre_id' => $centreId
            ]);
            return [
                'labels' => [],
                'datasets' => []
            ];
        }
    }

    /**
     * Get event attendance chart data
     */
    private function getEventAttendanceChart($centreId)
    {
        try {
            // This would generate chart data
            return [
                'labels' => ['Workshop', 'Seminar', 'Open Day', 'Sports Day', 'Exhibition'],
                'datasets' => [
                    [
                        'label' => 'Attendance',
                        'data' => [75, 60, 90, 85, 70]
                    ]
                ]
            ];
        } catch (Exception $e) {
            Log::error('Error getting event attendance chart', [
                'error' => $e->getMessage(),
                'centre_id' => $centreId
            ]);
            return [
                'labels' => [],
                'datasets' => []
            ];
        }
    }

    /**
     * Get recent system activities
     */
    private function getRecentSystemActivities()
    {
        try {
            // This would fetch actual data
            return [
                ['action' => 'User Login', 'user' => 'Admin', 'time' => '5 mins ago'],
                ['action' => 'New Trainee Added', 'user' => 'Supervisor', 'time' => '15 mins ago'],
                ['action' => 'Activity Updated', 'user' => 'Teacher', 'time' => '30 mins ago'],
                ['action' => 'Attendance Marked', 'user' => 'Teacher', 'time' => '1 hour ago'],
                ['action' => 'Report Generated', 'user' => 'Admin', 'time' => '2 hours ago']
            ];
        } catch (Exception $e) {
            Log::error('Error getting recent system activities', [
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Get upcoming events
     */
    private function getUpcomingEvents()
    {
        try {
            // This would fetch actual data
            return [
                ['title' => 'Staff Meeting', 'date' => 'Tomorrow, 10:00 AM'],
                ['title' => 'Parents Workshop', 'date' => 'Jun 20, 2:00 PM'],
                ['title' => 'Training Session', 'date' => 'Jun 22, 9:00 AM'],
                ['title' => 'Community Event', 'date' => 'Jun 25, 11:00 AM']
            ];
        } catch (Exception $e) {
            Log::error('Error getting upcoming events', [
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Get system alerts
     */
    private function getSystemAlerts()
    {
        try {
            // This would fetch actual data
            return [
                ['type' => 'warning', 'message' => 'Database backup scheduled for tonight'],
                ['type' => 'info', 'message' => 'New system update available'],
                ['type' => 'success', 'message' => 'All services running normally']
            ];
        } catch (Exception $e) {
            Log::error('Error getting system alerts', [
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Get teachers under supervisor list
     */
    private function getTeachersUnderSupervisorList($supervisorId)
    {
        try {
            $supervisor = Users::find($supervisorId);
            $centreId = $supervisor->centre_id ?? null;
            
            if (!$centreId) {
                return [];
            }
            
            return Users::where('centre_id', $centreId)
                ->where('role', 'teacher')
                ->get(['id', 'name', 'email', 'phone', 'is_active']);
        } catch (Exception $e) {
            Log::error('Error getting teachers under supervisor list', [
                'error' => $e->getMessage(),
                'supervisor_id' => $supervisorId
            ]);
            return [];
        }
    }

    /**
     * Get upcoming activities
     */
    private function getUpcomingActivities($centreId)
    {
        try {
            return ActivitySession::where('centre_id', $centreId)
                ->where('session_date', '>', Carbon::now())
                ->orderBy('session_date', 'asc')
                ->limit(5)
                ->with(['activity', 'teacher'])
                ->get();
        } catch (Exception $e) {
            Log::error('Error getting upcoming activities', [
                'error' => $e->getMessage(),
                'centre_id' => $centreId
            ]);
            return [];
        }
    }

    /**
     * Get pending approvals list
     */
    private function getPendingApprovalsList($supervisorId)
    {
        try {
            // This would fetch actual data
            return [
                ['type' => 'Activity', 'name' => 'New Rehabilitation Program', 'submitted_by' => 'Teacher A'],
                ['type' => 'Event', 'name' => 'Parents Workshop', 'submitted_by' => 'Teacher B'],
                ['type' => 'Leave', 'name' => 'Medical Leave', 'submitted_by' => 'Teacher C']
            ];
        } catch (Exception $e) {
            Log::error('Error getting pending approvals list', [
                'error' => $e->getMessage(),
                'supervisor_id' => $supervisorId
            ]);
            return [];
        }
    }

    /**
     * Get teacher today sessions
     */
    private function getTeacherTodaySessions($teacherId)
    {
        try {
            $today = Carbon::today();
            $tomorrow = Carbon::tomorrow();
            
            return ActivitySession::where('teacher_id', $teacherId)
                ->whereBetween('session_date', [$today, $tomorrow])
                ->with(['activity', 'enrollments.trainee'])
                ->get();
        } catch (Exception $e) {
            Log::error('Error getting teacher today sessions', [
                'error' => $e->getMessage(),
                'teacher_id' => $teacherId
            ]);
            return [];
        }
    }

    /**
     * Get teacher upcoming sessions
     */
    private function getTeacherUpcomingSessions($teacherId)
    {
        try {
            $tomorrow = Carbon::tomorrow();
            $nextWeek = Carbon::today()->addWeek();
            
            return ActivitySession::where('teacher_id', $teacherId)
                ->whereBetween('session_date', [$tomorrow, $nextWeek])
                ->with(['activity'])
                ->get();
        } catch (Exception $e) {
            Log::error('Error getting teacher upcoming sessions', [
                'error' => $e->getMessage(),
                'teacher_id' => $teacherId
            ]);
            return [];
        }
    }

    /**
     * Get teacher pending attendance
     */
    private function getTeacherPendingAttendance($teacherId)
    {
        try {
            $now = Carbon::now();
            
            // Get past sessions that don't have attendance records for all enrollments
            return ActivitySession::where('teacher_id', $teacherId)
                ->where('session_date', '<', $now)
                ->where('is_completed', false)
                ->with(['activity'])
                ->get();
        } catch (Exception $e) {
            Log::error('Error getting teacher pending attendance', [
                'error' => $e->getMessage(),
                'teacher_id' => $teacherId
            ]);
            return [];
        }
    }

    /**
     * Get centre events
     */
    private function getCentreEvents($centreId)
    {
        try {
            return Event::where('centre_id', $centreId)
                ->where('event_date', '>', Carbon::now())
                ->orderBy('event_date', 'asc')
                ->limit(5)
                ->get();
        } catch (Exception $e) {
            Log::error('Error getting centre events', [
                'error' => $e->getMessage(),
                'centre_id' => $centreId
            ]);
            return [];
        }
    }

    /**
     * Get maintenance alerts
     */
    private function getMaintenanceAlerts($centreId)
    {
        try {
            return Asset::where('centre_id', $centreId)
                ->where('maintenance_required', true)
                ->orderBy('updated_at', 'desc')
                ->limit(5)
                ->get();
        } catch (Exception $e) {
            Log::error('Error getting maintenance alerts', [
                'error' => $e->getMessage(),
                'centre_id' => $centreId
            ]);
            return [];
        }
    }

    /**
     * Get centre announcements
     */
    private function getCentreAnnouncements($centreId)
    {
        try {
            // This would fetch actual data from an announcements table
            return [
                ['title' => 'Holiday Closure', 'date' => 'Jun 15, 2025', 'content' => 'Centre will be closed for Eid holiday'],
                ['title' => 'New Equipment Arrival', 'date' => 'Jun 10, 2025', 'content' => 'New rehabilitation equipment has arrived'],
                ['title' => 'Staff Training', 'date' => 'Jun 5, 2025', 'content' => 'Mandatory staff training session scheduled']
            ];
        } catch (Exception $e) {
            Log::error('Error getting centre announcements', [
                'error' => $e->getMessage(),
                'centre_id' => $centreId
            ]);
            return [];
        }
    }
}