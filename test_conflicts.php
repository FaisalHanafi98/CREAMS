<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Activity;
use App\Models\Users;
use App\Models\Trainee;
use App\Models\ActivityEnrollment;
use Carbon\Carbon;

echo "\n=== ACTIVITY TIME CONFLICT DETECTION TEST ===\n";

// Get our test activities
$activityA = Activity::where('activity_id', 'TEST-A-001')->first();
$activityB = Activity::where('activity_id', 'TEST-B-002')->first();
$activityC = Activity::where('activity_id', 'TEST-C-003')->first();
$teacher = Users::find(16);
$trainee = Trainee::find(1);

if (!$activityA || !$activityB || !$activityC) {
    echo "❌ Test activities not found. Please run TestActivitiesSeeder first.\n";
    exit(1);
}

echo "Activity A: {$activityA->activity_name} ({$activityA->activity_start_time} - {$activityA->activity_end_time})\n";
echo "Activity B: {$activityB->activity_name} ({$activityB->activity_start_time} - {$activityB->activity_end_time})\n";
echo "Activity C: {$activityC->activity_name} ({$activityC->activity_start_time} - {$activityC->activity_end_time})\n";

// Test 1: Check if Activity A conflicts with Activity B
echo "\n--- Test 1: Activity A vs Activity B ---\n";
$conflictAB = $activityA->hasTimeConflictWith($activityB->activity_start_time, $activityB->activity_end_time, $activityB->activity_date);
echo "A conflicts with B: " . ($conflictAB ? 'YES ✅' : 'NO ❌') . "\n";
echo "Expected: YES (10:00-11:00 overlaps with 10:30-11:30)\n";

// Test 2: Check if Activity A conflicts with Activity C
echo "\n--- Test 2: Activity A vs Activity C ---\n";
$conflictAC = $activityA->hasTimeConflictWith($activityC->activity_start_time, $activityC->activity_end_time, $activityC->activity_date);
echo "A conflicts with C: " . ($conflictAC ? 'YES ❌' : 'NO ✅') . "\n";
echo "Expected: NO (10:00-11:00 does not overlap with 11:30-12:30)\n";

// Test 3: Check instructor conflicts for Activity B time slot
echo "\n--- Test 3: Instructor Conflict Detection ---\n";
$testDate = $activityA->activity_date->format('Y-m-d');
echo "Testing with date: {$testDate}\n";
$instructorConflicts = Activity::checkTimeConflicts(16, '10:30:00', '11:30:00', $testDate);
echo "Instructor conflicts found for 10:30-11:30: " . count($instructorConflicts) . "\n";
foreach ($instructorConflicts as $conflict) {
    echo "  - {$conflict->activity_name} ({$conflict->activity_start_time} - {$conflict->activity_end_time})\n";
}
echo "Expected: 1 conflict (Activity A)\n";

// Test 4: Try to enroll trainee in Activity A and test conflicts
echo "\n--- Test 4: Trainee Enrollment & Conflict Detection ---\n";
$enrollment = $activityA->enrollTrainee($trainee->id, $teacher->id);
if ($enrollment) {
    echo "✅ Trainee enrolled in Activity A successfully\n";
    
    // Now check if trainee has conflicts for Activity B
    $traineeConflicts = Activity::checkTraineeConflicts($trainee->id, '10:30:00', '11:30:00', $testDate);
    echo "Trainee conflicts found for 10:30-11:30: " . count($traineeConflicts) . "\n";
    foreach ($traineeConflicts as $conflict) {
        echo "  - {$conflict->activity_name} ({$conflict->activity_start_time} - {$conflict->activity_end_time})\n";
    }
    echo "Expected: 1 conflict (Activity A where trainee is enrolled)\n";
} else {
    echo "❌ Failed to enroll trainee in Activity A\n";
}

// Test 5: Check if trainee can enroll in Activity B (should be blocked due to conflict)
echo "\n--- Test 5: Conflict Prevention Test ---\n";
$canEnrollB = $activityB->canEnrollTrainee($trainee->id);
echo "Can trainee enroll in Activity B: " . ($canEnrollB ? 'YES ❌' : 'NO ✅') . "\n";
echo "Expected: NO (trainee already enrolled in conflicting Activity A)\n";

// Test 6: Test with Activity C (should allow enrollment)
echo "\n--- Test 6: Non-Conflict Enrollment Test ---\n";
$canEnrollC = $activityC->canEnrollTrainee($trainee->id);
echo "Can trainee enroll in Activity C: " . ($canEnrollC ? 'YES ✅' : 'NO ❌') . "\n";
echo "Expected: YES (no time conflict with Activity A)\n";

echo "\n=== TEST COMPLETED ===\n";