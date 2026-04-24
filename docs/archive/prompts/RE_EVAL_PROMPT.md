# CREAMS — System Re-Evaluation Prompt

> Copy this entire prompt into a fresh Claude Code session opened at the CREAMS project root.

---

You are performing a DELTA SYSTEM RE-EVALUATION of CREAMS — a Community-based Rehabilitation Management System for Malaysian PPDK centres.

## Why "Delta" — Not Full

This system has ALREADY been extensively audited and documented. The following documents exist and are current:

- `CREAMS_CODEBASE_DOCUMENTATION.md` (65KB — full system overview)
- `DATABASE_SCHEMA_DOCUMENTATION.md` (71KB — complete 37-table schema)
- `SECURITY_AUDIT_REPORT.md` (30KB — OWASP assessment, 66% > 85%)
- `PRODUCTION_READINESS_ROADMAP.md` (77KB — 6-phase strategic plan)
- `PRODUCTION_DEPLOYMENT.md` (16KB — deployment guide + 40-point checklist)
- `CREAMS_Testing_Infrastructure_PRD.md` (45KB — test strategy, phase plan)
- `MODULE_FUNCTIONALITY_INVENTORY.md` (46KB — feature-by-feature checklist)
- `SCHEMA_AUDIT_REPORT.md` (29KB — table-by-table schema audit)
- `PERFORMANCE_BASELINE_METHODOLOGY.md` (28KB — profiling data)
- `API_ENDPOINT_SECURITY_INVENTORY.md` (43KB — 374 routes audited)
- `creams_detailed_uat.md` (66KB — 69 UAT test cases)
- `PLAN.md` (12KB — testing phases 1-6)

**Your job is NOT to reproduce this work.** Your job is to find what these documents MISSED, what has DRIFTED since they were written, and what the NEW SOP (v2.2.0) requires that wasn't enforced when these docs were created.

---

## Context

- **Status**: 85% feature-complete, production system (live demo at faisalhanafi.com/creams/demo/)
- **Recognition**: Gold Medal FYP — IIUM Computer Science
- **Tech Stack**: Laravel 10, PHP 8.1+, MySQL 8.0, Blade/Tailwind/Alpine.js, Vite
- **Scale**: 70+ controllers, 58 models, 374 named routes, 159+ Blade templates, 37 database tables
- **Testing**: 13% coverage (target 60%+), 78 test files exist but many broken
- **Security**: Phase 1 hardening complete (OWASP 68% > 85%), 7 security headers, rate limiting, CSRF verified
- **Deployment**: 3-tier (dev/staging/prod) via GitHub Actions, Docker (PHP-FPM + Nginx + MySQL)
- **SOP Version**: Root CLAUDE.md v2.2.0 is now active — this system was built BEFORE the SOP overhaul

---

## PHASE 1 — DOCUMENTATION DRIFT ANALYSIS

The existing documentation is comprehensive but may be stale. For each major document, check:

1. **Read the document** (at least the summary sections and key claims)
2. **Verify 3-5 specific claims against actual code** — don't trust the docs blindly
3. **Flag any drift**: claims that were true when written but are no longer accurate

Priority documents to audit for drift:
- `SECURITY_AUDIT_REPORT.md` — are the "fixed" vulnerabilities actually fixed in code?
- `PLAN.md` — does the testing phase plan reflect current test state?
- `MODULE_FUNCTIONALITY_INVENTORY.md` — do the "complete" features actually work?
- `PRODUCTION_READINESS_ROADMAP.md` — which phases are actually done vs claimed done?

You are looking for **false confidence** — documents that say the system is in better shape than it actually is.

---

## PHASE 2 — SOP v2.2.0 COMPLIANCE GAPS

The existing audits were done BEFORE SOP v2.2.0. Evaluate what the NEW SOP requires that the OLD system doesn't satisfy:

### 2.1 TDD Compliance (SOP Section 13.2 — test-driven-development skill)

The SOP mandates: "NO PRODUCTION CODE WITHOUT A FAILING TEST FIRST"

- **Reality**: 85% of the codebase was written without tests. This is a historical fact, not fixable retroactively.
- **Actual question**: For the REMAINING 15% of work and all future changes — is the test infrastructure ready for TDD?
- Check: Do factories work? (`UserFactory`, `TraineeFactory`, `ActivityFactory`, `CentreFactory`)
- Check: Is the test database (`cream_test`) correctly configured and isolated?
- Check: Can a developer write a test, run it, and get a meaningful result?
- Check: The existing `AuthenticationTest.php` is reportedly broken (wrong endpoint, wrong field). Verify this. If true, it means the test infrastructure itself is untrustworthy.

### 2.2 Verification Compliance (SOP Section 13.2 — verification-before-completion)

- Are there features marked "complete" in any document that have never been tested?
- Does the CI pipeline (`ci.yml`) actually catch failures, or does it pass with `continue-on-error`?
- Is `phpunit-ci.xml` (SQLite) divergent from `phpunit.xml` (MySQL)? Could tests pass in CI but fail locally?

### 2.3 Architecture Quality (SOP Section 2.2 — deep modules)

- 70+ controllers, 58 models — is this deep or shallow?
- Are controllers thin (delegating to services) or fat (business logic inline)?
- The 16+ services in `app/Services/` — do they encapsulate complexity, or are they just controller extraction?
- 21 middleware classes but NO Policies folder — is authorization done via middleware only? Is this sustainable at 374 routes?

### 2.4 Security Gaps Post-Hardening

The security audit claims 85% OWASP compliance after hardening. Verify:

- **XSS in `/letters-archive`** — was this actually fixed? Check the Blade template for `{!! !!}` vs `{{ }}`
- **Session fixation** — is `session()->regenerate()` called on login? Check `MainController::check()`
- **Debug routes** — are the 6 unprotected debug routes still accessible?
- **APP_DEBUG** — is it `false` in `.env.production`?
- **Password policy** — is 12+ char enforcement in the validation rules?
- **Rate limiting** — is it on the actual auth route, not just documented?

### 2.5 PDPA Compliance (SOP Section 8.2)

The SOP says: "NEVER expose real personal data from CREAMS"

- Are seeders using Faker consistently? Check ALL seeders, not just the main one
- Is there a data retention policy documented anywhere?
- Is there a right-to-be-forgotten mechanism? (Soft deletes exist, but can a centre admin actually purge a trainee's data?)
- Is personal data encrypted at rest, or only in transit?
- Are database backups encrypted?

### 2.6 CI/CD Pipeline (SOP Section 4.6 — quality gates)

- `ci.yml` uses `continue-on-error` for linting — does this mean lint failures don't block merges?
- Is there a coverage threshold enforced in CI?
- Does the deploy workflow (`deploy.yml`) require CI to pass first?
- Are database migrations tested before running `--force` in production?

### 2.7 Multi-Centre Data Isolation

This is CREAMS-specific and critical — a centre should NEVER see another centre's data.

- Is centre isolation enforced at the query level (global scope) or per-controller?
- Are there any routes that bypass centre filtering?
- Can a supervisor of Centre A access trainee data from Centre B?
- Check the 374-route security inventory — are there gaps in centre-scoped middleware?

---

## PHASE 3 — GAP CLASSIFICATION

Classify issues into:

| Severity | Definition for CREAMS (production system) |
|----------|------------------------------------------|
| **CRITICAL** | Data exposure risk, authentication bypass, centre isolation failure, or anything that could harm real trainees |
| **HIGH** | Production stability risk, silent data corruption, CI/CD pipeline that doesn't catch real failures |
| **MEDIUM** | Test coverage gaps, documentation drift, non-blocking compliance gaps |
| **LOW** | Code quality, architecture improvements, nice-to-have features |

Be STRICT but also REALISTIC. This is a Gold Medal FYP with a working production demo. The bar is "safe to operate," not "enterprise-grade."

---

## PHASE 4 — ROOT CAUSE ANALYSIS

For each CRITICAL and HIGH issue:

- **Why it exists** — was it a pre-SOP decision, an oversight, or a known tradeoff?
- **Which SOP section it violates** — cite the specific section number
- **Production impact** — what could actually go wrong with real PPDK centre data?

Focus on REAL risk, not theoretical. "Pickle deserialization attack" matters for a public API; it matters less for an internal admin system behind authentication.

---

## PHASE 5 — RETROFIT PLAN (NO CODING)

For each issue:

1. **What needs to change** — specific file paths, specific code patterns
2. **Effort estimate** — is this a 30-minute fix or a 2-day refactor?
3. **Risk of the fix itself** — could fixing this break working features?
4. **How to validate** — real test or verification command
5. **Does existing documentation need updating?** — if a doc claims something is fixed but it isn't, the doc needs correction too

**Constraint**: Do NOT propose a full rewrite of authentication, a migration to Laravel Policies, or any other large-scale architectural change unless the CRITICAL issues demand it. Prefer targeted, surgical fixes.

---

## PHASE 6 — EXECUTION STRATEGY

Structure the roadmap around CREAMS's reality:

```
WAVE 0: Trust Restoration (fix broken test infrastructure)
  - Make the test suite trustworthy before adding to it
  - Fix broken tests, verify CI actually fails on real failures

WAVE 1: Safety Critical (PDPA, auth, centre isolation)
  - Any issue where real trainee data could be exposed
  - Must be fixed before any new production deployment

WAVE 2: CI/CD Hardening
  - Make the pipeline catch what it should catch
  - Coverage thresholds, lint enforcement, migration testing

WAVE 3: Documentation Correction
  - Update any docs that claim things the code doesn't deliver
  - Remove false confidence

WAVE 4: Test Coverage Expansion
  - Targeted tests for the highest-risk modules
  - RBAC, attendance, competency assessment, reports
```

Each fix must be:
- Independently deployable (CREAMS is a live system — no "big bang" fixes)
- Backward compatible with existing data
- Verifiable without manual testing

---

## OUTPUT FORMAT

```
1. DOCUMENTATION DRIFT FINDINGS
   - Per-document: claims verified, claims falsified, claims unverifiable

2. SOP v2.2.0 COMPLIANCE GAPS
   - Per-section findings (2.1 through 2.7)

3. GAP CLASSIFICATION
   - CRITICAL / HIGH / MEDIUM / LOW tables

4. ROOT CAUSE ANALYSIS
   - Per CRITICAL and HIGH: why, SOP violation, production impact

5. RETROFIT PLAN
   - Per issue: what, effort, risk, validation, doc updates

6. EXECUTION ROADMAP
   - Wave 0 / 1 / 2 / 3 / 4 with dependencies and estimated effort

7. RISKS AFTER FIXES
   - What remains risky even after all fixes

8. OPEN QUESTIONS
   - Decisions needed before execution
```

---

## CRITICAL RULES

- DO NOT write code
- DO NOT re-audit what's already been audited — VERIFY the existing audits
- DO NOT treat this like a greenfield project — CREAMS has a live demo, real users are coming
- DO NOT propose architectural rewrites unless safety demands it
- THINK like a CTO deciding whether this system is safe to hand over to a real PPDK centre

Begin now.
