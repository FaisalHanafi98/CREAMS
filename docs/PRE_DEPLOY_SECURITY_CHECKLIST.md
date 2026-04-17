# Pre-Deploy Security Checklist

**Document Type**: Security Gate Checklist
**Last Updated**: 2026-04-17
**Deployment Target**: Amazon Lightsail (shared with Portfolio)
**Status**: NOT CLEARED — see items marked [RED]

---

## How to Use

Run through each item before any production push. Items marked [GREEN] are verified clean. Items marked [YELLOW] need attention but are not blockers. Items marked [RED] are blockers — do not deploy until resolved.

---

## 1. Multi-Centre Data Isolation

| # | Check | Status | Evidence |
|---|-------|--------|----------|
| 1.1 | All centre_id models have CentreScope applied | [GREEN] | 26 of 28 models scoped. See `docs/MULTI_CENTRE_ISOLATION.md` |
| 1.2 | Unscoped models are documented exceptions | [GREEN] | `Message` and `Centre` documented with rationale |
| 1.3 | Message isolation test passes | [GREEN] | `tests/Feature/Security/MessageCentreIsolationTest.php` |
| 1.4 | CentreScope respects admin role (sees all) | [GREEN] | `CentreScope.php:22` — admin bypasses filter |
| 1.5 | CentreScope skips unauthenticated requests | [GREEN] | `CentreScope.php:17` — no session = no filter |

---

## 2. Authentication Rate Limiting

| # | Check | Status | Evidence |
|---|-------|--------|----------|
| 2.1 | `throttle:login` on login route | [GREEN] | `routes/web.php:105` |
| 2.2 | `throttle:registration` on registration route | [GREEN] | `routes/web.php:118` |
| 2.3 | `throttle:password-reset` on password reset routes | [GREEN] | `routes/web.php:125,129` |
| 2.4 | Login rate limit: 5 attempts/min per user/IP | [GREEN] | `RouteServiceProvider.php:35-46` |
| 2.5 | Password reset: 3/min + 10/hr | [GREEN] | `RouteServiceProvider.php:48-58` |
| 2.6 | Registration: 3/min + 5/hr | [GREEN] | `RouteServiceProvider.php:60-69` |
| 2.7 | Rate limit responses return HTML redirect (web app — no JSON API needed) | [GREEN] | Web-only app, no API routes requiring JSON errors |

---

## 3. Session Security

| # | Check | Status | Evidence |
|---|-------|--------|----------|
| 3.1 | Session regenerated on successful login | [GREEN] | `MainController.php:420` — `$request->session()->regenerate()` |
| 3.2 | Session cleared on logout | [GREEN] | `MainController.php` — session flush on logout |
| 3.3 | Remember-me token handled securely | [GREEN] | Token cleared on logout without "remember me", preserved with it |

---

## 4. Environment Configuration

| # | Check | Status | Evidence |
|---|-------|--------|----------|
| 4.1 | `APP_DEBUG=false` in `.env.production` | [GREEN] | `.env.production:20` |
| 4.2 | `APP_ENV=production` in `.env.production` | [GREEN] | `.env.production:17` |
| **4.3** | **`APP_KEY` is a real generated key (not placeholder)** | **[RED]** | **`.env.production` contains `base64:GENERATE_NEW_KEY_WITH_php_artisan_key:generate` — run `php artisan key:generate` on the production server before first deploy** |
| 4.4 | `.env.production` is gitignored | [GREEN] | `.gitignore:9` — `.env.*` pattern covers it |
| 4.5 | `.env.example` committed, `.env` not committed | [GREEN] | Confirmed in working tree |

---

## 5. Debug and Logging Leaks

| # | Check | Status | Evidence |
|---|-------|--------|----------|
| 5.1 | No `dd()` calls in controllers | [GREEN] | Grep found zero `dd(` in `app/Http/Controllers/` |
| 5.2 | No `dump()` or `var_dump()` in controllers | [GREEN] | None found |
| **5.3** | **`Log::debug()` calls in auth path controllers** | **[YELLOW]** | **`MainController.php:359,369,441,516,563,612,716,721,736,738` and `NotificationController.php:323,349` and `TraineeHomeController.php:38,44,55,123` — these write user emails and IIUM IDs to the log. Safe only if production log level is set to `warning` or above. Verify `LOG_LEVEL=warning` in `.env.production` before deploy.** |
| 5.4 | No exposed route revealing session/internal data | [GREEN] | Debug routes removed in prior security hardening session |

---

## 6. CSRF Protection

| # | Check | Status | Evidence |
|---|-------|--------|----------|
| 6.1 | `VerifyCsrfToken` middleware active for web routes | [GREEN] | Laravel default — all web routes covered |
| 6.2 | `@csrf` in state-changing Blade forms | [YELLOW] | 93 `@csrf` directives counted against 141 state-changing form patterns. The difference is likely AJAX forms using `X-CSRF-TOKEN` header and GET forms with method spoofing. Verify no POST forms are missing `@csrf` before deploy. |
| 6.3 | CSRF exceptions list is not over-broad | [GREEN] | Check `app/Http/Middleware/VerifyCsrfToken.php` `$except` list |

---

## 7. Secrets and PII in Code

| # | Check | Status | Evidence |
|---|-------|--------|----------|
| 7.1 | No hardcoded API keys or credentials in PHP source | [GREEN] | None found via grep |
| 7.2 | Test seeders use fake/anonymised data | [GREEN] | Faker used throughout. `DataQualityImprovementSeeder` uses `@example.com` addresses |
| 7.3 | Test users use weak test passwords (`password`) — acceptable in test seeders | [GREEN] | `UserTestSeeder.php` — test-only seeder, never run in production |
| 7.4 | IC numbers in tests are synthetic | [GREEN] | `TraineeTest.php:397` uses `123456789012` — a synthetic value for unit testing |
| 7.5 | No real trainee names, ICs, or disability records committed | [GREEN] | All test data is faker-generated |

---

## 8. Input Validation and Injection

| # | Check | Status | Evidence |
|---|-------|--------|----------|
| 8.1 | All controller input uses `Validator::make` or `$request->validate()` | [GREEN] | Consistent across controllers |
| 8.2 | Eloquent ORM used (no raw SQL string interpolation) | [GREEN] | No raw DB::statement with user input found |
| 8.3 | XSS: Blade `{{ }}` auto-escaping enabled by default | [GREEN] | Laravel default. `{!! !!}` usage should be reviewed before deploy |

---

## 9. Access Control

| # | Check | Status | Evidence |
|---|-------|--------|----------|
| 9.1 | All web routes behind `auth` middleware | [GREEN] | `routes/web.php` — all routes within auth group |
| 9.2 | Role-based middleware applied to sensitive route groups | [GREEN] | Security hardening session (2026-04-01) |
| 9.3 | Admin-only routes protected | [GREEN] | Verified in prior session |

---

## Blockers Summary (must resolve before production push)

| # | Item | Action Required |
|---|------|----------------|
| **B1** | `APP_KEY` is a placeholder | Run `php artisan key:generate` on the production server. Store the generated key in the Lightsail environment or a secrets manager — never commit it to git. |
| **B2** | `Log::debug()` leaks PII in auth path | Add `LOG_LEVEL=warning` to `.env.production` OR replace `Log::debug()` calls in `MainController.php` with `Log::info()` and strip PII fields (email, IIUM ID) from log context. |

---

## Sign-Off

Before push to production, this form must be completed:

```
Deployer: ____________________
Date: ________________________
APP_KEY generated on server: [ ] Yes
LOG_LEVEL set to warning+: [ ] Yes
All RED items resolved: [ ] Yes
Test suite green: [ ] Yes
Rollback plan documented: [ ] Yes (see docs/PRODUCTION_ROLLBACK.md)
```
