# CREAMS — Current Status

**Last updated**: 2026-06-14
**Evidence basis**: git log, git status, php artisan test (local), npx playwright test (full suite), session checkpoints 2026-06-13 and 2026-06-14

---

## Current goal

Branch `Fixers` is the active development branch. All immediate test harness
defects have been resolved. Deployment remains on hold pending owner decision.

---

## Current branch and commits

| Field | Value |
|---|---|
| Branch | `Fixers` |
| HEAD | `d49e8bb` — Chore(Gitignore): Ignore Playwright test-results directory. |
| Previous commit | `6f8a62c` — Test(Browser): Fix three Playwright defect regressions |
| Working tree | **Clean** — no uncommitted changes |
| Pushed to origin | YES — `8f89328..d49e8bb` fast-forward, 2026-06-14 |

---

## Current test state (VERIFIED 2026-06-14)

### PHPUnit

| Metric | Value |
|---|---|
| Passed | **377** |
| Failed | **0** |
| Assertions | 611 |
| Duration | 60.4s |

Command: `php -d memory_limit=1G artisan test`

### Playwright (full suite — chromium)

| Metric | Value |
|---|---|
| Passed | **215** |
| Skipped | **3** (pre-existing `test.skip()` in staff-crud.spec.ts) |
| Failed | **0** |
| Total | 218 |
| Duration | 21.3m |

Command: `npx playwright test --reporter=list` from `tests/Browser/`

---

## Resolved defects (this sprint — 2026-06-13 to 2026-06-14)

| ID | Description | Result |
|---|---|---|
| PW-001 | Activity wizard CRUD — instructor qualification seeding, learning_outcomes type mismatch, difficulty_level capitalisation | 19/19 ✓ |
| PW-002 | Trainee CRUD — BS5 selector mismatches, data uniqueness | 17/17 ✓ |
| PW-003 | Asset management — wrong route `/assets` → `/centre/assets` | 9/9 ✓ |

All fixes are confined to `tests/Browser/` — no application source code was changed.

---

## Deployment target (VERIFIED)

| Field | Value |
|---|---|
| Host | Hostinger shared hosting |
| Live URL | `https://pdk-creams.org` |
| Deploy method | Manual SSH `git pull` triggered by `.github/workflows/deploy.yml` |
| **Status** | **ON HOLD** — hard rule set by owner |

> The previous status doc (2026-05-08) described an AWS Lightsail target
> (`54.169.32.54`, `creams.faisalhanafi.com`). That target is superseded.
> The live site is `pdk-creams.org` on Hostinger. Do not reference Lightsail.

---

## Current blockers

1. **Owner decision required — deployment hold**
   The CREAMS CLAUDE.md contains a hard rule: "Do not deploy. Deployment is on
   hold pending reality audit." This must be explicitly lifted by the owner
   before any push to production is attempted.

2. **PDPA cleanup — real data in tracked files**
   Real email addresses were found in `automation/` scripts committed to the
   repository. A backup file (`CF-01` or similar) containing real data may
   also exist in git history. These must be scrubbed before production
   deployment to comply with PDPA.

3. **Stale deployment documentation**
   Several docs under `docs/` still describe the old Lightsail architecture,
   demo-route system, and stale credentials. A full reconciliation pass is
   needed before deployment.

---

## Working tree state

Clean. No uncommitted modifications. No untracked files (test-results/ now
in .gitignore).

---

## Completed work (this sprint)

| Work | Status | Evidence |
|---|---|---|
| PW-001 activity CRUD fix | DONE | commit 6f8a62c |
| PW-002 trainee CRUD fix | DONE | commit 6f8a62c |
| PW-003 asset route fix | DONE | commit 6f8a62c |
| Commit message SOP updated (AI footer) | DONE | commit 6f8a62c |
| Playwright full suite: 215/0 | VERIFIED | 2026-06-14 run |
| PHPUnit: 377/0 | VERIFIED | 2026-06-14 run |
| Fixers pushed to origin | DONE | d49e8bb |

---

## Do not repeat

- Do NOT deploy — hard rule is in effect until owner explicitly lifts it.
- Do NOT reference Lightsail or `creams.faisalhanafi.com` — that target is superseded.
- Do NOT run `migrate:fresh` on production.
- Do NOT use `GET /auth/logout` — use the Logout button (POST).
- Do NOT assume Breeze/Sanctum — auth is custom via `POST /auth/check`.
- Do NOT use `execSync` with multi-line PHP on Windows — use `spawnSync` with args array.
- Do NOT capitalise difficulty_level to 'Beginner' — server validates lowercase `in:beginner`.
- Do NOT inject `learning_outcomes` as a nested array — server expects `nullable|string`.
- Do NOT add Co-Authored-By AI trailers — use `[Assisted by AI, reviewed manually by Faisal]` footer instead.
