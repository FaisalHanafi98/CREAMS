# CREAMS Dev Session — Security Residuals & Pre-Deploy Test Baseline

> **Date**: 2026-04-16
> **Project Path**: ./CREAMS
> **Session Type**: Security residuals + test baseline (NOT deployment)
> **Governance**: Root CLAUDE.md (SOP v2.2.0) → CREAMS/CLAUDE.md
> **Supersedes**: CREAMS_SESSION_2026-04-01.md (most of its tasks are now complete)

---

## Context You Need

CREAMS is a **production-grade** Community-based Rehabilitation Management System for Malaysian PPDK centres. It handles real trainee data (names, ICs, disability records). Gold Medal FYP, IIUM Computer Science.

**Deployment status**: **ON HOLD**. Portfolio cybersec close-out must finish before any Lightsail push touches the shared $5 instance. Do NOT deploy from this session.

### What Was Completed in the 2026-04-01 Session

The prior session executed most of the "Security Close-Out" plan. An April 16 audit of the codebase confirmed:

| Item | Status | Evidence |
|------|--------|----------|
| CentreScope on Trainee, Activity, Asset | Done | Original 3 scoped models |
| CentreScope expanded to additional centre_id models | **Done — 26 of 28 models scoped** | Models directory audit |
| `throttle:login` on login route | Done | `routes/web.php:105` |
| `throttle:registration` on registration | Done | `routes/web.php:118` |
| `throttle:password-reset` on reset routes | Done | `routes/web.php:125,129` |
| RateLimiter definitions | Done | `RouteServiceProvider.php:35-107` |
| Session regeneration on login | Done | `MainController.php:420` `$request->session()->regenerate()` |
| `.env.production` with `APP_DEBUG=false` | Done | File exists, flag set |
| Auth test suite fixed | Done | `tests/Feature/Auth/AuthenticationTest.php` |
| Security test files added | Done | CentreScopeTest, CentreIsolationTest, RoleAccessTest, SoftDeleteTest |
| Test suite size | 85 test files present | `tests/` directory |

**So the 2026-04-01 prompt's Tasks 2-6 are all done.** What remains is Task 1 (CentreScope audit — partially complete) plus a few new items discovered in the audit.

---

## YOUR MISSION THIS SESSION

Short, focused cleanup. Target: one commit, one push, all tests green.

### Task 1 — Finish the CentreScope Audit (2 models left)

The April 1 session scoped 26 of 28 centre_id-bearing models. Two were NOT scoped:

**1. `app/Models/Message.php`** — confirmed unscoped (no `CentreScope` or `HasCentreScope`).
  - If `Message` is centre-scoped (internal messaging between staff of the same centre), ADD the scope.
  - If it's globally-scoped by design (e.g., system-wide announcements), document WHY in a comment and in `docs/MULTI_CENTRE_ISOLATION.md`.

**2. `app/Models/Centre.php`** — is the tenant itself, cannot self-scope.
  - Document this explicitly in `docs/MULTI_CENTRE_ISOLATION.md` so future auditors don't flag it again.
  - Ensure access to the `Centre` model is controlled at the controller/policy layer instead.

Write tests:
- `tests/Feature/Security/MessageCentreIsolationTest.php` — user at Centre A cannot read messages from Centre B.

### Task 2 — Lock Down the Final Security Checklist

Produce `docs/PRE_DEPLOY_SECURITY_CHECKLIST.md` (or update an existing equivalent) with a line-by-line verification of:

- [ ] All centre_id models scoped OR documented exception
- [ ] All auth routes throttled with correct per-route limits
- [ ] Session regeneration wired on successful login + logout
- [ ] `APP_DEBUG=false` in `.env.production`
- [ ] No `dd()`, `dump()`, `var_dump()`, or `Log::debug()` leaks in controllers
- [ ] CSRF on all state-changing routes (Laravel default, but verify)
- [ ] No hardcoded ICs, emails, or passwords in tests/seeders/factories
- [ ] `APP_KEY` rotated and stored outside git (verify `.env.production` is gitignored)
- [ ] Rate limit responses return JSON (not HTML) for API routes

### Task 3 — Baseline the Test Suite

1. Run `php artisan test` — capture the exact count (tests, assertions, failures, skipped).
2. Update `README.md` and `CLAUDE.md` if they cite stale numbers (the April 1 file claimed 329 tests, the count may now be higher after the CentreScope expansion added tests).
3. Record the baseline in `docs/TEST_BASELINE.md` — future sessions should never let this number drop.

### Task 4 — Prep For Lightsail Co-Tenancy Review

CREAMS shares the $5 Lightsail instance with Portfolio. The Portfolio session will audit cost/traffic. To prepare:

1. Document CREAMS current memory + disk footprint in `docs/LIGHTSAIL_FOOTPRINT.md`:
   - PHP-FPM memory target
   - MySQL data size
   - Session storage size
   - Daily log rotation setup (or missing)
2. No config changes. Just record current state so the Portfolio session can plan the co-tenancy traffic strategy.

---

## Key Files

| File | Purpose |
|------|---------|
| `app/Models/Message.php` | Needs CentreScope (Task 1) |
| `app/Models/Scopes/CentreScope.php` | GlobalScope implementation |
| `app/Http/Controllers/MainController.php` | Login handler (session regen at line 420) |
| `app/Providers/RouteServiceProvider.php` | RateLimiter definitions (lines 35-107) |
| `routes/web.php` | Route definitions with throttle middleware |
| `docs/MULTI_CENTRE_ISOLATION.md` | Update with Centre/Message exception rationale |
| `docs/PRE_DEPLOY_SECURITY_CHECKLIST.md` | Create or update |
| `docs/TEST_BASELINE.md` | Create — record current test count |
| `docs/LIGHTSAIL_FOOTPRINT.md` | Create — memory/disk/log profile |

---

## DO NOT

- **Do NOT re-scope Trainee, Activity, Asset, or the other 23 already-scoped models.** They are done. Wasting time re-validating them would be churn.
- **Do NOT re-wire throttle middleware on login / registration / password-reset.** Done. See `routes/web.php:105,118,125,129`.
- **Do NOT re-add `session()->regenerate()` in MainController.** Done. See line 420.
- **Do NOT rewrite `AuthenticationTest.php`.** Fixed in Wave 0 (Feb 2026).
- **Do NOT deploy to Lightsail.** Deployment is gated on Portfolio cybersec. Push to GitHub is fine; production push is not.
- **Do NOT add new features.** Stabilize, audit, document, commit.
- **Do NOT refactor auth to Laravel Policies.** Middleware-based auth works and is fully tested.
- **Do NOT change test framework or coverage tool.** Baseline first, refactor never.

---

## EXIT CRITERIA

- [ ] `Message` model: CentreScope added OR documented exception in `docs/MULTI_CENTRE_ISOLATION.md`
- [ ] `Centre` model exception documented
- [ ] `MessageCentreIsolationTest` written and passing
- [ ] `docs/PRE_DEPLOY_SECURITY_CHECKLIST.md` exists and all items verified green
- [ ] `php artisan test` recorded baseline in `docs/TEST_BASELINE.md`
- [ ] `docs/LIGHTSAIL_FOOTPRINT.md` documents current resource usage
- [ ] All changes committed (conventional commit style). NOT pushed to production.

**After this session**: Portfolio cybersec session → Lightsail cost/traffic audit → coordinated deployment push (CREAMS + Portfolio together).
