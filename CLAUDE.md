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

*Stale project CLAUDE.md (Feb 2026) remains at `docs/CLAUDE.md` for history; it will be archived in a later cleanup session.*
