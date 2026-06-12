# CREAMS — Current System State

> **Source**: `PROJECT_STATE_INVENTORY.md`, `SMOKE_TEST_INVENTORY.md`, `UAT_INVENTORY.md`, `ARCHITECTURE_INVENTORY.md`
> **Date**: 31 May 2026
> **Rule**: Only confirmed evidence. No speculation.

---

## What Is Working (Verified)

- Custom session-based auth via `POST /auth/check` — SessionManager flush+regenerate on login/logout
- 4 role accounts operational (Admin, Supervisor, Teacher, AJK)
- Role-based dashboards loading with per-role statistics
- PHPUnit test suite: 359 tests, 520 assertions, 0 failures (floor)
- CentreScope isolation: 25 models scoped (23 direct + 2 closure), dedicated isolation tests passing
- Rate limiting: 9 limiters active on all auth endpoints
- Security headers: 7 headers deployed (CSP, HSTS, X-Frame-Options, X-XSS-Protection, X-Content-Type-Options, Referrer-Policy, Permissions-Policy)
- 93 `@csrf` directives across 65 Blade templates
- Pre-commit hook active: blocks secrets, Malaysian IC patterns
- GitHub Actions CI: PHP 8.1 + MySQL 8.0, tests run on push/PR
- Docker: Dockerfile + docker-compose.yml available
- Staging server: creams.faisalhanafi.com reachable
- 8 user manuals v2.0 (May 2026) re-baselined against actual system
- UATSeeder: Faker-only anonymised data, 3 centres, 16 staff, 21 trainees, 9 activities
- IRLSeeder: 3-layer env gate (APP_ENV=local, APP_DEBUG=false, code guard)
- STAGING_SEED_POLICY.md: enforced
- HANDOVER_PACKAGE_2026-05-04.md: delivered
- KNOWN_LIMITATIONS_2026-05-04.md: published
- Documentation consolidation: complete (88 active files, 773 archived)
- AI context archive (ai-context/): 21 files, current

---

## What Is Partially Working

- Playwright E2E: 181/210 passing (86.2%) — 29 tests need redirect/wizard fixes. Demo-flow spec: 8/8.
- PHPUnit CI: uses SQLite :memory: while local uses MySQL `cream_test` — divergence may hide edge cases
- Dashboard statistics caching: partially implemented, not all queries cached
- Activity templates UI: `activity_schedule_templates` table exists, UI incomplete
- Recurring session exceptions: sessions can be deleted individually, no "exception to recurring rule" UI
- Activity prerequisites: `activity_prerequisites` table exists, UI minimal
- Cross-centre analytics: admin can switch contexts, no built-in compare-two-centres view
- PDF report templates: DomPDF generates basic layouts, not styled
- Cached dashboard statistics: some queries cached, not all
- Vite production build: config exists, path needs verification

---

## What Is Broken (Confirmed)

- **Logout session persistence**: Sessions persist after logout on pdk-creams.org — user can re-access dashboard without credentials. Confirmed by all May 15-18 live UAT runs. Source: `live_uat_gate_smoke_2026-05-17.md`, `full_browser_uat_report_2026-05-18.md`.
- **Trainee creation 500 error**: Registration form submission fails on pdk-creams.org. Confirmed by all May 15-18 live UAT runs. Source: `live_functional_uat_readiness_2026-05-16.md`, `full_browser_uat_report_2026-05-18.md`.
- **5 PHPUnit test failures** (local, as of May 8): All caused by single `demo_demo_route()` typo — not real regressions. 354/359 pass. Source: `test_commands.md`.
- **3 deployment blockers**: UATSeeder won't run on server, nginx config needs update, certbot SSL not yet issued. Source: `deployment.md` (ai-context).
- **Log::debug() PII leak**: `MainController.php` logs `session()->all()` (email, IIUM ID) at debug level in 10+ locations. Safe only if production `LOG_LEVEL=warning`. Source: `PRE_DEPLOY_SECURITY_CHECKLIST.md` item 5.3, `DELTA_REEVAL_REPORT_2026-03-22.md` item C2.
- **APP_KEY placeholder**: `.env.production` contains `base64:GENERATE_NEW_KEY_WITH_php_artisan_key:generate`. Source: `PRE_DEPLOY_SECURITY_CHECKLIST.md` item 4.3.
- `database/real_data_backup.json`: Real production data on disk (1 centre, 14 users, 57 assets). Source: `CRITICAL_FINDINGS_REGISTER.md` CF-01.
- `scripts/server-init.sh`: Hardcoded MySQL passwords (ProdPassword123!, StagingPassword123!, DevPassword123!). Source: `CRITICAL_FINDINGS_REGISTER.md` CF-03.
- `.claude/worktrees/`: Two full repo copies duplicating `real_data_backup.json` and `.env` files. Source: `CRITICAL_FINDINGS_REGISTER.md` CF-04.
- Live UAT screenshots: expose real production email (`lakshmi.krishnan@iium.edu.my`). Source: `CRITICAL_FINDINGS_REGISTER.md` CF-02.

---

## What Is Unknown or Inconsistent

- UNKNOWN: whether `real_data_backup.json` is tracked by git. Requires `git ls-files -- database/real_data_backup.json`. Source: CF-19.
- UNKNOWN: whether hardcoded passwords in `server-init.sh` were ever deployed to a live server. Requires checking Lightsail MySQL user list. Source: CF-03.
- UNKNOWN: whether `GombakDataExtractor.php` was ever executed post-hardening. Source: CF-17.
- UNKNOWN: whether archive `.env` APP_KEYs match any live key. Source: CF-05.
- UNKNOWN: full nature of 14 uncommitted working-tree files on Fixers branch (as of May 8). Requires `git diff`. Source: `01_CURRENT_STATUS.md`.
- UNKNOWN: current OWASP compliance percentage after hardening waves. Feb 2026 claims 66-78% but partially falsified. No post-April measurement. Source: `SECURITY_INVENTORY.md`.
- UNKNOWN: whether SoftDeletes trait is applied to Trainee, Staff, ActivitySession models. Migration exists. DELTA_REEVAL (Mar 2026) flagged as C3 — missing trait. Status after Apr 2026 hardening unknown. Source: `DELTA_REEVAL_REPORT_2026-03-22.md`.
- UNKNOWN: whether `config/sanctum.php` is truly unused or referenced by any code path. Source: `INVENTORY_COMPLETION_REPORT.md` unresolved question.
- UNKNOWN: whether `TestingGuideDataSeeder` literal IC numbers have been replaced with Faker. Source: CF-18.
- 6 memory checkpoint stubs (May 3, 10, 14, 15, 18, 19) are empty — context lost. Source: CF-13.
