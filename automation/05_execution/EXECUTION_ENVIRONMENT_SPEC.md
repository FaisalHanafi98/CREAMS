# CREAMS — Execution Environment Specification

> **Version**: 1.0
> **Generated**: 10 June 2026
> **Purpose**: Define the dual-environment execution architecture separating planning from file-system operations.
> **Environments**: OpenChamber (planning + reasoning) and OpenCode Desktop (execution + file system)
> **Authority**: This spec governs HOW the two environments interact. It does not create new tasks or modify runbooks.

---

## 1. Environment Definitions

### 1.1 OpenChamber — Planning & Reasoning Environment

**Role**: Strategic planner. Reads evidence, analyses state, produces execution plans. Never touches files.

**Capabilities**:
- Read all files in `automation/00_inventory/`, `automation/02_curated_evidence/`, `automation/04_audits/`, `automation/05_execution/`, `automation/07_generated/`
- Read all files in `docs/Validate/`, `docs/audit/`
- Cross-reference findings against inventories
- Produce execution batch plans
- Produce reconciliation matrices
- Produce governance rules
- Produce runbooks
- Read URLs and external standards

**Prohibitions**:
- MUST NOT modify any file in the repository
- MUST NOT execute shell commands that modify state (git, mv, rm, sed, composer, artisan)
- MUST NOT access the production server (Lightsail)
- MUST NOT run `php artisan test`
- MUST NOT modify `.env` files
- MUST NOT stage or commit changes
- MUST NOT create files outside `automation/` and `docs/`

**Allowed Shell Commands**:
- `ls`, `find`, `grep`, `wc`, `head`, `tail`, `cat` — read-only inspection
- `git log`, `git status`, `git diff`, `git ls-files`, `git worktree list`, `git branch` — read-only git
- `php -l` (syntax check, no execution)
- `bash -n` (syntax check, no execution)

### 1.2 OpenCode Desktop — Execution & File System Environment

**Role**: Executor. Follows runbooks produced by OpenChamber. Modifies files, runs commands, reports results. Never devises strategy.

**Capabilities**:
- Read all files in the repository
- Execute shell commands (git, mv, rm, sed, composer, artisan, npm)
- Modify files per runbook instructions
- Run `php artisan test`
- Stage and commit changes
- Access production server (Lightsail) with user supervision
- Run Playwright tests
- Report execution results back to OpenChamber

**Prohibitions**:
- MUST NOT modify execution plans, runbooks, governance files, or truth matrices
- MUST NOT deviate from the runbook's documented file scope
- MUST NOT execute a task whose status in `EXECUTION_TRUTH_MATRIX.md` is not READY
- MUST NOT bypass STOP POINTS in runbooks
- MUST NOT modify `.githooks/pre-commit`
- MUST NOT use `git push --force`
- MUST NOT execute server commands without user confirmation

---

## 2. Operation Classification

Every operation in every runbook is classified into one of three domains. The classification determines which environment can execute it.

| Domain | Environment | Examples | Rule |
|--------|------------|----------|------|
| **READ** | Both | `ls`, `grep`, `cat`, `git log`, `git status` | Either environment. No restrictions. |
| **PLAN** | OpenChamber ONLY | Write execution batch files, update truth matrix, create governance rules, produce runbooks | OpenCode MUST NOT create or modify planning documents. |
| **EXECUTE** | OpenCode Desktop ONLY | `git rm`, `sed`, `mv`, `rm`, `composer`, `artisan`, server SSH, `git commit` | OpenChamber MUST NOT execute. OpenCode executes only per runbook. |

---

## 3. Execution Handoff Protocol

### 3.1 Runbook → Execution Packet

When OpenChamber produces a runbook (e.g., `WAVE_1_EXECUTION_RUNBOOK.md`), OpenCode Desktop transforms it into an execution packet before proceeding.

**Execution Packet** — a runbook that has passed OpenCode's validation gate:

1. OpenCode reads the runbook end-to-end
2. OpenCode validates the PRE-FLIGHT CHECKLIST (5 checks)
3. OpenCode verifies every action's `Expected Before-State` matches actual reality
4. If validation fails: OpenCode stops, reports discrepancy back to OpenChamber, does not execute
5. If validation passes: the runbook becomes an execution packet and OpenCode proceeds

### 3.2 Task Transfer Format

OpenChamber communicates a task to OpenCode via the runbook's action format:

```
Action ID:     Unique identifier (e.g., 1.3, 2.2, 3.5)
Operation:     INSPECT | EXECUTE | VERIFY | DECISION
Command:       Exact shell command to run
Before-State:  Expected state before execution
After-State:   Expected state after execution
Verification:  How to confirm success
Rollback:      How to undo if verification fails
```

OpenCode MUST execute actions sequentially within an action group. Actions in different action groups may be parallelized only if the runbook explicitly permits it.

### 3.3 Handoff Tokens

After completing each STOP POINT, OpenCode produces a handoff token:

```
=== HANDOFF TOKEN — WAVE 1 — STOP POINT BRAVO ===
Timestamp:    2026-06-10T14:30:00
Status:       PASS
Actions:      1.1, 1.2, 1.3, 1.4, 1.5 — all VERIFIED
Files Changed: .gitignore
Git Status:   M .gitignore
Warnings:     None
Next Action:  Action 2.1
```

OpenChamber reads the handoff token before planning the next wave. If a wave was paused at a STOP POINT, OpenChamber must read the most recent handoff token before resuming planning.

---

## 4. Failure Modes

### 4.1 Execution in Wrong Environment

| Scenario | Detection | Consequence | Recovery |
|----------|-----------|-------------|----------|
| OpenChamber modifies a file | OpenCode detects unexpected file modification via `git diff` | Execution halted. File reverted from git. | OpenChamber session terminated. File restored. |
| OpenCode creates a planning document | OpenChamber detects new file in `automation/05_execution/` or `automation/02_curated_evidence/` not in the plan | Planning invalidated. File quarantined. | File moved to `automation/99_archive/`. OpenChamber re-plans if needed. |
| OpenChamber runs `php artisan test` | Command fails or produces unexpected output | Test environment may be polluted | OpenCode re-runs tests to verify no state change occurred |
| OpenCode modifies a runbook mid-execution | Runbook hash or line count changes during execution | Execution halted. Changes reverted. | Runbook restored from git. Execution restarted from last STOP POINT. |

### 4.2 Execution Deviation

If OpenCode deviates from the runbook (modifies a file not listed, skips a verification step, changes command parameters):

1. **Immediate**: Execution pauses. Deviation logged.
2. **Assessment**: OpenChamber is notified. OpenChamber determines if the deviation is safe.
3. **If safe**: OpenChamber updates the runbook to include the deviation. OpenCode resumes from the action after the deviated one.
4. **If unsafe**: OpenCode rolls back the deviation. Execution resumes from the last passed STOP POINT.

### 4.3 Verification Failure

If an action's `Verification` step fails:

1. OpenCode records the expected vs actual state
2. OpenCode attempts the `Rollback` step
3. OpenCode verifies the rollback restored the pre-action state
4. OpenCode produces a failure token:

```
=== FAILURE TOKEN — WAVE 1 — ACTION 3.3 ===
Timestamp:    2026-06-10T14:45:00
Action:       3.3 — Replace ProdPassword123!
Expected:     grep "ProdPassword123!" returns empty
Actual:       grep found 1 remaining match at line 47
Rollback:     Restored from scripts/server-init.sh.backup-20260610
Resolution:   REQUIRES PLANNING — sed pattern did not match all occurrences
```

OpenChamber reads the failure token and produces an updated action or a new runbook.

---

## 5. Validation Loop

### 5.1 OpenCode → OpenChamber Reporting

After every STOP POINT and at wave completion, OpenCode reports to OpenChamber:

```
=== EXECUTION REPORT — WAVE 1 — STOP POINT CHARLIE ===
Timestamp:    2026-06-10T15:00:00
Actions Executed:  1.1 through 2.4 (10 actions)
Actions Passed:    10
Actions Failed:    0
Files Modified:    .gitignore (2 entries added)
Files Deleted:     0
Git Status:        M .gitignore, D database/real_data_backup.json
Test Baseline:     NOT RUN (Wave 1 — no code changes)
Warnings:          None
Deviations:        None
Next:              ACTION GROUP 3 — Hardcoded Passwords
```

### 5.2 OpenChamber → OpenCode Planning Update

Based on the execution report, OpenChamber may:

- **Continue**: No change needed. OpenCode proceeds to next action group.
- **Re-plan**: A failure requires a new or updated action. OpenChamber produces a revised runbook or action.
- **Escalate**: A finding discovered during execution requires a new CF entry. OpenChamber adds to `CRITICAL_FINDINGS_REGISTER.md` and updates the truth matrix.
- **Invalidate**: The wave's assumptions were wrong. OpenChamber updates `EXECUTION_READINESS_REVIEW.md` and may restructure the wave.

### 5.3 Validation Loop Diagram

```
OpenChamber                    OpenCode Desktop
    │                               │
    │── produces runbook ──────────▶│
    │                               │── validates pre-flight
    │                               │── executes ACTION GROUP
    │                               │── hits STOP POINT
    │◀── handoff token ─────────────│
    │                               │
    │── analyses token              │
    │── continues / re-plans ──────▶│
    │                               │── executes next GROUP
    │                               │── ...
    │◀── execution report ──────────│
    │                               │
    │── wave complete               │
    │── updates truth matrix        │
    │── produces next wave ────────▶│
```

---

## 6. Synchronization Rules

### 6.1 State Consistency

Both environments must agree on the repository state at the start of each action group. The following files form the shared state:

| File | Read By | Updated By | Sync Rule |
|------|---------|------------|-----------|
| `EXECUTION_TRUTH_MATRIX.md` | Both | OpenChamber | OpenChamber updates after wave completion. OpenCode reads before execution. |
| `EXECUTION_READINESS_REVIEW.md` | Both | OpenChamber | OpenChamber updates after validation checks. OpenCode reads for current state. |
| `CRITICAL_FINDINGS_REGISTER.md` | Both | OpenChamber | OpenChamber adds findings. OpenCode reads for risk awareness. |
| `EXECUTION_MASTER_ROADMAP_V2.md` | Both | OpenChamber | OpenChamber updates after wave restructuring. OpenCode reads for wave sequence. |
| `.gitignore` | Both | OpenCode | OpenCode modifies per runbook. OpenChamber reads for planning. |
| `.memsearch/memory/YYYY-MM-DD.md` | Both | OpenCode | OpenCode writes checkpoints. OpenChamber reads for session context. |

### 6.2 Conflict Resolution

If OpenChamber and OpenCode disagree on state:

1. **File on disk wins**: OpenCode's `git status` and filesystem state are authoritative over OpenChamber's planning assumptions.
2. **If file on disk contradicts the truth matrix**: OpenChamber updates the truth matrix. Planning resumes from corrected state.
3. **If file on disk contradicts the runbook**: OpenCode stops. Reports to OpenChamber. OpenChamber updates runbook.
4. **If both environments modified the same file**: The file is reverted to git HEAD. OpenChamber re-plans. OpenCode re-executes.

### 6.3 Checkpoint Synchronization

After each wave completion, OpenCode writes a checkpoint to `.memsearch/memory/YYYY-MM-DD.md`. The checkpoint must include:

- Wave number and name
- Actions executed (count, passed, failed)
- Files changed (paths + operation type)
- Git commit hash (if committed)
- Test baseline (if code was changed)
- Handoff token for next wave

OpenChamber reads the checkpoint before planning the next wave. This ensures OpenChamber's plans are based on the post-wave repository state, not the pre-wave assumptions.

---

## 7. STOP Conditions Across Environments

### 7.1 OpenChamber STOP Conditions

OpenChamber MUST stop planning and NOT produce further runbooks if:

| # | Condition | Trigger |
|---|-----------|---------|
| SC1 | Execution report shows a deviation from the runbook that modified files outside scope | OpenCode reports deviation |
| SC2 | A new CRITICAL finding is discovered during execution | OpenCode discovers new PDPA data |
| SC3 | Test baseline drops below 359 after a wave that was supposed to have no code impact | OpenCode reports test regression |
| SC4 | OpenCode reports a failure that cannot be rolled back | OpenCode reports irreversible failure |
| SC5 | Truth matrix becomes inconsistent with execution reports | 3+ actions in a wave had incorrect before-state assumptions |

### 7.2 OpenCode STOP Conditions

OpenCode MUST stop execution and NOT proceed past the current action if:

| # | Condition | Trigger |
|---|-----------|---------|
| SD1 | Pre-flight check fails | `git status` not clean, wrong branch, hook inactive |
| SD2 | An action's before-state does not match actual state | `grep` or `ls` returns unexpected |
| SD3 | A verification step fails and rollback also fails | Both action and rollback failed |
| SD4 | A file outside the action's documented `Affected Files` requires modification | Runbook scope violation |
| SD5 | `php artisan test` drops below 359 (for code-changing waves) | Test regression |
| SD6 | A git operation fails unexpectedly | Merge conflict, detached HEAD |
| SD7 | Pre-commit hook blocks a planned commit | Hook contains IC pattern or secret |
| SD8 | Server command on Lightsail returns unexpected error | SSH timeout, permission denied, service failure |

### 7.3 Cross-Environment STOP

If EITHER environment hits a STOP condition, BOTH environments pause. No planning or execution resumes until:

1. The STOP condition is documented in the appropriate environment's report
2. The other environment acknowledges the STOP
3. A resolution is agreed (re-plan, rollback, escalate, or accept)

---

## 8. Wave Execution Flow Across Environments

### 8.1 Wave Lifecycle

```
PHASE 1: PLANNING (OpenChamber)
    │
    ├── Read EXECUTION_READINESS_REVIEW.md
    ├── Read EXECUTION_TRUTH_MATRIX.md  
    ├── Read previous wave's handoff tokens
    ├── Produce runbook (WAVE_N_EXECUTION_RUNBOOK.md)
    └── Produce handoff to OpenCode
    │
    ▼
PHASE 2: VALIDATION (OpenCode)
    │
    ├── Read runbook
    ├── Execute PRE-FLIGHT CHECKLIST
    ├── Validate before-state of Action 1.1
    ├── If pre-flight fails → STOP, report to OpenChamber
    └── If pre-flight passes → proceed to PHASE 3
    │
    ▼
PHASE 3: EXECUTION (OpenCode)
    │
    ├── For each ACTION GROUP:
    │   ├── Execute INSPECT actions
    │   ├── Execute EXECUTE actions
    │   ├── Execute VERIFY actions
    │   ├── If action fails → rollback → report to OpenChamber
    │   ├── If action passes → continue
    │   └── At STOP POINT → produce handoff token → send to OpenChamber
    │
    └── At WAVE COMPLETION → produce execution report → send to OpenChamber
    │
    ▼
PHASE 4: RECONCILIATION (OpenChamber)
    │
    ├── Read execution report
    ├── Update EXECUTION_TRUTH_MATRIX.md (mark tasks COMPLETE)
    ├── Update EXECUTION_READINESS_REVIEW.md (if state changed)
    ├── Update CRITICAL_FINDINGS_REGISTER.md (if findings closed/escalated)
    └── If more waves → return to PHASE 1 for next wave
    │
    ▼
END: All waves complete or STOP condition unrecoverable.
```

### 8.2 Wave Transition Rules

When transitioning from Wave N to Wave N+1:

1. **OpenCode** writes a checkpoint to `.memsearch/memory/YYYY-MM-DD.md`
2. **OpenCode** produces a wave completion execution report
3. **OpenChamber** reads the checkpoint and execution report
4. **OpenChamber** verifies all exit criteria for Wave N are met (from `EXECUTION_MASTER_ROADMAP_V2.md`)
5. **OpenChamber** verifies no new findings were introduced during Wave N
6. **OpenChamber** verifies the test baseline has not regressed
7. **If all conditions met**: OpenChamber produces the runbook for Wave N+1
8. **If conditions not met**: Wave N is re-opened. OpenChamber produces corrective actions.

### 8.3 Parallel Wave Allowance

Waves 1 and 2 are marked as parallelizable in `EXECUTION_MASTER_ROADMAP_V2.md`. When running parallel waves:

- Each wave gets its own runbook
- OpenCode executes Wave 1 actions and Wave 2 actions in separate git branches OR ensures no file overlap
- If both waves touch the same file (e.g., `.gitignore`), the waves are serialized for that action
- Handoff tokens include a `Parallel: true/false` flag
- Both waves must complete before Wave 3 begins

---

## 9. Environment Integrity Rules

### 9.1 File Ownership

| Directory | Owner | Rule |
|-----------|-------|------|
| `automation/00_inventory/` | OpenChamber | Written once during discovery. Read-only thereafter. |
| `automation/02_curated_evidence/` | OpenChamber | Written during state compilation. Read-only thereafter. |
| `automation/04_audits/` | OpenChamber | Written during audit. OpenChamber appends new findings. |
| `automation/05_execution/*_RUNBOOK.md` | OpenChamber | Written during planning. Read-only for OpenCode. |
| `automation/05_execution/EXECUTION_TRUTH_MATRIX.md` | OpenChamber | OpenChamber updates after wave completion. |
| `automation/05_execution/EXECUTION_GOVERNANCE_LAYER.md` | OpenChamber | Written once. Read-only for OpenCode. |
| `automation/07_generated/` | OpenChamber | Written once during packaging. Read-only thereafter. |
| Repository files (`app/`, `config/`, `database/`, `routes/`, `.env*`, `scripts/`, `.gitignore`) | OpenCode | OpenCode modifies per runbook. OpenChamber reads only. |
| `.memsearch/memory/` | OpenCode | OpenCode writes checkpoints. OpenChamber reads. |

### 9.2 Contamination Prevention

To prevent planning-execution contamination:

1. OpenChamber's working directory SHOULD be a read-only clone or a separate checkout of the repository
2. If OpenChamber and OpenCode share the same filesystem, OpenChamber MUST NOT be in the same directory as OpenCode's working tree during execution
3. After any OpenCode modification, OpenChamber MUST re-read affected files before producing new plans — never cache file contents across execution phases
4. OpenChamber MUST NOT assume that a file's content at planning time matches its content at execution time — always validate via handoff tokens

---

## 10. Specification Amendment Log

| Date | Version | Change | Author |
|------|---------|--------|--------|
| 2026-06-10 | 1.0 | Initial execution environment specification | — |

---

*This specification governs the interaction between OpenChamber and OpenCode Desktop. It does not modify any runbooks, governance files, or execution plans. In any conflict over operational boundaries, this specification wins.*
