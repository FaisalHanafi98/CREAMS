# CREAMS — Deviation Register

**Last updated**: 2026-05-08
Deviations between documentation and current code. Classified before any action is taken.

---

## DEV-01: docs/CLAUDE.md says Laravel 10.x — code is Laravel 12.58.0

**Docs say**: Laravel 10.x (last updated 2026-02-07)
**Code shows**: `composer.json` `"laravel/framework": "^12.0"`, `php artisan --version` → 12.58.0
**Classification**: STALE DOC — upgrade path 10→11→12 was executed in sprint (commits c69e696 through 80d3c3b)
**Risk**: LOW — misleads new agents into thinking the app is on L10
**Decision**: UPDATE DOCS (low priority — don't distract from deployment)

---

## DEV-02: docs/CLAUDE.md says PHP 8.1+ — code requires PHP ^8.2

**Docs say**: PHP 8.1+
**Code shows**: `composer.json` `"php": "^8.2"`, platform set to 8.2.29
**Classification**: STALE DOC — PHP minimum was raised during Laravel upgrade
**Risk**: LOW — same as DEV-01
**Decision**: UPDATE DOCS (same batch)

---

## DEV-03: docs/CLAUDE.md says auth is "Laravel Breeze + Sanctum" — code uses custom auth

**Docs say**: Auth is "Laravel Breeze + Sanctum"
**Code shows**: `POST /auth/check` in `MainController`, custom session-based auth. Breeze is a dev dep used only for initial scaffolding.
**Classification**: STALE DOC — and CONFLICTING with `docs/SOURCE_OF_TRUTH.md` and ADR notes which correctly describe custom auth
**Risk**: MEDIUM — any agent trusting docs/CLAUDE.md would incorrectly assume standard Laravel auth exists
**Decision**: UPDATE DOCS (critical to fix to prevent future confusion)

---

## DEV-04: docs/CLAUDE.md says CSS is "Tailwind CSS" — code uses Bootstrap 5

**Docs say**: "CSS: Tailwind CSS 3.x"
**Code shows**: Tailwind is installed in `package.json` but zero Tailwind classes appear in any view. All styling uses Bootstrap 5 (self-hosted in `public/libs/`) + 50+ custom CSS files. `tailwind.config.js` exists but is unused.
**Classification**: DEVIATION — architectural drift. Tailwind was intended but Bootstrap 5 was used in practice.
**Risk**: MEDIUM — new agents might try to use Tailwind classes that have no effect
**Decision**: ACCEPT CURRENT CODE AND UPDATE DOCS — Bootstrap 5 is what's deployed and tested

---

## DEV-05: Roles in docs say "6-Role RBAC: Admin, Manager, Staff, Caretaker, Trainee, Parent"

**Docs say**: 6 roles: Admin, Manager, Staff, Caretaker, Trainee, Parent (some docs)
**Code shows**: 4 implemented roles: Admin, Supervisor, Teacher, AJK. Trainee and Parent planned but not implemented. ADR-002 correctly documents this.
**Classification**: CONFLICTING DOCS — some older docs use old role names
**Risk**: MEDIUM — old role names cause confusion
**Decision**: Trust ADR-002 (Admin/Supervisor/Teacher/AJK). Flag other docs as STALE.

---

## DEV-06: demo_demo_route() in login.blade.php — function does not exist

**Docs say**: N/A — not documented
**Code shows**: `resources/views/auth/login.blade.php` lines 424 and 473 call `demo_demo_route()`. This function does not exist. Correct helper is `demo_route()`.
**Classification**: POSSIBLE DEFECT — typo introduced during 2026-05-07 server deployment session
**Risk**: CRITICAL — causes 5 test failures; blocks deployment verification
**Decision**: FIX IMMEDIATELY (2 occurrences in login.blade.php)

---

## DEV-07: UATSeeder rewritten but not committed; DemoSampleUsersSeeder is untracked

**Docs say**: UATSeeder generates 7 trainees/3 activities per centre (sprint baseline)
**Code shows**: Local `UATSeeder.php` is completely rewritten (60 trainees, 18 activities, Malaysian locale). `DemoSampleUsersSeeder.php` is untracked.
**Classification**: CODE AHEAD OF DOC — significant scope change in seeder, undocumented and uncommitted
**Risk**: HIGH — deployment fails without committing DemoSampleUsersSeeder; tests may fail after commit if new seeder has bugs
**Decision**: REVIEW both files, commit together, update docs/ai-context/04_DATABASE_STATE/seeders_status.md

---

## DEV-08: app.blade.php has new demo URL prefix JS — not in design docs

**Docs say**: N/A — layout changes not documented in design docs
**Code shows**: `resources/views/layouts/app.blade.php` has a new `<script>` block injecting demo URL prefix logic (intercepts window.fetch and XHR to prefix URLs with demo base).
**Classification**: CODE AHEAD OF DOC — new feature added during deployment prep
**Risk**: MEDIUM — may cause unexpected behavior in non-demo routes; needs review
**Decision**: REVIEW AND DOCUMENT — check if prefix logic interferes with direct-access routes in local/testing

---

## DEV-09: CREAMS_CODEBASE_DOCUMENTATION.md (63K lines) — pre-upgrade

**Docs say**: Full codebase documentation (accurate as of ~late 2025)
**Code shows**: Significant changes since this doc was written (L10→L12 upgrade, CSP fixes, Bootstrap 5 migration, demo_route helper, UATSeeder rewrite)
**Classification**: STALE DOC — too large to update manually
**Risk**: LOW — referenced as historical context, not as operational truth
**Decision**: ACCEPT AS HISTORICAL — do not try to update; rely on docs/ai-context/ as current truth

---

## DEV-10: LaravelBoost listed as upgrade path — never installed

**Docs say** (Stage 1 audit): "Laravel Boost ^2.0 provides /upgrade-laravel-v13 flow for L12→L13"
**Code shows**: Boost is not in composer.json or composer.lock. L13 was attempted but rolled back to L12 (PHP version incompatibility on server).
**Classification**: SUPERSEDED — the L13 upgrade path is on hold pending PHP 8.3 on server
**Risk**: LOW — informational note only
**Decision**: DOCUMENT THE CONSTRAINT — when server has PHP 8.3, revisit L13 via Boost or manual upgrade
