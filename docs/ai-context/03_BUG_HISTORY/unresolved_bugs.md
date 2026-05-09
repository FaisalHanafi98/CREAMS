# CREAMS — Unresolved Bugs

**Last updated**: 2026-05-08

---

## BUG-09 [P0 — CRITICAL]: demo_demo_route() typo in login.blade.php

**Symptom**: 5 test failures. Login page returns HTTP 500 in tests.
**Root cause**: `resources/views/auth/login.blade.php` lines 424 and 473 call `demo_demo_route()`. This function does NOT exist. Correct helper is `demo_route()`.
**Evidence**: `php artisan test --filter AuthenticationTest` → `Error: Call to undefined function demo_demo_route()`
**Fix**: In `resources/views/auth/login.blade.php`, change both occurrences: `demo_demo_route(` → `demo_route(`
**Acceptance criteria**: `php artisan test` → 359 passed, 0 failed.

---

## DEPLOY-01 [P1]: DemoSampleUsersSeeder.php untracked — UATSeeder fails on server

**Symptom**: `php artisan db:seed --class=UATSeeder --force` fails on server.
**Root cause**: Updated UATSeeder calls `$this->call(DemoSampleUsersSeeder::class)` but `DemoSampleUsersSeeder.php` is untracked locally and therefore absent on server after `git pull`.
**Fix**: Commit `database/seeders/DemoSampleUsersSeeder.php` together with updated `UATSeeder.php`.
**Acceptance criteria**: Seeder completes on server without class-not-found error.

---

## DEPLOY-02 [P1]: Nginx subdomain config not on server

**Symptom**: `https://creams.faisalhanafi.com` not serving CREAMS.
**Root cause**: Nginx config file for subdomain never created on server.
**Files**: `/etc/nginx/conf.d/creams.faisalhanafi.com.conf` (does not yet exist on server).
**Critical constraint**: Do NOT touch `/etc/nginx/conf.d/faisalhanafi.conf` — Portfolio config.
**Fix**: Create subdomain config using PHP-FPM socket `/run/php-fpm/www.sock`.
**Acceptance criteria**: HTTP 200 on `https://creams.faisalhanafi.com/auth/login`.

---

## DEPLOY-03 [P1]: Certbot HTTPS not configured for subdomain

**Symptom**: No SSL certificate for `creams.faisalhanafi.com`.
**Fix**: `sudo certbot --nginx -d creams.faisalhanafi.com` after Nginx HTTP config confirmed working.
**Acceptance criteria**: `https://creams.faisalhanafi.com` serves with valid SSL.

---

## BUG-01 [P2]: Profile tabs stuck — Bootstrap 4 data-toggle still in views

**Symptom**: Profile page tabs do not switch content.
**Evidence**: `data-toggle="tab"` (Bootstrap 4) in profile views; Bootstrap 5 ignores it.
**Files**: `resources/views/profile/home.blade.php` (search data-toggle="tab")
**Fix**: Replace `data-toggle="tab"` with `data-bs-toggle="tab"`.

---

## BUG-03 [P2]: Search expired-link — raw encrypt() vs EncryptionHelper

**Symptom**: Search result links expire or generate invalid token errors.
**Files**: `app/Http/Controllers/SearchController.php`
**Fix**: Replace `encrypt()` with `EncryptionHelper::encrypt()`.

---

## BUG-08 [P2]: GET /auth/logout does not clear session

**Symptom**: Navigating to `GET /auth/logout` does not log out. POST (button) works.
**Fix**: Ensure GET logout properly invalidates session or remove the GET route.
**Do not repeat**: Always use the Logout button during demos.

---

## BUG-02 [P3]: Profile form min-height constraint

**Fix**: Remove `min-height: 600px` from `.tab-content` in profile view.

---

## BUG-04 [P3]: Avatar camera icon jQuery timing

**Fix**: Ensure jQuery loads before avatar JS; remove CDN dependency.

---

## BUG-05 [P3]: Dashboard today schedule empty after seed

**Note**: May be fixed by new UATSeeder rewrite — UNVERIFIED (needs re-test after seed).

---

## BUG-06 [P3]: ExampleTest expects 200 on GET /, gets 302

**Root cause**: Test assertion wrong. Redirect is correct behavior.
**Fix**: Change `assertStatus(200)` to `assertRedirect()` in `tests/Feature/ExampleTest.php`.

---

## BUG-07 [P3]: Category.php model points at dropped table activity_categories

**Fix**: Delete `app/Models/Category.php` or repurpose. Tech debt, deferred post-deployment.

---

## DEBT-02 [P3]: 72 IC patterns in git history

**Status**: Deferred. Requires BFG Repo Cleaner post-deployment.
