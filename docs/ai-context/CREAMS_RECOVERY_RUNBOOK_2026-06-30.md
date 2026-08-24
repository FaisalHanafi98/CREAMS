# CREAMS — Post-Reset Recovery Runbook

> **Type**: Operational recovery runbook (single project: CREAMS)
> **Date**: 2026-06-30
> **Audience**: Implementation agent executing the recovery. Assume no prior conversation context.
> **Authority**: Owner (Faisal). Nothing in here is executed without the owner's go-ahead on the flagged decisions.
> **Governance**: Subordinate to root SOP and `CREAMS/CLAUDE.md`. PDPA applies.

---

## 0. Read this first — the three things that make this safe

1. **GitHub is the proven source of truth for CREAMS.** This was verified by fetch, not assumed (see §2). Do not "rescue" commits — there is nothing to rescue.
2. **The restored `.git` lied before the fetch.** Stale tracking refs reported `Fixers [ahead 115]` and `main [ahead 188, behind 18]`. After `git fetch`, both collapsed to `0/0`. **Never reason from ahead/behind numbers until you have fetched.**
3. **`IRL Files/` is protected confidential operational data — never delete, anonymise, or modify it.** See §4.

---

## 1. Scope and verdict

- **Scope**: CREAMS only. Do not touch other projects.
- **Verdict**: Commit graph is **Category A (local == remote)**. The only machine-local-only artifacts are 3 stashes and the dirty working tree. The recovery is small and low-risk **if** the freeze step (§5, Step 1) runs before any cleanup.
- **Remote**: `https://github.com/FaisalHanafi98/CREAMS.git`

---

## 2. Evidence snapshot (verified read-only on 2026-06-30)

| Claim | Command | Result |
|---|---|---|
| Local Fixers == remote | `git rev-list --left-right --count origin/Fixers...Fixers` | `0  0` (both at `7e1cb3d`) |
| Local main == remote | `git rev-list --left-right --count origin/main...main` | `0  0` (both at `82aabd1`) |
| No unpushed work anywhere | `git log --branches --not --remotes --oneline \| wc -l` | `0` |
| "ahead 115/188" was stale | `git fetch --all --prune` then re-check | collapsed to `0/0` |

**Branches**: local has `Fixers, dev, laughing-jones, main, reverent-sinoussi` (all 0 local-only commits). `origin` additionally has `Autocoder, Faisal, Man, master, chore/laravel-13-upgrade`, and a lowercase **`fixers`** (case-collision with `Fixers` — hazard on Windows).

**Note**: `origin/main` was **force-pushed** at some point (`+ dbb8440...82aabd1 main (forced update)`). Local and remote agree now. Flagged for owner (§7).

---

## 3. The actual local-only risk surface

### 3.1 Stashes (3) — local-only by nature; freeze before touching
| Stash | Base | Content (from `git stash show --stat`) | Disposition |
|---|---|---|---|
| `stash@{0}` | main | "remaining changes before Fixers switch" — untracking of `.env.example`, real-data files | Redundant cleanup snapshot |
| `stash@{1}` | main | "docs+automation consolidation work" — near-duplicate of `{0}` | Redundant cleanup snapshot |
| `stash@{2}` | Fixers | "pre-recovery-20260424-1404" — mass root-doc deletions **plus a ~159-line diff to `app/Http/Controllers/Activity/ActivityController.php`** and a small `MainController.php` edit | **REVIEW the ActivityController diff before dropping** — only artifact that may hold real work |

Dropping a stash does **not** delete any file on disk. The stash entries that show `IRL Files/...jpg → 0 bytes` are records of a prior *untracking* operation, not deletions of the files themselves.

### 3.2 Untracked "phantom" files — restore-resurrected ghosts, NOT lost work
The Google Drive restore re-created files that the `Fixers` branch deliberately deleted. **Do not `git add` these. Remove them.**

| File | Deleted by commit |
|---|---|
| `app/Http/Controllers/ClassController.php` | `9d23877` (PHANTOM-01 classes scaffold) |
| `app/Models/ClassModel.php` | `9d23877` (PHANTOM-01) |
| `app/Http/Controllers/LearningOutcomeController.php` | `fa2746a` (PHANTOM-02 learning-outcomes) |
| `app/Http/Controllers/Activity/SessionLearningOutcomeController.php` | `fa2746a` (PHANTOM-02) |

(If other untracked files under `app/` appear, check each with `git log --diff-filter=D --oneline -- <path>` before deciding. A non-empty result = it was intentionally deleted = remove, do not re-add.)

### 3.3 Repo-root contamination — personal files, safe to relocate out of repo
`Leetcode Exercises.txt`, `JAVA Bootcamp Day 2.txt`, `Shorts Tips Template.txt`, `Tips and tricks from shorts.txt`, `Recruitment Automation.txt`, `Claude related/`, and similar non-CREAMS notes. **Move out of the repo** (do not commit). These are unrelated to CREAMS.

### 3.4 Working tree — ~132 tracked changes, ~7,804 untracked non-ignored files
`HEAD == origin/Fixers`, so every tracked modification is either a deliberate post-restore edit or restore noise. **Diff before deciding** (§5, Step 4). Do not blanket-discard or blanket-commit.

---

## 4. PROTECTED DATA — `IRL Files/`

`IRL Files/` contains real, authorised, confidential operational records from the rehabilitation centre (pre-digitalisation attendance, forms, photos). The owner is explicitly authorised to hold and use them for CREAMS development.

**Rules — non-negotiable:**
- **Do not delete, anonymise, move, rename, or modify** any file under `IRL Files/` unless the owner explicitly requests it.
- **Keep them on disk, untracked/gitignored** (the existing decision in commit `99f134c Security(Data): Untrack real PDK files`). Preservation and git-exclusion are independent goals and both must hold: the files stay; they are **not** pushed to GitHub.
- When referencing them, **summarise — do not reproduce PII** (names, ICs, addresses, invoice contents) unless strictly required for debugging.
- Treat the same way any other real-data files (e.g. `PDK Bilal.xlsx`-derived invoice PDFs): preserve on disk, keep untracked, never push.

Before any cleanup command, confirm `IRL Files/` and other real-data paths are matched by `.gitignore` so they are never staged:
```
git check-ignore -v "IRL Files" || echo "WARNING: IRL Files NOT ignored — add to .gitignore before any git add"
```

---

## 5. Recovery sequence (run in order; STOP at each gate)

### Step 1 — FREEZE (mandatory, before anything else)
Make an immutable backup so any later cleanup is reversible. This covers untracked data including `IRL Files/`.
```
# Full folder copy (primary freeze). Deps can be excluded — they are reinstallable.
robocopy "." "..\CREAMS_RECOVERY_FREEZE_2026-06-30" /E /XD node_modules vendor /XJ
# Belt-and-suspenders: make the 3 stashes reachable, then bundle the whole history.
git tag recovery-stash-0 "stash@{0}"
git tag recovery-stash-1 "stash@{1}"
git tag recovery-stash-2 "stash@{2}"
git bundle create "..\CREAMS_RECOVERY_FREEZE_2026-06-30\creams-all.bundle" --all
```
**GATE**: confirm the freeze folder and bundle exist and are non-empty before continuing.

### Step 2 — Fetch and confirm parity (re-verify, do not trust prior state)
```
git fetch --all --prune
git rev-list --left-right --count origin/Fixers...Fixers   # expect 0  0
git rev-list --left-right --count origin/main...main        # expect 0  0
git log --branches --not --remotes --oneline                # expect empty
```
**GATE**: if any of these are non-zero/non-empty, STOP and report — the world changed since this runbook was written; re-audit before proceeding.

### Step 3 — Review the one stash that may hold work
```
git stash show -p "stash@{2}" -- app/Http/Controllers/Activity/ActivityController.php
git diff -- app/Http/Controllers/Activity/ActivityController.php   # compare to current working tree
```
**GATE (owner decision)**: keep, apply, or discard the ActivityController change. Do not drop any stash until this is decided. `stash@{0}` and `stash@{1}` are redundant cleanup snapshots and can be dropped after this review.

### Step 4 — Characterise the dirty working tree (decide, don't assume)
```
git status --porcelain | grep -v '^??'        # tracked modifications/deletions (~132)
git diff --stat                                # magnitude per file
```
Classify each tracked change as: (a) deliberate post-restore edit → keep, or (b) restore residue re-touching files already cleaned by `7e1cb3d` → discard. **Owner confirms** the keep/discard split before any `git checkout`/`git restore`.

### Step 5 — Remove the phantom ghosts (§3.2)
Delete the resurrected, intentionally-deleted files. Example:
```
rm "app/Http/Controllers/ClassController.php" \
   "app/Models/ClassModel.php" \
   "app/Http/Controllers/LearningOutcomeController.php" \
   "app/Http/Controllers/Activity/SessionLearningOutcomeController.php"
```
(Verify each first with `git log --diff-filter=D --oneline -- <path>`.)

### Step 6 — Relocate personal-file contamination (§3.3)
Move non-CREAMS personal notes **out of the repo** (not delete-from-disk unless owner says so). Leave `IRL Files/` untouched.

### Step 7 — Confirm clean parity
After Steps 3–6, the working tree should contain only intended changes, and:
```
git status -sb        # Fixers, clean or only-intended changes, == origin/Fixers
```

---

## 6. Do-not list

- Do **not** `git push --force` / `git reset --hard origin/*` / `git clean -fdx` before Step 1 freeze.
- Do **not** `git stash clear` before Step 3.
- Do **not** `git add` the phantom controllers or `IRL Files/`.
- Do **not** delete, anonymise, or modify `IRL Files/`.
- Do **not** auto-resolve the `Fixers` / `fixers` case collision or prune remote branches without owner sign-off (§7).

---

## 7. Decisions required from owner (not automatable)

1. **Working tree (~132 files)**: which are intended edits vs restore residue? (Step 4)
2. **`stash@{2}` ActivityController diff**: keep / apply / discard? (Step 3)
3. **`origin/main` force-push**: was the history rewrite authorised? If not, investigate before trusting `origin/main`.
4. **Branch hygiene**: keep or prune `Autocoder`, `Faisal`, `Man`, `master`, `chore/laravel-13-upgrade`, and the lowercase `fixers` (resolve the case collision with `Fixers`).
5. **`chore/laravel-13-upgrade`**: is the Laravel 12→13 bump still in flight or abandoned?

---

## 8. Deferred (separate from recovery)

- **Hostinger production** is behind and was not updated pre-reset. Before redeploying, SSH in and run `git status` on the server to catch any un-versioned direct edits (shared hosting invites hot-fixes). Redeploy is a post-recovery task, not part of this runbook.
- General code-health / docs / dependency auditing is **out of scope** here. Recovery first; health later, as its own effort.

---

*Generated from a read-only git audit on 2026-06-30. All findings in §2–§3 are reproducible with the commands shown. [Assisted by AI, reviewed manually by Faisal]*
