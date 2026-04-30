# CREAMS — Project Overlay

> **Governance**: The root SOP at `../CLAUDE.md` (repository parent) governs this project.
> This file does not override it. In conflict, root SOP wins.
> **Last containment pass**: 2026-04-24

## Start here

1. Read [`docs/SOURCE_OF_TRUTH.md`](docs/SOURCE_OF_TRUTH.md) — the documentation entry point.
2. Read [`docs/CREAMS_SESSION_CURRENT.md`](docs/CREAMS_SESSION_CURRENT.md) — current mission and DO-NOTs.
3. Follow the root SOP for tiering, validation, commits.

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

*Stale project CLAUDE.md (Feb 2026) remains at `docs/CLAUDE.md` for history; it will be archived in a later cleanup session.*
