<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\Trainee;
use App\Models\Activity;
use App\Models\SessionEnrollment;
use App\Models\ActivitySession;
use App\Models\Centres;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    /**
     * Display the attendance management page
     */
    public function index(Request $request)
    {
        try {
            // Get current user data from session
            $user = (object) [
                'id' => session('id'),
                'role' => session('role'),
                'name' => session('name'),
                'centre_id' => session('centre_id')
            ];

            Log::info('Attendance index accessed', [
                'user_id' => $user->id,
                'role' => $user->role
            ]);

            // Get filters from request
            $selectedDate = $request->get('date', Carbon::today()->format('Y-m-d'));
            $selectedCentreId = $request->get('centre_id');
            $selectedActivityId = $request->get('activity_id');

            // Get centres (for admin/supervisor)
            $centres = collect();
            if (in_array($user->role, ['admin', 'supervisor'])) {
                $centres = Centres::all();
            }

            // Filter activities based on role and centre
            $activitiesQuery = Activity::query();
            if ($user->role === 'teacher') {
                $activitiesQuery->where('created_by', $user->id);
            } elseif (!in_array($user->role, ['admin', 'supervisor'])) {
                $activitiesQuery->where('centre_id', $user->centre_id);
            }
            
            if ($selectedCentreId) {
                $activitiesQuery->where('centre_id', $selectedCentreId);
            }

            $activities = $activitiesQuery->get();

            // Get trainees based on filters
            $traineesQuery = Trainee::with('centre');
            
            if ($user->role === 'teacher') {
                // Teachers see trainees enrolled in their activities
                $teacherActivityIds = Activity::where('created_by', $user->id)->pluck('id');
                $traineesQuery->whereHas('enrollments', function($q) use ($teacherActivityIds) {
                    $q->whereIn('activity_id', $teacherActivityIds);
                });
            } elseif (!in_array($user->role, ['admin', 'supervisor'])) {
                $traineesQuery->where('centre_id', $user->centre_id);
            }

            if ($selectedCentreId) {
                $traineesQuery->where('centre_id', $selectedCentreId);
            }

            if ($selectedActivityId) {
                $traineesQuery->whereHas('enrollments', function($q) use ($selectedActivityId) {
                    $q->where('activity_id', $selectedActivityId);
                });
            }

            $trainees = $traineesQuery->get();

            // Get existing attendance records for the selected date
            $attendanceRecords = [];
            if ($selectedActivityId) {
                $sessions = ActivitySession::where('activity_id', $selectedActivityId)
                    ->where('scheduled_date', $selectedDate)
                    ->get();

                foreach ($sessions as $session) {
                    $enrollments = SessionEnrollment::where('session_id', $session->id)
                        ->get()
                        ->keyBy('trainee_id');
                    
                    foreach ($enrollments as $enrollment) {
                        $attendanceRecords[$enrollment->trainee_id] = $enrollment;
                    }
                }
            }

            // Calculate attendance statistics
            $stats = $this->calculateAttendanceStats($selectedDate, $selectedCentreId, $selectedActivityId);

            return view('attendance.index', compact(
                'user',
                'trainees', 
                'centres', 
                'activities', 
                'attendanceRecords',
                'stats',
                'selectedDate',
                'selectedCentreId',
                'selectedActivityId'
            ));

        } catch (\Exception $e) {
            Log::error('Error in attendance index', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            $user = (object) [
                'id' => session('id'),
                'role' => session('role'),
                'name' => session('name')
            ];

            return view('attendance.index', [
                'user' => $user,
                'trainees' => collect(),
                'centres' => collect(),
                'activities' => collect(),
                'attendanceRecords' => [],
                'stats' => [
                    'present_count' => 0,
                    'absent_count' => 0,
                    'late_count' => 0,
                    'excused_count' => 0
                ]
            ])->with('error', 'Failed to load attendance data. Please try again.');
        }
    }

    /**
     * Store attendance records
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'date' => 'required|date',
                'activity_id' => 'nullable|exists:activities,id',
                'attendance' => 'required|array',
                'attendance.*.trainee_id' => 'required|exists:trainees,id',
                'attendance.*.status' => 'required|in:present,absent,late,excused',
                'attendance.*.remarks' => 'nullable|string|max:255'
            ]);

            $date = $request->get('date');
            $activityId = $request->get('activity_id');
            $user = (object) [
                'id' => session('id'),
                'role' => session('role')
            ];

            Log::info('Storing attendance records', [
                'user_id' => $user->id,
                'date' => $date,
                'activity_id' => $activityId,
                'records_count' => count($request->get('attendance', []))
            ]);

            DB::beginTransaction();

            if ($activityId) {
                // Find or create session for this activity and date
                $session = ActivitySession::firstOrCreate([
                    'activity_id' => $activityId,
                    'scheduled_date' => $date
                ], [
                    'teacher_id' => $user->id,
                    'start_time' => '09:00:00',
                    'end_time' => '10:00:00',
                    'status' => 'completed',
                    'attendance_marked' => true
                ]);

                foreach ($request->get('attendance', []) as $attendanceData) {
                    SessionEnrollment::updateOrCreate([
                        'session_id' => $session->id,
                        'trainee_id' => $attendanceData['trainee_id']
                    ], [
                        'attendance_status' => $attendanceData['status'],
                        'progress_notes' => $attendanceData['remarks'] ?? null,
                        'checked_in_at' => $attendanceData['status'] === 'present' ? now() : null
                    ]);
                }
            }

            DB::commit();

            Log::info('Attendance records stored successfully', [
                'user_id' => $user->id,
                'date' => $date
            ]);

            return redirect()->route('attendance.index', [
                'date' => $date,
                'activity_id' => $activityId
            ])->with('success', 'Attendance records saved successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error storing attendance records', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to save attendance records. Please try again.');
        }
    }

    /**
     * Display attendance reports
     */
    public function report(Request $request)
    {
        try {
            $user = (object) [
                'id' => session('id'),
                'role' => session('role'),
                'centre_id' => session('centre_id')
            ];

            // Add report logic here
            return view('attendance.report', compact('user'));

        } catch (\Exception $e) {
            Log::error('Error in attendance report', [
                'error' => $e->getMessage()
            ]);

            return redirect()->route('attendance.index')
                ->with('error', 'Failed to load attendance reports.');
        }
    }

    /**
     * Calculate attendance statistics
     */
    private function calculateAttendanceStats($date = null, $centreId = null, $activityId = null)
    {
        try {
            $query = SessionEnrollment::query();

            if ($date) {
                $query->whereHas('session', function($q) use ($date) {
                    $q->where('scheduled_date', $date);
                });
            }

            if ($activityId) {
                $query->whereHas('session.activity', function($q) use ($activityId) {
                    $q->where('id', $activityId);
                });
            }

            if ($centreId) {
                $query->whereHas('session.activity', function($q) use ($centreId) {
                    $q->where('centre_id', $centreId);
                });
            }

            $attendanceData = $query->get();

            return [
                'present_count' => $attendanceData->where('attendance_status', 'present')->count(),
                'absent_count' => $attendanceData->where('attendance_status', 'absent')->count(),
                'late_count' => $attendanceData->where('attendance_status', 'late')->count(),
                'excused_count' => $attendanceData->where('attendance_status', 'excused')->count()
            ];

        } catch (\Exception $e) {
            Log::error('Error calculating attendance stats', [
                'error' => $e->getMessage()
            ]);

            return [
                'present_count' => 0,
                'absent_count' => 0,
                'late_count' => 0,
                'excused_count' => 0
            ];
        }
    }
}