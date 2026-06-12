# CREAMS — Architecture Inventory

> **Generated**: 2026-05-31
> **Category**: Architecture
> **Purpose**: Inventory of system design, module structure, database structure, workflows, and architecture decision records.

---

## System Architecture

| Source File | Evidence Type | Reason Included | Confidence | Importance |
|---|---|---|---|---|
| `CLAUDE.md` | Governance | Defines auth architecture (custom session via POST /auth/check), role definitions (Admin/Supervisor/Teacher/AJK), URL architecture (clean direct routes for production, /creams/{demo_id}/ for staging). CentreScope reference. | High | Critical |
| `AGENTS.md` | Governance | Points to CLAUDE.md and CODEX_INIT_PROMPT.md. Hard reminders: 4 roles, custom auth, PDPA, deployment hold, stale metrics ban. | High | Critical |
| `docs/Validate/MODULE_FUNCTIONALITY_INVENTORY.md` | Module Map | 163 features across 16 modules. Each feature mapped to Controller::method, route, views, roles, Playwright coverage, PHPUnit coverage, priority. Date: 2026-03-24. | High | Critical |
| `docs/Validate/DATABASE_SCHEMA_DOCUMENTATION.md` | Schema | 37 tables documented. ERD. 34 migrations timeline (2019-2026). Column-level inventory. Centre_id columns tracked. | High | Critical |
| `docs/Validate/MULTI_CENTRE_ISOLATION.md` | Security Architecture | 25 scoped models via 2 mechanisms: CentreScope (23) + closure scope via asset join (2). 2 exceptions (Message, Centre). Last updated: 2026-04-25. | High | Critical |
| `docs/03_Technical_Guides/TECHNICAL_ARCHITECTURE.md` | Architecture | System architecture document with component diagrams and full tech stack description. | Medium | Medium |
| `docs/03_Technical_Guides/BUSINESS_LOGIC.md` | Business Logic | Volunteer centre assignment logic (via reviewed_by → centre_id). Letter reference system (LTR/YYYY/MM/NNNN format). Letter template inheritance. Audit requirements. | Medium | Medium |
| `docs/ai-context/04_DATABASE_STATE/schema_assumptions.md` | Verified Schema | VERIFIED mappings: User→staffs table. Trainee dual-ID. 2 attendance tables (staff vs trainee). activity_occurrences vs sessions. | High | Critical |
| `docs/ai-context/04_DATABASE_STATE/migration_status.md` | Migration State | 34 migrations all ran on both local and server. Timeline across 6 date ranges (2019-12 through 2026-03). | High | High |
| `docs/ai-context/04_DATABASE_STATE/seeders_status.md` | Seeder State | UATSeeder rewrite: 20 trainees/centre vs original 7. DemoSampleUsersSeeder untracked dependency. PDPA-safe. | High | High |

## Architecture Decision Records

| Source File | Evidence Type | Reason Included | Confidence | Importance |
|---|---|---|---|---|
| `docs/archive/ADR-001-blade-over-spa.md` | ADR | Accepted: Blade chosen over React SPA for network reliability, simpler deployment, university handover. Date: 2025-01-01. | High | High |
| `docs/archive/ADR-002-six-role-rbac.md` | ADR | Accepted: 6-role RBAC (4 active: Admin/Supervisor/Teacher/AJK. 2 planned: Trainee/Parent). Maps to PPDK org chart. Date: 2025-01-01. | High | Critical |
| `docs/archive/ADR-003-mysql-over-postgresql.md` | ADR | Accepted: MySQL chosen for university hosting compatibility, simpler handover, Laravel ecosystem. Date: 2025-01-01. | High | Medium |

## Route Architecture

| Source File | Evidence Type | Reason Included | Confidence | Importance |
|---|---|---|---|---|
| `routes/web.php` | Routes | 629 routes. Imports 20+ controllers. Dual-URL system: production uses clean routes, non-production prefixes /creams/{demo_id}/. Rate limiting middleware. Role middleware on sensitive groups. | High | Critical |
| `routes/api.php` | Routes | 189 lines. Sanctum /user endpoint. web+auth+throttle group for dashboard search API. ForgotPassword controller. Inline DB queries. | High | High |
| `routes/auth.php` | Routes | 66 lines. Guest middleware. Register (throttle:registration), login (throttle:login), forgot/reset-password, email verification. Breeze-style controllers. | High | High |
| `routes/test.php` | Routes | 85 lines. Debug routes: test-trainee-stats, trainee enrollment queries, centre relationship diagnostics. Raw DB queries. | High | Medium |
| `docs/audit/routes_2026-04-30.json` | Inventory | 629 routes exported from Sprint Day 2 reality audit. JSON format. | High | High |
| `docs/08_Development_Planning/ROUTE_ACCESSIBILITY_REPORT.md` | Analysis | Route accessibility analysis from Jun 2025. Role-based access patterns documented. | Low | Medium |

## Application Architecture

| Source File | Evidence Type | Reason Included | Confidence | Importance |
|---|---|---|---|---|
| `app/Models/` (54 files) | Code | 23 CentreScoped (direct centre_id). 2 closure-scoped (AssetMaintenance, AssetMovement). 2 exceptions (Message, Centre). 27 unscoped (utility models, pivot tables). | High | Critical |
| `app/Http/Middleware/` (22 files) | Code | 4 authentication. 4 role/access control. 6 security (CSRF, encryption, headers, trust proxies, signature, session expiry). 3 request/response. 3 error handling. 1 demo instance routing. | High | High |
| `app/Http/Controllers/` (~40+ files) | Code | Dashboard, Profile, Staff, Trainee, Activity, Asset, Centre, Letter, Message, Notification, Auth, Admin, Reports, Volunteer, IEP, LearningOutcome controllers. | High | High |
| `app/Services/` (9 files) | Code | TraineeService, SessionManager, ScheduleConflictService, NotificationService, ErrorMonitoringService, DashboardService, CentreService, AssetService, AssetRepositoryService. | High | High |
| `config/app.php` | Config | Name: CREAMS. Timezone: UTC default. Locale: en. | High | Low |
| `config/malaysian.php` | Config | Timezone: Asia/KL. Currency: MYR/RM. Date format: d/m/Y. Working days: Mon-Fri. Public holidays list. States + federal territories. | High | Medium |
| `config/trainee.php` | Config | 11 trainee conditions (Autism, Down Syndrome, Cerebral Palsy, etc.). Statuses, genders, referral sources, program levels, 12 evaluation types. | High | Medium |
| `config/performance.php` | Config | Query caching (1hr TTL). View caching. Route caching. Asset compression (85% quality). Lazy loading. Pagination (15/page). | High | Medium |
| `config/auth.php` | Config | Single 'web' session guard. Eloquent provider to User model. | High | High |

## Database Architecture

| Source File | Evidence Type | Reason Included | Confidence | Importance |
|---|---|---|---|---|
| `database/migrations/` (34 files) | Schema | 7 module structure: foundation (centres, users), client (trainees, volunteers), service (activities, sessions, enrollments), attendance, asset, communication (messages, letters, notifications), system constraints (FKs, indexes). | High | Critical |
| `database/factories/` (10 files) | Factories | User, Trainee, Notification, Letter, Centre, Attendance, Asset, ActivitySession, Activity, ActivityEnrollment. All use Faker. | High | Medium |
| `database/seeders/` (18 files) | Seeders | 7 consolidated module seeders. UATSeeder (anonymised). IRLSeeder (real data, env-gated). TestingGuideDataSeeder (test credentials). | High | High |
| `config/database.php` | Config | Default: mysql. Connections: mysql, sqlite, pgsql, sqlsrv. Redis config. | High | Low |

## Documentation Hubs (Architecture-Relevant)

| Source File | Evidence Type | Reason Included | Confidence | Importance |
|---|---|---|---|---|
| `docs/09_New_Features/MEDIA_UPLOAD_SYSTEM.md` | Feature Spec | Optimized media upload system documentation. Date: Jan 2025. | Medium | Medium |
| `docs/09_New_Features/TOAST_NOTIFICATION_SYSTEM.md` | Feature Spec | Toast notification system documentation. Date: Jan 2025. | Medium | Medium |
| `docs/08_Development_Planning/ACTIVITY_MODULE_DIRECTORY_STRUCTURE.md` | Structure | Activity module directory structure reference. | Low | Medium |
| `docs/08_Development_Planning/ASSET_CONTROLLER_REFERENCES.md` | Reference | Asset view files to controller references mapping. | Low | Low |

---

## Architecture Summary (Synthesized from Evidence)

- **Stack**: Laravel 12.x, PHP 8.2+, MySQL 8.0+, Blade + Bootstrap 5 + jQuery, Vite
- **Auth**: Custom session-based (POST /auth/check). NOT Laravel Breeze/Sanctum.
- **Roles**: 4 active (Admin, Supervisor, Teacher, AJK) + 2 planned (Trainee, Parent)
- **Multi-tenancy**: CentreScope GlobalScope on 25 models (23 direct + 2 closure). 2 exceptions documented.
- **Routes**: 629 total. Dual-URL: clean (production) vs /creams/{demo_id}/ (non-production).
- **Database**: 34 migrations. 37 tables. 54 Eloquent models.
- **Rate Limiting**: 9 rate limiters defined in RouteServiceProvider.
- **Security Headers**: 7 headers deployed. CSP, HSTS, X-Frame-Options, etc.
- **ADRs**: 3 ADRs (Blade over SPA, 6-role RBAC, MySQL over PostgreSQL). All 3 still authoritative.

---

*Generated by automated repository exploration. Do not modify application code. Classification only.*
