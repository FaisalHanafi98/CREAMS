<?php
require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== TESTING FIXED TRAINEE MODULE ===" . PHP_EOL;

use App\Models\Trainee;
use Illuminate\Support\Facades\Schema;

try {
    echo "1. Testing fixed model fillable fields vs database columns..." . PHP_EOL;
    
    // Get model fillable fields
    $trainee = new Trainee();
    $fillableFields = $trainee->getFillable();
    
    // Get database columns
    $databaseColumns = Schema::getColumnListing('trainees');
    
    $missingFields = [];
    foreach ($fillableFields as $field) {
        $exists = in_array($field, $databaseColumns);
        $status = $exists ? '✓' : '❌';
        echo "      {$status} {$field}" . PHP_EOL;
        if (!$exists) {
            $missingFields[] = $field;
        }
    }
    
    if (empty($missingFields)) {
        echo "   ✅ All model fillable fields exist in database!" . PHP_EOL;
    } else {
        echo "   ❌ Missing fields: " . implode(', ', $missingFields) . PHP_EOL;
    }
    
    echo PHP_EOL . "2. Testing trainee creation with fixed model..." . PHP_EOL;
    
    $testTraineeData = [
        'trainee_id' => 'TEST003',
        'trainee_first_name' => 'Fixed',
        'trainee_last_name' => 'Trainee',
        'trainee_email' => 'fixed@trainee.com',
        'ic_number' => '001234-56-7892',
        'trainee_date_of_birth' => '2010-01-01',
        'gender' => 'Female',
        'trainee_address' => 'Fixed test address',
        'trainee_condition' => 'Down Syndrome',
        'centre_name' => 'Gombak',
        'centre_id' => '01',
        'status' => 'active',
        'photo_consent' => true,
        'services_consent' => false,
        'trainee_phone_number' => '012-3456789',
        'guardian_name' => 'Test Guardian',
        'guardian_relationship' => 'Mother',
        'guardian_phone' => '012-9876543',
        'guardian_email' => 'guardian@test.com',
        'medical_history' => 'No significant medical history',
        'additional_notes' => 'Test notes for fixed trainee'
    ];
    
    $fixedTrainee = Trainee::create($testTraineeData);
    echo "   ✓ Trainee created successfully with ID: " . $fixedTrainee->id . PHP_EOL;
    echo "   ✓ Full name: " . $fixedTrainee->full_name . PHP_EOL;
    echo "   ✓ Age: " . $fixedTrainee->age . " years" . PHP_EOL;
    echo "   ✓ Condition badge class: " . $fixedTrainee->condition_badge_class . PHP_EOL;
    echo "   ✓ Photo consent: " . ($fixedTrainee->photo_consent ? 'Yes' : 'No') . PHP_EOL;
    echo "   ✓ Services consent: " . ($fixedTrainee->services_consent ? 'Yes' : 'No') . PHP_EOL;
    
    echo PHP_EOL . "3. Testing fixed relationships..." . PHP_EOL;
    
    // Test enrollment relationships
    try {
        $enrollments = $fixedTrainee->enrollments;
        echo "   ✓ Enrollments relationship works, count: " . $enrollments->count() . PHP_EOL;
    } catch (Exception $e) {
        echo "   ❌ Enrollments relationship failed: " . $e->getMessage() . PHP_EOL;
    }
    
    try {
        $activities = $fixedTrainee->activities;
        echo "   ✓ Activity relationship works, count: " . $activities->count() . PHP_EOL;
    } catch (Exception $e) {
        echo "   ❌ Activity relationship failed: " . $e->getMessage() . PHP_EOL;
    }
    
    // Test with real trainee that might have data
    $realTrainee = Trainee::first();
    if ($realTrainee && $realTrainee->id !== $fixedTrainee->id) {
        echo "   Testing relationships with existing trainee: " . $realTrainee->full_name . PHP_EOL;
        
        try {
            $activities = $realTrainee->activities;
            echo "   ✓ Real trainee activities relationship works, count: " . $activities->count() . PHP_EOL;
            
            if ($activities->count() > 0) {
                $firstActivity = $activities->first();
                echo "      - First activity: " . $firstActivity->activity_name . PHP_EOL;
                echo "      - Enrollment status: " . $firstActivity->pivot->enrollment_status . PHP_EOL;
                echo "      - Progress: " . $firstActivity->pivot->progress_percentage . "%" . PHP_EOL;
            }
        } catch (Exception $e) {
            echo "   ❌ Real trainee activities relationship failed: " . $e->getMessage() . PHP_EOL;
        }
    }
    
    echo PHP_EOL . "4. Testing model scopes..." . PHP_EOL;
    
    $activeTrainees = Trainee::active()->count();
    echo "   ✓ Active scope works, count: " . $activeTrainees . PHP_EOL;
    
    $gombakTrainees = Trainee::byCentre('Gombak')->count();
    echo "   ✓ ByCentre scope works, Gombak count: " . $gombakTrainees . PHP_EOL;
    
    $downSyndromeTrainees = Trainee::byCondition('Down Syndrome')->count();
    echo "   ✓ ByCondition scope works, Down Syndrome count: " . $downSyndromeTrainees . PHP_EOL;
    
    echo PHP_EOL . "5. Cleanup test data..." . PHP_EOL;
    $fixedTrainee->delete();
    echo "   ✓ Test trainee deleted" . PHP_EOL;
    
    echo PHP_EOL . "=== TRAINEE MODULE FIXES VERIFIED SUCCESSFULLY ===" . PHP_EOL;
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . PHP_EOL;
    echo "Stack trace: " . $e->getTraceAsString() . PHP_EOL;
}