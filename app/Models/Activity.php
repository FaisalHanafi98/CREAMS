<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Activity extends Model
{
    use HasFactory;

    protected $fillable = [
        'activity_id',
        'activity_name',
        'activity_description',
        'activity_type',
        'activity_date',
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
        'instructor_id'
    ];

    protected $casts = [
        'activity_date' => 'date',
        'activity_start_time' => 'datetime:H:i',
        'activity_end_time' => 'datetime:H:i',
        'required_resources' => 'array',
        'max_participants' => 'integer',
        'current_participants' => 'integer'
    ];

    protected $appends = ['is_currently_running', 'duration_minutes'];

    // Relationships
    public function centre()
    {
        return $this->belongsTo(Centres::class, 'centre_id', 'centre_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function creator()
    {
        return $this->belongsTo(Users::class, 'created_by');
    }

    public function instructor()
    {
        return $this->belongsTo(Users::class, 'instructor_id');
    }

    public function enrollments()
    {
        return $this->hasMany(ActivityEnrollment::class, 'activity_id');
    }

    public function sessions()
    {
        return $this->hasMany(ActivitySession::class, 'activity_id');
    }

    public function trainees()
    {
        return $this->belongsToMany(Trainee::class, 'activity_enrollments', 'activity_id', 'trainee_id')
                    ->withPivot(['enrollment_date', 'status', 'notes'])
                    ->withTimestamps();
    }

    // Accessors
    public function getIsCurrentlyRunningAttribute()
    {
        $now = Carbon::now();
        $startTime = Carbon::parse($this->activity_date . ' ' . $this->activity_start_time);
        $endTime = Carbon::parse($this->activity_date . ' ' . $this->activity_end_time);
        
        return $now->between($startTime, $endTime);
    }

    public function getDurationMinutesAttribute()
    {
        $start = Carbon::parse($this->activity_start_time);
        $end = Carbon::parse($this->activity_end_time);
        return $end->diffInMinutes($start);
    }

    public function getFormattedTimeAttribute()
    {
        return Carbon::parse($this->activity_start_time)->format('H:i') . ' - ' . 
               Carbon::parse($this->activity_end_time)->format('H:i');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('activity_status', 'scheduled');
    }

    public function scopeOngoing($query)
    {
        return $query->where('activity_status', 'ongoing');
    }

    public function scopeToday($query)
    {
        return $query->whereDate('activity_date', Carbon::today());
    }

    public function scopeCurrentlyRunning($query)
    {
        $now = Carbon::now();
        return $query->where(function ($q) use ($now) {
            $q->where('activity_status', 'ongoing')
              ->orWhere(function ($subQ) use ($now) {
                  $subQ->where('activity_status', 'scheduled')
                       ->whereDate('activity_date', $now->toDateString())
                       ->whereTime('activity_start_time', '<=', $now->toTimeString())
                       ->whereTime('activity_end_time', '>=', $now->toTimeString());
              });
        });
    }

    public function scopeForCentre($query, $centreId)
    {
        return $query->where('centre_id', $centreId);
    }

    public function scopeForInstructor($query, $instructorId)
    {
        return $query->where('instructor_id', $instructorId);
    }

    // Methods
    public function hasTimeConflictWith($startTime, $endTime, $date = null)
    {
        $date = $date ?: $this->activity_date;
        
        if ($date != $this->activity_date) {
            return false;
        }

        $activityStart = Carbon::parse($this->activity_start_time);
        $activityEnd = Carbon::parse($this->activity_end_time);
        $newStart = Carbon::parse($startTime);
        $newEnd = Carbon::parse($endTime);

        return $newStart->lt($activityEnd) && $newEnd->gt($activityStart);
    }

    public static function checkTimeConflicts($instructorId, $startTime, $endTime, $date, $excludeActivityId = null)
    {
        $query = static::where('instructor_id', $instructorId)
                      ->where('activity_date', $date)
                      ->where('activity_status', '!=', 'cancelled');

        if ($excludeActivityId) {
            $query->where('id', '!=', $excludeActivityId);
        }

        $existingActivities = $query->get();
        $conflicts = [];

        foreach ($existingActivities as $activity) {
            if ($activity->hasTimeConflictWith($startTime, $endTime, $date)) {
                $conflicts[] = $activity;
            }
        }

        return $conflicts;
    }

    public static function checkTraineeConflicts($traineeId, $startTime, $endTime, $date, $excludeActivityId = null)
    {
        $query = static::whereHas('enrollments', function ($q) use ($traineeId) {
                      $q->where('trainee_id', $traineeId)
                        ->where('enrollment_status', 'enrolled');
                  })
                  ->where('activity_date', $date)
                  ->where('activity_status', '!=', 'cancelled');

        if ($excludeActivityId) {
            $query->where('id', '!=', $excludeActivityId);
        }

        $existingActivities = $query->get();
        $conflicts = [];

        foreach ($existingActivities as $activity) {
            if ($activity->hasTimeConflictWith($startTime, $endTime, $date)) {
                $conflicts[] = $activity;
            }
        }

        return $conflicts;
    }

    public function canEnrollTrainee($traineeId)
    {
        return $this->current_participants < $this->max_participants &&
               $this->activity_status === 'scheduled' &&
               !$this->enrollments()->where('trainee_id', $traineeId)->exists();
    }

    public function enrollTrainee($traineeId, $enrolledBy)
    {
        if (!$this->canEnrollTrainee($traineeId)) {
            return false;
        }

        $enrollment = ActivityEnrollment::create([
            'activity_id' => $this->id,
            'trainee_id' => $traineeId,
            'enrollment_date' => Carbon::now(),
            'enrollment_status' => 'enrolled',
            'enrolled_by' => $enrolledBy
        ]);

        $this->increment('current_participants');
        
        return $enrollment;
    }

    public function markAsOngoing()
    {
        $this->update(['activity_status' => 'ongoing']);
    }

    public function markAsCompleted()
    {
        $this->update(['activity_status' => 'completed']);
    }

    public function markAsCancelled()
    {
        $this->update(['activity_status' => 'cancelled']);
    }
}