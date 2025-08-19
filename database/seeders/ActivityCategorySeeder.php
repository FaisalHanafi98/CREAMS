<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ActivityCategorySeeder extends Seeder
{
    /**
     * Seed activity categories for rehabilitation programs
     */
    public function run(): void
    {
        $this->command->info('📂 Seeding activity categories...');

        $categories = [
            [
                'category_name' => 'Speech & Language Therapy',
                'category_description' => 'Activities focused on developing communication skills, speech articulation, and language comprehension',
                'category_type' => 'rehabilitation',
                'category_color' => '#e74c3c',
                'category_icon' => 'fas fa-comments',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'category_name' => 'Occupational Therapy',
                'category_description' => 'Activities to develop fine motor skills, daily living skills, and sensory integration',
                'category_type' => 'rehabilitation',
                'category_color' => '#3498db',
                'category_icon' => 'fas fa-hand-paper',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'category_name' => 'Physical Therapy',
                'category_description' => 'Activities to improve gross motor skills, strength, balance, and coordination',
                'category_type' => 'rehabilitation',
                'category_color' => '#2ecc71',
                'category_icon' => 'fas fa-running',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'category_name' => 'Behavioral Intervention',
                'category_description' => 'Structured activities to address behavioral challenges and develop social skills',
                'category_type' => 'rehabilitation',
                'category_color' => '#f39c12',
                'category_icon' => 'fas fa-brain',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'category_name' => 'Academic Skills',
                'category_description' => 'Educational activities focused on literacy, numeracy, and cognitive development',
                'category_type' => 'academic',
                'category_color' => '#9b59b6',
                'category_icon' => 'fas fa-book',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'category_name' => 'Creative Arts',
                'category_description' => 'Art, music, and creative expression activities for therapeutic and developmental purposes',
                'category_type' => 'creative_social',
                'category_color' => '#e67e22',
                'category_icon' => 'fas fa-palette',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'category_name' => 'Social Skills Training',
                'category_description' => 'Group activities to develop interpersonal skills and social interaction abilities',
                'category_type' => 'creative_social',
                'category_color' => '#1abc9c',
                'category_icon' => 'fas fa-users',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'category_name' => 'Life Skills Training',
                'category_description' => 'Practical skills for independent living and daily functioning',
                'category_type' => 'academic',
                'category_color' => '#34495e',
                'category_icon' => 'fas fa-home',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'category_name' => 'Technology Skills',
                'category_description' => 'Computer and assistive technology training for enhanced communication and learning',
                'category_type' => 'academic',
                'category_color' => '#95a5a6',
                'category_icon' => 'fas fa-laptop',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'category_name' => 'Sensory Integration',
                'category_description' => 'Activities to help process and respond to sensory information effectively',
                'category_type' => 'rehabilitation',
                'category_color' => '#ff6b6b',
                'category_icon' => 'fas fa-hand-holding-heart',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'category_name' => 'Islamic Education',
                'category_description' => 'Religious education and character development based on Islamic values',
                'category_type' => 'faith',
                'category_color' => '#27ae60',
                'category_icon' => 'fas fa-moon',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];

        foreach ($categories as $category) {
            DB::table('activity_categories')->insert($category);
            $this->command->line("   ✅ Created category: {$category['category_name']}");
        }

        $this->command->info("📂 Successfully seeded " . count($categories) . " activity categories");
    }
}