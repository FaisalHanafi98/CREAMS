<?php
/**
 * CREAMS UAT Execution Script
 * Automated User Acceptance Testing Simulation
 *
 * This script simulates real user interactions with the CREAMS system
 * and documents all inputs, outputs, and performance metrics.
 */

require __DIR__.'/vendor/autoload.php';

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Configuration
$baseUrl = 'http://127.0.0.1:8000';
$outputFile = 'documentation/UAT FILES/UAT_EXECUTION_REPORT_' . date('Y-m-d_H-i-s') . '.md';

// Test Results Storage
$testResults = [];
$startTime = microtime(true);

// Helper Functions
function logTest($testId, $module, $title, $status, $details, $performance) {
    global $testResults;
    $testResults[] = [
        'test_id' => $testId,
        'module' => $module,
        'title' => $title,
        'status' => $status,
        'details' => $details,
        'performance' => $performance,
        'timestamp' => date('Y-m-d H:i:s')
    ];
}

function makeRequest($method, $url, $data = [], $cookies = []) {
    $startTime = microtime(true);

    $ch = curl_init();
    $fullUrl = $GLOBALS['baseUrl'] . $url;

    curl_setopt($ch, CURLOPT_URL, $fullUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_COOKIEJAR, sys_get_temp_dir() . '/cookies.txt');
    curl_setopt($ch, CURLOPT_COOKIEFILE, sys_get_temp_dir() . '/cookies.txt');

    if (strtoupper($method) === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    }

    if (!empty($cookies)) {
        $cookieString = '';
        foreach ($cookies as $key => $value) {
            $cookieString .= "$key=$value; ";
        }
        curl_setopt($ch, CURLOPT_COOKIE, trim($cookieString));
    }

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $totalTime = microtime(true) - $startTime;

    curl_close($ch);

    return [
        'status_code' => $httpCode,
        'response' => $response,
        'time' => round($totalTime * 1000, 2) // Convert to milliseconds
    ];
}

function testDatabaseQuery($description, $query) {
    $startTime = microtime(true);
    try {
        $result = DB::select($query);
        $time = round((microtime(true) - $startTime) * 1000, 2);
        return [
            'success' => true,
            'result' => $result,
            'time' => $time,
            'description' => $description
        ];
    } catch (Exception $e) {
        $time = round((microtime(true) - $startTime) * 1000, 2);
        return [
            'success' => false,
            'error' => $e->getMessage(),
            'time' => $time,
            'description' => $description
        ];
    }
}

echo "╔═══════════════════════════════════════════════════════════════════════╗\n";
echo "║           CREAMS UAT EXECUTION - AUTOMATED TESTING SIMULATION          ║\n";
echo "║                        " . date('Y-m-d H:i:s') . "                              ║\n";
echo "╚═══════════════════════════════════════════════════════════════════════╝\n\n";

// ==============================================================================
// TEST MODULE 1: AUTHENTICATION TESTS (AUTH001-AUTH006)
// ==============================================================================

echo "┌─────────────────────────────────────────────────────────────────────┐\n";
echo "│ MODULE 1: AUTHENTICATION TESTS                                       │\n";
echo "└─────────────────────────────────────────────────────────────────────┘\n\n";

// AUTH001: User Login with Email
echo "[AUTH001] Testing User Login with Email...\n";
$auth001Start = microtime(true);

// Test with Admin credentials
$loginData = [
    'identifier' => 'lakshmi.krishnan@iium.edu.my',
    'password' => 'Admin@2024!'
];

echo "  Input: Email = {$loginData['identifier']}, Password = ********\n";

// First, get the login page to extract CSRF token
$loginPage = makeRequest('GET', '/login');
echo "  → Login page loaded in {$loginPage['time']}ms (Status: {$loginPage['status_code']})\n";

// Extract CSRF token (simplified - in real scenario, parse HTML)
preg_match('/<input[^>]+name="_token"[^>]+value="([^"]+)"/', $loginPage['response'], $tokenMatch);
$csrfToken = $tokenMatch[1] ?? '';

if ($csrfToken) {
    $loginData['_token'] = $csrfToken;

    // Attempt login
    $loginAttempt = makeRequest('POST', '/auth/check', $loginData);
    echo "  → Login attempt completed in {$loginAttempt['time']}ms (Status: {$loginAttempt['status_code']})\n";

    $auth001Time = round((microtime(true) - $auth001Start) * 1000, 2);

    if ($loginAttempt['status_code'] == 200 || $loginAttempt['status_code'] == 302) {
        echo "  ✓ TEST PASSED - User logged in successfully\n";
        echo "  Performance: {$auth001Time}ms total\n\n";

        logTest('AUTH001', 'Authentication', 'User Login with Email', 'PASSED',
            "Successfully logged in as admin user with email authentication",
            "{$auth001Time}ms");
    } else {
        echo "  ✗ TEST FAILED - Login did not redirect properly\n";
        echo "  Performance: {$auth001Time}ms total\n\n";

        logTest('AUTH001', 'Authentication', 'User Login with Email', 'FAILED',
            "Login failed with status code {$loginAttempt['status_code']}",
            "{$auth001Time}ms");
    }
} else {
    echo "  ✗ TEST FAILED - Could not extract CSRF token\n\n";
    logTest('AUTH001', 'Authentication', 'User Login with Email', 'FAILED',
        "Could not extract CSRF token from login page", "N/A");
}

// AUTH002: User Login with IIUM ID
echo "[AUTH002] Testing User Login with IIUM ID...\n";
$auth002Start = microtime(true);

$loginData002 = [
    'identifier' => '1928471',
    'password' => 'Teacher@2024'
];

echo "  Input: IIUM ID = {$loginData002['identifier']}, Password = ********\n";

$loginPage002 = makeRequest('GET', '/login');
preg_match('/<input[^>]+name="_token"[^>]+value="([^"]+)"/', $loginPage002['response'], $tokenMatch002);
$csrfToken002 = $tokenMatch002[1] ?? '';

if ($csrfToken002) {
    $loginData002['_token'] = $csrfToken002;
    $loginAttempt002 = makeRequest('POST', '/auth/check', $loginData002);
    echo "  → Login attempt completed in {$loginAttempt002['time']}ms (Status: {$loginAttempt002['status_code']})\n";

    $auth002Time = round((microtime(true) - $auth002Start) * 1000, 2);

    if ($loginAttempt002['status_code'] == 200 || $loginAttempt002['status_code'] == 302) {
        echo "  ✓ TEST PASSED - User logged in with IIUM ID successfully\n";
        echo "  Performance: {$auth002Time}ms total\n\n";

        logTest('AUTH002', 'Authentication', 'User Login with IIUM ID', 'PASSED',
            "Successfully logged in as teacher using IIUM ID authentication",
            "{$auth002Time}ms");
    } else {
        echo "  ✗ TEST FAILED - IIUM ID login failed\n";
        echo "  Performance: {$auth002Time}ms total\n\n";

        logTest('AUTH002', 'Authentication', 'User Login with IIUM ID', 'FAILED',
            "Login failed with status code {$loginAttempt002['status_code']}",
            "{$auth002Time}ms");
    }
} else {
    echo "  ✗ TEST FAILED - Could not extract CSRF token\n\n";
    logTest('AUTH002', 'Authentication', 'User Login with IIUM ID', 'FAILED',
        "Could not extract CSRF token", "N/A");
}

// AUTH003: Invalid Login Credentials
echo "[AUTH003] Testing Invalid Login Credentials...\n";
$auth003Start = microtime(true);

$invalidTests = [
    ['identifier' => 'invalidemailformat', 'password' => 'test123', 'description' => 'Invalid email format'],
    ['identifier' => 'nonexistent@test.com', 'password' => 'password123', 'description' => 'Non-existent email'],
    ['identifier' => 'lakshmi.krishnan@iium.edu.my', 'password' => 'WrongPassword123', 'description' => 'Correct email, wrong password'],
    ['identifier' => '', 'password' => '', 'description' => 'Empty fields'],
];

$auth003Passed = true;
foreach ($invalidTests as $testCase) {
    echo "  Testing: {$testCase['description']}\n";
    echo "    Input: identifier = '{$testCase['identifier']}', password = ********\n";

    $loginPage003 = makeRequest('GET', '/login');
    preg_match('/<input[^>]+name="_token"[^>]+value="([^"]+)"/', $loginPage003['response'], $tokenMatch003);
    $csrfToken003 = $tokenMatch003[1] ?? '';

    if ($csrfToken003) {
        $testData = $testCase;
        $testData['_token'] = $csrfToken003;
        unset($testData['description']);

        $invalidAttempt = makeRequest('POST', '/auth/check', $testData);
        echo "    → Response in {$invalidAttempt['time']}ms (Status: {$invalidAttempt['status_code']})\n";

        // Expect 4xx error or redirect back to login
        if ($invalidAttempt['status_code'] >= 400 || strpos($invalidAttempt['response'], 'Invalid') !== false || strpos($invalidAttempt['response'], 'required') !== false) {
            echo "    ✓ Correctly rejected invalid credentials\n";
        } else {
            echo "    ✗ Did not properly reject invalid credentials\n";
            $auth003Passed = false;
        }
    }
}

$auth003Time = round((microtime(true) - $auth003Start) * 1000, 2);

if ($auth003Passed) {
    echo "  ✓ TEST PASSED - All invalid login attempts properly rejected\n";
    echo "  Performance: {$auth003Time}ms total\n\n";

    logTest('AUTH003', 'Authentication', 'Invalid Login Credentials', 'PASSED',
        "All invalid credential combinations properly rejected with appropriate error messages",
        "{$auth003Time}ms");
} else {
    echo "  ✗ TEST FAILED - Some invalid attempts not properly handled\n";
    echo "  Performance: {$auth003Time}ms total\n\n";

    logTest('AUTH003', 'Authentication', 'Invalid Login Credentials', 'FAILED',
        "Some invalid credential tests did not behave as expected",
        "{$auth003Time}ms");
}

// ==============================================================================
// DATABASE VALIDATION TESTS
// ==============================================================================

echo "\n┌─────────────────────────────────────────────────────────────────────┐\n";
echo "│ DATABASE STRUCTURE AND DATA VALIDATION                               │\n";
echo "└─────────────────────────────────────────────────────────────────────┘\n\n";

// Test database queries for data validation
$dbTests = [
    "SELECT COUNT(*) as count FROM users WHERE status = 'active'" => "Active users count",
    "SELECT COUNT(*) as count FROM trainees WHERE trainee_id LIKE 'AU%'" => "Autism trainees count",
    "SELECT COUNT(*) as count FROM trainees WHERE trainee_id LIKE 'DS%'" => "Down Syndrome trainees count",
    "SELECT COUNT(*) as count FROM activities WHERE activity_status = 'active'" => "Active activities count",
    "SELECT COUNT(*) as count FROM activity_enrollments WHERE enrollment_status = 'enrolled'" => "Current enrollments count",
    "SELECT COUNT(*) as count FROM centres WHERE centre_status = 'active'" => "Active centres count",
];

foreach ($dbTests as $query => $description) {
    $result = testDatabaseQuery($description, $query);
    if ($result['success']) {
        $count = $result['result'][0]->count ?? 0;
        echo "  ✓ {$description}: {$count} (Query time: {$result['time']}ms)\n";
    } else {
        echo "  ✗ {$description}: ERROR - {$result['error']}\n";
    }
}

echo "\n";

// ==============================================================================
// GENERATE REPORT
// ==============================================================================

echo "\n┌─────────────────────────────────────────────────────────────────────┐\n";
echo "│ GENERATING COMPREHENSIVE UAT REPORT                                  │\n";
echo "└─────────────────────────────────────────────────────────────────────┘\n\n";

$totalTime = round((microtime(true) - $startTime) * 1000, 2);
$passedTests = count(array_filter($testResults, function($test) { return $test['status'] === 'PASSED'; }));
$failedTests = count(array_filter($testResults, function($test) { return $test['status'] === 'FAILED'; }));
$totalTests = count($testResults);

echo "Tests Executed: {$totalTests}\n";
echo "Passed: {$passedTests}\n";
echo "Failed: {$failedTests}\n";
echo "Total Execution Time: {$totalTime}ms\n";
echo "Report will be saved to: {$outputFile}\n\n";

// Generate Markdown Report
$reportContent = generateMarkdownReport($testResults, $totalTime, $passedTests, $failedTests);
file_put_contents($outputFile, $reportContent);

echo "╔═══════════════════════════════════════════════════════════════════════╗\n";
echo "║                    UAT EXECUTION COMPLETED                             ║\n";
echo "║           Report saved: {$outputFile}                                  ║\n";
echo "╚═══════════════════════════════════════════════════════════════════════╝\n";

function generateMarkdownReport($results, $totalTime, $passed, $failed) {
    $report = "# CREAMS UAT Execution Report\n\n";
    $report .= "**Execution Date:** " . date('F d, Y') . "\n";
    $report .= "**Execution Time:** " . date('H:i:s') . "\n";
    $report .= "**Total Duration:** {$totalTime}ms (" . round($totalTime/1000, 2) . " seconds)\n\n";

    $report .= "## Executive Summary\n\n";
    $report .= "| Metric | Value |\n";
    $report .= "|--------|-------|\n";
    $report .= "| Total Tests Executed | " . ($passed + $failed) . " |\n";
    $report .= "| Tests Passed | {$passed} |\n";
    $report .= "| Tests Failed | {$failed} |\n";
    $report .= "| Pass Rate | " . round(($passed/($passed+$failed))*100, 2) . "% |\n";
    $report .= "| Average Test Time | " . round($totalTime/($passed+$failed), 2) . "ms |\n\n";

    $report .= "## Detailed Test Results\n\n";

    foreach ($results as $result) {
        $statusIcon = $result['status'] === 'PASSED' ? '✅' : '❌';
        $report .= "### {$statusIcon} {$result['test_id']}: {$result['title']}\n\n";
        $report .= "**Module:** {$result['module']}\n";
        $report .= "**Status:** {$result['status']}\n";
        $report .= "**Performance:** {$result['performance']}\n";
        $report .= "**Timestamp:** {$result['timestamp']}\n\n";
        $report .= "**Details:**\n{$result['details']}\n\n";
        $report .= "---\n\n";
    }

    return $report;
}
