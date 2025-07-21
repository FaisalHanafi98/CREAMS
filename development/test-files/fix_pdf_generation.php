<?php

require_once 'vendor/autoload.php';

// Initialize Laravel application
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Letter;
use App\Models\LetterTemplate;
use Barryvdh\DomPDF\Facade\Pdf;

try {
    echo "=== FIXING PDF GENERATION ===\n";
    
    // Get the latest letter
    $letter = Letter::where('letter_reference', 'LTR/2025/07/00321123')->first();
    $template = LetterTemplate::find($letter->template_id);
    
    echo "Testing different PDF generation approaches...\n";
    
    // Test 1: Try with different DomPDF options
    echo "\n=== TEST 1: Modified DomPDF Options ===\n";
    
    $data = [
        'letter' => $letter,
        'template' => $template,
        'gd_available' => false
    ];
    
    $html = view('letters.pdf-template', $data)->render();
    
    try {
        $pdf = Pdf::loadHTML($html);
        $pdf->setPaper('A4', 'portrait');
        
        // Try different options
        $pdf->setOptions([
            'isHtml5ParserEnabled' => false,  // Try disabling HTML5 parser
            'isPhpEnabled' => false,          // Disable PHP in PDF
            'defaultFont' => 'Arial',         // Try different font
            'dpi' => 150,                     // Higher DPI
            'fontHeightRatio' => 1.0,         // Different font height
            'isRemoteEnabled' => false,
            'debugKeepTemp' => true,          // Keep temp files for debugging
            'debugCss' => true,               // Debug CSS
            'debugLayout' => true,            // Debug layout
            'debugLayoutLines' => true,       // Debug layout lines
            'debugLayoutBlocks' => true,      // Debug layout blocks
            'debugLayoutInline' => true,      // Debug layout inline
            'debugLayoutPaddingBox' => true,  // Debug padding box
        ]);
        
        $pdfContent = $pdf->output();
        echo "PDF generated with debug options\n";
        echo "PDF size: " . strlen($pdfContent) . " bytes\n";
        
        $debugPdfPath = public_path('letters/DEBUG_FIXED_' . time() . '.pdf');
        file_put_contents($debugPdfPath, $pdfContent);
        echo "Debug PDF saved to: " . $debugPdfPath . "\n";
        
    } catch (Exception $e) {
        echo "PDF generation error: " . $e->getMessage() . "\n";
    }
    
    // Test 2: Try with simplified HTML
    echo "\n=== TEST 2: Simplified HTML ===\n";
    
    $simplifiedHtml = "<!DOCTYPE html>
<html>
<head>
    <meta charset='utf-8'>
    <title>Letter</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            margin: 20px;
            color: #000000;
        }
        .content {
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <h2>Reference: {$letter->letter_reference}</h2>
    <p><strong>Date:</strong> {$letter->letter_date->format('d F Y')}</p>
    <p><strong>To:</strong> {$letter->letter_data['recipient_name']}</p>
    <p><strong>Address:</strong> {$letter->letter_data['recipient_address']}</p>
    <p><strong>Subject:</strong> {$letter->letter_subject}</p>
    <div class='content'>
        <p>Dear {$letter->letter_data['recipient_name']},</p>
        <p>{$letter->letter_content}</p>
        <p>Yours sincerely,</p>
        <p><strong>{$letter->letter_data['generated_by_name']}</strong></p>
    </div>
</body>
</html>";
    
    try {
        $simplePdf = Pdf::loadHTML($simplifiedHtml);
        $simplePdf->setPaper('A4', 'portrait');
        $simplePdf->setOptions([
            'defaultFont' => 'DejaVu Sans',
            'isHtml5ParserEnabled' => true,
            'isPhpEnabled' => true,
        ]);
        
        $simplePdfContent = $simplePdf->output();
        echo "Simplified PDF generated\n";
        echo "Simplified PDF size: " . strlen($simplePdfContent) . " bytes\n";
        
        $simplePdfPath = public_path('letters/DEBUG_SIMPLIFIED_' . time() . '.pdf');
        file_put_contents($simplePdfPath, $simplePdfContent);
        echo "Simplified PDF saved to: " . $simplePdfPath . "\n";
        
        // Check if this PDF contains the text
        if (strpos($simplePdfContent, $letter->letter_subject) !== false) {
            echo "Simplified PDF contains subject: YES\n";
        } else {
            echo "Simplified PDF contains subject: NO\n";
        }
        
    } catch (Exception $e) {
        echo "Simplified PDF generation error: " . $e->getMessage() . "\n";
    }
    
    // Test 3: Check DomPDF logs
    echo "\n=== TEST 3: Check DomPDF Logs ===\n";
    
    $logPath = storage_path('logs/dompdf.log');
    if (file_exists($logPath)) {
        echo "DomPDF log exists\n";
        $logContent = file_get_contents($logPath);
        echo "Log content (last 1000 chars):\n";
        echo substr($logContent, -1000) . "\n";
    } else {
        echo "DomPDF log not found\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}