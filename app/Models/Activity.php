<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Activity Model
 *
 * @property int $id
 * @property string $activity_name
 * @property string $activity_description
 * @property string $category
 * @property string $centre_id
 * @property int|null $instructor_id
 * @property int|null $created_by
 * @property boolean $is_active
 * @property int|null $duration_weeks
 * @property int|null $sessions_per_week
 * @property int|null $session_duration_minutes
 * @property int|null $max_participants
 * @property string|null $learning_outcomes
 * @property string|null $activity_location
 * @property string|null $activity_status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @property-read Centre $centre
 * @property-read User $instructor
 * @property-read User $creator
 * @property-read \Illuminate\Database\Eloquent\Collection|ActivitySession[] $sessions
 * @property-read \Illuminate\Database\Eloquent\Collection|ActivityEnrollment[] $enrollments
 * @property-read \Illuminate\Database\Eloquent\Collection|ActivityEnrollment[] $activeEnrollments
 * @property-read \Illuminate\Database\Eloquent\Collection|Trainee[] $participants
 * @property-read \Illuminate\Database\Eloquent\Collection|ActivitySession[] $upcomingSessions
 * @property-read \Illuminate\Database\Eloquent\Collection|ActivitySession[] $completedSessions
 * @property-read \Illuminate\Database\Eloquent\Collection|ActivityTemplateApplication[] $templateApplications
 * @property-read \Illuminate\Database\Eloquent\Collection|ActivityPrerequisite[] $prerequisites
 * @property-read \Illuminate\Database\Eloquent\Collection|ActivityPrerequisite[] $requiredPrerequisites
 * @property-read \Illuminate\Database\Eloquent\Collection|ActivityPrerequisite[] $dependentActivities
 *
 * @property-read string $category_icon
 * @property-read string $category_color
 * @property-read string $formatted_duration
 * @property-read int $active_enrollments_count
 * @property-read int $total_sessions_count
 * @property-read float $completion_rate
 * @property-read string $overarching_type
 * @property-read array $academic_progression_stats
 */
class Activity extends Model
{
    use HasFactory;

    protected $table = 'activities';

    protected $fillable = [
        'activity_name',
        'activity_description',
        'category',
        'centre_id',
        'duration_weeks',
        'sessions_per_week',
        'session_duration_minutes',
        'max_participants',
        'learning_outcomes',
        'activity_location',
        'instructor_id',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'duration_weeks' => 'integer',
        'sessions_per_week' => 'integer',
        'session_duration_minutes' => 'integer',
        'max_participants' => 'integer'
    ];

    protected $appends = ['category_icon', 'category_color', 'formatted_duration'];

    /**
     * Get the centre that owns this activity
     */
    public function centre()
    {
        return $this->belongsTo(Centre::class, 'centre_id', 'centre_id');
    }

    /**
     * Get the instructor for this activity
     */
    public function instructor()
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    /**
     * Get the creator/instructor for this activity (alias for instructor)
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    // Category is now a direct enum field, no relationship needed

    /**
     * Get all sessions for this activity
     */
    public function sessions()
    {
        return $this->hasMany(ActivitySession::class);
    }

    /**
     * Get template applications for this activity
     */
    public function templateApplications()
    {
        return $this->hasMany(ActivityTemplateApplication::class);
    }

    /**
     * Get learning outcomes for this activity from activity_outcomes field
     */
    public function getLearningOutcomesAttribute()
    {
        if (empty($this->activity_outcomes)) {
            return collect();
        }
        
        $outcomes = is_string($this->activity_outcomes) 
            ? json_decode($this->activity_outcomes, true) 
            : $this->activity_outcomes;
            
        return collect($outcomes ?: []);
    }

    /**
     * Get active learning outcomes for this activity
     */
    public function getActiveLearningOutcomesAttribute()
    {
        return $this->getLearningOutcomesAttribute()->filter(function($outcome) {
            return is_array($outcome) ? ($outcome['is_active'] ?? true) : true;
        });
    }

    /**
     * Get learning outcomes - returns the collection from accessor method
     * Note: This is not a relationship, it's computed from learning_outcomes field
     */
    public function learningOutcomes()
    {
        return $this->getLearningOutcomesAttribute();
    }

    /**
     * Get learning outcomes count for compatibility
     */
    public function getLearningOutcomesCountAttribute()
    {
        return $this->learning_outcomes->count();
    }

    /**
     * Get prerequisites for this activity
     */
    public function prerequisites()
    {
        return $this->hasMany(ActivityPrerequisite::class);
    }

    /**
     * Get required prerequisites for this activity
     */
    public function requiredPrerequisites()
    {
        return $this->hasMany(ActivityPrerequisite::class)->where('is_required', true);
    }

    /**
     * Get activities that have this activity as a prerequisite
     */
    public function dependentActivities()
    {
        return $this->hasMany(ActivityPrerequisite::class, 'prerequisite_activity_id');
    }

    /**
     * Get all enrollments for this activity
     */
    public function enrollments()
    {
        return $this->hasMany(ActivityEnrollment::class);
    }

    /**
     * Get only active enrollments for this activity
     */
    public function activeEnrollments()
    {
        return $this->hasMany(ActivityEnrollment::class)
            ->where('enrollment_status', 'enrolled');
    }

    /**
     * Get enrolled trainees (participants) for this activity
     */
    public function participants()
    {
        return $this->belongsToMany(Trainee::class, 'activity_enrollments', 'activity_id', 'trainee_id')
                    ->wherePivot('enrollment_status', 'enrolled')
                    ->withPivot(['enrollment_date', 'enrollment_status', 'progress_percentage', 'attendance_count'])
                    ->withTimestamps();
    }

    /**
     * Get upcoming sessions
     */
    public function upcomingSessions()
    {
        return $this->hasMany(ActivitySession::class)
            ->where('session_date', '>=', now()->toDateString())
            ->where('status', 'scheduled')
            ->orderBy('session_date')
            ->orderBy('start_time');
    }

    /**
     * Get completed sessions
     */
    public function completedSessions()
    {
        return $this->hasMany(ActivitySession::class)
            ->where('status', 'completed')
            ->orderBy('session_date', 'desc');
    }

    /**
     * Get active enrollments count
     */
    public function getActiveEnrollmentsCountAttribute()
    {
        return $this->enrollments()
            ->where('enrollment_status', 'enrolled')
            ->distinct('trainee_id')
            ->count('trainee_id');
    }

    /**
     * Get total sessions count
     */
    public function getTotalSessionsCountAttribute()
    {
        return $this->sessions()->count();
    }

    /**
     * Get completion rate
     */
    public function getCompletionRateAttribute()
    {
        $totalSessions = $this->sessions()->count();
        if ($totalSessions === 0) return 0;
        
        $completedSessions = $this->sessions()->where('session_status', 'completed')->count();
        return round(($completedSessions / $totalSessions) * 100, 1);
    }

    /**
     * Check if all sessions are completed and activity should be marked as inactive
     */
    public function shouldBeCompleted()
    {
        $totalSessions = $this->sessions()->count();
        
        // If no sessions exist, activity should remain active
        if ($totalSessions === 0) {
            return false;
        }
        
        // Check if all sessions are completed
        $completedSessions = $this->sessions()->where('session_status', 'completed')->count();
        
        // Also check if there are any future scheduled sessions
        $futureScheduledSessions = $this->sessions()
            ->whereIn('session_status', ['scheduled', 'active'])
            ->where('session_date', '>', now()->toDateString())
            ->count();
        
        // Activity should be completed if all sessions are done AND no future sessions scheduled
        return $completedSessions === $totalSessions && $futureScheduledSessions === 0;
    }

    /**
     * Update activity status based on session completion
     */
    public function updateCompletionStatus()
    {
        if ($this->shouldBeCompleted() && $this->is_active) {
            $this->update(['is_active' => false]);
            
            \Log::info('Activity marked as completed', [
                'activity_id' => $this->id,
                'activity_name' => $this->activity_name
            ]);
            
            return true;
        }
        
        return false;
    }

    /**
     * Get the overarching activity type (rehabilitation, academic, creative_social)
     */
    public function getOverarchingTypeAttribute()
    {
        // Map categories to overarching types
        $categoryTypes = [
            'Autism Spectrum Support' => 'rehabilitation',
            'Hearing Impairment' => 'rehabilitation',
            'Visual Impairment' => 'rehabilitation',
            'Physical Disabilities' => 'rehabilitation',
            'Learning Support' => 'academic',
            'Speech Therapy' => 'rehabilitation'
        ];

        return $categoryTypes[$this->category] ?? 'general';
    }

    /**
     * Check if activity is rehabilitation type
     */
    public function isRehabilitation()
    {
        return $this->overarching_type === 'rehabilitation';
    }

    /**
     * Check if activity is academic type
     */
    public function isAcademic()
    {
        return $this->overarching_type === 'academic';
    }

    /**
     * Check if activity is creative/social type
     */
    public function isCreativeSocial()
    {
        return $this->overarching_type === 'creative_social';
    }

    /**
     * Scope for active activities
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for activities by category
     */
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope for activities by centre
     */
    public function scopeByCentre($query, $centreId)
    {
        return $query->where('centre_id', $centreId);
    }

    /**
     * Scope for activities with upcoming sessions
     */
    public function scopeWithUpcomingSessions($query)
    {
        return $query->whereHas('sessions', function($q) {
            $q->where('session_date', '>=', now()->toDateString())
              ->where('status', 'scheduled');
        });
    }

    /**
     * Check if activity has capacity for more participants
     */
    public function hasCapacity()
    {
        return $this->active_enrollments_count < $this->max_participants;
    }

    /**
     * Get category icon
     */
    public function getCategoryIconAttribute()
    {
        $icons = [
            'Autism Spectrum Support' => 'fas fa-brain',
            'Hearing Impairment' => 'fas fa-deaf',
            'Visual Impairment' => 'fas fa-low-vision',
            'Physical Disabilities' => 'fas fa-wheelchair',
            'Learning Support' => 'fas fa-graduation-cap',
            'Speech Therapy' => 'fas fa-comments'
        ];

        return $icons[$this->category] ?? 'fas fa-circle';
    }

    /**
     * Get category color
     */
    public function getCategoryColorAttribute()
    {
        $colors = [
            'Autism Spectrum Support' => '#9C27B0',
            'Hearing Impairment' => '#FF9800',
            'Visual Impairment' => '#00BCD4',
            'Physical Disabilities' => '#4CAF50',
            'Learning Support' => '#3F51B5',
            'Speech Therapy' => '#F44336'
        ];

        return $colors[$this->category] ?? '#6c757d';
    }

    /**
     * Format duration for display
     */
    public function getFormattedDurationAttribute()
    {
        $hours = floor($this->session_duration_minutes / 60);
        $minutes = $this->session_duration_minutes % 60;
        
        if ($hours > 0) {
            return $hours . 'h ' . ($minutes > 0 ? $minutes . 'm' : '');
        }
        
        return $minutes . 'm';
    }

    /**
     * Generate session schedule from template
     */
    public function generateSessionSchedule($template, $startDate = null, $customizations = [])
    {
        if (is_numeric($template)) {
            $template = ActivityScheduleTemplate::find($template);
        }
        
        if (!$template) {
            throw new \Exception('Template not found');
        }
        
        $startDate = $startDate ? \Carbon\Carbon::parse($startDate) : \Carbon\Carbon::now();
        
        // Generate sessions using the template
        $sessions = $template->generateSessions($this, $startDate, $customizations);
        
        if (!empty($sessions)) {
            // Insert sessions in bulk
            ActivitySession::insert($sessions);
            
            // Create template application record
            ActivityTemplateApplication::create([
                'activity_id' => $this->id,
                'template_id' => $template->id,
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $startDate->copy()->addWeeks($template->duration_weeks)->format('Y-m-d'),
                'customizations' => $customizations,
                'sessions_generated' => count($sessions)
            ]);
            
            return count($sessions);
        }
        
        return 0;
    }

    /**
     * Check if trainee meets prerequisites for this activity
     */
    public function checkPrerequisitesForTrainee($traineeId)
    {
        return ActivityPrerequisite::getActivityPrerequisiteStatus($this->id, $traineeId);
    }

    /**
     * Get academic progression statistics for this activity
     */
    public function getAcademicProgressionStatsAttribute()
    {
        $outcomes = $this->activeLearningOutcomes;
        $stats = [
            'total_outcomes' => $outcomes->count(),
            'enrolled_trainees' => $this->active_enrollments_count,
            'completion_stats' => [
                'not_started' => 0,
                'in_progress' => 0,
                'achieved' => 0,
                'mastered' => 0
            ],
            'average_progress' => 0
        ];

        if ($outcomes->isEmpty() || $stats['enrolled_trainees'] === 0) {
            return $stats;
        }

        $totalProgress = 0;
        $totalRecords = 0;

        foreach ($outcomes as $outcome) {
            $progressStats = $outcome->progress_statistics;
            $stats['completion_stats']['not_started'] += $progressStats['not_started'];
            $stats['completion_stats']['in_progress'] += $progressStats['in_progress'];
            $stats['completion_stats']['achieved'] += $progressStats['achieved'];
            
            // Count mastered separately
            $mastered = $outcome->competencyProgress()->where('current_level', 'Mastered')->count();
            $stats['completion_stats']['mastered'] += $mastered;
            $stats['completion_stats']['achieved'] -= $mastered; // Remove mastered from achieved count
            
            $totalProgress += $progressStats['completion_rate'] * $progressStats['total'];
            $totalRecords += $progressStats['total'];
        }

        $stats['average_progress'] = $totalRecords > 0 ? round($totalProgress / $totalRecords, 2) : 0;

        return $stats;
    }

    /**
     * Get trainee's overall progress in this activity
     */
    public function getTraineeProgress($traineeId)
    {
        $outcomes = $this->activeLearningOutcomes;
        
        if ($outcomes->isEmpty()) {
            return $this->getSessionBasedProgress($traineeId);
        }

        return $this->getOutcomeBasedProgress($traineeId, $outcomes);
    }

    private function getSessionBasedProgress($traineeId)
    {
        $totalSessions = $this->sessions()->count();
        
        if ($totalSessions === 0) {
            return [
                'type' => 'session_based',
                'overall_percentage' => 0,
                'status' => 'not_started',
                'sessions' => ['attended' => 0, 'total' => 0]
            ];
        }

        $attendedSessions = $this->sessions()
            ->whereHas('sessionEnrollments', function ($query) use ($traineeId) {
                $query->where('trainee_id', $traineeId)
                      ->where('status', 'attended');
            })
            ->count();

        $percentage = round(($attendedSessions / $totalSessions) * 100, 2);
        
        $status = match(true) {
            $percentage >= 90 => 'mastered',
            $percentage >= 70 => 'achieved',
            $percentage > 0 => 'in_progress',
            default => 'not_started'
        };

        return [
            'type' => 'session_based',
            'overall_percentage' => $percentage,
            'status' => $status,
            'sessions' => [
                'attended' => $attendedSessions,
                'total' => $totalSessions
            ]
        ];
    }

    private function getOutcomeBasedProgress($traineeId, $outcomes)
    {
        $totalOutcomes = $outcomes->count();
        $totalProgress = 0;
        $achievedOutcomes = 0;
        $masteredOutcomes = 0;
        $inProgressOutcomes = 0;

        $outcomeDetails = [];

        foreach ($outcomes as $outcome) {
            $progress = $outcome->getTraineeProgress($traineeId);
            
            if ($progress) {
                $totalProgress += $progress->progress_percentage;
                
                if ($progress->current_level === 'Mastered') {
                    $masteredOutcomes++;
                    $achievedOutcomes++;
                } elseif ($progress->isAchieved()) {
                    $achievedOutcomes++;
                } elseif ($progress->isInProgress()) {
                    $inProgressOutcomes++;
                }

                $outcomeDetails[] = [
                    'outcome_id' => $outcome->id,
                    'title' => $outcome->outcome_title,
                    'level' => $progress->current_level,
                    'percentage' => $progress->progress_percentage
                ];
            } else {
                $outcomeDetails[] = [
                    'outcome_id' => $outcome->id,
                    'title' => $outcome->outcome_title,
                    'level' => 'Not Started',
                    'percentage' => 0
                ];
            }
        }

        $averageProgress = $totalOutcomes > 0 ? round($totalProgress / $totalOutcomes, 2) : 0;
        $completionRate = $totalOutcomes > 0 ? round(($achievedOutcomes / $totalOutcomes) * 100, 2) : 0;

        $status = match(true) {
            $masteredOutcomes === $totalOutcomes => 'mastered',
            $achievedOutcomes === $totalOutcomes => 'achieved',
            $inProgressOutcomes > 0 || $achievedOutcomes > 0 => 'in_progress',
            default => 'not_started'
        };

        return [
            'type' => 'outcome_based',
            'overall_percentage' => $averageProgress,
            'completion_rate' => $completionRate,
            'status' => $status,
            'outcomes' => [
                'total' => $totalOutcomes,
                'achieved' => $achievedOutcomes,
                'mastered' => $masteredOutcomes,
                'in_progress' => $inProgressOutcomes,
                'not_started' => $totalOutcomes - $achievedOutcomes - $inProgressOutcomes
            ],
            'outcome_details' => $outcomeDetails
        ];
    }

    // Category is now a direct enum field, no need for getCategoryAttribute method
}