<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Models\ActivitySession;
use App\Models\Activity;
use Carbon\Carbon;

class ActivityTimeBufferRule implements Rule
{
    protected $location;
    protected $date;
    protected $endTime;
    protected $excludeActivityId;
    protected $bufferMinutes;

    public function __construct($location, $date, $endTime, $excludeActivityId = null, $bufferMinutes = 15)
    {
        $this->location = $location;
        $this->date = $date;
        $this->endTime = $endTime;
        $this->excludeActivityId = $excludeActivityId;
        $this->bufferMinutes = $bufferMinutes;
    }

    public function passes($attribute, $value)
    {
        if (!$value || !$this->location || !$this->date || !$this->endTime) {
            return true;
        }

        try {
            $startTime = Carbon::parse($value);
            $endTime = Carbon::parse($this->endTime);
            $date = Carbon::parse($this->date);
            
            // Create buffer zones
            $bufferStart = $startTime->copy()->subMinutes($this->bufferMinutes);
            $bufferEnd = $endTime->copy()->addMinutes($this->bufferMinutes);

            // Check for conflicts in activity_sessions table
            $sessionConflicts = ActivitySession::where('location', $this->location)
                ->where('session_date', $date->format('Y-m-d'))
                ->when($this->excludeActivityId, function($query) {
                    $query->where('activity_id', '!=', $this->excludeActivityId);
                })
                ->where(function($query) use ($bufferStart, $bufferEnd) {
                    $query->where(function($q) use ($bufferStart, $bufferEnd) {
                        // Check if existing session overlaps with our buffer zone
                        $q->whereBetween('start_time', [$bufferStart->format('H:i'), $bufferEnd->format('H:i')])
                          ->orWhereBetween('end_time', [$bufferStart->format('H:i'), $bufferEnd->format('H:i')])
                          ->orWhere(function($subQ) use ($bufferStart, $bufferEnd) {
                              // Check if our buffer zone is completely within existing session
                              $subQ->where('start_time', '<=', $bufferStart->format('H:i'))
                                   ->where('end_time', '>=', $bufferEnd->format('H:i'));
                          });
                    });
                })
                ->exists();

            if ($sessionConflicts) {
                return false;
            }

            // Also check direct activities table for single-session activities
            $activityConflicts = Activity::where('activity_location', $this->location)
                ->where('activity_date', $date->format('Y-m-d'))
                ->when($this->excludeActivityId, function($query) {
                    $query->where('id', '!=', $this->excludeActivityId);
                })
                ->where(function($query) use ($bufferStart, $bufferEnd) {
                    $query->where(function($q) use ($bufferStart, $bufferEnd) {
                        $q->whereBetween('activity_start_time', [$bufferStart->format('H:i'), $bufferEnd->format('H:i')])
                          ->orWhereBetween('activity_end_time', [$bufferStart->format('H:i'), $bufferEnd->format('H:i')])
                          ->orWhere(function($subQ) use ($bufferStart, $bufferEnd) {
                              $subQ->where('activity_start_time', '<=', $bufferStart->format('H:i'))
                                   ->where('activity_end_time', '>=', $bufferEnd->format('H:i'));
                          });
                    });
                })
                ->exists();

            return !$activityConflicts;

        } catch (\Exception $e) {
            // If there's an error parsing times, allow the validation to pass
            // Let other validation rules handle format issues
            return true;
        }
    }

    public function message()
    {
        return "This time slot conflicts with another activity. Please ensure at least {$this->bufferMinutes} minutes between activities for setup/cleanup and participant transition.";
    }
}