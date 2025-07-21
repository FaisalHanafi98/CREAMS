<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Letter;
use App\Models\LetterTemplate;

echo "=== LETTER PDF DEBUG TEST ===\n\n";

// Get the specific letter
$letter = Letter::find(3);
$template = LetterTemplate::find($letter->template_id);

echo "Letter Data:\n";
echo "- ID: " . $letter->id . "\n";
echo "- Reference: " . $letter->letter_reference . "\n";
echo "- Subject: " . $letter->letter_subject . "\n";  
echo "- Content: " . $letter->letter_content . "\n";
echo "- Letter Data: " . json_encode($letter->letter_data) . "\n";
echo "- Template ID: " . $letter->template_id . "\n\n";

echo "Template Data:\n";
if ($template) {
    echo "- Template Name: " . $template->template_name . "\n";
    echo "- Template Variables: " . json_encode($template->template_variables) . "\n";
} else {
    echo "- Template not found!\n";
}

echo "\n=== TESTING PDF TEMPLATE DATA ACCESS ===\n";

// Test the exact data that would be passed to the PDF template
$data = [
    'letter' => $letter,
    'template' => $template,
    'gd_available' => true
];

// Simulate what the PDF template is trying to access
echo "Testing PDF template data access:\n";
echo "- letter_subject: " . ($letter->letter_subject ?? 'NULL') . "\n";
echo "- letter_content: " . ($letter->letter_content ?? 'NULL') . "\n";
echo "- letter_reference: " . ($letter->letter_reference ?? 'NULL') . "\n";
echo "- letter_data array: " . (is_array($letter->letter_data) ? 'YES' : 'NO') . "\n";

if (is_array($letter->letter_data)) {
    echo "- recipient_name: " . ($letter->letter_data['recipient_name'] ?? 'NULL') . "\n";
    echo "- recipient_address: " . ($letter->letter_data['recipient_address'] ?? 'NULL') . "\n";
    echo "- generated_by_name: " . ($letter->letter_data['generated_by_name'] ?? 'NULL') . "\n";
    echo "- generated_by_position: " . ($letter->letter_data['generated_by_position'] ?? 'NULL') . "\n";
}

echo "\n=== TEMPLATE VARIABLES ACCESS ===\n";
if ($template && $template->template_variables) {
    echo "- Template variables is array: " . (is_array($template->template_variables) ? 'YES' : 'NO') . "\n";
    if (is_array($template->template_variables)) {
        echo "- header_image: " . ($template->template_variables['header_image'] ?? 'NULL') . "\n";
        echo "- header_content: " . ($template->template_variables['header_content'] ?? 'NULL') . "\n";
        echo "- footer_image: " . ($template->template_variables['footer_image'] ?? 'NULL') . "\n";
        echo "- footer_content: " . ($template->template_variables['footer_content'] ?? 'NULL') . "\n";
    }
}

echo "\n=== CONCLUSION ===\n";
echo "All data appears to be accessible. The issue may be:\n";
echo "1. DomPDF rendering issue\n";
echo "2. Template file path issue\n";
echo "3. CSS/styling preventing content display\n";
echo "4. Character encoding issues\n";

?>