<?php
require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== TESTING TRAINEES MODULE ===" . PHP_EOL;

// Test trainees table structure
echo "Testing trainees table structure..." . PHP_EOL;
try {
    $columns = DB::select('DESCRIBE trainees');
    echo "✅ Trainee table columns:" . PHP_EOL;
    foreach ($columns as $column) {
        echo "  - {$column->Field} ({$column->Type})" . PHP_EOL;
    }
} catch (Exception $e) {
    echo "❌ Trainee table structure error: " . $e->getMessage() . PHP_EOL;
}

// Test trainees data
echo PHP_EOL . "Testing trainees data..." . PHP_EOL;
try {
    $trainees = App\Models\Trainee::all();
    echo "✅ Trainee retrieved: " . $trainees->count() . " trainees" . PHP_EOL;
    foreach ($trainees->take(5) as $trainee) {
        echo "  - {$trainee->full_name} (ID: {$trainee->id})" . PHP_EOL;
    }
} catch (Exception $e) {
    echo "❌ Trainee data error: " . $e->getMessage() . PHP_EOL;
}

// Test if TraineeController can be instantiated
echo PHP_EOL . "Testing TraineeController instantiation..." . PHP_EOL;
try {
    $controller = new App\Http\Controllers\TraineeController();
    echo "✅ TraineeController instantiated successfully" . PHP_EOL;
} catch (Exception $e) {
    echo "❌ TraineeController instantiation failed: " . $e->getMessage() . PHP_EOL;
}

// Test CRUD operations
echo PHP_EOL . "Testing CRUD operations..." . PHP_EOL;

// Test CREATE
echo "Testing CREATE operation..." . PHP_EOL;
try {
    $testTrainee = App\Models\Trainee::create([
        'trainee_id' => 'TEST' . now()->format('mdHi'),
        'trainee_first_name' => 'Test',
        'trainee_last_name' => 'Trainee ' . now()->format('His'),
        'ic_number' => '999999' . now()->format('mdHi'),
        'trainee_date_of_birth' => '1995-01-01',
        'gender' => 'Male',
        'trainee_phone_number' => '0123456789',
        'trainee_email' => 'test' . now()->format('mdHi') . '@example.com',
        'trainee_address' => 'Test Address',
        'guardian_name' => 'Test Guardian',
        'guardian_phone' => '0123456788',
        'trainee_condition' => 'Others',
        'centre_name' => 'Gombak',
        'centre_id' => '01',
        'status' => 'active'
    ]);
    echo "✅ Trainee created successfully: {$testTrainee->full_name}" . PHP_EOL;
} catch (Exception $e) {
    echo "❌ Trainee creation error: " . $e->getMessage() . PHP_EOL;
}

// Test READ
echo PHP_EOL . "Testing READ operation..." . PHP_EOL;
try {
    $trainee = App\Models\Trainee::first();
    if ($trainee) {
        echo "✅ Trainee read successfully: {$trainee->full_name}" . PHP_EOL;
        echo "  - ID: {$trainee->trainee_id}" . PHP_EOL;
        echo "  - IC: {$trainee->ic_number}" . PHP_EOL;
        echo "  - Gender: {$trainee->gender}" . PHP_EOL;
        echo "  - Contact: {$trainee->trainee_phone_number}" . PHP_EOL;
        echo "  - Email: {$trainee->trainee_email}" . PHP_EOL;
        echo "  - Centre: {$trainee->centre_name}" . PHP_EOL;
        echo "  - Status: {$trainee->status}" . PHP_EOL;
        echo "  - Condition: {$trainee->trainee_condition}" . PHP_EOL;
    } else {
        echo "❌ No trainees found" . PHP_EOL;
    }
} catch (Exception $e) {
    echo "❌ Trainee read error: " . $e->getMessage() . PHP_EOL;
}

// Test UPDATE
echo PHP_EOL . "Testing UPDATE operation..." . PHP_EOL;
try {
    $trainee = App\Models\Trainee::where('trainee_first_name', 'Test')->first();
    if ($trainee) {
        $trainee->update([
            'trainee_last_name' => 'Updated Trainee ' . now()->format('H:i:s'),
            'status' => 'inactive'
        ]);
        echo "✅ Trainee updated successfully: {$trainee->full_name}" . PHP_EOL;
    } else {
        echo "⚠️ No test trainee found for update" . PHP_EOL;
    }
} catch (Exception $e) {
    echo "❌ Trainee update error: " . $e->getMessage() . PHP_EOL;
}

// Test DELETE
echo PHP_EOL . "Testing DELETE operation..." . PHP_EOL;
try {
    $trainee = App\Models\Trainee::where('trainee_first_name', 'Test')->first();
    if ($trainee) {
        $traineeName = $trainee->full_name;
        $trainee->delete();
        echo "✅ Trainee deleted successfully: {$traineeName}" . PHP_EOL;
    } else {
        echo "⚠️ No test trainee found for deletion" . PHP_EOL;
    }
} catch (Exception $e) {
    echo "❌ Trainee deletion error: " . $e->getMessage() . PHP_EOL;
}

echo PHP_EOL . "=== END TRAINEES MODULE TEST ===" . PHP_EOL;