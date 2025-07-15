<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Activity;
use App\Models\User;
use App\Models\Centres;

class ActivitySeeder extends Seeder
{
    public function run()
    {
        $admin = User::where('role', 'admin')->first();
        $centres = Centres::all();

        $activities = [
            // Physical Therapy
            [
                'activity_code' => 'PT001',
                'activity_name' => 'Basic Motor Skills Development',
                'description' => 'Fundamental exercises to improve gross motor skills including balance, coordination, and strength.',
                'category' => 'Physical Therapy',
                'activity_type' => 'Both',
                'objectives' => 'Improve balance, enhance coordination, build muscle strength, increase flexibility',
                'materials_needed' => 'Exercise mats, balance boards, therapy balls, resistance bands',
                'age_group' => '5-12 years',
                'difficulty_level' => 'Beginner',
                'min_participants' => 1,
                'max_participants' => 8,
                'duration_minutes' => 45,
                'is_active' => true
            ],
            [
                'activity_code' => 'PT002',
                'activity_name' => 'Aquatic Therapy',
                'description' => 'Water-based exercises for improving mobility and reducing joint stress.',
                'category' => 'Physical Therapy',
                'activity_type' => 'Group',
                'objectives' => 'Improve joint mobility, reduce pain, enhance cardiovascular fitness',
                'materials_needed' => 'Pool access, flotation devices, water weights',
                'age_group' => '8-18 years',
                'difficulty_level' => 'Intermediate',
                'min_participants' => 3,
                'max_participants' => 10,
                'duration_minutes' => 60,
                'is_active' => true
            ],
            // Occupational Therapy
            [
                'activity_code' => 'OT001',
                'activity_name' => 'Daily Living Skills Training',
                'description' => 'Practice essential daily activities like dressing, eating, and personal hygiene.',
                'category' => 'Occupational Therapy',
                'activity_type' => 'Individual',
                'objectives' => 'Develop independence in daily activities, improve fine motor skills',
                'materials_needed' => 'Adaptive utensils, button boards, zipper boards, practice clothing',
                'age_group' => '6-15 years',
                'difficulty_level' => 'Beginner',
                'min_participants' => 1,
                'max_participants' => 4,
                'duration_minutes' => 30,
                'is_active' => true
            ],
            [
                'activity_code' => 'OT002',
                'activity_name' => 'Sensory Integration Activities',
                'description' => 'Activities designed to help process and respond to sensory information.',
                'category' => 'Occupational Therapy',
                'activity_type' => 'Group',
                'objectives' => 'Improve sensory processing, enhance focus and attention',
                'materials_needed' => 'Sensory bins, textured materials, weighted blankets, fidget tools',
                'age_group' => '4-10 years',
                'difficulty_level' => 'Beginner',
                'min_participants' => 2,
                'max_participants' => 6,
                'duration_minutes' => 45,
                'is_active' => true
            ],
            // Speech Therapy
            [
                'activity_code' => 'ST001',
                'activity_name' => 'Articulation Practice',
                'description' => 'Focused exercises to improve speech clarity and pronunciation.',
                'category' => 'Speech Therapy',
                'activity_type' => 'Individual',
                'objectives' => 'Improve articulation, enhance speech clarity, build confidence',
                'materials_needed' => 'Mirror, flashcards, recording device, articulation cards',
                'age_group' => '4-12 years',
                'difficulty_level' => 'Intermediate',
                'min_participants' => 1,
                'max_participants' => 3,
                'duration_minutes' => 30,
                'is_active' => true
            ],
            [
                'activity_code' => 'ST002',
                'activity_name' => 'Social Communication Group',
                'description' => 'Group activities to practice conversation skills and social interaction.',
                'category' => 'Speech Therapy',
                'activity_type' => 'Group',
                'objectives' => 'Develop conversation skills, practice turn-taking, improve social cues recognition',
                'materials_needed' => 'Role-play cards, conversation starters, social stories',
                'age_group' => '8-16 years',
                'difficulty_level' => 'Intermediate',
                'min_participants' => 4,
                'max_participants' => 8,
                'duration_minutes' => 60,
                'is_active' => true
            ],
            // Academic Support
            [
                'activity_code' => 'AC001',
                'activity_name' => 'Basic Mathematics Skills',
                'description' => 'Foundational math concepts including counting, addition, and subtraction.',
                'category' => 'Mathematics',
                'activity_type' => 'Both',
                'objectives' => 'Master basic arithmetic, develop problem-solving skills',
                'materials_needed' => 'Manipulatives, worksheets, counting blocks, number lines',
                'age_group' => '6-10 years',
                'difficulty_level' => 'Beginner',
                'min_participants' => 1,
                'max_participants' => 12,
                'duration_minutes' => 45,
                'is_active' => true
            ],
            [
                'activity_code' => 'AC002',
                'activity_name' => 'Reading Comprehension',
                'description' => 'Interactive reading sessions to improve literacy and comprehension skills.',
                'category' => 'Literacy',
                'activity_type' => 'Group',
                'objectives' => 'Improve reading fluency, enhance comprehension, expand vocabulary',
                'materials_needed' => 'Age-appropriate books, reading guides, vocabulary cards',
                'age_group' => '7-14 years',
                'difficulty_level' => 'Intermediate',
                'min_participants' => 3,
                'max_participants' => 10,
                'duration_minutes' => 60,
                'is_active' => true
            ],
            // Life Skills
            [
                'activity_code' => 'LS001',
                'activity_name' => 'Money Management Basics',
                'description' => 'Learn to count money, make change, and understand basic financial concepts.',
                'category' => 'Life Skills',
                'activity_type' => 'Group',
                'objectives' => 'Understand money value, practice making purchases, develop budgeting skills',
                'materials_needed' => 'Play money, cash register, price tags, shopping scenarios',
                'age_group' => '10-18 years',
                'difficulty_level' => 'Intermediate',
                'min_participants' => 4,
                'max_participants' => 8,
                'duration_minutes' => 60,
                'is_active' => true
            ],
            [
                'activity_code' => 'LS002',
                'activity_name' => 'Cooking and Nutrition',
                'description' => 'Basic cooking skills and understanding of healthy eating habits.',
                'category' => 'Life Skills',
                'activity_type' => 'Group',
                'objectives' => 'Learn basic cooking techniques, understand nutrition, practice kitchen safety',
                'materials_needed' => 'Kitchen access, basic ingredients, cooking utensils, recipe cards',
                'age_group' => '12-18 years',
                'difficulty_level' => 'Advanced',
                'min_participants' => 4,
                'max_participants' => 6,
                'duration_minutes' => 90,
                'is_active' => true
            ]
        ];

        foreach ($activities as $activityData) {
            $centre = $centres->random();
            Activity::create(array_merge($activityData, [
                'created_by' => $admin->id,
                'centre_id' => $centre->centre_id
            ]));
        }
    }
}