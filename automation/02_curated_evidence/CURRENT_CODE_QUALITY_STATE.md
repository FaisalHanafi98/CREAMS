# CREAMS — Current Code Quality State

> **Source**: `CODE_QUALITY_INVENTORY.md`, `ARCHITECTURE_INVENTORY.md`, `SMOKE_TEST_INVENTORY.md`, `CRITICAL_FINDINGS_REGISTER.md`
> **Date**: 31 May 2026
> **Rule**: Code quality only. No architecture or security issues (covered elsewhere).

---

## PHPStan

- Level: 5
- Config: `phpstan.neon` — scans `app/`, excludes Kernel.php and Handler.php, ignores `new static` and undefined Builder methods
- Last run: unknown date — output in `archive/phpstan_output.txt` — 200 files analyzed, errors in commands (CreateSampleSessions, DiversifyCentres, SyncCentres)
- No CI integration — PHPStan not in GitHub Actions workflow
- Source: `CODE_QUALITY_INVENTORY.md`

---

## SonarScanner

- Config: `sonar-project.properties` — projectKey=creams-rehabilitation-system, PHP 8.1, excludes migrations, seeders, Blade, letters
- Quality gates: NOT DOCUMENTED — no defined thresholds for coverage, duplication, complexity
- No CI integration status confirmed
- Source: `CODE_QUALITY_INVENTORY.md`, `MISSING_ARTIFACTS.md` M06

---

## Linting / Formatting

- Pint: referenced in CI workflow but no `pint.json` config file exists
- `.editorconfig`: UTF-8, LF, 4-space indent (2-space for yaml), trim trailing whitespace
- CI lint step: `continue-on-error: true` — lint failures do not block CI
- No php-cs-fixer config
- Source: `CODE_QUALITY_INVENTORY.md`, `MISSING_ARTIFACTS.md` M01

---

## Test Instability Patterns

### Playwright (29 failing, 181/210 passing)
- Post-submit redirect timeout (14 tests): brittle `waitForTimeout()` instead of `waitForURL()` — tests wait on old page context that has been destroyed by 302 redirect
- Activity wizard incomplete (12 tests): backend validation requires fields not filled by test (period_type, times, days, participants). Frontend shows 100% complete, backend rejects with 422
- Performance threshold (1 test): schedule page 19.5s exceeds 10s threshold
- Browser context closed (2 tests): form submission triggers redirect, test tries to interact with destroyed context
- Source: `TEST_STABILIZATION_REPORT.md` (Feb 2026)

### PHPUnit (5 failing as of May 8)
- All 5 failures = single `demo_demo_route()` typo — not real regressions
- Source: `test_commands.md`

### CI vs Local Divergence
- CI: SQLite :memory: (`phpunit-ci.xml`)
- Local: MySQL `cream_test` (`phpunit.xml`)
- Risk: JSON columns, fulltext indexes, spatial types may pass in CI but fail locally
- Source: CF-15

---

## Duplication and Inconsistency

- **3 role middleware files** (`RoleMiddleware.php`, `Role.php`, `EnhancedRoleMiddleware.php`) — code duplication, confusion about active implementation
- **2 attendance models** (`Attendances.php`, `ActivityAttendance.php`) with similar fields but different table structures
- **49 inline role checks** across 12 controllers — same authorization pattern repeated without abstraction
- **2 letter controllers** (Profile/LetterController + modern letter routes) — parallel implementations
- **Services duplicated by controllers** — `DELTA_REEVAL` notes controllers duplicate service work
- Source: `ARCHITECTURE_INVENTORY.md`, `DELTA_REEVAL_REPORT_2026-03-22.md`

---

## CI Pipeline Gaps

| Gap | Detail | Source |
|-----|--------|--------|
| No PHPStan in CI | Static analysis not run on push/PR | `CODE_QUALITY_INVENTORY.md` |
| No coverage tracking | PHPUnit runs with no `--coverage` flag. 0% coverage would pass CI. | `DELTA_REEVAL_REPORT_2026-03-22.md` H7 |
| Lint `continue-on-error: true` | Pint failures do not block CI. | `DELTA_REEVAL_REPORT_2026-03-22.md` M4 |
| No dependency audit | `composer audit` not in CI | `ci.yml` |
| No SAST | No security scanning in pipeline | `DELTA_REEVAL_REPORT_2026-03-22.md` §2.6 |
| No production migration test | `--force` runs directly in production without `--pretend` first | `DELTA_REEVAL_REPORT_2026-03-22.md` H8 |

---

## Commit Quality

- Standard: `COMMIT_MESSAGE_SOP.md` — Type(Scope) conventional format, "Verified that" checklist, UK English
- Template: `.gitmessage` available, activated via `git config commit.template .gitmessage`
- Pre-commit hook: active — blocks secrets, IC patterns
- Source: `CODE_QUALITY_INVENTORY.md`

---

## Documentation Alignment

- 6 stale docs flagged (old CLAUDE.md copies, CONSOLIDATED_DOCUMENTATION_INDEX, CREAMS_CODEBASE_DOCUMENTATION, PRODUCTION_READINESS_ROADMAP, SECURITY_AUDIT_REPORT) — all now archived
- 8 deviations registered between docs and actual code (L10→L12, Breeze→custom auth, Tailwind→Bootstrap5, 6 roles→4 active, "329 tests"→359)
- 15-row doc-vs-code alignment matrix — each claim checked against code
- All stale docs now moved to archive. Current docs consolidated into 88 active files.
- Source: `CODE_QUALITY_INVENTORY.md`, `deviation_register.md`, `docs_vs_code_alignment.md`
