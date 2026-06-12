# CREAMS — Execution Batch 02: P0 Deployment

> **Wave**: 2
> **Priority**: P0 — BLOCKS PRODUCTION DEPLOYMENT
> **Source**: `CURRENT_DEPLOYMENT_STATE.md`, `CURRENT_BLOCKERS.md`, `CRITICAL_FINDINGS_REGISTER.md` (CF-10, CF-11), `PRE_DEPLOY_SECURITY_CHECKLIST.md`, `ai-context/05_MODULE_STATUS/deployment.md`
> **Risk Level**: HIGH — Without these fixes, production push will fail or be insecure
> **Estimated Sessions**: 1-2
> **Dependencies**: Can run parallel to Wave 1 (different file sets). Must complete before Wave 3 (UAT requires functional server).
> **Precondition**: SSH access to Lightsail server. MySQL root access. File write access to `.env.production` and `scripts/server-init.sh`.

---

## Task B2-T1: Generate real APP_KEY for production

| Field | Value |
|-------|-------|
| **Task ID** | B2-T1 |
| **Priority** | P0 |
| **Source Evidence** | `CRITICAL_FINDINGS_REGISTER.md` CF-10, `PRE_DEPLOY_SECURITY_CHECKLIST.md` item 4.3 (RED blocker B1) |
| **Finding Reference** | CF-10 — APP_KEY placeholder in `.env.production` (`base64:GENERATE_NEW_KEY_WITH_php_artisan_key:generate`) |
| **Problem Statement** | `.env.production` contains a placeholder APP_KEY. Without a real key: sessions cannot be encrypted/decrypted, CSRF tokens will not validate, encrypted cookies will fail. This is a RED blocker in the pre-deploy security checklist. |
| **Affected Files** | `.env.production` |
| **Affected Components** | Session encryption, CSRF protection, encrypted cookies, all Laravel encryption |
| **Dependencies** | Requires server access or local PHP with artisan. |
| **Execution Preconditions** | [ ] `.env.production` exists and is writable. [ ] PHP with artisan available on target environment. |
| **Verification Steps** | 1. On the target server: `cd /path/to/creams && php artisan key:generate --env=production`. 2. Verify key was written: `grep APP_KEY .env.production` — should show `base64:` followed by 44 characters (not the placeholder string). 3. Restart PHP-FPM: `sudo systemctl restart php8.2-fpm` (or appropriate version). 4. Test: attempt a login on the production site. Session should be created. CSRF tokens should validate. 5. NEVER commit the generated key to git — `.env.production` is gitignored. Verify with `git status`. |
| **Rollback Strategy** | Before generating: copy existing `.env.production` to `.env.production.backup`. If key generation fails or causes issues, restore from backup. |
| **Completion Criteria** | [ ] `.env.production` APP_KEY is a real 44-character base64 key. [ ] Placeholder string no longer present. [ ] Login + CSRF functional after restart. [ ] Key NOT committed to git. |
| **Estimated Effort** | 5 minutes |
| **Risk of Change** | LOW — standard Laravel operation. All existing sessions will be invalidated (expected — new key). |
| **Notes** | Do NOT use `php artisan key:generate` locally and copy the key — generate on the target server. The key must be unique to the production environment. |

---

## Task B2-T2: Set LOG_LEVEL=warning in production

| Field | Value |
|-------|-------|
| **Task ID** | B2-T2 |
| **Priority** | P0 |
| **Source Evidence** | `CRITICAL_FINDINGS_REGISTER.md` CF-08, `PRE_DEPLOY_SECURITY_CHECKLIST.md` item 5.3 (YELLOW blocker B2), `DELTA_REEVAL_REPORT_2026-03-22.md` C2 |
| **Finding Reference** | CF-08 — Session PII logged on every authenticated request (MainController.php, NotificationController.php, TraineeHomeController.php) |
| **Problem Statement** | `MainController.php` logs `session()->all()` at debug level in 10+ locations (lines 359, 369, 441, 516, 563, 612, 716, 721, 736, 738). Also in `NotificationController.php` (lines 323, 349) and `TraineeHomeController.php` (lines 38, 44, 55, 123). These logs contain user email and IIUM ID. Safe only if production `LOG_LEVEL=warning` or higher. |
| **Affected Files** | `.env.production`, `app/Http/Controllers/MainController.php`, `app/Http/Controllers/NotificationController.php`, `app/Http/Controllers/TraineeHomeController.php` |
| **Affected Components** | Laravel logging, PDPA compliance |
| **Dependencies** | None |
| **Execution Preconditions** | [ ] `.env.production` exists and is writable. [ ] Understand the trade-off: setting LOG_LEVEL=warning silences ALL debug/info logs, not just these PII ones. |
| **Verification Steps** | **Option A (Quick — recommended for immediate safety):** 1. Add `LOG_LEVEL=warning` to `.env.production`. 2. Restart PHP-FPM. 3. Verify: trigger a login and check `storage/logs/laravel.log` — no debug-level entries should appear. **Option B (Thorough — recommended for long-term):** 1. In each affected controller, replace `Log::debug()` calls with `Log::info()` and strip PII fields (email, IIUM ID) from log context arrays. 2. Leave non-PII debug info (performance metrics, query counts) intact. 3. Set `LOG_LEVEL=info` in `.env.production`. 4. Verify: login and check logs — informational entries present but no email/IIUM ID visible. |
| **Rollback Strategy** | Option A: remove `LOG_LEVEL=warning` line or change to `debug`. Option B: revert changed controller files via `git checkout`. |
| **Completion Criteria** | [ ] Production log level set to `warning` or higher (Option A) OR PII stripped from Log::debug calls (Option B). [ ] Login event does NOT write user email or IIUM ID to laravel.log. [ ] YELLOW item 5.3 in pre-deploy checklist → GREEN. |
| **Estimated Effort** | Option A: 5 minutes. Option B: 30 minutes. |
| **Risk of Change** | LOW for Option A. MEDIUM for Option B (touching 3 controllers — requires test verification). |
| **Notes** | Option A is sufficient to clear the pre-deploy gate. Option B can be deferred to Wave 5 (tech debt). |

---

## Task B2-T3: Rotate .env.testing database password

| Field | Value |
|-------|-------|
| **Task ID** | B2-T3 |
| **Priority** | P0 |
| **Source Evidence** | `CRITICAL_FINDINGS_REGISTER.md` CF-11, `DELTA_REEVAL_REPORT_2026-03-22.md` H6 |
| **Finding Reference** | CF-11 — `.env.testing` contains real database password (`DB_PASSWORD=[REDACTED-CF03]`) |
| **Problem Statement** | The testing environment file contains what appears to be a personal password reused across environments. Flagged in March 2026 delta re-evaluation. Rotation status unknown as of May 2026. |
| **Affected Files** | `.env.testing` |
| **Affected Components** | Test database, credential security |
| **Dependencies** | Must verify the password is not used elsewhere before rotating. |
| **Execution Preconditions** | [ ] Confirm `.env.testing` still contains the real password. [ ] Identify if this password is used in any other `.env` file or service. |
| **Verification Steps** | 1. Check current `.env.testing`: `grep DB_PASSWORD .env.testing`. 2. Search for the password elsewhere: `grep -r "<LEAKED-DB-PASSWORD-CF03>" .` (exclude vendor/, node_modules/, archive/). 3. If found in other locations: document all occurrences. Prioritize rotation everywhere. 4. Replace password in `.env.testing` with `DB_PASSWORD=test_password` (or an equally obvious placeholder). 5. Verify tests still pass: `php artisan test --env=testing`. 6. If tests pass, the password was not critical to test DB connectivity (likely using a separate test DB or SQLite). |
| **Rollback Strategy** | If tests fail after password change, the testing DB user still has the old password. Restore old password in `.env.testing` and update the testing DB user instead. |
| **Completion Criteria** | [ ] `.env.testing` no longer contains real password. [ ] Replaced with placeholder. [ ] All tests passing (359/359). [ ] No other file in the repo contains the old password. |
| **Estimated Effort** | 15 minutes |
| **Risk of Change** | LOW — `.env.testing` is only used for local test runs. CI uses `phpunit-ci.xml` with SQLite :memory:. |
| **Notes** | Also verify `.env.testing` is covered by `.gitignore` (the `.env.*` pattern should cover it). Check: `git ls-files -- .env.testing` — if tracked, this password has been in git history and requires BFG cleanup. |

---

## Task B2-T4: Unblock UATSeeder on Lightsail server

| Field | Value |
|-------|-------|
| **Task ID** | B2-T4 |
| **Priority** | P0 |
| **Source Evidence** | `CURRENT_DEPLOYMENT_STATE.md` deployment blockers #1, `ai-context/05_MODULE_STATUS/deployment.md` |
| **Finding Reference** | Deployment blocker — UATSeeder won't run on Lightsail server |
| **Problem Statement** | `php artisan db:seed --class=UATSeeder` fails on the Lightsail server. The exact error is not documented in evidence. REQUIRES VALIDATION — check server logs. Possible causes: class not found (composer autoload issue), database connection failure, migration mismatch, PHP version incompatibility. |
| **Affected Files** | `database/seeders/UATSeeder.php`, server `.env` |
| **Affected Components** | Lightsail PHP environment, Composer autoload, MySQL connection |
| **Dependencies** | B2-T1 (APP_KEY), B2-T3 (testing password rotated — may share root cause) |
| **Execution Preconditions** | [ ] SSH access to Lightsail. [ ] Composer install has been run on server. [ ] Migrations applied: `php artisan migrate:status` shows all DONE. |
| **Verification Steps** | 1. SSH to Lightsail. 2. Run `php artisan migrate:status` — confirm all migrations are DONE. 3. Run `php artisan db:seed --class=UATSeeder` — capture exact error. 4. If class not found: run `composer dump-autoload` and retry. 5. If DB connection fails: verify `.env` DB credentials match actual MySQL users. 6. If FK constraint fails: check migration order and seed order. 7. If PHP version error: verify PHP 8.2 compatibility of UATSeeder. 8. After fix: verify by running `php artisan db:seed --class=UATSeeder --force` — should complete with "3 centres, 16 staff, 21 trainees, 9 activities." |
| **Rollback Strategy** | UATSeeder is idempotent? REQUIRES VALIDATION. If not: `php artisan migrate:fresh --seeder=UATSeeder --force` before retrying. If testing on staging, use a staging-specific database. |
| **Completion Criteria** | [ ] `php artisan db:seed --class=UATSeeder` completes without error. [ ] Expected output: 3 centres, 16 staff, 21 trainees, 9 activities. |
| **Estimated Effort** | 15-45 minutes (depends on root cause — REQUIRES VALIDATION) |
| **Risk of Change** | LOW — UATSeeder uses Faker-only anonymised data. No PDPA risk. Can be run with `--force` on non-production environments. |
| **Notes** | This is the seeder that STAGING_SEED_POLICY mandates for staging/UAT/demo environments. It must work before any stakeholder demo or UAT cycle. |

---

## Task B2-T5: Update nginx config and issue SSL

| Field | Value |
|-------|-------|
| **Task ID** | B2-T5 |
| **Priority** | P0 |
| **Source Evidence** | `CURRENT_DEPLOYMENT_STATE.md` deployment blockers #2, #3, `failed_attempts.md` #7 |
| **Finding Reference** | Deployment blockers — nginx config needs update, SSL not issued |
| **Problem Statement** | Current nginx config routes `/` to `/creams/uat/auth/login` (staging-only routing). Production needs clean direct routes. certbot SSL blocked by previous failed attempt (certbot --apache used on nginx server). |
| **Affected Files** | `creams_subdomain.conf` (or active nginx config on server), SSL certificate |
| **Affected Components** | nginx, certbot, DNS |
| **Dependencies** | B2-T1 (APP_KEY must be real before public access). DNS must point `pdk-creams.org` to Lightsail IP. |
| **Execution Preconditions** | [ ] DNS A record for pdk-creams.org points to Lightsail IP. [ ] Port 80 and 443 open on Lightsail firewall. [ ] nginx installed and running. |
| **Verification Steps** | 1. Update nginx config — replace `/creams/uat/` routing with clean direct routes. The reference config is at `docs/03_Deployment_Guides/nginx/pdk-creams.org.conf`. 2. Test nginx config: `sudo nginx -t`. 3. Reload nginx: `sudo systemctl reload nginx`. 4. Issue SSL: `sudo certbot --nginx -d pdk-creams.org -d www.pdk-creams.org`. 5. Verify HTTPS: `curl -I https://pdk-creams.org` returns 200. 6. Verify HTTP→HTTPS redirect: `curl -I http://pdk-creams.org` returns 301 to https. 7. Verify auto-renewal: `sudo certbot renew --dry-run`. |
| **Rollback Strategy** | Before nginx changes: `sudo cp /etc/nginx/sites-available/creams /etc/nginx/sites-available/creams.backup`. If SSL issuance fails, restore old nginx config and debug certbot error. |
| **Completion Criteria** | [ ] nginx serves clean routes (no /creams/uat/ prefix in production). [ ] HTTPS active with valid SSL certificate. [ ] HTTP→HTTPS redirect working. [ ] Auto-renewal configured. |
| **Estimated Effort** | 30-45 minutes (if DNS is already configured) |
| **Risk of Change** | MEDIUM — nginx misconfiguration can take the site offline. Perform during maintenance window. Have backup config ready. |
| **Notes** | Failed approach #7 (certbot --apache on nginx) is documented in `failed_attempts.md`. Use `certbot --nginx` ONLY. The reference nginx config for production is at `docs/03_Deployment_Guides/nginx/pdk-creams.org.conf`. |

---

## Wave 2 Completion Checklist

- [ ] B2-T1: Real APP_KEY in `.env.production` (placeholder replaced)
- [ ] B2-T2: LOG_LEVEL=warning in production (or PII stripped from logs)
- [ ] B2-T3: `.env.testing` password rotated (placeholder, no real password)
- [ ] B2-T4: UATSeeder runs on Lightsail (3 centres, 16 staff, 21 trainees, 9 activities)
- [ ] B2-T5: nginx updated + SSL issued (HTTPS active, clean routes)
- [ ] Pre-deploy checklist: 0 RED items, 0 YELLOW items remaining
- [ ] PHP version mismatch resolved (composer.lock vs server)
- [ ] All 359 PHPUnit tests still passing after any `.env` changes
- [ ] Server accessible at `https://pdk-creams.org` with valid SSL

---

*Server-side tasks require SSH access. REQUIRES VALIDATION items flagged where evidence is insufficient.*
