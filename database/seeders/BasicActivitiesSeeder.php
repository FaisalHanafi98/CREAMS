<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Activity;
use App\Models\Users;

class BasicActivitiesSeeder extends Seeder
{
    private array $activityData = [
        [
            'activity_name' => 'Speech Therapy Session',
            'activity_description' => 'Individual speech therapy session focusing on articulation and language development.',
            'activity_type' => 'Therapy',
            'activity_location' => 'Speech Therapy Room',
            'activity_goals' => 'Improve speech clarity and communication skills',
            'activity_outcomes' => 'Enhanced verbal communication abilities'
        ],
        [
            'activity_name' => 'Occupational Therapy',
            'activity_description' => 'Occupational therapy session to develop fine motor skills and daily living activities.',
            'activity_type' => 'Therapy',
            'activity_location' => 'OT Room',
            'activity_goals' => 'Improve fine motor skills and independence',
            'activity_outcomes' => 'Better daily living skills'
        ],
        [
            'activity_name' => 'Physical Therapy',
            'activity_description' => 'Physical therapy session to improve gross motor skills and mobility.',
            'activity_type' => 'Therapy',
            'activity_location' => 'Gymnasium',
            'activity_goals' => 'Enhance physical strength and mobility',
            'activity_outcomes' => 'Improved physical capabilities'
        ],
        [
            'activity_name' => 'Art Therapy',
            'activity_description' => 'Creative art therapy session for self-expression and emotional development.',
            'activity_type' => 'Therapy',
            'activity_location' => 'Art Room',
            'activity_goals' => 'Encourage creativity and emotional expression',
            'activity_outcomes' => 'Better emotional regulation'
        ],
        [
            'activity_name' => 'Music Therapy',
            'activity_description' => 'Music therapy session using rhythm and melody for therapeutic benefits.',
            'activity_type' => 'Therapy',
            'activity_location' => 'Music Room',
            'activity_goals' => 'Improve coordination and social interaction',
            'activity_outcomes' => 'Enhanced social and motor skills'
        ],
        [
            'activity_name' => 'Basic Mathematics',
            'activity_description' => 'Basic mathematics lesson adapted for special needs students.',
            'activity_type' => 'Education',
            'activity_location' => 'Classroom A',
            'activity_goals' => 'Develop basic numeracy skills',
            'activity_outcomes' => 'Improved mathematical understanding'
        ],
        [
            'activity_name' => 'Life Skills Training',
            'activity_description' => 'Practical life skills training for daily independence.',
            'activity_type' => 'Training',
            'activity_location' => 'Life Skills Kitchen',
            'activity_goals' => 'Develop independence in daily activities',
            'activity_outcomes' => 'Better self-sufficiency'
        ],
        [
            'activity_name' => 'Social Skills Group',
            'activity_description' => 'Group activity to develop social interaction and communication skills.',
            'activity_type' => 'Training',
            'activity_location' => 'Group Room',
            'activity_goals' => 'Improve social interaction abilities',
            'activity_outcomes' => 'Enhanced social skills'
        ]
    ];

    public function run(): void
    {
        $this->command->info('🎯 Creating basic activities for all centres...');

        $users = Users::where('role', 'teacher')->get();
        
        if ($users->isEmpty()) {
            $this->command->error('No teachers found! Please run staff seeders first.');
            return;
        }

        $centres = ['01', '02', '03'];
        $totalActivities = 0;

        foreach ($centres as $centreId) {
            $centreTeachers = $users->where('centre_id', $centreId);
            $centreName = $centreId == '01' ? 'Gombak' : ($centreId == '02' ? 'Kuantan' : 'Pagoh');
            
            $this->command->info("Creating activities for $centreName...");
            
            foreach ($this->activityData as $index => $activityInfo) {
                $teacher = $centreTeachers->random();
                
                $activity = Activity::create([
                    'activity_id' => $centreId . '-ACT-' . str_pad($index + 1, 3, '0', STR_PAD_LEFT),
                    'activity_name' => $activityInfo['activity_name'],
                    'activity_description' => $activityInfo['activity_description'],
                    'activity_type' => $activityInfo['activity_type'],
                    'activity_date' => now()->addDays(rand(1, 30)),
                    'activity_start_time' => '09:00:00',
                    'activity_end_time' => '10:00:00',
                    'activity_location' => $activityInfo['activity_location'],
                    'max_participants' => 15,
                    'current_participants' => 0,
                    'activity_goals' => $activityInfo['activity_goals'],
                    'activity_outcomes' => $activityInfo['activity_outcomes'],
                    'activity_status' => 'scheduled',
                    'centre_id' => $centreId,
                    'created_by' => $teacher->id,
                    'instructor_id' => $teacher->id
                ]);

                $totalActivities++;
                $this->command->line("   ✅ {$activity->activity_id}: {$activity->activity_name}");
            }
        }

        $this->command->info("🎯 Total activities created: $totalActivities");
        $this->command->info("   📋 Each centre has 8 different activities");
        $this->command->info("   🎯 Activity types: Therapy, Education, Training");
    }
}