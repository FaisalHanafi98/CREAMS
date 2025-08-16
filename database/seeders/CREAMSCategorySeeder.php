<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Category;

class CREAMSCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            // Rehabilitation Categories
            [
                'category_name' => 'Physical Therapy',
                'category_icon' => 'fas fa-running',
                'category_color' => '#28a745',
                'category_description' => 'Improve physical strength, mobility, and motor skills through targeted exercises and therapeutic activities.',
                'category_type' => 'rehabilitation',
                'is_active' => true
            ],
            [
                'category_name' => 'Occupational Therapy',
                'category_icon' => 'fas fa-hands-helping',
                'category_color' => '#17a2b8',
                'category_description' => 'Develop daily living skills, fine motor abilities, and adaptive behaviors for independent living.',
                'category_type' => 'rehabilitation',
                'is_active' => true
            ],
            [
                'category_name' => 'Speech Therapy',
                'category_icon' => 'fas fa-comments',
                'category_color' => '#fd7e14',
                'category_description' => 'Enhance communication abilities, language development, and speech articulation skills.',
                'category_type' => 'rehabilitation',
                'is_active' => true
            ],
            [
                'category_name' => 'Behavioral Therapy',
                'category_icon' => 'fas fa-brain',
                'category_color' => '#6f42c1',
                'category_description' => 'Manage behaviors, develop emotional regulation, and improve social interaction skills.',
                'category_type' => 'rehabilitation',
                'is_active' => true
            ],
            [
                'category_name' => 'Music Therapy',
                'category_icon' => 'fas fa-music',
                'category_color' => '#e83e8c',
                'category_description' => 'Use music and musical activities to achieve therapeutic goals and improve well-being.',
                'category_type' => 'rehabilitation',
                'is_active' => true
            ],
            // Academic Categories
            [
                'category_name' => 'Basic Literacy',
                'category_icon' => 'fas fa-book-open',
                'category_color' => '#007bff',
                'category_description' => 'Develop reading, writing, and fundamental language skills.',
                'category_type' => 'academic',
                'is_active' => true
            ],
            [
                'category_name' => 'Mathematics',
                'category_icon' => 'fas fa-calculator',
                'category_color' => '#6c757d',
                'category_description' => 'Build numerical understanding, problem-solving, and mathematical reasoning abilities.',
                'category_type' => 'academic',
                'is_active' => true
            ],
            [
                'category_name' => 'Life Skills',
                'category_icon' => 'fas fa-home',
                'category_color' => '#20c997',
                'category_description' => 'Learn practical daily living skills for independence and community integration.',
                'category_type' => 'academic',
                'is_active' => true
            ],
            // Recreational Categories
            [
                'category_name' => 'Arts & Crafts',
                'category_icon' => 'fas fa-paint-brush',
                'category_color' => '#ffc107',
                'category_description' => 'Express creativity through various artistic mediums and craft activities.',
                'category_type' => 'recreational',
                'is_active' => true
            ],
            [
                'category_name' => 'Sports & Games',
                'category_icon' => 'fas fa-futbol',
                'category_color' => '#dc3545',
                'category_description' => 'Participate in adaptive sports and recreational games for fitness and fun.',
                'category_type' => 'recreational',
                'is_active' => true
            ],
            // Faith Category
            [
                'category_name' => 'Islamic Studies',
                'category_icon' => 'fas fa-mosque',
                'category_color' => '#198754',
                'category_description' => 'Learn Islamic values, prayers, and spiritual development activities.',
                'category_type' => 'faith',
                'is_active' => true
            ]
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }

        $this->command->info('✅ Created ' . count($categories) . ' rehabilitation categories');
    }
}