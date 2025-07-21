<?php
require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== TESTING ACTIVITIES MODULE ===\n";

// Set up a mock session for testing
Session::put('id', 1);
Session::put('role', 'admin');
Session::put('centre_id', '01');
Session::put('name', 'Test Admin');

// Test if we can get categories
echo "Testing categories...\n";
try {
    $categories = App\Models\Category::active()->ordered()->get();
    echo "✅ Categories retrieved: " . $categories->count() . " categories\n";
    foreach ($categories as $category) {
        echo "  - {$category->category_name} (Status: {$category->category_status})\n";
    }
} catch (Exception $e) {
    echo "❌ Categories error: " . $e->getMessage() . "\n";
}

// Test if we can create an activity
echo "\nTesting activity creation...\n";
try {
    $testActivity = App\Models\Activity::create([
        'activity_name' => 'Test Activity ' . now()->format('Y-m-d H:i:s'),
        'activity_id' => 'TEST-' . now()->format('mdHi'),
        'activity_description' => 'This is a test activity',
        'activity_type' => 'Education',
        'activity_date' => now()->addDays(1)->format('Y-m-d'),
        'activity_start_time' => '09:00',
        'activity_end_time' => '10:00',
        'activity_location' => 'Test Room',
        'max_participants' => 20,
        'current_participants' => 0,
        'activity_goals' => 'Test goals',
        'activity_outcomes' => 'Test outcomes',
        'required_resources' => 'Test resources',
        'activity_status' => 'scheduled',
        'created_by' => 1,
        'centre_id' => '01',
        'instructor_id' => 1
    ]);
    
    echo "✅ Activity created successfully: {$testActivity->activity_name} (ID: {$testActivity->id})\n";
} catch (Exception $e) {
    echo "❌ Activity creation error: " . $e->getMessage() . "\n";
}

// Test if we can read activities
echo "\nTesting activity reading...\n";
try {
    $activities = App\Models\Activity::take(3)->get();
    echo "✅ Activities retrieved: " . $activities->count() . " activities\n";
    foreach ($activities as $activity) {
        echo "  - {$activity->activity_name} (Status: {$activity->activity_status})\n";
    }
} catch (Exception $e) {
    echo "❌ Activity reading error: " . $e->getMessage() . "\n";
}

echo "\n=== END ACTIVITIES MODULE TEST ===\n";