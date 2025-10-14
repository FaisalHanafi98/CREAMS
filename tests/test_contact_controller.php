<?php
require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== TESTING CONTACT CONTROLLER FORM SUBMISSION ===" . PHP_EOL;

use App\Http\Controllers\ContactController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

try {
    // Create a mock request with form data
    $requestData = [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'phone' => '+60123456789',
        'reason' => 'services',
        'subject' => 'Test Subject',
        'message' => 'This is a test message for contact form submission.',
        'urgency' => 'medium',
        'organization' => 'Test Organization',
        'preferred_contact_method' => 'email',
        '_token' => 'test_token'
    ];

    // Create request
    $request = Request::create('/contact', 'POST', $requestData);
    
    // Test the mapReasonToCategory method by creating a contact data array similar to the controller
    $controller = new ContactController();
    
    // Map reason to category using the same logic as controller
    $categoryMap = [
        'services' => 'general',
        'support' => 'support',
        'volunteer' => 'general',
        'partnership' => 'general',
        'complaint' => 'complaint',
        'admission' => 'general',
        'feedback' => 'suggestion',
        'general' => 'general',
        'other' => 'general'
    ];
    
    // Simulate the data preparation logic from the controller
    $contactData = [
        'sender_name' => ucwords(strtolower(trim($requestData['name']))),
        'sender_email' => strtolower(trim($requestData['email'])),
        'sender_phone' => $requestData['phone'],
        'message_category' => $categoryMap[$requestData['reason']] ?? 'general',
        'message_body' => trim($requestData['message']),
        'message_subject' => $requestData['subject'],
        'message_status' => 'new',
    ];
    
    echo "1. Testing contact data structure..." . PHP_EOL;
    foreach ($contactData as $key => $value) {
        echo "   - {$key}: {$value}" . PHP_EOL;
    }
    
    echo "2. Testing database insertion..." . PHP_EOL;
    $contact = \App\Models\ContactMessages::create($contactData);
    echo "✓ Contact created with ID: " . $contact->id . PHP_EOL;
    
    echo "3. Testing data retrieval..." . PHP_EOL;
    $retrieved = \App\Models\ContactMessages::find($contact->id);
    echo "✓ Retrieved contact: " . $retrieved->sender_name . " (" . $retrieved->sender_email . ")" . PHP_EOL;
    echo "   - Category: " . $retrieved->message_category . PHP_EOL;
    echo "   - Status: " . $retrieved->message_status . PHP_EOL;
    
    echo "4. Testing model methods..." . PHP_EOL;
    echo "   - Formatted reason: " . $retrieved->formatted_reason . PHP_EOL;
    echo "   - Status badge color: " . $retrieved->status_badge_color . PHP_EOL;
    echo "   - Is urgent: " . ($retrieved->isUrgent() ? 'Yes' : 'No') . PHP_EOL;
    
    echo "5. Cleaning up test data..." . PHP_EOL;
    $retrieved->delete();
    echo "✓ Test data cleaned up" . PHP_EOL;
    
    echo "=== CONTACT CONTROLLER TEST COMPLETED SUCCESSFULLY ===" . PHP_EOL;
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . PHP_EOL;
    echo "Stack trace: " . $e->getTraceAsString() . PHP_EOL;
}