<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Activity;
use App\Models\ActivitySession;
use App\Models\ActivityEnrollment;
use App\Models\SessionAttendance;
use App\Models\Trainee;
use App\Models\User;
use Carbon\Carbon;

class TimeBasedProgressSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Update existing activities with time-based tracking data
        $activities = Activity::take(3)->get(); // Update first 3 activities
        
        foreach ($activities as $index => $activity) {
            $activity->update([
                'start_date' => Carbon::now()->subMonths(2), // Started 2 months ago
                'end_date' => Carbon::now()->addMonths(3),   // Ends in 3 months (5 month program)
                'sessions_per_week' => 2,
                'pass_threshold' => 60.00,
                'is_active' => true
            ]);

            // Create some sample sessions for the activity (only if none exist)
            if ($activity->sessions()->count() == 0) {
                for ($week = 0; $week < 10; $week++) { // 10 weeks of sessions (20 sessions total)
                    for ($session = 0; $session < 2; $session++) { // 2 sessions per week
                        ActivitySession::create([
                            'session_id' => 'DEMO' . $activity->id . sprintf('%03d', ($week * 2) + $session + 1),
                            'activity_id' => $activity->id,
                            'session_name' => $activity->activity_name . ' - Session ' . (($week * 2) + $session + 1),
                            'session_description' => 'Regular session for ' . $activity->activity_name,
                            'session_date' => Carbon::now()->subMonths(2)->addWeeks($week)->addDays($session * 3), // Mon/Thu schedule
                            'session_start_time' => '09:00',
                            'session_end_time' => '10:30',
                            'session_location' => $activity->activity_location,
                            'max_participants' => $activity->max_participants,
                            'session_status' => 'completed',
                            'instructor_id' => $activity->instructor_id ?? 1
                        ]);
                    }
                }
            }
        }

        // Get some trainees and enroll them in activities
        $trainees = Trainee::take(5)->get();
        $sessions = ActivitySession::all();

        foreach ($trainees as $trainee) {
            // Enroll trainee in 2-3 activities
            $activitiesToEnroll = $activities->take(rand(2, 3));
            
            foreach ($activitiesToEnroll as $activity) {
                // Create enrollment
                ActivityEnrollment::firstOrCreate([
                    'activity_id' => $activity->id,
                    'trainee_id' => $trainee->id,
                ], [
                    'enrollment_date' => $activity->start_date,
                    'enrollment_status' => 'enrolled',
                    'enrolled_by' => 1
                ]);

                // Create attendance records for sessions (random attendance pattern)
                $activitySessions = $sessions->where('activity_id', $activity->id);
                
                foreach ($activitySessions as $session) {
                    // 80% attendance rate with some random absences
                    $attended = rand(1, 10) <= 8; // 80% chance of attendance
                    
                    SessionAttendance::create([
                        'session_id' => $session->id,
                        'trainee_id' => $trainee->id,
                        'attended' => $attended,
                        'recorded_by' => 1,
                        'recorded_at' => $session->session_date
                    ]);
                }
            }
        }
    }
}
