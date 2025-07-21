<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Letter;
use App\Models\LetterTemplate;

echo "=== PDF TEMPLATE DEBUG ===\n\n";

// Get the specific letter
$letter = Letter::find(3);
$template = LetterTemplate::find($letter->template_id);

$data = [
    'letter' => $letter,
    'template' => $template,
    'gd_available' => true
];

echo "Rendering template to HTML to check content...\n";

// Render the actual template to HTML to see what's being generated
$html = view('letters.pdf-template', $data)->render();

// Save the HTML to a file for inspection
$htmlPath = public_path('letters/DEBUG_TEMPLATE_' . time() . '.html');
file_put_contents($htmlPath, $html);

echo "HTML output saved to: " . $htmlPath . "\n";
echo "HTML size: " . strlen($html) . " bytes\n";

// Check if the HTML contains our expected content
echo "\n=== CONTENT ANALYSIS ===\n";
echo "Contains subject 'dsffd': " . (strpos($html, 'dsffd') !== false ? 'YES' : 'NO') . "\n";
echo "Contains content 'dsfdsf': " . (strpos($html, 'dsfdsf') !== false ? 'YES' : 'NO') . "\n";
echo "Contains reference 'LTR/2025/07/0002': " . (strpos($html, 'LTR/2025/07/0002') !== false ? 'YES' : 'NO') . "\n";
echo "Contains recipient name 'dsfdsf': " . (strpos($html, 'dsfdsf') !== false ? 'YES' : 'NO') . "\n";

// Check for common issues
echo "\n=== POTENTIAL ISSUES ===\n";
echo "Contains 'null' or 'NULL': " . (stripos($html, 'null') !== false ? 'YES' : 'NO') . "\n";
echo "Contains error messages: " . (stripos($html, 'error') !== false ? 'YES' : 'NO') . "\n";
echo "Contains 'undefined': " . (stripos($html, 'undefined') !== false ? 'YES' : 'NO') . "\n";

// Show first 500 characters of the HTML
echo "\n=== FIRST 500 CHARACTERS OF HTML ===\n";
echo substr($html, 0, 500) . "...\n";

?>