## Current recovery resumed — 4 November 2026

Owner explicitly resumed the existing-release workflow after context reconstruction
and required confirmation. CR01 baseline/release acceptance is recorded in the
[current release matrix](../MODULE_FUNCTIONALITY_INVENTORY.md#current-release-acceptance-matrix--4-november-2026)
and [recovery evidence](../audit/LOCAL_RECOVERY_2026-11-04.md). CR02/CR03 local
investigation proceeds incrementally with synthetic isolated resources. Fresh
baseline: 409 tests / 721 assertions, including caught errors outside accepted
paths. Local commits `aa42757` (centre recent records, 13 tests / 24 assertions)
and `0c540c7` (category browse, 12 tests / 30 assertions) are recorded. Attendance
dashboard policy tests pass 3 / 23 locally; that slice is still in progress.
Owner delegated reversible defaults to the central decision register; no more
routine approval pauses. Existing dirty work is preserved. Production,
ordinary database access, dependency installation, push/deployment and future
funding/allowance/RPI expansions remain unauthorized. Business-rule decisions stay
in the [current inconsistency register](08_DOC_ALIGNMENT/deviation_register.md).

# CREAMS — Current Status

> **Last updated**: 2026-08-23 (AUTH-LOGIN-001 standardized, PLAN.md synchronized with test phases)
> **Evidence basis**: git log, PHPUnit 392/0, secret scan hooks clean, CHANGELOG.md

---

## Current goal

Branch `Fixers` is the active development branch. AUTH-LOGIN-001 and AUTH-MESSAGE-001 resolved. Next backlog item: FLASH-GLOBAL-001 / DASH-SEARCH-001.

---

## Active branch state

| Field | Value |
|---|---|
| Branch | `Fixers` |
| HEAD | `3a5ceac` — Fix(Auth): Standardize seeded staff passwords to password constant |
| Working tree | Clean |
| Commits on Fixers | `9622ca0`, `3a5ceac` |

---

## Current test state

### PHPUnit (EXECUTED — 2026-06-24)

| Metric | Value |
|---|---|
| Passed | **392** |
| Failed | **0** |
| Assertions | 636 |

Command: `php -d memory_limit=512M vendor/bin/phpunit --no-coverage`

### Playwright (EXECUTED — 2026-06-24, re-verified post AssetMovement alignment)

| Metric | Value |
|---|---|
| Passed | **215** |
| Skipped | **3** (staff-crud tests 118, 119, 125 — pre-existing) |
| Failed | **0** |

Suite: `npx playwright test` from `tests/Browser/`

---

## Work completed on Fixers (2026-06-24 session)

| Commit | Description |
|---|---|
| `fa2746a` | PHANTOM-02: Removed 13 learning-outcomes routes + 2 controllers. Model shim retained (cascade risk). |
| `c1c339a` | B5-02/B5-03: Removed phantom eager-loads from IepController::show() and storeGoal() — `goals.learningOutcome` (non-Eloquent Error), `progressReports` (missing table → QueryException). |
| `8e1e2ff` | Fix(Tests): Playwright test 94 IEP locator — click-navigation race + CSS comma union selector bug. 214/1/3 → 215/0/3. |
| `9d23877` | PHANTOM-01: Removed classes feature scaffold — routes, controllers, model, relationships, scaffold test, dead view refs. PHPUnit 392/0. |
| `35a365a` | Fix(Git): Excluded AdminController from hardcoded-password pre-commit regex false positive. |
| `892f129` | Docs(Status): Session history + status docs updated for PHANTOM-01 removal. |
| `07c5320` | Fix(Asset): AssetMovement model + 3 write-path fixes — AssetController, AssetManagementService, AssetRepositoryService. All use real DB columns. |

All prior RC remediation (65 files, `ccea28e`) was also pushed this session.

---

## Open issues

| ID | Severity | Description | Status |
|---|---|---|---|
| B5-01 | Low | `GET /staff/updateuser/{id}` → missing `profile.blade.php` | Graceful catch in place, deferred |
| task_d9a381b5 | ~~Medium~~ | ~~4 deferred asset models + cascade cleanup~~ | **RESOLVED** — AssetMovement aligned; AssetLocation/AssetParent/AssetEnhanced deferred (read-only risk only) |
| CF-08 | Blocker | Production `LOG_LEVEL` on Hostinger SSH check | Owner-only — deploy hold in force |

---

## Deployment target (VERIFIED)

| Field | Value |
|---|---|
| Host | Hostinger shared hosting |
| Live URL | `https://pdk-creams.org` |
| Deploy method | Manual SSH `git pull` triggered by `.github/workflows/deploy.yml` |
| **Status** | **ON HOLD** — CF-08 must be resolved first; `main` merge requires explicit owner approval |

---

## Architecture notes (stable)

- Auth: custom session-based via `POST /auth/check` → `MainController@check`. Not Breeze/Sanctum.
- Login URL: `http://localhost:8000/auth/login`
- Roles: Admin, Supervisor, Teacher, AJK (Trainee, Parent planned)
- `LearningOutcome` model: NON-Eloquent shim (plain PHP class, not `extends Model`). Retained as dormant shim. Do NOT remove — cascades into `IepActivityGoal::learningOutcome()` and `ActivityWizardController::createLearningOutcomes()`.
- `progress_reports` table: does NOT exist. Do NOT eager-load `progressReports` relation.
- Global exception handler: catches `QueryException` only → graceful 302. Does NOT catch `Error` (e.g. calling Eloquent methods on non-Eloquent class) → hard HTTP 500.
- Resume tooling: `.opencode/skills/creams-resume/SKILL.md`, `.opencode/command/resume.md`, and `docs/audit/continuation-prompt.md` created for session migration. Requires opencode restart to load.

---

## Do not repeat

- Do NOT flag `@iium.edu.my` emails as real PII — synthetic seeder personas.
- Do NOT remove `LearningOutcome` model — cascades into live IEP/ActivityWizard features.
- Do NOT restore `progressReports` or `learningOutcome` eager-loads in IepController.
- Do NOT deploy — hard rule, CF-08 must be resolved first.
- Do NOT reference Lightsail or `creams.faisalhanafi.com` — superseded by Hostinger.
- Do NOT use `GET /auth/logout` — use POST via Logout button.
- Do NOT assume Breeze/Sanctum.
- Do NOT use `text=/goal/i, text=/objective/i` CSS comma union in Playwright — use `getAttribute('href')` + `page.goto()` + `waitForLoadState`.
- Do NOT add Co-Authored-By AI trailers — use `[Assisted by AI, reviewed manually by Faisal]` footer.
- Do NOT commit on `main` without explicit owner go-ahead + CF-08 resolution.
