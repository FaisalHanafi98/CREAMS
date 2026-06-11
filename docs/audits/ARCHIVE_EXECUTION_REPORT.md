# Archive Execution Report — CREAMS

> **Date**: 2026-06-11
> **Branch**: `Fixers`
> **Authority**: `REPOSITORY_GOVERNANCE_AUDIT.md` (same folder) — only items scored **High Confidence** there are executed here.
> **Method**: `git mv` for tracked files (history preserved); filesystem `mv` for untracked artifacts. **Zero deletions.**

---

## 1. Archive root structure

`Archive/` at repository root (absorbs the previously empty, gitignored `archive/` folder):

```
Archive/
├── Historical_Audits/
├── Historical_UAT/
├── Historical_Screenshots/
├── Historical_Reports/
├── Historical_AI_Artifacts/
├── Historical_Handoffs/        (reserved — no High Confidence items this pass)
├── Superseded_Documents/
├── Legacy_Exports/
└── Legacy_Backups/
```

## 2. Executed move manifest (High Confidence only)

| # | Source | Destination | Files | Size | Tracked | Final-validation gate |
|---|--------|-------------|-------|------|---------|----------------------|
| 1 | `tests/Browser/test-results.json/` | `Archive/Historical_UAT/playwright-traces/` | 469 | 1.49 GB | No | 10×NO — regenerable Playwright traces |
| 2 | `tests/Browser/playwright-report/` | `Archive/Historical_UAT/playwright-report/` | 679 | 205 MB | No | 10×NO — regenerable HTML report |
| 3 | `storage/logs/*-20??-*.log` (dated, ≤2026-05-20; **`laravel.log` current log kept in place**) | `Archive/Legacy_Backups/storage-logs/` | ~125 | 583 MB | No | 10×NO — historical runtime logs; Laravel recreates |
| 4 | `docs/archive/audit_screenshots/` | `Archive/Historical_Screenshots/audit_screenshots/` | 550 | 505 MB | No | 10×NO — historical evidence, preserved intact |
| 5 | `docs/archive/misc_images/` | `Archive/Historical_Screenshots/misc_images/` | 4 | 1.8 MB | No | 10×NO |
| 6 | `docs/archive/irl_reference_materials/` | `Archive/Legacy_Exports/irl_reference_materials_DUPLICATE/` | 43 | 63 MB | No | 10×NO — MD5-verified byte-duplicate of `IRL Files/` |
| 7 | `docs/archive/prompts/` | `Archive/Historical_AI_Artifacts/prompts/` | 12 | 0.3 MB | Yes | 10×NO — SOURCE_OF_TRUTH: "do not follow"; cataloging refs updated (§5) |
| 8 | `docs/archive/deployment/` | `Archive/Superseded_Documents/deployment/` | 3 | 0.1 MB | Yes | 10×NO — abandoned Vercel/AWS targets |
| 9 | `docs/archive/duplicates/` | `Archive/Superseded_Documents/duplicates/` | 9 | 0.3 MB | Yes | 10×NO — byte-duplicate of `docs/10_User_Manuals/` |
| 10 | `docs/archive/audits/` | `Archive/Historical_Audits/` | 2 | 48 KB | Yes | 10×NO — pre-recovery snapshot + old test log |
| 11 | `docs/archive/quarantine/` | `Archive/Legacy_Backups/quarantine/` | 2 | 4 MB | Yes | 10×NO — malformed-filename artifacts |
| 12 | `docs/archive/README.md`, `docs/archive/MOVE_OLD_CREAM_FOLDERS.bat` | `Archive/Superseded_Documents/` | 2 | <50 KB | Mixed | 10×NO |
| 13 | Root one-off analysis artifacts: `routes_analysis.json`, `routes_export.json`, `system_wide_verification.php`, `comprehensive_route_controller_verification.php`, `comprehensive_uat_automation.php`, `check_columns.php`, `automation_script.sh` | `Archive/Historical_AI_Artifacts/root-analysis-scripts/` | 7 | 0.6 MB | 6 of 7 | 10×NO — zero refs in code/CI/composer/package/deploy |
| 14 | `docs/CREAMS_PROJECT_AUDIT_EVALUATION.txt` | `Archive/Historical_Reports/` | 1 | ~0.5 MB | Yes | 10×NO — historical AI audit transcript, zero inbound refs |
| 15 | `IRL Files/` | `Archive/Legacy_Exports/IRL_Files/` | 49 | 65 MB | Yes | 10×NO for app/deploy/tests/docs; retains full evidentiary value inside Archive. PDPA note in audit §4.4 |
| 16 | `tmp-pw-harness/` | `Archive/Historical_AI_Artifacts/tmp-pw-harness/` | ~2 | 56 KB | No | 10×NO — scratch harness |
| 17 | `docs/CLAUDE.md` (stale Feb 2026) | `Archive/Superseded_Documents/docs-CLAUDE.md` | 1 | 12 KB | Yes | Archival pre-authorized by `CREAMS/CLAUDE.md` ("will be archived in a later cleanup session"); refs updated (§5) |

**Totals: ~1,960 files, ~2.92 GB relocated. 0 deleted.**

## 3. Items expressly NOT moved

Everything scored Medium/Low/Manual-Review in `REPOSITORY_GOVERNANCE_AUDIT.md` §7, all protected paths (`app/`, `routes/`, `resources/`, `database/`, `tests/` sources, `public/` served assets incl. `public/letters/`, configs, CI/CD, runbooks, ADRs, `docs/UAT FILES/`, `docs/audit/`, numbered docs tree), plus `storage/logs/laravel.log` (live log), `tests/Browser/nul` (Windows reserved name), and all `node_modules/`/`vendor/`.

## 4. Supporting changes

1. **`.gitignore`** — the blanket `/archive/` rule was replaced with targeted rules so (a) tracked files moved into `Archive/` remain tracked, and (b) the moved untracked artifact sets do not flood `git status`:
   - `/Archive/Historical_UAT/`
   - `/Archive/Historical_Screenshots/`
   - `/Archive/Legacy_Backups/storage-logs/`
   - `/Archive/Legacy_Exports/irl_reference_materials_DUPLICATE/`
   - `/Archive/Historical_AI_Artifacts/tmp-pw-harness/`
2. **`Archive/README.md`** created — explains layout, provenance, restoration procedure, and the no-deletion guarantee.
3. **Reference updates** (cataloging documents repointed, §5).

## 5. Reference updates applied

| Document | Change |
|----------|--------|
| `docs/SOURCE_OF_TRUTH.md` | "What you must NOT trust" table rows repointed from `docs/archive/...` to `Archive/...`; `docs/CLAUDE.md` row updated to its archived location |
| `CLAUDE.md` (CREAMS project overlay) | Hard-rule path `docs/archive/prompts/` → `Archive/Historical_AI_Artifacts/prompts/`; stale-CLAUDE.md footnote updated to reflect completed archival |
| `docs/CREAMS_SESSION_CURRENT.md` | DO-NOT rule path `docs/archive/prompts/` → `Archive/Historical_AI_Artifacts/prompts/` |

## 6. Post-move verification

- [x] `git status --porcelain` shows **86 R (renames), 5 M (this session's governance-doc edits + .gitignore), 4 ?? (`docs/audits/` with the three new reports, `Archive/README.md`, plus `routes_export.json` and the duplicate env example which became visible after relocation — both safe to commit), 1 pre-existing D (`archive/chmod`, deleted before this session began)**. No unexpected deletions.
- [x] All previously-tracked `docs/archive/`, root-script, `IRL Files/`, and `docs/CLAUDE.md` paths show as `R` (rename) in the git index — full history preserved.
- [x] `composer.json` autoload (`app/`, `database/`, `tests/`, `app/Helpers/helpers.php`), `package.json`, `playwright.config.ts`, CI workflows, `deploy.sh`, `Dockerfile`: zero references to any moved path.
- [x] `storage/logs/` retains live `laravel.log` + its `.gitignore`; directory intact and writable.
- [x] `docs/` shrunk 591 MB → ~8 MB; `tests/` 1.78 GB → 43 MB; `storage/` 610 MB → 14 MB.
- [ ] **Application smoke test (`php artisan route:list`) FAILS — pre-existing defect, not caused by this session.** Every artisan command (incl. `php artisan --version`) crashes with `Carbon\Exceptions\UnknownMethodException: Carbon::setWeekStartsAt does not exist`, thrown from committed app code at `app/Providers/MalaysianLocaleServiceProvider.php:40` (method removed in Carbon 3). Evidence it pre-dates the audit: this session moved zero PHP source, and `git diff HEAD -- app/ config/ composer.json composer.lock` is empty. Logged as a separate fix task.

## 7. Rollback procedure

Every move is reversible:
- Tracked files: `git mv` back, or `git reset` the staged renames before commit.
- Untracked artifacts: plain `mv` back to original paths (original locations recorded in §2).
No content was altered; only paths changed.

---

## ARCHIVE CONFIDENCE SCORE

**High Confidence** — items #1–#17 above. All eight reference-analysis searches negative (or cataloging-only references, updated in-place). All ten final-validation questions answered NO. **Moved automatically.**

**Medium Confidence** — flagged, NOT moved: `docs/CONSOLIDATED_DOCUMENTATION_INDEX.md`, `docs/CREAMS_CODEBASE_DOCUMENTATION.md`, `docs/CREAMS_SESSION_2026-04-16.md`, `docs/06_Status_Reports/` demo-era snapshots, `docs/MASTER_PROGRESS_LOG.md`, `docs/PHASE2_PROGRESS.md`, `docs/PLAN.md`, `docs/UAT FILES/OLD_CREAMS_*.csv`.

**Low Confidence** — retained: `docs/CODEX_INIT_PROMPT.md`, demo-cycle reports, active UAT trackers.

**Manual Review Required** — owner decision: `public/letters/` debug PDFs (check DB `letter_file_path` first), `tests/Browser/nul`, `.git` history scrub (`git filter-repo`, TIER 4), PDPA exposure of `IRL Files/` in git history (TIER 4).

---

## Verification output (captured at execution, 2026-06-11)

```
$ git status --porcelain | awk '{print $1}' | sort | uniq -c
      4 ??     (docs/audits/, Archive/README.md, relocated routes_export.json, relocated env example)
      1 D      (archive/chmod — pre-existing deletion, present before this session)
      5 M      (.gitignore, CLAUDE.md, docs/SOURCE_OF_TRUTH.md, docs/CREAMS_SESSION_CURRENT.md, .claude/settings.local.json)
     86 R      (all tracked moves — history-preserving renames)

$ du -sh docs tests storage          # after moves
7.8M  docs        (was 591 MB)
43M   tests       (was 1.78 GB)
14M   storage     (was 610 MB)

$ ls storage/logs/
laravel.log        (live log retained; ~125 dated logs archived)

$ ls -d docs/archive
ls: cannot access 'docs/archive': No such file or directory   (tree fully dissolved)

$ php artisan route:list ; php artisan --version
Carbon\Exceptions\UnknownMethodException: Method Carbon\Carbon::setWeekStartsAt does not exist
  at app/Providers/MalaysianLocaleServiceProvider.php:40
# PRE-EXISTING defect (Carbon 3 removed the method). git diff HEAD -- app/ config/ composer.json
# composer.lock is empty — this session changed no PHP. Tracked as a separate fix task.
```

**Result**: ~2.92 GB / ~1,960 files relocated, 0 deleted, 0 application files touched. Changes are staged but **not committed** — commit requires owner approval per the root SOP (CREAMS is a PDPA project; auto-commit is prohibited).

---

## INCIDENT ADDENDUM — 2026-06-11 (late evening): untracked Archive bulk deleted by unknown actor

After the archive moves were committed (`0ac1ac4`) and pushed, the on-disk `Archive/` tree was deleted by an unidentified process or manual action (not in the Recycle Bin; no matching commands in OpenCode session logs; not caused by this session's git operations).

**Recovered in full** (from git): all 89 tracked Archive files via `git restore Archive/`.

**Recovered partially** (from `stash@{1}`, the 2026-06-10 OpenCode Wave-1 stash): 6 UAT result JSONs (now force-added and tracked under `Archive/Historical_Screenshots/audit_screenshots/`) and 6 IRL reference PDFs (byte-duplicates of tracked files — no information loss).

**Lost permanently** (untracked, gitignored, no surviving copy found):
- `Archive/Historical_Screenshots/audit_screenshots/` PNG corpus (~505 MB, ~550 images). The machine-readable UAT JSON evidence survives.
- `Archive/Legacy_Backups/storage-logs/` (~583 MB of dated Laravel/security/database logs, Nov 2025 - May 2026). PDPA-sensitive; treat as an unplanned retention end.
- `Archive/Historical_UAT/` Playwright traces + HTML report (~1.7 GB) — regenerable by re-running the Playwright suite; no unique information.
- `Archive/Historical_Screenshots/misc_images/` (4 mockup images) and `tmp-pw-harness/` node_modules (no value).

**Root-cause lesson**: ignored-and-untracked content inside the repo tree has no safety net — it survives neither stashes, branch switches, nor deletion. Anything in `Archive/` worth keeping must be either git-tracked or backed up outside the repository. The recovered UAT JSONs were tracked immediately for this reason; `automation/` (recovered the same evening from `stash@{1}`) was backed up to `../CREAMS_automation_backup_2026-06-11.tar.gz` pending a PII-redaction pass before tracking.
