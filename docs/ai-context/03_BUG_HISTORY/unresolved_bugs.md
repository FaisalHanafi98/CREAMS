# CREAMS — Unresolved Bugs

**Last updated**: 2026-06-24 (AssetMovement alignment — task_d9a381b5 partially resolved)

---

## task_d9a381b5 [RESOLVED — 2026-06-24]: AssetMovement schema-model alignment

**Scope resolved**: `AssetMovement` model rewritten to match `asset_movements` DB schema (commit `07c5320`).
- `$fillable` corrected: removed phantom fields (`type, from_user, to_user, from_location, to_location, performed_by`); real fields used (`asset_id, from_location_id, to_location_id, moved_by_user_id, movement_date, reason, notes`).
- `AssetController::destroy()` — fixed disposal log (was throwing QueryException on every asset delete).
- `AssetManagementService::recordMovement()` — fixed column names (`moved_by_user_id`, `reason`).
- `AssetRepositoryService::transferAssetBetweenCentres()` — removed phantom columns; fixed write path.
- `AssetRepositoryService::getAssetUtilizationReport()` — removed phantom `movement_type` filters.

**Deferred (read-only risk only)**: `AssetLocation`, `AssetParent`, `AssetEnhanced` — these models have phantom or unverified columns but none have active write paths through any live controller. Deferred to next session.

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

## PHANTOM-01 [P2]: `classes` + `class_trainee` tables — unimplemented feature — RESOLVED

**Symptom**: `GET /teacher/schedule` route existed but `classes` table did not — returned 302 redirect with error.
**Root cause**: Classes feature was scaffolded (model, controller, routes) but never completed (no migration, no views).
**Resolution**: Removed scaffold end-to-end in commit `9d23877` (routes, controllers, model, relationships, tests, dead view refs).
**Current state**: Route no longer exists; no remaining `ClassModel` / `ClassController` / `TeacherController` / `teacher.schedule` references.
**Verified**: PHPUnit 392/0.

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
