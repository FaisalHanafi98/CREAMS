# CREAMS Dev Session — Security Close-Out (Session 1 of 3 to Production)

> **Date**: 2026-04-01
> **Project Path**: ./CREAMS
> **Session Type**: Security hardening — NOT feature work
> **Governance**: Root CLAUDE.md (SOP v2.2.0) → CREAMS/CLAUDE.md

---

## Context You Need

CREAMS is a **production** Community-based Rehabilitation Management System for Malaysian PPDK centres. It handles real trainee data (names, ICs, disability records). Gold Medal FYP, IIUM Computer Science.

A **delta re-evaluation** was completed on 2026-03-24 against SOP v2.2.0. All 5 waves were executed:
- Wave 0: Fixed 19 broken tests, blanked real DB password from .env.testing, added coverage tracking
- Wave 1: XSS fix (e() wrapping), PII leak fix, CentreScope GlobalScope on 3 core models, debug routes deleted, password min:12, hardcoded ICs removed
- Wave 2: Removed continue-on-error on lint, added composer audit, pre-commit secrets hook
- Wave 3: Updated SECURITY_AUDIT_REPORT.md, PRODUCTION_READINESS_ROADMAP.md, MODULE_FUNCTIONALITY_INVENTORY.md
- Wave 4: +23 new tests (CentreScopeTest, CentreIsolationTest, RoleAccessTest, SoftDeleteTest)

**Final state after re-eval**: 329 tests, 864 assertions, 0 failures.

---

## YOUR MISSION THIS SESSION

This is **Session 1 of 3** before production deployment. Focus: **Security close-out**.

### Task 1 — CentreScope Audit (HIGHEST PRIORITY)

**This is the single highest-risk item across all projects.**

CentreScope GlobalScope was added to only 3 models: `Trainee`, `Activity`, `Asset`.

You MUST audit ALL models with `centre_id` (directly or via trainee relationship). If any model with trainee-linked data isn't scoped, a teacher at Centre A sees Centre B's records = **PDPA violation**.

Steps:
1. `grep -r "centre_id" app/Models/` — find every model with centre_id
2. For each model found, check if CentreScope is applied (uses `HasCentreScope` trait or `CentreScope` GlobalScope)
3. For models that access trainee data indirectly (e.g., Attendance belongs to Trainee, Trainee has centre_id), verify the query path is scoped
4. Models to specifically check: **Staff, Attendance, Letters, IEP records** — these were flagged as potentially unscoped
5. Add CentreScope to every unscoped model that needs it
6. Write tests proving cross-centre isolation for each newly scoped model

### Task 2 — Verify Session Regeneration

Check `MainController::check()` (or wherever login happens). Verify `session()->regenerate()` is called on successful authentication. If not, add it. This prevents session fixation attacks.

### Task 3 — Verify APP_DEBUG

Confirm `.env.production` has `APP_DEBUG=false`. If `.env.production` doesn't exist, check `.env.example` and document what production env must contain.

### Task 4 — Verify Rate Limiting on Auth Route

Check that the actual login route (not just documentation) has rate limiting applied. Look for `throttle` middleware on the auth routes in `routes/web.php`.

### Task 5 — Verify AuthenticationTest

`tests/Feature/Auth/AuthenticationTest.php` was broken before the re-eval (wrong endpoint, wrong fields). It was fixed in Wave 0. Run it and confirm it tests the REAL auth flow — correct endpoint, correct field names, correct assertions.

### Task 6 — Full Test Suite

Run `php artisan test`. Expect 329+ tests, 0 failures. If anything fails, fix it. Do not move to Session 2 until the suite is green.

---

## Key Files

| File | Purpose |
|------|---------|
| `app/Models/Scopes/CentreScope.php` | The GlobalScope for multi-centre isolation |
| `app/Http/Controllers/MainController.php` | Login handler |
| `routes/web.php` | Route definitions (check throttle middleware) |
| `tests/Feature/Auth/AuthenticationTest.php` | Auth test (was broken, fixed in Wave 0) |
| `SECURITY_AUDIT_REPORT.md` | Updated security findings |
| `PRODUCTION_READINESS_ROADMAP.md` | Deployment roadmap |
| `PRODUCTION_DEPLOYMENT.md` | 40-point checklist |

---

## DO NOT

- Add features. This is security hardening only.
- Rewrite auth to Laravel Policies. Working middleware-based auth > theoretically better Policies with no tests.
- Increase test coverage % as a blocker. 329 tests on critical paths is sufficient.
- Build monitoring infrastructure. Tail logs is enough for a portfolio project.
- Touch anything outside security scope.

---

## EXIT CRITERIA

Before closing this session, you must have:
- [ ] CentreScope audit complete — every centre-linked model verified, newly scoped models tested
- [ ] Session regeneration on login verified (or added)
- [ ] APP_DEBUG=false confirmed for production
- [ ] Rate limiting on auth route verified
- [ ] AuthenticationTest confirmed working against real auth flow
- [ ] Full test suite: 329+ tests, 0 failures
- [ ] Commit all changes with descriptive message

After this session: Session 2 (Deployment Infrastructure) and Session 3 (Production Deploy) follow.
