<?php
require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== TRAINEES TABLE STRUCTURE AUDIT ===" . PHP_EOL;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

try {
    echo "1. Trainee table columns:" . PHP_EOL;
    $columns = Schema::getColumnListing('trainees');
    foreach ($columns as $column) {
        echo "   - " . $column . PHP_EOL;
    }
    
    echo PHP_EOL . "2. Trainee table structure details:" . PHP_EOL;
    $details = DB::select('DESCRIBE trainees');
    foreach ($details as $detail) {
        echo "   " . $detail->Field . " | " . $detail->Type . " | " . ($detail->Null == 'YES' ? 'NULL' : 'NOT NULL') . " | " . ($detail->Key ?: 'NO KEY') . PHP_EOL;
    }
    
    echo PHP_EOL . "3. Sample trainee record:" . PHP_EOL;
    $trainee = DB::table('trainees')->first();
    if ($trainee) {
        foreach ((array)$trainee as $key => $value) {
            echo "   " . $key . ": " . (is_null($value) ? 'NULL' : $value) . PHP_EOL;
        }
    } else {
        echo "   No trainee records found" . PHP_EOL;
    }
    
    echo PHP_EOL . "4. Checking for trainee_attendance and enrollment_status columns:" . PHP_EOL;
    $hasTraineeAttendance = in_array('trainee_attendance', $columns);
    $hasEnrollmentStatus = in_array('enrollment_status', $columns);
    echo "   - Has 'trainee_attendance' field: " . ($hasTraineeAttendance ? 'YES' : 'NO') . PHP_EOL;
    echo "   - Has 'enrollment_status' field: " . ($hasEnrollmentStatus ? 'YES' : 'NO') . PHP_EOL;
    
    // Check for related columns
    $attendanceColumns = array_filter($columns, function($col) {
        return strpos($col, 'attendance') !== false;
    });
    $statusColumns = array_filter($columns, function($col) {
        return strpos($col, 'status') !== false;
    });
    $enrollmentColumns = array_filter($columns, function($col) {
        return strpos($col, 'enroll') !== false;
    });
    
    echo "   - Attendance-related columns: " . implode(', ', $attendanceColumns) . PHP_EOL;
    echo "   - Status-related columns: " . implode(', ', $statusColumns) . PHP_EOL;
    echo "   - Enrollment-related columns: " . implode(', ', $enrollmentColumns) . PHP_EOL;
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . PHP_EOL;
    echo "Stack trace: " . $e->getTraceAsString() . PHP_EOL;
}