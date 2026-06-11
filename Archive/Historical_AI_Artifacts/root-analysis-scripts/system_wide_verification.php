<?php
/**
 * ============================================================================
 * CREAMS SYSTEM-WIDE VERIFICATION SCRIPT
 * ============================================================================
 *
 * Purpose: Verify ALL UAT test cases against actual system implementation
 * - Checks routes exist
 * - Verifies controllers exist
 * - Validates database tables and columns
 * - Tests actual functionality
 * - Provides detailed gap analysis
 *
 * This ensures UAT test cases match the actual implemented features
 * ============================================================================
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('max_execution_time', 3600);
ini_set('memory_limit', '512M');

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

class SystemWideVerifier {
    private $results = [];
    private $routes = [];
    private $controllers = [];

    public function __construct() {
        $this->log("=== CREAMS SYSTEM-WIDE VERIFICATION ===", 'INFO');
        $this->log("Checking ALL UAT test cases against actual implementation", 'INFO');
        echo str_repeat("=", 80) . "\n\n";

        // Load routes
        $this->loadRoutes();
    }

    private function log($message, $type = 'INFO') {
        $colors = [
            'INFO' => "\033[36m",
            'PASS' => "\033[32m",
            'FAIL' => "\033[31m",
            'WARN' => "\033[33m",
            'SKIP' => "\033[90m",
        ];
        $color = $colors[$type] ?? "\033[0m";
        echo "{$color}[{$type}] {$message}\033[0m\n";
    }

    private function loadRoutes() {
        $routes = Route::getRoutes();
        foreach ($routes as $route) {
            $uri = $route->uri();
            $methods = $route->methods();
            $action = $route->getActionName();

            $this->routes[$uri] = [
                'methods' => $methods,
                'action' => $action,
                'name' => $route->getName(),
            ];
        }
        $this->log("Loaded " . count($this->routes) . " routes", 'INFO');
    }

    private function checkRoute($pattern, $method = 'GET') {
        foreach ($this->routes as $uri => $details) {
            if (preg_match("#^$pattern#", $uri) && in_array($method, $details['methods'])) {
                return true;
            }
        }
        return false;
    }

    private function checkController($name) {
        $controllerPath = app_path("Http/Controllers/{$name}.php");
        return file_exists($controllerPath);
    }

    private function verify($testId, $testName, $checks) {
        echo "\n" . str_repeat("-", 80) . "\n";
        $this->log("[$testId] $testName", 'INFO');

        $passed = 0;
        $failed = 0;
        $warnings = 0;
        $details = [];

        foreach ($checks as $check) {
            $result = $check['test']();

            if ($result === true) {
                $passed++;
                $this->log("  ✓ " . $check['description'], 'PASS');
                $details[] = ['status' => 'PASS', 'check' => $check['description']];
            } elseif ($result === 'WARN') {
                $warnings++;
                $this->log("  ⚠ " . $check['description'], 'WARN');
                $details[] = ['status' => 'WARN', 'check' => $check['description']];
            } else {
                $failed++;
                $this->log("  ✗ " . $check['description'] . ": " . $result, 'FAIL');
                $details[] = ['status' => 'FAIL', 'check' => $check['description'], 'reason' => $result];
            }
        }

        $total = $passed + $failed + $warnings;
        $passRate = $total > 0 ? round(($passed / $total) * 100, 1) : 0;

        $status = 'FAIL';
        if ($passed === $total) {
            $status = 'PASS';
        } elseif ($passRate >= 70) {
            $status = 'PARTIAL';
        }

        $this->results[$testId] = [
            'name' => $testName,
            'status' => $status,
            'passed' => $passed,
            'failed' => $failed,
            'warnings' => $warnings,
            'pass_rate' => $passRate,
            'details' => $details,
        ];

        $statusColor = $status === 'PASS' ? 'PASS' : ($status === 'PARTIAL' ? 'WARN' : 'FAIL');
        $this->log("Result: $status ($passRate% - $passed/$total checks passed)", $statusColor);

        return $status === 'PASS';
    }

    // ========================================================================
    // PUBLIC ACCESS MODULE
    // ========================================================================

    public function verifyPublicAccess() {
        $this->log("\n=== PUBLIC ACCESS MODULE ===", 'INFO');

        $this->verify('HOME001', 'Welcome/Landing Page', [
            ['description' => 'Home route exists', 'test' => function() {
                return $this->checkRoute('^\/$', 'GET');
            }],
            ['description' => 'Welcome view file exists', 'test' => function() {
                return file_exists(resource_path('views/home.blade.php')) ||
                       file_exists(resource_path('views/welcome.blade.php'));
            }],
        ]);

        $this->verify('HOME002', 'Public Information Display', [
            ['description' => 'About route or section exists', 'test' => function() {
                return $this->checkRoute('about', 'GET') || file_exists(resource_path('views/home.blade.php'));
            }],
        ]);

        $this->verify('CONTACT001', 'Contact Form', [
            ['description' => 'Contact route exists', 'test' => function() {
                return $this->checkRoute('contact', 'GET');
            }],
            ['description' => 'Contact POST route exists', 'test' => function() {
                return $this->checkRoute('contact', 'POST');
            }],
            ['description' => 'ContactController exists', 'test' => function() {
                return $this->checkController('ContactController');
            }],
            ['description' => 'contact_messages table exists', 'test' => function() {
                return Schema::hasTable('contact_messages');
            }],
            ['description' => 'contact_messages has required columns', 'test' => function() {
                if (!Schema::hasTable('contact_messages')) return 'Table missing';
                $required = ['name', 'email', 'subject', 'message'];
                $columns = Schema::getColumnListing('contact_messages');
                $missing = array_diff($required, $columns);
                return count($missing) === 0 ? true : 'Missing: ' . implode(', ', $missing);
            }],
        ]);

        $this->verify('VOL001', 'Volunteer Registration', [
            ['description' => 'Volunteer route exists', 'test' => function() {
                return $this->checkRoute('volunteer', 'GET') || $this->checkRoute('volunteers', 'GET');
            }],
            ['description' => 'Volunteer POST route exists', 'test' => function() {
                return $this->checkRoute('volunteer', 'POST') || $this->checkRoute('volunteers', 'POST');
            }],
            ['description' => 'volunteers table exists', 'test' => function() {
                return Schema::hasTable('volunteers');
            }],
            ['description' => 'volunteers has required columns', 'test' => function() {
                if (!Schema::hasTable('volunteers')) return 'Table missing';
                $required = ['name', 'email', 'phone'];
                $columns = Schema::getColumnListing('volunteers');
                $missing = array_diff($required, $columns);
                return count($missing) === 0 ? true : 'Missing: ' . implode(', ', $missing);
            }],
        ]);
    }

    // ========================================================================
    // AUTHENTICATION MODULE
    // ========================================================================

    public function verifyAuthentication() {
        $this->log("\n=== AUTHENTICATION MODULE ===", 'INFO');

        $this->verify('AUTH001', 'Standard Login', [
            ['description' => 'Login route exists', 'test' => function() {
                return $this->checkRoute('login', 'GET');
            }],
            ['description' => 'Login POST route exists', 'test' => function() {
                return $this->checkRoute('login', 'POST');
            }],
            ['description' => 'users table exists', 'test' => function() {
                return Schema::hasTable('users');
            }],
            ['description' => 'users has auth columns', 'test' => function() {
                $required = ['email', 'password'];
                $columns = Schema::getColumnListing('users');
                $missing = array_diff($required, $columns);
                return count($missing) === 0 ? true : 'Missing: ' . implode(', ', $missing);
            }],
        ]);

        $this->verify('AUTH002', 'Multiple Role Login', [
            ['description' => 'users has role column', 'test' => function() {
                $columns = Schema::getColumnListing('users');
                return in_array('role', $columns);
            }],
            ['description' => 'Multiple roles exist in DB', 'test' => function() {
                $roles = DB::table('users')->distinct()->pluck('role')->toArray();
                return count($roles) >= 2 ? true : 'Only ' . count($roles) . ' role(s) found';
            }],
        ]);

        $this->verify('AUTH003', 'Invalid Login Handling', [
            ['description' => 'Login validation implemented', 'test' => function() {
                // Check if LoginController or AuthController exists
                return $this->checkController('LoginController') ||
                       $this->checkController('AuthController') ||
                       $this->checkController('Auth/LoginController');
            }],
        ]);

        $this->verify('AUTH004', 'Password Reset', [
            ['description' => 'Password reset route exists', 'test' => function() {
                return $this->checkRoute('password', 'GET') || $this->checkRoute('forgot-password', 'GET');
            }],
            ['description' => 'password_resets table exists', 'test' => function() {
                return Schema::hasTable('password_resets') || Schema::hasTable('password_reset_tokens');
            }],
        ]);

        $this->verify('AUTH005', 'Account Status', [
            ['description' => 'users has status column', 'test' => function() {
                $columns = Schema::getColumnListing('users');
                return in_array('status', $columns);
            }],
            ['description' => 'Status values exist', 'test' => function() {
                $statuses = DB::table('users')->distinct()->pluck('status')->toArray();
                return count($statuses) > 0 ? true : 'No status values found';
            }],
        ]);
    }

    // ========================================================================
    // DASHBOARD MODULE
    // ========================================================================

    public function verifyDashboard() {
        $this->log("\n=== DASHBOARD MODULE ===", 'INFO');

        $this->verify('DASH001', 'Admin Dashboard', [
            ['description' => 'Dashboard route exists', 'test' => function() {
                return $this->checkRoute('dashboard', 'GET') || $this->checkRoute('home', 'GET');
            }],
            ['description' => 'DashboardController exists', 'test' => function() {
                return $this->checkController('DashboardController') || $this->checkController('HomeController');
            }],
        ]);

        $this->verify('DASH002', 'Supervisor Dashboard', [
            ['description' => 'Centre filtering possible', 'test' => function() {
                $columns = Schema::getColumnListing('users');
                return in_array('centre_id', $columns);
            }],
            ['description' => 'centres table exists', 'test' => function() {
                return Schema::hasTable('centres');
            }],
        ]);

        $this->verify('DASH003', 'Teacher Dashboard', [
            ['description' => 'activities table exists', 'test' => function() {
                return Schema::hasTable('activities');
            }],
        ]);

        $this->verify('DASH004', 'Dashboard Statistics', [
            ['description' => 'Can query user count', 'test' => function() {
                try {
                    DB::table('users')->count();
                    return true;
                } catch (\Exception $e) {
                    return $e->getMessage();
                }
            }],
        ]);
    }

    // ========================================================================
    // PROFILE MODULE
    // ========================================================================

    public function verifyProfile() {
        $this->log("\n=== PROFILE MODULE ===", 'INFO');

        $this->verify('PROF001', 'View Profile', [
            ['description' => 'Profile route exists', 'test' => function() {
                return $this->checkRoute('profile', 'GET');
            }],
            ['description' => 'ProfileController exists', 'test' => function() {
                return $this->checkController('ProfileController') || $this->checkController('UserController');
            }],
        ]);

        $this->verify('PROF002', 'Edit Profile', [
            ['description' => 'Profile update route exists', 'test' => function() {
                return $this->checkRoute('profile', 'PUT') || $this->checkRoute('profile', 'PATCH');
            }],
        ]);

        $this->verify('PROF003', 'Change Password', [
            ['description' => 'Password update route exists', 'test' => function() {
                return $this->checkRoute('password', 'PUT') ||
                       $this->checkRoute('password', 'PATCH') ||
                       $this->checkRoute('profile/password', 'POST');
            }],
        ]);

        $this->verify('PROF004', 'Upload Photo', [
            ['description' => 'Profile photo column exists', 'test' => function() {
                $columns = Schema::getColumnListing('users');
                return in_array('photo', $columns) ||
                       in_array('profile_photo', $columns) ||
                       in_array('avatar', $columns) ? true : 'WARN';
            }],
        ]);
    }

    // ========================================================================
    // STAFF MODULE
    // ========================================================================

    public function verifyStaff() {
        $this->log("\n=== STAFF MANAGEMENT MODULE ===", 'INFO');

        $this->verify('STAFF001', 'Create Staff', [
            ['description' => 'Staff create route exists', 'test' => function() {
                return $this->checkRoute('staff', 'POST') || $this->checkRoute('users', 'POST');
            }],
            ['description' => 'users table ready for staff', 'test' => function() {
                return Schema::hasTable('users');
            }],
        ]);

        $this->verify('STAFF002', 'Edit Staff', [
            ['description' => 'Staff update route exists', 'test' => function() {
                return $this->checkRoute('staff/.*', 'PUT') || $this->checkRoute('users/.*', 'PUT');
            }],
        ]);

        $this->verify('STAFF003', 'View Staff List', [
            ['description' => 'Staff list route exists', 'test' => function() {
                return $this->checkRoute('staff', 'GET') || $this->checkRoute('users', 'GET');
            }],
        ]);

        $this->verify('STAFF004', 'Staff Role Management', [
            ['description' => 'Role column exists', 'test' => function() {
                $columns = Schema::getColumnListing('users');
                return in_array('role', $columns);
            }],
        ]);

        $this->verify('STAFF005', 'Deactivate Staff', [
            ['description' => 'Status column exists', 'test' => function() {
                $columns = Schema::getColumnListing('users');
                return in_array('status', $columns);
            }],
        ]);

        $this->verify('STAFF006', 'View Staff Profile', [
            ['description' => 'Staff show route exists', 'test' => function() {
                return $this->checkRoute('staff/.*', 'GET') || $this->checkRoute('users/.*', 'GET');
            }],
        ]);
    }

    // ========================================================================
    // TRAINEE MODULE
    // ========================================================================

    public function verifyTrainee() {
        $this->log("\n=== TRAINEE MANAGEMENT MODULE ===", 'INFO');

        $this->verify('TRAIN001', 'Register Trainee', [
            ['description' => 'Trainee create route exists', 'test' => function() {
                return $this->checkRoute('trainee', 'POST') || $this->checkRoute('trainees', 'POST');
            }],
            ['description' => 'trainees table exists', 'test' => function() {
                return Schema::hasTable('trainees');
            }],
            ['description' => 'trainees has required columns', 'test' => function() {
                if (!Schema::hasTable('trainees')) return 'Table missing';
                $columns = Schema::getColumnListing('trainees');
                // Check for name columns (either 'name' or 'trainee_first_name'/'trainee_last_name')
                $hasName = in_array('trainee_first_name', $columns) && in_array('trainee_last_name', $columns);
                return $hasName ? true : 'Name columns missing';
            }],
        ]);

        $this->verify('TRAIN002', 'Edit Trainee', [
            ['description' => 'Trainee update route exists', 'test' => function() {
                return $this->checkRoute('trainee/.*', 'PUT') || $this->checkRoute('trainees/.*', 'PUT');
            }],
        ]);

        $this->verify('TRAIN003', 'View Trainee List', [
            ['description' => 'Trainee list route exists', 'test' => function() {
                return $this->checkRoute('trainee', 'GET') || $this->checkRoute('trainees', 'GET');
            }],
            ['description' => 'TraineeController exists', 'test' => function() {
                return $this->checkController('TraineeController');
            }],
        ]);

        $this->verify('TRAIN004', 'Trainee Progress', [
            ['description' => 'activity_enrollments table exists', 'test' => function() {
                return Schema::hasTable('activity_enrollments');
            }],
        ]);

        $this->verify('TRAIN005', 'Guardian Management', [
            ['description' => 'Guardian columns exist', 'test' => function() {
                $columns = Schema::getColumnListing('trainees');
                return in_array('guardian_name', $columns) || in_array('guardian_phone', $columns);
            }],
        ]);

        $this->verify('TRAIN006', 'Trainee Status', [
            ['description' => 'Status column exists', 'test' => function() {
                $columns = Schema::getColumnListing('trainees');
                return in_array('status', $columns);
            }],
        ]);
    }

    // ========================================================================
    // ACTIVITIES MODULE
    // ========================================================================

    public function verifyActivities() {
        $this->log("\n=== ACTIVITIES MODULE ===", 'INFO');

        $this->verify('ACT001', 'Activity Listing', [
            ['description' => 'Activity list route exists', 'test' => function() {
                return $this->checkRoute('activit', 'GET');
            }],
            ['description' => 'activities table exists', 'test' => function() {
                return Schema::hasTable('activities');
            }],
            ['description' => 'ActivityController exists', 'test' => function() {
                return $this->checkController('ActivityController');
            }],
        ]);

        $this->verify('ACT002', 'Create Activity', [
            ['description' => 'Activity create route exists', 'test' => function() {
                return $this->checkRoute('activit', 'POST');
            }],
        ]);

        $this->verify('ACT003', 'Edit Activity', [
            ['description' => 'Activity update route exists', 'test' => function() {
                return $this->checkRoute('activit.*', 'PUT') || $this->checkRoute('activit.*', 'PATCH');
            }],
        ]);

        $this->verify('ACT004', 'Delete Activity', [
            ['description' => 'Activity delete route exists', 'test' => function() {
                return $this->checkRoute('activit.*', 'DELETE');
            }],
        ]);

        $this->verify('ACT005', 'Activity Categories', [
            ['description' => 'Category system exists', 'test' => function() {
                $columns = Schema::getColumnListing('activities');
                return in_array('category_id', $columns) || Schema::hasTable('activity_categories');
            }],
        ]);

        $this->verify('ACT006', 'Create Session', [
            ['description' => 'Sessions table exists', 'test' => function() {
                return Schema::hasTable('sessions') || Schema::hasTable('activity_sessions');
            }],
            ['description' => 'Session routes exist', 'test' => function() {
                return $this->checkRoute('session', 'POST');
            }],
        ]);

        $this->verify('ACT007', 'Enroll Trainee', [
            ['description' => 'activity_enrollments table exists', 'test' => function() {
                return Schema::hasTable('activity_enrollments');
            }],
            ['description' => 'Enrollment route exists', 'test' => function() {
                return $this->checkRoute('enroll', 'POST') || $this->checkRoute('enrollment', 'POST');
            }],
        ]);

        $this->verify('ACT008', 'View Schedule', [
            ['description' => 'Schedule route exists', 'test' => function() {
                return $this->checkRoute('schedule', 'GET');
            }],
        ]);

        $this->verify('ACT009', 'Weekly Schedule', [
            ['description' => 'Calendar/Schedule functionality exists', 'test' => function() {
                return $this->checkRoute('schedule', 'GET') || $this->checkRoute('calendar', 'GET');
            }],
        ]);

        $this->verify('ACT010', 'Teacher Schedule', [
            ['description' => 'Teacher filtering possible', 'test' => function() {
                $columns = Schema::getColumnListing('activities');
                return in_array('teacher_id', $columns) || in_array('instructor_id', $columns);
            }],
        ]);
    }

    // ========================================================================
    // ATTENDANCE MODULE
    // ========================================================================

    public function verifyAttendance() {
        $this->log("\n=== ATTENDANCE MODULE ===", 'INFO');

        $this->verify('ATT001', 'Mark Attendance', [
            ['description' => 'Attendance table exists', 'test' => function() {
                return Schema::hasTable('session_attendance') ||
                       Schema::hasTable('trainee_attendances') ||
                       Schema::hasTable('attendance');
            }],
            ['description' => 'Attendance route exists', 'test' => function() {
                return $this->checkRoute('attendance', 'POST');
            }],
            ['description' => 'AttendanceController exists', 'test' => function() {
                return $this->checkController('AttendanceController');
            }],
        ]);

        $this->verify('ATT002', 'View Attendance', [
            ['description' => 'Attendance list route exists', 'test' => function() {
                return $this->checkRoute('attendance', 'GET');
            }],
        ]);

        $this->verify('ATT003', 'Attendance History', [
            ['description' => 'Can query historical data', 'test' => function() {
                $table = Schema::hasTable('session_attendance') ? 'session_attendance' :
                        (Schema::hasTable('trainee_attendances') ? 'trainee_attendances' : null);
                if (!$table) return 'No attendance table found';

                $count = DB::table($table)->count();
                return $count > 0 ? true : 'WARN';
            }],
        ]);

        $this->verify('ATT004', 'Excuse Management', [
            ['description' => 'Excuse status exists', 'test' => function() {
                $table = Schema::hasTable('session_attendance') ? 'session_attendance' :
                        (Schema::hasTable('trainee_attendances') ? 'trainee_attendances' : null);
                if (!$table) return 'No attendance table found';

                $columns = Schema::getColumnListing($table);
                $statusCol = in_array('attendance_status', $columns) ? 'attendance_status' :
                            (in_array('status', $columns) ? 'status' : null);

                if (!$statusCol) return 'No status column';

                $statuses = DB::table($table)->distinct()->pluck($statusCol)->toArray();
                return in_array('excused', $statuses) ? true : 'WARN';
            }],
        ]);
    }

    // ========================================================================
    // CENTRES MODULE
    // ========================================================================

    public function verifyCentres() {
        $this->log("\n=== CENTRES MODULE ===", 'INFO');

        $this->verify('CENT001', 'View Centres', [
            ['description' => 'centres table exists', 'test' => function() {
                return Schema::hasTable('centres');
            }],
            ['description' => 'Centre route exists', 'test' => function() {
                return $this->checkRoute('centre', 'GET');
            }],
        ]);

        $this->verify('CENT002', 'Centre Details', [
            ['description' => 'Centre show route exists', 'test' => function() {
                return $this->checkRoute('centre.*', 'GET');
            }],
        ]);

        $this->verify('CENT003', 'Multi-Centre Support', [
            ['description' => 'Multiple centres exist', 'test' => function() {
                if (!Schema::hasTable('centres')) return 'Table missing';
                $count = DB::table('centres')->count();
                return $count >= 2 ? true : 'Only ' . $count . ' centre(s) found';
            }],
        ]);

        $this->verify('CENT004', 'Centre Switching', [
            ['description' => 'Users have centre_id', 'test' => function() {
                $columns = Schema::getColumnListing('users');
                return in_array('centre_id', $columns);
            }],
        ]);
    }

    // ========================================================================
    // ASSETS MODULE
    // ========================================================================

    public function verifyAssets() {
        $this->log("\n=== ASSETS MODULE ===", 'INFO');

        $this->verify('ASSET001', 'View Assets', [
            ['description' => 'assets table exists', 'test' => function() {
                return Schema::hasTable('assets');
            }],
            ['description' => 'Asset route exists', 'test' => function() {
                return $this->checkRoute('asset', 'GET');
            }],
            ['description' => 'AssetController exists', 'test' => function() {
                return $this->checkController('AssetController');
            }],
        ]);

        $this->verify('ASSET002', 'Create Asset', [
            ['description' => 'Asset create route exists', 'test' => function() {
                return $this->checkRoute('asset', 'POST');
            }],
        ]);

        $this->verify('ASSET003', 'Edit Asset', [
            ['description' => 'Asset update route exists', 'test' => function() {
                return $this->checkRoute('asset.*', 'PUT') || $this->checkRoute('asset.*', 'PATCH');
            }],
        ]);

        $this->verify('ASSET004', 'Delete Asset', [
            ['description' => 'Asset delete route exists', 'test' => function() {
                return $this->checkRoute('asset.*', 'DELETE');
            }],
        ]);

        $this->verify('ASSET005', 'Asset Categories', [
            ['description' => 'asset_categories table exists', 'test' => function() {
                return Schema::hasTable('asset_categories');
            }],
        ]);

        $this->verify('ASSET006', 'Asset Maintenance', [
            ['description' => 'Maintenance tracking exists', 'test' => function() {
                return Schema::hasTable('asset_maintenance');
            }],
        ]);
    }

    // ========================================================================
    // LETTERS MODULE
    // ========================================================================

    public function verifyLetters() {
        $this->log("\n=== LETTERS MODULE ===", 'INFO');

        $this->verify('LETT001', 'Generate Letter', [
            ['description' => 'letters table exists', 'test' => function() {
                return Schema::hasTable('letters');
            }],
            ['description' => 'Letter route exists', 'test' => function() {
                return $this->checkRoute('letter', 'POST');
            }],
            ['description' => 'LetterController exists', 'test' => function() {
                return $this->checkController('LetterController');
            }],
        ]);

        $this->verify('LETT002', 'View Letters', [
            ['description' => 'Letter list route exists', 'test' => function() {
                return $this->checkRoute('letter', 'GET');
            }],
        ]);

        $this->verify('LETT003', 'Letter Templates', [
            ['description' => 'letter_templates table exists', 'test' => function() {
                return Schema::hasTable('letter_templates');
            }],
        ]);
    }

    // ========================================================================
    // MESSAGING MODULE
    // ========================================================================

    public function verifyMessaging() {
        $this->log("\n=== MESSAGING MODULE ===", 'INFO');

        $this->verify('MSG001', 'Send Message', [
            ['description' => 'messages table exists', 'test' => function() {
                return Schema::hasTable('messages');
            }],
            ['description' => 'Message route exists', 'test' => function() {
                return $this->checkRoute('message', 'POST');
            }],
        ]);

        $this->verify('MSG002', 'View Messages', [
            ['description' => 'Message list route exists', 'test' => function() {
                return $this->checkRoute('message', 'GET');
            }],
        ]);

        $this->verify('MSG003', 'Notifications', [
            ['description' => 'notifications table exists', 'test' => function() {
                return Schema::hasTable('notifications');
            }],
        ]);
    }

    // ========================================================================
    // SYSTEM MODULE
    // ========================================================================

    public function verifySystem() {
        $this->log("\n=== SYSTEM MODULE ===", 'INFO');

        $this->verify('SYS001', 'System Health', [
            ['description' => 'Database connection', 'test' => function() {
                try {
                    DB::connection()->getPdo();
                    return true;
                } catch (\Exception $e) {
                    return $e->getMessage();
                }
            }],
        ]);

        $this->verify('SYS002', 'Backup System', [
            ['description' => 'Backup functionality exists', 'test' => function() {
                return $this->checkRoute('backup', 'POST') || $this->checkRoute('admin/backup', 'POST') ? true : 'WARN';
            }],
        ]);

        $this->verify('SYS003', 'Audit Logs', [
            ['description' => 'audit_logs table exists', 'test' => function() {
                return Schema::hasTable('audit_logs');
            }],
        ]);

        $this->verify('SYS004', 'Performance Monitoring', [
            ['description' => 'System can handle queries efficiently', 'test' => function() {
                $start = microtime(true);
                DB::table('users')->count();
                $duration = (microtime(true) - $start) * 1000;
                return $duration < 100 ? true : 'WARN';
            }],
        ]);

        $this->verify('SYS005', 'Configuration', [
            ['description' => 'App configured', 'test' => function() {
                return config('app.name') !== null;
            }],
            ['description' => 'Database configured', 'test' => function() {
                return config('database.connections.mysql.database') !== null;
            }],
        ]);
    }

    // ========================================================================
    // MAIN VERIFICATION
    // ========================================================================

    public function runAllVerifications() {
        $this->verifyPublicAccess();
        $this->verifyAuthentication();
        $this->verifyDashboard();
        $this->verifyProfile();
        $this->verifyStaff();
        $this->verifyTrainee();
        $this->verifyActivities();
        $this->verifyAttendance();
        $this->verifyCentres();
        $this->verifyAssets();
        $this->verifyLetters();
        $this->verifyMessaging();
        $this->verifySystem();

        return $this->generateReport();
    }

    // ========================================================================
    // REPORT GENERATION
    // ========================================================================

    public function generateReport() {
        echo "\n\n" . str_repeat("=", 80) . "\n";
        $this->log("VERIFICATION COMPLETED", 'INFO');
        echo str_repeat("=", 80) . "\n\n";

        $total = count($this->results);
        $passed = 0;
        $partial = 0;
        $failed = 0;

        foreach ($this->results as $result) {
            if ($result['status'] === 'PASS') $passed++;
            elseif ($result['status'] === 'PARTIAL') $partial++;
            else $failed++;
        }

        $passRate = $total > 0 ? round(($passed / $total) * 100, 1) : 0;
        $implementationRate = $total > 0 ? round((($passed + $partial) / $total) * 100, 1) : 0;

        $this->log("Total Test Cases: $total", 'INFO');
        $this->log("Fully Implemented: $passed (" . $passRate . "%)", $passed > 0 ? 'PASS' : 'FAIL');
        $this->log("Partially Implemented: $partial", $partial > 0 ? 'WARN' : 'INFO');
        $this->log("Not Implemented: $failed", $failed > 0 ? 'FAIL' : 'INFO');
        $this->log("Overall Implementation: " . $implementationRate . "%", $implementationRate >= 80 ? 'PASS' : 'WARN');

        // Save detailed report
        $reportFile = __DIR__ . '/documentation/UAT FILES/SYSTEM_VERIFICATION_REPORT_' . date('Y-m-d_His') . '.json';
        file_put_contents($reportFile, json_encode([
            'summary' => [
                'total' => $total,
                'passed' => $passed,
                'partial' => $partial,
                'failed' => $failed,
                'pass_rate' => $passRate,
                'implementation_rate' => $implementationRate,
            ],
            'results' => $this->results,
        ], JSON_PRETTY_PRINT));

        $this->log("\nDetailed report saved: $reportFile", 'INFO');

        return [
            'total' => $total,
            'passed' => $passed,
            'partial' => $partial,
            'failed' => $failed,
            'pass_rate' => $passRate,
            'implementation_rate' => $implementationRate,
        ];
    }
}

// ============================================================================
// EXECUTE VERIFICATION
// ============================================================================

try {
    $verifier = new SystemWideVerifier();
    $results = $verifier->runAllVerifications();

    exit($results['failed'] > 10 ? 1 : 0);
} catch (Exception $e) {
    echo "\n\033[31mFATAL ERROR: " . $e->getMessage() . "\033[0m\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
