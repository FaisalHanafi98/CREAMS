# Module: Authentication

**Status**: IMPLEMENTED (custom) | **Last verified**: 2026-05-07

---

## What it does
Custom session-based login. NOT Breeze/Sanctum.

## Routes (VERIFIED)
- `GET /auth/login` — login page (returns view auth.login)
- `POST /auth/check` — credential check, sets session, redirects to role dashboard
- Logout via a form POST button (GET /auth/logout does not fully clear session — BUG-08)

## Controller
`app/Http/Controllers/MainController.php` — `check()` method handles authentication.

## Middleware stack
- `Authenticate` — checks `session('id')` and `session('role')`
- `RoleMiddleware` — enforces `role:admin|supervisor|teacher|ajk`
- `DemoInstanceMiddleware` — prefixes routes with `/creams/{demo_id}/` in non-direct-access mode

## Models
`User` model maps to `staffs` table (not `users`).

## Known bugs
- BUG-08: GET /auth/logout does not fully clear session
- BUG-09: demo_demo_route() typo in login.blade.php causes 500 on test suite

## Next actions
- Fix BUG-09 (demo_demo_route typo) — P0
- Fix BUG-08 (GET logout) — P2
