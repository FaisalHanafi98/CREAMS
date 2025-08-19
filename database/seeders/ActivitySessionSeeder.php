<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;
use Carbon\Carbon;

class ActivitySessionSeeder extends Seeder
{
    /**
     * Seed activity sessions based on activity schedules
     */
    public function run(): void
    {
        $this->command->info('📅 Seeding activity sessions...');

        $faker = Faker::create();
        $activities = DB::table('activities')->get();
        
        if ($activities->isEmpty()) {
            $this->command->error('No activities found! Run ActivitySeeder first.');
            return;
        }

        $totalSessions = 0;
        
        foreach ($activities as $activity) {
            // Generate sessions based on activity schedule
            $sessionsCreated = $this->createSessionsForActivity($activity, $faker);
            $totalSessions += $sessionsCreated;
            
            if ($sessionsCreated > 0) {
                $this->command->line("   ✅ Created {$sessionsCreated} sessions for: {$activity->activity_name}");
            }
        }

        $this->command->info("📅 Successfully seeded {$totalSessions} activity sessions");
    }

    private function createSessionsForActivity($activity, $faker)
    {
        $startDate = Carbon::now()->subWeeks(4); // Start 4 weeks ago
        $endDate = Carbon::now()->addWeeks($activity->duration_weeks);
        
        $sessions = [];
        $sessionCount = 0;
        $currentDate = $startDate->copy();
        
        // Define session times based on activity type
        $sessionTimes = [
            '09:00:00', '10:30:00', '14:00:00', '15:30:00'
        ];
        
        // Generate sessions for the duration
        while ($currentDate->lte($endDate) && $sessionCount < ($activity->duration_weeks * $activity->sessions_per_week)) {
            // Skip weekends
            if ($currentDate->isWeekend()) {
                $currentDate->addDay();
                continue;
            }
            
            // Create sessions based on sessions_per_week
            if ($sessionCount % 7 < $activity->sessions_per_week) {
                $startTime = $faker->randomElement($sessionTimes);
                $endTime = Carbon::parse($startTime)->addMinutes($activity->session_duration_minutes)->format('H:i:s');
                
                // Determine session status based on date
                $status = 'scheduled';
                if ($currentDate->lt(Carbon::now()->subDay())) {
                    $status = $faker->randomElement(['completed', 'completed', 'completed', 'cancelled'], [70, 20, 5, 5]);
                } elseif ($currentDate->isToday()) {
                    $status = 'ongoing';
                }
                
                $sessionData = [
                    'activity_id' => $activity->id,
                    'session_name' => 'Session ' . ($sessionCount + 1),
                    'session_description' => 'Regular session for ' . $activity->activity_name,
                    'session_date' => $currentDate->format('Y-m-d'),
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                    'location' => $this->getLocationByCategory($activity->category_id),
                    'instructor_id' => $activity->instructor_id,
                    'session_status' => $status,
                    'session_notes' => $status === 'completed' ? $this->generateSessionNotes($faker) : null,
                    'max_participants' => $activity->max_participants,
                    'created_at' => now(),
                    'updated_at' => now()
                ];
                
                DB::table('activity_sessions')->insert($sessionData);
                $sessionCount++;
            }
            
            $currentDate->addDay();
        }
        
        return $sessionCount;
    }
    
    private function getLocationByCategory($categoryId)
    {
        $locations = [
            1 => 'Speech Therapy Room',
            2 => 'Occupational Therapy Room', 
            3 => 'Gymnasium',
            4 => 'Behavioral Intervention Room',
            5 => 'Classroom A',
            6 => 'Art Therapy Room',
            7 => 'Group Activity Room',
            8 => 'Life Skills Kitchen',
            9 => 'Computer Lab',
            10 => 'Sensory Integration Room',
            11 => 'Prayer Hall'
        ];
        
        return $locations[$categoryId] ?? 'Multi-Purpose Room';
    }
    
    private function generateSessionNotes($faker)
    {
        $notes = [
            'Good participation from all trainees. Objectives met successfully.',
            'Challenging session with mixed results. Some trainees showed improvement.',
            'Excellent progress noted. All participants actively engaged.',
            'Modified activities to better suit group needs. Positive outcomes.',
            'One trainee absent due to illness. Session proceeded as planned.',
            'Required additional support for two participants. Overall successful session.',
            'Outstanding engagement levels. Exceeded expected outcomes.',
            'Implemented new techniques with positive response from group.'
        ];
        
        return $faker->randomElement($notes);
    }
}