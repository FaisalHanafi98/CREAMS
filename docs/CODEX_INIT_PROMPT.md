# CREAMS — Codex Session Init Prompt

> **Purpose**: Bootstrap an OpenAI Codex session on the CREAMS project with the same resume + checkpoint behaviour that Claude Code's `/resume` provides.
> **How to use**: Copy the section labelled `=== PASTE THIS BLOCK INTO CODEX AT SESSION START ===` and send it as the first message of a new Codex session.
> **Why this exists**: Claude Code uses custom slash commands and hooks (defined in `.claude/commands/`, `.claude/settings.local.json`, and `scripts/`) that Codex does not understand. The mechanism has to be re-stated inline for Codex.

---

## What this prompt replicates from Claude Code

| Claude Code mechanism | Codex equivalent in this prompt |
|---|---|
| `/resume` slash command | Init prompt with explicit resume protocol |
| `SessionStart` hook → `MEMSEARCH: run /resume` reminder | Inline first-step instruction |
| `UserPromptSubmit` hook → checkpoint stub every 10 prompts | Manual instruction: model self-tracks prompt count, appends stub at 10 |
| `PreCompact` hook → `pre_compaction_checkpoint.sh` | Manual instruction: model writes checkpoint before any context compression |
| `SessionEnd` hook → `session_end_checkpoint.sh` | Manual instruction: model writes final checkpoint when user signals end |
| Auto-memory in `~/.claude/projects/.../memory/` | Codex has no equivalent — only per-project `.memsearch/` is portable |

## What Codex cannot replicate

- **Automatic hooks**: no `UserPromptSubmit`, `PreCompact`, `SessionEnd` triggers. The model has to remember to checkpoint on its own. This is best-effort.
- **Cross-project auto-memory**: stays in Claude Code only.
- **`.claude/settings.local.json` permission allowlist**: Codex has its own permission model.
- **Plugins / skills system**: Codex does not load `.claude/skills/` or `.claude/agents/`.

If Codex CLI is being used, you can also save the prompt below as `AGENTS.md` at the repo root — Codex CLI auto-loads that file the same way Claude Code auto-loads `CLAUDE.md`. If using the Codex API directly, paste the block as the first user message (or as the system prompt).

---

## === PASTE THIS BLOCK INTO CODEX AT SESSION START ===

```
You are working on CREAMS (Community-based Rehabilitation Management System), a Laravel 10 application handling Malaysian PPDK rehabilitation centre data under PDPA. This is an existing production-bound system, not a greenfield project.

## STEP 1 — Run the resume protocol BEFORE any other work

Do not modify files, do not write code, do not propose changes until you have completed all of the following:

1. List the files in `.memsearch/memory/` and read the most recent one end-to-end.
   Command: `ls -t .memsearch/memory/*.md 2>/dev/null | head -3` then read each.
2. Read the project root `CLAUDE.md` (the Claude Code overlay — Codex should follow it too, since it documents project rules and the memory protocol).
3. Read `docs/CREAMS_SESSION_CURRENT.md` for the active mission and DO-NOTs.
4. Read `docs/SOURCE_OF_TRUTH.md` for the documentation index.
5. Summarize what you found in this exact format:

   **Date of last session:** YYYY-MM-DD
   **What was worked on:** [one sentence]
   **Status:** [done / in progress / blocked]
   **Open issues:** [bullet list]
   **Next best action:** [specific enough to act on immediately]
   **Do not repeat:** [failed approaches already ruled out]

6. End your first message with: "Context reconstructed. Ready to continue — confirm to proceed."
7. Do not read, edit, or run anything else until the user says yes.

## STEP 2 — Trust hierarchy

When sources conflict, trust in this order (highest first):
1. Code on disk (the running source)
2. Git history (`git log`, `git blame`, `git show`)
3. Memsearch memory in `.memsearch/memory/` (per-project session checkpoints)
4. Documentation in `docs/` (often drifts — verify against code before relying)

If a memory record names a function, file, or flag, verify it still exists before recommending action on it. Memory is a snapshot, not the present.

## STEP 3 — Hard project rules (do not violate)

- **PDPA**: Real trainee data must never appear in seeders, factories, tests, commits, or session checkpoints. If you have to refer to a real record, refer abstractly ("trainee record edit flow", "centre 04"). Use Faker for all generated data.
- **Roles**: Admin, Supervisor, Teacher, AJK (plus Trainee and Parent planned). NOT Admin/Manager/Staff/Caretaker. Do not invent roles.
- **Auth**: Custom session-based via `POST /auth/check` handled by `MainController@check`. NOT Laravel Breeze + Sanctum.
- **URL architecture**: production routes are under `/creams/{demo_id}/*` (handled by `App\Http\Middleware\DemoInstanceMiddleware`). Direct routes (no prefix) work only in `local` and `testing` environments.
- **CentreScope**: 23 models use `App\Models\Scopes\CentreScope` (Mechanism 1, direct centre_id). 2 models use closure scope `centre_isolation` (Mechanism 2, via asset relationship): `AssetMaintenance`, `AssetMovement`. Do not re-apply CentreScope to already-scoped models — see `docs/MULTI_CENTRE_ISOLATION.md`.
- **Deployment**: ON HOLD pending the 5-day UAT staging sprint. No production push.
- **Stale metrics**: Do not cite "329 tests", "306 tests", "13% coverage", or any pre-2026-04-30 number. The current verified baseline lives in `docs/audit/test_baseline_2026-04-30.log`.

## STEP 4 — Checkpoint protocol (manual — Codex has no hooks)

Claude Code fires checkpoints automatically. Codex must do them manually. Track and act:

**Every 10 user prompts:**
- Append a checkpoint stub to today's `.memsearch/memory/YYYY-MM-DD.md` (create file if absent, today's date format `YYYY-MM-DD.md`).
- Fill all 8 sections from live context BEFORE answering the next user prompt.

**Before any context compression / summarization:**
- Write a checkpoint stub first. Fill it. Then compress.

**At session end (when user says "done", "ending session", "goodbye", or similar):**
- Write a final checkpoint stub. Fill it.

### Checkpoint stub format (use this exactly)

```
## CHECKPOINT — CREAMS — YYYY-MM-DDTHH:MM:SS — [reason: prompt_interval | pre_compaction | session_end]

### Current objective
[what you are trying to achieve right now, one sentence]

### Completed this session
[bulleted list of concrete deliverables; commits, files created, tests run]

### Files changed
[bulleted list of file paths with one-word reason: created / edited / deleted]

### Commands/tests run
[bulleted list of commands; include test pass/fail counts when available]

### Current system state
[branch, working-tree state, test suite state, app/server state, DB state]

### Open issues
[bulleted list — what is still pending or blocked]

### Next best action
[the next concrete step with enough detail to act on immediately]

### Do not repeat
[failed approaches, dead ends, things ruled out — protect future sessions from repeating mistakes]
```

### Checkpoint discipline rules

- Never fabricate a value. If you do not know what tests ran, leave the field blank rather than invent a number.
- Never write real PII into a checkpoint (no real ICs, names, centre identifiers tied to people).
- Append to the existing daily file, do not overwrite.
- A checkpoint that lies is worse than no checkpoint.

## STEP 5 — Tooling and conventions

- **Tests**: `php artisan test`. Current baseline (2026-04-30): 359 tests, 520 assertions, 0 failures.
- **Migrations**: `php artisan migrate`. Always check `php artisan migrate:status` before assuming the DB is up to date.
- **Routes**: 629 total. Inventory at `docs/audit/routes_2026-04-30.json`.
- **Local env**: `APP_URL=http://localhost:8000`, `APP_ENV=local`. Login URL locally is `http://localhost:8000/auth/login`. In staging it will be `http://staging-host/creams/uat/auth/login`.
- **Commit style**: see `docs/COMMIT_MESSAGE_SOP.md`. Format: `Type(Scope): Sentence-case title.` followed by Project / Date / Task / "Verified that:" sections. Sign with `[Assisted by AI, reviewed manually by Faisal]`.
- **Pre-commit hook**: blocks secrets and Malaysian IC patterns. Located at `.githooks/pre-commit`. Excludes `docs/` from password scan but not from IC scan. Never bypass with `--no-verify` without explicit user approval.
- **Branches**: working branch is `Fixers`. Main branch is `main`. Quarantined work lives on `wip/*` branches (e.g. `wip/abandoned-activity-category-2026-04-30`).

## STEP 6 — Begin

Now perform STEP 1 immediately. Do not skip ahead.
```

## === END OF PASTE BLOCK ===

---

## Optional: install as Codex CLI persistent context

If you are using Codex CLI (not the API directly), Codex CLI auto-loads `AGENTS.md` from the repo root the same way Claude Code auto-loads `CLAUDE.md`. To make this prompt persistent across all Codex sessions on this project:

1. Copy the paste-block above into a new file at `AGENTS.md` (repo root).
2. The first time you start a Codex session, just say: "Run STEP 1 of AGENTS.md."
3. Codex will read AGENTS.md automatically and follow the instructions.

Note: `AGENTS.md` is plain markdown with no frontmatter — same convention as `CLAUDE.md`. If you want both Claude Code and Codex to read the same governance, you can have AGENTS.md just contain `Read CLAUDE.md and follow it. Then run the Codex-specific resume protocol below: ...` — that avoids drift.

---

## Honest limits

- Codex will be slower at the resume protocol than Claude Code's `/resume` because it has to read everything inline rather than execute a pre-baked slash command.
- The 10-prompt checkpoint cadence is best-effort — Codex may forget to track if the conversation gets long. If you notice it has stopped checkpointing, ask: "How many prompts since the last checkpoint?" to nudge it.
- The PreCompact equivalent is fragile — Codex does not get notified before its context is compressed, so the checkpoint may miss the most recent turn. Ask the model to checkpoint before you trigger any compression command yourself.
- The pre-commit hook still works (it is a git hook, independent of which AI is driving). Codex commits will be blocked by the same secret/IC patterns that block Claude Code commits.

---

*Created: 2026-04-30 — sprint Day 2*
*Mirrors: `.claude/commands/resume.md`, `scripts/*_checkpoint.sh`, `CLAUDE.md` Memory Protocol section*
