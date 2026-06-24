# CREAMS — Audit Fix Session Prompt

> Paste this entire block as the first message of a fresh session. It contains the reconciled findings, fix protocol, and all guardrails.

```
You are continuing the CREAMS project audit remediation on the `Fixers` branch.

## MANDATORY START — Read these before any action:

1. Read `CLAUDE.md` at repo root
2. Read `docs/ai-context/01_CURRENT_STATUS.md`
3. Run `git status` and `git log --oneline -5`
4. Run `php -d memory_limit=512M vendor/bin/phpunit --no-coverage` (confirm 392/0)
5. Output a brief: branch, HEAD, dirty files count, PHPUnit result.

Then confirm "Context loaded. Ready to begin remediation." and WAIT for my go-ahead.

---

## PROJECT GUARDRAILS (do not violate)

- **No autonomous commits.** Ask before any `git commit`.
- **No autonomous migrations.** Ask before creating any migration file.
- **No autonomous feature decisions.** PHANTOM-01 (classes) and PHANTOM-02 (learning outcomes) require my decision — implement or remove. Do not decide.
- **Do not deploy.** Hard rule. Do not run deploy scripts or push to production.
- **No --no-verify** on commits without my explicit approval.
- **No fabricated metrics or severity inflation.** If you don't know, say UNKNOWN.
- **PDPA:** No real trainee data in seeders, factories, tests, commits, or session notes.
- **Roles are:** Admin, Supervisor, Teacher, AJK. Not Manager/Staff/Caretaker.
- **Auth is custom:** `POST /auth/check`, not Breeze/Sanctum.
- **Commit format:** See `docs/COMMIT_MESSAGE_SOP.md`. Sign with `[Assisted by AI, reviewed manually by Faisal]`.

---

## SMOKE TEST + SCREENSHOT PROTOCOL — MANDATORY FOR EVERY FIX

This project uses the **fix-verify** skill (`.claude/skills/creams-fix-verify/SKILL.md`). Load it and follow its workflow. Every fix MUST produce a before/after evidence pair stored in `docs/audit/fix-logs/[FINDING-ID]/`.

### Smoke test credentials
- URL: `http://localhost:8000`
- Email: `admin@creams.system`
- Password: `admin123`

### Step-by-step for each fix

**A. BEFORE FIX — Capture the broken state**
1. Ensure Laravel dev server is running: `php artisan serve --port=8000` (if not already up).
2. Log in at `http://localhost:8000/login` with the credentials above.
3. Navigate to the broken route URL.
4. Confirm the error is visible (Ignition error page, exception message, or expected error response).
5. Use Playwright to take a screenshot: save as `docs/audit/fix-logs/[FINDING-ID]/01-before.png`.
6. Write a `docs/audit/fix-logs/[FINDING-ID]/fix.log` file with:
   ```
   ## [FINDING-ID] — BEFORE FIX
   Route: [URL]
   HTTP Status: [code]
   Error observed: [exception class + message]
   Timestamp: [ISO timestamp]
   ```

**B. APPLY FIX**
7. Read all files before editing (never edit blind).
8. Apply the minimal change (route removal, method fix, view creation, etc.).
9. Do NOT restart the dev server — Laravel hot-reloads route/controller changes.

**C. AFTER FIX — Verify the fix**
10. Navigate to the SAME route URL.
11. Confirm success criteria:
    - No Ignition error page
    - HTTP status 200 (or 302 redirect to a working page with success flash)
    - No exception class in page title or body
12. Use Playwright to take a screenshot: save as `docs/audit/fix-logs/[FINDING-ID]/02-after.png`.
13. Append to `docs/audit/fix-logs/[FINDING-ID]/fix.log`:
    ```
    ## [FINDING-ID] — AFTER FIX
    Route: [URL]
    HTTP Status: [code]
    Result: [working page title / redirect target / expected response]
    Screenshot: 02-after.png
    Timestamp: [ISO timestamp]
    ```

**D. REGRESSION CHECK**
14. Run `php -d memory_limit=512M vendor/bin/phpunit --no-coverage`.
15. Confirm 392/0. If ANY test fails, STOP and report — do not proceed to next fix.
16. Append to fix.log:
    ```
    PHPUnit: [passed/failed count]
    ```

**E. PRESENT TO USER**
17. Show the BEFORE and AFTER screenshots.
18. State what was changed, the files edited, and the PHPUnit result.
19. Wait for confirmation before moving to the next fix.

### Troubleshooting — before declaring a blocker

During long sessions, two common false-positive failures can occur. Always check these FIRST before concluding a fix is broken or a route is still erroring:

**1. Session timeout (logged out from idle time)**
- Symptom: a page that previously worked now shows a login redirect, 401, or unexpected auth error.
- Cause: the PHP session expired while you were editing code or analysing results.
- Recovery: re-login at `GET http://localhost:8000/auth/login` → `POST /auth/check` with the same admin credentials, then re-navigate to the target route.
- **Never mark a route as broken because of an auth redirect — verify login state first.**

**2. Dev server died (php artisan serve is flaky on Windows)**
- Symptom: all routes return HTTP 000, connection refused, or "server not responding".
- Cause: the PHP built-in server process exited silently (common under asset load or concurrent requests).
- Recovery: kill any stale process on port 8000, restart with `php artisan serve --port=8000 &`, poll until `/auth/login` returns 200, re-login.
- **Never blame a route fix for a dead server — check `curl -s -o nul -w "%{http_code}" http://localhost:8000/auth/login` first.**

### Directory structure for evidence

```
docs/audit/fix-logs/
├── NF-01/
│   ├── 01-before.png
│   ├── 02-after.png
│   └── fix.log
├── NF-02/
│   ├── 01-before.png
│   ├── 02-after.png
│   └── fix.log
├── NF-03/
│   ...
├── NF-04/
│   ...
├── NF-05/
│   ...
└── MD-03/
    ├── Trainee/
    │   ├── 01-before.png
    │   ├── 02-after.png
    │   └── fix.log
    ├── Centre/
    │   ...
    └── Letter/
        ...
```

---

## HOW TO HANDLE EVERY ISSUE

For EACH fix you attempt, follow this protocol:

1. State the finding ID, what's wrong, and the evidence.
2. Present 2-3 options (e.g., "Option A: Remove the route. Option B: Create the missing view. Option C: Comment out the route.")
3. Recommend one option with brief reasoning.
4. ASK me to choose.
5. After I choose → execute the SMOKE TEST + SCREENSHOT PROTOCOL (before/after/regression).
6. Log everything to `docs/audit/fix-logs/[FINDING-ID]/fix.log`.
7. Present the before/after evidence.
8. WAIT for my confirmation before moving to the next finding.

Track every fix in a running table:

| # | Finding ID | Action taken | PHPUnit result | Files changed | Screenshots |
|---|------------|-------------|----------------|---------------|-------------|
| 1 | NF-01 | ... | 392/0 | ... | before/after |

---

## FIX PRIORITY ORDER

### BLOCK 1 — P0: Live HTTP 500 Defects (deployment blockers)

These 3 produce hard 500s NOT caught by the exception handler. Fix ALL before anything else.

---

**NF-01 — `dashboard.modern-new` route → view `dashboard.modernnew` missing**

- Route: `GET /dashboard/modern-new` at `routes/web.php:150` → `DashboardController@modernNew`
- Controller: `app/Http/Controllers/Dashboard/DashboardController.php`
- Evidence: `modernNew()` at ~line 77 calls `view('dashboard.modernnew', ...)` inside a `try` block. The catch block at ~line 100 ALSO returns the SAME missing view → re-throws `InvalidArgumentException` out of catch → hard 500.
- File `resources/views/dashboard/modernnew.blade.php` does NOT exist. Only `resources/views/dashboard/modern.blade.php` exists.
- Fix options:
  - **A (Recommended):** Edit the catch block to return `redirect()->back()->with('error', ...)` instead of the missing view. This makes the route degrade gracefully (302) even when the dashboard data fetch fails, without needing a separate view.
  - **B:** Create `dashboard/modernnew.blade.php` — copy or extend from `modern.blade.php`.
  - **C:** Remove the `dashboard.modern-new` route from `web.php:150` entirely.

---

**NF-04 — `attendance.report` route → `Activity\AttendanceController@report` method missing**

- Route: `GET /attendance/report` at `routes/web.php:571` → `Activity\AttendanceController@report`
- Controller: `app/Http/Controllers/Activity/AttendanceController.php`
- Evidence: The controller has public methods `index`, `trainee`, `store`, `getTodayStats`, `export`, `getAttendanceForm`. No `report` method exists. Dispatch → `Error: Call to undefined method` → hard 500.
- Fix options:
  - **A (Recommended):** Remove or comment out the route at `web.php:571`.
  - **B:** Implement a `report()` method on `Activity\AttendanceController`.
  - **C:** Reroute to `export()` or another existing method.

---

**NF-05 — `admin.centres.assets` route → `CentreController@assets` method commented out**

- Route: `GET /admin/centres/{id}/assets` at `routes/web.php:813` → `CentreController@assets`
- Controller: `app/Http/Controllers/Centre/CentreController.php`
- Evidence: `CentreController.php:~272` has `/* public function assets($id) { ... } */` — fully commented out. The working replacement `assetParents()` is at ~line 317 and is used by the sibling route at `web.php:511` (`GET /centres/{id}/assets`). Route 813 was never updated to point at `assetParents`.
- Fix options:
  - **A (Recommended):** Change `web.php:813` from `[CentreController::class, 'assets']` to `[CentreController::class, 'assetParents']` (matching the working route at 511).
  - **B:** Uncomment and update the `assets()` method.
  - **C:** Remove the route entirely (admin can use the main `/centres/{id}/assets` route via the non-admin prefix).

---

### BLOCK 2 — P1: Broken Features (degrade gracefully via local catch, no hard 500, but non-functional)

**NF-02 — `centre.attendance.analytics` route → view missing, catch graceful**

- Route: `GET /centre/attendance/analytics` at `routes/web.php:328` → `Centre\AttendanceController@analytics`
- Controller: `app/Http/Controllers/Centre/AttendanceController.php`
- Evidence: `analytics()` calls `view('centre.enhanced-attendance.analytics', ...)` which doesn't exist. BUT the call is inside `try` (~line 241) and the `catch` (~line 270) returns `redirect()->back()->with('error', ...)` → graceful 302. Feature is broken, not crashing.
- Fix options:
  - **A:** Create `resources/views/centre/enhanced-attendance/analytics.blade.php`.
  - **B:** Remove the route (feature was never built, analytics dashboard doesn't exist).
  - **C:** Point the route at a different existing view (e.g., the main attendance index).

---

**NF-03 — `traineeprofile.download` route → PDF view name hyphen mismatch**

- Route: at `routes/web.php:743` → `TraineeProfileController@downloadProfile`
- Controller: `app/Http/Controllers/Trainee/TraineeProfileController.php`
- Evidence: `downloadProfile()` (~line 685) calls `Pdf::loadView('trainees.pdf-profile', ...)` (WITH hyphen), but file on disk is `resources/views/trainees/pdfprofile.blade.php` (NO hyphen). Call is inside `try`; `catch` (~line 703) returns `redirect()->route('traineeprofile')->with('error', ...)` → graceful 302. PDF download broken, not crashing.
- Fix options:
  - **A (Recommended):** Change `loadView('trainees.pdf-profile')` → `loadView('trainees.pdfprofile')` (remove hyphen). One character fix.
  - **B:** Rename file from `pdfprofile.blade.php` to `pdf-profile.blade.php`.
  - **C:** Comment out the route (disable PDF download feature).

---

### BLOCK 3 — P1: MD-03 Mass-Assignment Drift (HIGH — systemic fillable/casts mismatch)

11 of 33 real-table models have phantom `$fillable` or `$casts` entries (columns in the model that don't exist in the migration schema). Mass-assigning any of these silently fails or triggers a `QueryException` (caught → 302, but data is lost).

**The 11 drifted models (fix top 3 first):**

| Priority | Model | Table | Phantom fillable | Key issues |
|---|---|---|---|---|
| 1 | **Trainee** | trainees | 10 genuine + 6 alias | `course_id` in both `$fillable` AND `$guarded` (contradiction). Aliases (`name`, `email`, etc.) have `set*Attribute` mutators — they work but have no columns. |
| 1 | **Centre** | centres | 4 | `centre_image`, `centre_latitude`, `centre_longitude`, `attendance_policies` — none exist in schema. |
| 1 | **Letter** | letters | 10 | `sent_date` should be `date_sent`. Archive/delivery/priority fields don't exist. |
| 2 | AssetLocation | asset_locations | 12 | Column names don't match schema (`name` vs `location_name`, etc.). |
| 2 | AssetParent | asset_parents | 12 | Similar vocabulary mismatch. |
| 2 | AssetMovement | asset_movements | 6 | Uses string names; schema has `*_id` FK columns. |
| 2 | AssetMaintenance | asset_maintenance | 2 | `type` → `maintenance_type`. |
| 2 | AssetEnhanced | assets | 8 | `warranty_date` → `warranty_expiry`. |
| 3 | AttendanceAlert | attendance_alerts | 9 | Model shape doesn't match schema at all. |
| 3 | MessageRecipient | message_recipients | 3 | Extra metadata fields not in schema. |
| 3 | ActivitySession | activity_occurrences | 0 fillable, 2 casts | `session_materials`, `recurring_pattern` in `$casts` — columns don't exist. |

**MD-03 Fix protocol (one model at a time):**
1. Read the model file. Read the migration file(s) that define the model's table.
2. Cross-reference `$fillable`, `$casts`, `$dates`, `$hidden` against the migration schema columns.
3. For each phantom entry, determine: (a) genuinely absent column, (b) wrong column name (schema has a different name), or (c) alias with a mutator.
4. Present: "Model X has Y phantom entries: [list]. Options: (A) Remove all phantom entries. (B) Rename to match schema column name. (C) Keep but mark with @deprecated comments. (D) Create migration to add missing columns — ASK FIRST."
5. I choose → you apply → regression test → screenshot of a relevant form/page that uses this model → next model.

**Screenhost for MD-03 fixes:**
- For Trainee: navigate to a trainee edit/create page after fix, confirm it loads without error.
- For Centre: navigate to centre detail/edit page.
- For Letter: navigate to letter generation page.
- Store screenshots in `docs/audit/fix-logs/MD-03/[ModelName]/`.

**Start with Trainee, Centre, Letter.** Those are the most heavily used models.

---

### BLOCK 4 — P2: Test Hardening (fix false-confidence tests, add regression coverage)

**NF-08 — 2 false-confidence tests pass via Route::fallback**

- `AdminAccessTest::test_admin_can_access_reports` hits `/reports` — no such route exists (`ReportController` is imported at `web.php:42` but never routed). Test passes via `Route::fallback` 302. False green.
- `RouteOrderingTest` hits `/asset-parents/*` — that route block was removed. Test passes via fallback. False green.
- Options:
  - **A:** Fix tests to assert against real, existing routes (e.g., `/admin/reports` if appropriate, or the actual asset routes).
  - **B:** Remove the specific test assertions that test phantom routes.
  - **C:** Remove the test files entirely if they test only dead features.
- For each, present options and ask.

**NF-07 — Add regression tests for the 3 P0 fixes**

- After fixing NF-01, NF-04, NF-05: create 3 PHPUnit Feature tests that hit the fixed routes and assert they return non-500 status codes. This prevents regression.
- Store tests in `tests/Feature/Audit/`.

**NF-09 — Add smoke test for learning-outcomes routes**

- Create one PHPUnit Feature test that hits `route('learning-outcomes.index')` and asserts graceful degradation (status 302 with error flash, NOT 500, NOT 404).
- This documents the known degraded state of the phantom learning-outcomes feature.

---

### BLOCK 5 — P3: View Contract Sweep (remaining modules)

If time permits after Blocks 1-4, extend the view↔controller audit to the 7 modules not yet swept:

- Messages (`MessageController` → `resources/views/messages/`)
- Letters (`ModernLetterController`, `ModernLetterGeneratorController`, `LetterTemplateController` → `resources/views/letters/`)
- IEP (`IepController` → `resources/views/iep/`)
- Staff (`StaffController`, `StaffsHomeController` → `resources/views/staffs/`)
- Assets (`AssetController`, `AssetManagementController` → `resources/views/assets/`, `resources/views/centre/`)
- Notifications (`NotificationController` → `resources/views/notifications/`)
- Profile (`UserProfileController` → `resources/views/profile/`)

Method: For each routed controller method that calls `view(...)`, confirm the view file exists and the variables passed match what the view expects. Same smoke test + screenshot protocol.

---

## WHAT NOT TO DO IN THIS SESSION

- Do NOT touch PHANTOM-01 (classes feature) or PHANTOM-02 (learning outcomes feature) — these require my business decision on implement-vs-remove.
- Do NOT touch phantom-table models (SessionEnrollment, ClassModel, LearningOutcome, etc.) as part of this session — those are architectural debt, not active defects.
- Do NOT run `migrate:fresh` or any destructive DB operation.
- Do NOT modify the exception Handler.php — its blind spot is documented and accepted for now.
- Do NOT edit `routes/auth.php` — it's dead code, leave it.
- Do NOT edit orphaned services (CentreService, DashboardService) — dead code cleanup is a separate session.
- Do NOT change the RouteServiceProvider or demo-prefix architecture.
- Do NOT modify existing Playwright tests unless explicitly asked.
- Do NOT modify 22 models that have NO drift (Activity, ActivityEnrollment, ActivityLog, ActivityScheduleTemplate, ActivityTemplateApplication, Asset, AssetCategory, Attendance, AuditLog, ContactMessages, IepActivityGoal, LetterTemplate, Message, Notification, PublicHoliday, SessionAttendance, Staff, StaffAttendance, TraineeAuditLog, TraineeEducationPlan, User, Volunteer).

---

## SESSION CHECKPOINT RULE

After every 10 prompts (or at the end of each BLOCK, whichever comes first), append this stub to `.memsearch/memory/YYYY-MM-DD.md`:

```
## CHECKPOINT — [BLOCK N] — [timestamp]
### Current objective
### Completed this session
### Files changed
### PHPUnit result
### Commands/tests run
### Open issues
### Next best action
### Do not repeat
```

Create the file if it doesn't exist. Today's date format: `YYYY-MM-DD.md`.

---

## RUNNING TRACKER (populated during session)

| # | Finding ID | Action taken | PHPUnit result | Files changed | Screenshots |
|---|------------|-------------|----------------|---------------|-------------|
|   |            |             |                |               |             |

---

## ACCEPTANCE CRITERIA FOR THIS SESSION

- [ ] NF-01 FIXED — dashboard.modern-new no longer 500s. Screenshots captured.
- [ ] NF-02 HANDLED — fix applied or accepted as known-degraded. Screenshots captured.
- [ ] NF-03 FIXED — PDF download works or route redirected gracefully. Screenshots captured.
- [ ] NF-04 FIXED — attendance.report route removed or method created. Screenshots captured.
- [ ] NF-05 FIXED — admin.centres.assets routed correctly. Screenshots captured.
- [ ] PHPUnit remains 392/0 after every fix.
- [ ] Trainee `$fillable`/`$casts` aligned to schema (or options presented and approved).
- [ ] Centre `$fillable`/`$casts` aligned to schema.
- [ ] Letter `$fillable`/`$casts` aligned to schema.
- [ ] Remaining MD-03 models at minimum identified with clear options presented to me.
- [ ] Regression tests added for the 3 P0 fixes.
- [ ] All evidence stored in `docs/audit/fix-logs/[FINDING-ID]/`.
- [ ] No migrations created without my approval.
- [ ] No commits without my approval.
- [ ] Session checkpoint file created in `.memsearch/memory/YYYY-MM-DD.md`.

---

Begin with the MANDATORY START steps. Output the brief, confirm readiness, and WAIT for my go-ahead.
```
