# CREAMS — AGENTS.md (Codex auto-loaded context)

> **Purpose**: This file is auto-loaded by Codex CLI on session start, the same way `CLAUDE.md` is auto-loaded by Claude Code. It points Codex to the governance and resume protocol that already live in this repo.
> **Do not duplicate content here**. If governance changes, update the source file (`CLAUDE.md` or `docs/CODEX_INIT_PROMPT.md`), not this shim.

---

## What you must do at session start

1. **Read `CLAUDE.md`** at the repo root. It contains project rules, role definitions, auth architecture, PDPA constraints, and the Memory Protocol. Every rule there applies to you (Codex) too.

2. **Read `docs/CODEX_INIT_PROMPT.md`** end-to-end. It contains:
   - The resume protocol (read latest `.memsearch/memory/*.md`, summarize, wait)
   - The manual checkpoint protocol (every 10 prompts, before compaction, at session end)
   - The 8-section checkpoint stub format
   - The trust hierarchy
   - Tooling and commit conventions

3. **Execute the resume protocol from `docs/CODEX_INIT_PROMPT.md` STEP 1** before doing any other work. Output the standard summary block, then say "Context reconstructed. Ready to continue — confirm to proceed." and wait for the user.

## Hard reminders that bypass everything else

- No real PDPA data in seeders, factories, tests, commits, or `.memsearch/` checkpoints.
- Roles are Admin, Supervisor, Teacher, AJK (plus Trainee/Parent planned). Not Admin/Manager/Staff/Caretaker.
- Auth is custom session via `POST /auth/check`, not Breeze + Sanctum.
- Production routes are under `/creams/{demo_id}/*`. Direct routes (no prefix) work only in `local`/`testing`.
- Deployment is on hold — no production push this sprint.
- Stale metrics ("329 tests", "13% coverage", etc.) are banned. Use the verified baseline at `docs/audit/test_baseline_2026-04-30.log`.
- Never bypass the pre-commit hook (`--no-verify`) without explicit user approval.

## Why this file is a shim

Three documents already define how the project is run:
- `CLAUDE.md` — project rules, memory protocol, PDPA constraints
- `docs/CODEX_INIT_PROMPT.md` — Codex-specific resume + checkpoint behaviour
- `docs/COMMIT_MESSAGE_SOP.md` — commit format

If this file repeated their content, it would drift. Pointing instead keeps one source of truth per concern.

---

*Created: 2026-04-30 — sprint Day 2*
*Mirror of: `CLAUDE.md` + `docs/CODEX_INIT_PROMPT.md`*
