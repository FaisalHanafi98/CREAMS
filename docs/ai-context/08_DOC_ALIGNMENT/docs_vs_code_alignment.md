# CREAMS — Docs vs Code Alignment Matrix

**Last updated**: 2026-05-08
**Method**: Phase 1 codebase scan vs Phase 0 documentation inventory

---

## Alignment Matrix

| Topic / Feature | Existing Docs Say | Codebase Shows | Status | Evidence | Recommended Action |
|---|---|---|---|---|---|
| Framework version | Laravel 10.x (docs/CLAUDE.md) | Laravel 12.58.0 | STALE DOC | composer.json, `php artisan --version` | Update docs/CLAUDE.md |
| PHP requirement | 8.1+ (docs/CLAUDE.md) | ^8.2 (composer.json) | STALE DOC | composer.json | Update docs/CLAUDE.md |
| Auth system | Breeze + Sanctum (docs/CLAUDE.md) | Custom POST /auth/check | STALE DOC | routes/web.php, MainController | Update docs/CLAUDE.md |
| CSS framework | Tailwind CSS 3.x | Bootstrap 5 + hand-rolled CSS (zero Tailwind in views) | DEVIATION | grep result: no Tailwind classes in views | Accept Bootstrap; update docs |
| Roles | Admin/Manager/Staff/Caretaker (some docs) | Admin/Supervisor/Teacher/AJK | CONFLICTING DOCS | ADR-002, code | Trust ADR-002; flag old docs |
| Test count | Various numbers in old docs | 359 tests (354 passing as of 2026-05-08) | STALE DOC | `php artisan test` | Trust terminal output |
| Test baseline | "359 passed" (TEST_BASELINE.md, May 2026) | 354 passed / 5 failed (demo_demo_route typo) | DEVIATION | `php artisan test` | Fix typo → restore 359/0 |
| Deployment target | Lightsail $5 shared (docs) | Lightsail (54.169.32.54) confirmed | ALIGNED | DNS, curl confirms |  |
| DB seeding | UATSeeder 7/3 (trainees/activities per centre) | UATSeeder rewritten to 20/6 (uncommitted) | CODE AHEAD OF DOC | git diff HEAD -- database/seeders/UATSeeder.php | Commit + update docs |
| CentreScope | 25 models isolated (docs/MULTI_CENTRE_ISOLATION.md) | 25 models (23 direct + 2 closure) | ALIGNED | CentreScope.php, docs confirm | None |
| Custom auth route | POST /auth/check | Confirmed in routes/web.php | ALIGNED | route:list | None |
| demo_route() helper | Not in docs | Exists in app/Helpers/helpers.php + DemoUrlHelper.php | CODE AHEAD OF DOC | helpers.php | Document in module status |
| Login view typo | Not in docs | demo_demo_route() called (invalid) | POSSIBLE DEFECT | `php artisan test` output | Fix immediately |
| IRLSeeder guard | Hard-gated to local (docs/STAGING_SEED_POLICY.md) | RuntimeException on non-local (line 29-34) | ALIGNED | IRLSeeder.php | None |
| Playwright tests | 8/8 demo beats pass (docs/audit) | Not re-run since 2026-05-07 changes | UNVERIFIED | Last run 2026-05-07 | Re-run after typo fix |
| Laravel upgrade path | Proposed L10→L11→L12→L13 | Completed to L12; L13 attempted and rolled back | DEVIATION | git log, composer.json | Document L13 constraint |
| User manuals | v2.0 re-baselined May 2026 | Likely still accurate | ALIGNED (INFERRED) | Manuals re-baselined during sprint | Verify post-deployment |
| Module documentation | 10 module docs in docs/02_Module_Documentation/ | Sprint found 60+ inaccuracies, fixed in manuals | STALE DOC | Handover package notes | Trust v2.0 user manuals over module docs |
| Security checklist | PRE_DEPLOY_SECURITY_CHECKLIST.md exists | Status: NOT CLEARED (multiple RED items) | ALIGNED | Checklist itself | Must pass before real production |
