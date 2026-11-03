# CREAMS local recovery — 4 November 2026

Status: CR01 baseline recorded; two CR03 slices committed locally; attendance work in progress.
Owner explicitly resumed CREAMS with “Continue dont stop until told so” after the
required reconstruction/confirmation exchange. This authorizes continued existing
release work; production, ordinary databases, future modules, dependency installation,
stakeholder contact, push and deployment remain outside scope.

## Baseline and acceptance

Checkout: `projects/CREAMS`, `Fixers`, HEAD
`489d87b7c86cbf0148037b6eae8f637418efb3de`; cached tracking branch is five commits
behind. One registered worktree. Before this batch: 71 modified tracked entries,
31 tracked deletions, 76 collapsed untracked entries, nothing staged. These are
pre-existing changes, not a deletion list. Preserve all of them.

PHP 8.5.7 and local MySQL binary 8.4.3 were checked without application startup.
`composer.lock` records Laravel 12.58.0 and PHPUnit 11.5.55. There are 37 active
migrations / 131,034 bytes, no byte-identical duplicates. Two uncommitted forward
repair migrations remain intentional; no historical migration is to be simplified.
January SQL and the ignored record JSON exist, but neither is a test fixture or
proof of current production schema/restore safety. No raw records are read here.

The [release matrix](../MODULE_FUNCTIONALITY_INVENTORY.md#current-release-acceptance-matrix--4-november-2026)
owns the finite capability inventory. The existing
[deviation register](../ai-context/08_DOC_ALIGNMENT/deviation_register.md) owns
cross-layer inconsistencies and business decisions. Current owner scope overrides
historical claims that every scaffold/parent portal is delivered.

Requirements-source inventory: owner handoffs and November workflow; August 2025
`08_Development_Planning/modules_and_requirements.txt`; `USER_STORIES.md`;
eight May manuals; module summaries; UAT workbook/CSVs; May handover; ADRs; dated
test evidence. The two UAT workbook copies and two requirements-text copies have
matching hashes; root and `docs/` module inventories differ. No duplicate is removed.
These records describe intent and history; they do not establish signed stakeholder
acceptance. Clinical/allowance rules remain undecided; no inferred entitlement.

## Selected technical slice and safe resources

CR03 first slice: reproduce existing centre recent-activity, category navigation and
attendance-read failures. Preserve existing permissions and accepted domain rules;
fix obsolete schema references using canonical records. No schema addition, new
role, metric redefinition or standalone learning-outcome restoration in this slice.
Acceptance: nonzero permitted HTTP results from actual fixtures; denied role/centre
cases; correct deletion/state filtering; no caught SQL fallback treated as success.
Work test-first, one demonstrated failure at a time; review each incremental diff.

Local verification target: new fixture-owned MySQL data directory under
`workspace/implementation-2026-11-04-creams-recovery/private/mysql-baseline/data`,
loopback port 13308, synthetic `cream_test` schema only. A second synthetic sentinel
schema must survive test resets. Before any schema operation verify server process
identity and `@@datadir`; reject occupied ports and pre-existing unowned directories.
No connection to port 3306 or an ordinary app/production database is allowed.
HTTP port 18010 is reserved for later fixture-owned synthetic journeys.

All storage, named log channels, framework/package/config/route caches and compiled
views go into this task's private directory. Mail uses the array transport; no
scheduler/worker is started. Disable network-capable PHP transports and URL fopen
in verification processes; breach checks/external services are not live evidence.
Inspect providers, configured test classes, fixtures and runner before startup.
Local platform/dependency checks do not send inventories to advisory services.

## Preservation, verification and remaining work

Before-state SHA-256 inventory: 705 source/config/test/view/custom-asset/cache/log
paths. Original tracked diff and exact working copies of expected edit targets are
in task-local `private/`. Hashes/metadata and synthetic runtime logs remain local;
these are scoped evidence, not a complete repository/database backup.

Fresh isolated baseline `initial-full-isolated`: 409 tests / 721 assertions,
zero failures/errors; all fresh migrations, local platform checks and 601-route
enumeration passed. The synthetic sentinel survived. Caught SQL errors still
included `activity_categories` (1), `session_enrollments` (3), `activity_status`
(3), `centres.id` (15), `is_active` (6) and `teacher_id` (3). These counts are log
occurrences, not distinct defects; a green suite is not release acceptance.

| Slice | Reproduction and verification | Local commit / limit |
|---|---|---|
| Centre recent activities | `red-centre-recent` failed the nonzero record assertion and logged absent `activity_status`; `green-centre-suite-final` passed 13 tests / 24 assertions, including deleted/inactive/foreign records and guest/teacher denials | `aa42757`; only centre controller and new regression class committed |
| Category browse/detail/empty | `red-category-index` reproduced removed-table redirect; `red-category-fields` exposed missing canonical description/duration; `red-category-empty-action` exposed supervisor creation affordance. `green-category-suite` passed 12 tests / 30 assertions | `0c540c7`; only category methods, owning Blade and tests committed; unrelated controller work remains unstaged |
| Attendance dashboard | `red-attendance-valid-fixture` reproduced missing Activity.category relation; canonical summary and dashboard pass 3 tests / 23 assertions in `attendance-dashboard-policy-valid`, including unmarked opportunities, cancelled/future sessions, invalid date and foreign-centre denial | Local work in progress, not yet committed; mark/report/export journeys remain open |

The first harness startup hit sandbox loopback error 10013 before migration/tests;
the reviewed command then ran with execution permission. A second attempt exposed
unsupported Windows absolute package-cache paths. The harness now uses relative
paths into its own task directory and requires valid isolation JSON. Neither
attempt is an application regression. Initial new test fixtures also used an
absent trainee-name field and omitted required session names; corrected fixtures
are not evidence of application defects. A guest-redirect expectation was aligned
with the actual `/auth/login` guard contract. Preserve the failed-run evidence.

Owner delegated reversible defaults and central question tracking to the existing
decision register. It distinguishes the cited education-sector denominator analogy
from a Malaysian PPDK requirement; no statutory/allowance/clinical rule is inferred.
No independent human/security certification is claimed. Self-review covers the
selected changes; browser accessibility and original stakeholder UAT remain open.

Next: finish the attendance read/mark/report slices with meaningful tests and atomic
local commits, then run the fresh broader gate. Retire only this task's owned processes
and disposable data/cache resources after identity checks. Preserve prior evidence,
all original working files and project logs. Deployment and original UAT stay open.
