# CREAMS — Current Deployment State

> **Source**: `DEPLOYMENT_INVENTORY.md`, `CRITICAL_FINDINGS_REGISTER.md`, `SECURITY_INVENTORY.md`, `ai-context/05_MODULE_STATUS/deployment.md`
> **Date**: 31 May 2026
> **Rule**: Only confirmed deployment evidence.

---

## VPS Readiness

| Component | Status | Detail | Source |
|-----------|--------|--------|--------|
| Target | Lightsail $5/mo | 512MB RAM, 20GB SSD, 1 vCPU, co-tenancy with Portfolio | `LIGHTSAIL_FOOTPRINT.md` |
| Server | Amazon Linux 2023 | PHP 8.2, MariaDB | `deployment.md` (ai-context) |
| Reachable | Yes | creams.faisalhanafi.com | `deployment.md` |
| PHP | 8.2 installed | composer.lock required PHP 8.1 — version mismatch was a blocker | `2026-05-07.md` (memsearch) |
| nginx | Config exists but needs update | creams_subdomain.conf routes / to /creams/uat/auth/login | `creams_subdomain.conf` |
| SSL | NOT ISSUED | certbot blocked — certbot --apache attempted on nginx server (failed approach #7) | `deployment.md`, `failed_attempts.md` |
| MySQL | Running | MariaDB variant | `deployment.md` |
| Domain | pdk-creams.org | Production domain. creams.faisalhanafi.com for staging | `creams_subdomain.conf` |

---

## Deployment Blockers

| # | Blocker | Detail | Source |
|---|---------|--------|--------|
| 1 | UATSeeder won't run on server | Seeder execution fails on Lightsail | `deployment.md` |
| 2 | nginx config needs update | Current config routes to /creams/uat/auth/login — needs clean routing | `deployment.md` |
| 3 | certbot SSL not issued | certbot --apache attempted on nginx (failed approach). Must use certbot --nginx | `failed_attempts.md` #7 |
| 4 | composer.lock PHP version mismatch | Requires 8.1, server has 8.2 | `2026-05-07.md` |

---

## CI/CD State

| Component | Status | Detail | Source |
|-----------|--------|--------|--------|
| CI | Active | GitHub Actions: PHP 8.1 + MySQL 8.0. Tests on push/PR to main/dev/Fixers | `ci.yml` |
| Deploy | Manual only | SSH to Hostinger shared hosting. No automated deploy. `deploy.yml` runs tests only. | `deploy.yml` |
| Docker | Available but unused | Dockerfile + docker-compose.yml exist. Deploy is bare-metal via server-init.sh | `Dockerfile`, `docker-compose.yml` |
| deploy.sh | Ready | 142-line production deploy script: git pull, composer install --no-dev, npm build, optimize, migrate --force | `deploy.sh` |

---

## server-init.sh Risks

| # | Risk | Severity | Detail | Source |
|---|------|----------|--------|--------|
| 1 | Hardcoded MySQL passwords | CRITICAL | ProdPassword123!, StagingPassword123!, DevPassword123! in plaintext. Creates 3 users with full privileges. | CF-03 |
| 2 | Unknown deployment history | HIGH | Unclear if script was ever run on live server. If yes, passwords must be rotated immediately. | CF-03 |
| 3 | Writes .env files with hardcoded creds | HIGH | Script generates .env files containing passwords to disk | CF-03 |
| 4 | Installs Certbot | MEDIUM | Previous attempt used wrong command (--apache vs --nginx) | `failed_attempts.md` #7 |

---

## Environment Configuration Issues

| # | Issue | Severity | Detail | Source |
|---|-------|----------|--------|--------|
| 1 | APP_KEY placeholder | HIGH | `.env.production`: `base64:GENERATE_NEW_KEY_WITH_php_artisan_key:generate` | CF-10 |
| 2 | LOG_LEVEL not set to warning | HIGH | Log::debug() PII leak in auth path. Safe only if production LOG_LEVEL=warning | CF-08 |
| 3 | `.env.testing` real password | HIGH | `DB_PASSWORD=[REDACTED-CF03]`. Rotation status unknown. | CF-11 |
| 4 | Multiple .env variants | MEDIUM | .env.example, .env.production, .env.testing, .env.testing.example, .env.professional, .env.mysql.backup — no comprehensive reference doc | `MISSING_ARTIFACTS.md` M12 |
| 5 | CORS overly permissive | HIGH | `allowed_origins: *` in config/cors.php | CF-09 |

---

## Staging Seed Policy

- Enforced: staging/UAT may ONLY run UATSeeder (`--class=UATSeeder`)
- 3-layer enforcement: code guard (RuntimeException), deploy script (hardcoded), .env.staging (APP_ENV=staging + APP_DEBUG=false)
- IRLSeeder hard-gated to APP_ENV=local
- Source: `STAGING_SEED_POLICY.md`

---

## Rollback

- Procedure exists: `PRODUCTION_ROLLBACK.md` — git reset --hard, composer install, migrate:rollback, optimize:clear, restart PHP-FPM + nginx
- Sign-off form included
- Source: `archive/PRODUCTION_ROLLBACK.md`

---

## Deployment Freeze

- OFFICIALLY ON HOLD per `CREAMS_SESSION_CURRENT.md` (2026-04-24)
- Gated on: code-reality audit + Portfolio co-tenancy coordination
- No production push permitted from current session chain
- Push to origin/Fixers after documentation commits is fine; production push is not
- Source: `CREAMS_SESSION_CURRENT.md`
