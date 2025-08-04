<?php
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

// Start session to simulate login
session_start();

// Simulate admin user login
$_SESSION['id'] = 1;
$_SESSION['role'] = 'admin';
$_SESSION['name'] = 'Goh Ai Ling';
$_SESSION['centre_id'] = '01';

// Test activity enrollment page access
try {
    echo "Testing activity enrollment functionality...\n";
    
    // First, check if we have activities
    $activityCount = App\Models\Activity::count();
    echo "Total activities in database: {$activityCount}\n";
    
    if ($activityCount > 0) {
        $activity = App\Models\Activity::first();
        echo "Sample activity: {$activity->activity_name} (ID: {$activity->id})\n";
        
        // Test enrollment form access
        $request = Illuminate\Http\Request::create("/activities/{$activity->id}/enroll", 'GET');
        $response = $app->handle($request);
        
        echo "Enrollment form access status: " . $response->getStatusCode() . "\n";
        
        if ($response->getStatusCode() === 200) {
            echo "✓ Enrollment form loads successfully!\n";
            
            // Check if form contains expected elements
            $content = $response->getContent();
            if (strpos($content, 'trainee_ids') !== false) {
                echo "✓ Form contains trainee selection fields!\n";
            } else {
                echo "✗ Form missing trainee selection fields.\n";
            }
        } else {
            echo "✗ Enrollment form failed to load.\n";
            if ($response->getStatusCode() >= 400) {
                echo "Error content: " . substr($response->getContent(), 0, 200) . "\n";
            }
        }
        
        // Check trainees count
        $traineeCount = App\Models\Trainee::count();
        echo "Total trainees in database: {$traineeCount}\n";
        
        // Check current enrollments
        $enrollmentCount = App\Models\ActivityEnrollment::where('activity_id', $activity->id)->count();
        echo "Current enrollments for activity '{$activity->activity_name}': {$enrollmentCount}\n";
        
    } else {
        echo "No activities found in database.\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
}

echo "\n---\n";

// Test session-based activities
try {
    echo "Testing activity sessions...\n";
    
    $sessionCount = App\Models\ActivitySession::count();
    echo "Total activity sessions: {$sessionCount}\n";
    
    if ($sessionCount > 0) {
        $session = App\Models\ActivitySession::first();
        echo "Sample session: {$session->session_name} (ID: {$session->id})\n";
        echo "Session date: {$session->session_date}\n";
        echo "Session status: {$session->status}\n";
    }
    
} catch (Exception $e) {
    echo "Session check error: " . $e->getMessage() . "\n";
}

echo "\n---\n";

// Check if activity models have proper relationships
try {
    echo "Testing model relationships...\n";
    
    if (method_exists(App\Models\Activity::class, 'activeEnrollments')) {
        echo "✓ Activity model has activeEnrollments relationship\n";
    } else {
        echo "✗ Activity model missing activeEnrollments relationship\n";
    }
    
    if (method_exists(App\Models\ActivityEnrollment::class, 'trainee')) {
        echo "✓ ActivityEnrollment model has trainee relationship\n";
    } else {
        echo "✗ ActivityEnrollment model missing trainee relationship\n";
    }
    
} catch (Exception $e) {
    echo "Relationship check error: " . $e->getMessage() . "\n";
}