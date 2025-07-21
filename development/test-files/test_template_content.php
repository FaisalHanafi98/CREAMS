<?php
require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\LetterTemplate;

echo "=== TESTING TEMPLATE CONTENT ===" . PHP_EOL;

$template = LetterTemplate::getActive();
if ($template) {
    echo "Active template found: " . $template->template_name . PHP_EOL;
    echo "Template ID: " . $template->id . PHP_EOL;
    echo "Template variables (raw): " . json_encode($template->template_variables) . PHP_EOL;
    echo "Header content accessor: '" . $template->header_content . "'" . PHP_EOL;
    echo "Footer content accessor: '" . $template->footer_content . "'" . PHP_EOL;
    echo "Template content: " . $template->template_content . PHP_EOL;
    
    // Test if the accessors are working
    $variables = $template->template_variables;
    echo "Variables is array: " . (is_array($variables) ? 'YES' : 'NO') . PHP_EOL;
    if (is_array($variables)) {
        echo "Header from variables: " . ($variables['header_content'] ?? 'NOT SET') . PHP_EOL;
        echo "Footer from variables: " . ($variables['footer_content'] ?? 'NOT SET') . PHP_EOL;
    }
} else {
    echo "No active template found" . PHP_EOL;
}