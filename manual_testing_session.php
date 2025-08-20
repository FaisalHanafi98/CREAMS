<?php
/**
 * CREAMS Manual Testing Session
 * Simulating real user interactions for demo day preparation
 */

require_once 'vendor/autoload.php';

// Start output buffering to capture any output
ob_start();

echo "=============================================================================\n";
echo "                   CREAMS MANUAL TESTING SESSION\n";
echo "=============================================================================\n";
echo "Testing Date: " . date('Y-m-d H:i:s') . "\n";
echo "Environment: Laravel Development Server\n";
echo "Purpose: Simulate real user interactions for demo day preparation\n";
echo "=============================================================================\n\n";

// Test 1: Database Connection Test
echo "TEST 1: DATABASE CONNECTION\n";
echo "----------------------------\n";
try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=creams', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Test basic query
    $stmt = $pdo->query("SELECT COUNT(*) as user_count FROM users");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "✅ Database connection successful\n";
    echo "✅ Found {$result['user_count']} users in database\n";
    
    // Test tables existence
    $tables = ['users', 'trainees', 'activities', 'centres', 'activity_sessions'];
    foreach ($tables as $table) {
        $stmt = $pdo->query("SELECT COUNT(*) FROM $table");
        $count = $stmt->fetchColumn();
        echo "✅ Table '$table': $count records\n";
    }
} catch (Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
}
echo "\n";

// Test 2: Authentication System
echo "TEST 2: AUTHENTICATION SYSTEM\n";
echo "------------------------------\n";
try {
    // Test user credentials
    $stmt = $pdo->prepare("SELECT id, name, email, role FROM users WHERE role = 'admin' LIMIT 1");
    $stmt->execute();
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($admin) {
        echo "✅ Admin user found: {$admin['name']} ({$admin['email']})\n";
        echo "✅ Role: {$admin['role']}\n";
    } else {
        echo "❌ No admin user found\n";
    }
    
    // Test different roles
    $roles = ['admin', 'supervisor', 'teacher', 'ajk'];
    foreach ($roles as $role) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE role = ?");
        $stmt->execute([$role]);
        $count = $stmt->fetchColumn();
        echo "✅ {$role} users: $count\n";
    }
} catch (Exception $e) {
    echo "❌ Authentication test failed: " . $e->getMessage() . "\n";
}
echo "\n";

// Test 3: Trainee Management System
echo "TEST 3: TRAINEE MANAGEMENT SYSTEM\n";
echo "----------------------------------\n";
try {
    // Test trainee records
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM trainees");
    $traineeCount = $stmt->fetchColumn();
    echo "✅ Total trainees: $traineeCount\n";
    
    // Test trainee ID format (should be disability-specific)
    $stmt = $pdo->query("SELECT trainee_id, trainee_condition FROM trainees WHERE trainee_id IS NOT NULL LIMIT 5");
    $trainees = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($trainees as $trainee) {
        $prefix = substr($trainee['trainee_id'], 0, 3);
        echo "✅ Trainee ID: {$trainee['trainee_id']} (Condition: {$trainee['trainee_condition']})\n";
    }
    
    // Test consent compliance
    $stmt = $pdo->query("SELECT COUNT(*) FROM trainees WHERE photo_consent = 1 AND services_consent = 1");
    $compliantCount = $stmt->fetchColumn();
    echo "✅ Trainees with full consent: $compliantCount/$traineeCount\n";
    
} catch (Exception $e) {
    echo "❌ Trainee management test failed: " . $e->getMessage() . "\n";
}
echo "\n";

// Test 4: Activity Management System
echo "TEST 4: ACTIVITY MANAGEMENT SYSTEM\n";
echo "-----------------------------------\n";
try {
    // Test activities
    $stmt = $pdo->query("SELECT COUNT(*) FROM activities");
    $activityCount = $stmt->fetchColumn();
    echo "✅ Total activities: $activityCount\n";
    
    // Test activity sessions
    $stmt = $pdo->query("SELECT COUNT(*) FROM activity_sessions");
    $sessionCount = $stmt->fetchColumn();
    echo "✅ Total activity sessions: $sessionCount\n";
    
    // Test enrollments
    $stmt = $pdo->query("SELECT COUNT(*) FROM activity_enrollments");
    $enrollmentCount = $stmt->fetchColumn();
    echo "✅ Total enrollments: $enrollmentCount\n";
    
    // Test enrollment status distribution
    $stmt = $pdo->query("SELECT enrollment_status, COUNT(*) as count FROM activity_enrollments GROUP BY enrollment_status");
    $statuses = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($statuses as $status) {
        echo "✅ {$status['enrollment_status']} enrollments: {$status['count']}\n";
    }
    
} catch (Exception $e) {
    echo "❌ Activity management test failed: " . $e->getMessage() . "\n";
}
echo "\n";

// Test 5: Attendance System
echo "TEST 5: ATTENDANCE SYSTEM\n";
echo "--------------------------\n";
try {
    // Test staff attendance
    $stmt = $pdo->query("SELECT COUNT(*) FROM staff_attendances");
    $staffAttendanceCount = $stmt->fetchColumn();
    echo "✅ Staff attendance records: $staffAttendanceCount\n";
    
    // Test trainee attendance
    $stmt = $pdo->query("SELECT COUNT(*) FROM trainee_attendances");
    $traineeAttendanceCount = $stmt->fetchColumn();
    echo "✅ Trainee attendance records: $traineeAttendanceCount\n";
    
    // Test attendance status distribution for trainees
    $stmt = $pdo->query("SELECT status, COUNT(*) as count FROM trainee_attendances GROUP BY status");
    $statuses = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($statuses as $status) {
        echo "✅ Trainee {$status['status']}: {$status['count']} records\n";
    }
    
} catch (Exception $e) {
    echo "❌ Attendance system test failed: " . $e->getMessage() . "\n";
}
echo "\n";

// Test 6: Asset Management System
echo "TEST 6: ASSET MANAGEMENT SYSTEM\n";
echo "--------------------------------\n";
try {
    // Test assets
    $stmt = $pdo->query("SELECT COUNT(*) FROM assets");
    $assetCount = $stmt->fetchColumn();
    echo "✅ Total assets: $assetCount\n";
    
    // Test asset categories
    $stmt = $pdo->query("SELECT COUNT(*) FROM asset_categories");
    $categoryCount = $stmt->fetchColumn();
    echo "✅ Asset categories: $categoryCount\n";
    
    // Test maintenance records
    $stmt = $pdo->query("SELECT COUNT(*) FROM asset_maintenances");
    $maintenanceCount = $stmt->fetchColumn();
    echo "✅ Maintenance records: $maintenanceCount\n";
    
} catch (Exception $e) {
    echo "❌ Asset management test failed: " . $e->getMessage() . "\n";
}
echo "\n";

// Test 7: Communication System
echo "TEST 7: COMMUNICATION SYSTEM\n";
echo "-----------------------------\n";
try {
    // Test notifications
    $stmt = $pdo->query("SELECT COUNT(*) FROM notifications");
    $notificationCount = $stmt->fetchColumn();
    echo "✅ Total notifications: $notificationCount\n";
    
    // Test messages
    $stmt = $pdo->query("SELECT COUNT(*) FROM messages");
    $messageCount = $stmt->fetchColumn();
    echo "✅ Internal messages: $messageCount\n";
    
    // Test contact messages
    $stmt = $pdo->query("SELECT COUNT(*) FROM contact_messages");
    $contactCount = $stmt->fetchColumn();
    echo "✅ Contact messages: $contactCount\n";
    
    // Test volunteers
    $stmt = $pdo->query("SELECT COUNT(*) FROM volunteers");
    $volunteerCount = $stmt->fetchColumn();
    echo "✅ Volunteer applications: $volunteerCount\n";
    
} catch (Exception $e) {
    echo "❌ Communication system test failed: " . $e->getMessage() . "\n";
}
echo "\n";

// Test 8: Letter Generation System
echo "TEST 8: LETTER GENERATION SYSTEM\n";
echo "---------------------------------\n";
try {
    // Test letter templates
    $stmt = $pdo->query("SELECT COUNT(*) FROM letter_templates");
    $templateCount = $stmt->fetchColumn();
    echo "✅ Letter templates: $templateCount\n";
    
    // Test generated letters
    $stmt = $pdo->query("SELECT COUNT(*) FROM letters");
    $letterCount = $stmt->fetchColumn();
    echo "✅ Generated letters: $letterCount\n";
    
} catch (Exception $e) {
    echo "❌ Letter generation test failed: " . $e->getMessage() . "\n";
}
echo "\n";

echo "=============================================================================\n";
echo "                         SYSTEM HEALTH SUMMARY\n";
echo "=============================================================================\n";

// Overall system health check
try {
    $healthChecks = [
        'Users' => $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn(),
        'Trainees' => $pdo->query("SELECT COUNT(*) FROM trainees")->fetchColumn(),
        'Activities' => $pdo->query("SELECT COUNT(*) FROM activities")->fetchColumn(),
        'Sessions' => $pdo->query("SELECT COUNT(*) FROM activity_sessions")->fetchColumn(),
        'Enrollments' => $pdo->query("SELECT COUNT(*) FROM activity_enrollments")->fetchColumn(),
        'Staff Attendance' => $pdo->query("SELECT COUNT(*) FROM staff_attendances")->fetchColumn(),
        'Trainee Attendance' => $pdo->query("SELECT COUNT(*) FROM trainee_attendances")->fetchColumn(),
        'Assets' => $pdo->query("SELECT COUNT(*) FROM assets")->fetchColumn(),
        'Notifications' => $pdo->query("SELECT COUNT(*) FROM notifications")->fetchColumn(),
    ];
    
    $allSystemsOperational = true;
    foreach ($healthChecks as $system => $count) {
        $status = $count > 0 ? "✅ OPERATIONAL" : "⚠️  EMPTY";
        echo sprintf("%-20s: %s (%d records)\n", $system, $status, $count);
        if ($count == 0 && in_array($system, ['Users', 'Trainees', 'Activities'])) {
            $allSystemsOperational = false;
        }
    }
    
    echo "\n";
    if ($allSystemsOperational) {
        echo "🎉 SYSTEM STATUS: READY FOR DEMO DAY! 🎉\n";
    } else {
        echo "⚠️  SYSTEM STATUS: REQUIRES ATTENTION\n";
    }
    
} catch (Exception $e) {
    echo "❌ Health check failed: " . $e->getMessage() . "\n";
}

echo "=============================================================================\n";
echo "Testing completed at: " . date('Y-m-d H:i:s') . "\n";
echo "=============================================================================\n";

// Get the output and save to file
$output = ob_get_clean();
echo $output;

// Also save to a log file
file_put_contents('testing_results_' . date('Y-m-d_H-i-s') . '.log', $output);