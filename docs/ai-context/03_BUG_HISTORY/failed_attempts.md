# CREAMS — Failed Attempts

**Last updated**: 2026-05-08
Do not repeat these. Each entry explains what was tried, why it failed, and the lesson.

---

## FA-01: Remi el9 RPM on Amazon Linux 2023

**Attempted**: `sudo dnf install https://rpms.remirepo.net/enterprise/remi-release-9.rpm` to get PHP 8.3.
**Failure**: Package requires `redhat-release >= 9.7` and `system-release(releasever) = 9` — neither provided by AL2023.
**Lesson**: AL2023 is NOT RHEL 9. Remi el9 packages are incompatible. AL2023 has PHP 8.1 and 8.2 natively; PHP 8.3 requires third-party repo or source build.
**Do not repeat**: Never use Remi el9 on Amazon Linux 2023.

---

## FA-02: Laravel 13 on PHP 8.2

**Attempted**: Deploy CREAMS with `laravel/framework ^13.0` to server with PHP 8.2.29.
**Failure**: All Laravel 13.x versions (13.0.0–13.8.0) require PHP ^8.3. Symfony 8.x (pulled in by L13) requires PHP >=8.4. `composer install` failed with incompatible lock file.
**Lesson**: Laravel 13 cannot run on PHP 8.2. Must either upgrade PHP to 8.3+ or downgrade to Laravel 12 (which supports PHP 8.2). We chose Laravel 12 (commit 80d3c3b).
**Do not repeat**: Do not attempt to install Laravel 13 on a PHP 8.2 server without upgrading PHP first.

---

## FA-03: Side-by-side PHP 8.1 and PHP 8.2 on AL2023

**Attempted**: Install `php8.2-*` packages alongside existing `php8.1-*` on AL2023.
**Failure**: `php8.2-common` conflicts with `php8.1-common` — both provide `php-common` virtual package; only one can be installed at a time on AL2023.
**Lesson**: AL2023 does not support side-by-side PHP version installs via native packages. Use `--allowerasing` to REPLACE the current PHP version.
**Do not repeat**: Do not attempt side-by-side PHP installation on AL2023 without `--allowerasing`.

---

## FA-04: Multi-line backslash commands in Lightsail browser SSH

**Attempted**: Pasting multi-line `dnf install \ php8.2 \ php8.2-fpm \` commands into browser SSH.
**Failure**: Browser SSH terminal interpreted each `\` + newline as a shell command separator. Each package name ran as a standalone command and failed with "command not found".
**Lesson**: Lightsail browser SSH does not handle multi-line backslash continuation. Always paste commands as a single line.
**Do not repeat**: Never use backslash line continuation in browser SSH. Concatenate onto one line.

---

## FA-05: Git checkout while files owned by nginx user

**Attempted**: `sudo git -C /var/www/creams checkout -B main origin/main`
**Failure**: Command appeared to succeed but HEAD remained on old `cdba49c` (Fixers, January) commit. The git fetch did work, but the checkout did not update the working tree.
**Root cause**: Likely a git safe.directory or ownership conflict between ec2-user running sudo and nginx-owned files.
**Lesson**: Use `git reset --hard origin/main` instead of `git checkout -B main origin/main` when updating an existing deployment directory with mixed ownership.
**Do not repeat**: Use `git reset --hard origin/main` for deployment updates, not `git checkout`.

---

## FA-06: Composer install with --no-dev before seeding

**Attempted**: Installed production deps only (`--no-dev`), then tried to seed with UATSeeder.
**Failure**: UATSeeder uses `Faker\Factory` from `fakerphp/faker` which is in `require-dev`. Seeder failed with "Class Faker\Factory not found".
**Lesson**: When seeding with Faker-dependent seeders on a production server, must install with dev deps first, seed, then re-run without dev.
**Pattern**: `composer install`, seed, `composer install --no-dev --optimize-autoloader`.
**Do not repeat**: Do not run UATSeeder immediately after `--no-dev` install.

---

## FA-07: Migrating against old database (creams) instead of new (creams_app)

**Attempted**: Ran `php artisan migrate --force` before the new .env was in place.
**Failure**: Migrations ran against the old `creams` database (from old .env). The new `creams_app` database had no tables, causing seeding to fail later.
**Lesson**: Always verify `php artisan about` shows the correct `DB_DATABASE` before running migrations.
**Do not repeat**: Confirm `about` shows correct DB before migrating.

---

## FA-08: MySQL TRIGGER creation without log_bin_trust_function_creators

**Attempted**: Migration `2025_09_29_140000_improve_activity_sessions_defaults` creates a MySQL trigger.
**Failure**: `SQLSTATE[HY000]: General error: 1419 You do not have the SUPER privilege and binary logging is enabled`.
**Fix**: `SET GLOBAL log_bin_trust_function_creators = 1;` in MySQL, then retry migration. Also added permanently to `/etc/my.cnf`.
**Do not repeat**: Always set `log_bin_trust_function_creators = 1` in `/etc/my.cnf` before running migrations that create triggers on MySQL servers with binary logging enabled.
