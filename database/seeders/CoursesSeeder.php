<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Courses;
use App\Models\Users;

class CoursesSeeder extends Seeder
{
    /**
     * Malaysian rehabilitation courses
     */
    private array $courseData = [
        [
            'course_name' => 'Early Intervention Program',
            'course_description' => 'Comprehensive early intervention program for children aged 3-6 years with developmental delays.',
            'course_level' => 'Beginner',
            'course_duration_months' => 12,
            'course_objectives' => ['Improve developmental milestones', 'Enhance communication skills', 'Develop social interaction'],
            'course_modules' => ['Basic Communication', 'Social Skills', 'Motor Development', 'Cognitive Skills'],
            'course_certificate' => 'Certificate of Early Intervention Completion',
            'course_fee' => 500.00,
            'max_trainees' => 15
        ],
        [
            'course_name' => 'Speech Therapy Program',
            'course_description' => 'Specialized speech and language therapy program for communication disorders.',
            'course_level' => 'All Levels',
            'course_duration_months' => 6,
            'course_objectives' => ['Improve speech clarity', 'Develop language skills', 'Enhance communication confidence'],
            'course_modules' => ['Speech Production', 'Language Development', 'Communication Strategies', 'Articulation Training'],
            'course_certificate' => 'Certificate of Speech Therapy Progress',
            'course_fee' => 300.00,
            'max_trainees' => 10
        ],
        [
            'course_name' => 'Occupational Therapy Program',
            'course_description' => 'Practical occupational therapy program focusing on daily living skills.',
            'course_level' => 'Intermediate',
            'course_duration_months' => 9,
            'course_objectives' => ['Develop fine motor skills', 'Improve daily living skills', 'Enhance sensory integration'],
            'course_modules' => ['Fine Motor Skills', 'Daily Living Activities', 'Sensory Integration', 'Adaptive Equipment'],
            'course_certificate' => 'Certificate of Occupational Therapy Achievement',
            'course_fee' => 400.00,
            'max_trainees' => 12
        ],
        [
            'course_name' => 'Vocational Training Program',
            'course_description' => 'Vocational training program preparing trainees for employment opportunities.',
            'course_level' => 'Advanced',
            'course_duration_months' => 18,
            'course_objectives' => ['Develop job skills', 'Prepare for employment', 'Build work confidence'],
            'course_modules' => ['Basic Job Skills', 'Computer Literacy', 'Interview Skills', 'Workplace Behavior'],
            'course_certificate' => 'Certificate of Vocational Training',
            'course_fee' => 600.00,
            'max_trainees' => 20
        ],
        [
            'course_name' => 'Behavioral Support Program',
            'course_description' => 'Behavioral intervention program for children with behavioral challenges.',
            'course_level' => 'Intermediate',
            'course_duration_months' => 8,
            'course_objectives' => ['Reduce challenging behaviors', 'Improve social skills', 'Develop coping strategies'],
            'course_modules' => ['Behavioral Analysis', 'Intervention Strategies', 'Social Skills Training', 'Family Support'],
            'course_certificate' => 'Certificate of Behavioral Support',
            'course_fee' => 450.00,
            'max_trainees' => 8
        ],
        [
            'course_name' => 'Life Skills Training Program',
            'course_description' => 'Comprehensive life skills training for independent living.',
            'course_level' => 'Advanced',
            'course_duration_months' => 15,
            'course_objectives' => ['Develop independence', 'Improve daily living skills', 'Enhance self-confidence'],
            'course_modules' => ['Personal Care', 'Home Management', 'Community Skills', 'Financial Literacy'],
            'course_certificate' => 'Certificate of Life Skills Mastery',
            'course_fee' => 550.00,
            'max_trainees' => 15
        ]
    ];

    public function run(): void
    {
        $this->command->info('📚 Creating Malaysian rehabilitation courses...');

        $users = Users::where('role', 'teacher')->get();

        if ($users->isEmpty()) {
            $this->command->error('No teachers found! Please run staff seeders first.');
            return;
        }

        $totalCourses = 0;

        foreach ($this->courseData as $index => $courseInfo) {
            $teacher = $users->random();
            
            $course = Courses::create([
                'course_id' => 'CRS-' . str_pad($index + 1, 3, '0', STR_PAD_LEFT),
                'course_name' => $courseInfo['course_name'],
                'course_description' => $courseInfo['course_description'],
                'course_level' => $courseInfo['course_level'],
                'course_duration_months' => $courseInfo['course_duration_months'],
                'course_objectives' => json_encode($courseInfo['course_objectives']),
                'course_modules' => json_encode($courseInfo['course_modules']),
                'course_certificate' => $courseInfo['course_certificate'],
                'course_status' => 'active',
                'course_fee' => $courseInfo['course_fee'],
                'max_trainees' => $courseInfo['max_trainees'],
                'created_by' => $teacher->id,
            ]);

            $totalCourses++;
            $this->command->line("   ✅ {$course->course_id}: {$course->course_name}");
        }

        $this->command->info("\n🎯 Total: {$totalCourses} courses created!");
    }
}