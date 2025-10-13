# CREAMS Code Quality Guide: SonarScanner Standards

## Table of Contents
1. [Overview](#overview)
2. [SonarScanner Setup](#sonarscanner-setup)
3. [Quality Gates Configuration](#quality-gates-configuration)
4. [Code Quality Standards](#code-quality-standards)
5. [Common Issues & Fixes](#common-issues--fixes)
6. [Continuous Integration](#continuous-integration)
7. [Quality Metrics](#quality-metrics)

---

## Overview

This guide establishes SonarScanner-level code quality standards for the CREAMS project, ensuring maintainable, secure, and reliable code that meets industry best practices.

### Quality Goals
- **Maintainability Rating**: A (≤5% technical debt ratio)
- **Reliability Rating**: A (0 bugs in new code)
- **Security Rating**: A (0 security vulnerabilities)
- **Coverage**: ≥80% test coverage on new code
- **Duplications**: ≤3% duplicated lines

---

## SonarScanner Setup

### Prerequisites
```bash
# Install SonarScanner CLI
# Windows (via Chocolatey)
choco install sonarscanner-msbuild-net46

# macOS (via Homebrew)
brew install sonar-scanner

# Linux (Manual Installation)
wget https://binaries.sonarsource.com/Distribution/sonar-scanner-cli/sonar-scanner-cli-4.8.0.2856-linux.zip
unzip sonar-scanner-cli-4.8.0.2856-linux.zip
export PATH=$PATH:/path/to/sonar-scanner/bin
```

### Project Configuration

#### 1. Create sonar-project.properties
```properties
# Project Identification
sonar.projectKey=creams-rehabilitation-system
sonar.projectName=CREAMS Rehabilitation Management System
sonar.projectVersion=1.0

# Source Configuration
sonar.sources=app,config,database,resources,routes
sonar.exclusions=**/vendor/**,**/node_modules/**,**/storage/**,**/bootstrap/cache/**,**/public/build/**
sonar.tests=tests
sonar.test.exclusions=**/vendor/**

# Language Configuration
sonar.php.coverage.reportPaths=coverage-clover.xml
sonar.php.tests.reportPath=tests-junit.xml
sonar.javascript.lcov.reportPaths=coverage/lcov.info

# Code Analysis Configuration
sonar.php.exclusions=**/migrations/**
sonar.coverage.exclusions=**/config/**,**/database/migrations/**,**/database/seeders/**,**/tests/**

# Quality Gate Configuration
sonar.qualitygate.wait=true
```

#### 2. Environment Configuration
Create `.sonarcloud.properties` for SonarCloud:
```properties
sonar.organization=your-organization
sonar.projectKey=your-organization_creams
sonar.token=${SONAR_TOKEN}
```

### Local Setup Script
Create `scripts/setup-sonar.sh`:
```bash
#!/bin/bash

echo "Setting up SonarScanner for CREAMS..."

# Install PHP dependencies for testing
composer install --dev

# Install Node.js dependencies
npm install

# Generate Laravel application key if not exists
if [ ! -f .env ]; then
    cp .env.example .env
    php artisan key:generate
fi

# Create necessary directories
mkdir -p storage/logs
mkdir -p bootstrap/cache
mkdir -p coverage

# Install PHPUnit globally if not present
which phpunit > /dev/null || composer global require phpunit/phpunit

echo "SonarScanner setup complete!"
echo "Run 'composer run sonar' to execute code analysis"
```

---

## Quality Gates Configuration

### Default Quality Gate
```yaml
# Minimum quality standards that must be met
conditions:
  - metric: new_coverage
    op: LT
    value: 80
    
  - metric: new_duplicated_lines_density
    op: GT
    value: 3
    
  - metric: new_maintainability_rating
    op: GT
    value: 1  # A rating
    
  - metric: new_reliability_rating
    op: GT
    value: 1  # A rating
    
  - metric: new_security_rating
    op: GT
    value: 1  # A rating
    
  - metric: new_security_hotspots_reviewed
    op: LT
    value: 100
```

### Custom Quality Profile for Laravel
Create `sonar-quality-profile.xml`:
```xml
<?xml version="1.0" encoding="UTF-8"?>
<profile>
    <name>CREAMS Laravel Quality Profile</name>
    <language>php</language>
    <rules>
        <!-- Security Rules -->
        <rule>
            <repositoryKey>php</repositoryKey>
            <key>S2068</key> <!-- Hard-coded credentials -->
            <priority>BLOCKER</priority>
        </rule>
        <rule>
            <repositoryKey>php</repositoryKey>
            <key>S5146</key> <!-- OS Command injection -->
            <priority>BLOCKER</priority>
        </rule>
        
        <!-- Maintainability Rules -->
        <rule>
            <repositoryKey>php</repositoryKey>
            <key>S138</key> <!-- Functions should not have too many lines -->
            <priority>MAJOR</priority>
            <parameters>
                <parameter>
                    <key>max</key>
                    <value>50</value>
                </parameter>
            </parameters>
        </rule>
        
        <!-- Laravel-specific Rules -->
        <rule>
            <repositoryKey>php</repositoryKey>
            <key>S1192</key> <!-- String literals should not be duplicated -->
            <priority>MINOR</priority>
        </rule>
    </rules>
</profile>
```

---

## Code Quality Standards

### 1. Security Standards

#### Mandatory Security Practices
```php
// ✅ GOOD: Use Laravel's built-in security features
class UserController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8|confirmed'
        ]);
        
        $user = User::create([
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']) // Hashed password
        ]);
        
        return response()->json($user, 201);
    }
}

// ❌ BAD: Direct database queries without sanitization
class BadController extends Controller
{
    public function search($query)
    {
        // SQL Injection vulnerability
        $results = DB::select("SELECT * FROM users WHERE name LIKE '%{$query}%'");
        return $results;
    }
}
```

#### Environment Security
```php
// ✅ GOOD: Use environment variables for sensitive data
config('database.connections.mysql.password') // From .env

// ❌ BAD: Hard-coded credentials
$password = 'hardcoded_password_123';
```

### 2. Maintainability Standards

#### Function Length and Complexity
```php
// ✅ GOOD: Short, focused functions
class TraineeService
{
    public function createTrainee(array $data): Trainee
    {
        $validated = $this->validateTraineeData($data);
        $trainee = $this->saveTrainee($validated);
        $this->sendWelcomeEmail($trainee);
        
        return $trainee;
    }
    
    private function validateTraineeData(array $data): array
    {
        return Validator::make($data, [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:trainees',
            'centre_id' => 'required|exists:centres,centre_id'
        ])->validate();
    }
}

// ❌ BAD: Long function with multiple responsibilities
class BadTraineeService
{
    public function createTrainee(array $data): Trainee
    {
        // 100+ lines of mixed validation, creation, email sending, etc.
        // This violates Single Responsibility Principle
    }
}
```

#### Naming Conventions
```php
// ✅ GOOD: Clear, descriptive names
class AttendanceCalculationService
{
    public function calculateMonthlyAttendancePercentage(Trainee $trainee, Carbon $month): float
    {
        $totalSessions = $this->getTotalScheduledSessions($trainee, $month);
        $attendedSessions = $this->getAttendedSessions($trainee, $month);
        
        return $attendedSessions / $totalSessions * 100;
    }
}

// ❌ BAD: Unclear abbreviations and names
class AttCalcSvc
{
    public function calc($t, $m): float
    {
        $ts = $this->getTS($t, $m);
        $as = $this->getAS($t, $m);
        return $as / $ts * 100;
    }
}
```

### 3. Testing Standards

#### Unit Test Coverage
```php
// ✅ GOOD: Comprehensive test coverage
class TraineeServiceTest extends TestCase
{
    use RefreshDatabase;
    
    /** @test */
    public function it_creates_trainee_with_valid_data()
    {
        $centre = Centre::factory()->create();
        $data = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'centre_id' => $centre->centre_id
        ];
        
        $trainee = app(TraineeService::class)->createTrainee($data);
        
        $this->assertInstanceOf(Trainee::class, $trainee);
        $this->assertEquals('John Doe', $trainee->name);
        $this->assertDatabaseHas('trainees', ['email' => 'john@example.com']);
    }
    
    /** @test */
    public function it_throws_validation_exception_for_invalid_email()
    {
        $this->expectException(ValidationException::class);
        
        $data = [
            'name' => 'John Doe',
            'email' => 'invalid-email',
            'centre_id' => 1
        ];
        
        app(TraineeService::class)->createTrainee($data);
    }
}
```

#### Feature Test Coverage
```php
// ✅ GOOD: End-to-end feature testing
class TraineeManagementTest extends TestCase
{
    use RefreshDatabase;
    
    /** @test */
    public function authenticated_user_can_create_trainee()
    {
        $user = User::factory()->create();
        $centre = Centre::factory()->create();
        
        $response = $this->actingAs($user)->post('/api/trainees', [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'centre_id' => $centre->centre_id
        ]);
        
        $response->assertStatus(201)
                 ->assertJsonStructure(['id', 'name', 'email', 'centre_id']);
    }
}
```

---

## Common Issues & Fixes

### 1. Security Vulnerabilities

#### Issue: SQL Injection
```php
// ❌ Problem
$users = DB::select("SELECT * FROM users WHERE id = " . $request->id);

// ✅ Solution
$users = DB::select("SELECT * FROM users WHERE id = ?", [$request->id]);
// or better
$user = User::find($request->id);
```

#### Issue: Mass Assignment Vulnerability
```php
// ❌ Problem
User::create($request->all());

// ✅ Solution
User::create($request->only(['name', 'email', 'centre_id']));
// or define $fillable in model
```

#### Issue: Cross-Site Scripting (XSS)
```blade
{{-- ❌ Problem --}}
{!! $user->description !!}

{{-- ✅ Solution --}}
{{ $user->description }}
{{-- or if HTML is needed --}}
{!! Purifier::clean($user->description) !!}
```

### 2. Code Smells

#### Issue: Long Parameter Lists
```php
// ❌ Problem
public function createReport($userId, $startDate, $endDate, $includeAttendance, $includeProgress, $format, $emailRecipient)

// ✅ Solution
public function createReport(ReportRequest $request)
{
    // Use a dedicated request object or DTO
}
```

#### Issue: Duplicated Code
```php
// ❌ Problem: Repeated validation logic
class TraineeController {
    public function store(Request $request) {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:trainees'
        ]);
    }
    
    public function update(Request $request, $id) {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:trainees,email,' . $id
        ]);
    }
}

// ✅ Solution: Extract to Form Request
class StoreTraineeRequest extends FormRequest
{
    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:trainees'
        ];
    }
}

class UpdateTraineeRequest extends FormRequest
{
    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:trainees,email,' . $this->trainee->id
        ];
    }
}
```

### 3. Performance Issues

#### Issue: N+1 Query Problem
```php
// ❌ Problem
public function getTraineesWithCentres()
{
    $trainees = Trainee::all();
    foreach ($trainees as $trainee) {
        echo $trainee->centre->name; // N+1 queries
    }
}

// ✅ Solution
public function getTraineesWithCentres()
{
    $trainees = Trainee::with('centre')->get();
    foreach ($trainees as $trainee) {
        echo $trainee->centre->name; // Single query with join
    }
}
```

### 4. Code Coverage Issues

#### Configuration for PHPUnit Coverage
Update `phpunit.xml`:
```xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:noNamespaceSchemaLocation="./vendor/phpunit/phpunit/phpunit.xsd"
         bootstrap="vendor/autoload.php"
         colors="true">
    <testsuites>
        <testsuite name="Unit">
            <directory suffix="Test.php">./tests/Unit</directory>
        </testsuite>
        <testsuite name="Feature">
            <directory suffix="Test.php">./tests/Feature</directory>
        </testsuite>
    </testsuites>
    
    <coverage processUncoveredFiles="true">
        <include>
            <directory suffix=".php">./app</directory>
        </include>
        <exclude>
            <directory suffix=".php">./app/Console/Commands</directory>
            <file>./app/Http/Middleware/TrustHosts.php</file>
        </exclude>
        <report>
            <clover outputFile="coverage-clover.xml"/>
            <html outputDirectory="coverage-html"/>
        </report>
    </coverage>
    
    <logging>
        <junit outputFile="tests-junit.xml"/>
    </logging>
</phpunit>
```

---

## Continuous Integration

### GitHub Actions Configuration

Create `.github/workflows/sonarcloud.yml`:
```yaml
name: SonarCloud Analysis

on:
  push:
    branches: [ main, develop ]
  pull_request:
    branches: [ main ]

jobs:
  sonarcloud:
    runs-on: ubuntu-latest
    
    services:
      mysql:
        image: mysql:8.0
        env:
          MYSQL_ROOT_PASSWORD: password
          MYSQL_DATABASE: creams_test
        ports:
          - 3306:3306
        options: --health-cmd="mysqladmin ping" --health-interval=10s --health-timeout=5s --health-retries=3
    
    steps:
    - uses: actions/checkout@v3
      with:
        # Shallow clones should be disabled for better relevancy of analysis
        fetch-depth: 0
        
    - name: Setup PHP
      uses: shivammathur/setup-php@v2
      with:
        php-version: 8.1
        extensions: dom, curl, libxml, mbstring, zip, pcntl, pdo, sqlite, pdo_sqlite, bcmath, soap, intl, gd, exif, iconv
        coverage: xdebug
        
    - name: Setup Node.js
      uses: actions/setup-node@v3
      with:
        node-version: '18'
        cache: 'npm'
        
    - name: Install PHP Dependencies
      run: composer install --no-progress --prefer-dist --optimize-autoloader
      
    - name: Install Node Dependencies
      run: npm ci
      
    - name: Build Assets
      run: npm run production
      
    - name: Generate Application Key
      run: php artisan key:generate --env=testing
      
    - name: Copy Environment File
      run: php -r "file_exists('.env') || copy('.env.ci', '.env');"
      
    - name: Create Database
      run: php artisan migrate --env=testing --force
      
    - name: Run Tests with Coverage
      run: |
        php artisan config:cache --env=testing
        ./vendor/bin/phpunit --coverage-clover=coverage-clover.xml --log-junit=tests-junit.xml
        
    - name: Run ESLint for JavaScript
      run: npm run lint:js
      
    - name: SonarCloud Scan
      uses: SonarSource/sonarcloud-github-action@master
      env:
        GITHUB_TOKEN: ${{ secrets.GITHUB_TOKEN }}
        SONAR_TOKEN: ${{ secrets.SONAR_TOKEN }}
```

### Pre-commit Hooks

Create `.pre-commit-config.yaml`:
```yaml
repos:
  - repo: local
    hooks:
      - id: phpcs
        name: PHP CodeSniffer
        entry: ./vendor/bin/phpcs
        language: system
        types: [php]
        args: [--standard=PSR12]
        
      - id: phpstan
        name: PHPStan
        entry: ./vendor/bin/phpstan
        language: system
        types: [php]
        args: [analyse, --level=8]
        
      - id: phpunit
        name: PHPUnit Tests
        entry: ./vendor/bin/phpunit
        language: system
        types: [php]
        pass_filenames: false
```

### Composer Scripts

Add to `composer.json`:
```json
{
    "scripts": {
        "test": "phpunit",
        "test:coverage": "phpunit --coverage-clover=coverage-clover.xml",
        "analyse": "phpstan analyse",
        "format": "php-cs-fixer fix",
        "sonar": [
            "@test:coverage",
            "sonar-scanner"
        ],
        "quality": [
            "@format",
            "@analyse", 
            "@test",
            "@sonar"
        ]
    }
}
```

---

## Quality Metrics

### Key Performance Indicators

#### Security Metrics
- **Security Hotspots**: 0 unreviewed hotspots
- **Vulnerabilities**: 0 in new code
- **Security Rating**: A (no security issues)

#### Maintainability Metrics
- **Technical Debt Ratio**: ≤5%
- **Code Smells**: ≤10 per 1000 lines
- **Maintainability Rating**: A
- **Cyclomatic Complexity**: ≤15 per function

#### Reliability Metrics
- **Bugs**: 0 in new code
- **Reliability Rating**: A
- **Test Coverage**: ≥80% line coverage
- **Test Success Rate**: 100%

#### Quality Tracking Dashboard

Create `docs/quality-metrics.md`:
```markdown
# CREAMS Quality Metrics Dashboard

## Current Metrics (Updated: {{date}})

| Metric | Current | Target | Status |
|--------|---------|--------|--------|
| Coverage | 85% | ≥80% | ✅ Pass |
| Technical Debt | 3.2% | ≤5% | ✅ Pass |
| Security Rating | A | A | ✅ Pass |
| Maintainability | A | A | ✅ Pass |
| Reliability | A | A | ✅ Pass |
| Duplications | 2.1% | ≤3% | ✅ Pass |

## Trend Analysis
- Coverage improved by 5% this month
- Security hotspots reduced from 3 to 0
- Code smells decreased by 12%

## Action Items
- [ ] Increase test coverage in AuthController
- [ ] Refactor UserService::createUser() method
- [ ] Add integration tests for API endpoints
```

### Quality Gate Enforcement

Create `sonar-quality-gate.sh`:
```bash
#!/bin/bash

# Get quality gate status from SonarCloud
QUALITY_GATE_STATUS=$(curl -s -u "${SONAR_TOKEN}:" \
  "https://sonarcloud.io/api/qualitygates/project_status?projectKey=${SONAR_PROJECT_KEY}" \
  | jq -r '.projectStatus.status')

echo "Quality Gate Status: $QUALITY_GATE_STATUS"

if [ "$QUALITY_GATE_STATUS" != "OK" ]; then
    echo "❌ Quality gate failed! Check SonarCloud for details."
    echo "🔗 Project URL: https://sonarcloud.io/dashboard?id=${SONAR_PROJECT_KEY}"
    exit 1
else
    echo "✅ Quality gate passed!"
    exit 0
fi
```

---

## Best Practices Summary

### Daily Development
1. **Write tests first** (TDD approach)
2. **Run quality checks** before committing
3. **Review SonarCloud feedback** on pull requests
4. **Fix critical/blocker issues** immediately

### Code Review Process
1. **Automated quality checks** must pass
2. **Manual code review** by senior developer
3. **Security review** for authentication/authorization changes
4. **Performance review** for database-related changes

### Release Process
1. **All quality gates** must pass
2. **Coverage threshold** must be maintained
3. **Security scan** must show no vulnerabilities
4. **Performance benchmarks** must be met

### Monitoring and Alerting
1. **Daily quality reports** via email/Slack
2. **Quality gate failures** trigger immediate alerts
3. **Coverage drops** below threshold alert team
4. **Security issues** trigger security team notification

---

## Tools and Resources

### Required Tools
- **SonarCloud/SonarQube**: Code quality analysis
- **PHPStan**: Static analysis for PHP
- **PHP_CodeSniffer**: Code style checking
- **PHPUnit**: Unit and integration testing
- **ESLint**: JavaScript code quality

### Recommended IDE Plugins
- **SonarLint**: Real-time code quality feedback
- **PHP Intelephense**: Advanced PHP support
- **GitLens**: Git integration and blame information
- **Better Comments**: Enhanced code commenting

### Additional Resources
- [Laravel Testing Documentation](https://laravel.com/docs/testing)
- [SonarCloud Documentation](https://sonarcloud.io/documentation/)
- [PHP The Right Way](https://phptherightway.com/)
- [Clean Code PHP](https://github.com/jupeter/clean-code-php)

---

**Remember**: Code quality is not just about passing automated checks—it's about writing maintainable, secure, and efficient code that serves users well and makes fellow developers' lives easier.