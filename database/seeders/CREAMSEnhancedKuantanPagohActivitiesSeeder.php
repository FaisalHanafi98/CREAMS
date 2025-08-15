<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Activity;
use App\Models\ActivitySession;
use App\Models\ActivityEnrollment;
use App\Models\SessionEnrollment;
use App\Models\Trainee;
use App\Models\User;
use App\Models\Category;
use App\Models\Centre;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CREAMSEnhancedKuantanPagohActivitiesSeeder extends Seeder
{
    /**
     * Kuantan-specific activities focusing on autism spectrum disorders and developmental disabilities
     */
    private array $kuantanActivities = [
        // Autism Spectrum Disorder Specialized Programs (25 activities)
        'autism_intervention' => [
            'ABA Therapy - Basic Compliance Training',
            'ABA Therapy - Verbal Behavior Intervention',
            'ABA Therapy - Social Stories Implementation',
            'ABA Therapy - Task Analysis Training',
            'ABA Therapy - Discrete Trial Teaching',
            'TEACCH Method - Structured Learning',
            'TEACCH Method - Visual Schedules Training',
            'TEACCH Method - Work System Development',
            'TEACCH Method - Environmental Organization',
            'TEACCH Method - Independent Work Skills',
            'PECS Communication - Phase 1 Basic Exchange',
            'PECS Communication - Phase 2 Distance and Persistence',
            'PECS Communication - Phase 3 Picture Discrimination',
            'PECS Communication - Phase 4 Sentence Structure',
            'PECS Communication - Phase 5 Responsive Requesting',
            'Social Skills Training - Turn-Taking',
            'Social Skills Training - Eye Contact Development',
            'Social Skills Training - Greeting and Farewell',
            'Social Skills Training - Sharing and Cooperation',
            'Social Skills Training - Conversation Skills',
            'Sensory Integration - Deep Pressure Activities',
            'Sensory Integration - Proprioceptive Input',
            'Sensory Integration - Vestibular Activities',
            'Sensory Integration - Tactile Desensitization',
            'Sensory Integration - Sensory Diet Planning'
        ],
        
        // Developmental Disabilities Support (25 activities)
        'developmental_support' => [
            'Early Intervention - Cognitive Development',
            'Early Intervention - Language Stimulation',
            'Early Intervention - Motor Skills Development',
            'Early Intervention - Social Interaction',
            'Early Intervention - Play Skills Training',
            'Down Syndrome Support - Muscle Tone Improvement',
            'Down Syndrome Support - Speech Clarity Training',
            'Down Syndrome Support - Fine Motor Skills',
            'Down Syndrome Support - Academic Readiness',
            'Down Syndrome Support - Independence Training',
            'Intellectual Disability - Functional Academics',
            'Intellectual Disability - Community Skills',
            'Intellectual Disability - Self-Help Skills',
            'Intellectual Disability - Safety Awareness',
            'Intellectual Disability - Money Management',
            'ADHD Management - Attention Training',
            'ADHD Management - Impulse Control',
            'ADHD Management - Organization Skills',
            'ADHD Management - Study Strategies',
            'ADHD Management - Behavioral Modification',
            'Global Developmental Delay - Multi-Sensory Learning',
            'Global Developmental Delay - Milestone Tracking',
            'Global Developmental Delay - Family Support',
            'Global Developmental Delay - Adaptive Equipment',
            'Global Developmental Delay - Progress Monitoring'
        ],

        // Specialized Therapy Programs (25 activities)
        'specialized_therapy' => [
            'Music Therapy - Rhythmic Skills Development',
            'Music Therapy - Emotional Expression',
            'Music Therapy - Social Interaction Through Music',
            'Music Therapy - Cognitive Stimulation',
            'Music Therapy - Motor Skills Enhancement',
            'Art Therapy - Self-Expression Through Drawing',
            'Art Therapy - Fine Motor Development',
            'Art Therapy - Color Recognition and Matching',
            'Art Therapy - Creative Problem Solving',
            'Art Therapy - Emotional Regulation',
            'Aqua Therapy - Water Safety Skills',
            'Aqua Therapy - Muscle Strengthening',
            'Aqua Therapy - Balance and Coordination',
            'Aqua Therapy - Sensory Integration',
            'Aqua Therapy - Relaxation Techniques',
            'Animal Assisted Therapy - Pet Interaction',
            'Animal Assisted Therapy - Responsibility Training',
            'Animal Assisted Therapy - Emotional Bonding',
            'Animal Assisted Therapy - Communication Encouragement',
            'Animal Assisted Therapy - Stress Reduction',
            'Therapeutic Horseback Riding - Balance Improvement',
            'Therapeutic Horseback Riding - Core Strength',
            'Therapeutic Horseback Riding - Confidence Building',
            'Therapeutic Horseback Riding - Following Instructions',
            'Therapeutic Horseback Riding - Sensory Input'
        ],

        // Academic and Life Skills (25 activities)
        'academic_life_skills' => [
            'Pre-Academic - Letter Recognition',
            'Pre-Academic - Number Concepts',
            'Pre-Academic - Shape and Color Identification',
            'Pre-Academic - Pattern Recognition',
            'Pre-Academic - Categorization Skills',
            'Literacy Development - Phonics Training',
            'Literacy Development - Reading Comprehension',
            'Literacy Development - Writing Skills',
            'Literacy Development - Vocabulary Building',
            'Literacy Development - Story Telling',
            'Numeracy Skills - Basic Math Operations',
            'Numeracy Skills - Time Concepts',
            'Numeracy Skills - Money Recognition',
            'Numeracy Skills - Measurement Activities',
            'Numeracy Skills - Problem Solving',
            'Life Skills - Personal Hygiene',
            'Life Skills - Dressing Independence',
            'Life Skills - Meal Preparation',
            'Life Skills - Home Safety',
            'Life Skills - Public Transportation',
            'Technology Skills - Computer Basics',
            'Technology Skills - Educational Apps',
            'Technology Skills - Communication Devices',
            'Technology Skills - Internet Safety',
            'Technology Skills - Assistive Technology'
        ]
    ];

    /**
     * Pagoh-specific activities focusing on vocational training and life skills development
     */
    private array $pagohActivities = [
        // Vocational Training Programs (30 activities)
        'vocational_training' => [
            'Culinary Arts - Basic Cooking Skills',
            'Culinary Arts - Food Preparation Safety',
            'Culinary Arts - Menu Planning',
            'Culinary Arts - Kitchen Management',
            'Culinary Arts - Customer Service in Food Industry',
            'Horticulture - Basic Gardening Skills',
            'Horticulture - Plant Care and Maintenance',
            'Horticulture - Landscape Design',
            'Horticulture - Greenhouse Management',
            'Horticulture - Organic Farming Techniques',
            'Automotive Skills - Basic Car Maintenance',
            'Automotive Skills - Tool Recognition and Use',
            'Automotive Skills - Safety Procedures',
            'Automotive Skills - Simple Repairs',
            'Automotive Skills - Customer Communication',
            'Office Skills - Data Entry Training',
            'Office Skills - Filing and Organization',
            'Office Skills - Phone Etiquette',
            'Office Skills - Email Communication',
            'Office Skills - Meeting Preparation',
            'Retail Skills - Customer Service',
            'Retail Skills - Cash Handling',
            'Retail Skills - Product Knowledge',
            'Retail Skills - Inventory Management',
            'Retail Skills - Visual Merchandising',
            'Manufacturing Skills - Assembly Line Work',
            'Manufacturing Skills - Quality Control',
            'Manufacturing Skills - Safety Protocols',
            'Manufacturing Skills - Equipment Operation',
            'Manufacturing Skills - Teamwork in Production'
        ],

        // Advanced Life Skills (25 activities)
        'advanced_life_skills' => [
            'Independent Living - Apartment Management',
            'Independent Living - Bill Paying and Budgeting',
            'Independent Living - Grocery Shopping',
            'Independent Living - Laundry and Cleaning',
            'Independent Living - Home Maintenance',
            'Community Integration - Public Services Access',
            'Community Integration - Healthcare Navigation',
            'Community Integration - Banking Skills',
            'Community Integration - Legal Rights Awareness',
            'Community Integration - Emergency Procedures',
            'Social Relationships - Friendship Skills',
            'Social Relationships - Dating and Romance',
            'Social Relationships - Conflict Resolution',
            'Social Relationships - Workplace Relationships',
            'Social Relationships - Community Participation',
            'Financial Literacy - Banking Basics',
            'Financial Literacy - Savings and Investment',
            'Financial Literacy - Insurance Understanding',
            'Financial Literacy - Consumer Rights',
            'Financial Literacy - Credit Management',
            'Health and Wellness - Exercise Planning',
            'Health and Wellness - Nutrition Education',
            'Health and Wellness - Medical Appointments',
            'Health and Wellness - Medication Management',
            'Health and Wellness - Mental Health Awareness'
        ],

        // Job Readiness and Professional Skills (25 activities)
        'job_readiness' => [
            'Job Search Skills - Resume Writing',
            'Job Search Skills - Interview Preparation',
            'Job Search Skills - Job Application Process',
            'Job Search Skills - Online Job Searching',
            'Job Search Skills - Reference Management',
            'Workplace Behavior - Professional Appearance',
            'Workplace Behavior - Punctuality and Attendance',
            'Workplace Behavior - Following Directions',
            'Workplace Behavior - Workplace Communication',
            'Workplace Behavior - Problem Solving',
            'Soft Skills Development - Leadership Training',
            'Soft Skills Development - Team Collaboration',
            'Soft Skills Development - Time Management',
            'Soft Skills Development - Stress Management',
            'Soft Skills Development - Adaptability Training',
            'Customer Service Excellence - Communication Skills',
            'Customer Service Excellence - Problem Resolution',
            'Customer Service Excellence - Professional Demeanor',
            'Customer Service Excellence - Product Knowledge',
            'Customer Service Excellence - Complaint Handling',
            'Entrepreneurship - Business Idea Development',
            'Entrepreneurship - Basic Business Planning',
            'Entrepreneurship - Marketing Fundamentals',
            'Entrepreneurship - Financial Planning',
            'Entrepreneurship - Customer Relations'
        ],

        // Community Engagement and Civic Participation (20 activities)
        'community_engagement' => [
            'Civic Education - Rights and Responsibilities',
            'Civic Education - Voting and Democracy',
            'Civic Education - Local Government Understanding',
            'Civic Education - Community Resources',
            'Civic Education - Volunteer Opportunities',
            'Cultural Activities - Traditional Malaysian Arts',
            'Cultural Activities - Festival Celebrations',
            'Cultural Activities - Cultural Appreciation',
            'Cultural Activities - Language Learning',
            'Cultural Activities - Heritage Preservation',
            'Environmental Awareness - Recycling Programs',
            'Environmental Awareness - Conservation Practices',
            'Environmental Awareness - Sustainable Living',
            'Environmental Awareness - Community Clean-up',
            'Environmental Awareness - Green Technology',
            'Sports and Recreation - Team Sports Participation',
            'Sports and Recreation - Individual Fitness',
            'Sports and Recreation - Adaptive Sports',
            'Sports and Recreation - Recreation Planning',
            'Sports and Recreation - Leadership in Sports'
        ]
    ];

    public function run(): void
    {
        $this->command->info('🚀 Starting enhanced activities and sessions for Kuantan and Pagoh centres...');
        
        // Get centres
        $kuantanCentre = Centre::where('centre_id', '02')->first();
        $pagohCentre = Centre::where('centre_id', '03')->first();
        
        if (!$kuantanCentre || !$pagohCentre) {
            $this->command->error('Kuantan or Pagoh centre not found!');
            return;
        }
        
        // Create activities for Kuantan centre
        $this->command->info('📚 Creating specialized activities for Kuantan centre...');
        $kuantanActivities = $this->createActivitiesForCentre($kuantanCentre, $this->kuantanActivities, 'kuantan');
        
        // Create activities for Pagoh centre
        $this->command->info('🏭 Creating vocational and life skills activities for Pagoh centre...');
        $pagohActivities = $this->createActivitiesForCentre($pagohCentre, $this->pagohActivities, 'pagoh');
        
        // Create comprehensive sessions (5x multiplier)
        $this->command->info('📅 Generating comprehensive session schedules...');
        $this->createEnhancedSessions($kuantanActivities, $kuantanCentre, 5);
        $this->createEnhancedSessions($pagohActivities, $pagohCentre, 5);
        
        // Enroll trainees in activities
        $this->command->info('👥 Enrolling trainees in activities...');
        $this->enrollTraineesInActivities($kuantanActivities, $kuantanCentre);
        $this->enrollTraineesInActivities($pagohActivities, $pagohCentre);
        
        $this->showFinalStatistics($kuantanCentre, $pagohCentre);
    }

    /**
     * Create activities for a specific centre
     */
    private function createActivitiesForCentre(Centre $centre, array $activityCategories, string $centreType): array
    {
        $activities = [];
        $categoryMap = $this->getCategoryMapping();
        
        $activityCounter = 1;
        foreach ($activityCategories as $categoryKey => $activityList) {
            foreach ($activityList as $activityName) {
                // Generate unique activity ID with timestamp
                $activityId = $centre->centre_id . '_' . time() . '_' . $activityCounter;
                
                $activity = Activity::create([
                    'activity_id' => $activityId,
                    'activity_name' => $activityName,
                    'activity_description' => $this->generateDescription($activityName, $centreType),
                    'activity_type' => $this->getActivityType($categoryKey),
                    'activity_date' => Carbon::now()->addDays(rand(1, 30)),
                    'start_date' => Carbon::now()->addDays(rand(1, 7)),
                    'end_date' => Carbon::now()->addMonths(rand(3, 12)),
                    'sessions_per_week' => rand(2, 5),
                    'activity_period' => rand(3, 12),
                    'pass_threshold' => rand(70, 90),
                    'is_active' => 1,
                    'activity_start_time' => $this->getActivityStartTime(),
                    'activity_end_time' => $this->getActivityEndTime(),
                    'activity_location' => $this->getActivityLocation($centre),
                    'max_participants' => $this->getMaxParticipants($activityName),
                    'current_participants' => 0,
                    'activity_goals' => $this->generateObjectives($activityName, $centreType),
                    'activity_outcomes' => $this->generateOutcomes($activityName, $centreType),
                    'required_resources' => $this->getRequiredResources($activityName, $centreType),
                    'activity_status' => 'scheduled',
                    'centre_id' => $centre->centre_id,
                    'category_id' => $categoryMap[$categoryKey] ?? $categoryMap['rehabilitation'],
                    'created_by' => $this->getRandomStaffForCentre($centre->centre_id),
                    'times_conducted' => rand(0, 5),
                    'instructor_id' => $this->getRandomStaffForCentre($centre->centre_id),
                    'created_at' => Carbon::now()->subDays(rand(1, 60)),
                    'updated_at' => Carbon::now()->subDays(rand(0, 30))
                ]);
                
                $activities[] = $activity;
                $activityCounter++;
            }
        }
        
        $this->command->info("✅ Created " . count($activities) . " activities for {$centre->centre_name}");
        return $activities;
    }

    /**
     * Create enhanced sessions with 5x multiplier
     */
    private function createEnhancedSessions(array $activities, Centre $centre, int $multiplier): void
    {
        $sessionCount = 0;
        
        foreach ($activities as $activity) {
            // Create multiple sessions per activity (5x multiplier)
            $sessionsPerActivity = rand(8, 15) * $multiplier; // 40-75 sessions per activity
            
            for ($i = 0; $i < $sessionsPerActivity; $i++) {
                $sessionDate = $this->generateSessionDate();
                $sessionTime = $this->generateSessionTime($activity->activity_name);
                
                $session = ActivitySession::create([
                    'activity_id' => $activity->id,
                    'session_date' => $sessionDate->format('Y-m-d'),
                    'start_time' => $sessionTime['start'],
                    'end_time' => $sessionTime['end'],
                    'location' => $this->getSessionLocation($activity->activity_name, $centre),
                    'instructor_id' => $this->getRandomStaffForCentre($centre->centre_id),
                    'assistant_id' => rand(0, 1) ? $this->getRandomStaffForCentre($centre->centre_id) : null,
                    'max_participants' => $activity->max_participants,
                    'current_participants' => 0,
                    'session_status' => $this->getSessionStatus($sessionDate),
                    'session_notes' => $this->generateSessionNotes($activity->activity_name),
                    'required_materials' => json_encode($this->getSessionMaterials($activity->activity_name)),
                    'weather_condition' => $this->getWeatherCondition(),
                    'session_rating' => $this->getSessionRating(),
                    'feedback' => $this->generateSessionFeedback($activity->activity_name),
                    'created_at' => $sessionDate->subDays(rand(1, 7)),
                    'updated_at' => $sessionDate->addDays(rand(0, 2))
                ]);
                
                $sessionCount++;
            }
        }
        
        $this->command->info("✅ Created {$sessionCount} sessions for {$centre->centre_name}");
    }

    /**
     * Enroll trainees in activities
     */
    private function enrollTraineesInActivities(array $activities, Centre $centre): void
    {
        $trainees = Trainee::where('centre_id', $centre->centre_id)->get();
        $enrollmentCount = 0;
        
        foreach ($trainees as $trainee) {
            // Each trainee gets enrolled in 10-20 activities (significant increase)
            $activitiesToEnroll = collect($activities)->random(rand(10, 20));
            
            foreach ($activitiesToEnroll as $activity) {
                // Create activity enrollment
                $enrollment = ActivityEnrollment::create([
                    'trainee_id' => $trainee->id,
                    'activity_id' => $activity->id,
                    'enrollment_date' => Carbon::now()->subDays(rand(1, 30)),
                    'enrollment_status' => 'active',
                    'progress_level' => rand(1, 5),
                    'notes' => $this->generateEnrollmentNotes($activity->activity_name),
                    'created_at' => Carbon::now()->subDays(rand(1, 30))
                ]);
                
                // Enroll in multiple sessions for this activity
                $sessions = ActivitySession::where('activity_id', $activity->id)
                    ->where('session_status', '!=', 'cancelled')
                    ->inRandomOrder()
                    ->limit(rand(15, 30)) // 15-30 sessions per trainee per activity
                    ->get();
                
                foreach ($sessions as $session) {
                    if ($session->current_participants < $session->max_participants) {
                        SessionEnrollment::create([
                            'session_id' => $session->id,
                            'trainee_id' => $trainee->id,
                            'enrollment_date' => $session->session_date,
                            'attendance_status' => $this->getAttendanceStatus(),
                            'participation_level' => rand(1, 5),
                            'behavior_notes' => $this->generateBehaviorNotes(),
                            'skill_demonstration' => $this->generateSkillDemonstration($activity->activity_name),
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
        
        $this->command->info("✅ Created {$enrollmentCount} session enrollments for {$centre->centre_name}");
    }

    /**
     * Helper methods for generating realistic data
     */
    private function getCategoryMapping(): array
    {
        return [
            'autism_intervention' => 1,
            'developmental_support' => 2,
            'specialized_therapy' => 3,
            'academic_life_skills' => 4,
            'vocational_training' => 5,
            'advanced_life_skills' => 6,
            'job_readiness' => 7,
            'community_engagement' => 8,
            'rehabilitation' => 1 // fallback
        ];
    }

    private function generateMalayName(string $activityName, string $centreType): string
    {
        $malayTranslations = [
            'Speech Therapy' => 'Terapi Pertuturan',
            'Occupational Therapy' => 'Terapi Pekerjaan',
            'Physical Therapy' => 'Terapi Fizikal',
            'Behavioral Therapy' => 'Terapi Tingkah Laku',
            'ABA Therapy' => 'Terapi ABA',
            'Social Skills Training' => 'Latihan Kemahiran Sosial',
            'Life Skills' => 'Kemahiran Hidup',
            'Vocational Training' => 'Latihan Vokasional',
            'Culinary Arts' => 'Seni Kulinari',
            'Job Search Skills' => 'Kemahiran Mencari Kerja',
            'Community Integration' => 'Integrasi Komuniti'
        ];
        
        foreach ($malayTranslations as $english => $malay) {
            if (strpos($activityName, $english) !== false) {
                return str_replace($english, $malay, $activityName);
            }
        }
        
        return $activityName;
    }

    private function generateDescription(string $activityName, string $centreType): string
    {
        if ($centreType === 'kuantan') {
            return "Specialized intervention program designed for individuals with autism spectrum disorders and developmental disabilities. This evidence-based approach focuses on individualized treatment plans with measurable outcomes.";
        } else {
            return "Comprehensive vocational and life skills program designed to prepare individuals for independent living and meaningful employment. This community-based approach emphasizes practical skills and real-world application.";
        }
    }

    private function generateMalayDescription(string $activityName, string $centreType): string
    {
        if ($centreType === 'kuantan') {
            return "Program intervensi khusus yang direka untuk individu dengan spektrum autisme dan kecacatan perkembangan. Pendekatan berasaskan bukti ini memfokuskan kepada rancangan rawatan individu dengan hasil yang boleh diukur.";
        } else {
            return "Program kemahiran vokasional dan hidup yang komprehensif direka untuk menyediakan individu untuk kehidupan berdikari dan pekerjaan bermakna. Pendekatan berasaskan komuniti ini menekankan kemahiran praktikal dan aplikasi dunia sebenar.";
        }
    }

    private function generateObjectives(string $activityName, string $centreType): array
    {
        if ($centreType === 'kuantan') {
            return [
                "Improve functional communication skills",
                "Enhance social interaction abilities", 
                "Develop self-regulation strategies",
                "Increase independence in daily activities",
                "Strengthen sensory processing capabilities"
            ];
        } else {
            return [
                "Develop marketable job skills",
                "Enhance independent living capabilities",
                "Improve community integration",
                "Build professional work habits",
                "Strengthen financial literacy"
            ];
        }
    }

    private function getRandomStaffForCentre(string $centreId): int
    {
        $staff = User::where('centre_id', $centreId)
            ->whereIn('role', ['teacher', 'supervisor'])
            ->inRandomOrder()
            ->first();
        
        return $staff ? $staff->id : 1;
    }

    private function getMaxParticipants(string $activityName): int
    {
        if (strpos($activityName, 'Group') !== false || strpos($activityName, 'Team') !== false) {
            return rand(8, 15);
        }
        return rand(3, 8);
    }

    private function getDuration(string $activityName): int
    {
        if (strpos($activityName, 'Vocational') !== false || strpos($activityName, 'Workshop') !== false) {
            return rand(120, 240); // 2-4 hours
        }
        return rand(45, 90); // 45-90 minutes
    }

    private function getRequiredResources(string $activityName, string $centreType): array
    {
        if ($centreType === 'kuantan') {
            return ['Therapy materials', 'Assessment tools', 'Visual aids', 'Sensory equipment'];
        } else {
            return ['Training materials', 'Safety equipment', 'Work tools', 'Assessment forms'];
        }
    }

    private function getActivityType(string $categoryKey): string
    {
        $typeMapping = [
            'autism_intervention' => 'therapy',
            'developmental_support' => 'therapy',
            'specialized_therapy' => 'therapy',
            'academic_life_skills' => 'education',
            'vocational_training' => 'vocational',
            'advanced_life_skills' => 'life_skills',
            'job_readiness' => 'vocational',
            'community_engagement' => 'social'
        ];
        
        return $typeMapping[$categoryKey] ?? 'therapy';
    }

    private function getActivityStartTime(): string
    {
        $hours = [8, 9, 10, 11, 14, 15, 16];
        $hour = $hours[array_rand($hours)];
        $minute = [0, 30][rand(0, 1)];
        
        return sprintf('%02d:%02d:00', $hour, $minute);
    }

    private function getActivityEndTime(): string
    {
        $hours = [12, 13, 16, 17, 18];
        $hour = $hours[array_rand($hours)];
        $minute = [0, 30][rand(0, 1)];
        
        return sprintf('%02d:%02d:00', $hour, $minute);
    }

    private function getActivityLocation(Centre $centre): string
    {
        $locations = [
            'Therapy Room A', 'Therapy Room B', 'Group Activity Hall',
            'Sensory Room', 'Computer Lab', 'Outdoor Area',
            'Workshop', 'Training Center', 'Conference Room'
        ];
        
        return $locations[array_rand($locations)];
    }

    private function generateOutcomes(string $activityName, string $centreType): array
    {
        if ($centreType === 'kuantan') {
            return [
                "Improved communication skills demonstrated",
                "Enhanced social interaction observed",
                "Better self-regulation achieved",
                "Increased task completion rate"
            ];
        } else {
            return [
                "Job skills successfully acquired",
                "Independent living capabilities enhanced",
                "Professional behaviors developed",
                "Community integration improved"
            ];
        }
    }

    private function getSkillLevel(string $activityName): string
    {
        $levels = ['beginner', 'intermediate', 'advanced'];
        return $levels[array_rand($levels)];
    }

    private function getAgeGroup(string $activityName): string
    {
        $groups = ['children', 'adolescent', 'adult', 'all_ages'];
        return $groups[array_rand($groups)];
    }

    private function isGroupActivity(string $activityName): bool
    {
        return strpos($activityName, 'Group') !== false || 
               strpos($activityName, 'Team') !== false ||
               strpos($activityName, 'Community') !== false;
    }

    private function requiresAssessment(string $activityName): bool
    {
        return rand(0, 1);
    }

    private function generateSessionDate(): Carbon
    {
        $startDate = Carbon::now()->subMonths(6);
        $endDate = Carbon::now()->addMonths(3);
        
        return Carbon::createFromTimestamp(
            rand($startDate->timestamp, $endDate->timestamp)
        );
    }

    private function generateSessionTime(string $activityName): array
    {
        $startHour = rand(8, 16);
        $startMinute = [0, 30][rand(0, 1)];
        
        $start = sprintf('%02d:%02d:00', $startHour, $startMinute);
        
        // Calculate end time based on activity duration
        $duration = strpos($activityName, 'Vocational') !== false ? 3 : 1;
        $endHour = $startHour + $duration;
        $end = sprintf('%02d:%02d:00', $endHour, $startMinute);
        
        return ['start' => $start, 'end' => $end];
    }

    private function getSessionLocation(string $activityName, Centre $centre): string
    {
        $locations = [
            'Therapy Room 1', 'Therapy Room 2', 'Group Activity Hall',
            'Sensory Integration Room', 'Computer Lab', 'Outdoor Area',
            'Workshop', 'Kitchen Training Area', 'Conference Room'
        ];
        
        return $locations[array_rand($locations)];
    }

    private function getSessionStatus(Carbon $sessionDate): string
    {
        if ($sessionDate->isFuture()) {
            return ['scheduled', 'confirmed'][rand(0, 1)];
        } else {
            return ['completed', 'completed', 'completed', 'cancelled'][rand(0, 3)];
        }
    }

    private function generateSessionNotes(string $activityName): string
    {
        $notes = [
            "Session proceeded as planned with good participation from all attendees.",
            "Excellent engagement and progress observed in target skills.",
            "Some participants needed additional support with activity components.",
            "Weather conditions affected outdoor portions of the session.",
            "Modified activity approach based on participant needs and responses."
        ];
        
        return $notes[array_rand($notes)];
    }

    private function getSessionMaterials(string $activityName): array
    {
        return ['Training materials', 'Assessment sheets', 'Safety equipment', 'Activity supplies'];
    }

    private function getWeatherCondition(): string
    {
        $conditions = ['sunny', 'cloudy', 'rainy', 'hot', 'mild'];
        return $conditions[array_rand($conditions)];
    }

    private function getSessionRating(): int
    {
        return rand(3, 5);
    }

    private function generateSessionFeedback(string $activityName): string
    {
        $feedback = [
            "Participants showed great enthusiasm and engagement throughout the session.",
            "Good progress observed in targeted skill development areas.",
            "Some challenges encountered but overall positive outcomes achieved.",
            "Excellent collaboration and peer support demonstrated.",
            "Participants successfully completed all planned activities."
        ];
        
        return $feedback[array_rand($feedback)];
    }

    private function generateEnrollmentNotes(string $activityName): string
    {
        return "Trainee enrolled based on individual assessment and treatment planning recommendations.";
    }

    private function getAttendanceStatus(): string
    {
        $statuses = ['present', 'present', 'present', 'present', 'absent', 'late'];
        return $statuses[array_rand($statuses)];
    }

    private function generateBehaviorNotes(): string
    {
        $notes = [
            "Demonstrated appropriate social behavior throughout session.",
            "Required some prompting to maintain attention and focus.",
            "Showed excellent cooperation and willingness to participate.",
            "Needed additional support with task completion.",
            "Displayed positive attitude and enthusiasm for learning."
        ];
        
        return $notes[array_rand($notes)];
    }

    private function generateSkillDemonstration(string $activityName): string
    {
        return "Demonstrated progress in targeted skill areas with measurable improvements noted.";
    }

    private function generateParentFeedback(): string
    {
        $feedback = [
            "Parents report positive changes in behavior at home.",
            "Family notices improved skills being applied in daily activities.",
            "Parents appreciate the structured approach and clear communication.",
            "Positive feedback regarding trainee's enthusiasm for sessions.",
            "Family requests continued focus on independence skills."
        ];
        
        return $feedback[array_rand($feedback)];
    }

    private function showFinalStatistics(Centre $kuantanCentre, Centre $pagohCentre): void
    {
        $kuantanStats = $this->getCentreStatistics($kuantanCentre);
        $pagohStats = $this->getCentreStatistics($pagohCentre);
        
        $this->command->info("\n" . str_repeat('=', 80));
        $this->command->info("🎉 ENHANCED ACTIVITIES AND SESSIONS CREATION COMPLETED! 🎉");
        $this->command->info(str_repeat('=', 80));
        
        $this->command->info("\n📊 KUANTAN CENTRE STATISTICS:");
        $this->command->info("🎯 Total Activities: " . $kuantanStats['activities']);
        $this->command->info("📅 Total Sessions: " . $kuantanStats['sessions']);
        $this->command->info("👥 Total Enrollments: " . $kuantanStats['enrollments']);
        $this->command->info("🧒 Trainees Enrolled: " . $kuantanStats['trainees']);
        
        $this->command->info("\n📊 PAGOH CENTRE STATISTICS:");
        $this->command->info("🎯 Total Activities: " . $pagohStats['activities']);
        $this->command->info("📅 Total Sessions: " . $pagohStats['sessions']);
        $this->command->info("👥 Total Enrollments: " . $pagohStats['enrollments']);
        $this->command->info("🧒 Trainees Enrolled: " . $pagohStats['trainees']);
        
        $totalActivities = $kuantanStats['activities'] + $pagohStats['activities'];
        $totalSessions = $kuantanStats['sessions'] + $pagohStats['sessions'];
        $totalEnrollments = $kuantanStats['enrollments'] + $pagohStats['enrollments'];
        
        $this->command->info("\n🌟 COMBINED TOTALS:");
        $this->command->info("🎯 Total New Activities: " . $totalActivities);
        $this->command->info("📅 Total New Sessions: " . $totalSessions);
        $this->command->info("👥 Total New Enrollments: " . $totalEnrollments);
        
        $this->command->info("\n✅ Both centres now have comprehensive programs with 5x increased activity sessions!");
        $this->command->info("✅ Every trainee and staff member has significantly more activities and sessions!");
        $this->command->info(str_repeat('=', 80) . "\n");
    }

    private function getCentreStatistics(Centre $centre): array
    {
        return [
            'activities' => Activity::where('centre_id', $centre->centre_id)->count(),
            'sessions' => ActivitySession::whereHas('activity', function($query) use ($centre) {
                $query->where('centre_id', $centre->centre_id);
            })->count(),
            'enrollments' => SessionEnrollment::whereHas('session.activity', function($query) use ($centre) {
                $query->where('centre_id', $centre->centre_id);
            })->count(),
            'trainees' => Trainee::where('centre_id', $centre->centre_id)->count()
        ];
    }
}