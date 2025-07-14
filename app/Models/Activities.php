<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Activities extends Model
{
    use HasFactory;

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true; // Changed from protected to public

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'trainee_id',
        'activity_name',
        'activity_type',
        'activity_description',
        'activity_date',
        'activity_goals',
        'activity_outcomes',
        'created_by',
        'updated_by',
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
     * Get the trainee associated with this activity.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function trainee()
    {
        return $this->belongsTo(Trainees::class, 'trainee_id');
    }

    /**
     * Get the user who created this activity.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function creator()
    {
        return $this->belongsTo(Users::class, 'created_by');
    }

    /**
     * Get the user who last updated this activity.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function updater()
    {
        return $this->belongsTo(Users::class, 'updated_by');
    }

    /**
     * Scope a query to only include activities for a specific trainee.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $traineeId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForTrainee($query, $traineeId)
    {
        return $query->where('trainee_id', $traineeId);
    }

    /**
     * Scope a query to only include activities of a specific type.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $type
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('activity_type', $type);
    }

    /**
     * Scope a query to only include activities within a date range.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $startDate
     * @param string $endDate
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeInDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('activity_date', [$startDate, $endDate]);
    }

    /**
     * Scope a query to only include recent activities.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $days
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeRecent($query, $days = 30)
    {
        return $query->where('activity_date', '>=', now()->subDays($days));
    }
}