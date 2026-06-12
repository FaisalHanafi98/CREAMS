# CREAMS — Execution Master Roadmap v2

> **Version**: 2.0 — Validated
> **Replaces**: `EXECUTION_MASTER_ROADMAP.md` v1.0
> **Generated**: 2 June 2026
> **Source**: `EXECUTION_TRUTH_MATRIX.md`, `EXECUTION_READINESS_REVIEW.md`, `PRIORITY_FIX_QUEUE.md`
> **Authority**: All 30 tasks reconciled against actual repository state. Obsolete assumptions removed. Invalid findings excluded.

---

## 1. What Changed From v1.0

| Change | Detail |
|--------|--------|
| **3 tasks closed** | B1-T2 (screenshots already cleaned), B2-T2 (LOG_LEVEL already set), B3-T3 (Edge single-run artifact) |
| **1 task escalated** | B1-T1 — real_data_backup.json is git-tracked, not just on disk. Requires BFG history rewrite. |
| **1 task redefined** | B5-T6 — Sanctum is active, not dormant. Rewritten as audit task. |
| **2 tasks downgraded** | B4-T3 — only 3 Log::debug calls remain (not 16). LOG_LEVEL blocks them. B3-T3 removed entirely. |
| **5 tasks gated** | B2-T4, B2-T5, B3-T1, B3-T2, B5-T4 require server access or live environment. |
| **Dead tasks removed** | Original Wave 1-5 numbering preserved for traceability. Completed/invalid tasks noted but no longer in execution order. |

---

## 2. Wave Structure v2

```
WAVE 1: P0 Critical PDPA Risk Closure (6 tasks, 30-45 min)
    │  No server access needed. File operations only.
    │
    ▼
WAVE 2: Repository P1 Hardening (4 tasks, 30-45 min)
    │  Config + documentation changes. No server needed.
    │
    ▼
WAVE 3: Deployment + Server Validation (5 tasks, requires SSH)
    │  Lightsail access required. Can parallel with Wave 2.
    │
    ▼
WAVE 4: UAT Stabilization (2 tasks, requires live diagnosis)
    │  Depends on Wave 3 (server functional). Live testing required.
    │
    ▼
WAVE 5: Technical Debt Cleanup (7 tasks, 2-3 sessions)
    │  No dependencies. Can run any time.
```

---

## WAVE 1: P0 Critical PDPA Risk Closure

**Goal**: Eliminate all real production data exposure on disk and in git tracking.

**Inputs**: `EXECUTION_TRUTH_MATRIX.md`, `CRITICAL_FINDINGS_REGISTER.md` (CF-01, CF-03, CF-04, CF-17)

**Dependencies**: None. Can begin immediately.

**Risks**: LOW — all tasks are file operations. No application code changes. No server access needed.

**Rollback**: All operations are reversible: `.gitignore` entries can be removed, worktrees can be recreated, passwords in scripts can be restored from git history.

### Tasks

| Order | Original ID | Task | Updated Priority | Effort |
|-------|------------|------|-----------------|--------|
| 1 | B1-T4 | Prune `.claude/worktrees/` — remove both worktrees (eliminates 2 copies of real_data_backup.json) | P0 | 5 min |
| 2 | B1-T1 | Remove `database/real_data_backup.json` from git tracking — `git rm --cached`, add to `.gitignore`, plan BFG rewrite | P0 (ESCALATED) | 15 min |
| 3 | B1-T3 | Replace hardcoded passwords in `scripts/server-init.sh` with env var references | P0 | 10 min |
| 4 | B5-T8 | Gate `database/seeders/GombakDataExtractor.php` (matching IRLSeeder pattern) or delete it | P1 | 5 min |
| 5 | B5-T5 | Audit 4 archive `.env` APP_KEYs — compare against current `.env` + `.env.production`. Verify no live key matches. | P2 | 10 min |
| 6 | B2-T3 | Rotate `.env.testing` password — replace `[REDACTED-CF03]` with placeholder. Verify 359 tests pass. | P1 | 10 min |

**Exit Criteria**:
- [ ] `git ls-files -- database/real_data_backup.json` returns nothing (file removed from tracking)
- [ ] `.gitignore` has entry for `database/real_data_backup.json`
- [ ] `git worktree list` shows only main worktree
- [ ] `scripts/server-init.sh` has 0 hardcoded passwords
- [ ] `GombakDataExtractor.php` env-gated or deleted
- [ ] No archive APP_KEY matches current `.env` key
- [ ] `.env.testing` has placeholder password
- [ ] 359 PHPUnit tests still pass
- [ ] Pre-commit hook still active

---

## WAVE 2: Repository P1 Hardening

**Goal**: Close remaining HIGH-priority configuration and documentation gaps. No server access needed.

**Inputs**: `EXECUTION_TRUTH_MATRIX.md`, `CURRENT_SECURITY_STATE.md`, `CODE_QUALITY_INVENTORY.md`

**Dependencies**: None. Can run parallel to Wave 1.

**Risks**: LOW — config-only changes (CORS, pint.json). Documentation tasks (IC cleanup plan, CI divergence doc).

**Rollback**: Revert config files via `git checkout`. Remove created docs.

### Tasks

| Order | Original ID | Task | Effort |
|-------|------------|------|--------|
| 1 | B4-T4 | Restrict CORS `allowed_origins` from `['*']` to known production domains | 5 min |
| 2 | B4-T2 | Create IC history cleanup plan — `docs/04_Development_Planning/IC_HISTORY_CLEANUP_PLAN.md` | 15 min |
| 3 | B5-T6 | Audit active Sanctum usage — map all references in Kernel, api.php, cors.php. Document dual-stack (custom session web + Sanctum API). Verify no conflict. | 15 min |
| 4 | B5-T7 | Create `pint.json` coding standard. Generate from Laravel preset. Enforce in CI (remove `continue-on-error`). | 15 min |

**Exit Criteria**:
- [ ] `allowed_origins` restricted to `https://pdk-creams.org`, `https://www.pdk-creams.org`, `https://creams.faisalhanafi.com`
- [ ] `IC_HISTORY_CLEANUP_PLAN.md` created and linked from `KNOWN_LIMITATIONS`
- [ ] Sanctum active usage mapped — no conflict with custom session auth
- [ ] `pint.json` exists. `./vendor/bin/pint --test` passes. CI lint enforced.

---

## WAVE 3: Deployment + Server Validation

**Goal**: Validate and unblock deployment to Lightsail. Requires SSH access.

**Inputs**: `CURRENT_DEPLOYMENT_STATE.md`, `EXECUTION_TRUTH_MATRIX.md`

**Dependencies**: SSH access to Lightsail server. MySQL root access. DNS configured.

**Risks**: MEDIUM — server config changes (nginx, SSL, APP_KEY generation). Password rotation if server-init.sh was deployed.

**Rollback**: Before any change: backup `/etc/nginx/sites-available/` configs. Before key generation: backup `.env.production`. Before password rotation: backup MySQL user list.

### Tasks

| Order | Original ID | Task | Effort |
|-------|------------|------|--------|
| 1 | B2-T4 | SSH to Lightsail. Run `php artisan migrate:status` — confirm all migrations DONE. Run `php artisan db:seed --class=UATSeeder`. Capture output. If fails, diagnose from error log. | 15-45 min |
| 2 | B2-T5 | SSH to Lightsail. Verify nginx running. Update config from `docs/03_Deployment_Guides/nginx/pdk-creams.org.conf`. `nginx -t` → reload. Issue SSL: `certbot --nginx -d pdk-creams.org`. Verify `certbot renew --dry-run`. | 30 min |
| 3 | B2-T1 | SSH to Lightsail. `php artisan key:generate --env=production`. Verify key in `.env.production`. Restart PHP-FPM. Test login + CSRF. | 5 min |
| 4 | B3-T2 | SSH to Lightsail. Access `storage/logs/laravel.log`. Reproduce trainee creation failure. Capture 500 error stack trace. Document root cause for Wave 4 fix. | 15-30 min |
| 5 | B3-T1 | Live test on pdk-creams.org. Login → navigate to /dashboard → logout → paste /dashboard URL. Capture session cookie state before/after logout. Document root cause for Wave 4 fix. | 15 min |

**Exit Criteria**:
- [ ] UATSeeder completes on Lightsail (3 centres, 16 staff, 21 trainees, 9 activities)
- [ ] HTTPS active on pdk-creams.org with valid SSL
- [ ] Clean routes (no /creams/uat/ prefix in production)
- [ ] Real APP_KEY in production `.env`
- [ ] Trainee creation 500 stack trace captured and root cause documented
- [ ] Logout session persistence reproduced and root cause documented

---

## WAVE 4: UAT Stabilization

**Goal**: Fix 2 persistent live UAT blockers. Re-run full browser UAT. Enable stakeholder demo.

**Inputs**: Wave 3 diagnosis results (trainee 500 stack trace, logout root cause), `CURRENT_UAT_STATE.md`

**Dependencies**: Wave 3 complete (diagnosis must be done before fixes can be designed).

**Risks**: HIGH — code changes to auth and trainee controllers. Any regression breaks authentication or data integrity.

**Rollback**: Before any code change: `git stash`. After fix: run `php artisan test` (must be 359/359). If regression: `git stash pop`.

### Tasks

| Order | Original ID | Task | Effort |
|-------|------------|------|--------|
| 1 | B3-T1 | Fix logout session persistence. Based on Wave 3 diagnosis. Likely fix: change session driver from `file` to `database` on shared host, OR add explicit cookie clearing after `invalidate()`. Verify: reproduce logout → dashboard URL → must redirect to /auth/login. All 4 roles tested. 359 tests pass. | 30-60 min |
| 2 | B3-T2 | Fix trainee creation 500 error. Based on Wave 3 error stack trace. Fix root cause (FK constraint, missing column, file path, or validation). Verify: create trainee with minimal + all fields. Trainee appears in list. 359 tests pass. | 30-90 min |
| 3 | — | Re-run full browser UAT (Chrome + Edge). Verify 0 P0 blockers. Include contact form sweep. Document results. | 30 min |
| 4 | — | If 0 P0 blockers: stakeholder demo can proceed per `DEMO_SCRIPT_2026-05-03.md`. | — |

**Exit Criteria**:
- [ ] Logout terminates sessions on pdk-creams.org (all 4 roles)
- [ ] Trainee creation returns 200 (not 500) on pdk-creams.org
- [ ] Full browser UAT: 0 P0 blockers
- [ ] 359 PHPUnit tests passing
- [ ] Stakeholder demo: READY

---

## WAVE 5: Technical Debt Cleanup

**Goal**: Close all P2 documentation, CI, and hygiene tasks. No code changes to application logic.

**Inputs**: `EXECUTION_TRUTH_MATRIX.md`, `CURRENT_CODE_QUALITY_STATE.md`, `MISSING_ARTIFACTS.md`

**Dependencies**: None. Can run any time, independent of Waves 1-4.

**Risks**: NONE — documentation, config, and cleanup only. No application code changes.

**Rollback**: Revert created files. Restore cleaned temp files from git.

### Tasks

| Order | Original ID | Task | Effort |
|-------|------------|------|--------|
| 1 | B5-T3 | Document CI test divergence — `docs/03_Deployment_Guides/CI_TEST_DIVERGENCE.md`. Tag MySQL-specific tests `@group mysql`. | 15 min |
| 2 | B5-T2 | Clean root temp files — archive `tmp_routes_audit.json` + `routes_analysis.json` to `docs/audit/`. Delete `routes_export.json` (junk). Evaluate `tmp_creams_routes.json`. | 5 min |
| 3 | B5-T1 | Backfill 3 empty checkpoint stubs (May 3, 10, 15). Cross-reference git log. Mark idle or backfill from commit history. | 15 min |
| 4 | B4-T3 | Strip remaining 3 Log::debug PII calls (MainController line 612, NotificationController lines 323+349). Replace with `Log::info()` and exclude PII fields. | 10 min |
| 5 | B5-T4 | Profile activity schedule page with Laravel Debugbar. Measure current N+1 count. Add eager loading. Measure improvement. Document new baseline. | 30-60 min |
| 6 | — | Add `composer audit` to CI workflow. Add `migrate --pretend` before `--force` in deploy script. | 15 min |
| 7 | — | Create database backup script — `scripts/backup-db.sh` with mysqldump + cron instructions + rotation. | 15 min |

**Exit Criteria**:
- [ ] CI divergence documented and mitigated
- [ ] Root directory cleaned of temp files
- [ ] 0 empty checkpoint stubs
- [ ] 0 Log::debug calls with PII
- [ ] Schedule page N+1 count measured and documented
- [ ] `composer audit` in CI
- [ ] `migrate --pretend` before `--force`
- [ ] Database backup script created

---

## 3. STOP Conditions

Execution must HALT and require manual review if:

| # | Condition | Trigger | Response |
|---|-----------|---------|----------|
| S1 | New PDPA exposure discovered | Any file found with real trainee IC numbers, names, or medical records outside known locations (CF-01, CF-06) | Stop. Log location. Add to CF register. Do not delete without review. |
| S2 | Git history contamination discovered | `git log` reveals real data commits not previously documented | Stop. Assess scope. Add to IC_HISTORY_CLEANUP_PLAN. |
| S3 | Production outage risk introduced | nginx `-t` fails, SSL issuance errors, or APP_KEY generation breaks login | Stop. Restore backup configs. Debug before retry. |
| S4 | Test baseline drops below 359 | `php artisan test` count < 359 after any code change (primarily Wave 4) | Stop. Revert last change. Investigate regression. |
| S5 | CentreScope isolation failure | Any isolation test fails during or after changes to scoped models | Stop. Revert. Verify CentreScope `booted()` on affected model. |
| S6 | Authentication regression | Any role cannot log in after Wave 4 auth changes | Stop. Revert. Verify SessionManager + MainController. |
| S7 | Wave 4 fixes cannot be diagnosed | Server logs inaccessible, 500 error stack trace unavailable, logout cannot be reproduced | Stop. Gain log access before proceeding. Do not fix by guesswork. |
| S8 | Sanctum conflict discovered | Wave 2 audit reveals Sanctum API usage conflicts with custom session web auth | Stop. Document conflict. Plan architectural resolution. |
| S9 | Pre-commit hook bypassed | Any commit made with `--no-verify` without stakeholder approval | Stop. Audit the commit. Verify no secrets in diff. |
| S10 | Deployment attempted before gate cleared | Any production push before Waves 1-4 exit criteria met | Stop. Re-enforce deployment freeze per `CREAMS_SESSION_CURRENT.md`. |

---

## 4. Estimated Effort Summary

| Wave | Tasks | Est. Time | Risk | Requires |
|------|-------|-----------|------|----------|
| Wave 1 — PDPA Risk Closure | 6 | 30-45 min | LOW | Git CLI, file editor |
| Wave 2 — Repository Hardening | 4 | 30-45 min | LOW | File editor, Composer |
| Wave 3 — Deployment Validation | 5 | 1.5-3 hrs | MEDIUM | SSH to Lightsail, MySQL root |
| Wave 4 — UAT Stabilization | 4 | 1-3 hrs | HIGH | Wave 3 diagnosis results, PHPUnit |
| Wave 5 — Tech Debt Cleanup | 7 | 1.5-2.5 hrs | NONE | File editor, local server, Debugbar |
| **TOTAL** | **26** | **5-10 hrs** | — | — |

---

## 5. Final Production Readiness Criteria

Before ANY production push, ALL must be true:

- [ ] Wave 1 complete: 0 real data files tracked by git
- [ ] Wave 2 complete: CORS restricted, IC cleanup plan documented, Sanctum audited, coding standard active
- [ ] Wave 3 complete: nginx + SSL active, APP_KEY real, UATSeeder works, both UAT blockers diagnosed
- [ ] Wave 4 complete: 0 P0 UAT blockers, full browser UAT PASS, stakeholder demo completed
- [ ] PHPUnit: 359/359 passing
- [ ] Playwright: 98%+ pass rate
- [ ] CentreScope: 25 isolation tests passing
- [ ] RBAC: 12 role access tests passing
- [ ] Pre-deploy checklist: 0 RED, 0 YELLOW
- [ ] STAGING_SEED_POLICY enforced
- [ ] 0 real IC numbers/emails in codebase (outside known-gated IRLSeeder)
- [ ] Database backup automated and verified
- [ ] Rollback procedure tested

---

*Validated against actual repository state. 3 completed tasks removed. 1 invalid task removed. 1 task redefined. Wave structure reorganized for execution efficiency. All STOP conditions defined.*
