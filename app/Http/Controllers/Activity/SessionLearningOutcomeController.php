<?php

namespace App\Http\Controllers\Activity;

use App\Http\Controllers\Controller;
use App\Models\ActivitySession;
use App\Models\LearningOutcome;
use App\Models\TraineeCompetencyProgress;
use App\Models\SessionEnrollment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class SessionLearningOutcomeController extends Controller
{
    /**
     * Display session-specific learning outcome assignment interface
     */
    public function index($sessionId)
    {
        try {
            $session = ActivitySession::with([
                'activity.learningOutcomes',
                'activity.centre',
                'teacher',
                'sessionEnrollments.trainee'
            ])->findOrFail($sessionId);

            // Check permissions - only assigned teacher, supervisors, and admins can access
            $userRole = session('role');
            $userId = session('id');
            
            if (!in_array($userRole, ['admin', 'supervisor']) && 
                $session->teacher_id != $userId) {
                return redirect()->back()->with('error', 'Unauthorized access to session learning outcomes.');
            }

            // Get all learning outcomes for this activity
            $availableOutcomes = $session->activity->learningOutcomes;

            // Get session-specific outcome assignments (stored in session metadata)
            $sessionOutcomes = $this->getSessionOutcomes($sessionId);

            // Get enrolled trainees with their current progress
            $enrolledTrainees = $session->sessionEnrollments->map(function ($enrollment) use ($sessionOutcomes) {
                $trainee = $enrollment->trainee;
                $trainee->session_progress = $this->getTraineeSessionProgress($trainee->id, $sessionOutcomes);
                return $trainee;
            });

            // Get session outcome statistics
            $outcomeStats = $this->calculateSessionOutcomeStats($sessionId, $sessionOutcomes, $enrolledTrainees);

            return view('activities.sessions.learning-outcomes.index', compact(
                'session',
                'availableOutcomes',
                'sessionOutcomes',
                'enrolledTrainees',
                'outcomeStats'
            ));

        } catch (\Exception $e) {
            Log::error('Session learning outcomes view error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error loading session learning outcomes.');
        }
    }

    /**
     * Store session-specific learning outcome assignments
     */
    public function store(Request $request, $sessionId)
    {
        $validator = Validator::make($request->all(), [
            'selected_outcomes' => 'required|array|min:1',
            'selected_outcomes.*' => 'exists:learning_outcomes,id',
            'outcome_weights' => 'array',
            'outcome_weights.*' => 'numeric|min:0|max:100',
            'session_focus' => 'nullable|string|max:500',
            'assessment_method' => 'required|in:observation,practical,assessment,mixed'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $session = ActivitySession::findOrFail($sessionId);
            
            // Verify permission
            $userRole = session('role');
            $userId = session('id');
            
            if (!in_array($userRole, ['admin', 'supervisor']) && 
                $session->teacher_id != $userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access'
                ], 403);
            }

            // Prepare session learning outcome data
            $sessionOutcomeData = [
                'assigned_outcomes' => $request->selected_outcomes,
                'outcome_weights' => $request->outcome_weights ?? [],
                'session_focus' => $request->session_focus,
                'assessment_method' => $request->assessment_method,
                'assigned_by' => $userId,
                'assigned_at' => now()
            ];

            // Store in session's learning_outcomes_data JSON field
            $session->update([
                'learning_outcomes_data' => json_encode($sessionOutcomeData)
            ]);

            // Initialize progress tracking for enrolled trainees
            $this->initializeTraineeProgressTracking($sessionId, $request->selected_outcomes);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Session learning outcomes assigned successfully',
                'data' => [
                    'session_id' => $sessionId,
                    'outcomes_count' => count($request->selected_outcomes),
                    'assessment_method' => $request->assessment_method
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Session learning outcome assignment error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error assigning learning outcomes to session'
            ], 500);
        }
    }

    /**
     * Update trainee progress for session-specific outcomes
     */
    public function updateTraineeProgress(Request $request, $sessionId)
    {
        $validator = Validator::make($request->all(), [
            'trainee_progress' => 'required|array',
            'trainee_progress.*.trainee_id' => 'required|exists:trainees,id',
            'trainee_progress.*.outcome_id' => 'required|exists:learning_outcomes,id',
            'trainee_progress.*.progress_level' => 'required|in:Not Started,In Progress,Achieved,Mastered',
            'trainee_progress.*.competency_score' => 'nullable|numeric|min:0|max:10',
            'trainee_progress.*.session_notes' => 'nullable|string|max:1000'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $session = ActivitySession::findOrFail($sessionId);
            $updatedCount = 0;

            foreach ($request->trainee_progress as $progressData) {
                // Update or create trainee competency progress
                $progress = TraineeCompetencyProgress::updateOrCreate([
                    'trainee_id' => $progressData['trainee_id'],
                    'learning_outcome_id' => $progressData['outcome_id']
                ], [
                    'current_level' => $progressData['progress_level'],
                    'competency_score' => $progressData['competency_score'] ?? null,
                    'progress_percentage' => $this->calculateProgressPercentage($progressData['progress_level'], $progressData['competency_score'] ?? 0),
                    'last_assessed_at' => now(),
                    'assessed_by' => session('id'),
                    'notes' => $progressData['session_notes'] ?? null,
                    'session_context' => json_encode([
                        'session_id' => $sessionId,
                        'session_date' => $session->session_date,
                        'assessment_method' => json_decode($session->learning_outcomes_data, true)['assessment_method'] ?? 'observation'
                    ])
                ]);

                $updatedCount++;
            }

            // Update session completion statistics
            $this->updateSessionCompletionStats($sessionId);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Updated progress for {$updatedCount} trainee-outcome combinations",
                'data' => [
                    'updated_count' => $updatedCount,
                    'session_id' => $sessionId
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Trainee progress update error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error updating trainee progress'
            ], 500);
        }
    }

    /**
     * Get session outcome analytics
     */
    public function getSessionAnalytics($sessionId)
    {
        try {
            $session = ActivitySession::with(['activity.learningOutcomes', 'sessionEnrollments.trainee'])
                ->findOrFail($sessionId);

            $sessionOutcomes = $this->getSessionOutcomes($sessionId);
            
            if (empty($sessionOutcomes)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No learning outcomes assigned to this session'
                ]);
            }

            // Calculate analytics
            $analytics = [
                'session_info' => [
                    'session_id' => $sessionId,
                    'activity_name' => $session->activity->activity_name,
                    'session_date' => $session->session_date,
                    'enrolled_count' => $session->sessionEnrollments->count()
                ],
                'outcome_summary' => [],
                'trainee_performance' => [],
                'competency_distribution' => [],
                'progress_trends' => []
            ];

            // Calculate outcome-specific analytics
            foreach ($sessionOutcomes as $outcomeId) {
                $outcome = LearningOutcome::find($outcomeId);
                if (!$outcome) continue;

                $progressRecords = TraineeCompetencyProgress::where('learning_outcome_id', $outcomeId)
                    ->whereIn('trainee_id', $session->sessionEnrollments->pluck('trainee_id'))
                    ->get();

                $analytics['outcome_summary'][] = [
                    'outcome_id' => $outcomeId,
                    'outcome_title' => $outcome->outcome_title,
                    'competency_level' => $outcome->competency_level,
                    'total_trainees' => $session->sessionEnrollments->count(),
                    'assessed_trainees' => $progressRecords->count(),
                    'average_score' => $progressRecords->avg('competency_score') ?? 0,
                    'achievement_rate' => $this->calculateAchievementRate($progressRecords),
                    'progress_distribution' => $this->getProgressDistribution($progressRecords)
                ];
            }

            // Calculate individual trainee performance
            foreach ($session->sessionEnrollments as $enrollment) {
                $traineeProgress = TraineeCompetencyProgress::where('trainee_id', $enrollment->trainee_id)
                    ->whereIn('learning_outcome_id', $sessionOutcomes)
                    ->get();

                $analytics['trainee_performance'][] = [
                    'trainee_id' => $enrollment->trainee_id,
                    'trainee_name' => $enrollment->trainee->trainee_name,
                    'outcomes_assessed' => $traineeProgress->count(),
                    'total_outcomes' => count($sessionOutcomes),
                    'average_score' => $traineeProgress->avg('competency_score') ?? 0,
                    'highest_level' => $this->getHighestProgressLevel($traineeProgress),
                    'assessment_completion' => ($traineeProgress->count() / count($sessionOutcomes)) * 100
                ];
            }

            return response()->json([
                'success' => true,
                'analytics' => $analytics
            ]);

        } catch (\Exception $e) {
            Log::error('Session analytics error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error generating session analytics'
            ], 500);
        }
    }

    /**
     * Get available learning outcomes for session assignment
     */
    public function getAvailableOutcomes($sessionId)
    {
        try {
            $session = ActivitySession::with('activity.learningOutcomes')->findOrFail($sessionId);
            
            $outcomes = $session->activity->learningOutcomes->map(function ($outcome) {
                return [
                    'id' => $outcome->id,
                    'title' => $outcome->outcome_title,
                    'description' => $outcome->outcome_description,
                    'competency_level' => $outcome->competency_level,
                    'assessment_criteria' => $outcome->assessment_criteria
                ];
            });

            return response()->json([
                'success' => true,
                'outcomes' => $outcomes
            ]);

        } catch (\Exception $e) {
            Log::error('Available outcomes fetch error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error fetching available outcomes'
            ], 500);
        }
    }

    // Helper Methods

    private function getSessionOutcomes($sessionId)
    {
        $session = ActivitySession::find($sessionId);
        if (!$session || !$session->learning_outcomes_data) {
            return [];
        }

        $data = json_decode($session->learning_outcomes_data, true);
        return $data['assigned_outcomes'] ?? [];
    }

    private function getTraineeSessionProgress($traineeId, $sessionOutcomes)
    {
        if (empty($sessionOutcomes)) {
            return [];
        }

        return TraineeCompetencyProgress::where('trainee_id', $traineeId)
            ->whereIn('learning_outcome_id', $sessionOutcomes)
            ->with('learningOutcome')
            ->get()
            ->keyBy('learning_outcome_id')
            ->toArray();
    }

    private function calculateSessionOutcomeStats($sessionId, $sessionOutcomes, $enrolledTrainees)
    {
        if (empty($sessionOutcomes)) {
            return [
                'total_outcomes' => 0,
                'total_trainees' => $enrolledTrainees->count(),
                'assessments_completed' => 0,
                'average_progress' => 0
            ];
        }

        $totalAssessments = count($sessionOutcomes) * $enrolledTrainees->count();
        $completedAssessments = 0;
        $totalProgress = 0;

        foreach ($enrolledTrainees as $trainee) {
            foreach ($sessionOutcomes as $outcomeId) {
                $progress = TraineeCompetencyProgress::where('trainee_id', $trainee->id)
                    ->where('learning_outcome_id', $outcomeId)
                    ->first();

                if ($progress) {
                    $completedAssessments++;
                    $totalProgress += $progress->progress_percentage ?? 0;
                }
            }
        }

        return [
            'total_outcomes' => count($sessionOutcomes),
            'total_trainees' => $enrolledTrainees->count(),
            'total_possible_assessments' => $totalAssessments,
            'assessments_completed' => $completedAssessments,
            'completion_rate' => $totalAssessments > 0 ? ($completedAssessments / $totalAssessments) * 100 : 0,
            'average_progress' => $completedAssessments > 0 ? $totalProgress / $completedAssessments : 0
        ];
    }

    private function initializeTraineeProgressTracking($sessionId, $outcomeIds)
    {
        $session = ActivitySession::with('sessionEnrollments')->find($sessionId);
        if (!$session) return;

        foreach ($session->sessionEnrollments as $enrollment) {
            foreach ($outcomeIds as $outcomeId) {
                // Only create if doesn't exist (don't overwrite existing progress)
                TraineeCompetencyProgress::firstOrCreate([
                    'trainee_id' => $enrollment->trainee_id,
                    'learning_outcome_id' => $outcomeId
                ], [
                    'current_level' => 'Not Started',
                    'progress_percentage' => 0,
                    'assessed_by' => session('id'),
                    'last_assessed_at' => now()
                ]);
            }
        }
    }

    private function calculateProgressPercentage($level, $score)
    {
        $levelWeights = [
            'Not Started' => 0,
            'In Progress' => 25,
            'Achieved' => 70,
            'Mastered' => 100
        ];

        $basePercentage = $levelWeights[$level] ?? 0;
        
        // Adjust based on competency score if available
        if ($score > 0) {
            $scorePercentage = ($score / 10) * 100;
            return min(100, ($basePercentage + $scorePercentage) / 2);
        }

        return $basePercentage;
    }

    private function updateSessionCompletionStats($sessionId)
    {
        $session = ActivitySession::find($sessionId);
        if (!$session) return;

        $sessionOutcomes = $this->getSessionOutcomes($sessionId);
        if (empty($sessionOutcomes)) return;

        $enrolledCount = SessionEnrollment::where('session_id', $sessionId)->count();
        $totalPossibleAssessments = $enrolledCount * count($sessionOutcomes);
        
        $completedAssessments = TraineeCompetencyProgress::whereIn('learning_outcome_id', $sessionOutcomes)
            ->whereIn('trainee_id', SessionEnrollment::where('session_id', $sessionId)->pluck('trainee_id'))
            ->where('current_level', '!=', 'Not Started')
            ->count();

        $completionRate = $totalPossibleAssessments > 0 ? ($completedAssessments / $totalPossibleAssessments) * 100 : 0;

        // Update session with completion statistics
        $session->update([
            'outcome_completion_rate' => $completionRate,
            'last_progress_update' => now()
        ]);
    }

    private function calculateAchievementRate($progressRecords)
    {
        if ($progressRecords->isEmpty()) return 0;

        $achievedCount = $progressRecords->whereIn('current_level', ['Achieved', 'Mastered'])->count();
        return ($achievedCount / $progressRecords->count()) * 100;
    }

    private function getProgressDistribution($progressRecords)
    {
        $distribution = [
            'Not Started' => 0,
            'In Progress' => 0,
            'Achieved' => 0,
            'Mastered' => 0
        ];

        foreach ($progressRecords as $record) {
            $distribution[$record->current_level] = ($distribution[$record->current_level] ?? 0) + 1;
        }

        return $distribution;
    }

    private function getHighestProgressLevel($progressRecords)
    {
        if ($progressRecords->isEmpty()) return 'Not Started';

        $levels = ['Not Started' => 0, 'In Progress' => 1, 'Achieved' => 2, 'Mastered' => 3];
        $highestLevel = 'Not Started';
        $highestValue = 0;

        foreach ($progressRecords as $record) {
            $levelValue = $levels[$record->current_level] ?? 0;
            if ($levelValue > $highestValue) {
                $highestValue = $levelValue;
                $highestLevel = $record->current_level;
            }
        }

        return $highestLevel;
    }
}