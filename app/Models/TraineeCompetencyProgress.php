<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TraineeCompetencyProgress extends Model
{
    use HasFactory;

    protected $table = 'trainee_competency_progress';

    protected $fillable = [
        'trainee_id',
        'learning_outcome_id',
        'current_level',
        'progress_percentage',
        'last_assessed_at',
        'assessed_by',
        'notes',
        'assessment_data'
    ];

    protected $casts = [
        'progress_percentage' => 'decimal:2',
        'last_assessed_at' => 'datetime',
        'assessment_data' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // Relationships
    public function trainee(): BelongsTo
    {
        return $this->belongsTo(Trainee::class);
    }

    public function learningOutcome(): BelongsTo
    {
        return $this->belongsTo(LearningOutcome::class);
    }

    public function assessor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assessed_by');
    }

    // Scopes
    public function scopeByLevel($query, $level)
    {
        return $query->where('current_level', $level);
    }

    public function scopeAchieved($query)
    {
        return $query->whereIn('current_level', ['Achieved', 'Mastered']);
    }

    public function scopeInProgress($query)
    {
        return $query->where('current_level', 'In Progress');
    }

    public function scopeNotStarted($query)
    {
        return $query->where('current_level', 'Not Started');
    }

    public function scopeForTrainee($query, $traineeId)
    {
        return $query->where('trainee_id', $traineeId);
    }

    public function scopeForOutcome($query, $outcomeId)
    {
        return $query->where('learning_outcome_id', $outcomeId);
    }

    public function scopeRecentlyAssessed($query, $days = 30)
    {
        return $query->where('last_assessed_at', '>=', now()->subDays($days));
    }

    // Helper Methods
    public function getLevelColorAttribute()
    {
        return match($this->current_level) {
            'Not Started' => 'secondary',
            'In Progress' => 'primary',
            'Achieved' => 'success',
            'Mastered' => 'warning',
            default => 'secondary'
        };
    }

    public function getLevelIconAttribute()
    {
        return match($this->current_level) {
            'Not Started' => 'fas fa-circle',
            'In Progress' => 'fas fa-clock',
            'Achieved' => 'fas fa-check-circle',
            'Mastered' => 'fas fa-star',
            default => 'fas fa-circle'
        };
    }

    public function getProgressBadgeAttribute()
    {
        $color = $this->level_color;
        $icon = $this->level_icon;
        return "<span class='badge badge-{$color}'><i class='{$icon}'></i> {$this->current_level}</span>";
    }

    public function isAchieved()
    {
        return in_array($this->current_level, ['Achieved', 'Mastered']);
    }

    public function isInProgress()
    {
        return $this->current_level === 'In Progress';
    }

    public function isNotStarted()
    {
        return $this->current_level === 'Not Started';
    }

    public function isMastered()
    {
        return $this->current_level === 'Mastered';
    }

    public function getCompletionStatusAttribute()
    {
        if ($this->isAchieved()) {
            return [
                'status' => 'completed',
                'percentage' => $this->progress_percentage,
                'message' => 'Learning outcome achieved'
            ];
        } elseif ($this->isInProgress()) {
            return [
                'status' => 'in_progress',
                'percentage' => $this->progress_percentage,
                'message' => 'Currently working on this outcome'
            ];
        } else {
            return [
                'status' => 'not_started',
                'percentage' => 0,
                'message' => 'Not yet started'
            ];
        }
    }

    public function updateProgress($level, $percentage, $assessedBy, $notes = null, $assessmentData = null)
    {
        $this->update([
            'current_level' => $level,
            'progress_percentage' => $percentage,
            'last_assessed_at' => now(),
            'assessed_by' => $assessedBy,
            'notes' => $notes,
            'assessment_data' => $assessmentData
        ]);

        return $this;
    }

    public function getDaysInLevelAttribute()
    {
        $created = $this->created_at;
        $lastAssessed = $this->last_assessed_at ?? $created;
        
        return $lastAssessed->diffInDays(now());
    }

    public function getAssessmentSummaryAttribute()
    {
        return [
            'current_level' => $this->current_level,
            'progress_percentage' => $this->progress_percentage,
            'days_in_level' => $this->days_in_level,
            'last_assessed' => $this->last_assessed_at?->format('M d, Y'),
            'assessor' => $this->assessor?->name,
            'has_notes' => !empty($this->notes)
        ];
    }
}