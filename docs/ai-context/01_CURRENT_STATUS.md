# CREAMS — Current Status

**Last updated**: 2026-06-23 (RC defect remediation — Block 1 P0 fixes complete)
**Evidence basis**: git log, git status, php artisan test (local), session history 2026-06-22

---

## Current goal

Branch `Fixers` is the active development branch. Autonomous hardening in
progress — A-01 through A-03 complete, model cleanup complete, SE-ARCH complete,
phantom table audit complete.
Deployment remains on hold pending owner decision.

---

## RC defect remediation (2026-06-23) — IN PROGRESS

A second independent integrity audit found an exception-handler blind spot (the global
`Handler` registers renderables only for QueryException + a few others; it does NOT catch
missing-view `InvalidArgumentException` or missing-method `Error`/`ReflectionException`).
This exposed live HTTP 500s that the prior audit's "no hard 500" conclusion missed. Of the
5 candidate findings, code verification showed **3 are real hard 500s** (NF-01/04/05); NF-02
and NF-03 are missing-view defects but degrade to a graceful 302 via local try/catch.

Remediating under the fix-verify protocol: per-finding owner approval → before/after Playwright
evidence → PHPUnit 392/0 regression gate → log. Nothing committed (owner approval pending).

**Block 1 — P0 live HTTP 500s (deployment blockers): COMPLETE (3/3)**

| Finding | Route | Fix | Result |
|---|---|---|---|
| NF-01 | `/dashboard/modern-new` | `DashboardController@modernNew` catch now `redirect()->route('dashboard')` (the `dashboard.modernnew` view never existed; catch had re-rendered it → re-throw → 500) | 500→302 |
| NF-04 | `/attendance/report` | Added `Activity\AttendanceController@report()` returning the existing static `attendance.report` view (route had no method; 3 blades link to `route('attendance.report')`, so removing it was unsafe) | 500→200 |
| NF-05 | `/admin/centres/{id}/assets` | `web.php:813` repointed from commented-out `assets` to live `assetParents` (identical to working sibling route `web.php:511`) | 500→200 |

Files changed: `app/Http/Controllers/Dashboard/DashboardController.php`,
`app/Http/Controllers/Activity/AttendanceController.php`, `routes/web.php`.
Evidence: `docs/audit/fix-logs/NF-01/`, `NF-04/`, `NF-05/` (01-before.png, 02-after.png, fix.log each).

**Remaining:**
- Block 2 — NF-02 (`centre.attendance.analytics` missing view, currently graceful 302),
  NF-03 (`traineeprofile.download` → `loadView('trainees.pdf-profile')` hyphen mismatch with
  `pdfprofile.blade.php`, currently graceful 302).
- Block 3 — MD-03 fillable/casts drift across 11 real-table models (start Trainee, Centre, Letter).
- Block 4 — test hardening: NF-08 (2 false-confidence tests), NF-07 (regression tests for the 3
  P0 fixes), NF-09 (learning-outcomes smoke test).

---

## Current branch and commits

| Field | Value |
|---|---|
| Branch | `Fixers` |
| HEAD | `99f134c` — Security(Data): Untrack real PDK files and harden PDPA guards |
| Working tree | **Dirty** — A-01/A-02/A-03 + model cleanup uncommitted (needs owner OK) |
| Pushed to origin | Last push was `d49e8bb` (2026-06-14) |

---

## Current test state

### PHPUnit (EXECUTED — 2026-06-22)

| Metric | Value |
|---|---|
| Passed | **392** |
| Failed | **0** |
| Assertions | 636 |

Command: `php -d memory_limit=512M vendor/bin/phpunit --no-coverage`

Note: `php artisan test` OOMs at 128 MB default; always use the phpunit binary with 512M.

### Playwright (UNKNOWN — last run 2026-06-14)

| Metric | Value |
|---|---|
| Passed | **215** (last known) |
| Skipped | **3** |
| Failed | **0** |

Not re-run this session. Application source changes in this session may affect browser tests — re-run before readiness claim.

---

## Resolved defects (autonomous hardening 2026-06-21 to 2026-06-22)

| ID | Description | Files changed | Result |
|---|---|---|---|
| A-01 | Activity/AttendanceController::store() HTTP 500 — SessionEnrollment write to missing table | `Activity/AttendanceController.php` | GREEN (EXECUTED) |
| A-02 | ActivityController::enrollTrainees() silent data loss — phantom columns on INSERT | `ActivityController.php` | GREEN (EXECUTED) |
| A-03 | Centre/AttendanceController::storeActivityAttendance() — 4 simultaneous failures | `Centre/AttendanceController.php` | GREEN (EXECUTED) |
| MC-01 | ActivityEnrollment phantom $fillable and $casts | `ActivityEnrollment.php` | Cleaned (384/0) |
| MC-02 | ActivitySession $casts has attendance_marked (no column) | `ActivitySession.php` | Cleaned (384/0) |
| MC-03 | SessionAttendance $fillable and $casts wrong (attended/recorded_by vs attendance_status/marked_by) | `SessionAttendance.php` | Cleaned (384/0) |
| MC-04 | Trainee 3× phantom `->where('status','active')` on ActivityEnrollment | `Trainee.php` | Cleaned (384/0) |
| MC-05 | AdminController counts from missing session_enrollments table | `AdminController.php` | Fixed (384/0) |
| SE-ARCH | SessionEnrollment → ActivityEnrollment migration across 6+ files | Multiple | Fixed (389/0) |
| PHANTOM-AUDIT | Phantom table scan across all DB::table() calls and model $table declarations | None (doc + tests) | Verified (392/0) |

Previous resolved:
| PW-001 | Activity wizard CRUD fixes | `tests/Browser/` | 19/19 ✓ (2026-06-14) |
| PW-002 | Trainee CRUD BS5 fixes | `tests/Browser/` | 17/17 ✓ (2026-06-14) |
| PW-003 | Asset route fix | `tests/Browser/` | 9/9 ✓ (2026-06-14) |

---

## Deployment target (VERIFIED)

| Field | Value |
|---|---|
| Host | Hostinger shared hosting |
| Live URL | `https://pdk-creams.org` |
| Deploy method | Manual SSH `git pull` triggered by `.github/workflows/deploy.yml` |
| **Status** | **ON HOLD** — hard rule set by owner |

> The previous status doc (2026-05-08) described an AWS Lightsail target
> (`54.169.32.54`, `creams.faisalhanafi.com`). That target is superseded.
> The live site is `pdk-creams.org` on Hostinger. Do not reference Lightsail.

---

## Current blockers

0. **SE-ARCH — RESOLVED (2026-06-22)**
   `sessionEnrollments()` and `traineeAttendances()` added to `ActivitySession`.
   All `SessionEnrollment::` static calls replaced with `ActivityEnrollment` across
   6+ files. Dead-code controllers (EnrollmentController, ActivitySessionController,
   ParentPortalController::viewProgress) still have no routes — left as-is.
   NOTE: SessionLearningOutcomeController IS routed (web.php 263-267); its
   TraineeCompetencyProgress references are all inside try/catch — no unguarded crashes.

1. **Owner decision required — deployment hold**
   The CREAMS CLAUDE.md contains a hard rule: "Do not deploy. Deployment is on
   hold pending reality audit." This must be explicitly lifted by the owner
   before any push to production is attempted.

2. **PDPA — real-data artefacts removed from tracking (2026-06-21)**
   *Correction to the 2026-06-15 entry, which was a false positive.* The emails in
   `Archive/Historical_Screenshots/audit_screenshots/*.json`
   (`lakshmi.krishnan@`, `ahmad.hassan@`, `supervisor.gombak@`, `fatimah.abdullah@`)
   are **synthetic test personas** defined in `database/seeders/TestingGuideDataSeeder.php`
   with demo passwords — not real data. Owner confirmed: the **only** real-life data
   lives in the IRL files; everything else is synthetic or semi-synthetic.

   The genuine exposure was `Archive/Legacy_Exports/IRL_Files/` (49 real PDK centre
   photos, invoices, forms — 64 MB) tracked **only on `Fixers`** (never on `main`/`dev`),
   plus `database/real_data_backup.json` (synthetic DB dump with bcrypt hashes).
   **Remediated this session**: both `git rm --cached` (local copies kept), added to
   `.gitignore`, `GombakDataExtractor` env-gated to local-only, pre-commit hook extended
   to block these paths.

   **Remaining owner-only step**: the 64 MB of IRL files still exist in `Fixers` git
   *history* (and on `origin/Fixers`, a public repo). Full purge needs `git filter-repo`
   + force-push (high-risk, rewrites history). `main`/`dev` are clean, so a future
   `main` merge after this commit will not carry the files.

3. **Stale deployment documentation**
   Several docs under `docs/` still describe the old Lightsail architecture,
   demo-route system, and stale credentials. A full reconciliation pass is
   needed before deployment.

---

## Working tree state

**Dirty.** A-01 through A-03, model cleanup (MC-01 to MC-05), and new tests are
all uncommitted. Owner OK required before committing CREAMS (PDPA project).

Uncommitted changed files:
- `app/Http/Controllers/Activity/AttendanceController.php` (A-01, SE-ARCH)
- `resources/views/attendance/trainee.blade.php` (D-08 — prior session)
- `routes/web.php` (D-08 — prior session)
- `app/Http/Controllers/Centre/AttendanceController.php` (A-03, SE-ARCH)
- `app/Http/Controllers/AdminController.php` (MC-05)
- `app/Models/ActivityEnrollment.php` (MC-01)
- `app/Models/ActivitySession.php` (MC-02, SE-ARCH: +sessionEnrollments, +traineeAttendances)
- `app/Models/SessionAttendance.php` (MC-03)
- `app/Models/Trainee.php` (MC-04, SE-ARCH: sessionEnrollments→ActivityEnrollment)
- `app/Http/Controllers/Activity/ActivityController.php` (A-02, SE-ARCH)
- `app/Http/Controllers/Activity/SessionTemplateController.php` (SE-ARCH)
- `app/Http/Middleware/ValidateRouteParameters.php` (SE-ARCH: session before activit)
- `app/Services/Dashboard/TeacherDashboardService.php` (SE-ARCH)
- `resources/views/attendance/formmodal.blade.php` (SE-ARCH: route name fix)

New untracked:
- `tests/Feature/Attendance/AttendanceStoreTest.php` (A-01)
- `tests/Feature/Activity/EnrollmentTest.php` (A-02)
- `tests/Feature/Centre/CentreAttendanceStoreTest.php` (A-03)
- `tests/Feature/Activity/SessionEnrollmentMigrationTest.php` (SE-ARCH)
- `tests/Feature/Staff/TeacherScheduleTest.php` (PHANTOM-AUDIT: 3 tests — schedule route graceful degradation)
- `tests/Feature/Attendance/TraineeAttendanceViewTest.php` (D-08 — prior session)
- `docs/ai-context/06_TESTING_EVIDENCE/STRICT_UAT_PROTOCOL.md`
- `docs/ai-context/06_TESTING_EVIDENCE/uat_2026-06-21/`

---

## Do not repeat

- Do NOT flag `@iium.edu.my` emails as real PII without checking the seeders first —
  the audit-JSON emails are synthetic personas from `TestingGuideDataSeeder.php`. The
  2026-06-15 session wasted effort treating them as a real breach. Owner confirmed: the
  ONLY real data is in the IRL files.
- Do NOT re-commit `Archive/Legacy_Exports/IRL_Files/` or `database/real_data_backup.json` —
  now gitignored; the pre-commit hook blocks them by path.
- Do NOT deploy — hard rule is in effect until owner explicitly lifts it.
- Do NOT reference Lightsail or `creams.faisalhanafi.com` — that target is superseded.
- Do NOT run `migrate:fresh` on production.
- Do NOT use `GET /auth/logout` — use the Logout button (POST).
- Do NOT assume Breeze/Sanctum — auth is custom via `POST /auth/check`.
- Do NOT use `execSync` with multi-line PHP on Windows — use `spawnSync` with args array.
- Do NOT capitalise difficulty_level to 'Beginner' — server validates lowercase `in:beginner`.
- Do NOT inject `learning_outcomes` as a nested array — server expects `nullable|string`.
- Do NOT add Co-Authored-By AI trailers — use `[Assisted by AI, reviewed manually by Faisal]` footer instead.
