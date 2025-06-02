<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Rehabilitation Objective Model
 * 
 * Stores learning objectives for rehabilitation activities
 */
class RehabilitationObjective extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'activity_id',
        'description',
        'order'
    ];

    /**
     * Get the activity that owns this objective
     */
    public function activity()
    {
        return $this->belongsTo(RehabilitationActivity::class, 'activity_id');
    }
}

/**
 * Rehabilitation Resource Model
 * 
 * Stores required resources for rehabilitation activities
 */
class RehabilitationResource extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'activity_id',
        'name',
        'type',
        'optional'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'optional' => 'boolean',
    ];

    /**
     * Get the activity that owns this resource
     */
    public function activity()
    {
        return $this->belongsTo(RehabilitationActivity::class, 'activity_id');
    }
}

/**
 * Rehabilitation Step Model
 * 
 * Stores implementation steps for rehabilitation activities
 */
class RehabilitationStep extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'activity_id',
        'title',
        'description',
        'order',
        'duration'
    ];

    /**
     * Get the activity that owns this step
     */
    public function activity()
    {
        return $this->belongsTo(RehabilitationActivity::class, 'activity_id');
    }
}

/**
 * Rehabilitation Milestone Model
 * 
 * Stores progress milestones for rehabilitation activities
 */
class RehabilitationMilestone extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'activity_id',
        'description',
        'order'
    ];

    /**
     * Get the activity that owns this milestone
     */
    public function activity()
    {
        return $this->belongsTo(RehabilitationActivity::class, 'activity_id');
    }
}

/**
 * Rehabilitation Schedule Model
 * 
 * Stores scheduling information for rehabilitation activities
 */
class RehabilitationSchedule extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'activity_id',
        'teacher_id',
        'centre_id',
        'start_time',
        'end_time',
        'status',
        'max_participants',
        'notes',
        'created_by'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime'
    ];

    /**
     * Get the activity associated with this schedule
     */
    public function activity()
    {
        return $this->belongsTo(RehabilitationActivity::class, 'activity_id');
    }

    /**
     * Get the teacher responsible for this schedule
     */
    public function teacher()
    {
        return $this->belongsTo(Users::class, 'teacher_id');
    }

    /**
     * Get the centre where this activity is scheduled
     */
    public function centre()
    {
        return $this->belongsTo(Centres::class, 'centre_id', 'centre_id');
    }

    /**
     * Get the participants for this scheduled activity
     */
    public function participants()
    {
        return $this->hasMany(RehabilitationParticipant::class, 'schedule_id');
    }

    /**
     * Get the trainees participating in this scheduled activity
     */
    public function trainees()
    {
        return $this->belongsToMany(Trainees::class, 
                                   'rehabilitation_participants',
                                   'schedule_id',
                                   'trainee_id')
                    ->withPivot('attendance_status', 'progress_rating', 'notes')
                    ->withTimestamps();
    }

    /**
     * Get the user who created this schedule
     */
    public function creator()
    {
        return $this->belongsTo(Users::class, 'created_by');
    }
}

/**
 * Rehabilitation Participant Model
 * 
 * Stores trainee participation in scheduled rehabilitation activities
 */
class RehabilitationParticipant extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'schedule_id',
        'trainee_id',
        'attendance_status',
        'progress_rating',
        'notes',
        'marked_by'
    ];

    /**
     * Get the schedule this participation is for
     */
    public function schedule()
    {
        return $this->belongsTo(RehabilitationSchedule::class, 'schedule_id');
    }

    /**
     * Get the trainee who is participating
     */
    public function trainee()
    {
        return $this->belongsTo(Trainees::class, 'trainee_id');
    }

    /**
     * Get the user who marked this participant's attendance/progress
     */
    public function marker()
    {
        return $this->belongsTo(Users::class, 'marked_by');
    }
}