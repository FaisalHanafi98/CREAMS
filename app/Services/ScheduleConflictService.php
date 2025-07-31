<?php

namespace App\Services;

use App\Models\Activity;
use App\Models\ActivitySession;
use App\Models\ActivityEnrollment;
use App\Models\Trainee;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ScheduleConflictService
{
    /**
     * Check for schedule conflicts when enrolling a trainee in an activity
     */
    public function checkTraineeConflict($traineeId, $activityId)
    {
        $trainee = Trainee::find($traineeId);
        $activity = Activity::with('sessions')->find($activityId);
        
        if (!$trainee || !$activity) {
            return ['conflict' => false];
        }

        // Get all activities the trainee is currently enrolled in
        $enrolledActivities = $trainee->activities()
            ->with('sessions')
            ->where('activity_enrollments.enrollment_status', 'enrolled')
            ->get();

        $conflicts = [];

        // Check each session of the new activity against existing enrollments
        foreach ($activity->sessions as $newSession) {
            foreach ($enrolledActivities as $existingActivity) {
                foreach ($existingActivity->sessions as $existingSession) {
                    if ($this->sessionsOverlap($newSession, $existingSession)) {
                        $conflicts[] = [
                            'type' => 'trainee',
                            'participant' => $trainee->trainee_first_name . ' ' . $trainee->trainee_last_name,
                            'conflicting_activity' => $existingActivity->activity_name,
                            'new_session_time' => $newSession->session_date->format('Y-m-d') . ' ' . $newSession->session_start_time,
                            'existing_session_time' => $existingSession->session_date->format('Y-m-d') . ' ' . $existingSession->session_start_time,
                            'message' => "Conflict: {$trainee->trainee_first_name} {$trainee->trainee_last_name} already has '{$existingActivity->activity_name}' at {$existingSession->session_start_time} on {$existingSession->session_date->format('l, M j')}"
                        ];
                    }
                }
            }
        }

        return [
            'conflict' => !empty($conflicts),
            'conflicts' => $conflicts,
            'participant' => $trainee
        ];
    }

    /**
     * Check for instructor conflicts when creating/scheduling an activity
     */
    public function checkInstructorConflict($instructorId, $activityId, $sessions = [])
    {
        $instructor = User::find($instructorId);
        $activity = Activity::find($activityId);
        
        if (!$instructor || !$activity) {
            return ['conflict' => false];
        }

        // Get all activities where this instructor is assigned
        $instructorActivities = Activity::with('sessions')
            ->where('instructor_id', $instructorId)
            ->where('id', '!=', $activityId)
            ->where('is_active', true)
            ->get();

        $conflicts = [];

        // If sessions are provided (new activity), check them
        // Otherwise check existing sessions of the activity
        $sessionsToCheck = !empty($sessions) ? $sessions : $activity->sessions;

        foreach ($sessionsToCheck as $newSession) {
            foreach ($instructorActivities as $existingActivity) {
                foreach ($existingActivity->sessions as $existingSession) {
                    if ($this->sessionsOverlap($newSession, $existingSession)) {
                        $conflicts[] = [
                            'type' => 'instructor',
                            'participant' => $instructor->name,
                            'conflicting_activity' => $existingActivity->activity_name,
                            'message' => "Conflict: Instructor {$instructor->name} already has '{$existingActivity->activity_name}' at {$existingSession->session_start_time} on {$existingSession->session_date->format('l, M j')}"
                        ];
                    }
                }
            }
        }

        return [
            'conflict' => !empty($conflicts),
            'conflicts' => $conflicts,
            'participant' => $instructor
        ];
    }

    /**
     * Check for venue conflicts
     */
    public function checkVenueConflict($venue, $activityId, $sessions = [])
    {
        $activity = Activity::find($activityId);
        
        if (!$activity) {
            return ['conflict' => false];
        }

        // Get all activities using the same venue
        $venueActivities = Activity::with('sessions')
            ->where('activity_location', $venue)
            ->where('id', '!=', $activityId)
            ->where('is_active', true)
            ->get();

        $conflicts = [];
        $sessionsToCheck = !empty($sessions) ? $sessions : $activity->sessions;

        foreach ($sessionsToCheck as $newSession) {
            foreach ($venueActivities as $existingActivity) {
                foreach ($existingActivity->sessions as $existingSession) {
                    if ($this->sessionsOverlap($newSession, $existingSession)) {
                        $conflicts[] = [
                            'type' => 'venue',
                            'venue' => $venue,
                            'conflicting_activity' => $existingActivity->activity_name,
                            'message' => "Conflict: Venue '{$venue}' already booked for '{$existingActivity->activity_name}' at {$existingSession->session_start_time} on {$existingSession->session_date->format('l, M j')}"
                        ];
                    }
                }
            }
        }

        return [
            'conflict' => !empty($conflicts),
            'conflicts' => $conflicts
        ];
    }

    /**
     * Comprehensive conflict check for activity creation/update
     */
    public function checkAllConflicts($activityData, $activityId = null)
    {
        $allConflicts = [];

        // Check instructor conflicts
        if (isset($activityData['instructor_id'])) {
            $instructorCheck = $this->checkInstructorConflict(
                $activityData['instructor_id'], 
                $activityId ?? 0,
                $activityData['sessions'] ?? []
            );
            if ($instructorCheck['conflict']) {
                $allConflicts = array_merge($allConflicts, $instructorCheck['conflicts']);
            }
        }

        // Check venue conflicts
        if (isset($activityData['activity_location'])) {
            $venueCheck = $this->checkVenueConflict(
                $activityData['activity_location'],
                $activityId ?? 0,
                $activityData['sessions'] ?? []
            );
            if ($venueCheck['conflict']) {
                $allConflicts = array_merge($allConflicts, $venueCheck['conflicts']);
            }
        }

        return [
            'conflict' => !empty($allConflicts),
            'conflicts' => $allConflicts,
            'total_conflicts' => count($allConflicts)
        ];
    }

    /**
     * Check if two sessions overlap in time
     */
    private function sessionsOverlap($session1, $session2)
    {
        // Convert to Carbon instances for comparison
        $date1 = Carbon::parse($session1->session_date ?? $session1['session_date']);
        $date2 = Carbon::parse($session2->session_date ?? $session2['session_date']);

        // Different dates = no conflict
        if (!$date1->isSameDay($date2)) {
            return false;
        }

        // Parse times
        $start1 = Carbon::parse($session1->session_start_time ?? $session1['session_start_time']);
        $end1 = Carbon::parse($session1->session_end_time ?? $session1['session_end_time']);
        $start2 = Carbon::parse($session2->session_start_time ?? $session2['session_start_time']);
        $end2 = Carbon::parse($session2->session_end_time ?? $session2['session_end_time']);

        // Check for time overlap
        return ($start1 < $end2) && ($end1 > $start2);
    }

    /**
     * Get suggested alternative times for conflicted sessions
     */
    public function suggestAlternativeTimes($venue, $date, $duration = 90)
    {
        $busyTimes = ActivitySession::whereDate('session_date', $date)
            ->where('session_location', $venue)
            ->get(['session_start_time', 'session_end_time']);

        $suggestions = [];
        $workingHours = ['08:00', '09:30', '11:00', '13:00', '14:30', '16:00'];

        foreach ($workingHours as $time) {
            $start = Carbon::parse($time);
            $end = $start->copy()->addMinutes($duration);
            
            $conflict = false;
            foreach ($busyTimes as $busyTime) {
                $busyStart = Carbon::parse($busyTime->session_start_time);
                $busyEnd = Carbon::parse($busyTime->session_end_time);
                
                if (($start < $busyEnd) && ($end > $busyStart)) {
                    $conflict = true;
                    break;
                }
            }
            
            if (!$conflict) {
                $suggestions[] = [
                    'start_time' => $start->format('H:i'),
                    'end_time' => $end->format('H:i'),
                    'display' => $start->format('g:i A') . ' - ' . $end->format('g:i A')
                ];
            }
        }

        return $suggestions;
    }
}