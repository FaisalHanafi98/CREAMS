<?php
require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== TESTING DASHBOARD FIXES ===" . PHP_EOL;

// Test AdminDashboardService
echo "Testing AdminDashboardService..." . PHP_EOL;
try {
    $adminService = new App\Services\Dashboard\AdminDashboardService();
    
    // Get admin user
    $admin = App\Models\Users::where('role', 'admin')->first();
    if (!$admin) {
        echo "❌ No admin user found" . PHP_EOL;
        exit;
    }
    
    echo "✅ Admin user found: {$admin->name}" . PHP_EOL;
    
    // Test dashboard data retrieval
    $dashboardData = $adminService->getDashboardData($admin->id);
    
    echo "✅ Dashboard data retrieved successfully" . PHP_EOL;
    echo "  - Total users: " . ($dashboardData['stats']['total_users'] ?? 'N/A') . PHP_EOL;
    echo "  - Total trainees: " . ($dashboardData['stats']['total_trainees'] ?? 'N/A') . PHP_EOL;
    echo "  - Total activities: " . ($dashboardData['stats']['total_activities'] ?? 'N/A') . PHP_EOL;
    echo "  - Pending volunteers: " . ($dashboardData['stats']['pending_volunteers'] ?? 'N/A') . PHP_EOL;
    echo "  - Unread messages: " . ($dashboardData['stats']['unread_messages'] ?? 'N/A') . PHP_EOL;
    
} catch (Exception $e) {
    echo "❌ AdminDashboardService error: " . $e->getMessage() . PHP_EOL;
}

// Test AjkDashboardService
echo PHP_EOL . "Testing AjkDashboardService..." . PHP_EOL;
try {
    $ajkService = new App\Services\Dashboard\AjkDashboardService(
        new App\Services\Asset\AssetManagementService()
    );
    
    // Get AJK user
    $ajk = App\Models\Users::where('role', 'ajk')->first();
    if (!$ajk) {
        echo "❌ No AJK user found" . PHP_EOL;
    } else {
        echo "✅ AJK user found: {$ajk->name}" . PHP_EOL;
        
        // Test dashboard data retrieval
        $dashboardData = $ajkService->getDashboardData($ajk->id);
        
        echo "✅ AJK Dashboard data retrieved successfully" . PHP_EOL;
        echo "  - Active volunteers: " . ($dashboardData['stats']['active_volunteers'] ?? 'N/A') . PHP_EOL;
        echo "  - Pending volunteers: " . ($dashboardData['stats']['pending_volunteers'] ?? 'N/A') . PHP_EOL;
        echo "  - Total assets: " . ($dashboardData['stats']['total_assets'] ?? 'N/A') . PHP_EOL;
        echo "  - Unread messages: " . ($dashboardData['stats']['unread_messages'] ?? 'N/A') . PHP_EOL;
    }
    
} catch (Exception $e) {
    echo "❌ AjkDashboardService error: " . $e->getMessage() . PHP_EOL;
}

// Test specific problematic queries directly
echo PHP_EOL . "Testing individual queries..." . PHP_EOL;

echo "Testing Volunteers with volunteer_status..." . PHP_EOL;
try {
    $pendingCount = App\Models\Volunteers::where('volunteer_status', 'pending')->count();
    echo "✅ Pending volunteers: {$pendingCount}" . PHP_EOL;
} catch (Exception $e) {
    echo "❌ Volunteers query error: " . $e->getMessage() . PHP_EOL;
}

echo "Testing ContactMessages with message_status..." . PHP_EOL;
try {
    $unreadCount = App\Models\ContactMessages::where('message_status', 'unread')->count();
    echo "✅ Unread messages: {$unreadCount}" . PHP_EOL;
} catch (Exception $e) {
    echo "❌ ContactMessages query error: " . $e->getMessage() . PHP_EOL;
}

echo "Testing Centres with centre_status..." . PHP_EOL;
try {
    $activeCentres = App\Models\Centres::where('centre_status', 'active')->count();
    echo "✅ Active centres: {$activeCentres}" . PHP_EOL;
} catch (Exception $e) {
    echo "❌ Centres query error: " . $e->getMessage() . PHP_EOL;
}

echo PHP_EOL . "=== END DASHBOARD FIXES TEST ===" . PHP_EOL;