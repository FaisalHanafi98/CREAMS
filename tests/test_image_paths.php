<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\LetterTemplate;

echo "=== IMAGE PATH TESTING ===\n\n";

$template = LetterTemplate::find(11);
$headerImage = $template->template_variables['header_image'];
$footerImage = $template->template_variables['footer_image'];

echo "Template Variables:\n";
echo "- Header Image: " . $headerImage . "\n";
echo "- Footer Image: " . $footerImage . "\n\n";

echo "=== PATH VARIATIONS ===\n";

// Test different path combinations
$paths = [
    'storage_path' => storage_path('app/public/' . $headerImage),
    'public_path' => public_path('storage/' . $headerImage),
    'public_storage' => public_path() . '/storage/' . $headerImage,
    'relative_public' => 'storage/' . $headerImage,
    'base64_encoded' => 'data:image/jpeg;base64,' . base64_encode(file_get_contents(storage_path('app/public/' . $headerImage))),
];

foreach ($paths as $name => $path) {
    if ($name === 'base64_encoded') {
        echo "- {$name}: [base64 encoded - " . strlen($path) . " chars]\n";
    } else {
        echo "- {$name}: {$path}\n";
        echo "  Exists: " . (file_exists($path) ? 'YES' : 'NO') . "\n";
    }
}

echo "\n=== TESTING DOMPDF IMAGE COMPATIBILITY ===\n";

// Test which approach works with DomPDF
use Barryvdh\DomPDF\Facade\Pdf;

$testHtml = '
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Image Test</title>
</head>
<body>
    <h1>Image Path Test</h1>
    
    <h2>Method 1: Storage Path</h2>
    <img src="' . storage_path('app/public/' . $headerImage) . '" alt="Header1" style="max-width: 200px;">
    
    <h2>Method 2: Public Path</h2>
    <img src="' . public_path('storage/' . $headerImage) . '" alt="Header2" style="max-width: 200px;">
    
    <h2>Method 3: Base64 Encoded</h2>
    <img src="data:image/jpeg;base64,' . base64_encode(file_get_contents(storage_path('app/public/' . $headerImage))) . '" alt="Header3" style="max-width: 200px;">
    
    <p>This is test content to verify PDF generation.</p>
</body>
</html>';

try {
    $pdf = Pdf::loadHTML($testHtml);
    $pdf->setPaper('A4', 'portrait');
    $testPdfPath = public_path('letters/IMAGE_PATH_TEST_' . time() . '.pdf');
    $pdf->save($testPdfPath);
    
    echo "Image test PDF generated: " . $testPdfPath . "\n";
    echo "File size: " . filesize($testPdfPath) . " bytes\n";
    
} catch (\Exception $e) {
    echo "Error generating test PDF: " . $e->getMessage() . "\n";
}

?>