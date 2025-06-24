<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Admins;
use App\Models\Supervisors;
use App\Models\Teachers;
use App\Models\AJKs;
use App\Models\Trainees;
use App\Models\Centres;
use App\Models\Activities;
use App\Models\Assets;
use App\Models\Messages;
use App\Models\Notifications;

class ApiController extends Controller
{
    /**
     * Get centres information.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCentres()
    {
        $centres = Centres::where('centre_status', true)->get();
        
        return response()->json([
            'success' => true,
            'data' => $centres
        ]);
    }
    
    /**
     * Get public activities.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPublicActivities()
    {
        $activities = Activities::where('is_public', true)
            ->with('centre')
            ->orderBy('activity_date', 'desc')
            ->take(5)
            ->get();
            
        return response()->json([
            'success' => true,
            'data' => $activities
        ]);
    }
    
    /**
     * Get user profile.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUserProfile()
    {
        $user = null;
        $role = session('role');
        $id = session('id');
        
        if (!$role || !$id) {
            return response()->json([
                'success' => false,
                'message' => 'User not authenticated'
            ], 401);
        }
        
        switch ($role) {
            case 'admin':
                $user = Admins::find($id);
                break;
            case 'supervisor':
                $user = Supervisors::with('centre')->find($id);
                break;
            case 'teacher':
                $user = Teachers::with('centre')->find($id);
                break;
            case 'ajk':
                $user = AJKs::with('centre')->find($id);
                break;
            default:
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid user role'
                ], 400);
        }
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }
        
        // Prepare user data
        $userData = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $role,
            'iium_id' => $user->iium_id,
            'phone' => $user->phone,
            'address' => $user->address,
            'position' => $user->position,
            'avatar' => $user->avatar ? asset('storage/avatars/' . $user->avatar) : asset('images/default-avatar.png'),
        ];
        
        // Add centre information if available
        if (isset($user->centre)) {
            $userData['centre'] = [
                'id' => $user->centre->centre_id,
                'name' => $user->centre->centre_name,
                'address' => $user->centre->address,
                'phone_number' => $user->centre->phone_number,
            ];
        }
        
        return response()->json([
            'success' => true,
            'data' => $userData
        ]);
    }
    
    /**
     * Update user profile.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateUserProfile(Request $request)
    {
        $user = null;
        $role = session('role');
        $id = session('id');
        
        if (!$role || !$id) {
            return response()->json([
                'success' => false,
                'message' => 'User not authenticated'
            ], 401);
        }
        
        switch ($role) {
            case 'admin':
                $user = Admins::find($id);
                break;
            case 'supervisor':
                $user = Supervisors::find($id);
                break;
            case 'teacher':
                $user = Teachers::find($id);
                break;
            case 'ajk':
                $user = AJKs::find($id);
                break;
            default:
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid user role'
                ], 400);
        }
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }
        
        // Validate request data
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:' . $role . 's,email,' . $id,
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'position' => 'nullable|string|max:100',
        ]);
        
        // Update user information
        $user->name = $request->name;
        $user->email = $request->email;
        $user->phone = $request->phone;
        $user->address = $request->address;
        $user->position = $request->position;
        
        $user->save();
        
        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'data' => $user
        ]);
    }
    
    /**
     * Get dashboard statistics.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDashboardStats()
    {
        $role = session('role');
        $id = session('id');
        
        if (!$role || !$id) {
            return response()->json([
                'success' => false,
                'message' => 'User not authenticated'
            ], 401);
        }
        
        $stats = [];
        
        // Common stats for all roles
        $stats['totalCentres'] = Centres::count();
        $stats['totalTrainees'] = Trainees::count();
        
        // Role-specific stats
        switch ($role) {
            case 'admin':
                $stats['supervisorCount'] = Supervisors::count();
                $stats['teacherCount'] = Teachers::count();
                $stats['ajkCount'] = AJKs::count();
                $stats['activeCentres'] = Centres::where('centre_status', true)->count();
                $stats['totalActivities'] = Activities::count();
                $stats['totalAssets'] = Assets::count();
                
                // Monthly registration stats
                $stats['monthlyRegistrations'] = DB::table('trainees')
                    ->selectRaw('MONTH(created_at) as month, COUNT(*) as count')
                    ->whereYear('created_at', date('Y'))
                    ->groupBy('month')
                    ->orderBy('month')
                    ->get();
                
                break;
                
            case 'supervisor':
                $user = Supervisors::find($id);
                $centreId = $user->centre_id;
                
                $stats['teacherCount'] = Teachers::where('centre_id', $centreId)->count();
                $stats['traineesInCentre'] = Trainees::where('centre_id', $centreId)->count();
                $stats['activitiesInCentre'] = Activities::where('centre_id', $centreId)->count();
                $stats['assetsInCentre'] = Assets::where('centre_id', $centreId)->count();
                
                break;
                
            case 'teacher':
                $user = Teachers::find($id);
                $centreId = $user->centre_id;
                
                // Count of trainees assigned to this teacher
                $stats['assignedTrainees'] = Trainees::where('user_id', $id)->count();
                
                // Count of activities this teacher is involved in
                $stats['teacherActivities'] = Activities::where('teacher_id', $id)->count();
                
                // Recent attendance statistics
                $stats['attendanceStats'] = DB::table('attendances')
                    ->join('activities', 'attendances.activity_id', '=', 'activities.id')
                    ->where('activities.teacher_id', $id)
                    ->selectRaw('DATE(attendances.date) as date, COUNT(*) as total, SUM(CASE WHEN attendances.status = "present" THEN 1 ELSE 0 END) as present')
                    ->groupBy('date')
                    ->orderBy('date', 'desc')
                    ->limit(7)
                    ->get();
                
                break;
                
            case 'ajk':
                $user = AJKs::find($id);
                $centreId = $user->centre_id;
                
                // Events organized by this AJK
                $stats['eventsOrganized'] = DB::table('events')
                    ->where('ajk_id', $id)
                    ->count();
                
                // Volunteers managed by this AJK
                $stats['volunteersManaged'] = DB::table('volunteers')
                    ->where('ajk_id', $id)
                    ->count();
                
                break;
        }
        
        // Recent activities for all roles
        $recentActivities = Activities::orderBy('activity_date', 'desc')
            ->take(5)
            ->with('centre')
            ->get();
            
        $stats['recentActivities'] = $recentActivities;
        
        // Unread messages count
        $stats['unreadMessages'] = Messages::where('recipient_id', $id)
            ->where('recipient_type', $role)
            ->where('read', false)
            ->count();
            
        // Unread notifications count
        $stats['unreadNotifications'] = Notifications::where('user_id', $id)
            ->where('user_type', $role)
            ->where('read', false)
            ->count();
            
        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }
    
    /**
     * Search users by name, email, or IIUM ID.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function searchUsers(Request $request)
    {
        $query = $request->input('query');
        $roleFilter = $request->input('role');
        
        if (!$query) {
            return response()->json([
                'success' => false,
                'message' => 'Search query is required'
            ], 400);
        }
        
        $results = collect();
        
        // Search in each user model
        if (!$roleFilter || $roleFilter === 'admin') {
            $admins = Admins::where('name', 'like', "%{$query}%")
                ->orWhere('email', 'like', "%{$query}%")
                ->orWhere('iium_id', 'like', "%{$query}%")
                ->get()
                ->map(function ($item) {
                    $item->role = 'admin';
                    return $item;
                });
            $results = $results->concat($admins);
        }
        
        if (!$roleFilter || $roleFilter === 'supervisor') {
            $supervisors = Supervisors::where('name', 'like', "%{$query}%")
                ->orWhere('email', 'like', "%{$query}%")
                ->orWhere('iium_id', 'like', "%{$query}%")
                ->with('centre')
                ->get()
                ->map(function ($item) {
                    $item->role = 'supervisor';
                    return $item;
                });
            $results = $results->concat($supervisors);
        }
        
        if (!$roleFilter || $roleFilter === 'teacher') {
            $teachers = Teachers::where('name', 'like', "%{$query}%")
                ->orWhere('email', 'like', "%{$query}%")
                ->orWhere('iium_id', 'like', "%{$query}%")
                ->with('centre')
                ->get()
                ->map(function ($item) {
                    $item->role = 'teacher';
                    return $item;
                });
            $results = $results->concat($teachers);
        }
        
        if (!$roleFilter || $roleFilter === 'ajk') {
            $ajks = AJKs::where('name', 'like', "%{$query}%")
                ->orWhere('email', 'like', "%{$query}%")
                ->orWhere('iium_id', 'like', "%{$query}%")
                ->with('centre')
                ->get()
                ->map(function ($item) {
                    $item->role = 'ajk';
                    return $item;
                });
            $results = $results->concat($ajks);
        }
        
        return response()->json([
            'success' => true,
            'data' => $results
        ]);
    }
    
    /**
     * Get centre data with associated users and activities.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCentreData($id)
    {
        $centre = Centres::findOrFail($id);
        
        // Get users associated with this centre
        $supervisors = Supervisors::where('centre_id', $id)->get();
        $teachers = Teachers::where('centre_id', $id)->get();
        $ajks = AJKs::where('centre_id', $id)->get();
        
        // Get activities for this centre
        $activities = Activities::where('centre_id', $id)
            ->orderBy('activity_date', 'desc')
            ->get();
            
        // Get trainees for this centre
        $trainees = Trainees::where('centre_id', $id)->get();
        
        // Get assets for this centre
        $assets = Assets::where('centre_id', $id)->get();
        
        // Centre statistics
        $stats = [
            'supervisorCount' => $supervisors->count(),
            'teacherCount' => $teachers->count(),
            'ajkCount' => $ajks->count(),
            'traineeCount' => $trainees->count(),
            'activityCount' => $activities->count(),
            'assetCount' => $assets->count(),
        ];
        
        return response()->json([
            'success' => true,
            'data' => [
                'centre' => $centre,
                'supervisors' => $supervisors,
                'teachers' => $teachers,
                'ajks' => $ajks,
                'activities' => $activities->take(10),
                'stats' => $stats
            ]
        ]);
    }
    
    /**
     * Get user activities.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUserActivities(Request $request)
    {
        $role = session('role');
        $id = session('id');
        
        if (!$role || !$id) {
            return response()->json([
                'success' => false,
                'message' => 'User not authenticated'
            ], 401);
        }
        
        $activities = collect();
        
        switch ($role) {
            case 'admin':
                // Admins see all activities
                $activities = Activities::orderBy('activity_date', 'desc')
                    ->with(['centre', 'teacher'])
                    ->paginate(10);
                break;
                
            case 'supervisor':
                $user = Supervisors::find($id);
                // Supervisors see activities in their centre
                $activities = Activities::where('centre_id', $user->centre_id)
                    ->orderBy('activity_date', 'desc')
                    ->with(['centre', 'teacher'])
                    ->paginate(10);
                break;
                
            case 'teacher':
                // Teachers see activities they're assigned to
                $activities = Activities::where('teacher_id', $id)
                    ->orderBy('activity_date', 'desc')
                    ->with(['centre', 'teacher'])
                    ->paginate(10);
                break;
                
            case 'ajk':
                $user = AJKs::find($id);
                // AJKs see activities in their centre
                $activities = Activities::where('centre_id', $user->centre_id)
                    ->orderBy('activity_date', 'desc')
                    ->with(['centre', 'teacher'])
                    ->paginate(10);
                break;
        }
        
        return response()->json([
            'success' => true,
            'data' => $activities
        ]);
    }
    
    /**
     * Get user notifications.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUserNotifications(Request $request)
    {
        $role = session('role');
        $id = session('id');
        
        if (!$role || !$id) {
            return response()->json([
                'success' => false,
                'message' => 'User not authenticated'
            ], 401);
        }
        
        $notifications = Notifications::where('user_id', $id)
            ->where('user_type', $role)
            ->orderBy('created_at', 'desc')
            ->paginate(10);
            
        return response()->json([
            'success' => true,
            'data' => $notifications
        ]);
    }
    
    /**
     * Mark notification as read.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function markNotificationRead($id)
    {
        $role = session('role');
        $userId = session('id');
        
        if (!$role || !$userId) {
            return response()->json([
                'success' => false,
                'message' => 'User not authenticated'
            ], 401);
        }
        
        $notification = Notifications::findOrFail($id);
        
        // Ensure the notification belongs to the user
        if ($notification->user_id != $userId || $notification->user_type != $role) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to notification'
            ], 403);
        }
        
        $notification->read = true;
        $notification->save();
        
        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read'
        ]);
    }
}