<?php
require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== TESTING CENTRES MODULE ===\n";

// Test centres table structure
echo "Testing centres table structure...\n";
try {
    $columns = DB::select('DESCRIBE centres');
    echo "✅ Centres table columns:\n";
    foreach ($columns as $column) {
        echo "  - {$column->Field} ({$column->Type})\n";
    }
} catch (Exception $e) {
    echo "❌ Centres table structure error: " . $e->getMessage() . "\n";
}

// Test centres data
echo "\nTesting centres data...\n";
try {
    $centres = App\Models\Centres::all();
    echo "✅ Centres retrieved: " . $centres->count() . " centres\n";
    foreach ($centres as $centre) {
        echo "  - {$centre->centre_name} (ID: {$centre->centre_id})\n";
    }
} catch (Exception $e) {
    echo "❌ Centres data error: " . $e->getMessage() . "\n";
}

// Test if CentreController can be instantiated
echo "\nTesting CentreController instantiation...\n";
try {
    $controller = new App\Http\Controllers\CentreController();
    echo "✅ CentreController instantiated successfully\n";
} catch (Exception $e) {
    echo "❌ CentreController instantiation failed: " . $e->getMessage() . "\n";
}

// Test CRUD operations
echo "\nTesting CRUD operations...\n";

// Test CREATE
echo "Testing CREATE operation...\n";
try {
    $testCentre = App\Models\Centres::create([
        'centre_id' => 'TEST' . now()->format('mdHi'),
        'centre_name' => 'Test Centre ' . now()->format('Y-m-d H:i:s'),
        'centre_address' => 'Test Address',
        'centre_phone' => '123-456-7890',
        'centre_email' => 'test@example.com',
        'centre_capacity' => '100',
        'centre_manager' => 'Test Manager',
        'centre_manager_contact' => '123-456-7891',
        'centre_status' => 'active',
        'centre_description' => 'Test Description'
    ]);
    echo "✅ Centre created successfully: {$testCentre->centre_name}\n";
} catch (Exception $e) {
    echo "❌ Centre creation error: " . $e->getMessage() . "\n";
}

// Test READ
echo "\nTesting READ operation...\n";
try {
    $centre = App\Models\Centres::first();
    if ($centre) {
        echo "✅ Centre read successfully: {$centre->centre_name}\n";
        echo "  - ID: {$centre->centre_id}\n";
        echo "  - Address: {$centre->centre_address}\n";
        echo "  - Capacity: {$centre->centre_capacity}\n";
        echo "  - Status: {$centre->centre_status}\n";
    } else {
        echo "❌ No centres found\n";
    }
} catch (Exception $e) {
    echo "❌ Centre read error: " . $e->getMessage() . "\n";
}

// Test UPDATE
echo "\nTesting UPDATE operation...\n";
try {
    $centre = App\Models\Centres::where('centre_id', 'LIKE', 'TEST%')->first();
    if ($centre) {
        $centre->update([
            'centre_name' => 'Updated Test Centre ' . now()->format('H:i:s'),
            'centre_address' => 'Updated Test Address'
        ]);
        echo "✅ Centre updated successfully: {$centre->centre_name}\n";
    } else {
        echo "⚠️ No test centre found for update\n";
    }
} catch (Exception $e) {
    echo "❌ Centre update error: " . $e->getMessage() . "\n";
}

// Test DELETE
echo "\nTesting DELETE operation...\n";
try {
    $centre = App\Models\Centres::where('centre_id', 'LIKE', 'TEST%')->first();
    if ($centre) {
        $centreName = $centre->centre_name;
        $centre->delete();
        echo "✅ Centre deleted successfully: {$centreName}\n";
    } else {
        echo "⚠️ No test centre found for deletion\n";
    }
} catch (Exception $e) {
    echo "❌ Centre deletion error: " . $e->getMessage() . "\n";
}

echo "\n=== END CENTRES MODULE TEST ===\n";