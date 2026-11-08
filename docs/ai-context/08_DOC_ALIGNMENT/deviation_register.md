## Current recovery register — 4 November 2026

Owner resumed the existing release. This table owns current cross-layer leads;
entries below retain their dated scope. State is static observation until a
specific synthetic runtime reproduction is recorded in
[the recovery evidence](../../audit/LOCAL_RECOVERY_2026-11-04.md).

### Central provisional decisions

Owner instruction on 4 November: retain all questions in one place and act on
reasonable industry-informed defaults, subject to later owner revision. This is
the single decision log; link it from implementation/configuration rather than
creating a second question backlog. Defaults are reversible product choices,
not institutional approval, clinical rules or regulatory certification.

| Decision | Provisional choice / rationale | Evidence / limitation | How to revise |
|---|---|---|---|
| P01 Current release | Stabilise existing C01-C18 capabilities; keep future roles and D02 expansions deferred. Preserve existing IEP; do not rebuild removed classes/outcomes scaffolds automatically | Owner's current handoffs/workflow; standalone scaffold overlap still needs trace | Owner changes release matrix scope explicitly |
| P02 Attendance unit | One trainee in one eligible, non-cancelled activity session is one opportunity. Compute a percentage against eligible opportunities, never trainee marks divided by number of sessions | [DfE published methodology](https://explore-education-statistics.service.gov.uk/methodology/pupil-absence-statistics-methodology) uses possible sessions as denominator. This is a useful education-sector analogy, not a Malaysian PPDK rule | Change the owning attendance policy/configuration and acceptance cases |
| P03 Attendance states | `present` and `late` count as attended for the generic CREAMS late flag; `absent` and `excused` do not. No automatic positive attendance. Keep unmarked opportunities explicitly unmarked and disclose recording coverage | Product default: current schema has no registration cutoff for late; external school coding cannot be transplanted directly. No allowance/eligibility consequence | Change the central status policy; preserve stored original marks |
| P04 Attendance period/history | Use due sessions in the selected period, enrolment start dates and available completion dates; exclude cancelled, deleted and out-of-centre objects. Current mutable enrolment state cannot prove complete historical membership; disclose that limitation | Actual schema has no complete membership history; never claim an exact historical expected roster from present-day state | Add a separately scoped membership-history contract before retrospective statutory use |
| P05 Role/centre permissions | Preserve current four-role server boundaries; admin cross-centre, other roles assigned-centre only, teacher writes only assigned sessions, AJK attendance writes denied. Tighten demonstrated bypasses without adding roles or broader privileges | Current role/middleware/source contract and owner handoff; consequential ambiguous privileges stay visible here | Owner revises the role matrix and its permitted/denied tests |
| P06 Existing time rules | Preserve current weekday and session-time validations while repairing schema references. Do not infer public-holiday calendars, clinic policy or compulsory attendance from these code checks | Existing model validations are observed behaviour, not proven stakeholder policy | Owner changes rule plus targeted tests; no unrelated time-rule rewrite |
| P07 Recovery | No automatic down() is promised for irreversible/unclear historical migration changes. Use synthetic upgrade/retry cases and documented forward repair/verified restore before release claims | Historical migration ownership lead; production schema and tested real recovery remain unknown | Separate authorized schema/recovery work order |
| P08 Session marking | Read and save only the current session's eligible roster; reject same-centre non-members, duplicate/invalid marks and cancelled sessions. Keep existing weekday restriction and recorded notes; do not imply unsupported scoring is saved | C08 read/write trace and synthetic regression. No new future-date/correction-window policy or clinical score storage is introduced | Revise roster/correction rules and paired browser/feature tests together |

Implementation status is tracked by the corresponding CR-I entry below. A chosen
default is not evidence that it has been implemented or verified. New material
questions/defaults belong in this same table; do not interrupt for routine choices.

| ID | Requirement / expected vs actual / trace | Confidence and impact | Owner / disposition / acceptance | State |
|---|---|---|---|---|
| CR-I01 | Centre recent activities: `/centres/{id}` -> `CentreController::show/getRecentActivities` -> query selected absent `activity_status` and concealed valid records | Reproduced with a real nonzero fixture; repaired with scoped Activity query/soft deletes | Commit `aa42757`; permitted/deleted/inactive/foreign/guest cases; 13 tests / 24 assertions in centre subset | VERIFIED selected local read; module-wide acceptance remains open |
| CR-I02 | Category navigation: working controller queried removed `activity_categories`, while `Activity.category` is canonical enum | Reproduced redirect, blank canonical fields and invalid affordance; reconciled only category methods against HEAD | Commit `0c540c7`; nonzero/empty/slug/admin/centre cases; 12 tests / 30 assertions in category/activity subset | VERIFIED selected local navigation; unrelated controller work preserved |
| CR-I03 | Session attendance reads: `centre.attendance.*` -> legacy helpers / computed collections as Eloquent relations | Nonempty dashboard reproduced missing category relationship; canonical summary now reads actual enrolment/marks | Dashboard policy subset 3 tests / 23 assertions; mark form, reports/exports and other paths still require reproduction | PARTIAL local work, not yet committed |
| CR-I04 | Historical requirements describe Trainee/Parent accounts and standalone classes/outcomes as functional; current owner keeps future roles deferred and code removed scaffolds | Confirmed source contradiction; risks rebuilding unapproved features | Owner retains consequential scope decisions; C10 existing IEP remains in release, no automatic deletion/restoration | Open scope boundary |
| CR-I05 | Attendance rate: old code could divide trainee marks by session count or infer attendance | Owner delegated defaults P02-P04; canonical summary tested at 2 attended / 4 eligible opportunities, 3 recorded, 1 unmarked | Config and summary are local in-progress work; original marks retained; no allowance/clinical interpretation | Provisional default locally verified for dashboard; broader paths pending |
| CR-I06 | Migration skipped-up/down ownership and asset remove/restore chain; fresh install success does not prove upgrade/recovery | Static lead, no ordinary/live ledger inspected | CR02 inspect actual chain; synthetic prior schemas and sentinels; no historical migration deletion or unsafe automatic rollback | Unverified |
| CR-I07 | Passing suite contains caught SQL failures and dormant service fallback tests | Dated October evidence; shape assertions can hide incorrect results | QA: positive/negative records and zero unexpected schema errors for accepted paths; do not wire dormant service into production | Open verification gap |
| CR-I08 | Historical public letter/avatar exposure and cached authenticated pages | Historical security leads only; current reachability unverified | Synthetic scoped reproduction before remediation; protect real file contents; preserve retention/business rules | Unverified |

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
