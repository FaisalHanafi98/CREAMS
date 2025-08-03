<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityPrerequisite extends Model
{
    use HasFactory;

    protected $fillable = [
        'activity_id',
        'prerequisite_activity_id',
        'minimum_completion_percentage',
        'required_competency_level',
        'is_required',
        'description'
    ];

    protected $casts = [
        'minimum_completion_percentage' => 'decimal:2',
        'is_required' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // Relationships
    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class);
    }

    public function prerequisiteActivity(): BelongsTo
    {
        return $this->belongsTo(Activity::class, 'prerequisite_activity_id');
    }

    // Scopes
    public function scopeRequired($query)
    {
        return $query->where('is_required', true);
    }

    public function scopeOptional($query)
    {
        return $query->where('is_required', false);
    }

    public function scopeForActivity($query, $activityId)
    {
        return $query->where('activity_id', $activityId);
    }

    public function scopeByCompetencyLevel($query, $level)
    {
        return $query->where('required_competency_level', $level);
    }

    // Helper Methods
    public function getCompetencyLevelColorAttribute()
    {
        return match($this->required_competency_level) {
            'Beginner' => 'success',
            'Intermediate' => 'warning',
            'Advanced' => 'danger',
            default => 'secondary'
        };
    }

    public function getRequiredBadgeAttribute()
    {
        $color = $this->is_required ? 'danger' : 'secondary';
        $text = $this->is_required ? 'Required' : 'Optional';
        return "<span class='badge badge-{$color}'>{$text}</span>";
    }

    public function checkPrerequisiteCompletion($traineeId)
    {
        // Get trainee's completion status for the prerequisite activity
        $prerequisiteActivity = $this->prerequisiteActivity;
        
        // Calculate completion based on learning outcomes progress
        $outcomes = $prerequisiteActivity->learningOutcomes()->active()->get();
        
        if ($outcomes->isEmpty()) {
            // If no learning outcomes defined, check activity sessions completion
            return $this->checkSessionBasedCompletion($traineeId);
        }

        return $this->checkOutcomeBasedCompletion($traineeId, $outcomes);
    }

    private function checkSessionBasedCompletion($traineeId)
    {
        $totalSessions = $this->prerequisiteActivity->activitySessions()->count();
        
        if ($totalSessions === 0) {
            return [
                'met' => true,
                'completion_percentage' => 100,
                'type' => 'session_based',
                'message' => 'No sessions required'
            ];
        }

        // Count attended sessions
        $attendedSessions = $this->prerequisiteActivity->activitySessions()
            ->whereHas('sessionEnrollments', function ($query) use ($traineeId) {
                $query->where('trainee_id', $traineeId)
                      ->where('status', 'attended');
            })
            ->count();

        $completionPercentage = ($attendedSessions / $totalSessions) * 100;
        $met = $completionPercentage >= $this->minimum_completion_percentage;

        return [
            'met' => $met,
            'completion_percentage' => round($completionPercentage, 2),
            'attended_sessions' => $attendedSessions,
            'total_sessions' => $totalSessions,
            'type' => 'session_based',
            'message' => $met ? 'Prerequisite met' : "Need {$this->minimum_completion_percentage}% completion"
        ];
    }

    private function checkOutcomeBasedCompletion($traineeId, $outcomes)
    {
        $totalOutcomes = $outcomes->count();
        $achievedOutcomes = 0;
        $totalProgress = 0;

        foreach ($outcomes as $outcome) {
            $progress = $outcome->getTraineeProgress($traineeId);
            
            if ($progress) {
                if ($progress->isAchieved()) {
                    $achievedOutcomes++;
                }
                $totalProgress += $progress->progress_percentage;
            }
        }

        $averageProgress = $totalOutcomes > 0 ? $totalProgress / $totalOutcomes : 0;
        $completionPercentage = ($achievedOutcomes / $totalOutcomes) * 100;
        
        // Check if minimum competency level is met
        $competencyMet = $this->checkCompetencyLevel($traineeId, $outcomes);
        
        $met = $completionPercentage >= $this->minimum_completion_percentage && $competencyMet;

        return [
            'met' => $met,
            'completion_percentage' => round($completionPercentage, 2),
            'average_progress' => round($averageProgress, 2),
            'achieved_outcomes' => $achievedOutcomes,
            'total_outcomes' => $totalOutcomes,
            'competency_met' => $competencyMet,
            'type' => 'outcome_based',
            'message' => $met ? 'Prerequisite met' : $this->getPrerequisiteMessage($completionPercentage, $competencyMet)
        ];
    }

    private function checkCompetencyLevel($traineeId, $outcomes)
    {
        $requiredLevel = $this->required_competency_level;
        
        // Map competency levels to numeric values for comparison
        $levelValues = [
            'Beginner' => 1,
            'Intermediate' => 2,
            'Advanced' => 3
        ];

        $requiredValue = $levelValues[$requiredLevel] ?? 1;

        foreach ($outcomes as $outcome) {
            $outcomeLevel = $levelValues[$outcome->competency_level] ?? 1;
            
            if ($outcomeLevel >= $requiredValue) {
                $progress = $outcome->getTraineeProgress($traineeId);
                
                if ($progress && $progress->isAchieved()) {
                    return true; // At least one outcome at required level is achieved
                }
            }
        }

        return false;
    }

    private function getPrerequisiteMessage($completionPercentage, $competencyMet)
    {
        $messages = [];
        
        if ($completionPercentage < $this->minimum_completion_percentage) {
            $messages[] = "Need {$this->minimum_completion_percentage}% completion";
        }
        
        if (!$competencyMet) {
            $messages[] = "Need {$this->required_competency_level} level competency";
        }

        return implode(', ', $messages);
    }

    public function getPrerequisiteStatusForTrainee($traineeId)
    {
        $completion = $this->checkPrerequisiteCompletion($traineeId);
        
        return [
            'prerequisite_id' => $this->id,
            'activity_name' => $this->prerequisiteActivity->activity_name,
            'is_required' => $this->is_required,
            'minimum_completion' => $this->minimum_completion_percentage,
            'required_level' => $this->required_competency_level,
            'status' => $completion,
            'description' => $this->description
        ];
    }

    public static function getActivityPrerequisiteStatus($activityId, $traineeId)
    {
        $prerequisites = static::forActivity($activityId)->with(['prerequisiteActivity', 'activity'])->get();
        
        $status = [
            'all_met' => true,
            'required_met' => true,
            'prerequisites' => []
        ];

        foreach ($prerequisites as $prerequisite) {
            $prereqStatus = $prerequisite->getPrerequisiteStatusForTrainee($traineeId);
            $status['prerequisites'][] = $prereqStatus;
            
            if (!$prereqStatus['status']['met']) {
                $status['all_met'] = false;
                
                if ($prerequisite->is_required) {
                    $status['required_met'] = false;
                }
            }
        }

        return $status;
    }
}