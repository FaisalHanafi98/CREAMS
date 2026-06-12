# CREAMS — Execution Batch 05: Technical Debt Cleanup

> **Wave**: 5
> **Priority**: P2 — MEDIUM/LOW (non-blocking cleanup, documentation, and hygiene)
> **Source**: `CRITICAL_FINDINGS_REGISTER.md` (CF-13 through CF-20), `CURRENT_CODE_QUALITY_STATE.md`, `CURRENT_ARCHITECTURE_STATE.md`, `REFACTORING_INVENTORY.md`, `PRIORITY_FIX_QUEUE.md` P2 items
> **Risk Level**: LOW — No code changes required for most tasks. Documentation, CI config, and cleanup only.
> **Estimated Sessions**: 2-3
> **Dependencies**: Waves 1-4 complete. Can run independently if desired.
> **Precondition**: 359 PHPUnit tests passing. 0 P0 blockers remaining.

---

## Task B5-T1: Backfill empty memory checkpoint stubs

| Field | Value |
|-------|-------|
| **Task ID** | B5-T1 |
| **Priority** | P2 |
| **Source Evidence** | `CRITICAL_FINDINGS_REGISTER.md` CF-13 |
| **Finding Reference** | CF-13 — 6 memory checkpoint files are empty stubs (May 3, 10, 14, 15, 18, 19) |
| **Problem Statement** | 6 of 13 `.memsearch/memory/` files contain only empty template stubs or 3-line headers. The auto-checkpointer fired but the AI agent never filled in context. Future sessions using the resume protocol will hit gaps on these dates, potentially missing important state. |
| **Affected Files** | `.memsearch/memory/2026-05-03.md`, `2026-05-10.md`, `2026-05-14.md`, `2026-05-15.md`, `2026-05-18.md`, `2026-05-19.md` |
| **Affected Components** | AI session continuity, resume protocol |
| **Dependencies** | None |
| **Execution Preconditions** | [ ] Git log accessible for date ranges. [ ] `docs/ai-context/` files accessible for cross-reference. |
| **Verification Steps** | 1. For each empty stub date: run `git log --since="YYYY-MM-DD" --until="YYYY-MM-DD+1" --oneline` to check if any commits occurred on that date. 2. If commits exist: cross-reference with `docs/ai-context/` and `docs/audit/` for that date. Backfill a summary from commit messages. 3. If no commits exist (idle day): mark the file with `## NO ACTIVITY — idle day`. 4. If uncertain: mark as `## ACTIVITY UNKNOWN — git log shows commits but context lost`. 5. Do NOT fabricate content. If evidence is insufficient, state that. |
| **Rollback Strategy** | Memory files are append-only. No rollback needed for adding content. |
| **Completion Criteria** | [ ] All 6 files contain either: evidence-based summary, "NO ACTIVITY" marker, or "ACTIVITY UNKNOWN" marker. [ ] No empty template stubs remain. [ ] Resume protocol will not hit blank gaps. |
| **Estimated Effort** | 20-30 minutes |
| **Risk of Change** | NONE — memory files are informational. Gitignored. |
| **Notes** | May 18-19 are the most critical gaps — these overlap with the live UAT runs that showed FAIL conditions. Cross-reference with `full_browser_uat_report_2026-05-18.md` and `full_browser_uat_retest_2026-05-18_092233Z.md`. |

---

## Task B5-T2: Clean root-level temp files

| Field | Value |
|-------|-------|
| **Task ID** | B5-T2 |
| **Priority** | P2 |
| **Source Evidence** | `CRITICAL_FINDINGS_REGISTER.md` CF-14 |
| **Finding Reference** | CF-14 — Root-level temp files from route auditing never cleaned |
| **Problem Statement** | `tmp_routes_audit.json` (valid 629-route inventory), `tmp_creams_routes.json` (export variant), `routes_export.json` (junk — Laravel error message), `routes_analysis.json` (analysis output) are temp files at the repository root. They were generated during route auditing and never cleaned. |
| **Affected Files** | `tmp_routes_audit.json`, `tmp_creams_routes.json`, `routes_export.json`, `routes_analysis.json` |
| **Affected Components** | Repository root cleanliness |
| **Dependencies** | None |
| **Execution Preconditions** | [ ] Confirm files still exist at repo root. |
| **Verification Steps** | 1. Archive valid files: `mv tmp_routes_audit.json docs/audit/` and `mv routes_analysis.json docs/audit/`. 2. Delete junk: `rm routes_export.json` (contains only a Laravel error — no value). 3. Evaluate `tmp_creams_routes.json` — if duplicate of tmp_routes_audit.json, delete. If has unique data, archive. 4. Run `git status` — repo root should be clean of these files. |
| **Rollback Strategy** | Archived files can be restored from `docs/audit/`. Deleted junk is valueless — no rollback needed. |
| **Completion Criteria** | [ ] 0 temp JSON files at repo root. [ ] Valid route data archived to `docs/audit/`. [ ] Junk file deleted. |
| **Estimated Effort** | 5 minutes |
| **Risk of Change** | NONE — temp files, not application code |
| **Notes** | These files were generated during the WIP_REGISTER_2026-04-30.md sprint audit. The authoritative route inventory is already at `docs/audit/routes_2026-04-30.json`. |

---

## Task B5-T3: Document or resolve CI test environment divergence

| Field | Value |
|-------|-------|
| **Task ID** | B5-T3 |
| **Priority** | P2 |
| **Source Evidence** | `CRITICAL_FINDINGS_REGISTER.md` CF-15, `CURRENT_CODE_QUALITY_STATE.md` |
| **Finding Reference** | CF-15 — PHPUnit CI uses SQLite :memory: while local uses MySQL `cream_test` |
| **Problem Statement** | CI runs tests against SQLite (via `phpunit-ci.xml`), while local development runs against MySQL (`phpunit.xml`). This divergence means edge cases in JSON columns, fulltext indexes, or MySQL-specific features may pass in CI but fail locally (or vice versa). `DELTA_REEVAL` classified this as "acceptable but requires documentation." |
| **Affected Files** | `phpunit-ci.xml`, `.github/workflows/ci.yml` |
| **Affected Components** | CI pipeline, test reliability |
| **Dependencies** | None |
| **Execution Preconditions** | [ ] Understand the divergence cost: adding MySQL to CI increases CI run time. |
| **Verification Steps** | **Option A (Document — recommended):** 1. Create `docs/03_Deployment_Guides/CI_TEST_DIVERGENCE.md`. 2. Document: (a) what differs between CI and local, (b) what features are at risk (JSON columns in `required_resources`, `activity_attributes`, `centre_facilities`; fulltext search; spatial types — none currently used), (c) how to test MySQL-specific features locally before merging. 3. Tag existing tests in `tests/Feature/` that use JSON columns with a `@group mysql` annotation for selective local runs. **Option B (Resolve):** 1. Add a MySQL service container to `.github/workflows/ci.yml`. 2. Duplicate the test step: one run against SQLite (fast, catches most issues), one run against MySQL (catches edge cases). 3. Expected CI time increase: ~2x. |
| **Rollback Strategy** | Documentation change only (Option A). If CI change (Option B) causes timeout, revert to SQLite-only. |
| **Completion Criteria** | [ ] Divergence documented with known risk categories. OR [ ] MySQL service added to CI. [ ] Tests tagged for MySQL-specific behavior. |
| **Estimated Effort** | Option A: 15 minutes. Option B: 30-60 minutes. |
| **Risk of Change** | LOW (Option A — documentation). MEDIUM (Option B — CI config change may affect deploy pipeline). |
| **Notes** | `DELTA_REEVAL` (Mar 2026) noted this as "YES but acceptable — standard Laravel practice." The risk is theoretical — no JSON-specific tests have been observed to fail due to SQLite vs MySQL divergence. Documenting is likely sufficient. |

---

## Task B5-T4: Add eager loading to ActivityController::schedule()

| Field | Value |
|-------|-------|
| **Task ID** | B5-T4 |
| **Priority** | P2 |
| **Source Evidence** | `CRITICAL_FINDINGS_REGISTER.md` CF-16, `PERFORMANCE_BASELINE_METHODOLOGY.md`, `MASTER_PROGRESS_LOG.md` |
| **Finding Reference** | CF-16 — 221 N+1 queries on activity schedule page (19.5s load time) |
| **Problem Statement** | `ActivityController::schedule()` generates 221 N+1 queries, causing a 19.5s page load. Trainee creation takes 26s due to synchronous email notifications and N+1 enrollment queries. Both are Phase 3 deferred items. |
| **Affected Files** | `app/Http/Controllers/ActivityController.php` (schedule method), `app/Http/Controllers/TraineeRegistrationController.php` (store method) |
| **Affected Components** | Schedule page, trainee creation flow |
| **Dependencies** | B3-T2 (trainee controller changes) — coordinate to avoid merge conflicts. |
| **Execution Preconditions** | [ ] Laravel Debugbar installed (or another query profiler). [ ] Local environment with seeded data (UATSeeder). [ ] 359 PHPUnit tests passing. |
| **Verification Steps** | 1. Install/enable Laravel Debugbar for query profiling. 2. Load `/activities/{id}/schedule` — note query count in Debugbar. Baseline: ~221. 3. Read `ActivityController::schedule()` — identify the loop or relationship that triggers N+1. Common patterns: iterating over activities and calling `$activity->sessions` or `$activity->enrollments` without eager loading. 4. Add `with(['sessions', 'enrollments.trainee', 'sessions.teacher'])` or equivalent eager loads to the initial query. 5. Re-load the page — query count should drop significantly (221 → <10 target). 6. Measure page load time — target <3s. 7. Run `php artisan test --filter=ActivityManagementTest` — must pass. 8. Run full `php artisan test` — all 359 must still pass. |
| **Rollback Strategy** | Eager loading is additive — adding `with()` clauses doesn't change query results, only how they're fetched. If performance worsens (rare but possible with over-eager-loading), remove `with()` clauses. |
| **Completion Criteria** | [ ] Schedule page query count: <10 (was 221). [ ] Schedule page load time: measured and documented (target <3s). [ ] ActivityManagementTest passes. [ ] 359 PHPUnit tests passing. |
| **Estimated Effort** | 30-60 minutes |
| **Risk of Change** | LOW — eager loading is a read optimization. Does not change query results. |
| **Notes** | Trainee creation (26s) is a separate optimization — queueing email notifications and bulk-inserting enrollments. Defer trainee creation optimization to Phase 3 unless it's blocking UAT. This task focuses on the schedule page only. |

---

## Task B5-T5: Audit archive .env APP_KEYs

| Field | Value |
|-------|-------|
| **Task ID** | B5-T5 |
| **Priority** | P2 |
| **Source Evidence** | `CRITICAL_FINDINGS_REGISTER.md` CF-05 |
| **Finding Reference** | CF-05 — 4 old .env files with exposed APP_KEY values |
| **Problem Statement** | `archive/cream/.env`, `archive/Code VSC/CREAM/.env`, `archive/Code VSC/creams/.env`, `archive/Code VSC/creamtest1/.env` contain real `APP_KEY=base64:...` values. Unknown if any of these keys match a key still in use on any live system. |
| **Affected Files** | `archive/cream/.env`, `archive/Code VSC/CREAM/.env`, `archive/Code VSC/creams/.env`, `archive/Code VSC/creamtest1/.env` |
| **Affected Components** | Credential security |
| **Dependencies** | None |
| **Execution Preconditions** | [ ] Access to archive files. |
| **Verification Steps** | 1. Extract APP_KEY from each archive .env: `grep APP_KEY archive/cream/.env` etc. 2. Compare against current `.env` APP_KEY (if .env exists locally): `grep APP_KEY .env`. 3. Compare against `.env.production`: `grep APP_KEY .env.production`. 4. If any match: the key is exposed and must be rotated. 5. If none match: these are old local dev keys, low risk. 6. Add all 4 archive .env paths to `.gitignore`. 7. Document findings in CF-05. |
| **Rollback Strategy** | No code changes needed. If a key matches and rotation is required, follow B2-T1 procedure. |
| **Completion Criteria** | [ ] All 4 archive APP_KEYs compared against current .env and .env.production. [ ] No matches → low risk confirmed. OR matches found → rotation planned. [ ] Archive .env paths added to .gitignore. |
| **Estimated Effort** | 10 minutes |
| **Risk of Change** | NONE — audit only |
| **Notes** | These are old local development keys. They were likely never used in production. The risk is LOW if no match is found. |

---

## Task B5-T6: Document Sanctum dormancy

| Field | Value |
|-------|-------|
| **Task ID** | B5-T6 |
| **Priority** | P2 |
| **Source Evidence** | `CRITICAL_FINDINGS_REGISTER.md` CF-20 |
| **Finding Reference** | CF-20 — `config/sanctum.php` exists but custom session auth is used |
| **Problem Statement** | Laravel Sanctum config file exists in `config/` but the auth system is custom session-based (`POST /auth/check` via `MainController@check`). The config file's presence may mislead developers into thinking Sanctum is active. Multiple stale docs (old CLAUDE.md copies) claimed Breeze+Sanctum. |
| **Affected Files** | `config/sanctum.php` |
| **Affected Components** | Auth architecture documentation |
| **Dependencies** | None |
| **Execution Preconditions** | [ ] Verify no code references Sanctum classes. |
| **Verification Steps** | 1. Search codebase for Sanctum usage: `grep -r "Laravel\\\\Sanctum" app/ routes/` and `grep -r "Sanctum::" app/ routes/`. 2. Check `routes/api.php` for `auth:sanctum` middleware. 3. If NO code references found: Sanctum is fully dormant. (a) Add a comment at the top of `config/sanctum.php`: `// DORMANT: CREAMS uses custom session-based auth (POST /auth/check via MainController@check). This config is not used. Do NOT activate without architectural review.`. (b) Document in `docs/Validate/SOURCE_OF_TRUTH.md` architecture section. 4. If code references ARE found: REQUIRES VALIDATION — investigate what routes or features depend on Sanctum. |
| **Rollback Strategy** | Adding a comment is reversible. Do NOT delete the config file — it may be required by a Composer package or Laravel framework boot sequence. |
| **Completion Criteria** | [ ] Sanctum code references audited. [ ] Dormancy documented in config file comment AND architecture docs. [ ] Stale doc claims about Breeze+Sanctum confirmed archived. |
| **Estimated Effort** | 10 minutes |
| **Risk of Change** | NONE — documentation only |
| **Notes** | Do NOT remove `config/sanctum.php`. Laravel's config system may expect it to exist even if unused. The comment at the top of the file is sufficient. |

---

## Task B5-T7: Create missing pint.json coding standard

| Field | Value |
|-------|-------|
| **Task ID** | B5-T7 |
| **Priority** | P2 |
| **Source Evidence** | `MISSING_ARTIFACTS.md` M01, `CURRENT_CODE_QUALITY_STATE.md` linting section |
| **Finding Reference** | M01 — No coding standard config (pint.json or .php-cs-fixer.php) |
| **Problem Statement** | Pint is referenced in CI workflow but no project-specific `pint.json` exists. Without it, Pint runs with Laravel defaults, which may not match project conventions. AI agents and new developers have no codified formatting rules. |
| **Affected Files** | `pint.json` (to be created) |
| **Affected Components** | Code formatting, CI pipeline |
| **Dependencies** | None |
| **Execution Preconditions** | [ ] Laravel Pint installed (`./vendor/bin/pint` or `composer require laravel/pint --dev`). |
| **Verification Steps** | 1. Generate a default pint.json: `./vendor/bin/pint --generate` or create manually. 2. Configure: `"preset": "laravel"` as base. 3. Based on `.editorconfig`: set `"indent": "    "` (4 spaces), `"lineEnding": "\n"`. 4. Set `"rules"`: consider `"not_operator_with_successor_space": false` (project may use `! $var` vs `!$var`). 5. Run `./vendor/bin/pint --test` — check how many files would be changed. If large number (>50), the preset is too aggressive — relax rules. 6. Run `./vendor/bin/pint` to apply formatting. 7. Commit `pint.json`. 8. Update CI: remove `continue-on-error: true` from lint step so formatting failures block CI. |
| **Rollback Strategy** | Pint formatting is reversible: `git checkout .` before committing. If pint.json rules are too aggressive, adjust preset or add `"notPath"` exclusions. |
| **Completion Criteria** | [ ] `pint.json` exists at repo root. [ ] `./vendor/bin/pint --test` passes (0 files need changes). [ ] CI lint step enforced (no continue-on-error). |
| **Estimated Effort** | 20-30 minutes |
| **Risk of Change** | LOW — formatting only. Does not change code logic. |
| **Notes** | Run Pint on a separate branch first to evaluate impact. Large reformats should be a dedicated commit with `Docs(CodingStandard): Apply Pint formatting.` per COMMIT_MESSAGE_SOP. |

---

## Task B5-T8: Add env gate to GombakDataExtractor.php or delete it

| Field | Value |
|-------|-------|
| **Task ID** | B5-T8 |
| **Priority** | P2 |
| **Source Evidence** | `CRITICAL_FINDINGS_REGISTER.md` CF-17 |
| **Finding Reference** | CF-17 — GombakDataExtractor.php can regenerate PDPA backup on demand |
| **Problem Statement** | `database/seeders/GombakDataExtractor.php` is the source of `real_data_backup.json`. If executed, it will regenerate the backup with CURRENT database data — potentially more dangerous than the stale file already on disk. It has no env gate. |
| **Affected Files** | `database/seeders/GombakDataExtractor.php` |
| **Affected Components** | Seeder execution, PDPA boundary |
| **Dependencies** | B1-T1 (real_data_backup.json already secured — this prevents regeneration) |
| **Execution Preconditions** | [ ] B1-T1 complete. |
| **Verification Steps** | **Option A (Env gate):** 1. Add `if (!app()->environment('local')) { throw new \RuntimeException('GombakDataExtractor can only run in local environment.'); }` at the top of the extractor's run method. 2. Also check `APP_DEBUG=true` as an additional gate (matching IRLSeeder pattern). **Option B (Delete):** 1. If `real_data_backup.json` is the only output and the backup is sufficient for recovery, delete `GombakDataExtractor.php` entirely. 2. Verify no code references it: `grep -r "GombakDataExtractor" app/ database/`. |
| **Rollback Strategy** | Option A: remove the env gate lines. Option B: restore from git history. |
| **Completion Criteria** | [ ] GombakDataExtractor cannot execute outside local env (Option A) OR file deleted (Option B). [ ] If deleted: 0 code references remain. |
| **Estimated Effort** | 5 minutes (Option A) or 10 minutes (Option B with reference check) |
| **Risk of Change** | NONE — file is a utility, not part of application flow |
| **Notes** | This was flagged as H3 in DELTA_REEVAL (Mar 2026). The extractor was used to create the initial backup before migration changes. It is no longer needed if `real_data_backup.json` is sufficient for recovery reference. |

---

## Wave 5 Completion Checklist

- [ ] B5-T1: 6 empty checkpoint stubs resolved (backfilled or marked idle)
- [ ] B5-T2: Root-level temp files cleaned (archived or deleted)
- [ ] B5-T3: CI test divergence documented or resolved
- [ ] B5-T4: Schedule page N+1 queries optimized (measured baseline)
- [ ] B5-T5: Archive .env APP_KEYs audited (no live key matches)
- [ ] B5-T6: Sanctum dormancy documented in config + architecture docs
- [ ] B5-T7: pint.json created and CI lint enforced
- [ ] B5-T8: GombakDataExtractor env-gated or deleted
- [ ] 359 PHPUnit tests still passing (all tasks are non-breaking)
- [ ] No new technical debt introduced by cleanup operations

---

*All P2 tasks are documentation, config, or cleanup. No deep code changes. Most can run in parallel. No deployment dependency.*
