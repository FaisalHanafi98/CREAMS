<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Activity;
use App\Models\ActivitySession;
use App\Models\SessionEnrollment;
use App\Models\SessionAttendance;
use App\Models\Trainee;
use App\Models\Category;
use App\Models\Centre;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class KuantanPagohStaffActivitiesSeeder extends Seeder
{
    private $activityTemplates = [
        'rehabilitation' => [
            ['name' => 'Physical Therapy Session', 'description' => 'Comprehensive physical rehabilitation focusing on motor skills development and mobility improvement'],
            ['name' => 'Occupational Therapy', 'description' => 'Daily living skills training and adaptive techniques for independence'],
            ['name' => 'Speech Therapy', 'description' => 'Communication skills development and speech improvement techniques'],
            ['name' => 'Cognitive Rehabilitation', 'description' => 'Memory enhancement and cognitive function improvement exercises'],
            ['name' => 'Sensory Integration Therapy', 'description' => 'Multi-sensory approach to improve processing and integration skills']
        ],
        'academic' => [
            ['name' => 'Basic Literacy Program', 'description' => 'Fundamental reading and writing skills development for various ability levels'],
            ['name' => 'Numeracy Skills Training', 'description' => 'Mathematics and number concepts tailored to individual learning needs'],
            ['name' => 'Life Skills Education', 'description' => 'Practical skills for daily living and community integration'],
            ['name' => 'Digital Literacy Workshop', 'description' => 'Computer and technology skills for modern communication'],
            ['name' => 'Vocational Training Program', 'description' => 'Job-related skills development and workplace preparation']
        ],
        'creative_social' => [
            ['name' => 'Art Therapy Workshop', 'description' => 'Creative expression through various art mediums for emotional development'],
            ['name' => 'Music Therapy Session', 'description' => 'Musical activities to enhance social interaction and emotional regulation'],
            ['name' => 'Drama and Role-Play', 'description' => 'Social skills development through theatrical activities and storytelling'],
            ['name' => 'Sports and Recreation', 'description' => 'Physical activities and team sports for fitness and social interaction'],
            ['name' => 'Community Integration', 'description' => 'Supervised community outings and social interaction activities']
        ],
        'faith' => [
            ['name' => 'Islamic Studies', 'description' => 'Quran reading, Islamic values, and spiritual development sessions'],
            ['name' => 'Prayer Training', 'description' => 'Practical guidance for daily prayers and religious observance'],
            ['name' => 'Character Building', 'description' => 'Moral and ethical development through religious teachings'],
            ['name' => 'Community Service', 'description' => 'Religious-based community service and charity activities']
        ]
    ];

    private $attendanceProfiles = [
        'excellent' => ['present' => 95, 'late' => 3, 'absent' => 2, 'excused' => 0],
        'good' => ['present' => 85, 'late' => 8, 'absent' => 5, 'excused' => 2],
        'average' => ['present' => 75, 'late' => 10, 'absent' => 12, 'excused' => 3],
        'struggling' => ['present' => 60, 'late' => 15, 'absent' => 20, 'excused' => 5],
        'irregular' => ['present' => 45, 'late' => 20, 'absent' => 30, 'excused' => 5]
    ];

    private $timeSlots = [
        ['start' => '08:00', 'end' => '09:30', 'duration' => 1.5],
        ['start' => '09:45', 'end' => '11:15', 'duration' => 1.5],
        ['start' => '11:30', 'end' => '12:30', 'duration' => 1.0],
        ['start' => '14:00', 'end' => '15:30', 'duration' => 1.5],
        ['start' => '15:45', 'end' => '17:15', 'duration' => 1.5]
    ];

    private $weekDays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];

    public function run()
    {
        DB::beginTransaction();
        
        try {
            Log::info('Starting Kuantan and Pagoh staff activities seeding...');
            
            // Get Kuantan and Pagoh centres
            $centres = Centre::whereIn('centre_id', ['02', '03'])->get();
            
            if ($centres->isEmpty()) {
                Log::warning('No Kuantan or Pagoh centres found');
                return;
            }

            $totalActivitiesCreated = 0;
            $totalSessionsCreated = 0;
            $totalEnrollmentsCreated = 0;
            $totalAttendanceRecords = 0;

            foreach ($centres as $centre) {
                Log::info("Processing centre: {$centre->centre_name} ({$centre->centre_id})");
                
                // Get all staff members for this centre
                $staffMembers = User::where('centre_id', $centre->centre_id)
                    ->whereIn('role', ['teacher', 'supervisor'])
                    ->where('status', 'active')
                    ->get();

                Log::info("Found {$staffMembers->count()} staff members in {$centre->centre_name}");

                foreach ($staffMembers as $staff) {
                    $activitiesForStaff = $this->createActivitiesForStaff($staff, $centre);
                    $totalActivitiesCreated += count($activitiesForStaff);

                    foreach ($activitiesForStaff as $activity) {
                        $sessions = $this->createSessionsForActivity($activity);
                        $totalSessionsCreated += count($sessions);

                        $selectedTrainees = $this->getSelectedTraineesForActivity($activity, $centre);
                        
                        $attendanceRecords = $this->createSessionEnrollmentsAndAttendance($activity, $sessions, $selectedTrainees);
                        $totalEnrollmentsCreated += count($selectedTrainees) * count($sessions);
                        $totalAttendanceRecords += $attendanceRecords;
                    }
                }
            }

            DB::commit();

            Log::info("Seeding completed successfully!");
            Log::info("Created: {$totalActivitiesCreated} activities, {$totalSessionsCreated} sessions, {$totalEnrollmentsCreated} enrollments, {$totalAttendanceRecords} attendance records");

            $this->command->info("✅ Successfully created:");
            $this->command->info("   📚 {$totalActivitiesCreated} activities");
            $this->command->info("   📅 {$totalSessionsCreated} sessions");
            $this->command->info("   👥 {$totalEnrollmentsCreated} enrollments");
            $this->command->info("   ✓ {$totalAttendanceRecords} attendance records");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error during seeding: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            throw $e;
        }
    }

    private function createActivitiesForStaff(User $staff, Centre $centre): array
    {
        $activities = [];
        $activitiesCount = rand(5, 8); // At least 5, up to 8 activities per staff

        // Get available categories
        $categories = Category::all();
        if ($categories->isEmpty()) {
            // Create default categories if none exist
            $this->createDefaultCategories();
            $categories = Category::all();
        }

        for ($i = 0; $i < $activitiesCount; $i++) {
            $category = $categories->random();
            $categoryType = $category->category_type ?? 'rehabilitation';
            
            // Get template based on category type
            $templates = $this->activityTemplates[$categoryType] ?? $this->activityTemplates['rehabilitation'];
            $template = $templates[array_rand($templates)];

            // Generate unique activity ID
            $activityId = $this->generateUniqueActivityId($centre->centre_id);

            // Random activity period (minimum 1 month, up to 6 months)
            $activityPeriod = rand(1, 6);
            $startDate = Carbon::now()->addDays(rand(-30, 30));
            $endDate = $startDate->copy()->addMonths($activityPeriod);

            $timeSlot = $this->getRandomTimeSlot();
            
            $activity = Activity::create([
                'activity_id' => $activityId,
                'activity_name' => $template['name'] . ' - ' . $centre->centre_name,
                'activity_description' => $template['description'],
                'activity_type' => $categoryType,
                'activity_date' => $startDate->format('Y-m-d'),
                'activity_start_time' => $timeSlot['start'],
                'activity_end_time' => $timeSlot['end'],
                'activity_location' => $this->getRandomLocation($centre->centre_name),
                'max_participants' => rand(8, 20),
                'current_participants' => 0,
                'activity_goals' => json_encode($this->generateActivityGoals($categoryType)),
                'activity_outcomes' => json_encode($this->generateActivityOutcomes($categoryType)),
                'activity_status' => 'ongoing',
                'centre_id' => $centre->centre_id,
                'category_id' => $category->id,
                'created_by' => $staff->id,
                'instructor_id' => $staff->id,
                'times_conducted' => rand(0, 10),
                'average_rating' => rand(35, 50) / 10, // 3.5 to 5.0 rating
                'duration_minutes' => $timeSlot['duration'] * 60,
                'min_participants' => rand(3, 6),
                'difficulty_level' => $this->getRandomDifficultyLevel(),
                'age_group' => $this->getRandomAgeGroup(),
                'activity_period' => $activityPeriod,
                'is_active' => true
            ]);
            
            // Store start and end dates in activity description or use activity_date
            $activity->update([
                'activity_description' => $activity->activity_description . 
                    " [Period: {$startDate->format('M Y')} - {$endDate->format('M Y')}]"
            ]);

            $activities[] = $activity;
            
            Log::info("Created activity: {$activity->activity_name} for {$staff->name}");
        }

        return $activities;
    }

    private function createSessionsForActivity(Activity $activity): array
    {
        $sessions = [];
        $sessionsPerWeek = rand(3, 5); // Minimum 3 sessions per week
        
        $startDate = Carbon::parse($activity->activity_date);
        $endDate = $startDate->copy()->addMonths($activity->activity_period);
        $currentDate = $startDate->copy();

        // Generate consistent time slot for this activity
        $timeSlot = $this->getRandomTimeSlot();
        
        // Select random days of the week for sessions
        $sessionDays = collect($this->weekDays)->random($sessionsPerWeek)->toArray();

        $sessionCounter = 1;

        while ($currentDate->lte($endDate)) {
            $dayName = $currentDate->format('l');
            
            if (in_array($dayName, $sessionDays)) {
                $session = ActivitySession::create([
                    'activity_id' => $activity->id,
                    'session_date' => $currentDate->format('Y-m-d'),
                    'scheduled_date' => $currentDate->format('Y-m-d'),
                    'start_time' => $timeSlot['start'],
                    'end_time' => $timeSlot['end'],
                    'venue' => $activity->activity_location,
                    'room_number' => $this->generateRoomNumber(),
                    'instructor_id' => $activity->instructor_id,
                    'teacher_id' => $activity->instructor_id,
                    'max_capacity' => $activity->max_participants,
                    'max_participants' => $activity->max_participants,
                    'current_enrollment' => 0,
                    'current_participants' => 0,
                    'session_status' => $this->getSessionStatus($currentDate),
                    'status' => $this->getSessionStatus($currentDate),
                    'attendance_marked' => $currentDate->isPast() ? 1 : 0,
                    'session_notes' => $this->generateSessionNotes($activity->activity_type),
                    'materials_used' => $this->generateMaterialsList(),
                    'session_rating' => $currentDate->isPast() ? rand(35, 50) / 10 : null,
                    'centre_id' => $activity->centre_id,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                $sessions[] = $session;
                $sessionCounter++;
            }
            
            $currentDate->addDay();
        }

        $sessionCount = count($sessions);
        Log::info("Created {$sessionCount} sessions for activity: {$activity->activity_name}");
        return $sessions;
    }

    private function getSelectedTraineesForActivity(Activity $activity, Centre $centre): array
    {
        // Get trainees from the same centre
        $trainees = Trainee::where('centre_id', $centre->centre_id)
            ->where('status', 'active')
            ->get();

        if ($trainees->isEmpty()) {
            Log::warning("No trainees found for centre: {$centre->centre_name}");
            return [];
        }

        // Enroll 60-90% of available trainees, but not exceeding max_participants
        $enrollmentCount = min(
            ceil($trainees->count() * (rand(60, 90) / 100)),
            $activity->max_participants
        );

        $selectedTrainees = $trainees->random(min($enrollmentCount, $trainees->count()))->toArray();

        // Update activity's current participants
        $activity->update(['current_participants' => count($selectedTrainees)]);

        Log::info("Selected " . count($selectedTrainees) . " trainees for activity: {$activity->activity_name}");
        return $selectedTrainees;
    }

    private function createSessionEnrollmentsAndAttendance(Activity $activity, array $sessions, array $selectedTrainees): int
    {
        $attendanceCount = 0;

        foreach ($sessions as $session) {
            $enrollmentCount = 0;
            foreach ($selectedTrainees as $trainee) {
                $enrollmentDate = Carbon::parse($activity->activity_date)->addDays(rand(-7, 7));
                
                // Create session-specific enrollment
                $sessionEnrollment = SessionEnrollment::create([
                    'session_id' => $session->id,
                    'trainee_id' => $trainee['id'],
                    'enrollment_date' => $enrollmentDate->format('Y-m-d'),
                    'enrollment_status' => 'enrolled',
                    'special_requirements' => $this->generateSpecialRequirements(),
                    'enrolled_by' => $activity->instructor_id,
                    'centre_id' => $activity->centre_id,
                    'created_at' => $enrollmentDate,
                    'updated_at' => now()
                ]);
                
                $enrollmentCount++;

                // Create logical attendance based on trainee profile
                $traineeModel = Trainee::find($trainee['id']);
                $attendanceProfile = $this->getTraineeAttendanceProfile($traineeModel);
                $attendanceStatus = $this->generateLogicalAttendanceStatus($attendanceProfile, $session);

                if ($attendanceStatus !== 'not_recorded') {
                    $attendanceRecord = SessionAttendance::create([
                        'session_id' => $session->id,
                        'trainee_id' => $trainee['id'],
                        'attendance_status' => $attendanceStatus,
                        'check_in_time' => $this->generateAttendanceTime($session, $attendanceStatus),
                        'check_out_time' => $this->generateCheckOutTime($session, $attendanceStatus),
                        'participation_score' => $this->generateParticipationLevel($attendanceStatus),
                        'session_progress_notes' => $this->generateAttendanceNotes($attendanceStatus),
                        'behavioral_notes' => $this->generateBehaviorNotes(),
                        'goals_achieved' => rand(0, 1),
                        'recorded_by' => $activity->instructor_id,
                        'centre_id' => $activity->centre_id,
                        'created_at' => Carbon::parse($session->session_date),
                        'updated_at' => Carbon::parse($session->session_date)
                    ]);

                    $attendanceCount++;
                }
            }

            // Update session's enrolled count
            $session->update(['current_enrollment' => $enrollmentCount, 'current_participants' => $enrollmentCount]);
        }

        return $attendanceCount;
    }

    private function getTraineeAttendanceProfile(Trainee $trainee): string
    {
        // Generate realistic attendance profile based on trainee characteristics
        $condition = strtolower($trainee->trainee_condition ?? '');
        
        if (str_contains($condition, 'autism') || str_contains($condition, 'adhd')) {
            return rand(1, 10) <= 3 ? 'struggling' : 'average';
        } elseif (str_contains($condition, 'down syndrome') || str_contains($condition, 'intellectual')) {
            return rand(1, 10) <= 6 ? 'good' : 'excellent';
        } elseif (str_contains($condition, 'cerebral palsy') || str_contains($condition, 'physical')) {
            return rand(1, 10) <= 4 ? 'irregular' : 'average';
        } else {
            // Default distribution
            $rand = rand(1, 100);
            if ($rand <= 15) return 'excellent';
            if ($rand <= 40) return 'good';
            if ($rand <= 70) return 'average';
            if ($rand <= 90) return 'struggling';
            return 'irregular';
        }
    }

    private function generateLogicalAttendanceStatus(string $profile, ActivitySession $session): string
    {
        $sessionDate = Carbon::parse($session->session_date);
        $today = Carbon::now();

        // Don't create attendance for future sessions
        if ($sessionDate->isFuture()) {
            return 'not_recorded';
        }

        $probabilities = $this->attendanceProfiles[$profile];
        $rand = rand(1, 100);

        if ($rand <= $probabilities['present']) {
            return 'present';
        } elseif ($rand <= $probabilities['present'] + $probabilities['late']) {
            return 'late';
        } elseif ($rand <= $probabilities['present'] + $probabilities['late'] + $probabilities['excused']) {
            return 'excused';
        } else {
            return 'absent';
        }
    }

    private function generateAttendanceTime(ActivitySession $session, string $status): ?string
    {
        if ($status === 'absent' || $status === 'excused') {
            return null;
        }

        $sessionStart = Carbon::parse($session->session_date)->setTimeFromTimeString($session->start_time);
        
        if ($status === 'late') {
            // Arrive 5-30 minutes late
            $arrivalTime = $sessionStart->copy()->addMinutes(rand(5, 30));
        } else {
            // Arrive 0-10 minutes early or on time
            $arrivalTime = $sessionStart->copy()->subMinutes(rand(0, 10));
        }

        return $arrivalTime->format('H:i:s');
    }

    private function generateCheckOutTime(ActivitySession $session, string $status): ?string
    {
        if ($status === 'absent' || $status === 'excused') {
            return null;
        }

        $sessionEnd = Carbon::parse($session->session_date)->setTimeFromTimeString($session->end_time);
        
        if ($status === 'late') {
            // Might leave slightly early (0-15 minutes early)
            $departureTime = $sessionEnd->copy()->subMinutes(rand(0, 15));
        } else {
            // Stay until end or leave up to 10 minutes after
            $departureTime = $sessionEnd->copy()->addMinutes(rand(-5, 10));
        }

        return $departureTime->format('H:i:s');
    }

    private function generateUniqueActivityId(string $centreId): string
    {
        do {
            $activityId = strtoupper($centreId . substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 6));
        } while (Activity::where('activity_id', $activityId)->exists());

        return $activityId;
    }

    private function getRandomTimeSlot(): array
    {
        return $this->timeSlots[array_rand($this->timeSlots)];
    }

    private function getRandomLocation(string $centreName): string
    {
        $locations = [
            "Therapy Room 1 - {$centreName}",
            "Therapy Room 2 - {$centreName}",
            "Classroom A - {$centreName}",
            "Classroom B - {$centreName}",
            "Activity Hall - {$centreName}",
            "Multi-purpose Room - {$centreName}",
            "Outdoor Area - {$centreName}",
            "Computer Lab - {$centreName}"
        ];

        return $locations[array_rand($locations)];
    }

    private function generateActivityGoals(string $categoryType): array
    {
        $goals = [
            'rehabilitation' => [
                'Improve motor skills and coordination',
                'Enhance cognitive function and memory',
                'Develop communication abilities',
                'Increase independence in daily activities'
            ],
            'academic' => [
                'Develop literacy and numeracy skills',
                'Improve problem-solving abilities',
                'Enhance learning strategies',
                'Build academic confidence'
            ],
            'creative_social' => [
                'Foster creative expression',
                'Improve social interaction skills',
                'Build emotional regulation',
                'Develop teamwork abilities'
            ],
            'faith' => [
                'Strengthen spiritual foundation',
                'Develop moral character',
                'Build community connections',
                'Practice religious observances'
            ]
        ];

        return $goals[$categoryType] ?? $goals['rehabilitation'];
    }

    private function generateActivityOutcomes(string $categoryType): array
    {
        $outcomes = [
            'rehabilitation' => [
                'Measurable improvement in target skills',
                'Increased functional independence',
                'Enhanced quality of life',
                'Progress toward individual goals'
            ],
            'academic' => [
                'Improved academic performance',
                'Increased learning engagement',
                'Better study habits',
                'Enhanced cognitive abilities'
            ],
            'creative_social' => [
                'Improved self-expression',
                'Better social relationships',
                'Increased confidence',
                'Enhanced emotional well-being'
            ],
            'faith' => [
                'Stronger spiritual practice',
                'Improved moral behavior',
                'Community engagement',
                'Personal growth'
            ]
        ];

        return $outcomes[$categoryType] ?? $outcomes['rehabilitation'];
    }

    private function getRandomAgeGroup(): string
    {
        $ageGroups = ['children', 'adolescents', 'adults', 'elderly', 'all_ages'];
        return $ageGroups[array_rand($ageGroups)];
    }

    private function getRandomDifficultyLevel(): string
    {
        $levels = ['beginner', 'intermediate', 'advanced'];
        return $levels[array_rand($levels)];
    }

    private function generateRoomNumber(): string
    {
        return 'R' . str_pad(rand(101, 299), 3, '0', STR_PAD_LEFT);
    }

    private function getSessionStatus(Carbon $sessionDate): string
    {
        $now = Carbon::now();
        
        if ($sessionDate->isFuture()) {
            return 'scheduled';
        } elseif ($sessionDate->isPast()) {
            return rand(1, 10) <= 9 ? 'completed' : 'cancelled';
        } else {
            return 'ongoing';
        }
    }

    private function generateSessionNotes(string $activityType): string
    {
        $notes = [
            'Regular weekly session as scheduled',
            'Focus on individual progress tracking',
            'Group activities and peer interaction',
            'Assessment and evaluation session',
            'Make-up session for previous absence'
        ];

        return $notes[array_rand($notes)];
    }

    private function generateSpecialRequirements(): ?string
    {
        $requirements = [
            null, // No special requirements
            'Wheelchair accessible seating required',
            'Visual aids and large print materials needed',
            'Hearing assistance device required',
            'Extra time for activities due to processing delays',
            'Sensory break area access needed',
            'Modified equipment for limited mobility',
            'Behavioral support strategies required',
            'Dietary restrictions - no allergens'
        ];

        return $requirements[array_rand($requirements)];
    }

    private function generateAttendanceNotes(string $status): ?string
    {
        $notes = [
            'present' => [
                'Actively participated throughout session',
                'Good engagement with activities',
                'On time and prepared',
                'Positive interaction with peers'
            ],
            'late' => [
                'Arrived late due to transportation',
                'Late arrival but caught up quickly',
                'Traffic delay - participated fully',
                'Medical appointment caused delay'
            ],
            'absent' => [
                'Illness - unable to attend',
                'Family emergency',
                'Medical appointment conflict',
                'Unexcused absence'
            ],
            'excused' => [
                'Pre-approved medical appointment',
                'Family event - excused absence',
                'School examination conflict',
                'Religious observance'
            ]
        ];

        $statusNotes = $notes[$status] ?? ['No additional notes'];
        return $statusNotes[array_rand($statusNotes)];
    }

    private function generateParticipationLevel(string $status): int
    {
        if ($status === 'absent' || $status === 'excused') {
            return 0;
        }

        if ($status === 'late') {
            return rand(60, 80); // Slightly lower participation (60-80%)
        }

        return rand(75, 100); // Normal to high participation (75-100%)
    }

    private function generateBehaviorNotes(): string
    {
        $notes = [
            'Cooperative and engaged',
            'Required minimal prompting',
            'Excellent social interaction',
            'Focused throughout session',
            'Needed some encouragement',
            'Very enthusiastic participation',
            'Worked well independently',
            'Good peer interaction'
        ];

        return $notes[array_rand($notes)];
    }

    private function generateMaterialsList(): string
    {
        $materials = [
            'Therapy balls, exercise mats, resistance bands',
            'Educational worksheets, pencils, markers',
            'Musical instruments, speakers, microphone',
            'Art supplies, canvas, paintbrushes',
            'Computer, tablets, educational software',
            'Books, writing materials, flashcards',
            'Sports equipment, cones, balls',
            'Sensory toys, textured materials'
        ];

        return $materials[array_rand($materials)];
    }

    private function createDefaultCategories(): void
    {
        $categories = [
            ['category_name' => 'Rehabilitation Services', 'category_type' => 'rehabilitation'],
            ['category_name' => 'Academic Programs', 'category_type' => 'academic'],
            ['category_name' => 'Creative & Social Activities', 'category_type' => 'creative_social'],
            ['category_name' => 'Faith-Based Programs', 'category_type' => 'faith']
        ];

        foreach ($categories as $category) {
            Category::firstOrCreate(
                ['category_name' => $category['category_name']],
                $category
            );
        }
    }
}