<?php

namespace App\Http\Controllers\Activity;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\ActivitySession;
use App\Models\ActivityAttendance;
use App\Models\SessionEnrollment;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    /**
     * Show attendance marking form
     *
     * @param  int  $sessionId
     * @return \Illuminate\View\View
     */
    public function mark($sessionId)
    {
        try {
            // Get session with related data
            $session = ActivitySession::with([
                'activity',
                'teacher',
                'enrollments' => function($query) {
                    $query->where('status', 'Active')
                          ->with('trainee');
                }
            ])->findOrFail($sessionId);
            
            // Check if user has permission
            if (session('role') === 'teacher' && session('id') != $session->teacher_id) {
                $redirectRoute = session('role') . '.schedule';
                return redirect()->route($redirectRoute)
                    ->with('error', 'You are not authorized to mark attendance for this session.');
            }
            
            // Get date parameter or use today's date
            $date = request('date', Carbon::now()->format('Y-m-d'));
            $dayOfWeek = Carbon::parse($date)->format('l');
            
            // Verify the date matches the session day
            if ($dayOfWeek !== $session->day_of_week) {
                return back()->with('error', 'Selected date does not match the session day (' . $session->day_of_week . ').');
            }
            
            // Get existing attendance records
            $attendanceRecords = ActivityAttendance::where('session_id', $sessionId)
                ->where('attendance_date', $date)
                ->get()
                ->keyBy('trainee_id');
                
            Log::info('Attendance marking form accessed', [
                'session_id' => $sessionId,
                'date' => $date,
                'user_id' => session('id')
            ]);
            
            return view('activities.attendance.mark', [
                'session' => $session,
                'date' => $date,
                'attendanceRecords' => $attendanceRecords
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error accessing attendance form', [
                'session_id' => $sessionId,
                'error' => $e->getMessage()
            ]);
            
            $redirectRoute = session('role') . '.schedule';
            return redirect()->route($redirectRoute)
                ->with('error', 'Session not found or an error occurred.');
        }
    }
    
    /**
     * Store attendance records
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $sessionId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request, $sessionId)
    {
        try {
            // Get session
            $session = ActivitySession::findOrFail($sessionId);
            
            // Check if user has permission
            if (session('role') === 'teacher' && session('id') != $session->teacher_id) {
                $redirectRoute = session('role') . '.schedule';
                return redirect()->route($redirectRoute)
                    ->with('error', 'You are not authorized to mark attendance for this session.');
            }
            
            // Validate the request
            $validator = $this->validateAttendanceRequest($request);
            
            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }
            
            DB::beginTransaction();
            
            // Get date from request
            $date = $request->attendance_date;
            
            // Process each attendance record
            foreach ($request->attendance as $traineeId => $data) {
                // Find existing record or create new one
                $attendance = ActivityAttendance::updateOrCreate(
                    [
                        'session_id' => $sessionId,
                        'trainee_id' => $traineeId,
                        'attendance_date' => $date
                    ],
                    [
                        'status' => $data['status'],
                        'remarks' => $data['remarks'] ?? null,
                        'marked_by' => session('id'),
                        'created_by' => session('id'),
                        'updated_by' => session('id')
                    ]
                );
            }
            
            DB::commit();
            
            Log::info('Attendance recorded successfully', [
                'session_id' => $sessionId,
                'date' => $date,
                'marked_by' => session('id')
            ]);
            
            return redirect()->route(session('role') . '.schedule')
                ->with('success', 'Attendance recorded successfully.');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error recording attendance', [
                'session_id' => $sessionId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'An error occurred while recording attendance. Please try again.');
        }
    }
    
    /**
     * Show attendance report
     *
     * @param  int  $sessionId
     * @return \Illuminate\View\View
     */
    public function report($sessionId)
    {
        try {
            // Get session with related data
            $session = ActivitySession::with([
                'activity',
                'teacher',
                'enrollments' => function($query) {
                    $query->where('status', 'Active')
                          ->with('trainee');
                }
            ])->findOrFail($sessionId);
            
            // Get attendance records
            $attendanceRecords = ActivityAttendance::where('session_id', $sessionId)
                ->orderBy('attendance_date', 'desc')
                ->get()
                ->groupBy('attendance_date');
                
            // Get statistics
            $stats = $this->getAttendanceStats($sessionId);
            
            Log::info('Attendance report accessed', [
                'session_id' => $sessionId,
                'user_id' => session('id')
            ]);
            
            return view('activities.attendance.report', [
                'session' => $session,
                'attendanceRecords' => $attendanceRecords,
                'stats' => $stats
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error accessing attendance report', [
                'session_id' => $sessionId,
                'error' => $e->getMessage()
            ]);
            
            $redirectRoute = session('role') . '.schedule';
            return redirect()->route($redirectRoute)
                ->with('error', 'Session not found or an error occurred.');
        }
    }
    
    // Helper methods
    
    /**
     * Validate attendance request
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\Validation\Validator
     */
    private function validateAttendanceRequest(Request $request)
    {
        $rules = [
            'attendance_date' => 'required|date|before_or_equal:' . Carbon::now()->format('Y-m-d'),
            'attendance' => 'required|array',
            'attendance.*.status' => 'required|in:Present,Absent,Late,Excused',
            'attendance.*.remarks' => 'nullable|string|max:255'
        ];
        
        return Validator::make($request->all(), $rules);
    }
    
    /**
     * Get attendance statistics
     *
     * @param  int  $sessionId
     * @return array
     */
    private function getAttendanceStats($sessionId)
    {
        // Get total number of enrollments
        $totalEnrollments = SessionEnrollment::where('session_id', $sessionId)
            ->where('status', 'Active')
            ->count();
            
        // Get total attendance records
        $totalRecords = ActivityAttendance::where('session_id', $sessionId)->count();
        
        // Get attendance by status
        $presentCount = ActivityAttendance::where('session_id', $sessionId)
            ->where('status', 'Present')
            ->count();
            
        $absentCount = ActivityAttendance::where('session_id', $sessionId)
            ->where('status', 'Absent')
            ->count();
            
        $lateCount = ActivityAttendance::where('session_id', $sessionId)
            ->where('status', 'Late')
            ->count();
            
        $excusedCount = ActivityAttendance::where('session_id', $sessionId)
            ->where('status', 'Excused')
            ->count();
            
        // Calculate attendance rate
        $attendanceRate = $totalRecords > 0 
            ? round((($presentCount + $lateCount) / $totalRecords) * 100, 2)
            : 0;
            
        return [
            'total_enrollments' => $totalEnrollments,
            'total_records' => $totalRecords,
            'present_count' => $presentCount,
            'absent_count' => $absentCount,
            'late_count' => $lateCount,
            'excused_count' => $excusedCount,
            'attendance_rate' => $attendanceRate
        ];
    }
}