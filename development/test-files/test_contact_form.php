<?php
require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== TESTING CONTACT FORM SUBMISSION ===" . PHP_EOL;

use App\Models\ContactMessages;
use Illuminate\Http\Request;
use App\Http\Controllers\ContactController;

try {
    // Test 1: Check if ContactMessages model can create a record
    echo "1. Testing ContactMessages model creation..." . PHP_EOL;
    
    $testData = [
        'sender_name' => 'John Doe',
        'sender_email' => 'john@example.com',
        'sender_phone' => '+60123456789',
        'message_category' => 'general',
        'message_body' => 'This is a test message.',
        'message_subject' => 'Test Subject',
        'message_status' => 'new',
    ];
    
    $contact = ContactMessages::create($testData);
    echo "✓ ContactMessages model created successfully with ID: " . $contact->id . PHP_EOL;
    
    // Test 2: Check if we can retrieve the record
    echo "2. Testing ContactMessages retrieval..." . PHP_EOL;
    $retrieved = ContactMessages::find($contact->id);
    echo "✓ Retrieved contact: " . $retrieved->sender_name . " (" . $retrieved->sender_email . ")" . PHP_EOL;
    
    // Test 3: Check column access
    echo "3. Testing column access..." . PHP_EOL;
    echo "   - sender_name: " . $retrieved->sender_name . PHP_EOL;
    echo "   - sender_email: " . $retrieved->sender_email . PHP_EOL;
    echo "   - sender_phone: " . $retrieved->sender_phone . PHP_EOL;
    echo "   - message_category: " . $retrieved->message_category . PHP_EOL;
    echo "   - message_body: " . $retrieved->message_body . PHP_EOL;
    echo "   - message_subject: " . $retrieved->message_subject . PHP_EOL;
    echo "   - message_status: " . $retrieved->message_status . PHP_EOL;
    
    // Test 4: Clean up
    echo "4. Cleaning up test data..." . PHP_EOL;
    $retrieved->delete();
    echo "✓ Test data cleaned up" . PHP_EOL;
    
    echo "=== CONTACT FORM TEST COMPLETED SUCCESSFULLY ===" . PHP_EOL;
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . PHP_EOL;
    echo "Stack trace: " . $e->getTraceAsString() . PHP_EOL;
}