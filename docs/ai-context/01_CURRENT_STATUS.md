# CREAMS — Current Status

**Last updated**: 2026-05-08
**Evidence basis**: git status, git diff --stat HEAD, php artisan test, session checkpoints 2026-05-07

---

## Current goal

Complete deployment of CREAMS to `https://creams.faisalhanafi.com` (AWS Lightsail, Amazon Linux 2023).

Deployment is IN PROGRESS and BLOCKED at database seeding. The server has:
- PHP 8.2.29 installed and running
- Laravel 12.58.0 installed via composer
- All 34 migrations run successfully against `creams_app` DB
- `php artisan about` shows correct production config
- Nginx subdomain config NOT YET created
- Certbot HTTPS NOT YET configured
- UATSeeder FAILING (calls `DemoSampleUsersSeeder` which exists on server but is untracked/uncommitted locally)

---

## Current branch and commits

| Field | Value |
|---|---|
| Branch | `main` |
| HEAD | `80d3c3b` — chore(deploy): target PHP 8.2 server — downgrade L13→L12 |
| Local uncommitted files | **14 modified + 4 untracked** (see below) |
| In sync with origin | YES (HEAD matches remote main) |

---

## Current test state (VERIFIED 2026-05-08)

| Metric | Value | vs sprint baseline (359 passed) |
|---|---|---|
| **Passed** | 354 | -5 regressions introduced |
| **Failed** | **5** | NEW failures since 2026-05-07 |
| Total | 359 | Same count |

### Failing tests (VERIFIED — all same root cause):

**Root cause**: `demo_demo_route()` called in `resources/views/auth/login.blade.php` — this function does NOT EXIST. The correct helper is `demo_route()`. This is a TYPO introduced during server deployment work.

| Test | Failure |
|---|---|
| `AuthenticationTest::login page renders` | 500 — `Call to undefined function demo_demo_route()` |
| `AuthenticationTest::auth login page renders` | 500 — same |
| `MiddlewareTest::public routes accessible without auth` | 500 — same |
| `BrokenPageTest::login page loads without duplicate` | 500 — same |
| `CsrfProtectionTest::csrf meta tag is present` | 500 — same |

**Fix**: In `resources/views/auth/login.blade.php`, change `demo_demo_route(` → `demo_route(` (2 occurrences at lines 424 and 473).

---

## Working tree state (VERIFIED 2026-05-08)

### Modified files (committed HEAD vs local):

| File | Lines changed | Nature | Risk | Action |
|---|---|---|---|---|
| `.claude/settings.local.json` | +47/-0 | Claude Code auto-config | NONE | Do not commit |
| `app/Http/Controllers/Activity/ActivityController.php` | +126/-50 | Demo routing changes | MEDIUM | Review before commit |
| `app/Http/Middleware/DemoInstanceMiddleware.php` | +4 | Demo instance setup | LOW | Commit after review |
| `app/Http/Middleware/ValidateRouteParameters.php` | +17/-11 | Route validation changes | MEDIUM | Review |
| `app/Models/AssetMaintenance.php` | +4/-4 | Minor model change | LOW | Review |
| `database/seeders/UATSeeder.php` | +534/-439 | **COMPLETE REWRITE** | HIGH | See seeders status |
| `resources/views/auth/login.blade.php` | typo | `demo_demo_route()` typo | **CRITICAL** | Fix immediately |
| `resources/views/auth/confirmpassword.blade.php` | +1/-1 | demo_route() usage | LOW | Review |
| `resources/views/auth/forgotpassword.blade.php` | +2/-2 | demo_route() usage | LOW | Review |
| `resources/views/auth/passwordemail.blade.php` | +1/-1 | demo_route() usage | LOW | Review |
| `resources/views/auth/register.blade.php` | +2/-2 | demo_route() usage | LOW | Review |
| `resources/views/auth/resetpassword.blade.php` | +2/-2 | demo_route() usage | LOW | Review |
| `resources/views/auth/verifyemail.blade.php` | +2/-2 | demo_route() usage | LOW | Review |
| `resources/views/layouts/app.blade.php` | +48 | demo URL prefix JS injected | MEDIUM | Review carefully |

### Untracked files:

| File | Nature | Action needed |
|---|---|---|
| `.playwright-mcp/` | Playwright MCP artifacts | Gitignore, do not commit |
| `creams_subdomain.conf` | Nginx config for creams subdomain | Commit or move to docs/ |
| `database/seeders/DemoSampleUsersSeeder.php` | New seeder called by UATSeeder | **Must commit with UATSeeder** |
| `tmp_creams_routes.json` | Temporary route export | Delete |

---

## Current database state (LOCAL — cream dev DB)

| Table | Est. rows | State |
|---|---|---|
| centres | 3 | UATSeeder |
| staffs | 17 | UATSeeder |
| trainees | 21 | UATSeeder |
| activities | 9 | UATSeeder |
| migrations | 34 | All ran |

## Server database state (creams_app on 54.169.32.54)

| State | Value |
|---|---|
| All 34 migrations | RAN — confirmed |
| Seeding | **BLOCKED** — `DemoSampleUsersSeeder` not available on server |
| Demo data | NOT SEEDED |

---

## Completed work

| Work | Status | Evidence |
|---|---|---|
| Sprint Days 1–6 | DONE | git log |
| Live demo (4 stakeholders) | DONE | checkpoint |
| Bug fix wave (CSP, session loop, volunteer 500, etc.) | DONE | commits c627ac4–395158e |
| Laravel upgrade 10→11→12 (13 rolled back for PHP compat) | DONE | commits c69e696–80d3c3b |
| MySQL strict-mode migration fixes | DONE | commit 09f0c99 |
| All 4 role flows browser-verified on L12 | DONE | L13 smoke test chapter |
| Merge Fixers→main (force push) | DONE | 2026-05-06 |
| DNS A record for creams.faisalhanafi.com | DONE | resolves to 54.169.32.54 |
| PHP 8.2 installed on server | DONE | AL2023 native repos |
| All 34 migrations run on server creams_app | DONE | server output |
| `php artisan about` shows correct production config | DONE | server output |
| demo_route() helper system added | DONE (uncommitted) | helpers.php, DemoUrlHelper.php |
| UATSeeder rewritten for Malaysian locale + more data | DONE (uncommitted) | UATSeeder.php diff |

---

## Current blockers

1. **CRITICAL (P0)**: `demo_demo_route()` typo in `login.blade.php` → 5 test failures
2. **BLOCKER (P1)**: `DemoSampleUsersSeeder.php` is untracked — UATSeeder fails on server
3. **DEPLOYMENT (P1)**: Nginx config for creams.faisalhanafi.com not yet created
4. **DEPLOYMENT (P1)**: Certbot HTTPS not yet configured

---

## Next 5 recommended actions

1. **IMMEDIATE**: Fix `demo_demo_route()` → `demo_route()` in `login.blade.php` (2 occurrences)
2. **COMMIT**: Stage and commit `DemoSampleUsersSeeder.php` + updated `UATSeeder.php` together
3. **COMMIT**: Stage remaining auth view changes + `app.blade.php` demo URL script (review first)
4. **SERVER**: After push, `git pull` on server + seed + Nginx config + Certbot
5. **TEST**: Run `php artisan test` — must reach 359/0 before any further commits

---

## Do-not-repeat list

- Do NOT use `demo_demo_route()` — it does not exist. Use `demo_route()`.
- Do NOT commit `UATSeeder.php` without `DemoSampleUsersSeeder.php` — they are co-dependent.
- Do NOT use `GET /auth/logout` — use the Logout button (POST).
- Do NOT use `migrate:fresh` on production — use `migrate --force`.
- Do NOT use old credentials (`lakshmi.krishnan@`, `Admin@2024!`).
- Do NOT save Playwright screenshots to `test-results/` — Playwright wipes it.
- Do NOT run IRLSeeder outside local env — throws RuntimeException.
- Do NOT use multi-line backslash continuation in browser SSH — commands break.
- Do NOT use Remi el9 RPM on Amazon Linux 2023 — incompatible.
- Do NOT touch `/etc/nginx/conf.d/faisalhanafi.conf` — Portfolio config, hands off.
- Do NOT assume the app uses Breeze/Sanctum — auth is custom via `POST /auth/check`.
- Do NOT trust `docs/06_Status_Reports/ULTIMATE_FINAL_STATUS.md` — deeply stale.
- Do NOT run `db:seed` without `--class=UATSeeder` in non-local environments.
