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
     * Display modern dashboard
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
            
            // Get dashboard data based on role
            $dashboardData = $this->getDashboardData($role, $userId, $centreId);
            
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
     * Get admin statistics
     */
    private function getAdminStats()
    {
        return [
            [
                'title' => 'Total Users',
                'value' => DB::table('users')->count(),
                'icon' => 'fas fa-users',
                'color' => 'primary',
                'trend' => '+12%'
            ],
            [
                'title' => 'Active Trainees',
                'value' => DB::table('trainees')->where('status', 'active')->count(),
                'icon' => 'fas fa-user-graduate',
                'color' => 'success',
                'trend' => '+8%'
            ],
            [
                'title' => 'Total Activities',
                'value' => DB::table('activities')->where('is_active', true)->count(),
                'icon' => 'fas fa-tasks',
                'color' => 'info',
                'trend' => '+15%'
            ],
            [
                'title' => 'Active Centres',
                'value' => DB::table('centres')->where('is_active', true)->count(),
                'icon' => 'fas fa-building',
                'color' => 'warning',
                'trend' => 'stable'
            ]
        ];
    }

    /**
     * Get supervisor statistics
     */
    private function getSupervisorStats($centreId)
    {
        return [
            [
                'title' => 'Centre Staff',
                'value' => DB::table('users')->where('centre_id', $centreId)->count(),
                'icon' => 'fas fa-users',
                'color' => 'primary'
            ],
            [
                'title' => 'Centre Trainees',
                'value' => DB::table('trainees')->where('centre_id', $centreId)->where('status', 'active')->count(),
                'icon' => 'fas fa-user-graduate',
                'color' => 'success'
            ],
            [
                'title' => 'Active Activities',
                'value' => DB::table('activities')->where('centre_id', $centreId)->where('is_active', true)->count(),
                'icon' => 'fas fa-tasks',
                'color' => 'info'
            ],
            [
                'title' => 'Assets',
                'value' => DB::table('assets')->where('centre_id', $centreId)->count(),
                'icon' => 'fas fa-boxes',
                'color' => 'warning'
            ]
        ];
    }

    /**
     * Get teacher statistics
     */
    private function getTeacherStats($userId, $centreId)
    {
        return [
            [
                'title' => 'My Activities',
                'value' => DB::table('activities')->where('created_by', $userId)->count(),
                'icon' => 'fas fa-clipboard-list',
                'color' => 'primary'
            ],
            [
                'title' => 'Assigned Sessions',
                'value' => DB::table('activity_sessions')->where('teacher_id', $userId)->where('status', 'scheduled')->count(),
                'icon' => 'fas fa-calendar',
                'color' => 'success'
            ],
            [
                'title' => 'Centre Trainees',
                'value' => DB::table('trainees')->where('centre_id', $centreId)->where('status', 'active')->count(),
                'icon' => 'fas fa-user-graduate',
                'color' => 'info'
            ],
            [
                'title' => 'Completed Sessions',
                'value' => DB::table('activity_sessions')->where('teacher_id', $userId)->where('status', 'completed')->count(),
                'icon' => 'fas fa-check-circle',
                'color' => 'success'
            ]
        ];
    }

    /**
     * Get AJK statistics
     */
    private function getAjkStats($centreId)
    {
        return [
            [
                'title' => 'Centre Trainees',
                'value' => DB::table('trainees')->where('centre_id', $centreId)->where('status', 'active')->count(),
                'icon' => 'fas fa-user-graduate',
                'color' => 'primary'
            ],
            [
                'title' => 'Active Activities',
                'value' => DB::table('activities')->where('centre_id', $centreId)->where('is_active', true)->count(),
                'icon' => 'fas fa-tasks',
                'color' => 'success'
            ],
            [
                'title' => 'Today\'s Sessions',
                'value' => DB::table('activity_sessions')
                    ->join('activities', 'activity_sessions.activity_id', '=', 'activities.id')
                    ->where('activities.centre_id', $centreId)
                    ->whereDate('activity_sessions.scheduled_date', today())
                    ->count(),
                'icon' => 'fas fa-calendar-day',
                'color' => 'info'
            ]
        ];
    }

    /**
     * Get recent activities
     */
    private function getRecentActivities($centreId = null)
    {
        $query = DB::table('activities')
            ->select('name', 'created_at', 'is_active')
            ->orderBy('created_at', 'desc')
            ->limit(5);
            
        if ($centreId) {
            $query->where('centre_id', $centreId);
        }
        
        return $query->get()->map(function ($activity) {
            return [
                'title' => $activity->name,
                'time' => Carbon::parse($activity->created_at)->diffForHumans(),
                'status' => $activity->is_active ? 'active' : 'inactive'
            ];
        });
    }

    /**
     * Get upcoming sessions for teacher
     */
    private function getUpcomingSessions($teacherId)
    {
        return DB::table('activity_sessions')
            ->join('activities', 'activity_sessions.activity_id', '=', 'activities.id')
            ->select('activities.name as activity_name', 'activity_sessions.scheduled_date', 'activity_sessions.start_time', 'activity_sessions.venue')
            ->where('activity_sessions.teacher_id', $teacherId)
            ->where('activity_sessions.status', 'scheduled')
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
                    'activity_sessions.status',
                    'activity_sessions.id as session_id'
                )
                ->where('activity_sessions.status', 'ongoing')
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
                    'activity_sessions.status'
                )
                ->whereBetween('activity_sessions.scheduled_date', [$startDate, $endDate])
                ->where('activity_sessions.status', '!=', 'cancelled');

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
                $completedSessions = $query->where('activity_sessions.status', 'completed')->count();
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
}