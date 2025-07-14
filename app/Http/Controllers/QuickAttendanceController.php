<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Models\ActivitySession;
use App\Models\Attendance;
use App\Models\SessionEnrollment;
use Carbon\Carbon;

class QuickAttendanceController extends Controller
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
     * Display today's sessions for quick attendance marking
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        try {
            $userId = session('id');
            $role = session('role');
            
            Log::info('Quick attendance accessed', [
                'user_id' => $userId,
                'role' => $role
            ]);
            
            if (!$userId || !$role) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication required'
                ], 401);
            }
            
            $today = Carbon::today();
            $sessions = collect();
            
            // Get today's sessions based on user role
            if ($role === 'teacher') {
                // Teachers see only their assigned sessions
                $sessions = ActivitySession::with(['activity', 'enrollments.trainee'])
                    ->where('session_date', $today->format('Y-m-d'))
                    ->where('teacher_id', $userId)
                    ->where('status', 'active')
                    ->orderBy('start_time')
                    ->get();
            } elseif (in_array($role, ['admin', 'supervisor'])) {
                // Admin and supervisors see all sessions
                $sessions = ActivitySession::with(['activity', 'enrollments.trainee', 'teacher'])
                    ->where('session_date', $today->format('Y-m-d'))
                    ->where('status', 'active')
                    ->orderBy('start_time')
                    ->get();
            }
            
            // Format sessions for frontend
            $formattedSessions = [];
            foreach ($sessions as $session) {
                $enrolledTrainees = $session->enrollments->map(function($enrollment) {
                    return [
                        'id' => $enrollment->trainee->id,
                        'name' => $enrollment->trainee->name,
                        'enrollment_id' => $enrollment->id
                    ];
                });
                
                // Check if attendance already exists for this session
                $existingAttendance = Attendance::where('session_id', $session->id)->exists();
                
                $formattedSessions[] = [
                    'id' => $session->id,
                    'activity_name' => $session->activity->activity_name,
                    'session_date' => $session->session_date,
                    'start_time' => $session->start_time,
                    'end_time' => $session->end_time,
                    'location' => $session->location,
                    'teacher_name' => $role === 'teacher' ? null : ($session->teacher->name ?? 'Unassigned'),
                    'enrolled_trainees' => $enrolledTrainees,
                    'attendance_marked' => $existingAttendance
                ];
            }
            
            Log::info('Quick attendance sessions retrieved', [
                'user_id' => $userId,
                'role' => $role,
                'sessions_count' => count($formattedSessions)
            ]);
            
            return response()->json([
                'success' => true,
                'sessions' => $formattedSessions,
                'date' => $today->format('Y-m-d'),
                'user_role' => $role
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error loading quick attendance', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => session('id')
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to load sessions. Please try again later.'
            ], 500);
        }
    }
    
    /**
     * Store attendance for a session
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            $userId = session('id');
            $role = session('role');
            
            Log::info('Quick attendance submission started', [
                'user_id' => $userId,
                'role' => $role,
                'request_data' => $request->all()
            ]);
            
            if (!$userId || !$role) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication required'
                ], 401);
            }
            
            // Validate input
            $validator = Validator::make($request->all(), [
                'session_id' => 'required|exists:activity_sessions,id',
                'attendance_data' => 'required|array',
                'attendance_data.*.enrollment_id' => 'required|exists:session_enrollments,id',
                'attendance_data.*.status' => 'required|in:present,absent,late,excused',
                'notes' => 'nullable|string|max:500'
            ], [
                'session_id.required' => 'Session ID is required',
                'session_id.exists' => 'Invalid session selected',
                'attendance_data.required' => 'Attendance data is required',
                'attendance_data.array' => 'Invalid attendance data format',
                'attendance_data.*.enrollment_id.required' => 'Enrollment ID is required for each trainee',
                'attendance_data.*.enrollment_id.exists' => 'Invalid enrollment ID',
                'attendance_data.*.status.required' => 'Attendance status is required for each trainee',
                'attendance_data.*.status.in' => 'Invalid attendance status'
            ]);
            
            if ($validator->fails()) {
                Log::warning('Quick attendance validation failed', [
                    'errors' => $validator->errors()->toArray(),
                    'user_id' => $userId
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            $sessionId = $request->session_id;
            $attendanceData = $request->attendance_data;
            $notes = $request->notes;
            
            // Verify session exists and user has permission
            $session = ActivitySession::with(['activity', 'enrollments.trainee'])->find($sessionId);
            
            if (!$session) {
                return response()->json([
                    'success' => false,
                    'message' => 'Session not found'
                ], 404);
            }
            
            // Check permissions
            if ($role === 'teacher' && $session->teacher_id != $userId) {
                Log::warning('Teacher attempted to mark attendance for session not assigned to them', [
                    'teacher_id' => $userId,
                    'session_teacher_id' => $session->teacher_id,
                    'session_id' => $sessionId
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'You are not authorized to mark attendance for this session'
                ], 403);
            }
            
            // Check if attendance already exists
            $existingAttendance = Attendance::where('session_id', $sessionId)->exists();
            
            if ($existingAttendance) {
                return response()->json([
                    'success' => false,
                    'message' => 'Attendance has already been marked for this session'
                ], 409);
            }
            
            // Begin transaction for atomic operation
            \DB::beginTransaction();
            
            try {
                $attendanceRecords = [];
                $presentCount = 0;
                $absentCount = 0;
                $lateCount = 0;
                $excusedCount = 0;
                
                foreach ($attendanceData as $attendance) {
                    $enrollmentId = $attendance['enrollment_id'];
                    $status = $attendance['status'];
                    
                    // Verify enrollment belongs to this session
                    $enrollment = SessionEnrollment::where('id', $enrollmentId)
                        ->where('session_id', $sessionId)
                        ->first();
                        
                    if (!$enrollment) {
                        \DB::rollBack();
                        return response()->json([
                            'success' => false,
                            'message' => 'Invalid enrollment for this session'
                        ], 400);
                    }
                    
                    // Create attendance record
                    $attendanceRecord = Attendance::create([
                        'session_id' => $sessionId,
                        'trainee_id' => $enrollment->trainee_id,
                        'enrollment_id' => $enrollmentId,
                        'status' => $status,
                        'attendance_date' => Carbon::today()->format('Y-m-d'),
                        'marked_by' => $userId,
                        'marked_at' => now(),
                        'notes' => $notes
                    ]);
                    
                    $attendanceRecords[] = $attendanceRecord;
                    
                    // Count attendance statuses
                    switch ($status) {
                        case 'present':
                            $presentCount++;
                            break;
                        case 'absent':
                            $absentCount++;
                            break;
                        case 'late':
                            $lateCount++;
                            break;
                        case 'excused':
                            $excusedCount++;
                            break;
                    }
                }
                
                // Update session status
                $session->attendance_marked = true;
                $session->attendance_marked_by = $userId;
                $session->attendance_marked_at = now();
                $session->save();
                
                \DB::commit();
                
                Log::info('Quick attendance marked successfully', [
                    'session_id' => $sessionId,
                    'marked_by' => $userId,
                    'total_trainees' => count($attendanceRecords),
                    'present' => $presentCount,
                    'absent' => $absentCount,
                    'late' => $lateCount,
                    'excused' => $excusedCount
                ]);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Attendance marked successfully',
                    'summary' => [
                        'total' => count($attendanceRecords),
                        'present' => $presentCount,
                        'absent' => $absentCount,
                        'late' => $lateCount,
                        'excused' => $excusedCount
                    ],
                    'session' => [
                        'id' => $session->id,
                        'activity_name' => $session->activity->activity_name,
                        'date' => $session->session_date,
                        'time' => $session->start_time . ' - ' . $session->end_time
                    ]
                ]);
                
            } catch (\Exception $e) {
                \DB::rollBack();
                throw $e;
            }
            
        } catch (\Exception $e) {
            Log::error('Error marking quick attendance', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => session('id'),
                'session_id' => $request->session_id ?? null
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark attendance. Please try again later.'
            ], 500);
        }
    }
    
    /**
     * Get attendance summary for today
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function summary()
    {
        try {
            $userId = session('id');
            $role = session('role');
            
            if (!$userId || !$role) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication required'
                ], 401);
            }
            
            $today = Carbon::today();
            
            // Get sessions based on role
            $sessionsQuery = ActivitySession::where('session_date', $today->format('Y-m-d'))
                ->where('status', 'active');
                
            if ($role === 'teacher') {
                $sessionsQuery->where('teacher_id', $userId);
            }
            
            $totalSessions = $sessionsQuery->count();
            $markedSessions = $sessionsQuery->where('attendance_marked', true)->count();
            $pendingSessions = $totalSessions - $markedSessions;
            
            return response()->json([
                'success' => true,
                'summary' => [
                    'total_sessions' => $totalSessions,
                    'marked_sessions' => $markedSessions,
                    'pending_sessions' => $pendingSessions,
                    'completion_rate' => $totalSessions > 0 ? round(($markedSessions / $totalSessions) * 100, 1) : 0
                ],
                'date' => $today->format('Y-m-d')
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error getting attendance summary', [
                'error' => $e->getMessage(),
                'user_id' => session('id')
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to get summary'
            ], 500);
        }
    }
}