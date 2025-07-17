<?php
require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== TESTING LETTER TEMPLATE CONTROLLER SIMULATION ===" . PHP_EOL;

use App\Models\LetterTemplate;

try {
    // Simulate the controller data preparation
    $requestData = [
        'template_name' => 'Testing Letter Template',
        'header_content' => 'Additional Header Text Testing',
        'footer_content' => 'Footer Text TESTING',
    ];
    
    echo "1. Testing controller data preparation..." . PHP_EOL;
    
    // Build template content from header and footer content
    $templateContent = '';
    if (!empty($requestData['header_content'])) {
        $templateContent .= '<div class="header-content">' . $requestData['header_content'] . '</div>';
    }
    $templateContent .= '<div class="main-content">[CONTENT]</div>';
    if (!empty($requestData['footer_content'])) {
        $templateContent .= '<div class="footer-content">' . $requestData['footer_content'] . '</div>';
    }

    $templateData = [
        'template_name' => $requestData['template_name'],
        'template_content' => $templateContent ?: '<div class="main-content">[CONTENT]</div>',
        'template_type' => 'letter',
        'template_variables' => [
            'header_content' => $requestData['header_content'] ?? '',
            'footer_content' => $requestData['footer_content'] ?? ''
        ],
        'created_by' => 1,
        'is_active' => true,
    ];
    
    echo "2. Testing template data structure..." . PHP_EOL;
    foreach ($templateData as $key => $value) {
        if (is_array($value)) {
            echo "   - {$key}: " . json_encode($value) . PHP_EOL;
        } else {
            echo "   - {$key}: " . (strlen($value) > 50 ? substr($value, 0, 50) . "..." : $value) . PHP_EOL;
        }
    }
    
    echo "3. Testing database insertion..." . PHP_EOL;
    $template = LetterTemplate::create($templateData);
    echo "✓ Template created with ID: " . $template->id . PHP_EOL;
    
    echo "4. Testing data retrieval..." . PHP_EOL;
    $retrieved = LetterTemplate::find($template->id);
    echo "✓ Retrieved template: " . $retrieved->template_name . PHP_EOL;
    echo "   - Template type: " . $retrieved->template_type . PHP_EOL;
    echo "   - Is active: " . ($retrieved->is_active ? 'Yes' : 'No') . PHP_EOL;
    
    echo "5. Testing template activation..." . PHP_EOL;
    $activeTemplate = LetterTemplate::getActive();
    echo "✓ Active template: " . ($activeTemplate ? $activeTemplate->template_name : 'None found') . PHP_EOL;
    
    echo "6. Cleaning up test data..." . PHP_EOL;
    $retrieved->delete();
    echo "✓ Test data cleaned up" . PHP_EOL;
    
    echo "=== LETTER TEMPLATE CONTROLLER TEST COMPLETED SUCCESSFULLY ===" . PHP_EOL;
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . PHP_EOL;
    echo "Stack trace: " . $e->getTraceAsString() . PHP_EOL;
}