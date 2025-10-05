<?php

require_once __DIR__ . '/bootstrap/app.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== CREAMS SYSTEM FIXES VALIDATION ===\n\n";

try {
    // Test 1: Trainee activities relationship (Fix #1)
    echo "1. Testing Trainee Activity Relationship:\n";
    $trainee = App\Models\Trainee::first();
    if ($trainee) {
        echo "   ✓ Trainee found: {$trainee->trainee_first_name}\n";
        $activities = $trainee->activities()->limit(1)->get();
        if ($activities->count() > 0) {
            $activity = $activities->first();
            echo "   ✓ Activity relationship working\n";
            if ($activity->pivot && property_exists($activity->pivot, 'enrollment_status')) {
                echo "   ✓ Pivot enrollment_status field accessible\n";
            } else {
                echo "   ✗ Pivot enrollment_status field not accessible\n";
            }
        } else {
            echo "   - No activities found for this trainee\n";
        }
    } else {
        echo "   - No trainees found in database\n";
    }
    
    // Test 2: ActivitySession scheduled_date field (Fix #2)
    echo "\n2. Testing ActivitySession scheduled_date field:\n";
    $session = App\Models\ActivitySession::first();
    if ($session) {
        echo "   ✓ Activity session found\n";
        if (Illuminate\Support\Facades\Schema::hasColumn('activity_sessions', 'scheduled_date')) {
            echo "   ✓ scheduled_date column exists\n";
            if ($session->scheduled_date) {
                echo "   ✓ scheduled_date has value: {$session->scheduled_date}\n";
            } else {
                echo "   - scheduled_date is null\n";
            }
        } else {
            echo "   ✗ scheduled_date column missing\n";
        }
    } else {
        echo "   - No activity sessions found\n";
    }
    
    // Test 3: Routes working (Fix #3)
    echo "\n3. Testing Route Registration:\n";
    $routes = [
        'staffs.home',
        'trainees.home', 
        'activities.home',
        'activities.categories'
    ];
    
    foreach ($routes as $routeName) {
        try {
            $url = route($routeName);
            echo "   ✓ Route '{$routeName}' → {$url}\n";
        } catch (Exception $e) {
            echo "   ✗ Route '{$routeName}' failed: {$e->getMessage()}\n";
        }
    }
    
    // Test 4: Asset-Centre relationship (Fix #4)
    echo "\n4. Testing Asset-Centre Relationship:\n";
    $centre = App\Models\Centre::first();
    if ($centre) {
        echo "   ✓ Centre found: {$centre->centre_name} (ID: {$centre->centre_id})\n";
        try {
            $assetsCount = $centre->assets()->count();
            echo "   ✓ Centre-Asset relationship working, count: {$assetsCount}\n";
        } catch (Exception $e) {
            echo "   ✗ Centre-Asset relationship error: {$e->getMessage()}\n";
        }
    } else {
        echo "   - No centres found\n";
    }
    
    // Test 5: AssetMaintenance priority field (Fix #5)
    echo "\n5. Testing AssetMaintenance priority field:\n";
    if (Illuminate\Support\Facades\Schema::hasColumn('asset_maintenance', 'priority')) {
        echo "   ✓ priority column exists in asset_maintenance table\n";
        
        $maintenance = App\Models\AssetMaintenance::first();
        if ($maintenance) {
            echo "   ✓ AssetMaintenance record found\n";
            echo "   ✓ Priority value: " . ($maintenance->priority ?: 'null') . "\n";
        } else {
            echo "   - No maintenance records found\n";
        }
    } else {
        echo "   ✗ priority column missing from asset_maintenance table\n";
    }
    
    echo "\n=== VALIDATION COMPLETE ===\n";
    
} catch (Exception $e) {
    echo "ERROR: {$e->getMessage()}\n";
    echo "Trace: {$e->getTraceAsString()}\n";
}