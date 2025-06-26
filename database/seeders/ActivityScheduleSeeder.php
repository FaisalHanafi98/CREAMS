<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Activity;
use App\Models\ActivitySchedule;
use App\Models\ActivityEnrollment;
use App\Models\ActivitySession;
use App\Models\Users;
use App\Models\Trainees;
use App\Models\Centres;
use Carbon\Carbon;

class ActivityScheduleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get existing users who are teachers
        $teachers = Users::where('role', 'teacher')->get();
        $centres = Centres::all();
        $trainees = Trainees::all();

        if ($teachers->isEmpty() || $centres->isEmpty()) {
            $this->command->info('No teachers or centres found. Please seed users and centres first.');
            return;
        }

        // Malaysian therapy activities data
        $activitiesData = [
            [
                'activity_name' => 'Speech Therapy',
                'activity_name_bm' => 'Terapi Pertuturan',
                'description' => 'Individual and group sessions to improve communication skills, speech clarity, and language development.',
                'category' => 'Speech Therapy',
                'objectives' => 'Improve articulation, enhance vocabulary, develop communication confidence',
                'materials_needed' => ['Picture cards', 'Speech mirrors', 'Audio recording device', 'Communication board'],
                'skills_developed' => ['Communication', 'Language', 'Social interaction', 'Confidence'],
                'age_group' => '3-18',
                'difficulty_level' => 'Beginner',
                'min_participants' => 1,
                'max_participants' => 4,
                'duration_minutes' => 45,
                'location_type' => 'Therapy Room',
                'requires_equipment' => true,
                'equipment_list' => ['Speech therapy tools', 'Audio equipment', 'Visual aids']
            ],
            [
                'activity_name' => 'Occupational Therapy',
                'activity_name_bm' => 'Terapi Pekerjaan',
                'description' => 'Activities to develop fine motor skills, daily living skills, and sensory processing abilities.',
                'category' => 'Occupational Therapy',
                'objectives' => 'Improve fine motor skills, develop independence in daily activities',
                'materials_needed' => ['Therapy putty', 'Sensory tools', 'Daily living aids', 'Fine motor activities'],
                'skills_developed' => ['Fine motor', 'Daily living', 'Sensory processing', 'Independence'],
                'age_group' => '3-18',
                'difficulty_level' => 'Intermediate',
                'min_participants' => 1,
                'max_participants' => 6,
                'duration_minutes' => 60,
                'location_type' => 'OT Room',
                'requires_equipment' => true,
                'equipment_list' => ['OT tools', 'Sensory equipment', 'Daily living props']
            ],
            [
                'activity_name' => 'Physiotherapy',
                'activity_name_bm' => 'Fisioterapi',
                'description' => 'Physical exercises and treatments to improve mobility, strength, and motor function.',
                'category' => 'Physical Therapy',
                'objectives' => 'Enhance gross motor skills, improve physical strength and coordination',
                'materials_needed' => ['Exercise mats', 'Therapy balls', 'Resistance bands', 'Walking aids'],
                'skills_developed' => ['Gross motor', 'Balance', 'Strength', 'Coordination'],
                'age_group' => '3-18',
                'difficulty_level' => 'Intermediate',
                'min_participants' => 1,
                'max_participants' => 5,
                'duration_minutes' => 45,
                'location_type' => 'Gym/Therapy Hall',
                'requires_equipment' => true,
                'equipment_list' => ['Exercise equipment', 'Therapy tools', 'Safety mats']
            ],
            [
                'activity_name' => 'Behavioral Intervention',
                'activity_name_bm' => 'Intervensi Tingkah Laku',
                'description' => 'Structured programs to address behavioral challenges and develop appropriate social behaviors.',
                'category' => 'Behavioral Therapy',
                'objectives' => 'Reduce challenging behaviors, develop positive social skills',
                'materials_needed' => ['Behavior charts', 'Visual schedules', 'Reward systems', 'Social stories'],
                'skills_developed' => ['Social skills', 'Self-regulation', 'Communication', 'Compliance'],
                'age_group' => '3-18',
                'difficulty_level' => 'Advanced',
                'min_participants' => 1,
                'max_participants' => 8,
                'duration_minutes' => 60,
                'location_type' => 'Classroom',
                'requires_equipment' => false,
                'equipment_list' => ['Visual aids', 'Behavior tracking tools']
            ],
            [
                'activity_name' => 'Social Skills Training',
                'activity_name_bm' => 'Latihan Kemahiran Sosial',
                'description' => 'Group activities focused on developing social interaction skills, friendship building, and community integration.',
                'category' => 'Life Skills',
                'objectives' => 'Improve social interaction, develop friendships, enhance community participation',
                'materials_needed' => ['Social games', 'Role-play props', 'Group activities', 'Community outing supplies'],
                'skills_developed' => ['Social interaction', 'Friendship', 'Communication', 'Community skills'],
                'age_group' => '7-18',
                'difficulty_level' => 'Intermediate',
                'min_participants' => 4,
                'max_participants' => 12,
                'duration_minutes' => 90,
                'location_type' => 'Community Room',
                'requires_equipment' => false,
                'equipment_list' => ['Games', 'Activity materials']
            ],
            [
                'activity_name' => 'Academic Support',
                'activity_name_bm' => 'Sokongan Akademik',
                'description' => 'Educational support sessions to help with learning difficulties and academic skills development.',
                'category' => 'Literacy',
                'objectives' => 'Improve literacy and numeracy skills, develop learning strategies',
                'materials_needed' => ['Learning materials', 'Educational games', 'Worksheets', 'Digital tools'],
                'skills_developed' => ['Reading', 'Writing', 'Mathematics', 'Problem solving'],
                'age_group' => '6-18',
                'difficulty_level' => 'Beginner',
                'min_participants' => 2,
                'max_participants' => 8,
                'duration_minutes' => 60,
                'location_type' => 'Learning Center',
                'requires_equipment' => true,
                'equipment_list' => ['Educational tools', 'Computers', 'Learning aids']
            ],
            [
                'activity_name' => 'Art Therapy',
                'activity_name_bm' => 'Terapi Seni',
                'description' => 'Creative expression through various art mediums to promote emotional well-being and self-expression.',
                'category' => 'Art & Creativity',
                'objectives' => 'Enhance creativity, improve emotional expression, develop fine motor skills',
                'materials_needed' => ['Art supplies', 'Paints', 'Brushes', 'Canvas', 'Clay'],
                'skills_developed' => ['Creativity', 'Emotional expression', 'Fine motor', 'Focus'],
                'age_group' => '3-18',
                'difficulty_level' => 'Beginner',
                'min_participants' => 2,
                'max_participants' => 10,
                'duration_minutes' => 75,
                'location_type' => 'Art Studio',
                'requires_equipment' => true,
                'equipment_list' => ['Art materials', 'Tables', 'Storage']
            ],
            [
                'activity_name' => 'Music Therapy',
                'activity_name_bm' => 'Terapi Muzik',
                'description' => 'Musical activities and interventions to support cognitive, emotional, and social development.',
                'category' => 'Music Therapy',
                'objectives' => 'Improve cognitive skills, enhance emotional regulation, develop social interaction',
                'materials_needed' => ['Musical instruments', 'Audio system', 'Sheet music', 'Recording equipment'],
                'skills_developed' => ['Musical skills', 'Rhythm', 'Social interaction', 'Memory'],
                'age_group' => '3-18',
                'difficulty_level' => 'Beginner',
                'min_participants' => 1,
                'max_participants' => 12,
                'duration_minutes' => 45,
                'location_type' => 'Music Room',
                'requires_equipment' => true,
                'equipment_list' => ['Instruments', 'Sound system', 'Recording tools']
            ]
        ];

        // Create activities and schedules
        foreach ($activitiesData as $index => $activityData) {
            $teacher = $teachers->random();
            $centre = $centres->random();

            // Create activity
            $activity = Activity::create([
                'activity_code' => 'ACT' . str_pad($index + 1, 3, '0', STR_PAD_LEFT),
                'activity_name' => $activityData['activity_name'],
                'description' => $activityData['description'],
                'category' => $activityData['category'],
                'activity_type' => 'Both',
                'objectives' => $activityData['objectives'],
                'materials_needed' => json_encode($activityData['materials_needed']),
                'skills_developed' => $activityData['skills_developed'],
                'age_group' => $activityData['age_group'],
                'difficulty_level' => $activityData['difficulty_level'],
                'min_participants' => $activityData['min_participants'],
                'max_participants' => $activityData['max_participants'],
                'duration_minutes' => $activityData['duration_minutes'],
                'location_type' => $activityData['location_type'],
                'requires_equipment' => $activityData['requires_equipment'],
                'equipment_list' => $activityData['equipment_list'],
                'is_active' => true,
                'times_conducted' => rand(5, 50),
                'average_rating' => rand(40, 50) / 10, // 4.0 to 5.0
                'created_by' => $teacher->id,
                'centre_id' => $centre->centre_id
            ]);

            // Create weekly schedules for each activity
            $daysOfWeek = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
            $selectedDays = collect($daysOfWeek)->random(rand(1, 3)); // 1-3 days per week

            foreach ($selectedDays as $day) {
                $startHour = rand(9, 15); // 9 AM to 3 PM
                $startTime = sprintf('%02d:00', $startHour);
                $endTime = sprintf('%02d:%02d', $startHour, $activityData['duration_minutes']);

                ActivitySchedule::create([
                    'activity_id' => $activity->id,
                    'day_of_week' => $day,
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                    'location' => $activityData['location_type'],
                    'room' => 'Room ' . chr(65 + rand(0, 5)), // Room A to F
                    'recurring' => 'weekly',
                    'start_date' => Carbon::now()->subMonths(3),
                    'end_date' => Carbon::now()->addMonths(6),
                    'status' => 'active',
                    'max_capacity' => $activityData['max_participants']
                ]);
            }

            // Create activity sessions for schedules
            foreach ($selectedDays as $day) {
                $startHour = rand(9, 15);
                $sessionData = [
                    'activity_id' => $activity->id,
                    'teacher_id' => $teacher->id,
                    'day_of_week' => $day,
                    'start_time' => sprintf('%02d:00', $startHour),
                    'end_time' => sprintf('%02d:%02d', $startHour, $activityData['duration_minutes']),
                    'location' => $activityData['location_type'] . ' - Room ' . chr(65 + rand(0, 5)),
                    'max_capacity' => $activityData['max_participants'],
                    'current_enrollment' => 0,
                    'status' => 'scheduled',
                    'class_name' => $activityData['activity_name'] . ' - ' . $day,
                    'scheduled_date' => Carbon::now()->next($day),
                ];

                ActivitySession::create($sessionData);
            }

            // Enroll some trainees if we have any
            if ($trainees->isNotEmpty()) {
                $numEnrollments = rand(1, min($activityData['max_participants'], $trainees->count()));
                $enrolledTrainees = $trainees->random($numEnrollments);

                foreach ($enrolledTrainees as $trainee) {
                    ActivityEnrollment::create([
                        'activity_id' => $activity->id,
                        'trainee_id' => $trainee->id,
                        'enrollment_date' => Carbon::now()->subDays(rand(7, 90)),
                        'start_date' => Carbon::now()->subDays(rand(7, 90)),
                        'status' => collect(['enrolled', 'active'])->random(),
                        'attendance_rate' => rand(70, 100),
                        'sessions_attended' => rand(5, 20),
                        'total_sessions' => rand(10, 25),
                        'goals' => 'Individual therapy goals for ' . $trainee->full_name,
                        'enrolled_by' => $teacher->id
                    ]);
                }
            }

            $this->command->info("Created activity: {$activityData['activity_name']} with schedules and enrollments");
        }

        $this->command->info('Activity and schedule seeding completed successfully!');
    }
}