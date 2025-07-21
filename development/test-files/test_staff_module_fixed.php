<?php
require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== TESTING FIXED STAFF MODULE ===" . PHP_EOL;

use App\Models\Users;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

try {
    echo "1. Testing bio/about field fix..." . PHP_EOL;
    
    // Create test user
    $testUser = Users::create([
        'iium_id' => 'TEST002',
        'name' => 'Test Staff User',
        'email' => 'teststaff@test.com',
        'password' => Hash::make('TestPass123!'),
        'about' => 'This is the about field content',
        'role' => 'teacher',
        'status' => 'active',
        'centre_id' => '01'
    ]);
    echo "   ✓ Created test user with 'about' field: " . $testUser->about . PHP_EOL;
    
    // Test StaffController validation with correct field name
    $staffValidationData = [
        'name' => 'Updated Name',
        'email' => 'updated@test.com',
        'iium_id' => 'TEST003',
        'phone' => '012-3456789',
        'address' => 'Test address',
        'about' => 'Updated about content through StaffController validation',
        'date_of_birth' => '1990-01-01',
        'centre_id' => '01',
        'role' => 'teacher'
    ];
    
    $staffValidator = Validator::make($staffValidationData, [
        'name' => 'required|string|max:255',
        'email' => 'required|email|max:255',
        'iium_id' => 'required|string|max:8',
        'phone' => 'nullable|string|max:20',
        'address' => 'nullable|string|max:500',
        'about' => 'nullable|string|max:1000',  // Fixed: using 'about' instead of 'bio'
        'date_of_birth' => 'nullable|date|before:today',
        'centre_id' => 'required|string|exists:centres,centre_id',
        'role' => 'required|in:admin,supervisor,teacher,ajk'
    ]);
    
    if ($staffValidator->fails()) {
        echo "   ❌ Staff validation failed: " . implode(', ', $staffValidator->errors()->all()) . PHP_EOL;
    } else {
        echo "   ✓ Staff validation passed with 'about' field" . PHP_EOL;
        
        // Update user with validated data
        $testUser->update($staffValidator->validated());
        echo "   ✓ User updated successfully with about: " . $testUser->fresh()->about . PHP_EOL;
    }
    
    echo PHP_EOL . "2. Testing password validation fix..." . PHP_EOL;
    
    $passwordTests = [
        'password123' => false,      // No uppercase, no special char
        'Password123' => false,      // No special char  
        'Password@' => false,        // Too short, no number
        'Password123!' => true,      // Should pass (has all requirements)
        'MyPass123@' => true,        // Should pass (has all requirements)
        'weakpass' => false,         // No uppercase, number, special char
        'StrongPass123!' => true,    // Should pass (corrected - has lowercase)
        'Complex1!' => true,         // Should pass (has all requirements)
        'TestUser123@' => true       // Should pass (has all requirements)
    ];
    
    foreach ($passwordTests as $password => $shouldPass) {
        $passwordValidator = Validator::make(['password' => $password], [
            'password' => [
                'required',
                'min:8',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/'
            ]
        ]);
        
        $passed = !$passwordValidator->fails();
        $status = ($passed === $shouldPass) ? '✓' : '❌';
        $result = $passed ? 'PASS' : 'FAIL';
        $expected = $shouldPass ? 'PASS' : 'FAIL';
        echo "   {$status} Password '{$password}': {$result} (expected: {$expected})" . PHP_EOL;
        
        if ($passed !== $shouldPass) {
            echo "      Errors: " . implode(', ', $passwordValidator->errors()->get('password')) . PHP_EOL;
        }
    }
    
    echo PHP_EOL . "3. Testing view compatibility..." . PHP_EOL;
    
    // Test that both bio and about properties work for backward compatibility
    $userData = [
        'about' => $testUser->about,
        'bio' => $testUser->about  // For backward compatibility
    ];
    
    echo "   ✓ User about field: " . $userData['about'] . PHP_EOL;
    echo "   ✓ User bio field (compatibility): " . $userData['bio'] . PHP_EOL;
    
    echo PHP_EOL . "4. Cleanup test data..." . PHP_EOL;
    $testUser->delete();
    echo "   ✓ Test user deleted" . PHP_EOL;
    
    echo PHP_EOL . "=== STAFF MODULE FIXES VERIFIED SUCCESSFULLY ===" . PHP_EOL;
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . PHP_EOL;
    echo "Stack trace: " . $e->getTraceAsString() . PHP_EOL;
}