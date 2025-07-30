<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StaffAttendance;
use App\Models\User;
use App\Models\Centre;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class StaffAttendanceController extends Controller
{
    /**
     * Display attendance dashboard
     */
    public function index(Request $request)
    {
        try {
            // Get current user's centre for data isolation
            $centreId = $request->get('centre') ?? session('centre_id');
            $userRole = session('role');
            $userId = session('id');

            // Determine which users to show based on role
            $query = User::query();
            
            if ($userRole === 'admin') {
                // Admin can see all users, filter by centre if specified
                if ($centreId) {
                    $query->where('centre_id', $centreId);
                } else {
                    $query->whereNotNull('id');
                }
            } elseif ($userRole === 'supervisor') {
                // Supervisor can see users in their centre or specified centre
                $query->where('centre_id', $centreId);
            } else {
                // Teachers and AJK can only see themselves
                $query->where('id', $userId);
            }

            $users = $query->with(['staffAttendances' => function($q) {
                $q->whereDate('attendance_date', Carbon::today())
                  ->orderBy('attendance_time', 'desc');
            }])->get();

            // Get today's attendance summary
            $todayAttendanceQuery = StaffAttendance::whereDate('attendance_date', Carbon::today());
            
            if ($userRole !== 'admin' && $centreId) {
                $todayAttendanceQuery->where('centre_id', $centreId);
            }
            
            $todayAttendance = $todayAttendanceQuery
                ->with(['user', 'markedBy'])
                ->orderBy('attendance_time', 'desc')
                ->get() ?? collect([]);

            // Get attendance statistics
            $stats = $this->getAttendanceStatistics($centreId, $userRole);

            return view('attendance.staffdashboard', compact('users', 'todayAttendance', 'stats'));

        } catch (\Exception $e) {
            Log::error('Error loading staff attendance dashboard', [
                'error' => $e->getMessage(),
                'user_id' => session('id'),
                'centre_id' => session('centre_id')
            ]);

            return redirect()->back()->with('error', 'Unable to load attendance dashboard.');
        }
    }

    /**
     * Mark attendance for self or others
     */
    public function markAttendance(Request $request)
    {
        try {
            Log::info('Mark attendance request received', [
                'request_data' => $request->all(),
                'session_user' => session('id')
            ]);
            
            $validated = $request->validate([
                'user_id' => 'required|integer|exists:users,id',
                'status' => 'required|in:present,absent,late,sick_leave,emergency_leave,authorized_leave',
                'attendance_type' => 'required|in:check_in,check_out',
                'remarks' => 'nullable|string|max:500'
            ]);

            $targetUserId = $validated['user_id'];
            $currentUserId = session('id');
            $currentUserEmail = session('email') ?? User::find($currentUserId)->email;
            $centreId = session('centre_id');

            // Check permissions
            if (!$this->canMarkAttendanceFor($targetUserId, $currentUserId)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to mark attendance for this user.'
                ], 403);
            }

            // Check if already marked today for this type
            $alreadyMarked = StaffAttendance::where('user_id', $targetUserId)
                ->whereDate('attendance_date', Carbon::today())
                ->where('attendance_type', $validated['attendance_type'])
                ->exists();

            if ($alreadyMarked) {
                return response()->json([
                    'success' => false,
                    'message' => 'Attendance already marked for today (' . $validated['attendance_type'] . ').'
                ]);
            }

            // Mark attendance
            $attendance = StaffAttendance::markAttendance(
                $targetUserId,
                $currentUserId,
                $currentUserEmail,
                $centreId,
                $validated['status'],
                $validated['attendance_type'],
                $validated['remarks']
            );

            Log::info('Staff attendance marked', [
                'attendance_id' => $attendance->id,
                'user_id' => $targetUserId,
                'marked_by' => $currentUserId,
                'status' => $validated['status'],
                'type' => $validated['attendance_type']
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Attendance marked successfully!',
                'attendance' => $attendance->load(['user', 'markedBy'])
            ]);

        } catch (\Exception $e) {
            Log::error('Error marking staff attendance', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all(),
                'user_id' => session('id')
            ]);

            // Check if it's a database table error
            if (str_contains($e->getMessage(), "doesn't exist") || str_contains($e->getMessage(), 'staff_attendances')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Staff attendance system is not properly configured. Please contact the administrator.'
                ], 500);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to mark attendance. Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get attendance for specific user
     */
    public function getUserAttendance(Request $request, $userId)
    {
        try {
            // Check permissions
            if (!$this->canViewAttendanceFor($userId, session('id'))) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied.'
                ], 403);
            }

            $startDate = $request->get('start_date', Carbon::now()->startOfMonth());
            $endDate = $request->get('end_date', Carbon::now()->endOfMonth());

            $attendance = StaffAttendance::where('user_id', $userId)
                ->dateRange($startDate, $endDate)
                ->with(['markedBy'])
                ->orderBy('attendance_date', 'desc')
                ->orderBy('attendance_time', 'desc')
                ->get();

            $stats = StaffAttendance::getAttendanceStats($userId, $startDate, $endDate);

            return response()->json([
                'success' => true,
                'attendance' => $attendance,
                'statistics' => $stats
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting user attendance', [
                'error' => $e->getMessage(),
                'user_id' => $userId,
                'requested_by' => session('id')
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to load attendance data.'
            ], 500);
        }
    }

    /**
     * Get today's attendance status for user
     */
    public function getTodayStatus($userId)
    {
        try {
            if (!$this->canViewAttendanceFor($userId, session('id'))) {
                return response()->json(['success' => false, 'message' => 'Access denied.'], 403);
            }

            $todayAttendance = StaffAttendance::getTodayAttendance($userId);
            $hasCheckedIn = StaffAttendance::hasCheckedInToday($userId);
            $hasCheckedOut = StaffAttendance::hasCheckedOutToday($userId);

            return response()->json([
                'success' => true,
                'today_attendance' => $todayAttendance,
                'has_checked_in' => $hasCheckedIn,
                'has_checked_out' => $hasCheckedOut,
                'can_check_in' => !$hasCheckedIn,
                'can_check_out' => $hasCheckedIn && !$hasCheckedOut
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting today attendance status', [
                'error' => $e->getMessage(),
                'user_id' => $userId
            ]);

            return response()->json(['success' => false, 'message' => 'Failed to load status.'], 500);
        }
    }

    /**
     * Check if current user can mark attendance for target user
     */
    private function canMarkAttendanceFor($targetUserId, $currentUserId)
    {
        $currentUserRole = session('role');
        $currentUserCentre = session('centre_id');

        // Users can always mark their own attendance
        if ($targetUserId == $currentUserId) {
            return true;
        }

        // Admin can mark for anyone
        if ($currentUserRole === 'admin') {
            return true;
        }

        // Supervisor can mark for staff in their centre
        if ($currentUserRole === 'supervisor') {
            $targetUser = User::find($targetUserId);
            return $targetUser && $targetUser->centre_id === $currentUserCentre && 
                   in_array($targetUser->role, ['teacher', 'ajk']);
        }

        // Teachers and AJK cannot mark for others
        return false;
    }

    /**
     * Check if current user can view attendance for target user
     */
    private function canViewAttendanceFor($targetUserId, $currentUserId)
    {
        $currentUserRole = session('role');
        $currentUserCentre = session('centre_id');

        // Users can always view their own attendance
        if ($targetUserId == $currentUserId) {
            return true;
        }

        // Admin can view anyone's attendance
        if ($currentUserRole === 'admin') {
            return true;
        }

        // Supervisor can view attendance for staff in their centre
        if ($currentUserRole === 'supervisor') {
            $targetUser = User::find($targetUserId);
            return $targetUser && $targetUser->centre_id === $currentUserCentre;
        }

        // Teachers and AJK cannot view others' attendance
        return false;
    }


    /**
     * Get attendance statistics
     */
    private function getAttendanceStatistics($centreId, $userRole)
    {
        $today = Carbon::today();
        $thisMonth = Carbon::now()->startOfMonth();

        $query = StaffAttendance::query();

        if ($userRole !== 'admin') {
            $query->forCentre($centreId);
        }

        return [
            'today_present' => (clone $query)->today()->where('status', 'present')->count(),
            'today_absent' => (clone $query)->today()->where('status', 'absent')->count(),
            'today_late' => (clone $query)->today()->where('status', 'late')->count(),
            'month_total' => (clone $query)->whereMonth('attendance_date', $today->month)->count(),
        ];
    }
}
