# CREAMS — UAT Matrix (STEP A: Planning, pre-approval)

**Date**: 2026-06-21 · **Branch**: Fixers @ 99f134c · **Purpose**: Full inventory to execute a
traceable Demo-Readiness Simulation. Built from `php artisan route:list` (629 routes; legacy
`creams/{demo_id}/*` duplicates and framework internals excluded → ~250 direct routes).

> This is the plan. No execution beyond inventory has happened in this step.
> Approve / amend before STEP B (browser trace), STEP C (UX), STEP D (workflows), STEP E (report).

---

## 1. Module Inventory (direct routes; methods)

| # | Logical Module | Prefix(es) | GET | Mutating (POST/PUT/DEL) | Primary roles |
|---|---|---|---|---|---|
| M01 | Public / Marketing | `/`, aboutus, contact, trademark, registration | 5 | 1 (contact submit) | Guest |
| M02 | Authentication | auth, login, logout, forgot/reset-password, validateEmail | 7 | 8 | Guest/all |
| M03 | Dashboards | dashboard, {role}/dashboard | 12 | 2 | All |
| M04 | Staff Management | staffs, staff, admin/users, supervisor/users | 14 | 1+ | Admin, Supervisor |
| M05 | Trainee Management | trainees, trainee, traineeprofile, traineeshome | 18 | 7 | Admin, Sup, Teacher |
| M06 | Activity Management | activities, activity-attendance | 36 | 27 | Admin, Sup, Teacher |
| M07 | Attendance | attendance, staff-attendance, centre/centres attendance | 9 | 2 | Admin, Sup, Teacher |
| M08 | IEP | iep | 4 | 5 | Admin, Sup, Teacher |
| M09 | Letters | letters, letters-old, letters-archive | 14 | 5 | Admin, Sup |
| M10 | Assets | assets, centre/assets, admin/assets-admin | 9 | 6 | Admin, Sup |
| M11 | Centres | centres, centre, admin/centres | 15 | 6 | Admin, Sup |
| M12 | Volunteer | volunteer | 4 | 4 | Guest (apply), Admin/Sup (review) |
| M13 | Messaging | messages | 3 | 2 | All |
| M14 | Notifications | notifications | 3 | 4 | All |
| M15 | Profile | profile | 10 | 9 | All |
| M16 | Search | search, api | 14 | 3 | All |
| M17 | Rehabilitation / Categories | rehabilitation, activities/categories | 2+ | — | Admin, Sup |
| M18 | Schedule | schedulehomepage, activities/schedule | 5 | — | Admin, Sup, Teacher |
| M19 | Reports & Settings | settings (Reports disabled) | 2 | 1 | Admin |
| M20 | Parent / Trainee portals | parent/*, trainee/* | 6 | 0 | **PLANNED — verify if live** |

**Mutating endpoints total: 105** (target for API-level RBAC/PDPA negative tests in STEP D).

---

## 2. Roles

| Role | Account (UAT) | Status | Notes |
|---|---|---|---|
| Admin | super.admin@uat.creams.test | Implemented | Cross-centre directories; dashboard centre-scoped (D-01) |
| Supervisor | supervisor.a1@uat.creams.test | Implemented | Centre-scoped; manages teachers/AJK |
| Teacher | teacher.a1@uat.creams.test | Implemented | Centre-scoped; activities + sessions |
| AJK | ajk.a1@uat.creams.test | Implemented | Centre-scoped, read-oriented |
| Parent | — | **PLANNED** | Routes exist; no seeded account — will verify if functional or stub |
| Trainee | — | **PLANNED** | Same caveat |

---

## 3. Per-Role Workflow Journeys (STEP D — end-to-end, not component clicks)

**Admin journey**: login → dashboard → register staff → register trainee → create activity (5-step
wizard) → assign instructor → create centre → add asset → generate letter → review volunteer
application → logout.

**Supervisor journey**: login → dashboard → view centre staff → create/edit activity → schedule
session → mark attendance → review trainee → approve volunteer → logout.

**Teacher journey** (core): login → dashboard → view my activities → open activity → **record a
session / mark activity attendance end-to-end** → view trainee progress → logout.

**AJK journey**: login → dashboard → read trainees/activities/centres → confirm create/edit blocked
(403) → logout.

**Parent journey** (if live): login → dashboard → view linked trainee → view progress → notifications.
*If not implemented, recorded as PLANNED/Not-Testable with evidence.*

**Guest journey**: home → services → contact (submit form, verify DB) → volunteer (submit application,
verify DB) → staff portal login.

---

## 4. Page-Level Visual + Functional Inventory (STEP B/C)

For **every** GET page below: capture screenshot, enumerate buttons/links/forms on the page,
check load + console, and run the UX checklist (§6). Pages (direct, non-param), grouped:

- **Public**: /, aboutus, contact, volunteer, volunteer/centres, trademark, registration
- **Auth**: auth/login, forgot-password, reset-password
- **Admin**: admin/dashboard, admin/users(staffs/home), staffs/create, staffs/register, admin/trainees,
  trainees/home, trainees/create, admin/activities, activities/home, activities/create,
  activities/categories, activities/schedule, activities/templates, admin/centres(centres/home),
  centres/create, centre/assets, assets/create, assets/maintenance, assets/movements, assets/reports,
  admin/letters(letters), letters/modern, letters/modern/create, admin/letter-templates,
  admin/volunteers, admin/notifications, iep, iep/create, messages, messages/create, attendance,
  attendance/report, staff-attendance, centres/attendance, profile, profile/home, search, settings
- **Teacher**: teacher/dashboard, teacher/activities, teacher/trainees, teacher/schedule,
  teacher/centres, teacher/notifications
- **Supervisor**: supervisor/dashboard, supervisor/users, supervisor/activities, supervisor/trainees,
  supervisor/centres, supervisor/notifications
- **AJK**: ajk/dashboard, ajk/activities, ajk/trainees, ajk/centres, ajk/notifications
- **Parent/Trainee (planned)**: parent/dashboard, parent/notifications, trainee/dashboard, trainee/activities

Estimated unique screens to inspect: **~70** (excludes per-record detail/edit pages, which are
sampled, not exhaustive).

---

## 5. API-Level RBAC / PDPA Test Plan (STEP D — beyond UI)

Addressing the valid gap: UI hiding ≠ endpoint protection. Representative negative tests against
mutating endpoints, executed as the wrong role (expect 403/redirect, not 200):

| Test | Endpoint (example) | As role | Expect |
|---|---|---|---|
| Create trainee | POST trainees | AJK | 403 |
| Delete trainee | DELETE trainees/{id} | Teacher | 403 |
| Cross-centre read | GET traineeprofile/{id} (other centre) | Teacher | blocked/empty |
| Create staff | POST staffs | Teacher | 403 |
| Delete activity | DELETE activities/{id} (other centre) | Teacher | 403 |
| Edit centre | PUT centres/{id} | AJK | 403 |
| IDOR check | GET trainees/{id} with another centre's id | Teacher | denied |

Method: authenticated fetch with CSRF token from the wrong role's session; assert status + that no
cross-centre data returns. (Scope: ~10–15 representative cases, not all 105 — full coverage noted as
a follow-up if required.)

---

## 6. UX / UI Review Checklist (STEP C — per page)

- Alignment & grid consistency (cards, headers, sidebar)
- Spacing/padding rhythm; no cramped or overlapping elements
- Typography consistency (font family/sizes/weights across modules)
- Colour/theme consistency (brand purple/blue gradient usage)
- Empty states (do they read intentionally vs broken?)
- Broken images / missing assets / icon glyphs rendering
- Responsive: 1440px desktop + 390px mobile (overflow, nav collapse, tap targets ≥44px)
- Loading/feedback states (toasts, spinners) on actions
- Visual defects log per page

---

## 7. Screenshot Traceability System (addresses audit-grade gap)

**Naming convention**: `UAT-<TCID>-<role>-<module>-<state>.png`
e.g. `UAT-018-teacher-dashboard-default.png`, `UAT-031-admin-trainee-create-validation.png`.

**Index table** (built live in STEP E) — every screenshot maps to exactly one test case:

| Screenshot | TC ID | Role | Module | Action/State | Result |
|---|---|---|---|---|---|

(Existing 16 screenshots from the first pass will be renamed into this scheme or supplemented.)

---

## 8. Demo-Readiness Score Rubric (0–100, reported in STEP E)

| Dimension | Weight |
|---|---|
| Functional completeness (workflows complete end-to-end) | 30 |
| RBAC + PDPA (UI **and** API level) | 20 |
| Data integrity (CRUD persists, no partial saves) | 15 |
| UX/UI quality (consistency, no visual defects) | 15 |
| Demo data quality (no test junk, seeded IEP/letters) | 10 |
| Stability (no console/runtime errors) | 10 |

---

## 9. Open questions / caveats (need your call before STEP B)

1. **Parent/Trainee roles**: PLANNED per source-of-truth. Shall I (a) test the routes to confirm
   stub-vs-functional and report, or (b) mark out-of-scope? Default: (a) verify and report.
2. **API-level tests**: representative ~12 cases, or exhaustive across all 105 endpoints? Default:
   representative (exhaustive is a much larger pass).
3. **Visibility**: Playwright MCP drives a real Chromium but **headless** by default. I cannot force a
   visible Chrome window on this server reliably. The testing **is** real browser interaction
   (real DOM, real clicks, real navigation, real screenshots) — just not a window you watch live.
   Acceptable, or do you want me to attempt headed mode?
4. **Detail/edit pages & exhaustive per-button clicks**: sampled (representative records) vs every
   record. Default: sample + every distinct button *type* per page.

---

## Coverage commitment for STEP B–E
- Every GET page in §4 visited, screenshotted, console-checked → **explicit pass/fail row each**.
- Buttons/links/forms enumerated per page (the "all buttons" requirement) during STEP B.
- 6 role journeys (§3) run end-to-end with per-step evidence.
- ~12 API-level RBAC/PDPA negative tests (§5).
- UX checklist (§6) per page with a visual-defects log.
- Strict final report (§7 index + §8 score + defects + deployment checklist).

**STOP — awaiting approval / amendments to this matrix before execution.**
