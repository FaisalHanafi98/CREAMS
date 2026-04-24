# CREAMS - Comprehensive Production Readiness Roadmap
**Date:** February 6, 2026
**Approach:** Strategic, Phased, Evidence-Based
**Timeline:** 6-8 Weeks to Production-Grade System
**Philosophy:** Do it right, not just fast

---

## STRATEGIC OVERVIEW

### Current State Assessment
- **Functionality:** 85% complete (core features working)
- **Test Coverage:** 100% pass rate (306/306 tests passing as of 2026-03-24; infrastructure fixed)
- **Security:** ~78% OWASP compliance (XSS fixed, debug routes removed, password policy strengthened, PII logging fixed, CentreScope GlobalScope added)
- **Performance:** Acceptable but not optimized (26s trainee creation, 19s schedule load)

### Target State (Production-Grade)
- **Functionality:** 100% verified and documented
- **Test Coverage:** 95%+ with robust infrastructure
- **Security:** 95%+ OWASP compliance with defense-in-depth
- **Performance:** <5s for all operations, <2s for page loads

### Guiding Principles
1. **Evidence-Based:** Every decision backed by data and testing
2. **Defense-in-Depth:** Multiple layers of security, not single fixes
3. **Maintainable:** Code quality and documentation for long-term support
4. **Scalable:** Architecture that can grow with the organization

---

## PHASE 0: COMPREHENSIVE AUDIT & BASELINE (Week 1)

**Objective:** Understand EXACTLY what we have before making changes.

### Day 1-2: Complete Functionality Audit

**Task 0.1: Module Inventory & Verification**

Create comprehensive checklist of ALL system features:

```markdown
## MODULE AUDIT CHECKLIST

### 1. Foundation Management
- [ ] Centre Management (CRUD)
  - [ ] Create centre with validation
  - [ ] View centre list (paginated)
  - [ ] Edit centre details
  - [ ] Soft delete centre
  - [ ] Restore deleted centre
  - [ ] Centre status toggle (active/inactive)
  - [ ] Centre capacity management

- [ ] Staff Management (CRUD)
  - [ ] Staff registration with all roles
  - [ ] Staff list with search/filter
  - [ ] Staff profile view
  - [ ] Staff profile edit
  - [ ] Staff role change (with authorization)
  - [ ] Staff deactivation
  - [ ] Staff avatar upload
  - [ ] Staff assignment to centres

- [ ] User Authentication & Authorization
  - [ ] Login (all roles: Admin, Supervisor, Teacher, AJK)
  - [ ] Logout with session clearing
  - [ ] Password reset request
  - [ ] Password reset confirmation
  - [ ] Remember me functionality
  - [ ] Session timeout handling
  - [ ] Role-based dashboard redirect

### 2. Client Management
- [ ] Trainee Registration
  - [ ] Full registration form (personal info, medical, guardian)
  - [ ] IC number validation (Malaysian format)
  - [ ] Phone number validation (Malaysian format)
  - [ ] Photo upload with consent
  - [ ] Duplicate email detection
  - [ ] Age validation (must be under 18 for rehabilitation)

- [ ] Trainee Management
  - [ ] Trainee list (paginated, searchable, filterable)
  - [ ] Trainee profile view (comprehensive)
  - [ ] Trainee profile edit
  - [ ] Trainee deactivation (with reason)
  - [ ] Trainee photo management
  - [ ] Medical history updates
  - [ ] Guardian information updates

- [ ] Volunteer Management
  - [ ] Volunteer application form
  - [ ] Volunteer approval workflow
  - [ ] Volunteer assignment to activities
  - [ ] Volunteer hour tracking

- [ ] Contact Management
  - [ ] Contact form submission (public)
  - [ ] Contact message list (admin)
  - [ ] Contact message reply
  - [ ] Contact message status tracking

### 3. Service Delivery Management
- [ ] Activity Management
  - [ ] Activity creation wizard (5 steps)
    - [ ] Step 1: Basic info (name, centre, category, description)
    - [ ] Step 2: Details (difficulty, age group, prerequisites)
    - [ ] Step 3: Schedule (period, dates, times, recurring days)
    - [ ] Step 4: Resources (instructor, participants, materials)
    - [ ] Step 5: Review and submit
  - [ ] Activity list (with filters by centre, category, status)
  - [ ] Activity details view
  - [ ] Activity edit (all steps)
  - [ ] Activity status change (active/inactive/completed)
  - [ ] Activity duplication
  - [ ] Activity deletion (soft delete)

- [ ] Activity Categories
  - [ ] Predefined categories (Autism Support, Physical Disabilities, etc.)
  - [ ] Category-based filtering
  - [ ] Category statistics

- [ ] Activity Sessions
  - [ ] Automatic session generation based on schedule
  - [ ] Manual session creation
  - [ ] Session rescheduling
  - [ ] Session cancellation
  - [ ] Session completion marking

- [ ] Activity Enrollment
  - [ ] Trainee enrollment in activities
  - [ ] Enrollment prerequisites validation
  - [ ] Enrollment capacity checking
  - [ ] Enrollment approval workflow
  - [ ] Enrollment cancellation
  - [ ] Waiting list management

### 4. Attendance Management
- [ ] Staff Attendance
  - [ ] Clock in/out system
  - [ ] Attendance record view
  - [ ] Attendance correction requests
  - [ ] Attendance reports by date range
  - [ ] Leave management

- [ ] Session Attendance
  - [ ] Mark attendance for activity sessions
  - [ ] Bulk attendance marking
  - [ ] Attendance status (Present, Absent, Excused, Late)
  - [ ] Attendance notes
  - [ ] Historical attendance view

- [ ] Attendance Alerts
  - [ ] Low attendance warnings
  - [ ] Consecutive absence detection
  - [ ] Attendance reports to guardians
  - [ ] Automated notifications

### 5. Assessment & Progress Tracking
- [ ] IEP (Individual Education Plan)
  - [ ] IEP creation for trainees
  - [ ] Goal setting and tracking
  - [ ] Progress notes
  - [ ] IEP review and updates
  - [ ] Guardian access to IEP

- [ ] Competency Assessment
  - [ ] 4-tier competency framework
  - [ ] Assessment recording per activity
  - [ ] Competency progression tracking
  - [ ] Competency reports

- [ ] Learning Outcomes
  - [ ] Learning outcome definition
  - [ ] Outcome mapping to activities
  - [ ] Outcome achievement tracking
  - [ ] Outcome reporting

- [ ] Progress Reports
  - [ ] Report generation (PDF)
  - [ ] Report templates
  - [ ] Progress visualization
  - [ ] Report history

### 6. Letter Generation & Communication
- [ ] Letter Templates
  - [ ] Template creation with headers/footers
  - [ ] Template editing
  - [ ] Template versioning
  - [ ] Template activation/deactivation

- [ ] Letter Generation
  - [ ] Modern letter builder
  - [ ] Recipient selection (trainee, guardian, staff)
  - [ ] Dynamic field insertion
  - [ ] Letter preview
  - [ ] PDF generation
  - [ ] Letter download

- [ ] Letter Archive
  - [ ] Letter history view
  - [ ] Letter search and filter
  - [ ] Letter re-download
  - [ ] Letter deletion

- [ ] Notification System
  - [ ] Email notifications (activity enrollment, alerts, etc.)
  - [ ] In-app notifications
  - [ ] Notification preferences
  - [ ] Notification history
  - [ ] Mark as read/unread
  - [ ] Notification badges

- [ ] Messaging System
  - [ ] Internal messaging between users
  - [ ] Message threads
  - [ ] Message attachments
  - [ ] Message search
  - [ ] Unread message indicators

### 7. Centre-Specific Features
- [ ] Asset Management
  - [ ] Asset registration (equipment, facilities)
  - [ ] Asset assignment to centres
  - [ ] Asset maintenance tracking
  - [ ] Asset movement/transfer
  - [ ] Asset reports

- [ ] Centre Attendance (Different from Session Attendance)
  - [ ] Daily centre check-in for trainees
  - [ ] Centre attendance reports
  - [ ] Centre capacity monitoring

### 8. Reporting & Analytics
- [ ] Dashboard
  - [ ] Role-based dashboard views
  - [ ] Key metrics (trainees enrolled, activities active, attendance rate)
  - [ ] Quick links to common tasks
  - [ ] Recent activity feed

- [ ] Reports
  - [ ] Activity reports (enrollment, completion, outcomes)
  - [ ] Trainee reports (progress, attendance, assessments)
  - [ ] Staff reports (attendance, activities taught)
  - [ ] Centre reports (utilization, capacity, statistics)
  - [ ] Custom date range filtering
  - [ ] Export to PDF/Excel

### 9. Profile & Settings
- [ ] User Profile
  - [ ] View own profile
  - [ ] Edit profile (name, phone, email, about, education)
  - [ ] Change password
  - [ ] Avatar upload
  - [ ] Profile photo management

- [ ] System Settings (Admin only)
  - [ ] System configuration
  - [ ] Email settings
  - [ ] Notification settings
  - [ ] Security settings
  - [ ] Backup/restore

### 10. Additional Features
- [ ] Global Search
  - [ ] Search across trainees, staff, activities
  - [ ] Quick search in header
  - [ ] Advanced search with filters

- [ ] Audit Logging
  - [ ] User action logging
  - [ ] Data change tracking
  - [ ] Login/logout tracking
  - [ ] Audit log viewing (admin)

- [ ] Data Export
  - [ ] Export trainees to Excel
  - [ ] Export activities to Excel
  - [ ] Export attendance records
  - [ ] Bulk data export for reporting
```

**Deliverable:** Complete functionality matrix (Excel/Google Sheets) with test status for each feature.

---

**Task 0.2: Database Schema Documentation**

Generate complete database documentation:

```bash
# Generate schema documentation
php artisan db:show
php artisan migrate:status

# Export schema to SQL
mysqldump -u root -p --no-data cream > schema.sql

# Generate ERD using Laravel package
composer require --dev beyondcode/laravel-er-diagram-generator
php artisan generate:erd output.png

# Or use online tool
mysql -u root -p cream -e "SHOW TABLES;" | tail -n +2 | xargs -I {} mysql -u root -p cream -e "DESCRIBE {};" > schema_details.txt
```

Document:
- All tables with column definitions
- Foreign key relationships
- Indexes (identify missing indexes)
- Data types and constraints
- Soft deletes tracking

**Deliverable:** `DATABASE_SCHEMA_DOCUMENTATION.md` with ERD diagram.

---

**Task 0.3: API Endpoint Inventory**

Document ALL routes:

```bash
php artisan route:list --columns=method,uri,name,action,middleware > routes_inventory.txt
```

Categorize routes:
- Public (no auth required)
- Authenticated
- Role-restricted (admin, supervisor, teacher, ajk)
- Debug/test routes (to be removed)
- API routes

**Deliverable:** `API_ENDPOINT_INVENTORY.md` with security classification.

---

**Task 0.4: Performance Baseline**

Capture current performance metrics:

```bash
# Install Laravel Debugbar for profiling
composer require barryvdh/laravel-debugbar --dev

# Enable query logging
DB::enableQueryLog();

# Profile key operations
# - Trainee creation: 26s (capture query count, memory usage)
# - Activity schedule load: 19.5s (identify slow queries)
# - Dashboard load: measure for each role
# - Letter generation: measure PDF creation time
```

Create performance baseline spreadsheet:
| Operation | Current Time | Query Count | Memory Usage | N+1 Issues | Target Time |
|-----------|--------------|-------------|--------------|------------|-------------|
| Trainee creation | 26s | ? | ? | ? | <5s |
| Activity schedule | 19.5s | ? | ? | ? | <2s |
| Dashboard (Admin) | ? | ? | ? | ? | <1s |
| Letter PDF | ? | ? | ? | ? | <3s |

**Deliverable:** `PERFORMANCE_BASELINE.xlsx` with detailed profiling data.

---

**Task 0.5: Security Baseline Scan**

Run automated security scans:

```bash
# 1. PHP Security Checker
composer require --dev enlightn/security-checker
php artisan security-check

# 2. Static Analysis
./vendor/bin/phpstan analyse app/ --level=8

# 3. Dependency vulnerabilities
composer audit

# 4. OWASP ZAP baseline scan (if server running)
docker run -t owasp/zap2docker-stable zap-baseline.py -t http://localhost:8000

# 5. Code quality
composer require --dev squizlabs/php_codesniffer
./vendor/bin/phpcs --standard=PSR12 app/
```

**Deliverable:** `SECURITY_BASELINE_REPORT.md` with all scan results.

---

### Day 3-4: Functional Verification Testing

**Task 0.6: Manual End-to-End Testing**

Test complete user journeys for each role:

**Admin Journey:**
1. Login as admin
2. Create new centre
3. Register new staff member (all roles)
4. Create activity (full wizard)
5. Enroll trainees in activity
6. Mark session attendance
7. Generate progress report
8. Generate letter
9. View dashboard statistics
10. Logout

**Supervisor Journey:**
1. Login as supervisor
2. View assigned centre's trainees
3. Approve trainee enrollment
4. View activity schedules
5. Mark own attendance
6. Generate centre report
7. Logout

**Teacher Journey:**
1. Login as teacher
2. View assigned activities
3. Mark session attendance
4. Update trainee progress
5. Add session notes
6. View assigned trainees
7. Logout

**AJK Journey:**
1. Login as AJK
2. View trainee list
3. Update trainee medical info
4. View trainee progress
5. Communicate with guardians
6. Logout

**Deliverable:** `FUNCTIONAL_TEST_RESULTS.xlsx` with pass/fail status and screenshots.

---

**Task 0.7: Cross-Browser & Device Testing**

Test on:
- Chrome (latest)
- Firefox (latest)
- Edge (latest)
- Safari (if available)
- Mobile (iOS Safari, Android Chrome)

Test key workflows:
- Login/logout
- Form submission (trainee registration, activity creation)
- File upload (photos, documents)
- PDF generation
- Dashboard responsiveness

**Deliverable:** `BROWSER_COMPATIBILITY_MATRIX.xlsx`

---

### Day 5: Report Review & Gap Analysis

**Task 0.8: Consolidate Findings**

Review all generated reports:
- Test Stabilization Report
- Security Audit Report
- Functionality Matrix
- Performance Baseline
- Browser Compatibility

**Create Gap Analysis:**

| Category | Current State | Target State | Gap | Priority | Effort |
|----------|---------------|--------------|-----|----------|--------|
| Functionality | 85% verified | 100% working | 15% | HIGH | 2 weeks |
| Security | ~78% OWASP (was 66%, improved 2026-03-24) | 95% OWASP | ~17% | HIGH | 2 weeks |
| Performance | Acceptable | Optimized | Medium | MEDIUM | 2 weeks |
| Test Coverage | 100% pass rate (306 tests, was 81%) | 95%+ line coverage | Line coverage gap | HIGH | 2 weeks |
| Documentation | Partial | Complete | High | MEDIUM | 1 week |

**Deliverable:** `GAP_ANALYSIS_AND_PRIORITIES.md`

---

## PHASE 1: COMPREHENSIVE SECURITY HARDENING (Weeks 2-4)

**Objective:** Implement defense-in-depth security, not just patch vulnerabilities.

### Week 2: Foundation Security (Layer 1)

**Task 1.1: Secure Configuration Management**

**1.1.1: Environment Configuration Hardening**

```bash
# Create environment-specific configs
.env.production.example
.env.staging.example
.env.development.example
```

**Production .env hardening:**
```env
# Application
APP_ENV=production
APP_DEBUG=false
APP_LOG_LEVEL=error

# Security
SESSION_ENCRYPT=true
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=strict
SESSION_HTTP_ONLY=true

# Passwords
PASSWORD_MIN_LENGTH=12
PASSWORD_REQUIRE_UPPERCASE=true
PASSWORD_REQUIRE_LOWERCASE=true
PASSWORD_REQUIRE_NUMBERS=true
PASSWORD_REQUIRE_SYMBOLS=true

# Rate Limiting
RATE_LIMIT_LOGIN=5
RATE_LIMIT_API=60
RATE_LIMIT_GLOBAL=100

# Lockout
LOGIN_LOCKOUT_ATTEMPTS=5
LOGIN_LOCKOUT_DURATION=900

# Session
SESSION_LIFETIME=480
SESSION_IDLE_TIMEOUT=60

# Security Headers
HSTS_ENABLED=true
CSP_ENABLED=true
X_FRAME_OPTIONS=DENY
```

**1.1.2: Secrets Management**

Implement Laravel's encryption for sensitive data:

```php
// config/database.php
'mysql' => [
    'password' => env('DB_PASSWORD') ? Crypt::decryptString(env('DB_PASSWORD_ENCRYPTED')) : '',
],

// Command to encrypt secrets
php artisan tinker
> use Illuminate\Support\Facades\Crypt;
> Crypt::encryptString('your_password_here');
```

Or use external secrets manager (for enterprise):
- AWS Secrets Manager
- HashiCorp Vault
- Azure Key Vault

**1.1.3: Configuration Validation Middleware**

Create middleware to validate critical config on every request:

```php
// app/Http/Middleware/ValidateSecurityConfig.php
class ValidateSecurityConfig
{
    public function handle($request, Closure $next)
    {
        // In production, ensure critical security settings
        if (app()->environment('production')) {
            abort_unless(config('app.debug') === false, 500, 'Debug mode must be off in production');
            abort_unless(config('session.secure') === true, 500, 'Secure cookies required');
            abort_unless(config('session.encrypt') === true, 500, 'Session encryption required');
        }

        return $next($request);
    }
}
```

---

**Task 1.2: Input Validation & Sanitization Layer**

**1.2.1: Create Custom Validation Rules**

```php
// app/Rules/NoSQLInjection.php
class NoSQLInjection implements Rule
{
    public function passes($attribute, $value)
    {
        $malicious_patterns = [
            '/(\b(SELECT|INSERT|UPDATE|DELETE|DROP|CREATE|ALTER|EXEC)\b)/i',
            '/(\-\-|\;|\*|\/\*|\*\/|\@\@|char|nchar|varchar|nvarchar|sp_|xp_)/',
        ];

        foreach ($malicious_patterns as $pattern) {
            if (preg_match($pattern, $value)) {
                return false;
            }
        }

        return true;
    }

    public function message()
    {
        return 'The :attribute contains potentially malicious content.';
    }
}

// app/Rules/NoXSS.php
class NoXSS implements Rule
{
    public function passes($attribute, $value)
    {
        $malicious_patterns = [
            '/<script\b[^>]*>(.*?)<\/script>/is',
            '/javascript:/i',
            '/on\w+\s*=/i', // onclick, onerror, etc.
            '/<iframe/i',
            '/<object/i',
            '/<embed/i',
        ];

        foreach ($malicious_patterns as $pattern) {
            if (preg_match($pattern, $value)) {
                return false;
            }
        }

        return true;
    }

    public function message()
    {
        return 'The :attribute contains potentially malicious content.';
    }
}

// app/Rules/SafeFilename.php
class SafeFilename implements Rule
{
    public function passes($attribute, $value)
    {
        // Only allow alphanumeric, dash, underscore, dot
        return preg_match('/^[a-zA-Z0-9_\-\.]+$/', $value);
    }
}
```

**1.2.2: Apply Validation Globally**

Create base request class:

```php
// app/Http/Requests/SecureRequest.php
abstract class SecureRequest extends FormRequest
{
    protected function prepareForValidation()
    {
        // Strip tags from all string inputs (except explicitly allowed)
        $input = $this->all();

        $allowHtml = $this->allowHtmlFields() ?? [];

        array_walk_recursive($input, function (&$value, $key) use ($allowHtml) {
            if (is_string($value) && !in_array($key, $allowHtml)) {
                $value = strip_tags($value);
            }
        });

        $this->replace($input);
    }

    // Override in child classes to allow HTML in specific fields
    protected function allowHtmlFields(): array
    {
        return [];
    }

    // Add security rules to all requests
    protected function baseSecurityRules(): array
    {
        return [
            '*' => [new NoXSS(), new NoSQLInjection()],
        ];
    }
}
```

**1.2.3: Sanitize Output in Blade Templates**

Create Blade directive for safe output:

```php
// app/Providers/AppServiceProvider.php
Blade::directive('safeEcho', function ($expression) {
    return "<?php echo e($expression); ?>";
});

// Usage in views
{{-- Instead of {!! $userContent !!} --}}
@safeEcho($userContent)

// Or for content that needs formatting
{!! nl2br(e($content)) !!}
```

---

**Task 1.3: Authentication & Session Security**

**1.3.1: Enhanced Authentication**

```php
// app/Http/Controllers/Auth/LoginController.php
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

public function check(Request $request)
{
    $throttleKey = $this->throttleKey($request);

    // Check rate limiting
    if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
        $seconds = RateLimiter::availableIn($throttleKey);

        // Log suspicious activity
        Log::warning('Login rate limit exceeded', [
            'ip' => $request->ip(),
            'email' => $request->input('identifier'),
            'user_agent' => $request->userAgent(),
        ]);

        throw ValidationException::withMessages([
            'identifier' => ["Too many login attempts. Please try again in {$seconds} seconds."],
        ])->status(429);
    }

    // Validate input
    $validated = $request->validate([
        'identifier' => ['required', 'string', new NoXSS()],
        'password' => 'required|string',
    ]);

    // Authenticate
    $user = $this->authenticate($validated['identifier'], $validated['password']);

    if (!$user) {
        // Increment failed attempts
        RateLimiter::hit($throttleKey, 60);

        // Log failed attempt
        Log::warning('Failed login attempt', [
            'identifier' => $validated['identifier'],
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        throw ValidationException::withMessages([
            'identifier' => ['These credentials do not match our records.'],
        ]);
    }

    // Clear rate limiter on success
    RateLimiter::clear($throttleKey);

    // CRITICAL: Regenerate session ID (prevent session fixation)
    $request->session()->regenerate();

    // Store user data
    session([
        'id' => $user->id,
        'role' => $user->role,
        'name' => $user->name,
        'email' => $user->email,
        'centre_id' => $user->centre_id,
        'login_time' => now(),
        'last_activity' => now(),
    ]);

    // Log successful login
    Log::info('User logged in', [
        'user_id' => $user->id,
        'role' => $user->role,
        'ip' => $request->ip(),
    ]);

    return response()->json([
        'status' => 'success',
        'redirect' => $this->getRedirectUrl($user->role),
    ]);
}

protected function throttleKey(Request $request): string
{
    return Str::lower($request->input('identifier')).'|'.$request->ip();
}
```

**1.3.2: Session Security Middleware**

```php
// app/Http/Middleware/SecureSession.php
class SecureSession
{
    public function handle($request, Closure $next)
    {
        if (!session()->has('id')) {
            return $next($request);
        }

        // Check session age (regenerate every 10 minutes)
        if (!session()->has('last_regeneration') ||
            session('last_regeneration')->diffInMinutes(now()) > 10) {
            session()->regenerate();
            session(['last_regeneration' => now()]);
        }

        // Check idle timeout
        $idleTimeout = config('session.idle_timeout', 60); // minutes
        if (session()->has('last_activity')) {
            $lastActivity = session('last_activity');
            if (now()->diffInMinutes($lastActivity) > $idleTimeout) {
                session()->flush();
                return redirect()->route('login')->with('message', 'Session expired due to inactivity');
            }
        }

        // Update last activity
        session(['last_activity' => now()]);

        // Validate session integrity
        $this->validateSessionIntegrity($request);

        return $next($request);
    }

    protected function validateSessionIntegrity($request)
    {
        // Check if user's IP changed (potential session hijacking)
        if (session()->has('ip_address') && session('ip_address') !== $request->ip()) {
            Log::critical('Potential session hijacking detected', [
                'user_id' => session('id'),
                'original_ip' => session('ip_address'),
                'current_ip' => $request->ip(),
            ]);

            session()->flush();
            abort(401, 'Session security validation failed');
        }

        // Store IP on first request
        if (!session()->has('ip_address')) {
            session(['ip_address' => $request->ip()]);
        }

        // Validate user agent hasn't changed
        if (session()->has('user_agent') && session('user_agent') !== $request->userAgent()) {
            Log::warning('User agent mismatch detected', [
                'user_id' => session('id'),
            ]);
        }

        if (!session()->has('user_agent')) {
            session(['user_agent' => $request->userAgent()]);
        }
    }
}
```

---

### Week 3: Application Security (Layer 2)

**Task 1.4: Authorization Framework**

**1.4.1: Implement Laravel Policies for All Models**

```php
// app/Policies/TraineePolicy.php
class TraineePolicy
{
    use HandlesAuthorization;

    // View any trainee
    public function viewAny(User $user): bool
    {
        return in_array($user->role, ['admin', 'supervisor', 'teacher', 'ajk']);
    }

    // View specific trainee (centre-restricted)
    public function view(User $user, Trainee $trainee): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        // Supervisor/Teacher/AJK can only view trainees from their centre
        return $user->centre_id === $trainee->centre_id;
    }

    // Create trainee
    public function create(User $user): bool
    {
        return in_array($user->role, ['admin', 'supervisor', 'ajk']);
    }

    // Update trainee
    public function update(User $user, Trainee $trainee): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        if (!in_array($user->role, ['supervisor', 'ajk'])) {
            return false;
        }

        return $user->centre_id === $trainee->centre_id;
    }

    // Delete trainee
    public function delete(User $user, Trainee $trainee): bool
    {
        if ($user->role !== 'admin') {
            return false;
        }

        return $user->centre_id === $trainee->centre_id;
    }

    // Update medical info (restricted to AJK and Admin)
    public function updateMedical(User $user, Trainee $trainee): bool
    {
        if (!in_array($user->role, ['admin', 'ajk'])) {
            return false;
        }

        return $user->centre_id === $trainee->centre_id;
    }
}

// Register all policies in AuthServiceProvider
protected $policies = [
    Activity::class => ActivityPolicy::class,
    Trainee::class => TraineePolicy::class,
    Staff::class => StaffPolicy::class,
    Centre::class => CentrePolicy::class,
    Letter::class => LetterPolicy::class,
    // ... all models
];
```

**1.4.2: Enforce Policies in Controllers**

```php
// app/Http/Controllers/Trainee/TraineeController.php
class TraineeController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Trainee::class);

        $trainees = Trainee::query()
            ->when(auth()->user()->role !== 'admin', function ($query) {
                $query->where('centre_id', auth()->user()->centre_id);
            })
            ->paginate(20);

        return view('trainees.index', compact('trainees'));
    }

    public function show(Trainee $trainee)
    {
        $this->authorize('view', $trainee);

        return view('trainees.show', compact('trainee'));
    }

    public function update(Request $request, Trainee $trainee)
    {
        $this->authorize('update', $trainee);

        // Update logic...
    }
}
```

---

**Task 1.5: XSS Protection & Output Encoding**

**1.5.1: Fix Identified XSS Vulnerabilities**

```php
// routes/web.php - FIX /letters-archive route
Route::get('/letters-archive', function (Request $request) {
    $letters = Letter::with('creator')->get();

    $html = '<table>';
    $html .= '<thead><tr><th>Subject</th><th>Recipient</th><th>Generated By</th><th>Date</th></tr></thead>';
    $html .= '<tbody>';

    foreach ($letters as $letter) {
        $letterData = json_decode($letter->letter_data, true) ?? [];

        $html .= '<tr>';
        $html .= '<td>' . e(\Str::limit($letter->letter_subject, 50)) . '</td>';  // ✅ ESCAPED
        $html .= '<td>' . e($letterData['recipient_name'] ?? 'Unknown') . '</td>';  // ✅ ESCAPED
        $html .= '<td>' . e($letterData['generated_by_name'] ?? 'Unknown') . '</td>';  // ✅ ESCAPED
        $html .= '<td>' . e($letter->created_at->format('Y-m-d')) . '</td>';  // ✅ ESCAPED
        $html .= '</tr>';
    }

    $html .= '</tbody></table>';
    $html .= '<p><strong>User:</strong> ' . e(session('name')) . '</p>';  // ✅ ESCAPED

    return response($html);
})->middleware('auth');
```

**Better: Convert to Blade View**

```php
// routes/web.php
Route::get('/letters-archive', [LetterController::class, 'archive'])->middleware('auth')->name('letters.archive');

// app/Http/Controllers/LetterController.php
public function archive()
{
    $letters = Letter::with('creator')->latest()->paginate(50);
    return view('letters.archive', compact('letters'));
}

// resources/views/letters/archive.blade.php
<table class="table">
    <thead>
        <tr>
            <th>Subject</th>
            <th>Recipient</th>
            <th>Generated By</th>
            <th>Date</th>
        </tr>
    </thead>
    <tbody>
        @foreach($letters as $letter)
            <tr>
                <td>{{ Str::limit($letter->letter_subject, 50) }}</td>
                <td>{{ $letter->recipientName ?? 'Unknown' }}</td>
                <td>{{ $letter->creator->name ?? 'Unknown' }}</td>
                <td>{{ $letter->created_at->format('Y-m-d') }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
```

**1.5.2: Content Security Policy (CSP)**

```php
// app/Http/Middleware/ContentSecurityPolicy.php
class ContentSecurityPolicy
{
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        if (config('app.env') === 'production') {
            $csp = [
                "default-src 'self'",
                "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net", // Adjust for your CDN
                "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com",
                "font-src 'self' https://fonts.gstatic.com",
                "img-src 'self' data: https:",
                "connect-src 'self'",
                "frame-ancestors 'none'",
                "base-uri 'self'",
                "form-action 'self'",
            ];

            $response->headers->set('Content-Security-Policy', implode('; ', $csp));
        }

        return $response;
    }
}
```

**1.5.3: Comprehensive Security Headers**

```php
// app/Http/Middleware/SecurityHeaders.php
class SecurityHeaders
{
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        // Prevent clickjacking
        $response->headers->set('X-Frame-Options', 'DENY');

        // Prevent MIME sniffing
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // XSS Protection (legacy browsers)
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // Referrer Policy
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Permissions Policy
        $response->headers->set('Permissions-Policy', 'geolocation=(), microphone=(), camera=()');

        // HSTS (only in production with HTTPS)
        if ($request->secure() && config('app.env') === 'production') {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
        }

        return $response;
    }
}
```

---

**Task 1.6: File Upload Security**

**1.6.1: Secure File Upload Service**

```php
// app/Services/SecureFileUploadService.php
class SecureFileUploadService
{
    protected $allowedMimeTypes = [
        'image' => ['image/jpeg', 'image/png', 'image/gif', 'image/webp'],
        'document' => ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
    ];

    protected $maxFileSizes = [
        'image' => 5 * 1024 * 1024, // 5MB
        'document' => 10 * 1024 * 1024, // 10MB
    ];

    public function upload(UploadedFile $file, string $type, string $directory): string
    {
        // Validate file type
        $this->validateFileType($file, $type);

        // Validate file size
        $this->validateFileSize($file, $type);

        // Scan for malware (if ClamAV available)
        $this->scanForMalware($file);

        // Generate secure filename
        $filename = $this->generateSecureFilename($file);

        // Store file outside public directory
        $path = $file->storeAs(
            $directory,
            $filename,
            'private' // Use private disk, not public
        );

        return $path;
    }

    protected function validateFileType(UploadedFile $file, string $type): void
    {
        $allowedTypes = $this->allowedMimeTypes[$type] ?? [];

        // Check MIME type
        if (!in_array($file->getMimeType(), $allowedTypes)) {
            throw new \InvalidArgumentException('Invalid file type');
        }

        // Double-check file extension
        $allowedExtensions = [
            'image' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
            'document' => ['pdf', 'doc', 'docx'],
        ];

        $extension = strtolower($file->getClientOriginalExtension());
        if (!in_array($extension, $allowedExtensions[$type] ?? [])) {
            throw new \InvalidArgumentException('Invalid file extension');
        }

        // For images, validate it's actually an image
        if ($type === 'image') {
            $imageInfo = getimagesize($file->getRealPath());
            if ($imageInfo === false) {
                throw new \InvalidArgumentException('File is not a valid image');
            }
        }
    }

    protected function validateFileSize(UploadedFile $file, string $type): void
    {
        $maxSize = $this->maxFileSizes[$type] ?? $this->maxFileSizes['document'];

        if ($file->getSize() > $maxSize) {
            throw new \InvalidArgumentException('File size exceeds maximum allowed');
        }
    }

    protected function scanForMalware(UploadedFile $file): void
    {
        // If ClamAV is installed
        if (class_exists(\Xenolope\Quahog\Client::class)) {
            $scanner = new \Xenolope\Quahog\Client(
                new \Socket\Raw\Factory(),
                'unix:///var/run/clamav/clamd.ctl'
            );

            $result = $scanner->scanFile($file->getRealPath());

            if ($result['status'] === 'FOUND') {
                Log::critical('Malware detected in uploaded file', [
                    'filename' => $file->getClientOriginalName(),
                    'virus' => $result['reason'],
                ]);

                throw new \RuntimeException('Malware detected in file');
            }
        }
    }

    protected function generateSecureFilename(UploadedFile $file): string
    {
        // Generate cryptographically secure random filename
        $hash = hash('sha256', $file->getClientOriginalName() . time() . random_bytes(16));
        $extension = $file->getClientOriginalExtension();

        return substr($hash, 0, 40) . '.' . $extension;
    }
}
```

**1.6.2: Fix Insecure File Moves**

```php
// app/Http/Controllers/Trainee/TraineeRegistrationController.php
// BEFORE (Line 402):
$file->move(public_path('storage/trainee_avatars'), $filename);

// AFTER:
$uploadService = app(SecureFileUploadService::class);
$path = $uploadService->upload($request->file('avatar'), 'image', 'trainee_avatars');

// Store path in database
$trainee->avatar_path = $path;
$trainee->save();
```

---

### Week 4: Data & Infrastructure Security (Layer 3)

**Task 1.7: Database Security**

**1.7.1: Encrypt Sensitive Columns**

```php
// Install encryption package
composer require pragmarx/cryptographic-columns

// app/Models/Trainee.php
use PragmaRX\CryptographicColumns\Traits\Encryptable;

class Trainee extends Model
{
    use Encryptable;

    protected $encryptable = [
        'ic_number',
        'phone',
        'guardian_phone',
        'medical_history',
    ];
}
```

**1.7.2: Database Access Control**

```sql
-- Create application-specific database user
CREATE USER 'creams_app'@'localhost' IDENTIFIED BY 'StrongPassword!2024';

-- Grant only necessary permissions
GRANT SELECT, INSERT, UPDATE ON cream.* TO 'creams_app'@'localhost';

-- Don't grant DELETE or DROP (handle via soft deletes)
-- Don't grant CREATE or ALTER (migrations run separately)

-- Create read-only user for reports
CREATE USER 'creams_readonly'@'localhost' IDENTIFIED BY 'ReadOnlyPass!2024';
GRANT SELECT ON cream.* TO 'creams_readonly'@'localhost';
```

**1.7.3: Database Auditing**

```php
// Create audit log table
php artisan make:migration create_audit_logs_table

// migration
Schema::create('audit_logs', function (Blueprint $table) {
    $table->id();
    $table->string('action'); // create, update, delete
    $table->string('model'); // Model class name
    $table->unsignedBigInteger('model_id');
    $table->unsignedBigInteger('user_id')->nullable();
    $table->json('old_values')->nullable();
    $table->json('new_values')->nullable();
    $table->string('ip_address');
    $table->string('user_agent');
    $table->timestamps();

    $table->index(['model', 'model_id']);
    $table->index('user_id');
    $table->index('created_at');
});

// app/Observers/AuditObserver.php
class AuditObserver
{
    public function created($model)
    {
        AuditLog::create([
            'action' => 'create',
            'model' => get_class($model),
            'model_id' => $model->id,
            'user_id' => auth()->id(),
            'new_values' => $model->toArray(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    public function updated($model)
    {
        AuditLog::create([
            'action' => 'update',
            'model' => get_class($model),
            'model_id' => $model->id,
            'user_id' => auth()->id(),
            'old_values' => $model->getOriginal(),
            'new_values' => $model->getChanges(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    public function deleted($model)
    {
        AuditLog::create([
            'action' => 'delete',
            'model' => get_class($model),
            'model_id' => $model->id,
            'user_id' => auth()->id(),
            'old_values' => $model->toArray(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}

// Register observers for critical models
Trainee::observe(AuditObserver::class);
Activity::observe(AuditObserver::class);
Staff::observe(AuditObserver::class);
```

---

**Task 1.8: Logging & Monitoring**

**1.8.1: Comprehensive Security Logging**

```php
// app/Logging/SecurityLogger.php
class SecurityLogger
{
    public static function logAuthenticationAttempt(bool $success, string $identifier, Request $request)
    {
        Log::channel('security')->info('Authentication attempt', [
            'success' => $success,
            'identifier' => $identifier,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'timestamp' => now(),
        ]);
    }

    public static function logAuthorizationFailure(User $user, string $action, $model)
    {
        Log::channel('security')->warning('Authorization failure', [
            'user_id' => $user->id,
            'user_role' => $user->role,
            'action' => $action,
            'model' => get_class($model),
            'model_id' => $model->id ?? null,
            'ip' => request()->ip(),
            'timestamp' => now(),
        ]);
    }

    public static function logSuspiciousActivity(string $description, array $context = [])
    {
        Log::channel('security')->critical('Suspicious activity detected', array_merge([
            'description' => $description,
            'user_id' => auth()->id(),
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'url' => request()->fullUrl(),
            'timestamp' => now(),
        ], $context));
    }
}

// config/logging.php
'channels' => [
    'security' => [
        'driver' => 'daily',
        'path' => storage_path('logs/security.log'),
        'level' => 'debug',
        'days' => 90, // Keep security logs for 90 days
    ],
],
```

**1.8.2: Intrusion Detection**

```php
// app/Http/Middleware/IntrusionDetection.php
class IntrusionDetection
{
    protected $suspiciousPatterns = [
        'sql_injection' => '/(\b(SELECT|INSERT|UPDATE|DELETE|DROP|CREATE|ALTER)\b.*\b(FROM|INTO|WHERE|TABLE)\b)/i',
        'xss' => '/<script|javascript:|onerror=|onload=/i',
        'path_traversal' => '/\.\.(\/|\\\\)/i',
        'command_injection' => '/;|\||&|`|\$\(|\$\{/i',
    ];

    public function handle($request, Closure $next)
    {
        // Check all input parameters
        $this->scanInput($request->all(), $request);

        // Check URL for suspicious patterns
        if ($this->containsSuspiciousPattern($request->fullUrl())) {
            SecurityLogger::logSuspiciousActivity('Suspicious URL pattern detected', [
                'url' => $request->fullUrl(),
            ]);

            abort(403, 'Suspicious request detected');
        }

        return $next($request);
    }

    protected function scanInput(array $input, Request $request, string $path = '')
    {
        foreach ($input as $key => $value) {
            if (is_array($value)) {
                $this->scanInput($value, $request, $path . $key . '.');
            } elseif (is_string($value)) {
                if ($this->containsSuspiciousPattern($value)) {
                    SecurityLogger::logSuspiciousActivity('Suspicious input pattern detected', [
                        'field' => $path . $key,
                        'value' => $value,
                    ]);

                    throw ValidationException::withMessages([
                        $key => 'Invalid input detected.',
                    ]);
                }
            }
        }
    }

    protected function containsSuspiciousPattern(string $input): bool
    {
        foreach ($this->suspiciousPatterns as $type => $pattern) {
            if (preg_match($pattern, $input)) {
                return true;
            }
        }

        return false;
    }
}
```

---

**Task 1.9: Backup & Disaster Recovery**

**1.9.1: Automated Backup Script**

```bash
#!/bin/bash
# scripts/backup.sh

# Configuration
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/backups/creams"
DB_NAME="cream"
DB_USER="root"
DB_PASS="your_password"

# Create backup directory
mkdir -p $BACKUP_DIR

# Database backup
mysqldump -u $DB_USER -p$DB_PASS $DB_NAME | gzip > $BACKUP_DIR/db_$DATE.sql.gz

# Files backup (storage directory)
tar -czf $BACKUP_DIR/files_$DATE.tar.gz /path/to/creams/storage

# Keep only last 30 days of backups
find $BACKUP_DIR -name "*.gz" -mtime +30 -delete

# Upload to S3 (optional)
if command -v aws &> /dev/null; then
    aws s3 sync $BACKUP_DIR s3://creams-backups/
fi

# Log backup completion
echo "[$DATE] Backup completed successfully" >> /var/log/creams_backup.log
```

**1.9.2: Backup Verification Script**

```bash
#!/bin/bash
# scripts/verify_backup.sh

LATEST_BACKUP=$(ls -t /backups/creams/db_*.sql.gz | head -1)

# Test restore to temporary database
zcat $LATEST_BACKUP | mysql -u root -p creams_test

# Verify table counts
TABLE_COUNT=$(mysql -u root -p -e "USE creams_test; SHOW TABLES;" | wc -l)

if [ $TABLE_COUNT -gt 30 ]; then
    echo "Backup verification successful: $TABLE_COUNT tables"
    exit 0
else
    echo "Backup verification FAILED: only $TABLE_COUNT tables"
    exit 1
fi
```

**1.9.3: Cron Setup**

```bash
# Daily backup at 2 AM
0 2 * * * /path/to/creams/scripts/backup.sh

# Weekly backup verification on Sundays
0 3 * * 0 /path/to/creams/scripts/verify_backup.sh
```

---

**Deliverables for Weeks 2-4:**
- [ ] Secured .env configuration with validation
- [ ] Custom validation rules applied globally
- [ ] Enhanced authentication with rate limiting
- [ ] Session security middleware
- [ ] Policies for all models
- [ ] XSS vulnerabilities fixed
- [ ] Security headers implemented
- [ ] Secure file upload service
- [ ] Database encryption for sensitive data
- [ ] Audit logging system
- [ ] Security logging and monitoring
- [ ] Intrusion detection middleware
- [ ] Automated backup system
- [ ] Backup verification process

**Verification:**
- [ ] Run security scan (composer audit, OWASP ZAP)
- [ ] Penetration test simulation
- [ ] Security checklist review
- [ ] Documentation updated

---

## PHASE 2: ADVANCED TEST INFRASTRUCTURE (Weeks 5-7)

**Objective:** Build robust, maintainable, comprehensive test suite.

### Week 5: Test Foundation Architecture

**Task 2.1: Test Database Architecture**

**2.1.1: Separate Test Database with Seeding Strategy**

```php
// config/database.php - Add test connection
'connections' => [
    'mysql_test' => [
        'driver' => 'mysql',
        'host' => env('DB_TEST_HOST', '127.0.0.1'),
        'database' => env('DB_TEST_DATABASE', 'cream_test'),
        'username' => env('DB_TEST_USERNAME', 'root'),
        'password' => env('DB_TEST_PASSWORD', ''),
        // ... rest of config
    ],
],

// .env.testing
APP_ENV=testing
DB_CONNECTION=mysql_test
DB_TEST_DATABASE=cream_test
```

**2.1.2: Fast Test Data Factories**

```php
// database/factories/TestDataFactory.php
class TestDataFactory
{
    public static function createCompleteTestEnvironment(): array
    {
        // Create centres
        $centres = Centre::factory()->count(4)->create();

        // Create users for each role
        $admin = User::factory()->admin()->create(['centre_id' => $centres[0]->centre_id]);
        $supervisor = User::factory()->supervisor()->create(['centre_id' => $centres[1]->centre_id]);
        $teachers = User::factory()->teacher()->count(10)->create();
        $ajks = User::factory()->ajk()->count(5)->create();

        // Create trainees
        $trainees = Trainee::factory()->count(50)->create();

        // Create activities
        $activities = Activity::factory()->count(20)->create();

        return [
            'centres' => $centres,
            'admin' => $admin,
            'supervisor' => $supervisor,
            'teachers' => $teachers,
            'ajks' => $ajks,
            'trainees' => $trainees,
            'activities' => $activities,
        ];
    }
}

// tests/TestCase.php
abstract class TestCase extends BaseTestCase
{
    use CreatesApplication, DatabaseMigrations;

    protected static $testDataCreated = false;

    protected function setUp(): void
    {
        parent::setUp();

        if (!static::$testDataCreated) {
            // Run once per test suite
            TestDataFactory::createCompleteTestEnvironment();
            static::$testDataCreated = true;
        }
    }
}
```

---

**Task 2.2: Page Object Architecture (Playwright)**

**2.2.1: Base Page Object with Common Patterns**

```typescript
// tests/Browser/pages/BasePage.ts
export abstract class BasePage {
    constructor(protected page: Page) {}

    // Smart waiting - detects redirect vs in-place update
    async submitFormAndWait(
        submitSelector: string,
        options: {
            expectRedirect?: boolean;
            redirectPattern?: RegExp;
            successSelector?: string;
            timeout?: number;
        } = {}
    ): Promise<void> {
        const timeout = options.timeout ?? 30000;

        if (options.expectRedirect) {
            // Wait for navigation
            await Promise.all([
                this.page.waitForURL(options.redirectPattern || /\//, { timeout }),
                this.page.click(submitSelector)
            ]);

            // Wait for page to be fully loaded
            await this.page.waitForLoadState('networkidle', { timeout: 5000 });

        } else {
            // Click and wait for success indicator
            await this.page.click(submitSelector);

            if (options.successSelector) {
                await this.page.waitForSelector(options.successSelector, {
                    state: 'visible',
                    timeout
                });
            } else {
                // Wait for AJAX to complete
                await this.page.waitForLoadState('networkidle', { timeout: 5000 });
            }
        }
    }

    // Retry-able fill (handles dynamic forms)
    async fillWithRetry(selector: string, value: string, maxRetries = 3): Promise<void> {
        for (let i = 0; i < maxRetries; i++) {
            try {
                await this.page.fill(selector, value, { timeout: 5000 });
                return;
            } catch (error) {
                if (i === maxRetries - 1) throw error;
                await this.page.waitForTimeout(1000);
            }
        }
    }

    // Handle custom select (dropdowns with JS)
    async selectCustomDropdown(
        containerSelector: string,
        optionText: string
    ): Promise<void> {
        // Click to open dropdown
        await this.page.click(containerSelector);

        // Wait for options to appear
        await this.page.waitForSelector(`${containerSelector} .dropdown-option`, {
            state: 'visible'
        });

        // Click option by text
        await this.page.click(`${containerSelector} .dropdown-option:has-text("${optionText}")`);
    }

    // Verify success message appears
    async expectSuccessMessage(message?: string): Promise<void> {
        const successLocator = this.page.locator('.alert-success, .toast-success, .swal2-success');
        await expect(successLocator).toBeVisible({ timeout: 5000 });

        if (message) {
            await expect(successLocator).toContainText(message);
        }
    }

    // Verify error message appears
    async expectErrorMessage(message?: string): Promise<void> {
        const errorLocator = this.page.locator('.alert-danger, .toast-error, .swal2-error');
        await expect(errorLocator).toBeVisible({ timeout: 5000 });

        if (message) {
            await expect(errorLocator).toContainText(message);
        }
    }

    // Wait for API call to complete
    async waitForApiCall(urlPattern: string | RegExp, method = 'POST'): Promise<void> {
        await this.page.waitForResponse(
            response =>
                response.url().match(urlPattern) !== null &&
                response.request().method() === method &&
                response.status() < 400
        );
    }

    // Take screenshot on error
    async captureErrorState(testName: string): Promise<void> {
        const timestamp = new Date().toISOString().replace(/[:.]/g, '-');
        await this.page.screenshot({
            path: `test-results/errors/${testName}_${timestamp}.png`,
            fullPage: true
        });

        // Also capture HTML for debugging
        const html = await this.page.content();
        await fs.writeFile(
            `test-results/errors/${testName}_${timestamp}.html`,
            html
        );
    }
}
```

**2.2.2: Enhanced TraineePage with Robust Patterns**

```typescript
// tests/Browser/pages/TraineePage.ts
export class TraineePage extends BasePage {
    async goto(): Promise<void> {
        await this.page.goto('/trainees/create');
        await this.page.waitForLoadState('networkidle');
    }

    async fillForm(data: TraineeFormData): Promise<void> {
        // Personal Information
        await this.fillWithRetry('#first_name', data.firstName);
        await this.fillWithRetry('#last_name', data.lastName);
        await this.fillWithRetry('#date_of_birth', data.dateOfBirth);
        await this.page.selectOption('#gender', data.gender);
        await this.fillWithRetry('#email', data.email);
        await this.fillWithRetry('#ic_number', data.icNumber);
        await this.fillWithRetry('#phone', data.phone);
        await this.fillWithRetry('#address', data.address);

        // Centre - use actual select, not custom dropdown
        await this.page.selectOption('#centre_name', { label: data.centreName });

        // Service Category
        await this.page.selectOption('#trainee_condition', data.condition);

        // Guardian Information
        if (data.guardianName) {
            await this.fillWithRetry('#guardian_name', data.guardianName);
        }
        if (data.guardianPhone) {
            await this.fillWithRetry('#guardian_phone', data.guardianPhone);
        }
        if (data.guardianRelationship) {
            await this.page.selectOption('#guardian_relationship', data.guardianRelationship);
        }
        if (data.guardianEmail) {
            await this.fillWithRetry('#guardian_email', data.guardianEmail);
        }

        // Consent checkboxes
        await this.page.check('#photo_consent');
        await this.page.check('#service_consent');
        await this.page.check('#data_consent');
    }

    async submitForm(): Promise<void> {
        // Use enhanced submit method with redirect handling
        await this.submitFormAndWait('button[type="submit"]', {
            expectRedirect: true,
            redirectPattern: /\/(trainees\/home|admin\/trainees)/,
            timeout: 35000  // Allow for 26s processing + buffer
        });
    }

    async createTrainee(data: TraineeFormData): Promise<void> {
        await this.goto();
        await this.fillForm(data);
        await this.submitForm();
        await this.expectSuccessMessage('Trainee registered successfully');
    }
}
```

---

**Task 2.3: Test Data Management**

**2.3.1: Database Cleanup Strategy**

```typescript
// tests/Browser/fixtures/database.ts
import { test as base } from '@playwright/test';
import { execSync } from 'child_process';

export const test = base.extend({
    // Fresh database state for each test
    freshDatabase: async ({}, use) => {
        // Truncate tables before test
        execSync('php artisan db:wipe --env=testing');
        execSync('php artisan migrate --env=testing');
        execSync('php artisan db:seed --class=TestSeeder --env=testing');

        await use();

        // Cleanup after test (optional - migrations handle this)
    },

    // Reusable test data
    testCentres: async ({}, use) => {
        const centres = JSON.parse(
            execSync('php artisan tinker --execute="echo Centre::all()->toJson();"').toString()
        );
        await use(centres);
    },

    testTrainees: async ({}, use) => {
        const trainees = JSON.parse(
            execSync('php artisan tinker --execute="echo Trainee::limit(10)->get()->toJson();"').toString()
        );
        await use(trainees);
    }
});
```

---

### Week 6: Comprehensive Test Coverage

**Task 2.4: Unit Tests (Models, Services, Helpers)**

```php
// tests/Unit/Models/TraineeTest.php
class TraineeTest extends TestCase
{
    /** @test */
    public function it_has_full_name_accessor()
    {
        $trainee = Trainee::factory()->create([
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);

        $this->assertEquals('John Doe', $trainee->full_name);
    }

    /** @test */
    public function it_belongs_to_centre()
    {
        $centre = Centre::factory()->create();
        $trainee = Trainee::factory()->create(['centre_id' => $centre->centre_id]);

        $this->assertInstanceOf(Centre::class, $trainee->centre);
        $this->assertEquals($centre->centre_id, $trainee->centre->centre_id);
    }

    /** @test */
    public function it_has_many_activity_enrollments()
    {
        $trainee = Trainee::factory()->create();
        $activity = Activity::factory()->create();

        ActivityEnrollment::factory()->create([
            'trainee_id' => $trainee->trainee_id,
            'activity_id' => $activity->activity_id,
        ]);

        $this->assertCount(1, $trainee->enrollments);
    }

    /** @test */
    public function it_soft_deletes()
    {
        $trainee = Trainee::factory()->create();
        $traineeId = $trainee->trainee_id;

        $trainee->delete();

        $this->assertSoftDeleted('trainees', ['trainee_id' => $traineeId]);
    }

    /** @test */
    public function ic_number_is_encrypted()
    {
        $trainee = Trainee::factory()->create(['ic_number' => '990101-14-1234']);

        // Check raw database value is encrypted
        $rawValue = DB::table('trainees')
            ->where('trainee_id', $trainee->trainee_id)
            ->value('ic_number');

        $this->assertNotEquals('990101-14-1234', $rawValue);

        // Check model accessor decrypts
        $this->assertEquals('990101-14-1234', $trainee->ic_number);
    }
}

// tests/Unit/Services/NotificationServiceTest.php
class NotificationServiceTest extends TestCase
{
    protected NotificationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(NotificationService::class);
    }

    /** @test */
    public function it_creates_notification_for_user()
    {
        $user = User::factory()->create();

        $this->service->notify($user, [
            'title' => 'Test Notification',
            'message' => 'This is a test',
            'type' => 'info',
        ]);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $user->id,
            'notification_title' => 'Test Notification',
        ]);
    }

    /** @test */
    public function it_sends_email_if_channel_is_mail()
    {
        Mail::fake();

        $user = User::factory()->create();

        $this->service->notify($user, [
            'title' => 'Test Notification',
            'message' => 'This is a test',
            'type' => 'info',
            'channel' => 'mail',
        ]);

        Mail::assertSent(NotificationMail::class);
    }
}
```

---

**Task 2.5: Integration Tests (API, Workflows)**

```php
// tests/Feature/Trainee/TraineeWorkflowTest.php
class TraineeWorkflowTest extends TestCase
{
    /** @test */
    public function complete_trainee_journey()
    {
        // 1. Register trainee
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);

        $response = $this->post('/trainees/store', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'date_of_birth' => '2010-01-01',
            'gender' => 'Male',
            'email' => 'john.doe@test.com',
            'ic_number' => '100101-14-1234',
            'phone' => '+60123456789',
            'address' => '123 Test St',
            'centre_name' => 'Kuantan',
            'trainee_condition' => 'Learning Support',
            'guardian_name' => 'Jane Doe',
            'guardian_phone' => '+60198765432',
            'guardian_relationship' => 'Parent',
            'guardian_email' => 'jane.doe@test.com',
            'photo_consent' => true,
            'service_consent' => true,
            'data_consent' => true,
        ]);

        $response->assertRedirect('/trainees/home');
        $this->assertDatabaseHas('trainees', ['email' => 'john.doe@test.com']);

        $trainee = Trainee::where('email', 'john.doe@test.com')->first();

        // 2. Enroll in activity
        $activity = Activity::factory()->create(['centre_id' => $trainee->centre_id]);

        $response = $this->post("/activities/{$activity->activity_id}/enroll", [
            'trainee_id' => $trainee->trainee_id,
        ]);

        $response->assertSuccessful();
        $this->assertDatabaseHas('activity_enrollments', [
            'trainee_id' => $trainee->trainee_id,
            'activity_id' => $activity->activity_id,
        ]);

        // 3. Mark attendance
        $session = ActivitySession::factory()->create([
            'activity_id' => $activity->activity_id,
            'session_date' => today(),
        ]);

        $response = $this->post("/sessions/{$session->session_id}/attendance", [
            'attendances' => [
                $trainee->trainee_id => 'present'
            ]
        ]);

        $response->assertSuccessful();
        $this->assertDatabaseHas('session_attendances', [
            'trainee_id' => $trainee->trainee_id,
            'session_id' => $session->session_id,
            'attendance_status' => 'present',
        ]);

        // 4. Record progress
        $response = $this->post("/trainees/{$trainee->trainee_id}/progress", [
            'activity_id' => $activity->activity_id,
            'competency_level' => 2,
            'notes' => 'Good progress',
        ]);

        $response->assertSuccessful();

        // 5. Generate report
        $response = $this->get("/trainees/{$trainee->trainee_id}/report");
        $response->assertSuccessful();
        $response->assertHeader('content-type', 'application/pdf');
    }
}
```

---

### Week 7: Performance & Load Testing

**Task 2.6: Performance Profiling**

```php
// tests/Performance/TraineeCreationPerformanceTest.php
class TraineeCreationPerformanceTest extends TestCase
{
    /** @test */
    public function trainee_creation_completes_within_5_seconds()
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);

        $start = microtime(true);

        $response = $this->post('/trainees/store', [
            // ... complete trainee data
        ]);

        $end = microtime(true);
        $duration = ($end - $start) * 1000; // Convert to milliseconds

        $response->assertRedirect();

        // Assert performance target
        $this->assertLessThan(5000, $duration, "Trainee creation took {$duration}ms, expected <5000ms");

        // Log for performance tracking
        Log::channel('performance')->info('Trainee creation performance', [
            'duration_ms' => $duration,
            'queries' => count(DB::getQueryLog()),
        ]);
    }

    /** @test */
    public function identify_n_plus_one_queries()
    {
        DB::enableQueryLog();

        $admin = User::factory()->admin()->create();
        $this->actingAs($admin);

        $this->get('/trainees/home');

        $queries = DB::getQueryLog();

        // Check for duplicate queries (N+1 problem indicator)
        $queryStrings = array_map(fn($q) => $q['query'], $queries);
        $duplicates = array_filter(array_count_values($queryStrings), fn($count) => $count > 1);

        $this->assertEmpty($duplicates, 'N+1 query problem detected: ' . print_r($duplicates, true));
    }
}
```

**Task 2.7: Load Testing with k6**

```javascript
// tests/Performance/load-test.js
import http from 'k6/http';
import { check, sleep } from 'k6';
import { Rate } from 'k6/metrics';

const errorRate = new Rate('errors');

export let options = {
    stages: [
        { duration: '2m', target: 10 },  // Ramp up to 10 users
        { duration: '5m', target: 10 },  // Stay at 10 users
        { duration: '2m', target: 50 },  // Ramp up to 50 users
        { duration: '5m', target: 50 },  // Stay at 50 users
        { duration: '2m', target: 0 },   // Ramp down
    ],
    thresholds: {
        'http_req_duration': ['p(95)<3000'], // 95% of requests under 3s
        'errors': ['rate<0.1'],              // Error rate under 10%
    },
};

export default function () {
    // Login
    let loginRes = http.post('http://localhost:8000/auth/check', {
        identifier: 'admin@test.com',
        password: 'password',
    });

    check(loginRes, {
        'login successful': (r) => r.status === 200,
    }) || errorRate.add(1);

    sleep(1);

    // View dashboard
    let dashboardRes = http.get('http://localhost:8000/admin/dashboard');

    check(dashboardRes, {
        'dashboard loaded': (r) => r.status === 200,
        'response time OK': (r) => r.timings.duration < 2000,
    }) || errorRate.add(1);

    sleep(2);

    // View trainees list
    let traineeListRes = http.get('http://localhost:8000/trainees/home');

    check(traineeListRes, {
        'trainee list loaded': (r) => r.status === 200,
    }) || errorRate.add(1);

    sleep(3);
}
```

Run load test:
```bash
k6 run tests/Performance/load-test.js
```

---

**Deliverables for Weeks 5-7:**
- [ ] Test database architecture with fast seeding
- [ ] Enhanced page objects with robust patterns
- [ ] Test data management and cleanup
- [ ] Unit tests for all models and services (target: 80% coverage)
- [ ] Integration tests for complete workflows
- [ ] Performance profiling tests
- [ ] Load testing scripts and results
- [ ] Test documentation and guidelines

---

## PHASE 3: PERFORMANCE OPTIMIZATION (Week 8)

**Objective:** Achieve <5s for all operations, <2s for page loads.

### Task 3.1: Database Query Optimization

**3.1.1: Identify Slow Queries**

```bash
# Enable MySQL slow query log
mysql -u root -p
SET GLOBAL slow_query_log = 'ON';
SET GLOBAL long_query_time = 1; # Queries taking >1s
SET GLOBAL slow_query_log_file = '/var/log/mysql/slow-query.log';

# Run application, then analyze
mysqldumpslow -s t /var/log/mysql/slow-query.log
```

**3.1.2: Add Missing Indexes**

```php
// Based on slow query analysis, create migration
php artisan make:migration add_performance_indexes

Schema::table('activity_sessions', function (Blueprint $table) {
    $table->index(['activity_id', 'session_date']); // For schedule queries
    $table->index('session_status'); // For filtering
});

Schema::table('session_attendances', function (Blueprint $table) {
    $table->index(['session_id', 'attendance_status']); // For attendance reports
    $table->index('trainee_id'); // For trainee attendance history
});

Schema::table('activity_enrollments', function (Blueprint $table) {
    $table->index(['activity_id', 'enrollment_status']); // For active enrollments
    $table->index('trainee_id'); // For trainee activities
});

Schema::table('trainees', function (Blueprint $table) {
    $table->index('centre_id'); // For centre filtering
    $table->index(['centre_id', 'is_active']); // For active trainees per centre
});
```

**3.1.3: Fix N+1 Queries**

```php
// BEFORE (N+1 problem):
$activities = Activity::all();
foreach ($activities as $activity) {
    echo $activity->centre->centre_name; // Queries centre for each activity
    echo $activity->creator->name; // Queries user for each activity
}

// AFTER (Eager loading):
$activities = Activity::with(['centre', 'creator'])->get();
foreach ($activities as $activity) {
    echo $activity->centre->centre_name; // No additional query
    echo $activity->creator->name; // No additional query
}

// Apply globally in controllers:
// app/Http/Controllers/Activity/ActivityController.php
public function index()
{
    $activities = Activity::query()
        ->with(['centre', 'creator', 'instructor']) // Eager load relationships
        ->when(auth()->user()->role !== 'admin', function ($query) {
            $query->where('centre_id', auth()->user()->centre_id);
        })
        ->latest()
        ->paginate(20);

    return view('activities.index', compact('activities'));
}
```

---

**Task 3.2: Caching Strategy**

**3.2.1: Cache Dashboard Statistics**

```php
// app/Services/DashboardService.php
public function getStatistics(User $user): array
{
    $cacheKey = "dashboard_stats_{$user->id}_{$user->centre_id}";

    return Cache::remember($cacheKey, now()->addMinutes(10), function () use ($user) {
        return [
            'total_trainees' => $this->getTotalTrainees($user),
            'active_activities' => $this->getActiveActivities($user),
            'attendance_rate' => $this->getAttendanceRate($user),
            'recent_enrollments' => $this->getRecentEnrollments($user),
        ];
    });
}

// Clear cache when data changes
public function clearDashboardCache(User $user): void
{
    $cacheKey = "dashboard_stats_{$user->id}_{$user->centre_id}";
    Cache::forget($cacheKey);
}

// Call after trainee creation, activity updates, etc.
```

**3.2.2: Cache Centre and Category Lookups**

```php
// app/Models/Centre.php
public static function getCachedCentres(): Collection
{
    return Cache::remember('centres_all', now()->addHours(24), function () {
        return self::where('is_active', true)->get();
    });
}

// app/Models/Activity.php
public static function getCategories(): array
{
    return Cache::remember('activity_categories', now()->addHours(24), function () {
        return [
            'Autism Spectrum Support',
            'Physical Disabilities',
            'Learning Support',
            'Visual Impairment',
            'Hearing Impairment',
            'Speech Therapy',
        ];
    });
}
```

**3.2.3: Cache Blade Views**

```bash
# Compile views in production
php artisan view:cache

# Clear when deploying
php artisan view:clear
```

---

**Task 3.3: Async Processing (Queue)**

**3.3.1: Move Slow Operations to Queue**

```php
// Trainee creation notifications
// app/Jobs/SendTraineeWelcomeNotification.php
class SendTraineeWelcomeNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public Trainee $trainee,
        public User $guardian
    ) {}

    public function handle(): void
    {
        // Send welcome email
        Mail::to($this->trainee->email)->send(new TraineeWelcomeMail($this->trainee));

        // Create notification
        Notification::create([
            'user_id' => $this->guardian->id,
            'notification_title' => 'Trainee Registered',
            'notification_message' => "{$this->trainee->full_name} has been successfully registered.",
            'notification_type' => 'success',
        ]);

        // Log activity
        Log::info('Trainee welcome notification sent', [
            'trainee_id' => $this->trainee->trainee_id,
        ]);
    }
}

// Dispatch in controller
SendTraineeWelcomeNotification::dispatch($trainee, $guardian);
// Returns immediately, job processed in background
```

**3.3.2: PDF Generation Queue**

```php
// app/Jobs/GenerateProgressReportPdf.php
class GenerateProgressReportPdf implements ShouldQueue
{
    public function __construct(
        public Trainee $trainee,
        public string $reportType,
        public User $requestedBy
    ) {}

    public function handle(): void
    {
        $pdf = PDF::loadView('reports.progress', [
            'trainee' => $this->trainee,
            'activities' => $this->trainee->activities,
            'progress' => $this->trainee->progressRecords,
        ]);

        $filename = "progress_report_{$this->trainee->trainee_id}_" . time() . ".pdf";
        $path = storage_path("app/reports/{$filename}");

        $pdf->save($path);

        // Notify user that report is ready
        Notification::create([
            'user_id' => $this->requestedBy->id,
            'notification_title' => 'Report Ready',
            'notification_message' => "Progress report for {$this->trainee->full_name} is ready for download.",
            'notification_type' => 'info',
            'notification_data' => json_encode(['file_path' => $path]),
        ]);
    }
}
```

**3.3.3: Setup Queue Worker**

```bash
# .env
QUEUE_CONNECTION=database

# Run migration
php artisan queue:table
php artisan migrate

# Start queue worker (use supervisor in production)
php artisan queue:work --tries=3 --timeout=90

# Supervisor config for production
# /etc/supervisor/conf.d/creams-worker.conf
[program:creams-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/creams/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
numprocs=2
redirect_stderr=true
stdout_logfile=/path/to/creams/storage/logs/worker.log
```

---

**Task 3.4: Frontend Optimization**

**3.4.1: Minify Assets**

```bash
# Install Laravel Mix
npm install --save-dev laravel-mix

# webpack.mix.js
mix.js('resources/js/app.js', 'public/js')
   .css('resources/css/app.css', 'public/css')
   .version(); // Cache busting

# Build for production
npm run production
```

**3.4.2: Lazy Load Images**

```html
<!-- Use native lazy loading -->
<img src="{{ $trainee->avatar_url }}"
     alt="{{ $trainee->full_name }}"
     loading="lazy"
     width="150"
     height="150">
```

**3.4.3: Optimize Database Queries in Views**

```php
// BEFORE (in view):
@foreach($activities as $activity)
    {{ $activity->enrollments->count() }} <!-- N+1 query -->
@endforeach

// AFTER (in controller):
$activities = Activity::withCount('enrollments')->get();

// In view:
@foreach($activities as $activity)
    {{ $activity->enrollments_count }} <!-- No query -->
@endforeach
```

---

**Deliverables for Week 8:**
- [ ] All slow queries identified and indexed
- [ ] N+1 queries eliminated
- [ ] Caching implemented for dashboard and lookups
- [ ] Queue system setup for async processing
- [ ] Frontend assets minified
- [ ] Performance benchmarks met (<5s operations, <2s page loads)

---

## PHASE 4: DEPLOYMENT & MONITORING (Week 9)

### Task 4.1: Production Server Setup

**4.1.1: Server Hardening Checklist**

```bash
# 1. Update system
sudo apt update && sudo apt upgrade -y

# 2. Configure firewall
sudo ufw allow 22/tcp   # SSH
sudo ufw allow 80/tcp   # HTTP
sudo ufw allow 443/tcp  # HTTPS
sudo ufw enable

# 3. Install fail2ban (prevent brute force)
sudo apt install fail2ban -y
sudo systemctl enable fail2ban

# 4. Disable root SSH login
sudo sed -i 's/PermitRootLogin yes/PermitRootLogin no/' /etc/ssh/sshd_config
sudo systemctl restart sshd

# 5. Setup SSL/TLS (Let's Encrypt)
sudo apt install certbot python3-certbot-nginx -y
sudo certbot --nginx -d your domain.com

# 6. Configure PHP-FPM
sudo nano /etc/php/8.1/fpm/php.ini
# Set: expose_php = Off
# Set: display_errors = Off
# Set: log_errors = On

# 7. Setup log rotation
sudo nano /etc/logrotate.d/creams
```

**4.1.2: Application Deployment Script**

```bash
#!/bin/bash
# scripts/deploy.sh

set -e # Exit on error

echo "Starting deployment..."

# Pull latest code
git pull origin main

# Install dependencies
composer install --no-dev --optimize-autoloader

# Run migrations
php artisan migrate --force

# Clear and cache config
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Restart services
sudo systemctl restart php8.1-fpm
sudo systemctl restart nginx

# Restart queue workers
sudo supervisorctl restart creams-worker:*

echo "Deployment completed successfully!"
```

---

**Task 4.2: Monitoring & Alerting**

**4.2.1: Application Monitoring**

```php
// Install Laravel Telescope for production monitoring
composer require laravel/telescope

// config/telescope.php - Limit data retention
'storage' => [
    'database' => [
        'connection' => env('DB_CONNECTION', 'mysql'),
        'chunk' => 1000,
    ],
],

'watchers' => [
    Watchers\QueryWatcher::class => [
        'enabled' => true,
        'slow' => 1000, // Log queries >1s
    ],
    // ... other watchers
],

// Prune old records daily
// app/Console/Kernel.php
protected function schedule(Schedule $schedule)
{
    $schedule->command('telescope:prune --hours=48')->daily();
}
```

**4.2.2: Server Monitoring**

```bash
# Install monitoring tools
sudo apt install htop iotop nethogs -y

# Setup custom health check endpoint
# routes/web.php
Route::get('/health', function () {
    $healthy = true;
    $checks = [];

    // Check database connection
    try {
        DB::connection()->getPdo();
        $checks['database'] = 'ok';
    } catch (\Exception $e) {
        $healthy = false;
        $checks['database'] = 'failed';
    }

    // Check storage writable
    $checks['storage'] = is_writable(storage_path()) ? 'ok' : 'failed';
    if ($checks['storage'] === 'failed') $healthy = false;

    // Check queue workers
    $queueSize = DB::table('jobs')->count();
    $checks['queue'] = $queueSize < 100 ? 'ok' : 'warning';

    return response()->json([
        'status' => $healthy ? 'healthy' : 'unhealthy',
        'checks' => $checks,
        'timestamp' => now(),
    ], $healthy ? 200 : 503);
});
```

---

## TIMELINE SUMMARY

| Phase | Duration | Key Deliverables |
|-------|----------|------------------|
| Phase 0: Audit | 1 week | Complete functionality verification, baseline metrics |
| Phase 1: Security | 3 weeks | OWASP 95% compliance, defense-in-depth implementation |
| Phase 2: Testing | 3 weeks | 95% test coverage, robust test infrastructure |
| Phase 3: Performance | 1 week | <5s operations, <2s page loads |
| Phase 4: Deployment | 1 week | Production server, monitoring, CI/CD |
| **TOTAL** | **9 weeks** | **Production-grade system ready** |

---

## SUCCESS CRITERIA

### After Phase 1 (Security)
- [ ] 0 CRITICAL vulnerabilities
- [ ] 0 HIGH vulnerabilities affecting production
- [ ] 95%+ OWASP Top 10 compliance
- [ ] All authentication/authorization tested and verified
- [ ] Audit logging implemented
- [ ] Automated backups working

### After Phase 2 (Testing)
- [ ] 95%+ test pass rate
- [ ] 0 flaky tests
- [ ] All critical paths have integration tests
- [ ] Performance tests baseline established
- [ ] Test execution time <10 minutes

### After Phase 3 (Performance)
- [ ] Dashboard loads in <1s
- [ ] Trainee creation <5s
- [ ] Activity schedule <2s
- [ ] All pages <2s load time
- [ ] 0 N+1 queries
- [ ] Queue system processing <1min lag

### Production Readiness Gate
- [ ] All security critical fixes applied
- [ ] Test coverage ≥95%
- [ ] Performance targets met
- [ ] Documentation complete
- [ ] Backup and disaster recovery tested
- [ ] Monitoring and alerting configured
- [ ] Stakeholder sign-off

---

## RESOURCE REQUIREMENTS

**Team:**
- 1 Full-stack developer (Laravel + Playwright)
- 0.5 Security engineer (consulting/review)
- 0.5 DevOps engineer (deployment/monitoring)

**Tools & Services:**
- Laravel development environment
- Playwright browser testing
- MySQL database
- Redis (for caching, queuing)
- Monitoring service (optional: Sentry, New Relic)
- CI/CD pipeline (GitHub Actions or GitLab CI)

**Budget Estimate:**
- Development time: 9 weeks × $X/week
- Server infrastructure: $50-200/month
- Monitoring tools: $0-100/month
- Security scanning tools: Free (OWASP ZAP) or $X/month

---

*This is a comprehensive, evidence-based roadmap. Each phase builds on the previous, ensuring quality at every step.*
