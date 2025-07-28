<?php
require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== TESTING VOLUNTEER FORM SUBMISSION ===" . PHP_EOL;

use App\Models\Volunteer;

try {
    echo "1. Testing Volunteer model creation..." . PHP_EOL;
    
    $testData = [
        'volunteer_name' => 'John Doe',
        'volunteer_email' => 'john@example.com',
        'volunteer_phone' => '+60123456789',
        'volunteer_address' => '123 Test Street',
        'volunteer_birth_date' => '1990-01-01',
        'volunteer_gender' => 'Male',
        'volunteer_skills' => 'Teaching, Communication',
        'volunteer_experience' => 'Previous volunteer work with children',
        'volunteer_availability' => 'Weekends, Evenings',
        'volunteer_status' => 'pending',
        'volunteer_start_date' => '2025-01-01',
        'emergency_contact_name' => 'Jane Doe',
        'emergency_contact_phone' => '+60987654321',
    ];
    
    $volunteer = Volunteer::create($testData);
    echo "✓ Volunteer model created successfully with ID: " . $volunteer->id . PHP_EOL;
    
    echo "2. Testing Volunteer retrieval..." . PHP_EOL;
    $retrieved = Volunteer::find($volunteer->id);
    echo "✓ Retrieved volunteer: " . $retrieved->volunteer_name . " (" . $retrieved->volunteer_email . ")" . PHP_EOL;
    
    echo "3. Testing column access..." . PHP_EOL;
    echo "   - volunteer_name: " . $retrieved->volunteer_name . PHP_EOL;
    echo "   - volunteer_email: " . $retrieved->volunteer_email . PHP_EOL;
    echo "   - volunteer_phone: " . $retrieved->volunteer_phone . PHP_EOL;
    echo "   - volunteer_address: " . $retrieved->volunteer_address . PHP_EOL;
    echo "   - volunteer_skills: " . $retrieved->volunteer_skills . PHP_EOL;
    echo "   - volunteer_experience: " . $retrieved->volunteer_experience . PHP_EOL;
    echo "   - volunteer_availability: " . $retrieved->volunteer_availability . PHP_EOL;
    echo "   - volunteer_status: " . $retrieved->volunteer_status . PHP_EOL;
    
    echo "4. Testing model methods..." . PHP_EOL;
    echo "   - Status badge color: " . $retrieved->status_badge_color . PHP_EOL;
    
    echo "5. Cleaning up test data..." . PHP_EOL;
    $retrieved->delete();
    echo "✓ Test data cleaned up" . PHP_EOL;
    
    echo "=== VOLUNTEER FORM TEST COMPLETED SUCCESSFULLY ===" . PHP_EOL;
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . PHP_EOL;
    echo "Stack trace: " . $e->getTraceAsString() . PHP_EOL;
}