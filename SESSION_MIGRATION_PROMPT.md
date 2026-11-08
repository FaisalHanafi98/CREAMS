# CREAMS workstation migration prompt

**Handoff date:** 8 November 2026. **Scope:** current-release recovery on
`Fixers`, with synthetic local verification and case-by-case browser coverage.

Use this as the first read when opening CREAMS on a new workstation. A single
request is enough to start discovery:

> Research and discover this CREAMS folder. Read `SESSION_MIGRATION_PROMPT.md`
> first, follow its discovery checklist, and give me the resume report.

## Discovery checklist

1. Locate the repository root with `git rev-parse --show-toplevel`. Read its
   [AGENTS.md](AGENTS.md) and [Codex session init](docs/CODEX_INIT_PROMPT.md).
   When the Development workspace is present, use its `standards/README.md`
   and the nearest project instructions. A standalone clone can still
   reconstruct the project state from the repository documents below.
2. Run `git status --short --branch`, `git log -12 --oneline`,
   `git worktree list` and `git diff --stat`. Check the staged diff separately.
   Identify the exact branch and upstream before drawing conclusions about
   publication or local edits. GitHub has both `Fixers` and `fixers`. This
   recovery uses the capitalised `origin/Fixers` ref. For a live comparison,
   use `git ls-remote --heads origin refs/heads/Fixers`.
3. Read the [project source of truth](docs/ai-context/00_PROJECT_SOURCE_OF_TRUTH.md),
   [current status](docs/ai-context/01_CURRENT_STATUS.md), newest
   [session history](docs/ai-context/02_SESSION_HISTORY/) and any newer
   `.memsearch/memory/` checkpoint available in the transferred workspace.
   Read the [current release matrix](docs/MODULE_FUNCTIONALITY_INVENTORY.md#current-release-acceptance-matrix--4-november-2026)
   and the [central decision and deviation register](docs/ai-context/08_DOC_ALIGNMENT/deviation_register.md).
   Use those sources to scope the work. Verify claims against live Git state,
   current code and dated test evidence.
4. Check local capability by inspecting tool versions and paths. Match PHP,
   MySQL, Composer and Chromium to the project and installed dependencies
   before starting the app. Treat an ordinary `.env`, a running localhost
   service or saved browser cookies as outside the recovery fixture. Discovery
   is read-only. Run Artisan database commands only after identifying an owned
   synthetic target and receiving the project-required continuation.
5. Give one resume report in the format from the Codex init guide: date of last
   session, what was worked on, status, open issues, next best action and what
   to avoid repeating. State which evidence came from GitHub and which
   workstation-only files were available. Use that single report for the
   project’s confirmation step. Once the owner confirms implementation, carry
   on with the next verified task and record provisional choices centrally.

## What reached GitHub

The recovery checkpoint `0c649dc36718eb9c0dff3bf1f2a87d08d973956a` was published
as a normal fast-forward to `origin/Fixers`. Local and remote tips matched
after the push, and the local branches had zero commits outside the verified
GitHub branch tips. Use the live branch for any newer changes. The published
series excludes an unpublished edit to the legacy real-data seeder. The
original pre-publication revisions were retained in the old workstation's
local reflog. [Recovery checkpoint](https://github.com/FaisalHanafi98/CREAMS/commit/0c649dc36718eb9c0dff3bf1f2a87d08d973956a)

| Area | Work published before this handoff |
|---|---|
| Centre and activities | Recent centre records and category browse use canonical records. Browser cases checked centre access and a populated, empty and invalid category. |
| Attendance dashboard and marking | Read from `activity_occurrences` and `trainee_attendances`. Save only the eligible session roster. The browser checked a mark and note surviving reload, plus denied and invalid submissions. |
| Attendance reports | Admin and Supervisor can view recorded marks and download scoped CSV or PDF. The legacy `excel` option returns CSV. Centre and date constraints and export fields live in decision P09. |
| Dashboard API | Replaced stale model and column references. Synthetic Admin, Supervisor, Teacher and AJK sessions reached their dashboards and returned the expected nonzero counts. |
| Clean checkout prerequisites | Two existing local migration prerequisites were reviewed, tested on a fresh schema and committed. PHP 8.3.30 and 8.5.7 loaded the MySQL CA setting in a config probe. The hook was tested with safe environment lookups and blocked synthetic literals. |
| Recovery evidence | The release acceptance matrix, decisions, named browser cases and dated results are in Git. Use the [8 November evidence record](docs/ai-context/02_SESSION_HISTORY/2026-11-08_browser_verification.md). |

The clean candidate completed a fresh MySQL 8.4.3 migration, **413 PHPUnit tests
with 756 assertions**, and **17 Chromium cases**. The route ledger has **16 of
301 direct entries asserted** and **285 still to exercise**. It tracks 294 demo
registrations and six deferred-role registrations separately. The earlier
430-test result included additional working-tree tests on the old workstation.
Keep these numbers tied to their dated runs. Production behaviour, original
stakeholder UAT and populated-database migration or recovery remain open.

GitHub Actions reached its Composer dependency audit and failed there with
58 advisories affecting 13 packages and one abandoned package. The subsequent
hosted lint and PHPUnit steps were skipped in that run. Keep this separate
from the passing local suite. Dependency advisory work uses the approved local
process. Package changes and production access have their own authorisation.
[GitHub Actions run](https://github.com/FaisalHanafi98/CREAMS/actions/runs/42448400617)

## Work that may need a private transfer

A fresh clone contains commits only. The old working copy held unrelated
modifications, deletions and untracked work before this recovery began. Keep a
private copy of the existing tree or review a scoped source patch if you need
to retain it. Compare its status and hashes before applying anything to a fresh
checkout. Transfer only the approved source, scripts and synthetic evidence.
Keep environment files, credentials, logs containing sensitive data, saved
browser sessions and real data out of the migration package and Git.

| Old workstation location | What it contains |
|---|---|
| CREAMS `.memsearch/memory/2026-11-08.md` | Project-local checkpoint and commands. This path is Git-ignored. |
| Development `workspace/implementation-2026-11-04-creams-recovery/scripts/` | Owned MySQL and HTTP runners, browser fixture and router, and preservation checker. This directory is outside the CREAMS repository. |
| Same task directory, `private/runs/publish-release-chromium/` | Clean-candidate test output and route ledger. Use the dated evidence in Git if these private files were not transferred. |
| Same task directory, `private/before-state.json` and `after-state.json` | Source-preservation hashes from the old workstation. All 705 originally inventoried files were present afterward, with zero bootstrap cache or application log changes. |
| CREAMS trainee test and browser driver edits | A draft regression and browser case for C03 exist only in the working tree. The fixture change lives in the external scripts directory. The regression reproduced the bug. |

If the scripts are unavailable, reconstruct a small owned runner from the
[browser recovery guide](tests/Browser/recovery/README.md) and the isolation
requirements below. Preserve the project workflow even if the old drive layout,
local packages or private logs are unavailable.

## Resume at C03 trainee registration

Start with **CR-I09** in the central register. The next repair concerns the
highest allocated trainee ID after soft deletion. On the owned MySQL fixture,
keep a synthetic trainee with suffix `0006`, soft-delete another with suffix
`0007`, then submit the real registration request for that year and centre.
The old code chose `0007` again, hit the unique constraint, and returned to the
form. The dated red run is `red-trainee-registration-collision`.

- Trace the allocator in [Trainee.php](app/Models/Trainee.php) and the second
  allocation in [TraineeRegistrationController.php](app/Http/Controllers/Trainee/TraineeRegistrationController.php).
  Bring the registration and model paths through a single allocation rule.
  Account for soft-deleted IDs and centre scope while preserving existing
  identifiers, explicit IDs and registration permissions.
- Use a failing regression before editing. Assert the registration persists
  `0008`, the deleted row still owns `0007`, and the next model-created
  trainee gets `0009`. The draft file was
  `tests/Feature/Trainee/TraineeRegistrationIdentifierTest.php`.
- In Chromium, sign in with the synthetic Admin account, submit the actual
  `/trainees/create` form and verify the new identifier after reloading the
  trainee list. The draft case ID is `C03-registration-after-delete`.
  Then cover the remaining C03 role, centre, invalid input, edit, deletion
  and state journeys as separate assertions.
- Continue through C01–C18 in the release matrix. Expand the case-to-route
  ledger with actual observed actions, permission denials, persistence and
  empty or error states. Review the relevant current code and routes before
  probing an endpoint. Some GET routes perform actions.

## Re-establish safe local verification

- CREAMS declares Laravel 12 and PHP `^8.2`. Check the current
  [Composer manifest](composer.json) and lock file before choosing a runtime.
  Authentication uses the project’s custom session flow through
  `POST /auth/check`. The current roles are Admin, Supervisor, Teacher and
  AJK. The release matrix separates deferred roles and future module work.
- Set up an isolated loopback MySQL instance, storage and cache directories,
  and an app listener using synthetic records. Verify executable, bound port,
  data directory and schema ownership before creating or resetting the
  fixture. The old Windows runner used MySQL on `127.0.0.1:13308`, schema
  `cream_test`, and a PHP listener on `127.0.0.1:18010`. Keep network requests,
  mail, workers, uploads, sessions and browser storage in the fixture
  boundary. Rebuild the owned schema after a run that created HTTP fixtures.
- Use the isolated runner with a unique evidence label. Run the targeted
  failing test first, then the repaired PHP and Chromium paths and a wider
  pass on the clean commit candidate. Use the
  [browser driver and ledger](tests/Browser/recovery/) rather than the
  legacy Playwright setup that can reuse another server or seed its database.
  Check the browser’s real form, response, role and persisted result.
- Use the shared Development commit SOP and Humanizer when available. Stage
  specific files or hunks and run the active pre-commit hook. Keep commits
  atomic. Check the current authorisation and exact upstream before publishing.
  Deployment and stakeholder UAT are separate operations.
