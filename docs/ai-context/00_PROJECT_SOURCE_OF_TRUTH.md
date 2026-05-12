# CREAMS — Project Source of Truth

**Status**: VERIFIED — confirmed against codebase 2026-05-07
**This document contains stable facts. Update only when architecture changes.**

---

## Project identity

| Field | Value | Evidence |
|---|---|---|
| Full name | Community-based Rehabilitation Management System | App name in .env |
| Abbreviation | CREAMS | Throughout codebase |
| Domain | Malaysian PPDK rehabilitation centre management | Business logic |
| Origin | Gold Medal FYP — IIUM Computer Science | Historical docs |
| PDPA scope | YES — handles real trainee medical/personal data | IRLSeeder, STAGING_SEED_POLICY |
| Status | Post-demo, Laravel 12 upgrade complete, deployment pending | git log |

---

## Tech stack (VERIFIED 2026-05-07)

| Layer | Technology | Version | Evidence |
|---|---|---|---|
| Framework | Laravel | ^12.0 | composer.json |
| Language | PHP | ^8.2 | composer.json |
| Database | MySQL | 8.0+ | .env.example, migrations |
| Frontend | Blade templates + Tailwind CSS v3 | — | resources/views/ |
| CSS framework | Tailwind CSS (self-hosted Bootstrap partially) | — | public/css/ |
| JS | Vite bundled + partially self-hosted CDN | — | vite.config.js, public/js/ |
| Auth | Custom session via `POST /auth/check` | — | routes/web.php, MainController |
| PDF | DomPDF | ^3.1 | composer.json |
| Testing | PHPUnit 11 via Laravel test runner | — | composer.json |
| Browser tests | Playwright 1.40+ | — | tests/Browser/package.json |

> ⚠️ **NOT Breeze + Sanctum** — the auth stack is custom. `POST /auth/check` handled by `MainController@check`. See `app/Http/Controllers/MainController.php`.

---

## Architecture overview

### URL structure (VERIFIED)

Two routing modes exist simultaneously:

- **Direct routes** (local/testing only): `http://localhost:8000/auth/login`, `/admin/dashboard`
- **Demo-prefixed routes** (all environments): `/creams/{demo_id}/auth/login` — handled by `DemoInstanceMiddleware`

The `demo_id` can be any alphanumeric string up to 32 chars. `uat` and `staging` are recommended for non-production.

> **Production URL distinction (2026-05-11):** The official stakeholder-facing production domain is `pdk-creams.org` and its intended public UX is clean direct routes such as `/`, `/login`, `/contact`, and `/dashboard`. If `/creams/{demo_id}/...` routes still appear in code, treat them as legacy/dev-compat or multi-instance architecture, not as the desired public production address scheme. Production must not visibly expose `demo`, `uat`, `staging`, or similar testing markers in the user journey.

### Multi-centre isolation (VERIFIED)

Two mechanisms enforce PDPA-required data isolation between centres:

1. **Mechanism 1 — CentreScope** (`App\Models\Scopes\CentreScope`): 23 models with direct `centre_id` column use `static::addGlobalScope(new CentreScope)` in `booted()`.
2. **Mechanism 2 — Closure scope**: `AssetMaintenance` and `AssetMovement` (no direct `centre_id`) use `addGlobalScope('centre_isolation', fn($q) => $q->whereHas('asset', ...))`.

Total: 25 models isolated. See `docs/MULTI_CENTRE_ISOLATION.md` for the full list.

---

## User roles (VERIFIED)

| Role | Access level | DB column value | Notes |
|---|---|---|---|
| `admin` | Full system, cross-centre | `staffs.role = 'admin'` | Super admin spans all centres |
| `supervisor` | Centre-scoped, manages teachers/AJK | `staffs.role = 'supervisor'` | — |
| `teacher` | Centre-scoped, trainees and activities | `staffs.role = 'teacher'` | — |
| `ajk` | Centre-scoped, limited read | `staffs.role = 'ajk'` | Committee members |
| `trainee` | Planned, not implemented | — | PLANNED |
| `parent` | Planned, not implemented | — | PLANNED |

> ⚠️ **User model uses `staffs` table**, not `users`. `User::getTable()` returns `'staffs'`.

---

## Database key facts (VERIFIED)

| Fact | Value | Evidence |
|---|---|---|
| DB name (local) | `cream` | .env |
| Total tables | 41 (including 4 views) | SHOW TABLES output |
| Migrations status | All ran (no pending) | `php artisan migrate:status` |
| Primary auth table | `staffs` | User model |
| Trainee table | `trainees` | Trainee model |
| Session storage | `sessions` table | config/session.php |
| Soft deletes | Applied to critical tables (trainees, activities, staffs, assets) | migrations |

---

## Routes summary (VERIFIED)

| Method | Count |
|---|---|
| GET\|HEAD | 416 |
| POST | 165 |
| DELETE | 26 |
| PUT | 22 |
| **Total** | **629** |

Key auth routes:
- `GET /auth/login` — login page (`MainController@login`)
- `POST /auth/check` — submit login (`MainController@check`)
- `POST /auth/save` — register (`MainController@save`)

---

## Non-negotiable project rules

1. **Never use `db:seed`** without `--class=UATSeeder` in non-local environments.
2. **IRLSeeder is local-only** — throws `RuntimeException` if `APP_ENV !== 'local'`.
3. **No real PDPA data** in commits, seeders, factories, or tests.
4. **Roles are exactly**: admin, supervisor, teacher, ajk (not manager, staff, caretaker).
5. **Auth is POST /auth/check** — not Breeze, not Sanctum, not `/login`.
6. **Logout via button only** — `GET /auth/logout` does not fully clear session.
7. **CentreScope already applied** to 25 models — do not re-apply.
8. **Commit SOP** documented in `docs/COMMIT_MESSAGE_SOP.md` — always follow.
9. **Pre-commit hook** at `.githooks/pre-commit` blocks IC patterns and password literals.
10. **PHP 8.2+** required (server is PHP 8.2, not 8.5 local).

---

## Naming conventions

- Controllers: `app/Http/Controllers/{Module}/{Name}Controller.php`
- Models: `app/Models/{Name}.php`
- Views: `resources/views/{role}/{module}/...`
- Tests: `tests/Feature/{Module}/{Name}Test.php`
- Seeders: `database/seeders/{Name}Seeder.php`

---

## Links to existing docs

| Topic | Document |
|---|---|
| Governance | `CLAUDE.md` (root, project overlay) |
| Session memory | `.memsearch/memory/YYYY-MM-DD.md` |
| Multi-centre isolation | `docs/MULTI_CENTRE_ISOLATION.md` |
| Staging seed policy | `docs/04_Deployment_Guides/STAGING_SEED_POLICY.md` |
| User manuals | `docs/10_User_Manuals/` |
| Handover package | `docs/HANDOVER_PACKAGE_2026-05-04.md` |
| Known limitations | `docs/KNOWN_LIMITATIONS_2026-05-04.md` |
| Local setup guide | `docs/LOCAL_SETUP_GUIDE_2026-05-04.md` |
| Commit SOP | `docs/COMMIT_MESSAGE_SOP.md` |
| Codex init prompt | `docs/CODEX_INIT_PROMPT.md` |
| Source of truth (old) | `docs/SOURCE_OF_TRUTH.md` |
| Audit baseline | `docs/audit/test_baseline_2026-04-30.log` |
| Browser findings | `docs/audit/PLAYWRIGHT_FINDINGS_2026-05-04.md` |
