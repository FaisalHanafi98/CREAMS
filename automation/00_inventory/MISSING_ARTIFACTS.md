# CREAMS — Missing Artifacts

> **Generated**: 2026-05-31
> **Category**: Gap Analysis
> **Purpose**: Identify genuinely missing assets that would normally exist in a mature Laravel project. Based on comparison of discovered evidence against what is expected for production-bound systems.

---

| # | Missing Artifact | Why It Would Be Valuable | Expected Location |
|---|-----------------|------------------------|-------------------|
| M01 | **Coding Standard** (`.php-cs-fixer.php` or `pint.json`) | Pint is referenced in CI workflow but no project-specific coding standard config file exists. AI agents and new developers have no codified rules to auto-format against. Would enforce consistent style across 54 models, 40+ controllers, and 100+ Blade templates. | `pint.json` at repo root |
| M02 | **Security Standard** | OWASP compliance is measured (66-85% range variously claimed) but no codified security baseline document exists. New developers/agents would not know: required password complexity, session security expectations, XSS prevention patterns, API authentication requirements, or encryption-at-rest policy. | `docs/03_Technical_Guides/SECURITY_STANDARD.md` |
| M03 | **Technical Debt Register** | Technical debt is scattered across 6+ files (KNOWN_LIMITATIONS, unresolved_bugs, failed_attempts, MASTER_PROGRESS_LOG Phase 2-4, DELTA_REEVAL retrofit plan, 16 historical fix logs). No single prioritized register with severity, effort, and owner columns exists. Would enable triage during maintenance sprints. | `docs/archive/technical_debt_register.md` or `automation/04_audits/TECHNICAL_DEBT_REGISTER.md` |
| M04 | **Deployment Runbook** | `deploy.sh` exists but no step-by-step runbook with: expected pre-deploy checklist results, environment-specific commands, health check URLs with expected responses, rollback triggers, rollback verification steps, post-deploy smoke test commands. The current deploy.sh assumes everything works on first try. | `docs/04_Deployment_Guides/DEPLOYMENT_RUNBOOK.md` |
| M05 | **Disaster Recovery Plan** | No backup/restore procedure documented. No Recovery Point Objective (RPO) or Recovery Time Objective (RTO) defined. No periodic backup verification script. `mysqldump` is mentioned in KNOWN_LIMITATIONS as "manual only". Given CREAMS handles PDPA-sensitive trainee data, this is a compliance risk. | `docs/04_Deployment_Guides/DISASTER_RECOVERY_PLAN.md` |
| M06 | **SonarQube Quality Gate Rules** | `sonar-project.properties` exists and connects to SonarScanner, but no documented quality gate thresholds exist (minimum coverage %, maximum duplication %, maximum complexity, maximum cognitive complexity, blocker/critical issue limits). Without defined gates, the scanner runs but never fails the build. | `sonar-project.properties` comments or `docs/03_Technical_Guides/SONAR_QUALITY_GATES.md` |
| M07 | **VPS Migration Guide** | `scripts/server-init.sh` bootstraps a fresh Lightsail instance. `LIGHTSAIL_FOOTPRINT.md` documents resource usage. But no step-by-step guide exists for migrating from local dev to VPS. Steps like DNS configuration, SSL certificate issuance order, database migration strategy, environment variable transfer, storage symlink, and first-deploy verification are undocumented. | `docs/04_Deployment_Guides/VPS_MIGRATION_GUIDE.md` |
| M08 | **DNS / Email Configuration Guide** | `creams_subdomain.conf` handles nginx routing. But no guide exists for: DNS A record setup, SPF record for email deliverability, DKIM signing for outgoing mail, DMARC policy, rDNS for the VPS IP, or MX records. Email-dependent features (password reset, notifications) are documented but the infrastructure to support them is not. | `docs/04_Deployment_Guides/DNS_EMAIL_SETUP.md` |
| M09 | **Performance Test Suite** | `PERFORMANCE_BASELINE_METHODOLOGY.md` describes methodology but no actionable test scripts exist. No JMeter `.jmx` files. No k6 scripts. No `artisan` benchmark commands. The 26s trainee creation and 19.5s schedule page load are known issues with no automated regression detection. | `tests/Performance/` or `benchmark/` |
| M10 | **API Documentation (Current)** | `API_REFERENCE.md` exists but is pre-fix — shows endpoints as "broken" that were later fixed in hardening waves. No Postman collection, OpenAPI/Swagger spec, or auto-generated API docs exist. Future mobile app or third-party integration would start from zero. | `docs/03_Technical_Guides/API_REFERENCE.md` (regenerated) or `storage/api-docs/` |
| M11 | **Database Backup Script** | No automated backup script exists. `mysqldump` is documented as "manual only" in KNOWN_LIMITATIONS. For a system handling PDPA-sensitive data, a cron-jobbed backup with rotation, off-site copy, and integrity verification is a compliance baseline. | `scripts/backup-db.sh` |
| M12 | **Environment Variables Reference** | Multiple `.env` variants exist (`.env.example`, `.env.production`, `.env.testing`, `.env.professional`, `.env.mysql.backup`). But no comprehensive reference documents: what every variable means, which are required vs optional, valid values, default behavior when absent, and which must differ per environment. New developers guess from `.env.example`. | `docs/03_Technical_Guides/ENVIRONMENT_VARIABLES.md` |
| M13 | **Onboarding Guide for New Developers** | `LOCAL_SETUP_GUIDE_2026-05-04.md` covers installation. But no architecture walkthrough exists: "here's how the code is organized, here's the request lifecycle with custom auth, here's how CentreScope works, here's a 'fix your first bug' tutorial." The handoff package exists but is stakeholder-facing, not developer-facing. | `docs/03_Technical_Guides/DEVELOPER_ONBOARDING.md` |
| M14 | **CI/CD Pipeline Documentation** | `.github/workflows/ci.yml` and `deploy.yml` exist but no pipeline diagram, no expected run times, no failure recovery steps, no explanation of when each workflow triggers. A new developer looking at a failed CI run has no guide to interpret it. | `docs/04_Deployment_Guides/CI_CD_PIPELINE.md` |
| M15 | **Playwright Test Suite Inventory** | 181/210 Playwright tests exist but no inventory of: what each spec file covers, which roles are tested in each, what database state each test expects, which tests are known-flaky, and what the 29 failing tests need to pass. Running `npx playwright test` without context is trial and error. | `tests/Browser/README.md` or `docs/05_Testing_Documentation/PLAYWRIGHT_INVENTORY.md` |
| M16 | **PDPA Data Handling SOP** | CentreScope provides technical isolation. Pre-commit hook blocks commits. STAGING_SEED_POLICY controls deployment seeding. But no formal SOP exists for: how to handle PII during development, what to do if real data is accidentally committed, data retention periods, right-to-access and right-to-deletion procedures, breach notification protocol. For a system processing Malaysian IC numbers and disability records, this is a legal compliance gap. | `docs/03_Technical_Guides/PDPA_DATA_HANDLING_SOP.md` |

---

## Priority Triage

| Priority | Items | Rationale |
|----------|-------|-----------|
| **CRITICAL** | M16 (PDPA SOP), M05 (DR Plan), M11 (DB Backup) | Legal/compliance risk for PDPA-protected data |
| **HIGH** | M02 (Security Standard), M04 (Deploy Runbook), M10 (API Docs), M12 (Env Reference) | Blockers for production deployment |
| **MEDIUM** | M03 (Debt Register), M07 (VPS Migration), M14 (CI/CD Docs), M15 (Playwright Inventory) | Operational efficiency |
| **LOW** | M01 (Coding Standard), M06 (Quality Gates), M08 (DNS Guide), M09 (Perf Tests), M13 (Onboarding) | Nice-to-have, not blocking |

---

## Evidence Basis

These gaps were identified by comparing the 220+ discovered artifacts against what is normally expected in a mature Laravel project targeting production deployment with PDPA-sensitive data. Each gap was verified by confirming the artifact does not exist anywhere in the repository. Where partial evidence exists (e.g., methodology docs without tooling), the gap is documented as "missing actionable artifact."

---

*Generated by automated repository exploration. Gap identification only. No recommendations. No fixes.*
