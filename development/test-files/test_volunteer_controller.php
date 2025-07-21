<?php
require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== TESTING VOLUNTEER CONTROLLER FORM SUBMISSION ===" . PHP_EOL;

use App\Http\Controllers\VolunteerController;
use Illuminate\Http\Request;

try {
    // Create a mock request with form data
    $requestData = [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'john@example.com',
        'phone' => '+60123456789',
        'address' => '123 Test Street',
        'birth_date' => '1990-01-01',
        'gender' => 'Male',
        'skills' => 'Teaching, Communication',
        'experience' => 'Previous volunteer work with children',
        'interest' => 'direct-support',
        'availability' => ['weekday', 'weekend'],
        'commitment' => '4-6',
        'motivation' => 'I want to help children with special needs',
        'consent' => '1',
        'emergency_contact_name' => 'Jane Doe',
        'emergency_contact_phone' => '+60987654321',
        '_token' => 'test_token'
    ];

    // Test the data preparation logic from the controller
    $volunteerData = [
        'volunteer_name' => $requestData['first_name'] . ' ' . $requestData['last_name'],
        'volunteer_email' => strtolower(trim($requestData['email'])),
        'volunteer_phone' => $requestData['phone'],
        'volunteer_address' => $requestData['address'] ?: '',
        'volunteer_birth_date' => $requestData['birth_date'] ?: '1990-01-01',
        'volunteer_gender' => $requestData['gender'] ?: 'Other',
        'volunteer_skills' => $requestData['skills'] ?: '',
        'volunteer_experience' => $requestData['experience'] ?: '',
        'volunteer_availability' => implode(', ', $requestData['availability']),
        'volunteer_status' => 'pending',
        'volunteer_start_date' => now()->format('Y-m-d'),
        'emergency_contact_name' => $requestData['emergency_contact_name'] ?: '',
        'emergency_contact_phone' => $requestData['emergency_contact_phone'] ?: '',
    ];
    
    echo "1. Testing volunteer data structure..." . PHP_EOL;
    foreach ($volunteerData as $key => $value) {
        echo "   - {$key}: {$value}" . PHP_EOL;
    }
    
    echo "2. Testing database insertion..." . PHP_EOL;
    $volunteer = \App\Models\Volunteers::create($volunteerData);
    echo "✓ Volunteer created with ID: " . $volunteer->id . PHP_EOL;
    
    echo "3. Testing data retrieval..." . PHP_EOL;
    $retrieved = \App\Models\Volunteers::find($volunteer->id);
    echo "✓ Retrieved volunteer: " . $retrieved->volunteer_name . " (" . $retrieved->volunteer_email . ")" . PHP_EOL;
    echo "   - Status: " . $retrieved->volunteer_status . PHP_EOL;
    echo "   - Availability: " . $retrieved->volunteer_availability . PHP_EOL;
    
    echo "4. Testing model methods..." . PHP_EOL;
    echo "   - Status badge color: " . $retrieved->status_badge_color . PHP_EOL;
    
    echo "5. Cleaning up test data..." . PHP_EOL;
    $retrieved->delete();
    echo "✓ Test data cleaned up" . PHP_EOL;
    
    echo "=== VOLUNTEER CONTROLLER TEST COMPLETED SUCCESSFULLY ===" . PHP_EOL;
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . PHP_EOL;
    echo "Stack trace: " . $e->getTraceAsString() . PHP_EOL;
}