# CREAMS — Local Setup Guide (Fresh Machine)

**Date**: 4 May 2026 (sprint Day 6)
**Audience**: A developer or operator setting up CREAMS on a new machine for the first time
**Tested on**: Windows 11 with bash (current dev environment). Steps adapt to macOS / Linux.

---

## 1. What you'll have at the end

A working local CREAMS instance at `http://localhost:8000` with:

- All migrations applied
- Anonymised UAT data seeded (3 centres, 16 staff, 21 trainees, 9 activities)
- Test suite passing (359/359)
- Git pre-commit hook installed
- AI agent governance files in place (CLAUDE.md, AGENTS.md)

Estimated time: **30–45 minutes** for someone who already has PHP 8.1+ and MySQL installed; **1–2 hours** if you need to install those too.

---

## 2. Prerequisites

| Requirement | Minimum version | How to verify |
|---|---|---|
| PHP | 8.1.0 | `php -v` |
| Composer | 2.x | `composer --version` |
| MySQL or MariaDB | MySQL 8.0+ / MariaDB 10.4+ | `mysql --version` |
| Node.js (for Vite asset build) | 18.x+ | `node --version` |
| npm | 9.x+ | `npm --version` |
| Git | 2.x | `git --version` |

### PHP extensions required

CREAMS needs these PHP extensions enabled:

```
bcmath, ctype, curl, dom, fileinfo, gd, intl, mbstring, mysqli, openssl,
pcre, pdo, pdo_mysql, tokenizer, xml, zip
```

Verify with: `php -m | sort`

If any are missing on Linux: `sudo apt install php8.1-{ext}` for each.
On Windows / XAMPP: enable in `php.ini`.

---

## 3. Install steps

### 3.1 Clone the repository

```
git clone <repo-url> creams
cd creams
```

### 3.2 Install PHP dependencies

```
composer install
```

If composer fails on a memory limit, retry with:

```
COMPOSER_MEMORY_LIMIT=-1 composer install
```

### 3.3 Install Node dependencies (for Vite asset build)

```
npm install
```

Skip this step only if you don't need to rebuild front-end assets — the existing `public/build/` artefacts in the repo work for read-only viewing.

### 3.4 Configure environment

Copy the example environment file:

```
cp .env.example .env
```

Open `.env` in an editor and set at minimum:

```
APP_NAME="CREAMS"
APP_ENV=local
APP_KEY=                          # leave blank — generated next step
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=cream                 # or your preferred name
DB_USERNAME=root                  # adjust to your local MySQL user
DB_PASSWORD=                      # adjust to your local MySQL password

SESSION_LIFETIME=480
RATE_LIMIT_LOGIN=5
```

### 3.5 Generate the application key

```
php artisan key:generate
```

This populates `APP_KEY` in `.env` automatically. Without this, sessions and CSRF tokens will not work.

### 3.6 Create the database

In MySQL:

```
CREATE DATABASE cream CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

(Use whatever name you set in `DB_DATABASE`.)

### 3.7 Run migrations

```
php artisan migrate
```

You should see ~80 migrations all reach `DONE`. Time: 30–60 seconds depending on disk speed.

### 3.8 Seed UAT data

For an anonymised dataset suitable for development and testing:

```
php artisan db:seed --class=UATSeeder
```

You should see:

```
UATSeeder complete.
  centres:  3
  staff:    16
  trainees: 21
  activities: 9
```

**Do NOT run** `php artisan db:seed` (the full chain) — it includes seeders with real-data content (`IRLSeeder`, `TestingGuideDataSeeder`) that should only run on authorised environments. See `docs/04_Deployment_Guides/STAGING_SEED_POLICY.md` for the policy.

### 3.9 Build front-end assets (if needed)

For development mode (live reload):

```
npm run dev
```

For a production-style build (one-shot):

```
npm run build
```

Skip this step if you're working purely on backend code — existing built assets are sufficient.

### 3.10 Install the git pre-commit hook

```
git config core.hooksPath .githooks
```

This activates the hook at `.githooks/pre-commit`, which blocks commits containing PDPA-protected patterns (Malaysian IC numbers, password literals).

### 3.11 Start the development server

```
php artisan serve
```

The app is now running at `http://localhost:8000`.

### 3.12 Verify everything works

Open `http://localhost:8000/auth/login` in a browser. Log in as:

- **Email**: `super.admin@uat.creams.test`
- **Password**: `UatPass2026!`

You should land on the admin dashboard with stats showing 16 staff, 21 trainees, 9 activities.

### 3.13 Run the test suite

```
php artisan test
```

Expected: **359 passing, 520 assertions, 0 failures**. Run time ~60–120 seconds.

If tests fail, check that your DB user has CREATE/DROP privileges (the test suite uses transactions and may need to recreate state).

---

## 4. Common setup issues

| Symptom | Cause | Fix |
|---|---|---|
| `php -v` shows < 8.1 | Older PHP | Install PHP 8.1+ from your OS package manager or php.net |
| `composer install` fails: missing extension | PHP extensions not enabled | Install/enable the missing extension (see prerequisites above) |
| `php artisan migrate` fails with "access denied" | DB user permissions | Grant DB user CREATE, DROP, ALTER, INDEX, SELECT, INSERT, UPDATE, DELETE on the database |
| `php artisan migrate` fails with "table already exists" | Database not empty | Drop and recreate the database, or use `migrate:fresh --force` to wipe and re-migrate |
| `php artisan key:generate` fails | `.env` missing or read-only | Ensure `.env` exists (copied from `.env.example`) and is writable |
| Login page returns 500 | Missing `APP_KEY` | Run `php artisan key:generate` |
| All assets 404 (no CSS/JS) | Vite build not run | Run `npm install && npm run build` |
| Pre-commit hook doesn't fire | Hook not activated | Run `git config core.hooksPath .githooks` |
| Tests fail with "table not found" | Migrations not applied to test DB | Tests use `DatabaseTransactions` against the same DB; re-run migrations |
| `APP_ENV=local` not respected, IRLSeeder refuses to run | `.env` typo | Verify `APP_ENV=local` exactly (lowercase) |

---

## 5. AI agent setup (optional)

If you use Claude Code or OpenAI Codex to assist with development:

### Claude Code

`CLAUDE.md` at the repo root is auto-loaded. The `/resume` slash command (defined in `.claude/commands/resume.md`) reconstructs context at session start.

Memsearch hooks are configured in `.claude/settings.local.json` and write checkpoints to `.memsearch/memory/YYYY-MM-DD.md`. The `.memsearch/` folder is gitignored.

### OpenAI Codex

`AGENTS.md` at the repo root is auto-loaded by Codex CLI. It points at `CLAUDE.md` (governance) and `docs/CODEX_INIT_PROMPT.md` (resume + checkpoint protocol).

For the Codex API or web interface, paste the block from `docs/CODEX_INIT_PROMPT.md` as the first message.

The `.memsearch/memory/` files are the portable layer between Claude Code and Codex sessions — both read/write the same 8-section checkpoint format.

---

## 6. Working with the codebase

### Running tests

```
php artisan test                              # all tests, serial
php artisan test --filter=AuthenticationTest  # one test class
php artisan test --filter=test_admin_can      # tests matching name
```

### Inspecting routes

```
php artisan route:list                # all 629 routes
php artisan route:list --path=trainee # routes matching 'trainee'
```

### Tinker REPL

```
php artisan tinker
>>> App\Models\User::where('role', 'admin')->first()
>>> App\Models\Trainee::withTrashed()->count()
```

### Resetting data

```
# Wipe all tables and re-seed UAT data
php artisan migrate:fresh --seeder=UATSeeder --force

# Reset UAT data without dropping tables (slower if there are FK conflicts)
php artisan db:seed --class=UATSeeder
```

### Clearing caches when behaviour seems stuck

```
php artisan optimize:clear
```

---

## 7. Where to go after setup

Read in this order:

1. `CLAUDE.md` (root) — governance
2. `docs/HANDOVER_PACKAGE_2026-05-04.md` — entry point for everything
3. `docs/SOURCE_OF_TRUTH.md` — documentation index
4. `docs/MULTI_CENTRE_ISOLATION.md` — the most important architectural concept
5. `docs/10_User_Manuals/USER_MANUALS_MASTER_INDEX.md` — user-facing workflows
6. `docs/KNOWN_LIMITATIONS_2026-05-04.md` — what's NOT in the system

---

## 8. When you get stuck

1. Check `storage/logs/laravel.log` — most issues leave a trace there.
2. Check the test suite — if tests pass but a feature is broken, the bug is likely in the UI/Blade layer, not the controller.
3. Use tinker to verify model state: `App\Models\User::find(1)`.
4. Check `php artisan migrate:status` for pending migrations.
5. Confirm `.env` matches `.env.example` for any new variables added since you set up.

---

*Created: 4 May 2026 — sprint Day 6*
*Companion to: `docs/HANDOVER_PACKAGE_2026-05-04.md`*
