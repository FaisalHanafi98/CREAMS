<?php
/**
 * Comprehensive Activity Management Testing Suite
 * Tests activities, enrollments, routes, and related functionality
 */

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== COMPREHENSIVE ACTIVITY MANAGEMENT TEST SUITE ===" . PHP_EOL;
echo "Testing all activity management functionality..." . PHP_EOL . PHP_EOL;

// Test 1: Activity Creation and Management
echo "1. TESTING ACTIVITY CREATION AND MANAGEMENT" . PHP_EOL;
echo str_repeat("-", 40) . PHP_EOL;

try {
    echo "Testing activity management..." . PHP_EOL;

    // Test activity creation
    $activityData = [
        'activity_name' => 'Test Activity',
        'description' => 'Test activity for system testing',
        'category' => 'therapy',
        'centre_id' => '01',
        'status' => 'active',
        'created_by' => 1
    ];

    $existingActivity = App\Models\Activity::where('activity_name', $activityData['activity_name'])->first();
    if ($existingActivity) {
        echo "✅ Test activity already exists (ID: {$existingActivity->id})" . PHP_EOL;
        $activity = $existingActivity;
    } else {
        $activity = App\Models\Activity::create($activityData);
        echo "✅ Activity created successfully (ID: {$activity->id})" . PHP_EOL;
    }

    // Test activity statistics
    $totalActivities = App\Models\Activity::count();
    $activeActivities = App\Models\Activity::where('status', 'active')->count();
    echo "✅ Activity statistics: $activeActivities active out of $totalActivities total" . PHP_EOL;

    // Test activity categories
    $categories = App\Models\Activity::select('category', DB::raw('count(*) as count'))
        ->groupBy('category')
        ->get();

    echo "✅ Activity categories:" . PHP_EOL;
    foreach ($categories as $category) {
        echo "   - {$category->category}: {$category->count} activities" . PHP_EOL;
    }

} catch (Exception $e) {
    echo "❌ Activity Management Error: " . $e->getMessage() . PHP_EOL;
}

// Test 2: Activity Enrollment System
echo PHP_EOL . "2. TESTING ACTIVITY ENROLLMENT SYSTEM" . PHP_EOL;
echo str_repeat("-", 40) . PHP_EOL;

try {
    echo "Testing activity enrollment..." . PHP_EOL;

    // Get test activity and trainee
    $activity = App\Models\Activity::first();
    $trainee = App\Models\Trainee::first();

    if ($activity && $trainee) {
        // Test enrollment creation
        $enrollmentData = [
            'activity_id' => $activity->id,
            'trainee_id' => $trainee->id,
            'enrollment_date' => now(),
            'status' => 'enrolled'
        ];

        $existingEnrollment = App\Models\ActivityEnrollment::where([
            'activity_id' => $activity->id,
            'trainee_id' => $trainee->id
        ])->first();

        if ($existingEnrollment) {
            echo "✅ Test enrollment already exists (ID: {$existingEnrollment->id})" . PHP_EOL;
        } else {
            $enrollment = App\Models\ActivityEnrollment::create($enrollmentData);
            echo "✅ Enrollment created successfully (ID: {$enrollment->id})" . PHP_EOL;
        }

        // Test enrollment statistics
        $totalEnrollments = App\Models\ActivityEnrollment::count();
        $activeEnrollments = App\Models\ActivityEnrollment::where('status', 'enrolled')->count();
        echo "✅ Enrollment statistics: $activeEnrollments active out of $totalEnrollments total" . PHP_EOL;

        // Test enrollment relationships
        $enrollment = App\Models\ActivityEnrollment::with(['activity', 'trainee'])->first();
        if ($enrollment) {
            echo "✅ Enrollment relationships working:" . PHP_EOL;
            echo "   - Activity: {$enrollment->activity->activity_name}" . PHP_EOL;
            echo "   - Trainee: {$enrollment->trainee->name}" . PHP_EOL;
        }

    } else {
        echo "⚠️ Missing activity or trainee for enrollment testing" . PHP_EOL;
    }

} catch (Exception $e) {
    echo "❌ Activity Enrollment Error: " . $e->getMessage() . PHP_EOL;
}

// Test 3: Activity Routes and Controllers
echo PHP_EOL . "3. TESTING ACTIVITY ROUTES AND CONTROLLERS" . PHP_EOL;
echo str_repeat("-", 40) . PHP_EOL;

try {
    echo "Testing activity routes and controllers..." . PHP_EOL;

    // Test ActivityController
    $activityController = new App\Http\Controllers\Activity\ActivityController();
    echo "✅ Activity controller accessible" . PHP_EOL;

    // Test route definitions (check if routes exist)
    $routes = [
        'activities.index',
        'activities.create',
        'activities.store',
        'activities.show',
        'activities.edit',
        'activities.update',
        'activities.destroy'
    ];

    foreach ($routes as $routeName) {
        try {
            $route = route($routeName, ['activity' => 1]);
            echo "✅ Route '$routeName' exists: $route" . PHP_EOL;
        } catch (Exception $e) {
            echo "⚠️ Route '$routeName' not found or has issues" . PHP_EOL;
        }
    }

} catch (Exception $e) {
    echo "❌ Activity Routes Error: " . $e->getMessage() . PHP_EOL;
}

// Test 4: Activity Sessions
echo PHP_EOL . "4. TESTING ACTIVITY SESSIONS" . PHP_EOL;
echo str_repeat("-", 40) . PHP_EOL;

try {
    echo "Testing activity sessions..." . PHP_EOL;

    $activity = App\Models\Activity::first();
    if ($activity) {
        // Test session creation
        $sessionData = [
            'activity_id' => $activity->id,
            'session_name' => 'Test Session',
            'session_date' => now()->addDays(1),
            'start_time' => '09:00:00',
            'end_time' => '10:00:00',
            'instructor_id' => 1,
            'max_participants' => 10,
            'status' => 'scheduled'
        ];

        $existingSession = App\Models\ActivitySession::where([
            'activity_id' => $activity->id,
            'session_name' => 'Test Session'
        ])->first();

        if ($existingSession) {
            echo "✅ Test session already exists (ID: {$existingSession->id})" . PHP_EOL;
        } else {
            $session = App\Models\ActivitySession::create($sessionData);
            echo "✅ Session created successfully (ID: {$session->id})" . PHP_EOL;
        }

        // Test session statistics
        $totalSessions = App\Models\ActivitySession::count();
        $scheduledSessions = App\Models\ActivitySession::where('status', 'scheduled')->count();
        echo "✅ Session statistics: $scheduledSessions scheduled out of $totalSessions total" . PHP_EOL;

        // Test session relationships
        $session = App\Models\ActivitySession::with(['activity', 'instructor'])->first();
        if ($session) {
            echo "✅ Session relationships working:" . PHP_EOL;
            echo "   - Activity: {$session->activity->activity_name}" . PHP_EOL;
            if ($session->instructor) {
                echo "   - Instructor: {$session->instructor->name}" . PHP_EOL;
            }
        }
    }

} catch (Exception $e) {
    echo "❌ Activity Sessions Error: " . $e->getMessage() . PHP_EOL;
}

// Test 5: Activity Data Integrity
echo PHP_EOL . "5. TESTING ACTIVITY DATA INTEGRITY" . PHP_EOL;
echo str_repeat("-", 40) . PHP_EOL;

try {
    echo "Checking activity data integrity..." . PHP_EOL;

    // Check for orphaned enrollments
    $orphanedEnrollments = App\Models\ActivityEnrollment::whereNotIn('activity_id',
        App\Models\Activity::pluck('id'))->count();

    if ($orphanedEnrollments > 0) {
        echo "⚠️ Found $orphanedEnrollments orphaned enrollments" . PHP_EOL;
    } else {
        echo "✅ No orphaned enrollments found" . PHP_EOL;
    }

    // Check for sessions without activities
    $orphanedSessions = App\Models\ActivitySession::whereNotIn('activity_id',
        App\Models\Activity::pluck('id'))->count();

    if ($orphanedSessions > 0) {
        echo "⚠️ Found $orphanedSessions orphaned sessions" . PHP_EOL;
    } else {
        echo "✅ No orphaned sessions found" . PHP_EOL;
    }

    // Check for activities without centres
    $activitiesWithoutCentre = App\Models\Activity::whereNull('centre_id')->count();

    if ($activitiesWithoutCentre > 0) {
        echo "⚠️ Found $activitiesWithoutCentre activities without centre assignments" . PHP_EOL;
    } else {
        echo "✅ All activities have centre assignments" . PHP_EOL;
    }

} catch (Exception $e) {
    echo "❌ Activity Data Integrity Error: " . $e->getMessage() . PHP_EOL;
}

echo PHP_EOL . "=== ACTIVITY MANAGEMENT TEST SUITE COMPLETED ===" . PHP_EOL;
echo "Check above for any ❌ errors that need attention." . PHP_EOL;