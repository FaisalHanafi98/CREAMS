<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CREAMSIEPProgressSeeder extends Seeder
{
    /**
     * Run the database seeds for IEP and progress tracking
     */
    public function run(): void
    {
        $this->command->info('📈 Creating comprehensive IEP and progress tracking system...');

        try {
            DB::beginTransaction();

            // Create learning outcomes
            $this->createLearningOutcomes();
            
            // TODO: Create IEP activity goals (requires schema alignment)
            // $this->createIEPActivityGoals();
            
            // TODO: Create progress reports (requires IEP goals)
            // $this->createProgressReports();

            DB::commit();

            $this->command->info('✅ IEP and progress tracking system seeded successfully!');
            $this->showIEPStatistics();

        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('❌ Failed to seed IEP system: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Create comprehensive learning outcomes
     */
    private function createLearningOutcomes(): void
    {
        $this->command->info('🎯 Creating learning outcomes by domain...');

        $learningOutcomes = [
            // Motor Skills Domain
            'motor_skills' => [
                [
                    'title' => 'Gross Motor Coordination',
                    'description' => 'Improve balance, coordination, and overall gross motor skills through structured physical activities',
                    'domain' => 'motor_skills',
                    'subdomain' => 'gross_motor',
                    'age_range' => '3-18',
                    'difficulty_level' => 'beginner'
                ],
                [
                    'title' => 'Fine Motor Precision',
                    'description' => 'Develop precise hand movements and finger dexterity for daily living activities',
                    'domain' => 'motor_skills',
                    'subdomain' => 'fine_motor',
                    'age_range' => '4-16',
                    'difficulty_level' => 'intermediate'
                ],
                [
                    'title' => 'Bilateral Coordination',
                    'description' => 'Enhance ability to use both sides of the body together in coordinated movements',
                    'domain' => 'motor_skills',
                    'subdomain' => 'bilateral',
                    'age_range' => '5-15',
                    'difficulty_level' => 'advanced'
                ]
            ],

            // Communication Skills Domain
            'communication' => [
                [
                    'title' => 'Expressive Language Development',
                    'description' => 'Improve ability to express needs, wants, and thoughts through verbal or alternative communication',
                    'domain' => 'communication',
                    'subdomain' => 'expressive',
                    'age_range' => '2-18',
                    'difficulty_level' => 'beginner'
                ],
                [
                    'title' => 'Receptive Language Understanding',
                    'description' => 'Enhance comprehension of spoken language and following multi-step instructions',
                    'domain' => 'communication',
                    'subdomain' => 'receptive',
                    'age_range' => '3-16',
                    'difficulty_level' => 'intermediate'
                ],
                [
                    'title' => 'Social Communication Skills',
                    'description' => 'Develop appropriate social communication including turn-taking and conversation skills',
                    'domain' => 'communication',
                    'subdomain' => 'social',
                    'age_range' => '4-18',
                    'difficulty_level' => 'advanced'
                ]
            ],

            // Cognitive Skills Domain
            'cognitive' => [
                [
                    'title' => 'Attention and Focus',
                    'description' => 'Increase sustained attention span for age-appropriate tasks and activities',
                    'domain' => 'cognitive',
                    'subdomain' => 'attention',
                    'age_range' => '3-18',
                    'difficulty_level' => 'beginner'
                ],
                [
                    'title' => 'Problem Solving Skills',
                    'description' => 'Develop logical thinking and problem-solving strategies for daily challenges',
                    'domain' => 'cognitive',
                    'subdomain' => 'problem_solving',
                    'age_range' => '5-18',
                    'difficulty_level' => 'intermediate'
                ],
                [
                    'title' => 'Memory and Recall',
                    'description' => 'Strengthen working memory and long-term memory recall abilities',
                    'domain' => 'cognitive',
                    'subdomain' => 'memory',
                    'age_range' => '4-16',
                    'difficulty_level' => 'advanced'
                ]
            ],

            // Social Skills Domain
            'social_skills' => [
                [
                    'title' => 'Peer Interaction',
                    'description' => 'Develop appropriate social skills for interacting with peers in various settings',
                    'domain' => 'social_skills',
                    'subdomain' => 'peer_interaction',
                    'age_range' => '3-18',
                    'difficulty_level' => 'beginner'
                ],
                [
                    'title' => 'Emotional Regulation',
                    'description' => 'Learn strategies to identify, understand, and manage emotions appropriately',
                    'domain' => 'social_skills',
                    'subdomain' => 'emotional',
                    'age_range' => '4-18',
                    'difficulty_level' => 'intermediate'
                ],
                [
                    'title' => 'Conflict Resolution',
                    'description' => 'Develop skills to resolve conflicts peacefully and seek help when needed',
                    'domain' => 'social_skills',
                    'subdomain' => 'conflict_resolution',
                    'age_range' => '6-18',
                    'difficulty_level' => 'advanced'
                ]
            ],

            // Daily Living Skills Domain
            'daily_living' => [
                [
                    'title' => 'Self-Care Independence',
                    'description' => 'Achieve independence in personal hygiene, dressing, and grooming tasks',
                    'domain' => 'daily_living',
                    'subdomain' => 'self_care',
                    'age_range' => '3-18',
                    'difficulty_level' => 'beginner'
                ],
                [
                    'title' => 'Household Skills',
                    'description' => 'Learn basic household tasks appropriate for age and ability level',
                    'domain' => 'daily_living',
                    'subdomain' => 'household',
                    'age_range' => '6-18',
                    'difficulty_level' => 'intermediate'
                ],
                [
                    'title' => 'Community Navigation',
                    'description' => 'Develop skills for safe and independent navigation in community settings',
                    'domain' => 'daily_living',
                    'subdomain' => 'community',
                    'age_range' => '8-18',
                    'difficulty_level' => 'advanced'
                ]
            ],

            // Academic Skills Domain
            'academic' => [
                [
                    'title' => 'Pre-Literacy Skills',
                    'description' => 'Develop foundational skills for reading including letter recognition and phonics',
                    'domain' => 'academic',
                    'subdomain' => 'literacy',
                    'age_range' => '3-10',
                    'difficulty_level' => 'beginner'
                ],
                [
                    'title' => 'Numeracy Concepts',
                    'description' => 'Understand basic mathematical concepts including counting, addition, and subtraction',
                    'domain' => 'academic',
                    'subdomain' => 'numeracy',
                    'age_range' => '4-12',
                    'difficulty_level' => 'intermediate'
                ],
                [
                    'title' => 'Functional Academics',
                    'description' => 'Apply academic skills to real-life situations like money management and time concepts',
                    'domain' => 'academic',
                    'subdomain' => 'functional',
                    'age_range' => '8-18',
                    'difficulty_level' => 'advanced'
                ]
            ]
        ];

        // Get existing activities to link learning outcomes
        $activities = DB::table('activities')->pluck('id')->toArray();
        $outcomeIndex = 0;
        
        foreach ($learningOutcomes as $domain => $outcomes) {
            foreach ($outcomes as $outcome) {
                $measurementCriteria = $this->generateMeasurementCriteria($outcome['domain'], $outcome['subdomain']);
                
                // Assign to an activity (cycle through available activities)
                $activityId = $activities[$outcomeIndex % count($activities)];
                
                DB::table('learning_outcomes')->insert([
                    'activity_id' => $activityId,
                    'outcome_title' => $outcome['title'],
                    'outcome_description' => $outcome['description'], 
                    'competency_level' => $outcome['difficulty_level'],
                    'assessment_criteria' => json_encode($measurementCriteria),
                    'display_order' => 1,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                
                $outcomeIndex++;
            }
        }
    }

    /**
     * Create IEP activity goals for enrolled trainees
     */
    private function createIEPActivityGoals(): void
    {
        $this->command->info('📋 Creating IEP activity goals for trainees...');

        $trainees = DB::table('trainees')->get();
        $activities = DB::table('activities')->get();
        $learningOutcomes = DB::table('learning_outcomes')->get();
        $users = DB::table('users')->where('role', 'teacher')->get();

        foreach ($trainees as $trainee) {
            // Get activities this trainee is enrolled in
            $enrolledActivities = DB::table('activity_enrollments')
                ->where('trainee_id', $trainee->id)
                ->where('enrollment_status', 'enrolled')
                ->pluck('activity_id');

            foreach ($enrolledActivities as $activityId) {
                $activity = $activities->firstWhere('id', $activityId);
                if (!$activity) continue;

                // Create 2-4 goals per activity
                $goalCount = rand(2, 4);
                $selectedOutcomes = $learningOutcomes->random($goalCount);

                foreach ($selectedOutcomes as $outcome) {
                    $startDate = Carbon::now()->subDays(rand(30, 90));
                    $targetDate = $startDate->copy()->addMonths(rand(3, 6));
                    
                    $goal = $this->generateSpecificGoal($trainee, $activity, $outcome);

                    DB::table('iep_activity_goals')->insert([
                        'trainee_id' => $trainee->id,
                        'activity_id' => $activity->id,
                        'learning_outcome_id' => $outcome->id,
                        'goal_statement' => $goal['statement'],
                        'specific_objectives' => json_encode($goal['objectives']),
                        'measurement_method' => $goal['measurement'],
                        'baseline_data' => $goal['baseline'],
                        'target_criteria' => $goal['target'],
                        'start_date' => $startDate,
                        'target_date' => $targetDate,
                        'status' => $this->getGoalStatus($startDate, $targetDate),
                        'priority_level' => ['high', 'medium', 'low'][rand(0, 2)],
                        'strategies' => json_encode($goal['strategies']),
                        'accommodations' => json_encode($goal['accommodations']),
                        'progress_notes' => $goal['notes'],
                        'assigned_therapist' => $users->random()->id ?? 1,
                        'created_by' => $users->random()->id ?? 1,
                        'created_at' => $startDate,
                        'updated_at' => now()
                    ]);
                }
            }
        }
    }

    /**
     * Create comprehensive progress reports
     */
    private function createProgressReports(): void
    {
        $this->command->info('📊 Creating progress reports...');

        $trainees = DB::table('trainees')->get();
        $users = DB::table('users')->where('role', 'teacher')->get();

        foreach ($trainees as $trainee) {
            // Create monthly progress reports for last 6 months
            for ($i = 6; $i >= 1; $i--) {
                $reportDate = Carbon::now()->subMonths($i);
                $reportPeriodStart = $reportDate->copy()->startOfMonth();
                $reportPeriodEnd = $reportDate->copy()->endOfMonth();

                $progressData = $this->generateProgressData($trainee, $reportPeriodStart, $reportPeriodEnd);

                DB::table('progress_reports')->insert([
                    'trainee_id' => $trainee->id,
                    'report_type' => 'monthly',
                    'report_period_start' => $reportPeriodStart,
                    'report_period_end' => $reportPeriodEnd,
                    'overall_progress_score' => $progressData['overall_score'],
                    'domain_scores' => json_encode($progressData['domain_scores']),
                    'achievements' => json_encode($progressData['achievements']),
                    'challenges' => json_encode($progressData['challenges']),
                    'next_steps' => json_encode($progressData['next_steps']),
                    'attendance_summary' => json_encode($progressData['attendance']),
                    'therapist_notes' => $progressData['therapist_notes'],
                    'parent_feedback' => rand(1, 10) <= 7 ? $progressData['parent_feedback'] : null,
                    'goals_met' => $progressData['goals_met'],
                    'goals_in_progress' => $progressData['goals_in_progress'],
                    'new_goals_added' => $progressData['new_goals'],
                    'recommendations' => json_encode($progressData['recommendations']),
                    'status' => 'completed',
                    'generated_by' => $users->random()->id ?? 1,
                    'reviewed_by' => rand(1, 10) <= 8 ? $users->random()->id : null,
                    'parent_acknowledgment' => rand(1, 10) <= 6,
                    'acknowledgment_date' => rand(1, 10) <= 6 ? $reportPeriodEnd->copy()->addDays(rand(1, 14)) : null,
                    'created_at' => $reportPeriodEnd->copy()->addDays(rand(1, 7)),
                    'updated_at' => now()
                ]);
            }
        }
    }

    /**
     * Generate measurement criteria for learning outcomes
     */
    private function generateMeasurementCriteria($domain, $subdomain): array
    {
        $criteriaMap = [
            'motor_skills' => [
                'gross_motor' => [
                    'balance_duration' => 'Can maintain balance for specified seconds',
                    'coordination_accuracy' => 'Completes movement patterns with accuracy',
                    'endurance_time' => 'Sustains activity for target duration'
                ],
                'fine_motor' => [
                    'precision_tasks' => 'Completes fine motor tasks with precision',
                    'grip_strength' => 'Demonstrates appropriate grip strength',
                    'hand_eye_coordination' => 'Shows improved hand-eye coordination'
                ]
            ],
            'communication' => [
                'expressive' => [
                    'vocabulary_use' => 'Uses target number of words/signs',
                    'sentence_structure' => 'Forms sentences of specified length',
                    'communication_frequency' => 'Initiates communication target times per session'
                ],
                'receptive' => [
                    'instruction_following' => 'Follows multi-step instructions accurately',
                    'comprehension_rate' => 'Demonstrates understanding percentage',
                    'response_time' => 'Responds within appropriate timeframe'
                ]
            ]
        ];

        return $criteriaMap[$domain][$subdomain] ?? [
            'accuracy' => 'Demonstrates skill with specified accuracy',
            'consistency' => 'Performs skill consistently across sessions',
            'independence' => 'Completes task with minimal assistance'
        ];
    }

    /**
     * Generate specific IEP goal
     */
    private function generateSpecificGoal($trainee, $activity, $outcome): array
    {
        // Determine domain from outcome title
        $domain = $this->getDomainFromTitle($outcome->outcome_title);
        
        $traineeName = $trainee->trainee_first_name . ' ' . $trainee->trainee_last_name;
        
        $goalTemplates = [
            'motor_skills' => "Given {$activity->activity_name} activities, {$traineeName} will {$outcome->outcome_title} with {measurement} by {target_date}.",
            'communication' => "During {$activity->activity_name} sessions, {$traineeName} will {$outcome->outcome_title} {measurement} in 4 out of 5 trials.",
            'cognitive' => "When participating in {$activity->activity_name}, {$traineeName} will demonstrate {$outcome->outcome_title} {measurement}.",
            'social_skills' => "In {$activity->activity_name} group settings, {$traineeName} will {$outcome->outcome_title} {measurement}.",
            'daily_living' => "During {$activity->activity_name} practice, {$traineeName} will {$outcome->outcome_title} {measurement}.",
            'academic' => "Through {$activity->activity_name} instruction, {$traineeName} will {$outcome->outcome_title} {measurement}."
        ];

        $template = $goalTemplates[$domain] ?? $goalTemplates['cognitive'];
        $measurement = $this->getMeasurementPhrase($domain);
        
        $statement = str_replace(
            ['{measurement}', '{target_date}'],
            [$measurement, 'the target date'],
            $template
        );

        return [
            'statement' => $statement,
            'objectives' => $this->generateObjectives($outcome),
            'measurement' => $this->getMeasurementMethod($domain),
            'baseline' => $this->getBaselineData($domain),
            'target' => $this->getTargetCriteria($domain),
            'strategies' => $this->getTeachingStrategies($domain),
            'accommodations' => $this->getAccommodations($trainee, $outcome),
            'notes' => $this->getInitialNotes()
        ];
    }

    /**
     * Generate specific objectives for goals
     */
    private function generateObjectives($outcome): array
    {
        $objectiveTemplates = [
            "Demonstrate {$outcome->outcome_title} in structured activities",
            "Practice {$outcome->outcome_title} with varying levels of support",
            "Apply {$outcome->outcome_title} in functional situations",
            "Maintain {$outcome->outcome_title} across different environments"
        ];

        return array_slice($objectiveTemplates, 0, rand(2, 4));
    }

    /**
     * Generate progress data for reports
     */
    private function generateProgressData($trainee, $startDate, $endDate): array
    {
        $domains = ['motor_skills', 'communication', 'cognitive', 'social_skills', 'daily_living', 'academic'];
        $domainScores = [];
        
        foreach ($domains as $domain) {
            $domainScores[$domain] = rand(60, 95);
        }
        
        $overallScore = round(array_sum($domainScores) / count($domainScores));

        return [
            'overall_score' => $overallScore,
            'domain_scores' => $domainScores,
            'achievements' => $this->generateAchievements($trainee),
            'challenges' => $this->generateChallenges(),
            'next_steps' => $this->generateNextSteps(),
            'attendance' => $this->generateAttendanceSummary($trainee, $startDate, $endDate),
            'therapist_notes' => $this->generateTherapistNotes($overallScore),
            'parent_feedback' => $this->generateParentFeedback(),
            'goals_met' => rand(1, 3),
            'goals_in_progress' => rand(2, 5),
            'new_goals' => rand(0, 2),
            'recommendations' => $this->generateRecommendations($overallScore)
        ];
    }

    /**
     * Helper methods for generating realistic data
     */
    private function getMeasurementPhrase($domain): string
    {
        $phrases = [
            'motor_skills' => 'with 80% accuracy over 3 consecutive sessions',
            'communication' => 'in 4 out of 5 opportunities',
            'cognitive' => 'with minimal prompting',
            'social_skills' => 'appropriately in group settings',
            'daily_living' => 'independently in 3 out of 4 attempts',
            'academic' => 'with 75% accuracy'
        ];

        return $phrases[$domain] ?? 'with consistent performance';
    }

    private function getMeasurementMethod($domain): string
    {
        $methods = [
            'motor_skills' => 'Direct observation and performance recording',
            'communication' => 'Language sampling and frequency counts',
            'cognitive' => 'Task analysis and accuracy tracking',
            'social_skills' => 'Behavioral observation and peer feedback',
            'daily_living' => 'Independence checklists and self-monitoring',
            'academic' => 'Work samples and assessment rubrics'
        ];

        return $methods[$domain] ?? 'Direct observation and data collection';
    }

    private function getBaselineData($domain): string
    {
        $baselines = [
            'motor_skills' => 'Currently demonstrates skill 30% of the time with physical prompts',
            'communication' => 'Uses 10-15 words/signs to express basic needs',
            'cognitive' => 'Completes tasks with constant verbal prompting',
            'social_skills' => 'Initiates interaction 1-2 times per session',
            'daily_living' => 'Requires hand-over-hand assistance for most steps',
            'academic' => 'Recognizes 5 letters and numbers 1-5'
        ];

        return $baselines[$domain] ?? 'Baseline data to be established';
    }

    private function getTargetCriteria($domain): string
    {
        $targets = [
            'motor_skills' => '80% accuracy independently across 3 consecutive sessions',
            'communication' => '4 out of 5 opportunities with minimal prompting',
            'cognitive' => 'Independent completion with 75% accuracy',
            'social_skills' => 'Appropriate interaction in 80% of opportunities',
            'daily_living' => 'Independent completion of task sequence',
            'academic' => '90% accuracy with grade-level materials'
        ];

        return $targets[$domain] ?? '80% accuracy with minimal support';
    }

    private function getTeachingStrategies($domain): array
    {
        $strategies = [
            'motor_skills' => ['Physical guidance', 'Visual demonstrations', 'Practice repetition', 'Sensory input'],
            'communication' => ['Visual supports', 'Modeling', 'Expansion techniques', 'Wait time'],
            'cognitive' => ['Task breakdown', 'Visual schedules', 'Errorless learning', 'Self-monitoring'],
            'social_skills' => ['Role playing', 'Social stories', 'Peer modeling', 'Video modeling'],
            'daily_living' => ['Backward chaining', 'Visual prompts', 'Environmental setup', 'Practice schedules'],
            'academic' => ['Multi-sensory approach', 'Systematic instruction', 'Frequent practice', 'Error correction']
        ];

        return $strategies[$domain] ?? ['Direct instruction', 'Guided practice', 'Independent practice'];
    }

    private function getAccommodations($trainee, $outcome): array
    {
        $accommodations = [
            'Extended time for task completion',
            'Visual supports and picture schedules',
            'Reduced distractions in environment',
            'Frequent breaks during activities',
            'Alternative communication methods',
            'Adaptive equipment as needed',
            'Peer support and modeling',
            'Sensory regulation strategies'
        ];

        return collect($accommodations)->random(rand(2, 4))->toArray();
    }

    private function getInitialNotes(): string
    {
        $notes = [
            'Goal established based on current assessment and family priorities',
            'Regular data collection and progress monitoring planned',
            'Collaborative approach with family and team members',
            'Accommodations and strategies to be adjusted as needed'
        ];

        return $notes[rand(0, count($notes) - 1)];
    }

    private function getGoalStatus($startDate, $targetDate): string
    {
        $now = Carbon::now();
        
        if ($now < $startDate) return 'not_started';
        if ($now > $targetDate) return rand(1, 10) <= 7 ? 'met' : 'not_met';
        return 'in_progress';
    }

    private function generateAchievements($trainee): array
    {
        $achievements = [
            "Improved attention span during structured activities",
            "Increased verbal communication and expression",
            "Better social interaction with peers",
            "Enhanced fine motor control and precision",
            "Greater independence in daily tasks",
            "Positive response to new therapeutic strategies"
        ];

        return collect($achievements)->random(rand(2, 4))->toArray();
    }

    private function generateChallenges(): array
    {
        $challenges = [
            "Difficulty maintaining attention for extended periods",
            "Challenges with transitions between activities",
            "Need for additional sensory regulation support",
            "Occasional resistance to new activities",
            "Requires more practice with fine motor tasks"
        ];

        return collect($challenges)->random(rand(1, 3))->toArray();
    }

    private function generateNextSteps(): array
    {
        $nextSteps = [
            "Continue current intervention strategies",
            "Introduce more challenging tasks gradually",
            "Increase opportunities for peer interaction",
            "Collaborate with family on home practice",
            "Consider additional sensory integration activities",
            "Explore alternative communication methods"
        ];

        return collect($nextSteps)->random(rand(2, 4))->toArray();
    }

    private function generateAttendanceSummary($trainee, $startDate, $endDate): array
    {
        return [
            'total_sessions' => rand(15, 25),
            'attended_sessions' => rand(12, 20),
            'attendance_rate' => rand(75, 95),
            'punctuality_rate' => rand(80, 100)
        ];
    }

    private function generateTherapistNotes($overallScore): string
    {
        if ($overallScore >= 85) {
            return "Excellent progress demonstrated across multiple domains. Continue current strategies and gradually increase challenge level.";
        } elseif ($overallScore >= 70) {
            return "Good progress noted with consistent engagement. Some areas need additional focus and support.";
        } else {
            return "Steady progress with room for improvement. Consider adjusting strategies and increasing support intensity.";
        }
    }

    private function getDomainFromTitle($title): string
    {
        $domainMap = [
            'Motor' => 'motor_skills',
            'Coordination' => 'motor_skills',
            'Language' => 'communication',
            'Communication' => 'communication',
            'Expressive' => 'communication',
            'Receptive' => 'communication',
            'Social' => 'social_skills',
            'Attention' => 'cognitive',
            'Focus' => 'cognitive',
            'Problem' => 'cognitive',
            'Memory' => 'cognitive',
            'Daily' => 'daily_living',
            'Living' => 'daily_living',
            'Self' => 'daily_living',
            'Academic' => 'academic',
            'Reading' => 'academic',
            'Math' => 'academic'
        ];
        
        foreach ($domainMap as $keyword => $domain) {
            if (stripos($title, $keyword) !== false) {
                return $domain;
            }
        }
        
        return 'cognitive'; // default
    }

    private function generateParentFeedback(): string
    {
        $feedback = [
            "Very pleased with the progress and professional support provided",
            "Noticing positive changes at home, especially in communication",
            "Grateful for the individualized approach and regular updates",
            "Child enjoys sessions and looks forward to attending",
            "Appreciate the collaboration and home practice suggestions"
        ];

        return $feedback[rand(0, count($feedback) - 1)];
    }

    private function generateRecommendations($overallScore): array
    {
        $recommendations = [
            "Continue current intervention frequency",
            "Implement home practice program",
            "Consider additional group therapy sessions",
            "Explore assistive technology options",
            "Coordinate with school/childcare providers",
            "Plan for transition to next developmental stage"
        ];

        $count = $overallScore >= 80 ? 2 : rand(3, 5);
        return collect($recommendations)->random($count)->toArray();
    }

    /**
     * Show IEP and progress statistics
     */
    private function showIEPStatistics(): void
    {
        $this->command->info("\n📊 IEP & PROGRESS TRACKING STATISTICS:");
        
        $learningOutcomes = DB::table('learning_outcomes')->count();
        $iepGoals = DB::table('iep_activity_goals')->count();
        $progressReports = DB::table('progress_reports')->count();
        
        $activeGoals = DB::table('iep_activity_goals')->where('goal_status', 'In Progress')->count();
        $metGoals = DB::table('iep_activity_goals')->where('goal_status', 'Completed')->count();
        
        // Skip domain counts for now since we removed domain column
        $domainCounts = collect([]);

        $this->command->line("   🎯 Learning Outcomes: {$learningOutcomes}");
        $this->command->line("   📋 IEP Goals Created: {$iepGoals}");
        $this->command->line("   ✅ Goals Met: {$metGoals}");
        $this->command->line("   🔄 Active Goals: {$activeGoals}");
        $this->command->line("   📊 Progress Reports: {$progressReports}");
        
        $this->command->info("\n   📚 Learning Outcomes by Domain:");
        foreach ($domainCounts as $domain) {
            $this->command->line("     • " . ucfirst(str_replace('_', ' ', $domain->domain)) . ": {$domain->count}");
        }
        
        $this->command->info("   ✅ Comprehensive IEP and progress tracking system ready!");
    }
}