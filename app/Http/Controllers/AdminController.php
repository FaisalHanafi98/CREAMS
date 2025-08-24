<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Artisan;
use App\Models\User;
use App\Models\Admin;
use App\Models\Supervisor;
use App\Models\Teacher;
use App\Models\AJK;
use App\Models\Trainee;
use App\Models\Activity;
use App\Models\Centre;
use App\Models\Asset;
use App\Models\Message;
use App\Models\Notification;
use Carbon\Carbon;
use Exception;

class AdminController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:admin');
    }

    /**
     * Show the comprehensive admin dashboard with system-wide statistics
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        try {
            // Log the dashboard access
            Log::info('Admin dashboard accessed', [
                'user_id' => session('id'),
                'user_name' => session('name'),
                'timestamp' => now(),
                'ip_address' => request()->ip()
            ]);
            
            // Get comprehensive system statistics
            $systemStats = $this->getSystemStatistics();
            $recentActivity = $this->getRecentSystemActivity();
            $systemHealth = $this->getSystemHealthMetrics();
            $alerts = $this->getSystemAlerts();
            
            return view('admin.dashboard', [
                'name' => session('name'),
                'role' => 'admin',
                'systemStats' => $systemStats,
                'recentActivity' => $recentActivity,
                'systemHealth' => $systemHealth,
                'alerts' => $alerts
            ]);
            
        } catch (Exception $e) {
            Log::error('Admin dashboard error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => session('id')
            ]);
            
            return view('admin.dashboard', [
                'name' => session('name'),
                'role' => 'admin',
                'error' => 'Dashboard temporarily unavailable'
            ]);
        }
    }
    
    /**
     * Get comprehensive system statistics
     */
    private function getSystemStatistics()
    {
        try {
            $stats = [
                // User Statistics
                'users' => [
                    'total' => User::count(),
                    'admins' => User::where('role', 'admin')->count(),
                    'supervisors' => User::where('role', 'supervisor')->count(),
                    'teachers' => User::where('role', 'teacher')->count(),
                    'ajks' => User::where('role', 'ajk')->count(),
                    'active_today' => User::whereDate('last_login', today())->count(),
                    'new_this_month' => User::whereMonth('created_at', now()->month)->count()
                ],
                
                // Trainee Statistics
                'trainees' => [
                    'total' => Trainee::count(),
                    'active' => Trainee::where('status', 'active')->count(),
                    'enrolled_activities' => DB::table('session_enrollments')->where('status', 'Active')->count(),
                    'new_this_month' => Trainee::whereMonth('created_at', now()->month)->count()
                ],
                
                // Centre Statistics
                'centres' => [
                    'total' => Centre::count(),
                    'active' => Centre::where('is_active', true)->count(),
                    'assets_total' => Asset::count(),
                    'activities_total' => Activity::count()
                ],
                
                // System Activity
                'activity' => [
                    'messages_today' => Message::whereDate('created_at', today())->count(),
                    'logins_today' => User::whereDate('last_login', today())->count(),
                    'activities_this_week' => Activity::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count()
                ]
            ];
            
            return $stats;
            
        } catch (Exception $e) {
            Log::error('Error getting system statistics', ['error' => $e->getMessage()]);
            return [];
        }
    }
    
    /**
     * Get recent system activity
     */
    private function getRecentSystemActivity()
    {
        try {
            return [
                'recent_users' => User::latest()->limit(5)->get(['id', 'name', 'email', 'role', 'created_at']),
                'recent_trainees' => Trainee::latest()->limit(5)->get(['id', 'trainee_first_name', 'trainee_last_name', 'created_at']),
                'recent_activities' => Activity::latest()->limit(5)->get(['id', 'activity_name', 'created_at']),
                'recent_messages' => Message::with('sender')->latest()->limit(5)->get(['id', 'message_subject', 'sender_id', 'created_at'])
            ];
        } catch (Exception $e) {
            Log::error('Error getting recent activity', ['error' => $e->getMessage()]);
            return [];
        }
    }
    
    /**
     * Get system health metrics
     */
    private function getSystemHealthMetrics()
    {
        try {
            return [
                'database_status' => DB::connection()->getPdo() ? 'Connected' : 'Disconnected',
                'storage_usage' => $this->getStorageUsage(),
                'cache_status' => cache()->has('health_check') ? 'Active' : 'Inactive',
                'queue_jobs' => DB::table('jobs')->count(),
                'failed_jobs' => DB::table('failed_jobs')->count(),
                'log_size' => $this->getLogFileSize()
            ];
        } catch (Exception $e) {
            Log::error('Error getting system health', ['error' => $e->getMessage()]);
            return ['status' => 'Error checking system health'];
        }
    }
    
    /**
     * Get system alerts and warnings
     */
    private function getSystemAlerts()
    {
        $alerts = [];
        
        try {
            // Check for failed jobs
            $failedJobs = DB::table('failed_jobs')->count();
            if ($failedJobs > 0) {
                $alerts[] = [
                    'type' => 'warning',
                    'message' => "{$failedJobs} failed job(s) require attention",
                    'action' => 'View Failed Jobs'
                ];
            }
            
            // Check for inactive centres
            $inactiveCentres = Centre::where('is_active', false)->count();
            if ($inactiveCentres > 0) {
                $alerts[] = [
                    'type' => 'info',
                    'message' => "{$inactiveCentres} centre(s) are inactive",
                    'action' => 'Manage Centre'
                ];
            }
            
            // Check for overdue maintenance
            $overdueAssets = Asset::whereHas('maintenance', function($q) {
                $q->where('scheduled_date', '<', now())->where('status', 'scheduled');
            })->count();
            
            if ($overdueAssets > 0) {
                $alerts[] = [
                    'type' => 'warning',
                    'message' => "{$overdueAssets} asset(s) have overdue maintenance",
                    'action' => 'View Asset'
                ];
            }
            
        } catch (Exception $e) {
            Log::error('Error getting system alerts', ['error' => $e->getMessage()]);
        }
        
        return $alerts;
    }
    
    /**
     * Get storage usage information
     */
    private function getStorageUsage()
    {
        try {
            $path = storage_path();
            $bytes = disk_total_space($path);
            $free = disk_free_space($path);
            $used = $bytes - $free;
            
            return [
                'total' => $this->formatBytes($bytes),
                'used' => $this->formatBytes($used),
                'free' => $this->formatBytes($free),
                'percentage' => round(($used / $bytes) * 100, 2)
            ];
        } catch (Exception $e) {
            return ['error' => 'Unable to get storage info'];
        }
    }
    
    /**
     * Get log file size
     */
    private function getLogFileSize()
    {
        try {
            $logPath = storage_path('logs/laravel.log');
            if (file_exists($logPath)) {
                return $this->formatBytes(filesize($logPath));
            }
            return 'No log file';
        } catch (Exception $e) {
            return 'Error reading log';
        }
    }
    
    /**
     * Format bytes to human readable format
     */
    private function formatBytes($size, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $size > 1024 && $i < count($units) - 1; $i++) {
            $size /= 1024;
        }
        
        return round($size, $precision) . ' ' . $units[$i];
    }
    
    
    /**
     * Show comprehensive user management with filtering and search
     */
    public function manageUsers(Request $request)
    {
        try {
            $search = $request->get('search');
            $role = $request->get('role');
            $centre = $request->get('centre');
            $status = $request->get('status', 'all');
            
            // Build user query
            $usersQuery = User::with(['centre']);
            
            // Apply filters
            if ($search) {
                $usersQuery->where(function($q) use ($search) {
                    $q->where('name', 'LIKE', "%{$search}%")
                      ->orWhere('email', 'LIKE', "%{$search}%")
                      ->orWhere('phone', 'LIKE', "%{$search}%");
                });
            }
            
            if ($role) {
                $usersQuery->where('role', $role);
            }
            
            if ($centre) {
                $usersQuery->where('centre_id', $centre);
            }
            
            if ($status !== 'all') {
                $usersQuery->where('is_active', $status === 'active');
            }
            
            $users = $usersQuery->orderBy('created_at', 'desc')->paginate(20);
            
            // Get filter options
            $centres = Centre::all(['id', 'centre_name']);
            $roles = ['admin', 'supervisor', 'teacher', 'ajk', 'trainee'];
            
            // Get user statistics
            $userStats = [
                'total' => User::count(),
                'active' => User::where('is_active', true)->count(),
                'inactive' => User::where('is_active', false)->count(),
                'by_role' => User::select('role', DB::raw('count(*) as count'))
                    ->groupBy('role')->get()->pluck('count', 'role')
            ];
            
            return view('admin.users.index', compact(
                'users', 'centres', 'roles', 'userStats',
                'search', 'role', 'centre', 'status'
            ));
            
        } catch (Exception $e) {
            Log::error('Error in user management', [
                'error' => $e->getMessage(),
                'user_id' => session('id')
            ]);
            
            return back()->with('error', 'Error loading user management page');
        }
    }
    
    /**
     * Show user creation form
     */
    public function createUser()
    {
        $centres = Centre::where('is_active', true)->get(['id', 'centre_name']);
        $roles = ['admin', 'supervisor', 'teacher', 'ajk'];
        
        return view('admin.users.registration', compact('centres', 'roles'));
    }
    
    /**
     * Store new user
     */
    public function storeUser(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'phone' => 'nullable|string|max:20',
                'role' => 'required|in:admin,supervisor,teacher,ajk',
                'centre_id' => 'required|exists:centres,id',
                'password' => 'required|string|min:8|confirmed',
                'is_active' => 'boolean'
            ]);
            
            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }
            
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'role' => $request->role,
                'centre_id' => $request->centre_id,
                'password' => Hash::make($request->password),
                'is_active' => $request->has('is_active'),
                'created_by' => session('id')
            ]);
            
            Log::info('User created by admin', [
                'created_user_id' => $user->id,
                'created_by' => session('id'),
                'role' => $request->role
            ]);
            
            return redirect()->route('admin.users.show', $user->id)
                ->with('success', 'User created successfully');
                
        } catch (Exception $e) {
            Log::error('Error creating user', [
                'error' => $e->getMessage(),
                'admin_id' => session('id')
            ]);
            
            return back()->with('error', 'Error creating user')->withInput();
        }
    }
    
    /**
     * Show specific user details
     */
    public function showUser(User $user)
    {
        try {
            $user->load(['centre']);
            
            // Get user activity stats
            $userStats = [
                'login_count' => DB::table('user_login_logs')->where('user_id', $user->id)->count(),
                'last_login' => $user->last_login,
                'messages_sent' => Message::where('sender_id', $user->id)->count(),
                'activities_created' => Activity::where('created_by', $user->id)->count()
            ];
            
            return view('admin.users.show', compact('user', 'userStats'));
            
        } catch (Exception $e) {
            Log::error('Error showing user', [
                'error' => $e->getMessage(),
                'user_id' => $user->id
            ]);
            
            return back()->with('error', 'Error loading user details');
        }
    }
    
    /**
     * Show user edit form
     */
    public function editUser(User $user)
    {
        $centres = Centre::where('is_active', true)->get(['id', 'centre_name']);
        $roles = ['admin', 'supervisor', 'teacher', 'ajk'];
        
        return view('admin.users.edit', compact('user', 'centres', 'roles'));
    }
    
    /**
     * Update user
     */
    public function updateUser(Request $request, User $user)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email,' . $user->id,
                'phone' => 'nullable|string|max:20',
                'role' => 'required|in:admin,supervisor,teacher,ajk',
                'centre_id' => 'required|exists:centres,id',
                'password' => 'nullable|string|min:8|confirmed',
                'is_active' => 'boolean'
            ]);
            
            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }
            
            $updateData = [
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'role' => $request->role,
                'centre_id' => $request->centre_id,
                'is_active' => $request->has('is_active'),
                'updated_by' => session('id')
            ];
            
            if ($request->filled('password')) {
                $updateData['password'] = Hash::make($request->password);
            }
            
            $user->update($updateData);
            
            Log::info('User updated by admin', [
                'updated_user_id' => $user->id,
                'updated_by' => session('id'),
                'changes' => $request->only(['name', 'email', 'role', 'centre_id', 'is_active'])
            ]);
            
            return redirect()->route('admin.users.show', $user->id)
                ->with('success', 'User updated successfully');
                
        } catch (Exception $e) {
            Log::error('Error updating user', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'admin_id' => session('id')
            ]);
            
            return back()->with('error', 'Error updating user')->withInput();
        }
    }
    
    /**
     * Deactivate user (soft delete)
     */
    public function deactivateUser(User $user)
    {
        try {
            // Prevent admins from deactivating themselves
            if ($user->id == session('id')) {
                return back()->with('error', 'You cannot deactivate your own account');
            }
            
            $user->update([
                'is_active' => false,
                'deactivated_by' => session('id'),
                'deactivated_at' => now()
            ]);
            
            Log::warning('User deactivated by admin', [
                'deactivated_user_id' => $user->id,
                'deactivated_by' => session('id')
            ]);
            
            return back()->with('success', 'User deactivated successfully');
            
        } catch (Exception $e) {
            Log::error('Error deactivating user', [
                'error' => $e->getMessage(),
                'user_id' => $user->id
            ]);
            
            return back()->with('error', 'Error deactivating user');
        }
    }
    
    /**
     * Bulk user operations
     */
    public function bulkUserAction(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'user_ids' => 'required|array',
                'user_ids.*' => 'exists:users,id',
                'action' => 'required|in:activate,deactivate,change_centre,delete'
            ]);
            
            if ($validator->fails()) {
                return back()->withErrors($validator);
            }
            
            $userIds = $request->user_ids;
            $action = $request->action;
            $affectedCount = 0;
            
            // Prevent admin from affecting their own account in bulk operations
            $currentUserId = session('id');
            $userIds = array_filter($userIds, function($id) use ($currentUserId) {
                return $id != $currentUserId;
            });
            
            switch ($action) {
                case 'activate':
                    User::whereIn('id', $userIds)->update(['is_active' => true]);
                    $affectedCount = count($userIds);
                    break;
                    
                case 'deactivate':
                    User::whereIn('id', $userIds)->update([
                        'is_active' => false,
                        'deactivated_by' => session('id'),
                        'deactivated_at' => now()
                    ]);
                    $affectedCount = count($userIds);
                    break;
                    
                case 'change_centre':
                    if ($request->filled('centre_id')) {
                        User::whereIn('id', $userIds)->update(['centre_id' => $request->centre_id]);
                        $affectedCount = count($userIds);
                    }
                    break;
            }
            
            Log::info('Bulk user action performed', [
                'action' => $action,
                'affected_count' => $affectedCount,
                'user_ids' => $userIds,
                'performed_by' => session('id')
            ]);
            
            return back()->with('success', "Successfully {$action}d {$affectedCount} user(s)");
            
        } catch (Exception $e) {
            Log::error('Error in bulk user action', [
                'error' => $e->getMessage(),
                'admin_id' => session('id')
            ]);
            
            return back()->with('error', 'Error performing bulk action');
        }
    }
    
    /**
     * Show the trainee management page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function manageTrainees()
    {
        // Check if Trainee model exists and get trainees
        $trainees = class_exists('App\Models\Trainee') ? Trainee::all() : collect();
        
        return view('admin.trainees', [
            'name' => Auth::user()->name ?? session('name'),
            'trainees' => $trainees
        ]);
    }
    
    /**
     * Show the reports page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function reports()
    {
        return view('admin.reports', [
            'name' => Auth::user()->name ?? session('name')
        ]);
    }
    
    /**
     * Show the analytics page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function analytics()
    {
        return view('admin.analytics', [
            'name' => Auth::user()->name ?? session('name')
        ]);
    }
    
    /**
     * Show the settings page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function settings()
    {
        return view('admin.settings', [
            'name' => Auth::user()->name ?? session('name')
        ]);
    }
    
    /**
     * Show the activity management page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function activities()
    {
        return view('admin.activities', [
            'name' => Auth::user()->name ?? session('name')
        ]);
    }
    
    /**
     * Show the activity creation page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function createActivity()
    {
        return view('admin.activities.create', [
            'name' => Auth::user()->name ?? session('name')
        ]);
    }
    
    /**
     * Show the activity categories page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function activityCategories()
    {
        return view('admin.activities.categories', [
            'name' => Auth::user()->name ?? session('name')
        ]);
    }
    
    /**
     * Show the activity schedule page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function activitySchedule()
    {
        return view('admin.activities.schedule', [
            'name' => Auth::user()->name ?? session('name')
        ]);
    }
    
    /**
     * Show the centres management page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function centres()
    {
        return view('admin.centres', [
            'name' => Auth::user()->name ?? session('name')
        ]);
    }
    
    /**
     * Show the assets management page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function assets()
    {
        return view('admin.assets', [
            'name' => Auth::user()->name ?? session('name')
        ]);
    }
    
    /**
     * Show the logs page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function logs()
    {
        return view('admin.logs', [
            'name' => Auth::user()->name ?? session('name')
        ]);
    }
    
    /**
     * Show the profile page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function profile()
    {
        return view('admin.profile', [
            'name' => Auth::user()->name ?? session('name'),
            'user' => Auth::user() ?: null
        ]);
    }
}