<?php

use Illuminate\Support\Facades\Route;
use App\Models\Trainee;
use App\Models\Centre;
use App\Models\ActivityEnrollment;

Route::get('/test-trainee-stats', function () {
    try {
        // Get all trainees with relationships
        $trainees = Trainee::with([
            'centre', 
            'activities', 
            'enrollments' => function($query) {
                $query->whereIn('enrollment_status', ['enrolled', 'active']);
            }
        ])->get();
        
        echo "Total trainees found: " . $trainees->count() . "<br>";
        
        // Count active trainees
        $activeTrainees = $trainees->where('status', 'active')->count();
        echo "Active trainees: " . $activeTrainees . "<br>";
        
        // Count trainees enrolled in activities
        $enrolledTrainees = $trainees->filter(function($trainee) {
            return $trainee->enrollments && $trainee->enrollments->count() > 0;
        })->count();
        echo "Enrolled trainees: " . $enrolledTrainees . "<br>";
        
        // Calculate progress and attendance
        $totalProgress = 0;
        $totalAttendance = 0;
        $traineesWithProgress = 0;
        $traineesWithAttendance = 0;
        
        foreach ($trainees as $trainee) {
            // Calculate progress
            $traineeProgress = $trainee->calculateAverageProgress();
            if ($traineeProgress > 0) {
                $totalProgress += $traineeProgress;
                $traineesWithProgress++;
            }
            
            // Calculate attendance
            $attendanceAvg = $trainee->getOverallAttendanceAverage();
            if ($attendanceAvg > 0) {
                $totalAttendance += $attendanceAvg;
                $traineesWithAttendance++;
            }
        }
        
        $avgProgress = $traineesWithProgress > 0 ? round($totalProgress / $traineesWithProgress, 1) : 0;
        $avgAttendance = $traineesWithAttendance > 0 ? round($totalAttendance / $traineesWithAttendance, 1) : 0;
        
        echo "Average progress: " . $avgProgress . "%<br>";
        echo "Average attendance: " . $avgAttendance . "%<br>";
        echo "Trainees with progress: " . $traineesWithProgress . "<br>";
        echo "Trainees with attendance: " . $traineesWithAttendance . "<br>";
        
        // Count below threshold
        $belowThreshold = $trainees->filter(function($trainee) {
            $attendanceAvg = $trainee->getOverallAttendanceAverage();
            return $attendanceAvg > 0 && $attendanceAvg < 50;
        })->count();
        
        echo "Below 50% attendance: " . $belowThreshold . "<br>";
        
        $stats = [
            'total' => $trainees->count(),
            'active' => $activeTrainees,
            'enrolled' => $enrolledTrainees,
            'avg_progress' => $avgProgress,
            'avg_attendance' => $avgAttendance,
            'below_threshold' => $belowThreshold
        ];
        
        echo "<br>Final stats array:<br>";
        echo "<pre>" . print_r($stats, true) . "</pre>";
        
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . "<br>";
        echo "Trace: " . $e->getTraceAsString();
    }
});
