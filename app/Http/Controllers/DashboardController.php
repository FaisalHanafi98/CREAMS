<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\Centres;
use App\Models\Models;
use App\Models\Courses;
use App\Models\Events;
use App\Models\Activities;
use App\Models\Assets;
use App\Models\Users;
use App\Models\Admins;
use App\Models\Supervisors;
use App\Models\Teachers;
use App\Models\AJKs;
use App\Models\Classes;
use App\Models\Volunteers;
use App\Models\Notifications;

class DashboardController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    /**
     * Display the dashboard based on user role
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Get user data from session
        $role = session('role');
        $userId = session('id');
        $centreId = session('centre_id');
        
        Log::info('Dashboard accessed', [
            'user_id' => $userId,
            'role' => $role,
            'centre_id' => $centreId,
            'session_id' => session()->getId()
        ]);
        
        // Double check that the user exists in the database
        $user = Users::find($userId);
        
        if (!$user) {
            Log::error('Dashboard access attempted with invalid user ID', [
                'session_user_id' => $userId,
                'session_role' => $role
            ]);
            
            // Invalidate session and redirect to login
            session()->invalidate();
            return redirect()->route('auth.loginpage')
                ->with('error', 'Your session has expired. Please log in again.');
        }
        
        // Check if session role matches actual user role for security
        if ($role !== $user->role) {
            Log::warning('Session role mismatch with database role', [
                'session_role' => $role,
                'db_role' => $user->role,
                'user_id' => $userId
            ]);
            
            // Update session to match database (could be an alternative to redirecting)
            session(['role' => $user->role]);
            session()->save();
            
            // Redirect to the correct dashboard
            return redirect()->route($user->role . '.dashboard')
                ->with('warning', 'Your session has been updated. Please try again.');
        }
        
        // Get role-specific dashboard data
        $dashboardData = $this->getDashboardDataForRole($role, $userId, $centreId);
        
        // Get user's notifications
        $notifications = $this->getUserNotifications($userId);
        
        // Get centre info
        $centre = null;
        if ($centreId) {
            $centre = Centres::find($centreId);
            if (!$centre) {
                Log::warning('User has invalid centre_id in session', [
                    'user_id' => $userId,
                    'centre_id' => $centreId
                ]);
            }
        }
        
        // Complete user data
        $userData = [
            'id' => $userId,
            'name' => $user->name,
            'role' => $user->role,
            'iium_id' => $user->iium_id,
            'email' => $user->email,
            'centre_id' => $user->centre_id,
            'avatar' => $user->avatar,
            'user_avatar' => $user->user_avatar,
            'centre_name' => $centre ? $centre->name : 'Unknown Centre'
        ];
        
        Log::debug('Dashboard data prepared', [
            'user_id' => $userId,
            'data_sections' => array_keys($dashboardData),
            'notifications_count' => $notifications->count(),
            'view_data_prepared' => true
        ]);
        
        // Return the dashboard view with data
        return view('dashboard', [
            'user' => $userData,
            'data' => $dashboardData,
            'notifications' => $notifications
        ]);
    }
    
    /**
     * Get dashboard data specific to user role
     */
    private function getDashboardDataForRole($role, $userId, $centreId)
    {
        $data = [
            'title' => ucfirst($role) . ' Dashboard',
            'stats' => [],
            'menuItems' => $this->getMenuItemsForRole($role),
            'timestamp' => now()->toDateTimeString()
        ];
        
        try {
            // Get role-specific data
            switch ($role) {
                case 'admin':
                    $data['stats'] = $this->getAdminStats();
                    $data['userManagement'] = [
                        'recentUsers' => Users::orderBy('created_at', 'desc')->take(5)->get(),
                        'totalUsers' => Users::count(),
                        'activeUsers' => Users::where('status', 'active')->count(),
                    ];
                    break;
                    
                case 'supervisor':
                    $data['stats'] = $this->getSupervisorStats($centreId);
                    $data['teacherManagement'] = [
                        'teachers' => Teachers::where('centre_id', $centreId)->get(),
                        'pendingApprovals' => Classes::where('status', 'pending')->count(),
                    ];
                    break;
                    
                case 'teacher':
                    $data['stats'] = $this->getTeacherStats($userId);
                    $data['classList'] = Classes::where('teacher_id', $userId)->get();
                    $data['todaySchedule'] = Classes::where('teacher_id', $userId)
                        ->whereRaw('DAYNAME(created_at) = ?', [date('l')])
                        ->get();
                    break;
                    
                case 'ajk':
                    $data['stats'] = $this->getAjkStats($centreId);
                    $data['events'] = Events::where('centre_id', $centreId)
                        ->orderBy('date', 'asc')
                        ->where('date', '>=', now())
                        ->take(5)
                        ->get();
                    $data['volunteers'] = Volunteers::where('status', 'pending')
                        ->orderBy('created_at', 'desc')
                        ->take(5)
                        ->get();
                    break;
                    
                default:
                    $data['stats'] = $this->getDefaultStats();
            }
        } catch (\Exception $e) {
            $data['stats'] = $this->getDefaultStats();
            $data['error'] = 'Could not load all dashboard data. Please try again later.';
        }
        
        return $data;
    }
    
    /**
     * Get menu items for the sidebar based on user role
     */
    private function getMenuItemsForRole($role)
    {
        // Common menu items for all roles
        $menuItems = [
            ['route' => 'dashboard', 'icon' => 'home', 'label' => 'Dashboard'],
            ['route' => 'profile', 'icon' => 'user-circle', 'label' => 'My Profile'],
            ['route' => 'messages', 'icon' => 'envelope', 'label' => 'Messages'],
            ['route' => 'notifications', 'icon' => 'bell', 'label' => 'Notifications'],
            ['route' => 'activities.index', 'icon' => 'calendar-alt', 'label' => 'Activities']
        ];
        
        // Role-specific menu items
        switch ($role) {
            case 'admin':
                $adminItems = [
                    ['route' => 'admin.users', 'icon' => 'users', 'label' => 'User Management'],
                    ['route' => 'admin.trainees', 'icon' => 'user-graduate', 'label' => 'Tainee Management'],
                    ['route' => 'admin.centres', 'icon' => 'building', 'label' => 'Centres'],
                    ['route' => 'admin.assets', 'icon' => 'boxes', 'label' => 'Assets'],
                    ['route' => 'admin.reports', 'icon' => 'chart-bar', 'label' => 'Reports'],
                    ['route' => 'admin.settings', 'icon' => 'cog', 'label' => 'Settings']
                ];
                $menuItems = array_merge($menuItems, $adminItems);
                break;
                
            case 'supervisor':
                $supervisorItems = [
                    ['route' => 'supervisor.users', 'icon' => 'users', 'label' => 'User Management'],
                    ['route' => 'supervisor.trainees', 'icon' => 'user-graduate', 'label' => 'Tainee Management'],
                    ['route' => 'supervisor.centres', 'icon' => 'building', 'label' => 'Centres'],
                    ['route' => 'supervisor.assets', 'icon' => 'boxes', 'label' => 'Assets'],
                    ['route' => 'supervisor.reports', 'icon' => 'chart-bar', 'label' => 'Reports'],
                    ['route' => 'supervisor.settings', 'icon' => 'cog', 'label' => 'Settings']
                ];
                $menuItems = array_merge($menuItems, $supervisorItems);
                break;
                
            case 'ajk':
                $ajkItems = [
                    ['route' => 'ajk.users', 'icon' => 'users', 'label' => 'User Management'],
                    ['route' => 'ajk.trainees', 'icon' => 'user-graduate', 'label' => 'Tainee Management'],
                    ['route' => 'ajk.centres', 'icon' => 'building', 'label' => 'Centres'],
                    ['route' => 'ajk.assets', 'icon' => 'boxes', 'label' => 'Assets'],
                    ['route' => 'ajk.reports', 'icon' => 'chart-bar', 'label' => 'Reports'],
                    ['route' => 'ajk.settings', 'icon' => 'cog', 'label' => 'Settings']
                ];
                $menuItems = array_merge($menuItems, $ajkItems);
                break;
                
            case 'teacher':
                $teacherItems = [
                    ['route' => 'teacher.users', 'icon' => 'users', 'label' => 'User Management'],
                    ['route' => 'teacher.trainees', 'icon' => 'user-graduate', 'label' => 'Tainee Management'],
                    ['route' => 'teacher.centres', 'icon' => 'building', 'label' => 'Centres'],
                    ['route' => 'teacher.assets', 'icon' => 'boxes', 'label' => 'Assets'],
                    ['route' => 'teacher.reports', 'icon' => 'chart-bar', 'label' => 'Reports'],
                    ['route' => 'teacher.settings', 'icon' => 'cog', 'label' => 'Settings']
                ];
                $menuItems = array_merge($menuItems, $teacherItems);
                break;
        }
        
        return $menuItems;
    }
    
    /**
     * Get admin-specific stats
     */
    private function getAdminStats()
    {
        Log::debug('Gathering admin statistics');
        
        try {
            // Get real counts from the database with detailed error handling
            $supervisorCount = Users::where('role', 'supervisor')
                                ->where('status', 'active')
                                ->count();
            
            $teacherCount = Users::where('role', 'teacher')
                            ->where('status', 'active')
                            ->count();
            
            $ajkCount = Users::where('role', 'ajk')
                        ->where('status', 'active')
                        ->count();
            
            $adminCount = Users::where('role', 'admin')
                            ->where('status', 'active')
                            ->count();
            
            $totalUsers = $supervisorCount + $teacherCount + $ajkCount + $adminCount;
            
            Log::debug('Admin stats collected', [
                'supervisors' => $supervisorCount,
                'teachers' => $teacherCount,
                'ajks' => $ajkCount,
                'admins' => $adminCount,
                'total' => $totalUsers
            ]);
            
            // Calculate change over last month for each stat
            $lastMonthDate = now()->subMonth();
            
            $newSupervisorsCount = Users::where('role', 'supervisor')
                                    ->where('status', 'active')
                                    ->where('created_at', '>=', $lastMonthDate)
                                    ->count();
            
            $newTeachersCount = Users::where('role', 'teacher')
                                ->where('status', 'active')
                                ->where('created_at', '>=', $lastMonthDate)
                                ->count();
            
            $newAjksCount = Users::where('role', 'ajk')
                            ->where('status', 'active')
                            ->where('created_at', '>=', $lastMonthDate)
                            ->count();
            
            $totalNewUsers = $newSupervisorsCount + $newTeachersCount + $newAjksCount;
            
            // Format stats for display with dynamic change indicators
            return [
                [
                    'title' => 'Supervisors', 
                    'value' => $supervisorCount, 
                    'icon' => 'user-tie', 
                    'change' => ($newSupervisorsCount > 0 ? '+' . $newSupervisorsCount : ($newSupervisorsCount < 0 ? $newSupervisorsCount : 'No change')) . ' this month', 
                    'type' => $newSupervisorsCount > 0 ? 'positive' : ($newSupervisorsCount < 0 ? 'negative' : 'neutral')
                ],
                [
                    'title' => 'Teachers', 
                    'value' => $teacherCount, 
                    'icon' => 'chalkboard-teacher', 
                    'change' => ($newTeachersCount > 0 ? '+' . $newTeachersCount : ($newTeachersCount < 0 ? $newTeachersCount : 'No change')) . ' this month', 
                    'type' => $newTeachersCount > 0 ? 'positive' : ($newTeachersCount < 0 ? 'negative' : 'neutral')
                ],
                [
                    'title' => 'AJKs', 
                    'value' => $ajkCount, 
                    'icon' => 'users-cog', 
                    'change' => ($newAjksCount > 0 ? '+' . $newAjksCount : ($newAjksCount < 0 ? $newAjksCount : 'No change')) . ' this month', 
                    'type' => $newAjksCount > 0 ? 'positive' : ($newAjksCount < 0 ? 'negative' : 'neutral')
                ],
                [
                    'title' => 'Total Users', 
                    'value' => $totalUsers, 
                    'icon' => 'users', 
                    'change' => ($totalNewUsers > 0 ? '+' . $totalNewUsers : ($totalNewUsers < 0 ? $totalNewUsers : 'No change')) . ' this month', 
                    'type' => $totalNewUsers > 0 ? 'positive' : ($totalNewUsers < 0 ? 'negative' : 'neutral')
                ]
            ];
        } catch (\Exception $e) {
            Log::error('Error gathering admin stats', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Return dummy data in case of error
            return $this->getDefaultStats();
        }
    }
    
    /**
     * Get supervisor-specific stats
     */
    private function getSupervisorStats($centerId)
    {
        // Get real counts from the database for this centre
        $teacherCount = Teachers::where('center_id', $centerId)->count();
        $classCount = Classes::where('center_id', $centerId)->count();
        $courseCount = Courses::where('center_id', $centerId)->count();
        $activityCount = Activities::where('center_id', $centerId)->count();
        
        // Format stats for display
        return [
            ['title' => 'Teachers', 'value' => $teacherCount, 'icon' => 'chalkboard-teacher', 'change' => '+2 this month', 'type' => 'positive'],
            ['title' => 'Classes', 'value' => $classCount, 'icon' => 'book', 'change' => '+3 this month', 'type' => 'positive'],
            ['title' => 'Courses', 'value' => $courseCount, 'icon' => 'graduation-cap', 'change' => '+1 this month', 'type' => 'positive'],
            ['title' => 'Activities', 'value' => $activityCount, 'icon' => 'calendar-alt', 'change' => '+5 this month', 'type' => 'positive']
        ];
    }
    
    /**
     * Get teacher-specific stats
     */
    private function getTeacherStats($userId)
    {
        Log::debug('Gathering teacher statistics', ['teacher_id' => $userId]);
        
        try {
            // Get real counts from the database for this teacher
            $classCount = Classes::where('teacher_id', $userId)->count();
            
            // Using a subquery for more efficient counting
            $traineeCount = 0;
            $classes = Classes::where('teacher_id', $userId)->get();
            if ($classes->isNotEmpty()) {
                $classIds = $classes->pluck('id')->toArray();
                
                // Count unique trainees across all classes
                $traineeCount = DB::table('class_trainee')
                                ->whereIn('class_id', $classIds)
                                ->distinct('trainee_id')
                                ->count('trainee_id');
            }
            
            $activeClassCount = Classes::where('teacher_id', $userId)
                                    ->where('status', 'active')
                                    ->count();
            
            // Calculate attendance percentage
            $attendancePercentage = '0%';
            if ($classIds ?? false) {
                // Get attendance records for the last 30 days
                $totalAttendances = DB::table('attendances')
                                    ->whereIn('class_id', $classIds)
                                    ->where('date', '>=', now()->subDays(30))
                                    ->count();
                                    
                $presentAttendances = DB::table('attendances')
                                    ->whereIn('class_id', $classIds)
                                    ->where('date', '>=', now()->subDays(30))
                                    ->where('status', 'present')
                                    ->count();
                
                if ($totalAttendances > 0) {
                    $attendancePercentage = round(($presentAttendances / $totalAttendances) * 100) . '%';
                }
            }
            
            Log::debug('Teacher stats collected', [
                'teacher_id' => $userId,
                'classes' => $classCount,
                'trainees' => $traineeCount,
                'active_classes' => $activeClassCount,
                'attendance' => $attendancePercentage
            ]);
            
            // Format stats for display
            return [
                ['title' => 'Classes', 'value' => $classCount, 'icon' => 'book', 'change' => '+1 this month', 'type' => 'positive'],
                ['title' => 'Trainees', 'value' => $traineeCount, 'icon' => 'user-graduate', 'change' => '+3 this month', 'type' => 'positive'],
                ['title' => 'Active Classes', 'value' => $activeClassCount, 'icon' => 'chalkboard', 'change' => 'No change', 'type' => 'neutral'],
                ['title' => 'Attendance', 'value' => $attendancePercentage, 'icon' => 'clipboard-check', 'change' => '+2% this month', 'type' => 'positive']
            ];
        } catch (\Exception $e) {
            Log::error('Error gathering teacher stats', [
                'teacher_id' => $userId,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Return dummy data in case of error
            return $this->getDefaultStats();
        }
    }
    
    /**
     * Get AJK-specific stats
     */
    private function getAjkStats($centerId)
    {
        // Get real counts from the database for this centre
        $eventCount = Events::where('center_id', $centerId)->count();
        $upcomingEventCount = Events::where('center_id', $centerId)
                                  ->where('status', 'upcoming')
                                  ->count();
        $participantCount = Events::where('center_id', $centerId)
                                ->withCount('participants')
                                ->get()
                                ->sum('participants_count');
        $volunteerCount = Events::where('center_id', $centerId)
                              ->withCount('volunteers')
                              ->get()
                              ->sum('volunteers_count');
        
        // Format stats for display
        return [
            ['title' => 'Events', 'value' => $eventCount, 'icon' => 'calendar-day', 'change' => '+2 this month', 'type' => 'positive'],
            ['title' => 'Upcoming', 'value' => $upcomingEventCount, 'icon' => 'calendar-plus', 'change' => '+1 this month', 'type' => 'positive'],
            ['title' => 'Participants', 'value' => $participantCount, 'icon' => 'users', 'change' => '+15 this month', 'type' => 'positive'],
            ['title' => 'Volunteers', 'value' => $volunteerCount, 'icon' => 'hands-helping', 'change' => '+5 this month', 'type' => 'positive']
        ];
    }

    /**
     * Provide default stats when role-specific stats cannot be loaded
     * 
     * @return array
     */
    private function getDefaultStats()
    {
        Log::debug('Using default statistics (fallback)');
        
        // Generic stats that don't rely on database queries
        return [
            ['title' => 'System Status', 'value' => 'Online', 'icon' => 'server', 'change' => 'Stable', 'type' => 'positive'],
            ['title' => 'Current Time', 'value' => now()->format('H:i'), 'icon' => 'clock', 'change' => 'Updated now', 'type' => 'neutral'],
            ['title' => 'Today\'s Date', 'value' => now()->format('d M Y'), 'icon' => 'calendar-day', 'change' => 'Current', 'type' => 'neutral'],
            ['title' => 'System Version', 'value' => config('app.version', '1.0'), 'icon' => 'code-branch', 'change' => 'Latest version', 'type' => 'positive']
        ];
    }
    
    /**
     * Get user's recent notifications
     */
    private function getUserNotifications($userId)
    {
        Log::debug('Fetching notifications for user', ['user_id' => $userId]);
        
        try {
            $notifications = Notifications::where('user_id', $userId)
                                        ->where('user_type', Users::class)
                                        ->orderBy('created_at', 'desc')
                                        ->take(5)
                                        ->get();
            
            Log::debug('Notifications fetched', [
                'user_id' => $userId,
                'count' => $notifications->count(),
                'unread_count' => $notifications->where('read', false)->count()
            ]);
            
            return $notifications;
        } catch (\Exception $e) {
            Log::error('Error fetching notifications', [
                'user_id' => $userId,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Return empty collection instead of crashing
            return collect();
        }
    }
}