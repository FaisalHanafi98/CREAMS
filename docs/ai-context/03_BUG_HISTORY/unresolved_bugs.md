# CREAMS — Unresolved Bugs

**Last updated**: 2026-06-22 (phantom table audit + stale-bug sweep)

---

## STALE BUGS (resolved in prior sessions — kept for history)

| Bug | Status | Verified |
|-----|--------|---------|
| BUG-09: `demo_demo_route()` typo in login.blade.php | RESOLVED — not in file | 2026-06-22 |
| BUG-06: ExampleTest expects 200 on GET /, gets 302 | RESOLVED — test passes 200 | 2026-06-22 |
| BUG-03: SearchController uses raw `encrypt()` | RESOLVED — uses `EncryptionHelper::generateEncryptedId()` | 2026-06-22 |
| BUG-08: GET /auth/logout does not clear session | RESOLVED — GET route uses same session-clearing handler as POST | 2026-06-22 |

---

## BUG-01 [P2]: Bootstrap 4 `data-toggle="tab"` in registration view

**Symptom**: Tab navigation broken on trainee registration form (tabs do not switch content).
**Root cause**: `resources/views/trainees/registration.blade.php` used `data-toggle="tab"` (BS4); Bootstrap 5 ignores it.
**Fix applied 2026-06-22**: Replaced all `data-toggle="tab"` → `data-bs-toggle="tab"` in `registration.blade.php`.
**Profile view**: `profile/home.blade.php` was already using `data-bs-toggle="tab"` — no change needed.
**Status**: FIXED (uncommitted)

---

## BUG-07 [P3]: Category model points at dropped table `activity_categories`

**Status**: Effectively guarded — all `Category::` calls in active controllers are inside try/catch blocks.
- `ActivityController::edit()` line 919 — try/catch with enum fallback
- `ActivityController::getFilteredTrainees()` line 2034 — inside try block; `$category` is null-checked before use
- Tech debt, deferred post-deployment.

---

## PHANTOM-01 [P2]: `classes` + `class_trainee` tables — unimplemented feature

**Symptom**: `GET /teacher/schedule` route exists but `classes` table does not — returns 302 redirect with error (gracefully degraded via exception handler).
**Root cause**: Classes feature was scaffolded (model, controller, routes) but never completed (no migration, no views).
**Current state**: Route returns 302 + error flash; exception handler catches QueryException.
**Fix needed**: Business decision — implement or remove. Schema change required (STOP condition).

---

## PHANTOM-02 [P2]: `trainee_competency_progress` table — unimplemented learning outcomes tracking

**Symptom**: Learning outcomes progress endpoints (routes 263-267 in web.php) return error JSON or redirect-with-error when progress is written/read.
**Root cause**: `trainee_competency_progress` table has no migration.
**Current state**: All active usages are inside try/catch — no unguarded crashes.
**Fix needed**: Migration to create the table. Schema change (STOP condition).

---

## DEPLOY-01 [P1]: DemoSampleUsersSeeder.php untracked — UATSeeder fails on server

**Symptom**: `php artisan db:seed --class=UATSeeder --force` fails on server.
**Fix**: Commit `database/seeders/DemoSampleUsersSeeder.php` together with updated `UATSeeder.php`.

---

## DEPLOY-02 [P1]: Nginx subdomain config not on server

**Superseded**: Previous docs referenced `creams.faisalhanafi.com` / Lightsail. Live site is `pdk-creams.org` on Hostinger.
**Status**: Possibly stale — deployment method is now manual SSH `git pull` on Hostinger, not Nginx subdomain config.

---

## DEPLOY-03 [P1]: Certbot HTTPS not configured for subdomain

**Superseded**: Same as DEPLOY-02 — current target is `pdk-creams.org` on Hostinger, which may already have HTTPS.
**Status**: Possibly stale — verify against live Hostinger config.

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

## DEBT-01 [P3]: `SessionEnrollment` references in dead-code controllers

Dead-code controllers still reference the phantom `SessionEnrollment` model:
- `Activity/EnrollmentController.php` (no routes)
- `Activity/ActivitySessionController.php` (no routes)
- `Trainee/ParentPortalController::viewProgress()` (no route — dashboard() is routed but uses User, not SE)

Not blocking any test. Cleanup deferred to dedicated dead-code removal session.

---

## DEBT-02 [P3]: 72 IC patterns in git history

**Status**: Deferred. Requires BFG Repo Cleaner post-deployment.
