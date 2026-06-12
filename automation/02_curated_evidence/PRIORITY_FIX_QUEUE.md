# CREAMS — Priority Fix Queue

> **Source**: All execution state files + `CRITICAL_FINDINGS_REGISTER.md` + `CURRENT_BLOCKERS.md`
> **Date**: 31 May 2026
> **Rule**: Strictly ordered by impact. No explanations. No duplication. Derived from all prior state files.

---

## P0 — MUST FIX BEFORE DEPLOYMENT

1. Fix logout session persistence on pdk-creams.org
2. Fix trainee creation 500 error on pdk-creams.org
3. Generate real APP_KEY for .env.production
4. Set `LOG_LEVEL=warning` in .env.production (or replace Log::debug() with Log::info() and strip PII)
5. Fix UATSeeder execution on Lightsail server
6. Update nginx config for clean production routing
7. Issue SSL certificate via certbot --nginx
8. Fix composer.lock PHP version mismatch (requires 8.1, server has 8.2)

---

## P1 — HIGH PRIORITY

1. Verify whether `database/real_data_backup.json` is git-tracked. If yes: add to .gitignore + plan BFG history rewrite. If no: add to .gitignore.
2. Rotate all 3 MySQL passwords in server-init.sh if script was ever deployed to live server. Replace hardcoded passwords with env variable references.
3. Prune or gitignore `.claude/worktrees/` — two full repo copies duplicating real_data_backup.json.
4. Sanitize `docs/audit/screenshots/` — remove real production email from JSON files. Add to .gitignore.
5. Rotate `.env.testing` DB password. Replace with placeholder.
6. Fix `demo_demo_route()` typo causing 5 PHPUnit failures — restore 359/359 pass rate.
7. Replace hardcoded IC numbers in TestingGuideDataSeeder with Faker `numerify()`.
8. Add env gate to GombakDataExtractor.php or delete it.
9. Verify SoftDeletes trait application on Trainee, Staff, ActivitySession models.

---

## P2 — CLEANUP / TECH DEBT

1. Backfill 6 empty memory checkpoint stubs (May 3, 10, 14, 15, 18, 19) or mark as "no activity."
2. Clean root-level temp files: archive tmp_routes_audit.json, delete routes_export.json (junk).
3. Add MySQL service container to CI workflow for test environment parity (or accept documented divergence).
4. Add eager loading to ActivityController::schedule() — fix 221 N+1 queries (19.5s → target <3s).
5. Audit 4 archive .env files — verify no APP_KEY matches live key. Add to .gitignore.
6. Document whether config/sanctum.php has any code references. Remove if dormant.
7. Add PHPStan to CI workflow. Enforce lint in CI (remove continue-on-error).
8. Add `composer audit` to CI. Add coverage tracking with threshold.
9. Add `migrate --pretend` before `--force` in deploy pipeline.
10. Resolve 3 redundant role middleware files — identify active implementation, archive others.
11. Create missing pint.json coding standard config.
12. Create database backup script (mysqldump + cron + rotation).
13. Restrict CORS allowed_origins from `*` to known production domains.
