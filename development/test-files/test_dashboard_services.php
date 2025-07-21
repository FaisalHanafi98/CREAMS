<?php
require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== TESTING DASHBOARD SERVICES ===" . PHP_EOL;

use App\Services\Dashboard\AdminDashboardService;
use App\Services\Dashboard\AjkDashboardService;
use App\Services\Asset\AssetManagementService;

try {
    echo "1. Testing AdminDashboardService..." . PHP_EOL;
    $adminService = new AdminDashboardService();
    $adminData = $adminService->getDashboardData(1);
    echo "✓ AdminDashboardService working - got " . count($adminData) . " data items" . PHP_EOL;
    
    echo "2. Testing AjkDashboardService..." . PHP_EOL;
    $assetService = new AssetManagementService();
    $ajkService = new AjkDashboardService($assetService);
    $ajkData = $ajkService->getDashboardData(1);
    echo "✓ AjkDashboardService working - got " . count($ajkData) . " data items" . PHP_EOL;
    
    echo "3. Testing AssetManagementService..." . PHP_EOL;
    $assetService = new AssetManagementService();
    $assetData = $assetService->getDashboardData(1);
    echo "✓ AssetManagementService working - got " . count($assetData) . " data items" . PHP_EOL;
    
    echo "4. Testing specific dashboard queries..." . PHP_EOL;
    
    // Test today's sessions query
    $todaysSessions = \App\Models\ActivitySession::whereDate('session_date', today())
        ->orderBy('session_start_time', 'asc')
        ->get();
    echo "✓ Today's sessions query working - found " . $todaysSessions->count() . " sessions" . PHP_EOL;
    
    // Test active sessions query
    $activeSessions = \App\Models\ActivitySession::where('session_status', 'active')->count();
    echo "✓ Active sessions query working - found " . $activeSessions . " active sessions" . PHP_EOL;
    
    echo "=== DASHBOARD SERVICES TEST COMPLETED SUCCESSFULLY ===" . PHP_EOL;
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . PHP_EOL;
    echo "Stack trace: " . $e->getTraceAsString() . PHP_EOL;
}