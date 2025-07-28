<?php
require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== TESTING STAFF MODULE ISSUES ===" . PHP_EOL;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

try {
    echo "1. Testing bio/about field issue..." . PHP_EOL;
    
    // Create test user with about field
    $testUser = User::create([
        'iium_id' => 'TEST001',
        'name' => 'Test User',
        'email' => 'test@test.com',
        'password' => Hash::make('password123'),
        'about' => 'This is test about content',
        'role' => 'teacher',
        'status' => 'active',
        'centre_id' => '01'
    ]);
    echo "   ✓ Created test user with 'about' field: " . $testUser->about . PHP_EOL;
    
    // Try to update with bio field (this should fail)
    try {
        $testUser->update(['bio' => 'Updated bio content']);
        echo "   ❌ ERROR: User model accepted 'bio' field (should reject it)" . PHP_EOL;
    } catch (Exception $e) {
        echo "   ✓ User model correctly rejects 'bio' field" . PHP_EOL;
    }
    
    // Update with correct about field
    $testUser->update(['about' => 'Updated about content']);
    echo "   ✓ User model accepts 'about' field: " . $testUser->fresh()->about . PHP_EOL;
    
    echo PHP_EOL . "2. Testing password validation regex..." . PHP_EOL;
    
    $passwordTests = [
        'password123' => false,    // No uppercase, no special char
        'Password123' => false,    // No special char
        'Password@' => false,      // Too short, no number
        'Password123!' => true,    // Should pass all requirements
        'MyPass123@' => true,      // Should pass all requirements
        'weakpass' => false,       // No uppercase, number, special char
        'STRONGPASS123!' => true   // Should pass all requirements
    ];
    
    foreach ($passwordTests as $password => $shouldPass) {
        $validator = Validator::make(['password' => $password], [
            'password' => [
                'required',
                'min:8',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/'
            ]
        ]);
        
        $passed = !$validator->fails();
        $status = ($passed === $shouldPass) ? '✓' : '❌';
        echo "   {$status} Password '{$password}': " . ($passed ? 'PASS' : 'FAIL') . " (expected: " . ($shouldPass ? 'PASS' : 'FAIL') . ")" . PHP_EOL;
        
        if ($passed !== $shouldPass) {
            echo "      Validation errors: " . implode(', ', $validator->errors()->get('password')) . PHP_EOL;
        }
    }
    
    echo PHP_EOL . "3. Cleanup test data..." . PHP_EOL;
    $testUser->delete();
    echo "   ✓ Test user deleted" . PHP_EOL;
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . PHP_EOL;
    echo "Stack trace: " . $e->getTraceAsString() . PHP_EOL;
}

echo PHP_EOL . "=== STAFF MODULE ISSUES TEST COMPLETED ===" . PHP_EOL;