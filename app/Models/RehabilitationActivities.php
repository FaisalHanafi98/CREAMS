<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RehabilitationActivity extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'rehabilitation_activities';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'category',
        'short_description',
        'full_description',
        'difficulty_level',
        'age_range',
        'activity_type',
        'duration',
        'max_participants',
        'materials_required',
        'staff_requirements',
        'goals',
        'instructions',
        'lower_adaptations',
        'higher_adaptations',
        'progress_metrics',
        'notes',
        'status',
        'created_by',
        'updated_by'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the difficulty level badge color
     *
     * @return string
     */
    public function getDifficultyColorAttribute()
    {
        $colors = [
            'easy' => 'success',
            'medium' => 'warning',
            'hard' => 'danger'
        ];
        
        return $colors[$this->difficulty_level] ?? 'secondary';
    }

    /**
     * Get the category badge color
     *
     * @return string
     */
    public function getCategoryColorAttribute()
    {
        $colors = [
            'autism' => 'primary',
            'physical' => 'success',
            'speech' => 'info',
            'visual' => 'warning',
            'hearing' => 'danger',
            'learning' => 'secondary',
            'down' => 'dark',
            'cerebral' => 'light',
            'intellectual' => 'primary',
            'multiple' => 'dark',
            'all' => 'secondary'
        ];
        
        return $colors[$this->category] ?? 'secondary';
    }

    /**
     * Get category display name
     * 
     * @return string
     */
    public function getCategoryNameAttribute()
    {
        $categories = [
            'autism' => 'Autism Spectrum Disorder',
            'physical' => 'Physical Disability',
            'speech' => 'Speech & Language',
            'visual' => 'Visual Impairment',
            'hearing' => 'Hearing Impairment',
            'learning' => 'Learning Disability',
            'down' => 'Down Syndrome',
            'cerebral' => 'Cerebral Palsy',
            'intellectual' => 'Intellectual Disability',
            'multiple' => 'Multiple Disabilities',
            'all' => 'All Disabilities'
        ];
        
        return $categories[$this->category] ?? $this->category;
    }

    /**
     * Get the user who created this activity
     */
    public function creator()
    {
        return $this->belongsTo(Users::class, 'created_by');
    }

    /**
     * Get the user who last updated this activity
     */
    public function updater()
    {
        return $this->belongsTo(Users::class, 'updated_by');
    }

    /**
     * Get the objectives for this activity
     */
    public function objectives()
    {
        return $this->hasMany(RehabilitationObjective::class, 'activity_id');
    }

    /**
     * Get the resources for this activity
     */
    public function resources()
    {
        return $this->hasMany(RehabilitationResource::class, 'activity_id');
    }

    /**
     * Get the implementation steps for this activity
     */
    public function steps()
    {
        return $this->hasMany(RehabilitationStep::class, 'activity_id')
                    ->orderBy('order', 'asc');
    }

    /**
     * Get the milestones for this activity
     */
    public function milestones()
    {
        return $this->hasMany(RehabilitationMilestone::class, 'activity_id')
                    ->orderBy('order', 'asc');
    }

    /**
     * Get the schedules for this activity
     */
    public function schedules()
    {
        return $this->hasMany(RehabilitationSchedule::class, 'activity_id');
    }

    /**
     * Get the trainee activities based on this template
     */
    public function traineeActivities()
    {
        return $this->hasMany(Activities::class, 'rehab_activity_id');
    }

    /**
     * Get activities by category
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $category
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByCategory($query, $category)
    {
        if ($category === 'all') {
            return $query;
        }
        
        return $query->where('category', $category);
    }

    /**
     * Get activities by difficulty level
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $level
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByDifficulty($query, $level)
    {
        return $query->where('difficulty_level', $level);
    }

    /**
     * Get activities suitable for a specific age
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $age
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForAge($query, $age)
    {
        return $query->where(function($q) use ($age) {
            // Parse age ranges like '6-12', '13-18', etc.
            $q->whereRaw("SUBSTRING_INDEX(age_range, '-', 1) <= ?", [$age])
              ->whereRaw("SUBSTRING_INDEX(age_range, '-', -1) >= ?", [$age]);
        })->orWhere('age_range', 'all');
    }

    /**
     * Get published activities
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    /**
     * Get draft activities
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }
}