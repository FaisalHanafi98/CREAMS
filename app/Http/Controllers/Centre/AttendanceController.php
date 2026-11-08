<?php

namespace App\Http\Controllers\Centre;

use App\Http\Controllers\Controller;
use App\Services\Attendance\SessionAttendanceSummary;
use Illuminate\Http\Request;
use App\Models\ActivitySession;
use App\Models\Trainee;
use App\Models\Activity;
use App\Models\LearningOutcome;
use App\Models\TraineeCompetencyProgress;
use App\Models\IepActivityGoal;
use App\Models\Centre;
use App\Models\Attendance;
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
        $validated = $request->validate(['date' => 'nullable|date_format:Y-m-d', 'centre_id' => 'nullable|string|max:10']);
        $centreId = $validated['centre_id'] ?? session('centre_id');
        abort_if(!$centreId, 422, 'Select a centre to view attendance.');
        abort_if(session('role') !== 'admin' && $centreId !== session('centre_id'), 403);
        $centre = Centre::where('centre_id', $centreId)->firstOrFail();
        $selectedDate = $validated['date'] ?? now()->toDateString();

        try {
            $data = app(SessionAttendanceSummary::class)->dashboard($centreId, Carbon::parse($selectedDate));

            return view('centre.attendance.index', $data + compact('centre', 'selectedDate'));

        } catch (Exception $e) {
            Log::error('Attendance dashboard failed', ['error_type' => get_class($e)]);
            
            return redirect()->back()->with('error', 'Unable to load attendance dashboard.');
        }
    }

    /**
     * Activity-based attendance marking interface
     */
    public function markActivityAttendance($sessionId)
    {
        $session = $this->resolveMarkingSession($sessionId);
        if (now()->isWeekend()) {
            return redirect()->route('centre.attendance.index')->with('error', 'Attendance marking is not allowed on weekends.');
        }

        $summary = app(SessionAttendanceSummary::class);
        $enrolledTrainees = $summary->roster($session, $session->activity->centre_id);
        $marks = $summary->recordedMarks($session, $enrolledTrainees)->keyBy('trainee_id');
        $learningOutcomes = collect();
        $iepGoals = IepActivityGoal::where('activity_id', $session->activity_id)
            ->whereHas('iep.trainee', fn ($query) => $query->whereIn('id', $enrolledTrainees->pluck('trainee_id')))
            ->with('iep.trainee')->get();

        return view('centre.attendance.mark-session', compact('session', 'enrolledTrainees', 'marks', 'learningOutcomes', 'iepGoals'));
    }

    public function storeActivityAttendance(Request $request, $sessionId)
    {
        $session = $this->resolveMarkingSession($sessionId);
        if (now()->isWeekend()) {
            $message = 'Attendance marking is not allowed on weekends.';
            return $request->expectsJson()
                ? response()->json(['error' => $message], 422)
                : redirect()->route('centre.attendance.index')->with('error', $message);
        }

        $validated = $request->validate([
            'attendance' => 'required|array|min:1',
            'attendance.*.trainee_id' => 'required|integer|distinct',
            'attendance.*.status' => 'required|in:present,absent,late,excused',
            'attendance.*.notes' => 'nullable|string|max:500',
            'attendance.*.participation_score' => 'prohibited',
            'learning_progress' => 'prohibited',
            'session_notes' => 'nullable|string|max:1000',
        ]);

        $roster = app(SessionAttendanceSummary::class)->roster($session, $session->activity->centre_id);
        if (collect($validated['attendance'])->pluck('trainee_id')->diff($roster->pluck('trainee_id'))->isNotEmpty()) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'attendance' => 'Choose trainees on this session roster.',
            ]);
        }

        try {
            DB::transaction(function () use ($validated, $session) {
                foreach ($validated['attendance'] as $mark) {
                    Attendance::updateOrCreate([
                        'trainee_id' => $mark['trainee_id'],
                        'session_id' => $session->id,
                        'activity_id' => $session->activity_id,
                        'attendance_date' => $session->session_date,
                    ], [
                        'status' => $mark['status'],
                        'notes' => $mark['notes'] ?? null,
                        'marked_by_user_id' => session('id'),
                        'marked_at' => now(),
                    ]);
                }

                $changes = ['session_status' => 'completed'];
                if (array_key_exists('session_notes', $validated)) {
                    $changes['session_notes'] = $validated['session_notes'];
                }
                $session->update($changes);
            });
        } catch (Exception $exception) {
            Log::error('Attendance save failed', ['error_type' => get_class($exception)]);
            $message = 'Attendance was not saved. Please try again.';
            return $request->expectsJson()
                ? response()->json(['error' => $message], 500)
                : redirect()->back()->withInput()->with('error', $message);
        }

        return $request->expectsJson()
            ? response()->json(['success' => true])
            : redirect()->route('centre.attendance.index')->with('success', 'Attendance recorded successfully!');
    }

    private function resolveMarkingSession($sessionId): ActivitySession
    {
        $role = session('role');
        $centreId = session('centre_id');
        abort_unless(session()->has('id') && in_array($role, ['admin', 'supervisor', 'teacher'], true), 403);
        abort_if($role !== 'admin' && !$centreId, 403);

        $session = ActivitySession::with(['activity', 'teacher', 'enrollments.trainee', 'traineeAttendances'])
            ->whereHas('activity', function ($query) use ($role, $centreId) {
                if ($role !== 'admin') {
                    $query->where('centre_id', $centreId);
                }
            })->findOrFail($sessionId);

        abort_if($role === 'teacher' && (int) $session->instructor_id !== (int) session('id'), 403);
        abort_if($session->session_status === 'cancelled', 422, 'This session was cancelled.');

        return $session;
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
        try {
            $presentToday = 0;
            try {
                $presentToday = DB::table('trainee_attendances')
                    ->join('trainees', 'trainee_attendances.trainee_id', '=', 'trainees.id')
                    ->whereDate('trainee_attendances.attendance_date', $date)
                    ->where('trainees.centre_id', $centreId)
                    ->where('trainee_attendances.status', 'present')
                    ->count();
            } catch (\Exception $e) {
                // trainee_attendances table may not exist yet — return 0
            }

            return [
                'total_trainees' => Trainee::where('centre_id', $centreId)->count(),
                'present_today' => $presentToday,
                'total_sessions' => ActivitySession::whereHas('activity', function($q) use ($centreId) {
                                        $q->where('centre_id', $centreId);
                                    })
                                    ->whereDate('session_date', $date)
                                    ->count(),
                'completed_sessions' => ActivitySession::whereHas('activity', function($q) use ($centreId) {
                                            $q->where('centre_id', $centreId);
                                        })
                                        ->whereDate('session_date', $date)
                                        ->where('session_status', 'completed')
                                        ->count()
            ];
        } catch (\Exception $e) {
            Log::warning('getAttendanceOverview failed, returning defaults', ['error' => $e->getMessage()]);
            return ['total_trainees' => 0, 'present_today' => 0, 'total_sessions' => 0, 'completed_sessions' => 0];
        }
    }

    private function getTraineesWithEnrollments($centreId)
    {
        try {
            return Trainee::where('centre_id', $centreId)
                         ->with(['enrollments.activity'])
                         ->get();
        } catch (\Exception $e) {
            Log::warning('getTraineesWithEnrollments failed, returning empty', ['error' => $e->getMessage()]);
            return collect();
        }
    }

    private function getAttendanceAnalytics($centreId, $date)
    {
        try {
            $weekStart = Carbon::parse($date)->startOfWeek();
            $weekEnd = Carbon::parse($date)->endOfWeek();

            return [
                'weekly_attendance_rate' => $this->calculateWeeklyAttendanceRate($centreId, $weekStart, $weekEnd),
                'activity_participation' => $this->getActivityParticipationRates($centreId, $date),
                'learning_progress_summary' => $this->getLearningProgressSummary($centreId, $date)
            ];
        } catch (\Exception $e) {
            Log::warning('getAttendanceAnalytics failed, returning empty', ['error' => $e->getMessage()]);
            return [
                'weekly_attendance_rate' => 0,
                'activity_participation' => collect(),
                'learning_progress_summary' => []
            ];
        }
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

        $attendedSessions = Attendance::whereHas('activity', function($q) use ($centreId) {
                                $q->where('centre_id', $centreId);
                            })
                            ->whereHas('session', function($q) use ($weekStart, $weekEnd) {
                                $q->whereBetween('session_date', [$weekStart, $weekEnd]);
                            })
                            ->where('status', 'present')
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
                                ->withCount(['traineeAttendances as attended_count' => function($q) {
                                    $q->where('status', 'present');
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
