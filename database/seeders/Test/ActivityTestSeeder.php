<?php

namespace Database\Seeders\Test;

use Illuminate\Database\Seeder;
use App\Models\Activity;
use App\Models\ActivitySession;
use App\Models\ActivityEnrollment;
use App\Models\User;
use App\Models\Trainee;
use App\Models\Centre;

class ActivityTestSeeder extends Seeder
{
    /**
     * Seed test activities with sessions and enrollments.
     *
     * Creates:
     * - 30 activities distributed across centres and categories
     * - 3-5 sessions per activity
     * - Random trainee enrollments (3-10 per activity)
     *
     * Categories include:
     * - Autism Support
     * - Occupational Therapy
     * - Speech Therapy
     * - Life Skills Development
     * - Recreational Activities
     *
     * @return void
     */
    public function run(): void
    {
        $this->command->info('Seeding test activities...');

        $centres = Centre::all();
        $activitiesPerCentre = 8; // 8 x 4 centres = 32 activities (close to 30)

        foreach ($centres as $centre) {
            $this->command->info("Creating activities for {$centre->centre_name}...");

            // Get teachers for this centre
            $teachers = User::where('centre_id', $centre->centre_id)
                           ->where('role', 'teacher')
                           ->get();

            if ($teachers->isEmpty()) {
                $this->command->warn("No teachers found for {$centre->centre_name}, skipping...");
                continue;
            }

            // Get trainees for this centre
            $trainees = Trainee::where('centre_id', $centre->centre_id)->get();

            if ($trainees->isEmpty()) {
                $this->command->warn("No trainees found for {$centre->centre_name}, skipping...");
                continue;
            }

            // Create varied activities
            $categories = [
                'Autism Support',
                'Physical Disabilities Support',
                'Occupational Therapy',
                'Physiotherapy',
                'Speech Therapy',
                'Behavioral Therapy',
                'Social Skills Training',
                'Life Skills Development',
            ];

            for ($i = 0; $i < $activitiesPerCentre; $i++) {
                $teacher = $teachers->random();
                $category = $categories[$i % count($categories)];

                // Create activity with existing teacher
                $activity = Activity::create([
                    'activity_name' => $category . ' Program ' . ($i + 1),
                    'activity_description' => "Test activity for {$category}",
                    'category' => $category,
                    'centre_id' => $centre->centre_id,
                    'duration_weeks' => rand(4, 12),
                    'sessions_per_week' => rand(1, 3),
                    'session_duration_minutes' => [30, 45, 60, 90][array_rand([30, 45, 60, 90])],
                    'max_participants' => rand(10, 20),
                    'instructor_id' => $teacher->id,
                    'is_active' => true,
                ]);

                // Create 3-5 sessions for this activity
                $sessionsCount = rand(3, 5);
                for ($j = 0; $j < $sessionsCount; $j++) {
                    ActivitySession::factory()->create([
                        'activity_id' => $activity->id,
                        'instructor_id' => $teacher->id,
                        'session_name' => $activity->activity_name . ' - Session ' . ($j + 1),
                    ]);
                }

                // Enroll random trainees (3-10 per activity)
                $enrollmentCount = rand(3, min(10, $trainees->count()));
                $enrolledTrainees = $trainees->random($enrollmentCount);

                foreach ($enrolledTrainees as $trainee) {
                    ActivityEnrollment::factory()->create([
                        'activity_id' => $activity->id,
                        'trainee_id' => $trainee->id,
                        'enrolled_by' => $teacher->id,
                    ]);
                }
            }

            $this->command->info("✓ Created {$activitiesPerCentre} activities for {$centre->centre_name}");
        }

        $totalActivities = Activity::count();
        $totalSessions = ActivitySession::count();
        $totalEnrollments = ActivityEnrollment::count();

        $this->command->info("✓ Created {$totalActivities} activities with {$totalSessions} sessions and {$totalEnrollments} enrollments");
    }
}
