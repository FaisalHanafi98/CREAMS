<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class ActivityEnrollmentSeeder extends Seeder
{
    /**
     * Seed MASSIVE activity enrollments with disability matching and workload requirements
     * Ensures 2-10 activities per week per trainee/staff (average 5 activities = 10 sessions)
     * Matches activities to trainee disability conditions appropriately
     */
    public function run(): void
    {
        $this->command->info('📝 Seeding MASSIVE activity enrollments with disability matching and workload management...');

        $faker = Faker::create();
        
        // Get trainees from 4 centres (exclude Gombak)
        $trainees = DB::table('trainees')
            ->whereNotIn('centre_id', ['01'])
            ->get();
            
        // Get activities with categories from 4 centres
        $activities = DB::table('activities')
            ->join('activity_categories', 'activities.category_id', '=', 'activity_categories.id')
            ->whereNotIn('activities.centre_id', ['01'])
            ->select('activities.*', 'activity_categories.category_name')
            ->get();
        
        if ($trainees->isEmpty() || $activities->isEmpty()) {
            $this->command->error('No trainees or activities found! Run TraineeSeeder and ActivitySeeder first.');
            return;
        }
        
        $this->command->info("   Processing {$trainees->count()} trainees with {$activities->count()} activities...");
        
        $totalEnrollments = 0;
        $workloadStats = [];
        
        foreach ($trainees as $trainee) {
            // Get activities from the same centre
            $centreActivities = $activities->where('centre_id', $trainee->centre_id);
            
            if ($centreActivities->isEmpty()) {
                continue;
            }
            
            // Match trainee condition with appropriate activities
            $suitableActivities = $this->getSuitableActivities($trainee, $centreActivities);
            
            if ($suitableActivities->count() == 0) continue;
            
            // Calculate weekly workload: minimum 2, average 5, maximum 10 activities per week
            $weeklyActivityCount = $this->calculateWeeklyWorkload();
            
            // Select activities across different time periods to ensure consistent weekly load
            $selectedActivities = $this->selectActivitiesAcrossTimeline(
                $suitableActivities, 
                $weeklyActivityCount
            );
            
            $traineeEnrollments = 0;
            
            foreach ($selectedActivities as $activity) {
                $enrollmentDate = $this->getAppropriateEnrollmentDate($activity);
                $progress = $this->calculateProgress($enrollmentDate, $faker);
                
                $enrollmentData = [
                    'activity_id' => $activity->id,
                    'trainee_id' => $trainee->id,
                    'enrollment_date' => $enrollmentDate->format('Y-m-d'),
                    'enrollment_status' => $this->getEnrollmentStatus($progress),
                    'enrollment_notes' => $this->generateEnrollmentNotes($trainee, $activity, $faker),
                    'progress_percentage' => $progress,
                    'attendance_count' => $faker->numberBetween(5, 50),
                    'completion_date' => $progress >= 100 ? $faker->dateTimeBetween($enrollmentDate, 'now')->format('Y-m-d') : null,
                    'completion_notes' => $progress >= 100 ? 'Successfully completed all program objectives.' : null,
                    'enrolled_by' => $this->getRandomStaffMember($trainee->centre_id),
                    'created_at' => $enrollmentDate,
                    'updated_at' => now()
                ];
                
                DB::table('activity_enrollments')->insert($enrollmentData);
                $totalEnrollments++;
                $traineeEnrollments++;
            }
            
            // Track workload statistics
            $workloadStats[] = [
                'trainee_id' => $trainee->id,
                'condition' => $trainee->trainee_condition,
                'enrollments' => $traineeEnrollments
            ];
        }

        $this->command->info("📝 Successfully seeded {$totalEnrollments} MASSIVE activity enrollments");
        
        // Show enrollment and workload statistics
        $stats = DB::table('activity_enrollments')
            ->select('enrollment_status', DB::raw('count(*) as count'))
            ->groupBy('enrollment_status')
            ->get();
            
        foreach ($stats as $stat) {
            $this->command->line("   📊 {$stat->enrollment_status}: {$stat->count} enrollments");
        }
        
        // Show workload distribution
        $enrollmentCounts = array_count_values(array_column($workloadStats, 'enrollments'));
        $this->command->line("\n   Trainee workload distribution:");
        foreach ($enrollmentCounts as $count => $trainees) {
            $this->command->line("     {$count} activities: {$trainees} trainees");
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

    private function calculateWeeklyWorkload(): int
    {
        // Minimum 2, average 5, maximum 10 activities per week
        $weights = [
            2 => 10,  // 2 activities: 10% (minimum)
            3 => 15,  // 3 activities: 15%
            4 => 20,  // 4 activities: 20%
            5 => 25,  // 5 activities: 25% (most common)
            6 => 15,  // 6 activities: 15%
            7 => 10,  // 7 activities: 10%
            8 => 3,   // 8 activities: 3%
            9 => 1,   // 9 activities: 1%
            10 => 1   // 10 activities: 1% (maximum)
        ];
        
        $rand = rand(1, 100);
        $cumulative = 0;
        
        foreach ($weights as $count => $weight) {
            $cumulative += $weight;
            if ($rand <= $cumulative) {
                return $count;
            }
        }
        
        return 5; // Default fallback
    }

    private function selectActivitiesAcrossTimeline($suitableActivities, int $targetCount): array
    {
        $selected = [];
        $activities = $suitableActivities->toArray();
        
        // Shuffle to get random selection
        shuffle($activities);
        
        // Prioritize current ongoing activities (August) and future activities (September)
        $ongoingActivities = array_filter($activities, function($activity) {
            return $activity->is_active;
        });
        
        $completedActivities = array_filter($activities, function($activity) {
            return !$activity->is_active;
        });
        
        // Select 60% from ongoing/planned, 40% from completed
        $ongoingCount = (int) ($targetCount * 0.6);
        $completedCount = $targetCount - $ongoingCount;
        
        // Add ongoing activities
        $selectedOngoing = array_slice($ongoingActivities, 0, min($ongoingCount, count($ongoingActivities)));
        $selected = array_merge($selected, $selectedOngoing);
        
        // Add completed activities to reach target
        $remaining = $targetCount - count($selected);
        if ($remaining > 0) {
            $selectedCompleted = array_slice($completedActivities, 0, min($remaining, count($completedActivities)));
            $selected = array_merge($selected, $selectedCompleted);
        }
        
        return $selected;
    }

    private function getAppropriateEnrollmentDate($activity): \DateTime
    {
        $activityCreated = \Carbon\Carbon::parse($activity->created_at);
        
        // Enroll 1-2 weeks before or at activity start
        $enrollmentDate = $activityCreated->copy()->subDays(rand(0, 14));
        
        return $enrollmentDate;
    }
}