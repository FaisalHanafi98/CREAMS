# Branch Lineage & Deployment Baseline Audit — CREAMS

> **Date**: 2026-06-11
> **Trigger**: Pre-commit gate for the 2026-06-11 session work (repository rationalization + framework fixes)
> **Verdict up front**: **Option B — main contains newer work; synchronize before committing anything.**
> **Critical discovery**: the entire "Laravel 12 adoption" performed on `Fixers` this session **duplicates work already merged to `main` weeks ago**. None of this session's framework fixes may be committed as-is.

---

## 1. Branch relationship

```
merge-base(main, Fixers) = c627ac4 = tip of Fixers
```

`Fixers` is a **strict ancestor** of `main`. There is no divergence at the commit level — `main` simply continued 46 commits past where `Fixers` stopped (2026-05-04).

| Comparison | Ahead | Behind |
|------------|-------|--------|
| `main` vs `Fixers` | **46** | **0** |
| `main` vs `dev` | 16 | 0 |
| `chore/laravel-13-upgrade` | — | fully merged into `main` (verified `merge-base --is-ancestor`) |

Local and remote are in sync: `main` == `origin/main` (82aabd1), `Fixers` == `origin/Fixers` (c627ac4).

### Branch graph (linear — newest first, abridged)

```
* 82aabd1 (origin/main, main)  Fix(Tests): fix two test failures introduced in PublicBlockerFixTest.
* 631bcde                      Fix(UAT): unblock browser retest
* 3e405ec                      Fix(UX): logout BFCache, trainee banner, form feedback, dead links
* 47c940d..55fdc5a             Fix(Cache/Auth): LiteSpeed no-cache + logout hardening (5 commits)
* d90b30a..a1fb529             Fix(Assets): asset 500s, soft-deletes, edit view (3 commits)
* 7fb0702..edbe77a             Fix(Ci): MySQL/SQLite migration compat, PHP 8.2, Hostinger alignment
* f6ec859 (dev)                Docs(Governance): production URL policy + AI context files
* 0117296                      Security(Letters): REMOVE old public letter PDFs from the repository
* eb8407c                      security(letters): move letter PDFs to private storage (PDPA)
* 80d3c3b                      chore(deploy): target PHP 8.2 server — downgrade L13→L12, set platform
* 09d18f6 / 0c285dc            chore(upgrade): Laravel 12→13 branch, merged then downgraded
* 8568da8                      chore(upgrade): Laravel 11→12, fix L12 route resolution changes
* c69e696                      chore(upgrade): Laravel 10→11, Carbon 2→3, PHPUnit 10→11
* c627ac4 (Fixers, origin/Fixers)  Fix(Multi): password hash, volunteer 500, dashboard tab, sw.js  ← session base
```

## 2. Explicit answers

| Question | Answer |
|----------|--------|
| **A. Is Fixers a superset of main?** | **No.** Fixers has zero commits main lacks. |
| **B. Is main a superset of Fixers?** | **Yes — strictly.** Every Fixers commit is in main, plus 46 more. |
| **C. Are they diverged?** | **Not in committed history** (pure fast-forward relationship). However, the `Fixers` **working tree** carries large uncommitted work from the 2026-06-11 session (Archive/ rationalization, audit reports, framework fixes). |
| **D. Which branch is the development source of truth?** | **`main`.** It carries the framework upgrades, the Hostinger deployment alignment, PDPA letter remediation, CI fixes, and all UAT-cycle fixes. `dev` is also an ancestor of main. `Fixers` is a stale May-4 snapshot. |

## 3. Deployment baseline

- **Deployment model** (per `main:.github/workflows/deploy.yml`): manual `git pull` via SSH on **Hostinger shared hosting** (LiteSpeed, PHP 8.2 — see commits `edbe77a`, `80d3c3b`, `47c940d`). The workflow itself only runs tests on push to `main` and `dev`.
- **Deployed branch**: `main` (the only branch the deploy process references; LiteSpeed/Hostinger fixes on main confirm production tracks it).
- **Deployed commit**: **cannot be determined from this machine** (no server access from the audit session). Verification step: `ssh <hostinger> "cd <app-root> && git rev-parse HEAD"` and compare to `82aabd1`.
- **Note**: docs claiming **Lightsail** as the deployment target (`docs/LIGHTSAIL_FOOTPRINT.md`, `SOURCE_OF_TRUTH.md` row) are superseded by main's Hostinger alignment (`f6ec859`, `edbe77a`). The SOURCE_OF_TRUTH authority table needs this correction during synchronization.

## 4. Duplicate-work analysis (mandate item 7)

**Main already contains the Laravel 12 stack** — `composer.json` on main: `php ^8.2`, `laravel/framework ^12.0`, `sanctum ^4.0`, `collision ^8.1`, `phpunit ^11.0`, with `config.platform.php = 8.2.29` pinned for the Hostinger server.

Per-fix comparison of this session's `Fixers` work against main:

| Session fix (on Fixers) | Already on main? | Disposition |
|--------------------------|------------------|-------------|
| Remove `Carbon::setWeekStartsAt/EndsAt` | **Yes** — `grep` finds zero occurrences on main | **Discard ours** |
| RouteServiceProvider duplicate-name order | **Yes** — main restructured further: demo `creams/{demo_id}` routes only register in non-production | **Discard ours; main's is better** |
| `modern.blade.php` empty-param `route()` JS hacks | **Yes** — zero occurrences on main | **Discard ours** |
| composer.json/lock → Laravel 12 | **Yes** — and main's pins platform PHP 8.2.29 (deploy-correct); ours resolved against PHP 8.5 | **Discard ours** |
| `TEST_BASELINE.md` re-verification note | No, but the note describes the Fixers-tree fix set | **Rewrite during sync** |

This also **solves the vendor-drift mystery** from earlier today: `vendor/` contained Laravel 12.58 because the upgrade happened on main/upgrade branches; checking out the stale `Fixers` branch left the new vendor in place against old code.

## 5. Archive-move overlap analysis (mandate item 8)

`git diff Fixers..main --name-status` over every path this session archived or edited (226 changed paths on main in total):

| Path group (archived/edited this session) | Changed on main? | Risk |
|--------------------------------------------|------------------|------|
| `docs/archive/**` (moved → `Archive/`) | No | None — moves replay cleanly |
| `IRL Files/` (moved → `Archive/Legacy_Exports/`) | No | None |
| Root one-off scripts (`routes_analysis.json`, `system_wide_verification.php`, etc.) | No | None |
| `docs/CLAUDE.md` (archived) | No | None |
| `docs/SOURCE_OF_TRUTH.md`, `docs/CREAMS_SESSION_CURRENT.md` (edited) | No | Low — re-apply edits, plus Hostinger correction |
| `.gitignore` (edited) | No | Low |
| **`CLAUDE.md` (project overlay, edited)** | **Yes (M on main)** | **Medium — manual three-way merge of both edits required** |
| **`public/letters/*` (kept, flagged Manual Review)** | **Yes — main DELETED all letter PDFs** (`0117296`, `eb8407c` moved letters to private storage for PDPA) | **None to merge — main already resolved our Manual-Review item; do not re-add these files** |

Per mandate item 8: one genuine overlap exists (`CLAUDE.md`), which is a stop-and-report condition for *blind* committing — reported here; the synchronization plan below handles it explicitly. The `public/letters` overlap is benign (main's deletion supersedes our keep-and-review).

## 6. Conflict risk assessment

If the session work were committed on `Fixers` and rebased/merged onto `main`:

| File | Conflict? | Resolution |
|------|-----------|------------|
| `composer.json`, `composer.lock` | **Certain** | Take main's wholesale; run `composer install` |
| `app/Providers/MalaysianLocaleServiceProvider.php` | **Certain** | Take main's |
| `app/Providers/RouteServiceProvider.php` | **Certain** | Take main's |
| `resources/views/dashboard/modern.blade.php` | **Certain** | Take main's |
| `CLAUDE.md` | Likely | Manual merge (our Archive path updates + main's edits) |
| `docs/TEST_BASELINE.md` | Possible | Re-verify on main, write fresh note |
| 86 archive renames, `Archive/README.md`, `docs/audits/*` | None | Replay/keep as-is |
| `.gitignore`, `docs/SOURCE_OF_TRUTH.md`, `docs/CREAMS_SESSION_CURRENT.md` | None | Keep ours, plus Hostinger/Lightsail correction |

Overall risk: **Low-to-medium, fully enumerated** — every certain conflict resolves by discarding our duplicate in favor of main.

## 7. Synchronization plan (no commits yet)

**Phase A — preserve and switch base**
1. Snapshot the session work (safety): `git stash list` stays empty; instead create a temp WIP commit on `Fixers` (`git add -A` of intended paths, `git commit -m "WIP"`) *or* export `git diff > session.patch` + keep `Archive/` (untracked parts persist across checkout anyway). Recommended: WIP commit — cheap, revertible, never pushed.
2. `git switch -c chore/repo-rationalization main` — new working branch off the source of truth.

**Phase B — drop duplicates, replay unique work**
3. Restore from the WIP commit ONLY the unique work: `Archive/` tree + the 86 renames (re-run `git mv` per `ARCHIVE_EXECUTION_REPORT.md` manifest), `docs/audits/*`, `.gitignore` Archive rules, `docs/SOURCE_OF_TRUTH.md` + `docs/CREAMS_SESSION_CURRENT.md` edits, manual-merged `CLAUDE.md`.
4. Explicitly do NOT carry over: composer.json/lock changes, both provider edits, `modern.blade.php` edit (main has all of them).
5. `composer install` against main's lock (platform pin 8.2.29 makes PHP 8.5 local tolerable) to eliminate the vendor drift properly.

**Phase C — verify**
6. `php artisan --version` and `php artisan route:list` (expect success — main already carries the fixes).
7. `php -d memory_limit=1G artisan test` — record numbers against `TEST_BASELINE.md` on main; annotate baseline doc with fresh, main-based results only.
8. Re-validate the three audit reports' statements that were Fixers-relative (deployment target → Hostinger; `public/letters` Manual-Review item → resolved by main; add an addendum noting both).
9. Confirm `git status` shows only intended changes; then commit (single `chore(repo)` commit or two as preferred) and open a PR to `main`.

**Phase D — deployment confirmation (manual)**
10. SSH to Hostinger: `git rev-parse HEAD` — confirm production == `origin/main` before any future deploy decisions.

## 8. RECOMMENDATION

**Option B: Main contains newer work → synchronize first.**

- `Fixers` is not diverged — it is simply 46 commits stale; nothing on it needs merging *into* main.
- This session's framework fixes are duplicates of main's and must be discarded, not committed.
- This session's unique, commit-worthy work is the **Archive/ rationalization + the four audit reports + governance-doc updates**, replayed on a fresh branch off `main` per the plan above.
- **No commits on `Fixers`. `main` becomes the development source of truth; `Fixers` should be retired after the sync.**
