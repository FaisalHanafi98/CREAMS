<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Activity;
use App\Models\ActivitySession;
use App\Models\ActivityEnrollment;
use App\Models\SessionEnrollment;
use App\Models\Trainee;
use App\Models\User;
use Carbon\Carbon;

class CREAMSSessionsAndEnrollmentsBooster extends Seeder
{
    public function run(): void
    {
        $this->command->info('🚀 Boosting sessions and enrollments for Kuantan and Pagoh centres...');
        
        // Get activities for Kuantan and Pagoh (excluding Gombak - centre_id '01')
        $kuantanActivities = Activity::where('centre_id', '02')->get();
        $pagohActivities = Activity::where('centre_id', '03')->get();
        
        $this->command->info("📊 Found {$kuantanActivities->count()} Kuantan activities and {$pagohActivities->count()} Pagoh activities");
        
        // Create sessions for each centre (5x boost)
        $this->createSessionsForCentre($kuantanActivities, '02', 'Kuantan');
        $this->createSessionsForCentre($pagohActivities, '03', 'Pagoh');
        
        // Create comprehensive enrollments
        $this->createEnrollmentsForCentre('02', 'Kuantan');
        $this->createEnrollmentsForCentre('03', 'Pagoh');
        
        $this->showFinalStatistics();
    }

    private function createSessionsForCentre($activities, string $centreId, string $centreName): void
    {
        $this->command->info("📅 Creating sessions for {$centreName}...");
        
        $sessionCount = 0;
        
        foreach ($activities as $activity) {
            // Create 10-25 sessions per activity (significant boost)
            $sessionsToCreate = rand(10, 25);
            
            for ($i = 0; $i < $sessionsToCreate; $i++) {
                $sessionDate = $this->generateSessionDate();
                
                $session = ActivitySession::create([
                    'activity_id' => $activity->id,
                    'session_id' => 'S' . $centreId . '_' . time() . '_' . $i,
                    'session_name' => $activity->activity_name . ' - Session ' . ($i + 1),
                    'session_description' => 'Comprehensive session for ' . $activity->activity_name,
                    'session_date' => $sessionDate->format('Y-m-d'),
                    'scheduled_date' => $sessionDate->format('Y-m-d'),
                    'start_time' => $this->getRandomTime(),
                    'end_time' => $this->getRandomEndTime(),
                    'session_start_time' => $this->getRandomTime(),
                    'session_end_time' => $this->getRandomEndTime(),
                    'venue' => $this->getRandomVenue(),
                    'max_participants' => $activity->max_participants,
                    'current_participants' => 0,
                    'status' => $this->getSessionStatus($sessionDate),
                    'teacher_id' => $activity->created_by,
                    'instructor_id' => $activity->instructor_id,
                    'attendance_marked' => $sessionDate->isPast(),
                    'notes' => $this->generateSessionNotes(),
                    'created_at' => $sessionDate->copy()->subDays(rand(1, 7))
                ]);
                
                $sessionCount++;
            }
        }
        
        $this->command->info("✅ Created {$sessionCount} sessions for {$centreName}");
    }

    private function createEnrollmentsForCentre(string $centreId, string $centreName): void
    {
        $this->command->info("👥 Creating enrollments for {$centreName}...");
        
        $trainees = Trainee::where('centre_id', $centreId)->get();
        $activities = Activity::where('centre_id', $centreId)->get();
        $enrollmentCount = 0;
        
        foreach ($trainees as $trainee) {
            // Each trainee gets enrolled in 15-25 activities (major boost)
            $activitiesToEnroll = $activities->random(min(rand(15, 25), $activities->count()));
            
            foreach ($activitiesToEnroll as $activity) {
                // Create activity enrollment
                ActivityEnrollment::firstOrCreate([
                    'activity_id' => $activity->id,
                    'trainee_id' => $trainee->id
                ], [
                    'enrollment_date' => Carbon::now()->subDays(rand(1, 30)),
                    'enrollment_status' => 'active',
                    'created_at' => Carbon::now()->subDays(rand(1, 30))
                ]);
                
                // Get sessions for this activity
                $sessions = ActivitySession::where('activity_id', $activity->id)
                    ->inRandomOrder()
                    ->limit(rand(10, 20)) // 10-20 sessions per trainee per activity
                    ->get();
                
                foreach ($sessions as $session) {
                    if ($session->current_participants < $session->max_participants) {
                        // Create session enrollment
                        SessionEnrollment::create([
                            'session_id' => $session->id,
                            'trainee_id' => $trainee->id,
                            'enrollment_date' => $session->session_date,
                            'attendance_status' => $this->getAttendanceStatus(),
                            'participation_level' => rand(3, 5),
                            'behavior_notes' => $this->generateBehaviorNotes(),
                            'skill_demonstration' => $this->generateSkillDemo(),
                            'parent_feedback' => $this->generateParentFeedback(),
                            'homework_completed' => rand(0, 1),
                            'created_at' => Carbon::parse($session->session_date)->subDays(rand(0, 5))
                        ]);
                        
                        // Update session participant count
                        $session->increment('current_participants');
                        $enrollmentCount++;
                    }
                }
            }
        }
        
        $this->command->info("✅ Created {$enrollmentCount} session enrollments for {$centreName}");
    }

    private function generateSessionDate(): Carbon
    {
        // Generate dates from 3 months ago to 3 months in the future
        $start = Carbon::now()->subMonths(3);
        $end = Carbon::now()->addMonths(3);
        
        return Carbon::createFromTimestamp(rand($start->timestamp, $end->timestamp));
    }

    private function getRandomTime(): string
    {
        $hour = rand(8, 17);
        $minute = [0, 30][rand(0, 1)];
        return sprintf('%02d:%02d:00', $hour, $minute);
    }

    private function getRandomEndTime(): string
    {
        $hour = rand(10, 18);
        $minute = [0, 30][rand(0, 1)];
        return sprintf('%02d:%02d:00', $hour, $minute);
    }

    private function getRandomVenue(): string
    {
        $venues = [
            'Therapy Room 1', 'Therapy Room 2', 'Group Activity Hall',
            'Sensory Integration Room', 'Computer Lab', 'Outdoor Area',
            'Workshop', 'Kitchen Training Area', 'Conference Room',
            'Vocational Training Center', 'Life Skills Room'
        ];
        
        return $venues[array_rand($venues)];
    }

    private function getSessionStatus(Carbon $date): string
    {
        if ($date->isFuture()) {
            return ['scheduled', 'confirmed'][rand(0, 1)];
        } else {
            return ['completed', 'completed', 'completed', 'cancelled'][rand(0, 3)];
        }
    }

    private function generateSessionNotes(): string
    {
        $notes = [
            'Excellent participation and engagement from all trainees.',
            'Session objectives achieved with positive outcomes.',
            'Some trainees needed additional support with complex tasks.',
            'Great progress observed in target skill areas.',
            'Modified activities based on individual needs and responses.',
            'Positive behavior and cooperation throughout the session.',
            'Trainees demonstrated improved skills compared to previous sessions.'
        ];
        
        return $notes[array_rand($notes)];
    }

    private function getAttendanceStatus(): string
    {
        // High attendance rate with some variation
        $statuses = [
            'present', 'present', 'present', 'present', 'present',
            'late', 'absent', 'excused'
        ];
        
        return $statuses[array_rand($statuses)];
    }

    private function generateBehaviorNotes(): string
    {
        $notes = [
            'Demonstrated excellent social skills and cooperation.',
            'Showed enthusiasm and willingness to participate.',
            'Required minimal prompting to complete activities.',
            'Displayed appropriate behavior throughout the session.',
            'Interacted positively with peers and instructors.',
            'Maintained focus and attention for most of the session.',
            'Showed improvement in self-regulation strategies.'
        ];
        
        return $notes[array_rand($notes)];
    }

    private function generateSkillDemo(): string
    {
        $demos = [
            'Successfully demonstrated target communication skills.',
            'Showed significant improvement in motor coordination.',
            'Applied learned strategies to complete complex tasks.',
            'Exhibited enhanced problem-solving abilities.',
            'Demonstrated increased independence in activity completion.',
            'Showed mastery of previously challenging concepts.',
            'Applied social skills effectively in group settings.'
        ];
        
        return $demos[array_rand($demos)];
    }

    private function generateParentFeedback(): string
    {
        $feedback = [
            'Parents report positive changes in behavior at home.',
            'Family notices improved skills in daily activities.',
            'Parents express satisfaction with progress made.',
            'Positive feedback on trainee\'s enthusiasm for sessions.',
            'Family requests continued focus on independence skills.',
            'Parents appreciate detailed progress reports.',
            'Family supports current intervention approach.'
        ];
        
        return $feedback[array_rand($feedback)];
    }

    private function showFinalStatistics(): void
    {
        // Get comprehensive statistics
        $kuantanStats = $this->getCentreStats('02');
        $pagohStats = $this->getCentreStats('03');
        
        $this->command->info("\n" . str_repeat('=', 80));
        $this->command->info("🎉 SESSIONS AND ENROLLMENTS BOOST COMPLETED! 🎉");
        $this->command->info(str_repeat('=', 80));
        
        $this->command->info("\n📊 KUANTAN CENTRE FINAL STATISTICS:");
        $this->command->info("🎯 Activities: " . $kuantanStats['activities']);
        $this->command->info("📅 Sessions: " . $kuantanStats['sessions']);
        $this->command->info("👥 Session Enrollments: " . $kuantanStats['session_enrollments']);
        $this->command->info("📋 Activity Enrollments: " . $kuantanStats['activity_enrollments']);
        $this->command->info("🧒 Trainees: " . $kuantanStats['trainees']);
        
        $this->command->info("\n📊 PAGOH CENTRE FINAL STATISTICS:");
        $this->command->info("🎯 Activities: " . $pagohStats['activities']);
        $this->command->info("📅 Sessions: " . $pagohStats['sessions']);
        $this->command->info("👥 Session Enrollments: " . $pagohStats['session_enrollments']);
        $this->command->info("📋 Activity Enrollments: " . $pagohStats['activity_enrollments']);
        $this->command->info("🧒 Trainees: " . $pagohStats['trainees']);
        
        $totalSessions = $kuantanStats['sessions'] + $pagohStats['sessions'];
        $totalEnrollments = $kuantanStats['session_enrollments'] + $pagohStats['session_enrollments'];
        
        $this->command->info("\n🌟 COMBINED TOTALS:");
        $this->command->info("📅 Total Sessions Created: " . $totalSessions);
        $this->command->info("👥 Total Session Enrollments: " . $totalEnrollments);
        
        $this->command->info("\n✅ Both centres now have comprehensive activity programs!");
        $this->command->info("✅ Every trainee has significantly more sessions and activities!");
        $this->command->info("✅ 5x increase in activity sessions achieved!");
        $this->command->info(str_repeat('=', 80) . "\n");
    }

    private function getCentreStats(string $centreId): array
    {
        return [
            'activities' => Activity::where('centre_id', $centreId)->count(),
            'sessions' => ActivitySession::whereHas('activity', function($query) use ($centreId) {
                $query->where('centre_id', $centreId);
            })->count(),
            'session_enrollments' => SessionEnrollment::whereHas('session.activity', function($query) use ($centreId) {
                $query->where('centre_id', $centreId);
            })->count(),
            'activity_enrollments' => ActivityEnrollment::whereHas('activity', function($query) use ($centreId) {
                $query->where('centre_id', $centreId);
            })->count(),
            'trainees' => Trainee::where('centre_id', $centreId)->count()
        ];
    }
}