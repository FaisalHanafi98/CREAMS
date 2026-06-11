# CREAMS — Session 1: Security Close-Out

> **Paste into**: A fresh Claude Code session at `C:\Users\User1\Desktop\Career\Work\Development\CREAMS`
> **Model**: Opus
> **Estimated duration**: 2-3 hours
> **Pre-requisite**: None (this is Priority 1 across all projects)

---

## CONTEXT — WHAT YOU NEED TO KNOW

You are a dev session agent working on **CREAMS** — a Laravel 10 Community-Based Rehabilitation Management System for Malaysian PPDK centres. This is a **production system** managing real trainee data. It won a Gold Medal FYP at IIUM Computer Science.

### Tech Stack
- Laravel 10, PHP 8.1+, MySQL 8.0, Blade/Tailwind/Alpine.js, Vite
- 6-Role RBAC: Admin, Manager (Supervisor), Staff (Teacher), Caretaker, Trainee, Parent (AJK)
- 58 Eloquent models, 231 routes, 35+ migrations
- PHPUnit + Playwright testing

### Current Test State
- **329 tests, 864 assertions, 0 failures** (as of 2026-03-24 delta re-eval)
- Authentication tests: 19 methods, all passing, endpoint is `POST /auth/check`
- Centre isolation tests: CentreScopeTest (7), CentreIsolationTest (6), RoleAccessTest (12), SoftDeleteTest (5)

### What Has Already Been Done (DO NOT REDO)
A full delta re-eval was completed across 5 waves:
- Wave 0: Fixed 19 broken tests, blanked real DB password from .env.testing, coverage tracking
- Wave 1: XSS fixes (`e()` wrapping), PII leak fix, CentreScope on 3 core models, debug routes deleted, password min:12, hardcoded ICs removed
- Wave 2: Removed continue-on-error on lint, added composer audit, pre-commit secrets hook
- Wave 3: Updated SECURITY_AUDIT_REPORT.md, PRODUCTION_READINESS_ROADMAP.md, MODULE_FUNCTIONALITY_INVENTORY.md
- Wave 4: +23 new tests for centre scope, isolation, roles, soft delete

**DO NOT re-run the delta re-eval. DO NOT re-audit things that were already fixed. This session is execution-only.**

---

## YOUR MISSION — 6 TASKS IN ORDER

### Task 1: CentreScope Coverage Audit (HIGHEST PRIORITY)

**This is the single highest-risk item across ALL projects.**

CentreScope GlobalScope is currently applied to only **4 models**: `Trainee`, `Activity`, `Asset`, `User`.

There are **91 instances of `centre_id`** across migrations, meaning many more models likely need scoping.

**What to do:**

1. Read `app/Models/Scopes/CentreScope.php` to understand the current implementation
2. Grep ALL models in `app/Models/` for `centre_id` — either as a fillable field, relationship, or column reference
3. Grep ALL migrations in `database/migrations/` for `centre_id` to find every table that has this column
4. Cross-reference: For every model/table with `centre_id` that does NOT have CentreScope applied, assess whether it **should** have it

**Classification rules:**
- **MUST scope**: Any model that contains trainee-linked data, staff-linked data, or centre-specific operational data. If a teacher at Centre A can query this model and see Centre B's records, it's a PDPA violation.
- **SHOULD scope**: Models that are centre-specific but lower sensitivity (e.g., asset categories)
- **SKIP**: Models that are truly global (e.g., lookup tables, system config, the Centre model itself)

5. For every model classified as MUST scope, add the CentreScope boot trait:
```php
protected static function booted(): void
{
    static::addGlobalScope(new CentreScope);
}
```

6. Write a test for EACH newly scoped model verifying centre isolation. Pattern:
```php
// Create data for Centre A and Centre B
// Login as Centre A user
// Assert only Centre A data is returned
// Assert Centre B data is NOT visible
```

**CRITICAL**: Do NOT remove CentreScope from models that already have it. Do NOT modify the CentreScope class itself unless you find a bug.

**Output**: List every model with centre_id, its scoping status (already scoped / newly scoped / skip with reason), and the tests written.

---

### Task 2: Verify Session Regeneration on Login

**File**: `app/Http/Controllers/MainController.php` — look for the `check()` method (handles `POST /auth/check`)

**Verify**: After successful authentication, does the code call `session()->regenerate()` or `$request->session()->regenerate()`?

- If YES: Document it and move on.
- If NO: Add `$request->session()->regenerate()` immediately after setting session data and before the redirect. This prevents session fixation attacks.

**Write a test** that verifies the session ID changes after login (compare session ID before and after `POST /auth/check`).

---

### Task 3: Verify APP_DEBUG Configuration

Check these files:
- `.env` — should have `APP_DEBUG=false` for production
- `.env.example` — should have `APP_DEBUG=false` as the default
- `.env.testing` — can be `true` (testing only)

If `.env` has `APP_DEBUG=true`, change it to `false`. If `.env.example` has `APP_DEBUG=true`, change it to `false`.

Also verify in `config/app.php` that debug reads from env: `'debug' => (bool) env('APP_DEBUG', false)` — the default should be `false`, not `true`.

---

### Task 4: Verify Rate Limiting on Auth Route

**Check**: `routes/web.php` — is the `POST /auth/check` route wrapped in rate limiting middleware?

Laravel rate limiting options:
- `throttle:5,1` (5 attempts per minute) — applied via route middleware
- Custom RateLimiter in `app/Providers/RouteServiceProvider.php`

**If not rate-limited:**
1. Add `throttle:5,1` middleware to the auth route
2. Write a test that makes 6 rapid login attempts and verifies the 6th is rejected with 429

**If already rate-limited:** Verify the test exists. If not, write it.

---

### Task 5: Verify AuthenticationTest Tests Real Flow

**File**: `tests/Feature/Auth/AuthenticationTest.php`

The auth test was previously broken (wrong endpoint, wrong fields). It was fixed during Wave 0. Verify:

1. Tests hit `POST /auth/check` (not `/login` or another endpoint)
2. Request sends `identifier` and `password` fields (not `email`/`password`)
3. Tests check session data is set correctly after login (`id`, `iium_id`, `name`, `role`, `email`, `centre_id`, `logged_in`)
4. Tests verify inactive user rejection
5. Tests verify invalid credentials rejection

**If any of these are wrong**, fix them. If all correct, move on.

---

### Task 6: Full Test Suite Run

Run the complete test suite:
```bash
php artisan test --parallel
```
or
```bash
vendor/bin/phpunit
```

**Expected**: 329+ tests (you may have added more in Tasks 1-4), 0 failures.

**If any test fails**: Fix it before ending the session. Do NOT leave failing tests.

**Report the final count**: X tests, Y assertions, 0 failures.

---

## RULES

1. Read the actual code before making changes. Do NOT assume based on file names or documentation.
2. Follow PSR-12 coding standards.
3. Use Faker for all test data. NEVER use real trainee names, ICs, or centre names.
4. Every change must have a corresponding test.
5. Do NOT refactor existing code that works. This is a security close-out, not a refactoring session.
6. Do NOT add features.
7. Do NOT change the auth system from middleware-based to Laravel Policies.
8. Do NOT modify CI/CD pipelines in this session.
9. Commit after each task with a descriptive message following the pattern: `fix(security): description` or `test(security): description`.

---

## DEFINITION OF DONE

- [ ] Every model with `centre_id` has been audited and scoped (or explicitly skipped with documented reason)
- [ ] Session regeneration verified or implemented
- [ ] APP_DEBUG=false in .env and .env.example
- [ ] Rate limiting on auth route verified or implemented
- [ ] AuthenticationTest verified as testing real flow
- [ ] Full test suite passes: 329+ tests, 0 failures
- [ ] All new scoped models have centre isolation tests

---

## KEY FILES

| File | Purpose |
|------|---------|
| `app/Models/Scopes/CentreScope.php` | GlobalScope implementation |
| `app/Models/*.php` | All 58 Eloquent models |
| `database/migrations/*.php` | All migrations (check for centre_id) |
| `app/Http/Controllers/MainController.php` | Auth handler (check() method) |
| `routes/web.php` | Route definitions (check rate limiting) |
| `tests/Feature/Auth/AuthenticationTest.php` | Auth test suite |
| `tests/Feature/RBAC/CentreScopeTest.php` | Existing centre scope tests |
| `tests/Feature/RBAC/CentreIsolationTest.php` | Existing isolation tests |
| `.env` / `.env.example` / `.env.testing` | Environment config |
| `config/app.php` | App debug config |
| `CLAUDE.md` | Project governance (read first) |
