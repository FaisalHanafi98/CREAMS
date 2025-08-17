<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Collection;

/**
 * Learning Outcome Compatibility Model
 * 
 * This model provides backward compatibility for code expecting LearningOutcome model
 * while actually using the activity_outcomes JSON field from the activities table.
 * 
 * This prevents breaking existing code while transitioning to the new structure.
 */
class LearningOutcome
{
    use HasFactory;

    /**
     * Static method to create fake collection from activity outcomes
     */
    public static function forActivity($activityId)
    {
        $activity = Activity::find($activityId);
        if (!$activity) {
            return new Collection();
        }
        
        return $activity->learning_outcomes->map(function($outcome, $index) use ($activityId) {
            return new static([
                'id' => $index + 1,
                'activity_id' => $activityId,
                'outcome_title' => is_array($outcome) ? ($outcome['title'] ?? 'Learning Outcome') : $outcome,
                'outcome_description' => is_array($outcome) ? ($outcome['description'] ?? '') : '',
                'competency_level' => is_array($outcome) ? ($outcome['level'] ?? 'Beginner') : 'Beginner',
                'assessment_criteria' => is_array($outcome) ? ($outcome['criteria'] ?? []) : [],
                'display_order' => $index + 1,
                'is_active' => is_array($outcome) ? ($outcome['is_active'] ?? true) : true,
                'created_by' => 1
            ]);
        });
    }

    protected $attributes = [];

    public function __construct($attributes = [])
    {
        $this->attributes = $attributes;
    }

    public function __get($key)
    {
        return $this->attributes[$key] ?? null;
    }

    public function __set($key, $value)
    {
        $this->attributes[$key] = $value;
    }

    public function __isset($key)
    {
        return isset($this->attributes[$key]);
    }

    // Fake relationships for compatibility
    public function activity()
    {
        return Activity::find($this->activity_id);
    }

    public function creator()
    {
        return User::find($this->created_by);
    }

    public function competencyProgress()
    {
        // Return empty collection since this is transitional
        return new Collection();
    }

    // Fake scopes as static methods
    public static function active()
    {
        return new static();
    }

    public static function where($field, $operator, $value = null)
    {
        return new Collection();
    }

    public static function find($id)
    {
        return null;
    }

    public static function all()
    {
        return new Collection();
    }

    public function count()
    {
        return 0;
    }

    // Fake attribute accessors
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
        return [
            'total' => 0,
            'achieved' => 0,
            'in_progress' => 0,
            'not_started' => 0,
            'completion_rate' => 0
        ];
    }

    public function getTraineeProgress($traineeId)
    {
        return null;
    }

    // Create/Update methods that do nothing (transitional)
    public static function create($attributes)
    {
        return new static($attributes);
    }

    public function update($attributes)
    {
        $this->attributes = array_merge($this->attributes, $attributes);
        return true;
    }

    public function delete()
    {
        return true;
    }

    public function save()
    {
        return true;
    }
}