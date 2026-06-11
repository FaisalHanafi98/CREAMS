# CREAMS — Current Session

> **Document type**: Active session prompt (convention pointer)
> **Last containment pass**: 2026-04-24
> **Status**: Re-baselining phase — no deployment permitted

---

## Read before touching anything

1. Open [`SOURCE_OF_TRUTH.md`](SOURCE_OF_TRUTH.md) first. It governs this file.
2. Do not trust test counts, coverage percentages, CentreScope coverage claims, or "production-ready" language you see anywhere in `docs/` without verifying against code and a fresh `php artisan test` run.
3. The most recent dated session prompt (`CREAMS_SESSION_2026-04-16.md`) is **historical context**, not current marching orders. The project has entered a containment phase since then.

---

## Current priority

**Re-baselining CREAMS.** The documentation set has drifted (see `SOURCE_OF_TRUTH.md` → "Known drift watchlist"). Before any further feature work, security work, or deploy prep, the project needs a verified snapshot of reality.

## Next required session

A **system/code reality audit** must run before anything else:

- Inventory actual routes (`routes/web.php`) vs. claimed routes in `MODULE_FUNCTIONALITY_INVENTORY.md`.
- Run the full test suite (`php artisan test`) and update `TEST_BASELINE.md` with the real numbers.
- Verify `MULTI_CENTRE_ISOLATION.md`'s claim of 26-of-28 scoped models against `app/Models/` by grepping for `CentreScope` usage.
- Run the app in a browser on each of the four role accounts (Admin, Supervisor, Teacher, AJK) and sanity-check that the described dashboard/flow actually works.
- Check the working tree: there are **un-committed modifications to controllers, models, tests, and `routes/web.php`** as of this containment pass. Understand what changed and whether it reflects intentional work before rebasing any new work on top.

Only after that audit should any feature or security task resume.

---

## Rules for any session following this one

### DO NOT
- Deploy to Lightsail or anywhere else. Deployment is gated on the code-reality audit + Portfolio co-tenancy coordination.
- Follow any archived prompt under `Archive/Historical_AI_Artifacts/prompts/`. They describe completed or superseded work.
- Re-run the delta re-evaluation. Its output is `DELTA_REEVAL_REPORT_2026-03-22.md`; the work is done.
- Re-apply CentreScope to the 26 already-scoped models. See `MULTI_CENTRE_ISOLATION.md`.
- Re-rewrite `AuthenticationTest.php`. It was fixed in Wave 0 (Feb 2026).
- Use "329 tests", "306 tests", "13% coverage", or any similar metric from an older doc as an assumption. Measure first.
- Assume roles are Admin/Manager/Staff/Caretaker/Trainee/Parent. The real roles are **Admin, Supervisor, Teacher, AJK** (plus Trainee and Parent as planned/future). See ADR-002.
- Assume the auth stack is Breeze + Sanctum. The real stack is **custom session auth via `POST /auth/check`**.

### DO
- Read the code before changing the code.
- Treat `docs/SOURCE_OF_TRUTH.md` as the starting index.
- When an archived document and current code disagree, the code is correct and the document is archived for a reason.
- Commit documentation changes separately from code changes.
- Follow root SOP v2.2.0 (`../CLAUDE.md`) for tiering, validation, and commit rules.

---

## Deployment status

**ON HOLD.** Reasons:

1. Working tree has un-committed app/test modifications (see `git status`).
2. Test baseline not yet verified after containment pass.
3. Portfolio co-tenancy (shared $5 Lightsail) traffic/cost plan not yet closed.
4. Documentation containment pass (this one) has not yet been followed by the reality audit.

No production push from this session chain. Push to `origin/Fixers` after documentation commit is fine; production push is not.

---

## Lineage

| Date | File | Status |
|------|------|--------|
| 2026-04-24 | `CREAMS_SESSION_CURRENT.md` (this) | Active — containment + re-baseline |
| 2026-04-16 | `CREAMS_SESSION_2026-04-16.md` | Historical reference (security residuals + baseline prep) |
| 2026-04-01 | `CREAMS_SESSION_2026-04-01.md` | Archived — `Archive/Historical_AI_Artifacts/prompts/` |
| 2026-04-01 | `CREAMS_SESSION_1_SECURITY_CLOSEOUT.md` | Archived — `Archive/Historical_AI_Artifacts/prompts/` |
| 2026-03-22 | `DELTA_REEVAL_REPORT_2026-03-22.md` | Historical — output preserved |
| Pre-SOP | various loose prompts | Archived — `Archive/Historical_AI_Artifacts/prompts/` |

When a new session replaces this one, the new prompt takes the name `CREAMS_SESSION_CURRENT.md` and this file moves to `Archive/Historical_AI_Artifacts/prompts/CREAMS_SESSION_2026-04-24.md`.
