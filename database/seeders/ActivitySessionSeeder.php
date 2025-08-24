<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ActivitySessionSeeder extends Seeder
{
    /**
     * Seed MASSIVE activity sessions (440 activities across 4 centres)
     * Creates realistic session progression for rehabilitation tracking
     * Handles 4-12 week durations (8-24 sessions per activity)
     */
    public function run(): void
    {
        $this->command->info('📅 Seeding MASSIVE activity sessions (440 activities, 4-12 week durations)...');

        // Get all activities grouped by timeline
        $activities = DB::table('activities')
            ->orderBy('created_at')
            ->get();

        if ($activities->isEmpty()) {
            $this->command->error('No activities found! Run ActivitySeeder first.');
            return;
        }

        $totalSessions = 0;
        $timeSlots = [
            ['09:00:00', '10:30:00'], // Morning session 1 (90 min)
            ['11:00:00', '12:30:00'], // Morning session 2 (90 min)
            ['14:00:00', '15:30:00'], // Afternoon session 1 (90 min)
            ['16:00:00', '17:30:00']  // Afternoon session 2 (90 min)
        ];

        // Process each activity based on its timeline
        foreach ($activities as $activity) {
            $sessionCounter = 1;
            $activityStartDate = Carbon::parse($activity->created_at);
            
            // Determine the timeline based on creation date
            if ($activityStartDate->month == 6) {
                // June activities: Completed (12 sessions over 6 weeks)
                $totalSessions += $this->createCompletedActivitySessions(
                    $activity, 
                    Carbon::create(2024, 6, 1), 
                    12, // 12 sessions total
                    6,  // 6 weeks duration
                    $timeSlots,
                    'completed'
                );
            } elseif ($activityStartDate->month == 7) {
                // July activities: Completed (10 sessions over 5 weeks)
                $totalSessions += $this->createCompletedActivitySessions(
                    $activity,
                    Carbon::create(2024, 7, 1),
                    10, // 10 sessions total
                    5,  // 5 weeks duration
                    $timeSlots,
                    'completed'
                );
            } elseif ($activityStartDate->month == 8 && $activityStartDate->day < 20) {
                // August activities: Currently running (some sessions completed)
                $totalSessions += $this->createOngoingActivitySessions(
                    $activity,
                    Carbon::create(2024, 8, 1),
                    $timeSlots
                );
            } else {
                // September activities: Planned (future sessions) - created late August for September start
                $totalSessions += $this->createPlannedActivitySessions(
                    $activity,
                    Carbon::create(2024, 9, 23), // Start on Monday Sept 23rd (future date)
                    $timeSlots
                );
            }
        }

        $this->command->info("📅 Successfully seeded {$totalSessions} activity sessions with progression timeline");
        $this->command->line("   • June activities: Completed with full session attendance records");
        $this->command->line("   • July activities: Completed with full session attendance records");
        $this->command->line("   • August activities: Ongoing with partial session completion");
        $this->command->line("   • September activities: Planned sessions for future tracking");
        
        // Show session statistics by status
        $stats = DB::table('activity_sessions')
            ->select('session_status', DB::raw('count(*) as count'))
            ->groupBy('session_status')
            ->get();
            
        foreach ($stats as $stat) {
            $this->command->line("   📊 {$stat->session_status}: {$stat->count} sessions");
        }
    }
    
    private function getSessionStatus($date): string
    {
        $today = Carbon::today();
        
        if ($date->lt($today)) {
            return 'completed';
        } elseif ($date->eq($today)) {
            return 'ongoing';
        } else {
            return 'scheduled';
        }
    }
    
    private function generateSessionDescription($activity, $sessionNumber): string
    {
        $descriptions = [
            "Session {$sessionNumber}: Introduction and assessment",
            "Session {$sessionNumber}: Skill building and practice",
            "Session {$sessionNumber}: Individual and group activities",
            "Session {$sessionNumber}: Progress evaluation and feedback",
            "Session {$sessionNumber}: Reinforcement and application",
            "Session {$sessionNumber}: Review and consolidation"
        ];
        
        return $descriptions[($sessionNumber - 1) % count($descriptions)];
    }
    
    private function generateSessionNotes($status): ?string
    {
        $notes = [
            'completed' => 'Session completed successfully with good participation',
            'ongoing' => 'Session in progress',
            'scheduled' => null
        ];
        
        return $notes[$status];
    }

    private function createCompletedActivitySessions($activity, $startDate, $totalSessions, $weeks, $timeSlots, $status)
    {
        $sessionsCreated = 0;
        $sessionsPerWeek = 2;
        $sessionCounter = 1;
        
        // Create sessions over the specified number of weeks
        for ($week = 0; $week < $weeks; $week++) {
            for ($sessionInWeek = 0; $sessionInWeek < $sessionsPerWeek; $sessionInWeek++) {
                if ($sessionCounter > $totalSessions) break;
                
                // Calculate session date (Monday and Wednesday)
                $dayOfWeek = $sessionInWeek == 0 ? 1 : 3; // Monday = 1, Wednesday = 3
                $sessionDate = $startDate->copy()->addWeeks($week)->startOfWeek()->addDays($dayOfWeek - 1);
                
                $timeSlot = $timeSlots[$sessionInWeek % count($timeSlots)];
                
                $sessionData = [
                    'activity_id' => $activity->id,
                    'session_name' => 'Week ' . ($week + 1) . ' Session ' . ($sessionInWeek + 1) . ' - ' . $activity->activity_name,
                    'session_description' => $this->generateSessionDescription($activity, $sessionCounter),
                    'session_date' => $sessionDate->format('Y-m-d'),
                    'start_time' => $timeSlot[0],
                    'end_time' => $timeSlot[1],
                    'location' => $activity->activity_location,
                    'instructor_id' => $activity->instructor_id,
                    'session_status' => $status,
                    'session_notes' => 'Session completed successfully with good participation from trainees.',
                    'max_participants' => $activity->max_participants,
                    'created_at' => $sessionDate,
                    'updated_at' => $sessionDate->copy()->addHours(2)
                ];
                
                DB::table('activity_sessions')->insert($sessionData);
                $sessionsCreated++;
                $sessionCounter++;
            }
        }
        
        return $sessionsCreated;
    }

    private function createOngoingActivitySessions($activity, $startDate, $timeSlots)
    {
        $sessionsCreated = 0;
        $weeksElapsed = $activity->times_conducted / 2; // 2 sessions per week
        $sessionCounter = 1;
        
        // Create completed sessions for weeks already elapsed
        for ($week = 0; $week < $weeksElapsed; $week++) {
            for ($sessionInWeek = 0; $sessionInWeek < 2; $sessionInWeek++) {
                $dayOfWeek = $sessionInWeek == 0 ? 1 : 3; // Monday and Wednesday
                $sessionDate = $startDate->copy()->addWeeks($week)->startOfWeek()->addDays($dayOfWeek - 1);
                
                $timeSlot = $timeSlots[$sessionInWeek % count($timeSlots)];
                
                $sessionData = [
                    'activity_id' => $activity->id,
                    'session_name' => 'Week ' . ($week + 1) . ' Session ' . ($sessionInWeek + 1) . ' - ' . $activity->activity_name,
                    'session_description' => $this->generateSessionDescription($activity, $sessionCounter),
                    'session_date' => $sessionDate->format('Y-m-d'),
                    'start_time' => $timeSlot[0],
                    'end_time' => $timeSlot[1],
                    'location' => $activity->activity_location,
                    'instructor_id' => $activity->instructor_id,
                    'session_status' => 'completed',
                    'session_notes' => 'Session completed with ongoing progress tracking.',
                    'max_participants' => $activity->max_participants,
                    'created_at' => $sessionDate,
                    'updated_at' => $sessionDate->copy()->addHours(2)
                ];
                
                DB::table('activity_sessions')->insert($sessionData);
                $sessionsCreated++;
                $sessionCounter++;
            }
        }
        
        // Create remaining planned sessions (up to 6 weeks total)
        for ($week = $weeksElapsed; $week < 6; $week++) {
            for ($sessionInWeek = 0; $sessionInWeek < 2; $sessionInWeek++) {
                $dayOfWeek = $sessionInWeek == 0 ? 1 : 3;
                $sessionDate = $startDate->copy()->addWeeks($week)->startOfWeek()->addDays($dayOfWeek - 1);
                
                $timeSlot = $timeSlots[$sessionInWeek % count($timeSlots)];
                $status = $sessionDate->isFuture() ? 'scheduled' : 'ongoing';
                
                $sessionData = [
                    'activity_id' => $activity->id,
                    'session_name' => 'Week ' . ($week + 1) . ' Session ' . ($sessionInWeek + 1) . ' - ' . $activity->activity_name,
                    'session_description' => $this->generateSessionDescription($activity, $sessionCounter),
                    'session_date' => $sessionDate->format('Y-m-d'),
                    'start_time' => $timeSlot[0],
                    'end_time' => $timeSlot[1],
                    'location' => $activity->activity_location,
                    'instructor_id' => $activity->instructor_id,
                    'session_status' => $status,
                    'session_notes' => $status === 'ongoing' ? 'Session in progress' : null,
                    'max_participants' => $activity->max_participants,
                    'created_at' => now(),
                    'updated_at' => now()
                ];
                
                DB::table('activity_sessions')->insert($sessionData);
                $sessionsCreated++;
                $sessionCounter++;
            }
        }
        
        return $sessionsCreated;
    }

    private function createPlannedActivitySessions($activity, $startDate, $timeSlots)
    {
        $sessionsCreated = 0;
        $sessionCounter = 1;
        
        // Create 6 weeks of planned sessions (12 sessions total)
        for ($week = 0; $week < 6; $week++) {
            for ($sessionInWeek = 0; $sessionInWeek < 2; $sessionInWeek++) {
                $dayOfWeek = $sessionInWeek == 0 ? 1 : 3; // Monday and Wednesday
                $sessionDate = $startDate->copy()->addWeeks($week)->startOfWeek()->addDays($dayOfWeek - 1);
                
                $timeSlot = $timeSlots[$sessionInWeek % count($timeSlots)];
                
                $sessionData = [
                    'activity_id' => $activity->id,
                    'session_name' => 'Week ' . ($week + 1) . ' Session ' . ($sessionInWeek + 1) . ' - ' . $activity->activity_name,
                    'session_description' => $this->generateSessionDescription($activity, $sessionCounter),
                    'session_date' => $sessionDate->format('Y-m-d'),
                    'start_time' => $timeSlot[0],
                    'end_time' => $timeSlot[1],
                    'location' => $activity->activity_location,
                    'instructor_id' => $activity->instructor_id,
                    'session_status' => 'scheduled',
                    'session_notes' => null,
                    'max_participants' => $activity->max_participants,
                    'created_at' => now(),
                    'updated_at' => now()
                ];
                
                DB::table('activity_sessions')->insert($sessionData);
                $sessionsCreated++;
                $sessionCounter++;
            }
        }
        
        return $sessionsCreated;
    }
}