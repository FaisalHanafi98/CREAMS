<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    use HasFactory;

    protected $table = 'activities';

    protected $fillable = [
        'activity_id',
        'activity_name',
        'activity_description',
        'activity_type',
        'activity_date',
        'start_date',
        'end_date',
        'sessions_per_week',
        'activity_period',
        'pass_threshold',
        'is_active',
        'activity_start_time',
        'activity_end_time',
        'activity_location',
        'max_participants',
        'current_participants',
        'activity_goals',
        'activity_outcomes',
        'activity_image',
        'required_resources',
        'activity_status',
        'centre_id',
        'category_id',
        'created_by',
        'times_conducted',
        'instructor_id'
    ];

    protected $casts = [
        'required_resources' => 'array',
        'activity_goals' => 'array',
        'activity_outcomes' => 'array',
        'activity_date' => 'date',
        'start_date' => 'date',
        'end_date' => 'date',
        'pass_threshold' => 'decimal:2',
        'is_active' => 'boolean',
        'activity_start_time' => 'datetime:H:i',
        'activity_end_time' => 'datetime:H:i'
    ];

    protected $appends = ['category_icon', 'category_color', 'formatted_duration'];

    /**
     * Get the centre that owns this activity
     */
    public function centre()
    {
        return $this->belongsTo(Centre::class, 'centre_id');
    }

    /**
     * Get the user who created this activity
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the instructor for this activity (alias for creator)
     */
    public function instructor()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the category that owns this activity
     */
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

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
     * Get learning outcomes for this activity
     */
    public function learningOutcomes()
    {
        return $this->hasMany(LearningOutcome::class);
    }

    /**
     * Get active learning outcomes for this activity
     */
    public function activeLearningOutcomes()
    {
        return $this->hasMany(LearningOutcome::class)->where('is_active', true)->orderBy('display_order');
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
        
        $completedSessions = $this->sessions()->where('status', 'completed')->count();
        return round(($completedSessions / $totalSessions) * 100, 1);
    }

    /**
     * Get the overarching activity type (rehabilitation, academic, creative_social)
     */
    public function getOverarchingTypeAttribute()
    {
        return $this->category?->category_type ?? 'general';
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
        return $query->where('activity_status', 'scheduled');
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
            'Physical Therapy' => 'fas fa-running',
            'Occupational Therapy' => 'fas fa-hands-helping',
            'Speech Therapy' => 'fas fa-comments',
            'Behavioral Therapy' => 'fas fa-brain',
            'Sensory Integration' => 'fas fa-hand-paper',
            'Mathematics' => 'fas fa-calculator',
            'Literacy' => 'fas fa-book',
            'Science' => 'fas fa-flask',
            'Computer Skills' => 'fas fa-laptop',
            'Art & Creativity' => 'fas fa-palette',
            'Music Therapy' => 'fas fa-music',
            'Social Skills' => 'fas fa-users',
            'Life Skills' => 'fas fa-home',
            'Vocational Training' => 'fas fa-briefcase'
        ];

        return $icons[$this->activity_type] ?? 'fas fa-circle';
    }

    /**
     * Get category color
     */
    public function getCategoryColorAttribute()
    {
        $colors = [
            'Physical Therapy' => '#4CAF50',
            'Occupational Therapy' => '#2196F3',
            'Speech Therapy' => '#FF9800',
            'Behavioral Therapy' => '#9C27B0',
            'Sensory Integration' => '#00BCD4',
            'Mathematics' => '#F44336',
            'Literacy' => '#3F51B5',
            'Science' => '#009688',
            'Computer Skills' => '#607D8B',
            'Art & Creativity' => '#E91E63',
            'Music Therapy' => '#673AB7',
            'Social Skills' => '#795548',
            'Life Skills' => '#FF5722',
            'Vocational Training' => '#FFC107'
        ];

        return $colors[$this->activity_type] ?? '#6c757d';
    }

    /**
     * Format duration for display
     */
    public function getFormattedDurationAttribute()
    {
        $hours = floor($this->duration_minutes / 60);
        $minutes = $this->duration_minutes % 60;
        
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

    /**
     * Get therapy category based on activity name
     */
    public function getCategoryAttribute()
    {
        $name = strtolower($this->activity_name ?? '');
        
        if (strpos($name, 'speech') !== false || strpos($name, 'pertuturan') !== false) {
            return 'Speech Therapy';
        } elseif (strpos($name, 'occupational') !== false || strpos($name, 'okupasi') !== false) {
            return 'Occupational Therapy';
        } elseif (strpos($name, 'physiotherapy') !== false || strpos($name, 'fisioterapi') !== false) {
            return 'Physical Therapy';
        } elseif (strpos($name, 'behavioral') !== false || strpos($name, 'tingkah laku') !== false) {
            return 'Behavioral Therapy';
        } elseif (strpos($name, 'sensory') !== false || strpos($name, 'sensori') !== false) {
            return 'Sensory Integration';
        } elseif (strpos($name, 'social') !== false || strpos($name, 'sosial') !== false) {
            return 'Social Skills';
        } elseif (strpos($name, 'life') !== false || strpos($name, 'hidup') !== false) {
            return 'Life Skills';
        } elseif (strpos($name, 'art') !== false || strpos($name, 'seni') !== false) {
            return 'Art & Creativity';
        } elseif (strpos($name, 'music') !== false || strpos($name, 'muzik') !== false) {
            return 'Music Therapy';
        } elseif (strpos($name, 'academic') !== false || strpos($name, 'akademik') !== false) {
            return 'Academic Support';
        } elseif (strpos($name, 'literacy') !== false || strpos($name, 'literasi') !== false) {
            return 'Literacy';
        } elseif (strpos($name, 'computer') !== false || strpos($name, 'komputer') !== false) {
            return 'Computer Skills';
        } elseif (strpos($name, 'mathematics') !== false || strpos($name, 'matematik') !== false) {
            return 'Mathematics';
        } elseif (strpos($name, 'vocational') !== false || strpos($name, 'vokasional') !== false) {
            return 'Vocational Training';
        } else {
            return 'Other';
        }
    }
}