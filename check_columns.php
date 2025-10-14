<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Activity;
use Illuminate\Support\Facades\Schema;

echo "Activities table columns:\n";
$columns = Schema::getColumnListing('activities');
foreach ($columns as $col) {
    echo "  - $col\n";
}

echo "\nSample activity data:\n";
$activity = Activity::first();
if ($activity) {
    foreach ($activity->getAttributes() as $key => $value) {
        echo "  $key: " . (is_string($value) ? substr($value, 0, 50) : $value) . "\n";
    }
}
