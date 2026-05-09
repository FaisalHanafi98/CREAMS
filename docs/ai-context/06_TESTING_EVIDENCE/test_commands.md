# CREAMS — Test Commands and Results

**Last updated**: 2026-05-08

---

## Standard test command

```bash
php artisan test
```

Run from project root. Uses PHPUnit 11. Config in `phpunit.xml`.

---

## Test results history

| Date | Passed | Failed | Notes |
|---|---|---|---|
| 2026-04-30 (sprint baseline) | 359 | 0 | Sprint Day 1 baseline |
| 2026-05-04 | 359 | 0 | After all sprint fixes |
| 2026-05-06 | 359 | 0 | After L12 upgrade |
| 2026-05-07 | 359 | 0 | After migration fixes |
| **2026-05-08** | **354** | **5** | `demo_demo_route()` typo introduced |

---

## Current failing tests (2026-05-08)

All 5 failures are the same root cause: `demo_demo_route()` undefined in `login.blade.php`.

```
Tests\Feature\Auth\AuthenticationTest::login page renders
Tests\Feature\Auth\AuthenticationTest::auth login page renders
Tests\Feature\Auth\MiddlewareTest::public routes accessible without auth
Tests\Feature\BrokenPageTest::login page loads without duplicate
Tests\Feature\CsrfProtectionTest::csrf meta tag is present in html
```

**Fix**: `resources/views/auth/login.blade.php` lines 424, 473 — change `demo_demo_route(` to `demo_route(`.

---

## Running specific test suites

```bash
# Single test class
php artisan test --filter AuthenticationTest

# Single test method
php artisan test --filter "AuthenticationTest::login page renders"

# Test suite
php artisan test --testsuite Feature
php artisan test --testsuite Unit

# Stop on first failure
php artisan test --stop-on-failure
```

---

## phpunit.xml configuration notes

- Test DB: `cream_test` (separate from dev DB `cream`)
- BCRYPT_ROUNDS: 4 (fast hashing for tests)
- SESSION_DRIVER: array (no file I/O)
- error_reporting: 24575 (E_ALL & ~E_DEPRECATED) — suppresses PHP 8.5 vendor deprecation

---

## Browser tests (Playwright)

Located at: `tests/Browser/`

```bash
# Run demo flow spec (last successful run: 2026-05-04, 8/8 beats)
npx playwright test 99-demo-flow.spec.ts

# Run full Playwright suite
npx playwright test
```

Last full Playwright run: 2026-05-04 — 181 passed, 26 failed (stale P3 specs), 3 skipped.
Last demo-flow run: 2026-05-04 — 8/8 PASS.

---

## npm build

```bash
npm run build
```

Last successful build: 2026-05-08, clean in ~1.4s.
Output: `public/build/manifest.json`, `public/build/assets/app-*.css`, `public/build/assets/app-*.js`.

---

## Known test gaps

- No tests for the new `demo_route()` helper system
- No tests verifying CentreScope isolation at the HTTP level (covered by existing RBAC tests)
- Playwright suite has 26 stale P3 specs (old test files needing rewrite)
- No unit tests for UATSeeder
- Avatar upload not tested
- PDF/letter generation not in automated tests
