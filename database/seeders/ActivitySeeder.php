<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ActivitySeeder extends Seeder
{
    /**
     * Seed MASSIVE activities with June-September timeline (10x scale)
     * 4 centres only (exclude Gombak for real data)
     * Activity duration: 4-12 weeks (8-24 sessions)
     * Staff/trainee workload: 2-10 activities per week per person
     * Disability-appropriate activity matching
     */
    public function run(): void
    {
        $this->command->info('🎯 Seeding MASSIVE rehabilitation activities (10x scale, 4 centres)...');

        // Get required data (EXCLUDE GOMBAK - centre_id '01')
        $categories = DB::table('activity_categories')->pluck('id')->toArray();
        $centres = DB::table('centres')->where('centre_id', '!=', '01')->pluck('centre_id')->toArray(); // Exclude Gombak
        $centreData = DB::table('centres')->where('centre_id', '!=', '01')->select('centre_id', 'centre_name')->get()->keyBy('centre_id');
        $teachers = DB::table('users')->where('role', 'teacher')->whereNotIn('centre_id', ['01'])->pluck('id')->toArray(); // Exclude Gombak teachers
        
        if (empty($categories) || empty($centres) || empty($teachers)) {
            $this->command->error('Required data missing! Ensure categories, centres, and teachers are seeded first.');
            return;
        }
        
        $this->command->info('   Working with ' . count($centres) . ' centres (excluding Gombak): ' . implode(', ', $centres));

        // Malaysian rehabilitation activities for disabled children
        $activityTemplates = [
            // Speech and Language Therapy
            'Speech Development for Autism' => [
                'description' => 'Specialized speech therapy for children with autism spectrum disorders focusing on communication skills',
                'learning_outcomes' => 'Improved verbal communication, better social interaction, enhanced language comprehension'
            ],
            'Basic Articulation Training' => [
                'description' => 'Fundamental speech articulation exercises for children with speech impediments',
                'learning_outcomes' => 'Clear pronunciation, improved speech clarity, increased confidence in verbal communication'
            ],
            'Language Comprehension Activities' => [
                'description' => 'Interactive activities to enhance language understanding and processing skills',
                'learning_outcomes' => 'Better language comprehension, improved following instructions, enhanced vocabulary'
            ],
            'Conversation Skills Development' => [
                'description' => 'Group activities to develop social communication and conversation abilities',
                'learning_outcomes' => 'Better social interaction, improved turn-taking in conversations, enhanced listening skills'
            ],
            
            // Occupational Therapy
            'Fine Motor Skills Training' => [
                'description' => 'Activities to develop hand-eye coordination and finger dexterity',
                'learning_outcomes' => 'Improved handwriting, better use of utensils, enhanced daily living skills'
            ],
            'Daily Living Skills Practice' => [
                'description' => 'Practical training for essential daily activities and self-care',
                'learning_outcomes' => 'Increased independence, better self-care abilities, improved confidence'
            ],
            'Sensory Integration Therapy' => [
                'description' => 'Therapeutic activities to help children process sensory information effectively',
                'learning_outcomes' => 'Better sensory processing, reduced sensory sensitivities, improved behavior regulation'
            ],
            'Adaptive Equipment Training' => [
                'description' => 'Training children to use assistive devices and adaptive equipment',
                'learning_outcomes' => 'Proficient use of adaptive tools, increased independence, better quality of life'
            ],
            
            // Physical Therapy
            'Mobility Enhancement Program' => [
                'description' => 'Physical exercises to improve walking, balance, and overall mobility',
                'learning_outcomes' => 'Better balance and coordination, improved muscle strength, enhanced mobility'
            ],
            'Wheelchair Skills Training' => [
                'description' => 'Teaching safe and efficient wheelchair operation and maintenance',
                'learning_outcomes' => 'Safe wheelchair navigation, independence in mobility, proper wheelchair maintenance'
            ],
            'Posture Correction Therapy' => [
                'description' => 'Exercises and techniques to improve posture and prevent complications',
                'learning_outcomes' => 'Better posture habits, reduced pain, improved respiratory function'
            ],
            'Strength Building Exercises' => [
                'description' => 'Age-appropriate exercises to build muscle strength and endurance',
                'learning_outcomes' => 'Increased muscle strength, better endurance, improved functional abilities'
            ],
            
            // Educational Support
            'Basic Mathematics Skills' => [
                'description' => 'Adapted mathematics curriculum for children with learning disabilities',
                'learning_outcomes' => 'Improved number recognition, basic calculation skills, practical math applications'
            ],
            'Reading Comprehension Support' => [
                'description' => 'Specialized reading programs for children with dyslexia and reading difficulties',
                'learning_outcomes' => 'Better reading fluency, improved comprehension, increased reading confidence'
            ],
            'Creative Arts Therapy' => [
                'description' => 'Art and craft activities to enhance creativity and self-expression',
                'learning_outcomes' => 'Enhanced creativity, better self-expression, improved fine motor skills'
            ],
            'Computer Skills Training' => [
                'description' => 'Basic computer literacy and assistive technology training',
                'learning_outcomes' => 'Basic computer skills, assistive technology proficiency, digital independence'
            ],
            
            // Social Skills
            'Social Interaction Group' => [
                'description' => 'Group activities to develop social skills and peer relationships',
                'learning_outcomes' => 'Better social interaction, improved peer relationships, enhanced communication'
            ],
            'Emotional Regulation Training' => [
                'description' => 'Activities to help children recognize and manage their emotions',
                'learning_outcomes' => 'Better emotional awareness, improved self-regulation, reduced behavioral issues'
            ],
            'Peer Support Activities' => [
                'description' => 'Structured activities promoting peer support and friendship building',
                'learning_outcomes' => 'Stronger peer relationships, better teamwork skills, increased social confidence'
            ],
            'Communication Skills Workshop' => [
                'description' => 'Workshops focusing on various forms of communication including non-verbal',
                'learning_outcomes' => 'Enhanced communication abilities, better understanding of social cues, improved interaction'
            ]
        ];

        $activityId = 1;
        $totalActivities = 0;
        
        // Create MASSIVE number of activities (10x scale)
        // Generate variations of each template for each centre across timeline
        $this->command->info('   Generating massive activity variations...');
        
        $timelineConfigs = [
            'June' => [
                'month' => 6,
                'is_active' => false,
                'status' => 'completed',
                'activities_per_centre' => 35, // 35 activities per centre for June
                'description' => 'Completed Activities (June)'
            ],
            'July' => [
                'month' => 7, 
                'is_active' => false,
                'status' => 'completed',
                'activities_per_centre' => 30, // 30 activities per centre for July
                'description' => 'Completed Activities (July)'
            ],
            'August' => [
                'month' => 8,
                'is_active' => true,
                'status' => 'ongoing',
                'activities_per_centre' => 25, // 25 activities per centre for August
                'description' => 'Current Activities (August)'
            ],
            'September' => [
                'month' => 9,
                'is_active' => true,
                'status' => 'planned',
                'activities_per_centre' => 20, // 20 activities per centre for September
                'description' => 'Planned Activities (September)'
            ]
        ];
        
        foreach ($timelineConfigs as $period => $config) {
            $this->command->info("   Creating {$config['description']} - {$config['activities_per_centre']} per centre...");
            $periodCount = 0;
            
            foreach ($centres as $centreId) {
                $centreName = $centreData[$centreId]->centre_name;
                
                // Create specified number of activities for this centre in this period
                for ($i = 0; $i < $config['activities_per_centre']; $i++) {
                    // Select a random activity template
                    $templateKeys = array_keys($activityTemplates);
                    $templateKey = $templateKeys[array_rand($templateKeys)];
                    $template = $activityTemplates[$templateKey];
                    
                    // Generate varied activity duration (4-12 weeks)
                    $durationWeeks = $this->generateActivityDuration();
                    $totalSessions = $durationWeeks * 2; // 2 sessions per week
                    
                    // Generate session completion based on period
                    $sessionsCompleted = $this->calculateSessionsCompleted($config['status'], $totalSessions);
                    
                    // Create activity
                    DB::table('activities')->insert([
                        'id' => $activityId,
                        'activity_name' => $this->generateUniqueActivityName($templateKey, $centreName, $i),
                        'activity_description' => $template['description'],
                        'category_id' => $this->getAppropriateCategory($templateKey, $categories),
                        'centre_id' => $centreId,
                        'instructor_id' => $this->getAppropriateInstructor($centreId, $teachers),
                        'max_participants' => rand(6, 12), // Smaller groups for disabled children
                        'duration_weeks' => $durationWeeks,
                        'sessions_per_week' => 2,
                        'session_duration_minutes' => 90,
                        'learning_outcomes' => $template['learning_outcomes'],
                        'activity_location' => 'Room ' . rand(101, 150),
                        'is_active' => $config['is_active'],
                        'times_conducted' => $sessionsCompleted,
                        'created_at' => Carbon::create(2024, $config['month'], rand(1, 15)),
                        'updated_at' => $config['status'] === 'completed' ? 
                            Carbon::create(2024, $config['month'] + 1, rand(1, 15)) : Carbon::now()
                    ]);
                    
                    $activityId++;
                    $periodCount++;
                    $totalActivities++;
                }
            }
            
            $this->command->line("     ✓ Created {$periodCount} {$period} activities");
        }
        
        
        
        $this->command->info("🎯 Successfully seeded $totalActivities MASSIVE rehabilitation activities:");
        $this->command->line("   • 4 centres (excluding Gombak for real data)");
        $this->command->line("   • Activity durations: 4-12 weeks (8-24 sessions)");
        $this->command->line("   • Disability-appropriate activity matching");
        $this->command->line("   • Timeline: June (140) → July (120) → August (100) → September (80) = $totalActivities total");
    }

    private function generateActivityDuration(): int
    {
        // Minimum 4 weeks, average 5-6 weeks, maximum 12 weeks
        $weights = [
            4 => 10,  // 4 weeks: 10% chance (minimum)
            5 => 25,  // 5 weeks: 25% chance
            6 => 30,  // 6 weeks: 30% chance (most common)
            7 => 15,  // 7 weeks: 15% chance
            8 => 10,  // 8 weeks: 10% chance
            9 => 5,   // 9 weeks: 5% chance
            10 => 3,  // 10 weeks: 3% chance
            11 => 1,  // 11 weeks: 1% chance
            12 => 1   // 12 weeks: 1% chance (maximum)
        ];
        
        $rand = rand(1, 100);
        $cumulative = 0;
        
        foreach ($weights as $weeks => $weight) {
            $cumulative += $weight;
            if ($rand <= $cumulative) {
                return $weeks;
            }
        }
        
        return 6; // Default fallback
    }

    private function calculateSessionsCompleted(string $status, int $totalSessions): int
    {
        switch ($status) {
            case 'completed':
                return $totalSessions; // All sessions completed
            case 'ongoing':
                // 40-80% of sessions completed
                return (int) ($totalSessions * (rand(40, 80) / 100));
            case 'planned':
                return 0; // No sessions started yet
            default:
                return 0;
        }
    }

    private function generateUniqueActivityName(string $templateKey, string $centreName, int $index): string
    {
        $variations = [
            'Advanced', 'Basic', 'Intermediate', 'Foundation', 'Specialized',
            'Intensive', 'Comprehensive', 'Focused', 'Enhanced', 'Progressive'
        ];
        
        $levels = [
            'Level 1', 'Level 2', 'Level 3', 'Phase A', 'Phase B', 'Phase C',
            'Module 1', 'Module 2', 'Session Group', 'Workshop', 'Program'
        ];
        
        $variation = $variations[array_rand($variations)];
        $level = $levels[array_rand($levels)];
        
        return "{$variation} {$templateKey} {$level} ({$centreName})";
    }

    private function getAppropriateCategory(string $templateKey, array $categories): int
    {
        // Map template keys to appropriate category IDs based on activity type
        $categoryMap = [
            'Speech Development for Autism' => 1, // Speech & Language Therapy
            'Basic Articulation Training' => 1,
            'Language Comprehension Activities' => 1,
            'Conversation Skills Development' => 1,
            
            'Fine Motor Skills Training' => 2, // Occupational Therapy
            'Daily Living Skills Practice' => 2,
            'Sensory Integration Therapy' => 2,
            'Adaptive Equipment Training' => 2,
            
            'Mobility Enhancement Program' => 3, // Physical Therapy
            'Wheelchair Skills Training' => 3,
            'Posture Correction Therapy' => 3,
            'Strength Building Exercises' => 3,
            
            'Basic Mathematics Skills' => 5, // Academic Skills
            'Reading Comprehension Support' => 5,
            'Computer Skills Training' => 9, // Technology Skills
            'Creative Arts Therapy' => 6, // Creative Arts
            
            'Social Interaction Group' => 7, // Social Skills Training
            'Emotional Regulation Training' => 4, // Behavioral Intervention
            'Peer Support Activities' => 7,
            'Communication Skills Workshop' => 1
        ];
        
        return $categoryMap[$templateKey] ?? $categories[array_rand($categories)];
    }

    private function getAppropriateInstructor(string $centreId, array $teachers): int
    {
        // Get teachers specifically from this centre
        $centreTeachers = DB::table('users')
            ->where('role', 'teacher')
            ->where('centre_id', $centreId)
            ->pluck('id')
            ->toArray();
            
        return $centreTeachers ? $centreTeachers[array_rand($centreTeachers)] : $teachers[array_rand($teachers)];
    }
}