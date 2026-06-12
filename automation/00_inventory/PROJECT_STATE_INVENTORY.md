# CREAMS — Project State Inventory

> **Generated**: 2026-05-31
> **Category**: Project State
> **Purpose**: Inventory of all files that describe current project condition, status, milestones, and completion reports.

---

| Source File | Evidence Type | Reason Included | Confidence | Importance |
|---|---|---|---|---|
| `CLAUDE.md` | Governance | Root SOP v2.2.0 — defines roles (Admin/Supervisor/Teacher/AJK), custom session auth (POST /auth/check), PDPA constraints, deployment freeze, memory protocol. Last containment pass: 2026-04-24. | High | Critical |
| `AGENTS.md` | Governance | Codex CLI shim — points to CLAUDE.md for governance and CODEX_INIT_PROMPT.md for resume/checkpoint protocol. Hard reminders on roles, auth, routes, deployment hold. | High | Critical |
| `docs/Validate/SOURCE_OF_TRUTH.md` | Authority Index | Documentation router. Last containment pass: 2026-04-24. Lists authoritative files, stale docs to avoid, known drift watchlist (test counts, CentreScope, deployment, roles, auth stack). Capped at ~8 rows. | High | Critical |
| `docs/Validate/CREAMS_SESSION_CURRENT.md` | Mission | Active session prompt. Status: re-baselining phase — no deployment permitted. Defines required code-reality audit before any feature or security work. DO-NOTs: deploy, follow archived prompts, re-apply CentreScope, re-rewrite auth tests. | High | Critical |
| `docs/Validate/CREAMS_SESSION_2026-04-16.md` | Historical Mission | Prior session (2026-04-16): security residuals + test baseline prep. Documents what was completed in 2026-04-01 session. States CentreScope expanded to 26 of 28 models. Exit criteria listed. | High | High |
| `docs/Validate/HANDOVER_PACKAGE_2026-05-04.md` | Stakeholder Handover | Entry point for PPDK staff, IIUM committee, and future developers. 359 tests pass (520 assertions, 0 failures). 4 roles working end-to-end. 25 scoped models. 8 user manuals v2.0. Live demo Day 7 scheduled. | High | Critical |
| `docs/Validate/KNOWN_LIMITATIONS_2026-05-04.md` | Reality Register | 14 categories of honest limitations: roles, auth, trainees, activities, attendance, letters, admin, reporting, notifications, performance, ops, docs, PDPA history, UAT gap. | High | Critical |
| `docs/Validate/MASTER_PROGRESS_LOG.md` | Phased Progress | Phase 0 (Audit) = 100%, Phase 1 (Security Hardening) = 100% complete. Phase 2-4 = 0% pending. Last updated: 2026-02-07. NOTE: This file is stale — frozen for 3+ months. CURRENT_SESSION supersedes it. | Medium | Medium |
| `docs/Validate/PRODUCTION_READINESS_ROADMAP.md` | Roadmap | Feb 2026: 2,790-line phased plan. Claims 85% complete, ~78% OWASP. DELTA_REEVAL falsified many claims. Marked stale by SOURCE_OF_TRUTH. | Medium | Low |
| `docs/ai-context/00_PROJECT_SOURCE_OF_TRUTH.md` | Verified Facts | TECH STACK: L12, PHP 8.2+, custom auth, 4 active roles (Admin/Supervisor/Teacher/AJK). URL: `/creams/{demo_id}/`. 359 tests baseline. Last containment: 2026-05-07. | High | Critical |
| `docs/ai-context/01_CURRENT_STATUS.md` | Current State | Deployment blocked at seeder. 354/359 tests pass (5 regressions from demo_demo_route() typo). 14 uncommitted files. Goal: deploy to creams.faisalhanafi.com. Date: 2026-05-08. | High | Critical |
| `docs/ai-context/02_SESSION_HISTORY/2026-05-08_ai-context-archive-build.md` | Session Log | Session that built ai-context archive: 155+ docs catalogued, codebase reality scan, deviation register created. | High | High |
| `docs/ai-context/02_SESSION_HISTORY/2026-05-07_ai-context-archive.md` | Session Log | Original archive creation: all ai-context files created, 50+ docs catalogued, 9 deviations classified. | High | High |
| `docs/audit/WIP_REGISTER_2026-04-30.md` | Git State | Sprint Day 1 classification of every dirty/untracked item in git status: COMMITTED, IGNORED, ARCHIVED, DEFERRED, BLOCKER. | High | High |
| `docs/audit/DAY3_REPORT_2026-05-01.md` | Sprint Status | Sprint Day 3: zero P0/P1 fixes needed. UATSeeder built (Faker-only, 3 centres, 16 staff, 21 trainees). PDPA gate passed. 4-role walkthrough done. | High | High |
| `docs/audit/live_uat_gate_smoke_2026-05-17.md` | Gate Status | Latest production gate smoke: all 4 roles login OK. Asset detail 200. No 500s. But logout still fails = FAIL condition. | High | Critical |
| `.memsearch/memory/2026-04-30.md` | Checkpoint | Sprint Days 1-2 foundation: 629 routes inventoried, CentreScope verified (25 models), 359 tests, trainee_audit_logs P0 fix. | High | Critical |
| `.memsearch/memory/2026-05-04.md` | Checkpoint | Sprint Days 1-6 complete retrospective: Playwright MCP setup, demo walkthrough, IRLSeeder gating, 8 manuals rewritten to v2.0. | High | Critical |
| `.memsearch/memory/2026-05-07.md` | Checkpoint | Deployment to creams.faisalhanafi.com (Lightsail). PHP 8.2 upgrade. composer.lock PHP version mismatch blocker. Server IP: 54.169.32.54. | High | Critical |
| `.memsearch/memory/2026-05-12.md` | Checkpoint | Production patch groups deployed. BUG tracking. UAT smoke test. Trainee form fixes. Valuable "Do not repeat" rules captured. | High | Critical |

---

## Duplicate Evidence Across Categories

| File | Also Appears In |
|------|----------------|
| `CLAUDE.md` | ARCHITECTURE_INVENTORY, SECURITY_INVENTORY, DEPLOYMENT_INVENTORY |
| `AGENTS.md` | ARCHITECTURE_INVENTORY |
| `SOURCE_OF_TRUTH.md` | REFERENCES_INVENTORY |
| `HANDOVER_PACKAGE_2026-05-04.md` | DEPLOYMENT_INVENTORY |
| `KNOWN_LIMITATIONS_2026-05-04.md` | REFACTORING_INVENTORY |
| `CREAMS_SESSION_CURRENT.md` | ARCHITECTURE_INVENTORY |

---

## Current Project State (Synthesized from Evidence)

- **Phase**: Re-baselining / containment — no deployment, no feature work
- **Test baseline**: 359 tests, 520 assertions, 0 failures (floor, no regressions allowed)
- **Deployment**: ON HOLD (gated on code-reality audit + Portfolio co-tenancy)
- **Live UAT status**: FAIL — logout session termination + trainee creation broken
- **Documentation**: v2.0 May 2026 (8 user manuals, HANDOVER_PACKAGE, KNOWN_LIMITATIONS)
- **Auth**: Custom session-based via POST /auth/check. 4 active roles (Admin, Supervisor, Teacher, AJK)
- **Centre isolation**: 25 scoped models (23 direct + 2 closure). 2 exceptions (Message, Centre)
- **Security**: Rate limiting active. Security headers deployed. CentreScope operational. 2 RED blockers for deploy (APP_KEY placeholder, Log::debug PII leak)

---

*Generated by automated repository exploration. Do not modify application code. Classification only.*
