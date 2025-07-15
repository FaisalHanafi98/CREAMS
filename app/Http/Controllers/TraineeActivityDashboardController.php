<?php

namespace App\Http\Controllers;

use App\Models\Trainee;
use App\Models\SessionEnrollment;
use App\Models\ActivityAttendance;
use App\Models\ActivitySession;
use Illuminate\Http\Request;
use Carbon\Carbon;

class TraineeActivityDashboardController extends Controller
{
    public function index()
    {
        $userId = session('id');
        $trainee = Trainee::where('user_id', $userId)->first();
        
        if (!$trainee) {
            return redirect()->route('dashboard')
                ->with('error', 'Trainee profile not found.');
        }
        
        // Get enrolled activities
        $enrollments = SessionEnrollment::with([
            'session.activity',
            'session.teacher',
            'attendance'
        ])
        ->where('trainee_id', $trainee->id)
        ->where('status', 'Active')
        ->get();
        
        // Get today's schedule
        $todaySchedule = $this->getTodaySchedule($enrollments);
        
        // Calculate attendance statistics
        $attendanceStats = $this->calculateAttendanceStats($trainee->id);
        
        // Get upcoming activities
        $upcomingActivities = $this->getUpcomingActivities($enrollments);
        
        return view('trainees.activity-dashboard', compact(
            'trainee',
            'enrollments',
            'todaySchedule',
            'attendanceStats',
            'upcomingActivities'
        ));
    }
    
    public function mySchedule()
    {
        $userId = session('id');
        $trainee = Trainee::where('user_id', $userId)->first();
        
        if (!$trainee) {
            return redirect()->route('dashboard')
                ->with('error', 'Trainee profile not found.');
        }
        
        $enrollments = SessionEnrollment::with(['session.activity', 'session.teacher'])
            ->where('trainee_id', $trainee->id)
            ->where('status', 'Active')
            ->get();
            
        // Group by day of week
        $schedule = $enrollments->groupBy(function ($enrollment) {
            return $enrollment->session->day_of_week;
        });
        
        return view('trainees.schedule', compact('trainee', 'schedule'));
    }
    
    public function myProgress($activityId = null)
    {
        $userId = session('id');
        $trainee = Trainee::where('user_id', $userId)->first();
        
        if (!$trainee) {
            return redirect()->route('dashboard')
                ->with('error', 'Trainee profile not found.');
        }
        
        $query = SessionEnrollment::with([
            'session.activity',
            'attendance'
        ])
        ->where('trainee_id', $trainee->id);
        
        if ($activityId) {
            $query->whereHas('session', function($q) use ($activityId) {
                $q->where('activity_id', $activityId);
            });
        }
        
        $enrollments = $query->get();
        
        // Calculate progress for each activity
        $progress = $enrollments->map(function ($enrollment) {
            $totalClasses = $enrollment->attendance->count();
            $attendedClasses = $enrollment->attendance
                ->whereIn('status', ['Present', 'Late'])->count();
            
            return [
                'activity' => $enrollment->session->activity,
                'total_classes' => $totalClasses,
                'attended_classes' => $attendedClasses,
                'attendance_rate' => $totalClasses > 0 
                    ? round(($attendedClasses / $totalClasses) * 100, 2) 
                    : 0,
                'participation_scores' => $enrollment->attendance
                    ->pluck('participation_score')
                    ->filter()
                    ->avg()
            ];
        });
        
        return view('trainees.progress', compact('trainee', 'progress'));
    }
    
    private function getTodaySchedule($enrollments)
    {
        $today = Carbon::now()->format('l');
        
        return $enrollments->filter(function ($enrollment) use ($today) {
            return $enrollment->session->day_of_week == $today;
        })->sortBy('session.start_time');
    }
    
    private function calculateAttendanceStats($traineeId)
    {
        $last30Days = Carbon::now()->subDays(30);
        
        $attendance = ActivityAttendance::where('trainee_id', $traineeId)
            ->where('attendance_date', '>=', $last30Days)
            ->get();
            
        $total = $attendance->count();
        $present = $attendance->whereIn('status', ['Present', 'Late'])->count();
        $absent = $attendance->where('status', 'Absent')->count();
        $excused = $attendance->where('status', 'Excused')->count();
        
        return [
            'total' => $total,
            'present' => $present,
            'absent' => $absent,
            'excused' => $excused,
            'rate' => $total > 0 ? round(($present / $total) * 100, 2) : 0
        ];
    }
    
    private function getUpcomingActivities($enrollments)
    {
        // Get next 7 days of activities
        $activities = collect();
        
        for ($i = 1; $i <= 7; $i++) {
            $date = Carbon::now()->addDays($i);
            $dayName = $date->format('l');
            
            $dayActivities = $enrollments->filter(function ($enrollment) use ($dayName) {
                return $enrollment->session->day_of_week == $dayName;
            });
            
            if ($dayActivities->count() > 0) {
                $activities->push([
                    'date' => $date,
                    'day' => $dayName,
                    'sessions' => $dayActivities
                ]);
            }
        }
        
        return $activities->take(5);
    }
}
