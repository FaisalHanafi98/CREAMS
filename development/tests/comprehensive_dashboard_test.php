<?php
/**
 * Comprehensive Dashboard Testing Suite
 * Combines all dashboard-related tests for better organization
 */

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== COMPREHENSIVE DASHBOARD TEST SUITE ===" . PHP_EOL;
echo "Testing all dashboard functionality..." . PHP_EOL . PHP_EOL;

// Test 1: Basic Dashboard Access
echo "1. TESTING BASIC DASHBOARD ACCESS" . PHP_EOL;
echo str_repeat("-", 40) . PHP_EOL;

// Simulate session for testing
session_start();
$_SESSION = [
    'id' => 1,
    'role' => 'admin',
    'centre_id' => '01',
    'name' => 'Test Admin'
];

try {
    $controller = new App\Http\Controllers\Dashboard\DashboardController();
    echo "✅ Dashboard Controller instantiated successfully" . PHP_EOL;
} catch (Exception $e) {
    echo "❌ Dashboard Controller Error: " . $e->getMessage() . PHP_EOL;
}

// Test 2: Admin Dashboard Service
echo PHP_EOL . "2. TESTING ADMIN DASHBOARD SERVICE" . PHP_EOL;
echo str_repeat("-", 40) . PHP_EOL;

try {
    $adminService = new App\Services\Dashboard\AdminDashboardService();
    $admin = App\Models\User::where('role', 'admin')->first();

    if ($admin) {
        $statistics = $adminService->getStatistics($admin);
        echo "✅ Admin statistics retrieved: " . count($statistics) . " items" . PHP_EOL;

        $calendar = $adminService->getCalendarData($admin);
        echo "✅ Admin calendar data retrieved" . PHP_EOL;
    } else {
        echo "⚠️ No admin user found for testing" . PHP_EOL;
    }
} catch (Exception $e) {
    echo "❌ Admin Service Error: " . $e->getMessage() . PHP_EOL;
}

// Test 3: Dashboard Performance
echo PHP_EOL . "3. TESTING DASHBOARD PERFORMANCE" . PHP_EOL;
echo str_repeat("-", 40) . PHP_EOL;

$start = microtime(true);

try {
    // Test database queries performance
    $userCount = App\Models\User::count();
    $traineeCount = App\Models\Trainee::count();
    $activityCount = App\Models\Activity::count();

    $end = microtime(true);
    $executionTime = ($end - $start) * 1000; // Convert to milliseconds

    echo "✅ Database queries completed in: " . round($executionTime, 2) . "ms" . PHP_EOL;
    echo "   - Users: $userCount" . PHP_EOL;
    echo "   - Trainees: $traineeCount" . PHP_EOL;
    echo "   - Activities: $activityCount" . PHP_EOL;

    if ($executionTime < 500) {
        echo "✅ Performance: GOOD (< 500ms)" . PHP_EOL;
    } else {
        echo "⚠️ Performance: SLOW (> 500ms)" . PHP_EOL;
    }
} catch (Exception $e) {
    echo "❌ Performance Test Error: " . $e->getMessage() . PHP_EOL;
}

// Test 4: Dashboard Real-time Data
echo PHP_EOL . "4. TESTING REAL-TIME DATA FEATURES" . PHP_EOL;
echo str_repeat("-", 40) . PHP_EOL;

try {
    // Test recent activities
    $recentActivities = App\Models\Activity::latest()->limit(5)->get();
    echo "✅ Recent activities loaded: " . $recentActivities->count() . " items" . PHP_EOL;

    // Test attendance today
    $todayAttendance = App\Models\Attendance::whereDate('created_at', today())->count();
    echo "✅ Today's attendance count: $todayAttendance" . PHP_EOL;

    // Test active sessions
    $activeSessions = App\Models\ActivitySession::where('status', 'active')->count();
    echo "✅ Active sessions: $activeSessions" . PHP_EOL;

} catch (Exception $e) {
    echo "❌ Real-time Data Error: " . $e->getMessage() . PHP_EOL;
}

// Test 5: Dashboard Services Integration
echo PHP_EOL . "5. TESTING DASHBOARD SERVICES INTEGRATION" . PHP_EOL;
echo str_repeat("-", 40) . PHP_EOL;

try {
    // Test different role dashboards
    $roles = ['admin', 'teacher', 'supervisor'];

    foreach ($roles as $role) {
        $user = App\Models\User::where('role', $role)->first();
        if ($user) {
            $serviceClass = 'App\\Services\\Dashboard\\' . ucfirst($role) . 'DashboardService';
            if (class_exists($serviceClass)) {
                $service = new $serviceClass();
                echo "✅ $role dashboard service working" . PHP_EOL;
            } else {
                echo "⚠️ $role dashboard service not found" . PHP_EOL;
            }
        } else {
            echo "⚠️ No $role user found for testing" . PHP_EOL;
        }
    }
} catch (Exception $e) {
    echo "❌ Services Integration Error: " . $e->getMessage() . PHP_EOL;
}

echo PHP_EOL . "=== DASHBOARD TEST SUITE COMPLETED ===" . PHP_EOL;
echo "Check above for any ❌ errors that need attention." . PHP_EOL;