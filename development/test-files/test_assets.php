<?php
require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== TESTING ASSET MANAGEMENT MODULE ===" . PHP_EOL;

// Test assets table structure
echo "Testing assets table structure..." . PHP_EOL;
try {
    $columns = DB::select('DESCRIBE assets');
    echo "✅ Asset table columns:" . PHP_EOL;
    foreach ($columns as $column) {
        echo "  - {$column->Field} ({$column->Type})" . PHP_EOL;
    }
} catch (Exception $e) {
    echo "❌ Asset table structure error: " . $e->getMessage() . PHP_EOL;
}

// Test assets data
echo PHP_EOL . "Testing assets data..." . PHP_EOL;
try {
    $assets = App\Models\Asset::all();
    echo "✅ Asset retrieved: " . $assets->count() . " assets" . PHP_EOL;
    foreach ($assets->take(5) as $asset) {
        echo "  - {$asset->asset_name} (ID: {$asset->asset_id})" . PHP_EOL;
    }
} catch (Exception $e) {
    echo "❌ Asset data error: " . $e->getMessage() . PHP_EOL;
}

// Test if AssetController can be instantiated
echo PHP_EOL . "Testing AssetController instantiation..." . PHP_EOL;
try {
    $controller = new App\Http\Controllers\AssetController();
    echo "✅ AssetController instantiated successfully" . PHP_EOL;
} catch (Exception $e) {
    echo "❌ AssetController instantiation failed: " . $e->getMessage() . PHP_EOL;
}

// Test CRUD operations
echo PHP_EOL . "Testing CRUD operations..." . PHP_EOL;

// Test CREATE
echo "Testing CREATE operation..." . PHP_EOL;
try {
    // Get first asset type
    $assetType = DB::table('asset_types')->first();
    if (!$assetType) {
        echo "❌ No asset types found. Please run seed_asset_types.php first." . PHP_EOL;
        exit;
    }

    $testAsset = App\Models\Asset::create([
        'asset_id' => 'TEST' . now()->format('mdHi'),
        'asset_name' => 'Test Asset ' . now()->format('Y-m-d H:i:s'),
        'asset_description' => 'Test Description',
        'type_id' => $assetType->id,
        'asset_model' => 'Test Model',
        'asset_brand' => 'Test Brand',
        'asset_serial_number' => 'TEST' . now()->format('mdHis'),
        'asset_value' => '1000.00',
        'asset_location' => 'Test Location',
        'centre_id' => '01',
        'purchase_date' => now()->format('Y-m-d'),
        'supplier' => 'Test Supplier',
        'warranty_info' => 'Test warranty info',
        'asset_condition' => 'good',
        'asset_status' => 'available'
    ]);
    echo "✅ Asset created successfully: {$testAsset->asset_name}" . PHP_EOL;
} catch (Exception $e) {
    echo "❌ Asset creation error: " . $e->getMessage() . PHP_EOL;
}

// Test READ
echo PHP_EOL . "Testing READ operation..." . PHP_EOL;
try {
    $asset = App\Models\Asset::first();
    if ($asset) {
        echo "✅ Asset read successfully: {$asset->asset_name}" . PHP_EOL;
        echo "  - ID: {$asset->asset_id}" . PHP_EOL;
        echo "  - Model: {$asset->asset_model}" . PHP_EOL;
        echo "  - Brand: {$asset->asset_brand}" . PHP_EOL;
        echo "  - Value: {$asset->asset_value}" . PHP_EOL;
        echo "  - Location: {$asset->asset_location}" . PHP_EOL;
        echo "  - Centre: {$asset->centre_id}" . PHP_EOL;
        echo "  - Status: {$asset->asset_status}" . PHP_EOL;
        echo "  - Condition: {$asset->asset_condition}" . PHP_EOL;
    } else {
        echo "❌ No assets found" . PHP_EOL;
    }
} catch (Exception $e) {
    echo "❌ Asset read error: " . $e->getMessage() . PHP_EOL;
}

// Test UPDATE
echo PHP_EOL . "Testing UPDATE operation..." . PHP_EOL;
try {
    $asset = App\Models\Asset::where('asset_id', 'LIKE', 'TEST%')->first();
    if ($asset) {
        $asset->update([
            'asset_name' => 'Updated Test Asset ' . now()->format('H:i:s'),
            'asset_condition' => 'excellent'
        ]);
        echo "✅ Asset updated successfully: {$asset->asset_name}" . PHP_EOL;
    } else {
        echo "⚠️ No test asset found for update" . PHP_EOL;
    }
} catch (Exception $e) {
    echo "❌ Asset update error: " . $e->getMessage() . PHP_EOL;
}

// Test DELETE
echo PHP_EOL . "Testing DELETE operation..." . PHP_EOL;
try {
    $asset = App\Models\Asset::where('asset_id', 'LIKE', 'TEST%')->first();
    if ($asset) {
        $assetName = $asset->asset_name;
        $asset->delete();
        echo "✅ Asset deleted successfully: {$assetName}" . PHP_EOL;
    } else {
        echo "⚠️ No test asset found for deletion" . PHP_EOL;
    }
} catch (Exception $e) {
    echo "❌ Asset deletion error: " . $e->getMessage() . PHP_EOL;
}

echo PHP_EOL . "=== END ASSET MANAGEMENT MODULE TEST ===" . PHP_EOL;