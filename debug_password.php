<?php
require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== DEBUGGING STRONGPASS123! ===" . PHP_EOL;

$password = 'STRONGPASS123!';
echo "Password: {$password}" . PHP_EOL;
echo "Length: " . strlen($password) . PHP_EOL;

// Check each requirement individually
echo "Has lowercase: " . (preg_match('/[a-z]/', $password) ? 'YES' : 'NO') . PHP_EOL;
echo "Has uppercase: " . (preg_match('/[A-Z]/', $password) ? 'YES' : 'NO') . PHP_EOL;
echo "Has digit: " . (preg_match('/\d/', $password) ? 'YES' : 'NO') . PHP_EOL;
echo "Has special char [@$!%*?&]: " . (preg_match('/[@$!%*?&]/', $password) ? 'YES' : 'NO') . PHP_EOL;
echo "Has special char [^\w\s]: " . (preg_match('/[^\w\s]/', $password) ? 'YES' : 'NO') . PHP_EOL;

// Test the full regex
$currentRegex = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/';
echo "Current regex matches: " . (preg_match($currentRegex, $password) ? 'YES' : 'NO') . PHP_EOL;

// Try simpler approach
$betterRegex = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*()_+\-=\[\]{};\':"\\|,.<>\?])/';
echo "Better regex matches: " . (preg_match($betterRegex, $password) ? 'YES' : 'NO') . PHP_EOL;

use Illuminate\Support\Facades\Validator;

echo PHP_EOL . "Testing with Validator:" . PHP_EOL;
$validator = Validator::make(['password' => $password], [
    'password' => [
        'required',
        'min:8',
        'regex:' . $betterRegex
    ]
]);

if ($validator->fails()) {
    echo "Validation errors: " . implode(', ', $validator->errors()->get('password')) . PHP_EOL;
} else {
    echo "Validation passed!" . PHP_EOL;
}