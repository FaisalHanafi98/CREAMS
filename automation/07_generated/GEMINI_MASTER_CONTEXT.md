# CREAMS — Gemini Master Context

> **Purpose**: Single executive-level summary of the entire CREAMS project for an AI model that knows nothing about it.
> **Generated**: 2026-05-31
> **Source**: `automation/00_inventory/*` + `automation/04_audits/CRITICAL_FINDINGS_REGISTER.md`
> **Length**: ~6 pages

---

## 1. System Overview

CREAMS (Community-based Rehabilitation Management System) is a **Laravel 12.x** web application for managing Malaysian PPDK rehabilitation centres serving children with special needs. It handles real trainee data (names, Malaysian IC numbers, disability records) under PDPA (Personal Data Protection Act) compliance.

- **Stack**: Laravel 12.x, PHP 8.2+, MySQL 8.0+, Blade + Bootstrap 5 + jQuery, Vite
- **Context**: Gold Medal FYP, IIUM Computer Science. Production-bound but not yet deployed.
- **Scope**: Multi-centre operations with strict data isolation. 16 modules, 163 features.
- **Environment**: Local development active. Staging at `creams.faisalhanafi.com`. Production at `pdk-creams.org` (not yet live for public use).
- **Branch**: Working branch is `Fixers`. Main branch is `main`.

---

## 2. Current Architecture

### Authentication
**Custom session-based auth via `POST /auth/check`** handled by `MainController@check`. NOT Laravel Breeze + Sanctum. Session stored in MySQL `sessions` table. Session regeneration on login enforced via `SessionManager::flush()` + `Session::regenerate()`. Rate limited at 5 login attempts per minute per identifier+IP.

### Roles (4 active, 2 planned)
| Role | Status | Access |
|------|--------|--------|
| Admin | Active | Full system access, cross-centre visibility |
| Supervisor | Active | Centre-level management, can view own centre only |
| Teacher | Active | Activity instruction, trainee management within centre |
| AJK | Active | Committee member, limited centre-level access |
| Trainee | Planned (Phase 3) | Self-service dashboard |
| Parent/Guardian | Planned (Phase 3) | Guardian portal |

### Multi-Centre Data Isolation (The PDPA Boundary)
Two isolation mechanisms via `app/Models/Scopes/CentreScope.php`:
- **Mechanism 1 — CentreScope** (23 models with direct `centre_id` column): Auto-appends `WHERE centre_id = session('centre_id')` to every Eloquent query. Admin bypass (`role === 'admin'` = sees all centres). No-session skip (CLI, queue).
- **Mechanism 2 — `centre_isolation` closure scope** (2 models without `centre_id`): `AssetMaintenance`, `AssetMovement`. Isolates via `whereHas('asset', fn($q) => $q->where('assets.centre_id', $centreId))` — DB-level subquery.
- **Documented exceptions** (2 models): `Message` (controller-level isolation by sender_id), `Centre` (tenant root — cannot self-scope).

**Total scoped**: 25. **Total covered**: 27 (25 scoped + 2 exceptions).

### URL Architecture
- **Production**: Clean direct routes (`/`, `/login`, `/dashboard`, etc.). Intended domain: `pdk-creams.org`.
- **Non-production**: Prefixed routes (`/creams/{demo_id}/` prefix via `DemoInstanceMiddleware`).
- **Staging login**: `http://staging-host/creams/uat/auth/login`

### Route Architecture
629 total routes. Rate limiting on all auth endpoints. Role middleware on sensitive route groups (volunteer management = admin only, attendance = staff only, letter generation = admin/supervisor only). Security headers middleware active on web + api groups (CSP, HSTS, X-Frame-Options, X-XSS-Protection, X-Content-Type-Options, Referrer-Policy, Permissions-Policy).

---

## 3. Current Deployment State

**Status**: ON HOLD. Deployment is frozen per `CREAMS_SESSION_CURRENT.md`.

- **Target**: Amazon Lightsail ($5/mo, 512MB RAM, 20GB SSD, co-tenancy with Portfolio site)
- **Live URL**: `creams.faisalhanafi.com` (staging — not for public use)
- **Blockers**: 3 active deployment blockers (seeder won't run, nginx config needs update, certbot SSL not issued)
- **Pre-deploy security gate**: NOT CLEARED. 2 RED items: APP_KEY is placeholder, Log::debug() leaks PII in auth path logs
- **Docker**: Dockerfile + docker-compose.yml exist but deployment is via bare-metal Lightsail bootstrap script
- **Rollback**: `PRODUCTION_ROLLBACK.md` exists
- **Staging seed policy**: Only `UATSeeder` permitted. Real data (`IRLSeeder`) hard-gated to `APP_ENV=local` with 3-layer enforcement

---

## 4. Current Test Baseline

| Metric | Value | Source |
|--------|-------|--------|
| **PHPUnit tests** | 359 | `test_baseline_2026-04-30.log` |
| **Assertions** | 520 | Same |
| **Failures** | 0 | Same |
| **Errors** | 0 | Same |
| **Test files** | 37 | `TEST_BASELINE.md` |
| **Coverage estimate** | ~15-20% | `TEST_BASELINE.md` |
| **Playwright E2E** | 181/210 passing (86.2%) | `PLAYWRIGHT_FINDINGS_2026-05-04.md` |
| **Demo-flow spec** | 8/8 passing | `playwright_results.md` |

**Rule**: 359 tests is the FLOOR. No session may leave below this count.

---

## 5. Current UAT Status

**Overall**: FAIL. Two persistent blockers across all May 2026 live UAT runs.

| Blocker | Detail | All Runs |
|---------|--------|----------|
| **Logout** | Sessions persist after logout — user can access dashboard without re-authentication | FAIL (all runs) |
| **Trainee creation** | 500 error on trainee registration form submission | FAIL (all runs) |

**Live UAT timeline** (May 15-18, 2026 on `pdk-creams.org`):
- May 15: Mutation smoke — synthetic records created, all roles authenticated, CRUD tested
- May 16: Functional UAT readiness — FAIL (logout, trainee create, weather widget broken)
- May 16: Invasive UAT — RBAC and form behavior recorded. Logout + trainee create FAIL
- May 16: Rerun after patches — authenticated UAT blocked (all test accounts redirect to login). P0 access blocker
- May 16: Smoke claim verification — asset 500 fixed (confirmed). RBAC confirmed. Logout + trainee create still FAIL
- May 17: Gate smoke — all roles login, asset detail 200, no 500s. BUT logout fails = FAIL
- May 18: Full browser UAT — 3,558 controls inventoried. Same 2 blockers. FAIL
- May 18: Retest — 108 screenshots, 109 RBAC probes. FAIL
- May 18: Edge Chromium — all credentials fail from clean context. Auth blocked. FAIL

**Stakeholder demo**: NOT SAFE for stakeholder demo per May 16 invasive UAT conclusion.

**Demo script**: Ready (`DEMO_SCRIPT_2026-05-03.md`, 45 mins, 13 beats).

---

## 6. Security Posture

| Domain | Status | Evidence |
|--------|--------|----------|
| Rate limiting | Active | 9 limiters: login (5/min), password-reset (3/min+10/hr), registration (3/min+5/hr), api (60/min), dashboard (120/min), etc. |
| Security headers | Active | 7 headers via SecurityHeadersMiddleware (CSP, HSTS, X-Frame-Options, X-XSS-Protection, X-Content-Type-Options, Referrer-Policy, Permissions-Policy) |
| Centre isolation | Active | 25 scoped models. 2 mechanisms. 2 documented exceptions. Dedicated isolation tests passing. |
| CSRF | Active | Laravel default. 93 @csrf directives across 65 Blade templates. VerifyCsrfToken middleware. |
| Session security | Active | Session::flush() + Session::regenerate() on login and logout |
| Pre-commit hook | Active | Blocks secrets (DB_PASSWORD, API_KEY, AWS keys, RSA/DSA keys) and Malaysian NRIC patterns |
| OWASP | ~66-78% (various claims, partially falsified) | Feb 2026 audits claimed ~66%, later falsified by DELTA_REEVAL. Post-hardening estimate unknown. |
| Pre-deploy gate | NOT CLEARED | 2 RED blockers (CF-08, CF-10) |

---

## 7. Critical Findings Summary (Top 5)

| # | Finding | Risk | Location |
|---|---------|------|----------|
| CF-01 | Real production data backup (1 centre, 14 users, 57 assets) on disk | CRITICAL | `database/real_data_backup.json` |
| CF-02 | Live UAT screenshots expose real production email | CRITICAL | `docs/audit/screenshots/` |
| CF-03 | Hardcoded DB passwords in Lightsail bootstrap script | CRITICAL | `scripts/server-init.sh` |
| CF-04 | Two full repo copies in `.claude/worktrees/` duplicating sensitive data | CRITICAL | `.claude/worktrees/` |
| CF-12 | 2 persistent UAT blockers (logout + trainee creation) | HIGH | All May 2026 live UAT reports |

Full register: 20 findings in `automation/04_audits/CRITICAL_FINDINGS_REGISTER.md`.

---

## 8. Technical Debt Summary

| Area | Debt | Phase |
|------|------|-------|
| Performance | Trainee creation 26s (target <5s), schedule page 19.5s (target <3s), 221 N+1 queries | Phase 3 (deferred) |
| Testing | Playwright 181/210 (86.2%). 29 tests need redirect/wizard field fixes. Coverage ~15-20% | Phase 2 (deferred) |
| Architecture | Fat controllers (400-900 lines). Services underutilized. No Policy classes. 49 inline role checks. No field-level PII encryption. SoftDeletes migration exists but models may lack trait | — |
| Documentation | 6 stale docs flagged. 8 deviations registered. 15 alignment issues. Numbered folders (01-08) mostly stale | Consolidation in progress |
| Features | 14 categories of "not implemented" or "partially implemented" features | KNOWN_LIMITATIONS_2026-05-04.md |

---

## 9. Missing Artifacts (Top 5)

| # | Artifact | Priority | Why |
|---|----------|----------|-----|
| M16 | PDPA Data Handling SOP | CRITICAL | Legal compliance for IC numbers and disability records |
| M05 | Disaster Recovery Plan | CRITICAL | No backup/restore procedure for PDPA data |
| M11 | Database Backup Script | CRITICAL | Manual `mysqldump` only — no cron, no rotation, no off-site |
| M02 | Security Standard | HIGH | No codified OWASP/security baseline |
| M04 | Deployment Runbook | HIGH | deploy.sh exists but no step-by-step with health checks |

Full catalog: 16 gaps in `MISSING_ARTIFACTS.md`.

---

## 10. Project Objective

**Immediate**: Fix 2 live UAT blockers (logout session termination, trainee creation 500 error). Clear pre-deploy security gate (2 RED items). Enable stakeholder demo.

**Short-term**: Complete documentation consolidation across Validate and numbered folders. Address 4 CRITICAL PDPA findings. Stabilize Playwright tests from 86% to 98%. Run 4-role code-reality audit per `CREAMS_SESSION_CURRENT.md`.

**Medium-term**: Deploy to Lightsail (coordinated with Portfolio co-tenancy). Phase 2 (test infrastructure to 70% coverage). Phase 3 (performance optimization). External security audit. Pilot rollout — one centre, real users, real data.

**Long-term**: Trainee/Parent self-service portals. MFA. Mobile-first redesign. Production CI/CD pipeline. git history rewrite to remove 72 historical IC patterns.

---

## 11. Key Files for AI Context

| Priority | File | Why |
|----------|------|-----|
| P1 | `docs/Validate/SOURCE_OF_TRUTH.md` | Documentation router — tells you what to trust |
| P1 | `docs/Validate/CREAMS_SESSION_CURRENT.md` | Active mission + DO-NOTs |
| P1 | `docs/Validate/MULTI_CENTRE_ISOLATION.md` | The PDPA boundary — read before touching any model |
| P1 | `docs/audit/test_baseline_2026-04-30.log` | You cannot drop below 359 tests |
| P2 | `docs/Validate/HANDOVER_PACKAGE_2026-05-04.md` | Stakeholder view of what works |
| P2 | `docs/Validate/KNOWN_LIMITATIONS_2026-05-04.md` | What is NOT implemented (honest) |
| P2 | `automation/04_audits/CRITICAL_FINDINGS_REGISTER.md` | 20 security/PDPA findings |
| P3 | `docs/Validate/TEST_BASELINE.md` | Test floor and history |
| P3 | `docs/Validate/PRE_DEPLOY_SECURITY_CHECKLIST.md` | Gate status (2 RED, 2 YELLOW) |
| P3 | `CLAUDE.md` (root) | Root governance — role definitions, PDPA rules, commit SOP |

---

## 12. What NOT to Trust

- `docs/06_Status_Reports/*` files with "FINAL"/"COMPLETE"/"ULTIMATE" in names — misleading demo-era snapshots
- `docs/Validate/MASTER_PROGRESS_LOG.md` — frozen Feb 2026, claims stale metrics
- `docs/Validate/PRODUCTION_READINESS_ROADMAP.md` — falsified claims per DELTA_REEVAL
- `docs/Validate/CREAMS_CODEBASE_DOCUMENTATION.md` — Dec 2025, pre-CentresScope
- `docs/archive/CLAUDE.md` — claims wrong auth stack (Breeze+Sanctum), wrong roles
- Old CLAUDE.md copies — say Laravel 10, PHP 8.1, Tailwind (all wrong)
- Any document claiming "329 tests" or "13% coverage" — stale metrics

---

*Generated from 295 catalogued entries across 12 inventories. No speculation. Evidence only.*
