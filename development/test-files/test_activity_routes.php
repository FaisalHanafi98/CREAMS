<?php
require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== TESTING ACTIVITY ROUTES ===\n";

// Test if ActivityController can be instantiated
echo "Testing ActivityController instantiation...\n";
try {
    $controller = new App\Http\Controllers\ActivityController();
    echo "✅ ActivityController instantiated successfully\n";
} catch (Exception $e) {
    echo "❌ ActivityController instantiation failed: " . $e->getMessage() . "\n";
}

// Test if categories can be retrieved for create form
echo "\nTesting categories for create form...\n";
try {
    $categories = App\Models\Category::active()->ordered()->get();
    echo "✅ Category retrieved: " . $categories->count() . " categories\n";
    foreach ($categories as $category) {
        echo "  - {$category->category_name}\n";
    }
} catch (Exception $e) {
    echo "❌ Category retrieval failed: " . $e->getMessage() . "\n";
}

// Test if activities index works
echo "\nTesting activities index...\n";
try {
    $activities = App\Models\Activity::with('centre')->paginate(10);
    echo "✅ Activity index data retrieved: " . $activities->count() . " activities\n";
    foreach ($activities as $activity) {
        echo "  - {$activity->activity_name} ({$activity->activity_status})\n";
    }
} catch (Exception $e) {
    echo "❌ Activity index failed: " . $e->getMessage() . "\n";
}

// Test if activity show works
echo "\nTesting activity show...\n";
try {
    $activity = App\Models\Activity::first();
    if ($activity) {
        echo "✅ Activity show works: {$activity->activity_name}\n";
        echo "  - Description: " . substr($activity->activity_description, 0, 50) . "...\n";
        echo "  - Type: {$activity->activity_type}\n";
        echo "  - Date: {$activity->activity_date}\n";
        echo "  - Location: {$activity->activity_location}\n";
        echo "  - Max Participants: {$activity->max_participants}\n";
        echo "  - Current Participants: {$activity->current_participants}\n";
    } else {
        echo "❌ No activities found for show test\n";
    }
} catch (Exception $e) {
    echo "❌ Activity show failed: " . $e->getMessage() . "\n";
}

echo "\n=== END ACTIVITY ROUTES TEST ===\n";