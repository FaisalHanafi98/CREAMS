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

class ComprehensiveActivitiesReseeder extends Seeder
{
    private $activityTemplates = [
        'rehabilitation' => [
            ['name' => 'Physical Therapy Session', 'description' => 'Comprehensive physical rehabilitation focusing on motor skills development'],
            ['name' => 'Occupational Therapy', 'description' => 'Daily living skills training and adaptive techniques'],
            ['name' => 'Speech Therapy', 'description' => 'Communication skills development and speech improvement'],
            ['name' => 'Cognitive Rehabilitation', 'description' => 'Memory enhancement and cognitive function improvement'],
            ['name' => 'Sensory Integration Therapy', 'description' => 'Multi-sensory approach to improve processing skills']
        ],
        'academic' => [
            ['name' => 'Basic Literacy Program', 'description' => 'Reading and writing skills development'],
            ['name' => 'Numeracy Skills Training', 'description' => 'Mathematics and number concepts training'],
            ['name' => 'Life Skills Education', 'description' => 'Practical skills for daily living'],
            ['name' => 'Digital Literacy Workshop', 'description' => 'Computer and technology skills'],
            ['name' => 'Vocational Training Program', 'description' => 'Job-related skills development']
        ],
        'recreational' => [
            ['name' => 'Art Therapy Workshop', 'description' => 'Creative expression through art mediums'],
            ['name' => 'Music Therapy Session', 'description' => 'Musical activities for emotional development'],
            ['name' => 'Drama and Role-Play', 'description' => 'Social skills through theatrical activities'],
            ['name' => 'Sports and Recreation', 'description' => 'Physical activities and team sports'],
            ['name' => 'Community Integration', 'description' => 'Community outings and social interaction']
        ],
        'faith' => [
            ['name' => 'Islamic Studies', 'description' => 'Quran reading and Islamic values'],
            ['name' => 'Prayer Training', 'description' => 'Practical guidance for daily prayers'],
            ['name' => 'Character Building', 'description' => 'Moral and ethical development'],
            ['name' => 'Community Service', 'description' => 'Religious-based community service']
        ]
    ];

    private $attendanceProfiles = [
        'excellent' => ['present' => 95, 'late' => 3, 'absent' => 2],
        'good' => ['present' => 85, 'late' => 8, 'absent' => 7],
        'average' => ['present' => 75, 'late' => 10, 'absent' => 15],
        'struggling' => ['present' => 60, 'late' => 15, 'absent' => 25]
    ];

    private $timeSlots = [
        ['start' => '08:00', 'end' => '09:30'],
        ['start' => '09:45', 'end' => '11:15'],
        ['start' => '11:30', 'end' => '12:30'],
        ['start' => '14:00', 'end' => '15:30'],
        ['start' => '15:45', 'end' => '17:15']
    ];

    private $weekDays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];

    public function run()
    {
        DB::beginTransaction();
        
        try {
            Log::info('Starting Comprehensive Activities Reseeding...');
            
            // Clean existing activity data
            $this->cleanExistingData();
            
            // Create default categories if needed
            $this->ensureCategories();
            
            // Get all centres
            $centres = Centre::whereIn('centre_id', ['01', '02', '03'])->get();
            
            $totalStats = [
                'activities' => 0,
                'sessions' => 0,
                'enrollments' => 0,
                'attendance' => 0
            ];

            foreach ($centres as $centre) {
                Log::info("Processing centre: {$centre->centre_name}");
                $centreStats = $this->seedCentreActivities($centre);
                
                $totalStats['activities'] += $centreStats['activities'];
                $totalStats['sessions'] += $centreStats['sessions'];
                $totalStats['enrollments'] += $centreStats['enrollments'];
                $totalStats['attendance'] += $centreStats['attendance'];
            }
            
            DB::commit();
            
            Log::info('Comprehensive Activities Reseeding completed successfully!');
            Log::info("Total created: {$totalStats['activities']} activities, {$totalStats['sessions']} sessions, {$totalStats['enrollments']} enrollments, {$totalStats['attendance']} attendance records");
            
            $this->command->info("✅ Successfully reseeded all centres:");
            $this->command->info("   📚 {$totalStats['activities']} activities");
            $this->command->info("   📅 {$totalStats['sessions']} sessions");
            $this->command->info("   👥 {$totalStats['enrollments']} enrollments");
            $this->command->info("   ✓ {$totalStats['attendance']} attendance records");
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error during comprehensive reseeding: ' . $e->getMessage());
            throw $e;
        }
    }

    private function cleanExistingData()
    {
        Log::info('Cleaning existing activity data...');
        
        // Delete using raw queries to avoid model constraints
        DB::unprepared('SET FOREIGN_KEY_CHECKS=0');
        DB::unprepared('DELETE FROM session_attendance');
        DB::unprepared('DELETE FROM session_enrollments');
        DB::unprepared('DELETE FROM activity_sessions');
        DB::unprepared('DELETE FROM activities');
        DB::unprepared('SET FOREIGN_KEY_CHECKS=1');
        
        Log::info('Existing activity data cleaned');
    }

    private function ensureCategories()
    {
        $categories = [
            ['category_name' => 'Rehabilitation Services', 'category_type' => 'rehabilitation'],
            ['category_name' => 'Academic Programs', 'category_type' => 'academic'], 
            ['category_name' => 'Recreational Activities', 'category_type' => 'recreational'],
            ['category_name' => 'Faith-Based Programs', 'category_type' => 'faith']
        ];

        foreach ($categories as $category) {
            Category::firstOrCreate(
                ['category_name' => $category['category_name']],
                $category
            );
        }
    }

    private function seedCentreActivities(Centre $centre)
    {
        $stats = ['activities' => 0, 'sessions' => 0, 'enrollments' => 0, 'attendance' => 0];
        
        // Get staff for this centre
        $staff = User::where('centre_id', $centre->centre_id)
            ->whereIn('role', ['admin', 'supervisor', 'teacher'])
            ->where('status', 'active')
            ->get();

        if ($staff->isEmpty()) {
            Log::warning("No staff found for centre: {$centre->centre_name}");
            return $stats;
        }

        // Determine activities per staff based on centre and role
        $activitiesPerStaff = $this->getActivitiesPerStaff($centre->centre_id);

        foreach ($staff as $staffMember) {
            $roleActivities = $activitiesPerStaff[$staffMember->role] ?? 0;
            
            for ($i = 0; $i < $roleActivities; $i++) {
                $activity = $this->createActivity($staffMember, $centre);
                if ($activity) {
                    $stats['activities']++;
                    
                    $sessions = $this->createSessionsForActivity($activity);
                    $stats['sessions'] += count($sessions);
                    
                    $attendanceRecords = $this->createEnrollmentsAndAttendance($activity, $sessions, $centre);
                    $stats['enrollments'] += $attendanceRecords['enrollments'];
                    $stats['attendance'] += $attendanceRecords['attendance'];
                }
            }
        }

        return $stats;
    }

    private function getActivitiesPerStaff($centreId)
    {
        // Different activity loads based on centre
        if ($centreId === '01') { // Gombak - reduced
            return [
                'admin' => 1,
                'supervisor' => 2,
                'teacher' => 1
            ];
        } else { // Kuantan and Pagoh - full load
            return [
                'admin' => 2,
                'supervisor' => 3,
                'teacher' => 4
            ];
        }
    }

    private function createActivity(User $staff, Centre $centre)
    {
        $categories = Category::all();
        if ($categories->isEmpty()) {
            return null;
        }

        $category = $categories->random();
        $categoryType = $category->category_type ?? 'rehabilitation';
        
        $templates = $this->activityTemplates[$categoryType] ?? $this->activityTemplates['rehabilitation'];
        $template = $templates[array_rand($templates)];

        $activityId = $this->generateUniqueActivityId($centre->centre_id);
        $timeSlot = $this->timeSlots[array_rand($this->timeSlots)];
        
        // Activity duration: 1-4 months
        $durationMonths = rand(1, 4);
        $startDate = Carbon::now()->addDays(rand(-30, 30));
        
        return Activity::create([
            'activity_id' => $activityId,
            'activity_name' => $template['name'] . ' - ' . $centre->centre_name,
            'activity_description' => $template['description'],
            'activity_type' => $categoryType,
            'activity_date' => $startDate->format('Y-m-d'),
            'activity_start_time' => $timeSlot['start'],
            'activity_end_time' => $timeSlot['end'],
            'activity_location' => $this->getRandomLocation($centre->centre_name),
            'max_participants' => rand(8, 15),
            'current_participants' => 0,
            'activity_goals' => json_encode($this->generateActivityGoals($categoryType)),
            'activity_outcomes' => json_encode($this->generateActivityOutcomes($categoryType)),
            'activity_status' => 'ongoing',
            'centre_id' => $centre->centre_id,
            'category_id' => $category->id,
            'created_by' => $staff->id,
            'instructor_id' => $staff->id,
            'times_conducted' => rand(0, 5),
            'average_rating' => rand(35, 50) / 10,
            'duration_minutes' => 90,
            'min_participants' => rand(3, 6),
            'difficulty_level' => ['beginner', 'intermediate', 'advanced'][array_rand(['beginner', 'intermediate', 'advanced'])],
            'age_group' => ['children', 'adolescents', 'adults', 'all_ages'][array_rand(['children', 'adolescents', 'adults', 'all_ages'])],
            'activity_period' => $durationMonths,
            'is_active' => true
        ]);
    }

    private function createSessionsForActivity(Activity $activity)
    {
        $sessions = [];
        $sessionsPerWeek = rand(2, 4); // 2-4 sessions per week
        
        $startDate = Carbon::parse($activity->activity_date);
        $endDate = $startDate->copy()->addMonths($activity->activity_period);
        $currentDate = $startDate->copy();

        $timeSlot = ['start' => $activity->activity_start_time, 'end' => $activity->activity_end_time];
        $sessionDays = collect($this->weekDays)->random($sessionsPerWeek)->toArray();

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
                    'room_number' => 'R' . str_pad(rand(101, 299), 3, '0', STR_PAD_LEFT),
                    'instructor_id' => $activity->instructor_id,
                    'teacher_id' => $activity->instructor_id,
                    'max_capacity' => $activity->max_participants,
                    'max_participants' => $activity->max_participants,
                    'current_enrollment' => 0,
                    'current_participants' => 0,
                    'session_status' => $this->getSessionStatus($currentDate),
                    'status' => $this->getSessionStatus($currentDate),
                    'attendance_marked' => $currentDate->isPast() ? 1 : 0,
                    'session_notes' => 'Regular session as scheduled',
                    'materials_used' => $this->generateMaterialsList(),
                    'session_rating' => $currentDate->isPast() ? rand(35, 50) / 10 : null,
                    'centre_id' => $activity->centre_id,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                $sessions[] = $session;
            }
            
            $currentDate->addDay();
        }

        return $sessions;
    }

    private function createEnrollmentsAndAttendance(Activity $activity, array $sessions, Centre $centre)
    {
        $stats = ['enrollments' => 0, 'attendance' => 0];
        
        // Get trainees for this centre
        $trainees = Trainee::where('centre_id', $centre->centre_id)
            ->where('status', 'active')
            ->get();

        if ($trainees->isEmpty()) {
            return $stats;
        }

        // Select 60-80% of available trainees, max capacity
        $enrollmentCount = min(
            ceil($trainees->count() * (rand(60, 80) / 100)),
            $activity->max_participants
        );

        $selectedTrainees = $trainees->random(min($enrollmentCount, $trainees->count()));

        foreach ($sessions as $session) {
            foreach ($selectedTrainees as $trainee) {
                // Create enrollment for each session
                $enrollment = SessionEnrollment::create([
                    'session_id' => $session->id,
                    'trainee_id' => $trainee->id,
                    'enrollment_date' => Carbon::parse($activity->activity_date)->format('Y-m-d'),
                    'enrollment_status' => 'enrolled',
                    'special_requirements' => $this->generateSpecialRequirements(),
                    'enrolled_by' => $activity->instructor_id,
                    'centre_id' => $centre->centre_id,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                $stats['enrollments']++;

                // Create attendance if session is in the past
                if (Carbon::parse($session->session_date)->isPast()) {
                    $attendanceProfile = $this->getTraineeAttendanceProfile($trainee);
                    $attendanceStatus = $this->generateAttendanceStatus($attendanceProfile);

                    SessionAttendance::create([
                        'session_id' => $session->id,
                        'trainee_id' => $trainee->id,
                        'attendance_status' => $attendanceStatus,
                        'check_in_time' => $this->generateCheckInTime($session, $attendanceStatus),
                        'check_out_time' => $this->generateCheckOutTime($session, $attendanceStatus),
                        'participation_score' => $this->generateParticipationScore($attendanceStatus),
                        'session_progress_notes' => $this->generateProgressNotes($attendanceStatus),
                        'behavioral_notes' => $this->generateBehaviorNotes(),
                        'goals_achieved' => rand(0, 1),
                        'recorded_by' => $activity->instructor_id,
                        'centre_id' => $centre->centre_id,
                        'created_at' => Carbon::parse($session->session_date),
                        'updated_at' => Carbon::parse($session->session_date)
                    ]);

                    $stats['attendance']++;
                }
            }

            // Update session enrollment count
            $session->update([
                'current_enrollment' => $selectedTrainees->count(),
                'current_participants' => $selectedTrainees->count()
            ]);
        }

        // Update activity participant count
        $activity->update(['current_participants' => $selectedTrainees->count()]);

        return $stats;
    }

    private function generateUniqueActivityId($centreId)
    {
        do {
            $activityId = strtoupper($centreId . substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 6));
        } while (Activity::where('activity_id', $activityId)->exists());

        return $activityId;
    }

    private function getRandomLocation($centreName)
    {
        $locations = [
            "Therapy Room 1 - {$centreName}",
            "Therapy Room 2 - {$centreName}",
            "Classroom A - {$centreName}",
            "Activity Hall - {$centreName}",
            "Multi-purpose Room - {$centreName}"
        ];

        return $locations[array_rand($locations)];
    }

    private function generateActivityGoals($categoryType)
    {
        $goals = [
            'rehabilitation' => ['Improve motor skills', 'Enhance cognitive function', 'Develop communication'],
            'academic' => ['Develop literacy skills', 'Improve problem-solving', 'Build confidence'],
            'recreational' => ['Foster creativity', 'Improve social skills', 'Build emotional regulation'],
            'faith' => ['Strengthen spiritual foundation', 'Develop moral character', 'Build community connections']
        ];

        return $goals[$categoryType] ?? $goals['rehabilitation'];
    }

    private function generateActivityOutcomes($categoryType)
    {
        $outcomes = [
            'rehabilitation' => ['Measurable skill improvement', 'Increased independence', 'Enhanced quality of life'],
            'academic' => ['Improved academic performance', 'Better study habits', 'Enhanced cognitive abilities'],
            'recreational' => ['Improved self-expression', 'Better relationships', 'Increased confidence'],
            'faith' => ['Stronger spiritual practice', 'Improved moral behavior', 'Community engagement']
        ];

        return $outcomes[$categoryType] ?? $outcomes['rehabilitation'];
    }

    private function getSessionStatus($sessionDate)
    {
        if ($sessionDate->isFuture()) {
            return 'scheduled';
        } elseif ($sessionDate->isPast()) {
            return rand(1, 10) <= 9 ? 'completed' : 'cancelled';
        } else {
            return 'ongoing';
        }
    }

    private function generateMaterialsList()
    {
        $materials = [
            'Therapy equipment and exercise mats',
            'Educational worksheets and materials',
            'Art supplies and creative materials',
            'Computer and digital tools',
            'Sports equipment and games'
        ];

        return $materials[array_rand($materials)];
    }

    private function generateSpecialRequirements()
    {
        $requirements = [
            null,
            'Wheelchair accessible seating',
            'Visual aids needed',
            'Extra time required',
            'Sensory break access'
        ];

        return $requirements[array_rand($requirements)];
    }

    private function getTraineeAttendanceProfile($trainee)
    {
        $condition = strtolower($trainee->trainee_condition ?? '');
        
        if (str_contains($condition, 'autism')) {
            return rand(1, 10) <= 3 ? 'struggling' : 'average';
        } elseif (str_contains($condition, 'down syndrome')) {
            return rand(1, 10) <= 6 ? 'good' : 'excellent';
        } else {
            $rand = rand(1, 100);
            if ($rand <= 20) return 'excellent';
            if ($rand <= 50) return 'good';
            if ($rand <= 80) return 'average';
            return 'struggling';
        }
    }

    private function generateAttendanceStatus($profile)
    {
        $probabilities = $this->attendanceProfiles[$profile];
        $rand = rand(1, 100);

        if ($rand <= $probabilities['present']) {
            return 'present';
        } elseif ($rand <= $probabilities['present'] + $probabilities['late']) {
            return 'late';
        } else {
            return 'absent';
        }
    }

    private function generateCheckInTime($session, $status)
    {
        if ($status === 'absent') return null;

        $sessionStart = Carbon::parse($session->start_time);
        
        if ($status === 'late') {
            return $sessionStart->copy()->addMinutes(rand(5, 20))->format('H:i:s');
        } else {
            return $sessionStart->copy()->subMinutes(rand(0, 10))->format('H:i:s');
        }
    }

    private function generateCheckOutTime($session, $status)
    {
        if ($status === 'absent') return null;

        $sessionEnd = Carbon::parse($session->end_time);
        return $sessionEnd->copy()->addMinutes(rand(-10, 5))->format('H:i:s');
    }

    private function generateParticipationScore($status)
    {
        if ($status === 'absent') return 0;
        if ($status === 'late') return rand(60, 80);
        return rand(75, 100);
    }

    private function generateProgressNotes($status)
    {
        $notes = [
            'present' => ['Good participation throughout session', 'Actively engaged with activities'],
            'late' => ['Late arrival but participated well', 'Caught up quickly with activities'],
            'absent' => ['Did not attend session', 'Absence noted']
        ];

        $statusNotes = $notes[$status] ?? $notes['present'];
        return $statusNotes[array_rand($statusNotes)];
    }

    private function generateBehaviorNotes()
    {
        $notes = [
            'Cooperative and engaged',
            'Required some encouragement',
            'Excellent participation',
            'Focused throughout session',
            'Good peer interaction'
        ];

        return $notes[array_rand($notes)];
    }
}