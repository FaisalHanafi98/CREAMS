<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class TraineeEducationPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'trainee_id',
        'plan_name',
        'plan_description',
        'start_date',
        'end_date',
        'review_date',
        'overall_goals',
        'strengths',
        'challenges',
        'support_services',
        'status',
        'plan_type',
        'target_completion_percentage',
        'notes',
        'created_by',
        'last_updated_by',
        'last_reviewed_at'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'review_date' => 'date',
        'overall_goals' => 'array',
        'strengths' => 'array',
        'challenges' => 'array',
        'support_services' => 'array',
        'target_completion_percentage' => 'decimal:2',
        'last_reviewed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($plan) {
            if (!$plan->plan_name) {
                $plan->plan_name = $plan->generateDefaultPlanName();
            }
            
            if (!$plan->review_date && $plan->start_date) {
                $plan->review_date = $plan->calculateNextReviewDate();
            }
        });

        static::updating(function ($plan) {
            if ($plan->isDirty(['overall_goals', 'strengths', 'challenges', 'support_services', 'notes'])) {
                $plan->last_updated_by = session('user_id');
            }
        });
    }

    // Relationships
    public function trainee(): BelongsTo
    {
        return $this->belongsTo(Trainee::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function lastUpdater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'last_updated_by');
    }

    public function goals(): HasMany
    {
        return $this->hasMany(IepActivityGoal::class, 'iep_id');
    }

    public function activeGoals(): HasMany
    {
        return $this->hasMany(IepActivityGoal::class, 'iep_id')
                    ->whereNotIn('goal_status', ['Discontinued']);
    }

    public function progressReports(): HasMany
    {
        return $this->hasMany(ProgressReport::class, 'iep_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'Active');
    }

    public function scopeForTrainee($query, $traineeId)
    {
        return $query->where('trainee_id', $traineeId);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('plan_type', $type);
    }

    public function scopeDueForReview($query, $days = 30)
    {
        return $query->where('review_date', '<=', now()->addDays($days))
                    ->where('status', 'Active');
    }

    public function scopeOverdue($query)
    {
        return $query->where('review_date', '<', now())
                    ->where('status', 'Active');
    }

    // Helper Methods
    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'Active' => 'success',
            'Completed' => 'primary',
            'Suspended' => 'warning',
            'Under Review' => 'info',
            default => 'secondary'
        };
    }

    public function getStatusIconAttribute()
    {
        return match($this->status) {
            'Active' => 'fas fa-play-circle',
            'Completed' => 'fas fa-check-circle',
            'Suspended' => 'fas fa-pause-circle',
            'Under Review' => 'fas fa-eye',
            default => 'fas fa-circle'
        };
    }

    public function getTypeColorAttribute()
    {
        return match($this->plan_type) {
            'Annual' => 'primary',
            'Quarterly' => 'info',
            'Custom' => 'warning',
            default => 'secondary'
        };
    }

    public function getDurationInDaysAttribute()
    {
        return $this->start_date->diffInDays($this->end_date);
    }

    public function getDaysRemainingAttribute()
    {
        return now()->diffInDays($this->end_date, false);
    }

    public function getProgressPercentageAttribute()
    {
        $totalGoals = $this->goals()->count();
        
        if ($totalGoals === 0) {
            return 0;
        }

        $achievedGoals = $this->goals()->where('goal_status', 'Achieved')->count();
        
        return round(($achievedGoals / $totalGoals) * 100, 2);
    }

    public function getOverallProgressSummaryAttribute()
    {
        $goals = $this->goals;
        $totalGoals = $goals->count();
        
        if ($totalGoals === 0) {
            return [
                'total_goals' => 0,
                'achieved' => 0,
                'in_progress' => 0,
                'not_started' => 0,
                'modified' => 0,
                'discontinued' => 0,
                'completion_rate' => 0,
                'average_progress' => 0
            ];
        }

        $statusCounts = $goals->groupBy('goal_status')->map->count();
        $averageProgress = $goals->avg('current_progress_percentage');

        return [
            'total_goals' => $totalGoals,
            'achieved' => $statusCounts->get('Achieved', 0),
            'in_progress' => $statusCounts->get('In Progress', 0),
            'not_started' => $statusCounts->get('Not Started', 0),
            'modified' => $statusCounts->get('Modified', 0),
            'discontinued' => $statusCounts->get('Discontinued', 0),
            'completion_rate' => $this->progress_percentage,
            'average_progress' => round($averageProgress, 2)
        ];
    }

    public function isActive()
    {
        return $this->status === 'Active';
    }

    public function isCompleted()
    {
        return $this->status === 'Completed';
    }

    public function isDueForReview($days = 7)
    {
        return $this->review_date && $this->review_date <= now()->addDays($days);
    }

    public function isOverdue()
    {
        return $this->review_date && $this->review_date < now();
    }

    public function isPastEndDate()
    {
        return $this->end_date < now();
    }

    public function updateReviewDate($date = null)
    {
        $this->update([
            'review_date' => $date ?: $this->calculateNextReviewDate(),
            'last_reviewed_at' => now()
        ]);
    }

    public function calculateNextReviewDate()
    {
        if (!$this->start_date) {
            return null;
        }

        return match($this->plan_type) {
            'Annual' => $this->start_date->addMonths(3),
            'Quarterly' => $this->start_date->addMonth(),
            'Custom' => $this->start_date->addMonths(2),
            default => $this->start_date->addMonths(3)
        };
    }

    private function generateDefaultPlanName()
    {
        $trainee = $this->trainee;
        $year = $this->start_date ? $this->start_date->year : now()->year;
        
        return "{$trainee->trainee_first_name} {$trainee->trainee_last_name} - {$this->plan_type} Plan {$year}";
    }

    public function createGoal(array $goalData)
    {
        return $this->goals()->create(array_merge($goalData, [
            'iep_id' => $this->id
        ]));
    }

    public function getGoalsByActivity($activityId)
    {
        return $this->goals()->where('activity_id', $activityId)->get();
    }

    public function getGoalsByStatus($status)
    {
        return $this->goals()->where('goal_status', $status)->get();
    }

    public function getGoalsByPriority($priority)
    {
        return $this->goals()->where('priority_level', $priority)->get();
    }

    public function getUpcomingDeadlines($days = 30)
    {
        return $this->goals()
                   ->where('target_completion_date', '<=', now()->addDays($days))
                   ->where('target_completion_date', '>=', now())
                   ->whereNotIn('goal_status', ['Achieved', 'Discontinued'])
                   ->orderBy('target_completion_date')
                   ->get();
    }

    public function getOverdueGoals()
    {
        return $this->goals()
                   ->where('target_completion_date', '<', now())
                   ->whereNotIn('goal_status', ['Achieved', 'Discontinued'])
                   ->orderBy('target_completion_date')
                   ->get();
    }

    public function generateProgressReport($type = 'Monthly')
    {
        $periodStart = match($type) {
            'Weekly' => now()->startOfWeek(),
            'Monthly' => now()->startOfMonth(),
            'Quarterly' => now()->startOfQuarter(),
            'Annual' => now()->startOfYear(),
            default => now()->startOfMonth()
        };

        $periodEnd = match($type) {
            'Weekly' => now()->endOfWeek(),
            'Monthly' => now()->endOfMonth(),
            'Quarterly' => now()->endOfQuarter(),
            'Annual' => now()->endOfYear(),
            default => now()->endOfMonth()
        };

        return ProgressReport::generateReport($this->trainee_id, $this->id, $type, $periodStart, $periodEnd);
    }

    public function extendPlan($months = 3, $reason = null)
    {
        $oldEndDate = $this->end_date;
        $newEndDate = $this->end_date->addMonths($months);
        
        $this->update([
            'end_date' => $newEndDate,
            'notes' => $this->notes . "\n\nPlan extended from {$oldEndDate->format('Y-m-d')} to {$newEndDate->format('Y-m-d')}." . 
                      ($reason ? " Reason: {$reason}" : ''),
            'last_updated_by' => session('user_id')
        ]);

        return $this;
    }
}