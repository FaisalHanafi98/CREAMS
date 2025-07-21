<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TraineeProgress extends Model
{
    use HasFactory;

    protected $fillable = [
        'trainee_id',
        'activity_id',
        'assessment_date',
        'skill_area',
        'baseline_score',
        'current_score',
        'target_score',
        'notes',
        'assessed_by'
    ];

    protected $casts = [
        'assessment_date' => 'date',
        'baseline_score' => 'integer',
        'current_score' => 'integer',
        'target_score' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Get the trainee that owns the progress record
     */
    public function trainee()
    {
        return $this->belongsTo(Trainee::class);
    }

    /**
     * Get the activity associated with this progress
     */
    public function activity()
    {
        return $this->belongsTo(Activity::class);
    }

    /**
     * Get the user who assessed the progress
     */
    public function assessor()
    {
        return $this->belongsTo(Users::class, 'assessed_by');
    }

    /**
     * Calculate progress percentage
     */
    public function getProgressPercentageAttribute()
    {
        if (!$this->baseline_score || !$this->target_score) {
            return 0;
        }

        $totalImprovement = $this->target_score - $this->baseline_score;
        $currentImprovement = $this->current_score - $this->baseline_score;
        
        if ($totalImprovement <= 0) {
            return 100;
        }

        $percentage = ($currentImprovement / $totalImprovement) * 100;
        return max(0, min(100, round($percentage, 1)));
    }

    /**
     * Get improvement from baseline
     */
    public function getImprovementAttribute()
    {
        if (!$this->baseline_score) {
            return 0;
        }

        return $this->current_score - $this->baseline_score;
    }

    /**
     * Get progress status
     */
    public function getStatusAttribute()
    {
        $percentage = $this->progress_percentage;
        
        if ($percentage >= 100) {
            return 'achieved';
        } elseif ($percentage >= 75) {
            return 'excellent';
        } elseif ($percentage >= 50) {
            return 'good';
        } elseif ($percentage >= 25) {
            return 'improving';
        } else {
            return 'needs_attention';
        }
    }

    /**
     * Get status color class
     */
    public function getStatusColorAttribute()
    {
        switch ($this->status) {
            case 'achieved':
                return 'success';
            case 'excellent':
                return 'primary';
            case 'good':
                return 'info';
            case 'improving':
                return 'warning';
            case 'needs_attention':
                return 'danger';
            default:
                return 'secondary';
        }
    }

    /**
     * Scope for specific trainee
     */
    public function scopeForTrainee($query, $traineeId)
    {
        return $query->where('trainee_id', $traineeId);
    }

    /**
     * Scope for specific skill area
     */
    public function scopeBySkillArea($query, $skillArea)
    {
        return $query->where('skill_area', $skillArea);
    }

    /**
     * Scope for recent assessments
     */
    public function scopeRecent($query, $days = 30)
    {
        return $query->where('assessment_date', '>=', now()->subDays($days));
    }

    /**
     * Scope for date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('assessment_date', [$startDate, $endDate]);
    }

    /**
     * Get latest progress for each skill area
     */
    public function scopeLatestPerSkill($query, $traineeId)
    {
        return $query->select('*')
            ->where('trainee_id', $traineeId)
            ->whereIn('id', function($subQuery) use ($traineeId) {
                $subQuery->selectRaw('MAX(id)')
                    ->from('trainee_progress')
                    ->where('trainee_id', $traineeId)
                    ->groupBy('skill_area');
            });
    }

    /**
     * Calculate average progress for a trainee
     */
    public static function getAverageProgress($traineeId)
    {
        $latestProgress = self::latestPerSkill($traineeId)->get();
        
        if ($latestProgress->isEmpty()) {
            return 0;
        }

        $totalPercentage = $latestProgress->sum('progress_percentage');
        return round($totalPercentage / $latestProgress->count(), 1);
    }

    /**
     * Get skill areas for a trainee
     */
    public static function getSkillAreas($traineeId = null)
    {
        $query = self::select('skill_area')->distinct();
        
        if ($traineeId) {
            $query->where('trainee_id', $traineeId);
        }
        
        return $query->pluck('skill_area');
    }
}