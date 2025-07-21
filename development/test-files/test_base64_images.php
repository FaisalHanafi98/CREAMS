<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Letter;
use App\Models\LetterTemplate;
use Barryvdh\DomPDF\Facade\Pdf;

echo "=== TESTING BASE64 ENCODED IMAGES ===\n\n";

// Get the specific letter and template
$letter = Letter::find(3);
$template = LetterTemplate::find($letter->template_id);

echo "Testing with letter ID: " . $letter->id . "\n";
echo "Template ID: " . $template->id . "\n";
echo "Template Name: " . $template->template_name . "\n\n";

$data = [
    'letter' => $letter,
    'template' => $template,
    'gd_available' => true
];

echo "Generating PDF with base64 encoded images...\n";

try {
    $pdf = Pdf::loadView('letters.pdf-template', $data);
    $pdf->setPaper('A4', 'portrait');
    
    $testPdfPath = public_path('letters/BASE64_TEST_' . time() . '.pdf');
    $pdf->save($testPdfPath);
    
    echo "Base64 PDF generated: " . $testPdfPath . "\n";
    echo "File size: " . filesize($testPdfPath) . " bytes\n";
    
    // Also generate the HTML to verify base64 encoding
    $html = view('letters.pdf-template', $data)->render();
    $htmlPath = public_path('letters/BASE64_HTML_' . time() . '.html');
    file_put_contents($htmlPath, $html);
    
    echo "HTML saved: " . $htmlPath . "\n";
    echo "HTML size: " . strlen($html) . " bytes\n";
    
    // Check if HTML contains base64 data
    $hasBase64 = strpos($html, 'data:image') !== false;
    echo "Contains base64 images: " . ($hasBase64 ? 'YES' : 'NO') . "\n";
    
    if ($hasBase64) {
        $base64Count = substr_count($html, 'data:image');
        echo "Number of base64 images: " . $base64Count . "\n";
    }
    
} catch (\Exception $e) {
    echo "Error generating PDF: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\n=== SUMMARY ===\n";
echo "The PDF should now contain:\n";
echo "- All text content (subject, content, recipient info)\n";
echo "- Header image (base64 encoded)\n";
echo "- Footer image (base64 encoded)\n";
echo "- Proper formatting and layout\n";

?>