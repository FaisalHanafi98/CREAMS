# CREAMS — Generic AI Agent Transfer Prompt

Use this prompt when handing off to any AI coding agent (Claude Code, Codex, OpenCode, Cursor, etc.).

---

```
You are resuming development of CREAMS (Community-based Rehabilitation Management System), a Laravel 12.58.0 PHP application for Malaysian PPDK rehabilitation centres.

## CRITICAL: Read these files FIRST before doing anything

1. docs/ai-context/README.md
2. docs/ai-context/00_PROJECT_SOURCE_OF_TRUTH.md
3. docs/ai-context/01_CURRENT_STATUS.md
4. docs/ai-context/03_BUG_HISTORY/unresolved_bugs.md
5. docs/ai-context/03_BUG_HISTORY/failed_attempts.md
6. Latest file in docs/ai-context/02_SESSION_HISTORY/

Then verify:
- git status
- git log --oneline -5
- php artisan test (record result)
- php artisan migrate:status

## Architecture facts (do not assume otherwise)

- Auth is NOT Breeze/Sanctum. It is CUSTOM: POST /auth/check in MainController.
- User model maps to 'staffs' table, NOT 'users'. Never query DB::table('users').
- Roles are: Admin, Supervisor, Teacher, AJK (NOT Manager/Staff/Caretaker).
- PDPA applies — never commit real trainee IC numbers, names, or medical data.
- CentreScope is a global scope on 23 models enforcing centre data isolation.
- The demo_route() helper generates URLs for demo-prefixed routes (/creams/{demo_id}/).
  Never call demo_demo_route() — it does not exist.

## Source-of-truth hierarchy

1. Current code and database (highest authority)
2. Terminal output from commands you run
3. Browser/Playwright behavior
4. CLAUDE.md
5. docs/ai-context/ (this archive)
6. docs/01_* through docs/10_* (existing docs — may be stale)
7. Past session summaries (context only, not proof)

## Before editing any file

1. Run php artisan test — record the baseline
2. Check git status — are there uncommitted changes?
3. Read the file you plan to edit
4. Classify any deviation from docs before acting on it

## After your session

Update:
- docs/ai-context/01_CURRENT_STATUS.md
- Create docs/ai-context/02_SESSION_HISTORY/YYYY-MM-DD_description.md
- docs/ai-context/03_BUG_HISTORY/ if bugs changed

## PDPA rules (non-negotiable)

- Never use real trainee names, IC numbers, addresses, or medical info
- Never use real centre names in test data (use UAT Centre A/B/C or similar)
- Use UATSeeder for test data — never IRLSeeder outside APP_ENV=local
- Do not commit .env files

## Current blocker (as of 2026-05-08)

5 test failures from demo_demo_route() typo in resources/views/auth/login.blade.php.
Fix: change demo_demo_route( to demo_route( at lines 424 and 473 of that file.
Deployment to creams.faisalhanafi.com is in progress, blocked at seeding.
```
