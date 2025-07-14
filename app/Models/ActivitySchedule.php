<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ActivitySchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'activity_id',
        'day_of_week',
        'start_time',
        'end_time',
        'location',
        'room',
        'recurring',
        'start_date',
        'end_date',
        'status',
        'notes',
        'max_capacity'
    ];

    protected $casts = [
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'start_date' => 'date',
        'end_date' => 'date'
    ];

    // Relationships
    public function activity()
    {
        return $this->belongsTo(Activity::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeForDay($query, $day)
    {
        return $query->where('day_of_week', $day);
    }

    public function scopeForWeek($query)
    {
        return $query->orderBy('day_of_week')->orderBy('start_time');
    }

    public function scopeToday($query)
    {
        $today = Carbon::now()->format('l'); // Full day name (Monday, Tuesday, etc.)
        return $query->where('day_of_week', $today);
    }

    // Accessors
    public function getFormattedTimeAttribute()
    {
        return $this->start_time->format('H:i') . ' - ' . $this->end_time->format('H:i');
    }

    public function getDurationMinutesAttribute()
    {
        $start = Carbon::parse($this->start_time);
        $end = Carbon::parse($this->end_time);
        return $end->diffInMinutes($start);
    }

    public function getDisplayLocationAttribute()
    {
        $location = $this->location;
        if ($this->room) {
            $location .= ' - ' . $this->room;
        }
        return $location;
    }

    // Helper methods
    public function isToday()
    {
        $today = Carbon::now()->format('l');
        return $this->day_of_week === $today;
    }

    public function isActive()
    {
        return $this->status === 'active';
    }

    public function getMaxCapacity()
    {
        return $this->max_capacity ?? $this->activity->max_participants;
    }

    public function getCurrentEnrollmentCount()
    {
        return $this->activity->activeEnrollments()->count();
    }

    public function hasAvailableSpots()
    {
        return $this->getCurrentEnrollmentCount() < $this->getMaxCapacity();
    }
}