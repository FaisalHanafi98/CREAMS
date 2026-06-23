# CREAMS — Full Client-Demo UAT Report

**Date**: 2026-06-21
**Branch / HEAD**: Fixers @ 99f134c
**Tester**: Browser-driven UAT (Playwright MCP, real Chromium)
**Environment**: Local dev server `php artisan serve` on 127.0.0.1:8000, Laravel 12.58.0, MySQL `cream`
**Method**: Stakeholder-perspective walkthrough — real browser navigation, form submission, DB verification, RBAC checks across all 4 roles. Not a smoke test.

---

## Executive Summary

CREAMS is **functionally solid and visually demo-ready**. Across all four roles and
the major modules, the system loaded with **zero JavaScript console errors**,
authentication and role-based access control worked correctly, data-level PDPA
isolation (CentreScope) held, and a live trainee create/delete round-trip persisted
and removed correctly in the database.

No Critical or High functional defects were found in the browser audit. The findings
are **demo-polish and content-seeding items** (Medium/Low), plus one reporting
inconsistency on the admin dashboard. The system can be demonstrated to stakeholders
today with a short pre-demo data-cleanup pass.

Production deployment remains gated on items outside this UAT: the CF-08 production
`LOG_LEVEL` decision, the "do not deploy" governance rule, and the Fixers git-history
cleanup — all tracked separately.

**Deployment readiness: CONDITIONALLY READY** (ready for demo; see conditions below).

---

## Scope

| In scope | Out of scope |
|---|---|
| Public site, auth, all 4 role dashboards, core modules, 1 full CRUD with DB verification, RBAC matrix, mobile responsiveness, console health | Load/performance testing, security pen-test, email delivery, file-upload storage, payment, cross-browser (Chromium only), exhaustive every-record CRUD (covered by automated suite) |

---

## Test Environment

- Laravel 12.58.0, PHP 8.x, MySQL `cream`
- Seeded demo data: 18 staff, 32 trainees, 4 centres (UA1–UA3 + Test Centre), 10 activities, 5 assets
- Accounts (all `UatPass2026!`): `super.admin@uat.creams.test`, `supervisor.a1@…`, `teacher.a1@…`, `ajk.a1@…`
- Automated baselines (same HEAD): PHPUnit **377/0**, Playwright smoke **14/14**

---

## Modules Tested

Public landing, Contact, Volunteer, Login/Logout, Admin Dashboard, Staff Directory,
Trainee Management (+ create/delete), Activities (+ create wizard), Centres, Assets,
Letters, Volunteer Applications, Profile, Notifications, IEP, Messages, Attendance,
Global Search; Teacher / Supervisor / AJK dashboards and scoped views.

---

## Test Cases

| # | Module | Action | Expected | Actual | Result | Evidence |
|---|---|---|---|---|---|---|
| 1 | Public | Load landing page | Renders, clean routes | Full content, 0 errors, direct routes | PASS | uat-01-home.png |
| 2 | Public | Volunteer page | Loads | Loads, 0 errors | PASS | uat-02-volunteer.png |
| 3 | Auth | Invalid login | Rejected + message | "No account found…" shown, stays on login | PASS | uat-03-login-invalid.png |
| 4 | Auth | Admin login | → admin dashboard | Redirected correctly | PASS | uat-04-admin-dashboard.png |
| 5 | Admin | Dashboard stats | Show counts | Shows 7/13/3/1 (centre-scoped — see D-01) | PASS* | uat-04 |
| 6 | Admin | Staff Directory | List 18 staff | 18 total, paginated, filters | PASS | uat-05 |
| 7 | Admin | Trainee list | List trainees | 32 total/active, 15 enrolled | PASS | uat-06 |
| 8 | Admin | **Trainee CREATE** | Persist new trainee | id=193 created, all fields saved, count 32→33 | PASS | uat-07 |
| 9 | Admin | Trainee DELETE | Remove record | Deleted, count 33→32 | PASS | (DB) |
| 10 | Admin | Activities | List activities | 9 activities, 58 sessions, clean names | PASS | uat-08 |
| 11 | Admin | Centres | List centres | Per-centre counts correct | PASS | uat-09 |
| 12 | Admin | Assets | List assets | 5 assets, filters | PASS | uat-10 |
| 13 | Admin | Letters | Letter generation | Works; 0 templates (see D-03) | PASS* | — |
| 14 | Admin | Notifications | List/empty state | Empty state + filters work | PASS | — |
| 15 | Admin | IEP | List plans | Works; 0 records (see D-02) | PASS* | uat-16 |
| 16 | Admin | Activity create wizard | 5-step form | Renders all steps | PASS | — |
| 17 | Admin | Global search | Return results | JSON results, encrypted IDs | PASS | — |
| 18 | Teacher | Login + dashboard | Role-scoped view | No staff-mgmt nav, personalized | PASS | uat-11 |
| 19 | Teacher | RBAC: /admin/users | Denied | 403 Access Denied page | PASS | uat-12 |
| 20 | Teacher | Trainee list scope | Centre-only | 14 (UA1 only), not 32 | PASS | — |
| 21 | Supervisor | Login + dashboard | Scoped + staff mgmt | Has Staff/Volunteer mgmt, scoped | PASS | uat-13 |
| 22 | Supervisor | Staff Directory scope | Centre-only | 7 (UA1 only) | PASS | — |
| 23 | AJK | Login + dashboard | Read-oriented | Loads, centre-scoped | PASS | uat-14 |
| 24 | AJK | RBAC: /trainees/create | Denied | 403 Access Denied | PASS | — |
| 25 | AJK | Read access | trainees/home loads | Loads OK | PASS | — |
| 26 | Cross | Mobile responsiveness | No overflow, collapses | Hamburger + stacked cards, no h-scroll | PASS | uat-15 |
| 27 | Cross | Console health | No JS errors | 0 errors all authed pages | PASS | — |

\* PASS with a Medium/Low note (see Defects).

---

## Defects

No Critical or High functional defects.

### D-01 [Medium] — Admin dashboard counts are centre-scoped, directories are not
The admin dashboard shows centre-UA1 counts (7 staff, 13 trainees, 3 activities) while
the directory pages show cross-centre totals (18 staff, 32 trainees, 9 activities).
For a "super admin spanning all centres" this under-reports on the landing dashboard
and looks inconsistent.
- **Repro**: Login admin → dashboard (7/13) → Staff Directory (18).
- **Impact**: Confusing headline numbers in the first screen of a demo.
- **Fix**: Decide intended admin scope; make dashboard and directories consistent.

### D-02 [Medium] — IEP module has no seeded records
Individual Education Plans is a core rehabilitation feature but shows 0 plans.
- **Impact**: A key differentiator looks empty during a demo.
- **Fix**: Seed a few synthetic IEP records for UAT centres.

### D-03 [Medium] — No letter templates seeded
Letters page shows "0 Available Templates / No templates available".
- **Impact**: "Generate from template" cannot be demonstrated.
- **Fix**: Seed 1–2 letter templates.

### D-04 [Low] — Test/junk data in dashboard "Recent Changes" feed
The admin dashboard activity-log feed shows entries like "ToDelete 1781376643078",
"Delete Me 1781376526674", "Test Activity 511". The activities list itself is clean;
only the audit-log feed carries these.
- **Fix**: Purge test rows from the activity-log table before the demo.

### D-05 [Low] — "Test Centre" looks unfinished
"Test Centre" shows "Location not specified", 0 staff, 0 assets, 5 trainees (leftover
centre `01` Gombak data). Untidy in the centre list.
- **Fix**: Hide/relabel/remove the legacy centre for the demo dataset.

### D-06 [Low] — Public footer placeholder links
Privacy Policy / Terms / Sitemap and social icons are `#` placeholders; newsletter says
"under development".
- **Fix**: Hide or point to real pages before a public-facing demo.

### D-07 [Low] — "Smoke Test Letter" in letters list
A test-named letter is visible in Recent Letters.
- **Fix**: Remove test letter rows.

### Info (not defects)
- Reports & Settings are intentionally disabled and clearly marked "Development".
- 403 pages return an HTTP 403 (shows as a console "error") — this is correct behaviour, not a bug.

---

## Positive findings (demo strengths)

- Zero JS console errors across all authenticated pages.
- RBAC enforced: cross-role access returns a styled 403 (teacher→admin, ajk→create).
- PDPA data isolation works: teacher/supervisor see only their centre's records.
- Trainee form includes explicit PDPA consent checkboxes (photo/services/data).
- Encrypted IDs used in profile/search URLs (no raw IDs exposed).
- Live weather widget, personalized greetings, modern consistent UI.
- Mobile-responsive (hamburger nav, stacked cards, no horizontal overflow).
- End-to-end CRUD persists correctly with clear success feedback.

---

## Recommendations Before Demo

1. Purge test-named rows from the activity-log and letters tables (D-04, D-07).
2. Seed IEP records and letter templates (D-02, D-03).
3. Reconcile admin dashboard vs directory counts, or brief the presenter on it (D-01).
4. Tidy/remove the legacy "Test Centre" (D-05).
5. Hide public footer placeholder links if the public site is shown (D-06).

## Recommendations Before Production

1. Resolve CF-08: confirm/set production `LOG_LEVEL=warning` (debug PII logging).
2. Owner to lift the "do not deploy" governance rule.
3. Complete the Fixers git-history cleanup (IRL files) if a public history is a concern.
4. Implement or formally defer Reports & Settings.
5. Cross-browser pass (Firefox/Safari) and a basic load test.

---

## Deployment Readiness

**CONDITIONALLY READY.**

- **Client demo / stakeholder review**: READY after the short pre-demo cleanup above
  (items are cosmetic/data-seeding, ~1–2 hours). Core flows are solid.
- **Production**: NOT READY until the CF-08 log-level decision, the deploy-hold rule,
  and the git-history cleanup are resolved. None are functional code defects.

Justification: the application is functionally complete and stable for the demo
surface area tested — authentication, RBAC, CRUD, centre isolation, and all major
module pages work with a clean console. The blockers are governance/config and
presentation-data items, not broken functionality.
