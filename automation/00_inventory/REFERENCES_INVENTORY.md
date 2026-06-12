# CREAMS — References Inventory

> **Generated**: 2026-05-31
> **Category**: References
> **Purpose**: Inventory of URLs, repositories, templates, indices, external research, tool configurations, and AI agent prompts.

---

## Documentation Indices & Entry Points

| Source File | Evidence Type | Reason Included | Confidence | Importance |
|---|---|---|---|---|
| `docs/Validate/SOURCE_OF_TRUTH.md` | Authority Index | Documentation router: authority table (6 files), known-drift watchlist (5 contradictions), files NOT to trust, architecture decisions reference. Last containment: 2026-04-24. | High | Critical |
| `docs/Validate/README_DOCUMENTATION_ORGANIZATION.md` | Folder Index | Sep 2025: describes numbered folder structure 01-08. References OLD CLAUDE.md. Superseded by SOURCE_OF_TRUTH. | Medium | Low |
| `docs/10_User_Manuals/USER_MANUALS_MASTER_INDEX.md` | Manual Index | v2.0 (May 2026): cross-references to MULTI_CENTRE_ISOLATION, SOURCE_OF_TRUTH, CLAUDE.md, AGENTS.md, test_baseline, routes audit, COMMIT_MESSAGE_SOP. Reading orders per role. | High | Critical |
| `docs/archive/CONSOLIDATED_DOCUMENTATION_INDEX.md` | Index (STALE) | Jan 2025 master docs index. Routes to non-existent folders. Superseded. Awaits cleanup per SOURCE_OF_TRUTH. | Low | Low |
| `docs/ai-context/08_DOC_ALIGNMENT/documentation_inventory.md` | Inventory | 155+ docs catalogued across governance, session, architecture, testing, module docs. Currency status per file. Date: 2026-05-08. | High | High |

## AI Agent Prompts & Templates

| Source File | Evidence Type | Reason Included | Confidence | Importance |
|---|---|---|---|---|
| `docs/Validate/CODEX_INIT_PROMPT.md` | Template | Codex session bootstrap: resume protocol (STEP 1), trust hierarchy (STEP 2), hard rules (STEP 3), checkpoint protocol (STEP 4), tools/conventions (STEP 5). Created: 2026-04-30. | High | Critical |
| `docs/ai-context/07_AI_HANDOFF/generic_ai_transfer_prompt.md` | Template | Self-contained bootstrap prompt for any AI agent: Claude Code, Codex, OpenCode, Cursor. Includes project context, conventions, and initial instructions. | High | Critical |
| `docs/ai-context/README.md` | Guide | AI context archive usage: 10-step /resume SOP order. Source-of-truth hierarchy for AI agents. Purpose and conventions. | High | Critical |
| `.claude/commands/resume.md` | Command | 5-step resume protocol: read latest memory, read CLAUDE.md, read CREAMS_SESSION_CURRENT, read SOURCE_OF_TRUTH, summarize and wait. | High | Critical |
| `.claude/skills/SKILLS_REGISTRY.md` | Registry | Master index: 5 local skills (route-audit, fix-verify, planning, dead-code, password-reset) + 14 plugin skills (obra/superpowers suite). | High | High |
| `docs/archive/prompts/RE_EVAL_PROMPT.md` | Historical | Delta re-evaluation prompt for Claude Code. References 8 pre-existing docs for context-efficient resume. | Low | Low |
| `docs/archive/prompts/FIRST_PROMPT_TEMPLATE.md` | Historical | Old session initiation template: read CREAMS_MASTER_DOCUMENTATION first, checklist of critical gotchas. | Low | Low |

## Tool Configurations

| Source File | Evidence Type | Reason Included | Confidence | Importance |
|---|---|---|---|---|
| `.mcp.json` | MCP Config | Playwright browser automation via @playwright/mcp@latest. Chrome browser. 1280x800 viewport. | High | Medium |
| `.claude/settings.local.json` | Claude Config | 159 lines. Permissions whitelist. Commit hooks configuration. Checkpoint trigger settings. Commit identity: faisalhanafi.dsa@gmail.com. | High | Medium |
| `AGENTS.md` | Codex Config | Codex CLI auto-loads this file. Points to CLAUDE.md + CODEX_INIT_PROMPT.md. Hard reminders on roles, auth, routes, deployment. | High | High |

## Commit & Development Standards

| Source File | Evidence Type | Reason Included | Confidence | Importance |
|---|---|---|---|---|
| `docs/Validate/COMMIT_MESSAGE_SOP.md` | Standard | Type(Scope) format. "Verified that" checklist. UK English. Avoid-list and prefer-list. Full example. Date: 2026-04-25. | High | High |
| `.gitmessage` | Template | Commit template: Type(Scope), Date, Task, Verified that sections. Activated via git config commit.template. | High | Medium |
| `docs/Validate/LOCAL_SETUP_GUIDE_2026-05-04.md` | Guide | Fresh machine setup: prerequisites, install steps, database, seeders, pre-commit hook, dev server, AI agent setup. Date: 2026-05-04. | High | Critical |

## UAT & Testing References

| Source File | Evidence Type | Reason Included | Confidence | Importance |
|---|---|---|---|---|
| `docs/UAT FILES/TESTING_CREDENTIALS.md` | Reference | Test credentials: 4 role accounts. 3 seeding methods (migrate:fresh --seed, TestingGuideDataSeeder, standalone script). | High | High |
| `docs/UAT FILES/UAT_EXECUTION_GUIDE.md` | Reference | 75 test cases across 11 modules. CSV import instructions. Comprehensive procedures. | Medium | Medium |
| `docs/UAT FILES/CHRONOLOGICAL_UAT_TEST_ORDER.md` | Reference | 56+ test cases in 7 chronological phases. Master test order. | High | High |
| `docs/UAT FILES/README_UAT_ORGANIZATION.md` | Reference | Directory-purpose: lists all UAT files and their roles (primary test, supporting, logs). | Medium | Low |
| `docs/UAT FILES/ENTERPRISE_SPECIALIZED_UAT_MASTER_GUIDE.md` | Reference | Enterprise suites for auth/security, dashboard analytics, RBAC, performance. NIST/OWASP/ISO 27001 references. | Medium | Medium |

## Route & API References

| Source File | Evidence Type | Reason Included | Confidence | Importance |
|---|---|---|---|---|
| `docs/audit/routes_2026-04-30.json` | Inventory | 629 routes exported in JSON format. Sprint Day 2 reality audit output. | High | High |
| `tmp_routes_audit.json` | Inventory | Route audit JSON dump (single line). 629+ routes. Root-level temp file. | Medium | Medium |
| `docs/03_Technical_Guides/API_REFERENCE.md` | Reference (STALE) | API endpoint listing. Marks many endpoints as broken. Pre-fix snapshot — dangerously misleading. | Medium | Low |
| `docs/archive/API_ENDPOINT_SECURITY_INVENTORY.md` | Reference | 231 routes security classification: 68% compliant. Pre-L12. Pre-sprint. | Medium | Medium |
| `docs/archive/routes_sample.txt` | Sample | php artisan route:list output sample (100 lines). activities, schedule, attendance routes. | Low | Low |

## Architecture Decision Records

| Source File | Evidence Type | Reason Included | Confidence | Importance |
|---|---|---|---|---|
| `docs/archive/ADR-001-blade-over-spa.md` | ADR | Blade chosen over React SPA. Date: 2025-01-01. | High | High |
| `docs/archive/ADR-002-six-role-rbac.md` | ADR | 6-role RBAC design: 4 active, 2 planned. Date: 2025-01-01. | High | Critical |
| `docs/archive/ADR-003-mysql-over-postgresql.md` | ADR | MySQL over PostgreSQL. Date: 2025-01-01. | High | Medium |

## Archive Reference

| Source File | Evidence Type | Reason Included | Confidence | Importance |
|---|---|---|---|---|
| `docs/archive/README.md` | Index | Archive purpose: historical only, preserved for traceability, 9 sub-categories. Rules for future use. | High | Medium |

---

## References Summary (Synthesized from Evidence)

- **Primary indices**: SOURCE_OF_TRUTH.md (authority), USER_MANUALS_MASTER_INDEX (v2.0 May 2026), AI context README
- **AI agent templates**: CODEX_INIT_PROMPT (Codex), generic_ai_transfer_prompt (any agent), .claude/commands/resume.md (Claude)
- **Test credentials**: 4 role accounts (super.admin / supervisor.a1 / teacher.a1 / ajk.a1 @uat.creams.test, password UatPass2026!)
- **Route inventory**: 629 routes (audit/routes_2026-04-30.json)
- **Commit standard**: COMMIT_MESSAGE_SOP.md with Type(Scope) convention
- **3 ADRs**: Blade over SPA, 6-role RBAC, MySQL over PostgreSQL (all still authoritative)
- **5 Claude skills**: route-audit, fix-verify, planning, dead-code, password-reset
- **Setup guide**: LOCAL_SETUP_GUIDE_2026-05-04.md (latest)

---

*Generated by automated repository exploration. Do not modify application code. Classification only.*
