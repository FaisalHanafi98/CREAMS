<?php
/**
 * Form Audit Script for CREAMS
 * This script will check form-route connectivity and validation
 */

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Define critical forms to audit
$critical_forms = [
    // Authentication
    'login' => [
        'view' => 'auth.login',
        'route' => 'auth.check',
        'method' => 'POST'
    ],
    'register' => [
        'view' => 'auth.register', 
        'route' => 'auth.save',
        'method' => 'POST'
    ],
    
    // User Management
    'trainee_create' => [
        'route' => 'trainees.store',
        'method' => 'POST'
    ],
    
    // Activity Management
    'activity_create' => [
        'route' => 'activities.store', 
        'method' => 'POST'
    ],
    'session_create' => [
        'route' => 'activities.sessions.create',
        'method' => 'POST'
    ],
    
    // Attendance
    'attendance_mark' => [
        'route' => 'activities.activities.attendance.store',
        'method' => 'POST'
    ]
];

echo "CREAMS Form Audit Report\n";
echo "========================\n\n";

// Check if routes exist
$router = app('router');
$routes = $router->getRoutes();

foreach ($critical_forms as $form_name => $form_data) {
    echo "Checking: {$form_name}\n";
    
    try {
        $route = $routes->getByName($form_data['route']);
        if ($route) {
            echo "  ✓ Route exists: {$form_data['route']}\n";
            echo "  ✓ Method: " . implode('|', $route->methods()) . "\n";
            echo "  ✓ URI: {$route->uri()}\n";
        } else {
            echo "  ✗ Route NOT FOUND: {$form_data['route']}\n";
        }
    } catch (Exception $e) {
        echo "  ✗ Error checking route: " . $e->getMessage() . "\n";
    }
    
    echo "\n";
}

echo "Route audit completed.\n";