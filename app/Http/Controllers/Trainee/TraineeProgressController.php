<?php

namespace App\Http\Controllers\Trainee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Trainee;
use App\Models\Activity;
use App\Models\ActivitySession;
use App\Models\SessionAttendance;
use Carbon\Carbon;
use App\Traits\HandlesEncryptedIds;
use Exception;

class TraineeProgressController extends Controller
{
    use HandlesEncryptedIds;

    /**
     * Show individual trainee's activity progress page
     */
    public function show($encrypted_id)
    {
        try {
            $traineeId = $this->decryptId($encrypted_id);
            $trainee = Trainee::with(['activities.sessions', 'sessionAttendances'])->findOrFail($traineeId);

            // Categorize activities by status
            $currentActivities = [];
            $pastActivities = [];
            $futureActivities = [];

            foreach ($trainee->activities as $activity) {
                $progress = $trainee->calculateActivityProgress($activity);
                $status = $this->getActivityStatus($activity);
                
                $activityData = [
                    'activity' => $activity,
                    'progress' => $progress,
                    'status' => $status,
                    'total_sessions' => $this->calculateTotalSessions($activity),
                    'attended_sessions' => $this->getAttendedSessions($trainee, $activity),
                    'upcoming_sessions' => $this->getUpcomingSessions($activity),
                    'pass_status' => $progress >= $activity->pass_threshold ? 'passing' : 'at_risk'
                ];

                if ($status === 'current') {
                    $currentActivities[] = $activityData;
                } elseif ($status === 'past') {
                    $pastActivities[] = $activityData;
                } else {
                    $futureActivities[] = $activityData;
                }
            }

            // Calculate overall statistics
            $overallStats = [
                'total_activities' => count($currentActivities) + count($pastActivities),
                'current_activities' => count($currentActivities),
                'completed_activities' => count($pastActivities),
                'average_progress' => $trainee->calculateAverageProgress(),
                'passing_activities' => collect($currentActivities)->where('pass_status', 'passing')->count(),
                'at_risk_activities' => collect($currentActivities)->where('pass_status', 'at_risk')->count()
            ];

            return view('trainees.progress', compact(
                'trainee', 
                'currentActivities', 
                'pastActivities', 
                'futureActivities',
                'overallStats'
            ));

        } catch (Exception $e) {
            return redirect()->route('trainees.home')
                ->with('error', 'Unable to load trainee progress. Please try again.');
        }
    }

    /**
     * Show weekly schedule for a trainee
     */
    public function weeklySchedule($encrypted_id)
    {
        try {
            $traineeId = $this->decryptId($encrypted_id);
            $trainee = Trainee::with(['activities.sessions'])->findOrFail($traineeId);

            // Get current week's sessions
            $startOfWeek = Carbon::now()->startOfWeek();
            $endOfWeek = Carbon::now()->endOfWeek();

            $weeklySchedule = [];
            
            // Get all sessions for this trainee for the current week
            foreach ($trainee->activities as $activity) {
                $sessions = $activity->sessions()
                    ->whereBetween('session_date', [$startOfWeek, $endOfWeek])
                    ->orderBy('session_date')
                    ->orderBy('session_start_time')
                    ->get();

                foreach ($sessions as $session) {
                    $dayOfWeek = Carbon::parse($session->session_date)->format('l'); // Monday, Tuesday, etc.
                    
                    if (!isset($weeklySchedule[$dayOfWeek])) {
                        $weeklySchedule[$dayOfWeek] = [];
                    }
                    
                    $weeklySchedule[$dayOfWeek][] = [
                        'activity' => $activity,
                        'session' => $session,
                        'time' => Carbon::parse($session->session_start_time)->format('g:i A') . ' - ' . 
                                 Carbon::parse($session->session_end_time)->format('g:i A'),
                        'venue' => $session->session_location,
                        'instructor' => $activity->creator->name ?? 'TBA'
                    ];
                }
            }

            // Sort each day's sessions by time
            foreach ($weeklySchedule as $day => $sessions) {
                usort($weeklySchedule[$day], function($a, $b) {
                    return strcmp($a['session']->session_start_time, $b['session']->session_start_time);
                });
            }

            return view('trainees.schedule', compact('trainee', 'weeklySchedule', 'startOfWeek', 'endOfWeek'));

        } catch (Exception $e) {
            return redirect()->route('trainees.home')
                ->with('error', 'Unable to load trainee schedule. Please try again.');
        }
    }

    /**
     * Helper methods
     */
    private function getActivityStatus($activity)
    {
        $now = Carbon::now();
        
        if (!$activity->start_date || !$activity->end_date) {
            return 'unknown';
        }
        
        $start = Carbon::parse($activity->start_date);
        $end = Carbon::parse($activity->end_date);
        
        if ($now->isBefore($start)) {
            return 'future';
        } elseif ($now->isAfter($end)) {
            return 'past';
        } else {
            return 'current';
        }
    }

    private function calculateTotalSessions($activity)
    {
        if (!$activity->start_date || !$activity->end_date || !$activity->sessions_per_week) {
            return $activity->sessions()->count();
        }
        
        $weeks = Carbon::parse($activity->start_date)->diffInWeeks(Carbon::parse($activity->end_date));
        return $weeks * $activity->sessions_per_week;
    }

    private function getAttendedSessions($trainee, $activity)
    {
        return $trainee->sessionAttendances()
            ->whereHas('session', function($query) use ($activity) {
                $query->where('activity_id', $activity->id);
            })
            ->where('attended', true)
            ->count();
    }

    private function getUpcomingSessions($activity)
    {
        return $activity->sessions()
            ->where('session_date', '>', Carbon::now())
            ->orderBy('session_date')
            ->orderBy('session_start_time')
            ->take(3)
            ->get();
    }
}
