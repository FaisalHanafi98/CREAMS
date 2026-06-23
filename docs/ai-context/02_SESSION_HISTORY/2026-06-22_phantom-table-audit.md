# Session — 2026-06-22 — Phantom Table Audit

**Branch**: `Fixers` (continued from SE-ARCH session)
**PHPUnit baseline entering**: 389 tests, 633 assertions
**PHPUnit baseline leaving**: 392 tests, 636 assertions (0 failures)

---

## Objective

Complete phantom table audit — identify all `DB::table()` calls and Eloquent model
`$table` declarations referencing tables that do not exist in the live DB, classify
each by severity, and fix any unguarded active-route crashes.

---

## DB Table Inventory

Verified live tables via `php artisan db:show --json` (cream + cream_test schemas):

```
activities, activity_enrollments, activity_logs, activity_occurrences,
activity_schedule_templates, activity_template_applications,
asset_categories, asset_locations, asset_maintenance, asset_parents,
assets, attendance_alerts, audit_logs, centres, contact_messages,
failed_jobs, iep_activity_goals, jobs, letter_templates, letters,
message_recipients, messages, migrations, notifications, password_resets,
public_holidays, session_attendance, sessions, staff_attendances, staffs,
trainee_attendances, trainee_audit_logs, trainee_education_plans,
trainees, volunteers
```

---

## Phantom Tables Found — Complete Register

| Phantom Table | Where Referenced | Routed? | Guard | Impact |
|---|---|---|---|---|
| `classes` | `ClassModel` ($table), `ClassController::schedule()` | YES — `teacher.schedule` | Exception handler catches QueryException → redirect 302 + error flash | Gracefully degraded |
| `class_trainee` | `TeacherController` (3 methods), `ClassModel::trainees()`, `Trainee::classes()` | NO — TeacherController unrouted; relationship only called explicitly | N/A — dead code | None |
| `events` | `BaseDashboardService::getUpcomingEvents()` | YES (via dashboard) | `Schema::hasTable('events')` guard → returns `[]` | None |
| `events` | `ApiController` (wrong namespace `App\Http\Controllers`) | NO — routed class is `Api\ApiController` | N/A — dead code | None |
| `user_login_logs` | `AdminController::showUser()` line 413 | YES | Inside `try/catch` → redirects back with error | Gracefully degraded |
| `trainee_competency_progress` | `Centre/AttendanceController::getLearningProgressSummary()` | YES | Called inside `getAttendanceAnalytics()` which has `try/catch` → returns `[]` | Gracefully degraded |
| `trainee_competency_progress` | `SessionLearningOutcomeController` (5+ methods) | YES — routes 263-267 web.php | All public methods inside `try/catch` → return error JSON | Functional but degraded (JSON errors) |
| `centre_statistics` | `CentreController` (import only) | YES (import only, never called in method body) | N/A — dead import | None |
| `centre_audit_logs` | `CentreController` (import only) | YES (import only, never called in method body) | N/A — dead import | None |
| `ajks` | `AJK` model ($table) | `MultipleUserGuard` references AJK::find() | `MultipleUserGuard` driver NOT configured in auth.php — never invoked | None |
| `admins`, `teachers`, `supervisors` | `Admin`, `Teacher`, `Supervisor` models (inferred from class name) | Same as above — MultipleUserGuard not active | Same | None |

**Note on auth**: Default guard (`web`) uses `User` model → `staffs` table. `MultipleUserGuard`
is registered as `multiple-user` driver but auth.php only configures `web` driver → `MultipleUserGuard::user()`
is never called. `Auth::user()` in `AdminController` resolves through `User` → `staffs` (works).

---

## Key Finding: No Unguarded 500 Crashes

All phantom table references in active routed code are covered by:
1. Global `Handler::handleDatabaseException()` — catches `QueryException` → redirect with error (for `classes` table)
2. `try/catch` blocks in individual methods
3. `Schema::hasTable()` guards
4. Dead imports / unused code

---

## Correction to Previous Session

Previous SE-ARCH closeout doc incorrectly listed `SessionLearningOutcomeController`
as dead code with no routes. CORRECTED: it IS routed at web.php lines 263-267:
- `learning-outcomes.index`
- `learning-outcomes.store`
- `learning-outcomes.update-progress`
- `learning-outcomes.analytics`
- `learning-outcomes.available`

Its `TraineeCompetencyProgress` references are all inside try/catch — no unguarded crashes.
It also has a remaining `SessionEnrollment::` reference in `updateSessionCompletionStats()`
(private helper called from within a try/catch) — this will return an error response when
the `learning-outcomes.update-progress` route is hit, but the feature already returns errors
due to the phantom `trainee_competency_progress` table.

---

## New Tests

`tests/Feature/Staff/TeacherScheduleTest.php` (3 tests, GREEN):

| Test | Assertion |
|------|-----------|
| `test_teacher_schedule_route_does_not_500_when_classes_feature_not_implemented` | assertNotEquals(500) |
| `test_teacher_schedule_redirects_gracefully` | assertRedirect() |
| `test_teacher_schedule_shows_error_in_session_when_classes_not_implemented` | assertSessionHas('error') |

These document that the `teacher.schedule` route degrades gracefully (exception handler
catches the QueryException from `classes` table and redirects with an error flash).

---

## Remaining Concerns (STOP conditions — not fixed this session)

1. `SessionLearningOutcomeController` references `SessionEnrollment::` in private helper
   (SE-ARCH cleanup deferred — inside try/catch, not crashing, but wrong model).
2. `classes` + `class_trainee` tables — completely unimplemented feature; no migration,
   no views, no tests. Would require business decision to implement or remove route.
3. `trainee_competency_progress` table — not migrated; feature yields JSON 500 responses
   via catch blocks. Would require migration to activate.

---

## Commit status

Uncommitted. Owner OK required (PDPA project).
