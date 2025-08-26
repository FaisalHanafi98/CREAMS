<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IepActivityGoal extends Model
{
    use HasFactory;

    protected $fillable = [
        'iep_id',
        'activity_id',
        'learning_outcome_id',
        'goal_title',
        'goal_description',
        'target_start_date',
        'target_completion_date',
        'progress_tracking_method',
        'target_percentage',
        'priority_level',
        'goal_status',
        'success_criteria',
        'accommodation_strategies',
        'notes',
        'current_progress_percentage',
        'last_progress_update',
        'assigned_user_id'
    ];

    protected $casts = [
        'target_start_date' => 'date',
        'target_completion_date' => 'date',
        'target_percentage' => 'decimal:2',
        'current_progress_percentage' => 'decimal:2',
        'success_criteria' => 'array',
        'accommodation_strategies' => 'array',
        'last_progress_update' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::updating(function ($goal) {
            if ($goal->isDirty('current_progress_percentage')) {
                $goal->last_progress_update = now();
                
                // Auto-update status based on progress
                if ($goal->current_progress_percentage >= $goal->target_percentage) {
                    $goal->goal_status = 'Achieved';
                } elseif ($goal->current_progress_percentage > 0) {
                    $goal->goal_status = 'In Progress';
                }
            }
        });
    }

    // Relationships
    public function iep(): BelongsTo
    {
        return $this->belongsTo(TraineeEducationPlan::class, 'iep_id');
    }

    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class);
    }

    public function learningOutcome(): BelongsTo
    {
        return $this->belongsTo(LearningOutcome::class);
    }

    public function assignedStaff(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    // Scopes
    public function scopeByStatus($query, $status)
    {
        return $query->where('goal_status', $status);
    }

    public function scopeActive($query)
    {
        return $query->whereNotIn('goal_status', ['Discontinued']);
    }

    public function scopeAchieved($query)
    {
        return $query->where('goal_status', 'Achieved');
    }

    public function scopeInProgress($query)
    {
        return $query->where('goal_status', 'In Progress');
    }

    public function scopeNotStarted($query)
    {
        return $query->where('goal_status', 'Not Started');
    }

    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority_level', $priority);
    }

    public function scopeHighPriority($query)
    {
        return $query->whereIn('priority_level', ['High', 'Critical']);
    }

    public function scopeForActivity($query, $activityId)
    {
        return $query->where('activity_id', $activityId);
    }

    public function scopeForLearningOutcome($query, $outcomeId)
    {
        return $query->where('learning_outcome_id', $outcomeId);
    }

    public function scopeAssignedTo($query, $staffId)
    {
        return $query->where('assigned_user_id', $staffId);
    }

    public function scopeDueSoon($query, $days = 7)
    {
        return $query->where('target_completion_date', '<=', now()->addDays($days))
                    ->where('target_completion_date', '>=', now())
                    ->whereNotIn('goal_status', ['Achieved', 'Discontinued']);
    }

    public function scopeOverdue($query)
    {
        return $query->where('target_completion_date', '<', now())
                    ->whereNotIn('goal_status', ['Achieved', 'Discontinued']);
    }

    public function scopeByTrackingMethod($query, $method)
    {
        return $query->where('progress_tracking_method', $method);
    }

    // Helper Methods
    public function getStatusColorAttribute()
    {
        return match($this->goal_status) {
            'Not Started' => 'secondary',
            'In Progress' => 'primary',
            'Achieved' => 'success',
            'Modified' => 'warning',
            'Discontinued' => 'danger',
            default => 'secondary'
        };
    }

    public function getStatusIconAttribute()
    {
        return match($this->goal_status) {
            'Not Started' => 'fas fa-circle',
            'In Progress' => 'fas fa-clock',
            'Achieved' => 'fas fa-check-circle',
            'Modified' => 'fas fa-edit',
            'Discontinued' => 'fas fa-times-circle',
            default => 'fas fa-circle'
        };
    }

    public function getPriorityColorAttribute()
    {
        return match($this->priority_level) {
            'Low' => 'success',
            'Medium' => 'info',
            'High' => 'warning',
            'Critical' => 'danger',
            default => 'secondary'
        };
    }

    public function getPriorityIconAttribute()
    {
        return match($this->priority_level) {
            'Low' => 'fas fa-chevron-down',
            'Medium' => 'fas fa-minus',
            'High' => 'fas fa-chevron-up',
            'Critical' => 'fas fa-exclamation-triangle',
            default => 'fas fa-minus'
        };
    }

    public function getTrackingMethodIconAttribute()
    {
        return match($this->progress_tracking_method) {
            'Attendance' => 'fas fa-calendar-check',
            'Competency' => 'fas fa-star',
            'Assessment' => 'fas fa-clipboard-list',
            'Mixed' => 'fas fa-layer-group',
            default => 'fas fa-chart-line'
        };
    }

    public function getDaysUntilDeadlineAttribute()
    {
        return now()->diffInDays($this->target_completion_date, false);
    }

    public function getDurationInDaysAttribute()
    {
        return $this->target_start_date->diffInDays($this->target_completion_date);
    }

    public function getTimeElapsedPercentageAttribute()
    {
        $totalDays = $this->duration_in_days;
        $daysElapsed = $this->target_start_date->diffInDays(now());
        
        if ($totalDays <= 0) {
            return 0;
        }
        
        return min(100, round(($daysElapsed / $totalDays) * 100, 2));
    }

    public function isAchieved()
    {
        return $this->goal_status === 'Achieved';
    }

    public function isInProgress()
    {
        return $this->goal_status === 'In Progress';
    }

    public function isNotStarted()
    {
        return $this->goal_status === 'Not Started';
    }

    public function isOverdue()
    {
        return $this->target_completion_date < now() && !$this->isAchieved();
    }

    public function isDueSoon($days = 7)
    {
        return $this->target_completion_date <= now()->addDays($days) && 
               $this->target_completion_date >= now() && 
               !$this->isAchieved();
    }

    public function isHighPriority()
    {
        return in_array($this->priority_level, ['High', 'Critical']);
    }

    public function updateProgress($percentage, $notes = null)
    {
        $this->update([
            'current_progress_percentage' => $percentage,
            'notes' => $notes ? ($this->notes . "\n\n" . now()->format('Y-m-d H:i') . ": " . $notes) : $this->notes,
            'last_progress_update' => now()
        ]);

        return $this;
    }

    public function markAsAchieved($notes = null)
    {
        $this->update([
            'goal_status' => 'Achieved',
            'current_progress_percentage' => 100,
            'notes' => $notes ? ($this->notes . "\n\n" . now()->format('Y-m-d H:i') . ": ACHIEVED - " . $notes) : $this->notes,
            'last_progress_update' => now()
        ]);

        return $this;
    }

    public function modifyGoal($newDescription, $newTargetDate = null, $reason = null)
    {
        $oldDescription = $this->goal_description;
        $oldTargetDate = $this->target_completion_date;
        
        $this->update([
            'goal_description' => $newDescription,
            'target_completion_date' => $newTargetDate ?: $this->target_completion_date,
            'goal_status' => 'Modified',
            'notes' => $this->notes . "\n\n" . now()->format('Y-m-d H:i') . ": MODIFIED" .
                      ($reason ? " - Reason: " . $reason : '') .
                      "\nOld description: " . $oldDescription .
                      ($newTargetDate ? "\nTarget date changed from {$oldTargetDate->format('Y-m-d')} to {$newTargetDate->format('Y-m-d')}" : ''),
            'last_progress_update' => now()
        ]);

        return $this;
    }

    public function discontinueGoal($reason)
    {
        $this->update([
            'goal_status' => 'Discontinued',
            'notes' => $this->notes . "\n\n" . now()->format('Y-m-d H:i') . ": DISCONTINUED - " . $reason,
            'last_progress_update' => now()
        ]);

        return $this;
    }

    public function getProgressSummaryAttribute()
    {
        return [
            'goal_id' => $this->id,
            'goal_title' => $this->goal_title,
            'activity' => $this->activity->activity_name ?? 'Unknown Activity',
            'learning_outcome' => $this->learningOutcome->outcome_title ?? null,
            'status' => $this->goal_status,
            'priority' => $this->priority_level,
            'current_progress' => $this->current_progress_percentage,
            'target_progress' => $this->target_percentage,
            'days_until_deadline' => $this->days_until_deadline,
            'is_overdue' => $this->isOverdue(),
            'is_due_soon' => $this->isDueSoon(),
            'tracking_method' => $this->progress_tracking_method,
            'assigned_staff' => $this->assignedStaff->name ?? null,
            'last_updated' => $this->last_progress_update?->format('Y-m-d H:i')
        ];
    }

    public function calculateAutomaticProgress()
    {
        return match($this->progress_tracking_method) {
            'Attendance' => $this->calculateAttendanceProgress(),
            'Competency' => $this->calculateCompetencyProgress(),
            'Assessment' => $this->calculateAssessmentProgress(),
            'Mixed' => $this->calculateMixedProgress(),
            default => $this->current_progress_percentage
        };
    }

    private function calculateAttendanceProgress()
    {
        // Calculate based on activity session attendance
        $traineeId = $this->iep->trainee_id;
        $activityId = $this->activity_id;
        
        $totalSessions = $this->activity->sessions()
            ->where('session_date', '>=', $this->target_start_date)
            ->where('session_date', '<=', $this->target_completion_date)
            ->count();

        if ($totalSessions === 0) {
            return 0;
        }

        $attendedSessions = $this->activity->sessions()
            ->where('session_date', '>=', $this->target_start_date)
            ->where('session_date', '<=', $this->target_completion_date)
            ->whereHas('sessionEnrollments', function ($query) use ($traineeId) {
                $query->where('trainee_id', $traineeId)
                      ->where('status', 'attended');
            })
            ->count();

        return round(($attendedSessions / $totalSessions) * 100, 2);
    }

    private function calculateCompetencyProgress()
    {
        // Calculate based on learning outcome competency progress
        if (!$this->learning_outcome_id) {
            return $this->current_progress_percentage;
        }

        $traineeId = $this->iep->trainee_id;
        $competencyProgress = TraineeCompetencyProgress::where('trainee_id', $traineeId)
            ->where('learning_outcome_id', $this->learning_outcome_id)
            ->first();

        return $competencyProgress ? $competencyProgress->progress_percentage : 0;
    }

    private function calculateAssessmentProgress()
    {
        // This would integrate with an assessment system
        // For now, return current progress
        return $this->current_progress_percentage;
    }

    private function calculateMixedProgress()
    {
        // Combine attendance and competency progress
        $attendanceProgress = $this->calculateAttendanceProgress();
        $competencyProgress = $this->calculateCompetencyProgress();
        
        // Weight: 40% attendance, 60% competency
        return round(($attendanceProgress * 0.4) + ($competencyProgress * 0.6), 2);
    }
}