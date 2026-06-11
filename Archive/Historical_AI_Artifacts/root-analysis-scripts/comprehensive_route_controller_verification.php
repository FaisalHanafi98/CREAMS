<?php
/**
 * COMPREHENSIVE UAT VERIFICATION
 * Systematically verifies every UAT test case against actual implementation
 * Checks routes, controllers, database tables, and views
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

class ComprehensiveUATVerifier {
    private $routes = [];
    private $controllers = [];
    private $results = [];
    private $issueCount = 0;
    private $passCount = 0;

    public function __construct() {
        $this->loadRoutes();
        $this->loadControllers();
    }

    private function loadRoutes() {
        $routeCollection = Route::getRoutes();
        foreach ($routeCollection as $route) {
            $uri = $route->uri();
            $methods = $route->methods();
            $action = $route->getActionName();

            $this->routes[$uri] = [
                'methods' => $methods,
                'action' => $action,
                'name' => $route->getName()
            ];
        }
    }

    private function loadControllers() {
        $controllerPath = app_path('Http/Controllers');
        $this->scanControllersRecursive($controllerPath);
    }

    private function scanControllersRecursive($path, $namespace = 'App\\Http\\Controllers') {
        $items = scandir($path);
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') continue;

            $fullPath = $path . DIRECTORY_SEPARATOR . $item;

            if (is_dir($fullPath)) {
                $this->scanControllersRecursive($fullPath, $namespace . '\\' . $item);
            } elseif (pathinfo($item, PATHINFO_EXTENSION) === 'php') {
                $className = pathinfo($item, PATHINFO_FILENAME);
                $fullClassName = $namespace . '\\' . $className;
                $this->controllers[$fullClassName] = $fullPath;
            }
        }
    }

    private function checkRoute($pattern, $methods = ['GET'], $exactMatch = false) {
        $methods = is_array($methods) ? $methods : [$methods];

        foreach ($this->routes as $uri => $details) {
            if ($exactMatch) {
                $match = ($uri === $pattern);
            } else {
                $pattern = str_replace('{id}', '[^/]+', $pattern);
                $pattern = str_replace('{token}', '[^/]+', $pattern);
                $match = preg_match("#^" . $pattern . "$#", $uri);
            }

            if ($match) {
                foreach ($methods as $method) {
                    if (in_array($method, $details['methods'])) {
                        return [
                            'found' => true,
                            'uri' => $uri,
                            'methods' => $details['methods'],
                            'action' => $details['action']
                        ];
                    }
                }
            }
        }
        return ['found' => false];
    }

    private function checkController($controllerName) {
        foreach ($this->controllers as $fullName => $path) {
            if (strpos($fullName, $controllerName) !== false) {
                return [
                    'found' => true,
                    'class' => $fullName,
                    'path' => $path
                ];
            }
        }
        return ['found' => false];
    }

    private function checkTable($tableName) {
        return Schema::hasTable($tableName);
    }

    private function checkView($viewName) {
        $viewPath = resource_path('views/' . str_replace('.', '/', $viewName) . '.blade.php');
        return file_exists($viewPath);
    }

    public function verifyTestCase($testId, $testName, $requirements) {
        echo "\n" . str_repeat('=', 80) . "\n";
        echo "TEST: {$testId} - {$testName}\n";
        echo str_repeat('=', 80) . "\n";

        $passed = true;
        $issues = [];
        $findings = [];

        // Check required routes
        if (isset($requirements['routes'])) {
            foreach ($requirements['routes'] as $routeReq) {
                $result = $this->checkRoute(
                    $routeReq['pattern'],
                    $routeReq['methods'] ?? ['GET'],
                    $routeReq['exact'] ?? false
                );

                if ($result['found']) {
                    $findings[] = "✅ Route Found: {$result['uri']} [" . implode(',', $result['methods']) . "]";
                    if ($result['action']) {
                        $findings[] = "   → Controller: {$result['action']}";
                    }
                } else {
                    $passed = false;
                    $issues[] = "❌ Route Missing: {$routeReq['pattern']} [" . implode(',', $routeReq['methods'] ?? ['GET']) . "]";
                    if (isset($routeReq['alternative'])) {
                        $issues[] = "   ℹ️  Alternative: {$routeReq['alternative']}";
                    }
                }
            }
        }

        // Check required controllers
        if (isset($requirements['controllers'])) {
            foreach ($requirements['controllers'] as $controllerName) {
                $result = $this->checkController($controllerName);
                if ($result['found']) {
                    $findings[] = "✅ Controller Found: {$result['class']}";
                } else {
                    $passed = false;
                    $issues[] = "❌ Controller Missing: {$controllerName}";
                }
            }
        }

        // Check required tables
        if (isset($requirements['tables'])) {
            foreach ($requirements['tables'] as $tableName) {
                if ($this->checkTable($tableName)) {
                    $findings[] = "✅ Table Found: {$tableName}";
                } else {
                    $passed = false;
                    $issues[] = "❌ Table Missing: {$tableName}";
                }
            }
        }

        // Check required views
        if (isset($requirements['views'])) {
            foreach ($requirements['views'] as $viewName) {
                if ($this->checkView($viewName)) {
                    $findings[] = "✅ View Found: {$viewName}";
                } else {
                    $passed = false;
                    $issues[] = "❌ View Missing: {$viewName}";
                }
            }
        }

        // Display results
        if (!empty($findings)) {
            echo "\n✅ PASSING CHECKS:\n";
            foreach ($findings as $finding) {
                echo $finding . "\n";
            }
        }

        if (!empty($issues)) {
            echo "\n❌ ISSUES FOUND:\n";
            foreach ($issues as $issue) {
                echo $issue . "\n";
            }
        }

        // Final status
        echo "\n";
        if ($passed) {
            echo "🎯 STATUS: PASS ✅\n";
            $this->passCount++;
        } else {
            echo "⚠️  STATUS: NEEDS ATTENTION ❌\n";
            $this->issueCount++;
        }

        $this->results[$testId] = [
            'name' => $testName,
            'passed' => $passed,
            'issues' => $issues,
            'findings' => $findings
        ];

        return $passed;
    }

    public function runAllTests() {
        echo "\n";
        echo "╔══════════════════════════════════════════════════════════════════════════════╗\n";
        echo "║                    CREAMS COMPREHENSIVE UAT VERIFICATION                     ║\n";
        echo "║                     Systematic Test Case Verification                        ║\n";
        echo "╚══════════════════════════════════════════════════════════════════════════════╝\n";

        // PHASE 1: PUBLIC ACCESS
        echo "\n\n";
        echo "████████████████████████████████████████████████████████████████████████████████\n";
        echo "█                          PHASE 1: PUBLIC ACCESS                              █\n";
        echo "████████████████████████████████████████████████████████████████████████████████\n";

        $this->verifyTestCase('HOME001', 'Welcome/Landing Page Load', [
            'routes' => [
                ['pattern' => '/', 'methods' => ['GET'], 'exact' => true],
                ['pattern' => 'home', 'methods' => ['GET'], 'exact' => true]
            ],
            'views' => ['home']
        ]);

        $this->verifyTestCase('HOME002', 'Public Information Display', [
            'routes' => [
                ['pattern' => '/', 'methods' => ['GET'], 'exact' => true]
            ],
            'views' => ['home']
        ]);

        $this->verifyTestCase('CONTACT001', 'Contact Us Form Submission', [
            'routes' => [
                ['pattern' => 'contactus', 'methods' => ['GET'], 'exact' => true],
                ['pattern' => 'contact', 'methods' => ['POST'], 'exact' => true]
            ],
            'controllers' => ['ContactController'],
            'tables' => ['contact_messages'],
            'views' => ['contactus']
        ]);

        $this->verifyTestCase('CONTACT002', 'Contact Form Validation', [
            'routes' => [
                ['pattern' => 'contact', 'methods' => ['POST'], 'exact' => true]
            ],
            'controllers' => ['ContactController']
        ]);

        $this->verifyTestCase('VOL001', 'Volunteer Registration Form', [
            'routes' => [
                ['pattern' => 'volunteers/home', 'methods' => ['GET'], 'exact' => true],
                ['pattern' => 'volunteers', 'methods' => ['POST'], 'exact' => true]
            ],
            'controllers' => ['VolunteerController'],
            'tables' => ['volunteers'],
            'views' => ['volunteers.home']
        ]);

        $this->verifyTestCase('VOL002', 'Volunteer Form Validation', [
            'routes' => [
                ['pattern' => 'volunteers', 'methods' => ['POST'], 'exact' => true]
            ],
            'controllers' => ['VolunteerController']
        ]);

        // PHASE 2: AUTHENTICATION
        echo "\n\n";
        echo "████████████████████████████████████████████████████████████████████████████████\n";
        echo "█                          PHASE 2: AUTHENTICATION                             █\n";
        echo "████████████████████████████████████████████████████████████████████████████████\n";

        $this->verifyTestCase('AUTH001', 'Standard Login Functionality', [
            'routes' => [
                ['pattern' => 'login', 'methods' => ['GET'], 'exact' => true],
                ['pattern' => 'login', 'methods' => ['POST'], 'exact' => true]
            ],
            'controllers' => ['AuthenticationController'],
            'tables' => ['users'],
            'views' => ['auth.login']
        ]);

        $this->verifyTestCase('AUTH002', 'Enhanced Login with Multi-Factor', [
            'routes' => [
                ['pattern' => 'enhanced-login', 'methods' => ['GET'], 'exact' => true]
            ],
            'views' => ['auth.enhanced-login']
        ]);

        $this->verifyTestCase('AUTH003', 'User Registration Process', [
            'routes' => [
                ['pattern' => 'staffs/register', 'methods' => ['GET'], 'exact' => true],
                ['pattern' => 'staffs', 'methods' => ['POST'], 'exact' => true]
            ],
            'controllers' => ['Staff', 'UserController'],
            'tables' => ['users']
        ]);

        $this->verifyTestCase('AUTH004', 'Password Reset Flow', [
            'routes' => [
                ['pattern' => 'forgot-password', 'methods' => ['GET'], 'exact' => true],
                ['pattern' => 'forgot-password', 'methods' => ['POST'], 'exact' => true],
                ['pattern' => 'reset-password/{token}', 'methods' => ['GET']],
                ['pattern' => 'reset-password', 'methods' => ['POST'], 'exact' => true]
            ],
            'controllers' => ['AuthenticationController'],
            'tables' => ['password_resets']
        ]);

        $this->verifyTestCase('AUTH005', 'Logout Functionality', [
            'routes' => [
                ['pattern' => 'logout', 'methods' => ['POST', 'GET']]
            ],
            'controllers' => ['AuthenticationController']
        ]);

        // PHASE 3: DASHBOARDS
        echo "\n\n";
        echo "████████████████████████████████████████████████████████████████████████████████\n";
        echo "█                       PHASE 3: ROLE-SPECIFIC DASHBOARDS                      █\n";
        echo "████████████████████████████████████████████████████████████████████████████████\n";

        $this->verifyTestCase('DASH001', 'Admin Dashboard Display', [
            'routes' => [
                ['pattern' => 'dashboard', 'methods' => ['GET'], 'exact' => true],
                ['pattern' => 'admin/dashboard', 'methods' => ['GET'], 'exact' => true]
            ],
            'controllers' => ['DashboardController'],
            'views' => ['dashboard']
        ]);

        $this->verifyTestCase('DASH002', 'Teacher Dashboard Display', [
            'routes' => [
                ['pattern' => 'dashboard', 'methods' => ['GET'], 'exact' => true]
            ],
            'controllers' => ['DashboardController']
        ]);

        $this->verifyTestCase('DASH003', 'Supervisor Dashboard Display', [
            'routes' => [
                ['pattern' => 'dashboard', 'methods' => ['GET'], 'exact' => true]
            ],
            'controllers' => ['DashboardController']
        ]);

        // PHASE 4: PROFILE
        echo "\n\n";
        echo "████████████████████████████████████████████████████████████████████████████████\n";
        echo "█                            PHASE 4: PROFILE MODULE                           █\n";
        echo "████████████████████████████████████████████████████████████████████████████████\n";

        $this->verifyTestCase('PROF001', 'View Own Profile', [
            'routes' => [
                ['pattern' => 'profile', 'methods' => ['GET'], 'exact' => true],
                ['pattern' => 'users/profile/{id}', 'methods' => ['GET']]
            ],
            'controllers' => ['UserProfileController'],
            'views' => ['profile', 'users.profile']
        ]);

        $this->verifyTestCase('PROF002', 'Edit Profile Information', [
            'routes' => [
                ['pattern' => 'profile/update', 'methods' => ['POST', 'PUT', 'PATCH']],
                ['pattern' => 'profile', 'methods' => ['PUT', 'PATCH']]
            ],
            'controllers' => ['UserProfileController']
        ]);

        $this->verifyTestCase('PROF003', 'Change Password', [
            'routes' => [
                ['pattern' => 'profile/change-password', 'methods' => ['POST']],
                ['pattern' => 'profile/password', 'methods' => ['PUT', 'PATCH', 'POST']]
            ],
            'controllers' => ['UserProfileController']
        ]);

        $this->verifyTestCase('PROF004', 'Update Profile Image', [
            'routes' => [
                ['pattern' => 'profile/update', 'methods' => ['POST']]
            ],
            'controllers' => ['UserProfileController']
        ]);

        // PHASE 5: STAFF MODULE
        echo "\n\n";
        echo "████████████████████████████████████████████████████████████████████████████████\n";
        echo "█                            PHASE 5: STAFF MODULE                             █\n";
        echo "████████████████████████████████████████████████████████████████████████████████\n";

        $this->verifyTestCase('STAFF001', 'Staff Listing and Search', [
            'routes' => [
                ['pattern' => 'staffs', 'methods' => ['GET'], 'exact' => true]
            ],
            'controllers' => ['Staff', 'UserController'],
            'tables' => ['users'],
            'views' => ['staffs.index', 'users.index']
        ]);

        $this->verifyTestCase('STAFF002', 'Add New Staff Member', [
            'routes' => [
                ['pattern' => 'staffs/create', 'methods' => ['GET']],
                ['pattern' => 'staffs/register', 'methods' => ['GET']],
                ['pattern' => 'staffs', 'methods' => ['POST']]
            ],
            'controllers' => ['Staff', 'UserController']
        ]);

        $this->verifyTestCase('STAFF003', 'View Staff Profile', [
            'routes' => [
                ['pattern' => 'staffs/{id}', 'methods' => ['GET']],
                ['pattern' => 'users/profile/{id}', 'methods' => ['GET']]
            ],
            'controllers' => ['Staff', 'UserProfileController']
        ]);

        $this->verifyTestCase('STAFF004', 'Edit Staff Information', [
            'routes' => [
                ['pattern' => 'staffs/{id}/edit', 'methods' => ['GET']],
                ['pattern' => 'staffs/{id}', 'methods' => ['PUT', 'PATCH', 'POST']]
            ],
            'controllers' => ['Staff', 'UserController']
        ]);

        $this->verifyTestCase('STAFF005', 'Delete/Deactivate Staff', [
            'routes' => [
                ['pattern' => 'staffs/{id}', 'methods' => ['DELETE']]
            ],
            'controllers' => ['Staff', 'UserController']
        ]);

        // PHASE 6: TRAINEE MODULE
        echo "\n\n";
        echo "████████████████████████████████████████████████████████████████████████████████\n";
        echo "█                           PHASE 6: TRAINEE MODULE                            █\n";
        echo "████████████████████████████████████████████████████████████████████████████████\n";

        $this->verifyTestCase('TRAIN001', 'Trainee Listing and Search', [
            'routes' => [
                ['pattern' => 'trainees', 'methods' => ['GET'], 'exact' => true]
            ],
            'controllers' => ['Trainee', 'TraineeProfileController'],
            'tables' => ['trainees'],
            'views' => ['trainees.index']
        ]);

        $this->verifyTestCase('TRAIN002', 'Register New Trainee', [
            'routes' => [
                ['pattern' => 'trainees/create', 'methods' => ['GET']],
                ['pattern' => 'trainees/register', 'methods' => ['GET']],
                ['pattern' => 'trainees', 'methods' => ['POST']]
            ],
            'controllers' => ['Trainee', 'TraineeProfileController'],
            'tables' => ['trainees']
        ]);

        $this->verifyTestCase('TRAIN003', 'Edit Trainee Information', [
            'routes' => [
                ['pattern' => 'trainees/{id}/edit', 'methods' => ['GET']],
                ['pattern' => 'trainees/{id}', 'methods' => ['PUT', 'PATCH', 'POST']]
            ],
            'controllers' => ['Trainee', 'TraineeProfileController']
        ]);

        $this->verifyTestCase('TRAIN004', 'Manage Trainee Status', [
            'routes' => [
                ['pattern' => 'trainees/{id}/status', 'methods' => ['POST', 'PUT', 'PATCH']]
            ],
            'controllers' => ['Trainee', 'TraineeProfileController'],
            'tables' => ['trainees']
        ]);

        $this->verifyTestCase('TRAIN005', 'Delete/Archive Trainee', [
            'routes' => [
                ['pattern' => 'trainees/{id}', 'methods' => ['DELETE']]
            ],
            'controllers' => ['Trainee', 'TraineeProfileController']
        ]);

        // PHASE 7: ACTIVITIES MODULE
        echo "\n\n";
        echo "████████████████████████████████████████████████████████████████████████████████\n";
        echo "█                          PHASE 7: ACTIVITIES MODULE                          █\n";
        echo "████████████████████████████████████████████████████████████████████████████████\n";

        $this->verifyTestCase('ACT001', 'Activity Listing and Filtering', [
            'routes' => [
                ['pattern' => 'activities', 'methods' => ['GET'], 'exact' => true]
            ],
            'controllers' => ['Activity', 'ActivityController'],
            'tables' => ['activities'],
            'views' => ['activities.index']
        ]);

        $this->verifyTestCase('ACT002', 'Create New Activity', [
            'routes' => [
                ['pattern' => 'activities/create', 'methods' => ['GET']],
                ['pattern' => 'activities', 'methods' => ['POST']]
            ],
            'controllers' => ['Activity', 'ActivityController'],
            'tables' => ['activities']
        ]);

        $this->verifyTestCase('ACT003', 'Edit Activity Details', [
            'routes' => [
                ['pattern' => 'activities/{id}/edit', 'methods' => ['GET']],
                ['pattern' => 'activities/{id}', 'methods' => ['PUT', 'PATCH', 'POST']]
            ],
            'controllers' => ['Activity', 'ActivityController']
        ]);

        $this->verifyTestCase('ACT004', 'Delete Activity', [
            'routes' => [
                ['pattern' => 'activities/{id}', 'methods' => ['DELETE']]
            ],
            'controllers' => ['Activity', 'ActivityController']
        ]);

        $this->verifyTestCase('ACT005', 'Activity Categories View', [
            'routes' => [
                ['pattern' => 'activities', 'methods' => ['GET']]
            ],
            'controllers' => ['Activity', 'ActivityController'],
            'tables' => ['activity_categories']
        ]);

        $this->verifyTestCase('ACT006', 'Create Activity Session', [
            'routes' => [
                ['pattern' => 'activities/{id}/sessions', 'methods' => ['POST']],
                ['pattern' => 'sessions', 'methods' => ['POST']]
            ],
            'controllers' => ['Activity', 'SessionController'],
            'tables' => ['activity_sessions']
        ]);

        $this->verifyTestCase('ACT007', 'Enroll Trainee in Activity', [
            'routes' => [
                ['pattern' => 'activities/{id}/enroll', 'methods' => ['POST']],
                ['pattern' => 'enrollments', 'methods' => ['POST']]
            ],
            'controllers' => ['Activity', 'EnrollmentController'],
            'tables' => ['activity_enrollments']
        ]);

        // PHASE 8: ATTENDANCE MODULE
        echo "\n\n";
        echo "████████████████████████████████████████████████████████████████████████████████\n";
        echo "█                         PHASE 8: ATTENDANCE MODULE                           █\n";
        echo "████████████████████████████████████████████████████████████████████████████████\n";

        $this->verifyTestCase('ATT001', 'Mark Attendance for Session', [
            'routes' => [
                ['pattern' => 'attendance', 'methods' => ['GET', 'POST']],
                ['pattern' => 'attendance/mark', 'methods' => ['POST']]
            ],
            'controllers' => ['Attendance', 'AttendanceController'],
            'tables' => ['session_attendance', 'trainee_attendances']
        ]);

        $this->verifyTestCase('ATT002', 'View Attendance Reports', [
            'routes' => [
                ['pattern' => 'reports/attendance', 'methods' => ['GET']],
                ['pattern' => 'attendance/reports', 'methods' => ['GET']]
            ],
            'controllers' => ['Attendance', 'ReportController'],
            'tables' => ['session_attendance', 'trainee_attendances']
        ]);

        // PHASE 9: CENTRES MODULE
        echo "\n\n";
        echo "████████████████████████████████████████████████████████████████████████████████\n";
        echo "█                          PHASE 9: CENTRES MODULE                             █\n";
        echo "████████████████████████████████████████████████████████████████████████████████\n";

        $this->verifyTestCase('CENT001', 'Centre Listing', [
            'routes' => [
                ['pattern' => 'centres', 'methods' => ['GET'], 'exact' => true]
            ],
            'controllers' => ['Centre', 'CentreController'],
            'tables' => ['centres'],
            'views' => ['centres.index']
        ]);

        $this->verifyTestCase('CENT002', 'Create New Centre', [
            'routes' => [
                ['pattern' => 'centres/create', 'methods' => ['GET']],
                ['pattern' => 'centres', 'methods' => ['POST']]
            ],
            'controllers' => ['Centre', 'CentreController']
        ]);

        $this->verifyTestCase('CENT003', 'Edit Centre Information', [
            'routes' => [
                ['pattern' => 'centres/{id}/edit', 'methods' => ['GET']],
                ['pattern' => 'centres/{id}', 'methods' => ['PUT', 'PATCH', 'POST']]
            ],
            'controllers' => ['Centre', 'CentreController']
        ]);

        // PHASE 10: ASSETS MODULE
        echo "\n\n";
        echo "████████████████████████████████████████████████████████████████████████████████\n";
        echo "█                           PHASE 10: ASSETS MODULE                            █\n";
        echo "████████████████████████████████████████████████████████████████████████████████\n";

        $this->verifyTestCase('ASSET001', 'Asset Inventory Listing', [
            'routes' => [
                ['pattern' => 'assets', 'methods' => ['GET'], 'exact' => true]
            ],
            'controllers' => ['Asset', 'AssetController'],
            'tables' => ['assets'],
            'views' => ['assets.index']
        ]);

        $this->verifyTestCase('ASSET002', 'Add New Asset', [
            'routes' => [
                ['pattern' => 'assets/create', 'methods' => ['GET']],
                ['pattern' => 'assets', 'methods' => ['POST']]
            ],
            'controllers' => ['Asset', 'AssetController']
        ]);

        $this->verifyTestCase('ASSET003', 'Edit Asset Details', [
            'routes' => [
                ['pattern' => 'assets/{id}/edit', 'methods' => ['GET']],
                ['pattern' => 'assets/{id}', 'methods' => ['PUT', 'PATCH', 'POST']]
            ],
            'controllers' => ['Asset', 'AssetController']
        ]);

        $this->verifyTestCase('ASSET004', 'Delete Asset', [
            'routes' => [
                ['pattern' => 'assets/{id}', 'methods' => ['DELETE']]
            ],
            'controllers' => ['Asset', 'AssetController']
        ]);

        // PHASE 11: LETTERS MODULE
        echo "\n\n";
        echo "████████████████████████████████████████████████████████████████████████████████\n";
        echo "█                          PHASE 11: LETTERS MODULE                            █\n";
        echo "████████████████████████████████████████████████████████████████████████████████\n";

        $this->verifyTestCase('LETT001', 'Letter Template Management', [
            'routes' => [
                ['pattern' => 'letters', 'methods' => ['GET'], 'exact' => true]
            ],
            'controllers' => ['Letter', 'LetterController'],
            'tables' => ['letter_templates', 'letters'],
            'views' => ['letters.index']
        ]);

        $this->verifyTestCase('LETT002', 'Generate Letter from Template', [
            'routes' => [
                ['pattern' => 'letters/generate', 'methods' => ['POST']],
                ['pattern' => 'letters/create', 'methods' => ['GET', 'POST']]
            ],
            'controllers' => ['Letter', 'LetterController']
        ]);

        $this->verifyTestCase('LETT003', 'Letter History and Tracking', [
            'routes' => [
                ['pattern' => 'letters', 'methods' => ['GET']],
                ['pattern' => 'letters/history', 'methods' => ['GET']]
            ],
            'controllers' => ['Letter', 'LetterController'],
            'tables' => ['letters']
        ]);

        // PHASE 12: MESSAGING MODULE
        echo "\n\n";
        echo "████████████████████████████████████████████████████████████████████████████████\n";
        echo "█                         PHASE 12: MESSAGING MODULE                           █\n";
        echo "████████████████████████████████████████████████████████████████████████████████\n";

        $this->verifyTestCase('MSG001', 'Send Message to Guardian', [
            'routes' => [
                ['pattern' => 'messages', 'methods' => ['POST']],
                ['pattern' => 'messages/send', 'methods' => ['POST']]
            ],
            'controllers' => ['Message', 'MessageController'],
            'tables' => ['messages']
        ]);

        $this->verifyTestCase('MSG002', 'View Message History', [
            'routes' => [
                ['pattern' => 'messages', 'methods' => ['GET'], 'exact' => true]
            ],
            'controllers' => ['Message', 'MessageController'],
            'tables' => ['messages']
        ]);

        $this->verifyTestCase('MSG003', 'Notification Settings', [
            'routes' => [
                ['pattern' => 'settings/notifications', 'methods' => ['GET']],
                ['pattern' => 'profile/notifications', 'methods' => ['GET', 'POST']]
            ],
            'controllers' => ['UserProfileController', 'SettingsController']
        ]);

        // PHASE 13: SYSTEM MODULE
        echo "\n\n";
        echo "████████████████████████████████████████████████████████████████████████████████\n";
        echo "█                           PHASE 13: SYSTEM MODULE                            █\n";
        echo "████████████████████████████████████████████████████████████████████████████████\n";

        $this->verifyTestCase('SYS001', 'User Role Management', [
            'routes' => [
                ['pattern' => 'settings', 'methods' => ['GET']],
                ['pattern' => 'admin/roles', 'methods' => ['GET']]
            ],
            'controllers' => ['SettingsController', 'RoleController'],
            'tables' => ['users']
        ]);

        $this->verifyTestCase('SYS002', 'System Backup and Restore', [
            'routes' => [
                ['pattern' => 'admin/backup', 'methods' => ['GET', 'POST']],
                ['pattern' => 'settings/backup', 'methods' => ['GET', 'POST']]
            ],
            'controllers' => ['BackupController', 'SettingsController']
        ]);

        $this->verifyTestCase('SYS003', 'Audit Log Review', [
            'routes' => [
                ['pattern' => 'logs', 'methods' => ['GET']],
                ['pattern' => 'admin/logs', 'methods' => ['GET']]
            ],
            'controllers' => ['LogController', 'AuditController'],
            'tables' => ['audit_logs', 'activity_log']
        ]);

        // GENERATE FINAL REPORT
        $this->generateFinalReport();
    }

    private function generateFinalReport() {
        echo "\n\n";
        echo "╔══════════════════════════════════════════════════════════════════════════════╗\n";
        echo "║                             VERIFICATION SUMMARY                             ║\n";
        echo "╚══════════════════════════════════════════════════════════════════════════════╝\n\n";

        $total = count($this->results);
        $passRate = ($this->passCount / $total) * 100;

        echo "Total Test Cases Verified: {$total}\n";
        echo "✅ Passing: {$this->passCount}\n";
        echo "❌ Needs Attention: {$this->issueCount}\n";
        echo "Pass Rate: " . number_format($passRate, 2) . "%\n\n";

        if ($this->issueCount > 0) {
            echo "\n" . str_repeat('=', 80) . "\n";
            echo "SUMMARY OF ISSUES BY TEST CASE:\n";
            echo str_repeat('=', 80) . "\n\n";

            foreach ($this->results as $testId => $result) {
                if (!$result['passed']) {
                    echo "{$testId} - {$result['name']}\n";
                    foreach ($result['issues'] as $issue) {
                        echo "  {$issue}\n";
                    }
                    echo "\n";
                }
            }
        }

        // Save detailed report
        $reportPath = __DIR__ . '/documentation/UAT FILES/DETAILED_VERIFICATION_REPORT_' . date('Y-m-d_H-i-s') . '.md';
        $this->saveDetailedReport($reportPath);

        echo "\n📄 Detailed report saved to: {$reportPath}\n";

        echo "\n";
        echo "╔══════════════════════════════════════════════════════════════════════════════╗\n";
        if ($passRate >= 95) {
            echo "║                         ✅ SYSTEM STATUS: EXCELLENT                          ║\n";
        } elseif ($passRate >= 85) {
            echo "║                           ⚠️  SYSTEM STATUS: GOOD                            ║\n";
        } elseif ($passRate >= 75) {
            echo "║                          ⚠️  SYSTEM STATUS: FAIR                             ║\n";
        } else {
            echo "║                   ❌ SYSTEM STATUS: NEEDS SIGNIFICANT WORK                   ║\n";
        }
        echo "╚══════════════════════════════════════════════════════════════════════════════╝\n";
    }

    private function saveDetailedReport($path) {
        $report = "# CREAMS UAT DETAILED VERIFICATION REPORT\n\n";
        $report .= "**Date:** " . date('F d, Y H:i:s') . "\n";
        $report .= "**Total Tests:** " . count($this->results) . "\n";
        $report .= "**Passing:** {$this->passCount}\n";
        $report .= "**Issues:** {$this->issueCount}\n\n";

        $report .= "---\n\n";

        foreach ($this->results as $testId => $result) {
            $report .= "## {$testId}: {$result['name']}\n\n";
            $report .= "**Status:** " . ($result['passed'] ? '✅ PASS' : '❌ NEEDS ATTENTION') . "\n\n";

            if (!empty($result['findings'])) {
                $report .= "### ✅ Passing Checks:\n";
                foreach ($result['findings'] as $finding) {
                    $report .= "- {$finding}\n";
                }
                $report .= "\n";
            }

            if (!empty($result['issues'])) {
                $report .= "### ❌ Issues Found:\n";
                foreach ($result['issues'] as $issue) {
                    $report .= "- {$issue}\n";
                }
                $report .= "\n";
            }

            $report .= "---\n\n";
        }

        file_put_contents($path, $report);
    }
}

// Run verification
$verifier = new ComprehensiveUATVerifier();
$verifier->runAllTests();
