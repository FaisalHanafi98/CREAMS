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
     * NEW BUSINESS LOGIC:
     * - 3-10 trainees per session (updated from 8-15)
     * - Operating hours: 9:30 AM - 3:30 PM (sessions end by 4:30 PM)
     * - Maximum 5 sessions per instructor per day
     * - Maximum 5 sessions per trainee per day
     * - Activities require minimum 3 trainees enrolled before creation
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
        // No longer needed - categories are now direct enum values in activities table
        // Categories: Autism Spectrum Support, Hearing Impairment, Visual Impairment,
        // Physical Disabilities, Learning Support, Speech Therapy
    }
    
    private function seedActivities(): void
    {
        $centres = DB::table('centres')->get();
        $categories = [
            'Autism Spectrum Support',
            'Hearing Impairment',
            'Visual Impairment',
            'Physical Disabilities',
            'Learning Support',
            'Speech Therapy'
        ];
        $instructors = DB::table('staffs')->get();

        $totalActivities = 0;

        foreach ($centres as $centre) {
            // 100 activities per centre
            for ($i = 1; $i <= 100; $i++) {
                $category = $categories[array_rand($categories)];
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
                    'activity_name' => $this->generateActivityName($category, $i),
                    'activity_description' => $this->generateActivityDescription($category),
                    'category' => $category,
                    'centre_id' => $centre->centre_id,
                    'duration_weeks' => rand(4, 12), // 1-3 months
                    'sessions_per_week' => rand(2, 4), // 2-4 sessions per week
                    'session_duration_minutes' => [60, 90, 120][array_rand([60, 90, 120])],
                    'max_participants' => rand(3, 10),
                    'learning_outcomes' => $this->generateLearningOutcomes($category),
                    'activity_location' => 'Room ' . rand(101, 599),
                    'instructor_id' => $instructor ? $instructor->id : null,
                    'is_active' => $status !== 'completed',
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
        
        // Track daily sessions per instructor and date
        $dailyInstructorSessions = [];
        
        foreach ($activities as $activity) {
            $sessionsPerWeek = DB::table('activities')->where('id', $activity->id)->value('sessions_per_week');
            $durationWeeks = DB::table('activities')->where('id', $activity->id)->value('duration_weeks');
            $instructorId = $activity->instructor_id;
            
            $startDate = Carbon::parse($activity->created_at);
            
            // Create realistic sessions with business rule compliance
            for ($week = 0; $week < $durationWeeks; $week++) {
                $weekStart = $startDate->copy()->addWeeks($week)->startOfWeek();
                
                for ($session = 1; $session <= $sessionsPerWeek; $session++) {
                    // Find a suitable day in the week for this session
                    $sessionDate = $this->findAvailableSessionSlot(
                        $weekStart, 
                        $instructorId, 
                        $dailyInstructorSessions
                    );
                    
                    if (!$sessionDate) {
                        // Skip if no available slot (instructor overbooked)
                        continue;
                    }
                    
                    // Determine session status
                    if ($sessionDate->isPast()) {
                        $sessionStatus = 'completed';
                    } elseif ($sessionDate->isToday() || $sessionDate->diffInDays(now()) <= 7) {
                        $sessionStatus = 'ongoing';
                    } else {
                        $sessionStatus = 'scheduled';
                    }
                    
                    // Generate realistic time slots (1.5-2 hour sessions)
                    $timeSlot = $this->getAvailableTimeSlot();
                    
                    DB::table('activity_occurrences')->insert([
                        'activity_id' => $activity->id,
                        'session_name' => $activity->activity_name . ' - Week ' . ($week + 1),
                        'session_description' => 'Week ' . ($week + 1) . ' session for ' . $activity->activity_name,
                        'session_date' => $sessionDate->format('Y-m-d'),
                        'start_time' => $timeSlot['start'],
                        'end_time' => $timeSlot['end'],
                        'location' => $activity->activity_location,
                        'instructor_id' => $instructorId,
                        'session_status' => $sessionStatus,
                        'max_participants' => $activity->max_participants,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                    
                    // Track this session for daily limits
                    $dateKey = $sessionDate->format('Y-m-d');
                    if (!isset($dailyInstructorSessions[$instructorId][$dateKey])) {
                        $dailyInstructorSessions[$instructorId][$dateKey] = 0;
                    }
                    $dailyInstructorSessions[$instructorId][$dateKey]++;
                    
                    $totalSessions++;
                }
            }
        }
        
        $this->command->line("      ✓ Created {$totalSessions} activity sessions (compliant with 5 sessions/day limit)");
    }
    
    /**
     * Find an available session slot respecting the 5 sessions per day limit
     */
    private function findAvailableSessionSlot($weekStart, $instructorId, &$dailyInstructorSessions)
    {
        // Try each weekday (Monday to Friday)
        for ($day = 0; $day < 5; $day++) {
            $potentialDate = $weekStart->copy()->addDays($day);
            $dateKey = $potentialDate->format('Y-m-d');
            
            // Check if instructor has less than 5 sessions on this day
            $currentSessions = $dailyInstructorSessions[$instructorId][$dateKey] ?? 0;
            
            if ($currentSessions < 5) {
                return $potentialDate;
            }
        }
        
        return null; // No available slots this week
    }
    
    /**
     * Get realistic time slots (1.5-2 hour sessions)
     */
    private function getAvailableTimeSlot()
    {
        $timeSlots = [
            ['start' => '09:30:00', 'end' => '11:00:00'], // 1.5 hours
            ['start' => '11:15:00', 'end' => '12:45:00'], // 1.5 hours
            ['start' => '13:00:00', 'end' => '14:30:00'], // 1.5 hours
            ['start' => '14:45:00', 'end' => '16:15:00'], // 1.5 hours
            ['start' => '10:00:00', 'end' => '12:00:00'], // 2 hours
            ['start' => '13:30:00', 'end' => '15:30:00'], // 2 hours
        ];
        
        return $timeSlots[array_rand($timeSlots)];
    }
    
    private function seedActivityEnrollments(): void
    {
        $activities = DB::table('activities')->get();
        $trainees = DB::table('trainees')->get();
        $totalEnrollments = 0;

        foreach ($trainees as $trainee) {
            $enrolledActivities = 0;
            $targetActivities = rand(4, 8); // Each trainee in 4-8 activities

            foreach ($activities->shuffle() as $activity) {
                if ($enrolledActivities >= $targetActivities) break;

                // Check if trainee should be enrolled based on category
                $shouldEnroll = false;

                if (in_array($activity->category, ['Learning Support'])) {
                    // All trainees can join faith and academic activities
                    $shouldEnroll = rand(1, 100) <= 70; // 70% chance
                } else {
                    // Rehabilitation activities - match disability type
                    $shouldEnroll = $this->shouldTraineeEnrollInActivity($trainee, $activity->category);
                }

                if ($shouldEnroll) {
                    // Calculate enrollment details based on activity status
                    $enrollmentDetails = $this->calculateEnrollmentDetails($activity, $trainee);

                    DB::table('activity_enrollments')->insert([
                        'activity_id' => $activity->id,
                        'trainee_id' => $trainee->id,
                        'enrollment_date' => Carbon::parse($activity->created_at)->format('Y-m-d'),
                        'enrollment_status' => $enrollmentDetails['status'],
                        'enrollment_notes' => $enrollmentDetails['enrollment_notes'],
                        'progress_percentage' => $enrollmentDetails['progress_percentage'],
                        'completion_date' => $enrollmentDetails['completion_date'],
                        'completion_notes' => $enrollmentDetails['completion_notes'],
                        'attendance_count' => $enrollmentDetails['attendance_count'],
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);

                    $totalEnrollments++;
                    $enrolledActivities++;
                }
            }
        }

        // Ensure each activity has at least 3 trainees (NEW BUSINESS RULE)
        $this->ensureMinimumEnrollments($activities, $trainees);

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
            'Autism Spectrum Support' => '1. Improved social interaction skills 2. Enhanced communication abilities 3. Better behavioral self-regulation',
            'Hearing Impairment' => '1. Proficiency in sign language 2. Improved auditory processing 3. Enhanced communication confidence',
            'Visual Impairment' => '1. Independent mobility skills 2. Adaptive technology proficiency 3. Enhanced spatial awareness',
            'Physical Disabilities' => '1. Improved motor function 2. Enhanced physical strength 3. Better coordination and balance',
            'Learning Support' => '1. Improved academic performance 2. Enhanced study skills 3. Better cognitive strategies',
            'Speech Therapy' => '1. Clear articulation 2. Improved language comprehension 3. Enhanced communication confidence',
            'Faith & Values' => '1. Strong moral foundation 2. Islamic knowledge 3. Character development',
            'Academic Skills' => '1. Academic competency 2. Critical thinking skills 3. Educational confidence'
        ];

        return $outcomes[$category] ?? '1. Skill development 2. Personal growth 3. Independence enhancement';
    }
    
    /**
     * Ensure each activity has at least 3 trainees enrolled (NEW BUSINESS RULE)
     */
    /**
     * Calculate enrollment details based on activity and session status
     */
    private function calculateEnrollmentDetails($activity, $trainee): array
    {
        // Get all sessions for this activity
        $totalSessions = DB::table('activity_occurrences')
            ->where('activity_id', $activity->id)
            ->count();

        if ($totalSessions === 0) {
            // No sessions yet - activity is just enrolled
            return [
                'status' => 'enrolled',
                'enrollment_notes' => $this->generateEnrollmentNotes($activity->category, $trainee),
                'progress_percentage' => 0.00,
                'completion_date' => null,
                'completion_notes' => null,
                'attendance_count' => 0
            ];
        }

        // Check if activity has any future sessions
        $futureSessions = DB::table('activity_occurrences')
            ->where('activity_id', $activity->id)
            ->where('session_date', '>', now()->toDateString())
            ->count();

        $completedSessions = DB::table('activity_occurrences')
            ->where('activity_id', $activity->id)
            ->where('session_status', 'completed')
            ->count();

        // Simulate realistic attendance (70-95% for most trainees)
        $attendanceRate = rand(70, 95) / 100;
        $attendedSessions = min($completedSessions, (int)ceil($completedSessions * $attendanceRate));

        $progressPercentage = $totalSessions > 0 ? round(($attendedSessions / $totalSessions) * 100, 2) : 0;

        if ($futureSessions === 0 && $completedSessions > 0) {
            // Activity is completed - no more future sessions
            $lastSessionDate = DB::table('activity_occurrences')
                ->where('activity_id', $activity->id)
                ->where('session_status', 'completed')
                ->orderBy('session_date', 'desc')
                ->value('session_date');

            return [
                'status' => 'completed',
                'enrollment_notes' => $this->generateEnrollmentNotes($activity->category, $trainee),
                'progress_percentage' => $progressPercentage,
                'completion_date' => $lastSessionDate,
                'completion_notes' => $this->generateCompletionNotes($activity->category, $progressPercentage),
                'attendance_count' => $attendedSessions
            ];
        } else {
            // Activity is ongoing
            return [
                'status' => 'enrolled',
                'enrollment_notes' => $this->generateEnrollmentNotes($activity->category, $trainee),
                'progress_percentage' => $progressPercentage,
                'completion_date' => null,
                'completion_notes' => null,
                'attendance_count' => $attendedSessions
            ];
        }
    }

    /**
     * Generate realistic enrollment notes
     */
    private function generateEnrollmentNotes($category, $trainee): ?string
    {
        if (rand(1, 100) <= 70) { // 70% chance of having enrollment notes
            $notes = [
                'Autism Spectrum Support' => [
                    'Trainee shows potential for social skill improvement',
                    'Recommended by assessment team for communication development',
                    'Parent requested enrollment for behavioral support',
                    'Assessment indicates good potential for progress'
                ],
                'Hearing Impairment' => [
                    'Requires sign language support during sessions',
                    'Has basic hearing aid - accommodation needed',
                    'Motivated to improve communication skills',
                    'Previous therapy shows positive response'
                ],
                'Visual Impairment' => [
                    'Needs braille materials and assistive technology',
                    'Excellent tactile learning potential identified',
                    'Requires mobility orientation support',
                    'Family very supportive of independence goals'
                ],
                'Physical Disabilities' => [
                    'Physiotherapy assessment completed - good prognosis',
                    'Motivated for motor skill improvement',
                    'Requires adaptive equipment during sessions',
                    'Previous therapy shows steady progress'
                ],
                'Learning Support' => [
                    'Academic assessment shows specific learning needs',
                    'Responds well to structured learning environment',
                    'Teacher recommendation for additional support',
                    'Family committed to educational goals'
                ],
                'Speech Therapy' => [
                    'Speech assessment indicates articulation needs',
                    'Motivated to improve communication clarity',
                    'Previous sessions show good compliance',
                    'Family practices exercises at home'
                ]
            ];

            $categoryNotes = $notes[$category] ?? ['General enrollment - standard admission criteria met'];
            return $categoryNotes[array_rand($categoryNotes)];
        }

        return null;
    }

    /**
     * Generate realistic completion notes
     */
    private function generateCompletionNotes($category, $progressPercentage): ?string
    {
        if (rand(1, 100) <= 80) { // 80% chance of having completion notes
            if ($progressPercentage >= 90) {
                $excellentNotes = [
                    'Excellent progress achieved - all objectives met',
                    'Outstanding attendance and participation throughout',
                    'Significant improvement in targeted skills',
                    'Ready for advanced level programs'
                ];
                return $excellentNotes[array_rand($excellentNotes)];
            } elseif ($progressPercentage >= 70) {
                $goodNotes = [
                    'Good progress made - most objectives achieved',
                    'Consistent attendance with positive outcomes',
                    'Noticeable improvement in key areas',
                    'Recommend continued similar programs'
                ];
                return $goodNotes[array_rand($goodNotes)];
            } elseif ($progressPercentage >= 50) {
                $moderateNotes = [
                    'Moderate progress - some objectives met',
                    'Irregular attendance affected overall progress',
                    'Shows potential with more consistent participation',
                    'May benefit from modified approach next time'
                ];
                return $moderateNotes[array_rand($moderateNotes)];
            } else {
                $concernNotes = [
                    'Limited progress due to poor attendance',
                    'Recommend reassessment of suitability',
                    'May need different intervention approach',
                    'Family support needed for better outcomes'
                ];
                return $concernNotes[array_rand($concernNotes)];
            }
        }

        return null;
    }

    private function ensureMinimumEnrollments($activities, $trainees): void
    {
        $additionalEnrollments = 0;

        foreach ($activities as $activity) {
            $currentEnrollments = DB::table('activity_enrollments')
                ->where('activity_id', $activity->id)
                ->count();

            if ($currentEnrollments < 3) {
                $needed = 3 - $currentEnrollments;

                // Get trainees not already enrolled in this activity
                $enrolledTraineeIds = DB::table('activity_enrollments')
                    ->where('activity_id', $activity->id)
                    ->pluck('trainee_id')
                    ->toArray();

                $availableTrainees = $trainees->whereNotIn('id', $enrolledTraineeIds)
                    ->shuffle()
                    ->take($needed);

                foreach ($availableTrainees as $trainee) {
                    // Calculate enrollment details for additional trainees too
                    $enrollmentDetails = $this->calculateEnrollmentDetails($activity, $trainee);

                    DB::table('activity_enrollments')->insert([
                        'activity_id' => $activity->id,
                        'trainee_id' => $trainee->id,
                        'enrollment_date' => Carbon::parse($activity->created_at)->format('Y-m-d'),
                        'enrollment_status' => $enrollmentDetails['status'],
                        'enrollment_notes' => $enrollmentDetails['enrollment_notes'],
                        'progress_percentage' => $enrollmentDetails['progress_percentage'],
                        'completion_date' => $enrollmentDetails['completion_date'],
                        'completion_notes' => $enrollmentDetails['completion_notes'],
                        'attendance_count' => $enrollmentDetails['attendance_count'],
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);

                    $additionalEnrollments++;
                }
            }
        }

        if ($additionalEnrollments > 0) {
            $this->command->line("      ✓ Added {$additionalEnrollments} additional enrollments to ensure minimum 3 trainees per activity");
        }
    }
}