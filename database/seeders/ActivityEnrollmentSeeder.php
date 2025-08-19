<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class ActivityEnrollmentSeeder extends Seeder
{
    /**
     * Seed activity enrollments matching trainees with appropriate activities
     */
    public function run(): void
    {
        $this->command->info('📝 Seeding activity enrollments...');

        $faker = Faker::create();
        
        $trainees = DB::table('trainees')->get();
        $activities = DB::table('activities')
            ->join('activity_categories', 'activities.category_id', '=', 'activity_categories.id')
            ->select('activities.*', 'activity_categories.category_name')
            ->get();
        
        if ($trainees->isEmpty() || $activities->isEmpty()) {
            $this->command->error('No trainees or activities found! Run TraineeSeeder and ActivitySeeder first.');
            return;
        }

        $totalEnrollments = 0;
        
        foreach ($trainees as $trainee) {
            // Get activities from the same centre
            $centreActivities = $activities->where('centre_id', $trainee->centre_id);
            
            if ($centreActivities->isEmpty()) {
                continue;
            }
            
            // Match trainee condition with appropriate activities
            $suitableActivities = $this->getSuitableActivities($trainee, $centreActivities);
            
            // Enroll trainee in 2-4 suitable activities
            $enrollmentCount = $faker->numberBetween(2, min(4, $suitableActivities->count()));
            $selectedActivities = $suitableActivities->random($enrollmentCount);
            
            foreach ($selectedActivities as $activity) {
                $enrollmentDate = $faker->dateTimeBetween('-3 months', '-1 month');
                $progress = $this->calculateProgress($enrollmentDate, $faker);
                
                $enrollmentData = [
                    'activity_id' => $activity->id,
                    'trainee_id' => $trainee->id,
                    'enrollment_date' => $enrollmentDate->format('Y-m-d'),
                    'enrollment_status' => $this->getEnrollmentStatus($progress),
                    'enrollment_notes' => $this->generateEnrollmentNotes($trainee, $activity, $faker),
                    'progress_percentage' => $progress,
                    'attendance_count' => $faker->numberBetween(5, 30),
                    'completion_date' => $progress >= 100 ? $faker->dateTimeBetween($enrollmentDate, 'now')->format('Y-m-d') : null,
                    'completion_notes' => $progress >= 100 ? 'Successfully completed all program objectives.' : null,
                    'enrolled_by' => $this->getRandomStaffMember($trainee->centre_id),
                    'created_at' => $enrollmentDate,
                    'updated_at' => now()
                ];
                
                DB::table('activity_enrollments')->insert($enrollmentData);
                $totalEnrollments++;
            }
        }

        $this->command->info("📝 Successfully seeded {$totalEnrollments} activity enrollments");
        
        // Show enrollment statistics
        $stats = DB::table('activity_enrollments')
            ->select('enrollment_status', DB::raw('count(*) as count'))
            ->groupBy('enrollment_status')
            ->get();
            
        foreach ($stats as $stat) {
            $this->command->line("   📊 {$stat->enrollment_status}: {$stat->count} enrollments");
        }
    }
    
    private function getSuitableActivities($trainee, $activities)
    {
        // Match activities based on trainee's condition
        $conditionMatches = [
            'Autism Spectrum Disorder' => ['Speech & Language Therapy', 'Behavioral Intervention', 'Social Skills Training', 'Sensory Integration'],
            'Down Syndrome' => ['Speech & Language Therapy', 'Occupational Therapy', 'Academic Skills', 'Life Skills Training'],
            'Cerebral Palsy' => ['Physical Therapy', 'Occupational Therapy', 'Technology Skills', 'Creative Arts'],
            'Intellectual Disability' => ['Academic Skills', 'Life Skills Training', 'Social Skills Training', 'Creative Arts'],
            'ADHD' => ['Behavioral Intervention', 'Physical Therapy', 'Academic Skills', 'Social Skills Training'],
            'Learning Disabilities' => ['Academic Skills', 'Technology Skills', 'Creative Arts', 'Speech & Language Therapy'],
            'Speech and Language Disorders' => ['Speech & Language Therapy', 'Social Skills Training', 'Academic Skills', 'Technology Skills'],
            'Hearing Impairment' => ['Technology Skills', 'Creative Arts', 'Academic Skills', 'Social Skills Training'],
            'Visual Impairment' => ['Technology Skills', 'Sensory Integration', 'Academic Skills', 'Life Skills Training'],
            'Physical Disability' => ['Physical Therapy', 'Occupational Therapy', 'Technology Skills', 'Creative Arts'],
            'Multiple Disabilities' => ['Speech & Language Therapy', 'Occupational Therapy', 'Behavioral Intervention', 'Life Skills Training']
        ];
        
        $suitableCategories = $conditionMatches[$trainee->trainee_condition] ?? ['Academic Skills', 'Creative Arts', 'Social Skills Training'];
        
        return $activities->filter(function ($activity) use ($suitableCategories) {
            return in_array($activity->category_name, $suitableCategories);
        });
    }
    
    private function calculateProgress($enrollmentDate, $faker)
    {
        $daysSinceEnrollment = now()->diffInDays($enrollmentDate);
        
        // More realistic progress based on time enrolled
        if ($daysSinceEnrollment < 30) {
            return $faker->numberBetween(5, 25);
        } elseif ($daysSinceEnrollment < 60) {
            return $faker->numberBetween(20, 50);
        } elseif ($daysSinceEnrollment < 90) {
            return $faker->numberBetween(40, 75);
        } else {
            return $faker->numberBetween(60, 100);
        }
    }
    
    private function getEnrollmentStatus($progress)
    {
        if ($progress >= 100) {
            return 'completed';
        } elseif ($progress >= 80) {
            return 'enrolled';
        } elseif ($progress >= 20) {
            return 'enrolled';
        } else {
            return 'pending';
        }
    }
    
    private function generateEnrollmentNotes($trainee, $activity, $faker)
    {
        $notes = [
            "Enrolled in {$activity->activity_name} to address specific needs related to {$trainee->trainee_condition}.",
            "Recommended by assessment team. Good candidate for this program.",
            "Parent requested enrollment. Trainee shows interest in this activity type.",
            "Following IEP recommendations. Expected to benefit significantly from this program.",
            "Continuation from previous program. Building on existing skills.",
            "Trial enrollment to assess suitability and engagement levels.",
            "Group placement based on age and developmental level."
        ];
        
        return $faker->randomElement($notes);
    }
    
    private function getRandomStaffMember($centreId)
    {
        $staff = DB::table('users')
            ->where('centre_id', $centreId)
            ->whereIn('role', ['supervisor', 'admin'])
            ->inRandomOrder()
            ->first();
            
        return $staff ? $staff->id : null;
    }
}