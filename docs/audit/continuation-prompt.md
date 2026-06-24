> **CREAMS continuation prompt — paste this into a fresh AI session to resume work.**

You are continuing a CREAMS (Laravel) project session. Do NOT start coding until you have read the context below and confirmed the current state.

---

## 1. Read these files first (in order)

1. `CLAUDE.md`
2. `docs/CODEX_INIT_PROMPT.md`
3. `.memsearch/memory/2026-06-24.md`
4. `docs/ai-context/02_SESSION_HISTORY/2026-06-24_phantom02-block5-playwright-fix.md`
5. `docs/ai-context/02_SESSION_HISTORY/2026-06-24_session-wrap-resume-skill.md`
6. `docs/ai-context/01_CURRENT_STATUS.md`

---

## 2. Verify current state

Run and report:

```bash
git status
git log --oneline -10
php -d memory_limit=512M vendor/bin/phpunit --no-coverage
npx playwright test
```

Expected baseline:
- Branch: `Fixers`
- HEAD: `8e1e2ff`
- Working tree: clean
- PHPUnit: 395/0
- Playwright: 215/0/3

If any metric differs, stop and investigate before proceeding.

---

## 3. Next best action

Start with **PHANTOM-01 (Classes feature)**:

- `GET /teacher/schedule` currently fails because `ClassController` and the `classes` table are missing.
- Investigate all `class`/`schedule` references in routes, controllers, views, tests, and migrations.
- Decide with the owner whether to **implement** (restore/create controller, migration, views) or **remove** (delete routes, references, tests).
- Make minimal, safe changes; run full PHPUnit + Playwright after each change.

---

## 4. Remaining open issues (priority order)

| Priority | ID | Description |
|---|---|---|
| 1 | **PHANTOM-01** | Classes feature — implement or remove |
| 2 | **task_d9a381b5** | 4 deferred asset models + `LearningOutcome` cascade cleanup |
| 3 | **B5-01** | `profile.blade.php` missing for `/staff/updateuser/{id}` |
| 4 | **task_078c8612** | Pre-commit hook regex false positive on `Hash::make` |
| 5 | **CF-08** | Production `LOG_LEVEL` on Hostinger — deploy hold |

---

## 5. Hard guardrails

- **Do NOT deploy.** Deployment is on hold until CF-08 is resolved and owner approves.
- **Do NOT remove the `LearningOutcome` model.** It is a non-Eloquent shim still referenced by live IEP/ActivityWizard code.
- **Do NOT restore** `progressReports` or `learningOutcome` eager-loads in `IepController`.
- **Do NOT use** `text=/goal/i, text=/objective/i` CSS comma unions in Playwright.
- **Do NOT use** `GET /auth/logout` — use POST via Logout button.
- **Do NOT assume** Breeze/Sanctum. Auth is custom session via `POST /auth/check`.
- **Do NOT commit on `main`** without explicit owner go-ahead.
- **No real PDPA data** in seeders, factories, tests, commits, or `.memsearch/`.
- Never bypass the pre-commit hook (`--no-verify`) without explicit user approval.

---

## 6. After context reconstruction

Output the standard CREAMS summary block, then say:

> "Context reconstructed. Ready to continue — confirm to proceed."

Wait for user confirmation before writing any code.
