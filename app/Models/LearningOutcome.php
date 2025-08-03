<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LearningOutcome extends Model
{
    use HasFactory;

    protected $fillable = [
        'activity_id',
        'outcome_title',
        'outcome_description',
        'competency_level',
        'assessment_criteria',
        'display_order',
        'is_active',
        'created_by'
    ];

    protected $casts = [
        'assessment_criteria' => 'array',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::deleting(function ($outcome) {
            // Delete all competency progress records when outcome is deleted
            $outcome->competencyProgress()->delete();
        });
    }

    // Relationships
    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function competencyProgress(): HasMany
    {
        return $this->hasMany(TraineeCompetencyProgress::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByCompetencyLevel($query, $level)
    {
        return $query->where('competency_level', $level);
    }

    public function scopeForActivity($query, $activityId)
    {
        return $query->where('activity_id', $activityId);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order');
    }

    // Mutators and Accessors
    public function setAssessmentCriteriaAttribute($value)
    {
        $this->attributes['assessment_criteria'] = is_array($value) ? json_encode($value) : $value;
    }

    public function getAssessmentCriteriaAttribute($value)
    {
        return is_string($value) ? json_decode($value, true) : $value;
    }

    // Helper Methods
    public function getCompetencyLevelColorAttribute()
    {
        return match($this->competency_level) {
            'Beginner' => 'success',
            'Intermediate' => 'warning', 
            'Advanced' => 'danger',
            default => 'secondary'
        };
    }

    public function getProgressStatisticsAttribute()
    {
        $total = $this->competencyProgress()->count();
        $achieved = $this->competencyProgress()->whereIn('current_level', ['Achieved', 'Mastered'])->count();
        $inProgress = $this->competencyProgress()->where('current_level', 'In Progress')->count();
        
        return [
            'total' => $total,
            'achieved' => $achieved,
            'in_progress' => $inProgress,
            'not_started' => $total - $achieved - $inProgress,
            'completion_rate' => $total > 0 ? round(($achieved / $total) * 100, 2) : 0
        ];
    }

    public function getTraineeProgress($traineeId)
    {
        return $this->competencyProgress()
            ->where('trainee_id', $traineeId)
            ->first();
    }

    public function updateTraineeProgress($traineeId, $level, $percentage, $assessedBy, $notes = null, $assessmentData = null)
    {
        return $this->competencyProgress()->updateOrCreate(
            ['trainee_id' => $traineeId],
            [
                'current_level' => $level,
                'progress_percentage' => $percentage,
                'last_assessed_at' => now(),
                'assessed_by' => $assessedBy,
                'notes' => $notes,
                'assessment_data' => $assessmentData
            ]
        );
    }
}