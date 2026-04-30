# CREAMS Delta System Re-Evaluation Report

**Date:** 2026-03-22
**Evaluator:** Claude Opus 4.6 (automated delta audit)
**SOP Version:** Root CLAUDE.md v2.2.0
**Scope:** Documentation drift, SOP compliance, security, PDPA, architecture, CI/CD

---

## Executive Summary

4 research agents audited the CREAMS codebase against existing documentation and SOP v2.2.0 requirements. The system has strong foundations (session fixation fixed, rate limiting active, security headers deployed, test infrastructure ready) but carries false confidence in its documentation — several "fixed" vulnerabilities remain unfixed in code.

**Findings:** 4 CRITICAL, 8 HIGH, 6 MEDIUM, 5 LOW issues.

**Most dangerous gaps:**
1. XSS in /letters-archive — audit says fixed, code says otherwise
2. Full session data dumped to logs on every authenticated request
3. SoftDeletes migration exists but models lack the trait — "deleted" records still returned
4. No centre isolation GlobalScope — relies on developers remembering `where('centre_id')` in every query

---

## 1. Documentation Drift Findings

### SECURITY_AUDIT_REPORT.md

| Claim | Verdict | Evidence |
|-------|---------|----------|
| XSS in /letters-archive fixed with `e()` | **FALSIFIED** | `routes/web.php` lines 685-708 still concatenate unescaped user data into HTML |
| Session fixation fixed | **VERIFIED** | `SessionManager.php` calls `Session::flush()` + `Session::regenerate()` on login |
| Debug routes removed | **FALSIFIED** | `/test` and `/test-sessions` still exist in `routes/web.php` lines 220-225 |
| Rate limiting on auth | **VERIFIED** | `throttle:login` middleware on `/auth/check`, 5 req/min in RouteServiceProvider |
| Security headers active | **VERIFIED** | `SecurityHeadersMiddleware.php` registered in `Kernel.php` line 42 |
| Password min:12 enforced | **FALSIFIED** | `MainController.php` line 127 uses `min:8`, not `min:12` |
| APP_DEBUG=false | **PARTIAL** | `.env` has `true` (dev), `.env.production` has `false` |

### PLAN.md

| Claim | Verdict | Evidence |
|-------|---------|----------|
| "9 PHPUnit test classes" | **DRIFT** | 27 test files now exist (6 Unit + 21 Feature) |
| "AuthenticationTest is WRONG" | **FALSIFIED** | Rewritten — now correctly uses `/auth/check` with `identifier` field |
| Phase 2-4 status | **DRIFT** | Phases 2-4 are complete but PLAN.md doesn't reflect this |
| Phase 5 (Docker) | **NOT FOUND** | No Dockerfile or docker-compose.yml in repo |
| Phase 6 (ADRs) | **NOT FOUND** | No `docs/adr/` directory |
| Coverage trajectory | **UNVERIFIABLE** | No coverage metrics tracked in CI |

### PRODUCTION_READINESS_ROADMAP.md

| Claim | Verdict | Evidence |
|-------|---------|----------|
| "81% test pass rate" | **MISLEADING** | Only 13.4% of 163 features have any test coverage |
| "66% OWASP compliance -> 85%" | **FALSIFIED** | XSS still unfixed, debug routes still accessible, password policy weaker than claimed |
| Phase 1 security complete | **PARTIALLY FALSE** | 3 of 7 verified fixes are real; 3 claimed fixes are not in the code |

### MODULE_FUNCTIONALITY_INVENTORY.md

| Claim | Verdict | Evidence |
|-------|---------|----------|
| Feature completeness claims | **UNVERIFIABLE** | 163 features listed; only 17 have any automated test |

---

## 2. SOP v2.2.0 Compliance Gaps

### 2.1 TDD compliance

| Check | Status | Detail |
|-------|--------|--------|
| Factories exist and work | **READY** | 10 factories, all well-formed with Faker |
| Test database isolated | **READY** | `cream_test` MySQL (local), SQLite `:memory:` (CI) |
| Developer can run tests | **READY** | `php artisan test` works with `phpunit.xml` |
| AuthenticationTest broken? | **NO — FIXED** | 12 test methods, correct endpoint/fields |
| `.env.testing` security | **FAIL** | Contains real password in version control |

### 2.2 Verification compliance

| Check | Status | Detail |
|-------|--------|--------|
| Features marked "complete" without tests | **YES** | 146 of 163 features have zero automated tests |
| CI catches real failures | **YES** | PHPUnit step has no `continue-on-error` |
| CI catches lint failures | **NO** | `continue-on-error: true` on Pint step |
| phpunit-ci.xml divergence | **YES but acceptable** | SQLite (CI) vs MySQL (local) — standard Laravel practice |

### 2.3 Architecture quality

| Aspect | Finding |
|--------|---------|
| Controller depth | **SHALLOW/FAT** — 400-900 lines, inline business logic |
| Services | **EXIST but UNDERUTILIZED** — controllers duplicate service work |
| Policies | **MISSING** — no `app/Policies/` directory |
| Authorization | **MIXED** — middleware (good) + 49 inline `if (session('role'))` checks (fragile) |

### 2.4 Security gaps post-hardening

| Issue | Status | SOP Violation |
|-------|--------|--------------|
| XSS in /letters-archive | **UNFIXED** | Section 8.3 |
| Debug routes still accessible | **UNFIXED** | Section 8.3 |
| Password min:8 not min:12 | **WEAKER THAN DOCUMENTED** | Section 4.4 |
| Session fixation | Fixed | — |
| Rate limiting | Fixed | — |
| Security headers | Fixed | — |

### 2.5 PDPA compliance

| Issue | Status | SOP Violation |
|-------|--------|--------------|
| Hardcoded IC numbers in `TestingGuideDataSeeder` | **VIOLATION** | Section 8.2 |
| `GombakDataExtractor.php` backs up real data to JSON | **VIOLATION** | Section 8.2 |
| Entire session logged in `Authenticate.php` | **VIOLATION** | Section 8.2 |
| API error sanitizer misses IC/phone/email | **VIOLATION** | Section 8.2 |
| No field-level encryption on PII | **GAP** | Section 8.2 |
| Soft deletes migration exists but models lack `SoftDeletes` trait | **BROKEN** | Section 8.2 |
| No data purge mechanism | **MISSING** | Section 8.2 |

### 2.6 CI/CD pipeline

| Check | Status | Detail |
|-------|--------|--------|
| `continue-on-error` on lint | Yes — lint failures don't block | Low severity |
| Coverage threshold | **NONE** | Tests pass at 0% coverage |
| Deploy requires CI pass | **YES** | All deploy jobs have `needs: test` |
| Migration tested before `--force` | **NO** | `--force` runs directly in production |
| Security scanning | **NONE** | No SAST, no dependency audit |

### 2.7 Multi-centre data isolation

| Check | Status | Detail |
|-------|--------|--------|
| Global scope on models | **NOT IMPLEMENTED** | No `addGlobalScope` for centre_id |
| Middleware enforcement | **PARTIAL** | `CentreAccessControl` exists but not on all routes |
| Controller-level filtering | **INCONSISTENT** | Inline `where('centre_id')` — depends on developer memory |
| Admin bypass | **INTENTIONAL** | Admins see all centres — documented and correct |

---

## 3. Gap Classification

### CRITICAL

| # | Issue | Risk |
|---|-------|------|
| C1 | XSS in /letters-archive — unescaped user data in HTML | Admin session hijacking |
| C2 | Entire session logged via `Authenticate.php` (`session()->all()`) | PII in log files |
| C3 | `SoftDeletes` trait missing from Trainee, Staff, ActivitySession models | "Deleted" records still returned — PDPA violation |
| C4 | No centre isolation GlobalScope — relies on manual `where()` in every query | Cross-centre data leak |

### HIGH

| # | Issue | Risk |
|---|-------|------|
| H1 | Debug routes `/test`, `/test-sessions` still in `routes/web.php` | Session data exposure |
| H2 | Password policy enforces min:8, not min:12 | Weaker passwords than documented |
| H3 | `GombakDataExtractor.php` creates `real_data_backup.json` | Real PII in codebase |
| H4 | API error sanitizer doesn't redact IC/phone/email | PII in error logs |
| H5 | Hardcoded IC numbers in `TestingGuideDataSeeder.php` | Test data resembles real data |
| H6 | `.env.testing` contains real database password | Credential exposure |
| H7 | No CI coverage threshold | False confidence in test safety |
| H8 | Production migrations `--force` without backup | Data loss if migration fails |

### MEDIUM

| # | Issue | Risk |
|---|-------|------|
| M1 | PLAN.md stale — claims AuthTest broken, says 9 tests | False picture of project state |
| M2 | PRODUCTION_READINESS_ROADMAP claims 85% OWASP | False confidence |
| M3 | No field-level encryption on IC numbers, phone, address | PII at rest unprotected |
| M4 | `continue-on-error` on lint in CI | Code style violations don't block deploys |
| M5 | Phases 5-6 (Docker, ADRs) claimed in plan but not implemented | Incomplete infrastructure |
| M6 | 146 of 163 features have zero automated tests | Low coverage |

### LOW

| # | Issue | Risk |
|---|-------|------|
| L1 | Fat controllers (400-900 lines) | Maintenance burden |
| L2 | Services exist but underutilized by controllers | Duplicate logic |
| L3 | 49 inline `if (session('role'))` checks across 12 controllers | Authorization fragmentation |
| L4 | No Policy classes for resource-level authorization | Middleware + inline only |
| L5 | Redundant role middleware (3 files) | Confusion about which is active |

---

## 4. Root Cause Analysis

### C1: XSS in /letters-archive
- **Why:** Pre-SOP code. Route built as inline closure in `routes/web.php` rather than controller+Blade. Security audit documented the fix but code was never updated.
- **SOP violation:** Section 8.3 (OWASP Top 10)
- **Production impact:** Attacker injects `<script>` in letter subject or recipient name, steals admin session cookies.

### C2: Session data in logs
- **Why:** Debug-oriented logging added during development, never removed.
- **SOP violation:** Section 8.2 (PDPA)
- **Production impact:** Log files contain full user session data. Any log aggregator compromise exposes all active user PII.

### C3: SoftDeletes trait missing
- **Why:** Migration written to add `deleted_at` columns but model changes were not made in the same commit.
- **SOP violation:** Section 8.2 (PDPA)
- **Production impact:** `$trainee->delete()` sets `deleted_at` but `Trainee::all()` still returns the record.

### C4: No centre GlobalScope
- **Why:** Pre-SOP decision. Centre filtering was added ad-hoc per controller.
- **SOP violation:** Section 8.2 (PDPA)
- **Production impact:** Any new controller that forgets `where('centre_id')` silently returns all centres' data.

### H1-H8: Debug routes, password policy, PII exposure, CI gaps
- **Why:** All pre-SOP decisions. Debug routes useful during dev. Password min:8 was original requirement. Sanitizer built for auth tokens not PDPA fields.
- **SOP violations:** Sections 8.2, 8.3, 4.6

---

## 5. Retrofit Plan

| # | What | Effort | Fix Risk | Validation | Doc Update |
|---|------|--------|----------|------------|------------|
| C1 | Wrap `/letters-archive` output in `e()` or move to Blade | 30 min | Low | Load page, verify no raw HTML | SECURITY_AUDIT_REPORT.md |
| C2 | Remove `session()->all()` from Authenticate.php log | 15 min | None | Grep logs for session dumps | None |
| C3 | Add `use SoftDeletes` to Trainee, Staff, ActivitySession | 30 min + 2h test | **MODERATE** | Compare counts with/without `withTrashed()` | None |
| C4 | Create CentreScope GlobalScope on Trainee, Activity, Asset | 3 hours | **HIGH** | Full Playwright suite | None |
| H1 | Delete `/test` and `/test-sessions` routes | 5 min | None | `route:list \| grep test` | None |
| H2 | Change `min:8` to `min:12` in MainController | 15 min | Low | Test registration | SECURITY_AUDIT_REPORT.md |
| H3 | Delete GombakDataExtractor.php + real_data_backup.json | 10 min | None | Verify file removed | None |
| H4 | Add PDPA fields to API error sanitizer | 15 min | None | Trigger error, check logs | None |
| H5 | Replace hardcoded ICs with Faker in TestingGuideDataSeeder | 30 min | None | Run seeder | None |
| H6 | Replace password in `.env.testing` with placeholder | 5 min | None | CI still passes | None |
| H7 | Add coverage tracking + threshold to CI | 1 hour | Low | Push failing coverage commit | None |
| H8 | Add `migrate --pretend` before `--force` in deploy | 30 min | None | Deploy to dev | None |

---

## 6. Execution Roadmap

### Wave 0: Trust Restoration (1-2 days)

| Task | ID | Effort |
|------|----|--------|
| Remove real password from `.env.testing` | H6 | 5 min |
| Add coverage tracking to CI | H7 | 1 hour |
| Add `migrate --pretend` before `--force` in deploy | H8 | 30 min |
| Update PLAN.md to reflect actual state | M1 | 30 min |
| Verify all 27 PHPUnit tests pass locally | — | 30 min |

### Wave 1: Safety Critical (3-5 days)

| Task | ID | Effort | Depends On |
|------|----|--------|-----------|
| Fix XSS in /letters-archive | C1 | 30 min | Nothing |
| Remove session dump from logs | C2 | 15 min | Nothing |
| Add SoftDeletes trait to models | C3 | 30 min + 2h test | Wave 0 |
| Delete debug routes | H1 | 5 min | Nothing |
| Enforce min:12 password | H2 | 15 min | Nothing |
| Delete GombakDataExtractor.php | H3 | 10 min | Nothing |
| Add PDPA fields to error sanitizer | H4 | 15 min | Nothing |
| Replace hardcoded ICs in seeder | H5 | 30 min | Nothing |
| Implement CentreScope GlobalScope | C4 | 3 hours + test | Wave 0, C3 |

### Wave 2: CI/CD Hardening (1-2 days)

| Task | ID | Effort | Depends On |
|------|----|--------|-----------|
| Enforce lint in CI | M4 | 15 min | Fix lint violations first |
| Add `composer audit` to CI | — | 30 min | Nothing |
| Add pre-commit hook for secrets | — | 1 hour | Nothing |
| Document production rollback | — | 1 hour | Nothing |

### Wave 3: Documentation Correction (1 day)

| Task | ID | Effort | Depends On |
|------|----|--------|-----------|
| Update SECURITY_AUDIT_REPORT.md | — | 30 min | Wave 1 |
| Update PRODUCTION_READINESS_ROADMAP.md | M2 | 30 min | Wave 1 |
| Correct MODULE_FUNCTIONALITY_INVENTORY | — | 1 hour | Wave 0 |

### Wave 4: Test Coverage Expansion (2-4 weeks, ongoing)

| Task | Effort | Depends On |
|------|--------|-----------|
| RBAC tests for all 6 roles | 3 days | Wave 0-1 |
| Centre isolation tests | 1 day | C4 done |
| Attendance module tests | 2 days | Wave 0 |
| Competency assessment tests | 2 days | Wave 0 |
| Report generation tests | 1 day | Wave 0 |

---

## 7. Risks After Fixes

1. **No Policy classes** — Authorization stays middleware + inline. Works at current scale but fragile at growth.
2. **Fat controllers** — Maintenance burden, not a safety risk.
3. **No field-level encryption** — IC numbers in plaintext. Mitigated by DB access controls.
4. **SQLite vs MySQL CI divergence** — Edge cases in JSON/fulltext could slip through.
5. **No SAST in pipeline** — Security depends on manual review.
6. **85% of codebase written without tests** — Permanent risk. TDD forward prevents new risk only.

## 8. Open Questions

1. **CentreScope rollout**: Model-by-model or all at once? (Rec: model-by-model, Trainee first)
2. **SoftDeletes impact**: Any "deleted" records being queried intentionally? Check before applying.
3. **Field-level encryption**: Worth the complexity? MySQL TDE might suffice.
4. **Password migration**: Force existing users with 8-11 char passwords to change?
5. **GombakDataExtractor**: Was `real_data_backup.json` ever committed? Check git history.
6. **Coverage target**: Realistic intermediate target? (Rec: 25% overall, 60%+ auth/RBAC)
