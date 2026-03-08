# CREAMS Testing Infrastructure + CI/CD Implementation Plan

## Research Findings (Current State)

### What Exists
- **9 PHPUnit test classes** (13% coverage) — but AuthenticationTest is WRONG: it POSTs to `/login` with `email` field, while CREAMS actually uses POST `/auth/check` with `identifier` field
- **TestCase.php** already has custom `actingAs()` that sets session data — good foundation
- **`mysql_test` connection** configured in `config/database.php` → `cream_test` database
- **10 factories** with role states (UserFactory has `admin()`, `supervisor()`, `teacher()`, `ajk()`)
- **phpunit.xml** has SQLite in-memory COMMENTED OUT — tests currently hit dev MySQL
- **CREAMS_Testing_Infrastructure_PRD.md** exists with full test strategy (1200 lines)
- **Laravel Dusk NOT installed** (not in composer.json despite PRD specifying it)
- **Zero CI/CD** — no `.github/workflows/`, no Dockerfile, no docker-compose.yml
- **Zero ADRs** — no `docs/adr/` directory

### Critical Issues Found
1. **Existing auth tests are broken** — they test Laravel Breeze scaffolding, not the actual CREAMS custom session auth (`MainController::check()`)
2. **No test database isolation** — SQLite commented out, `cream_test` DB may not exist
3. **Factory cascading problem** — `AttendanceFactory` creates 4 related records per call, `ActivityFactory` auto-creates a teacher user
4. **Table name inconsistency** — Migration creates `activity_occurrences` but code references `activity_sessions`

---

## Implementation Plan

### PHASE 1: Fix Test Foundation (MUST DO FIRST)
**Why**: Everything else depends on a working test environment.

#### 1A. Fix phpunit.xml — Enable SQLite for CI, MySQL for local
Create `phpunit-ci.xml` for GitHub Actions (SQLite in-memory):
```xml
<env name="DB_CONNECTION" value="sqlite"/>
<env name="DB_DATABASE" value=":memory:"/>
```

Update `phpunit.xml` to use `mysql_test` connection properly:
```xml
<env name="DB_CONNECTION" value="mysql_test"/>
```

#### 1B. Fix existing AuthenticationTest
Current test is fundamentally wrong. Rewrite to test ACTUAL login flow:
- POST to `/auth/check` (not `/login`)
- Use field `identifier` (not `email`)
- Assert session has `id`, `role`, `centre_id` keys (not `assertAuthenticated()`)
- Test both email and IIUM ID login paths
- Test each of 4 roles: admin, supervisor, teacher, ajk

#### 1C. Verify factory compatibility with SQLite
- Test that all 10 factories work with SQLite in-memory
- Fix any MySQL-specific queries (e.g., `DB::table()` raw calls in AssetFactory)
- Ensure Centre seeding works in test context

---

### PHASE 2: GitHub Actions CI Pipeline
**File**: `.github/workflows/ci.yml`

```yaml
Triggers: push to Fixers, pull requests to main/Fixers
Matrix: PHP 8.1
Steps:
  1. Checkout code
  2. Setup PHP 8.1 + extensions (mbstring, xml, sqlite3, dom, curl)
  3. Cache Composer dependencies
  4. Copy .env.example → .env
  5. composer install --no-interaction --prefer-dist
  6. php artisan key:generate
  7. touch database/testing.db (SQLite for CI)
  8. php artisan migrate --env=testing --database=sqlite
  9. Run Laravel Pint (lint check, non-blocking)
  10. Run PHPUnit with phpunit-ci.xml
  11. Upload coverage report as artifact
```

**Additional files**:
- `phpunit-ci.xml` — SQLite configuration for CI
- CI badge added to README.md

**Decision: SQLite for CI, MySQL for local**
- CI runs faster with SQLite in-memory (no MySQL service needed)
- Local tests use `cream_test` MySQL DB for production-like behavior
- Trade-off: some MySQL-specific features won't be caught in CI, but coverage gain is worth it

---

### PHASE 3: PHPUnit — Authentication & RBAC Tests
**Directory**: `tests/Feature/Auth/`

#### 3A. Rewrite AuthenticationTest (replace existing)
| Test | What it verifies |
|------|-----------------|
| `test_login_page_renders` | GET `/login` returns 200 |
| `test_admin_can_login_via_email` | POST `/auth/check` with admin email → session set, redirect to admin dashboard |
| `test_teacher_can_login_via_email` | POST `/auth/check` with teacher email → session set, redirect to teacher dashboard |
| `test_supervisor_can_login_via_email` | Same pattern for supervisor |
| `test_ajk_can_login_via_email` | Same pattern for AJK |
| `test_can_login_via_iium_id` | POST `/auth/check` with IIUM ID → session set |
| `test_invalid_credentials_rejected` | Wrong password → redirect back with error |
| `test_nonexistent_user_rejected` | Unknown email → redirect back with error |
| `test_inactive_user_cannot_login` | User with status != 'active' → rejected |
| `test_session_data_set_correctly` | After login: session has id, iium_id, name, role, email, centre_id |
| `test_logout_clears_session` | POST `/logout` → session flushed, redirected |

#### 3B. New RBACTest
| Test | What it verifies |
|------|-----------------|
| `test_admin_can_access_admin_routes` | Admin can hit /admin/users, /admin/centres, /letter-templates |
| `test_teacher_cannot_access_admin_routes` | Teacher hitting admin routes → 403 or redirect |
| `test_supervisor_can_access_supervisor_routes` | Supervisor can access their permitted routes |
| `test_ajk_limited_access` | AJK can only access their permitted subset |
| `test_unauthenticated_redirected_to_login` | No session → GET any protected route → redirect /login |

#### 3C. New MiddlewareTest
| Test | What it verifies |
|------|-----------------|
| `test_auth_middleware_blocks_unauthenticated` | No session → blocked |
| `test_centre_access_middleware_filters_by_centre` | User from centre 01 cannot access centre 02 data |
| `test_expired_session_redirected` | Session without required keys → redirect to login |

**Estimated test count**: 20-25 tests
**Coverage impact**: 13% → ~25% (covers MainController, middleware, session handling)

---

### PHASE 4: PHPUnit — Critical Business Flow Tests
**Priority**: Test the 5 core workflows identified in planning mode.

#### 4A. `tests/Feature/Trainee/TraineeManagementTest.php`
| Test | What it verifies |
|------|-----------------|
| `test_admin_can_view_trainee_list` | GET /trainees/home → 200, sees trainee data |
| `test_admin_can_create_trainee` | POST /trainees/register → creates record |
| `test_admin_can_view_trainee_profile` | GET /traineeprofile/{id} → 200 |
| `test_teacher_can_view_trainees` | Teacher has read access |
| `test_trainee_data_is_centre_scoped` | Only see trainees from own centre |

#### 4B. `tests/Feature/Activity/ActivityManagementTest.php`
| Test | What it verifies |
|------|-----------------|
| `test_can_view_activities_list` | GET /activities/home → 200 |
| `test_admin_can_create_activity` | POST /activities → creates record |
| `test_can_view_activity_sessions` | GET /activities/{id}/sessions → 200 |
| `test_can_view_schedule` | GET /activities/schedule → 200 |

#### 4C. `tests/Feature/Asset/AssetManagementTest.php`
| Test | What it verifies |
|------|-----------------|
| `test_can_view_asset_list` | GET /centre/asset-parents → 200 |
| `test_can_create_asset` | POST /centre/asset-parents → creates record |
| `test_can_view_asset_detail` | GET /centre/asset-parents/{id} → 200 |
| `test_asset_filtering_works` | Category/centre/status filters return correct results |

#### 4D. `tests/Feature/Letter/LetterGenerationTest.php`
| Test | What it verifies |
|------|-----------------|
| `test_can_view_letter_generator` | GET /letters/modern → 200 |
| `test_can_create_letter` | POST /letters/modern/generate → creates letter |
| `test_pdf_generation_does_not_crash` | Letter → PDF download returns 200 with PDF content-type |

#### 4E. `tests/Feature/IEP/IepManagementTest.php`
| Test | What it verifies |
|------|-----------------|
| `test_can_view_iep_list` | GET /iep → 200 |
| `test_can_create_iep_plan` | POST /iep → creates record with required plan_name |

**Estimated test count**: 20-25 tests
**Coverage impact**: ~25% → ~40% (covers core controllers and models)

---

### PHASE 5: Docker Setup
**Files to create**:

#### 5A. `Dockerfile`
- Base: `php:8.1-fpm`
- Extensions: mbstring, xml, mysql, pdo_mysql, zip, gd, curl
- Install Composer
- Copy app code
- `composer install --no-dev --optimize-autoloader`

#### 5B. `docker-compose.yml`
| Service | Image | Port | Purpose |
|---------|-------|------|---------|
| `app` | Custom Dockerfile | 9000 | PHP-FPM + Laravel |
| `nginx` | nginx:alpine | 80 | Web server |
| `db` | mysql:8.0 | 3306 | Database |

Volumes: `./storage`, `./database`, MySQL data persistence

#### 5C. `docker/nginx/default.conf`
- Standard Laravel nginx config (public/ as root, php-fpm upstream)

#### 5D. `.dockerignore`
- node_modules, vendor, .git, archive/, test-results/, .env

---

### PHASE 6: Architecture Decision Records
**Directory**: `docs/adr/`

#### ADR-001: Server-Rendered Blade over React SPA
- **Context**: CREAMS serves Malaysian PPDK rehabilitation centres with varying network quality
- **Decision**: Laravel Blade (server-rendered)
- **Reasons**: Reliability on slow networks, simpler deployment for university handover, no separate API layer needed for internal tool, reduced bundle size
- **Trade-offs**: Less interactive UI, full page reloads

#### ADR-002: 6-Role RBAC Design
- **Context**: PPDK organizational structure has distinct access levels
- **Decision**: 6 roles (Admin, Supervisor, Teacher, AJK, plus Trainee, Parent as future)
- **Reasons**: Maps 1:1 to actual org chart, principle of least privilege, audit trail per role
- **Trade-offs**: More middleware complexity than 2-3 role system

#### ADR-003: MySQL over PostgreSQL
- **Context**: University hosting environment and future handover considerations
- **Decision**: MySQL 8.0
- **Reasons**: University server compatibility, simpler setup for non-technical handover, sufficient for relational data (no vector/JSON needs), wider Malaysian hosting support
- **Trade-offs**: No native JSON operators, no PostGIS if mapping needed later

---

## Execution Order and Dependencies

```
PHASE 1 (Foundation) ← MUST complete before anything else
  ↓
PHASE 2 (CI Pipeline) ← Needs Phase 1 to have working tests to run
  ↓
PHASE 3 (Auth Tests) ← Can run in CI immediately
  ↓
PHASE 4 (Business Tests) ← Builds on Phase 3 patterns
  ↓
PHASE 5 (Docker) ← Independent, can run in parallel with Phase 3-4
  ↓
PHASE 6 (ADRs) ← Independent, can run in parallel with anything
```

## Constraints Checklist
- [ ] PDPA: All test data uses Faker, no real names/ICs
- [ ] No modification to existing application code (test-only session)
- [ ] All factories use anonymized data
- [ ] Target: 13% → 40% coverage
- [ ] Follow existing Laravel conventions

## Risk Assessment

| Risk | Impact | Mitigation |
|------|--------|------------|
| SQLite vs MySQL behavior differences | Tests pass in CI but fail locally | Run both: SQLite in CI, MySQL locally |
| Factory cascade creates slow tests | Test suite takes 5+ minutes | Use `DatabaseTransactions` instead of `RefreshDatabase` where possible |
| `activity_occurrences` vs `activity_sessions` table name mismatch | Migration failures in test DB | Investigate and document which name is correct before writing tests |
| Custom auth makes standard Laravel test helpers unreliable | `assertAuthenticated()` doesn't work | Use custom session assertions instead (already started in TestCase.php) |

## Files to Create (Summary)

| File | Phase |
|------|-------|
| `.github/workflows/ci.yml` | 2 |
| `phpunit-ci.xml` | 1 |
| `tests/Feature/Auth/AuthenticationTest.php` (REWRITE) | 3 |
| `tests/Feature/Auth/RBACTest.php` | 3 |
| `tests/Feature/Auth/MiddlewareTest.php` | 3 |
| `tests/Feature/Trainee/TraineeManagementTest.php` | 4 |
| `tests/Feature/Activity/ActivityManagementTest.php` | 4 |
| `tests/Feature/Asset/AssetManagementTest.php` | 4 |
| `tests/Feature/Letter/LetterGenerationTest.php` | 4 |
| `tests/Feature/IEP/IepManagementTest.php` | 4 |
| `Dockerfile` | 5 |
| `docker-compose.yml` | 5 |
| `docker/nginx/default.conf` | 5 |
| `.dockerignore` | 5 |
| `docs/adr/ADR-001-blade-over-spa.md` | 6 |
| `docs/adr/ADR-002-six-role-rbac.md` | 6 |
| `docs/adr/ADR-003-mysql-over-postgresql.md` | 6 |

## Estimated Coverage Impact

| Phase | Tests Added | Coverage |
|-------|------------|----------|
| Current | 9 (broken) | 13% |
| After Phase 3 | +20 auth/RBAC | ~25% |
| After Phase 4 | +20 business flows | ~40% |
| **Total** | ~49 working tests | **40%** |
