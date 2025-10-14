<?php
require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== ACTIVITIES TABLE COLUMNS ===\n";
$columns = DB::select('DESCRIBE activities');
foreach ($columns as $column) {
    echo "- {$column->Field} ({$column->Type})\n";
}

echo "\n=== CATEGORIES TABLE ===\n";
try {
    $categories = DB::select('DESCRIBE categories');
    foreach ($categories as $column) {
        echo "- {$column->Field} ({$column->Type})\n";
    }
} catch (Exception $e) {
    echo "❌ Category table doesn't exist: " . $e->getMessage() . "\n";
}

echo "\n=== CHECKING EXISTING DATA ===\n";
$activity = App\Models\Activity::first();
if ($activity) {
    echo "Sample activity attributes:\n";
    foreach ($activity->getAttributes() as $key => $value) {
        echo "- {$key}: " . (is_string($value) ? substr($value, 0, 50) : $value) . "\n";
    }
}