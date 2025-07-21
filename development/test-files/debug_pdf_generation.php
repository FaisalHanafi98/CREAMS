<?php

require_once 'vendor/autoload.php';

// Initialize Laravel application
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Letter;
use App\Models\LetterTemplate;
use Barryvdh\DomPDF\Facade\Pdf;

try {
    echo "=== DEBUGGING PDF GENERATION ===\n";
    
    // Get the latest letter
    $letter = Letter::where('letter_reference', 'LTR/2025/07/00321123')->first();
    $template = LetterTemplate::find($letter->template_id);
    
    echo "Testing PDF generation for letter ID: " . $letter->id . "\n";
    echo "Subject: " . $letter->letter_subject . "\n";
    echo "Content: " . $letter->letter_content . "\n";
    
    // Test 1: Generate HTML
    echo "\n=== TEST 1: HTML Generation ===\n";
    
    $data = [
        'letter' => $letter,
        'template' => $template,
        'gd_available' => false
    ];
    
    $html = view('letters.pdf-template', $data)->render();
    echo "HTML generated successfully\n";
    echo "HTML length: " . strlen($html) . " chars\n";
    
    // Save HTML to file for inspection
    $htmlPath = public_path('letters/DEBUG_HTML_' . time() . '.html');
    file_put_contents($htmlPath, $html);
    echo "HTML saved to: " . $htmlPath . "\n";
    
    // Check if HTML contains the data
    $contains = [
        'subject' => strpos($html, $letter->letter_subject) !== false,
        'content' => strpos($html, $letter->letter_content) !== false,
        'recipient' => strpos($html, $letter->letter_data['recipient_name']) !== false,
        'reference' => strpos($html, $letter->letter_reference) !== false,
    ];
    
    echo "HTML contains:\n";
    foreach ($contains as $key => $found) {
        echo "  - $key: " . ($found ? 'YES' : 'NO') . "\n";
    }
    
    // Test 2: Generate PDF
    echo "\n=== TEST 2: PDF Generation ===\n";
    
    try {
        $pdf = Pdf::loadView('letters.pdf-template', $data);
        $pdf->setPaper('A4', 'portrait');
        
        // Configure PDF options
        $pdf->setOptions([
            'isHtml5ParserEnabled' => true,
            'isPhpEnabled' => true,
            'defaultFont' => 'DejaVu Sans',
            'dpi' => 96,
            'fontHeightRatio' => 1.1,
            'isRemoteEnabled' => false,
            'chroot' => public_path(),
            'logOutputFile' => storage_path('logs/dompdf.log'),
            'tempDir' => storage_path('app/temp'),
            'enableCssFloat' => true,
            'enableHtml5Parser' => true
        ]);
        
        $pdfContent = $pdf->output();
        echo "PDF generated successfully\n";
        echo "PDF size: " . strlen($pdfContent) . " bytes\n";
        
        // Save PDF for inspection
        $pdfPath = public_path('letters/DEBUG_PDF_' . time() . '.pdf');
        file_put_contents($pdfPath, $pdfContent);
        echo "PDF saved to: " . $pdfPath . "\n";
        
        // Try to extract text from PDF (basic check)
        if (strpos($pdfContent, $letter->letter_subject) !== false) {
            echo "PDF contains subject: YES\n";
        } else {
            echo "PDF contains subject: NO\n";
        }
        
        if (strpos($pdfContent, $letter->letter_content) !== false) {
            echo "PDF contains content: YES\n";
        } else {
            echo "PDF contains content: NO\n";
        }
        
    } catch (Exception $e) {
        echo "PDF generation error: " . $e->getMessage() . "\n";
        echo "Trace: " . $e->getTraceAsString() . "\n";
    }
    
    // Test 3: Simple PDF without template
    echo "\n=== TEST 3: Simple PDF without template ===\n";
    
    $simpleHtml = "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='utf-8'>
        <title>Simple Test</title>
    </head>
    <body>
        <h1>Simple Test PDF</h1>
        <p>Subject: {$letter->letter_subject}</p>
        <p>Content: {$letter->letter_content}</p>
        <p>Recipient: {$letter->letter_data['recipient_name']}</p>
    </body>
    </html>";
    
    try {
        $simplePdf = Pdf::loadHTML($simpleHtml);
        $simplePdf->setPaper('A4', 'portrait');
        $simplePdfContent = $simplePdf->output();
        
        echo "Simple PDF generated successfully\n";
        echo "Simple PDF size: " . strlen($simplePdfContent) . " bytes\n";
        
        $simplePdfPath = public_path('letters/DEBUG_SIMPLE_' . time() . '.pdf');
        file_put_contents($simplePdfPath, $simplePdfContent);
        echo "Simple PDF saved to: " . $simplePdfPath . "\n";
        
        // Check if simple PDF contains the data
        if (strpos($simplePdfContent, $letter->letter_subject) !== false) {
            echo "Simple PDF contains subject: YES\n";
        } else {
            echo "Simple PDF contains subject: NO\n";
        }
        
    } catch (Exception $e) {
        echo "Simple PDF generation error: " . $e->getMessage() . "\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}