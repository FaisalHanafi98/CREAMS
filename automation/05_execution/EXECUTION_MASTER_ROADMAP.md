# CREAMS — Execution Master Roadmap

> **Phase**: Phase 2 — Execution Planning
> **Generated**: 31 May 2026
> **Source**: `CURRENT_SYSTEM_STATE.md`, `CURRENT_BLOCKERS.md`, `CURRENT_SECURITY_STATE.md`, `CURRENT_UAT_STATE.md`, `CURRENT_DEPLOYMENT_STATE.md`, `PRIORITY_FIX_QUEUE.md`, `CRITICAL_FINDINGS_REGISTER.md`
> **Authority**: All 295 inventory entries + 20 critical findings mapped to executable tasks.

---

## 1. Executive Summary

CREAMS is a Laravel 12.x Malaysian rehab centre management system with 4 active roles, custom session auth, and 25 CentreScoped models for PDPA isolation. The project is in a **re-baselining phase with deployment frozen**. The codebase has 359 passing PHPUnit tests (floor), 181/210 Playwright tests (86.2%), and 2 persistent live UAT blockers on `pdk-creams.org`.

**Risk landscape**: 4 CRITICAL PDPA findings on disk, 8 HIGH security/credential risks, 7 MEDIUM cleanup items, 5 P0 deployment blockers.

**This roadmap** defines 5 execution waves sequenced by impact: security first (PDPA risks on disk), then deployment blockers, then UAT fixes, then P1 security hardening, then technical debt cleanup. Each wave has clear entry/exit criteria and verification gates.

---

## 2. Current Project Position

| Metric | Value | Source |
|--------|-------|--------|
| Phase | Re-baselining / containment | `CREAMS_SESSION_CURRENT.md` |
| Deployment | Frozen — no production push | `CREAMS_SESSION_CURRENT.md` |
| Test floor | 359 PHPUnit, 0 failures | `test_baseline_2026-04-30.log` |
| Codebase | Fixers branch, 14 uncommitted files | `01_CURRENT_STATUS.md` |
| Playwright | 181/210 (86.2%) | `PLAYWRIGHT_FINDINGS_2026-05-04.md` |
| Live UAT | FAIL (2 persistent blockers) | All May 15-18 audit files |
| Pre-deploy gate | NOT CLEARED (1 RED, 2 YELLOW) | `PRE_DEPLOY_SECURITY_CHECKLIST.md` |
| Documentation | Consolidated — 88 active files | `DOCS_MASTER_INDEX.md` |

---

## 3. Risk Distribution

| Severity | Count | Covered By |
|----------|-------|------------|
| CRITICAL (PDPA/Security on disk) | 4 | Wave 1 — EXECUTION_BATCH_01 |
| HIGH (Deployment blockers) | 5 | Wave 2 — EXECUTION_BATCH_02 |
| HIGH (UAT blockers) | 2 | Wave 3 — EXECUTION_BATCH_03 |
| HIGH (Security hardening) | 4 | Wave 4 — EXECUTION_BATCH_04 |
| MEDIUM/LOW (Technical debt) | 8 | Wave 5 — EXECUTION_BATCH_05 |

---

## 4. Phase Sequencing

```
WAVE 1: P0 Security (CF-01, CF-02, CF-03, CF-04)
    │  CRITICAL PDPA risks on disk. Must resolve before anything else
    │  touches the repo. Prevents further data exposure.
    │
    ▼
WAVE 2: P0 Deployment (CF-10, CF-11, deploy blockers)
    │  Pre-deploy gate items + server configuration. Required before
    │  any production push. Can run parallel to Wave 1 (different files).
    │
    ▼
WAVE 3: P0 UAT (Logout, Trainee Create, Contact Form)
    │  Live UAT blockers on pdk-creams.org. Required before stakeholder
    │  demo or any production-adjacent activity. Depends on Wave 2
    │  (server must be functional for testing).
    │
    ▼
WAVE 4: P1 Security (CF-06, CF-07, CF-08, CF-09)
    │  HIGH security hardening. IRLSeeder gating, IC history, PII logging,
    │  CORS restriction. Depends on Wave 1 (PDPA awareness).
    │
    ▼
WAVE 5: Technical Debt (CF-13 through CF-20 + P2 items)
    │  Checkpoint stubs, temp files, CI parity, N+1 queries, archive cleanup,
    │  Sanctum dormancy, coding standards, backup scripts. Can run independently.
```

---

## 5. Dependency Mapping

```
Wave 1 (P0 Security) ──no dependencies──▶ Start immediately
    │
    ├──▶ Wave 2 (P0 Deployment) can run parallel to Wave 1
    │
    ├──▶ Wave 3 (P0 UAT) depends on: Wave 2 (server functional)
    │
    ├──▶ Wave 4 (P1 Security) depends on: Wave 1 (PDPA awareness)
    │
    └──▶ Wave 5 (Tech Debt) ──no hard dependencies──▶ Run any time
```

---

## 6. Estimated Remediation Waves

| Wave | Batch File | Tasks | Estimated Sessions | Risk Level |
|------|-----------|-------|--------------------|------------|
| 1 | `EXECUTION_BATCH_01_P0_SECURITY.md` | 4 | 1-2 sessions | LOW (file operations only, no code changes) |
| 2 | `EXECUTION_BATCH_02_P0_DEPLOYMENT.md` | 5 | 1-2 sessions | MEDIUM (server config changes, credential rotation) |
| 3 | `EXECUTION_BATCH_03_P0_UAT.md` | 2 | 1-2 sessions | HIGH (code changes to auth + trainee controllers) |
| 4 | `EXECUTION_BATCH_04_P1_SECURITY.md` | 4 | 1-2 sessions | MEDIUM (config changes, seeder modifications, history audit) |
| 5 | `EXECUTION_BATCH_05_TECHNICAL_DEBT.md` | 8 | 2-3 sessions | LOW (cleanup, documentation, CI config) |

---

## 7. Exit Criteria for Each Wave

### Wave 1: P0 Security
- [ ] `database/real_data_backup.json` git-tracking status verified and file secured
- [ ] `docs/audit/screenshots/` sanitized — no real production emails in JSON
- [ ] `scripts/server-init.sh` passwords replaced with env var references (or confirmed never deployed)
- [ ] `.claude/worktrees/` pruned or gitignored
- [ ] Pre-commit hook still active and passing

### Wave 2: P0 Deployment
- [ ] `.env.production` APP_KEY is a real generated key
- [ ] Production LOG_LEVEL set to `warning` (or PII stripped from Log::debug)
- [ ] `.env.testing` real password rotated
- [ ] UATSeeder runs on Lightsail server
- [ ] nginx config updated for clean production routing
- [ ] SSL certificate issued and verified
- [ ] PHP version mismatch resolved

### Wave 3: P0 UAT
- [ ] Logout terminates sessions on pdk-creams.org
- [ ] Trainee creation returns 200 (not 500) on pdk-creams.org
- [ ] Full browser UAT re-run: 0 P0 blockers
- [ ] Stakeholder demo can proceed

### Wave 4: P1 Security
- [ ] IRLSeeder env gate verified (cannot bypass)
- [ ] IC pattern exposure in git history documented with cleanup plan
- [ ] Log::debug PII calls removed or gated behind LOG_LEVEL
- [ ] CORS restricted to known production origins

### Wave 5: Technical Debt
- [ ] 6 empty checkpoint stubs resolved
- [ ] Root-level temp files cleaned
- [ ] CI test parity documented or resolved
- [ ] N+1 query optimization baseline measured
- [ ] Archive .env APP_KEYs verified non-live
- [ ] pint.json coding standard created
- [ ] Database backup script deployed
- [ ] Sanctum dormancy documented

---

## 8. Final Production Readiness Criteria

Before ANY production push, ALL of the following must be true:

- [ ] All Wave 1 tasks complete (0 CRITICAL PDPA findings on disk)
- [ ] All Wave 2 tasks complete (pre-deploy gate CLEARED — 0 RED, 0 YELLOW)
- [ ] All Wave 3 tasks complete (0 P0 UAT blockers)
- [ ] PHPUnit: 359/359 passing (floor maintained)
- [ ] Playwright: 98%+ pass rate (up from 86.2%)
- [ ] CentreScope: all 25 isolation tests passing
- [ ] RBAC: all 12 role access tests passing
- [ ] CSRF: 93 @csrf directives verified
- [ ] Rate limiting: all 9 limiters active
- [ ] Security headers: all 7 deployed
- [ ] Session regeneration: verified on login AND logout
- [ ] APP_KEY: real generated key in production
- [ ] LOG_LEVEL: warning or higher in production
- [ ] STAGING_SEED_POLICY: enforced on staging
- [ ] PDPA: 0 real IC numbers/emails in test data, screenshots, or logs
- [ ] Deployment runbook: tested and verified
- [ ] Rollback procedure: tested and verified
- [ ] Database backup: automated, verified restore
- [ ] Stakeholder demo: completed successfully
- [ ] Code-reality audit: completed per `CREAMS_SESSION_CURRENT.md`

---

## 9. STOP CONDITIONS

Execution should **halt immediately** and require manual review if any of the following occur:

| # | Condition | Trigger | Response |
|---|-----------|---------|----------|
| S1 | Production data discovered in unexpected location | Any file found containing real trainee names, IC numbers, or medical records outside known locations | Stop. Log location. Add to CF register. Do not delete without review. |
| S2 | New P0 vulnerability discovered | Any new CRITICAL finding not in the existing 20-finding register | Stop. Add to CF register. Re-evaluate wave sequencing. |
| S3 | Test baseline drops below floor | PHPUnit test count falls below 359 after any code change | Stop. Revert last change. Investigate regression. |
| S4 | CentreScope isolation failure | Any isolation test fails during or after changes to scoped models | Stop. Revert. Verify CentreScope booted() on affected model. |
| S5 | Authentication regression | Any role cannot log in after changes to auth controllers or session config | Stop. Revert. Verify SessionManager and MainController. |
| S6 | Pre-commit hook bypassed | Any commit made with `--no-verify` without explicit stakeholder approval | Stop. Audit the commit. Verify no secrets or IC patterns in diff. |
| S7 | Real data discovered in seeders or tests | IRLSeeder exec outside local env gate, or real ICs found in test data | Stop. Verify no data leaked. Harden env gate. |
| S8 | Deployment attempted without clearance | Any production push attempt before all Wave 1-3 exit criteria met | Stop. Verify who initiated. Re-enforce deployment freeze. |
| S9 | CentreScope applied to Centre model | Centre self-scoping creates circular dependency | Stop. Revert. Document rationale. |
| S10 | Auth stack migration attempted | Any attempt to install Breeze, Sanctum, or Passport | Stop. Revert. Custom session auth is the project standard. |

---

*Roadmap generated from 295 inventory entries + 20 critical findings. Every task traceable to source evidence. No new findings.*
