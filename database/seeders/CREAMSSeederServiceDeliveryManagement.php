<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CREAMSSeederServiceDeliveryManagement extends Seeder
{
    /**
     * CREAMS Service Delivery Management Seeder
     * Seeds: Activity categories, activities, sessions, enrollments
     * Target: 400 activities (100 per centre), proper status distribution
     */
    public function run(): void
    {
        $this->command->info('🎯 Seeding CREAMS Service Delivery Management...');
        
        // Create activity categories
        $this->command->info('   📂 Creating activity categories...');
        $this->seedActivityCategories();
        
        // Create activities (100 per centre)
        $this->command->info('   🎯 Creating activities (400 total)...');
        $this->seedActivities();
        
        // Create activity sessions
        $this->command->info('   📅 Creating activity sessions...');
        $this->seedActivitySessions();
        
        // Create activity enrollments
        $this->command->info('   📝 Creating activity enrollments...');
        $this->seedActivityEnrollments();
        
        $this->command->info('✅ Service Delivery Management seeding completed');
    }
    
    private function seedActivityCategories(): void
    {
        $categories = [
            ['category_name' => 'Autism Spectrum Support', 'category_description' => 'Communication and social skills programs'],
            ['category_name' => 'Hearing Impairment', 'category_description' => 'Sign language and communication enhancement'],
            ['category_name' => 'Visual Impairment', 'category_description' => 'Mobility training and adaptive technology'],
            ['category_name' => 'Physical Disabilities', 'category_description' => 'Physical therapy and mobility aids'],
            ['category_name' => 'Learning Support', 'category_description' => 'Educational approaches for learning challenges'],
            ['category_name' => 'Speech Therapy', 'category_description' => 'Communication development programs'],
            ['category_name' => 'Faith & Values', 'category_description' => 'Religious and moral development'],
            ['category_name' => 'Academic Skills', 'category_description' => 'Educational and cognitive development']
        ];
        
        foreach ($categories as $category) {
            DB::table('activity_categories')->insertOrIgnore([
                'category_name' => $category['category_name'],
                'category_description' => $category['category_description'],
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    }
    
    private function seedActivities(): void
    {
        $centres = DB::table('centres')->get();
        $categories = DB::table('activity_categories')->get();
        $instructors = DB::table('users')->get();
        
        $totalActivities = 0;
        
        foreach ($centres as $centre) {
            // 100 activities per centre
            for ($i = 1; $i <= 100; $i++) {
                $category = $categories->random();
                $instructor = $instructors->where('centre_id', $centre->centre_id)->random();
                
                // Determine status (25% completed, 55% ongoing, 20% scheduled)
                $rand = rand(1, 100);
                if ($rand <= 25) {
                    $status = 'completed';
                    $createdDate = Carbon::create(2025, 6, rand(1, 30)); // June (completed)
                } elseif ($rand <= 80) { // 25 + 55 = 80
                    $status = 'ongoing';
                    $createdDate = Carbon::create(2025, 7, rand(1, 31)); // July-August (ongoing)
                } else {
                    $status = 'scheduled';
                    $createdDate = now(); // August (scheduled for September)
                }
                
                DB::table('activities')->insert([
                    'activity_name' => $this->generateActivityName($category->category_name, $i),
                    'activity_description' => $this->generateActivityDescription($category->category_name),
                    'category_id' => $category->id,
                    'centre_id' => $centre->centre_id,
                    'duration_weeks' => rand(4, 12), // 1-3 months
                    'sessions_per_week' => rand(2, 4), // 2-4 sessions per week
                    'session_duration_minutes' => [60, 90, 120][array_rand([60, 90, 120])],
                    'max_participants' => rand(8, 15),
                    'learning_outcomes' => $this->generateLearningOutcomes($category->category_name),
                    'activity_location' => 'Room ' . rand(101, 599),
                    'instructor_id' => $instructor ? $instructor->id : null,
                    'is_active' => $status !== 'completed',
                    'times_conducted' => $status === 'completed' ? 1 : 0,
                    'created_at' => $createdDate,
                    'updated_at' => $createdDate
                ]);
                
                $totalActivities++;
            }
        }
        
        $this->command->line("      ✓ Created {$totalActivities} activities across 4 centres");
    }
    
    private function seedActivitySessions(): void
    {
        $activities = DB::table('activities')->get();
        $totalSessions = 0;
        
        foreach ($activities as $activity) {
            $sessionsPerWeek = DB::table('activities')->where('id', $activity->id)->value('sessions_per_week');
            $durationWeeks = DB::table('activities')->where('id', $activity->id)->value('duration_weeks');
            $totalSessionsForActivity = $sessionsPerWeek * $durationWeeks;
            
            $startDate = Carbon::parse($activity->created_at);
            
            // Create sessions based on activity status
            for ($week = 0; $week < $durationWeeks; $week++) {
                for ($session = 1; $session <= $sessionsPerWeek; $session++) {
                    $sessionDate = $startDate->copy()->addWeeks($week)->addDays(($session - 1) * 2);
                    
                    // Determine session status
                    if ($sessionDate->isPast()) {
                        $sessionStatus = 'completed';
                    } elseif ($sessionDate->isToday() || $sessionDate->diffInDays(now()) <= 7) {
                        $sessionStatus = 'ongoing';
                    } else {
                        $sessionStatus = 'scheduled';
                    }
                    
                    DB::table('activity_sessions')->insert([
                        'activity_id' => $activity->id,
                        'session_name' => $activity->activity_name . ' - Session ' . (($week * $sessionsPerWeek) + $session),
                        'session_description' => 'Session ' . (($week * $sessionsPerWeek) + $session) . ' of ' . $totalSessionsForActivity,
                        'session_date' => $sessionDate->format('Y-m-d'),
                        'start_time' => ['09:00:00', '11:00:00', '14:00:00', '16:00:00'][array_rand(['09:00:00', '11:00:00', '14:00:00', '16:00:00'])],
                        'end_time' => '17:00:00', // Will be calculated properly based on duration
                        'location' => $activity->activity_location,
                        'instructor_id' => $activity->instructor_id,
                        'session_status' => $sessionStatus,
                        'max_participants' => $activity->max_participants,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                    
                    $totalSessions++;
                }
            }
        }
        
        $this->command->line("      ✓ Created {$totalSessions} activity sessions");
    }
    
    private function seedActivityEnrollments(): void
    {
        $activities = DB::table('activities')
            ->join('activity_categories', 'activities.category_id', '=', 'activity_categories.id')
            ->select('activities.*', 'activity_categories.category_name')
            ->get();
        
        $trainees = DB::table('trainees')->get();
        $totalEnrollments = 0;
        
        foreach ($trainees as $trainee) {
            $enrolledActivities = 0;
            $targetActivities = rand(4, 8); // Each trainee in 4-8 activities
            
            foreach ($activities->shuffle() as $activity) {
                if ($enrolledActivities >= $targetActivities) break;
                
                // Check if trainee should be enrolled based on category
                $shouldEnroll = false;
                
                if (in_array($activity->category_name, ['Faith & Values', 'Academic Skills'])) {
                    // All trainees can join faith and academic activities
                    $shouldEnroll = rand(1, 100) <= 70; // 70% chance
                } else {
                    // Rehabilitation activities - match disability type
                    $shouldEnroll = $this->shouldTraineeEnrollInActivity($trainee, $activity->category_name);
                }
                
                if ($shouldEnroll) {
                    DB::table('activity_enrollments')->insert([
                        'activity_id' => $activity->id,
                        'trainee_id' => $trainee->id,
                        'enrollment_date' => Carbon::parse($activity->created_at)->format('Y-m-d'),
                        'enrollment_status' => $activity->times_conducted > 0 ? 'completed' : 'enrolled',
                        'progress_percentage' => $activity->times_conducted > 0 ? 100.00 : rand(0, 80),
                        'completion_date' => $activity->times_conducted > 0 ? Carbon::parse($activity->created_at)->addWeeks($activity->duration_weeks) : null,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                    
                    $totalEnrollments++;
                    $enrolledActivities++;
                }
            }
        }
        
        $this->command->line("      ✓ Created {$totalEnrollments} activity enrollments");
    }
    
    private function shouldTraineeEnrollInActivity($trainee, $categoryName): bool
    {
        $traineeCondition = strtolower($trainee->trainee_condition);
        
        $matches = [
            'Autism Spectrum Support' => ['autism', 'spectrum'],
            'Hearing Impairment' => ['hearing', 'kurang upaya pendengaran'],
            'Visual Impairment' => ['visual', 'kurang upaya penglihatan'],
            'Physical Disabilities' => ['physical', 'palsy', 'fizikal'],
            'Learning Support' => ['learning', 'pembelajaran', 'adhd', 'attention'],
            'Speech Therapy' => ['speech', 'language', 'pertuturan']
        ];
        
        if (!isset($matches[$categoryName])) return false;
        
        foreach ($matches[$categoryName] as $keyword) {
            if (strpos($traineeCondition, $keyword) !== false) {
                return rand(1, 100) <= 85; // 85% chance if disability matches
            }
        }
        
        return rand(1, 100) <= 15; // 15% chance for non-matching disabilities
    }
    
    private function generateActivityName($category, $index): string
    {
        $names = [
            'Autism Spectrum Support' => ['Social Skills Workshop', 'Communication Circle', 'Behavior Management', 'Sensory Integration'],
            'Hearing Impairment' => ['Sign Language Class', 'Auditory Training', 'Communication Skills', 'Hearing Aid Workshop'],
            'Visual Impairment' => ['Mobility Training', 'Braille Learning', 'Adaptive Technology', 'Orientation Skills'],
            'Physical Disabilities' => ['Physiotherapy Session', 'Mobility Training', 'Adaptive Sports', 'Motor Skills'],
            'Learning Support' => ['Academic Support', 'Study Skills', 'Cognitive Training', 'Educational Therapy'],
            'Speech Therapy' => ['Articulation Training', 'Language Development', 'Communication Skills', 'Speech Practice'],
            'Faith & Values' => ['Islamic Studies', 'Moral Values', 'Character Building', 'Religious Activities'],
            'Academic Skills' => ['Mathematics Support', 'Reading Skills', 'Writing Workshop', 'Science Exploration']
        ];
        
        $baseName = $names[$category][array_rand($names[$category])];
        return $baseName . ' ' . $index;
    }
    
    private function generateActivityDescription($category): string
    {
        $descriptions = [
            'Autism Spectrum Support' => 'Specialized program focusing on communication and social interaction skills development.',
            'Hearing Impairment' => 'Comprehensive hearing support program with communication enhancement techniques.',
            'Visual Impairment' => 'Mobility and adaptive technology training for visual independence.',
            'Physical Disabilities' => 'Physical therapy and rehabilitation program for motor skill development.',
            'Learning Support' => 'Educational support program tailored for learning difficulties and challenges.',
            'Speech Therapy' => 'Communication development program focusing on speech and language skills.',
            'Faith & Values' => 'Islamic values and moral development program for character building.',
            'Academic Skills' => 'Academic support program for cognitive and educational skill development.'
        ];
        
        return $descriptions[$category] ?? 'Specialized rehabilitation program.';
    }
    
    private function generateLearningOutcomes($category): string
    {
        $outcomes = [
            'Autism Spectrum Support' => '1. Improved social interaction skills\n2. Enhanced communication abilities\n3. Better behavioral self-regulation',
            'Hearing Impairment' => '1. Proficiency in sign language\n2. Improved auditory processing\n3. Enhanced communication confidence',
            'Visual Impairment' => '1. Independent mobility skills\n2. Adaptive technology proficiency\n3. Enhanced spatial awareness',
            'Physical Disabilities' => '1. Improved motor function\n2. Enhanced physical strength\n3. Better coordination and balance',
            'Learning Support' => '1. Improved academic performance\n2. Enhanced study skills\n3. Better cognitive strategies',
            'Speech Therapy' => '1. Clear articulation\n2. Improved language comprehension\n3. Enhanced communication confidence',
            'Faith & Values' => '1. Strong moral foundation\n2. Islamic knowledge\n3. Character development',
            'Academic Skills' => '1. Academic competency\n2. Critical thinking skills\n3. Educational confidence'
        ];
        
        return $outcomes[$category] ?? '1. Skill development\n2. Personal growth\n3. Independence enhancement';
    }
}