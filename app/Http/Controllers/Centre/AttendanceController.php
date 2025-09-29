<?php

namespace App\Http\Controllers\Centre;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ActivitySession;
use App\Models\SessionEnrollment;
use App\Models\Trainee;
use App\Models\Activity;
use App\Models\LearningOutcome;
use App\Models\TraineeCompetencyProgress;
use App\Models\IepActivityGoal;
use App\Models\Centre;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Exception;

class AttendanceController extends Controller
{
    /**
     * Activity attendance dashboard showing both general and activity-based attendance
     */
    public function index(Request $request)
    {
        try {
            // Authentication check
            if (!session()->has('id')) {
                return redirect()->route('login');
            }

            $role = session('role');
            $centreId = session('centre_id');
            $selectedDate = $request->input('date', now()->toDateString());
            
            // Get centre information
            $centre = Centre::where('centre_id', $centreId)->first();
            
            // Get today's scheduled activity sessions
            $activitySessions = $this->getTodayActivitySessions($centreId, $selectedDate);
            
            // Get general attendance overview
            $attendanceOverview = $this->getAttendanceOverview($centreId, $selectedDate);
            
            // Get trainees with their activity enrollments
            $trainees = $this->getTraineesWithEnrollments($centreId);
            
            // Get attendance analytics
            $analytics = $this->getAttendanceAnalytics($centreId, $selectedDate);

            return view('centre.attendance.index', compact(
                'centre',
                'activitySessions',
                'attendanceOverview',
                'trainees',
                'analytics',
                'selectedDate'
            ));

        } catch (Exception $e) {
            Log::error('Enhanced attendance error: ' . $e->getMessage(), [
                'user_id' => session('id'),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()->with('error', 'Unable to load attendance dashboard.');
        }
    }

    /**
     * Activity-based attendance marking interface
     */
    public function markActivityAttendance($sessionId)
    {
        try {
            // Check if today is a weekend (Saturday or Sunday)
            $today = now();
            if ($today->isWeekend()) {
                return redirect()->back()->with('error', 'Attendance marking is not allowed on weekends. Please try again on a weekday.');
            }
            
            $session = ActivitySession::with([
                'activity.learningOutcomes',
                'activity.category',
                'sessionEnrollments.trainee',
                'teacher'
            ])->findOrFail($sessionId);

            // Check if user can mark attendance for this session
            if (!$this->canMarkAttendance($session)) {
                abort(403, 'You do not have permission to mark attendance for this session.');
            }

            // Get enrolled trainees
            $enrolledTrainees = $session->sessionEnrollments()
                                      ->with('trainee')
                                      ->get();

            // Get learning outcomes for progress tracking
            $learningOutcomes = $session->activity->learningOutcomes;

            // Get IEP goals related to this activity
            $iepGoals = IepActivityGoal::where('activity_id', $session->activity_id)
                                     ->with('iep.trainee')
                                     ->get();

            return view('centre.attendance.mark-session', compact(
                'session',
                'enrolledTrainees',
                'learningOutcomes',
                'iepGoals'
            ));

        } catch (Exception $e) {
            Log::error('Activity attendance marking error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Unable to load attendance marking interface.');
        }
    }

    /**
     * Store enhanced attendance with learning outcome progress
     */
    public function storeActivityAttendance(Request $request, $sessionId)
    {
        try {
            $session = ActivitySession::findOrFail($sessionId);
            
            // Check if today is a weekend (Saturday or Sunday)
            $today = now();
            if ($today->isWeekend()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Attendance marking is not allowed on weekends. Please try again on a weekday.'
                ], 422);
            }
            
            // Validate request
            $validated = $request->validate([
                'attendance' => 'required|array',
                'attendance.*.trainee_id' => 'required|exists:trainees,id',
                'attendance.*.status' => 'required|in:present,absent,late,excused',
                'attendance.*.participation_score' => 'nullable|integer|min:0|max:10',
                'attendance.*.notes' => 'nullable|string|max:500',
                'learning_progress' => 'nullable|array',
                'learning_progress.*.trainee_id' => 'required|exists:trainees,id',
                'learning_progress.*.outcome_id' => 'required|exists:learning_outcomes,id',
                'learning_progress.*.progress_level' => 'required|in:Not Started,In Progress,Achieved,Mastered',
                'learning_progress.*.notes' => 'nullable|string|max:500',
                'session_notes' => 'nullable|string|max:1000'
            ]);

            DB::beginTransaction();

            foreach ($validated['attendance'] as $attendanceData) {
                // Update or create session enrollment attendance
                SessionEnrollment::updateOrCreate(
                    [
                        'session_id' => $sessionId,
                        'trainee_id' => $attendanceData['trainee_id']
                    ],
                    [
                        'attendance_status' => $attendanceData['status'],
                        'participation_score' => $attendanceData['participation_score'] ?? null,
                        'progress_notes' => $attendanceData['notes'] ?? null,
                        'checked_in_at' => now(),
                        'recorded_by' => session('id')
                    ]
                );

                // Create general attendance record for centre tracking
                DB::table('attendances')->updateOrInsert(
                    [
                        'trainee_id' => $attendanceData['trainee_id'],
                        'date' => $session->session_date,
                        'session_type' => 'activity'
                    ],
                    [
                        'status' => $attendanceData['status'],
                        'activity_session_id' => $sessionId,
                        'recorded_by' => session('id'),
                        'recorded_at' => now(),
                        'notes' => $attendanceData['notes'] ?? null,
                        'updated_at' => now()
                    ]
                );
            }

            // Update learning outcome progress if provided
            if (isset($validated['learning_progress'])) {
                foreach ($validated['learning_progress'] as $progressData) {
                    TraineeCompetencyProgress::updateOrCreate(
                        [
                            'trainee_id' => $progressData['trainee_id'],
                            'learning_outcome_id' => $progressData['outcome_id']
                        ],
                        [
                            'current_level' => $progressData['progress_level'],
                            'progress_percentage' => $this->calculateProgressPercentage($progressData['progress_level']),
                            'last_assessed_at' => now(),
                            'assessed_by' => session('id'),
                            'notes' => $progressData['notes'] ?? null
                        ]
                    );
                }
            }

            // Update session notes if provided
            if (isset($validated['session_notes'])) {
                $session->update([
                    'session_notes' => $validated['session_notes']
                ]);
            }

            // Update session status
            $session->update([
                'status' => 'completed',
                'attendance_marked' => true,
                'completed_at' => now()
            ]);

            DB::commit();

            Log::info('Enhanced attendance marked successfully', [
                'session_id' => $sessionId,
                'marked_by' => session('id'),
                'attendees_count' => count($validated['attendance'])
            ]);

            return redirect()->route('centre.enhanced-attendance.index')
                           ->with('success', 'Attendance and learning progress recorded successfully!');

        } catch (Exception $e) {
            DB::rollBack();
            
            Log::error('Enhanced attendance storage error: ' . $e->getMessage(), [
                'session_id' => $sessionId,
                'user_id' => session('id'),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                           ->withInput()
                           ->with('error', 'Failed to record attendance. Please try again.');
        }
    }

    /**
     * Attendance analytics and reports
     */
    public function analytics(Request $request)
    {
        try {
            $centreId = session('centre_id');
            $role = session('role');
            
            // Date range filtering
            $startDate = $request->input('start_date', now()->subMonth()->toDateString());
            $endDate = $request->input('end_date', now()->toDateString());
            
            // Activity-based attendance analytics
            $activityAttendance = $this->getActivityAttendanceAnalytics($centreId, $startDate, $endDate);
            
            // Learning outcome progress analytics
            $learningProgress = $this->getLearningProgressAnalytics($centreId, $startDate, $endDate);
            
            // Individual trainee performance
            $traineePerformance = $this->getTraineePerformanceAnalytics($centreId, $startDate, $endDate);
            
            // IEP goal progress
            $iepProgress = $this->getIepProgressAnalytics($centreId, $startDate, $endDate);

            return view('centre.enhanced-attendance.analytics', compact(
                'activityAttendance',
                'learningProgress',
                'traineePerformance',
                'iepProgress',
                'startDate',
                'endDate'
            ));

        } catch (Exception $e) {
            Log::error('Attendance analytics error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Unable to load attendance analytics.');
        }
    }

    /**
     * Export attendance data with learning outcomes
     */
    public function export(Request $request)
    {
        try {
            $format = $request->input('format', 'excel');
            $startDate = $request->input('start_date', now()->subMonth()->toDateString());
            $endDate = $request->input('end_date', now()->toDateString());
            $centreId = session('centre_id');

            $data = $this->getComprehensiveAttendanceData($centreId, $startDate, $endDate);

            if ($format === 'pdf') {
                return $this->exportToPdf($data, $startDate, $endDate);
            } else {
                return $this->exportToExcel($data, $startDate, $endDate);
            }

        } catch (Exception $e) {
            Log::error('Attendance export error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Unable to export attendance data.');
        }
    }

    // Private helper methods

    private function getTodayActivitySessions($centreId, $date)
    {
        return ActivitySession::with([
            'activity.category',
            'teacher',
            'sessionEnrollments.trainee'
        ])
        ->whereHas('activity', function($query) use ($centreId) {
            $query->where('centre_id', $centreId);
        })
        ->whereDate('session_date', $date)
        ->orderBy('start_time')
        ->get();
    }

    private function getAttendanceOverview($centreId, $date)
    {
        return [
            'total_trainees' => Trainee::where('centre_id', $centreId)->count(),
            'present_today' => DB::table('trainee_attendances')
                                ->join('trainees', 'trainee_attendances.trainee_id', '=', 'trainees.id')
                                ->whereDate('trainee_attendances.attendance_date', $date)
                                ->where('trainees.centre_id', $centreId)
                                ->where('trainee_attendances.status', 'present')
                                ->count(),
            'total_sessions' => ActivitySession::whereHas('activity', function($q) use ($centreId) {
                                    $q->where('centre_id', $centreId);
                                })
                                ->whereDate('session_date', $date)
                                ->count(),
            'completed_sessions' => ActivitySession::whereHas('activity', function($q) use ($centreId) {
                                        $q->where('centre_id', $centreId);
                                    })
                                    ->whereDate('session_date', $date)
                                    ->where('attendance_marked', true)
                                    ->count()
        ];
    }

    private function getTraineesWithEnrollments($centreId)
    {
        return Trainee::where('centre_id', $centreId)
                     ->with([
                         'activityEnrollments.activity',
                         'sessionEnrollments' => function($query) {
                             $query->whereDate('created_at', now()->toDateString());
                         }
                     ])
                     ->get();
    }

    private function getAttendanceAnalytics($centreId, $date)
    {
        $weekStart = Carbon::parse($date)->startOfWeek();
        $weekEnd = Carbon::parse($date)->endOfWeek();

        return [
            'weekly_attendance_rate' => $this->calculateWeeklyAttendanceRate($centreId, $weekStart, $weekEnd),
            'activity_participation' => $this->getActivityParticipationRates($centreId, $date),
            'learning_progress_summary' => $this->getLearningProgressSummary($centreId, $date)
        ];
    }

    private function canMarkAttendance($session)
    {
        $role = session('role');
        $userId = session('id');

        // Admin and supervisors can mark any attendance
        if (in_array($role, ['admin', 'supervisor'])) {
            return true;
        }

        // Teachers can mark attendance for their assigned sessions
        if ($role === 'teacher' && $session->teacher_id == $userId) {
            return true;
        }

        return false;
    }

    private function calculateProgressPercentage($level)
    {
        $percentages = [
            'Not Started' => 0,
            'In Progress' => 50,
            'Achieved' => 80,
            'Mastered' => 100
        ];

        return $percentages[$level] ?? 0;
    }

    /**
     * Update session notes
     */
    public function updateSessionNotes(Request $request, $sessionId)
    {
        try {
            $validated = $request->validate([
                'session_notes' => 'nullable|string|max:1000'
            ]);

            $session = ActivitySession::findOrFail($sessionId);

            // Check permissions - only assigned teacher, supervisors, and admins can edit
            $userRole = session('role');
            $userId = session('id');

            if (!in_array($userRole, ['admin', 'supervisor']) &&
                $session->teacher_id != $userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access to edit session notes.'
                ], 403);
            }

            // Update session notes
            $session->update([
                'session_notes' => $validated['session_notes']
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Session notes updated successfully',
                'data' => [
                    'session_id' => $sessionId,
                    'notes_length' => strlen($validated['session_notes'] ?? ''),
                    'updated_at' => $session->updated_at->format('Y-m-d H:i:s')
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('Session notes update error: ' . $e->getMessage(), [
                'session_id' => $sessionId,
                'user_id' => session('id'),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error updating session notes'
            ], 500);
        }
    }

    private function calculateWeeklyAttendanceRate($centreId, $weekStart, $weekEnd)
    {
        $totalSessions = ActivitySession::whereHas('activity', function($q) use ($centreId) {
                            $q->where('centre_id', $centreId);
                        })
                        ->whereBetween('session_date', [$weekStart, $weekEnd])
                        ->count();

        $attendedSessions = SessionEnrollment::whereHas('session.activity', function($q) use ($centreId) {
                                $q->where('centre_id', $centreId);
                            })
                            ->whereHas('session', function($q) use ($weekStart, $weekEnd) {
                                $q->whereBetween('session_date', [$weekStart, $weekEnd]);
                            })
                            ->where('attendance_status', 'present')
                            ->count();

        return $totalSessions > 0 ? round(($attendedSessions / $totalSessions) * 100, 2) : 0;
    }

    private function getActivityParticipationRates($centreId, $date)
    {
        return Activity::where('centre_id', $centreId)
                      ->withCount(['sessions as total_sessions' => function($query) use ($date) {
                          $query->whereDate('session_date', $date);
                      }])
                      ->with(['sessions' => function($query) use ($date) {
                          $query->whereDate('session_date', $date)
                                ->withCount(['sessionEnrollments as attended_count' => function($q) {
                                    $q->where('attendance_status', 'present');
                                }]);
                      }])
                      ->get()
                      ->map(function($activity) {
                          $totalAttended = $activity->sessions->sum('attended_count');
                          $totalCapacity = $activity->sessions->sum('capacity') ?: $activity->max_participants;
                          
                          return [
                              'activity_name' => $activity->activity_name,
                              'participation_rate' => $totalCapacity > 0 ? round(($totalAttended / $totalCapacity) * 100, 2) : 0,
                              'total_attended' => $totalAttended,
                              'total_capacity' => $totalCapacity
                          ];
                      });
    }

    private function getLearningProgressSummary($centreId, $date)
    {
        return TraineeCompetencyProgress::whereHas('trainee', function($q) use ($centreId) {
                    $q->where('centre_id', $centreId);
                })
                ->whereDate('last_assessed_at', $date)
                ->selectRaw('current_level, COUNT(*) as count')
                ->groupBy('current_level')
                ->get()
                ->pluck('count', 'current_level')
                ->toArray();
    }

    // Additional analytics methods would be implemented here...
    private function getActivityAttendanceAnalytics($centreId, $startDate, $endDate) { /* Implementation */ }
    private function getLearningProgressAnalytics($centreId, $startDate, $endDate) { /* Implementation */ }
    private function getTraineePerformanceAnalytics($centreId, $startDate, $endDate) { /* Implementation */ }
    private function getIepProgressAnalytics($centreId, $startDate, $endDate) { /* Implementation */ }
    private function getComprehensiveAttendanceData($centreId, $startDate, $endDate) { /* Implementation */ }
    private function exportToPdf($data, $startDate, $endDate) { /* Implementation */ }
    private function exportToExcel($data, $startDate, $endDate) { /* Implementation */ }
}