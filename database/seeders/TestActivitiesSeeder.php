<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Activity;
use App\Models\Users;
use App\Models\Trainee;
use App\Models\ActivityEnrollment;
use Carbon\Carbon;

class TestActivitiesSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🧪 Creating test activities for time conflict testing...');

        // Get a teacher named Ahmad (or first teacher)
        $teacher = Users::where('role', 'teacher')
                        ->where('name', 'LIKE', '%Ahmad%')
                        ->first();
        
        if (!$teacher) {
            $teacher = Users::where('role', 'teacher')->first();
        }

        // Get a trainee named Ali (or first trainee)
        $trainee = Trainee::where('trainee_first_name', 'LIKE', '%Ali%')->first();
        
        if (!$trainee) {
            $trainee = Trainee::first();
        }

        if (!$teacher || !$trainee) {
            $this->command->error('Teacher or trainee not found!');
            return;
        }

        $this->command->info("Selected Teacher: {$teacher->name} (ID: {$teacher->id})");
        $this->command->info("Selected Trainee: {$trainee->trainee_first_name} {$trainee->trainee_last_name} (ID: {$trainee->id})");

        // Create Activity A: Monday, 10:00 AM – 11:00 AM
        $activityA = Activity::create([
            'activity_id' => 'TEST-A-001',
            'activity_name' => 'Activity A - Mathematics Class',
            'activity_description' => 'Basic mathematics lesson for special needs students',
            'activity_type' => 'Education',
            'activity_date' => '2025-07-14', // Today
            'activity_start_time' => '10:00:00',
            'activity_end_time' => '11:00:00',
            'activity_location' => 'Classroom A',
            'max_participants' => 15,
            'current_participants' => 0,
            'activity_goals' => 'Develop basic numeracy skills',
            'activity_outcomes' => 'Improved mathematical understanding',
            'activity_status' => 'scheduled',
            'centre_id' => $teacher->centre_id,
            'created_by' => $teacher->id,
            'instructor_id' => $teacher->id
        ]);

        $this->command->line("   ✅ Created Activity A: {$activityA->activity_name} (10:00 - 11:00)");

        // Create Activity B: Monday, 10:30 AM – 11:30 AM (OVERLAPPING)
        $activityB = Activity::create([
            'activity_id' => 'TEST-B-002',
            'activity_name' => 'Activity B - Speech Therapy',
            'activity_description' => 'Individual speech therapy session',
            'activity_type' => 'Therapy',
            'activity_date' => '2025-07-14', // Same day
            'activity_start_time' => '10:30:00',
            'activity_end_time' => '11:30:00',
            'activity_location' => 'Speech Therapy Room',
            'max_participants' => 8,
            'current_participants' => 0,
            'activity_goals' => 'Improve speech clarity',
            'activity_outcomes' => 'Enhanced communication skills',
            'activity_status' => 'scheduled',
            'centre_id' => $teacher->centre_id,
            'created_by' => $teacher->id,
            'instructor_id' => $teacher->id
        ]);

        $this->command->line("   ✅ Created Activity B: {$activityB->activity_name} (10:30 - 11:30)");

        // Create Activity C: Monday, 11:30 AM – 12:30 PM (NO CONFLICT)
        $activityC = Activity::create([
            'activity_id' => 'TEST-C-003',
            'activity_name' => 'Activity C - Art Therapy',
            'activity_description' => 'Creative art therapy session',
            'activity_type' => 'Therapy',
            'activity_date' => '2025-07-14', // Same day
            'activity_start_time' => '11:30:00',
            'activity_end_time' => '12:30:00',
            'activity_location' => 'Art Room',
            'max_participants' => 12,
            'current_participants' => 0,
            'activity_goals' => 'Encourage creativity',
            'activity_outcomes' => 'Better emotional expression',
            'activity_status' => 'scheduled',
            'centre_id' => $teacher->centre_id,
            'created_by' => $teacher->id,
            'instructor_id' => $teacher->id
        ]);

        $this->command->line("   ✅ Created Activity C: {$activityC->activity_name} (11:30 - 12:30)");

        // Create Activity D for batch assignment testing
        $activityD = Activity::create([
            'activity_id' => 'TEST-D-004',
            'activity_name' => 'Activity D - Life Skills Training',
            'activity_description' => 'Practical life skills training session',
            'activity_type' => 'Training',
            'activity_date' => '2025-07-22', // Tuesday
            'activity_start_time' => '09:00:00',
            'activity_end_time' => '10:00:00',
            'activity_location' => 'Life Skills Kitchen',
            'max_participants' => 10,
            'current_participants' => 0,
            'activity_goals' => 'Develop independence',
            'activity_outcomes' => 'Better self-sufficiency',
            'activity_status' => 'scheduled',
            'centre_id' => $teacher->centre_id,
            'created_by' => $teacher->id,
            'instructor_id' => $teacher->id
        ]);

        $this->command->line("   ✅ Created Activity D: {$activityD->activity_name} (09:00 - 10:00)");

        // Store the IDs for testing
        $this->command->info("\n📋 Test Data Summary:");
        $this->command->info("Teacher ID: {$teacher->id} ({$teacher->name})");
        $this->command->info("Trainee ID: {$trainee->id} ({$trainee->trainee_first_name} {$trainee->trainee_last_name})");
        $this->command->info("Activity A ID: {$activityA->id} (10:00-11:00)");
        $this->command->info("Activity B ID: {$activityB->id} (10:30-11:30) - CONFLICTS with A");
        $this->command->info("Activity C ID: {$activityC->id} (11:30-12:30) - NO CONFLICT");
        $this->command->info("Activity D ID: {$activityD->id} (09:00-10:00) - For batch testing");

        $this->command->info("\n🎯 Test activities created successfully!");
    }
}