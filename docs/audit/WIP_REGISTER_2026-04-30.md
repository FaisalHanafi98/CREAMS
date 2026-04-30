# CREAMS — WIP Register
**Date**: 30 April 2026
**Sprint**: 5-day UAT staging sprint
**Branch**: Fixers
**Author**: Faisal Hanafi

Every dirty/untracked item in `git status` as of sprint Day 1 is classified here.
No item is left unexplained. Classifications: COMMITTED | IGNORED | ARCHIVED | DEFERRED_WITH_REASON | BLOCKER.

---

## Modified tracked files

| File | Classification | Rationale |
|---|---|---|
| `.claude/settings.local.json` | COMMITTED (commit-1) | Accumulated Bash permission allowlist from prior sessions. Intentional governance. |
| `.gitignore` | COMMITTED (commit-1) | Added `.memsearch/` to exclude session checkpoints. Intentional. |
| `CLAUDE.md` | COMMITTED (commit-1) | Added Memory Protocol section (memsearch architecture, PDPA constraints). Intentional. |
| `Claude related/.claude/settings.local.json` | COMMITTED (commit-1) | Deleted — old stale Claude config folder, superseded. |
| `Claude related/FIRST_PROMPT_TEMPLATE.md` | COMMITTED (commit-1) | Deleted — same folder, superseded. |

---

## Untracked — App code

| File | Classification | Rationale |
|---|---|---|
| `resources/views/components/application-logo.blade.php` | COMMITTED (commit-2) | Real Blade component, renders the CREAMS logo. Needed by the UI. |

---

## Untracked — Test files

| File | Classification | Rationale |
|---|---|---|
| `tests/Feature/Auth/RateLimitTest.php` | COMMITTED (commit-3) | Rate limit test added during security hardening. Active, should be tracked. |
| `tests/Feature/CentreIsolation/CentreIsolationTest.php` | COMMITTED (commit-3) | Centre isolation tests. Core PDPA boundary test — must be tracked. |
| `tests/Feature/ExampleTest.php` | COMMITTED (commit-3) | Default Laravel smoke test (GET / returns 200). Harmless, keeps as sanity check. |
| `tests/Feature/RBAC/RoleAccessTest.php` | COMMITTED (commit-3) | RBAC role access tests. Active security test. |
| `tests/Unit/Models/SoftDeleteTest.php` | COMMITTED (commit-3) | Soft delete model tests. Active. |
| `tests/Unit/Scopes/CentreScopeTest.php` | COMMITTED (commit-3) | CentreScope unit tests. Directly tests the primary PDPA isolation mechanism. |

---

## Untracked — Scripts and hooks

| File | Classification | Rationale |
|---|---|---|
| `scripts/pre_compaction_checkpoint.sh` | COMMITTED (commit-4) | Memsearch PreCompact hook — fires before /compact to save session state. |
| `scripts/prompt_counter_checkpoint.sh` | COMMITTED (commit-4) | Memsearch prompt-counter hook — fires every 10 prompts. |
| `scripts/save_checkpoint.py` | COMMITTED (commit-4) | Memsearch checkpoint helper script. |
| `scripts/session_end_checkpoint.sh` | COMMITTED (commit-4) | Memsearch SessionEnd hook — fires on session close. |
| `.githooks/` | COMMITTED (commit-4) | Project git hooks (pre-commit, commit-msg). Governance. |
| `.claude/commands/` | COMMITTED (commit-4) | Custom Claude Code slash commands for CREAMS workflows. |

---

## Untracked — Documentation (CREAMS-specific)

| File/Folder | Classification | Rationale |
|---|---|---|
| `docs/01_System_Overview/` | COMMITTED (commit-5) | Reorganised system docs. All CREAMS content. |
| `docs/02_Module_Documentation/` | COMMITTED (commit-5) | Module summaries. CREAMS content. |
| `docs/03_Technical_Guides/` | COMMITTED (commit-5) | Technical reference. CREAMS content. |
| `docs/04_Deployment_Guides/` | COMMITTED (commit-5) | Deployment docs — needed for Day 4. |
| `docs/06_Status_Reports/` | COMMITTED (commit-5) | Status history. CREAMS content. |
| `docs/07_Fixes_and_Audits/` | COMMITTED (commit-5) | Fix logs and audit reports. CREAMS content. |
| `docs/08_Development_Planning/` | COMMITTED (commit-5) | Planning docs. CREAMS content. |
| `docs/09_New_Features/` | COMMITTED (commit-5) | Feature specs. CREAMS content. |
| `docs/10_User_Manuals/` | COMMITTED (commit-5) | User-facing docs. CREAMS content. |
| `docs/CLAUDE.md` | COMMITTED (commit-5) | Stale project CLAUDE.md kept for historical reference per overlay. |
| `docs/CONSOLIDATED_DOCUMENTATION_INDEX.md` | COMMITTED (commit-5) | Docs index. |
| `docs/CREAMS_SESSION_2026-04-16.md` | COMMITTED (commit-5) | Historical session doc — lineage record. |
| `docs/DELTA_REEVAL_REPORT_2026-03-22.md` | COMMITTED (commit-5) | Historical delta re-eval report. |
| `docs/PRODUCTION_ROLLBACK.md` | COMMITTED (commit-5) | Needed for Day 4 rollback rehearsal. |
| `docs/README_DOCUMENTATION_ORGANIZATION.md` | COMMITTED (commit-5) | Docs structure guide. |
| `docs/UAT FILES/` | COMMITTED (commit-5) | UAT documents — actively used this sprint. |
| `docs/routes_inventory.txt` | COMMITTED (commit-5) | Routes audit artifact from prior session. |
| `docs/routes_sample.txt` | COMMITTED (commit-5) | Routes sample from prior session. |
| `docs/creams database.txt` | COMMITTED (commit-5) | Aug 2025 schema documentation, 29-table reference. Useful for Day 2 audit. |

---

## Untracked — Deferred with reason

| File | Classification | Rationale |
|---|---|---|
| `docs/ACTIVITY MODULE IMPROVEMENT.txt` | DEFERRED_WITH_REASON | Historical planning note with a "24-hour deadline" fix context from prior work. Not sprint-blocking. Will commit in a separate docs cleanup session. |

---

## Untracked — Ignored (add to .gitignore)

Generated output, Playwright artefacts, personal notes, and archive folders that must not enter the repo.

| File/Folder | Reason |
|---|---|
| `archive/` | Old code archive — not app code. Gitignore whole folder. |
| `archive/settings.local.json` | Stale config inside archive. |
| `docs/migration_test_output.log` | Generated log. |
| `docs/phpstan_output.txt` | Generated PHPStan output. |
| `docs/JAVA Bootcamp Day 2.txt` | Personal learning notes — unrelated to CREAMS. |
| `docs/Leetcode Exercises.txt` | Personal learning notes — unrelated to CREAMS. |
| `docs/Recruitment Automation.txt` | Notes for a different project. |
| `docs/Shorts Tips Template.txt` | Personal notes — unrelated. |
| `docs/Tips and tricks from shorts.txt` | Personal notes — unrelated. |
| `cream issue 1.jpg` | Bug screenshot — generated artefact. |
| `cream login ss 1.png` | Login screenshot — generated artefact. |
| `template for welcome page 1.png` | UI mockup — not source code. |
| `template for welcome page 2.png` | UI mockup — not source code. |
| `MOVE_OLD_CREAM_FOLDERS.bat` | One-time Windows utility script — already used. |
| `routes_export.json` | Generated/corrupted artisan output. |
| `tests/BROWSER_TEST_REPORT_2026-02-06.html` | Generated HTML report. |
| `tests/Browser/.auth/` | Playwright auth session state. |
| `tests/Browser/dashboard-test-results.txt` | Generated. |
| `tests/Browser/node_modules/` | Playwright node_modules — not covered by root gitignore. |
| `tests/Browser/nul` | Windows null device artefact. |
| `tests/Browser/playwright-report/` | Generated Playwright report. |
| `tests/Browser/test-results.json/` | Generated. |
| `tests/Browser/test-results/` | Generated. |
| `tests/FUNCTIONAL_TEST_REPORT_2026-02-06.html` | Generated. |
| `tests/TEST_REPORT_2026-02-05.html` | Generated. |
| `tests/test-results/` | Generated. |

---

## Previously resolved (earlier this session)

| File | Classification | Rationale |
|---|---|---|
| `app/Models/ActivityCategory.php` | ARCHIVED (wip/abandoned-activity-category-2026-04-30) | Orphaned — table dropped by migration 2025-09-28. Preserved on wip branch. |
| `database/migrations/2026_03_14_214141_create_trainee_audit_logs_table.php` | COMMITTED | Active code in TraineeService/Trainee/TraineeAuditLog references it. Was a silent P0 missing table. |

---

## Known tech debt flagged (out of sprint scope)

| Item | Note |
|---|---|
| `app/Models/Category.php` | Tracked model pointing at dropped `activity_categories` table. Same bug as ActivityCategory. Defer to post-UAT cleanup. |
| `ActivityController.php` line 917 | Dead fallback code that tries `activity_categories` table "just in case". Harmless but confusing. Defer. |
