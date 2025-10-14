<?php

/**
 * Debug script to test letter generation manually
 * Run from project root: php debug-letter-generation.php
 */

require_once __DIR__ . '/vendor/autoload.php';

use App\Http\Controllers\Profile\LetterTemplateController;
use Illuminate\Http\Request;

echo "Letter Generation Debug Script\n";
echo "==============================\n\n";

try {
    // Initialize Laravel
    $app = require_once __DIR__ . '/bootstrap/app.php';
    $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
    
    echo "✓ Laravel bootstrapped successfully\n";
    
    // Check if Letter model exists and works
    $letterCount = \App\Models\Letter::count();
    echo "✓ Letter model accessible - found {$letterCount} existing letters\n";
    
    // Check if LetterTemplate model exists
    $templateCount = \App\Models\LetterTemplate::count();
    echo "✓ LetterTemplate model accessible - found {$templateCount} existing templates\n";
    
    // Check active templates
    $activeTemplates = \App\Models\LetterTemplate::where('is_active', true)->count();
    echo "✓ Active templates: {$activeTemplates}\n";
    
    // Check route registration
    echo "\nChecking routes...\n";
    $routes = app('router')->getRoutes();
    $letterRoutes = [];
    foreach ($routes as $route) {
        $uri = $route->uri();
        if (strpos($uri, 'letter') !== false) {
            $letterRoutes[] = $route->methods()[0] . ' ' . $uri;
        }
    }
    
    echo "Found " . count($letterRoutes) . " letter-related routes:\n";
    foreach (array_slice($letterRoutes, 0, 5) as $route) {
        echo "  - {$route}\n";
    }
    
    // Test validation rules
    echo "\nTesting validation...\n";
    $testData = [
        'letter_date' => '2025-01-07',
        'subject' => 'Test Letter',
        'content' => 'This is test content',
        'recipient_name' => 'John Doe',
        'recipient_address' => 'Test Address'
    ];
    
    $validator = validator($testData, [
        'letter_name' => 'nullable|string|max:255',
        'letter_date' => 'required|date',
        'subject' => 'required|string|max:255',
        'content' => 'required|string',
        'recipient_name' => 'required|string|max:255',
        'recipient_address' => 'nullable|string',
    ]);
    
    if ($validator->fails()) {
        echo "✗ Validation failed:\n";
        foreach ($validator->errors()->all() as $error) {
            echo "  - {$error}\n";
        }
    } else {
        echo "✓ Validation passed for test data\n";
    }
    
    echo "\n✓ Basic checks completed successfully\n";
    echo "\nTo test letter generation:\n";
    echo "1. Access the letter generation form\n";
    echo "2. Fill in the required fields\n";
    echo "3. Click Generate Letter\n";
    echo "4. Check browser console and network tab for errors\n";
    echo "5. Check Laravel logs for detailed error information\n";
    
} catch (Exception $e) {
    echo "✗ Error during testing: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

?>