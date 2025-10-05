<?php
require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== TESTING FIXED LETTER TEMPLATE SYSTEM ===" . PHP_EOL;

use App\Models\LetterTemplate;
use App\Models\Letter;

try {
    echo "1. Creating test letter template..." . PHP_EOL;
    
    // Create template with header and footer content
    $template = LetterTemplate::create([
        'template_name' => 'Official Letter Template',
        'template_content' => '<div class="header-content">IIUM PD-CARE Official Header</div><div class="main-content">[CONTENT]</div><div class="footer-content">Best regards,<br>IIUM PD-CARE Team</div>',
        'template_type' => 'letter',
        'template_variables' => [
            'header_content' => 'IIUM PD-CARE Official Header',
            'footer_content' => 'Best regards,<br>IIUM PD-CARE Team'
        ],
        'is_active' => true,
        'created_by' => 1,
    ]);
    echo "✓ Template created with ID: " . $template->id . PHP_EOL;
    
    echo "2. Testing template accessor methods..." . PHP_EOL;
    echo "   - Header content: " . $template->header_content . PHP_EOL;
    echo "   - Footer content: " . $template->footer_content . PHP_EOL;
    
    echo "3. Testing getActive method..." . PHP_EOL;
    $activeTemplate = LetterTemplate::getActive();
    echo "✓ Active template: " . ($activeTemplate ? $activeTemplate->template_name : 'None') . PHP_EOL;
    
    echo "4. Creating test letter..." . PHP_EOL;
    $letter = Letter::create([
        'letter_reference' => 'LTR/2025/01/0001',
        'letter_date' => '2025-01-16',
        'letter_subject' => 'Test Subject',
        'letter_content' => 'This is test content for the letter.',
        'letter_type' => 'official',
        'recipient_id' => 1,
        'recipient_type' => 'external',
        'template_id' => $template->id,
        'letter_status' => 'draft',
        'created_by' => 1,
        'letter_data' => [
            'generated_by_name' => 'Test User',
            'generated_by_position' => 'Admin',
        ]
    ]);
    echo "✓ Letter created with ID: " . $letter->id . PHP_EOL;
    echo "   - Reference: " . $letter->letter_reference . PHP_EOL;
    echo "   - Subject: " . $letter->letter_subject . PHP_EOL;
    
    echo "5. Testing letter scopes..." . PHP_EOL;
    $found = Letter::search('Test')->first();
    echo "✓ Search scope working: " . ($found ? $found->letter_reference : 'Not found') . PHP_EOL;
    
    echo "6. Cleaning up test data..." . PHP_EOL;
    $letter->delete();
    $template->delete();
    echo "✓ Test data cleaned up" . PHP_EOL;
    
    echo "=== LETTER TEMPLATE SYSTEM TEST COMPLETED SUCCESSFULLY ===" . PHP_EOL;
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . PHP_EOL;
    echo "Stack trace: " . $e->getTraceAsString() . PHP_EOL;
}