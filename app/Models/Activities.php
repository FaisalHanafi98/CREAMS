<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Activities extends Model
{
    use HasFactory;

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'trainee_id',
        'activity_name',
        'activity_type',
        'activity_date',
        'activity_description',
        'activity_goals',
        'activity_outcomes',
        'created_by',
        'updated_by',
        'rehab_activity_id',  // Retained from first model
        'status'  // Added for tracking activity status
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'activity_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the trainee this activity belongs to.
     */
    public function trainee()
    {
        return $this->belongsTo(Trainees::class, 'trainee_id');
    }

    /**
     * Get the user who created this activity.
     */
    public function creator()
    {
        return $this->belongsTo(Users::class, 'created_by');
    }

    /**
     * Get the user who last updated this activity.
     */
    public function updater()
    {
        return $this->belongsTo(Users::class, 'updated_by');
    }

    /**
     * Get the rehabilitation activity template this activity is based on.
     */
    public function rehabilitationActivity()
    {
        return $this->belongsTo(RehabilitationActivity::class, 'rehab_activity_id');
    }

    /**
     * Check if this activity is a rehabilitation activity.
     *
     * @return bool
     */
    public function isRehabActivity()
    {
        return !is_null($this->rehab_activity_id);
    }

    /**
     * Get the appropriate badge class for the activity type.
     *
     * @return string
     */
    public function getTypeBadgeClassAttribute()
    {
        $typeMap = [
            'Educational' => 'primary',
            'Therapy' => 'info',
            'Physical' => 'success',
            'Social' => 'warning',
            'Assessment' => 'secondary',
            'Rehabilitation' => 'danger',
            'Progress' => 'dark',
            'Custom' => 'light'
        ];
        
        return $typeMap[$this->activity_type] ?? 'secondary';
    }

    /**
     * Get the status color badge.
     *
     * @return string
     */
    public function getStatusColorAttribute()
    {
        $statusMap = [
            'completed' => 'success',
            'ongoing' => 'primary',
            'upcoming' => 'info',
            'cancelled' => 'danger',
            'pending' => 'warning'
        ];
        
        return $statusMap[$this->status] ?? 'secondary';
    }

    /**
     * Scope a query to only include activities for a specific trainee.
     */
    public function scopeForTrainee($query, $traineeId)
    {
        return $query->where('trainee_id', $traineeId);
    }

    /**
     * Scope a query to only include activities of a specific type.
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('activity_type', $type);
    }

    /**
     * Scope a query to only include rehabilitation-based activities.
     */
    public function scopeRehabilitation($query)
    {
        return $query->whereNotNull('rehab_activity_id');
    }

    /**
     * Scope a query to only include activities within a date range.
     */
    public function scopeInDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('activity_date', [$startDate, $endDate]);
    }

    /**
     * Scope a query to only include recent activities.
     */
    public function scopeRecent($query, $days = 30)
    {
        return $query->where('activity_date', '>=', now()->subDays($days));
    }

    /**
     * Scope a query to filter activities by status.
     */
    public function scopeWithStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Generate a summary of activities.
     *
     * @return array
     */
    public function getSummaryAttribute()
    {
        return [
            'id' => $this->id,
            'name' => $this->activity_name,
            'type' => $this->activity_type,
            'date' => $this->activity_date ? $this->activity_date->format('M d, Y') : null,
            'status' => $this->status,
            'status_color' => $this->status_color
        ];
    }

    /**
     * Check if the activity is upcoming.
     *
     * @return bool
     */
    public function getIsUpcomingAttribute()
    {
        return $this->activity_date && $this->activity_date->isFuture();
    }

    /**
     * Check if the activity is overdue.
     *
     * @return bool
     */
    public function getIsOverdueAttribute()
    {
        return $this->activity_date && $this->activity_date->isPast() && $this->status !== 'completed';
    }
}