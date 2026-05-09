# CREAMS — Documentation Inventory

**Last updated**: 2026-05-08
**Total doc files**: 155+ across docs/, .claude/, .memsearch/

---

## Governance docs (root)

| Path | Purpose | Currency |
|---|---|---|
| `CLAUDE.md` (root) | Root SOP — overrides all | Current (Feb 2026) |
| `docs/CLAUDE.md` | Project overlay | **STALE** — says Laravel 10.x, Breeze+Sanctum, PHP 8.1+ |
| `AGENTS.md` | Codex shim pointing to CLAUDE.md | Current |

---

## Session management

| Path | Purpose | Currency |
|---|---|---|
| `docs/SOURCE_OF_TRUTH.md` | Documentation authority index | Current (Apr 2026) |
| `docs/CREAMS_SESSION_CURRENT.md` | Current session context | Current |
| `docs/HANDOVER_PACKAGE_2026-05-04.md` | Stakeholder handover | Current (May 2026) |
| `docs/KNOWN_LIMITATIONS_2026-05-04.md` | Feature gap inventory | Current (May 2026) |
| `docs/LOCAL_SETUP_GUIDE_2026-05-04.md` | Dev setup | Current (May 2026) |
| `docs/PRE_DEPLOY_SECURITY_CHECKLIST.md` | Pre-deploy gates | Current — NOT CLEARED |
| `.memsearch/memory/*.md` | Session checkpoints | Current — latest: 2026-05-07 |

---

## Architecture

| Path | Purpose | Currency |
|---|---|---|
| `docs/ADR-001-blade-over-spa.md` | Blade vs SPA decision | Stable |
| `docs/ADR-002-six-role-rbac.md` | Role system decision | Stable |
| `docs/ADR-003-mysql-over-postgresql.md` | DB decision | Stable |
| `docs/MULTI_CENTRE_ISOLATION.md` | CentreScope architecture | Current (Apr 2026) |
| `docs/03_Technical_Guides/TECHNICAL_ARCHITECTURE.md` | System architecture | Partially stale |

---

## Module documentation (docs/02_Module_Documentation/)

All 10 module docs exist. Currency varies — written during sprint but may not reflect May 2026 code state. Treat as INFERRED guides, not VERIFIED specs.

---

## User manuals (docs/10_User_Manuals/)

8 manuals re-baselined to v2.0 in May 2026. Reflect running application behavior at time of sprint demo. MOST CURRENT of all documentation.

---

## Deployment guides

| Path | Purpose | Currency |
|---|---|---|
| `docs/04_Deployment_Guides/DEPLOYMENT_GUIDE.md` | Original guide (Vercel/AWS) | **STALE** — superseded |
| `docs/PRODUCTION_DEPLOYMENT.md` | Production checklist | **STALE** — references PHP 8.2 ok |
| `docs/04_Deployment_Guides/STAGING_SEED_POLICY.md` | Seed policy | Current |
| `docs/04_Deployment_Guides/LIGHTSAIL_FOOTPRINT.md` | Lightsail resource config | Current |
| `docs/archive/deployment/` | Old Vercel/ECS guides | **SUPERSEDED** |
| `creams_subdomain.conf` (untracked) | Nginx config for subdomain | Current — untracked, must be committed |

---

## Audit and status reports

| Path | Purpose | Currency |
|---|---|---|
| `docs/audit/PLAYWRIGHT_FINDINGS_2026-05-04.md` | Playwright batch findings | Current |
| `docs/audit/PLAYWRIGHT_MCP_DRYRUN_2026-05-04.md` | MCP interactive dry-run | Current |
| `docs/06_Status_Reports/ULTIMATE_FINAL_STATUS.md` | Old completion report | **STALE** — pre-sprint, deeply outdated |

---

## Core reference (root level — very large)

| Path | Lines | Currency | Notes |
|---|---|---|---|
| `CREAMS_CODEBASE_DOCUMENTATION.md` | 63,340 | Partially stale | Written before L10→L12 upgrade |
| `DATABASE_SCHEMA_DOCUMENTATION.md` | 71,451 | Partially stale | Does not reflect 2026 migrations |
| `MODULE_FUNCTIONALITY_INVENTORY.md` | 47,177 | Partially stale | Pre-sprint |
| `MASTER_PROGRESS_LOG.md` | 35,006 | Historical | Session log — context only |
| `SECURITY_AUDIT_REPORT.md` | ~5KB | Current (Apr 2026) | |
| `MULTI_CENTRE_ISOLATION.md` | ~8KB | Current (Apr 2026) | |
| `COMMIT_MESSAGE_SOP.md` | 4.5KB | Current | |
| `CODEX_INIT_PROMPT.md` | 10.3KB | Current | |
| `TEST_BASELINE.md` | Active | Current | Shows 359 tests as of May 2026 |
