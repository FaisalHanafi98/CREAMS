# CREAMS — Resolved Bugs

**Last updated**: 2026-05-07
Only bugs confirmed fixed by code evidence and test runs are listed here.

---

## BUG-R01: Volunteer page 500 error

**Symptom**: `/admin/volunteers` returned HTTP 500.
**Root cause**: Wrong route name in volunteer index view.
**Fix**: Corrected route name in `resources/views/admin/volunteers/index.blade.php`.
**Commit**: `c627ac4`
**Verification**: Page returns 200 in browser test.

---

## BUG-R02: Dashboard tab not switching (data-toggle vs data-bs-toggle)

**Symptom**: Dashboard tab pills did not switch panels (Bootstrap 5 migration issue).
**Root cause**: `data-toggle="tab"` (Bootstrap 4) vs `data-bs-toggle="tab"` (Bootstrap 5).
**Fix**: Updated `resources/views/dashboard/modern.blade.php`.
**Commit**: `c627ac4`
**Verification**: Tab switching confirmed functional in browser.

---

## BUG-R03: sw.js fetch handler crashing on chrome-extension:// URLs

**Symptom**: Service worker threw uncaught error on Chrome extension URLs.
**Root cause**: No guard for non-http(s) schemes in the fetch handler.
**Fix**: Added scheme guard in `public/sw.js`.
**Commit**: `c627ac4`
**Verification**: No more SW errors in Playwright console output.

---

## BUG-R04: Password not hashed on UATSeeder user creation

**Symptom**: Stored password was plaintext (pre-hashed string passed directly).
**Root cause**: UATSeeder passed raw string; model cast `'password' => 'hashed'` worked, but re-running seeder skipped `firstOrCreate` on already-existing users.
**Fix**: Added explicit `Hash::make()` in UATSeeder for all password fields.
**Commit**: Session at 2026-05-06 (c627ac4 context).
**Verification**: Login works after fresh seed.

---

## BUG-R05: Session re-auth infinite loop

**Symptom**: Some pages triggered repeated auth redirects in an infinite loop.
**Root cause**: `HandleSessionExpiration` middleware incorrectly triggered on already-authenticated requests.
**Fix**: `app/Http/Middleware/HandleSessionExpiration.php` patched.
**Commit**: `9a20f14`
**Verification**: Login flow no longer loops.

---

## BUG-R06: CSP blocking Bootstrap CDN sourcemap files

**Symptom**: Browser console: `cdn.jsdelivr.net` blocked by `connect-src`.
**Root cause**: Bootstrap JS generates sourcemap requests via `connect-src`, not `script-src`.
**Fix**: Added `cdn.jsdelivr.net` to `connect-src` in CSP header middleware.
**Commit**: `a3db309`
**Verification**: No more CDN connect-src errors for Bootstrap.

---

## BUG-R07: trainee_audit_logs table missing from fresh clone

**Symptom**: `TraineeService` crashed on write because `trainee_audit_logs` table didn't exist.
**Root cause**: Migration file `2026_03_14_214141_create_trainee_audit_logs_table.php` was untracked (existed on disk, never committed).
**Fix**: Committed the migration. Sprint Day 1.
**Commit**: `9306b0b`
**Verification**: `php artisan migrate` succeeds on fresh clone; `TraineeAuditLog::logAction()` no longer crashes.

---

## BUG-R08: MySQL strict-mode failures after Laravel 13 upgrade

**Symptom**: Multiple migration failures with `SQLSTATE[HY000]: General error: 1364 Field 'X' doesn't have a default value`.
**Root cause**: MySQL strict mode exposed missing default values in older migrations.
**Fix**: Migration adjustments in commit `09f0c99`.
**Commit**: `09f0c99`
**Verification**: `php artisan migrate:fresh` completes cleanly.

---

## BUG-R09: ActivityCategory model pointing at dropped table

**Symptom**: `ActivityCategory` model referenced `activity_categories` table which was dropped by migration `2025_09_28_164108`.
**Root cause**: Model existed on disk but was never committed; table was dropped but model wasn't.
**Fix**: Quarantined on `wip/abandoned-activity-category-2026-04-30` branch; deleted from `main`.
**Evidence**: Migration `2025_09_28_164108_drop_activity_categories_and_add_category_to_activities.php` confirms table dropped.
**Sprint commit**: Part of Day 1 triage.
