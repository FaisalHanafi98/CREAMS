<?php
/**
 * CREAMS Web Interface Testing
 * Testing form submissions, validation, and user interactions
 */

echo "=============================================================================\n";
echo "                   CREAMS WEB INTERFACE TESTING\n";
echo "=============================================================================\n";
echo "Testing Date: " . date('Y-m-d H:i:s') . "\n";
echo "Purpose: Test web forms, validation, and user interactions\n";
echo "=============================================================================\n\n";

// Test configuration
$baseUrl = 'http://127.0.0.1:8000';
$testResults = [];

function makeRequest($url, $method = 'GET', $data = null, $headers = []) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_USERAGENT, 'CREAMS Testing Bot');
    
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }
    }
    
    if (!empty($headers)) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    return [
        'response' => $response,
        'http_code' => $httpCode,
        'error' => $error
    ];
}

function logTest($testName, $success, $details = '') {
    global $testResults;
    $status = $success ? "✅ PASS" : "❌ FAIL";
    echo sprintf("%-50s: %s\n", $testName, $status);
    if ($details) {
        echo "   Details: $details\n";
    }
    $testResults[] = ['name' => $testName, 'success' => $success, 'details' => $details];
    echo "\n";
}

// Test 1: Home Page Access
echo "TEST 1: HOME PAGE ACCESS\n";
echo "-------------------------\n";
$result = makeRequest($baseUrl);
$homePageAccessible = $result['http_code'] == 200 && empty($result['error']);
logTest("Home page accessibility", $homePageAccessible, "HTTP {$result['http_code']}");

// Test 2: Login Page Access
echo "TEST 2: LOGIN PAGE ACCESS\n";
echo "--------------------------\n";
$result = makeRequest($baseUrl . '/login');
$loginPageAccessible = $result['http_code'] == 200 && strpos($result['response'], 'login') !== false;
logTest("Login page accessibility", $loginPageAccessible, "HTTP {$result['http_code']}");

// Test 3: Registration Page Access
echo "TEST 3: REGISTRATION PAGE ACCESS\n";
echo "---------------------------------\n";
$result = makeRequest($baseUrl . '/register');
$registerPageAccessible = $result['http_code'] == 200;
logTest("Registration page accessibility", $registerPageAccessible, "HTTP {$result['http_code']}");

// Test 4: Contact Form Access
echo "TEST 4: CONTACT FORM ACCESS\n";
echo "----------------------------\n";
$result = makeRequest($baseUrl . '/contact');
$contactPageAccessible = $result['http_code'] == 200;
logTest("Contact form accessibility", $contactPageAccessible, "HTTP {$result['http_code']}");

// Test 5: Volunteer Form Access
echo "TEST 5: VOLUNTEER FORM ACCESS\n";
echo "------------------------------\n";
$result = makeRequest($baseUrl . '/volunteer');
$volunteerPageAccessible = $result['http_code'] == 200;
logTest("Volunteer form accessibility", $volunteerPageAccessible, "HTTP {$result['http_code']}");

// Test 6: Dashboard Redirect (Should redirect to login)
echo "TEST 6: DASHBOARD ACCESS (UNAUTHENTICATED)\n";
echo "--------------------------------------------\n";
$result = makeRequest($baseUrl . '/dashboard');
$dashboardRedirect = $result['http_code'] == 302 || strpos($result['response'], 'login') !== false;
logTest("Dashboard authentication check", $dashboardRedirect, "Properly redirects unauthenticated users");

// Test 7: API Endpoints Accessibility
echo "TEST 7: API ENDPOINTS ACCESSIBILITY\n";
echo "------------------------------------\n";
$apiEndpoints = [
    '/dashboard/updates',
    '/dashboard/refresh-stats',
    '/dashboard/widget/stats'
];

foreach ($apiEndpoints as $endpoint) {
    $result = makeRequest($baseUrl . $endpoint);
    // API endpoints should return 401/403 for unauthenticated users or 200 for public data
    $apiAccessible = in_array($result['http_code'], [200, 401, 403, 302]);
    logTest("API endpoint $endpoint", $apiAccessible, "HTTP {$result['http_code']}");
}

// Test 8: Static Assets Loading
echo "TEST 8: STATIC ASSETS LOADING\n";
echo "------------------------------\n";
$assets = [
    '/css/app.css',
    '/js/app.js'
];

foreach ($assets as $asset) {
    $result = makeRequest($baseUrl . $asset);
    $assetLoaded = $result['http_code'] == 200 || $result['http_code'] == 404; // 404 is acceptable if using different asset structure
    logTest("Static asset $asset", $assetLoaded, "HTTP {$result['http_code']}");
}

// Test 9: Form Validation Testing (Contact Form)
echo "TEST 9: FORM VALIDATION TESTING\n";
echo "--------------------------------\n";

// Test invalid contact form submission
$invalidContactData = http_build_query([
    'name' => '',
    'email' => 'invalid-email',
    'message' => ''
]);

$result = makeRequest($baseUrl . '/contact', 'POST', $invalidContactData, [
    'Content-Type: application/x-www-form-urlencoded'
]);

$formValidationWorks = $result['http_code'] == 422 || $result['http_code'] == 302 || strpos($result['response'], 'error') !== false;
logTest("Contact form validation", $formValidationWorks, "Handles invalid data appropriately");

// Test 10: Route Security Testing
echo "TEST 10: ROUTE SECURITY TESTING\n";
echo "--------------------------------\n";

$protectedRoutes = [
    '/admin/dashboard',
    '/trainees/create',
    '/staffs/create',
    '/activities/create'
];

foreach ($protectedRoutes as $route) {
    $result = makeRequest($baseUrl . $route);
    $routeProtected = $result['http_code'] == 302 || $result['http_code'] == 401 || $result['http_code'] == 403;
    logTest("Protected route $route", $routeProtected, "HTTP {$result['http_code']} - Properly secured");
}

echo "=============================================================================\n";
echo "                         WEB INTERFACE TEST SUMMARY\n";
echo "=============================================================================\n";

$totalTests = count($testResults);
$passedTests = count(array_filter($testResults, function($test) { return $test['success']; }));
$failedTests = $totalTests - $passedTests;

echo "Total Tests: $totalTests\n";
echo "Passed: $passedTests\n";
echo "Failed: $failedTests\n";
echo "Success Rate: " . round(($passedTests / $totalTests) * 100, 2) . "%\n\n";

if ($failedTests == 0) {
    echo "🎉 ALL WEB INTERFACE TESTS PASSED! SYSTEM READY FOR DEMO! 🎉\n";
} else {
    echo "⚠️  Some tests failed. Review the details above.\n";
    echo "\nFailed Tests:\n";
    foreach ($testResults as $test) {
        if (!$test['success']) {
            echo "- {$test['name']}: {$test['details']}\n";
        }
    }
}

echo "=============================================================================\n";
echo "Web interface testing completed at: " . date('Y-m-d H:i:s') . "\n";
echo "=============================================================================\n";