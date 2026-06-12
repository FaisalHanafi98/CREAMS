# CREAMS — Current Security State

> **Source**: `SECURITY_INVENTORY.md`, `CRITICAL_FINDINGS_REGISTER.md`, `DELTA_REEVAL_REPORT_2026-03-22.md`, `PRE_DEPLOY_SECURITY_CHECKLIST.md`
> **Date**: 31 May 2026
> **Rule**: Only from security inventory and critical findings register. No new findings.

---

## Active Exploitable Risks

### CRITICAL

- **Real production data on disk** — `database/real_data_backup.json`: 1 real centre (Gombak), 14 users, 57 assets. Unclear if git-tracked. If committed, history rewrite required. Source: CF-01.
- **Live UAT screenshots with real email** — `lakshmi.krishnan@iium.edu.my` visible in gate-results.json from pdk-creams.org. Multiple JSON files under `docs/audit/screenshots/` contain full redirect chains and set-cookie headers. Source: CF-02.
- **Hardcoded MySQL passwords in deploy script** — `scripts/server-init.sh` creates 3 users with ProdPassword123!, StagingPassword123!, DevPassword123!. Unknown if ever deployed to live server. Source: CF-03.
- **Two full repo copies in worktrees** — `.claude/worktrees/` contains 2 full copies of repo including `real_data_backup.json` and `.env` variants. Source: CF-04.

### HIGH

- **Session PII logged on every authenticated request** — `MainController.php` 10+ `Log::debug()` calls with `session()->all()` (email, IIUM ID). Safe only if `LOG_LEVEL=warning`. Also in `NotificationController.php` and `TraineeHomeController.php`. Source: CF-08.
- **APP_KEY placeholder in .env.production** — `base64:GENERATE_NEW_KEY_WITH_php_artisan_key:generate`. Sessions and CSRF will not work. Source: CF-10.
- **`.env.testing` with real DB password** — `DB_PASSWORD=[REDACTED-CF03]`. Flagged Mar 2026. Rotation status unknown. Source: CF-11.
- **Overly permissive CORS** — `allowed_origins: ['*']`, all methods, all headers. Low risk now (session auth), high risk if API tokens added. Source: CF-09.
- **72 IC patterns in git history** — 131 commits scanned. Requires BFG rewrite. Deferred to post-delivery. Source: `git_history_audit_2026-05-01.log`.
- **IRLSeeder real data present** — 900 lines, 3-layer env gate. Physically present. GombakDataExtractor can regenerate backup. Source: CF-06, CF-17.

### MEDIUM

- **Hardcoded ICs in TestingGuideDataSeeder** — literal `######-##-####` strings. Source: CF-18.
- **4 old .env files with APP_KEYs** — archive copies. Unknown if any key matches live key. Source: CF-05.
- **No `.gitignore` coverage verified for real_data_backup.json** — git tracking status unknown. Source: CF-19.

---

## PDPA Risks

| # | Risk | Severity | Location | Evidence |
|---|------|----------|----------|----------|
| 1 | Real production backup on disk | CRITICAL | `database/real_data_backup.json` | CF-01 |
| 2 | Live UAT screenshots with real email | CRITICAL | `docs/audit/screenshots/` | CF-02 |
| 3 | Worktree copies duplicating real data | CRITICAL | `.claude/worktrees/` | CF-04 |
| 4 | Session PII in debug logs | HIGH | `MainController.php` | CF-08 |
| 5 | IRLSeeder real data with env gate | HIGH | `database/seeders/IRLSeeder.php` | CF-06 |
| 6 | 72 IC patterns in git history | HIGH | Git history (Fixers branch) | `git_history_audit_2026-05-01.log` |
| 7 | GombakDataExtractor can regenerate backup | MEDIUM | `database/seeders/GombakDataExtractor.php` | CF-17 |
| 8 | Hardcoded ICs in TestingGuideDataSeeder | MEDIUM | `database/seeders/TestingGuideDataSeeder.php` | CF-18 |
| 9 | No verified .gitignore for real_data_backup.json | MEDIUM | Repo root | CF-19 |

---

## Credential Exposure Risks

| # | Risk | Severity | Location | Evidence |
|---|------|----------|----------|----------|
| 1 | Hardcoded MySQL passwords in deploy script | CRITICAL | `scripts/server-init.sh` | CF-03 |
| 2 | `.env.testing` real DB password | HIGH | `.env.testing` | CF-11 |
| 3 | 4 archive `.env` files with APP_KEYs | MEDIUM | `archive/cream/.env` etc. | CF-05 |
| 4 | APP_KEY placeholder in production | HIGH | `.env.production` | CF-10 |

---

## Misconfiguration Risks

| # | Risk | Severity | Location | Evidence |
|---|------|----------|----------|----------|
| 1 | CORS `allowed_origins: *` | HIGH | `config/cors.php` | CF-09 |
| 2 | APP_KEY placeholder | HIGH | `.env.production` | CF-10 |
| 3 | Session encryption disabled | MEDIUM | `config/session.php` (SESSION_ENCRYPT=false) | `SECURITY_INVENTORY.md` |
| 4 | Sanctum config dormant but misleading | LOW | `config/sanctum.php` | CF-20 |
| 5 | SQLite CI vs MySQL local divergence | MEDIUM | `phpunit-ci.xml` vs `phpunit.xml` | CF-15 |

---

## Security Defenses (Active)

- Rate limiting: 9 limiters (login 5/min, password-reset 3/min+10/hr, registration 3/min+5/hr, api 60/min, dashboard 120/min, etc.) — `RouteServiceProvider.php` lines 35-107.
- Security headers: 7 headers (CSP, HSTS, X-Frame-Options, X-XSS-Protection, X-Content-Type-Options, Referrer-Policy, Permissions-Policy) — `SecurityHeadersMiddleware.php`.
- CentreScope: 25 models scoped (23 direct + 2 closure). 2 documented exceptions. Dedicated isolation tests passing.
- CSRF: 93 `@csrf` directives across 65 Blade templates. `VerifyCsrfToken` active.
- Session security: flush + regenerate on login/logout — `SessionManager.php`.
- Pre-commit hook: blocks DB_PASSWORD, API_KEY, API_SECRET, AWS keys, RSA/DSA keys, Malaysian NRIC patterns.
- Password policy: 12+ chars, mixed case, numbers, special characters — `AppServiceProvider.php` `Password::defaults()`.
- Bcrypt hashing: `config/hashing.php`.
- STAGING_SEED_POLICY: 3-layer enforcement for UAT/staging. IRLSeeder hard-gated to local.

---

## Pre-Deploy Gate Status

Source: `PRE_DEPLOY_SECURITY_CHECKLIST.md` (2026-04-17).

| Section | Status | Items |
|---------|--------|-------|
| 1. Multi-Centre Isolation | GREEN | 5/5 |
| 2. Auth Rate Limiting | GREEN | 7/7 |
| 3. Session Security | GREEN | 3/3 |
| 4. Environment Config | **1 RED** | APP_KEY placeholder (B1) |
| 5. Debug/Logging Leaks | **1 YELLOW** | Log::debug() PII (B2) |
| 6. CSRF Protection | YELLOW | 93 @csrf vs 141 forms |
| 7. Secrets & PII | GREEN | 5/5 |
| 8. Input Validation | GREEN | 3/3 |
| 9. Access Control | GREEN | 3/3 |

**NOT CLEARED FOR PRODUCTION** — 1 RED, 2 YELLOW items remaining.
