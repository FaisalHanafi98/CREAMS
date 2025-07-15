<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Activity;
use App\Models\ActivitySession;
use App\Models\ActivitySchedule;
use App\Models\ActivityEnrollment;
use App\Models\SessionEnrollment;
use App\Models\Category;
use App\Models\Trainee;
use App\Models\Users;
use App\Models\Centres;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EnhancedActivitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {
            $this->createActivitiesWithCategories();
            $this->createSessionsAndSchedules();
            $this->createEnrollments();
        });
    }

    /**
     * Create activities using the new Category model
     */
    private function createActivitiesWithCategories()
    {
        $categories = Category::all();
        $centres = Centres::all();
        $teachers = Users::whereIn('role', ['admin', 'supervisor', 'teacher'])->get();

        $activitiesData = [
            // Rehabilitation Activities
            [
                'category_name' => 'Physical Therapy',
                'activities' => [
                    [
                        'activity_name' => 'Motor Skills Development Program',
                        'activity_code' => 'PHY-001',
                        'description' => 'Comprehensive program focusing on gross and fine motor skills development through structured exercises and therapeutic activities.',
                        'objectives' => 'Improve gross motor coordination, enhance fine motor precision, develop bilateral coordination, strengthen core muscles',
                        'materials_needed' => 'Exercise mats, balance balls, resistance bands, fine motor tools, obstacle course equipment',
                        'age_group' => '6-12 years',
                        'difficulty_level' => 'Beginner',
                        'min_participants' => 2,
                        'max_participants' => 6,
                        'duration_minutes' => 60
                    ],
                    [
                        'activity_name' => 'Advanced Mobility Training',
                        'activity_code' => 'PHY-002',
                        'description' => 'Intensive mobility training for individuals with physical disabilities, focusing on independence and functional movement.',
                        'objectives' => 'Enhance mobility independence, improve balance and stability, develop adaptive movement strategies',
                        'materials_needed' => 'Walkers, parallel bars, mobility aids, safety equipment',
                        'age_group' => '13-17 years',
                        'difficulty_level' => 'Advanced',
                        'min_participants' => 1,
                        'max_participants' => 4,
                        'duration_minutes' => 90
                    ]
                ]
            ],
            [
                'category_name' => 'Occupational Therapy',
                'activities' => [
                    [
                        'activity_name' => 'Daily Living Skills Workshop',
                        'activity_code' => 'OT-001',
                        'description' => 'Practical training in essential daily living skills including personal hygiene, dressing, and basic self-care.',
                        'objectives' => 'Master personal hygiene routines, develop independent dressing skills, learn meal preparation basics',
                        'materials_needed' => 'Adaptive tools, practice clothing, hygiene supplies, kitchen equipment',
                        'age_group' => '9-12 years',
                        'difficulty_level' => 'Intermediate',
                        'min_participants' => 3,
                        'max_participants' => 8,
                        'duration_minutes' => 75
                    ],
                    [
                        'activity_name' => 'Sensory Processing Integration',
                        'activity_code' => 'OT-002',
                        'description' => 'Specialized therapy targeting sensory processing challenges through structured sensory experiences.',
                        'objectives' => 'Improve sensory regulation, enhance sensory discrimination, develop coping strategies',
                        'materials_needed' => 'Sensory tools, weighted items, textured materials, calming equipment',
                        'age_group' => '3-8 years',
                        'difficulty_level' => 'Beginner',
                        'min_participants' => 1,
                        'max_participants' => 4,
                        'duration_minutes' => 45
                    ]
                ]
            ],
            [
                'category_name' => 'Speech Therapy',
                'activities' => [
                    [
                        'activity_name' => 'Communication Development Program',
                        'activity_code' => 'ST-001',
                        'description' => 'Comprehensive speech and language therapy targeting communication skills development.',
                        'objectives' => 'Improve articulation clarity, expand vocabulary, develop sentence structure, enhance social communication',
                        'materials_needed' => 'Speech therapy tools, picture cards, communication boards, recording equipment',
                        'age_group' => '3-8 years',
                        'difficulty_level' => 'Beginner',
                        'min_participants' => 1,
                        'max_participants' => 3,
                        'duration_minutes' => 45
                    ],
                    [
                        'activity_name' => 'Advanced Language Skills',
                        'activity_code' => 'ST-002',
                        'description' => 'Advanced language therapy focusing on complex communication, reading comprehension, and social pragmatics.',
                        'objectives' => 'Master complex sentence structures, improve reading fluency, develop conversational skills',
                        'materials_needed' => 'Advanced reading materials, conversation prompts, social scenario cards',
                        'age_group' => '9-17 years',
                        'difficulty_level' => 'Advanced',
                        'min_participants' => 2,
                        'max_participants' => 5,
                        'duration_minutes' => 60
                    ]
                ]
            ],
            [
                'category_name' => 'Mathematics',
                'activities' => [
                    [
                        'activity_name' => 'Foundation Mathematics Skills',
                        'activity_code' => 'MATH-001',
                        'description' => 'Building fundamental mathematical concepts through hands-on activities and visual learning approaches.',
                        'objectives' => 'Master number recognition, develop counting skills, understand basic operations, apply math to daily life',
                        'materials_needed' => 'Manipulatives, counting tools, visual aids, interactive math games',
                        'age_group' => '6-10 years',
                        'difficulty_level' => 'Beginner',
                        'min_participants' => 4,
                        'max_participants' => 10,
                        'duration_minutes' => 60
                    ],
                    [
                        'activity_name' => 'Practical Mathematics Applications',
                        'activity_code' => 'MATH-002',
                        'description' => 'Real-world mathematics applications including money management, time concepts, and basic measurements.',
                        'objectives' => 'Apply math in daily situations, manage money effectively, understand time and measurement',
                        'materials_needed' => 'Play money, clocks, measuring tools, calculators, real-life scenarios',
                        'age_group' => '11-17 years',
                        'difficulty_level' => 'Intermediate',
                        'min_participants' => 3,
                        'max_participants' => 8,
                        'duration_minutes' => 75
                    ]
                ]
            ],
            [
                'category_name' => 'Computer Skills',
                'activities' => [
                    [
                        'activity_name' => 'Digital Literacy Basics',
                        'activity_code' => 'COMP-001',
                        'description' => 'Introduction to computer operation, basic software use, and digital safety concepts.',
                        'objectives' => 'Master basic computer operation, learn essential software, understand digital safety',
                        'materials_needed' => 'Computers, tablets, adaptive keyboards, educational software',
                        'age_group' => '8-15 years',
                        'difficulty_level' => 'Beginner',
                        'min_participants' => 2,
                        'max_participants' => 8,
                        'duration_minutes' => 60
                    ],
                    [
                        'activity_name' => 'Assistive Technology Training',
                        'activity_code' => 'COMP-002',
                        'description' => 'Specialized training in assistive technology tools to enhance independence and communication.',
                        'objectives' => 'Master assistive software, develop tech independence, enhance communication through technology',
                        'materials_needed' => 'Assistive devices, specialized software, communication apps, adaptive hardware',
                        'age_group' => '10-17 years',
                        'difficulty_level' => 'Intermediate',
                        'min_participants' => 1,
                        'max_participants' => 4,
                        'duration_minutes' => 90
                    ]
                ]
            ],
            [
                'category_name' => 'Art & Creativity',
                'activities' => [
                    [
                        'activity_name' => 'Therapeutic Art Expression',
                        'activity_code' => 'ART-001',
                        'description' => 'Creative expression through various art mediums to support emotional development and fine motor skills.',
                        'objectives' => 'Express emotions through art, develop fine motor skills, boost self-confidence, explore creativity',
                        'materials_needed' => 'Paints, brushes, paper, clay, drawing materials, adaptive art tools',
                        'age_group' => 'All Ages',
                        'difficulty_level' => 'Beginner',
                        'min_participants' => 3,
                        'max_participants' => 12,
                        'duration_minutes' => 60
                    ],
                    [
                        'activity_name' => 'Advanced Creative Projects',
                        'activity_code' => 'ART-002',
                        'description' => 'Complex art projects combining multiple techniques and mediums for advanced creative development.',
                        'objectives' => 'Master advanced techniques, complete long-term projects, develop artistic portfolio',
                        'materials_needed' => 'Professional art supplies, mixed media materials, display boards',
                        'age_group' => '13-17 years',
                        'difficulty_level' => 'Advanced',
                        'min_participants' => 2,
                        'max_participants' => 6,
                        'duration_minutes' => 120
                    ]
                ]
            ],
            [
                'category_name' => 'Social Skills',
                'activities' => [
                    [
                        'activity_name' => 'Interactive Social Learning',
                        'activity_code' => 'SOC-001',
                        'description' => 'Group activities designed to develop social interaction skills, empathy, and communication in social settings.',
                        'objectives' => 'Improve social interaction, develop empathy, learn conversation skills, practice group cooperation',
                        'materials_needed' => 'Role-play materials, social scenario cards, group games, communication aids',
                        'age_group' => '6-12 years',
                        'difficulty_level' => 'Intermediate',
                        'min_participants' => 4,
                        'max_participants' => 10,
                        'duration_minutes' => 60
                    ],
                    [
                        'activity_name' => 'Community Integration Skills',
                        'activity_code' => 'SOC-002',
                        'description' => 'Practical training for community participation and independence in social environments.',
                        'objectives' => 'Navigate community settings, practice public interaction, develop independence in social situations',
                        'materials_needed' => 'Community scenario materials, practice environments, social scripts',
                        'age_group' => '13-17 years',
                        'difficulty_level' => 'Advanced',
                        'min_participants' => 3,
                        'max_participants' => 8,
                        'duration_minutes' => 90
                    ]
                ]
            ]
        ];

        foreach ($activitiesData as $categoryData) {
            $category = $categories->where('name', $categoryData['category_name'])->first();
            
            if (!$category) {
                continue;
            }

            foreach ($categoryData['activities'] as $activityData) {
                foreach ($centres->take(3) as $centre) { // Create activities for first 3 centres
                    $teacher = $teachers->where('centre_id', $centre->centre_id)->random();
                    
                    Activity::create([
                        'activity_name' => $activityData['activity_name'],
                        'activity_code' => $activityData['activity_code'] . '-' . strtoupper(substr($centre->centre_name, 0, 3)),
                        'description' => $activityData['description'],
                        'category_id' => $category->id,
                        'category' => $category->name, // Backward compatibility
                        'activity_type' => $activityData['min_participants'] == 1 ? 'Individual' : 'Group',
                        'objectives' => $activityData['objectives'],
                        'materials_needed' => $activityData['materials_needed'],
                        'age_group' => $activityData['age_group'],
                        'difficulty_level' => $activityData['difficulty_level'],
                        'min_participants' => $activityData['min_participants'],
                        'max_participants' => $activityData['max_participants'],
                        'duration_minutes' => $activityData['duration_minutes'],
                        'is_active' => true,
                        'created_by' => $teacher->id,
                        'centre_id' => $centre->centre_id
                    ]);
                }
            }
        }
    }

    /**
     * Create realistic sessions and schedules
     */
    private function createSessionsAndSchedules()
    {
        $activities = Activity::with('category')->get();
        $teachers = Users::whereIn('role', ['teacher', 'supervisor'])->get();
        
        $venues = [
            'Bilik Terapi Pertuturan' => ['A1', 'A2'],
            'Bilik Terapi Okupasi' => ['B1', 'B2', 'B3'],
            'Bilik Fisioterapi' => ['C1', 'C2'],
            'Bilik Aktiviti Kumpulan' => ['D1', 'D2', 'D3'],
            'Makmal Komputer' => ['E1'],
            'Bilik Seni' => ['F1', 'F2'],
            'Bilik Kemahiran Hidup' => ['G1']
        ];

        $timeSlots = [
            ['start' => '08:00', 'end' => '09:00'],
            ['start' => '09:15', 'end' => '10:15'],
            ['start' => '10:30', 'end' => '11:30'],
            ['start' => '14:00', 'end' => '15:00'],
            ['start' => '15:15', 'end' => '16:15'],
            ['start' => '16:30', 'end' => '17:30']
        ];

        $daysOfWeek = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];

        foreach ($activities as $activity) {
            $centreTeachers = $teachers->where('centre_id', $activity->centre_id);
            
            if ($centreTeachers->isEmpty()) {
                continue;
            }

            $teacher = $centreTeachers->random();
            $venue = array_rand($venues);
            $room = $venues[$venue][array_rand($venues[$venue])];
            
            // Create 2-3 recurring schedules per activity
            $scheduleCount = rand(1, 3);
            
            for ($i = 0; $i < $scheduleCount; $i++) {
                $day = $daysOfWeek[array_rand($daysOfWeek)];
                $timeSlot = $timeSlots[array_rand($timeSlots)];
                
                // Create recurring schedule
                ActivitySchedule::create([
                    'activity_id' => $activity->id,
                    'day_of_week' => $day,
                    'start_time' => $timeSlot['start'],
                    'end_time' => $timeSlot['end'],
                    'location' => $venue,
                    'room' => $room,
                    'recurring' => 'weekly',
                    'start_date' => Carbon::now()->startOfWeek(),
                    'end_date' => Carbon::now()->addMonths(3),
                    'max_capacity' => $activity->max_participants,
                    'status' => 'active'
                ]);

                // Create individual sessions for the next 4 weeks
                for ($week = 0; $week < 4; $week++) {
                    $sessionDate = Carbon::now()->startOfWeek()->addWeeks($week);
                    
                    // Find the correct day of the week
                    while ($sessionDate->format('l') !== $day) {
                        $sessionDate->addDay();
                    }

                    // Skip weekends and ensure it's a valid date
                    if ($sessionDate->isWeekend() || $sessionDate->isPast()) {
                        continue;
                    }

                    $status = $sessionDate->isPast() ? 'completed' : 
                             ($sessionDate->isToday() ? 'ongoing' : 'scheduled');

                    ActivitySession::create([
                        'activity_id' => $activity->id,
                        'teacher_id' => $teacher->id,
                        'scheduled_date' => $sessionDate->format('Y-m-d'),
                        'start_time' => $timeSlot['start'],
                        'end_time' => $timeSlot['end'],
                        'duration_minutes' => Carbon::parse($timeSlot['start'])->diffInMinutes(Carbon::parse($timeSlot['end'])),
                        'venue' => $venue,
                        'room_number' => $room,
                        'max_participants' => $activity->max_participants,
                        'status' => $status,
                        'attendance_marked' => $status === 'completed'
                    ]);
                    
                    // Small delay to ensure unique session codes
                    usleep(1000); // 1ms delay
                }
            }
        }
    }

    /**
     * Create enrollments for activities
     */
    private function createEnrollments()
    {
        $activities = Activity::all();
        $sessions = ActivitySession::all();
        
        foreach ($activities as $activity) {
            $trainees = Trainee::where('centre_name', $activity->centre_id)->get();
            
            if ($trainees->isEmpty()) {
                $trainees = Trainee::take(5)->get(); // Fallback to any trainees
            }

            // Enroll 2-5 trainees per activity
            $enrollmentCount = min(rand(2, 5), $trainees->count(), $activity->max_participants);
            $selectedTrainees = $trainees->random($enrollmentCount);
            
            foreach ($selectedTrainees as $trainee) {
                // Create activity enrollment
                ActivityEnrollment::create([
                    'activity_id' => $activity->id,
                    'trainee_id' => $trainee->id,
                    'enrollment_date' => Carbon::now()->subDays(rand(7, 30)),
                    'status' => 'active',
                    'notes' => "Personalized development goals for {$trainee->trainee_first_name} in {$activity->activity_name}"
                ]);

                // Create session enrollments for this trainee
                $activitySessions = $sessions->where('activity_id', $activity->id);
                
                foreach ($activitySessions as $session) {
                    $attendanceStatus = $this->generateRealisticAttendance($session->status);
                    
                    SessionEnrollment::create([
                        'session_id' => $session->id,
                        'trainee_id' => $trainee->id,
                        'enrolled_at' => Carbon::now()->subDays(rand(1, 7)),
                        'enrolled_by' => $activity->created_by,
                        'enrollment_status' => 'enrolled',
                        'attendance_status' => $attendanceStatus,
                        'checked_in_at' => $attendanceStatus === 'present' ? 
                            Carbon::parse($session->scheduled_date . ' ' . $session->start_time)->addMinutes(rand(0, 10)) : null,
                        'participation_score' => $attendanceStatus === 'present' ? rand(6, 10) : null,
                        'progress_notes' => $attendanceStatus === 'present' ? 
                            $this->generateProgressNote($trainee->trainee_first_name, $activity->activity_name) : null
                    ]);
                }
            }
        }
    }

    /**
     * Generate realistic attendance patterns
     */
    private function generateRealisticAttendance($sessionStatus)
    {
        if ($sessionStatus !== 'completed') {
            return 'pending'; // Future sessions are pending
        }

        $random = rand(1, 100);
        
        if ($random <= 85) return 'present';      // 85% attendance rate
        if ($random <= 92) return 'late';        // 7% late
        if ($random <= 97) return 'excused';     // 5% excused absence
        return 'absent';                         // 3% unexcused absence
    }

    /**
     * Generate realistic progress notes
     */
    private function generateProgressNote($traineeName, $activityName)
    {
        $notes = [
            "{$traineeName} showed excellent engagement during {$activityName}. Made significant progress in targeted skills.",
            "{$traineeName} participated actively and demonstrated improved focus throughout the session.",
            "Good progress noted. {$traineeName} successfully completed 80% of planned activities with minimal assistance.",
            "{$traineeName} showed enthusiasm and cooperation. Recommend continuing current intervention strategies.",
            "Steady improvement observed. {$traineeName} is meeting session objectives consistently.",
            "{$traineeName} required additional support today but remained motivated throughout the session.",
            "Excellent session. {$traineeName} exceeded expectations and helped peers during group activities."
        ];

        return $notes[array_rand($notes)];
    }
}