<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class SessionProgress extends Model
{
    use HasFactory;

    protected $table = 'session_progress';

    protected $fillable = [
        'activity_session_id',
        'trainee_id', 
        'activity_id',
        'skills_practiced',
        'achievements',
        'challenges',
        'adaptations_made',
        'engagement_level',
        'comprehension_level', 
        'independence_level',
        'motor_skills_progress',
        'communication_progress',
        'social_interaction_progress',
        'cognitive_progress',
        'behavioral_progress',
        'session_goals_met',
        'next_session_focus',
        'recommendations',
        'parent_communication',
        'assessed_by',
        'assessment_date',
        'additional_notes'
    ];

    protected $casts = [
        'skills_practiced' => 'array',
        'achievements' => 'array', 
        'challenges' => 'array',
        'adaptations_made' => 'array',
        'assessment_date' => 'datetime',
        'motor_skills_progress' => 'integer',
        'communication_progress' => 'integer',
        'social_interaction_progress' => 'integer',
        'cognitive_progress' => 'integer',
        'behavioral_progress' => 'integer'
    ];

    // Relationships
    public function activitySession()
    {
        return $this->belongsTo(ActivitySession::class);
    }

    public function trainee()
    {
        return $this->belongsTo(Trainee::class);
    }

    public function activity()
    {
        return $this->belongsTo(Activity::class);
    }

    public function assessor()
    {
        return $this->belongsTo(User::class, 'assessed_by');
    }

    // Accessors
    public function getOverallProgressScoreAttribute()
    {
        $scores = [
            $this->motor_skills_progress,
            $this->communication_progress,
            $this->social_interaction_progress,
            $this->cognitive_progress,
            $this->behavioral_progress
        ];
        
        $validScores = array_filter($scores, function($score) {
            return $score !== null && $score > 0;
        });
        
        return !empty($validScores) ? round(array_sum($validScores) / count($validScores), 2) : 0;
    }

    public function getEngagementLevelDisplayAttribute()
    {
        $levels = [
            'very_high' => 'Very High',
            'high' => 'High', 
            'moderate' => 'Moderate',
            'low' => 'Low',
            'very_low' => 'Very Low'
        ];
        
        return $levels[$this->engagement_level] ?? 'Not Assessed';
    }

    public function getComprehensionLevelDisplayAttribute()
    {
        $levels = [
            'excellent' => 'Excellent',
            'good' => 'Good',
            'fair' => 'Fair', 
            'poor' => 'Poor',
            'unable_to_assess' => 'Unable to Assess'
        ];
        
        return $levels[$this->comprehension_level] ?? 'Not Assessed';
    }

    public function getIndependenceLevelDisplayAttribute()
    {
        $levels = [
            'independent' => 'Independent',
            'minimal_help' => 'Minimal Help',
            'moderate_help' => 'Moderate Help',
            'maximum_help' => 'Maximum Help', 
            'dependent' => 'Dependent'
        ];
        
        return $levels[$this->independence_level] ?? 'Not Assessed';
    }

    // Scopes
    public function scopeForTrainee($query, $traineeId)
    {
        return $query->where('trainee_id', $traineeId);
    }

    public function scopeForActivity($query, $activityId)
    {
        return $query->where('activity_id', $activityId);
    }

    public function scopeRecentProgress($query, $days = 30)
    {
        return $query->where('assessment_date', '>=', Carbon::now()->subDays($days));
    }

    public function scopeWithImprovement($query)
    {
        return $query->where(function($q) {
            $q->where('motor_skills_progress', '>=', 3)
              ->orWhere('communication_progress', '>=', 3)
              ->orWhere('social_interaction_progress', '>=', 3)
              ->orWhere('cognitive_progress', '>=', 3)
              ->orWhere('behavioral_progress', '>=', 3);
        });
    }

    public function scopeNeedingAttention($query)
    {
        return $query->where(function($q) {
            $q->where('engagement_level', 'low')
              ->orWhere('engagement_level', 'very_low')
              ->orWhere('comprehension_level', 'poor')
              ->orWhere('independence_level', 'dependent');
        });
    }

    // Helper methods
    public static function getProgressTrend($traineeId, $activityId, $skill = null, $months = 3)
    {
        $query = static::where('trainee_id', $traineeId)
                      ->where('activity_id', $activityId)
                      ->where('assessment_date', '>=', Carbon::now()->subMonths($months))
                      ->orderBy('assessment_date');

        if ($skill) {
            $query->whereNotNull($skill . '_progress');
        }

        $records = $query->get();
        
        if ($records->count() < 2) {
            return ['trend' => 'insufficient_data', 'data' => []];
        }

        // Calculate trend for specific skill or overall
        $values = $records->map(function($record) use ($skill) {
            return $skill ? $record->{$skill . '_progress'} : $record->overall_progress_score;
        })->filter()->values();

        if ($values->count() < 2) {
            return ['trend' => 'insufficient_data', 'data' => []];
        }

        $first = $values->first();
        $last = $values->last();
        $trend = $last > $first ? 'improving' : ($last < $first ? 'declining' : 'stable');

        return [
            'trend' => $trend,
            'data' => $values->toArray(),
            'improvement' => $last - $first,
            'percentage_change' => $first > 0 ? round((($last - $first) / $first) * 100, 2) : 0
        ];
    }

    public function hasSignificantProgress()
    {
        $threshold = 3; // Progress score of 3 or above
        return $this->motor_skills_progress >= $threshold ||
               $this->communication_progress >= $threshold ||
               $this->social_interaction_progress >= $threshold ||
               $this->cognitive_progress >= $threshold ||
               $this->behavioral_progress >= $threshold;
    }

    public function needsAttention()
    {
        return in_array($this->engagement_level, ['low', 'very_low']) ||
               $this->comprehension_level === 'poor' ||
               $this->independence_level === 'dependent' ||
               !empty($this->challenges);
    }
}