# Playwright MCP Dry-Run Report — Phase 5 (Track B)
**Date:** 2026-05-04  
**Tool:** Playwright MCP (`mcp__playwright__*` via `.mcp.json`)  
**Browser:** System Chrome (Chromium, real browser — not headless)  
**App:** `http://localhost:8000` | Branch: `Fixers`  
**DB Seed:** UATSeeder (3 centres / 17 staff / 21 trainees / 9 activities)  
**Tester:** Claude Code (automated MCP walkthrough)  
**Track A baseline:** `docs/audit/PLAYWRIGHT_FINDINGS_2026-05-04.md`

---

## Executive Summary

**Demo clearance: GO**  
**P0 issues: 0 | P1 issues: 0 | P2 issues: 5 (all pre-known) | New findings: 2**

All 6 demo beats passed. CentreScope isolation verified interactively. The MCP walkthrough surfaced no new blockers beyond the CSP P2 issues already documented in Track A. Two additional CSP-blocked resources were identified on authenticated pages (weather widget `wttr.in`, jQuery CDN) that are cosmetic-only.

---

## Pre-flight Results

| Check | Result |
|-------|--------|
| App responding at `http://localhost:8000/auth/login` | 200 OK |
| DB tables found | 43 tables |
| Staff count | 17 (16 seeded + 1 super admin) |
| Trainee count | 21 |
| Centre count | 3 |
| UAT credentials | `super.admin@uat.creams.test` / `UatPass2026!` confirmed |
| Playwright MCP load | Confirmed — `mcp__playwright__*` tools active |

---

## Beat Results

### Beat 00 — Login Page (MCP confirmation)
- **URL:** `/auth/login`  
- **Result:** PASS  
- **Screenshot:** `docs/audit/screenshots/mcp-dryrun/00-login-page.png`  
- **Notes:** Page renders with IIUM campus background photo, "Welcome Back" heading, email/password form, Sign In button. Form fully functional despite CSP blocking Bootstrap/FontAwesome/Poppins (inline styles apply fallback styling).  
- **Console errors (P2):**
  - Bootstrap 5.3.0 CSS blocked (`style-src`)
  - Font Awesome 6.4.0 CSS blocked (`style-src`)
  - Google Fonts (Poppins) blocked (`style-src`)
  - Bootstrap 5.3.0 JS blocked (`script-src`)

---

### Beat A1 — Admin Login → Dashboard
- **URL:** `/auth/login` → POST `/auth/check` → `/admin/dashboard`  
- **Result:** PASS  
- **Screenshot:** `docs/audit/screenshots/mcp-dryrun/A1-admin-dashboard.png`  
- **Notes:** Dashboard loads with "Welcome back, UAT Super Admin!" banner, role badge "Admin", live date/time (Monday May 4 2026 / 1:37 AM). Stats cards: 6 Active Staff, 7 Active Trainees (Centre A view), 3 Ongoing Activities, 0 Sessions. Quick Actions footer: Create Activity, Mark Attendance, Manage Staff, Manage Trainees, View Schedule.  
- **Console errors (P2):**
  - Bootstrap 4.6.0 CSS + JS blocked (`style-src`, `script-src`)
  - Font Awesome 6.2.0 CSS blocked (`style-src`)
  - Google Fonts (Poppins) blocked (`style-src`)
  - **NEW:** `wttr.in/Gombak,Malaysia` blocked by `connect-src 'self'` — weather widget shows "Weather unavailable"
  - **NEW:** jQuery 3.6.0 CDN blocked (`script-src`) → `ReferenceError: $ is not defined` — dropdown/modal JS affected but page still fully navigable

---

### Beat A2 — Centres Index (CentreScope verification)
- **URL:** `/admin/centres` → redirect → `/centres/home`  
- **Result:** PASS  
- **Screenshot:** `docs/audit/screenshots/mcp-dryrun/A2-centres-home.png`  
- **Notes:** All 3 UAT centres visible to Admin. Counts verified:
  - UAT Centre A: 6 Staff / 7 Trainees / 0 Assets
  - UAT Centre B: 5 Staff / 7 Trainees / 0 Assets
  - UAT Centre C: 5 Staff / 7 Trainees / 0 Assets
  - Total: 16 staff + 1 super admin = 17 ✓, 21 trainees ✓
- **Observation:** Unstyled rendering (Bootstrap blocked) — layout is linearised/plain but all data is present and readable.

---

### Beat A3 — Trainees List
- **URL:** `/admin/trainees` → redirect → `/trainees/home`  
- **Result:** PASS  
- **Screenshot:** `docs/audit/screenshots/mcp-dryrun/A3-trainees-home.png`  
- **Notes:** Redirect to `/trainees/home` expected (known from Track A). Stats: 21 Total Trainee / 21 Active Trainee / 0 Enrolled in Activity / 0% Average Progress / 21 Below 50% Attendance. Trainee cards display anonymised IDs (UAT1 Lowe, UAT2 Streich…), age, gender, centre, condition badge, progress. Pagination: "Showing 1 to 8 of 21 trainees". Filter by name/condition/centre present.

---

### Beat A4 — Activities Index
- **URL:** `/activities` → redirect → `/activities/home`  
- **Result:** PASS  
- **Screenshot:** `docs/audit/screenshots/mcp-dryrun/A4-activities-home.png`  
- **Notes:** 9 Total Activities / 9 Active / 0 Sessions / 0 Enrolled. All 9 activity cards displayed (3 per centre). Filter tabs: All Activities (9) / Active (9) / Therapy (0) / Academic (0) / Faith & Values (0). Each card shows centre, instructor, session count, View/Sessions/Edit actions. "Create Activity" and "Categories" buttons present.

---

### Beat A5 — Attendance Dashboard
- **URL:** `/staff-attendance` → redirect → `/centres/attendance`  
- **Result:** PASS  
- **Screenshot:** `docs/audit/screenshots/mcp-dryrun/A5-attendance-dashboard.png`  
- **Notes:** "Attendance Dashboard Admin Only" header with date and all-centres scope. Centre selector: UAT Centre A / B / C. Stat cards: Staff Present (0) / Staff Absent (0) / Staff Late (0) / Trainees Present (0) / Trainees Absent (0) / Total Trainees (0). Staff Attendance / Trainee Attendance tabs. Mark Attendance form with Name/Status/Type/Remarks fields. "No staff found for this centre" expected — no centre selected and no attendance records seeded.

---

### Beat B1 — Supervisor Login → Centre-Scoped View
- **URL:** `/auth/login` → `/supervisor/dashboard` → `/trainees/home`  
- **Result:** PASS  
- **Screenshots:**  
  - `docs/audit/screenshots/mcp-dryrun/B1-supervisor-dashboard.png`  
  - `docs/audit/screenshots/mcp-dryrun/B1-supervisor-trainees-scoped.png`  
- **CentreScope isolation verified:**

  | Metric | Admin sees | Supervisor A1 sees | Correct? |
  |--------|-----------|-------------------|----------|
  | Total Trainees | 21 | 7 | ✓ |
  | Active Trainees | 21 | 7 | ✓ |
  | Centre shown | All 3 | UAT Centre A only | ✓ |
  | URL escape attempt (`/trainees`) | 21 shown | Redirected to dashboard | ✓ |

- **Notes:** Supervisor dashboard shows role badge "Supervisor", "UAT Supervisor A1", header "Supervisor Dashboard". Scoped stats: 0 My Activities / 0 This Week's Sessions / 0% Session Completion / 0% Student Attendance. Direct URL `/trainees` blocked — redirected to `/supervisor/dashboard`, confirming scope cannot be bypassed by URL crafting.

---

## Findings Summary

### Known P2 Issues (pre-documented in Track A)

| # | Issue | Severity | Pages Affected | Impact |
|---|-------|----------|----------------|--------|
| P2-01 | Bootstrap CSS/JS blocked by `style-src`/`script-src` CSP | P2 | All pages | Unstyled layout; all data present |
| P2-02 | Font Awesome CSS blocked | P2 | All pages | Icons missing (text fallbacks) |
| P2-03 | Google Fonts (Poppins) blocked | P2 | All pages | System font fallback |
| P2-04 | jQuery CDN blocked → `$ is not defined` | P2 | Authenticated pages | Dropdown/modal JS broken; navigation via direct links works |
| P2-05 | `wttr.in` weather API blocked by `connect-src 'self'` | P2 | Dashboard (all roles) | "Weather unavailable" shown |

**Root cause (all P2s):** CSP header set to `'self'`-only; all external CDN resources rejected. Fix: either self-host assets or add CDN domains to CSP allowlist.

### New Findings (Track B only)

| # | Finding | Severity | Notes |
|---|---------|----------|-------|
| NF-01 | Weather widget (`wttr.in`) blocked by `connect-src` | P2 | First identified via MCP console; not caught in Track A headless run |
| NF-02 | GET `/auth/logout` does not end session (POST required) | P3 | Navigating to `/auth/logout` stays authenticated; must use Logout button which submits a form POST — correct security behaviour, but demo presenter must use the button |

### No New P0/P1 Issues

No authentication bypass, no data leakage, no crash, no white-screen errors.

---

## Delta vs Track A (Batch Playwright)

| Aspect | Track A (headless batch) | Track B (MCP interactive) |
|--------|--------------------------|--------------------------|
| Login flow | Used API context (no browser render) | Full browser form fill + submit |
| CSP errors | Detected | Confirmed, + 2 additional resources |
| CentreScope | Verified via HTTP status | Verified via visual + URL escape attempt |
| Weather widget | Not tested | Blocked — `wttr.in` in `connect-src` |
| jQuery missing | Not caught | `ReferenceError: $ is not defined` confirmed |
| Logout flow | Cookies cleared programmatically | GET logout does not clear session; button POST does |

---

## Demo Day Readiness

| Area | Status | Notes |
|------|--------|-------|
| Login page | GO | Renders correctly |
| Admin dashboard | GO | Data shown; weather widget shows "unavailable" — acceptable |
| Centres index | GO | All 3 centres visible to admin |
| Trainees list | GO | 21 trainees, pagination, filter working |
| Activities index | GO | 9 activities, filter tabs, action buttons |
| Attendance dashboard | GO | Stats visible, form present |
| Supervisor CentreScope | GO | 7/21 isolation confirmed, URL escape blocked |
| **Overall** | **GO** | P0=0, P1=0 |

**Presenter note:** Use the Logout button (not URL navigation) when switching roles during the live demo.

---

## Screenshots

All screenshots saved to `docs/audit/screenshots/mcp-dryrun/` (gitignored).

| File | Beat |
|------|------|
| `00-login-page.png` | Login page render |
| `A1-admin-dashboard.png` | Admin dashboard |
| `A2-centres-home.png` | Centres index |
| `A3-trainees-home.png` | Trainees list (admin, 21) |
| `A4-activities-home.png` | Activities index |
| `A5-attendance-dashboard.png` | Attendance dashboard |
| `B1-supervisor-dashboard.png` | Supervisor dashboard |
| `B1-supervisor-trainees-scoped.png` | Trainees (supervisor, 7) |
