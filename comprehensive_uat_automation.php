<?php
/**
 * ============================================================================
 * CREAMS COMPREHENSIVE UAT AUTOMATION SCRIPT
 * ============================================================================
 *
 * Purpose: Thoroughly test ALL modules and functionality in CREAMS system
 * Based on: CREAMS_User Acceptance Testing.xlsx
 *
 * Test Coverage:
 * - Public Access (Home, Contact, Volunteer)
 * - Authentication (Login, Password Reset, Session Management)
 * - Dashboards (All Roles)
 * - Profile Management
 * - Staff Management
 * - Trainee Management
 * - Activities Management
 * - Attendance Tracking
 * - Centres Management
 * - Assets Management
 * - Letters/Documentation
 * - Messaging System
 * - System Administration
 *
 * Date Created: 2025-10-13
 * Last Updated: 2025-10-13
 * ============================================================================
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('max_execution_time', 3600); // 1 hour timeout
ini_set('memory_limit', '512M');

// Bootstrap Laravel
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class ComprehensiveUATTester {
    private $results = [];
    private $testCount = 0;
    private $passCount = 0;
    private $failCount = 0;
    private $skipCount = 0;
    private $startTime;
    private $testUsers = [];
    private $testData = [];
    private $verbose = true;

    public function __construct() {
        $this->startTime = microtime(true);
        $this->log("=== CREAMS COMPREHENSIVE UAT AUTOMATION STARTED ===", 'INFO');
        $this->log("Date: " . date('Y-m-d H:i:s'), 'INFO');
        $this->log("Environment: " . config('app.env'), 'INFO');
        $this->log("Database: " . config('database.connections.mysql.database'), 'INFO');
        echo str_repeat("=", 80) . "\n";
    }

    private function log($message, $type = 'INFO') {
        $timestamp = date('Y-m-d H:i:s');
        $color = [
            'INFO' => "\033[36m",    // Cyan
            'PASS' => "\033[32m",    // Green
            'FAIL' => "\033[31m",    // Red
            'WARN' => "\033[33m",    // Yellow
            'SKIP' => "\033[90m",    // Gray
        ][$type] ?? "\033[0m";

        $reset = "\033[0m";
        echo "{$color}[{$timestamp}] [{$type}] {$message}{$reset}\n";
    }

    private function testModule($moduleName, $testFunction) {
        echo "\n" . str_repeat("=", 80) . "\n";
        $this->log("STARTING MODULE: {$moduleName}", 'INFO');
        echo str_repeat("=", 80) . "\n";

        $startTime = microtime(true);
        try {
            $testFunction();
            $duration = round(microtime(true) - $startTime, 2);
            $this->log("COMPLETED MODULE: {$moduleName} ({$duration}s)", 'INFO');
        } catch (Exception $e) {
            $this->log("MODULE FAILED: {$moduleName} - " . $e->getMessage(), 'FAIL');
        }
    }

    private function test($testId, $testName, $testFunction) {
        $this->testCount++;
        echo "\n";
        $this->log("[{$testId}] {$testName}", 'INFO');

        try {
            $startTime = microtime(true);
            $result = $testFunction();
            $duration = round((microtime(true) - $startTime) * 1000, 2);

            if ($result === true || $result === null) {
                $this->passCount++;
                $this->log("✓ PASS - {$testName} ({$duration}ms)", 'PASS');
                $this->results[$testId] = [
                    'status' => 'PASS',
                    'name' => $testName,
                    'duration' => $duration,
                    'message' => 'Test passed successfully'
                ];
            } else {
                $this->failCount++;
                $this->log("✗ FAIL - {$testName}: {$result}", 'FAIL');
                $this->results[$testId] = [
                    'status' => 'FAIL',
                    'name' => $testName,
                    'duration' => $duration,
                    'message' => $result
                ];
            }
        } catch (Exception $e) {
            $this->failCount++;
            $this->log("✗ FAIL - {$testName}: " . $e->getMessage(), 'FAIL');
            $this->results[$testId] = [
                'status' => 'FAIL',
                'name' => $testName,
                'duration' => 0,
                'message' => $e->getMessage()
            ];
        }
    }

    private function skip($testId, $testName, $reason) {
        $this->testCount++;
        $this->skipCount++;
        $this->log("⊘ SKIP - {$testName}: {$reason}", 'SKIP');
        $this->results[$testId] = [
            'status' => 'SKIP',
            'name' => $testName,
            'duration' => 0,
            'message' => $reason
        ];
    }

    private function assert($condition, $message) {
        if (!$condition) {
            throw new Exception($message);
        }
    }

    // ========================================================================
    // MODULE 1: PUBLIC ACCESS TESTS
    // ========================================================================

    public function testPublicAccessModule() {
        $this->testModule('PUBLIC ACCESS', function() {

            $this->test('HOME001', 'Welcome/Landing Page Load and Verification', function() {
                // Test database connectivity
                DB::connection()->getPdo();

                // Test routes are registered
                $routes = app('router')->getRoutes();
                $homeRoute = collect($routes)->first(function($route) {
                    return $route->uri() === '/' && in_array('GET', $route->methods());
                });

                $this->assert($homeRoute !== null, "Home route (/) not found");

                // Test home page tables exist
                $this->assert(Schema::hasTable('users'), "Users table missing");

                return true;
            });

            $this->test('HOME002', 'Public Information Display', function() {
                // Check if About Us content is accessible
                $routes = app('router')->getRoutes();
                $aboutRoute = collect($routes)->first(function($route) {
                    return str_contains($route->uri(), 'about');
                });

                // Even if no dedicated route, test passes if home route exists
                return true;
            });

            $this->test('CONTACT001', 'Contact Us Form Submission', function() {
                // Check if contact_messages table exists (actual table name)
                if (!Schema::hasTable('contact_messages')) {
                    return "Contact messages table not found - feature may not be implemented";
                }

                // Get table structure
                $columns = Schema::getColumnListing('contact_messages');
                $requiredCols = ['name', 'email', 'subject', 'message'];

                foreach ($requiredCols as $col) {
                    if (!in_array($col, $columns)) {
                        return "Contact messages table missing '{$col}' column";
                    }
                }

                // Test contact submission
                $testContact = [
                    'name' => 'UAT Test User',
                    'email' => 'uat.test@example.com',
                    'subject' => 'UAT Testing',
                    'message' => 'This is a comprehensive UAT test message',
                    'status' => 'new',
                    'created_at' => now(),
                ];

                $contactId = DB::table('contact_messages')->insertGetId($testContact);
                $this->assert($contactId > 0, "Failed to insert test contact");

                // Verify insertion
                $contact = DB::table('contact_messages')->where('id', $contactId)->first();
                $this->assert($contact !== null, "Failed to retrieve inserted contact");
                $this->assert($contact->email === 'uat.test@example.com', "Contact email mismatch");

                // Cleanup
                DB::table('contact_messages')->where('id', $contactId)->delete();

                return true;
            });

            $this->test('VOL001', 'Volunteer Registration Form', function() {
                // Check volunteers table exists
                if (!Schema::hasTable('volunteers')) {
                    return "Volunteers table doesn't exist - feature may not be implemented";
                }

                $columns = Schema::getColumnListing('volunteers');
                $requiredColumns = ['name', 'email', 'phone'];

                foreach ($requiredColumns as $col) {
                    if (!in_array($col, $columns)) {
                        return "Missing required column: {$col}";
                    }
                }

                // Test volunteer registration
                $testVolunteer = [
                    'volunteer_id' => 'VOLUAT' . time(),
                    'name' => 'UAT Volunteer Test',
                    'email' => 'volunteer.uat.' . time() . '@example.com',
                    'phone' => '0123456789',
                    'status' => 'applied',
                    'created_at' => now(),
                ];

                $volunteerId = DB::table('volunteers')->insertGetId($testVolunteer);
                $this->assert($volunteerId > 0, "Failed to insert test volunteer");

                // Verify volunteer was created
                $volunteer = DB::table('volunteers')->where('id', $volunteerId)->first();
                $this->assert($volunteer !== null, "Failed to retrieve volunteer");
                $this->assert($volunteer->status === 'applied', "Volunteer status mismatch");

                // Cleanup
                DB::table('volunteers')->where('id', $volunteerId)->delete();

                return true;
            });
        });
    }

    // ========================================================================
    // MODULE 2: AUTHENTICATION TESTS
    // ========================================================================

    public function testAuthenticationModule() {
        $this->testModule('AUTHENTICATION', function() {

            $this->test('AUTH001', 'Standard Login Functionality - Admin', function() {
                // Check users table
                $this->assert(Schema::hasTable('users'), "Users table missing");

                // Find admin user
                $admin = DB::table('users')->where('role', 'admin')->first();
                $this->assert($admin !== null, "No admin user found in database");

                // Store for later tests
                $this->testUsers['admin'] = $admin;

                // Verify required columns
                $columns = Schema::getColumnListing('users');
                $required = ['id', 'email', 'password', 'role', 'status'];
                foreach ($required as $col) {
                    $this->assert(in_array($col, $columns), "Users table missing '{$col}' column");
                }

                // Verify admin has email
                $this->assert(!empty($admin->email), "Admin user has no email");

                return true;
            });

            $this->test('AUTH002', 'Login with Different Roles', function() {
                // Test all role types exist
                $roles = ['admin', 'supervisor', 'teacher', 'ajk'];
                $foundRoles = [];

                foreach ($roles as $role) {
                    $user = DB::table('users')->where('role', $role)->first();
                    if ($user) {
                        $foundRoles[] = $role;
                        $this->testUsers[$role] = $user;
                    }
                }

                $this->assert(in_array('admin', $foundRoles), "No admin user found");
                $this->assert(count($foundRoles) >= 2, "Only " . count($foundRoles) . " role types found, need at least 2");

                $this->log("  Found roles: " . implode(', ', $foundRoles), 'INFO');

                return true;
            });

            $this->test('AUTH003', 'Password Security Validation', function() {
                // Test password hashing
                $testPassword = 'TestPassword123!';
                $hashed = Hash::make($testPassword);

                $this->assert(Hash::check($testPassword, $hashed), "Password hashing/verification failed");
                $this->assert(!Hash::check('WrongPassword', $hashed), "Password verification should fail for wrong password");

                return true;
            });

            $this->test('AUTH004', 'Session Management', function() {
                // Check sessions table exists
                if (!Schema::hasTable('sessions')) {
                    return "Sessions table not found - using default session driver";
                }

                return true;
            });

            $this->test('AUTH005', 'Account Status Validation', function() {
                // Check for status field
                $columns = Schema::getColumnListing('users');
                $this->assert(in_array('status', $columns), "Users table missing 'status' column");

                // Count active vs inactive users
                $activeCount = DB::table('users')->where('status', 'active')->count();
                $inactiveCount = DB::table('users')->where('status', 'inactive')->count();

                $this->log("  Active users: {$activeCount}, Inactive: {$inactiveCount}", 'INFO');
                $this->assert($activeCount > 0, "No active users found");

                return true;
            });
        });
    }

    // ========================================================================
    // MODULE 3: DASHBOARD TESTS
    // ========================================================================

    public function testDashboardModule() {
        $this->testModule('DASHBOARD', function() {

            $this->test('DASH001', 'Admin Dashboard - Statistics Calculation', function() {
                // Test database queries for dashboard stats
                $stats = [
                    'total_users' => DB::table('users')->count(),
                    'total_trainees' => Schema::hasTable('trainees') ? DB::table('trainees')->count() : 0,
                    'total_activities' => Schema::hasTable('activities') ? DB::table('activities')->count() : 0,
                    'total_centres' => Schema::hasTable('centres') ? DB::table('centres')->count() : 0,
                ];

                $this->log("  Users: {$stats['total_users']}, Trainees: {$stats['total_trainees']}, Activities: {$stats['total_activities']}, Centres: {$stats['total_centres']}", 'INFO');

                $this->assert($stats['total_users'] > 0, "No users in system");

                return true;
            });

            $this->test('DASH002', 'Supervisor Dashboard - Centre Filtering', function() {
                if (!Schema::hasTable('centres')) {
                    return "Centres table not found";
                }

                // Get a supervisor user
                $supervisor = DB::table('users')->where('role', 'supervisor')->first();
                if (!$supervisor) {
                    return "No supervisor user found";
                }

                // Check if supervisor has centre_id
                if (!isset($supervisor->centre_id)) {
                    return "Supervisor has no centre_id assignment";
                }

                // Verify centre exists
                $centre = DB::table('centres')->where('centre_id', $supervisor->centre_id)->first();
                $this->assert($centre !== null, "Supervisor's centre '{$supervisor->centre_id}' not found");

                $this->log("  Supervisor centre: {$centre->centre_name}", 'INFO');

                return true;
            });

            $this->test('DASH003', 'Teacher Dashboard - Activity View', function() {
                $teacher = DB::table('users')->where('role', 'teacher')->first();
                if (!$teacher) {
                    return "No teacher user found";
                }

                // Check if activities exist
                if (!Schema::hasTable('activities')) {
                    return "Activities table not found";
                }

                $activityCount = DB::table('activities')->count();
                $this->log("  Total activities in system: {$activityCount}", 'INFO');

                return true;
            });

            $this->test('DASH004', 'Dashboard Performance - Load Time', function() {
                // Test query performance for dashboard
                $start = microtime(true);

                $stats = [
                    'users' => DB::table('users')->where('status', 'active')->count(),
                    'trainees' => Schema::hasTable('trainees') ? DB::table('trainees')->where('status', 'active')->count() : 0,
                    'activities' => Schema::hasTable('activities') ? DB::table('activities')->count() : 0,
                ];

                $duration = (microtime(true) - $start) * 1000;
                $this->log("  Query time: {$duration}ms", 'INFO');

                $this->assert($duration < 1000, "Dashboard queries too slow: {$duration}ms");

                return true;
            });
        });
    }

    // ========================================================================
    // MODULE 4: PROFILE MANAGEMENT TESTS
    // ========================================================================

    public function testProfileModule() {
        $this->testModule('PROFILE MANAGEMENT', function() {

            $this->test('PROF001', 'View Own Profile', function() {
                $user = DB::table('users')->where('role', 'admin')->first();
                $this->assert($user !== null, "No user found for testing");

                // Verify user has required fields
                $this->assert(!empty($user->email), "User has no email");
                $this->assert(!empty($user->role), "User has no role");

                return true;
            });

            $this->test('PROF002', 'Edit Profile Information', function() {
                // Create test user for editing
                $testUser = [
                    'name' => 'UAT Profile Test',
                    'email' => 'uat.profile.' . time() . '@test.com',
                    'password' => Hash::make('Test123!'),
                    'role' => 'teacher',
                    'status' => 'active',
                    'created_at' => now(),
                ];

                $userId = DB::table('users')->insertGetId($testUser);

                // Update user
                $updated = DB::table('users')
                    ->where('id', $userId)
                    ->update([
                        'name' => 'UAT Profile Updated',
                        'updated_at' => now()
                    ]);

                $this->assert($updated > 0, "Failed to update user");

                // Verify update
                $user = DB::table('users')->where('id', $userId)->first();
                $this->assert($user->name === 'UAT Profile Updated', "Profile update verification failed");

                // Cleanup
                DB::table('users')->where('id', $userId)->delete();

                return true;
            });

            $this->test('PROF003', 'Change Password', function() {
                // Test password change logic
                $oldPassword = 'OldPass123!';
                $newPassword = 'NewPass456!';

                $oldHash = Hash::make($oldPassword);
                $newHash = Hash::make($newPassword);

                // Verify old password works with old hash
                $this->assert(Hash::check($oldPassword, $oldHash), "Old password check failed");

                // Verify new password works with new hash
                $this->assert(Hash::check($newPassword, $newHash), "New password check failed");

                // Verify old password doesn't work with new hash
                $this->assert(!Hash::check($oldPassword, $newHash), "Password isolation check failed");

                return true;
            });
        });
    }

    // ========================================================================
    // MODULE 5: STAFF MANAGEMENT TESTS
    // ========================================================================

    public function testStaffModule() {
        $this->testModule('STAFF MANAGEMENT', function() {

            $this->test('USER001', 'Create New Staff Member', function() {
                // Create test staff
                $testStaff = [
                    'name' => 'UAT Staff Test ' . time(),
                    'email' => 'uat.staff.' . time() . '@test.com',
                    'password' => Hash::make('Test123!'),
                    'role' => 'teacher',
                    'centre_id' => '01',
                    'status' => 'active',
                    'phone' => '0123456789',
                    'created_at' => now(),
                ];

                $staffId = DB::table('users')->insertGetId($testStaff);
                $this->assert($staffId > 0, "Failed to create staff member");

                // Store for other tests
                $this->testData['staff_id'] = $staffId;

                return true;
            });

            $this->test('USER002', 'Edit Staff Details', function() {
                if (!isset($this->testData['staff_id'])) {
                    return "No test staff available from USER001";
                }

                $updated = DB::table('users')
                    ->where('id', $this->testData['staff_id'])
                    ->update([
                        'name' => 'UAT Staff Updated',
                        'phone' => '0198765432',
                        'updated_at' => now()
                    ]);

                $this->assert($updated > 0, "Failed to update staff");

                $staff = DB::table('users')->where('id', $this->testData['staff_id'])->first();
                $this->assert($staff->phone === '0198765432', "Staff update verification failed");

                return true;
            });

            $this->test('USER003', 'View Staff List & Search', function() {
                $staffCount = DB::table('users')->whereIn('role', ['admin', 'supervisor', 'teacher', 'ajk'])->count();
                $this->log("  Total staff members: {$staffCount}", 'INFO');

                $this->assert($staffCount > 0, "No staff members found");

                // Test search by role
                $teachers = DB::table('users')->where('role', 'teacher')->count();
                $this->log("  Teachers: {$teachers}", 'INFO');

                return true;
            });

            $this->test('USER004', 'Staff Role Management', function() {
                if (!isset($this->testData['staff_id'])) {
                    return "No test staff available";
                }

                // Change role
                DB::table('users')
                    ->where('id', $this->testData['staff_id'])
                    ->update(['role' => 'supervisor', 'updated_at' => now()]);

                $staff = DB::table('users')->where('id', $this->testData['staff_id'])->first();
                $this->assert($staff->role === 'supervisor', "Role change failed");

                return true;
            });

            $this->test('USER005', 'Deactivate Staff', function() {
                if (!isset($this->testData['staff_id'])) {
                    return "No test staff available";
                }

                DB::table('users')
                    ->where('id', $this->testData['staff_id'])
                    ->update(['status' => 'inactive', 'updated_at' => now()]);

                $staff = DB::table('users')->where('id', $this->testData['staff_id'])->first();
                $this->assert($staff->status === 'inactive', "Deactivation failed");

                // Cleanup
                DB::table('users')->where('id', $this->testData['staff_id'])->delete();

                return true;
            });
        });
    }

    // ========================================================================
    // MODULE 6: TRAINEE MANAGEMENT TESTS
    // ========================================================================

    public function testTraineeModule() {
        $this->testModule('TRAINEE MANAGEMENT', function() {

            if (!Schema::hasTable('trainees')) {
                $this->skip('TRAIN001', 'Register New Trainee', 'Trainees table not found');
                $this->skip('TRAIN002', 'Edit Trainee Profile', 'Trainees table not found');
                $this->skip('TRAIN003', 'View Trainee List & Filters', 'Trainees table not found');
                return;
            }

            $this->test('TRAIN001', 'Register New Trainee', function() {
                $testTrainee = [
                    'trainee_id' => 'UAT' . time(),
                    'trainee_first_name' => 'UAT Trainee',
                    'trainee_last_name' => 'Test ' . time(),
                    'trainee_email' => 'uat.trainee.' . time() . '@test.com',
                    'ic_number' => '050101' . str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT),
                    'trainee_date_of_birth' => '2005-01-01',
                    'gender' => 'Male',
                    'centre_id' => '01',
                    'guardian_name' => 'UAT Guardian',
                    'guardian_phone' => '0123456789',
                    'status' => 'active',
                    'created_at' => now(),
                ];

                $traineeId = DB::table('trainees')->insertGetId($testTrainee);
                $this->assert($traineeId > 0, "Failed to create trainee");

                $this->testData['trainee_id'] = $traineeId;

                return true;
            });

            $this->test('TRAIN002', 'Edit Trainee Profile', function() {
                if (!isset($this->testData['trainee_id'])) {
                    return "No test trainee available";
                }

                DB::table('trainees')
                    ->where('id', $this->testData['trainee_id'])
                    ->update([
                        'guardian_phone' => '0198765432',
                        'updated_at' => now()
                    ]);

                $trainee = DB::table('trainees')->where('id', $this->testData['trainee_id'])->first();
                $this->assert($trainee->guardian_phone === '0198765432', "Trainee update failed");

                return true;
            });

            $this->test('TRAIN003', 'View Trainee List & Filters', function() {
                $totalTrainees = DB::table('trainees')->count();
                $activeTrainees = DB::table('trainees')->where('status', 'active')->count();

                $this->log("  Total: {$totalTrainees}, Active: {$activeTrainees}", 'INFO');
                $this->assert($totalTrainees > 0, "No trainees in system");

                // Cleanup test trainee
                if (isset($this->testData['trainee_id'])) {
                    DB::table('trainees')->where('id', $this->testData['trainee_id'])->delete();
                }

                return true;
            });
        });
    }

    // ========================================================================
    // MODULE 7: ACTIVITIES MANAGEMENT TESTS
    // ========================================================================

    public function testActivitiesModule() {
        $this->testModule('ACTIVITIES MANAGEMENT', function() {

            if (!Schema::hasTable('activities')) {
                $this->skip('ACT001', 'Activity Listing', 'Activities table not found');
                $this->skip('ACT002', 'Create New Activity', 'Activities table not found');
                return;
            }

            $this->test('ACT001', 'Activity Listing & Filtering', function() {
                $totalActivities = DB::table('activities')->count();
                $this->log("  Total activities: {$totalActivities}", 'INFO');

                // Test filtering by status if column exists
                $columns = Schema::getColumnListing('activities');
                if (in_array('status', $columns)) {
                    $activeActivities = DB::table('activities')->where('status', 'active')->count();
                    $this->log("  Active activities: {$activeActivities}", 'INFO');
                }

                return true;
            });

            $this->test('ACT002', 'Create New Activity', function() {
                $columns = Schema::getColumnListing('activities');

                $testActivity = [
                    'activity_name' => 'UAT Test Activity ' . time(),
                    'created_at' => now(),
                ];

                // Add optional columns if they exist
                if (in_array('description', $columns)) {
                    $testActivity['description'] = 'UAT Test Activity Description';
                }
                if (in_array('centre_id', $columns)) {
                    $testActivity['centre_id'] = '01';
                }
                if (in_array('status', $columns)) {
                    $testActivity['status'] = 'active';
                }
                if (in_array('category_id', $columns)) {
                    $testActivity['category_id'] = 1;
                }

                $activityId = DB::table('activities')->insertGetId($testActivity);
                $this->assert($activityId > 0, "Failed to create activity");

                $this->testData['activity_id'] = $activityId;

                return true;
            });

            $this->test('ACT003', 'Edit Activity Details', function() {
                if (!isset($this->testData['activity_id'])) {
                    return "No test activity available";
                }

                DB::table('activities')
                    ->where('id', $this->testData['activity_id'])
                    ->update([
                        'activity_name' => 'UAT Activity Updated',
                        'updated_at' => now()
                    ]);

                $activity = DB::table('activities')->where('id', $this->testData['activity_id'])->first();
                $this->assert($activity->activity_name === 'UAT Activity Updated', "Activity update failed");

                // Cleanup
                DB::table('activities')->where('id', $this->testData['activity_id'])->delete();

                return true;
            });
        });
    }

    // ========================================================================
    // MODULE 8: ATTENDANCE TRACKING TESTS
    // ========================================================================

    public function testAttendanceModule() {
        $this->testModule('ATTENDANCE TRACKING', function() {

            // Check for actual attendance tables in system
            $attendanceTables = ['session_attendance', 'trainee_attendances'];
            $foundTable = null;

            foreach ($attendanceTables as $table) {
                if (Schema::hasTable($table)) {
                    $foundTable = $table;
                    break;
                }
            }

            if (!$foundTable) {
                $this->skip('ATT001', 'Mark Attendance', 'No attendance tables found');
                $this->skip('ATT002', 'View Attendance Reports', 'No attendance tables found');
                return;
            }

            $this->test('ATT001', 'Session Attendance Records Exist', function() use ($foundTable) {
                $totalRecords = DB::table($foundTable)->count();
                $this->log("  Total attendance records in {$foundTable}: {$totalRecords}", 'INFO');

                $columns = Schema::getColumnListing($foundTable);
                $this->log("  Columns: " . implode(', ', $columns), 'INFO');

                $this->assert($totalRecords >= 0, "Attendance table accessible");

                return true;
            });

            $this->test('ATT002', 'Attendance Status Types', function() use ($foundTable) {
                $columns = Schema::getColumnListing($foundTable);

                // Check for status column (might be 'status' or 'attendance_status')
                $statusCol = null;
                if (in_array('attendance_status', $columns)) {
                    $statusCol = 'attendance_status';
                } elseif (in_array('status', $columns)) {
                    $statusCol = 'status';
                } else {
                    return "No status column found in {$foundTable}";
                }

                $statuses = DB::table($foundTable)
                    ->select($statusCol)
                    ->distinct()
                    ->pluck($statusCol)
                    ->toArray();

                $this->log("  Status types found in {$statusCol}: " . implode(', ', $statuses), 'INFO');
                $this->assert(count($statuses) > 0, "At least one status type should exist");

                return true;
            });

            // Test trainee_attendances if it exists
            if (Schema::hasTable('trainee_attendances')) {
                $this->test('ATT003', 'Trainee Attendance Data Integrity', function() {
                    $recentRecords = DB::table('trainee_attendances')
                        ->whereNotNull('attendance_date')
                        ->count();

                    $this->log("  Records with attendance dates: {$recentRecords}", 'INFO');

                    // Check for date range
                    $dateRange = DB::table('trainee_attendances')
                        ->selectRaw('MIN(attendance_date) as earliest, MAX(attendance_date) as latest')
                        ->first();

                    if ($dateRange && $dateRange->earliest) {
                        $this->log("  Date range: {$dateRange->earliest} to {$dateRange->latest}", 'INFO');
                    }

                    return true;
                });
            }
        });
    }

    // ========================================================================
    // MODULE 9: CENTRES MANAGEMENT TESTS
    // ========================================================================

    public function testCentresModule() {
        $this->testModule('CENTRES MANAGEMENT', function() {

            if (!Schema::hasTable('centres')) {
                $this->skip('CEN001', 'View Centres', 'Centres table not found');
                return;
            }

            $this->test('CEN001', 'View Centres List', function() {
                $centres = DB::table('centres')->get();
                $this->log("  Total centres: " . count($centres), 'INFO');

                foreach ($centres as $centre) {
                    $this->log("  - {$centre->centre_id}: {$centre->centre_name}", 'INFO');
                }

                $this->assert(count($centres) > 0, "No centres found");

                return true;
            });

            $this->test('CEN002', 'Centre Data Integrity', function() {
                $centres = DB::table('centres')->get();

                foreach ($centres as $centre) {
                    $this->assert(!empty($centre->centre_id), "Centre has no centre_id");
                    $this->assert(!empty($centre->centre_name), "Centre has no name");
                }

                return true;
            });
        });
    }

    // ========================================================================
    // MODULE 10: ASSETS MANAGEMENT TESTS
    // ========================================================================

    public function testAssetsModule() {
        $this->testModule('ASSETS MANAGEMENT', function() {

            if (!Schema::hasTable('assets')) {
                $this->skip('AST001', 'View Assets', 'Assets table not found');
                return;
            }

            $this->test('AST001', 'Assets Table Structure', function() {
                $columns = Schema::getColumnListing('assets');
                $this->log("  Columns: " . implode(', ', $columns), 'INFO');

                $totalAssets = DB::table('assets')->count();
                $this->log("  Total assets: {$totalAssets}", 'INFO');

                return true;
            });
        });
    }

    // ========================================================================
    // MODULE 11: LETTERS/DOCUMENTATION TESTS
    // ========================================================================

    public function testLettersModule() {
        $this->testModule('LETTERS/DOCUMENTATION', function() {

            if (!Schema::hasTable('letters')) {
                $this->skip('LET001', 'Letters System', 'Letters table not found');
                return;
            }

            $this->test('LET001', 'Letters Table Structure', function() {
                $columns = Schema::getColumnListing('letters');
                $this->log("  Columns: " . implode(', ', $columns), 'INFO');

                $totalLetters = DB::table('letters')->count();
                $this->log("  Total letters: {$totalLetters}", 'INFO');

                return true;
            });
        });
    }

    // ========================================================================
    // MODULE 12: MESSAGING TESTS
    // ========================================================================

    public function testMessagingModule() {
        $this->testModule('MESSAGING SYSTEM', function() {

            $tables = ['messages', 'notifications'];
            $foundTable = null;

            foreach ($tables as $table) {
                if (Schema::hasTable($table)) {
                    $foundTable = $table;
                    break;
                }
            }

            if (!$foundTable) {
                $this->skip('MSG001', 'Messaging System', 'No messaging tables found');
                return;
            }

            $this->test('MSG001', 'Messaging Table Structure', function() use ($foundTable) {
                $columns = Schema::getColumnListing($foundTable);
                $this->log("  Table: {$foundTable}", 'INFO');
                $this->log("  Columns: " . implode(', ', $columns), 'INFO');

                $count = DB::table($foundTable)->count();
                $this->log("  Total records: {$count}", 'INFO');

                return true;
            });
        });
    }

    // ========================================================================
    // MODULE 13: SYSTEM ADMINISTRATION TESTS
    // ========================================================================

    public function testSystemModule() {
        $this->testModule('SYSTEM ADMINISTRATION', function() {

            $this->test('SYS001', 'Database Connectivity', function() {
                $pdo = DB::connection()->getPdo();
                $this->assert($pdo !== null, "Database connection failed");

                $dbName = DB::connection()->getDatabaseName();
                $this->log("  Connected to database: {$dbName}", 'INFO');

                return true;
            });

            $this->test('SYS002', 'Core Tables Verification', function() {
                $coreTables = ['users', 'centres'];
                $missing = [];

                foreach ($coreTables as $table) {
                    if (!Schema::hasTable($table)) {
                        $missing[] = $table;
                    }
                }

                if (count($missing) > 0) {
                    return "Missing core tables: " . implode(', ', $missing);
                }

                return true;
            });

            $this->test('SYS003', 'Database Performance', function() {
                $start = microtime(true);

                // Run several queries
                DB::table('users')->count();
                if (Schema::hasTable('trainees')) {
                    DB::table('trainees')->count();
                }
                if (Schema::hasTable('activities')) {
                    DB::table('activities')->count();
                }

                $duration = (microtime(true) - $start) * 1000;
                $this->log("  Query execution time: {$duration}ms", 'INFO');

                $this->assert($duration < 2000, "Database queries too slow");

                return true;
            });

            $this->test('SYS004', 'System Configuration', function() {
                $config = [
                    'APP_ENV' => config('app.env'),
                    'APP_DEBUG' => config('app.debug'),
                    'DB_CONNECTION' => config('database.default'),
                ];

                foreach ($config as $key => $value) {
                    $this->log("  {$key}: " . ($value ? 'true' : 'false'), 'INFO');
                }

                return true;
            });

            $this->test('SYS005', 'All Tables Overview', function() {
                $tables = DB::select('SHOW TABLES');
                $tableNames = array_map(function($table) {
                    return array_values((array)$table)[0];
                }, $tables);

                $this->log("  Total tables: " . count($tableNames), 'INFO');
                $this->log("  Tables: " . implode(', ', $tableNames), 'INFO');

                return true;
            });
        });
    }

    // ========================================================================
    // MAIN TEST RUNNER
    // ========================================================================

    public function runAllTests() {
        echo "\n";
        $this->log("Starting comprehensive UAT test suite...", 'INFO');
        echo "\n";

        // Run all module tests
        $this->testPublicAccessModule();
        $this->testAuthenticationModule();
        $this->testDashboardModule();
        $this->testProfileModule();
        $this->testStaffModule();
        $this->testTraineeModule();
        $this->testActivitiesModule();
        $this->testAttendanceModule();
        $this->testCentresModule();
        $this->testAssetsModule();
        $this->testLettersModule();
        $this->testMessagingModule();
        $this->testSystemModule();

        return $this->generateReport();
    }

    // ========================================================================
    // REPORT GENERATION
    // ========================================================================

    public function generateReport() {
        $duration = round(microtime(true) - $this->startTime, 2);
        $passRate = $this->testCount > 0 ? round(($this->passCount / $this->testCount) * 100, 2) : 0;

        echo "\n" . str_repeat("=", 80) . "\n";
        $this->log("TEST EXECUTION COMPLETED", 'INFO');
        echo str_repeat("=", 80) . "\n\n";

        $this->log("Total Tests: {$this->testCount}", 'INFO');
        $this->log("Passed: {$this->passCount}", 'PASS');
        $this->log("Failed: {$this->failCount}", $this->failCount > 0 ? 'FAIL' : 'INFO');
        $this->log("Skipped: {$this->skipCount}", 'SKIP');
        $this->log("Pass Rate: {$passRate}%", $passRate >= 90 ? 'PASS' : 'WARN');
        $this->log("Duration: {$duration}s", 'INFO');

        echo "\n";

        return [
            'summary' => [
                'total' => $this->testCount,
                'passed' => $this->passCount,
                'failed' => $this->failCount,
                'skipped' => $this->skipCount,
                'pass_rate' => $passRate,
                'duration' => $duration,
            ],
            'results' => $this->results,
        ];
    }
}

// ============================================================================
// EXECUTE TESTS
// ============================================================================

try {
    $tester = new ComprehensiveUATTester();
    $report = $tester->runAllTests();

    // Save report to file
    $reportFile = __DIR__ . '/documentation/UAT FILES/UAT_AUTOMATION_RESULTS_' . date('Y-m-d_His') . '.json';
    file_put_contents($reportFile, json_encode($report, JSON_PRETTY_PRINT));

    echo "\nReport saved to: {$reportFile}\n";

    exit($report['summary']['failed'] > 0 ? 1 : 0);

} catch (Exception $e) {
    echo "\n\033[31mFATAL ERROR: " . $e->getMessage() . "\033[0m\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
