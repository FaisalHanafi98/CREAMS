<?php
require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== CHECKING ASSETS TABLE ===" . PHP_EOL;

try {
    $columns = DB::select('DESCRIBE assets');
    echo "Asset table columns:" . PHP_EOL;
    foreach ($columns as $column) {
        echo "  - {$column->Field} ({$column->Type})" . PHP_EOL;
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . PHP_EOL;
}

echo "=== END CHECK ===" . PHP_EOL;