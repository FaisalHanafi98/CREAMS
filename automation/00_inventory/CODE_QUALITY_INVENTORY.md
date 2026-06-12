# CREAMS — Code Quality Inventory

> **Generated**: 2026-05-31
> **Category**: Code Quality
> **Purpose**: Inventory of code quality standards, linting, formatting, CI/CD, quality rules, static analysis, and test infrastructure.

---

## Static Analysis & Linting

| Source File | Evidence Type | Reason Included | Confidence | Importance |
|---|---|---|---|---|
| `phpstan.neon` | Config | PHPStan level 5. Scans app/. Excludes Kernel.php, Handler.php. Ignores `new static` and undefined Builder methods. | High | High |
| `sonar-project.properties` | Config | SonarScanner: projectKey=creams-rehabilitation-system. PHP 8.1. Excludes migrations, seeders, Blade, letters from analysis. | High | High |
| `docs/03_Technical_Guides/SONARSCANNER_QUALITY_GUIDE.md` | Guide | SonarScanner setup instructions and quality gate configuration recommendations. | Medium | Medium |
| `docs/archive/phpstan_output.txt` | Results | PHPStan level 5 output: 200 files analyzed. Errors in commands (CreateSampleSessions, DiversifyCentres, SyncCentres). | Medium | Medium |
| `.editorconfig` | Config | UTF-8. LF line endings. 4-space indent (2-space for yaml/yml). Trim trailing whitespace (except .md). Final newline insertion. | High | Medium |

## Test Infrastructure

| Source File | Evidence Type | Reason Included | Confidence | Importance |
|---|---|---|---|---|
| `phpunit.xml` | Config | Unit + Feature suites. cream_test DB (MySQL). APP_ENV=testing. BCRYPT_ROUNDS=4. CACHE=array. SESSION_DRIVER=array. | High | High |
| `phpunit-ci.xml` | Config | CI variant: SQLite :memory:. Hardcoded test APP_KEY. DB_CONNECTION=sqlite. | High | High |
| `.github/workflows/ci.yml` | Pipeline | Triggers on push/PR to main/dev/Fixers. PHP 8.1 + MySQL 8.0 service. composer install --no-dev. npm build. php artisan test with phpunit-ci.xml. | High | High |
| `docs/Validate/TEST_BASELINE.md` | Quality Gate | Floor: 359 tests, 520 assertions, 0 failures. No regressions allowed. History: Feb→Apr 2026. | High | Critical |
| `docs/ai-context/06_TESTING_EVIDENCE/test_commands.md` | Results | Test command history: php artisan test results across sessions. 359/0 baseline. 354/5 current (all demo_demo_route() typo). | High | High |
| `tests/TestCase.php` | Base Class | Extends Illuminate\Foundation\Testing\TestCase. Uses CreatesApplication, DatabaseTransactions. $seed=false. Custom actingAs() for session auth. | High | High |

## Commit Standards & Governance

| Source File | Evidence Type | Reason Included | Confidence | Importance |
|---|---|---|---|---|
| `docs/Validate/COMMIT_MESSAGE_SOP.md` | Standard | Type(Scope) conventional commit format. "Verified that" checklist. UK English. Example commits. Avoid-list and prefer-list. Date: 2026-04-25. | High | High |
| `.gitmessage` | Template | Commit template skeleton following COMMIT_MESSAGE_SOP: Type(Scope), Date, Task, Verified that sections. | High | Medium |
| `.githooks/pre-commit` | Gate | Blocks: DB_PASSWORD, API_KEY, API_SECRET, AWS keys, RSA/DSA/EC keys, hardcoded passwords, Malaysian NRIC patterns. Excludes docs/ from password scan. | High | High |
| `CLAUDE.md` | Governance | Root SOP v2.2.0: tiering rules, validation requirements, commit rules. Requires documentation commits separate from code changes. | High | Critical |

## Performance Configuration

| Source File | Evidence Type | Reason Included | Confidence | Importance |
|---|---|---|---|---|
| `config/performance.php` | Config | Query caching (1hr TTL). View caching. Route caching. Asset compression (85% quality). Lazy loading enabled. Pagination (15/page). | High | Medium |
| `Dockerfile` | Config | PHP 8.1-FPM. composer install --no-dev. OPcache not explicitly configured (standard PHP image defaults apply). | High | Medium |

## Bug & Defect Tracking

| Source File | Evidence Type | Reason Included | Confidence | Importance |
|---|---|---|---|---|
| `docs/ai-context/03_BUG_HISTORY/resolved_bugs.md` | Register | 9 confirmed-fixed bugs: volunteer 500 error, dashboard tabs broken, sw.js crash, password hashing, missing CentreScope class, activity statistics zeros, more. | High | High |
| `docs/ai-context/03_BUG_HISTORY/unresolved_bugs.md` | Register | 6 open bugs: P0 demo_demo_route() typo, 3 deployment blockers (seeder, nginx, certbot), weather widget, template link cleanup. | High | Critical |
| `docs/ai-context/03_BUG_HISTORY/failed_attempts.md` | Log | 8 documented failed approaches: Remi el9 on AL2023, Laravel 13 on PHP 8.2, side-by-side PHP, git-submodule for openswoole, more. "Do not repeat" log. | High | High |
| `docs/archive/CHANGELOG.md` | Historical | Oct 2025 pending fixes: contact flash, auth messages, dashboard issues, attendance duplicates. Old but may still contain unfixed items. | Low | Medium |

## Documentation Quality (Alignment Audits)

| Source File | Evidence Type | Reason Included | Confidence | Importance |
|---|---|---|---|---|
| `docs/ai-context/08_DOC_ALIGNMENT/docs_vs_code_alignment.md` | Matrix | 15-row alignment matrix: every doc claim checked against actual code. Verdict column with status and recommended action. | High | Critical |
| `docs/ai-context/08_DOC_ALIGNMENT/deviation_register.md` | Register | 8 classified deviations: stale CLAUDE.md (L10→L12, Breeze→custom auth, Tailwind→Bootstrap5), UATSeeder ahead of commit, more. | High | Critical |
| `docs/ai-context/08_DOC_ALIGNMENT/stale_or_conflicting_docs.md` | Audit | 6 stale docs identified. 5 doc conflicts documented. 4 planned-only features flagged. Consolidation roadmap. | High | High |
| `docs/ai-context/08_DOC_ALIGNMENT/documentation_inventory.md` | Inventory | 155+ docs catalogued across governance, session management, architecture, testing, module docs. Currency status per file. | High | High |
| `docs/audit/MANUAL_AUDIT_FINDINGS_2026-05-02.md` | Audit | 8 user manuals audited against actual system: Manual 01 had 12 discrepancies (URLs, roles, IIUM ID format, password policy, session lifetime errors). | High | High |

## Error Handling Architecture

| Source File | Evidence Type | Reason Included | Confidence | Importance |
|---|---|---|---|---|
| `docs/03_Technical_Guides/ERROR_HANDLING_GUIDE.md` | Guide | 8-component error handling system: Handler, custom log channels (7 specialized), custom exceptions (6 types), HandlesErrors trait, BaseModel, 2 error middlewares, BaseFormRequest, ErrorMonitoringService. | Medium | Medium |
| `app/Http/Middleware/ErrorHandlingMiddleware.php` | Middleware | Request/response logging. Performance monitoring (response times, memory). Error context capture. | High | Medium |
| `app/Http/Middleware/ApiErrorHandlingMiddleware.php` | Middleware | Rate limiting by user type (Admin: 1000/hr, Teacher: 500/hr, Guest: 60/hr). Structured JSON error responses. | High | Medium |

## Form & Validation Quality

| Source File | Evidence Type | Reason Included | Confidence | Importance |
|---|---|---|---|---|
| `docs/03_Technical_Guides/FORM_ENHANCEMENT_IMPLEMENTATION_GUIDE.md` | Guide | Form patterns: client-side + server-side validation, analytics, accessibility. 92→75 forms streamlined (18.5% reduction). | Medium | Medium |

---

## Quality Summary (Synthesized from Evidence)

- **PHPStan**: Level 5. Scans app/. Kernel/Handler excluded.
- **SonarScanner**: Configured. Excludes migrations, seeders, Blade, letters.
- **CI**: GitHub Actions. PHP 8.1 + MySQL 8.0. Tests on push/PR.
- **Tests**: 359 passing floor. 37 test files. ~15-20% coverage (estimated).
- **Bugs**: 6 open (1 P0 typo, 3 deployment, 2 lower). 9 confirmed fixed.
- **Doc Alignment**: 15-row matrix verified. 8 deviations register. 6 stale docs flagged.
- **Commit Standard**: Conventional commit format with "Verified that" checklist. Pre-commit hook blocks secrets + IC patterns.
- **Config**: Performance caching configured (1hr TTL, 85% compression). No php-cs-fixer or pint config found (missing artifact).
- **Error handling**: 8-component architecture documented.

---

*Generated by automated repository exploration. Do not modify application code. Classification only.*
