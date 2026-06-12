# CREAMS — Automation Directory Index

> **Generated**: 31 May 2026
> **Purpose**: Explains every file in `automation/` and its subdirectories.
> **Total files**: 19 (across 9 directories — 6 are empty scaffolding for future use)

---

## Directory Structure

```
automation/
├── 00_inventory/         12 files  ✅ Knowledge inventories
├── 01_raw_evidence/       0 files  🗂 Future: raw audit evidence
├── 02_curated_evidence/   0 files  🗂 Future: curated/filtered evidence
├── 03_standards/          0 files  🗂 Future: generated standards docs
├── 04_audits/             1 file   ✅ Critical findings register
├── 05_execution/          0 files  🗂 Future: execution batch plans
├── 06_reports/            0 files  🗂 Future: remediation reports
├── 07_generated/          6 files  ✅ Gemini AI onboarding package
└── 99_archive/            0 files  🗂 Future: superseded automation output
```

---

## 00_inventory/ (12 files)

Knowledge inventories — generated from full repository exploration on 31 May 2026. 295 entries catalogued from ~467 files explored. Each file is a specialized evidence catalog for a single domain. No remediation, no fixes — evidence collection only.

| File | Entries | What It Catalogs | Purpose |
|------|---------|------------------|---------|
| `PROJECT_STATE_INVENTORY.md` | 20 | Current project phase, mission, milestones, session history, sprint status, live UAT gate | Understand where we are before deciding what to do |
| `UAT_INVENTORY.md` | 38 | UAT test cases (CSV+MD), execution reports, gap analysis, live production UAT (May 2026), demo script | UAT stabilisation planning |
| `SMOKE_TEST_INVENTORY.md` | 24 | Test baselines (359 floor), Playwright E2E (181/210), demo dry-runs, live smoke tests, CI test config | Test infrastructure planning |
| `SECURITY_INVENTORY.md` | 30 | Auth config, rate limiters (9), CentreScope (25 models), security headers (7), pre-deploy gate (2 RED), PDPA scans, secrets gate | Security remediation planning |
| `ARCHITECTURE_INVENTORY.md` | 36 | 54 models, 22 middleware, 9 services, 34 migrations, 629 routes, 3 ADRs, CentreScope mechanisms, URL architecture | Architecture review, refactoring |
| `CODE_QUALITY_INVENTORY.md` | 26 | PHPStan (level 5), SonarScanner, CI pipeline, commit standard, bug registers (resolved+unresolved), doc alignment matrix (15 rows), error handling architecture | Code quality baseline |
| `REFACTORING_INVENTORY.md` | 32 | Known limitations (14 categories), technical debt, failed approaches (8), doc drift (6 stale), historical fix logs (16 from Jul-Aug 2025), future user stories | Refactoring prioritisation |
| `DEPLOYMENT_INVENTORY.md` | 25 | deploy.sh, Dockerfile, server-init.sh, nginx configs, Lightsail footprint, staging seed policy, rollback procedure, superseded Vercel/AWS guides | VPS migration, deployment readiness |
| `REFERENCES_INVENTORY.md` | 28 | Documentation indices (SOURCE_OF_TRUTH, user manual index), AI agent prompts/templates, tool configs (.mcp.json, Claude settings), commit SOP, test credentials, route inventory | Onboarding new agents, standards enforcement |
| `MISSING_ARTIFACTS.md` | 16 gaps | Priority-triaged gaps vs mature Laravel project: PDPA SOP (CRITICAL), DR plan (CRITICAL), DB backup script (CRITICAL), security standard (HIGH), deploy runbook (HIGH), + 11 more | Standards creation planning |
| `REPOSITORY_KNOWLEDGE_MAP.md` | Full nav tree | Visual diagram of all knowledge hubs, trust hierarchy (10 tiers), cross-cutting relationships table, quick-nav section | "Where is X?" — every AI session |
| `INVENTORY_COMPLETION_REPORT.md` | Summary | Metadata about the inventories themselves — total entries (295), duplicates found, high-risk discoveries (14), authoritative sources identified, unresolved questions (5), completeness assessment | Validating inventory quality |

---

## 04_audits/ (1 file)

| File | Entries | What | Purpose |
|------|---------|------|---------|
| `CRITICAL_FINDINGS_REGISTER.md` | 20 findings | Security, PDPA, credentials, and deployment risks discovered during exploration. 4 CRITICAL, 8 HIGH, 7 MEDIUM, 1 LOW. Each with location, evidence, and recommended future audit action | Risk triage for remediation phases |

**Top 3 critical findings**:
1. `database/real_data_backup.json` — real production data on disk (1 centre, 14 users, 57 assets)
2. `docs/audit/screenshots/` — live UAT screenshots expose real production email
3. `scripts/server-init.sh` — hardcoded database passwords in Lightsail bootstrap

---

## 07_generated/ (6 files)

Gemini AI onboarding package — transforms inventory intelligence into a 10-file-prompt-limited format. Designed for AI models that must plan a continuous remediation programme without exceeding context limits.

| File | Lines | What | Purpose |
|------|-------|------|---------|
| `GEMINI_MASTER_CONTEXT.md` | 208 | Executive summary of entire CREAMS project — system overview, architecture, deployment state, test baseline, UAT status (FAIL), security posture, critical findings, technical debt, missing artifacts, project objective. ~6 pages. | **Always load first** — gives Gemini everything it needs to understand the project |
| `GEMINI_WORKING_SET.md` | 139 | File selection guide — Priority 1-3 tiers, 5 pre-built upload combinations (security, UAT, refactoring, deployment, documentation), file size estimates, rules for future sessions. 10-file limit enforced, 3 slots reserved for URLs. | **Load second** — tells Gemini which files to use for each objective |
| `GEMINI_EXECUTION_BRIEF.md` | 275 | Session briefing — current repository state, project objective, what's completed (Phase 0, Phase 1, Sprint), what NOT to repeat (8 failed approaches + DO-NOTs), known audit findings, constraints, context limitations, session checklist. | **Load third** — prevents wasted effort on completed work |
| `MASTER_REMEDIATION_PROMPT.md` | 228 | Reusable prompt framework — instructs Gemini to produce a 10-section remediation strategy (evidence assessment, gap analysis, phased strategy, execution batches, testing, security, refactoring, deployment, documentation, risk register). Anti-hallucination rules. Format requirements. | **Copy as prompt** — ensures structured output, prevents hallucinations |
| `GEMINI_URL_MANIFEST_TEMPLATE.md` | 271 | Template for future external URLs — 9 categories (AI agents, continuous sessions, refactoring, security, Laravel, SonarQube, DevOps, testing, deployment). Each with pre-filled CREAMS relevance context. 3 reserved URL slots per prompt with suggested fills. | **Populate during planning** — stores URLs for future sessions |
| `AI_PACKAGE_SUMMARY.md` | 247 | Package overview — recommended upload order, 5 pre-built file combinations, 4-phase workflow (Gemini strategic planning → DeepSeek execution → Continuous remediation → Verification), package maintenance rules, quick-reference table. | **Reference** — explains how to use the entire package |

---

## Empty Directories (6 — Future Scaffolding)

These directories exist as placeholders for future automation phases. They are currently empty.

| Directory | Intended Future Purpose |
|-----------|------------------------|
| `01_raw_evidence/` | Raw audit output, logs, screenshots collected during remediation |
| `02_curated_evidence/` | Filtered/annotated evidence — only what's relevant to a specific issue |
| `03_standards/` | Generated standards documents (security standard, coding standard, PDPA SOP) |
| `05_execution/` | Execution batch plans — task lists with file paths, line numbers, commit templates |
| `06_reports/` | Remediation completion reports — per-phase verification results |
| `99_archive/` | Superseded automation output — old inventory versions, previous Gemini outputs |

---

## How the Automation Directory Fits Together

```
REPOSITORY EXPLORATION (Phase Complete)
        │
        ▼
00_inventory/ ── 12 files, 295 entries
        │         Catalogued everything
        │
        ├──▶ 04_audits/CRITICAL_FINDINGS_REGISTER.md
        │        20 prioritized risks
        │
        └──▶ 07_generated/ ── 6 files
              Compressed into Gemini-ready format
              │
              │  (future phase)
              ▼
         GEMINI STRATEGIC PLANNING
              │
              ├──▶ 03_standards/ (generate missing artifacts)
              ├──▶ 05_execution/ (produce batch plans)
              │
              ▼
         CONTINUOUS REMEDIATION
              │
              ├──▶ 01_raw_evidence/ (collect audit output)
              ├──▶ 02_curated_evidence/ (filter findings)
              │
              ▼
         VERIFICATION & CERTIFICATION
              │
              └──▶ 06_reports/ (completion reports)
```

---

## Quick Reference

| You want to... | Read this |
|----------------|-----------|
| Understand the entire project at a glance | `07_generated/GEMINI_MASTER_CONTEXT.md` |
| Know where every file in the repo lives | `00_inventory/REPOSITORY_KNOWLEDGE_MAP.md` |
| Know what's broken (security/PDPA) | `04_audits/CRITICAL_FINDINGS_REGISTER.md` |
| Know what's missing | `00_inventory/MISSING_ARTIFACTS.md` |
| Know the test baseline | `00_inventory/SMOKE_TEST_INVENTORY.md` |
| Know the UAT status | `00_inventory/UAT_INVENTORY.md` |
| Plan a Gemini session | `07_generated/GEMINI_WORKING_SET.md` |
| Start a new AI agent session | `07_generated/GEMINI_EXECUTION_BRIEF.md` |
| Structure a remediation prompt | `07_generated/MASTER_REMEDIATION_PROMPT.md` |
| See what's been catalogued | `00_inventory/INVENTORY_COMPLETION_REPORT.md` |

---

*Generated: 31 May 2026. All 19 files documented. 6 empty directories are future scaffolding.*
