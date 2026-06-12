# CREAMS — Smoke Test Inventory

> **Generated**: 2026-05-31
> **Category**: Smoke Testing
> **Purpose**: Inventory of smoke tests, browser tests, regression tests, E2E tests, and sanity test evidence.

---

## Test Baselines

| Source File | Evidence Type | Reason Included | Confidence | Importance |
|---|---|---|---|---|
| `docs/audit/test_baseline_2026-04-30.log` | Baseline | Authoritative: 359 tests, 0 failures, 520 assertions in ~124s. Canonical baseline. | High | Critical |
| `docs/Validate/TEST_BASELINE.md` | Quality Gate | Floor: 359 tests, 520 assertions, 0 failures. No session may drop below. History tracked from Feb to Apr 2026. Coverage ~15-20%. | High | Critical |
| `docs/ai-context/06_TESTING_EVIDENCE/test_commands.md` | Results | php artisan test history: 359/0 baseline, 354/5 current (May 8). All 5 failures = single demo_demo_route() typo — not real regressions. | High | High |
| `docs/archive/audits/test-run-20260424-1642.log` | Historical | Pre-sprint test run: 359 passed, 0 failed, 520 assertions. Matches current baseline. | Medium | Medium |

## Playwright / Browser E2E

| Source File | Evidence Type | Reason Included | Confidence | Importance |
|---|---|---|---|---|
| `docs/audit/PLAYWRIGHT_FINDINGS_2026-05-04.md` | E2E Report | 8/8 demo spec passed in headless Chromium. Full suite: 181/210 passing (86.2%). 12 screenshots. CSP blocks external CDNs. | High | High |
| `docs/audit/PLAYWRIGHT_MCP_DRYRUN_2026-05-04.md` | E2E Report | Interactive MCP walkthrough: 6/6 beats passed. 0 P0/P1. 5 P2 (CSP). 2 cosmetic findings. | High | High |
| `docs/ai-context/06_TESTING_EVIDENCE/playwright_results.md` | E2E Status | Demo-flow spec: 8/8 passed. MCP dry-run: 6/6 verified. 12 screenshots. Known: CSP blocks external CDNs (console errors, not failures). | High | High |
| `docs/ai-context/06_TESTING_EVIDENCE/browser_verification.md` | Manual E2E | All 4 role flows verified in Chrome. CentreScope isolation confirmed: user at Centre A cannot access Centre B data. Known browser issues listed. | High | High |
| `.playwright-mcp/console-*.log` (~80+ files) | Console Logs | Browser console output from Playwright MCP sessions. May contain production URLs visible in logs. | Medium | Medium |

## Demo Dry-Run / Smoke

| Source File | Evidence Type | Reason Included | Confidence | Importance |
|---|---|---|---|---|
| `docs/audit/DEMO_DRYRUN_REPORT_2026-05-03.md` | Dry-Run | HTTP-level demo walkthrough: all demo pages return 200. RBAC enforced. Centre isolation verified. | High | High |
| `docs/audit/DEMO_DRYRUN_LOG_2026-05-03.txt` | Log | Raw curl-based dry-run: all 4 role logins succeed. Every demo page returns 200 with populated HTML. No 500s. | High | Medium |

## Live Production Smoke (May 2026)

| Source File | Evidence Type | Reason Included | Confidence | Importance |
|---|---|---|---|---|
| `docs/audit/live_mutation_smoke_2026-05-15.md` | Production | Production mutation test on pdk-creams.org: synthetic records created, all 4 roles authenticated, CRUD verified. | High | Critical |
| `docs/audit/live_smoke_claim_verification_2026-05-16.md` | Production | Claim verification: asset 500 fixed (confirmed), RBAC confirmed, template links gone. But logout + trainee-create + weather still fail = FAIL. | High | Critical |
| `docs/audit/live_uat_gate_smoke_2026-05-17.md` | Production | Latest gate smoke: all 4 roles login, asset detail 200, no 500s, no template links. But logout fails = FAIL. | High | Critical |
| `docs/audit/full_browser_uat_report_2026-05-18.md` | Production | Full browser UAT: 4 roles, 6 public pages, 3,558 controls inventoried. FAIL: logout doesn't terminate sessions, trainee creation broken. | High | Critical |
| `docs/audit/full_browser_uat_retest_2026-05-18_092233Z.md` | Production | Retest after fixes: 2 same blockers remain. 108 screenshots, 109 RBAC probes. Conclusion: FAIL. | High | Critical |
| `docs/audit/full_browser_uat_report_20260518T153020Z.md` | Production | Edge Chromium: all credentials fail from clean context. Contact form rejects valid emails. Auth blocked. FAIL. | High | Critical |

## Test Infrastructure

| Source File | Evidence Type | Reason Included | Confidence | Importance |
|---|---|---|---|---|
| `phpunit.xml` | Config | Unit + Feature suites. cream_test DB (MySQL). APP_ENV=testing. BCRYPT_ROUNDS=4. CACHE=array. | High | High |
| `phpunit-ci.xml` | Config | CI variant: SQLite :memory:. Hardcoded test APP_KEY. DB_CONNECTION=sqlite. | High | High |
| `.github/workflows/ci.yml` | Pipeline | PHP 8.1 + MySQL 8.0 service container. composer install, npm build, php artisan test with phpunit-ci.xml. | High | High |
| `tests/Feature/` (30 files) | Tests | 30 feature test files across 18 sub-modules including Auth, RBAC, Security, CentreIsolation, Trainee, Asset, Activity, Attendance, Dashboard. | High | High |
| `tests/Unit/` (8 files) | Tests | CentreScopeTest, SoftDeleteTest, 4 model tests (Activity, Centre, Trainee, User), DashboardServiceTest, ExampleTest. | High | High |
| `tests/TEST_REPORT_2026-02-05.html` | Historical | 813-line HTML: rendered browser testing and fix report from Feb 2026 audit wave. | Low | Medium |
| `tests/FUNCTIONAL_TEST_REPORT_2026-02-06.html` | Historical | Functional test report from Feb 2026. | Low | Medium |
| `tests/BROWSER_TEST_REPORT_2026-02-06.html` | Historical | Browser test report from Feb 2026. | Low | Low |

---

## Smoke Test Status Summary (Synthesized from Evidence)

- **PHPUnit**: 359 tests, 520 assertions, 0 failures (floor). 37 test files.
- **Playwright**: 181/210 passing (86.2%). Demo-flow: 8/8. Full suite needs 29 test fixes (redirect handling, wizard fields).
- **Live Production**: 2 persistent blockers — logout session termination, trainee creation. All May 15-18 smoke/retest runs show FAIL.
- **CI**: GitHub Actions with PHP 8.1 + MySQL 8.0. Tests run on push/PR to main/dev/Fixers.
- **Divergence**: CI uses SQLite :memory: while local uses MySQL cream_test. Documented as acceptable per standard practice.

---

*Generated by automated repository exploration. Do not modify application code. Classification only.*
