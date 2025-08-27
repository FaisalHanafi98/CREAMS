<?php
require_once 'vendor/autoload.php';

use Illuminate\Http\Request;

// Simulate a test admin session
$_SESSION = [];
session_start();
$_SESSION['id'] = 1;
$_SESSION['role'] = 'admin';
$_SESSION['centre_id'] = '01';
$_SESSION['name'] = 'Test Admin';

// Include Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Create a mock request to the dashboard
$request = Request::create('/dashboard', 'GET');
$response = $kernel->handle($request);

// Output the response
echo "Response Status: " . $response->getStatusCode() . "\n";
if ($response->getStatusCode() === 200) {
    echo "Dashboard loaded successfully!\n";
} else {
    echo "Response: " . $response->getContent() . "\n";
}