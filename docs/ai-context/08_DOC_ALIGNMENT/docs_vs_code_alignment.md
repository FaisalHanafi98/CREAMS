# CREAMS — Docs vs Code Alignment Matrix

**Last audited**: 2026-05-07
**Methodology**: Compared selected documentation claims against code inspection and terminal output.

For detailed deviation classification, see `deviation_register.md`.

---

## Alignment Matrix

| Topic / Feature | Existing Docs Say | Codebase Shows | Status | Evidence | Recommended Action |
|---|---|---|---|---|---|
| Framework version | Laravel 10.x | Laravel ^12.0 | STALE DOC | composer.json | Update all docs claiming L10 |
| PHP minimum | 8.1+ | 8.2+ | STALE DOC | composer.json `"php": "^8.2"` | Update LOCAL_SETUP_GUIDE |
| Auth mechanism | Laravel Breeze + Sanctum | POST /auth/check (MainController) | STALE DOC | routes/web.php; MainController.php | Update docs; do not add Breeze |
| User roles | Admin, Manager, Staff, Caretaker, Trainee, Parent (old docs) | admin, supervisor, teacher, ajk | STALE DOC | staffs table; RBAC tests | Update all old role references |
| User table | users (implied) | staffs | ACCEPTED DEVIATION | User model `$table = 'staffs'` | Document in all tech guides |
| Test count | 329 / 306 / 13% coverage | 354 passed / 5 failed (2026-05-08) | STALE DOC | php artisan test | Always run tests; never trust doc metrics |
| Activity categories | FK to activity_categories table | Enum column on activities | ACCEPTED DEVIATION | Migration 2025-09-28 | Doc Category.php as orphan (BUG-07) |
| Testing tools | PHPUnit + Laravel Dusk | PHPUnit 11 + Playwright | STALE DOC | composer.json; tests/Browser/ | Update tech stack docs |
| URL structure | /dashboard, /trainees etc. | Direct (local) + /creams/{demo_id}/* (prod) | CODE AHEAD OF DOC | RouteServiceProvider; DemoInstanceMiddleware | Document demo_route() helper |
| CentreScope count | "26 of 28 models" (old session doc) | 23 + 2 = 25 (corrected Apr 2026) | ALIGNED (corrected) | MULTI_CENTRE_ISOLATION.md updated | No action needed |
| Multi-centre isolation | Described | Verified in PHPUnit (CentreScope tests) | ALIGNED | 3 isolation test classes pass | — |
| Rate limiting | Described | 5/min via throttle:login middleware | ALIGNED | RateLimitTest; routes/web.php | — |
| Soft deletes | Planned | Applied to trainees, activities, staffs, assets | ALIGNED | migrations 2026-01-30 | — |
| IEP tables | Not in original docs | Exist in DB (iep_activity_goals, trainee_education_plans) | CODE AHEAD OF DOC | migration 2026-02-01; DB SHOW TABLES | Document IEP module |
| Trainee audit log | Not in original docs | trainee_audit_logs table; TraineeAuditLog model; 6 call sites | CODE AHEAD OF DOC | migration 2026-03-14; TraineeService.php | Document audit trail |
| Asset movements | Removed then restored | asset_movements table present | CODE AHEAD OF DOC | migration 2026-04-25 | Confirm AssetMovement model is active |
| PDPA compliance | Described | IRLSeeder gated; PDPA grep in pre-commit hook | ALIGNED | IRLSeeder.php:29-34; .githooks/pre-commit | — |
| Deployment target | AWS Lightsail $5 (old docs say ECS/Vercel) | AWS Lightsail Nano, faisalhanafi.com | ACCEPTED DEVIATION | DNS confirmed; session history | Update DEPLOYMENT_GUIDE.md |
| CI/CD pipeline | None described | None implemented | ALIGNED (not planned) | No GitHub Actions in repo | Phase-2 item |
| Demo prefixed routing | Not documented (new feature) | /creams/{demo_id}/* pattern | CODE AHEAD OF DOC | DemoInstanceMiddleware; RouteServiceProvider | Document demo_route() helper |
| GET /auth/logout | Implied to work | Does not fully clear session | POSSIBLE DEFECT | Playwright MCP dry-run | Fix BUG-08 |
| `demo_demo_route()` | Should not exist | Called in login.blade.php lines 424/473 | POSSIBLE DEFECT | php artisan test → 5 failures | Fix BUG-09 (P0) |
