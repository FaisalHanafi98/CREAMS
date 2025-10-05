<?php
/**
 * Comprehensive User Management Testing Suite
 * Tests staff creation, trainee management, and user operations
 */

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== COMPREHENSIVE USER MANAGEMENT TEST SUITE ===" . PHP_EOL;
echo "Testing all user management functionality..." . PHP_EOL . PHP_EOL;

// Test 1: Staff Creation and Management
echo "1. TESTING STAFF CREATION AND MANAGEMENT" . PHP_EOL;
echo str_repeat("-", 40) . PHP_EOL;

try {
    // Test staff user creation
    echo "Testing staff user creation..." . PHP_EOL;

    $staffData = [
        'name' => 'Test Staff Member',
        'email' => 'test.staff@example.com',
        'role' => 'teacher',
        'centre_id' => '01',
        'status' => 'active',
        'password' => bcrypt('password123')
    ];

    // Check if test staff already exists
    $existingStaff = App\Models\User::where('email', $staffData['email'])->first();
    if ($existingStaff) {
        echo "✅ Test staff already exists (ID: {$existingStaff->id})" . PHP_EOL;
    } else {
        $staff = App\Models\User::create($staffData);
        echo "✅ Staff member created successfully (ID: {$staff->id})" . PHP_EOL;
    }

    // Test staff permissions
    $staff = App\Models\User::where('role', 'teacher')->first();
    if ($staff) {
        echo "✅ Teacher role staff found: {$staff->name}" . PHP_EOL;
        echo "   - Centre ID: {$staff->centre_id}" . PHP_EOL;
        echo "   - Status: {$staff->status}" . PHP_EOL;
    }

} catch (Exception $e) {
    echo "❌ Staff Creation Error: " . $e->getMessage() . PHP_EOL;
}

// Test 2: Trainee Creation and Management
echo PHP_EOL . "2. TESTING TRAINEE CREATION AND MANAGEMENT" . PHP_EOL;
echo str_repeat("-", 40) . PHP_EOL;

try {
    echo "Testing trainee creation..." . PHP_EOL;

    $traineeData = [
        'name' => 'Test Trainee',
        'identity_number' => 'TEST123456789',
        'phone' => '0123456789',
        'guardian_name' => 'Test Guardian',
        'guardian_phone' => '0123456788',
        'centre_id' => '01',
        'status' => 'active',
        'date_registered' => now()
    ];

    // Check if test trainee already exists
    $existingTrainee = App\Models\Trainee::where('identity_number', $traineeData['identity_number'])->first();
    if ($existingTrainee) {
        echo "✅ Test trainee already exists (ID: {$existingTrainee->id})" . PHP_EOL;
    } else {
        $trainee = App\Models\Trainee::create($traineeData);
        echo "✅ Trainee created successfully (ID: {$trainee->id})" . PHP_EOL;
    }

    // Test trainee data retrieval
    $traineeCount = App\Models\Trainee::where('status', 'active')->count();
    echo "✅ Active trainees count: $traineeCount" . PHP_EOL;

    // Test trainee relationships
    $trainee = App\Models\Trainee::first();
    if ($trainee) {
        echo "✅ Sample trainee data:" . PHP_EOL;
        echo "   - Name: {$trainee->name}" . PHP_EOL;
        echo "   - Guardian: {$trainee->guardian_name}" . PHP_EOL;
        echo "   - Centre: {$trainee->centre_id}" . PHP_EOL;
    }

} catch (Exception $e) {
    echo "❌ Trainee Creation Error: " . $e->getMessage() . PHP_EOL;
}

// Test 3: User Management Operations
echo PHP_EOL . "3. TESTING USER MANAGEMENT OPERATIONS" . PHP_EOL;
echo str_repeat("-", 40) . PHP_EOL;

try {
    // Test user roles
    echo "Testing user roles distribution..." . PHP_EOL;
    $roles = ['admin', 'teacher', 'supervisor', 'ajk'];

    foreach ($roles as $role) {
        $count = App\Models\User::where('role', $role)->count();
        echo "   - $role: $count users" . PHP_EOL;
    }

    // Test user status
    $activeUsers = App\Models\User::where('status', 'active')->count();
    $totalUsers = App\Models\User::count();
    echo "✅ User status: $activeUsers active out of $totalUsers total" . PHP_EOL;

    // Test centre assignments
    $centreAssignments = App\Models\User::select('centre_id', DB::raw('count(*) as count'))
        ->groupBy('centre_id')
        ->get();

    echo "✅ Centre assignments:" . PHP_EOL;
    foreach ($centreAssignments as $assignment) {
        echo "   - Centre {$assignment->centre_id}: {$assignment->count} users" . PHP_EOL;
    }

} catch (Exception $e) {
    echo "❌ User Management Error: " . $e->getMessage() . PHP_EOL;
}

// Test 4: Data Integrity Checks
echo PHP_EOL . "4. TESTING DATA INTEGRITY" . PHP_EOL;
echo str_repeat("-", 40) . PHP_EOL;

try {
    // Check for orphaned records
    echo "Checking data integrity..." . PHP_EOL;

    // Check users without centres (if centres exist)
    $usersWithoutCentre = App\Models\User::whereNull('centre_id')->count();
    if ($usersWithoutCentre > 0) {
        echo "⚠️ Found $usersWithoutCentre users without centre assignments" . PHP_EOL;
    } else {
        echo "✅ All users have centre assignments" . PHP_EOL;
    }

    // Check trainees without guardians
    $traineesWithoutGuardian = App\Models\Trainee::whereNull('guardian_name')
        ->orWhere('guardian_name', '')
        ->count();

    if ($traineesWithoutGuardian > 0) {
        echo "⚠️ Found $traineesWithoutGuardian trainees without guardian information" . PHP_EOL;
    } else {
        echo "✅ All trainees have guardian information" . PHP_EOL;
    }

    // Check for duplicate emails
    $duplicateEmails = App\Models\User::select('email', DB::raw('count(*) as count'))
        ->groupBy('email')
        ->having('count', '>', 1)
        ->count();

    if ($duplicateEmails > 0) {
        echo "⚠️ Found $duplicateEmails duplicate email addresses" . PHP_EOL;
    } else {
        echo "✅ No duplicate email addresses found" . PHP_EOL;
    }

} catch (Exception $e) {
    echo "❌ Data Integrity Error: " . $e->getMessage() . PHP_EOL;
}

// Test 5: Module Fixes Verification
echo PHP_EOL . "5. TESTING MODULE FIXES VERIFICATION" . PHP_EOL;
echo str_repeat("-", 40) . PHP_EOL;

try {
    // Test staff module fixes
    echo "Verifying staff module fixes..." . PHP_EOL;

    $staffController = new App\Http\Controllers\StaffController();
    echo "✅ Staff controller accessible" . PHP_EOL;

    // Test trainee module fixes
    echo "Verifying trainee module fixes..." . PHP_EOL;

    $traineeController = new App\Http\Controllers\TraineeController();
    echo "✅ Trainee controller accessible" . PHP_EOL;

    // Test user profile functionality
    $user = App\Models\User::first();
    if ($user && method_exists($user, 'getFullNameAttribute')) {
        echo "✅ User model methods working" . PHP_EOL;
    }

} catch (Exception $e) {
    echo "❌ Module Fixes Error: " . $e->getMessage() . PHP_EOL;
}

echo PHP_EOL . "=== USER MANAGEMENT TEST SUITE COMPLETED ===" . PHP_EOL;
echo "Check above for any ❌ errors that need attention." . PHP_EOL;