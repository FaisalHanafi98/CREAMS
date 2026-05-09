# CREAMS — Stale or Conflicting Docs

**Last updated**: 2026-05-08

---

## Docs that are STALE (outdated behavior)

| Document | What it says | What's actually true | Risk |
|---|---|---|---|
| `docs/CLAUDE.md` | Laravel 10.x, PHP 8.1+, Breeze+Sanctum, Tailwind CSS | Laravel 12.58.0, PHP ^8.2, custom auth, Bootstrap 5 | MEDIUM |
| `docs/06_Status_Reports/ULTIMATE_FINAL_STATUS.md` | System "complete" at some 2025 milestone | Pre-sprint, deeply outdated | HIGH if trusted |
| `docs/PRODUCTION_DEPLOYMENT.md` | Old deployment checklist | New deployment steps needed for AL2023 | HIGH |
| `docs/04_Deployment_Guides/DEPLOYMENT_GUIDE.md` | Vercel/AWS architecture | Lightsail-based, not Vercel | HIGH |
| `CREAMS_CODEBASE_DOCUMENTATION.md` | Full codebase (pre-L10→L12) | Much has changed | MEDIUM |
| `DATABASE_SCHEMA_DOCUMENTATION.md` | Full schema (pre-2026 migrations) | 8 new migrations since writing | MEDIUM |
| `docs/archive/deployment/` | All docs in this folder | Completely superseded | LOW (archived) |

---

## Docs that CONFLICT with each other

| Topic | Doc A says | Doc B says | Recommended truth |
|---|---|---|---|
| Roles | Some docs: Admin/Manager/Staff/Caretaker/Trainee/Parent | ADR-002 and code: Admin/Supervisor/Teacher/AJK (+Trainee/Parent planned) | **Trust ADR-002** |
| Auth system | docs/CLAUDE.md: Breeze+Sanctum | routes/web.php, MainController: POST /auth/check custom | **Trust code + docs/SOURCE_OF_TRUTH.md** |
| Laravel version | docs/CLAUDE.md: 10.x | composer.json: ^12.0 | **Trust composer.json** |
| CSS framework | docs/CLAUDE.md: Tailwind | views and public/: Bootstrap 5 | **Trust views (Bootstrap 5 is deployed)** |
| Test count | docs/06_Status_Reports/: varies | TEST_BASELINE.md: 359 (May 2026) | **Trust most recent terminal output** |

---

## Docs that are PLANNED-ONLY (not yet implemented)

| Document | Claims | Reality |
|---|---|---|
| Trainee/Parent role docs | Describes Trainee and Parent login flows | Not implemented — no routes for trainee/parent self-service |
| `docs/09_New_Features/MEDIA_UPLOAD_SYSTEM.md` | Media upload feature | UNVERIFIED if fully implemented |
| `docs/09_New_Features/TOAST_NOTIFICATION_SYSTEM.md` | Toast notification | Likely implemented — UNVERIFIED |
| Various module docs claiming "complete" status | Full feature completion | Sprint audit found 60+ inaccuracies — treat module docs as INTENT not PROOF |

---

## Recommended cleanup actions (priority order)

1. **HIGH**: Update `docs/CLAUDE.md` (project overlay) with:
   - Laravel version: 12.58.0
   - PHP requirement: ^8.2
   - Auth: custom POST /auth/check
   - CSS: Bootstrap 5 (not Tailwind)
   - Roles: Admin, Supervisor, Teacher, AJK (not Manager/Staff/Caretaker)

2. **MEDIUM**: Add disclaimer to `docs/06_Status_Reports/ULTIMATE_FINAL_STATUS.md` marking it as STALE.

3. **MEDIUM**: Archive `docs/PRODUCTION_DEPLOYMENT.md` to `docs/archive/` and replace with AL2023-specific deployment notes.

4. **LOW**: Add "written before L10→L12 upgrade" note to `CREAMS_CODEBASE_DOCUMENTATION.md` header.

5. **LOW**: Delete `docs/archive/deployment/` content (Vercel/ECS guides — permanently superseded).
