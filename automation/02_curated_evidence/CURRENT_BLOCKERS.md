# CREAMS — Current Blockers

> **Source**: `CRITICAL_FINDINGS_REGISTER.md`, `UAT_INVENTORY.md`, `SMOKE_TEST_INVENTORY.md`, `DEPLOYMENT_INVENTORY.md`, `SECURITY_INVENTORY.md`
> **Date**: 31 May 2026
> **Rule**: Only blockers confirmed by live evidence. Grouped by impact.

---

## P0 — Critical (Blocks Deployment or Stakeholder Demo)

1. **Logout session persistence**
   - Sessions survive logout on pdk-creams.org. User can re-access dashboard without re-authentication.
   - Evidence: `live_uat_gate_smoke_2026-05-17.md` (all May 15-18 UAT runs)
   - File: `app/Http/Controllers/MainController.php` logout handler

2. **Trainee creation 500 error**
   - Registration form submission fails on pdk-creams.org.
   - Evidence: `live_functional_uat_readiness_2026-05-16.md`, `full_browser_uat_report_2026-05-18.md`
   - File: `app/Http/Controllers/TraineeRegistrationController.php`

3. **APP_KEY placeholder in .env.production**
   - Contains `base64:GENERATE_NEW_KEY_WITH_php_artisan_key:generate` — sessions and CSRF will not work.
   - Evidence: `PRE_DEPLOY_SECURITY_CHECKLIST.md` item 4.3, CF-10

4. **3 deployment blockers**
   - UATSeeder won't run on server, nginx config needs update, certbot SSL not issued.
   - Evidence: `deployment.md` (ai-context/05_MODULE_STATUS/)
   - File: `scripts/server-init.sh`, nginx config

5. **Log::debug() PII leak in auth path**
   - `MainController.php` logs `session()->all()` at debug level (email, IIUM ID) in 10+ locations.
   - Evidence: `PRE_DEPLOY_SECURITY_CHECKLIST.md` item 5.3, `DELTA_REEVAL_REPORT_2026-03-22.md` C2
   - File: `app/Http/Controllers/MainController.php`

---

## P1 — High Impact (System Broken, Not Blocking Immediate Deploy)

6. **`database/real_data_backup.json` — real PDPA data on disk**
   - 1,801 lines: 1 real centre (Gombak), 14 users, 57 assets. Unclear if git-tracked.
   - Evidence: CF-01
   - File: `database/real_data_backup.json`

7. **Hardcoded DB passwords in server-init.sh**
   - ProdPassword123!, StagingPassword123!, DevPassword123! in plaintext.
   - Evidence: CF-03
   - File: `scripts/server-init.sh`

8. **Two full repo copies in .claude/worktrees/**
   - Each duplicates `real_data_backup.json`, `.env` files, git history.
   - Evidence: CF-04
   - File: `.claude/worktrees/competent-jepsen-88ca88/`, `.claude/worktrees/nifty-tereshkova-2974e6/`

9. **Live UAT screenshots expose real production email**
   - `lakshmi.krishnan@iium.edu.my` in gate-results.json on pdk-creams.org.
   - Evidence: CF-02
   - File: `docs/audit/screenshots/live-uat-gate-20260516T163536Z/gate-results.json`

10. **`.env.testing` contains real DB password**
    - `DB_PASSWORD=[REDACTED-CF03]`. Flagged Mar 2026. Rotation status unknown.
    - Evidence: CF-11, `DELTA_REEVAL_REPORT_2026-03-22.md` H6
    - File: `.env.testing`

11. **5 PHPUnit test failures**
    - All from `demo_demo_route()` typo. 354/359 pass.
    - Evidence: `test_commands.md`
    - File: unknown — grep for `demo_demo_route`

12. **72 Malaysian IC patterns in git history**
    - 131 commits scanned, 72 matches on Fixers branch.
    - Evidence: `git_history_audit_2026-05-01.log`
    - File: git history (deferred cleanup)

---

## P2 — Medium Impact (Non-Blocking, Cleanup)

13. **6 empty memory checkpoint stubs**
    - May 3, 10, 14, 15, 18, 19 — checkpointer fired but no context saved. False continuity.
    - Evidence: CF-13
    - File: `.memsearch/memory/2026-05-{03,10,14,15,18,19}.md`

14. **Root-level temp files from route auditing**
    - `tmp_routes_audit.json`, `tmp_creams_routes.json`, `routes_export.json` (junk), `routes_analysis.json`.
    - Evidence: CF-14
    - File: repo root

15. **PHPUnit CI uses SQLite, local uses MySQL**
    - Divergence may hide JSON/fulltext edge cases.
    - Evidence: CF-15
    - File: `phpunit-ci.xml` vs `phpunit.xml`

16. **221 N+1 queries on activity schedule page**
    - 19.5s load time. Phase 3 deferred.
    - Evidence: `PERFORMANCE_BASELINE_METHODOLOGY.md`, `MASTER_PROGRESS_LOG.md`
    - File: `app/Http/Controllers/ActivityController.php` schedule method

17. **GombakDataExtractor.php can regenerate PDPA backup**
    - No env gate. If executed, produces fresh `real_data_backup.json`.
    - Evidence: CF-17
    - File: `database/seeders/GombakDataExtractor.php`

18. **Hardcoded IC numbers in TestingGuideDataSeeder**
    - Literal `######-##-####` strings, not Faker-generated.
    - Evidence: CF-18
    - File: `database/seeders/TestingGuideDataSeeder.php`

19. **4 archive .env files with exposed APP_KEYs**
    - `archive/cream/.env`, `archive/Code VSC/CREAM/.env`, `archive/Code VSC/creams/.env`, `archive/Code VSC/creamtest1/.env`
    - Evidence: CF-05
    - File: archive directories

20. **`config/sanctum.php` dormant but present**
    - Auth is custom session-based. Config file may mislead developers.
    - Evidence: CF-20
    - File: `config/sanctum.php`
