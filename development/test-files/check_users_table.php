<?php
require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== USERS TABLE STRUCTURE AUDIT ===" . PHP_EOL;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

try {
    echo "1. User table columns:" . PHP_EOL;
    $columns = Schema::getColumnListing('users');
    foreach ($columns as $column) {
        echo "   - " . $column . PHP_EOL;
    }
    
    echo PHP_EOL . "2. User table structure details:" . PHP_EOL;
    $details = DB::select('DESCRIBE users');
    foreach ($details as $detail) {
        echo "   " . $detail->Field . " | " . $detail->Type . " | " . ($detail->Null == 'YES' ? 'NULL' : 'NOT NULL') . " | " . ($detail->Key ?: 'NO KEY') . PHP_EOL;
    }
    
    echo PHP_EOL . "3. Sample user record:" . PHP_EOL;
    $user = DB::table('users')->first();
    if ($user) {
        foreach ((array)$user as $key => $value) {
            echo "   " . $key . ": " . (is_null($value) ? 'NULL' : $value) . PHP_EOL;
        }
    }
    
    echo PHP_EOL . "4. Checking for bio vs about field:" . PHP_EOL;
    $hasBio = in_array('bio', $columns);
    $hasAbout = in_array('about', $columns);
    echo "   - Has 'bio' field: " . ($hasBio ? 'YES' : 'NO') . PHP_EOL;
    echo "   - Has 'about' field: " . ($hasAbout ? 'YES' : 'NO') . PHP_EOL;
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . PHP_EOL;
    echo "Stack trace: " . $e->getTraceAsString() . PHP_EOL;
}