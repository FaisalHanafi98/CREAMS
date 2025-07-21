<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Letter;
use App\Models\LetterTemplate;
use Barryvdh\DomPDF\Facade\Pdf;

echo "=== PDF WITHOUT IMAGES TEST ===\n\n";

// Get the specific letter
$letter = Letter::find(3);
$template = LetterTemplate::find($letter->template_id);

// Create a modified template data without images
$modifiedTemplate = clone $template;
$modifiedTemplate->template_variables = [
    'header_content' => $template->template_variables['header_content'] ?? '',
    'footer_content' => $template->template_variables['footer_content'] ?? '',
    'header_image' => null,  // Remove image
    'footer_image' => null   // Remove image
];

$data = [
    'letter' => $letter,
    'template' => $modifiedTemplate,
    'gd_available' => true
];

echo "Generating PDF without images...\n";

try {
    $pdf = Pdf::loadView('letters.pdf-template', $data);
    $pdf->setPaper('A4', 'portrait');
    
    $testPdfPath = public_path('letters/TEST_NO_IMAGES_' . time() . '.pdf');
    $pdf->save($testPdfPath);
    
    echo "PDF without images generated: " . $testPdfPath . "\n";
    echo "File size: " . filesize($testPdfPath) . " bytes\n";
    
} catch (\Exception $e) {
    echo "Error generating PDF: " . $e->getMessage() . "\n";
}

?>