---
name: creams-resume
description: Resume a CREAMS (Laravel) project session. Use when the user says /resume, "continue", "resume work", "where were we", or starts a new CREAMS session. Reads governance docs, AI context archive, session history, git state, DB state, and test baselines.
---

# CREAMS — Session Resume Protocol

Run this protocol **before doing any work** on the CREAMS project. Do not modify files, propose changes, or run non-read-only commands until you have completed all steps below and output the Resume Report.

## Hard rules (apply to every CREAMS session)

- **PDPA**: No real trainee data in seeders, factories, tests, commits, or checkpoints. Use synthetic data only.
- **Roles**: Admin, Supervisor, Teacher, AJK only. Not Manager/Staff/Caretaker.
- **Auth**: Custom session-based via `POST /auth/check` (`MainController@check`). Not Breeze/Sanctum.
- **No autonomous commits / migrations / deploys / `--no-verify`** without explicit user approval.
- **No AI attribution in commit messages** per commit `8f89328`.
- **Deploy hold**: Deployment is on hold pending owner decision. Do not push to production.
- **Trust hierarchy**: code on disk > git history > docs > past session summaries.

## STEP 1 — Read governance and context files

Read these in order:

1. `CLAUDE.md` at repo root (project overlay + root SOP)
2. `docs/ai-context/README.md`
3. `docs/ai-context/00_PROJECT_SOURCE_OF_TRUTH.md`
4. `docs/ai-context/01_CURRENT_STATUS.md`
5. `docs/ai-context/08_DOC_ALIGNMENT/deviation_register.md`
6. `docs/ai-context/03_BUG_HISTORY/unresolved_bugs.md`
7. `docs/ai-context/03_BUG_HISTORY/failed_attempts.md`
8. Latest file under `docs/ai-context/02_SESSION_HISTORY/`
9. Latest file under `.memsearch/memory/` (if any)

## STEP 2 — Verify repository and runtime state

Run these read-only commands:

1. `git status`
2. `git log --oneline -15`
3. `git branch --show-current`
4. `php artisan migrate:status`
5. `php -d memory_limit=512M vendor/bin/phpunit --no-coverage`
6. `php artisan route:list` (fails fast if route→controller drift)

If Playwright was last run recently and the user asks about browser state, also run `npx playwright test` from `tests/Browser/` (longer; ask first if not requested).

## STEP 3 — Output the Resume Report

Use this exact format:

```
## Resume Report

**Current goal:** [one sentence]
**Current blockers:** [bulleted list]
**Branch / HEAD:** [branch] @ [commit hash] [ahead/behind origin?]
**Working tree state:** [clean / dirty — list key uncommitted files if dirty]
**DB state:** [all migrations ran / pending / unknown]
**Test state:** [PHPUnit pass/fail/assertions; Playwright pass/fail/skip if known]
**Docs/code alignment:** [any known deviations or stale docs]
**Known deviations:** [list from deviation_register + anything new you noticed]
**Immediate safe next action:** [specific]
**Actions requiring user confirmation:** [list]
```

## STEP 4 — Wait for confirmation

End your resume message with exactly:

> Context reconstructed. Ready to continue — confirm to proceed.

Then WAIT for the user to say yes before reading, editing, or running anything else.

---

## Session checkpoint protocol

Every 10 prompts (or at session end / before compaction), append a checkpoint stub to `.memsearch/memory/YYYY-MM-DD.md`:

```
## CHECKPOINT — CREAMS — YYYY-MM-DDTHH:MM:SS — [reason]

### Current objective
### Completed this session
### Files changed
### Commands/tests run
### Current system state
### Open issues
### Next best action
### Do not repeat
```

Never fabricate values. If you don't know a test count, leave it blank.

---

## Stable project facts (verified baseline)

Use these as hints, but re-verify with live commands in STEP 2:

- **Framework**: Laravel 12, PHP 8.2+, MySQL 8.0+
- **Frontend**: Blade + Bootstrap 5 (Tailwind config exists but is unused)
- **Auth**: `POST /auth/check`, `MainController@check`
- **Roles**: Admin, Supervisor, Teacher, AJK
- **Primary auth table**: `staffs` (User model points here)
- **Local URL**: `http://localhost:8000`
- **Login URL**: `http://localhost:8000/auth/login`
- **Smoke-test credentials** (UATSeeder): `super.admin@uat.creams.test` / `UatPass2026!`
- **PHPUnit command**: `php -d memory_limit=512M vendor/bin/phpunit --no-coverage`

## Known recent state (verify; do not trust blindly)

The 2026-06-23 remediation session left the project at:

- `Fixers` branch, HEAD `8e1e2ff`, pushed to origin
- PHPUnit: 395/0
- Playwright: 215/0/3
- 6 hard-500s eliminated, 7 models cleaned, 13 phantom learning-outcomes routes removed, IEP phantom eager-loads removed
- Remaining blockers: PHANTOM-01 (classes), CF-08 (production LOG_LEVEL), 4 asset models (broken write paths), pre-commit hook regex (task_078c8612)

**Do not assume this state is current.** Run the commands in STEP 2 to confirm.
