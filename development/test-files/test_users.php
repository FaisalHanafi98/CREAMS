<?php
require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== TESTING USERS/STAFF MODULE ===" . PHP_EOL;

// Test users table structure
echo "Testing users table structure..." . PHP_EOL;
try {
    $columns = DB::select('DESCRIBE users');
    echo "✅ User table columns:" . PHP_EOL;
    foreach ($columns as $column) {
        echo "  - {$column->Field} ({$column->Type})" . PHP_EOL;
    }
} catch (Exception $e) {
    echo "❌ User table structure error: " . $e->getMessage() . PHP_EOL;
}

// Test users data
echo PHP_EOL . "Testing users data..." . PHP_EOL;
try {
    $users = App\Models\User::all();
    echo "✅ User retrieved: " . $users->count() . " users" . PHP_EOL;
    foreach ($users->take(5) as $user) {
        echo "  - {$user->name} ({$user->role}) - Centre: {$user->centre_id}" . PHP_EOL;
    }
} catch (Exception $e) {
    echo "❌ User data error: " . $e->getMessage() . PHP_EOL;
}

// Test if UserController can be instantiated
echo PHP_EOL . "Testing UserController instantiation..." . PHP_EOL;
try {
    $controller = new App\Http\Controllers\UserController();
    echo "✅ UserController instantiated successfully" . PHP_EOL;
} catch (Exception $e) {
    echo "❌ UserController instantiation failed: " . $e->getMessage() . PHP_EOL;
}

// Test authentication-related operations
echo PHP_EOL . "Testing authentication operations..." . PHP_EOL;

// Test user authentication
echo "Testing user authentication..." . PHP_EOL;
try {
    $admin = App\Models\User::where('role', 'admin')->first();
    if ($admin) {
        echo "✅ Admin user found: {$admin->name}" . PHP_EOL;
        echo "  - Email: {$admin->email}" . PHP_EOL;
        echo "  - Role: {$admin->role}" . PHP_EOL;
        echo "  - Centre: {$admin->centre_id}" . PHP_EOL;
        echo "  - Status: {$admin->status}" . PHP_EOL;
    } else {
        echo "❌ No admin user found" . PHP_EOL;
    }
} catch (Exception $e) {
    echo "❌ User authentication test error: " . $e->getMessage() . PHP_EOL;
}

// Test CRUD operations
echo PHP_EOL . "Testing CRUD operations..." . PHP_EOL;

// Test CREATE
echo "Testing CREATE operation..." . PHP_EOL;
try {
    $testUser = App\Models\User::create([
        'iium_id' => 'TEST' . now()->format('mdHi'),
        'name' => 'Test User ' . now()->format('Y-m-d H:i:s'),
        'email' => 'test' . now()->format('mdHi') . '@example.com',
        'password' => bcrypt('password123'),
        'role' => 'teacher',
        'status' => 'active',
        'centre_id' => '01',
        'phone' => '0123456789',
        'address' => 'Test Address',
        'date_of_birth' => '1990-01-01',
        'education_level' => 'Bachelor',
        'education_specialization' => 'Education',
        'teaching_specialization' => 'Special Education'
    ]);
    echo "✅ User created successfully: {$testUser->name}" . PHP_EOL;
} catch (Exception $e) {
    echo "❌ User creation error: " . $e->getMessage() . PHP_EOL;
}

// Test READ
echo PHP_EOL . "Testing READ operation..." . PHP_EOL;
try {
    $user = App\Models\User::first();
    if ($user) {
        echo "✅ User read successfully: {$user->name}" . PHP_EOL;
        echo "  - Email: {$user->email}" . PHP_EOL;
        echo "  - Role: {$user->role}" . PHP_EOL;
        echo "  - Centre: {$user->centre_id}" . PHP_EOL;
        echo "  - Phone: {$user->phone}" . PHP_EOL;
        echo "  - Status: {$user->status}" . PHP_EOL;
    } else {
        echo "❌ No users found" . PHP_EOL;
    }
} catch (Exception $e) {
    echo "❌ User read error: " . $e->getMessage() . PHP_EOL;
}

// Test UPDATE
echo PHP_EOL . "Testing UPDATE operation..." . PHP_EOL;
try {
    $user = App\Models\User::where('name', 'LIKE', 'Test User%')->first();
    if ($user) {
        $user->update([
            'name' => 'Updated Test User ' . now()->format('H:i:s'),
            'teaching_specialization' => 'Updated Specialization'
        ]);
        echo "✅ User updated successfully: {$user->name}" . PHP_EOL;
    } else {
        echo "⚠️ No test user found for update" . PHP_EOL;
    }
} catch (Exception $e) {
    echo "❌ User update error: " . $e->getMessage() . PHP_EOL;
}

// Test role-based queries
echo PHP_EOL . "Testing role-based queries..." . PHP_EOL;
try {
    $roles = ['admin', 'supervisor', 'teacher', 'ajk'];
    foreach ($roles as $role) {
        $count = App\Models\User::where('role', $role)->count();
        echo "✅ {$role}: {$count} users" . PHP_EOL;
    }
} catch (Exception $e) {
    echo "❌ Role-based query error: " . $e->getMessage() . PHP_EOL;
}

// Test centre-based queries
echo PHP_EOL . "Testing centre-based queries..." . PHP_EOL;
try {
    $centres = App\Models\Centre::all();
    foreach ($centres as $centre) {
        $count = App\Models\User::where('centre_id', $centre->centre_id)->count();
        echo "✅ {$centre->centre_name}: {$count} users" . PHP_EOL;
    }
} catch (Exception $e) {
    echo "❌ Centre-based query error: " . $e->getMessage() . PHP_EOL;
}

// Test DELETE
echo PHP_EOL . "Testing DELETE operation..." . PHP_EOL;
try {
    $user = App\Models\User::where('name', 'LIKE', 'Updated Test User%')->first();
    if ($user) {
        $userName = $user->name;
        $user->delete();
        echo "✅ User deleted successfully: {$userName}" . PHP_EOL;
    } else {
        echo "⚠️ No test user found for deletion" . PHP_EOL;
    }
} catch (Exception $e) {
    echo "❌ User deletion error: " . $e->getMessage() . PHP_EOL;
}

echo PHP_EOL . "=== END USERS/STAFF MODULE TEST ===" . PHP_EOL;