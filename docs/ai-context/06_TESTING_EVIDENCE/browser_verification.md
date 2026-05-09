# CREAMS — Browser Verification Evidence

**Last updated**: 2026-05-08

---

## Verified role flows (Laravel 12, 2026-05-07)

All 4 role flows verified in Chrome via Playwright MCP on `http://localhost:8000`.

| Role | Login | Dashboard | Key page | Console errors |
|---|---|---|---|---|
| Admin | PASS | PASS — 6 staff, 7 trainees, 3 activities | Activities: 45 sessions, 45 enrolled | 0 |
| Supervisor | PASS | PASS — role badge, weather, live data | Trainees: 7 (scoped, not 21) | 0 |
| Teacher | PASS | PASS — 3 activities, 75% completion | Activities: 3 (Centre A only) | 0 |
| AJK | PASS | PASS — AJK-specific widgets | — | 0 |

**CentreScope isolation confirmed**: Supervisor and Teacher see centre-scoped data. URL escape blocked (navigating to `/admin/dashboard` as Supervisor → blocked).

---

## Playwright MCP dry-run beats (2026-05-04)

8/8 demo beats passed:
- Login page, Admin dashboard, Centres index, Trainees list, Activities index, Attendance dashboard, Supervisor dashboard, CentreScope isolation

Full report: `docs/audit/PLAYWRIGHT_MCP_DRYRUN_2026-05-04.md`
Screenshots: `docs/audit/screenshots/mcp-dryrun/` (gitignored)

---

## L13 smoke test (2026-05-06, after upgrade)

All 4 role flows repeated on Laravel 12.58.0:
- Admin, Supervisor, Teacher, AJK — all PASS
- CentreScope isolation confirmed on L12

---

## Known browser issues (not yet verified as fixed)

| Issue | Status | Expected fix |
|---|---|---|
| Profile tabs don't switch | UNVERIFIED FIX | data-bs-toggle migration pending |
| Avatar upload | UNVERIFIED | jQuery timing not confirmed |
| Search links expire | UNVERIFIED | EncryptionHelper migration pending |
| `creams.faisalhanafi.com` loads | NOT TESTED | Deployment not yet complete |

---

## Browser verification to run after deployment

```
[ ] https://creams.faisalhanafi.com/auth/login loads with styling (0 CSP errors)
[ ] Admin login → /admin/dashboard (stats visible)
[ ] Supervisor login → /supervisor/dashboard (7 trainees, CentreScope)
[ ] Teacher login → /teacher/dashboard (3 activities, Centre A only)
[ ] AJK login → /ajk/dashboard
[ ] Logout button works (no redirect loop)
[ ] Supervisor → /admin/dashboard directly → blocked (403 or redirect)
[ ] Activities list shows real session/enrollment counts
[ ] Vite assets load (no 404 on .css/.js)
[ ] https://faisalhanafi.com still returns 200 (Portfolio unaffected)
```
