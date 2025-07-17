<?php
require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== TESTING TRAINEE MODULE ISSUES ===" . PHP_EOL;

use App\Models\Trainees;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

try {
    echo "1. Comparing Trainees model fillable fields vs database columns..." . PHP_EOL;
    
    // Get model fillable fields
    $trainee = new Trainees();
    $fillableFields = $trainee->getFillable();
    
    // Get database columns
    $databaseColumns = Schema::getColumnListing('trainees');
    
    echo "   Model fillable fields:" . PHP_EOL;
    foreach ($fillableFields as $field) {
        $exists = in_array($field, $databaseColumns);
        $status = $exists ? '✓' : '❌';
        echo "      {$status} {$field}" . PHP_EOL;
        if (!$exists) {
            echo "         ERROR: Field '{$field}' doesn't exist in database" . PHP_EOL;
        }
    }
    
    echo PHP_EOL . "   Database columns not in model fillable:" . PHP_EOL;
    $unusedColumns = array_diff($databaseColumns, $fillableFields, ['id', 'created_at', 'updated_at']);
    foreach ($unusedColumns as $column) {
        echo "      ? {$column}" . PHP_EOL;
    }
    
    echo PHP_EOL . "2. Testing trainee creation with missing fields..." . PHP_EOL;
    
    try {
        $testTrainee = Trainees::create([
            'trainee_id' => 'TEST001',
            'trainee_first_name' => 'Test',
            'trainee_last_name' => 'Trainee',
            'trainee_email' => 'test@trainee.com',
            'ic_number' => '001234-56-7890',
            'trainee_date_of_birth' => '2010-01-01',
            'gender' => 'Male',
            'trainee_condition' => 'Autism Spectrum Disorder',
            'centre_name' => 'Gombak',
            'centre_id' => '01',
            'status' => 'active',
            'photo_consent' => true,
            'services_consent' => true,
            'trainee_attendance' => 0,  // This field doesn't exist in database
            'trainee_last_accessed_at' => now(),  // This field doesn't exist
            'registered_by' => 1,  // This field doesn't exist
            'address' => 'Test address'  // Model expects 'address' but DB has 'trainee_address'
        ]);
        
        echo "   ❌ ERROR: Trainee creation succeeded when it should have failed due to missing columns" . PHP_EOL;
        $testTrainee->delete();
        
    } catch (Exception $e) {
        echo "   ✓ Trainee creation correctly failed with error: " . $e->getMessage() . PHP_EOL;
    }
    
    echo PHP_EOL . "3. Testing trainee creation with correct database fields..." . PHP_EOL;
    
    try {
        $correctTrainee = Trainees::create([
            'trainee_id' => 'TEST002',
            'trainee_first_name' => 'Correct',
            'trainee_last_name' => 'Trainee',
            'trainee_email' => 'correct@trainee.com',
            'ic_number' => '001234-56-7891',
            'trainee_date_of_birth' => '2010-01-01',
            'gender' => 'Male',
            'trainee_condition' => 'Autism Spectrum Disorder',
            'centre_name' => 'Gombak',
            'centre_id' => '01',
            'status' => 'active',
            'photo_consent' => true,
            'services_consent' => true,
            'trainee_address' => 'Correct test address'  // Using correct database field
        ]);
        
        echo "   ✓ Trainee created successfully with ID: " . $correctTrainee->id . PHP_EOL;
        echo "   ✓ Full name: " . $correctTrainee->full_name . PHP_EOL;
        echo "   ✓ Address: " . $correctTrainee->trainee_address . PHP_EOL;
        
        // Test cleanup
        $correctTrainee->delete();
        echo "   ✓ Test trainee deleted" . PHP_EOL;
        
    } catch (Exception $e) {
        echo "   ❌ Trainee creation failed: " . $e->getMessage() . PHP_EOL;
    }
    
    echo PHP_EOL . "4. Testing enrollment status through relationships..." . PHP_EOL;
    
    // Get a real trainee to test relationships
    $realTrainee = Trainees::first();
    if ($realTrainee) {
        echo "   Testing with trainee: " . $realTrainee->full_name . PHP_EOL;
        
        // Test enrollment relationships
        try {
            $enrollments = $realTrainee->enrollments;
            echo "   ✓ Enrollments relationship works, count: " . $enrollments->count() . PHP_EOL;
        } catch (Exception $e) {
            echo "   ❌ Enrollments relationship failed: " . $e->getMessage() . PHP_EOL;
        }
        
        try {
            $activities = $realTrainee->activities;
            echo "   ✓ Activities relationship works, count: " . $activities->count() . PHP_EOL;
        } catch (Exception $e) {
            echo "   ❌ Activities relationship failed: " . $e->getMessage() . PHP_EOL;
        }
        
        try {
            $attendances = $realTrainee->attendances;
            echo "   ✓ Attendances relationship works, count: " . $attendances->count() . PHP_EOL;
        } catch (Exception $e) {
            echo "   ❌ Attendances relationship failed: " . $e->getMessage() . PHP_EOL;
        }
    } else {
        echo "   No trainee records found for testing" . PHP_EOL;
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . PHP_EOL;
    echo "Stack trace: " . $e->getTraceAsString() . PHP_EOL;
}

echo PHP_EOL . "=== TRAINEE MODULE ISSUES TEST COMPLETED ===" . PHP_EOL;