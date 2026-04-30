# CREAMS — UAT Blockers Register
**Date**: 30 April 2026
**Sprint Day**: 2
**Branch**: Fixers
**Auditor**: Faisal Hanafi + AI

This register captures every blocker found during the Day 2 reality audit.
Severity: P0 (login/dashboard broken) | P1 (golden path broken, security issue) | P2 (degraded experience) | P3 (cosmetic/minor)

---

## Routes inventory

- **Total routes**: 629
- **GET|HEAD**: 416 | **POST**: 165 | **DELETE**: 26 | **PUT**: 22
- **Saved to**: `docs/audit/routes_2026-04-30.json`

### Key architectural finding (informational, not a blocker)

The app uses a dual-URL system:
- `/creams/{demo_id}/*` — production/staging URLs (handled by `DemoInstanceMiddleware`)
- Direct routes `/auth/login`, `/dashboard` etc. — enabled only in `local`/`testing` environment

For staging (Day 4), all URLs must include the demo_id prefix (e.g. `/creams/uat/auth/login`).
The middleware validates any alphanumeric string up to 32 chars; `uat` and `staging` are suggested IDs.

---

## CentreScope verification

- **Docs claim**: MULTI_CENTRE_ISOLATION.md (updated 2026-04-25) documents 23 + 2 = 25 models
- **Reality**: 23 models with CentreScope (Mechanism 1) + 2 with closure scope (Mechanism 2)
- **Status**: CONFIRMED ACCURATE — no discrepancy
- The old "26 of 28" claim was in CREAMS_SESSION_2026-04-16.md (historical), already superseded

**Models under Mechanism 2 (closure scope via asset relationship):**
- `AssetMaintenance` — `addGlobalScope('centre_isolation', ...)` — verified in code
- `AssetMovement` — `addGlobalScope('centre_isolation', ...)` — verified in code

---

## 4-Role golden-path baseline

### Test method
PHP artisan test with DatabaseTransactions — HTTP-level testing against real DB.
App served at http://localhost:8000 (direct routes, local env).

### Login baseline — all 4 roles

| Role | Test | Result |
|---|---|---|
| Admin | Login via email, session set correctly | PASS |
| Supervisor | Login via email, redirected to dashboard | PASS |
| Teacher | Login via email, redirected to dashboard | PASS |
| AJK | Login via email, redirected to dashboard | PASS |

Test: `AuthenticationTest` — 13 tests, 39 assertions, all green.

### Dashboard access — all 4 roles

| Role | Dashboard | Result |
|---|---|---|
| Admin | admin.dashboard | PASS |
| Supervisor | supervisor.dashboard | PASS |
| Teacher | teacher.dashboard | PASS |
| AJK | ajk.dashboard | PASS |

Test: `RoleAccessTest` — 12 tests, 12 assertions, all green.

### RBAC enforcement — confirmed working

| Role | Blocked from | Result |
|---|---|---|
| Supervisor | admin centre management | PASS (403/redirect) |
| Teacher | admin user management | PASS (403/redirect) |
| Teacher | admin centre management | PASS (403/redirect) |
| AJK | admin user management | PASS (403/redirect) |
| Unauthenticated | dashboard | PASS (redirect to login) |

### Golden-path actions — all passing

| Role | Golden path | Result |
|---|---|---|
| Admin | View trainee list, staff list, volunteer list | PASS |
| Supervisor | View trainee list, activities | PASS |
| Teacher | View trainee list, activities | PASS |
| AJK | View dashboard (limited access) | PASS |

Tests: `TraineeManagementTest`, `StaffManagementTest`, `VolunteerTest` — 22 tests, all green.

### Centre isolation — confirmed working

| Model | Isolation type | Result |
|---|---|---|
| AssetMaintenance | Closure scope (asset relationship) | PASS |
| AssetMovement | Closure scope (asset relationship) | PASS |
| Message | User-based isolation | PASS |

Tests: `AssetMaintenanceCentreIsolationTest`, `AssetMovementCentreIsolationTest`, `MessageCentreIsolationTest` — 20 tests, all green.

### Full suite after Day 2 migrations

`php artisan test` — **359 tests, 520 assertions, 0 failures** (same as Day 1 baseline).

---

## Blockers found

### P0 — None

### P1 — None

### P2 — Informational / known

| ID | Description | Severity | Action |
|---|---|---|---|
| P2-01 | 14 pending migrations at session start (including trainee_audit_logs). Resolved by running `php artisan migrate`. Would have caused runtime errors on any trainee write. | P2 | RESOLVED — migrations ran clean |
| P2-02 | Only admin user existed in DB. Supervisor, teacher, and AJK users were missing. Created 3 UAT test users with known credentials. | P2 | RESOLVED — UAT users created |
| P2-03 | Admin password was set by an unknown old seeder. Reset to `UatPass2026!` for consistency. | P2 | RESOLVED |

### P3 — Minor / deferred

| ID | Description | Severity | Action |
|---|---|---|---|
| P3-01 | PHP 8.5 deprecation warning: `ReflectionMethod::setAccessible()` in Collision package. Appears in test output but does not affect functionality. | P3 | Defer — upstream fix needed |
| P3-02 | `app/Models/Category.php` (tracked) declares `$table = 'activity_categories'` which no longer exists. Dead model. Not referenced in controllers/routes. | P3 | Defer to post-UAT cleanup |
| P3-03 | `docs/ACTIVITY MODULE IMPROVEMENT.txt` left as DEFERRED_WITH_REASON in WIP register. Not committed. | P3 | Defer |
| P3-04 | Routes contain `/{id}` prefix group (39 routes) in addition to `/{demo_id}`. Source unclear — may be legacy or test routes. Does not block UAT. | P3 | Investigate post-UAT |

---

## Day 2 gate status

| Gate criterion | Status |
|---|---|
| All 4 roles can log in | PASS |
| All 4 roles reach a dashboard | PASS |
| UAT blockers classified | PASS (P0=0, P1=0, P2=3 resolved, P3=4 deferred) |
| No unknown 500s on golden paths | PASS |
| `routes_2026-04-30.json` saved | PASS |
| CentreScope count verified | PASS (23+2=25, doc accurate) |
| Full test suite still green | PASS (359/359) |

**Day 2 gate: PASSED.**

---

## UAT test credentials (local)

| Role | Email | Password |
|---|---|---|
| Admin | lueilwitz.harry@example.com | UatPass2026! |
| Supervisor | uat.supervisor@creams.test | UatPass2026! |
| Teacher | uat.teacher@creams.test | UatPass2026! |
| AJK | uat.ajk@creams.test | UatPass2026! |

Note: These credentials are for local UAT only. The UATSeeder (Day 3) will create fresh accounts with Faker-generated emails. These manual accounts are for the local development session only.
