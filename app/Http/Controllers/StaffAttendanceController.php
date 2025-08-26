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
            // Check if user is admin - only admins can access attendance dashboard
            $userRole = session('role');
            if (!in_array($userRole, ['admin'])) {
                Log::warning('Unauthorized attendance dashboard access attempt', [
                    'user_id' => session('id'),
                    'role' => $userRole,
                    'ip' => $request->ip()
                ]);
                
                return redirect()->route('dashboard')->with('error', 'Access denied. Only administrators can access the attendance dashboard.');
            }
            
            // Auto-select Gombak centre (01) by default, but allow switching
            $selectedCentreId = $request->get('centre') ?? '01'; // Default to Gombak
            $userId = session('id');

            // Get only the main 5 centres for navigation (excluding Nilai and Cyberjaya for now)
            $centres = Centre::where('centre_status', 'active')
                ->whereNotIn('centre_id', ['06', '07']) // Exclude Nilai and Cyberjaya
                ->orderBy('centre_name')
                ->get();
            $selectedCentre = Centre::find($selectedCentreId);

            // Get staff for the selected centre
            $staffQuery = User::where('centre_id', $selectedCentreId)
                ->whereIn('role', ['admin', 'supervisor', 'teacher', 'ajk']);
            
            $staff = $staffQuery->with(['staffAttendances' => function($q) {
                $q->whereDate('attendance_date', Carbon::today())
                  ->orderBy('check_in_time', 'desc');
            }])->get();

            // Get trainees for the selected centre
            $trainees = \App\Models\Trainee::where('centre_id', $selectedCentreId)
                ->with(['traineeAttendances' => function($q) {
                    $q->whereDate('attendance_date', Carbon::today())
                      ->with('markedBy'); // Include the user who marked the attendance
                }])
                ->get();

            // Get today's staff attendance summary
            $todayStaffAttendance = StaffAttendance::whereDate('attendance_date', Carbon::today())
                ->whereHas('user', function($q) use ($selectedCentreId) {
                    $q->where('centre_id', $selectedCentreId);
                })
                ->with(['user', 'markedBy'])
                ->orderBy('check_in_time', 'desc')
                ->get();

            // Get attendance statistics for the selected centre
            $stats = $this->getAttendanceStatistics($selectedCentreId, $userRole);
            
            // Add trainee statistics
            $traineeStats = $this->getTraineeAttendanceStatistics($selectedCentreId);

            return view('attendance.dashboard', compact(
                'staff', 
                'trainees', 
                'centres', 
                'selectedCentre', 
                'selectedCentreId',
                'todayStaffAttendance', 
                'stats',
                'traineeStats'
            ));

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
        // Debug logging at the very start
        Log::info('=== STAFF ATTENDANCE MARKING STARTED ===', [
            'timestamp' => now(),
            'request_data' => $request->all(),
            'session_id' => session('id'),
            'session_role' => session('role'),
            'session_centre_id' => session('centre_id'),
            'request_method' => $request->method(),
            'request_url' => $request->fullUrl(),
        ]);

        try {
            
            $validated = $request->validate([
                'user_id' => 'required|integer|exists:users,id',
                'status' => 'required|in:present,absent,late,sick_leave,emergency_leave,authorized_leave',
                'remarks' => 'nullable|string|max:500'
            ]);

            $targetUserId = $validated['user_id'];
            $currentUserId = session('id');
            $currentUser = User::find($currentUserId);
            $currentUserEmail = session('email') ?? $currentUser->email;
            $centreId = session('centre_id');

            // Debug logging for staff attendance
            $targetUser = User::find($targetUserId);
            Log::info('Staff attendance permission check', [
                'current_user_id' => $currentUserId,
                'current_user_centre_id' => $centreId,
                'current_user_role' => session('role'),
                'target_user_id' => $targetUserId,
                'target_user_centre_id' => $targetUser->centre_id ?? 'NULL',
                'target_user_name' => $targetUser->name ?? 'Unknown',
                'can_mark' => $this->canMarkAttendanceFor($targetUserId, $currentUserId)
            ]);

            // Check permissions
            if (!$this->canMarkAttendanceFor($targetUserId, $currentUserId)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to mark attendance for this user.'
                ], 403);
            }

            // Get target user and centre for policy validation
            $targetUser = User::find($targetUserId);
            $centre = Centre::find($targetUser->centre_id);
            
            // Validate attendance against centre policies
            $policyValidation = $this->validateAgainstCentrePolicies($centre, $validated, $targetUser);
            if (!$policyValidation['valid']) {
                return response()->json([
                    'success' => false,
                    'message' => $policyValidation['message']
                ]);
            }

            // Check if already marked today
            $existingAttendance = StaffAttendance::where('user_id', $targetUserId)
                ->whereDate('attendance_date', Carbon::today())
                ->first();

            if ($existingAttendance && $currentUserId !== $targetUserId && session('role') === 'admin') {
                // Admin can update existing attendance status
                $existingAttendance->update([
                    'status' => $validated['status'],
                    'remarks' => $validated['remarks'],
                    'marked_by_user_id' => $currentUserId,
                    'marked_by_email' => $currentUserEmail,
                    'attendance_time' => now()->format('H:i:s')
                ]);

                Log::info('Staff attendance updated by admin', [
                    'attendance_id' => $existingAttendance->id,
                    'user_id' => $targetUserId,
                    'updated_by' => $currentUserId,
                    'new_status' => $validated['status']
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Attendance status updated successfully!',
                    'attendance' => $existingAttendance->load(['user', 'markedBy'])
                ]);
            } elseif ($existingAttendance) {
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
     * Get today's attendance status for a user
     */
    public function getAttendanceStatus($encryptedUserId)
    {
        try {
            // Decrypt the user ID for security
            try {
                $userId = decrypt($encryptedUserId);
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or expired link.'
                ], 400);
            }
            
            // Check permissions
            if (!$this->canViewAttendanceFor($userId, session('id'))) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied.'
                ], 403);
            }

            // Get today's attendance record
            $todayAttendance = StaffAttendance::where('user_id', $userId)
                ->whereDate('attendance_date', Carbon::today())
                ->first();

            $response = [
                'success' => true,
                'has_checked_in' => false,
                'has_checked_out' => false,
                'check_in_time' => null,
                'check_out_time' => null,
                'status' => null
            ];

            if ($todayAttendance) {
                $response['has_checked_in'] = !is_null($todayAttendance->check_in_time);
                $response['has_checked_out'] = !is_null($todayAttendance->check_out_time);
                $response['check_in_time'] = $todayAttendance->check_in_time;
                $response['check_out_time'] = $todayAttendance->check_out_time;
                $response['status'] = $todayAttendance->status;
            }

            return response()->json($response);

        } catch (\Exception $e) {
            Log::error('Error getting attendance status', [
                'error' => $e->getMessage(),
                'encrypted_user_id' => $encryptedUserId,
                'user_id' => session('id')
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to get attendance status.'
            ], 500);
        }
    }

    /**
     * Get attendance for specific user
     */
    public function getUserAttendance(Request $request, $encryptedUserId)
    {
        try {
            // Decrypt the user ID for security
            try {
                $userId = decrypt($encryptedUserId);
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or expired link.'
                ], 400);
            }
            
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
                ->orderBy('check_in_time', 'desc')
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
    public function getTodayStatus($encryptedUserId)
    {
        try {
            // Decrypt the user ID for security
            try {
                $userId = decrypt($encryptedUserId);
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or expired link.'
                ], 400);
            }
            
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

        // Admin can mark for staff in their centre only
        if ($currentUserRole === 'admin') {
            $targetUser = User::find($targetUserId);
            // Use loose comparison to handle potential type differences
            return $targetUser && $targetUser->centre_id == $currentUserCentre;
        }

        // Supervisor can mark for staff in their centre
        if ($currentUserRole === 'supervisor') {
            $targetUser = User::find($targetUserId);
            return $targetUser && $targetUser->centre_id == $currentUserCentre && 
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
     * Get trainee attendance statistics
     */
    private function getTraineeAttendanceStatistics($centreId)
    {
        $today = Carbon::today();
        
        $totalTrainees = \App\Models\Trainee::where('centre_id', $centreId)->count();
        
        $todayAttendance = DB::table('trainee_attendances')
            ->join('trainees', 'trainee_attendances.trainee_id', '=', 'trainees.id')
            ->where('trainees.centre_id', $centreId)
            ->whereDate('trainee_attendances.attendance_date', $today)
            ->select('trainee_attendances.*')
            ->get();

        return [
            'total_trainees' => $totalTrainees,
            'today_present' => $todayAttendance->where('status', 'present')->count(),
            'today_absent' => $todayAttendance->where('status', 'absent')->count(),
            'today_late' => $todayAttendance->where('status', 'late')->count(),
            'today_excused' => $todayAttendance->where('status', 'excused')->count(),
        ];
    }

    /**
     * Get staff attendance statistics
     */
    private function getAttendanceStatistics($centreId, $userRole)
    {
        $today = Carbon::today();
        $thisMonth = Carbon::now()->startOfMonth();

        $query = StaffAttendance::query();

        // Always filter by centre when a centre is selected
        if ($centreId) {
            $query->forCentre($centreId);
        }

        return [
            'today_present' => (clone $query)->today()->where('status', 'present')->count(),
            'today_absent' => (clone $query)->today()->where('status', 'absent')->count(),
            'today_late' => (clone $query)->today()->where('status', 'late')->count(),
            'month_total' => (clone $query)->whereMonth('attendance_date', $today->month)->count(),
        ];
    }

    /**
     * Validate attendance against centre-specific policies
     *
     * @param Centre $centre
     * @param array $validated
     * @param User $targetUser
     * @return array
     */
    private function validateAgainstCentrePolicies($centre, $validated, $targetUser)
    {
        if (!$centre) {
            return ['valid' => true, 'message' => '']; // No centre policies to validate
        }

        $policies = $centre->getAttendancePolicies();
        $currentTime = Carbon::now();
        $today = Carbon::today();

        // Check if it's a work day
        if (!$centre->isWorkDay($today)) {
            return [
                'valid' => false,
                'message' => 'Today is not a scheduled work day for this centre.'
            ];
        }

        // Check office hours for attendance
        $officeStart = Carbon::parse($today->format('Y-m-d') . ' ' . $policies['office_hours']['start_time']);
        $officeEnd = Carbon::parse($today->format('Y-m-d') . ' ' . $policies['office_hours']['end_time']);
        
        // Allow check-in up to 2 hours before office start
        $earliestCheckIn = $officeStart->copy()->subHours(2);
        
        if ($currentTime->lessThan($earliestCheckIn)) {
            return [
                'valid' => false,
                'message' => 'Check-in is too early. Office hours start at ' . $policies['office_hours']['start_time'] . '.'
            ];
        }

        // Auto-suggest late status if checking in late
        if ($centre->isLateCheckIn($currentTime) && $validated['status'] === 'present') {
            return [
                'valid' => false,
                'message' => 'You are checking in late. Please select "Late" status or check-in before ' . 
                             $officeStart->addMinutes($policies['office_hours']['late_threshold_minutes'])->format('H:i') . '.'
            ];
        }

        // Check leave policies
        if (in_array($validated['status'], ['sick_leave', 'emergency_leave', 'authorized_leave'])) {
            $leaveType = $validated['status'];
            $requiresApproval = $policies['leave_policies'][$leaveType . '_require_approval'] ?? false;
            
            if ($requiresApproval && session('role') !== 'admin') {
                return [
                    'valid' => false,
                    'message' => ucfirst(str_replace('_', ' ', $leaveType)) . ' requires supervisor or admin approval.'
                ];
            }
        }

        // Check self-marking policy
        if ($targetUser->id === session('id')) {
            $allowSelfMarking = $policies['attendance_rules']['allow_self_marking'] ?? true;
            if (!$allowSelfMarking && session('role') !== 'admin') {
                return [
                    'valid' => false,
                    'message' => 'Self-marking attendance is not allowed in this centre. Please contact your supervisor.'
                ];
            }
        }

        return ['valid' => true, 'message' => ''];
    }

    /**
     * Mark trainee attendance (admin only)
     */
    public function markTraineeAttendance(Request $request)
    {
        // Debug logging at the very start
        Log::info('=== TRAINEE ATTENDANCE MARKING STARTED ===', [
            'timestamp' => now(),
            'request_data' => $request->all(),
            'session_id' => session('id'),
            'session_role' => session('role'),
            'session_centre_id' => session('centre_id'),
            'request_method' => $request->method(),
            'request_url' => $request->fullUrl(),
            'request_headers' => $request->headers->all()
        ]);

        try {
            // Only admin can mark trainee attendance
            if (session('role') !== 'admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only administrators can mark trainee attendance.'
                ], 403);
            }

            $validated = $request->validate([
                'trainee_id' => 'required|integer|exists:trainees,id',
                'status' => 'required|in:present,absent,late,excused',
                'activity_id' => 'nullable|exists:activities,id',
                'remarks' => 'nullable|string|max:500'
            ]);

            $currentUserId = session('id');
            $currentUserCentreId = session('centre_id');
            $currentUserEmail = session('email') ?? User::find($currentUserId)->email;
            $trainee = \App\Models\Trainee::find($validated['trainee_id']);

            // Debug logging for center validation
            Log::info('Trainee attendance center validation', [
                'current_user_id' => $currentUserId,
                'current_user_centre_id' => $currentUserCentreId,
                'current_user_centre_id_type' => gettype($currentUserCentreId),
                'current_user_email' => $currentUserEmail,
                'trainee_id' => $validated['trainee_id'],
                'trainee_centre_id' => $trainee->centre_id ?? 'NULL',
                'trainee_centre_id_type' => gettype($trainee->centre_id),
                'trainee_name' => $trainee->trainee_first_name . ' ' . $trainee->trainee_last_name,
                'validation_will_pass' => ((int) $trainee->centre_id === (int) $currentUserCentreId)
            ]);

            // Ensure admin can only mark attendance for trainees in their own centre
            // Normalize both centre IDs to integers for proper comparison
            $traineeCurrentUserCentreId = (int) $currentUserCentreId;
            $traineesCentreId = (int) $trainee->centre_id;
            
            if ($traineesCentreId !== $traineeCurrentUserCentreId) {
                Log::warning('Center validation failed for trainee attendance', [
                    'trainee_centre_id_raw' => $trainee->centre_id,
                    'trainee_centre_id_type' => gettype($trainee->centre_id),
                    'admin_centre_id_raw' => $currentUserCentreId,
                    'admin_centre_id_type' => gettype($currentUserCentreId),
                    'trainee_centre_id_normalized' => $traineesCentreId,
                    'admin_centre_id_normalized' => $traineeCurrentUserCentreId,
                    'trainee_name' => $trainee->trainee_first_name . ' ' . $trainee->trainee_last_name
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => "You can only mark attendance for trainees in your own centre. Trainee '{$trainee->trainee_first_name} {$trainee->trainee_last_name}' is in centre {$traineesCentreId}, but you are in centre {$traineeCurrentUserCentreId}."
                ], 403);
            }

            // Check if attendance already exists for today
            $existingAttendance = DB::table('trainee_attendances')
                ->where('trainee_id', $validated['trainee_id'])
                ->whereDate('attendance_date', now()->toDateString())
                ->first();

            if ($existingAttendance) {
                // Admin can update existing attendance
                DB::table('trainee_attendances')
                    ->where('id', $existingAttendance->id)
                    ->update([
                        'status' => $validated['status'],
                        'notes' => $validated['remarks'] ?? null,
                        'marked_by_user_id' => $currentUserId,
                        'marked_at' => now(),
                        'updated_at' => now(),
                    ]);

                Log::info('Trainee attendance updated by admin', [
                    'attendance_id' => $existingAttendance->id,
                    'trainee_id' => $validated['trainee_id'],
                    'updated_by' => $currentUserId,
                    'updated_by_email' => $currentUserEmail,
                    'new_status' => $validated['status']
                ]);

                return response()->json([
                    'success' => true,
                    'message' => "Attendance status updated successfully for {$trainee->trainee_first_name} {$trainee->trainee_last_name}!",
                    'attendance' => $existingAttendance
                ]);
            }

            // Create new attendance record
            $attendanceId = DB::table('trainee_attendances')->insertGetId([
                'trainee_id' => $validated['trainee_id'],
                'activity_id' => $validated['activity_id'] ?? null,
                'attendance_date' => now()->toDateString(),
                'status' => $validated['status'],
                'notes' => $validated['remarks'] ?? null,
                'marked_by_user_id' => $currentUserId,
                'marked_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            $attendance = DB::table('trainee_attendances')->where('id', $attendanceId)->first();

            Log::info('Trainee attendance marked', [
                'attendance_id' => $attendance->id,
                'trainee_id' => $validated['trainee_id'],
                'marked_by' => $currentUserId,
                'marked_by_email' => $currentUserEmail,
                'status' => $validated['status']
            ]);

            return response()->json([
                'success' => true,
                'message' => "Attendance marked successfully for {$trainee->trainee_first_name} {$trainee->trainee_last_name}!",
                'attendance' => $attendance
            ]);

        } catch (\Exception $e) {
            Log::error('Error marking trainee attendance', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all(),
                'user_id' => session('id')
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to mark trainee attendance. Error: ' . $e->getMessage()
            ], 500);
        }
    }
}
