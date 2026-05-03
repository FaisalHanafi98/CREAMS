# CREAMS User Manual — System Administration

**Version**: 2.0 (re-baselined 2 May 2026)
**Audience**: Whoever is operating the CREAMS instance (developer, deployer, sysadmin)
**Supersedes**: Version 1.0 (deprecated — invented multiple administrator role tiers, claimed Multi-Factor Authentication, IP address restrictions, real-time session monitoring, and other infrastructure features that are not implemented)

---

## 1. Honest scope

CREAMS is a single-application Laravel project. There is **no separate system-administration UI** in the app. "System administration" means operating the codebase, the database, the web server, and the scheduled tasks — not clicking buttons in a CREAMS admin panel.

This manual documents what actually exists. For features that earlier docs claimed but do not exist, see the "Not implemented" section.

The only in-app role with elevated authority is **Admin** (covered in Manual #5 — User & Staff Management). There are no "Security Administrator", "Database Administrator", "Integration Administrator", or "Support Administrator" roles. Those were aspirational labels in the v1.0 manual.

---

## 2. Authoritative sources of truth

When this manual conflicts with one of these, **trust the source, not the manual**:

| Topic | Source of truth |
|---|---|
| Project rules, governance, PDPA constraints | `CLAUDE.md` (repo root) |
| Deployment / staging seed policy | `docs/04_Deployment_Guides/STAGING_SEED_POLICY.md` |
| Multi-centre data isolation architecture | `docs/MULTI_CENTRE_ISOLATION.md` |
| Session checkpoints and memory protocol | `CLAUDE.md` Memory Protocol section + `.memsearch/memory/` |
| Routes inventory | `docs/audit/routes_2026-04-30.json` |
| Test baseline | `docs/audit/test_baseline_2026-04-30.log` |
| Commit message format | `docs/COMMIT_MESSAGE_SOP.md` and `.gitmessage` |
| Pre-commit hook policy | `.githooks/pre-commit` |
| AI agent governance | `CLAUDE.md` (for Claude Code), `AGENTS.md` (for Codex), `docs/CODEX_INIT_PROMPT.md` |

---

## 3. Operating CREAMS (the real list)

### Daily operations

| Task | Command |
|---|---|
| Start local dev server | `php artisan serve` (binds to `127.0.0.1:8000` by default) |
| Run the test suite | `php artisan test` (current baseline: 359 passing) |
| Run pending migrations | `php artisan migrate` |
| Reset DB and re-seed UAT data | `php artisan migrate:fresh --seeder=UATSeeder --force` |
| Reset DB and re-seed real data (LOCAL ONLY) | `php artisan migrate:fresh --seeder=IRLSeeder --force` |
| Inspect routes | `php artisan route:list` (629 routes in current build) |
| Clear caches | `php artisan optimize:clear` |
| Open tinker REPL | `php artisan tinker` |
| Inspect a model | `App\Models\User::find(1)` (in tinker) |

### Database

CREAMS targets MySQL 8 / MariaDB 10.4+. No support for PostgreSQL or SQLite is currently committed (parts may work but are not tested).

Connection config lives in `config/database.php` and reads from `.env`.

### Configuration

Most behaviour is driven by `.env`. Key variables:

| Variable | Purpose |
|---|---|
| `APP_ENV` | `local` enables developer-only features (e.g., direct routes without `/creams/{demo_id}/` prefix; IRLSeeder allowed) |
| `APP_DEBUG` | `true` shows full stack traces — set to `false` in any environment that is not your dev machine |
| `APP_URL` | Base URL the framework uses for asset and route generation |
| `SESSION_LIFETIME` | Session inactivity timeout in minutes (currently `480` = 8 hours) |
| `RATE_LIMIT_LOGIN` | Login attempts allowed per minute per identifier+IP (default 5) |
| `DB_*` | Database connection details |
| `MAIL_*` | Email sending — letters are generated as PDFs and not auto-emailed today |

### Logs

| Log | Path |
|---|---|
| Laravel application log | `storage/logs/laravel.log` |
| HTTP access logs | wherever your web server writes (Nginx default `/var/log/nginx/access.log`) |
| Audit logs (in-DB) | `centre_audit_logs`, `trainee_audit_logs`, `activity_logs` tables |

The in-DB audit logs are the closest CREAMS has to "real-time session monitoring". They record actor, action, old/new values, IP, and timestamp.

---

## 4. Multi-centre administration

The Admin role can switch context across centres without re-logging in. Centre isolation is enforced at the model layer via `CentreScope` (23 models) and `centre_isolation` closure scope (2 models). See `docs/MULTI_CENTRE_ISOLATION.md`.

To add a new centre:

1. Log in as Admin.
2. Navigate to `/centres` → **Add new centre**.
3. Provide `centre_id` (string code, e.g. `05`), `centre_name`, address, contact details, capacity, status.
4. Save.

The new centre appears immediately. Assign staff and seed activities through the normal Admin workflows.

---

## 5. Backup and recovery

There is **no built-in backup UI**. Backups are an operations task done outside CREAMS:

- **Database backup**: standard MySQL/MariaDB dump.
  ```
  mysqldump -u <user> -p <database> > creams-backup-$(date +%F).sql
  ```
- **Storage backup**: copy `storage/app/` (contains uploaded files, generated PDFs, etc.).
- **Code**: tracked in git — clone the repo to recover.
- **Lightsail snapshots** (when deployed there): use the Lightsail console or `aws lightsail create-instance-snapshot`.

Recovery: restore the DB dump into a fresh DB, copy `storage/app/` back, run `composer install --no-dev`, run `php artisan migrate`, point `.env` to the restored DB, restart the web server.

A recovery rehearsal has not been performed for the current build. Adding it to the post-UAT backlog.

---

## 6. Security posture

What is in place:

- **Custom session-based auth** (`POST /auth/check`) — not Breeze, not Sanctum, not JWT
- **Password hashing**: Eloquent's `hashed` cast (bcrypt) on the `password` column
- **CSRF protection** via Laravel's built-in middleware on all `POST/PUT/DELETE`
- **Rate limiting**: login (5/min), forgot-password (3/min, 10/hr), registration (3/min, 5/hr), API (60/min)
- **Session regeneration** on login (prevents session fixation)
- **`SameSite=Lax`** session cookies
- **Centre-scoped data isolation** (the primary PDPA boundary)
- **Soft deletes** on critical tables (trainees, users, activities) so data is recoverable
- **Audit logs** in `*_audit_logs` tables
- **PDPA grep gate** in `.githooks/pre-commit` (blocks Malaysian IC patterns and password literals from entering committed code)

What is NOT in place (despite the v1.0 manual claiming otherwise):

- Multi-Factor Authentication (TOTP, SMS OTP, etc.)
- IP address restrictions
- Real-time session monitoring dashboard
- Automated security alerts
- Geofence access control
- Browser fingerprinting
- Web Application Firewall (WAF) — typically a deployment concern, not an app concern

---

## 7. Performance posture

What is in place:

- Performance indexes on critical tables (migration `2026_01_30_165805_add_performance_indexes_to_critical_tables`)
- Foreign key constraints (migration `2026_01_30_165801`)
- Cached config in production (`php artisan config:cache`)
- Query-builder code review for N+1 patterns where surfaced

What is NOT in place:

- Real-time performance dashboard inside the app
- Automated slow-query alerting
- APM integration (no New Relic, Datadog, etc.)

For ad-hoc performance investigation: enable Laravel Telescope locally, or use `DB::listen` in tinker to dump every query.

---

## 8. Pre-commit hook

`.githooks/pre-commit` blocks commits that contain:

- `DB_PASSWORD=`, `API_KEY=`, `API_SECRET=`, `AWS_SECRET_ACCESS_KEY=`
- `PRIVATE_KEY` literal mentions (RSA / DSA / EC / OpenSSH)
- Password assignments matching `password.*=.*[A-Za-z0-9]{8,}`
- Malaysian IC pattern `[0-9]{6}-[0-9]{2}-[0-9]{4}`

Exclusions:
- `docs/` is exempt from password/key patterns (docs contain code examples)
- `.env.example` is exempt (placeholder values only)
- `.githooks/` is exempt (the patterns themselves appear in the hook source)
- `database/seeders/IRLSeeder.php` is exempt from all patterns (acknowledged real-data seeder, hard-gated to local-only by code)

To enable the hook on a fresh clone: `git config core.hooksPath .githooks`.

Bypassing the hook with `--no-verify` requires explicit user approval per project SOP.

---

## 9. Scheduled tasks

`php artisan schedule:list` shows currently registered scheduled tasks. The scheduler must be triggered by a cron entry on the host:

```
* * * * * cd /path/to/creams && php artisan schedule:run >> /dev/null 2>&1
```

Without this cron entry, no scheduled tasks fire.

---

## 10. AI agent operations (Claude Code and Codex)

CREAMS sessions can be driven by AI agents. Governance:

| File | Loaded by |
|---|---|
| `CLAUDE.md` (repo root) | Claude Code (auto) |
| `AGENTS.md` (repo root) | Codex CLI (auto) |
| `docs/CODEX_INIT_PROMPT.md` | Manual paste at session start |
| `.claude/commands/resume.md` | Claude Code via `/resume` |
| `.memsearch/memory/YYYY-MM-DD.md` | Both Claude Code and Codex (read manually or via /resume) |
| `scripts/*_checkpoint.sh` | Claude Code hooks |

If you want to switch AI tools mid-project, the `.memsearch/memory/` files are the portable layer — both agents read and write the same 8-section checkpoint format.

---

## 11. Not implemented (despite v1.0 manual)

- Web-based system configuration UI (settings live in `.env` only)
- User permission management UI beyond role assignment (no fine-grained per-action permission grid)
- Backup/recovery UI (operations are CLI/script)
- System monitoring dashboard
- Integration management (no built-in OAuth providers, no webhook infrastructure)
- Compliance dashboard
- Multi-tier administrator hierarchy

These are valid future-feature requests, not current capabilities.

---

## 12. Troubleshooting

| Symptom | What to check |
|---|---|
| App throws 500 on every page | Check `storage/logs/laravel.log` for the latest exception. Common causes: missing migration, expired `.env` value, file permission on `storage/` |
| Sessions not persisting between requests | `SESSION_DRIVER` in `.env` may be set to `file` but `storage/framework/sessions/` is not writable |
| Admin cannot see other centres' data | Confirm the user's `role` is exactly `admin` (case-sensitive) and not `Admin` or `administrator` |
| All POST requests fail with 419 | CSRF token missing — check that the form includes `@csrf` and that the session cookie is being sent |
| Pre-commit hook blocks a legitimate commit | Verify the file is in an exempt path (`docs/`, `.githooks/`, `IRLSeeder.php`). If it is genuinely a false positive, propose an additional exclusion in the hook with a clear rationale |
| Tests pass locally but a feature visibly fails in browser | Tests don't cover the UI rendering layer — feature tests use the HTTP layer. Open the page and check the browser console + `storage/logs/laravel.log` |

---

*Updated: 2 May 2026 — sprint Day 4*
*Version: 2.0*
*Source of truth: `CLAUDE.md`, `docs/MULTI_CENTRE_ISOLATION.md`, `docs/04_Deployment_Guides/STAGING_SEED_POLICY.md`, `app/Providers/RouteServiceProvider.php`, `.githooks/pre-commit`, `.env.example`*
