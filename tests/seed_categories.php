<?php
require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== SEEDING CATEGORIES ===\n";

$categories = [
    ['category_name' => 'Physical Therapy', 'category_description' => 'Physical rehabilitation and motor skills development', 'category_color' => '#FF6B6B', 'category_icon' => 'fas fa-running', 'category_status' => 'active', 'sort_order' => 1],
    ['category_name' => 'Occupational Therapy', 'category_description' => 'Daily living skills and occupational rehabilitation', 'category_color' => '#4ECDC4', 'category_icon' => 'fas fa-tools', 'category_status' => 'active', 'sort_order' => 2],
    ['category_name' => 'Speech Therapy', 'category_description' => 'Communication and speech development', 'category_color' => '#45B7D1', 'category_icon' => 'fas fa-comments', 'category_status' => 'active', 'sort_order' => 3],
    ['category_name' => 'Behavioral Therapy', 'category_description' => 'Behavioral modification and social skills', 'category_color' => '#96CEB4', 'category_icon' => 'fas fa-brain', 'category_status' => 'active', 'sort_order' => 4],
    ['category_name' => 'Educational', 'category_description' => 'Academic and educational activities', 'category_color' => '#FECA57', 'category_icon' => 'fas fa-book', 'category_status' => 'active', 'sort_order' => 5],
    ['category_name' => 'Life Skills', 'category_description' => 'Independent living and life skills training', 'category_color' => '#FF9FF3', 'category_icon' => 'fas fa-home', 'category_status' => 'active', 'sort_order' => 6],
    ['category_name' => 'Sensory Integration', 'category_description' => 'Sensory processing and integration therapy', 'category_color' => '#54A0FF', 'category_icon' => 'fas fa-eye', 'category_status' => 'active', 'sort_order' => 7],
    ['category_name' => 'Arts & Crafts', 'category_description' => 'Creative expression and fine motor skills', 'category_color' => '#5F27CD', 'category_icon' => 'fas fa-paint-brush', 'category_status' => 'active', 'sort_order' => 8],
    ['category_name' => 'Music Therapy', 'category_description' => 'Musical activities for therapeutic purposes', 'category_color' => '#00D2D3', 'category_icon' => 'fas fa-music', 'category_status' => 'active', 'sort_order' => 9],
    ['category_name' => 'Vocational Training', 'category_description' => 'Job skills and vocational preparation', 'category_color' => '#FF6348', 'category_icon' => 'fas fa-briefcase', 'category_status' => 'active', 'sort_order' => 10],
];

try {
    foreach ($categories as $category) {
        $existing = App\Models\Category::where('category_name', $category['category_name'])->first();
        if (!$existing) {
            App\Models\Category::create($category);
            echo "✅ Created category: {$category['category_name']}\n";
        } else {
            echo "⚠️ Category already exists: {$category['category_name']}\n";
        }
    }
    
    echo "\n✅ Category seeding completed!\n";
    echo "Total categories: " . App\Models\Category::count() . "\n";
} catch (Exception $e) {
    echo "❌ Error seeding categories: " . $e->getMessage() . "\n";
}

echo "\n=== END CATEGORY SEEDING ===\n";