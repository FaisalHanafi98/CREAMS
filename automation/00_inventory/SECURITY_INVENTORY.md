# CREAMS — Security Inventory

> **Generated**: 2026-05-31
> **Category**: Security
> **Purpose**: Inventory of security-related evidence including authentication, authorization, vulnerability findings, OWASP compliance, PDPA protection, and rate limiting.

---

## Authentication & Session Security

| Source File | Evidence Type | Reason Included | Confidence | Importance |
|---|---|---|---|---|
| `routes/auth.php` | Routes | 66 lines: register (throttle:registration), login (throttle:login), forgot-password, reset-password, email verification. Breeze-style controller structure. | High | High |
| `routes/web.php` | Routes | `/auth/check` (POST) is the primary login endpoint. Custom session auth handled by MainController. Rate limited: throttle:login (5/min). | High | Critical |
| `config/auth.php` | Config | Single 'web' session guard. Eloquent provider to App\Models\User. Comments note "Changed from Users to User" — simplified from multi-guard. | High | High |
| `config/session.php` | Config | File driver. 120min lifetime. No encryption (SESSION_ENCRYPT=false). same_site=lax. http_only=true. | High | High |
| `config/hashing.php` | Config | Bcrypt with configurable rounds. Standard Laravel hashing. | High | Medium |
| `app/Http/Controllers/Auth/LoginController.php` | Controller | Login handler. Session regeneration via SessionManager::login(). Uses Hash::check(). | High | Critical |
| `app/Services/SessionManager.php` | Service | Session lifecycle: flush() + regenerate() on login. regenerate() also on logout. Source of truth for session security. | High | Critical |
| `app/Http/Controllers/MainController.php` | Controller | Session regeneration added (line 420: $request->session()->regenerate()). PII logged via Log::debug() at 10+ locations — flagged YELLOW in pre-deploy checklist. | High | High |

## Rate Limiting

| Source File | Evidence Type | Reason Included | Confidence | Importance |
|---|---|---|---|---|
| `app/Providers/RouteServiceProvider.php` | Provider | Comprehensive rate limiters: login (5/min via env), password-reset (3/min + 10/hr), registration (3/min + 5/hr), api (60/min), dashboard (120/min), dashboard-updates (30/min), dashboard-refresh (10/min), export (5/min), admin-actions (20/min). Lines 35-107. | High | Critical |

## Centre Isolation (PDPA Boundary)

| Source File | Evidence Type | Reason Included | Confidence | Importance |
|---|---|---|---|---|
| `docs/Validate/MULTI_CENTRE_ISOLATION.md` | Architecture | 25 scoped models (23 CentreScope direct + 2 closure via asset relationship). 2 exceptions (Message, Centre) with documented rationale. Last updated: 2026-04-25. | High | Critical |
| `app/Models/Scopes/CentreScope.php` | GlobalScope | Auto-appends WHERE centre_id = session('centre_id') to every Eloquent query on scoped models. Admin bypass at line 22. No-session skip at line 17. | High | Critical |
| `app/Http/Middleware/CentreAccessControl.php` | Middleware | Resource-specific access: activity, trainee, asset, centre. Admin bypasses all. Users without centre blocked. Audit logging for all access attempts/denials. | High | Critical |
| `tests/Feature/Security/MessageCentreIsolationTest.php` | Test | Verifies Centre A user cannot read Centre B messages. Added 2026-04-17. | High | High |
| `tests/Feature/Security/AssetMaintenanceCentreIsolationTest.php` | Test | Verifies closure-scoped AssetMaintenance isolation. Added 2026-04-25. | High | High |
| `tests/Feature/Security/AssetMovementCentreIsolationTest.php` | Test | Verifies closure-scoped AssetMovement isolation. Added 2026-04-25. | High | High |

## Security Headers & CSRF

| Source File | Evidence Type | Reason Included | Confidence | Importance |
|---|---|---|---|---|
| `app/Http/Middleware/SecurityHeadersMiddleware.php` | Middleware | 7 security headers: X-Frame-Options, CSP, HSTS, X-XSS-Protection, X-Content-Type-Options, Referrer-Policy, Permissions-Policy. Registered in web + api middleware groups. | High | Critical |
| `app/Http/Middleware/VerifyCsrfToken.php` | Middleware | Laravel default CSRF protection on all web routes. | High | High |
| `creams_subdomain.conf` | Nginx Config | Security headers at nginx level: X-Frame-Options: SAMEORIGIN, X-Content-Type-Options: nosniff. Static asset caching 30d. | High | Medium |

## Pre-Deploy Security Gate

| Source File | Evidence Type | Reason Included | Confidence | Importance |
|---|---|---|---|---|
| `docs/Validate/PRE_DEPLOY_SECURITY_CHECKLIST.md` | Checklist | 9-section gate: isolation (GREEN), rate limiting (GREEN), sessions (GREEN), env config (1 RED: APP_KEY placeholder), debug leaks (1 YELLOW: Log::debug PII), CSRF (YELLOW), PII in code (GREEN), input validation (GREEN), access control (GREEN). | High | Critical |

## Security Audit Reports

| Source File | Evidence Type | Reason Included | Confidence | Importance |
|---|---|---|---|---|
| `docs/Validate/SECURITY_AUDIT_REPORT.md` | Audit | Feb 2026: 981 lines. OWASP 66%. 3 CRITICAL (XSS in letters-archive, env credentials, session dumping). Several claims later FALSIFIED by DELTA_REEVAL (Mar 2026). | Medium | High |
| `docs/Validate/DELTA_REEVAL_REPORT_2026-03-22.md` | Audit | Mar 2026: 304 lines. Falsified claims in earlier audits. 4 CRITICAL (XSS unfixed, session logging, SoftDeletes missing, no CentreScope), 8 HIGH, 6 MEDIUM. Retrofit plan: Wave 0-4. | High | Critical |
| `docs/Validate/SECURITY_BASELINE_SCAN_METHODOLOGY.md` | Methodology | Feb 2026: 33KB. OWASP Top 10 scan methodology. 73% compliance at time of writing. | Medium | Medium |
| `docs/archive/API_ENDPOINT_SECURITY_INVENTORY.md` | Audit | Feb 2026: 231 routes analyzed. 68% OWASP compliant. 3 critical, 8 medium. Pre-L12 upgrade. Pre-sprint. | Medium | Medium |

## PDPA & Secrets

| Source File | Evidence Type | Reason Included | Confidence | Importance |
|---|---|---|---|---|
| `.githooks/pre-commit` | Gate | Blocks commits with: DB_PASSWORD, API_KEY, API_SECRET, AWS keys, RSA/DSA/EC private keys, Malaysian NRIC patterns (######-##-####). | High | Critical |
| `docs/audit/pdpa_scan_2026-05-01.log` | Scan | PDPA compliance: IRLSeeder uses placeholder ICs (XXXXXX-XX-XXXX). UATSeeder uses Faker-generated fake ICs. Clean verdict. | High | Critical |
| `docs/audit/git_history_audit_2026-05-01.log` | Scan | 131 commits scanned: 72 IC patterns found in git history. 0 .env files committed. 0 real AWS keys. Deferred cleanup. | High | Critical |
| `docs/04_Deployment_Guides/STAGING_SEED_POLICY.md` | Policy | 3-layer enforcement: code guard (RuntimeException for non-local), deploy script (hardcodes UATSeeder), .env.staging (APP_ENV=staging + APP_DEBUG=false). | High | Critical |
| `config/cors.php` | Config | Overly permissive: allowed_origins: ['*'], all methods, all headers. Low risk while session-based auth only. Risky if API tokens added. | High | High |
| `database/real_data_backup.json` | **PDPA RISK** | 1,801 lines. Real production data: 1 centre (Gombak), 14 users, 57 assets. On disk outside .gitignore. Flagged CF-01 in CRITICAL_FINDINGS_REGISTER. | High | Critical |
| `database/seeders/IRLSeeder.php` | Seeder | 900 lines. Real PDPA data for Gombak centre. 3-layer env gate. Flagged CF-06 in CRITICAL_FINDINGS_REGISTER. | High | High |
| `scripts/server-init.sh` | Script | 261 lines. Hardcoded MySQL passwords (ProdPassword123! etc.) — flagged CF-03 in CRITICAL_FINDINGS_REGISTER. | High | Critical |
| `.env.testing` | Config | Contains real DB password ([REDACTED-CF03]). Flagged CF-11 in CRITICAL_FINDINGS_REGISTER. H6 in DELTA_REEVAL. | High | High |

## Architecture Decisions (Security)

| Source File | Evidence Type | Reason Included | Confidence | Importance |
|---|---|---|---|---|
| `docs/archive/ADR-002-six-role-rbac.md` | ADR | 6-role RBAC: Admin, Supervisor, Teacher, AJK active (4/6). Trainee/Parent planned. Maps to PPDK org chart. Date: 2025-01-01. Still authoritative. | High | Critical |

---

## Security Status Summary (Synthesized from Evidence)

- **OWASP**: ~66-78% range claimed in Feb 2026. Delta report falsified specific claims (XSS still unfixed at time, debug routes present, password min:8 not min:12).
- **CentreScope**: 25 scoped models (23 direct + 2 closure). All verified by dedicated isolation tests. 2 documented exceptions.
- **Rate Limiting**: Active on all auth endpoints (login 5/min, password-reset 3/min+10/hr, registration 3/min+5/hr).
- **Security Headers**: 7 headers deployed via SecurityHeadersMiddleware. CSP, HSTS, X-Frame-Options, etc.
- **Pre-Deploy Gate**: 2 RED blockers (APP_KEY placeholder, Log::debug PII leakage). 2 YELLOW. Not cleared for production.
- **PDPA Risks**: real_data_backup.json on disk. 72 IC patterns in git history. Hardcoded passwords in server-init.sh. 4 old .env files with APP_KEYs. 2 .claude/worktrees duplicating sensitive data.
- **Secrets Gate**: Pre-commit hook active. Blocks IC patterns and secret patterns.

---

*Generated by automated repository exploration. Do not modify application code. Classification only.*
