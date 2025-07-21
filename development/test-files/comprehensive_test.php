<?php
require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== COMPREHENSIVE CREAMS TESTING ===\n";

// Test database connection
echo "Testing database connection...\n";
try {
    DB::connection()->getPdo();
    echo "✅ Database connection successful\n";
} catch (Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
    exit(1);
}

// Check if we have test users
echo "\nChecking test users...\n";
$adminUser = App\Models\Users::where('role', 'admin')->first();
if ($adminUser) {
    echo "✅ Admin user found: {$adminUser->name} ({$adminUser->email})\n";
} else {
    echo "❌ No admin user found\n";
}

$teacherUser = App\Models\Users::where('role', 'teacher')->first();
if ($teacherUser) {
    echo "✅ Teacher user found: {$teacherUser->name} ({$teacherUser->email})\n";
} else {
    echo "❌ No teacher user found\n";
}

// Test Activities table structure
echo "\nTesting Activities table structure...\n";
$columns = DB::select('DESCRIBE activities');
$requiredColumns = ['id', 'activity_name', 'activity_description', 'activity_type', 'activity_status', 'centre_id'];
$existingColumns = array_column($columns, 'Field');

foreach ($requiredColumns as $col) {
    if (in_array($col, $existingColumns)) {
        echo "✅ Column '{$col}' exists\n";
    } else {
        echo "❌ Column '{$col}' missing\n";
    }
}

// Test Activities data
echo "\nTesting Activities data...\n";
$activities = App\Models\Activity::take(5)->get();
echo "Found " . App\Models\Activity::count() . " activities\n";
foreach ($activities as $activity) {
    echo "- {$activity->activity_name} (Status: {$activity->activity_status})\n";
}

// Test Centres table structure
echo "\nTesting Centres table structure...\n";
$centres = App\Models\Centres::take(3)->get();
echo "Found " . App\Models\Centres::count() . " centres\n";
foreach ($centres as $centre) {
    echo "- {$centre->centre_name} (ID: {$centre->centre_id})\n";
}

// Test Trainees table structure
echo "\nTesting Trainees table structure...\n";
$trainees = App\Models\Trainee::take(3)->get();
echo "Found " . App\Models\Trainee::count() . " trainees\n";
foreach ($trainees as $trainee) {
    echo "- {$trainee->name} (Centre: {$trainee->centre_name})\n";
}

echo "\n=== END COMPREHENSIVE TESTING ===\n";