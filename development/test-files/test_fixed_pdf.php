<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Letter;
use App\Models\LetterTemplate;
use Barryvdh\DomPDF\Facade\Pdf;

echo "=== TESTING FIXED PDF GENERATION ===\n\n";

// Get the specific letter
$letter = Letter::find(3);
$template = LetterTemplate::find($letter->template_id);

$data = [
    'letter' => $letter,
    'template' => $template,
    'gd_available' => true
];

echo "Generating PDF with fixed image paths...\n";

try {
    $pdf = Pdf::loadView('letters.pdf-template', $data);
    $pdf->setPaper('A4', 'portrait');
    
    $testPdfPath = public_path('letters/TEST_FIXED_' . time() . '.pdf');
    $pdf->save($testPdfPath);
    
    echo "Fixed PDF generated: " . $testPdfPath . "\n";
    echo "File size: " . filesize($testPdfPath) . " bytes\n";
    
    // Also test the HTML output
    $html = view('letters.pdf-template', $data)->render();
    $htmlPath = public_path('letters/FIXED_TEMPLATE_' . time() . '.html');
    file_put_contents($htmlPath, $html);
    
    echo "HTML saved to: " . $htmlPath . "\n";
    echo "HTML contains content: " . (strpos($html, 'dsfdsf') !== false ? 'YES' : 'NO') . "\n";
    
} catch (\Exception $e) {
    echo "Error generating PDF: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

?>