# CREAMS — Execution Governance Layer

> **Layer**: Governance — sits ABOVE all execution plans
> **Version**: 1.0
> **Generated**: 10 June 2026
> **Source**: `EXECUTION_TRUTH_MATRIX.md`, `EXECUTION_MASTER_ROADMAP_V2.md`
> **Authority**: This file governs HOW to execute. The batch files govern WHAT to execute. This file wins in any conflict.
> **Scope**: All Waves 1-5. All tasks. All AI agents. All developers.

---

## 1. Execution Rules of Engagement

### 1.1 Prerequisites for Any Execution

Before ANY task in ANY wave is executed, ALL of the following must be true:

- [ ] `EXECUTION_READINESS_REVIEW.md` has been read and understood
- [ ] `EXECUTION_TRUTH_MATRIX.md` has been read — the task's validated status is known
- [ ] The task's status in `EXECUTION_TRUTH_MATRIX.md` is READY (not REQUIRES_SERVER_ACCESS, not BLOCKED, not INVALID_FINDING)
- [ ] All dependencies listed in `EXECUTION_MASTER_ROADMAP_V2.md` for that task are satisfied
- [ ] A rollback strategy exists for the task (from the batch file or defined ad-hoc)
- [ ] `git status` is clean OR all uncommitted changes are intentional and documented
- [ ] Current branch is `Fixers` (unless a dedicated branch has been created for the wave)
- [ ] Pre-commit hook is active: `git config core.hooksPath` returns `.githooks`

### 1.2 Safe Execution Criteria

A task is safe to execute when:

- It modifies ONLY files listed in its `Affected Files` section of the batch document
- No file outside the task's documented scope is touched
- The verification steps from the batch document are followed in order
- Each verification step passes before proceeding to the next
- `php artisan test` is run after any change to `app/` or `config/` — result must be ≥ 359 passing
- The pre-commit hook fires on every commit — never bypass

### 1.3 Unsafe Execution — STOP IMMEDIATELY

Stop execution if ANY of the following occur during a task:

- A file outside the documented `Affected Files` requires modification to complete the task
- `php artisan test` count drops below 359
- A previously-passing isolation test fails (any test in `tests/Feature/Security/`)
- Authentication breaks (any role cannot log in)
- A file not listed in `.gitignore` but containing real data is discovered
- The pre-commit hook blocks a commit (do NOT bypass — fix the commit content)
- A git operation fails with an unexpected error (merge conflict, detached HEAD, etc.)
- A server command returns an unexpected error on Lightsail (for server-access tasks)
- A new finding is discovered that is not in `CRITICAL_FINDINGS_REGISTER.md`

**Response to STOP**: Revert the last change. Document what triggered the stop. Do not proceed until the cause is understood.

---

## 2. Task Classification Enforcement

Every task in every wave has exactly ONE status. The status determines whether and how the task can be executed.

### 2.1 READY

**Definition**: Task is fully validated. All dependencies are met. No server access required. No further investigation needed.

**Execution Rule**: Can proceed immediately. Follow the verification steps in the batch document.

**Evidence Required to Transition from READY to COMPLETE**:
- All verification steps from the batch document passed
- Any test run results captured (count, failures)
- Any file changes confirmed via `git diff`
- Any config changes confirmed via `grep` on the target file

### 2.2 PARTIALLY_RESOLVED

**Definition**: Some aspect of the task is complete, but some remains. The task should be split — the resolved portion is marked done and new subtask(s) created for the remainder.

**Execution Rule**: Do NOT execute the remaining portion until a subtask is created with updated `Affected Files`, `Verification Steps`, and `Rollback Strategy`. The original batch document is authoritative for the resolved portion only.

**Example**: B4-T1 (IRLSeeder gate) — resolved. B5-T8 (GombakDataExtractor) — remaining. Each is now a separate task.

### 2.3 REQUIRES_SERVER_ACCESS

**Definition**: Task is validated but cannot be executed without SSH access to Lightsail (`pdk-creams.org` / `creams.faisalhanafi.com`) or access to live server logs.

**Execution Rule**: Do NOT attempt without confirmed SSH connectivity. Before connecting:
- Verify the target server is the correct one (check hostname: `uname -n`)
- Confirm you are NOT on production if production is live with real users
- Backup any file before modifying it on the server
- Document the exact command run and its output (append to the relevant batch file or `docs/audit/`)

**Evidence Required**:
- SSH session log or command output captured
- Before/after state of any modified server file
- Service restart confirmation (nginx, PHP-FPM)
- Functional test result (e.g., HTTPS active, login works)

### 2.4 ESCALATED

**Definition**: Task severity has been upgraded from the original finding. Requires priority handling over non-escalated tasks in the same wave.

**Execution Rule**: Execute BEFORE other tasks in the same wave. The escalated task's dependencies may cascade — re-check dependencies for subsequent tasks after the escalated task is complete.

**Current Escalated Tasks**:
- B1-T1 (CF-01): real_data_backup.json — escalated because file is git-tracked, not just on disk. Every clone has this file. BFG history rewrite now required.

### 2.5 INVALID_FINDING

**Definition**: The finding the task was based on is incorrect. The task as written cannot be executed.

**Execution Rule**: Do NOT execute. The task is preserved for traceability but marked CLOSED-INVALID in `EXECUTION_TRUTH_MATRIX.md`. If the underlying concern is still valid (e.g., CF-20 Sanctum was thought dormant but is active), a NEW task must be created with corrected scope.

### 2.6 ALREADY_FIXED

**Definition**: The task was resolved by prior work (documentation consolidation, May 2026 sprint, etc.). No action needed.

**Execution Rule**: Verify the fix is still in place. If confirmed: mark CLOSED in `EXECUTION_TRUTH_MATRIX.md`. Do not re-execute. If the fix has regressed: re-open with current state documented.

### 2.7 BLOCKED

**Definition**: The task cannot proceed because a prerequisite is unsatisfied or the finding on which it is based is invalid.

**Execution Rule**: Do NOT execute until the blocker is resolved. Document what is blocking. Re-evaluate status when the blocker is cleared.

---

## 3. Wave Execution Rules

### 3.1 When a Wave Can Start

A wave can start when ALL of its prerequisites from `EXECUTION_MASTER_ROADMAP_V2.md` are met:

- **Wave 1**: No prerequisites — start immediately.
- **Wave 2**: No prerequisites — can run parallel to Wave 1.
- **Wave 3**: SSH access to Lightsail confirmed. DNS resolves to Lightsail IP.
- **Wave 4**: Wave 3 complete. Both UAT blockers diagnosed (stack trace for trainee 500, root cause for logout). All 359 PHPUnit tests passing.
- **Wave 5**: No prerequisites — can run any time, independent of all other waves.

### 3.2 When a Wave Must Stop

A wave must stop immediately if:

- Any STOP CONDITION (Section 8) is triggered
- A task classified as ESCALATED in the same wave fails verification and cannot be recovered by rollback
- `php artisan test` drops below 359 and the regression cannot be isolated to the current task
- A server command on Lightsail returns an error that cannot be diagnosed within the current session
- A file outside the wave's documented scope is affected
- Two consecutive tasks in the same wave fail verification

### 3.3 What Invalidates a Wave Mid-Execution

A wave is invalidated (all completed tasks in that wave must be re-verified) if:

- A git operation that affects the wave's files is performed outside the wave (e.g., merge, rebase, pull)
- The PHPUnit baseline changes for reasons unrelated to the wave (e.g., another developer commits test changes)
- A configuration file modified by the wave is also modified by an external process
- A server configuration change (nginx, PHP, MySQL) occurs on Lightsail during the wave
- A new CRITICAL finding is discovered that involves files in the wave's scope

**Response to invalidation**: Re-run verification steps for all completed tasks in the wave. If any previously-passing verification now fails, diagnose before continuing.

### 3.4 Wave Completion

A wave is complete when:

- ALL tasks in the wave have status COMPLETE
- ALL exit criteria for the wave (from `EXECUTION_MASTER_ROADMAP_V2.md`) are met
- The wave completion checklist (from the batch document) is fully checked
- `php artisan test` count is ≥ 359
- `git status` shows only expected changes (no unexpected modified files)
- The pre-commit hook is still active
- A wave completion checkpoint is written to `.memsearch/memory/YYYY-MM-DD.md`

---

## 4. Data Protection Rules

### 4.1 PDPA Handling Rules

These rules apply to ANY task that touches files containing or potentially containing real trainee data:

| Rule | Applies To |
|------|-----------|
| Never open `database/real_data_backup.json` in an editor — use `head`, `tail`, or `grep` to inspect | B1-T1 |
| Never copy real data to a new location — move, don't copy | All |
| Never commit real data to git — verify with `git diff --cached` before every commit | All |
| If real data is accidentally exposed during a task, STOP. Log the exposure. Do not proceed. | All |
| IRLSeeder must never execute outside `APP_ENV=local` + `APP_DEBUG=true`. Verify gate before any seeder work. | B4-T1, B5-T8 |
| GombakDataExtractor must never execute outside `APP_ENV=local`. Add gate before any other work touching seeders. | B5-T8 |
| UATSeeder is the ONLY seeder permitted on staging/UAT/demo per `STAGING_SEED_POLICY.md` | B2-T4 |

### 4.2 Git History Protection Rules

| Rule | Applies To |
|------|-----------|
| Never use `git push --force` on shared branches (Fixers, main) | All |
| BFG history rewrite (for CF-01 real_data_backup.json) must be planned, not executed ad-hoc. The plan is B4-T2. | B1-T1, B4-T2 |
| Before any history rewrite: tag the current HEAD. Notify all collaborators. Create a backup clone. | B4-T2 |
| 72 IC patterns in git history (CF-07) are deferred to post-delivery. Do NOT attempt cleanup now. | All |
| Archive `.env` APP_KEYs (CF-05) — never commit them. Verify `.gitignore` covers all archive `.env` paths. | B5-T5 |

### 4.3 Production Safety Rules

| Rule | Applies To |
|------|-----------|
| `pdk-creams.org` is the production domain. It may have live users. Assume it does until confirmed otherwise. | Wave 3, Wave 4 |
| Never test on production with real user accounts. Use UATSeeder accounts (`@uat.creams.test`). | Wave 3, Wave 4 |
| Never run database migrations with `--force` on production without `--pretend` first. | Wave 3 |
| Before any nginx change on Lightsail: `sudo cp /etc/nginx/sites-available/creams /etc/nginx/sites-available/creams.backup`. | B2-T5 |
| Before APP_KEY generation: backup `.env.production`. The new key invalidates all existing sessions. | B2-T1 |
| After APP_KEY generation: verify login + CSRF before leaving the server. | B2-T1 |
| LOG_LEVEL must remain `warning` or higher on production until B4-T3 is complete (PII stripped from Log::debug). | Wave 3, Wave 4 |

---

## 5. Verification Requirements

### 5.1 How Each Task Is Marked Complete

A task transitions from READY → COMPLETE when ALL of the following are true:

1. Every verification step in the batch document's `Verification Steps` section has been executed
2. Every step produced the expected result
3. If the task modifies code: `php artisan test` was run and count ≥ 359
4. If the task modifies config: the relevant service was tested (login, API call, dashboard load)
5. If the task modifies server state: the change persists after a service restart
6. The `Completion Criteria` from the batch document are all met
7. Evidence is captured (see 5.2)

### 5.2 What Evidence Is Required

| Task Type | Minimum Evidence |
|-----------|-----------------|
| File deletion/move | `ls` output showing file gone, `git status` showing the change |
| Git tracking change | `git ls-files -- <file>` showing empty output (untracked) |
| Config change | `grep` on the target file showing the new value, functional test result |
| Script modification | `diff` of the script before and after, `bash -n` syntax check passed |
| Server command | SSH command output captured and saved |
| Test run | `php artisan test` output with pass/fail counts |
| Documentation creation | File exists, `wc -l` shows expected line count |
| Credential rotation | Old credential no longer works, new credential works |
| UAT test | Browser screenshot or HTTP response code log |

### 5.3 Evidence Storage

- Local test results: document in the task's batch file or `docs/audit/`
- Server command output: append to `docs/audit/server_validation_YYYY-MM-DD.log`
- Screenshots: store in `docs/audit/screenshots/` (ensure no PII in filenames or content)
- Git operation logs: `git log --oneline -5` after each commit

---

## 6. Rollback Strategy Standard

### 6.1 Pre-Execution Rollback Preparation

Before executing ANY task:

1. Identify the rollback method (from the batch document's `Rollback Strategy` field)
2. If no rollback method is specified, define one: "How do I undo this change if it fails?"
3. For code changes: `git stash` is the universal pre-rollback safety net
4. For config changes: copy the file to `<filename>.backup` before editing
5. For server changes: `cp` the target file to `<filename>.backup-YYYYMMDD` before editing
6. For database operations: `mysqldump` the affected table before modifying

### 6.2 Rollback Triggers

Execute rollback if:

- Any verification step fails
- `php artisan test` count drops below 359
- The application becomes inaccessible (500 error, login failure)
- A STOP CONDITION is triggered
- The task affects more files than documented

### 6.3 Rollback Method by Task Type

| Task Type | Rollback Method |
|-----------|----------------|
| `.gitignore` change | Remove the added line |
| `git rm --cached` | `git reset HEAD <file>` |
| Config file edit | `cp <file>.backup <file>` |
| Script modification | `git checkout -- <file>` |
| Server file edit | `sudo cp <file>.backup-YYYYMMDD <file>` + restart service |
| Server command | Run the inverse command if one exists; otherwise restore from backup |
| Documentation creation | `rm <file>` |
| Seeder execution | `php artisan migrate:fresh --seeder=UATSeeder --force` (non-production only) |

### 6.4 Post-Rollback Verification

After any rollback:

1. Verify the system is in the pre-task state
2. Run `php artisan test` — must be ≥ 359
3. Test login (any role)
4. Document what was rolled back and why
5. Do NOT re-attempt the task until the failure cause is understood

---

## 7. AI Usage Rules

### 7.1 When to Use Each AI Agent

| Agent | Use For | Do NOT Use For |
|-------|---------|---------------|
| **Gemini** | Strategic planning — gap analysis, phased strategy, execution batch design. Use with `GEMINI_INPUT_PACKAGE.md` (7 files + 3 URLs). | Code changes, file operations, server commands. Gemini cannot access the repository. |
| **DeepSeek / Claude Code** | Code changes, file operations, git commands, server interactions (with user supervision). Can read and modify repository files directly. | Strategic planning from scratch — use Gemini's output as input. |
| **OpenCode / Codex CLI** | Session continuity — resume protocol, checkpointing, context management. Follows `CODEX_INIT_PROMPT.md`. | Server operations, production changes without user supervision. |

### 7.2 When NOT to Use AI at All

Do NOT use AI for:

- Rotating production credentials — must be done by a human who can verify the new credential works
- Modifying nginx config on a live production server — human must `nginx -t` and verify
- Deleting files containing real production data — human must verify the file is not needed for recovery
- Issuing SSL certificates — human must verify domain ownership
- Running database migrations with `--force` on production — human must verify backup exists
- Executing `git push --force` — human must coordinate with all collaborators
- Modifying `.githooks/pre-commit` — human must verify the hook still blocks secrets
- Any action that could expose PDPA data to an AI training corpus — assume all AI conversations may be logged

### 7.3 AI Session Handoff

When transitioning between AI agents:

1. Write a checkpoint to `.memsearch/memory/YYYY-MM-DD.md` using the 8-section format from `CODEX_INIT_PROMPT.md` STEP 4
2. Include: current wave, completed tasks, failed tasks, open issues, next action, do-not-repeat
3. The next AI agent must read the latest checkpoint AND `EXECUTION_GOVERNANCE_LAYER.md` before taking any action
4. The next AI agent must re-verify the status of any READY task before executing it — do not trust the previous agent's assessment

---

## 8. STOP CONDITIONS (GLOBAL)

These conditions apply to ALL waves, ALL tasks, ALL agents. They override any per-task or per-wave instruction. If ANY of these trigger, execution stops for the ENTIRE remediation programme until the condition is resolved.

### S1: PDPA EXPOSURE

**Trigger**: Any file discovered containing real Malaysian IC numbers (`######-##-####`), real trainee names, real medical records, or real contact information that is NOT in a known and gated location (IRLSeeder, real_data_backup.json).

**Response**:
1. STOP immediately. Do not read the file further.
2. Log the file path.
3. Add to `CRITICAL_FINDINGS_REGISTER.md` as a new CF entry.
4. Do NOT delete the file — assess whether it's git-tracked first.
5. Do NOT resume execution until the exposure is classified and a containment plan is approved.

### S2: GIT HISTORY CONTAMINATION

**Trigger**: `git log` reveals a commit containing real PDPA data that was not previously documented in `git_history_audit_2026-05-01.log`.

**Response**:
1. STOP. Document the commit hash, author, date, and affected files.
2. Add to IC_HISTORY_CLEANUP_PLAN.
3. Do NOT attempt ad-hoc history rewrite.
4. Do NOT resume execution until the scope of contamination is understood.

### S3: PRODUCTION OUTAGE

**Trigger**: After any server-side change (Wave 3 or Wave 4), `pdk-creams.org` returns 5xx errors, or login fails for all roles, or the site is unreachable.

**Response**:
1. STOP. Restore server config from backup.
2. Restart nginx + PHP-FPM.
3. Verify the site is reachable and login works.
4. Diagnose the cause before re-attempting.
5. Do NOT leave the server in a broken state.

### S4: TEST BASELINE REGRESSION

**Trigger**: `php artisan test` returns fewer than 359 passing tests after ANY code change.

**Response**:
1. STOP. Revert the last change (`git stash` or `git checkout`).
2. Re-run `php artisan test` — must return ≥ 359.
3. Identify which test(s) failed.
4. Understand WHY the change caused the regression before re-attempting.
5. Do NOT proceed with fewer than 359 passing tests.

### S5: CENTRESCOPE ISOLATION FAILURE

**Trigger**: Any test in `tests/Feature/Security/` fails. These tests verify that Centre A cannot access Centre B data.

**Response**:
1. STOP. Revert the last change.
2. Verify the CentreScope `booted()` method on the affected model.
3. Verify `session('centre_id')` is set correctly in the test environment.
4. Do NOT proceed until all isolation tests pass. CentreScope is the primary PDPA boundary.

### S6: AUTHENTICATION REGRESSION

**Trigger**: Any role cannot log in after a code change to `MainController.php`, `SessionManager.php`, or `config/auth.php`.

**Response**:
1. STOP. Revert the change.
2. Test login for all 4 roles (Admin, Supervisor, Teacher, AJK).
3. All 4 must succeed before proceeding.
4. Do NOT deploy any auth-related change that breaks login.

### S7: PRE-COMMIT HOOK BYPASSED

**Trigger**: A commit is made with `--no-verify` without explicit, documented stakeholder approval.

**Response**:
1. STOP. Audit the commit: `git show <commit-hash>`.
2. Verify no secrets, passwords, API keys, AWS keys, or IC patterns in the diff.
3. If secrets found: rotate them immediately.
4. Re-enable the hook: `git config core.hooksPath .githooks`.
5. Document the bypass: who, why, what was committed.

### S8: UNAUTHORISED DEPLOYMENT ATTEMPT

**Trigger**: Any attempt to deploy to production (`pdk-creams.org`) or run `deploy.sh` before ALL Wave 1-4 exit criteria in `EXECUTION_MASTER_ROADMAP_V2.md` are met.

**Response**:
1. STOP the deployment.
2. Verify which exit criteria are not met.
3. Re-enforce: deployment is FROZEN per `CREAMS_SESSION_CURRENT.md`.
4. Do NOT deploy until the pre-deploy checklist is cleared (0 RED, 0 YELLOW).

### S9: EVIDENCE-INCONSISTENCY

**Trigger**: A task's `Verification Steps` pass but the `Completion Criteria` are not met, OR the task claims completion but `EXECUTION_READINESS_REVIEW.md` validation contradicts the claim.

**Response**:
1. STOP. Do not mark the task COMPLETE.
2. Re-run validation checks from `EXECUTION_READINESS_REVIEW.md` for that task.
3. Resolve the inconsistency before proceeding.
4. If the readiness review itself is stale: update it with new validation data.

### S10: IRREVERSIBLE ACTION WITHOUT ROLLBACK

**Trigger**: A task requires an irreversible action (credential rotation on live server, database migration with `--force`, git history rewrite) but no rollback strategy is documented.

**Response**:
1. STOP. Do not execute the irreversible action.
2. Define a rollback strategy.
3. Test the rollback strategy on a non-production environment.
4. Only proceed when rollback is confirmed viable.

---

## Governance Amendment Log

| Date | Change | Author | Reason |
|------|--------|--------|--------|
| 2026-06-10 | v1.0 created | — | Initial governance layer established after execution readiness review |

---

*This governance layer sits above all execution plans. In any conflict between this file and a batch file, this file wins. No task may be executed without satisfying the rules defined here.*
