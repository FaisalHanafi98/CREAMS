# CREAMS — Execution Batch 03: P0 UAT Blockers

> **Wave**: 3
> **Priority**: P0 — BLOCKS STAKEHOLDER DEMO
> **Source**: `CURRENT_UAT_STATE.md`, `CURRENT_BLOCKERS.md`, all May 15-18 `live_*_uat_*.md` audit files
> **Risk Level**: HIGH — Code changes required to auth and trainee controllers
> **Estimated Sessions**: 1-2
> **Dependencies**: Wave 2 complete (server functional for testing). PHPUnit baseline at 359/359.
> **Precondition**: `pdk-creams.org` accessible. Test credentials working. 359 PHPUnit tests passing before any changes.

---

## Task B3-T1: Fix logout session persistence

| Field | Value |
|-------|-------|
| **Task ID** | B3-T1 |
| **Priority** | P0 |
| **Source Evidence** | `CURRENT_BLOCKERS.md` P0 #1, `CURRENT_UAT_STATE.md` Live UAT FAIL, `live_uat_gate_smoke_2026-05-17.md`, `full_browser_uat_report_2026-05-18.md` |
| **Finding Reference** | UAT blocker — Logout does not terminate sessions on pdk-creams.org |
| **Problem Statement** | After logout, user can navigate directly to authenticated routes (e.g., /dashboard) and access the application without re-authentication. Confirmed by all May 15-18 live UAT runs. The session survives the logout operation — indicating either `Session::flush()` or `Session::regenerate()` is not executing correctly in the logout handler. |
| **Affected Files** | `app/Http/Controllers/MainController.php` (logout method), `app/Services/SessionManager.php` (if logout routes through it) |
| **Affected Components** | Session lifecycle, authentication boundary |
| **Dependencies** | B2-T1 (APP_KEY must be real — sessions depend on encryption). B2-T2 (LOG_LEVEL may reveal session state during debugging). |
| **Execution Preconditions** | [ ] Server accessible at pdk-creams.org. [ ] Test credentials working (all 4 roles). [ ] PHPUnit baseline: 359/359. [ ] Browser DevTools open for cookie inspection. |
| **Reproduction Path** | 1. Log in as any role (e.g., super.admin@uat.creams.test / UatPass2026!). 2. Navigate to /dashboard — confirm access. 3. Copy the current URL. 4. Click logout. 5. Paste the dashboard URL directly in the address bar. 6. Expected: redirected to /auth/login. Actual: dashboard loads without authentication. |
| **Suspected Component** | `MainController.php` logout method. The `SessionManager.php` was verified to call `Session::flush()` + `Session::regenerate()` on login (line 19-20) and `Session::regenerate()` on logout (line 96). The logout handler in MainController may bypass SessionManager or call it with insufficient session destruction. REQUIRES VALIDATION — check actual logout code path. |
| **Verification Steps** | 1. Read `MainController.php` logout method — trace the session destruction path. Verify `Session::flush()` and `Session::regenerate()` are both called. 2. Add temporary debug: `Log::info('Session ID before logout', ['id' => session()->getId()])` before logout and after logout attempt. 3. Check if Laravel's session cookie is being cleared (`session()->invalidate()` may be needed instead of or in addition to flush). 4. Check if "remember me" token preserves session — if user has remember_me cookie, clear it on logout. 5. Fix: ensure the logout method calls one of: `auth()->logout()` (if using auth guard), OR `$request->session()->invalidate()` + `$request->session()->regenerateToken()` (Laravel recommended), OR `Session::flush()` + `Session::regenerate()` (current pattern — verify it's sufficient). 6. After fix: reproduce the steps above. Dashboard URL should redirect to /auth/login. 7. Run `php artisan test` — all 359 must still pass. |
| **Rollback Strategy** | Before any change: `git stash` the fix. If tests break or login breaks, `git stash pop` to restore. Test login → logout → dashboard flow after rollback to confirm original state. |
| **Completion Criteria** | [ ] After logout, navigating to any authenticated route redirects to /auth/login. [ ] Session cookie is cleared in browser after logout. [ ] Remember-me token (if present) is cleared on logout. [ ] All 4 roles tested (Admin, Supervisor, Teacher, AJK). [ ] 359 PHPUnit tests still passing. [ ] Gate smoke re-run: PASS (no logout failure). |
| **Estimated Effort** | 30-60 minutes (depends on root cause complexity) |
| **Risk of Change** | HIGH — touching auth controller. Any session regression breaks all authenticated access. Must verify login still works after logout fix. Must verify all 4 roles. |
| **Notes** | The `SessionManager.php` was part of the Phase 1 security hardening (Feb 2026). The PRE_DEPLOY_SECURITY_CHECKLIST item 3.1 marks session regeneration on login as GREEN (verified). But logout was not explicitly verified in the checklist — only 3.2 "Session cleared on logout" is marked GREEN. The bug may be that `Session::flush()` clears the server-side session but the client-side cookie persists. Laravel's `invalidate()` method handles both. |

---

## Task B3-T2: Fix trainee creation 500 error

| Field | Value |
|-------|-------|
| **Task ID** | B3-T2 |
| **Priority** | P0 |
| **Source Evidence** | `CURRENT_BLOCKERS.md` P0 #2, `CURRENT_UAT_STATE.md` Trainees module FAIL, `live_functional_uat_readiness_2026-05-16.md`, `full_browser_uat_report_2026-05-18.md` |
| **Finding Reference** | UAT blocker — Trainee registration form submission fails with 500 error on pdk-creams.org |
| **Problem Statement** | Submitting the trainee registration form on the production domain returns a 500 Internal Server Error. Confirmed by all May 15-18 live UAT runs. The exact error is not documented — REQUIRES VALIDATION from server logs. Possible causes: database FK constraint failure (centre_id, trainee_id format), missing auto-increment/default value, file upload path issue, or validation rule incompatibility with production PHP version. |
| **Affected Files** | `app/Http/Controllers/TraineeRegistrationController.php`, `app/Models/Trainee.php`, `resources/views/trainees/registration.blade.php`, `database/migrations/*_create_trainees_table.php` |
| **Affected Components** | Trainee creation flow, MySQL, file storage |
| **Dependencies** | B2-T4 (UATSeeder runs — verifies DB is functional). B2-T2 (LOG_LEVEL may reveal error details). |
| **Execution Preconditions** | [ ] Server logs accessible: `storage/logs/laravel.log` AND nginx error log. [ ] Test credentials working. [ ] UATSeeder ran successfully (DB functional). |
| **Reproduction Path** | 1. Log in as Admin or Supervisor. 2. Navigate to trainee registration page. 3. Fill all required fields (name, IC number, date of birth, guardian info, centre). 4. Submit form. 5. Expected: redirect to trainee list with success message. Actual: 500 error page. |
| **Suspected Component** | REQUIRES VALIDATION — check server logs first. Common causes for Laravel 500 on form submission: (a) Foreign key constraint — centre_id value doesn't match centres table. (b) trainee_id format failure — if controller generates a custom ID that collides or fails format validation. (c) File upload path — if avatar upload is included and storage directory is not writable or symlink not created. (d) Mass assignment — if `$fillable` array doesn't include a submitted field. (e) DB column mismatch — if a submitted field name doesn't match a column and isn't in `$fillable`. |
| **Verification Steps** | 1. Read `storage/logs/laravel.log` on server — find the stack trace for the 500 error. This reveals the exact exception. 2. Based on the exception: (a) FK constraint → verify centre_id in the form matches an existing centre. (b) trainee_id format → check how the ID is generated in the controller and if it conflicts with an existing record. (c) File path → run `php artisan storage:link` if not already done. Check `public/storage` symlink exists. (d) Mass assignment → add missing field to `$fillable` in Trainee model. (e) Column mismatch → verify form field names match database columns. 3. After fix: reproduce the trainee creation flow. Should redirect with success. 4. Run `php artisan test --filter=TraineeManagementTest` — must pass. 5. Run full `php artisan test` — all 359 must still pass. |
| **Rollback Strategy** | Before any code change: `git stash`. Reproduce the error to capture exact log output. Fix. If fix introduces other breakage, `git stash pop`. |
| **Completion Criteria** | [ ] Trainee registration form submits successfully on pdk-creams.org. [ ] New trainee appears in trainee list. [ ] No 500 errors in server logs. [ ] TraineeManagementTest passes. [ ] Full PHPUnit suite: 359/359. [ ] Contact form also verified (May 18 Edge UAT showed contact form rejects valid emails — may be related to validation rules). |
| **Estimated Effort** | 30-90 minutes (depends on root cause complexity — REQUIRES VALIDATION) |
| **Risk of Change** | MEDIUM — touching trainee registration may affect CentreScope, validation rules, and file uploads. |
| **Notes** | The trainee creation takes 26s locally (per performance baseline) due to email notifications and N+1 enrollment queries. On production with no email config, the timing may differ. The 500 is likely not a timeout issue (PHP-FPM timeout is typically 30s+) but check the execution time in the error log to confirm. |

---

## Task B3-T3: Verify contact form validation

| Field | Value |
|-------|-------|
| **Task ID** | B3-T3 |
| **Priority** | P2 (non-blocking but surfaced in UAT) |
| **Source Evidence** | `full_browser_uat_report_20260518T153020Z.md` — Edge Chromium run |
| **Finding Reference** | Contact form rejects valid emails in Edge Chromium UAT run (May 18) |
| **Problem Statement** | In the May 18 Edge Chromium UAT run, the contact form rejected valid email addresses. This was only observed in ONE run (Edge-specific) — not confirmed across all browsers. May be a browser-specific validation issue, a server-side validation rule that's too strict, or a JavaScript validation quirk. |
| **Affected Files** | `app/Http/Controllers/ContactController.php`, `resources/views/contactus.blade.php`, `public/js/contact.js` |
| **Affected Components** | Contact form, email validation, JavaScript |
| **Dependencies** | B3-T2 (trainee creation fix — may share validation rule patterns) |
| **Execution Preconditions** | [ ] B3-T1 and B3-T2 complete. [ ] Full browser UAT re-run planned. |
| **Verification Steps** | 1. Test contact form in Chrome: submit with `test@example.com`. 2. Test in Edge: submit with `test@example.com`. 3. If Edge fails: check server-side validation in ContactController — verify email rule is `email` (Laravel standard), not a custom regex. 4. Check client-side validation in contact.js — verify email regex is RFC-compliant, not overly restrictive. 5. After fix: test in Chrome, Edge, Firefox. 6. Include contact form in full browser UAT re-run. |
| **Rollback Strategy** | Contact form is a public-facing page. Rollback = restore original validation logic in controller and JS. |
| **Completion Criteria** | [ ] Contact form accepts valid email addresses in Chrome, Edge, and Firefox. [ ] Contact form rejects invalid email addresses (format validation still works). [ ] Full browser UAT re-run: contact form PASS. |
| **Estimated Effort** | 15-30 minutes |
| **Risk of Change** | LOW — contact form is public, non-authenticated. No PDPA data involved. |
| **Notes** | Contact page is one of the 3 "PROTECTED" pages per old CREAMS_MASTER_DOCUMENTATION (Jan 2025). However, that protection claim is from a stale doc now archived. The contact page can be modified if needed. |

---

## Wave 3 Completion Checklist

- [ ] B3-T1: Logout terminates sessions (all 4 roles verified)
- [ ] B3-T2: Trainee creation succeeds (200, not 500)
- [ ] B3-T3: Contact form accepts valid emails across browsers
- [ ] 359 PHPUnit tests still passing
- [ ] Full browser UAT re-run: 0 P0 blockers
- [ ] Gate smoke re-run: logout PASS
- [ ] Stakeholder demo: READY (can proceed)

---

## Post-Wave 3 UAT Re-Run

After all Wave 3 tasks complete, run the full verification:

1. Run `php artisan test` — confirm 359/359
2. Run full browser UAT (Chrome + Edge) per `full_browser_uat_report_2026-05-18.md` methodology
3. Verify logout across all 4 roles in both browsers
4. Verify trainee creation with minimal fields + with all fields
5. Verify contact form in Chrome, Edge, Firefox
6. Document results in `docs/audit/full_browser_uat_retest_YYYY-MM-DD.md`

---

*B3-T1 and B3-T2 are code-level fixes. B3-T3 is browser verification. All tasks traceable to May 2026 live UAT evidence. REQUIRES VALIDATION items flagged where server error logs are needed before fix can be designed.*
