<?php
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

// Test staff registration page access
try {
    echo "Testing staff registration page...\n";
    
    $request = Illuminate\Http\Request::create('/staffs/register', 'GET');
    $response = $app->handle($request);
    
    echo "Status: " . $response->getStatusCode() . "\n";
    
    if ($response->getStatusCode() >= 400) {
        echo "Error accessing staff registration!\n";
        echo "Content: " . substr($response->getContent(), 0, 500) . "\n";
    } else {
        echo "Staff registration page loads successfully!\n";
        
        // Check if the response contains the form
        $content = $response->getContent();
        if (strpos($content, 'registration') !== false || strpos($content, 'form') !== false) {
            echo "Registration form found in response!\n";
        } else {
            echo "No registration form found in response.\n";
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}

echo "\n---\n";

// Test if we can submit a staff registration form
try {
    echo "Testing staff registration form submission...\n";
    
    $formData = [
        'iium_id' => 'TEST1234',
        'role' => 'teacher',
        'name' => 'Test Teacher',
        'email' => 'test.teacher@iium.edu.my',
        'password' => 'test123',
        'password_confirmation' => 'test123',
        'centre_id' => '01',
        'centre_location' => 'Gombak',
        '_token' => 'test-token'
    ];
    
    $request = Illuminate\Http\Request::create('/auth/save', 'POST', $formData);
    $response = $app->handle($request);
    
    echo "Form submission status: " . $response->getStatusCode() . "\n";
    
    if ($response->getStatusCode() >= 400) {
        echo "Form submission failed!\n";
        echo "Error content: " . substr($response->getContent(), 0, 500) . "\n";
    } else {
        echo "Form submission successful or redirected (status: " . $response->getStatusCode() . ")!\n";
    }
    
} catch (Exception $e) {
    echo "Form submission error: " . $e->getMessage() . "\n";
}