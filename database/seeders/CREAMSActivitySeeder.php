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

class CREAMSActivitySeeder extends Seeder
{
    /**
     * Activity templates for 100 different activities
     */
    private array $activityTemplates = [
        // Rehabilitation Activities (25)
        'rehabilitation' => [
            'Speech Therapy - Basic Communication',
            'Speech Therapy - Articulation Training',
            'Speech Therapy - Language Development',
            'Speech Therapy - Voice Training',
            'Speech Therapy - Swallowing Therapy',
            'Occupational Therapy - Fine Motor Skills',
            'Occupational Therapy - Daily Living Skills',
            'Occupational Therapy - Cognitive Training',
            'Occupational Therapy - Sensory Processing',
            'Occupational Therapy - Hand-Eye Coordination',
            'Physical Therapy - Gross Motor Development',
            'Physical Therapy - Balance and Coordination',
            'Physical Therapy - Strength Training',
            'Physical Therapy - Gait Training',
            'Physical Therapy - Flexibility Exercises',
            'Behavioral Therapy - Social Skills Training',
            'Behavioral Therapy - Anger Management',
            'Behavioral Therapy - Self-Regulation',
            'Behavioral Therapy - Communication Skills',
            'Behavioral Therapy - Adaptive Behavior',
            'Sensory Integration - Tactile Stimulation',
            'Sensory Integration - Vestibular Training',
            'Sensory Integration - Proprioceptive Activities',
            'Sensory Integration - Auditory Processing',
            'Sensory Integration - Visual Processing'
        ],
        
        // Academic Activities (25)  
        'academic' => [
            'Mathematics - Basic Counting',
            'Mathematics - Addition and Subtraction',
            'Mathematics - Multiplication Tables',
            'Mathematics - Problem Solving',
            'Mathematics - Geometry Basics',
            'Literacy - Phonics Training',
            'Literacy - Reading Comprehension',
            'Literacy - Writing Skills',
            'Literacy - Vocabulary Building',
            'Literacy - Story Telling',
            'Science - Nature Exploration',
            'Science - Simple Experiments',
            'Science - Weather Learning',
            'Science - Animal Studies',
            'Science - Plant Life Cycles',
            'Computer Skills - Basic Mouse Training',
            'Computer Skills - Keyboard Practice',
            'Computer Skills - Educational Games',
            'Computer Skills - Drawing Software',
            'Computer Skills - Internet Safety',
            'Life Skills - Personal Hygiene',
            'Life Skills - Money Management',
            'Life Skills - Time Management',
            'Life Skills - Cooking Basics',
            'Life Skills - Safety Awareness'
        ],
        
        // Creative & Social Activities (25)
        'creative_social' => [
            'Art & Creativity - Painting Workshop',
            'Art & Creativity - Clay Modeling',
            'Art & Creativity - Collage Making',
            'Art & Creativity - Drawing Techniques',
            'Art & Creativity - Craft Projects',
            'Music Therapy - Rhythm Training',
            'Music Therapy - Singing Sessions',
            'Music Therapy - Instrument Playing',
            'Music Therapy - Movement to Music',
            'Music Therapy - Music Appreciation',
            'Social Skills - Friendship Building',
            'Social Skills - Group Interaction',
            'Social Skills - Conflict Resolution',
            'Social Skills - Communication Practice',
            'Social Skills - Teamwork Activities',
            'Recreational Therapy - Indoor Games',
            'Recreational Therapy - Outdoor Activities',
            'Recreational Therapy - Sports Training',
            'Recreational Therapy - Dance Movement',
            'Recreational Therapy - Gardening',
            'Drama Therapy - Role Playing',
            'Drama Therapy - Expression Training',
            'Drama Therapy - Storytelling',
            'Drama Therapy - Confidence Building',
            'Drama Therapy - Creative Expression'
        ],
        
        // Faith-Based Activities (25)
        'faith' => [
            'Pembelajaran Solat - Rukun Solat',
            'Pembelajaran Solat - Wudhu Training',
            'Pembelajaran Solat - Bacaan Solat',
            'Pembelajaran Solat - Gerakan Solat',
            'Pembelajaran Solat - Doa Selepas Solat',
            'Tilawah Al-Quran - Huruf Hijaiyah',
            'Tilawah Al-Quran - Bacaan Surah Pendek',
            'Tilawah Al-Quran - Tajwid Asas',
            'Tilawah Al-Quran - Hafazan Surah',
            'Tilawah Al-Quran - Fahaman Al-Quran',
            'Adab dan Akhlak - Sopan Santun',
            'Adab dan Akhlak - Hormat Menghormati',
            'Adab dan Akhlak - Tolong Menolong',
            'Adab dan Akhlak - Kejujuran',
            'Adab dan Akhlak - Kasih Sayang',
            'Sejarah Islam - Kisah Nabi',
            'Sejarah Islam - Sahabat Nabi',
            'Sejarah Islam - Peristiwa Penting',
            'Sejarah Islam - Nilai Murni',
            'Sejarah Islam - Teladan Hidup',
            'Doa dan Zikir - Doa Harian',
            'Doa dan Zikir - Zikir Asas',
            'Doa dan Zikir - Doa Makan',
            'Doa dan Zikir - Doa Tidur',
            'Doa dan Zikir - Istighfar dan Tasbih'
        ]
    ];

    /**
     * Time slots based on centre opening time + 30 minutes for first session
     */
    private array $timeSlots = [
        ['start' => '09:30', 'end' => '10:30'], // 30 min after 9AM opening
        ['start' => '10:30', 'end' => '11:30'],
        ['start' => '11:30', 'end' => '12:30'],
        ['start' => '14:00', 'end' => '15:00'], // After lunch
        ['start' => '15:00', 'end' => '16:00'],
        ['start' => '16:00', 'end' => '17:00'],
    ];

    /**
     * Venues for activities
     */
    private array $venues = [
        'Bilik Terapi Pertuturan',
        'Bilik Terapi Okupasi', 
        'Bilik Fisioterapi',
        'Bilik Integrasi Sensori',
        'Bilik Aktiviti Kumpulan',
        'Makmal Komputer',
        'Bilik Kemahiran Hidup',
        'Bilik Seni',
        'Bilik Muzik',
        'Dewan Serbaguna',
        'Taman Sensori',
        'Bengkel Vokasional'
    ];

    public function run(): void
    {
        $this->command->info('🎯 Creating 100 Activities with 300 Sessions for CREAMS System...');

        // Get all categories, centres, users, and trainees
        $categories = Category::all()->keyBy('category_type');
        $centres = Centre::all();
        $users = User::whereIn('role', ['admin', 'supervisor', 'teacher'])->get();
        $trainees = Trainee::all();
        
        if ($centres->isEmpty() || $users->isEmpty()) {
            $this->command->error('No centres or users found! Please run centre and user seeders first.');
            return;
        }

        // Step 1: Create 100 Activities (25 per category)
        $this->command->info('🏗️ Creating 100 diverse activities...');
        $activities = $this->createActivities($categories, $centres, $users);
        
        // Step 2: Create 300 Sessions (150 ongoing, 150 starting)
        $this->command->info('📅 Creating 300 sessions (150 ongoing, 150 starting)...');
        $sessions = $this->createSessions($activities, $centres);
        
        // Step 3: Enroll trainees in sessions
        if (!$trainees->isEmpty()) {
            $this->command->info('👥 Enrolling trainees in activities...');
            $enrollments = $this->createEnrollments($sessions, $trainees);
        }

        $this->showCompletionSummary(count($activities), count($sessions));
    }

    private function createActivities($categories, $centres, $users): array
    {
        $activities = [];
        $activityCounter = 1;

        foreach ($this->activityTemplates as $categoryType => $activityList) {
            $category = $categories->get($categoryType);
            if (!$category) continue;

            foreach ($activityList as $activityName) {
                $centre = $centres->random();
                $instructor = $users->random();
                
                $activityId = $this->generateActivityId($categoryType, $activityCounter);
                
                $activity = Activity::create([
                    'activity_id' => $activityId,
                    'activity_name' => $activityName,
                    'activity_description' => $this->generateActivityDescription($activityName),
                    'activity_type' => $this->getActivityType($activityName),
                    'activity_date' => Carbon::now()->subDays(rand(30, 90)),
                    'activity_period' => rand(3, 12) . ' months',
                    'activity_start_time' => $this->getRandomTime(),
                    'activity_end_time' => $this->getRandomEndTime(),
                    'activity_location' => $this->getVenueForCategory($categoryType),
                    'max_participants' => $this->getMaxParticipants($categoryType),
                    'current_participants' => 0,
                    'activity_goals' => json_encode($this->generateActivityGoals($activityName)),
                    'activity_outcomes' => json_encode($this->generateActivityOutcomes($activityName)),
                    'required_resources' => json_encode($this->generateRequiredResources($activityName)),
                    'activity_status' => 'scheduled',
                    'centre_id' => $centre->centre_id,
                    'category_id' => $category->id,
                    'created_by' => $instructor->id,
                    'instructor_id' => $instructor->id,
                    'times_conducted' => 0,
                    'is_active' => true
                ]);

                $activities[] = $activity;
                $activityCounter++;
                
                if ($activityCounter % 10 == 0) {
                    $this->command->line("   Created {$activityCounter} activities...");
                }
            }
        }

        return $activities;
    }

    private function createSessions($activities, $centres): array
    {
        $allSessions = [];
        $sessionCounter = 0;
        $targetSessions = 300;
        $ongoingSessions = 150;
        $startingSessions = 150;

        foreach ($activities as $activity) {
            if ($sessionCounter >= $targetSessions) break;

            // Create 3 sessions per activity on average
            $sessionsPerActivity = min(3, ceil(($targetSessions - $sessionCounter) / (count($activities) - array_search($activity, $activities))));
            
            for ($i = 0; $i < $sessionsPerActivity && $sessionCounter < $targetSessions; $i++) {
                $isOngoing = $sessionCounter < $ongoingSessions;
                $session = $this->createSession($activity, $isOngoing, $sessionCounter + 1);
                $allSessions[] = $session;
                $sessionCounter++;
                
                if ($sessionCounter % 50 == 0) {
                    $this->command->line("   Created {$sessionCounter} sessions...");
                }
            }
        }

        return $allSessions;
    }

    private function createSession($activity, $isOngoing, $sessionNumber): ActivitySession
    {
        $sessionId = $this->generateSessionId($activity, $sessionNumber);
        
        if ($isOngoing) {
            // Ongoing sessions (started in the past)
            $sessionDate = Carbon::now()->subDays(rand(1, 60));
            $status = rand(1, 100) <= 80 ? 'ongoing' : 'completed';
        } else {
            // Starting sessions (future dates)
            $sessionDate = Carbon::now()->addDays(rand(1, 120));
            $status = 'scheduled';
        }

        $timeSlot = $this->timeSlots[array_rand($this->timeSlots)];
        
        return ActivitySession::create([
            'activity_id' => $activity->id,
            'session_date' => $sessionDate->format('Y-m-d'),
            'scheduled_date' => $sessionDate->format('Y-m-d'),
            'start_time' => $timeSlot['start'],
            'end_time' => $timeSlot['end'],
            'venue' => $activity->activity_location,
            'max_participants' => $activity->max_participants,
            'current_participants' => 0,
            'session_status' => $status,
            'status' => $status,
            'teacher_id' => $activity->created_by,
            'instructor_id' => $activity->created_by,
            'attendance_marked' => $status === 'completed',
            'session_notes' => $this->generateSessionNotes($status),
            'centre_id' => $activity->centre_id,
            'created_at' => $sessionDate->copy()->subDays(rand(1, 7))
        ]);
    }

    private function createEnrollments($sessions, $trainees): int
    {
        $totalEnrollments = 0;
        $activityEnrollments = collect(); // Track created activity enrollments
        
        // Ensure sessions is a collection
        if (is_array($sessions)) {
            $sessions = collect($sessions);
        }
        
        // Group sessions by activity
        $sessionsByActivity = $sessions->groupBy('activity_id');
        
        foreach ($sessionsByActivity as $activityId => $activitySessions) {
            // First, create activity-level enrollments (once per activity-trainee pair)
            $enrollmentCount = rand(
                (int)($activitySessions->first()->max_participants * 0.6), 
                (int)($activitySessions->first()->max_participants * 0.9)
            );
            
            $selectedTrainees = $trainees->random(min($enrollmentCount, $trainees->count()));
            
            foreach ($selectedTrainees as $trainee) {
                // Create activity enrollment only once per activity-trainee pair
                $activityEnrollment = ActivityEnrollment::firstOrCreate([
                    'activity_id' => $activityId,
                    'trainee_id' => $trainee->id,
                ], [
                    'enrollment_status' => 'enrolled',
                    'enrollment_date' => $activitySessions->first()->created_at,
                    'overall_progress' => rand(70, 95),
                    'total_sessions' => $activitySessions->count(),
                    'enrolled_by' => $activitySessions->first()->teacher_id,
                    'centre_id' => $activitySessions->first()->centre_id,
                ]);
                
                if ($activityEnrollment->wasRecentlyCreated) {
                    $totalEnrollments++;
                }
                
                // Create session enrollments for each session this trainee will attend
                foreach ($activitySessions as $session) {
                    // 80% chance trainee attends each session
                    if (rand(1, 100) <= 80) {
                        $attendanceStatus = $this->getAttendanceStatus($session->status);
                        
                        SessionEnrollment::create([
                            'session_id' => $session->id,
                            'trainee_id' => $trainee->id,
                            'enrollment_date' => $session->created_at,
                            'enrolled_by' => $session->teacher_id,
                            'enrollment_status' => 'enrolled',
                            'centre_id' => $session->centre_id,
                        ]);
                    }
                }
            }
            
            // Update session participant counts based on session enrollments
            foreach ($activitySessions as $session) {
                $sessionParticipants = SessionEnrollment::where('session_id', $session->id)->count();
                $session->update(['current_participants' => $sessionParticipants]);
            }
        }
        
        return $totalEnrollments;
    }

    private function generateActivityId($categoryType, $counter): string
    {
        $prefixes = [
            'rehabilitation' => 'REH',
            'academic' => 'ACD', 
            'creative_social' => 'CRT',
            'faith' => 'FTH'
        ];
        
        $prefix = $prefixes[$categoryType] ?? 'ACT';
        return $prefix . '-' . date('Y') . '-' . str_pad($counter, 3, '0', STR_PAD_LEFT);
    }

    private function generateSessionId($activity, $sessionNumber): string
    {
        return 'SES-' . $activity->id . '-' . date('Ymd') . '-' . str_pad($sessionNumber, 3, '0', STR_PAD_LEFT);
    }

    private function generateActivityDescription($activityName): string
    {
        $descriptions = [
            'Comprehensive training program designed to enhance specific skills and abilities.',
            'Structured learning experience tailored for individual development needs.',
            'Interactive session focusing on practical skill development and application.',
            'Evidence-based intervention program for optimal therapeutic outcomes.',
            'Collaborative learning environment promoting growth and independence.'
        ];
        
        return $descriptions[array_rand($descriptions)];
    }

    private function getActivityType($activityName): string
    {
        if (strpos($activityName, 'Therapy') !== false) {
            return 'Individual';
        } elseif (strpos($activityName, 'Group') !== false || strpos($activityName, 'Social') !== false) {
            return 'Group';
        } else {
            return rand(1, 100) <= 60 ? 'Group' : 'Individual';
        }
    }

    private function getRandomTime(): string
    {
        $timeSlot = $this->timeSlots[array_rand($this->timeSlots)];
        return $timeSlot['start'];
    }

    private function getRandomEndTime(): string
    {
        $timeSlot = $this->timeSlots[array_rand($this->timeSlots)];
        return $timeSlot['end'];
    }

    private function getVenueForCategory($categoryType): string
    {
        $venueMapping = [
            'rehabilitation' => ['Bilik Terapi Pertuturan', 'Bilik Terapi Okupasi', 'Bilik Fisioterapi', 'Bilik Integrasi Sensori'],
            'academic' => ['Bilik Aktiviti Kumpulan', 'Makmal Komputer', 'Bilik Kemahiran Hidup'],
            'creative_social' => ['Bilik Seni', 'Bilik Muzik', 'Dewan Serbaguna', 'Taman Sensori'],
            'faith' => ['Bilik Aktiviti Kumpulan', 'Dewan Serbaguna']
        ];
        
        $venues = $venueMapping[$categoryType] ?? $this->venues;
        return $venues[array_rand($venues)];
    }

    private function getMaxParticipants($categoryType): int
    {
        $capacities = [
            'rehabilitation' => [1, 2, 3, 4], // Individual/small group therapy
            'academic' => [6, 8, 10, 12], // Academic classes
            'creative_social' => [8, 10, 12, 15], // Group activities
            'faith' => [10, 12, 15, 20] // Religious learning
        ];
        
        $options = $capacities[$categoryType] ?? [6, 8, 10];
        return $options[array_rand($options)];
    }

    private function generateActivityGoals($activityName): array
    {
        return [
            'Improve specific skills related to ' . $activityName,
            'Enhance participant engagement and motivation',
            'Develop independence and self-confidence',
            'Foster social interaction and communication'
        ];
    }

    private function generateActivityOutcomes($activityName): array
    {
        return [
            'Measurable improvement in target skills',
            'Increased participation and engagement',
            'Enhanced quality of life indicators',
            'Positive behavioral changes'
        ];
    }

    private function generateRequiredResources($activityName): array
    {
        $commonResources = ['Tables and chairs', 'Whiteboard', 'Basic stationery'];
        
        if (strpos($activityName, 'Computer') !== false) {
            return array_merge($commonResources, ['Computers', 'Software applications', 'Internet access']);
        } elseif (strpos($activityName, 'Art') !== false) {
            return array_merge($commonResources, ['Art supplies', 'Canvas', 'Paint brushes', 'Colored papers']);
        } elseif (strpos($activityName, 'Music') !== false) {
            return array_merge($commonResources, ['Musical instruments', 'Audio system', 'Music sheets']);
        } elseif (strpos($activityName, 'Physical') !== false || strpos($activityName, 'Therapy') !== false) {
            return array_merge($commonResources, ['Exercise equipment', 'Therapy tools', 'Safety mats']);
        }
        
        return $commonResources;
    }

    private function generateSessionNotes($status): string
    {
        if ($status === 'completed') {
            $notes = [
                'Session completed successfully with good participant engagement.',
                'All objectives met with positive outcomes observed.',
                'Participants showed improvement in target skills.',
                'Session adapted well to individual needs.'
            ];
        } elseif ($status === 'ongoing') {
            $notes = [
                'Session in progress with active participation.',
                'Good response from participants so far.',
                'Activities proceeding as planned.',
                'Positive engagement levels maintained.'
            ];
        } else {
            $notes = [
                'Session scheduled and materials prepared.',
                'Ready for upcoming session delivery.',
                'Participants confirmed for attendance.',
                'All preparations completed.'
            ];
        }
        
        return $notes[array_rand($notes)];
    }

    private function getAttendanceStatus($sessionStatus): string
    {
        if ($sessionStatus === 'completed') {
            $statuses = ['enrolled', 'enrolled', 'enrolled', 'completed']; // 75% completion rate
            return $statuses[array_rand($statuses)];
        } elseif ($sessionStatus === 'ongoing') {
            return 'enrolled'; // Still attending
        } else {
            return 'enrolled'; // Scheduled
        }
    }

    private function showCompletionSummary($activityCount, $sessionCount): void
    {
        $this->command->info("\n🎉 CREAMS Activity Seeder Completed Successfully!");
        $this->command->info("📊 Summary Statistics:");
        $this->command->line("   🎯 Total Activities Created: {$activityCount}");
        $this->command->line("   📅 Total Sessions Created: {$sessionCount}");
        
        // Show breakdown by category
        $categoryStats = DB::table('activities')
            ->join('categories', 'activities.category_id', '=', 'categories.id')
            ->selectRaw('categories.category_type, COUNT(*) as count')
            ->groupBy('categories.category_type')
            ->get();
            
        $this->command->info("\n📚 Activities by Category:");
        foreach ($categoryStats as $stat) {
            $this->command->line("   • " . ucfirst($stat->category_type) . ": {$stat->count} activities");
        }
        
        // Show session status breakdown
        $sessionStats = DB::table('activity_sessions')
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->get();
            
        $this->command->info("\n📅 Session Status Distribution:");
        foreach ($sessionStats as $stat) {
            $this->command->line("   • " . ucfirst($stat->status) . ": {$stat->count} sessions");
        }
        
        $this->command->info("\n✅ Ready for comprehensive attendance tracking implementation!");
        $this->command->info("🚀 All activities include proper category distribution and realistic scheduling!");
    }

    private function generateProgressNotes($attendanceStatus): string
    {
        $notes = [
            'present' => [
                'Active participation throughout the session',
                'Showed good engagement with activities',
                'Made progress on targeted skills',
                'Collaborated well with peers',
                'Demonstrated understanding of concepts'
            ],
            'absent' => [
                'Unable to attend session',
                'Will need catch-up activities',
                'Family scheduling conflict',
                'Health-related absence'
            ],
            'late' => [
                'Arrived late but participated well',
                'Caught up quickly with activities',
                'Transportation delay'
            ]
        ];

        $statusNotes = $notes[$attendanceStatus] ?? $notes['present'];
        return $statusNotes[array_rand($statusNotes)];
    }
}