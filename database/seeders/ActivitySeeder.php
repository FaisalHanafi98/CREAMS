<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class ActivitySeeder extends Seeder
{
    /**
     * Seed activities table with comprehensive rehabilitation activities
     */
    public function run(): void
    {
        $this->command->info('🎯 Seeding rehabilitation activities...');

        $faker = Faker::create();
        
        // Get categories and centres
        $categories = DB::table('activity_categories')->get();
        $centres = DB::table('centres')->get();
        $instructors = DB::table('users')->whereIn('role', ['teacher', 'supervisor'])->get();
        
        if ($categories->isEmpty() || $centres->isEmpty() || $instructors->isEmpty()) {
            $this->command->error('Required data missing! Ensure categories, centres, and users are seeded first.');
            return;
        }

        // Predefined activities for each category to ensure realistic content
        $activityTemplates = [
            'Speech & Language Therapy' => [
                ['name' => 'Basic Articulation Training', 'description' => 'Individual sessions focusing on correct pronunciation and speech sound production'],
                ['name' => 'Language Comprehension Activities', 'description' => 'Interactive activities to improve understanding of spoken and written language'],
                ['name' => 'Communication Board Training', 'description' => 'Using visual aids and communication boards for non-verbal communication'],
                ['name' => 'Conversation Skills Development', 'description' => 'Group sessions to practice social communication and turn-taking'],
                ['name' => 'Voice and Fluency Therapy', 'description' => 'Specialized sessions for voice quality and fluency improvement']
            ],
            'Occupational Therapy' => [
                ['name' => 'Fine Motor Skills Development', 'description' => 'Activities using puzzles, threading, and manipulation tasks'],
                ['name' => 'Sensory Integration Therapy', 'description' => 'Structured sensory experiences to improve processing and responses'],
                ['name' => 'Daily Living Skills Training', 'description' => 'Practical skills for eating, dressing, and personal hygiene'],
                ['name' => 'Handwriting and Grip Training', 'description' => 'Specialized activities to improve writing skills and pencil grip'],
                ['name' => 'Adaptive Equipment Training', 'description' => 'Learning to use assistive devices for daily activities']
            ],
            'Physical Therapy' => [
                ['name' => 'Gross Motor Development', 'description' => 'Activities to strengthen large muscle groups and coordination'],
                ['name' => 'Balance and Coordination Training', 'description' => 'Exercises using balance boards, balls, and movement activities'],
                ['name' => 'Mobility and Gait Training', 'description' => 'Walking practice and mobility aid training'],
                ['name' => 'Strength Building Exercises', 'description' => 'Age-appropriate resistance and strengthening activities'],
                ['name' => 'Flexibility and Range of Motion', 'description' => 'Stretching and movement exercises to maintain joint flexibility']
            ],
            'Behavioral Intervention' => [
                ['name' => 'Applied Behavior Analysis (ABA)', 'description' => 'Structured behavioral intervention using ABA principles'],
                ['name' => 'Social Stories and Role Play', 'description' => 'Using narratives and role-playing to teach appropriate behaviors'],
                ['name' => 'Positive Behavior Support', 'description' => 'Reinforcement-based strategies to encourage positive behaviors'],
                ['name' => 'Self-Regulation Training', 'description' => 'Teaching emotional regulation and coping strategies'],
                ['name' => 'Transition and Routine Practice', 'description' => 'Activities to help with changes and daily routines']
            ],
            'Academic Skills' => [
                ['name' => 'Reading Comprehension', 'description' => 'Literacy activities adapted for different learning abilities'],
                ['name' => 'Numeracy and Math Concepts', 'description' => 'Basic mathematics using concrete materials and visual aids'],
                ['name' => 'Science Exploration', 'description' => 'Hands-on science activities and experiments'],
                ['name' => 'Geography and Social Studies', 'description' => 'Learning about communities, cultures, and geography'],
                ['name' => 'Critical Thinking Skills', 'description' => 'Problem-solving and reasoning activities']
            ],
            'Creative Arts' => [
                ['name' => 'Art Therapy Sessions', 'description' => 'Expressive art activities for emotional and creative development'],
                ['name' => 'Music and Movement Therapy', 'description' => 'Using music, rhythm, and dance for therapeutic purposes'],
                ['name' => 'Drama and Theater Activities', 'description' => 'Role-playing and performance activities for self-expression'],
                ['name' => 'Craft and Construction Projects', 'description' => 'Hands-on building and creating activities'],
                ['name' => 'Photography and Digital Arts', 'description' => 'Using technology for creative expression and skill development']
            ],
            'Social Skills Training' => [
                ['name' => 'Group Interaction Activities', 'description' => 'Structured group activities to practice social skills'],
                ['name' => 'Friendship and Relationship Building', 'description' => 'Activities focused on building and maintaining relationships'],
                ['name' => 'Community Integration Practice', 'description' => 'Real-world social situations and community involvement'],
                ['name' => 'Conflict Resolution Training', 'description' => 'Learning to handle disagreements and social conflicts'],
                ['name' => 'Communication Etiquette', 'description' => 'Proper social communication and manners training']
            ],
            'Life Skills Training' => [
                ['name' => 'Cooking and Meal Preparation', 'description' => 'Basic cooking skills and kitchen safety'],
                ['name' => 'Personal Hygiene and Self-Care', 'description' => 'Daily grooming and personal care routines'],
                ['name' => 'Money Management Skills', 'description' => 'Basic financial literacy and money handling'],
                ['name' => 'Time Management and Scheduling', 'description' => 'Understanding time concepts and daily planning'],
                ['name' => 'Public Transportation Training', 'description' => 'Safe and independent travel skills']
            ],
            'Technology Skills' => [
                ['name' => 'Basic Computer Skills', 'description' => 'Introduction to computers, keyboard, and mouse usage'],
                ['name' => 'Educational Software Training', 'description' => 'Using specialized learning software and applications'],
                ['name' => 'Assistive Technology Training', 'description' => 'Learning to use adaptive equipment and communication devices'],
                ['name' => 'Internet Safety and Skills', 'description' => 'Safe internet usage and digital citizenship'],
                ['name' => 'Mobile Device Training', 'description' => 'Using tablets and smartphones for communication and learning']
            ],
            'Sensory Integration' => [
                ['name' => 'Sensory Diet Activities', 'description' => 'Personalized sensory activities to meet individual needs'],
                ['name' => 'Proprioceptive Training', 'description' => 'Activities to improve body awareness and positioning'],
                ['name' => 'Vestibular System Development', 'description' => 'Balance and spatial orientation activities'],
                ['name' => 'Tactile Sensitivity Training', 'description' => 'Gradual exposure to different textures and touch sensations'],
                ['name' => 'Visual-Motor Integration', 'description' => 'Activities combining visual perception with motor skills']
            ],
            'Islamic Education' => [
                ['name' => 'Quran Reading and Memorization', 'description' => 'Adapted Quranic studies for different learning abilities'],
                ['name' => 'Islamic Values and Character', 'description' => 'Teaching Islamic moral values and good character'],
                ['name' => 'Prayer and Worship Skills', 'description' => 'Learning the basics of Islamic worship and prayer'],
                ['name' => 'Islamic History Stories', 'description' => 'Stories from Islamic history adapted for special needs learners'],
                ['name' => 'Islamic Art and Calligraphy', 'description' => 'Creative activities incorporating Islamic art and writing']
            ]
        ];

        $totalActivities = 0;
        
        foreach ($categories as $category) {
            $templates = $activityTemplates[$category->category_name] ?? [];
            
            foreach ($centres as $centre) {
                // Create 1-2 activities per category per centre
                $activitiesPerCentre = min(count($templates), 2);
                $selectedTemplates = $faker->randomElements($templates, $activitiesPerCentre);
                
                foreach ($selectedTemplates as $template) {
                    $instructor = $instructors->random();
                    
                    $activityData = [
                        'activity_name' => $template['name'] . ' (' . $centre->centre_name . ')',
                        'activity_description' => $template['description'],
                        'category_id' => $category->id,
                        'centre_id' => $centre->centre_id,
                        'duration_weeks' => $faker->numberBetween(8, 16),
                        'sessions_per_week' => $faker->numberBetween(2, 4),
                        'session_duration_minutes' => $faker->randomElement([45, 60, 75, 90]),
                        'max_participants' => $faker->numberBetween(4, 12),
                        'learning_outcomes' => $this->generateLearningOutcomes($category->category_name, $faker),
                        'instructor_id' => $instructor->id,
                        'is_active' => true,
                        'times_conducted' => $faker->numberBetween(0, 5),
                        'created_at' => now(),
                        'updated_at' => now()
                    ];
                    
                    DB::table('activities')->insert($activityData);
                    $totalActivities++;
                }
            }
            
            $this->command->line("   ✅ Created activities for: {$category->category_name}");
        }

        $this->command->info("🎯 Successfully seeded {$totalActivities} rehabilitation activities");
        
        // Show distribution by centre
        $centreStats = DB::table('activities')
            ->join('centres', 'activities.centre_id', '=', 'centres.centre_id')
            ->select('centres.centre_name', DB::raw('count(*) as count'))
            ->groupBy('centres.centre_name')
            ->get();
            
        foreach ($centreStats as $stat) {
            $this->command->line("   📊 {$stat->centre_name}: {$stat->count} activities");
        }
    }

    private function generateLearningOutcomes($categoryName, $faker)
    {
        $outcomes = [
            'Speech & Language Therapy' => [
                'Improve articulation and speech clarity',
                'Enhance vocabulary and language comprehension',
                'Develop effective communication strategies',
                'Increase confidence in verbal expression'
            ],
            'Occupational Therapy' => [
                'Improve fine motor coordination and dexterity',
                'Develop independence in daily living activities',
                'Enhance sensory processing abilities',
                'Increase functional hand and finger strength'
            ],
            'Physical Therapy' => [
                'Improve gross motor skills and coordination',
                'Increase muscle strength and endurance',
                'Enhance balance and postural control',
                'Develop safe mobility and movement patterns'
            ],
            'Behavioral Intervention' => [
                'Reduce challenging behaviors',
                'Increase positive social interactions',
                'Develop self-regulation skills',
                'Improve compliance and following instructions'
            ],
            'Academic Skills' => [
                'Improve literacy and reading comprehension',
                'Develop basic numeracy skills',
                'Enhance problem-solving abilities',
                'Increase academic confidence and engagement'
            ]
        ];
        
        $categoryOutcomes = $outcomes[$categoryName] ?? [
            'Develop targeted skills in this area',
            'Improve functional abilities',
            'Increase independence and confidence',
            'Enhance overall quality of life'
        ];
        
        return implode('; ', $faker->randomElements($categoryOutcomes, rand(2, 4)));
    }
}