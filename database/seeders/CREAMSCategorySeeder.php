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
            // Rehabilitation Category
            [
                'category_name' => 'Physical Therapy',
                'category_icon' => 'fas fa-running',
                'category_color' => '#28a745', // Green
                'category_description' => 'Improve physical strength, mobility, and motor skills through targeted exercises and therapeutic activities.',
                'category_status' => 'active',
                'sort_order' => 1
            ],
            [
                'category_name' => 'Occupational Therapy',
                'category_icon' => 'fas fa-hands-helping',
                'category_color' => '#17a2b8', // Teal
                'category_description' => 'Develop daily living skills, fine motor abilities, and adaptive behaviors for independent living.',
                'category_status' => 'active',
                'sort_order' => 2
            ],
            [
                'category_name' => 'Speech Therapy',
                'category_icon' => 'fas fa-comments',
                'category_color' => '#fd7e14', // Orange
                'category_description' => 'Enhance communication abilities, language development, and speech articulation skills.',
                'category_status' => 'active',
                'sort_order' => 3
            ],
            [
                'category_name' => 'Behavioral Therapy',
                'category_icon' => 'fas fa-brain',
                'category_color' => '#6f42c1', // Purple
                'category_description' => 'Manage behaviors, develop emotional regulation, and improve social interaction skills.',
                'category_status' => 'active',
                'sort_order' => 4
            ],
            [
                'category_name' => 'Sensory Integration',
                'category_icon' => 'fas fa-hand-paper',
                'category_color' => '#e83e8c', // Pink
                'category_description' => 'Process sensory information effectively and improve sensory-motor coordination.',
                'category_status' => 'active',
                'sort_order' => 5
            ],

            // Academic Category
            [
                'category_name' => 'Mathematics',
                'category_icon' => 'fas fa-calculator',
                'category_color' => '#007bff', // Blue
                'category_description' => 'Develop numerical skills, problem-solving abilities, and mathematical reasoning.',
                'category_status' => 'active',
                'sort_order' => 6
            ],
            [
                'category_name' => 'Literacy',
                'category_icon' => 'fas fa-book-open',
                'category_color' => '#20c997', // Teal green
                'category_description' => 'Improve reading comprehension, writing skills, and language literacy.',
                'category_status' => 'active',
                'sort_order' => 7
            ],
            [
                'category_name' => 'Science',
                'category_icon' => 'fas fa-flask',
                'category_color' => '#6610f2', // Indigo
                'category_description' => 'Explore scientific concepts through hands-on experiments and discovery-based learning.',
                'category_status' => 'active',
                'sort_order' => 8
            ],
            [
                'category_name' => 'Computer Skills',
                'category_icon' => 'fas fa-laptop',
                'category_color' => '#6c757d', // Gray
                'category_description' => 'Develop digital literacy, computer operation skills, and basic programming concepts.',
                'category_status' => 'active',
                'sort_order' => 9
            ],

            // Creative & Social Category
            [
                'category_name' => 'Art & Creativity',
                'category_icon' => 'fas fa-palette',
                'category_color' => '#dc3545', // Red
                'category_description' => 'Express creativity through various art forms and develop aesthetic appreciation.',
                'category_status' => 'active',
                'sort_order' => 10
            ],
            [
                'category_name' => 'Music Therapy',
                'category_icon' => 'fas fa-music',
                'category_color' => '#ffc107', // Yellow
                'category_description' => 'Use music to promote healing, improve communication, and enhance emotional well-being.',
                'category_status' => 'active',
                'sort_order' => 11
            ],
            [
                'category_name' => 'Social Skills',
                'category_icon' => 'fas fa-users',
                'category_color' => '#32bdea', // Primary blue
                'category_description' => 'Develop interpersonal skills, social awareness, and communication abilities.',
                'category_status' => 'active',
                'sort_order' => 12
            ],
            [
                'category_name' => 'Life Skills',
                'category_icon' => 'fas fa-graduation-cap',
                'category_color' => '#795548', // Brown
                'category_description' => 'Learn essential life skills for independent living and community participation.',
                'category_status' => 'active',
                'sort_order' => 13
            ],
            [
                'category_name' => 'Vocational Training',
                'category_icon' => 'fas fa-tools',
                'category_color' => '#607d8b', // Blue gray
                'category_description' => 'Develop job-related skills and prepare for workforce participation.',
                'category_status' => 'active',
                'sort_order' => 14
            ]
        ];

        foreach ($categories as $categoryData) {
            Category::create($categoryData);
        }
    }
}