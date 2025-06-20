<?php

namespace App\Http\Controllers;

use App\Models\ActivityEnrollment;
use App\Models\ActivitySession;
use App\Models\ActivityAttendance;
use App\Models\Trainee;
use App\Models\TraineeProgress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class TraineeActivityController extends Controller
{
    /**
     * Display trainee's activity dashboard
     */
    public function dashboard($traineeId)
    {
        try {
            $trainee = Trainee::with(['centre'])->findOrFail($traineeId);
            
            // Check if user has permission to view this trainee
            if (!$this->canViewTrainee($trainee)) {
                return redirect()->route('trainees.home')
                    ->with('error', 'You do not have permission to view this trainee.');
            }

            // Get active enrollments with sessions and activities
            $activeEnrollments = ActivityEnrollment::where('trainee_id', $traineeId)
                ->where('enrollment_status', 'active')
                ->with(['activitySession.activity', 'activitySession.teacher'])
                ->get();

            // Get recent attendance
            $recentAttendance = ActivityAttendance::where('trainee_id', $traineeId)
                ->with(['activitySession.activity'])
                ->orderBy('attendance_date', 'desc')
                ->limit(10)
                ->get();

            // Calculate statistics
            $stats = $this->calculateTraineeStats($traineeId);

            // Get upcoming sessions
            $upcomingSessions = $this->getUpcomingSessions($traineeId);

            return view('trainees.activity-dashboard', compact(
                'trainee',
                'activeEnrollments',
                'recentAttendance',
                'stats',
                'upcomingSessions'
            ));

        } catch (\Exception $e) {
            Log::error('Error loading trainee activity dashboard', [
                'trainee_id' => $traineeId,
                'error' => $e->getMessage(),
                'user' => session('name')
            ]);

            return redirect()->route('trainees.home')
                ->with('error', 'Error loading trainee dashboard.');
        }
    }

    /**
     * Show available activities for enrollment
     */
    public function availableActivities($traineeId)
    {
        try {
            $trainee = Trainee::findOrFail($traineeId);
            
            if (!$this->canEnrollTrainee($trainee)) {
                return redirect()->back()
                    ->with('error', 'You do not have permission to enroll this trainee.');
            }

            // Get currently enrolled session IDs
            $enrolledSessionIds = ActivityEnrollment::where('trainee_id', $traineeId)
                ->whereIn('enrollment_status', ['active', 'pending'])
                ->pluck('activity_session_id')
                ->toArray();

            // Get available sessions (not at capacity, not already enrolled)
            $availableSessions = ActivitySession::where('is_active', true)
                ->whereNotIn('id', $enrolledSessionIds)
                ->whereRaw('current_enrollment < max_capacity')
                ->with(['activity', 'teacher'])
                ->get()
                ->groupBy('activity.category');

            return view('trainees.available-activities', compact(
                'trainee',
                'availableSessions'
            ));

        } catch (\Exception $e) {
            Log::error('Error loading available activities', [
                'trainee_id' => $traineeId,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()
                ->with('error', 'Error loading available activities.');
        }
    }

    /**
     * Enroll trainee in an activity session
     */
    public function enroll(Request $request, $traineeId)
    {
        $request->validate([
            'activity_session_id' => 'required|exists:activity_sessions,id',
            'start_date' => 'required|date|after_or_equal:today',
            'individual_goals' => 'nullable|array',
            'enrollment_notes' => 'nullable|string|max:1000'
        ]);

        try {
            DB::beginTransaction();

            $trainee = Trainee::findOrFail($traineeId);
            $session = ActivitySession::findOrFail($request->activity_session_id);

            // Check if trainee can be enrolled
            if (!$this->canEnrollTrainee($trainee)) {
                throw new \Exception('You do not have permission to enroll this trainee.');
            }

            // Check session capacity
            if ($session->current_enrollment >= $session->max_capacity) {
                throw new \Exception('This session is at full capacity.');
            }

            // Check for existing enrollment
            $existingEnrollment = ActivityEnrollment::where('trainee_id', $traineeId)
                ->where('activity_session_id', $request->activity_session_id)
                ->whereIn('enrollment_status', ['active', 'pending'])
                ->first();

            if ($existingEnrollment) {
                throw new \Exception('Trainee is already enrolled in this session.');
            }

            // Create enrollment
            $enrollment = ActivityEnrollment::create([
                'activity_session_id' => $request->activity_session_id,
                'trainee_id' => $traineeId,
                'enrolled_by' => session('id'),
                'enrollment_status' => 'active',
                'enrollment_date' => now()->toDateString(),
                'start_date' => $request->start_date,
                'individual_goals' => $request->individual_goals ?? [],
                'enrollment_notes' => $request->enrollment_notes
            ]);

            // Update session enrollment count
            $session->increment('current_enrollment');

            DB::commit();

            Log::info('Trainee enrolled in activity', [
                'enrollment_id' => $enrollment->id,
                'trainee_id' => $traineeId,
                'session_id' => $request->activity_session_id,
                'enrolled_by' => session('name')
            ]);

            return redirect()->route('trainees.activity-dashboard', $traineeId)
                ->with('success', 'Trainee enrolled successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to enroll trainee', [
                'trainee_id' => $traineeId,
                'session_id' => $request->activity_session_id,
                'error' => $e->getMessage(),
                'user' => session('name')
            ]);

            return redirect()->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Mark attendance for trainee
     */
    public function markAttendance(Request $request, $sessionId)
    {
        $request->validate([
            'attendance_data' => 'required|array',
            'attendance_data.*.trainee_id' => 'required|exists:trainees,id',
            'attendance_data.*.status' => 'required|in:present,absent,late,left_early,excused',
            'attendance_data.*.arrival_time' => 'nullable|date_format:H:i',
            'attendance_data.*.departure_time' => 'nullable|date_format:H:i',
            'attendance_data.*.participation_score' => 'nullable|integer|min:1|max:10',
            'attendance_data.*.session_notes' => 'nullable|string|max:500',
            'attendance_data.*.behavioral_notes' => 'nullable|string|max:500'
        ]);

        try {
            DB::beginTransaction();

            $session = ActivitySession::findOrFail($sessionId);
            $attendanceDate = now()->toDateString();

            foreach ($request->attendance_data as $attendance) {
                // Check if attendance already marked for today
                $existingAttendance = ActivityAttendance::where([
                    'activity_session_id' => $sessionId,
                    'trainee_id' => $attendance['trainee_id'],
                    'attendance_date' => $attendanceDate
                ])->first();

                $attendanceData = [
                    'activity_session_id' => $sessionId,
                    'trainee_id' => $attendance['trainee_id'],
                    'marked_by' => session('id'),
                    'attendance_date' => $attendanceDate,
                    'session_start_time' => $session->start_time,
                    'session_end_time' => $session->end_time,
                    'attendance_status' => $attendance['status'],
                    'actual_arrival_time' => $attendance['arrival_time'] ?? null,
                    'actual_departure_time' => $attendance['departure_time'] ?? null,
                    'participation_score' => $attendance['participation_score'] ?? null,
                    'session_notes' => $attendance['session_notes'] ?? null,
                    'behavioral_notes' => $attendance['behavioral_notes'] ?? null,
                    'requires_followup' => !empty($attendance['behavioral_notes'])
                ];

                if ($existingAttendance) {
                    $existingAttendance->update($attendanceData);
                } else {
                    ActivityAttendance::create($attendanceData);
                }
            }

            DB::commit();

            Log::info('Attendance marked for session', [
                'session_id' => $sessionId,
                'date' => $attendanceDate,
                'marked_by' => session('name'),
                'trainees_count' => count($request->attendance_data)
            ]);

            return redirect()->back()
                ->with('success', 'Attendance marked successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to mark attendance', [
                'session_id' => $sessionId,
                'error' => $e->getMessage(),
                'user' => session('name')
            ]);

            return redirect()->back()
                ->with('error', 'Failed to mark attendance. Please try again.');
        }
    }

    /**
     * Generate progress report for trainee
     */
    public function progressReport($traineeId, $enrollmentId)
    {
        try {
            $enrollment = ActivityEnrollment::with([
                'trainee',
                'activitySession.activity',
                'activitySession.teacher',
                'progressReports' => function($query) {
                    $query->orderBy('assessment_date', 'desc');
                }
            ])->findOrFail($enrollmentId);

            // Verify trainee ID matches
            if ($enrollment->trainee_id != $traineeId) {
                return redirect()->back()
                    ->with('error', 'Invalid enrollment for this trainee.');
            }

            // Get attendance data
            $attendanceData = ActivityAttendance::where('trainee_id', $traineeId)
                ->where('activity_session_id', $enrollment->activity_session_id)
                ->orderBy('attendance_date', 'desc')
                ->get();

            // Calculate statistics
            $stats = [
                'total_sessions' => $attendanceData->count(),
                'present_sessions' => $attendanceData->where('attendance_status', 'present')->count(),
                'absent_sessions' => $attendanceData->where('attendance_status', 'absent')->count(),
                'average_participation' => $attendanceData->where('participation_score', '>', 0)->avg('participation_score'),
                'attendance_rate' => $attendanceData->count() > 0 ? 
                    ($attendanceData->where('attendance_status', 'present')->count() / $attendanceData->count()) * 100 : 0
            ];

            return view('trainees.progress-report', compact(
                'enrollment',
                'attendanceData',
                'stats'
            ));

        } catch (\Exception $e) {
            Log::error('Error generating progress report', [
                'trainee_id' => $traineeId,
                'enrollment_id' => $enrollmentId,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()
                ->with('error', 'Error generating progress report.');
        }
    }

    // Helper methods
    private function canViewTrainee($trainee)
    {
        $userRole = session('role');
        $userCentreId = session('centre_id');

        // Admin and supervisors can view all trainees
        if (in_array($userRole, ['admin', 'supervisor'])) {
            return true;
        }

        // Teachers and AJKs can only view trainees from their centre
        return $trainee->centre_id == $userCentreId;
    }

    private function canEnrollTrainee($trainee)
    {
        $userRole = session('role');
        
        // Only admin, supervisor, and teachers can enroll trainees
        if (!in_array($userRole, ['admin', 'supervisor', 'teacher'])) {
            return false;
        }

        return $this->canViewTrainee($trainee);
    }

    private function calculateTraineeStats($traineeId)
    {
        $totalEnrollments = ActivityEnrollment::where('trainee_id', $traineeId)->count();
        $activeEnrollments = ActivityEnrollment::where('trainee_id', $traineeId)
            ->where('enrollment_status', 'active')->count();
        
        $totalAttendance = ActivityAttendance::where('trainee_id', $traineeId)->count();
        $presentAttendance = ActivityAttendance::where('trainee_id', $traineeId)
            ->where('attendance_status', 'present')->count();
        
        $attendanceRate = $totalAttendance > 0 ? ($presentAttendance / $totalAttendance) * 100 : 0;
        
        $avgParticipation = ActivityAttendance::where('trainee_id', $traineeId)
            ->where('participation_score', '>', 0)
            ->avg('participation_score');

        return [
            'total_enrollments' => $totalEnrollments,
            'active_enrollments' => $activeEnrollments,
            'attendance_rate' => round($attendanceRate, 1),
            'average_participation' => round($avgParticipation ?? 0, 1),
            'total_sessions_attended' => $presentAttendance
        ];
    }

    private function getUpcomingSessions($traineeId)
    {
        $dayMapping = [
            'Sunday' => 0, 'Monday' => 1, 'Tuesday' => 2, 'Wednesday' => 3,
            'Thursday' => 4, 'Friday' => 5, 'Saturday' => 6
        ];

        return ActivityEnrollment::where('trainee_id', $traineeId)
            ->where('enrollment_status', 'active')
            ->with(['activitySession.activity', 'activitySession.teacher'])
            ->get()
            ->map(function($enrollment) use ($dayMapping) {
                $session = $enrollment->activitySession;
                $today = now();
                $sessionDay = $dayMapping[$session->day_of_week];
                $currentDay = $today->dayOfWeek;
                
                $daysUntil = ($sessionDay - $currentDay + 7) % 7;
                if ($daysUntil == 0 && $today->format('H:i') > $session->start_time) {
                    $daysUntil = 7; // Next week
                }
                
                $nextSessionDate = $today->copy()->addDays($daysUntil);
                
                return [
                    'enrollment' => $enrollment,
                    'next_session_date' => $nextSessionDate,
                    'days_until' => $daysUntil
                ];
            })
            ->sortBy('days_until')
            ->take(5);
    }
}