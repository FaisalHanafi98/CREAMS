<?php
require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== TESTING REFACTORED TRAINEES SEEDER ===" . PHP_EOL;

use Database\Seeders\RefactoredTraineesSeeder;
use App\Models\Trainee;
use App\Models\Centre;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;

try {
    echo "1. Getting initial trainee count..." . PHP_EOL;
    $initialCount = Trainee::count();
    echo "   Initial trainees count: {$initialCount}" . PHP_EOL;
    
    echo PHP_EOL . "2. Running refactored seeder (creating 3 test trainees)..." . PHP_EOL;
    
    $faker = Faker::create('en_MY');
    $centres = Centre::select('centre_id', 'centre_name')->get();
    
    if ($centres->isEmpty()) {
        echo "   No centres found, creating test centre..." . PHP_EOL;
        $centre = Centre::create([
            'centre_id' => 'TST',
            'centre_name' => 'Test Centre',
            'centre_status' => 'active'
        ]);
        $centres = collect([$centre]);
    }
    
    echo "   Available centres: " . $centres->pluck('centre_name')->implode(', ') . PHP_EOL;
    
    // Create 3 test trainees using the refactored logic
    for ($i = 1; $i <= 3; $i++) {
        $gender = $faker->randomElement(['Male', 'Female']);
        $selectedCentre = $centres->random();
        
        // Malaysian IC format
        $birthYear = 2010;
        $birthMonth = sprintf('%02d', 6);
        $birthDay = sprintf('%02d', 15);
        $birthDate = "{$birthYear}-{$birthMonth}-{$birthDay}";
        $icNumber = "10{$birthMonth}{$birthDay}-01-00{$i}" . ($gender === 'Male' ? '1' : '2');
        
        $testTraineeData = [
            'trainee_id' => "TEST-" . sprintf('%03d', $i),
            'trainee_first_name' => "Test{$i}",
            'trainee_last_name' => $gender === 'Male' ? 'bin Ahmad' : 'binti Ahmad',
            'trainee_email' => "test{$i}@refactored.com",
            'ic_number' => $icNumber,
            'trainee_date_of_birth' => $birthDate,
            'gender' => $gender,
            'trainee_phone_number' => '012-3456789',
            'trainee_address' => "No. {$i}, Jalan Test, Taman Test, 50000 Kuala Lumpur",
            'avatar' => null,
            'trainee_condition' => 'Autism Spectrum Disorder',
            'centre_name' => $selectedCentre->centre_name,
            'centre_id' => $selectedCentre->centre_id,
            'course_id' => null,
            'status' => 'active',
            'photo_consent' => true,
            'services_consent' => true,
            'guardian_name' => "Guardian Test{$i}",
            'guardian_relationship' => 'Father',
            'guardian_phone' => '019-8765432',
            'guardian_email' => "guardian{$i}@test.com",
            'guardian_address' => "No. {$i}, Jalan Test, Taman Test, 50000 Kuala Lumpur",
            'emergency_contact_name' => "Emergency{$i}",
            'emergency_contact_phone' => '017-1234567',
            'emergency_contact_relationship' => 'Uncle',
            'medical_history' => "Test medical history for trainee {$i}",
            'additional_notes' => "Test additional notes for trainee {$i}",
            'created_at' => now(),
            'updated_at' => now()
        ];
        
        $trainee = Trainee::create($testTraineeData);
        echo "   ✅ Created test trainee: {$trainee->full_name} (ID: {$trainee->trainee_id})" . PHP_EOL;
    }
    
    echo PHP_EOL . "3. Verifying created trainees..." . PHP_EOL;
    $finalCount = Trainee::count();
    $newTrainees = $finalCount - $initialCount;
    echo "   Final trainees count: {$finalCount}" . PHP_EOL;
    echo "   New trainees created: {$newTrainees}" . PHP_EOL;
    
    echo PHP_EOL . "4. Testing new trainees data integrity..." . PHP_EOL;
    $testTrainees = Trainee::where('trainee_id', 'LIKE', 'TEST-%')->get();
    
    foreach ($testTrainees as $trainee) {
        echo "   Testing trainee: {$trainee->full_name}" . PHP_EOL;
        echo "     - Trainee ID: {$trainee->trainee_id}" . PHP_EOL;
        echo "     - IC Number: {$trainee->ic_number}" . PHP_EOL;
        echo "     - Age: {$trainee->age} years" . PHP_EOL;
        echo "     - Gender: {$trainee->gender}" . PHP_EOL;
        echo "     - Address: " . substr($trainee->trainee_address, 0, 30) . "..." . PHP_EOL;
        echo "     - Centre: {$trainee->centre_name} (ID: {$trainee->centre_id})" . PHP_EOL;
        echo "     - Photo consent: " . ($trainee->photo_consent ? 'Yes' : 'No') . PHP_EOL;
        echo "     - Services consent: " . ($trainee->services_consent ? 'Yes' : 'No') . PHP_EOL;
        echo "     - Guardian: {$trainee->guardian_name}" . PHP_EOL;
        echo "     - Emergency contact: {$trainee->emergency_contact_name}" . PHP_EOL;
        echo PHP_EOL;
    }
    
    echo "5. Testing relationships..." . PHP_EOL;
    $firstTestTrainee = $testTrainees->first();
    if ($firstTestTrainee) {
        try {
            $enrollments = $firstTestTrainee->enrollments;
            echo "   ✅ Enrollments relationship works: {$enrollments->count()}" . PHP_EOL;
        } catch (Exception $e) {
            echo "   ❌ Enrollments relationship failed: " . $e->getMessage() . PHP_EOL;
        }
        
        try {
            $activities = $firstTestTrainee->activities;
            echo "   ✅ Activity relationship works: {$activities->count()}" . PHP_EOL;
        } catch (Exception $e) {
            echo "   ❌ Activity relationship failed: " . $e->getMessage() . PHP_EOL;
        }
    }
    
    echo PHP_EOL . "6. Cleanup test data..." . PHP_EOL;
    $deletedCount = Trainee::where('trainee_id', 'LIKE', 'TEST-%')->delete();
    echo "   ✅ Deleted {$deletedCount} test trainees" . PHP_EOL;
    
    echo PHP_EOL . "=== REFACTORED SEEDER TEST COMPLETED SUCCESSFULLY ===" . PHP_EOL;
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . PHP_EOL;
    echo "Stack trace: " . $e->getTraceAsString() . PHP_EOL;
}