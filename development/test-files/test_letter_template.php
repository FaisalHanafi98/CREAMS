<?php
require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== TESTING LETTER TEMPLATE CREATION ===" . PHP_EOL;

use App\Models\LetterTemplate;

try {
    echo "1. Testing LetterTemplate model creation..." . PHP_EOL;
    
    $testData = [
        'template_name' => 'Test Letter Template',
        'template_content' => '<div class="header-content">Header Text</div><div class="main-content">[CONTENT]</div><div class="footer-content">Footer Text</div>',
        'template_type' => 'letter',
        'template_variables' => [
            'header_content' => 'Header Text',
            'footer_content' => 'Footer Text'
        ],
        'is_active' => true,
        'created_by' => 1,
    ];
    
    $template = LetterTemplate::create($testData);
    echo "✓ LetterTemplate created successfully with ID: " . $template->id . PHP_EOL;
    
    echo "2. Testing LetterTemplate retrieval..." . PHP_EOL;
    $retrieved = LetterTemplate::find($template->id);
    echo "✓ Retrieved template: " . $retrieved->template_name . PHP_EOL;
    
    echo "3. Testing column access..." . PHP_EOL;
    echo "   - template_name: " . $retrieved->template_name . PHP_EOL;
    echo "   - template_type: " . $retrieved->template_type . PHP_EOL;
    echo "   - template_content: " . substr($retrieved->template_content, 0, 50) . "..." . PHP_EOL;
    echo "   - is_active: " . ($retrieved->is_active ? 'true' : 'false') . PHP_EOL;
    echo "   - created_by: " . $retrieved->created_by . PHP_EOL;
    
    echo "4. Testing template variables..." . PHP_EOL;
    $variables = $retrieved->template_variables;
    if (is_array($variables)) {
        echo "   - header_content: " . ($variables['header_content'] ?? 'none') . PHP_EOL;
        echo "   - footer_content: " . ($variables['footer_content'] ?? 'none') . PHP_EOL;
    } else {
        echo "   - template_variables: " . $variables . PHP_EOL;
    }
    
    echo "5. Testing getActive method..." . PHP_EOL;
    $active = LetterTemplate::getActive();
    echo "✓ Active template: " . ($active ? $active->template_name : 'none') . PHP_EOL;
    
    echo "6. Cleaning up test data..." . PHP_EOL;
    $retrieved->delete();
    echo "✓ Test data cleaned up" . PHP_EOL;
    
    echo "=== LETTER TEMPLATE TEST COMPLETED SUCCESSFULLY ===" . PHP_EOL;
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . PHP_EOL;
    echo "Stack trace: " . $e->getTraceAsString() . PHP_EOL;
}