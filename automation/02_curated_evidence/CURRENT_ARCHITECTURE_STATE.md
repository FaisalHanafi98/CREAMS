# CREAMS — Current Architecture State

> **Source**: `ARCHITECTURE_INVENTORY.md`, `MULTI_CENTRE_ISOLATION.md`, `REFACTORING_INVENTORY.md`
> **Date**: 31 May 2026
> **Rule**: Only from architecture inventory. No new analysis.

---

## Correct Architecture Components

- Laravel 12.x, PHP 8.2+, MySQL 8.0+, Blade + Bootstrap 5 + jQuery, Vite
- Custom session-based auth via POST /auth/check (NOT Breeze/Sanctum) — `MainController@check`
- 4 active roles: Admin, Supervisor, Teacher, AJK (+ Trainee/Parent planned) — ADR-002
- 3 ADRs: Blade over SPA (ADR-001), 6-role RBAC (ADR-002), MySQL over PostgreSQL (ADR-003)
- CentreScope GlobalScope: 23 models with direct centre_id (Mechanism 1)
- `centre_isolation` closure scope: 2 models without centre_id via asset relationship join (Mechanism 2)
- 2 documented CentreScope exceptions: Message (controller-level sender_id isolation), Centre (tenant root — cannot self-scope)
- 54 Eloquent models total
- 22 middleware (4 auth, 4 role/access, 6 security, 3 request/response, 3 error handling, 1 demo routing)
- 9 service classes (TraineeService, SessionManager, ScheduleConflictService, NotificationService, ErrorMonitoringService, DashboardService, CentreService, AssetService, AssetRepositoryService)
- 9 rate limiters in RouteServiceProvider (login 5/min, password-reset 3/min+10/hr, registration 3/min+5/hr, api 60/min, dashboard 120/min, etc.)
- 7 security headers via SecurityHeadersMiddleware
- 629 routes in web.php, dual-URL system (production clean routes, non-production /creams/{demo_id}/ prefix)
- 34 migrations, 37 tables, 10 factories, 18 seeders
- Malaysian config: timezone Asia/KL, MYR currency, d/m/Y dates, public holidays, states
- Trainee config: 11 conditions, statuses, genders, referral sources, 12 evaluation types
- Performance config: query caching (1hr), view caching, route caching, asset compression (85%), lazy loading, pagination (15/page)

---

## Inconsistent Patterns

- **49 inline `if (session('role'))` checks** across 12 controllers — authorization is mixed: middleware (good) + inline (fragile). No Laravel Policy classes exist (`app/Policies/` directory missing). Source: `DELTA_REEVAL_REPORT_2026-03-22.md` L3, `ARCHITECTURE_INVENTORY.md`.
- **Fat controllers**: 400-900 lines. Business logic in controllers rather than services. Source: `DELTA_REEVAL_REPORT_2026-03-22.md` L1.
- **Services exist but underutilized**: controllers duplicate service work. Source: `DELTA_REEVAL_REPORT_2026-03-22.md` L2.
- **3 redundant role middleware files**: `RoleMiddleware.php`, `Role.php`, `EnhancedRoleMiddleware.php` — confusion about which is active. Source: `DELTA_REEVAL_REPORT_2026-03-22.md` L5.
- `Message.php` contains `belongsTo(Centre::class, 'centre_id')` and `scopeForCentre()` referencing a column that does not exist in the messages table. Methods never called. Left in place to avoid scope creep. Source: `MULTI_CENTRE_ISOLATION.md`.
- `config/sanctum.php` exists but auth is custom session-based. Dormant config may mislead. Source: CF-20.

---

## Over-Engineered or Dormant

- **Sanctum config**: unused — auth is custom session. Source: CF-20.
- **3 redundant role middleware**: only one should be active. Source: `DELTA_REEVAL_REPORT_2026-03-22.md` L5.
- **Message model**: contains relationship methods for non-existent centre_id column. Source: `MULTI_CENTRE_ISOLATION.md`.
- **Docker**: Dockerfile + docker-compose exist but deployment is bare-metal via server-init.sh. Source: `DEPLOYMENT_INVENTORY.md`.
- **8-component error handling**: Handler, 7 log channels, 6 custom exceptions, HandlesErrors trait, BaseModel, 2 error middlewares, BaseFormRequest, ErrorMonitoringService — documented but usage status across all controllers unknown. Source: `ERROR_HANDLING_GUIDE.md`.

---

## Structural Risks

### Scaling Risks
- No Policy classes — authorization stays middleware + inline. Works at current scale but fragile at growth. Source: `DELTA_REEVAL_REPORT_2026-03-22.md` Risk 1.
- Fat controllers — maintenance burden grows with each feature. Source: `DELTA_REEVAL_REPORT_2026-03-22.md` Risk 2.
- No field-level encryption on PII (IC numbers, phone, address) — plaintext in DB. Mitigated by DB access controls but not eliminated. Source: `DELTA_REEVAL_REPORT_2026-03-22.md` Risk 3.
- SQLite CI vs MySQL local divergence — edge cases in JSON/fulltext could slip through. Source: `DELTA_REEVAL_REPORT_2026-03-22.md` Risk 4, CF-15.

### Maintainability Risks
- 49 inline role checks — any new role or permission change requires updating 12 controllers. Source: `ARCHITECTURE_INVENTORY.md`.
- No SAST in CI pipeline — security depends on manual review. Source: `DELTA_REEVAL_REPORT_2026-03-22.md` Risk 5.
- 85% of codebase written without tests — permanent risk. TDD forward prevents new risk only. Source: `DELTA_REEVAL_REPORT_2026-03-22.md` Risk 6.
- Message isolation at application layer (controller), not database layer — no DB-level centre_id to enforce boundary. Source: `MULTI_CENTRE_ISOLATION.md` Message section.

### Data Integrity Risks
- SoftDeletes migration exists (deleted_at columns) but trait application status on Trainee, Staff, ActivitySession models UNKNOWN as of May 2026. Mar 2026 delta flagged as C3 — models lacked trait. Source: `DELTA_REEVAL_REPORT_2026-03-22.md` C3.
- No data purge mechanism — no automated deletion of old records. Source: `DELTA_REEVAL_REPORT_2026-03-22.md` §2.5.
