# CREAMS Repository Governance Audit

> **Document type**: Governance audit (full repository rationalization)
> **Audit date**: 2026-06-11
> **Branch at audit time**: `Fixers`
> **Auditor**: AI-assisted governance board session (architecture, SCM, release engineering, compliance, knowledge management, ILM perspectives)
> **Mandate**: Audit everything; classify active vs inactive; preserve all information; archive — never delete.
> **Companion reports**: `ARCHIVE_EXECUTION_REPORT.md`, `AI_INDEXING_REDUCTION_REPORT.md` (same folder)

---

## 1. Executive summary

The CREAMS working tree is approximately **5.3 GB** with **~16,000 files outside `.git/`** (8,316 git-tracked). The Laravel application itself (`app/`, `routes/`, `resources/`, `config/`, `database/`, test sources) is small and healthy — roughly **510 files / ~7 MB of source**. Over **95% of repository weight is non-source artifact accumulation**, concentrated in five locations:

| # | Location | Size | Files | Tracked in git? | Nature |
|---|----------|------|-------|-----------------|--------|
| 1 | `tests/Browser/test-results.json/` | 1.49 GB | 469 | No (ignored) | Playwright trace.zip archives (~20 MB each), regenerable |
| 2 | `storage/logs/` | 583 MB | ~30 | No (ignored) | Laravel/security/database logs, some 40 MB single files |
| 3 | `docs/archive/audit_screenshots/` | 505 MB | 550 | No (ignored via `*.png`) | Historical audit screenshot evidence |
| 4 | `tests/Browser/playwright-report/` | 205 MB | 679 | No (ignored) | Generated HTML test report |
| 5 | `docs/archive/irl_reference_materials/` | 63 MB | 43 | No | **Byte-identical duplicate** of root `IRL Files/` (verified by MD5) |

Additionally, `.git/` itself is **1.8 GB** — large binary artifacts have been committed historically and live on in pack history even where the working tree was cleaned.

The audit's conclusion: **the application is not bloated; the repository's historical evidence is**. Rationalization can recover ~2.9 GB of working-tree weight and remove ~1,800 files from indexer scope without deleting a single byte, by consolidating historical artifacts under a single top-level `Archive/` root that AI tooling can be told to skip.

---

## 2. Method

### 2.1 Source-of-truth hierarchy applied

Importance was judged against, in order: current application code (`app/`, `routes/`, `resources/`, `database/`), tests, deployment workflows (`.github/workflows/ci.yml`, `deploy.yml`, `deploy.sh`, `Dockerfile`), active documentation per `docs/SOURCE_OF_TRUTH.md` (the project's own authority index), AI instruction files (`CLAUDE.md` chain), then historical reports.

### 2.2 Reference analysis performed

For every archive candidate, the following searches were run before classification:

- **A. Direct reference**: `grep -r "<name>"` across `app/`, `routes/`, `config/`, `resources/`, `scripts/`, `docs/`, `.github/`, `.claude/`
- **B. Import/use**: PHP `require`/`include`/composer autoload and JS import checks for root-level scripts
- **C. Routes**: `routes/*.php` scanned for path references
- **D. Config**: `config/*.php`, `composer.json`, `package.json`, `vite.config.js`, `playwright.config.ts`
- **E. Documentation**: active docs (`docs/SOURCE_OF_TRUTH.md` authority table and everything it links)
- **F. Deployment**: `ci.yml`, `deploy.yml`, `deploy.sh`, `Dockerfile`, `docker-compose.yml`
- **G. Tests**: `tests/` suites and Playwright config
- **H. AI workflow**: root `CLAUDE.md`, `CREAMS/CLAUDE.md`, `docs/CLAUDE.md`, `AGENTS.md`, `.claude/`, `.mcp.json`

**Interpretation rule used**: a reference *blocks* archiving when it is a live dependency (code, config, CI, or an active doc relying on the content). A reference that merely *catalogs or deprecates* a file (e.g. `SOURCE_OF_TRUTH.md`'s "What you must NOT trust" table) does not block archiving, but does require the cataloging document to be updated with the new path in the same change. Without this rule, no historical file could ever be archived, since audit documents reference everything.

### 2.3 Duplicate detection

MD5 checksums were computed for suspected duplicate trees. Confirmed findings:

1. **`docs/archive/irl_reference_materials/` (43 files) is a 100% byte-identical subset of root `IRL Files/` (49 files).** Every hash in the archive copy exists in `IRL Files/`; `IRL Files/` has 6 additional unique files. No unique information exists in the archive copy.
2. **`docs/archive/duplicates/user_manuals_copy/`** — already identified by the project's own prior containment pass as a byte-identical duplicate of `docs/10_User_Manuals/` (per `SOURCE_OF_TRUTH.md`).
3. **UAT report near-duplicates** in `docs/UAT FILES/` (`COMPREHENSIVE_UAT_REPORT_2025-10-13.md` vs `..._FINAL.md`) — retained as-is; UAT evidence is protected (see §4.3).

---

## 3. Inventory

### 3.1 Top-level breakdown (size / file count)

| Directory | Size | Files | Verdict |
|-----------|------|-------|---------|
| `tests/` | 1.78 GB | 2,117 | **ARTIFACT-HEAVY** — 1.70 GB is generated Playwright output (ignored); source tests are ~300 KB |
| `storage/` | 610 MB | 764 | **ARTIFACT-HEAVY** — 583 MB old logs; framework cache is normal |
| `docs/` | 591 MB | 791 | **ARTIFACT-HEAVY** — 588 MB under `docs/archive/`; active docs ~3 MB |
| `public/` | 188 MB | 7,406 | **ACTIVE but heavy** — vendored assets/fonts (7,100 files), app images, videos; all referenced or servable |
| `IRL Files/` | 65 MB | 49 | **INACTIVE** — real-world reference photos/invoices; no application reference |
| `node_modules/` | 58 MB | 3,617 | Active dependency dir (ignored) |
| `app/` | 2.6 MB | 213 | **ACTIVE** — protected |
| `resources/` | 3.4 MB | 177 | **ACTIVE** — protected |
| `database/` | 657 KB | 67 | **ACTIVE** — protected |
| `config/` | 89 KB | 18 | **ACTIVE** — protected |
| `routes/` | 82 KB | 6 | **ACTIVE** — protected |
| `scripts/` | 26 KB | 5 | **ACTIVE** — referenced by hooks |
| `bootstrap/`, `docker/` | 49 KB | 7 | **ACTIVE** — protected |
| `tmp-pw-harness/` | 56 KB | ~2 | **INACTIVE** — leftover throwaway Playwright harness (untracked, node_modules only) |
| `archive/` (lowercase) | ~0 | 0 | Empty; its single tracked file (`chmod`) already shows as deleted in git status |
| `.git/` | 1.8 GB | — | History bloat — see §6.3 |

### 3.2 Top indexing bottlenecks (Top largest files)

The 50 largest files in the working tree (excluding `.git/`, `vendor/`, `node_modules/`) are, in order:

- 4× **40 MB log files** — `storage/logs/{database,laravel,security,application}-2026-05-06.log`
- 1× **26 MB video** — `public/videos/volunteerpage.mp4` (referenced by `resources/views/volunteers/home.blade.php:33` — **KEEP**)
- ~40× **~20 MB Playwright `trace.zip`** files under `tests/Browser/test-results.json/`
- Additional 20–23 MB logs from 2026-02/03 in `storage/logs/`

### 3.3 Root-level stray files (tracked one-off analysis artifacts)

| File | Size | Refs found | Verdict |
|------|------|-----------|---------|
| `routes_analysis.json` | 108 KB | Only `docs/CREAMS_PROJECT_AUDIT_EVALUATION.txt` (historical) | Archive |
| `system_wide_verification.php` | 37 KB | Historical docs only | Archive |
| `comprehensive_route_controller_verification.php` | 39 KB | None | Archive |
| `comprehensive_uat_automation.php` | 45 KB | None | Archive |
| `automation_script.sh` | 11 KB | None | Archive |
| `check_columns.php` | 0.6 KB | None | Archive |
| `routes_export.json` | 0.3 KB (untracked, ignored) | None | Archive |
| `deploy.sh`, `Dockerfile`, `docker-compose.yml`, `artisan`, configs | — | CI / runtime | **KEEP — protected** |
| `sonar-project.properties`, `phpstan.neon`, `phpunit*.xml` | — | CI / tooling | **KEEP** |

None of the archived candidates appear in `composer.json` scripts, `package.json` scripts, CI workflows, `deploy.sh`, or `Dockerfile`.

---

## 4. Classification

### 4.1 ACTIVE (never archive — special protection honored)

`app/`, `routes/`, `resources/`, `database/`, `tests/` (source: `Feature/`, `Unit/`, `Traits/`, `Browser/tests|pages|helpers|fixtures`, configs), `config/`, `public/` (servable assets — `volunteerpage.mp4` confirmed referenced; `public/assets`, `public/fonts`, `public/css`, `public/js`, `public/images`, `public/avatars`, `public/build`, `public/libs` are vendored/served), `composer.json`, `package.json`, lockfiles, `vite.config.js`, Docker/deploy files, CI workflows, `CLAUDE.md` (root overlay), `AGENTS.md`, `.claude/`, `.mcp.json`, `scripts/` (hook scripts), all ADRs, `docs/SOURCE_OF_TRUTH.md` and every file in its authority table, `docs/{01..10}_*/` numbered documentation tree, `docs/UAT FILES/`, `docs/audit/`, `docs/07_Fixes_and_Audits/`, production runbooks (`PRODUCTION_DEPLOYMENT.md`, `PRODUCTION_ROLLBACK.md`, `PRE_DEPLOY_SECURITY_CHECKLIST.md`), security docs, `HANDOVER_PACKAGE_2026-05-04.md` + companions (recent, referenced).

### 4.2 ARCHIVE — High Confidence (all reference checks negative; executed)

See `ARCHIVE_EXECUTION_REPORT.md` for the executed manifest. Summary:

| Asset | Evidence |
|-------|----------|
| `tests/Browser/test-results.json/` (1.49 GB) | Generated trace output; gitignored; regenerated by every Playwright run; `playwright.config.ts` recreates the folder |
| `tests/Browser/playwright-report/` (205 MB) | Generated HTML report; gitignored; regenerable |
| `storage/logs/*.log` (583 MB, newest 2026-05-06, >1 month old) | Runtime logs; gitignored; Laravel recreates on demand; historical forensic value preserved by archiving |
| `docs/archive/**` (588 MB total) | Project's own SOURCE_OF_TRUTH declares the entire tree "historical only — do not cite as current" |
| `docs/archive/irl_reference_materials/` | 100% byte-duplicate of `IRL Files/` (MD5-verified) |
| Root one-off scripts/exports (§3.3) | Zero active references |
| `docs/CREAMS_PROJECT_AUDIT_EVALUATION.txt` | Historical AI audit transcript; zero inbound references |
| `IRL Files/` (65 MB) | Real-centre reference photos/invoices from May 2025 site visit; referenced only by the historical audit transcript above; not used by app, tests, deployment, or active docs |
| `tmp-pw-harness/` | Untracked leftover scratch harness (node_modules only) |
| `docs/CLAUDE.md` (stale Feb 2026 AI instruction file) | Explicitly superseded; `CREAMS/CLAUDE.md` states it "will be archived in a later cleanup session" (= this session); `SOURCE_OF_TRUTH.md` lists it as do-not-trust; actively harmful because Claude Code auto-injects it (wrong auth stack, wrong role names). Cataloging references updated in same change. |

### 4.3 KEEP — explicitly evaluated and retained

| Asset | Why retained |
|-------|--------------|
| `docs/UAT FILES/` (810 KB, 40 files) | UAT evidence — protected by mandate question 2 ("required for UAT evidence?" = YES) |
| `docs/audit/` (352 KB) | Audit history/compliance evidence (question 3 = YES) |
| `docs/07_Fixes_and_Audits/` | Fix provenance — maintenance value (question 5 = YES) |
| `docs/06_Status_Reports/` | Flagged misleading by SOURCE_OF_TRUTH but small and cataloged; Medium confidence → retained |
| `public/letters/` (110 tracked PDFs/HTML incl. `DEBUG_*`/`TEST_*` files) | Runtime letter-output directory; `LetterController` et al. read/write `public_path('letters/...')` and DB rows may hold `letter_file_path` pointing here. Moving any file risks breaking stored links. **Manual review required** |
| `public/videos/volunteerpage.mp4` (26 MB) | Referenced by `volunteers/home.blade.php` |
| `docs/CONSOLIDATED_DOCUMENTATION_INDEX.md`, `docs/CREAMS_CODEBASE_DOCUMENTATION.md` | Stale per SOURCE_OF_TRUTH but cross-referenced by multiple docs; Medium confidence → flagged, not moved |
| `docs/CREAMS_SESSION_2026-04-16.md`, `DELTA_REEVAL_REPORT_2026-03-22.md`, `MASTER_PROGRESS_LOG.md`, `PHASE2_PROGRESS.md`, `PLAN.md` | Referenced by `CREAMS_SESSION_CURRENT.md` and/or `docs/audit/` registers; Medium confidence → flagged, not moved |
| `tests/Browser/node_modules/`, root `node_modules/`, `vendor/` | Live dependencies (ignored) |
| `tests/Browser/nul` | Windows reserved-device-name artifact (gitignored). Cannot be safely manipulated with normal tooling; flagged for manual removal with `\\?\` path syntax if ever desired. Left untouched |
| `.phpunit.result.cache`, `storage/framework/*` | Regenerable caches in expected locations |

### 4.4 PDPA observations (Compliance Auditor)

1. **`IRL Files/` is committed to git history** and contains real centre material (invoices naming "Rumah PDK Bilal", site photographs). Archiving moves it out of the active tree but **it remains in git history**. If this repository is ever made public or shared, a history scrub (`git filter-repo`) must be performed first. Flagged — not executed (destructive, requires explicit owner decision).
2. `docs/archive/audit_screenshots/` (550 PNGs) may contain real data captured during audits; it is untracked, and is archived to `Archive/` which remains local-only for these files.
3. `storage/logs/` may contain PII in plaintext; archived copies inherit this sensitivity. Recommend eventual retention-policy decision (out of scope for this session — no deletion permitted).

---

## 5. Final validation (10-question gate)

Applied to every High Confidence item; recorded per-group in `ARCHIVE_EXECUTION_REPORT.md`. All ten answers were NO for every moved asset. Any asset with a YES or an *uncertain* answer was kept active and listed in §4.3 / §7.

---

## 6. Recommendations beyond this session's scope

1. **`.git` is 1.8 GB.** The largest single lever remaining. A `git filter-repo` pass to strip historical screenshots/videos/logs from pack history would shrink clones dramatically — but rewrites history (TIER 4, destructive, needs explicit owner approval and coordination). **Not executed.**
2. **Add indexer ignore configuration** for OpenCode/AI tools covering `Archive/`, `vendor/`, `node_modules/`, `storage/`, `public/assets/`, `public/fonts/` (see `AI_INDEXING_REDUCTION_REPORT.md`).
3. **Log rotation**: configure `LOG_CHANNEL=daily` with `LOG_DAILY_DAYS` so 40 MB log files stop accumulating.
4. **Playwright output retention**: add `--max-failures` trace retention settings or periodic cleanup of `Archive/Historical_UAT/playwright-traces/`.
5. **Decide public/letters debug-file fate** after checking the `letters`/`generated_letters` DB tables for `letter_file_path` references (Manual Review queue).

---

## 7. ARCHIVE CONFIDENCE SCORE

### High Confidence — auto-move EXECUTED
- `tests/Browser/test-results.json/` → `Archive/Historical_UAT/playwright-traces/`
- `tests/Browser/playwright-report/` → `Archive/Historical_UAT/playwright-report/`
- `storage/logs/*.log` (all dated ≤ 2026-05-06) → `Archive/Legacy_Backups/storage-logs/`
- `docs/archive/audit_screenshots/` → `Archive/Historical_Screenshots/audit_screenshots/`
- `docs/archive/misc_images/` → `Archive/Historical_Screenshots/misc_images/`
- `docs/archive/irl_reference_materials/` → `Archive/Legacy_Exports/irl_reference_materials_DUPLICATE/`
- `docs/archive/prompts/` → `Archive/Historical_AI_Artifacts/prompts/`
- `docs/archive/deployment/` → `Archive/Superseded_Documents/deployment/`
- `docs/archive/duplicates/` → `Archive/Superseded_Documents/duplicates/`
- `docs/archive/audits/` → `Archive/Historical_Audits/`
- `docs/archive/quarantine/` → `Archive/Legacy_Backups/quarantine/`
- `docs/archive/README.md`, `MOVE_OLD_CREAM_FOLDERS.bat` → `Archive/Superseded_Documents/`
- Root one-off scripts/exports (§3.3, 7 files) → `Archive/Historical_AI_Artifacts/root-analysis-scripts/`
- `docs/CREAMS_PROJECT_AUDIT_EVALUATION.txt` → `Archive/Historical_Reports/`
- `IRL Files/` → `Archive/Legacy_Exports/IRL_Files/`
- `tmp-pw-harness/` → `Archive/Historical_AI_Artifacts/tmp-pw-harness/`
- `docs/CLAUDE.md` → `Archive/Superseded_Documents/docs-CLAUDE.md` (pre-authorized by `CREAMS/CLAUDE.md`; cataloging refs updated)

### Medium Confidence — flagged, NOT moved
- `docs/CONSOLIDATED_DOCUMENTATION_INDEX.md` (stale, but cross-referenced)
- `docs/CREAMS_CODEBASE_DOCUMENTATION.md` (stale, but cross-referenced)
- `docs/CREAMS_SESSION_2026-04-16.md` (superseded by `_CURRENT`, but referenced by active audit registers)
- `docs/06_Status_Reports/` demo-era "FINAL/COMPLETE/ULTIMATE" snapshots
- `docs/MASTER_PROGRESS_LOG.md`, `docs/PHASE2_PROGRESS.md`, `docs/PLAN.md` (dated progress docs; supersession unclear)
- `docs/UAT FILES/OLD_CREAMS_*.csv` (named "OLD" but inside protected UAT evidence set)

### Low Confidence — retain indefinitely unless owner decides otherwise
- `docs/06_Status_Reports/DEMO_CHECKLIST.md`, `DEMO_DAY_FINAL_REPORT.md` (recent demo-cycle evidence)
- `docs/CODEX_INIT_PROMPT.md` (referenced by handover and setup guides)
- `docs/creams_detailed_uat.md`, `docs/current uat progress.txt` (active UAT tracking)

### Manual Review Required — owner decision needed
- `public/letters/` `DEBUG_*`/`TEST_*`/`BASE64_*` PDFs (110 tracked files, ~10 MB) — verify no `letter_file_path` DB rows point at them, then archive
- `tests/Browser/nul` — Windows reserved-name artifact; needs `\\?\`-prefixed deletion or PowerShell `Remove-Item -LiteralPath` if removal is ever desired (deletion forbidden this session)
- `.git/` 1.8 GB history bloat — `git filter-repo` decision (TIER 4)
- `IRL Files/` presence in git history — PDPA exposure decision (TIER 4)

---

*Generated by the 2026-06-11 governance audit session. No files were deleted. All moves are reversible (`git mv` for tracked files preserves history; untracked files moved verbatim).*
