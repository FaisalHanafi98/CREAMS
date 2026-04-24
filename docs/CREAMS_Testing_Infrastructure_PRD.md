# CREAMS Testing Strategy & Infrastructure Readiness PRD

**Product Requirements Document for Claude Code Implementation**

**Document Version:** 1.0  
**Date:** December 28, 2025  
**System:** CREAMS (Community-based REhAbilitation Management System)  
**Current State:** Pre-production, Fixers Branch, ~13% Test Coverage  
**Target State:** Production-ready with 80%+ Test Coverage and AWS Deployment Readiness

---

## Executive Summary

### Current System State

CREAMS is a Laravel 10 multi-tenant rehabilitation center management system for Malaysian PPDK centers. The codebase comprises approximately 60,000 lines of PHP across 207 files, with 70+ controllers, 58 models, and 252+ Blade views. The system supports six user roles (Admin, Supervisor, Teacher, AJK, Trainee, Parent) across multiple rehabilitation centers.

**Critical Issues Requiring Resolution:**
- Test coverage stands at only 13% (87% untested code)
- No browser-based E2E testing exists
- No CI/CD pipeline configured
- Debug code present in production controllers
- Custom authentication system requires validation
- Infrastructure not production-ready

### Document Purpose

This PRD provides Claude Code with precise, actionable requirements to:
1. Implement comprehensive browser-based testing with Laravel Dusk
2. Establish a complete testing pyramid (Unit → Feature → Browser)
3. Prepare infrastructure for AWS deployment with CI/CD integration

### Success Criteria

| Metric | Current | Target |
|--------|---------|--------|
| Test Coverage | 13% | 80%+ |
| Browser E2E Tests | 0 | 69+ (matching UAT cases) |
| Feature Tests | 9 | 50+ |
| CI/CD Pipeline | None | GitHub Actions |
| Production Readiness | ❌ | ✅ |

---

## Part 1: System Architecture Context

### 1.1 Technology Stack

```
Backend:
├── Framework: Laravel 10.8+
├── Language: PHP 8.1/8.4
├── Database: MySQL 8.0+
├── Authentication: Custom Session-based (NOT Laravel Auth)
├── PDF: barryvdh/laravel-dompdf ^3.1
└── Testing: PHPUnit 10.1, Laravel Dusk (to be added)

Frontend:
├── Build: Vite 6.2.0
├── CSS: Tailwind 3.1.0 + Bootstrap 5.3.3 (mixed)
├── JS: Alpine.js 3.4.2 + jQuery 3.7.1 (mixed)
└── Templates: Blade (252+ files)
```

### 1.2 Critical Code Locations

```
tests/                              # Test directory (target location)
├── Feature/                        # Laravel Feature tests
├── Unit/                          # Unit tests
└── Browser/                       # Laravel Dusk tests (to create)

app/Http/Controllers/
├── Activity/ActivityController.php     # 3,545 lines - HIGHEST RISK
├── Dashboard/DashboardController.php   # 2,161 lines - HIGH RISK
├── Auth/                               # Custom authentication
└── [70+ other controllers]

app/Http/Middleware/
├── EnhancedAuthenticate.php           # Custom auth middleware
├── EnhancedRoleMiddleware.php         # Role-based access
└── CentreAccessControl.php            # Multi-tenant isolation

app/Services/
├── Dashboard/                         # Factory pattern services
├── SessionManager.php                 # Custom session handling
└── [16+ services]

routes/web.php                         # 1,073 lines, 406+ routes
```

### 1.3 Authentication System (CRITICAL FOR TESTING)

The system uses **custom session-based authentication**, NOT Laravel's built-in Auth. This is crucial for test setup.

**Session Variables:**
```php
session('id')        // User ID
session('role')      // admin|supervisor|teacher|ajk|trainee|parent
session('name')      // Display name
session('centre_id') // Data isolation key
session('email')     // User email
```

**Test Credentials (from seeders):**

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@creams.test | Admin123! |
| Supervisor | supervisor@creams.test | Supervisor123! |
| Teacher | teacher@creams.test | Teacher123! |
| AJK | ajk@creams.test | Ajk123! |

### 1.4 Multi-Tenant Data Isolation

All queries are filtered by `centre_id`. Tests must account for this:
```php
// Production pattern
if (session('role') !== 'admin') {
    $query->where('centre_id', session('centre_id'));
}
```

---

## Part 2: Browser-Based Testing Requirements

### 2.1 Why Laravel Dusk

**Selected Tool:** Laravel Dusk (NOT Cypress, Playwright, or Selenium standalone)

**Justification:**
1. **Native Laravel Integration** - Works with custom auth, sessions, database transactions
2. **Same Language** - PHP tests alongside PHP application
3. **Factory Support** - Uses existing Eloquent factories for test data
4. **Assertion Library** - Laravel-specific assertions (waitForText, assertSee, etc.)
5. **CI/CD Friendly** - Headless Chrome/Firefox support
6. **Malaysian Timezone** - Respects application timezone settings

### 2.2 Dusk Installation & Configuration

**Implementation Steps for Claude Code:**

```bash
# Step 1: Install Dusk
composer require --dev laravel/dusk

# Step 2: Install browser drivers
php artisan dusk:install

# Step 3: Create .env.dusk.local
cp .env .env.dusk.local
# Edit: APP_URL=http://localhost:8000
# Edit: DB_DATABASE=cream_testing
```

**DuskTestCase Base Class Configuration:**

Create `tests/DuskTestCase.php`:
```php
<?php

namespace Tests;

use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Laravel\Dusk\TestCase as BaseTestCase;

abstract class DuskTestCase extends BaseTestCase
{
    use CreatesApplication;

    protected static function driver(): RemoteWebDriver
    {
        $options = (new ChromeOptions)->addArguments([
            '--disable-gpu',
            '--headless',
            '--window-size=1920,1080',
            '--no-sandbox',
            '--disable-dev-shm-usage',
        ]);

        return RemoteWebDriver::create(
            $_ENV['DUSK_DRIVER_URL'] ?? 'http://localhost:9515',
            DesiredCapabilities::chrome()->setCapability(
                ChromeOptions::CAPABILITY, $options
            )
        );
    }

    /**
     * CREAMS Custom Login Helper
     * Handles custom session-based authentication
     */
    protected function loginAs(Browser $browser, string $email, string $password): Browser
    {
        return $browser->visit('/login')
            ->waitFor('#email')
            ->type('#email', $email)
            ->type('#password', $password)
            ->press('Login')
            ->waitForLocation('/dashboard');
    }

    /**
     * Role-based login shortcuts
     */
    protected function loginAsAdmin(Browser $browser): Browser
    {
        return $this->loginAs($browser, 'admin@creams.test', 'Admin123!');
    }

    protected function loginAsSupervisor(Browser $browser): Browser
    {
        return $this->loginAs($browser, 'supervisor@creams.test', 'Supervisor123!');
    }

    protected function loginAsTeacher(Browser $browser): Browser
    {
        return $this->loginAs($browser, 'teacher@creams.test', 'Teacher123!');
    }

    protected function loginAsAjk(Browser $browser): Browser
    {
        return $this->loginAs($browser, 'ajk@creams.test', 'Ajk123!');
    }
}
```

### 2.3 Test Naming Convention

**Pattern:** `{ModuleName}{Workflow}Test.php`

**Examples:**
```
tests/Browser/
├── Authentication/
│   ├── LoginTest.php
│   ├── LogoutTest.php
│   └── PasswordResetTest.php
├── Dashboard/
│   ├── AdminDashboardTest.php
│   ├── SupervisorDashboardTest.php
│   ├── TeacherDashboardTest.php
│   └── DashboardRbacTest.php
├── Activity/
│   ├── ActivityCreateTest.php
│   ├── ActivitySessionSchedulingTest.php
│   ├── TraineeEnrollmentTest.php
│   └── AttendanceMarkingTest.php
├── Trainee/
│   ├── TraineeRegistrationTest.php
│   ├── TraineeProgressTest.php
│   └── IepManagementTest.php
├── Staff/
│   ├── StaffCreationTest.php
│   └── StaffScheduleTest.php
├── Assets/
│   └── AssetManagementTest.php
├── Letters/
│   └── LetterGenerationTest.php
└── RBAC/
    └── RoleAccessControlTest.php
```

### 2.4 High-Risk Module Test Specifications

#### 2.4.1 Activity Module Tests (HIGHEST PRIORITY)

The `ActivityController.php` (3,545 lines) is the largest and most complex controller. It handles:
- Activity CRUD operations
- Session scheduling with Malaysian holiday awareness
- Trainee enrollment with prerequisite checking
- Attendance marking across multiple statuses
- Learning outcome tracking

**Required Test File:** `tests/Browser/Activity/ActivityCreateTest.php`

```php
<?php

namespace Tests\Browser\Activity;

use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class ActivityCreateTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * Test: Admin can access activity creation form
     * Covers: Route access, RBAC enforcement
     */
    public function test_admin_can_access_activity_creation_form(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsAdmin($browser)
                ->visit('/activities')
                ->assertSee('Activities')
                ->clickLink('Create Activity')
                ->assertPathIs('/activities/create')
                ->assertSee('Create New Activity');
        });
    }

    /**
     * Test: Admin can create complete activity
     * Covers: Form submission, validation, database persistence
     */
    public function test_admin_can_create_activity_with_all_fields(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsAdmin($browser)
                ->visit('/activities/create')
                ->type('name', 'Autism Sensory Integration')
                ->type('description', 'Sensory processing activities for autism spectrum')
                ->select('category_id', 'Autism Spectrum Support')
                ->type('duration_weeks', '12')
                ->type('sessions_per_week', '3')
                ->type('session_duration', '60')
                ->type('max_participants', '10')
                ->type('location', 'Therapy Room A')
                ->select('instructor_id', '2') // Teacher ID
                ->press('Create Activity')
                ->waitForText('Activity created successfully')
                ->assertPathBeginsWith('/activities/');
        });
    }

    /**
     * Test: Activity form validation prevents invalid data
     * Covers: Client-side and server-side validation
     */
    public function test_activity_creation_validates_required_fields(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsAdmin($browser)
                ->visit('/activities/create')
                ->press('Create Activity')
                ->waitForText('The name field is required')
                ->assertSee('The category field is required');
        });
    }

    /**
     * Test: Non-admin cannot create activities
     * Covers: RBAC enforcement for Teacher role
     */
    public function test_teacher_cannot_access_activity_creation(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsTeacher($browser)
                ->visit('/activities/create')
                ->assertPathIsNot('/activities/create')
                ->assertSee('Unauthorized'); // Or redirect behavior
        });
    }
}
```

**Required Test File:** `tests/Browser/Activity/AttendanceMarkingTest.php`

```php
<?php

namespace Tests\Browser\Activity;

use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use App\Models\Activity;
use App\Models\ActivitySession;
use App\Models\Trainee;

class AttendanceMarkingTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * Test: Teacher can mark attendance for session
     * Covers: Attendance workflow, status options
     */
    public function test_teacher_can_mark_attendance_for_session(): void
    {
        // Setup: Create activity with session and enrolled trainees
        $activity = Activity::factory()->create(['instructor_id' => 3]); // Teacher
        $session = ActivitySession::factory()->create([
            'activity_id' => $activity->id,
            'scheduled_date' => now()->format('Y-m-d'),
            'status' => 'scheduled'
        ]);
        $trainees = Trainee::factory()->count(3)->create();
        
        $this->browse(function (Browser $browser) use ($session) {
            $this->loginAsTeacher($browser)
                ->visit("/sessions/{$session->id}/attendance")
                ->assertSee('Mark Attendance')
                ->select('attendance[1]', 'present')
                ->select('attendance[2]', 'absent')
                ->select('attendance[3]', 'late')
                ->press('Save Attendance')
                ->waitForText('Attendance recorded successfully');
        });
    }

    /**
     * Test: Attendance statuses persist correctly
     * Covers: Database persistence, status values
     */
    public function test_attendance_status_options(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsTeacher($browser)
                ->visit("/sessions/1/attendance")
                ->assertSelectHasOptions('attendance[1]', [
                    'present',
                    'absent', 
                    'late',
                    'excused'
                ]);
        });
    }
}
```

#### 2.4.2 Dashboard Tests (HIGH PRIORITY)

The `DashboardController.php` (2,161 lines) uses a factory pattern to serve role-specific dashboards.

**Required Test File:** `tests/Browser/Dashboard/RoleBasedDashboardTest.php`

```php
<?php

namespace Tests\Browser\Dashboard;

use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class RoleBasedDashboardTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * Test: Admin sees system-wide statistics
     * Covers: Admin dashboard content, data visibility
     */
    public function test_admin_dashboard_shows_system_wide_stats(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsAdmin($browser)
                ->assertPathIs('/dashboard')
                ->assertSee('System Overview')
                ->assertSee('Total Centres')
                ->assertSee('Total Users')
                ->assertSee('Total Activities')
                ->assertSee('Total Trainees');
        });
    }

    /**
     * Test: Supervisor sees centre-specific statistics
     * Covers: Data isolation, role-specific content
     */
    public function test_supervisor_dashboard_shows_centre_stats(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsSupervisor($browser)
                ->assertPathIs('/dashboard')
                ->assertSee('Centre Overview')
                ->assertSee('Staff Members')
                ->assertSee('Active Trainees')
                ->assertDontSee('Total Centres'); // Admin-only stat
        });
    }

    /**
     * Test: Teacher sees activity-focused dashboard
     * Covers: Role-specific widgets, upcoming sessions
     */
    public function test_teacher_dashboard_shows_activities(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsTeacher($browser)
                ->assertPathIs('/dashboard')
                ->assertSee('My Activities')
                ->assertSee('Upcoming Sessions')
                ->assertSee('Quick Actions');
        });
    }

    /**
     * Test: Dashboard redirects unauthenticated users
     * Covers: Authentication enforcement
     */
    public function test_unauthenticated_user_redirected_to_login(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/dashboard')
                ->assertPathIs('/login');
        });
    }
}
```

#### 2.4.3 RBAC Enforcement Tests (CRITICAL)

**Required Test File:** `tests/Browser/RBAC/RoleAccessControlTest.php`

```php
<?php

namespace Tests\Browser\RBAC;

use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class RoleAccessControlTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * RBAC Matrix Test: Admin Access
     */
    public function test_admin_can_access_all_modules(): void
    {
        $adminRoutes = [
            '/dashboard',
            '/activities',
            '/activities/create',
            '/trainees',
            '/trainees/create',
            '/staffs',
            '/staffs/create',
            '/centres',
            '/asset-parents',
            '/letters/modern',
        ];

        $this->browse(function (Browser $browser) use ($adminRoutes) {
            $this->loginAsAdmin($browser);
            
            foreach ($adminRoutes as $route) {
                $browser->visit($route)
                    ->assertPathIs($route)
                    ->assertDontSee('Unauthorized')
                    ->assertDontSee('Access Denied');
            }
        });
    }

    /**
     * RBAC Matrix Test: Teacher Restricted Access
     */
    public function test_teacher_cannot_access_admin_routes(): void
    {
        $restrictedRoutes = [
            '/centres/create',
            '/staffs/create',
            '/activities/create',
        ];

        $this->browse(function (Browser $browser) use ($restrictedRoutes) {
            $this->loginAsTeacher($browser);
            
            foreach ($restrictedRoutes as $route) {
                $browser->visit($route)
                    ->assertPathIsNot($route); // Should redirect
            }
        });
    }

    /**
     * RBAC Matrix Test: Centre Data Isolation
     */
    public function test_supervisor_only_sees_own_centre_data(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsSupervisor($browser)
                ->visit('/trainees')
                ->assertSee('Centre: ') // Should show only assigned centre
                ->assertDontSee('All Centres'); // Admin-only
        });
    }
}
```

#### 2.4.4 Authentication Tests

**Required Test File:** `tests/Browser/Authentication/LoginTest.php`

```php
<?php

namespace Tests\Browser\Authentication;

use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class LoginTest extends DuskTestCase
{
    use DatabaseMigrations;

    public function test_login_page_displays(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                ->assertSee('Login')
                ->assertPresent('#email')
                ->assertPresent('#password');
        });
    }

    public function test_user_can_login_with_valid_credentials(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                ->type('#email', 'admin@creams.test')
                ->type('#password', 'Admin123!')
                ->press('Login')
                ->waitForLocation('/dashboard')
                ->assertPathIs('/dashboard');
        });
    }

    public function test_login_fails_with_invalid_credentials(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                ->type('#email', 'admin@creams.test')
                ->type('#password', 'WrongPassword')
                ->press('Login')
                ->assertPathIs('/login')
                ->assertSee('Invalid credentials');
        });
    }

    public function test_user_can_logout(): void
    {
        $this->browse(function (Browser $browser) {
            $this->loginAsAdmin($browser)
                ->click('@logout-button') // Use dusk selector
                ->assertPathIs('/login');
        });
    }
}
```

### 2.5 Complete E2E Test Matrix

Based on the existing 69 UAT test cases, implement browser tests for:

| Module | Test Count | Priority | Risk Level |
|--------|------------|----------|------------|
| Authentication | 5 | HIGH | Critical |
| Dashboard | 8 | HIGH | High |
| Activity Management | 15 | HIGHEST | Critical |
| Trainee Management | 12 | HIGH | High |
| Staff Management | 8 | MEDIUM | Medium |
| Asset Management | 6 | MEDIUM | Medium |
| Attendance | 6 | HIGH | High |
| Letters/Documents | 4 | MEDIUM | Medium |
| RBAC Enforcement | 5 | HIGHEST | Critical |

---

## Part 3: Testing Pyramid Strategy

### 3.1 Testing Layers Defined

```
                    ┌─────────────┐
                    │   Browser   │  ← Real user flows, E2E
                    │   (Dusk)    │     ~69 tests
                    └──────┬──────┘
                           │
                    ┌──────▼──────┐
                    │   Feature   │  ← HTTP request/response
                    │   Tests     │     ~50 tests  
                    └──────┬──────┘
                           │
                    ┌──────▼──────┐
                    │    Unit     │  ← Models, Services, Helpers
                    │   Tests     │     ~100 tests
                    └─────────────┘
```

### 3.2 Layer Responsibilities

#### Unit Tests (Bottom Layer)
- **Purpose:** Validate individual components in isolation
- **Target:** Models, Services, Helpers, Traits
- **Mocking:** Yes, mock dependencies
- **Database:** No (in-memory or mocked)
- **Speed:** Fast (<1ms per test)

**What to Test:**
```
app/Models/
├── Activity.php        → Relationships, scopes, accessors
├── Trainee.php         → Relationships, age calculations
├── User.php            → Role checks, password hashing
└── [55 more models]

app/Services/
├── SessionManager.php  → Session operations
├── TraineeService.php  → Business logic
└── Dashboard/          → Factory pattern, calculations

app/Helpers/
├── DateHelper.php      → Malaysian date formatting
├── MalaysianPhoneValidator.php → Phone validation
└── MediaUploadHelper.php → File handling
```

**Example Unit Test:**

```php
<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Activity;
use App\Models\ActivitySession;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ActivityTest extends TestCase
{
    use RefreshDatabase;

    public function test_activity_has_sessions_relationship(): void
    {
        $activity = Activity::factory()->create();
        $session = ActivitySession::factory()->create(['activity_id' => $activity->id]);

        $this->assertTrue($activity->sessions->contains($session));
    }

    public function test_activity_calculates_progress_percentage(): void
    {
        $activity = Activity::factory()->create([
            'total_sessions' => 10,
            'completed_sessions' => 5
        ]);

        $this->assertEquals(50, $activity->progress_percentage);
    }

    public function test_activity_scope_filters_by_centre(): void
    {
        $centreA = Activity::factory()->create(['centre_id' => 'CENTRE-A']);
        $centreB = Activity::factory()->create(['centre_id' => 'CENTRE-B']);

        $results = Activity::forCentre('CENTRE-A')->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($centreA));
        $this->assertFalse($results->contains($centreB));
    }
}
```

#### Feature Tests (Middle Layer)
- **Purpose:** Validate HTTP request/response cycles
- **Target:** Controllers, Routes, Middleware
- **Mocking:** Minimal
- **Database:** Yes (transactions or migrations)
- **Speed:** Medium (~50ms per test)

**What to Test:**
```
routes/web.php (406+ routes)
├── Authentication routes
├── Dashboard routes per role
├── Activity CRUD routes
├── Trainee CRUD routes
├── Asset management routes
└── API endpoints

app/Http/Middleware/
├── EnhancedAuthenticate  → Auth enforcement
├── EnhancedRoleMiddleware → Role checking
└── CentreAccessControl   → Data isolation
```

**Example Feature Test:**

```php
<?php

namespace Tests\Feature\Activity;

use Tests\TestCase;
use App\Models\User;
use App\Models\Activity;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ActivityControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(); // Load test data
    }

    public function test_admin_can_create_activity(): void
    {
        // Simulate custom auth session
        $admin = User::where('email', 'admin@creams.test')->first();
        session([
            'id' => $admin->id,
            'role' => 'admin',
            'name' => $admin->name,
            'centre_id' => $admin->centre_id,
        ]);

        $response = $this->post('/activities', [
            'name' => 'New Activity',
            'description' => 'Test description',
            'category_id' => 1,
            'duration_weeks' => 8,
            'sessions_per_week' => 2,
            'session_duration' => 45,
            'max_participants' => 8,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('activities', ['name' => 'New Activity']);
    }

    public function test_teacher_cannot_create_activity(): void
    {
        $teacher = User::where('email', 'teacher@creams.test')->first();
        session([
            'id' => $teacher->id,
            'role' => 'teacher',
            'name' => $teacher->name,
            'centre_id' => $teacher->centre_id,
        ]);

        $response = $this->post('/activities', [
            'name' => 'Unauthorized Activity',
        ]);

        $response->assertForbidden(); // 403
    }

    public function test_activity_index_respects_centre_isolation(): void
    {
        $supervisor = User::where('email', 'supervisor@creams.test')->first();
        session([
            'id' => $supervisor->id,
            'role' => 'supervisor',
            'centre_id' => 'CENTRE-001',
        ]);

        $response = $this->get('/activities');

        $response->assertOk();
        // Should only show activities for CENTRE-001
        $response->assertDontSee('CENTRE-002'); // Other centre data
    }
}
```

#### Browser Tests (Top Layer)
- **Purpose:** Validate complete user journeys
- **Target:** UI, JavaScript, Full workflows
- **Mocking:** None (real browser)
- **Database:** Yes (fresh per test)
- **Speed:** Slow (~2-5s per test)

**What to Test:**
- Complete login → action → logout flows
- Form interactions and JavaScript validation
- AJAX operations and dynamic content
- PDF generation and downloads
- Multi-step workflows

### 3.3 Test Distribution Recommendation

| Layer | Current | Target | Rationale |
|-------|---------|--------|-----------|
| Unit | ~5 | 100+ | Fast feedback, service logic |
| Feature | ~4 | 50+ | Controller coverage, middleware |
| Browser | 0 | 69+ | UAT alignment, user confidence |

### 3.4 Risk Mitigation by Layer

| Risk | Unit Tests | Feature Tests | Browser Tests |
|------|------------|---------------|---------------|
| Business logic bugs | ✅ Primary | ⚪ Secondary | ⚪ Indirect |
| Auth bypass | ⚪ Limited | ✅ Primary | ✅ Validates |
| RBAC failures | ⚪ Limited | ✅ Primary | ✅ Validates |
| Data leakage | ⚪ Model scopes | ✅ Middleware | ✅ Full flow |
| UI/UX regressions | ❌ None | ⚪ Limited | ✅ Primary |
| PDF generation | ⚪ Unit logic | ⚪ HTTP test | ✅ Download |

---

## Part 4: Implementation Roadmap for Claude Code

### Phase 1: Foundation Setup (Day 1-2)

**Objective:** Install testing infrastructure and establish patterns

**Tasks:**
```bash
# 1. Install Laravel Dusk
composer require --dev laravel/dusk
php artisan dusk:install

# 2. Create testing database
mysql -u root -p -e "CREATE DATABASE cream_testing;"

# 3. Create .env.dusk.local
cp .env .env.dusk.local
# Set: APP_ENV=testing
# Set: DB_DATABASE=cream_testing
# Set: APP_URL=http://localhost:8000

# 4. Setup ChromeDriver
php artisan dusk:chrome-driver --detect
```

**Files to Create:**
1. `tests/DuskTestCase.php` - Base class with login helpers
2. `tests/Browser/.gitignore` - Exclude screenshots
3. `phpunit.dusk.xml` - Dusk-specific config

### Phase 2: Authentication Tests (Day 3)

**Objective:** Validate custom authentication works correctly

**Files to Create:**
1. `tests/Browser/Authentication/LoginTest.php`
2. `tests/Browser/Authentication/LogoutTest.php`
3. `tests/Browser/Authentication/PasswordResetTest.php`
4. `tests/Feature/Auth/SessionManagerTest.php`

**Validation Command:**
```bash
php artisan dusk --filter=Authentication
```

### Phase 3: Dashboard Tests (Day 4-5)

**Objective:** Test all six role-specific dashboards

**Files to Create:**
1. `tests/Browser/Dashboard/AdminDashboardTest.php`
2. `tests/Browser/Dashboard/SupervisorDashboardTest.php`
3. `tests/Browser/Dashboard/TeacherDashboardTest.php`
4. `tests/Browser/Dashboard/AjkDashboardTest.php`
5. `tests/Browser/Dashboard/TraineeDashboardTest.php`
6. `tests/Browser/Dashboard/ParentDashboardTest.php`
7. `tests/Feature/Dashboard/DashboardServiceFactoryTest.php`

### Phase 4: Activity Module Tests (Day 6-8) - CRITICAL

**Objective:** Comprehensive testing of largest controller

**Files to Create:**
1. `tests/Browser/Activity/ActivityCreateTest.php`
2. `tests/Browser/Activity/ActivityEditTest.php`
3. `tests/Browser/Activity/SessionSchedulingTest.php`
4. `tests/Browser/Activity/TraineeEnrollmentTest.php`
5. `tests/Browser/Activity/AttendanceMarkingTest.php`
6. `tests/Browser/Activity/LearningOutcomeTest.php`
7. `tests/Feature/Activity/ActivityControllerTest.php`
8. `tests/Unit/Models/ActivityTest.php`
9. `tests/Unit/Models/ActivitySessionTest.php`

### Phase 5: RBAC & Security Tests (Day 9-10)

**Objective:** Validate authorization matrix

**Files to Create:**
1. `tests/Browser/RBAC/AdminAccessTest.php`
2. `tests/Browser/RBAC/SupervisorAccessTest.php`
3. `tests/Browser/RBAC/TeacherAccessTest.php`
4. `tests/Browser/RBAC/CentreIsolationTest.php`
5. `tests/Feature/Middleware/EnhancedAuthenticateTest.php`
6. `tests/Feature/Middleware/EnhancedRoleMiddlewareTest.php`

### Phase 6: Remaining Modules (Day 11-15)

**Files to Create:**
```
tests/Browser/Trainee/
├── TraineeRegistrationTest.php
├── TraineeEditTest.php
├── TraineeProgressTest.php
└── IepManagementTest.php

tests/Browser/Staff/
├── StaffCreationTest.php
├── StaffEditTest.php
└── StaffScheduleTest.php

tests/Browser/Assets/
├── AssetInventoryTest.php
└── AssetMaintenanceTest.php

tests/Browser/Letters/
├── LetterGenerationTest.php
└── LetterTemplateTest.php
```

### Phase 7: CI/CD Integration (Day 16-17)

**Objective:** Automated testing on every push

**File to Create:** `.github/workflows/tests.yml`

```yaml
name: CREAMS Tests

on:
  push:
    branches: [main, Fixers]
  pull_request:
    branches: [main, Fixers]

jobs:
  tests:
    runs-on: ubuntu-latest
    
    services:
      mysql:
        image: mysql:8.0
        env:
          MYSQL_ROOT_PASSWORD: password
          MYSQL_DATABASE: cream_testing
        ports:
          - 3306:3306
        options: --health-cmd="mysqladmin ping" --health-interval=10s --health-timeout=5s --health-retries=3

    steps:
      - uses: actions/checkout@v4

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.4'
          extensions: mbstring, pdo_mysql, bcmath
          coverage: xdebug

      - name: Install Composer dependencies
        run: composer install --prefer-dist --no-progress

      - name: Install NPM dependencies
        run: npm ci

      - name: Build assets
        run: npm run build

      - name: Copy .env
        run: cp .env.example .env

      - name: Generate key
        run: php artisan key:generate

      - name: Configure database
        run: |
          php artisan config:clear
          php artisan migrate --force
          php artisan db:seed --force
        env:
          DB_CONNECTION: mysql
          DB_HOST: 127.0.0.1
          DB_PORT: 3306
          DB_DATABASE: cream_testing
          DB_USERNAME: root
          DB_PASSWORD: password

      - name: Run Unit Tests
        run: php artisan test --testsuite=Unit

      - name: Run Feature Tests
        run: php artisan test --testsuite=Feature

      - name: Start Chrome Driver
        run: ./vendor/laravel/dusk/bin/chromedriver-linux &

      - name: Start Laravel Server
        run: php artisan serve --no-reload &

      - name: Run Dusk Tests
        run: php artisan dusk
        env:
          APP_URL: http://127.0.0.1:8000

      - name: Upload Dusk Failures
        if: failure()
        uses: actions/upload-artifact@v4
        with:
          name: dusk-failures
          path: tests/Browser/screenshots
```

---

## Part 5: Infrastructure & Deployment Readiness

### 5.1 Target Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                         AWS Cloud                                │
│  ┌─────────────────┐  ┌──────────────────┐  ┌───────────────┐  │
│  │   CloudFront    │  │   Application    │  │     RDS       │  │
│  │   (CDN/SSL)     │──│   Load Balancer  │──│   MySQL 8.0   │  │
│  └─────────────────┘  └────────┬─────────┘  └───────────────┘  │
│                                │                                 │
│  ┌─────────────────────────────┴─────────────────────────────┐  │
│  │                    EC2 Auto Scaling Group                  │  │
│  │  ┌───────────┐  ┌───────────┐  ┌───────────┐              │  │
│  │  │ Laravel   │  │ Laravel   │  │ Laravel   │              │  │
│  │  │ Instance  │  │ Instance  │  │ Instance  │              │  │
│  │  └───────────┘  └───────────┘  └───────────┘              │  │
│  └───────────────────────────────────────────────────────────┘  │
│                                                                  │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐            │
│  │ ElastiCache │  │     S3      │  │    SES      │            │
│  │   (Redis)   │  │  (Storage)  │  │  (Email)    │            │
│  └─────────────┘  └─────────────┘  └─────────────┘            │
└─────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────┐
│                        Vercel (Optional)                         │
│  ┌───────────────────────────────────────────────────────────┐  │
│  │              Static Assets / Frontend (if separated)       │  │
│  └───────────────────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────────────┘
```

### 5.2 Environment Configuration

**File to Create:** `.env.production.example`

```bash
# Application
APP_NAME=CREAMS
APP_ENV=production
APP_KEY=base64:GENERATE_NEW_KEY
APP_DEBUG=false
APP_URL=https://creams.ppdk.gov.my
APP_TIMEZONE=Asia/Kuala_Lumpur

# Database (RDS)
DB_CONNECTION=mysql
DB_HOST=creams-db.cluster-xxxxx.ap-southeast-1.rds.amazonaws.com
DB_PORT=3306
DB_DATABASE=cream
DB_USERNAME=${DB_USERNAME}
DB_PASSWORD=${DB_PASSWORD}

# Cache & Sessions (ElastiCache)
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
REDIS_HOST=creams-redis.xxxxx.0001.apse1.cache.amazonaws.com
REDIS_PASSWORD=null
REDIS_PORT=6379

# Storage (S3)
FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=${AWS_ACCESS_KEY_ID}
AWS_SECRET_ACCESS_KEY=${AWS_SECRET_ACCESS_KEY}
AWS_DEFAULT_REGION=ap-southeast-1
AWS_BUCKET=creams-storage
AWS_USE_PATH_STYLE_ENDPOINT=false

# Email (SES)
MAIL_MAILER=ses
MAIL_FROM_ADDRESS=noreply@creams.ppdk.gov.my
MAIL_FROM_NAME="${APP_NAME}"

# Logging (CloudWatch)
LOG_CHANNEL=cloudwatch
LOG_LEVEL=info

# Security
SESSION_SECURE_COOKIE=true
SESSION_LIFETIME=120
SANCTUM_STATEFUL_DOMAINS=creams.ppdk.gov.my
```

### 5.3 AWS Services Required

| Service | Purpose | Configuration |
|---------|---------|---------------|
| EC2 | Application servers | t3.medium, Auto Scaling 2-4 |
| RDS | MySQL database | db.t3.medium, Multi-AZ |
| ElastiCache | Redis (sessions/cache) | cache.t3.micro |
| S3 | File storage | Standard, versioning enabled |
| CloudFront | CDN, SSL termination | Custom domain + ACM cert |
| SES | Email sending | Verified domain |
| CloudWatch | Logging & monitoring | Log groups for Laravel |
| Secrets Manager | Credentials | DB, API keys |
| Route 53 | DNS | CNAME to CloudFront |

### 5.4 Pre-Production Checklist

**File to Create:** `DEPLOYMENT_CHECKLIST.md`

```markdown
# CREAMS Production Deployment Checklist

## Code Readiness
- [ ] All tests passing (unit, feature, browser)
- [ ] Debug code removed from controllers
- [ ] Error logging configured for CloudWatch
- [ ] .env.production configured
- [ ] APP_DEBUG=false

## Security
- [ ] CSRF protection verified
- [ ] Session cookie secure flag enabled
- [ ] HTTPS enforced
- [ ] SQL injection tested
- [ ] XSS prevention verified
- [ ] Rate limiting configured

## Database
- [ ] Migrations stable (no pending fixes)
- [ ] Seeders updated for production
- [ ] Backup strategy configured
- [ ] Connection pooling enabled

## Infrastructure
- [ ] AWS resources provisioned
- [ ] SSL certificate installed
- [ ] CloudFront configured
- [ ] ElastiCache connected
- [ ] S3 bucket created
- [ ] SES domain verified

## Monitoring
- [ ] CloudWatch alarms configured
- [ ] Error notification emails set up
- [ ] Health check endpoint working
- [ ] Performance baselines established

## Backup & Recovery
- [ ] RDS automated backups enabled
- [ ] S3 versioning enabled
- [ ] Recovery procedure documented
- [ ] Rollback plan tested
```

### 5.5 CI/CD Pipeline Stages

```yaml
# Enhanced .github/workflows/deploy.yml

name: CREAMS Deploy

on:
  push:
    branches: [main]

jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - name: Run Tests
        run: |
          composer install
          npm ci && npm run build
          php artisan test
          php artisan dusk

  staging:
    needs: test
    runs-on: ubuntu-latest
    environment: staging
    steps:
      - name: Deploy to Staging
        run: |
          # Deploy to staging EC2
          ssh staging "cd /var/www/creams && git pull && composer install --no-dev && php artisan migrate --force"

  production:
    needs: staging
    runs-on: ubuntu-latest
    environment: production
    steps:
      - name: Deploy to Production
        run: |
          # Blue-green deployment
          ssh production "cd /var/www/creams && ./deploy.sh"
```

---

## Part 6: Non-Functional Requirements

### 6.1 Performance

| Metric | Requirement | Measurement |
|--------|-------------|-------------|
| Page Load | < 3 seconds | Lighthouse |
| API Response | < 500ms | CloudWatch |
| Database Query | < 100ms avg | Query logs |
| Concurrent Users | 100+ | Load test |

### 6.2 Reliability

| Metric | Requirement |
|--------|-------------|
| Uptime | 99.5% |
| RTO | 4 hours |
| RPO | 1 hour |
| MTTR | 30 minutes |

### 6.3 Security

| Requirement | Implementation |
|-------------|----------------|
| Authentication | Custom session + CSRF |
| Authorization | Role-based + Centre isolation |
| Data Encryption | TLS in transit, encrypted at rest |
| Audit Logging | All data modifications |
| Session Timeout | 2 hours |

---

## Part 7: Out of Scope

This PRD explicitly **excludes**:

1. **System Redesign** - No architectural changes
2. **New Features** - No feature additions
3. **Controller Refactoring** - Large controller splits are future work
4. **Frontend Migration** - Tailwind/Bootstrap consolidation deferred
5. **Mobile App** - API development for mobile is separate initiative
6. **Multi-language Support** - English-only for initial production
7. **Real-time Features** - WebSocket implementation deferred

---

## Part 8: Claude Code Implementation Guide

### 8.1 Priority Order

```
1. [HIGHEST] Install Dusk & create base test class
2. [HIGHEST] Authentication tests (5 tests)
3. [HIGH] Dashboard tests per role (8 tests)
4. [HIGHEST] Activity module tests (15 tests)
5. [HIGH] RBAC enforcement tests (5 tests)
6. [MEDIUM] Trainee module tests (12 tests)
7. [MEDIUM] Staff module tests (8 tests)
8. [MEDIUM] Asset module tests (6 tests)
9. [MEDIUM] Letter generation tests (4 tests)
10. [HIGH] CI/CD pipeline setup
```

### 8.2 Test File Template

```php
<?php

namespace Tests\Browser\{Module};

use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class {Module}{Action}Test extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * Test: [Describe what this test validates]
     * Covers: [List functionality being tested]
     * UAT Case: [Reference UAT case if applicable]
     */
    public function test_{descriptive_test_name}(): void
    {
        $this->browse(function (Browser $browser) {
            // 1. Setup: Login, navigate
            // 2. Action: Perform user action
            // 3. Assert: Validate outcome
        });
    }
}
```

### 8.3 Running Tests

```bash
# Run all tests
php artisan test

# Run only unit tests
php artisan test --testsuite=Unit

# Run only feature tests
php artisan test --testsuite=Feature

# Run all browser tests
php artisan dusk

# Run specific browser test
php artisan dusk --filter=LoginTest

# Run tests with coverage
php artisan test --coverage

# Run tests in CI (headless)
php artisan dusk --without-tty
```

### 8.4 Debugging Failed Tests

```bash
# Screenshots saved to:
tests/Browser/screenshots/

# Console logs saved to:
tests/Browser/console/

# Run single test with output
php artisan dusk --filter=test_admin_can_create_activity -vvv
```

---

## Appendix A: UAT Test Case Mapping

The existing 69 UAT test cases (from `CREAMS_DETAILED_UAT_TEST_CASES.csv`) should map to browser tests:

| UAT ID | Module | Test Case | Browser Test File |
|--------|--------|-----------|-------------------|
| UAT-001 | Auth | Admin login | LoginTest.php |
| UAT-002 | Auth | Invalid login | LoginTest.php |
| UAT-003 | Dashboard | Admin view | AdminDashboardTest.php |
| UAT-004 | Dashboard | Supervisor view | SupervisorDashboardTest.php |
| ... | ... | ... | ... |

---

## Appendix B: Dusk Selector Conventions

Add Dusk selectors to Blade templates for reliable test targeting:

```html
<!-- Before -->
<button type="submit">Login</button>

<!-- After -->
<button type="submit" dusk="login-button">Login</button>
```

Common selectors to add:
- `dusk="login-button"` - Login form submit
- `dusk="logout-button"` - Logout action
- `dusk="activity-create-button"` - Create activity
- `dusk="trainee-list"` - Trainee listing table
- `dusk="attendance-form"` - Attendance marking form

---

## Document Control

| Version | Date | Author | Changes |
|---------|------|--------|---------|
| 1.0 | 2025-12-28 | Claude | Initial PRD |

---

**END OF DOCUMENT**

*This PRD is designed for consumption by Claude Code. Execute tasks in the priority order specified in Part 8. Report progress after each phase completion.*
