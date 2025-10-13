# CREAMS System Fixes Validation Commands

# Test 1: Trainee Activity Relationship (Fix #1)
echo "=== Testing Trainee Activity Relationship ==="
$trainee = App\Models\Trainee::first();
if ($trainee) {
    echo "Trainee found: " . $trainee->trainee_first_name;
    $activities = $trainee->activities()->limit(1)->get();
    if ($activities->count() > 0) {
        $activity = $activities->first();
        echo "Activity relationship working";
        if ($activity->pivot && property_exists($activity->pivot, 'enrollment_status')) {
            echo "✓ Pivot enrollment_status field accessible";
        } else {
            echo "✗ Pivot enrollment_status field not accessible";
        }
    }
}

# Test 2: ActivitySession scheduled_date field (Fix #2)  
echo "=== Testing ActivitySession scheduled_date field ==="
$session = App\Models\ActivitySession::first();
if ($session) {
    echo "Activity session found";
    if (Schema::hasColumn('activity_sessions', 'scheduled_date')) {
        echo "✓ scheduled_date column exists";
        if ($session->scheduled_date) {
            echo "✓ scheduled_date has value: " . $session->scheduled_date;
        }
    }
}

# Test 3: Routes working (Fix #3)
echo "=== Testing Route Registration ==="
$routes = ['staffs.home', 'trainees.home', 'activities.home', 'activities.categories'];
foreach ($routes as $routeName) {
    try {
        $url = route($routeName);
        echo "✓ Route '$routeName' → $url";
    } catch (Exception $e) {
        echo "✗ Route '$routeName' failed: " . $e->getMessage();
    }
}

# Test 4: Asset-Centre relationship (Fix #4)
echo "=== Testing Asset-Centre Relationship ==="
$centre = App\Models\Centre::first();
if ($centre) {
    echo "Centre found: " . $centre->centre_name . " (ID: " . $centre->centre_id . ")";
    try {
        $assetsCount = $centre->assets()->count();
        echo "✓ Centre-Asset relationship working, count: $assetsCount";
    } catch (Exception $e) {
        echo "✗ Centre-Asset relationship error: " . $e->getMessage();
    }
}

# Test 5: AssetMaintenance priority field (Fix #5)
echo "=== Testing AssetMaintenance priority field ==="
if (Schema::hasColumn('asset_maintenance', 'priority')) {
    echo "✓ priority column exists in asset_maintenance table";
    $maintenance = App\Models\AssetMaintenance::first();
    if ($maintenance) {
        echo "✓ AssetMaintenance record found";
        echo "Priority value: " . ($maintenance->priority ?: 'null');
    }
}

echo "=== VALIDATION COMPLETE ==="