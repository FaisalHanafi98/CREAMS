# CREAMS — Repository Knowledge Map

> **Generated**: 2026-05-31
> **Category**: Navigation Map
> **Purpose**: A relationship map showing all knowledge hubs, their connections, and the hierarchy of authoritative sources. Designed for future AI agents to navigate the repository without reading every file.

---

## Quick Navigation — Where to Start

```
PRIMARY ENTRY POINT
    │
    ▼
docs/Validate/SOURCE_OF_TRUTH.md
    │
    ├──▶ For current mission:    docs/Validate/CREAMS_SESSION_CURRENT.md
    ├──▶ For test baseline:      docs/Validate/TEST_BASELINE.md
    ├──▶ For architecture:       docs/Validate/MULTI_CENTRE_ISOLATION.md
    ├──▶ For stakeholders:       docs/Validate/HANDOVER_PACKAGE_2026-05-04.md
    ├──▶ For limitations:        docs/Validate/KNOWN_LIMITATIONS_2026-05-04.md
    ├──▶ For module inventory:   docs/Validate/MODULE_FUNCTIONALITY_INVENTORY.md
    ├──▶ For database schema:    docs/Validate/DATABASE_SCHEMA_DOCUMENTATION.md
    ├──▶ For security gate:      docs/Validate/PRE_DEPLOY_SECURITY_CHECKLIST.md
    ├──▶ For commit rules:       docs/Validate/COMMIT_MESSAGE_SOP.md
    ├──▶ For AI agent setup:     docs/Validate/CODEX_INIT_PROMPT.md
    └──▶ For user-facing docs:   docs/10_User_Manuals/USER_MANUALS_MASTER_INDEX.md
```

---

## Hub Map — All Documentation Locations and Their Relationships

```
┌─────────────────────────────────────────────────────────────────────────┐
│                        GOVERNANCE HUB                                    │
│                                                                          │
│  CLAUDE.md (root) ◄── Root SOP v2.2.0                                   │
│  AGENTS.md (root)  ◄── Codex CLI shim —→ reads CLAUDE.md                │
│                                                                          │
│  .claude/commands/resume.md    ◄── /resume slash command                 │
│  .claude/skills/               ◄── 5 local skills                       │
│  .claude/settings.local.json   ◄── Permissions + hook config            │
│                                                                          │
│  .githooks/pre-commit          ◄── Secrets + IC pattern gate            │
│  .github/workflows/ci.yml      ◄── Test pipeline                        │
│  .github/workflows/deploy.yml  ◄── Manual deploy                        │
│  .gitmessage                   ◄── Commit template                      │
│  .editorconfig                 ◄── Code style                           │
│  .mcp.json                     ◄── Playwright MCP config                │
└─────────────────────────────────────────────────────────────────────────┘
        │
        │  (points to)
        ▼
┌─────────────────────────────────────────────────────────────────────────┐
│                     AUTHORITATIVE DOCS HUB                               │
│                     docs/Validate/                                       │
│                                                                          │
│  SOURCE_OF_TRUTH.md                 ◄── Documentation router            │
│  CREAMS_SESSION_CURRENT.md          ◄── Active mission                  │
│  TEST_BASELINE.md                   ◄── 359 tests floor                 │
│  MULTI_CENTRE_ISOLATION.md          ◄── 25 scoped models                │
│  HANDOVER_PACKAGE_2026-05-04.md     ◄── Stakeholder entry point         │
│  KNOWN_LIMITATIONS_2026-05-04.md    ◄── 14 categories of gaps           │
│  LOCAL_SETUP_GUIDE_2026-05-04.md    ◄── Fresh machine setup             │
│  PRE_DEPLOY_SECURITY_CHECKLIST.md   ◄── 9-section gate (2 RED)          │
│  COMMIT_MESSAGE_SOP.md              ◄── Conventional commit format      │
│  CODEX_INIT_PROMPT.md               ◄── Codex session bootstrap         │
│  MODULE_FUNCTIONALITY_INVENTORY.md  ◄── 163 features, 16 modules        │
│  DATABASE_SCHEMA_DOCUMENTATION.md   ◄── 37 tables, 34 migrations        │
│  DELTA_REEVAL_REPORT_2026-03-22.md  ◄── Falsified audit claims          │
│  SECURITY_AUDIT_REPORT.md           ◄── Feb 2026 OWASP 66%              │
│  SECURITY_BASELINE_SCAN_METHODOLOGY.md ◄── Scan methodology             │
│  SCHEMA_AUDIT_REPORT.md             ◄── Schema audit                    │
│  PERFORMANCE_BASELINE_METHODOLOGY.md ◄── Perf measurement methodology   │
│  PRODUCTION_DEPLOYMENT.md           ◄── Production deploy guide         │
│  CREAMS_Testing_Infrastructure_PRD.md ◄── Testing strategy PRD          │
│  PRE_UAT_MANUAL_TESTING_TRACKER.md  ◄── Pre-UAT tracker                 │
│  creams_detailed_uat.md             ◄── Detailed UAT flows              │
│  PRODUCTION_READINESS_ROADMAP.md    ◄── Feb 2026 roadmap (STALE)        │
│  MASTER_PROGRESS_LOG.md             ◄── Feb 2026 phases (FROZEN)        │
│  README_DOCUMENTATION_ORGANIZATION.md ◄── Sep 2025 index (STALE)       │
│  CREAMS_CODEBASE_DOCUMENTATION.md   ◄── Dec 2025 codebase (STALE)      │
│  CREAMS_SESSION_2026-04-16.md       ◄── Historical session              │
│  TEST_STABILIZATION_REPORT.md       ◄── Feb 2026 Playwright fixes       │
└─────────────────────────────────────────────────────────────────────────┘
        │                    │                    │
        │  (complements)     │  (references)      │  (cross-refs)
        ▼                    ▼                    ▼
┌───────────────┐  ┌──────────────────┐  ┌──────────────────────┐
│ SESSION MEMORY│  │ AI CONTEXT       │  │ USER-FACING DOCS     │
│ HUB           │  │ ARCHIVE HUB      │  │ HUB                  │
│               │  │                  │  │                      │
│ .memsearch/   │  │ docs/ai-context/ │  │ docs/10_User_Manuals/│
│ memory/       │  │                  │  │                      │
│               │  │ 00_PROJECT_SOT   │  │ 01_Auth (v2.0)      │
│ 2026-04-30.md★│  │ 01_CURRENT_STATUS│  │ 02_Dashboard (v2.0) │
│ 2026-05-02.md │  │                  │  │ 03_Activities (v2.0)│
│ 2026-05-04.md★│  │ 02_SESSION_      │  │ 04_Attendance(v2.0) │
│ 2026-05-06.md │  │   HISTORY/       │  │ 05_Staff (v2.0)     │
│ 2026-05-07.md★│  │ 03_BUG_HISTORY/  │  │ 06_Trainees (v2.0)  │
│ 2026-05-12.md★│  │ 04_DATABASE_     │  │ 07_Letters (v2.0)   │
│ + 6 empty stubs│  │   STATE/        │  │ 08_Admin (v2.0)     │
│               │  │ 05_MODULE_STATUS/│  │ Master_Index (v2.0) │
│               │  │ 06_TESTING_      │  │                      │
│               │  │   EVIDENCE/      │  │ All dated May 2026   │
│               │  │ 07_AI_HANDOFF/   │  │                      │
│               │  │ 08_DOC_ALIGNMENT/│  │                      │
│               │  │                  │  │                      │
│               │  │ All dated May 7-8│  │                      │
│               │  │ 2026             │  │                      │
└───────────────┘  └──────────────────┘  └──────────────────────┘

┌─────────────────────────────────────────────────────────────────────────┐
│                        LIVE EVIDENCE HUB                                 │
│                        docs/audit/                                       │
│                                                                          │
│  test_baseline_2026-04-30.log       ◄── 359/0/520 baseline              │
│  UAT_BLOCKERS_2026-04-30.md         ◄── Sprint Day 2 blocker audit      │
│  WIP_REGISTER_2026-04-30.md         ◄── Git dirty-tree inventory        │
│  DAY3_REPORT_2026-05-01.md          ◄── Sprint Day 3 status             │
│  MANUAL_AUDIT_FINDINGS_2026-05-02.md◄── Manual v1→v2 discrepancy audit │
│  DEMO_DRYRUN_REPORT_2026-05-03.md   ◄── HTTP 200-check walkthrough       │
│  PLAYWRIGHT_FINDINGS_2026-05-04.md  ◄── 181/210 Playwright pass         │
│  PLAYWRIGHT_MCP_DRYRUN_2026-05-04.md◄── MCP interactive walkthrough     │
│  pdpa_scan_2026-05-01.log           ◄── PDPA compliance scan            │
│  git_history_audit_2026-05-01.log   ◄── 72 IC patterns in history       │
│                                                                          │
│  live_mutation_smoke_2026-05-15.md  ◄── Production CRUD test            │
│  live_functional_uat_readiness_2026-05-16.md ◄── Readiness retest: FAIL │
│  live_invasive_uat_2026-05-16.md    ◄── Full invasive UAT               │
│  live_smoke_claim_verification_2026-05-16.md ◄── Claim verify: FAIL     │
│  live_uat_gate_smoke_2026-05-17.md  ◄── Gate smoke: FAIL                │
│  full_browser_uat_report_2026-05-18.md ◄── 3,558 controls: FAIL         │
│  full_browser_uat_retest_2026-05-18_092233Z.md ◄── Retest: FAIL         │
│  full_browser_uat_report_20260518T153020Z.md ◄── Edge UAT: FAIL         │
└─────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────┐
│                     APPLICATION CODE HUBS                                 │
│                                                                          │
│  app/Models/ (54 files)                                                  │
│  ├── 23 CentreScoped (direct)                                            │
│  ├── 2 closure-scoped (asset relationship)                               │
│  ├── 2 exceptions (Message, Centre)                                      │
│  └── 27 unscoped                                                        │
│                                                                          │
│  app/Http/Controllers/ (40+ files)                                       │
│  app/Http/Middleware/ (22 files)                                         │
│  ├── SecurityHeadersMiddleware ◄── 7 headers deployed                    │
│  ├── CentreAccessControl      ◄── Centre-scoped resource gate            │
│  ├── DemoInstanceMiddleware   ◄── /creams/{demo_id}/ routing            │
│  └── ErrorHandlingMiddleware  ◄── Request/response logging               │
│                                                                          │
│  app/Services/ (9 files)                                                 │
│  ├── SessionManager          ◄── Session flush + regenerate              │
│  ├── CentreService           ◄── Centre operations                       │
│  └── DashboardService        ◄── Dashboard aggregation                   │
│                                                                          │
│  app/Providers/ (9 files)                                                │
│  ├── RouteServiceProvider    ◄── 9 rate limiters defined                 │
│  ├── CustomAuthServiceProvider ◄── Session auth (not Breeze/Sanctum)    │
│  └── AppServiceProvider      ◄── Password::defaults()                    │
│                                                                          │
│  routes/ (6 files)                                                       │
│  ├── web.php (629 routes)     ◄── All application routes                │
│  ├── api.php (189 lines)      ◄── Dashboard search API                  │
│  ├── auth.php (66 lines)      ◄── Registration + login routes            │
│  └── test.php (85 lines)      ◄── Debug/test routes                      │
│                                                                          │
│  database/                                                               │
│  ├── migrations/ (34 files)   ◄── 2019→2026 timeline                     │
│  ├── factories/ (10 files)    ◄── All Faker-based                        │
│  ├── seeders/ (18 files)      ◄── UATSeeder + IRLSeeder                  │
│  └── real_data_backup.json    ◄── ⚠ PDPA RISK on disk                   │
│                                                                          │
│  tests/                                                                  │
│  ├── Feature/ (30 files)      ◄── 18 sub-modules tested                  │
│  ├── Unit/ (8 files)          ◄── Model + scope tests                    │
│  └── Browser/                 ◄── Dusk tests (empty)                     │
└─────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────┐
│                     SUPPLEMENTARY DOCS HUBS                               │
│                                                                          │
│  docs/03_Technical_Guides/ (5 unique + 3 stale)                          │
│  ├── BUSINESS_LOGIC.md          ◄── Volunteer + letter module logic      │
│  ├── ERROR_HANDLING_GUIDE.md    ◄── 8-component error system             │
│  ├── FORM_ENHANCEMENT_IMPLEMENTATION_GUIDE.md ◄── Form patterns          │
│  ├── SONARSCANNER_QUALITY_GUIDE.md ◄── Quality tooling setup             │
│  └── TECHNICAL_ARCHITECTURE.md  ◄── System architecture overview         │
│                                                                          │
│  docs/04_Deployment_Guides/                                              │
│  └── STAGING_SEED_POLICY.md     ◄── 3-layer PDPA seeding gate           │
│                                                                          │
│  docs/09_New_Features/ (3 files)                                         │
│  ├── MEDIA_UPLOAD_SYSTEM.md                                            │
│  ├── TOAST_NOTIFICATION_SYSTEM.md                                       │
│  └── MEDIA_UPLOAD_MIGRATION_EXAMPLE.md                                  │
│                                                                          │
│  docs/UAT FILES/ (22 files — Oct 2025, mostly historical)               │
│  └── DEMO_SCRIPT_2026-05-03.md ◄── Active demo script                   │
└─────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────┐
│                     ARCHIVED KNOWLEDGE HUBS                               │
│                                                                          │
│  docs/archive/                                                           │
│  ├── ADR-001, 002, 003          ◄── ★ STILL AUTHORITATIVE                │
│  ├── deployment/ (3 files)      ◄── Vercel/AWS guides (SUPERSEDED)      │
│  ├── prompts/ (12 files)        ◄── Old session prompts                  │
│  ├── audits/ (2 files)          ◄── Pre-sprint test runs                 │
│  └── duplicates/ (9 files)      ◄── Byte-identical user manual copies    │
│                                                                          │
│  docs/01_System_Overview/ (2 files)  ◄── Jan 2025 (SUPERSEDED)          │
│  docs/02_Module_Documentation/ (9) ◄── Jan 2025 (SUPERSEDED, merge in)  │
│  docs/06_Status_Reports/ (6)      ◄── Demo-era snapshots (STALE)        │
│  docs/07_Fixes_and_Audits/ (16)   ◄── Jul-Aug 2025 (HISTORICAL)         │
│  docs/08_Development_Planning/ (6) ◄── Jun-Aug 2025 (HISTORICAL)        │
│                                                                          │
│  archive/ (old CREAMS versions + non-CREAMS files)                       │
│  ├── cream/                     ◄── Old full Laravel app + git history   │
│  ├── Code VSC/                  ◄── 3 old version copies with .env files │
│  ├── Delete These Files/        ◄── 26 files marked for deletion         │
│  └── quarantine/                ◄── Malformed temp files                 │
│                                                                          │
│  .claude/worktrees/ (2 copies)  ◄── ⚠ Full repo duplicates              │
└─────────────────────────────────────────────────────────────────────────┘
```

---

## Trust Hierarchy (Highest to Lowest)

1. **Code on disk** — the running source code is always correct
2. **Git history** — `git log`, `git blame`, `git show`
3. **Live evidence** — `docs/audit/` (May 2026 test baselines, UAT reports, scans)
4. **Session memory** — `.memsearch/memory/` (Apr-May 2026 checkpoints)
5. **AI context archive** — `docs/ai-context/` (May 7-8, 2026 — verified facts)
6. **Authoritative docs** — `docs/Validate/` (Apr-May 2026)
7. **User manuals** — `docs/10_User_Manuals/` (v2.0, May 2026)
8. **Technical guides** — `docs/03_Technical_Guides/` (unique content only)
9. **Historical docs** — `docs/archive/` and old numbered folders (for reference only)
10. **Non-CREAMS files** — archive artifacts (Java, Leetcode, tips) — ignore

---

## Cross-Cutting Relationships

| Topic | Primary Source | Verified By | Cross-Referenced In |
|-------|---------------|-------------|---------------------|
| Test count | TEST_BASELINE (359) | test_baseline_2026-04-30.log | HANDOVER_PACKAGE, CODEX_INIT_PROMPT, SESSION_CURRENT |
| CentreScope coverage | MULTI_CENTRE_ISOLATION (25) | CentreScope.php + isolation tests | HANDOVER_PACKAGE, PRE_DEPLOY_SECURITY_CHECKLIST |
| Auth stack | SESSION_CURRENT (custom POST /auth/check) | SessionManager.php + MainController | CLAUDE.md, AGENTS.md, CODEX_INIT_PROMPT |
| Roles | ADR-002 (4 active + 2 planned) | config/auth.php + middleware | CLAUDE.md, AGENTS.md, SESSION_CURRENT |
| Deployment target | SOURCE_OF_TRUTH (Lightsail) | deploy.sh + creams_subdomain.conf | HANDOVER_PACKAGE, DEPLOYMENT_INVENTORY |
| Rate limiting | RouteServiceProvider (9 limiters) | routes/web.php + routes/auth.php | PRE_DEPLOY_SECURITY_CHECKLIST |
| Commit format | COMMIT_MESSAGE_SOP | .gitmessage | CLAUDE.md, AGENTS.md |
| Live UAT status | live_uat_gate_smoke (FAIL) | full_browser_uat reports | PROJECT_STATE_INVENTORY |

---

*Generated by automated repository exploration. Navigation map only.*
