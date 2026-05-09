# CREAMS — Project Overlay

> **Governance**: The root SOP at `../CLAUDE.md` (repository parent) governs this project.
> This file does not override it. In conflict, root SOP wins.
> **Last containment pass**: 2026-04-24

## Start here (AI Context Archive — updated 2026-05-07)

1. Read [`docs/ai-context/README.md`](docs/ai-context/README.md) — the AI context archive entry point.
2. Read [`docs/ai-context/00_PROJECT_SOURCE_OF_TRUTH.md`](docs/ai-context/00_PROJECT_SOURCE_OF_TRUTH.md) — stable project facts.
3. Read [`docs/ai-context/01_CURRENT_STATUS.md`](docs/ai-context/01_CURRENT_STATUS.md) — current goal and verified state.
4. Follow the root SOP for tiering, validation, commits.

> Legacy entry points (`docs/SOURCE_OF_TRUTH.md`, `docs/CREAMS_SESSION_CURRENT.md`) are still valid but now superseded by `docs/ai-context/` for AI agent use.

## What this file is not

This is **not** a system description, not a tech-stack reference, not a feature list, not a test-coverage claim. Older project CLAUDE.md files made all of those claims and drifted. Do not rely on any prior CLAUDE.md content.

## Hard rules for CREAMS sessions

- Do **not** follow archived prompts under `docs/archive/prompts/`.
- Do **not** assume current state from documentation. Verify through code, `php artisan test`, and the running app before claiming anything is done.
- Do **not** deploy. Deployment is on hold pending reality audit.
- Do **not** use stale numbers ("329 tests", "13% coverage", "306 tests", etc.) — measure first.
- Roles are **Admin, Supervisor, Teacher, AJK** (+ Trainee, Parent planned). Not Admin/Manager/Staff/Caretaker. See ADR-002.
- Auth is **custom session-based** via `POST /auth/check`. Not Breeze + Sanctum.
- Deployment target is **Lightsail $5 shared** (pending verification). Not Vercel, not ECS.

## Data confidentiality

CREAMS handles real trainee data under PDPA. Use anonymised data in tests/seeders/factories. Never commit real ICs, names, centres, or personal information.

---

## Memory Protocol

### Two-layer architecture (separate roles)

1. **Auto-memory (cross-project)** — `~/.claude/projects/.../memory/`
   Long-lived persona, framework, preferences. Do NOT touch from session work.

2. **Memsearch (per-project)** — `.memsearch/memory/YYYY-MM-DD.md`
   Session checkpoints, per-turn summaries. Recall via `/resume`.
   Collection: `ms_creams_91bc6b96` (git-ignored under `.memsearch/`).

**Trust hierarchy:** code on disk > git history > memsearch memory > auto-memory.

### Rules

- **New session:** run `/resume` before any work. Wait for the confirmation summary.
- **Every 10 prompts:** the prompt-counter hook appends a checkpoint stub to today's memory file and emits a `systemMessage` instructing the agent to fill all 8 sections from live context before answering.
- **Before `/compact`:** the `PreCompact` hook fires `scripts/pre_compaction_checkpoint.sh` — fill the stub before compression.
- **Session close:** the `SessionEnd` hook fires `scripts/session_end_checkpoint.sh` — fill the final stub.
- **Never** fabricate file names or test results in checkpoints — leave the field blank.
- **Never** write session state into auto-memory.

### PDPA constraint (CREAMS-specific)

Checkpoint stubs live on disk in `.memsearch/memory/`. They MUST NOT contain:
- Real trainee names, ICs, addresses, or contact details
- Real centre names tied to identifiable individuals
- Any PII from production data, screenshots, or seeders

If a session touched such data, refer to it abstractly ("trainee record edit flow", "centre 04") — leave specifics blank. `.memsearch/` is in `.gitignore`, but the disk artefact still exists locally.

---

## AI Context Archive

**Location**: `docs/ai-context/`
**Purpose**: Structured project memory for AI agent session transfer, `/resume` behaviour, and deviation tracking.

### Source-of-truth hierarchy (highest → lowest)

1. Current repository code
2. Current database state (`php artisan migrate:status`, DB queries)
3. Terminal output (fresh runs — not cached)
4. Browser/Playwright behavior
5. `CLAUDE.md` (root) and this overlay
6. `docs/ai-context/` archive files
7. `docs/01_*` through `docs/10_*` (existing docs — may be stale)
8. `.memsearch/memory/` session checkpoints (context, not proof)
9. Any previous AI assumptions or chat memory

> **Critical principle**: Past session summaries are context, not proof. Existing docs are intent, not proof. Current code is reality, but not always correctness. When docs and code diverge, classify the deviation before deciding whether to update docs, update code, or ask the user.

### `/resume` SOP

When starting a new session, the agent must:

1. Read `docs/ai-context/README.md`
2. Read `docs/ai-context/00_PROJECT_SOURCE_OF_TRUTH.md`
3. Read `docs/ai-context/01_CURRENT_STATUS.md`
4. Read `docs/ai-context/08_DOC_ALIGNMENT/deviation_register.md`
5. Read `docs/ai-context/03_BUG_HISTORY/unresolved_bugs.md`
6. Read `docs/ai-context/03_BUG_HISTORY/failed_attempts.md`
7. Read the latest file under `docs/ai-context/02_SESSION_HISTORY/`
8. Run `git status`, `git log --oneline -10`, `git diff --stat`
9. Run `php artisan migrate:status`
10. Check test state (`php artisan test` or last log in `docs/audit/`)

Then output a **Resume Report**:

```
## Resume Report
Current goal:
Current blockers:
Branch / HEAD:
Working tree state:
DB state:
Test state:
Docs/code alignment:
Known deviations:
Immediate safe next action:
Actions requiring user confirmation:
```

### Documentation alignment SOP

Before editing any file:
1. Read the file to understand current state.
2. Check `docs/ai-context/08_DOC_ALIGNMENT/deviation_register.md` for known deviations.
3. If docs and code disagree, classify the deviation — do not auto-fix.
4. Use status labels: VERIFIED / INFERRED / UNVERIFIED / STALE DOC / DEVIATION / ACCEPTED DEVIATION / POSSIBLE DEFECT / PLANNED / SUPERSEDED.

### Deviation classification SOP

When docs and code diverge, evaluate:
1. Did user requirements change?
2. Did the implementation intentionally improve the architecture?
3. Did the previous documentation become stale?
4. Is the deviation causing a runtime, DB, UI, or test failure?

Classify then decide:
- ACCEPT CURRENT CODE AND UPDATE DOCS
- REVERT CODE TOWARD DOCS
- UPDATE BOTH CODE AND DOCS
- KEEP AS TEMPORARY WORKAROUND
- ASK USER FOR DECISION
- NEEDS MORE EVIDENCE

### End-of-session update SOP

Before ending a session or transferring to another AI:
1. Update `docs/ai-context/01_CURRENT_STATUS.md`
2. Create `docs/ai-context/02_SESSION_HISTORY/YYYY-MM-DD_description.md`
3. Update `docs/ai-context/03_BUG_HISTORY/` if bugs changed
4. Update `docs/ai-context/04_DATABASE_STATE/` if DB/migrations changed
5. Update `docs/ai-context/06_TESTING_EVIDENCE/` if tests ran
6. Update `docs/ai-context/08_DOC_ALIGNMENT/` if new deviations found

---

*Stale project CLAUDE.md (Feb 2026) remains at `docs/CLAUDE.md` for history. Do not trust it — it describes Laravel 10, Breeze+Sanctum, and 6 wrong roles.*
