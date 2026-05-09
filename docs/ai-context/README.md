# CREAMS — AI Context Archive

**Created**: 2026-05-07
**Maintained by**: Any AI agent working on CREAMS; updated at end of every session.
**Overrides**: Nothing. This archive is a layer on top of existing docs, not a replacement.

---

## Purpose

This archive helps any AI coding agent (Claude Code, Codex, OpenCode, or other) to:

1. Understand CREAMS quickly without re-reading all 80+ documentation files.
2. Resume work from the latest verified state using the `/resume` SOP.
3. Distinguish **implemented** features from **planned** features.
4. Detect deviation between documentation and code before editing anything.
5. Avoid repeating failed approaches that are already logged.
6. Trust current repository evidence over stale summaries.

---

## How to use this archive on `/resume`

Read files **in this exact order**:

1. `docs/ai-context/README.md` ← you are here
2. `docs/ai-context/00_PROJECT_SOURCE_OF_TRUTH.md` — stable facts
3. `docs/ai-context/01_CURRENT_STATUS.md` — current goal and verified state
4. `docs/ai-context/08_DOC_ALIGNMENT/deviation_register.md` — known deviations
5. `docs/ai-context/03_BUG_HISTORY/unresolved_bugs.md` — open bugs
6. `docs/ai-context/03_BUG_HISTORY/failed_attempts.md` — do not repeat these
7. Latest file under `docs/ai-context/02_SESSION_HISTORY/` — most recent session
8. `git status`, `git log --oneline -10`, `git diff --stat` — current repo reality
9. `php artisan migrate:status` — DB state
10. `php artisan test` (or test output in `06_TESTING_EVIDENCE/`) — test state

Then output a **Resume Report** (format defined in `CLAUDE.md`).

---

## Update rules

Every agent **must** update this archive at end of session:

- Always update: `01_CURRENT_STATUS.md`
- Always create: a new session file in `02_SESSION_HISTORY/YYYY-MM-DD_HH-MM.md`
- Update if bugs changed: `03_BUG_HISTORY/`
- Update if DB changed: `04_DATABASE_STATE/`
- Update if modules changed: `05_MODULE_STATUS/`
- Update if tests ran: `06_TESTING_EVIDENCE/`
- Update if preparing handoff: `07_AI_HANDOFF/`
- Update if docs/code alignment changed: `08_DOC_ALIGNMENT/`
- Move superseded context to: `09_ARCHIVE/`

---

## Status label meanings

| Label | Meaning |
|---|---|
| **VERIFIED** | Confirmed by terminal output, test run, or browser behavior |
| **INFERRED** | Strongly implied by code or docs, not directly run |
| **UNVERIFIED** | Mentioned in docs or code but not confirmed by evidence |
| **STALE DOC** | Documentation describes old behavior no longer present |
| **DEVIATION** | Code and docs describe different things |
| **ACCEPTED DEVIATION** | Deviation is intentional; supported by evidence or user decision |
| **POSSIBLE DEFECT** | Suspected mistake; needs human review |
| **PLANNED** | Described in docs or issues but not yet in code |
| **SUPERSEDED** | An older plan replaced by a newer approach |

---

## Critical principle

> **Past session summaries are context, not proof.**
> **Existing docs are intent, not proof.**
> **Current code is reality, but not always correctness.**
> When docs and code diverge, classify the deviation before deciding whether to update docs, update code, or ask the user.

---

## Source-of-truth hierarchy (highest → lowest)

1. Current repository files and actual code
2. Current database state (`php artisan migrate:status`, DB queries)
3. Terminal command output (fresh runs)
4. Browser/Playwright behavior
5. `CLAUDE.md` (root and project overlay)
6. This archive (`docs/ai-context/`)
7. Existing project docs (`docs/01_*` through `docs/10_*`)
8. Past session summaries (`.memsearch/memory/`)
9. Previous AI assumptions or chat memory
