<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            // Rehabilitation Categories
            [
                'name' => 'Physical Therapy',
                'type' => 'rehabilitation',
                'icon' => 'fas fa-running',
                'color' => '#28a745', // Green
                'description' => 'Improve physical strength, mobility, and motor skills through targeted exercises and therapeutic activities.',
                'sort_order' => 1
            ],
            [
                'name' => 'Occupational Therapy',
                'type' => 'rehabilitation',
                'icon' => 'fas fa-hands-helping',
                'color' => '#17a2b8', // Teal
                'description' => 'Develop daily living skills, fine motor abilities, and adaptive behaviors for independent living.',
                'sort_order' => 2
            ],
            [
                'name' => 'Speech Therapy',
                'type' => 'rehabilitation',
                'icon' => 'fas fa-comments',
                'color' => '#fd7e14', // Orange
                'description' => 'Enhance communication abilities, language development, and speech articulation skills.',
                'sort_order' => 3
            ],
            [
                'name' => 'Behavioral Therapy',
                'type' => 'rehabilitation',
                'icon' => 'fas fa-brain',
                'color' => '#6f42c1', // Purple
                'description' => 'Manage behaviors, develop emotional regulation, and improve social interaction skills.',
                'sort_order' => 4
            ],
            [
                'name' => 'Sensory Integration',
                'type' => 'rehabilitation',
                'icon' => 'fas fa-hand-paper',
                'color' => '#e83e8c', // Pink
                'description' => 'Process sensory information effectively and improve sensory-motor coordination.',
                'sort_order' => 5
            ],

            // Academic Categories
            [
                'name' => 'Mathematics',
                'type' => 'academic',
                'icon' => 'fas fa-calculator',
                'color' => '#007bff', // Blue
                'description' => 'Develop numerical skills, problem-solving abilities, and mathematical reasoning.',
                'sort_order' => 6
            ],
            [
                'name' => 'Literacy',
                'type' => 'academic',
                'icon' => 'fas fa-book-open',
                'color' => '#20c997', // Teal green
                'description' => 'Improve reading comprehension, writing skills, and language literacy.',
                'sort_order' => 7
            ],
            [
                'name' => 'Science',
                'type' => 'academic',
                'icon' => 'fas fa-flask',
                'color' => '#6610f2', // Indigo
                'description' => 'Explore scientific concepts through hands-on experiments and discovery-based learning.',
                'sort_order' => 8
            ],
            [
                'name' => 'Computer Skills',
                'type' => 'academic',
                'icon' => 'fas fa-laptop',
                'color' => '#6c757d', // Gray
                'description' => 'Develop digital literacy, computer operation skills, and basic programming concepts.',
                'sort_order' => 9
            ],

            // Creative & Social Categories
            [
                'name' => 'Art & Creativity',
                'type' => 'academic',
                'icon' => 'fas fa-palette',
                'color' => '#dc3545', // Red
                'description' => 'Express creativity through various art forms and develop aesthetic appreciation.',
                'sort_order' => 10
            ],
            [
                'name' => 'Music Therapy',
                'type' => 'rehabilitation',
                'icon' => 'fas fa-music',
                'color' => '#ffc107', // Yellow
                'description' => 'Use music to promote healing, improve communication, and enhance emotional well-being.',
                'sort_order' => 11
            ],
            [
                'name' => 'Social Skills',
                'type' => 'academic',
                'icon' => 'fas fa-users',
                'color' => '#32bdea', // Primary blue
                'description' => 'Develop interpersonal skills, social awareness, and communication abilities.',
                'sort_order' => 12
            ],
            [
                'name' => 'Life Skills',
                'type' => 'academic',
                'icon' => 'fas fa-graduation-cap',
                'color' => '#795548', // Brown
                'description' => 'Learn essential life skills for independent living and community participation.',
                'sort_order' => 13
            ],
            [
                'name' => 'Vocational Training',
                'type' => 'academic',
                'icon' => 'fas fa-tools',
                'color' => '#607d8b', // Blue gray
                'description' => 'Develop job-related skills and prepare for workforce participation.',
                'sort_order' => 14
            ]
        ];

        foreach ($categories as $categoryData) {
            Category::create($categoryData);
        }
    }
}