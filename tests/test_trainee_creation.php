<?php
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

// Start session to simulate login
session_start();

// Simulate admin user login for trainee creation
$_SESSION['id'] = 1;
$_SESSION['role'] = 'admin';
$_SESSION['name'] = 'Goh Ai Ling';
$_SESSION['centre_id'] = '01';

// Test trainee creation page access
try {
    echo "Testing trainee creation page...\n";
    
    $request = Illuminate\Http\Request::create('/trainees/create', 'GET');
    $response = $app->handle($request);
    
    echo "Status: " . $response->getStatusCode() . "\n";
    
    if ($response->getStatusCode() >= 400) {
        echo "Error accessing trainee creation!\n";
        echo "Content: " . substr($response->getContent(), 0, 500) . "\n";
    } else {
        echo "Trainee creation page loads successfully!\n";
        
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
}

echo "\n---\n";

// Test trainee registration form submission
try {
    echo "Testing trainee registration form submission...\n";
    
    $formData = [
        'trainee_id' => 'T001234',
        'ic_number' => '001122334455',
        'first_name' => 'Ahmad',
        'last_name' => 'Rahman',
        'date_of_birth' => '2015-01-01',
        'gender' => 'male',
        'contact_number' => '0123456789',
        'email' => 'test.trainee@email.com',
        'address' => 'Test Address',
        'centre_id' => '01',
        '_token' => 'test-token'
    ];
    
    $request = Illuminate\Http\Request::create('/trainees', 'POST', $formData);
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
    echo "Trace: " . $e->getTraceAsString() . "\n";
}

echo "\n---\n";

// Check if TraineeRegistrationController exists
try {
    echo "Checking TraineeRegistrationController...\n";
    if (class_exists('App\Http\Controllers\Trainee\TraineeRegistrationController')) {
        echo "TraineeRegistrationController exists!\n";
        
        $controller = new App\Http\Controllers\Trainee\TraineeRegistrationController();
        echo "Controller instantiated successfully!\n";
    } else {
        echo "TraineeRegistrationController does NOT exist!\n";
    }
} catch (Exception $e) {
    echo "Controller check error: " . $e->getMessage() . "\n";
}