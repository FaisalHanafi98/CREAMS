# CREAMS — Wave 1 Execution Runbook

> **Wave**: 1 — P0 Critical PDPA Risk Closure
> **Source**: `EXECUTION_TRUTH_MATRIX.md`, `EXECUTION_MASTER_ROADMAP_V2.md`, `EXECUTION_GOVERNANCE_LAYER.md`
> **Status**: READY — all 6 tasks validated. No server access required. No code changes.
> **Risk**: LOW — file operations only.
> **Estimated Time**: 30-45 minutes.
> **Dependencies**: None. Start immediately.

---

## PRE-FLIGHT CHECKLIST

Execute ALL of the following before any action. If any check fails, STOP and resolve before proceeding.

```
[ ] Current branch is Fixers       →  git branch --show-current
[ ] Working tree is clean           →  git status --porcelain | wc -l  (must be 0)
[ ] Pre-commit hook active          →  git config core.hooksPath  (must return .githooks)
[ ] PHPUnit baseline ≥ 359          →  php artisan test | grep "PASSED"  (optional for Wave 1 — no code changes)
[ ] ADMIN_REVIEW: Are you the authorised operator for PDPA-impacting operations?  (yes/no — if no, STOP)
```

---

## STOP POINT ALPHA — Pre-Flight Gate

```
STOP and verify:
- [ ] All 5 pre-flight checks passed
- [ ] You understand that B1-T1 involves git history modification — no force-push
- [ ] You understand that B1-T4 removes worktrees — verify with `git worktree list` that you are on main worktree (not inside a worktree)
- [ ] You have read EXECUTION_GOVERNANCE_LAYER.md Section 4 (Data Protection Rules)

If ALL confirmed: proceed to ACTION GROUP 1.
```

---

# ACTION GROUP 1: Worktree Pruning (B1-T4 / CF-04)

**Goal**: Remove 2 stale worktrees that each contain a full copy of `database/real_data_backup.json`.

**Files involved**: `.claude/worktrees/competent-jepsen-88ca88/`, `.claude/worktrees/nifty-tereshkova-2974e6/`

**Evidence source**: CF-04 — Two full repo copies duplicating real_data_backup.json and .env variants.

---

## Action 1.1 — INSPECT: Confirm current worktree state

| Field | Value |
|-------|-------|
| **Operation Type** | INSPECT |
| **Command** | `git worktree list` |
| **Expected Before-State** | Output shows 2 or more worktrees: main + competent-jepsen-88ca88 + nifty-tereshkova-2974e6 |
| **Expected After-State** | Same as before-state (inspect only) |
| **Verification** | Confirm the active worktree (marked with `*`) is the main repo, NOT a `.claude/worktrees/` subdirectory. If you are INSIDE a worktree, STOP — `cd` to the main repo first. |
| **Rollback** | N/A — inspect only |

---

## Action 1.2 — INSPECT: Confirm worktrees contain real_data_backup.json

| Field | Value |
|-------|-------|
| **Operation Type** | INSPECT |
| **Command** | `for d in .claude/worktrees/*/; do echo "=== $d ==="; [ -f "$d/database/real_data_backup.json" ] && echo "  ⚠ CONTAINS real_data_backup.json ($(wc -c < "$d/database/real_data_backup.json") bytes)" || echo "  Clean"; done` |
| **Expected Before-State** | Both worktrees report "CONTAINS real_data_backup.json" |
| **Expected After-State** | Same (inspect only) |
| **Verification** | Both worktrees confirmed to contain the file. This is the reason for pruning — doubles CF-01 exposure. |
| **Rollback** | N/A — inspect only |

---

## Action 1.3 — EXECUTE: Prune worktree nifty-tereshkova-2974e6

| Field | Value |
|-------|-------|
| **Operation Type** | DELETE (git worktree remove) |
| **Command** | `git worktree remove nifty-tereshkova-2974e6` |
| **Expected Before-State** | Worktree exists at `.claude/worktrees/nifty-tereshkova-2974e6/` with 8,412 files |
| **Expected After-State** | Worktree directory removed. `git worktree list` no longer shows it. |
| **Verification** | `[ ! -d ".claude/worktrees/nifty-tereshkova-2974e6" ] && echo "VERIFIED: worktree removed"` |
| **Rollback** | `git worktree add .claude/worktrees/nifty-tereshkova-2974e6 <branch-name>` — requires knowing which branch it was on. If branch unknown, check `git branch -a` for matching name. If unrecoverable, the worktree was stale — no code lost. |
| **Risk** | LOW — worktree is a parallel checkout. Main repo is unaffected. |

---

## Action 1.4 — EXECUTE: Prune worktree competent-jepsen-88ca88

| Field | Value |
|-------|-------|
| **Operation Type** | DELETE (git worktree remove) |
| **Command** | `git worktree remove competent-jepsen-88ca88` |
| **Expected Before-State** | Worktree exists at `.claude/worktrees/competent-jepsen-88ca88/` with 8,242 files |
| **Expected After-State** | Worktree directory removed. `git worktree list` no longer shows it. |
| **Verification** | `[ ! -d ".claude/worktrees/competent-jepsen-88ca88" ] && echo "VERIFIED: worktree removed"` |
| **Rollback** | Same as Action 1.3. |
| **Risk** | LOW |

---

## Action 1.5 — EXECUTE: Add .claude/worktrees/ to .gitignore

| Field | Value |
|-------|-------|
| **Operation Type** | MODIFY (append to .gitignore) |
| **Command** | `echo "" >> .gitignore && echo "# Claude Code worktrees — may contain duplicate repo copies" >> .gitignore && echo ".claude/worktrees/" >> .gitignore` |
| **Expected Before-State** | `.gitignore` has no entry for `.claude/worktrees/` |
| **Expected After-State** | `.gitignore` has entry for `.claude/worktrees/` |
| **Verification** | `grep "claude/worktrees" .gitignore && echo "VERIFIED: .gitignore entry exists"` |
| **Rollback** | Edit `.gitignore` and remove the 3 added lines. |
| **Risk** | NONE — only adds an ignore rule. No files affected. |

---

## STOP POINT BRAVO — After Action Group 1

```
STOP and verify:
- [ ] `git worktree list` shows ONLY the main worktree
- [ ] `.claude/worktrees/` directory is empty or gone
- [ ] `.gitignore` has `.claude/worktrees/` entry
- [ ] `git status` shows `.gitignore` as modified (expected)

If ALL confirmed: proceed to ACTION GROUP 2.
```

---

# ACTION GROUP 2: Secure real_data_backup.json (B1-T1 / CF-01) ⚠ ESCALATED

**Goal**: Remove `database/real_data_backup.json` from git tracking. Add to `.gitignore`. File is 76,257 bytes, 1,801 lines, containing real Gombak centre data (1 centre, 14 users, 57 assets).

**⚠ ESCALATED**: This file is git-tracked — committed to history. Every clone has it. BFG history rewrite required in post-delivery phase. This action removes it from FUTURE commits but does NOT remove it from history.

**Files involved**: `database/real_data_backup.json`, `.gitignore`

**Evidence source**: CF-01 — Real production data backup on disk. Confirmed git-tracked by validation.

---

## Action 2.1 — INSPECT: Capture file metadata before removal

| Field | Value |
|-------|-------|
| **Operation Type** | INSPECT |
| **Command** | `echo "Size: $(wc -c < database/real_data_backup.json) bytes" && echo "Lines: $(wc -l < database/real_data_backup.json)" && echo "First committed: $(git log --follow --format="%h %ad %s" --date=short -- database/real_data_backup.json | tail -1)"` |
| **Expected Before-State** | File exists. Size ~76,257 bytes. Lines: 1,801. Git log shows first commit. |
| **Expected After-State** | Same (inspect only) |
| **Verification** | Record the first commit hash for the BFG cleanup plan. |
| **Rollback** | N/A — inspect only |

---

## Action 2.2 — EXECUTE: Remove from git tracking (preserve on disk)

| Field | Value |
|-------|-------|
| **Operation Type** | MODIFY (git rm --cached) |
| **Command** | `git rm --cached database/real_data_backup.json` |
| **Expected Before-State** | `git ls-files -- database/real_data_backup.json` returns the file path (tracked) |
| **Expected After-State** | `git ls-files -- database/real_data_backup.json` returns nothing (untracked). File still EXISTS on disk. |
| **Verification** | `git ls-files -- database/real_data_backup.json | wc -l` must return `0`. `[ -f database/real_data_backup.json ] && echo "File still on disk (expected)" || echo "WARNING: File deleted from disk"` |
| **Rollback** | `git reset HEAD database/real_data_backup.json` — restores tracking. |
| **Risk** | LOW — `--cached` flag preserves the file on disk. Only git index is modified. |

---

## Action 2.3 — EXECUTE: Add to .gitignore

| Field | Value |
|-------|-------|
| **Operation Type** | MODIFY (append to .gitignore) |
| **Command** | `echo "" >> .gitignore && echo "# Real production data — must never be committed" >> .gitignore && echo "database/real_data_backup.json" >> .gitignore` |
| **Expected Before-State** | `.gitignore` has no entry for `real_data_backup.json` |
| **Expected After-State** | `.gitignore` has entry for `database/real_data_backup.json` |
| **Verification** | `grep "real_data_backup" .gitignore && echo "VERIFIED: .gitignore entry exists"` |
| **Rollback** | Edit `.gitignore` and remove the 3 added lines. Re-add to git tracking if needed: `git add database/real_data_backup.json`. |
| **Risk** | NONE |

---

## Action 2.4 — VERIFY: Confirm git status is clean for this file

| Field | Value |
|-------|-------|
| **Operation Type** | VERIFY |
| **Command** | `git status -- database/real_data_backup.json` |
| **Expected Before-State** | File shows as `deleted: database/real_data_backup.json` in staged changes |
| **Expected After-State** | File is staged as deleted. Not in untracked files (because .gitignore now covers it). |
| **Verification** | File should appear under "Changes to be committed: deleted:" and NOT under "Untracked files:". If it appears under untracked, the .gitignore entry may not have taken effect — check the pattern. |
| **Rollback** | N/A — verification only |

---

## STOP POINT CHARLIE — After Action Group 2

```
STOP and verify:
- [ ] `git ls-files -- database/real_data_backup.json` returns EMPTY
- [ ] `[ -f database/real_data_backup.json ]` returns TRUE (file still on disk)
- [ ] `.gitignore` has `database/real_data_backup.json` entry
- [ ] `git status` shows the file as staged deletion

CRITICAL CHECK: Is the file still needed for recovery reference?
- [ ] YES — leave on disk. It is now gitignored and will not be committed.
- [ ] NO — move to secure offline storage, then delete from disk.

If YES selected: proceed to ACTION GROUP 3.
If NO selected: `mv database/real_data_backup.json /secure/location/` then proceed.
```

---

# ACTION GROUP 3: Remediate Hardcoded Passwords (B1-T3 / CF-03)

**Goal**: Replace hardcoded MySQL passwords in `scripts/server-init.sh` with environment variable references.

**Files involved**: `scripts/server-init.sh`

**Evidence source**: CF-03 — 3 hardcoded passwords: ProdPassword123!, StagingPassword123!, DevPassword123!

---

## Action 3.1 — INSPECT: Capture current password lines

| Field | Value |
|-------|-------|
| **Operation Type** | INSPECT |
| **Command** | `grep -n "Password" scripts/server-init.sh` |
| **Expected Before-State** | 6 lines containing "Password", including: `IDENTIFIED BY 'ProdPassword123!'`, `IDENTIFIED BY 'StagingPassword123!'`, `IDENTIFIED BY 'DevPassword123!'` |
| **Expected After-State** | Same (inspect only) |
| **Verification** | Count and record exact line numbers for each hardcoded password. |
| **Rollback** | N/A — inspect only |

---

## Action 3.2 — EXECUTE: Create backup of script

| Field | Value |
|-------|-------|
| **Operation Type** | COPY (backup) |
| **Command** | `cp scripts/server-init.sh scripts/server-init.sh.backup-$(date +%Y%m%d)` |
| **Expected Before-State** | `scripts/server-init.sh` exists. No `.backup-*` file. |
| **Expected After-State** | `scripts/server-init.sh.backup-YYYYMMDD` exists with identical content. |
| **Verification** | `diff scripts/server-init.sh scripts/server-init.sh.backup-*` returns no output. |
| **Rollback** | N/A — copy only |

---

## Action 3.3 — EXECUTE: Replace ProdPassword123! with env var reference

| Field | Value |
|-------|-------|
| **Operation Type** | MODIFY (sed substitution) |
| **Command** | `sed -i "s/IDENTIFIED BY 'ProdPassword123!'/IDENTIFIED BY '\${PROD_DB_PASSWORD}'/g" scripts/server-init.sh` |
| **Expected Before-State** | Line contains `IDENTIFIED BY 'ProdPassword123!'` |
| **Expected After-State** | Same line contains `IDENTIFIED BY '${PROD_DB_PASSWORD}'` |
| **Verification** | `grep "PROD_DB_PASSWORD" scripts/server-init.sh && echo "VERIFIED" ; grep "ProdPassword123!" scripts/server-init.sh && echo "WARNING: hardcoded password still present" || echo "VERIFIED: hardcoded password removed"` |
| **Rollback** | `cp scripts/server-init.sh.backup-* scripts/server-init.sh` |

---

## Action 3.4 — EXECUTE: Replace StagingPassword123! with env var reference

| Field | Value |
|-------|-------|
| **Operation Type** | MODIFY (sed substitution) |
| **Command** | `sed -i "s/IDENTIFIED BY 'StagingPassword123!'/IDENTIFIED BY '\${STAGING_DB_PASSWORD}'/g" scripts/server-init.sh` |
| **Expected Before-State** | Line contains `IDENTIFIED BY 'StagingPassword123!'` |
| **Expected After-State** | Same line contains `IDENTIFIED BY '${STAGING_DB_PASSWORD}'` |
| **Verification** | `grep "STAGING_DB_PASSWORD" scripts/server-init.sh && echo "VERIFIED" ; grep "StagingPassword123!" scripts/server-init.sh && echo "WARNING: hardcoded" || echo "VERIFIED: removed"` |
| **Rollback** | `cp scripts/server-init.sh.backup-* scripts/server-init.sh` |

---

## Action 3.5 — EXECUTE: Replace DevPassword123! with env var reference

| Field | Value |
|-------|-------|
| **Operation Type** | MODIFY (sed substitution) |
| **Command** | `sed -i "s/IDENTIFIED BY 'DevPassword123!'/IDENTIFIED BY '\${DEV_DB_PASSWORD}'/g" scripts/server-init.sh` |
| **Expected Before-State** | Line contains `IDENTIFIED BY 'DevPassword123!'` |
| **Expected After-State** | Same line contains `IDENTIFIED BY '${DEV_DB_PASSWORD}'` |
| **Verification** | `grep "DEV_DB_PASSWORD" scripts/server-init.sh && echo "VERIFIED" ; grep "DevPassword123!" scripts/server-init.sh && echo "WARNING: hardcoded" || echo "VERIFIED: removed"` |
| **Rollback** | `cp scripts/server-init.sh.backup-* scripts/server-init.sh` |

---

## Action 3.6 — EXECUTE: Add usage comment to script

| Field | Value |
|-------|-------|
| **Operation Type** | MODIFY (prepend comment) |
| **Command** | `sed -i '1i# WARNING: Set these env vars before running:\n#   PROD_DB_PASSWORD\n#   STAGING_DB_PASSWORD\n#   DEV_DB_PASSWORD\n' scripts/server-init.sh` |
| **Expected Before-State** | Script starts with `#!/bin/bash` |
| **Expected After-State** | Script has 4-line comment block before `#!/bin/bash` |
| **Verification** | `head -5 scripts/server-init.sh` shows the comment block. |
| **Rollback** | `cp scripts/server-init.sh.backup-* scripts/server-init.sh` |

---

## Action 3.7 — VERIFY: Confirm 0 hardcoded passwords remain

| Field | Value |
|-------|-------|
| **Operation Type** | VERIFY |
| **Command** | `grep -E "ProdPassword123!|StagingPassword123!|DevPassword123!" scripts/server-init.sh` |
| **Expected Before-State** | 3 matches |
| **Expected After-State** | 0 matches (empty output) |
| **Verification** | Output must be empty. If any match remains, re-run the relevant sed command (3.3, 3.4, or 3.5). |
| **Rollback** | N/A — verification only |

---

## Action 3.8 — VERIFY: Syntax-check the modified script

| Field | Value |
|-------|-------|
| **Operation Type** | VERIFY |
| **Command** | `bash -n scripts/server-init.sh` |
| **Expected Before-State** | Script was syntactically valid |
| **Expected After-State** | Script is still syntactically valid (no output = no errors) |
| **Verification** | `bash -n scripts/server-init.sh` returns exit code 0 with no output. |
| **Rollback** | If syntax error: `cp scripts/server-init.sh.backup-* scripts/server-init.sh`. Re-do substitutions manually with an editor to avoid sed escaping issues. |

---

## STOP POINT DELTA — After Action Group 3

```
STOP and verify:
- [ ] 0 hardcoded passwords in scripts/server-init.sh (Action 3.7 passed)
- [ ] Script is syntactically valid (Action 3.8 passed)
- [ ] Backup file created at scripts/server-init.sh.backup-YYYYMMDD
- [ ] Comment block added at top of script

If ALL confirmed: proceed to ACTION GROUP 4.
```

---

# ACTION GROUP 4: Gate GombakDataExtractor (B5-T8 / CF-17)

**Goal**: Add env gate to `database/seeders/GombakDataExtractor.php` matching the IRLSeeder pattern, or delete it if it has no remaining purpose.

**Files involved**: `database/seeders/GombakDataExtractor.php`

**Evidence source**: CF-17 — GombakDataExtractor can regenerate PDPA backup. No env gate.

---

## Action 4.1 — INSPECT: Check if GombakDataExtractor is referenced elsewhere

| Field | Value |
|-------|-------|
| **Operation Type** | INSPECT |
| **Command** | `grep -r "GombakDataExtractor" app/ database/ --include="*.php" | grep -v "GombakDataExtractor.php"` |
| **Expected Before-State** | Unknown — may have references in DatabaseSeeder.php or other seeders |
| **Expected After-State** | Same (inspect only) |
| **Verification** | If output is EMPTY: file has 0 external references — safe to delete OR gate. If output has references: gate only — do NOT delete (other code depends on it). Record which files reference it. |
| **Rollback** | N/A — inspect only |

---

## Action 4.2 — DECISION: Gate or Delete?

```
Based on Action 4.1:

IF 0 external references:
    → Proceed to Action 4.3a (DELETE)
    → Skip Action 4.3b

IF references found:
    → Proceed to Action 4.3b (GATE)
    → Skip Action 4.3a
```

---

## Action 4.3a — EXECUTE (DELETE path): Delete GombakDataExtractor.php

| Field | Value |
|-------|-------|
| **Operation Type** | DELETE (git rm) |
| **Command** | `git rm database/seeders/GombakDataExtractor.php` |
| **Expected Before-State** | File exists at `database/seeders/GombakDataExtractor.php` |
| **Expected After-State** | File removed from disk and git tracking |
| **Verification** | `[ ! -f database/seeders/GombakDataExtractor.php ] && echo "VERIFIED: file deleted"` |
| **Rollback** | `git restore --staged database/seeders/GombakDataExtractor.php && git restore database/seeders/GombakDataExtractor.php` |
| **Risk** | NONE — file has 0 external references, functionally dead code |

---

## Action 4.3b — EXECUTE (GATE path): Add env gate to GombakDataExtractor.php

| Field | Value |
|-------|-------|
| **Operation Type** | MODIFY (insert PHP code) |
| **Command** | Read the file first: `head -30 database/seeders/GombakDataExtractor.php`. Find the first line inside `run()` method (after `{`). Insert BEFORE any DB operations: |
| | ```php |
| | // PDPA hard guard — prevent regeneration of real_data_backup.json outside local dev |
| | if (!app()->environment('local')) { |
| |     $msg = 'GombakDataExtractor is HARD-GATED to APP_ENV=local. Current env: ' |
| |         . app()->environment() . '. This extractor creates real_data_backup.json containing PDPA-protected data.'; |
| |     throw new \RuntimeException($msg); |
| | } |
| | if (!config('app.debug')) { |
| |     throw new \RuntimeException('GombakDataExtractor refuses to run with APP_DEBUG=false (production posture detected).'); |
| | } |
| | ``` |
| **Expected Before-State** | `run()` method has no env gate |
| **Expected After-State** | `run()` method starts with env gate (matching IRLSeeder pattern) |
| **Verification** | `grep -A5 "HARD-GATED" database/seeders/GombakDataExtractor.php` returns the gate code. |
| **Rollback** | `cp` backup from before edit, or `git checkout -- database/seeders/GombakDataExtractor.php`. |
| **Risk** | LOW — additive code only. No existing behavior changed. |

---

## Action 4.4 — VERIFY: Confirm gate or deletion

| Field | Value |
|-------|-------|
| **Operation Type** | VERIFY |
| **Command (DELETE path)** | `[ ! -f database/seeders/GombakDataExtractor.php ] && echo "VERIFIED: deleted"` |
| **Command (GATE path)** | `grep "HARD-GATED" database/seeders/GombakDataExtractor.php && echo "VERIFIED: gated"` |
| **Expected After-State** | File deleted OR file gated |
| **Rollback** | N/A — verification only |

---

## STOP POINT ECHO — After Action Group 4

```
STOP and verify:
- [ ] GombakDataExtractor is either deleted (0 references) OR env-gated (has references)
- [ ] If gated: gate matches IRLSeeder pattern (local + APP_DEBUG checks)
- [ ] No new references to GombakDataExtractor were introduced

If ALL confirmed: proceed to ACTION GROUP 5.
```

---

# ACTION GROUP 5: Rotate .env.testing Password (B2-T3 / CF-11)

**Goal**: Replace the real database password in `.env.testing` with a placeholder.

**Files involved**: `.env.testing`

**Evidence source**: CF-11 — `.env.testing` contains `DB_PASSWORD=[REDACTED-CF03]`. File is untracked.

---

## Action 5.1 — INSPECT: Capture current value

| Field | Value |
|-------|-------|
| **Operation Type** | INSPECT |
| **Command** | `grep "DB_PASSWORD" .env.testing` |
| **Expected Before-State** | `DB_PASSWORD=[REDACTED-CF03]` |
| **Expected After-State** | Same (inspect only) |
| **Verification** | Record exact line for replacement. |
| **Rollback** | N/A — inspect only |

---

## Action 5.2 — EXECUTE: Replace password with placeholder

| Field | Value |
|-------|-------|
| **Operation Type** | MODIFY (sed substitution) |
| **Command** | `sed -i "s/DB_PASSWORD=[REDACTED-CF03]/DB_PASSWORD=test_password_placeholder/g" .env.testing` |
| **Expected Before-State** | `DB_PASSWORD=[REDACTED-CF03]` |
| **Expected After-State** | `DB_PASSWORD=test_password_placeholder` |
| **Verification** | `grep "DB_PASSWORD" .env.testing` returns `DB_PASSWORD=test_password_placeholder` |
| **Rollback** | `sed -i "s/DB_PASSWORD=test_password_placeholder/DB_PASSWORD=[REDACTED-CF03]/g" .env.testing` |

---

## Action 5.3 — INSPECT: Check if password appears elsewhere

| Field | Value |
|-------|-------|
| **Operation Type** | INSPECT |
| **Command** | `grep -r "<LEAKED-DB-PASSWORD-CF03>" . --include="*.php" --include="*.env*" --include="*.sh" --include="*.json" --include="*.yml" --include="*.xml" 2>/dev/null | grep -v ".git/" | grep -v "vendor/" | grep -v "node_modules/" | grep -v "archive/"` |
| **Expected Before-State** | Unknown |
| **Expected After-State** | Output must be EMPTY (password is unique to .env.testing). If found elsewhere, record locations — those must be rotated too. |
| **Verification** | `[ -z "$(grep -r "<LEAKED-DB-PASSWORD-CF03>" . --include="*.php" --include="*.env*" --include="*.sh" --include="*.json" --include="*.yml" --include="*.xml" 2>/dev/null | grep -v ".git/" | grep -v "vendor/" | grep -v "node_modules/" | grep -v "archive/")" ] && echo "VERIFIED: password not found elsewhere" || echo "WARNING: password found elsewhere — see output above"` |
| **Rollback** | N/A — inspect only |

---

## Action 5.4 — VERIFY: Confirm .env.testing is untracked

| Field | Value |
|-------|-------|
| **Operation Type** | VERIFY |
| **Command** | `git ls-files -- .env.testing` |
| **Expected After-State** | Empty output (file is NOT tracked by git) |
| **Verification** | `[ -z "$(git ls-files -- .env.testing)" ] && echo "VERIFIED: untracked — password not in git history" || echo "WARNING: file is tracked — password may be in git history"` |
| **Rollback** | N/A — verification only |

---

## STOP POINT FOXTROT — After Action Group 5

```
STOP and verify:
- [ ] .env.testing has placeholder password
- [ ] "<LEAKED-DB-PASSWORD-CF03>" not found elsewhere in repo
- [ ] .env.testing is NOT git-tracked
- [ ] 359 PHPUnit tests pass (optional — no code changes in this wave)

If ALL confirmed: proceed to ACTION GROUP 6.
```

---

# ACTION GROUP 6: Audit Archive .env APP_KEYs (B5-T5 / CF-05)

**Goal**: Compare 4 archive `.env` APP_KEYs against the current `.env` to verify no live key matches.

**Files involved**: `archive/cream/.env`, `archive/Code VSC/CREAM/.env`, `archive/Code VSC/creams/.env`, `archive/Code VSC/creamtest1/.env`, `.env`

**Evidence source**: CF-05 — 4 archive `.env` files with exposed APP_KEY values.

---

## Action 6.1 — INSPECT: Extract archive APP_KEYs

| Field | Value |
|-------|-------|
| **Operation Type** | INSPECT |
| **Command** | `for f in "archive/cream/.env" "archive/Code VSC/CREAM/.env" "archive/Code VSC/creams/.env" "archive/Code VSC/creamtest1/.env"; do echo "=== $f ==="; grep "APP_KEY" "$f" 2>/dev/null || echo "  FILE NOT FOUND"; done` |
| **Expected Before-State** | All 4 files exist. Two share identical keys (`archive/Code VSC/creams` and `archive/Code VSC/creamtest1`). |
| **Expected After-State** | Same (inspect only) |
| **Verification** | Record each APP_KEY value. Note which keys are duplicates. |
| **Rollback** | N/A — inspect only |

---

## Action 6.2 — INSPECT: Extract current .env APP_KEY

| Field | Value |
|-------|-------|
| **Operation Type** | INSPECT |
| **Command** | `grep "APP_KEY" .env 2>/dev/null || echo ".env not found — checking .env.example: " && grep "APP_KEY" .env.example 2>/dev/null` |
| **Expected Before-State** | `.env` has `APP_KEY=base64:...` (real key for local dev) |
| **Expected After-State** | Same (inspect only) |
| **Verification** | Record current APP_KEY for comparison. |
| **Rollback** | N/A — inspect only |

---

## Action 6.3 — VERIFY: Compare keys

| Field | Value |
|-------|-------|
| **Operation Type** | VERIFY (manual comparison) |
| **Command** | Compare the current `.env` APP_KEY against each of the 4 archive keys from Action 6.1. |
| **Expected After-State** | `MATCH` or `NO MATCH` for each archive key vs current key. |
| **Verification** | If ANY archive key matches the current `.env` APP_KEY: **ESCALATE** — the key is exposed. Plan rotation. If NO matches: keys are old dev keys, low risk. |
| **Rollback** | N/A — verification only |

---

## Action 6.4 — EXECUTE: Add archive .env paths to .gitignore

| Field | Value |
|-------|-------|
| **Operation Type** | MODIFY (append to .gitignore) |
| **Command** | `echo "" >> .gitignore && echo "# Archive .env files — may contain old APP_KEYs" >> .gitignore && echo "archive/cream/.env" >> .gitignore && echo "archive/Code VSC/CREAM/.env" >> .gitignore && echo "archive/Code VSC/creams/.env" >> .gitignore && echo "archive/Code VSC/creamtest1/.env" >> .gitignore` |
| **Expected Before-State** | `.gitignore` has no entries for archive .env files |
| **Expected After-State** | `.gitignore` has entries for all 4 archive .env files |
| **Verification** | `grep "archive/.*\.env" .gitignore | wc -l` returns `4` |
| **Rollback** | Edit `.gitignore` and remove the added lines. |

---

## STOP POINT GOLF — End of Wave 1

```
WAVE 1 COMPLETE — Verification Checklist:

ACTION GROUP 1 (B1-T4 / CF-04):
- [ ] 0 .claude/worktrees/ remaining (git worktree list shows only main)
- [ ] .gitignore has .claude/worktrees/ entry

ACTION GROUP 2 (B1-T1 / CF-01):
- [ ] real_data_backup.json removed from git tracking
- [ ] .gitignore has database/real_data_backup.json entry
- [ ] File still on disk (for recovery) OR moved to secure storage

ACTION GROUP 3 (B1-T3 / CF-03):
- [ ] 0 hardcoded passwords in scripts/server-init.sh
- [ ] Script is syntactically valid (bash -n passed)

ACTION GROUP 4 (B5-T8 / CF-17):
- [ ] GombakDataExtractor.php deleted OR env-gated

ACTION GROUP 5 (B2-T3 / CF-11):
- [ ] .env.testing has placeholder password
- [ ] Real password not found elsewhere in repo

ACTION GROUP 6 (B5-T5 / CF-05):
- [ ] All 4 archive APP_KEYs compared against current .env
- [ ] No live key matches found (or escalation logged if match found)
- [ ] Archive .env paths in .gitignore

GLOBAL CHECKS:
- [ ] git status shows expected changes only
- [ ] Pre-commit hook still active
- [ ] No new PDPA exposures discovered during execution
- [ ] No files outside documented scope were modified

If ALL confirmed: Wave 1 is COMPLETE.
Proceed to Wave 2 (EXECUTION_BATCH_02_P0_DEPLOYMENT.md) OR commit changes.

COMMIT MESSAGE (if committing):
Docs(Security): Wave 1 — PDPA risk closure.
Remove real_data_backup.json from git tracking. Prune stale worktrees.
Replace hardcoded passwords with env vars. Gate GombakDataExtractor.
Rotate .env.testing password. Audit archive APP_KEYs.
```

---

*Wave 1 runbook: 23 atomic actions across 6 action groups. 7 STOP points. Every action traceable to CF-01 through CF-04 + CF-05 + CF-11 + CF-17. No code changes. File operations only.*
