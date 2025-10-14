<?php
/**
 * CREAMS UAT TEST VERIFICATION SCRIPT
 * Systematically checks which test cases are usable/testable
 *
 * This script verifies:
 * 1. Route existence
 * 2. Controller existence
 * 3. View files existence
 * 4. Database tables existence
 * 5. Test data availability
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

$outputFile = "documentation/UAT FILES/UAT_TEST_USABILITY_REPORT_" . date('Y-m-d_H-i-s') . ".md";

echo "╔══════════════════════════════════════════════════════════════════════════╗\n";
echo "║             CREAMS UAT TEST USABILITY VERIFICATION                        ║\n";
echo "║                    " . date('Y-m-d H:i:s') . "                                   ║\n";
echo "╚══════════════════════════════════════════════════════════════════════════╝\n\n";

// Test definitions based on chronological order
$testDefinitions = [
    // PHASE 1: PUBLIC ACCESS
    'HOME001' => [
        'title' => 'Welcome/Landing Page',
        'route' => '/',
        'method' => 'GET',
        'view' => 'home.blade.php',
        'controller' => null,
        'auth_required' => false,
        'priority' => 'High'
    ],
    'HOME002' => [
        'title' => 'About Us Page',
        'route' => '/aboutus',
        'method' => 'GET',
        'view' => 'about.blade.php',
        'controller' => null,
        'auth_required' => false,
        'priority' => 'Medium'
    ],
    'CONTACT001' => [
        'title' => 'Contact Us Form',
        'route' => '/contact',
        'method' => 'GET',
        'view' => 'contactus.blade.php',
        'controller' => 'ContactController',
        'auth_required' => false,
        'priority' => 'High'
    ],
    'CONTACT002' => [
        'title' => 'Contact Form Submission',
        'route' => '/contact/submit',
        'method' => 'POST',
        'view' => null,
        'controller' => 'ContactController@submit',
        'auth_required' => false,
        'table' => 'contact_messages',
        'priority' => 'High'
    ],
    'VOL001' => [
        'title' => 'Volunteer Registration Page',
        'route' => '/volunteer',
        'method' => 'GET',
        'view' => 'volunteers/home.blade.php',
        'controller' => 'VolunteerController',
        'auth_required' => false,
        'priority' => 'High'
    ],
    'VOL002' => [
        'title' => 'Volunteer Form Submission',
        'route' => '/volunteer/submit',
        'method' => 'POST',
        'view' => null,
        'controller' => 'VolunteerController@submit',
        'auth_required' => false,
        'table' => 'volunteers',
        'priority' => 'High'
    ],

    // PHASE 2: AUTHENTICATION
    'AUTH001' => [
        'title' => 'Login Page',
        'route' => '/login',
        'method' => 'GET',
        'view' => 'auth/login.blade.php',
        'controller' => 'Auth\LoginController',
        'auth_required' => false,
        'priority' => 'Critical'
    ],
    'AUTH002' => [
        'title' => 'Login Submit (Email/IIUM ID)',
        'route' => '/auth/check',
        'method' => 'POST',
        'view' => null,
        'controller' => 'MainController@check',
        'auth_required' => false,
        'table' => 'users',
        'priority' => 'Critical'
    ],
    'AUTH003' => [
        'title' => 'Invalid Login Handling',
        'route' => '/auth/check',
        'method' => 'POST',
        'view' => null,
        'controller' => 'MainController@check',
        'auth_required' => false,
        'priority' => 'Critical'
    ],
    'AUTH004' => [
        'title' => 'Forgot Password Page',
        'route' => '/forgot-password',
        'method' => 'GET',
        'view' => 'auth/forgot-password.blade.php',
        'controller' => 'Auth\ForgotPasswordController',
        'auth_required' => false,
        'priority' => 'High'
    ],
    'AUTH005' => [
        'title' => 'Forgot Password Submit',
        'route' => '/forgot-password',
        'method' => 'POST',
        'view' => null,
        'controller' => 'Auth\ForgotPasswordController@submitForgotPasswordForm',
        'auth_required' => false,
        'table' => 'password_resets',
        'priority' => 'High'
    ],
    'AUTH006' => [
        'title' => 'Reset Password Page',
        'route' => '/reset-password/{token}',
        'method' => 'GET',
        'view' => 'auth/reset-password.blade.php',
        'controller' => 'Auth\ForgotPasswordController',
        'auth_required' => false,
        'priority' => 'High'
    ],
    'AUTH007' => [
        'title' => 'Reset Password Submit',
        'route' => '/reset-password',
        'method' => 'POST',
        'view' => null,
        'controller' => 'Auth\ForgotPasswordController@submitResetPasswordForm',
        'auth_required' => false,
        'priority' => 'High'
    ],
    'AUTH008' => [
        'title' => 'Session Management',
        'route' => '/auth/check-status',
        'method' => 'GET',
        'view' => null,
        'controller' => 'Auth\LoginController@checkAuth',
        'auth_required' => true,
        'priority' => 'High'
    ],
    'AUTH009' => [
        'title' => 'Logout',
        'route' => '/logout',
        'method' => 'GET',
        'view' => null,
        'controller' => 'MainController@logout',
        'auth_required' => true,
        'priority' => 'High'
    ],

    // PHASE 3: DASHBOARDS
    'DASH001' => [
        'title' => 'Admin Dashboard',
        'route' => '/admin/dashboard',
        'method' => 'GET',
        'view' => 'admin/dashboard.blade.php',
        'controller' => 'Dashboard\DashboardController',
        'auth_required' => true,
        'role' => 'admin',
        'priority' => 'Critical'
    ],
    'DASH002' => [
        'title' => 'Supervisor Dashboard',
        'route' => '/admin/dashboard',
        'method' => 'GET',
        'view' => 'admin/dashboard.blade.php',
        'controller' => 'Dashboard\DashboardController',
        'auth_required' => true,
        'role' => 'supervisor',
        'priority' => 'Critical'
    ],
    'DASH003' => [
        'title' => 'Teacher Dashboard',
        'route' => '/teachershome',
        'method' => 'GET',
        'view' => 'dashboard/teachershome.blade.php',
        'controller' => null,
        'auth_required' => true,
        'role' => 'teacher',
        'priority' => 'Critical'
    ],
    'DASH004' => [
        'title' => 'AJK Dashboard',
        'route' => '/admin/dashboard',
        'method' => 'GET',
        'view' => 'admin/dashboard.blade.php',
        'controller' => 'Dashboard\DashboardController',
        'auth_required' => true,
        'role' => 'ajk',
        'priority' => 'High'
    ],

    // PHASE 4: PROFILE
    'PROF001' => [
        'title' => 'View Profile',
        'route' => '/profile',
        'method' => 'GET',
        'view' => 'profile/*.blade.php',
        'controller' => 'Profile\UserProfileController@showProfile',
        'auth_required' => true,
        'priority' => 'High'
    ],
    'PROF002' => [
        'title' => 'Edit Profile',
        'route' => '/profile',
        'method' => 'GET',
        'view' => 'profile/*.blade.php',
        'controller' => 'Profile\UserProfileController',
        'auth_required' => true,
        'priority' => 'High'
    ],
    'PROF003' => [
        'title' => 'Change Password',
        'route' => '/profile/change-password',
        'method' => 'POST',
        'view' => null,
        'controller' => 'Profile\UserProfileController@changePassword',
        'auth_required' => true,
        'priority' => 'High'
    ],

    // PHASE 5: STAFF MODULE
    'USER001' => [
        'title' => 'Staff List',
        'route' => '/staffs/home',
        'method' => 'GET',
        'view' => 'staffs/*.blade.php',
        'controller' => 'Staff\StaffsHomeController',
        'auth_required' => true,
        'role' => 'admin',
        'table' => 'users',
        'priority' => 'Critical'
    ],

    // PHASE 6: TRAINEES
    'TRAIN001' => [
        'title' => 'Trainee List',
        'route' => '/trainees/home',
        'method' => 'GET',
        'view' => 'trainees/*.blade.php',
        'controller' => 'Trainee\TraineeHomeController',
        'auth_required' => true,
        'table' => 'trainees',
        'priority' => 'Critical'
    ],

    // PHASE 7: ACTIVITIES
    'ACT001' => [
        'title' => 'Activities List',
        'route' => '/activities',
        'method' => 'GET',
        'view' => 'activities/*.blade.php',
        'controller' => 'Activity\ActivityController',
        'auth_required' => true,
        'table' => 'activities',
        'priority' => 'Critical'
    ],
    'ACT002' => [
        'title' => 'Create Activity',
        'route' => '/activities/create',
        'method' => 'GET',
        'view' => 'activities/create.blade.php',
        'controller' => 'Activity\ActivityController@create',
        'auth_required' => true,
        'role' => 'admin',
        'priority' => 'Critical'
    ],
    'ACT003' => [
        'title' => 'Edit Activity',
        'route' => '/activities/{id}/edit',
        'method' => 'GET',
        'view' => 'activities/edit.blade.php',
        'controller' => 'Activity\ActivityController@edit',
        'auth_required' => true,
        'role' => 'admin',
        'priority' => 'Critical'
    ],
    'ACT006' => [
        'title' => 'Create Session',
        'route' => '/activities/{id}/sessions',
        'method' => 'POST',
        'view' => null,
        'controller' => 'Activity\ActivityController@createSession',
        'auth_required' => true,
        'table' => 'activity_sessions',
        'priority' => 'Critical'
    ],
    'ACT007' => [
        'title' => 'Enroll Trainee',
        'route' => '/activities/{id}/enroll',
        'method' => 'GET',
        'view' => 'activities/enroll.blade.php',
        'controller' => 'Activity\ActivityController@enrollmentForm',
        'auth_required' => true,
        'table' => 'activity_enrollments',
        'priority' => 'Critical'
    ],
    'ACT008' => [
        'title' => 'Activity Schedule',
        'route' => '/activities/schedule',
        'method' => 'GET',
        'view' => 'activities/schedule*.blade.php',
        'controller' => 'Activity\ActivityController@scheduleIndex',
        'auth_required' => true,
        'priority' => 'High'
    ],
    'ACT009' => [
        'title' => 'Weekly Schedule',
        'route' => '/activities/schedule/weekly',
        'method' => 'GET',
        'view' => 'activities/schedule*.blade.php',
        'controller' => 'Activity\ActivityController@weeklySchedule',
        'auth_required' => true,
        'priority' => 'Medium'
    ],
    'ACT010' => [
        'title' => 'Teacher Personal Schedule',
        'route' => '/activities/schedule/personal',
        'method' => 'GET',
        'view' => 'activities/schedule*.blade.php',
        'controller' => 'Activity\ActivityController@personalSchedule',
        'auth_required' => true,
        'role' => 'teacher',
        'priority' => 'High'
    ],

    // PHASE 8: ATTENDANCE
    'ATT001' => [
        'title' => 'Mark Attendance',
        'route' => '/activity-attendance',
        'method' => 'GET',
        'view' => 'attendance/*.blade.php',
        'controller' => 'Activity\AttendanceController',
        'auth_required' => true,
        'role' => 'teacher',
        'priority' => 'Critical'
    ],
    'ATT002' => [
        'title' => 'View Attendance Records',
        'route' => '/activity-attendance',
        'method' => 'GET',
        'view' => 'attendance/*.blade.php',
        'controller' => 'Activity\AttendanceController',
        'auth_required' => true,
        'priority' => 'High'
    ],

    // PHASE 9: CENTRES
    'CENT001' => [
        'title' => 'Centre List',
        'route' => '/admin/centres',
        'method' => 'GET',
        'view' => 'admin/centres*.blade.php',
        'controller' => 'Centre\CentreController',
        'auth_required' => true,
        'role' => 'admin',
        'table' => 'centres',
        'priority' => 'High'
    ],

    // PHASE 10: ASSETS
    'ASSET001' => [
        'title' => 'Asset List',
        'route' => '/admin/assets',
        'method' => 'GET',
        'view' => 'admin/assets*.blade.php',
        'controller' => 'Centre\AssetController',
        'auth_required' => true,
        'table' => 'assets',
        'priority' => 'High'
    ],
    'ASSET002' => [
        'title' => 'Asset Inventory',
        'route' => '/admin/assets',
        'method' => 'GET',
        'view' => 'admin/assets*.blade.php',
        'controller' => 'Centre\AssetController',
        'auth_required' => true,
        'priority' => 'High'
    ],

    // PHASE 11: LETTERS
    'LETT001' => [
        'title' => 'Letters Home',
        'route' => '/letters/index',
        'method' => 'GET',
        'view' => 'profile/letters*.blade.php',
        'controller' => 'Profile\LetterTemplateController',
        'auth_required' => true,
        'table' => 'letters',
        'priority' => 'High'
    ],
    'LETT002' => [
        'title' => 'Letter Templates',
        'route' => '/admin/admin/letter-templates',
        'method' => 'GET',
        'view' => 'admin/letter-templates*.blade.php',
        'controller' => 'Profile\LetterTemplateController',
        'auth_required' => true,
        'role' => 'admin',
        'table' => 'letter_templates',
        'priority' => 'Medium'
    ],
];

// Verification functions
function checkRoute($route, $method = 'GET') {
    $routes = Route::getRoutes();
    foreach ($routes as $r) {
        if ($r->uri() === ltrim($route, '/') && in_array($method, $r->methods())) {
            return true;
        }
    }
    return false;
}

function checkView($viewPath) {
    if (!$viewPath) return null;
    $viewsPath = resource_path('views');

    // Handle wildcards
    if (strpos($viewPath, '*') !== false) {
        $pattern = str_replace('*', '', $viewPath);
        $dir = dirname($viewsPath . '/' . $pattern);
        if (is_dir($dir)) {
            $files = glob($dir . '/*.blade.php');
            return count($files) > 0;
        }
        return false;
    }

    return file_exists($viewsPath . '/' . $viewPath);
}

function checkTable($tableName) {
    if (!$tableName) return null;
    return Schema::hasTable($tableName);
}

function checkController($controllerName) {
    if (!$controllerName) return null;
    $parts = explode('@', $controllerName);
    $className = 'App\\Http\\Controllers\\' . $parts[0];
    return class_exists($className);
}

// Run verification
$results = [];
$totalTests = count($testDefinitions);
$usable = 0;
$partiallyUsable = 0;
$notUsable = 0;

echo "Verifying " . $totalTests . " test cases...\n\n";

foreach ($testDefinitions as $testId => $test) {
    echo "[{$testId}] {$test['title']}... ";

    $routeExists = checkRoute($test['route'], $test['method']);
    $viewExists = checkView($test['view'] ?? null);
    $tableExists = checkTable($test['table'] ?? null);
    $controllerExists = checkController($test['controller'] ?? null);

    // Determine usability
    $score = 0;
    if ($routeExists) $score++;
    if ($viewExists !== false) $score++;
    if ($tableExists !== false) $score++;
    if ($controllerExists !== false) $score++;

    $maxScore = 4;
    $percentage = ($score / $maxScore) * 100;

    if ($percentage >= 75) {
        $status = 'USABLE';
        $usable++;
        echo "✓ USABLE\n";
    } elseif ($percentage >= 50) {
        $status = 'PARTIAL';
        $partiallyUsable++;
        echo "⚠ PARTIAL\n";
    } else {
        $status = 'NOT_USABLE';
        $notUsable++;
        echo "✗ NOT USABLE\n";
    }

    $results[$testId] = [
        'title' => $test['title'],
        'priority' => $test['priority'],
        'status' => $status,
        'score' => $score,
        'max_score' => $maxScore,
        'percentage' => $percentage,
        'route_exists' => $routeExists,
        'view_exists' => $viewExists,
        'table_exists' => $tableExists,
        'controller_exists' => $controllerExists,
        'auth_required' => $test['auth_required'],
        'role' => $test['role'] ?? 'any',
        'route' => $test['route'],
        'method' => $test['method']
    ];
}

echo "\n";
echo "════════════════════════════════════════════════════════════════════════\n";
echo "SUMMARY:\n";
echo "  Total Tests: {$totalTests}\n";
echo "  ✓ Usable: {$usable} (" . round(($usable/$totalTests)*100) . "%)\n";
echo "  ⚠ Partially Usable: {$partiallyUsable} (" . round(($partiallyUsable/$totalTests)*100) . "%)\n";
echo "  ✗ Not Usable: {$notUsable} (" . round(($notUsable/$totalTests)*100) . "%)\n";
echo "════════════════════════════════════════════════════════════════════════\n\n";

// Generate detailed report
generateDetailedReport($results, $outputFile);

echo "✓ Detailed report saved to: {$outputFile}\n\n";

function generateDetailedReport($results, $outputFile) {
    $report = "# CREAMS UAT TEST USABILITY REPORT\n\n";
    $report .= "**Generated:** " . date('F d, Y \a\t H:i:s') . "\n";
    $report .= "**Total Tests Analyzed:** " . count($results) . "\n\n";

    $report .= "---\n\n";

    $report .= "## 📊 EXECUTIVE SUMMARY\n\n";

    $usable = count(array_filter($results, fn($r) => $r['status'] === 'USABLE'));
    $partial = count(array_filter($results, fn($r) => $r['status'] === 'PARTIAL'));
    $notUsable = count(array_filter($results, fn($r) => $r['status'] === 'NOT_USABLE'));
    $total = count($results);

    $report .= "| Status | Count | Percentage |\n";
    $report .= "|--------|-------|------------|\n";
    $report .= "| ✅ Fully Usable | {$usable} | " . round(($usable/$total)*100, 1) . "% |\n";
    $report .= "| ⚠️ Partially Usable | {$partial} | " . round(($partial/$total)*100, 1) . "% |\n";
    $report .= "| ❌ Not Usable | {$notUsable} | " . round(($notUsable/$total)*100, 1) . "% |\n\n";

    // Group by status
    $byStatus = [
        'USABLE' => [],
        'PARTIAL' => [],
        'NOT_USABLE' => []
    ];

    foreach ($results as $testId => $result) {
        $byStatus[$result['status']][$testId] = $result;
    }

    // Usable tests
    $report .= "## ✅ FULLY USABLE TESTS ({$usable} tests)\n\n";
    $report .= "*These tests can be executed immediately*\n\n";

    foreach ($byStatus['USABLE'] as $testId => $result) {
        $report .= "### {$testId}: {$result['title']}\n";
        $report .= "**Priority:** {$result['priority']} | **Auth:** " . ($result['auth_required'] ? "Required ({$result['role']})" : "Public") . "\n\n";
        $report .= "**Route:** `{$result['method']} {$result['route']}`\n\n";
        $report .= "**Verification:**\n";
        $report .= "- ✅ Route exists\n";
        $report .= "- " . ($result['view_exists'] ? "✅" : "⚠️") . " View " . ($result['view_exists'] ? "exists" : "not checked") . "\n";
        $report .= "- " . ($result['table_exists'] ? "✅" : "⚠️") . " Table " . ($result['table_exists'] ? "exists" : "not checked") . "\n";
        $report .= "- " . ($result['controller_exists'] ? "✅" : "⚠️") . " Controller " . ($result['controller_exists'] ? "exists" : "not checked") . "\n\n";
        $report .= "---\n\n";
    }

    // Partially usable
    $report .= "## ⚠️ PARTIALLY USABLE TESTS ({$partial} tests)\n\n";
    $report .= "*These tests have some components missing but can be adapted*\n\n";

    foreach ($byStatus['PARTIAL'] as $testId => $result) {
        $report .= "### {$testId}: {$result['title']}\n";
        $report .= "**Priority:** {$result['priority']} | **Usability:** {$result['percentage']}%\n\n";
        $report .= "**Issues:**\n";
        if (!$result['route_exists']) $report .= "- ❌ Route does not exist\n";
        if ($result['view_exists'] === false) $report .= "- ❌ View file not found\n";
        if ($result['table_exists'] === false) $report .= "- ❌ Database table missing\n";
        if ($result['controller_exists'] === false) $report .= "- ❌ Controller not found\n";
        $report .= "\n**Recommendation:** " . getRecommendation($result) . "\n\n";
        $report .= "---\n\n";
    }

    // Not usable
    $report .= "## ❌ NOT USABLE TESTS ({$notUsable} tests)\n\n";
    $report .= "*These tests cannot be executed without significant development*\n\n";

    foreach ($byStatus['NOT_USABLE'] as $testId => $result) {
        $report .= "### {$testId}: {$result['title']}\n";
        $report .= "**Priority:** {$result['priority']}\n\n";
        $report .= "**Missing Components:**\n";
        if (!$result['route_exists']) $report .= "- ❌ Route\n";
        if ($result['view_exists'] === false) $report .= "- ❌ View\n";
        if ($result['table_exists'] === false) $report .= "- ❌ Table\n";
        if ($result['controller_exists'] === false) $report .= "- ❌ Controller\n";
        $report .= "\n**Recommendation:** Skip this test or implement missing functionality\n\n";
        $report .= "---\n\n";
    }

    // Priority analysis
    $report .= "## 📋 PRIORITY ANALYSIS\n\n";
    $byPriority = [];
    foreach ($results as $testId => $result) {
        $priority = $result['priority'];
        if (!isset($byPriority[$priority])) {
            $byPriority[$priority] = ['usable' => 0, 'partial' => 0, 'not_usable' => 0];
        }
        if ($result['status'] === 'USABLE') $byPriority[$priority]['usable']++;
        elseif ($result['status'] === 'PARTIAL') $byPriority[$priority]['partial']++;
        else $byPriority[$priority]['not_usable']++;
    }

    $report .= "| Priority | Usable | Partial | Not Usable |\n";
    $report .= "|----------|--------|---------|------------|\n";
    foreach ($byPriority as $priority => $counts) {
        $report .= "| {$priority} | {$counts['usable']} | {$counts['partial']} | {$counts['not_usable']} |\n";
    }

    $report .= "\n---\n\n";
    $report .= "*Report generated by CREAMS UAT Verification Script*\n";

    file_put_contents($outputFile, $report);
}

function getRecommendation($result) {
    $issues = [];
    if (!$result['route_exists']) $issues[] = "route";
    if ($result['view_exists'] === false) $issues[] = "view";
    if ($result['table_exists'] === false) $issues[] = "table";
    if ($result['controller_exists'] === false) $issues[] = "controller";

    if (count($issues) === 1) {
        return "Create missing " . $issues[0] . " to make test fully usable";
    }

    return "Implement missing components: " . implode(', ', $issues);
}
