<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    use HasFactory;

    protected $fillable = [
        'activity_name',
        'activity_code',
        'description',
        'category',
        'subject_category',
        'curriculum_level',
        'objectives',
        'learning_outcomes',
        'assessment_criteria',
        'materials_needed',
        'age_group',
        'difficulty_level',
        'standard_duration_minutes',
        'minimum_duration_minutes',
        'maximum_duration_minutes',
        'requires_special_accommodation',
        'is_active',
        'created_by'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'requires_special_accommodation' => 'boolean',
        'learning_outcomes' => 'array',
        'assessment_criteria' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Get the user who created the activity
     */
    public function creator()
    {
        return $this->belongsTo(Users::class, 'created_by');
    }

    /**
     * Get all sessions for the activity
     */
    public function sessions()
    {
        return $this->hasMany(ActivitySession::class);
    }

    /**
     * Get active sessions
     */
    public function activeSessions()
    {
        return $this->hasMany(ActivitySession::class)->where('status', 'active');
    }

    /**
     * Scope for active activities
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for rehabilitation activities
     */
    public function scopeRehabilitation($query)
    {
        return $query->whereIn('category', [
            'Physical Therapy',
            'Occupational Therapy',
            'Speech & Language Therapy',
            'Sensory Integration',
            'Social Skills Training',
            'Daily Living Skills'
        ]);
    }

    /**
     * Scope for academic activities
     */
    public function scopeAcademic($query)
    {
        return $query->whereIn('category', [
            'Basic Mathematics',
            'Language & Literacy',
            'Science Exploration',
            'Art & Creativity',
            'Music Therapy',
            'Computer Skills'
        ]);
    }

    /**
     * Scope for Malaysian curriculum subjects
     */
    public function scopeMalaysianCurriculum($query)
    {
        return $query->whereIn('subject_category', [
            'bahasa_malaysia',
            'english_language', 
            'arabic_language',
            'mathematics',
            'science',
            'life_skills'
        ]);
    }

    /**
     * Scope for therapy activities
     */
    public function scopeTherapy($query)
    {
        return $query->whereIn('subject_category', [
            'physical_therapy',
            'occupational_therapy',
            'speech_therapy',
            'social_skills'
        ]);
    }

    /**
     * Get formatted subject category name
     */
    public function getFormattedSubjectCategoryAttribute()
    {
        $categories = [
            'bahasa_malaysia' => 'Bahasa Malaysia',
            'english_language' => 'English Language',
            'arabic_language' => 'Arabic Language',
            'mathematics' => 'Mathematics',
            'science' => 'Science',
            'life_skills' => 'Life Skills',
            'physical_therapy' => 'Physical Therapy',
            'occupational_therapy' => 'Occupational Therapy',
            'speech_therapy' => 'Speech Therapy',
            'social_skills' => 'Social Skills Training'
        ];

        return $categories[$this->subject_category] ?? $this->subject_category;
    }

    /**
     * Get formatted curriculum level
     */
    public function getFormattedCurriculumLevelAttribute()
    {
        $levels = [
            'pre_foundation' => 'Pre-Foundation (Pre-school)',
            'foundation' => 'Foundation (Primary 1-3)',
            'basic' => 'Basic (Primary 4-6)',
            'adaptive' => 'Adaptive (Special Needs)'
        ];

        return $levels[$this->curriculum_level] ?? $this->curriculum_level;
    }

    /**
     * Get recommended duration for a specific trainee
     */
    public function getRecommendedDurationForTrainee($traineeId)
    {
        // Check if trainee has specific adaptations
        $adaptation = \DB::table('trainee_subject_adaptations')
            ->where('trainee_id', $traineeId)
            ->where('subject_category', $this->subject_category)
            ->first();

        if ($adaptation) {
            return $adaptation->adapted_duration_minutes;
        }

        // Fall back to disability-based accommodations
        $trainee = \App\Models\Trainee::find($traineeId);
        if ($trainee && $trainee->trainee_condition) {
            $accommodation = \DB::table('disability_accommodations')
                ->where('disability_type', $trainee->trainee_condition)
                ->where('subject_category', $this->subject_category)
                ->first();

            if ($accommodation) {
                return $accommodation->recommended_duration_minutes;
            }
        }

        // Default to standard duration
        return $this->standard_duration_minutes ?? 45;
    }

    /**
     * Check if activity requires special accommodation
     */
    public function needsAccommodationForTrainee($traineeId)
    {
        $trainee = \App\Models\Trainee::find($traineeId);
        
        if (!$trainee || !$trainee->trainee_condition) {
            return false;
        }

        // Check if there are specific accommodations for this disability type and subject
        $hasAccommodation = \DB::table('disability_accommodations')
            ->where('disability_type', $trainee->trainee_condition)
            ->where('subject_category', $this->subject_category)
            ->exists();

        return $hasAccommodation || $this->requires_special_accommodation;
    }

    /**
     * Get subject category badge class for UI
     */
    public function getSubjectBadgeClassAttribute()
    {
        $badgeClasses = [
            'bahasa_malaysia' => 'primary',
            'english_language' => 'info',
            'arabic_language' => 'secondary',
            'mathematics' => 'success',
            'science' => 'warning',
            'life_skills' => 'dark',
            'physical_therapy' => 'danger',
            'occupational_therapy' => 'primary',
            'speech_therapy' => 'info',
            'social_skills' => 'secondary'
        ];

        return $badgeClasses[$this->subject_category] ?? 'secondary';
    }
}