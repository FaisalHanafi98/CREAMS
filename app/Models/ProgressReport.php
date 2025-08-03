<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ProgressReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'trainee_id',
        'iep_id',
        'report_title',
        'report_type',
        'report_period',
        'period_start_date',
        'period_end_date',
        'activity_progress',
        'learning_outcomes_progress',
        'iep_goals_progress',
        'attendance_summary',
        'competency_achievements',
        'recommendations',
        'overall_summary',
        'strengths_observed',
        'areas_for_improvement',
        'next_period_goals',
        'status',
        'parent_accessible',
        'shared_with_parents_at',
        'generated_by',
        'reviewed_by',
        'approved_by',
        'reviewed_at',
        'approved_at'
    ];

    protected $casts = [
        'period_start_date' => 'date',
        'period_end_date' => 'date',
        'activity_progress' => 'array',
        'learning_outcomes_progress' => 'array',
        'iep_goals_progress' => 'array',
        'attendance_summary' => 'array',
        'competency_achievements' => 'array',
        'recommendations' => 'array',
        'parent_accessible' => 'boolean',
        'shared_with_parents_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'approved_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($report) {
            if (!$report->report_title) {
                $report->report_title = $report->generateDefaultTitle();
            }
            
            $report->generated_by = session('user_id');
        });

        static::updating(function ($report) {
            if ($report->isDirty('status') && $report->status === 'Shared') {
                $report->shared_with_parents_at = now();
            }
        });
    }

    // Relationships
    public function trainee(): BelongsTo
    {
        return $this->belongsTo(Trainee::class);
    }

    public function iep(): BelongsTo
    {
        return $this->belongsTo(TraineeEducationPlan::class, 'iep_id');
    }

    public function generator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Scopes
    public function scopeForTrainee($query, $traineeId)
    {
        return $query->where('trainee_id', $traineeId);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('report_type', $type);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeParentAccessible($query)
    {
        return $query->where('parent_accessible', true)
                    ->where('status', 'Shared');
    }

    public function scopeInPeriod($query, $startDate, $endDate)
    {
        return $query->where('period_start_date', '>=', $startDate)
                    ->where('period_end_date', '<=', $endDate);
    }

    public function scopeRecentlyGenerated($query, $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    // Helper Methods
    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'Draft' => 'secondary',
            'In Review' => 'warning',
            'Approved' => 'success',
            'Shared' => 'primary',
            default => 'secondary'
        };
    }

    public function getStatusIconAttribute()
    {
        return match($this->status) {
            'Draft' => 'fas fa-edit',
            'In Review' => 'fas fa-eye',
            'Approved' => 'fas fa-check-circle',
            'Shared' => 'fas fa-share',
            default => 'fas fa-file'
        };
    }

    public function getTypeIconAttribute()
    {
        return match($this->report_type) {
            'Weekly' => 'fas fa-calendar-week',
            'Monthly' => 'fas fa-calendar-alt',
            'Quarterly' => 'fas fa-calendar',
            'Annual' => 'fas fa-calendar-plus',
            'Custom' => 'fas fa-cog',
            default => 'fas fa-file-alt'
        };
    }

    public function isDraft()
    {
        return $this->status === 'Draft';
    }

    public function isInReview()
    {
        return $this->status === 'In Review';
    }

    public function isApproved()
    {
        return $this->status === 'Approved';
    }

    public function isShared()
    {
        return $this->status === 'Shared';
    }

    public function canBeEdited()
    {
        return in_array($this->status, ['Draft', 'In Review']);
    }

    public function canBeSharedWithParents()
    {
        return $this->status === 'Approved';
    }

    public function submitForReview()
    {
        $this->update([
            'status' => 'In Review'
        ]);

        return $this;
    }

    public function approve($approverId, $notes = null)
    {
        $this->update([
            'status' => 'Approved',
            'approved_by' => $approverId,
            'approved_at' => now(),
            'recommendations' => $notes ? 
                array_merge($this->recommendations ?? [], [
                    'approval_notes' => $notes,
                    'approved_at' => now()->format('Y-m-d H:i:s')
                ]) : $this->recommendations
        ]);

        return $this;
    }

    public function shareWithParents()
    {
        if (!$this->isApproved()) {
            throw new \Exception('Report must be approved before sharing with parents.');
        }

        $this->update([
            'status' => 'Shared',
            'parent_accessible' => true,
            'shared_with_parents_at' => now()
        ]);

        return $this;
    }

    private function generateDefaultTitle()
    {
        $trainee = $this->trainee;
        $period = $this->period_start_date->format('M Y');
        
        return "{$trainee->trainee_first_name} {$trainee->trainee_last_name} - {$this->report_type} Progress Report ({$period})";
    }

    public function getDurationInDaysAttribute()
    {
        return $this->period_start_date->diffInDays($this->period_end_date);
    }

    public function getOverallProgressScoreAttribute()
    {
        $activityProgress = collect($this->activity_progress ?? []);
        $outcomeProgress = collect($this->learning_outcomes_progress ?? []);
        
        if ($activityProgress->isEmpty() && $outcomeProgress->isEmpty()) {
            return 0;
        }

        $activityAvg = $activityProgress->avg('progress_percentage') ?? 0;
        $outcomeAvg = $outcomeProgress->avg('progress_percentage') ?? 0;
        
        if ($activityAvg > 0 && $outcomeAvg > 0) {
            return round(($activityAvg + $outcomeAvg) / 2, 2);
        }
        
        return round($activityAvg ?: $outcomeAvg, 2);
    }

    public static function generateReport($traineeId, $iepId = null, $type = 'Monthly', $startDate = null, $endDate = null)
    {
        $trainee = Trainee::findOrFail($traineeId);
        $iep = $iepId ? TraineeEducationPlan::findOrFail($iepId) : null;

        // Set default dates based on report type
        if (!$startDate || !$endDate) {
            [$startDate, $endDate] = static::getDefaultDateRange($type);
        }

        // Generate comprehensive report data
        $reportData = [
            'trainee_id' => $traineeId,
            'iep_id' => $iepId,
            'report_type' => $type,
            'report_period' => 'Current',
            'period_start_date' => $startDate,
            'period_end_date' => $endDate,
            'activity_progress' => static::generateActivityProgress($traineeId, $startDate, $endDate),
            'learning_outcomes_progress' => static::generateLearningOutcomesProgress($traineeId, $startDate, $endDate),
            'iep_goals_progress' => $iep ? static::generateIepGoalsProgress($iep, $startDate, $endDate) : null,
            'attendance_summary' => static::generateAttendanceSummary($traineeId, $startDate, $endDate),
            'competency_achievements' => static::generateCompetencyAchievements($traineeId, $startDate, $endDate),
            'status' => 'Draft'
        ];

        return static::create($reportData);
    }

    private static function getDefaultDateRange($type)
    {
        return match($type) {
            'Weekly' => [now()->startOfWeek(), now()->endOfWeek()],
            'Monthly' => [now()->startOfMonth(), now()->endOfMonth()],
            'Quarterly' => [now()->startOfQuarter(), now()->endOfQuarter()],
            'Annual' => [now()->startOfYear(), now()->endOfYear()],
            default => [now()->startOfMonth(), now()->endOfMonth()]
        };
    }

    private static function generateActivityProgress($traineeId, $startDate, $endDate)
    {
        // Get trainee's activities and their progress
        $activities = Activity::whereHas('participants', function ($query) use ($traineeId) {
                $query->where('trainee_id', $traineeId);
            })
            ->with(['sessions' => function ($query) use ($startDate, $endDate) {
                $query->whereBetween('session_date', [$startDate, $endDate]);
            }])
            ->get();

        return $activities->map(function ($activity) use ($traineeId, $startDate, $endDate) {
            $totalSessions = $activity->sessions->count();
            $attendedSessions = $activity->sessions()
                ->whereHas('sessionEnrollments', function ($query) use ($traineeId) {
                    $query->where('trainee_id', $traineeId)
                          ->where('status', 'attended');
                })
                ->count();

            $progressPercentage = $totalSessions > 0 ? round(($attendedSessions / $totalSessions) * 100, 2) : 0;

            return [
                'activity_id' => $activity->id,
                'activity_name' => $activity->activity_name,
                'activity_type' => $activity->activity_type,
                'total_sessions' => $totalSessions,
                'attended_sessions' => $attendedSessions,
                'progress_percentage' => $progressPercentage,
                'status' => $progressPercentage >= 80 ? 'Good Progress' : 
                           ($progressPercentage >= 60 ? 'Moderate Progress' : 'Needs Improvement')
            ];
        })->toArray();
    }

    private static function generateLearningOutcomesProgress($traineeId, $startDate, $endDate)
    {
        // Get learning outcomes progress for the trainee
        $progress = TraineeCompetencyProgress::where('trainee_id', $traineeId)
            ->whereBetween('last_assessed_at', [$startDate, $endDate])
            ->with(['learningOutcome', 'learningOutcome.activity'])
            ->get();

        return $progress->map(function ($competency) {
            return [
                'learning_outcome_id' => $competency->learning_outcome_id,
                'outcome_title' => $competency->learningOutcome->outcome_title,
                'activity_name' => $competency->learningOutcome->activity->activity_name,
                'competency_level' => $competency->learningOutcome->competency_level,
                'current_level' => $competency->current_level,
                'progress_percentage' => $competency->progress_percentage,
                'last_assessed' => $competency->last_assessed_at->format('Y-m-d'),
                'assessor' => $competency->assessor->name ?? null,
                'notes' => $competency->notes
            ];
        })->toArray();
    }

    private static function generateIepGoalsProgress($iep, $startDate, $endDate)
    {
        $goals = $iep->goals()
            ->whereBetween('last_progress_update', [$startDate, $endDate])
            ->with(['activity', 'learningOutcome', 'assignedStaff'])
            ->get();

        return $goals->map(function ($goal) {
            return [
                'goal_id' => $goal->id,
                'goal_title' => $goal->goal_title,
                'activity_name' => $goal->activity->activity_name,
                'learning_outcome' => $goal->learningOutcome->outcome_title ?? null,
                'goal_status' => $goal->goal_status,
                'priority_level' => $goal->priority_level,
                'current_progress' => $goal->current_progress_percentage,
                'target_progress' => $goal->target_percentage,
                'target_completion_date' => $goal->target_completion_date->format('Y-m-d'),
                'days_remaining' => $goal->days_until_deadline,
                'assigned_staff' => $goal->assignedStaff->name ?? null,
                'tracking_method' => $goal->progress_tracking_method,
                'is_overdue' => $goal->isOverdue(),
                'is_due_soon' => $goal->isDueSoon()
            ];
        })->toArray();
    }

    private static function generateAttendanceSummary($traineeId, $startDate, $endDate)
    {
        // Calculate attendance statistics
        $totalSessions = ActivitySession::whereBetween('session_date', [$startDate, $endDate])
            ->whereHas('sessionEnrollments', function ($query) use ($traineeId) {
                $query->where('trainee_id', $traineeId);
            })
            ->count();

        $attendedSessions = ActivitySession::whereBetween('session_date', [$startDate, $endDate])
            ->whereHas('sessionEnrollments', function ($query) use ($traineeId) {
                $query->where('trainee_id', $traineeId)
                      ->where('status', 'attended');
            })
            ->count();

        $attendanceRate = $totalSessions > 0 ? round(($attendedSessions / $totalSessions) * 100, 2) : 0;

        return [
            'total_sessions' => $totalSessions,
            'attended_sessions' => $attendedSessions,
            'missed_sessions' => $totalSessions - $attendedSessions,
            'attendance_rate' => $attendanceRate,
            'attendance_grade' => $attendanceRate >= 90 ? 'Excellent' : 
                                ($attendanceRate >= 80 ? 'Good' : 
                                ($attendanceRate >= 70 ? 'Fair' : 'Needs Improvement')),
            'period_start' => $startDate->format('Y-m-d'),
            'period_end' => $endDate->format('Y-m-d')
        ];
    }

    private static function generateCompetencyAchievements($traineeId, $startDate, $endDate)
    {
        // Get recently achieved competencies
        $achievements = TraineeCompetencyProgress::where('trainee_id', $traineeId)
            ->whereIn('current_level', ['Achieved', 'Mastered'])
            ->whereBetween('last_assessed_at', [$startDate, $endDate])
            ->with(['learningOutcome', 'learningOutcome.activity', 'assessor'])
            ->get();

        return $achievements->map(function ($achievement) {
            return [
                'learning_outcome_id' => $achievement->learning_outcome_id,
                'outcome_title' => $achievement->learningOutcome->outcome_title,
                'activity_name' => $achievement->learningOutcome->activity->activity_name,
                'competency_level' => $achievement->learningOutcome->competency_level,
                'achieved_level' => $achievement->current_level,
                'achieved_date' => $achievement->last_assessed_at->format('Y-m-d'),
                'progress_percentage' => $achievement->progress_percentage,
                'assessor' => $achievement->assessor->name ?? null,
                'notes' => $achievement->notes
            ];
        })->toArray();
    }
}