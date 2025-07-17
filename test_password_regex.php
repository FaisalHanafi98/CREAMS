<?php
require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== TESTING PASSWORD REGEX PATTERNS ===" . PHP_EOL;

use Illuminate\Support\Facades\Validator;

$passwordTests = [
    'password123' => false,    // No uppercase, no special char
    'Password123' => false,    // No special char
    'Password@' => false,      // Too short, no number
    'Password123!' => true,    // Should pass all requirements
    'MyPass123@' => true,      // Should pass all requirements
    'weakpass' => false,       // No uppercase, number, special char
    'STRONGPASS123!' => true,  // Should pass all requirements
    'Complex1!' => true,       // Should pass all requirements
    'Aa1@bcde' => true,        // Should pass all requirements
];

// Current regex (too restrictive)
$currentRegex = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/';

// Fixed regex (more flexible with special characters)
$fixedRegex = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^\w\s])/';

echo "Testing Current Regex: {$currentRegex}" . PHP_EOL;
foreach ($passwordTests as $password => $shouldPass) {
    $validator = Validator::make(['password' => $password], [
        'password' => [
            'required',
            'min:8',
            'regex:' . $currentRegex
        ]
    ]);
    
    $passed = !$validator->fails();
    $status = ($passed === $shouldPass) ? '✓' : '❌';
    echo "   {$status} '{$password}': " . ($passed ? 'PASS' : 'FAIL') . " (expected: " . ($shouldPass ? 'PASS' : 'FAIL') . ")" . PHP_EOL;
}

echo PHP_EOL . "Testing Fixed Regex: {$fixedRegex}" . PHP_EOL;
foreach ($passwordTests as $password => $shouldPass) {
    $validator = Validator::make(['password' => $password], [
        'password' => [
            'required',
            'min:8',
            'regex:' . $fixedRegex
        ]
    ]);
    
    $passed = !$validator->fails();
    $status = ($passed === $shouldPass) ? '✓' : '❌';
    echo "   {$status} '{$password}': " . ($passed ? 'PASS' : 'FAIL') . " (expected: " . ($shouldPass ? 'PASS' : 'FAIL') . ")" . PHP_EOL;
}

echo PHP_EOL . "=== PASSWORD REGEX TEST COMPLETED ===" . PHP_EOL;