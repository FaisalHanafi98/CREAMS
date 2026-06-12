# CREAMS — Deployment Inventory

> **Generated**: 2026-05-31
> **Category**: Deployment
> **Purpose**: Inventory of VPS setup, migration plans, infrastructure, Docker, nginx, CI/CD deploy pipeline, backups, and recovery.

---

## Current Deployment Target

| Source File | Evidence Type | Reason Included | Confidence | Importance |
|---|---|---|---|---|
| `docs/Validate/SOURCE_OF_TRUTH.md` | Authority | Deployment target: Amazon Lightsail (shared $5 instance with Portfolio). Deployment status: ON HOLD — gated on code-reality audit + Portfolio co-tenancy coordination. | High | Critical |
| `docs/Validate/CREAMS_SESSION_CURRENT.md` | Mission | "No production push from this session chain. Push to origin/Fixers after documentation commit is fine; production push is not." | High | Critical |
| `docs/Validate/HANDOVER_PACKAGE_2026-05-04.md` | State | "No production hosting — system runs on local dev only this sprint. Hosting is a phase-two decision." Effective: May 5, 2026. | High | Critical |
| `docs/ai-context/01_CURRENT_STATUS.md` | State | Deployment blocked at seeder. Server: creams.faisalhanafi.com. 14 uncommitted files. Goal: deploy. Date: 2026-05-08. | High | Critical |

## Deployment Scripts & Config

| Source File | Evidence Type | Reason Included | Confidence | Importance |
|---|---|---|---|---|
| `deploy.sh` | Script | 142-line production deploy: git pull, composer install --no-dev --optimize-autoloader, npm ci, npm run build, optimize, migrate --force, route:cache, config:cache. Outputs colored status per step. Hardcoded cream_prod DB. | High | High |
| `scripts/server-init.sh` | Script | 261-line Lightsail bootstrap: installs PHP 8.1, MySQL, Nginx, Composer, Node.js. Clones repo into prod/staging/dev directories. Creates databases + users. Writes .env files. Hardcoded passwords (ProdPassword123!, StagingPassword123!, DevPassword123!). Installs Certbot. | High | Critical |
| `.github/workflows/deploy.yml` | Pipeline | Manual SSH deploy on Hostinger shared hosting. Runs tests only. No automated deploy. Explicitly states: deployment is manual. | High | High |
| `creams_subdomain.conf` | Nginx Config | Routes / to /creams/uat/auth/login. /creams/{demo_id}/ routing with PHP-FPM upstream. Security headers (X-Frame-Options, X-Content-Type-Options). Static asset caching 30d. | High | High |
| `.htaccess` | Apache Config | mod_rewrite: routes everything through index.php. Excludes /public/ and /letters/ from file-serving logic. | High | Medium |

## Docker / Containerization

| Source File | Evidence Type | Reason Included | Confidence | Importance |
|---|---|---|---|---|
| `Dockerfile` | Container | PHP 8.1-FPM base. Installs pdo_mysql, mbstring, gd, zip. Copies Composer from official image. composer install --no-dev --optimize-autoloader. Sets www-data permissions for storage/bootstrap. | High | High |
| `docker-compose.yml` | Orchestration | 3 services: app (PHP-FPM, volume-mounts code), nginx (alpine, maps port 80, depends_on app), db (MySQL 8.0, port 3306, volume for persistence). Networks + healthchecks. | High | High |
| `docker/nginx/` | Nginx Config | Docker-specific nginx configuration directory. | Medium | Medium |

## Pre-Deploy Security Gate

| Source File | Evidence Type | Reason Included | Confidence | Importance |
|---|---|---|---|---|
| `docs/Validate/PRE_DEPLOY_SECURITY_CHECKLIST.md` | Gate | 9-section deployment checklist. 2 RED blockers: APP_KEY is placeholder (B1), Log::debug() leaks PII (B2). 2 YELLOW. Rest GREEN. Not cleared for production push. | High | Critical |
| `docs/Validate/PRODUCTION_DEPLOYMENT.md` | Guide | Production deployment guide (16KB). 100+ checks. Date: Feb 2026. | High | Critical |

## Staging & Seeding Policy

| Source File | Evidence Type | Reason Included | Confidence | Importance |
|---|---|---|---|---|
| `docs/04_Deployment_Guides/STAGING_SEED_POLICY.md` | Policy | Hard rule: staging/UAT/demo may ONLY run UATSeeder. 3-layer enforcement: code guard (RuntimeException for non-local IRLSeeder), deploy script (hardcodes UATSeeder), .env.staging (APP_ENV=staging + APP_DEBUG=false). Real-data exception requires written approval, throwaway instance, 24-hour destruction. | High | Critical |

## Rollback & Recovery

| Source File | Evidence Type | Reason Included | Confidence | Importance |
|---|---|---|---|---|
| `docs/archive/PRODUCTION_ROLLBACK.md` | Runbook | Rollback SOP: git reset --hard to last known-good commit, composer install --no-dev, php artisan migrate:rollback --step=1, optimize:clear, restart PHP-FPM + nginx. Sign-off form. | High | High |

## Resource Footprint

| Source File | Evidence Type | Reason Included | Confidence | Importance |
|---|---|---|---|---|
| `docs/archive/LIGHTSAIL_FOOTPRINT.md` | Profile | $5/mo Lightsail: 512MB RAM, 20GB SSD, 1 vCPU. Co-tenancy with Portfolio. PHP-FPM memory target, MySQL data size, session storage, log rotation documented. Date: 2026-04-17. Pre-L12 stack versions. | Medium | Medium |

## Superseded Deployment Guides (Archived)

| Source File | Evidence Type | Reason Included | Confidence | Importance |
|---|---|---|---|---|
| `docs/04_Deployment_Guides/DEPLOYMENT_GUIDE.md` | Guide (STALE) | 527-line Vercel deployment guide. vercel.json config, api/index.php. PlanetScale DB. Superseded — target is now Lightsail. | Medium | Low |
| `docs/archive/deployment/VERCEL_DEPLOYMENT_GUIDE.md` | Guide (STALE) | 773-line Vercel guide. Duplicate of the above. | Low | Low |
| `docs/archive/deployment/VERCEL_BACKEND_DATABASE_GUIDE.md` | Guide (STALE) | 1,127-line Vercel backend/database handling. PlanetScale schema migration. | Low | Low |
| `docs/archive/deployment/AWS_DEPLOYMENT_MIGRATION_GUIDE.md` | Guide (STALE) | 1,865-line Vercel→ECS/Fargate migration guide. 4-phase migration plan. Never executed. | Low | Low |

## Live Deployment Status (docs/audit/ + .memsearch/)

| Source File | Evidence Type | Reason Included | Confidence | Importance |
|---|---|---|---|---|
| `docs/ai-context/05_MODULE_STATUS/deployment.md` | Status | 14 deployment steps completed. 3 blocked: seeder (UATSeeder won't run), nginx (config needs update), certbot (SSL cert not yet issued). Server facts: AL2023, PHP 8.2, MariaDB. | High | Critical |
| `.memsearch/memory/2026-05-07.md` | Checkpoint | Deployment to creams.faisalhanafi.com recorded. Server IP: 54.169.32.54. composer.lock PHP version mismatch (requires 8.1, server had 8.2). | High | Critical |
| `.memsearch/memory/2026-05-12.md` | Checkpoint | Production patch groups deployed. BUG tracking during deployment. UAT smoke test post-deploy. | High | Critical |
| `docs/audit/live_uat_gate_smoke_2026-05-17.md` | Gate | Latest production gate smoke: FAIL (logout session persistence). Not cleared for stakeholder demo. | High | Critical |

---

## Deployment Summary (Synthesized from Evidence)

- **Target**: Amazon Lightsail ($5/mo, 512MB RAM, 20GB SSD, co-tenancy with Portfolio)
- **Status**: ON HOLD — deployment freeze per CREAMS_SESSION_CURRENT.md
- **Blockers**: 3 active (seeder, nginx config, certbot SSL). Plus 2 live-UAT blockers (logout, trainee creation).
- **Scripts**: deploy.sh (ready), server-init.sh (needs password cleanup). Dockerfile + docker-compose available.
- **CI/CD**: GitHub Actions runs tests only. Deployment is manual SSH.
- **Pre-deploy gate**: Not cleared. 2 RED items (APP_KEY placeholder, PII logging).
- **Rollback**: PROCEDURE_PRODUCTION_ROLLBACK.md exists.
- **Seeding**: STAGING_SEED_POLICY.md enforced for staging/UAT.
- **Superseded**: Vercel and ECS/Fargate guides archived. Target is Lightsail.

---

*Generated by automated repository exploration. Do not modify application code. Classification only.*
