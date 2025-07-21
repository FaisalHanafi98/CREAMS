<?php
require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== ACTIVITY TABLES STRUCTURE AUDIT ===" . PHP_EOL;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

try {
    // Check activity_enrollments table
    echo "1. activity_enrollments table:" . PHP_EOL;
    if (Schema::hasTable('activity_enrollments')) {
        $columns = Schema::getColumnListing('activity_enrollments');
        foreach ($columns as $column) {
            echo "   - " . $column . PHP_EOL;
        }
    } else {
        echo "   Table does not exist" . PHP_EOL;
    }
    
    // Check for attendance table variations
    echo PHP_EOL . "2. Checking for attendance tables:" . PHP_EOL;
    $possibleTables = ['activity_attendance', 'activity_attendances', 'trainee_attendance', 'attendances'];
    foreach ($possibleTables as $table) {
        if (Schema::hasTable($table)) {
            echo "   ✓ Found table: {$table}" . PHP_EOL;
            $columns = Schema::getColumnListing($table);
            foreach ($columns as $column) {
                echo "      - " . $column . PHP_EOL;
            }
        } else {
            echo "   ❌ Table not found: {$table}" . PHP_EOL;
        }
    }
    
    // List all tables to see what's available
    echo PHP_EOL . "3. All available tables in database:" . PHP_EOL;
    $tables = DB::select('SHOW TABLES');
    $tableColumn = 'Tables_in_' . config('database.connections.mysql.database');
    foreach ($tables as $table) {
        echo "   - " . $table->$tableColumn . PHP_EOL;
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . PHP_EOL;
}