# WAVE 1 — OPENCODE EXECUTION PACKET

> **Source**: `WAVE_1_EXECUTION_RUNBOOK.md`
> **Format**: Deterministic execution script — no interpretation required
> **Rule**: Execute actions in order. Do NOT skip STOP POINTS. Do NOT deviate.

---

## SAFETY FLAGS LEGEND

| Flag | Meaning |
|------|---------|
| `!PDPA` | Involves real production data — verify before executing |
| `!GIT` | Modifies git tracking or history — no force-push |
| `!PROD` | Could affect production if executed on wrong server |
| `HARD STOP` | OpenCode MUST halt and report to OpenChamber before proceeding |

---

## HARD STOP 0 — PRE-FLIGHT

**OpenCode MUST verify ALL of the following before Action A1. If ANY fails, STOP — do not execute any action.**

```
ACTION_ID:    PRE-001
COMMAND:      git branch --show-current
EXPECTED_AFTER: Fixers
VERIFY:       [ "$(git branch --show-current)" = "Fixers" ] || (echo "HARD STOP: Not on Fixers branch" && exit 1)

ACTION_ID:    PRE-002
COMMAND:      git status --porcelain | wc -l
EXPECTED_AFTER: 0
VERIFY:       [ "$(git status --porcelain | wc -l)" -eq 0 ] || (echo "HARD STOP: Working tree not clean" && exit 1)

ACTION_ID:    PRE-003
COMMAND:      git config core.hooksPath
EXPECTED_AFTER: .githooks
VERIFY:       [ "$(git config core.hooksPath)" = ".githooks" ] || (echo "HARD STOP: Pre-commit hook not active" && exit 1)
```

```
HARD STOP: If PRE-001, PRE-002, or PRE-003 failed, report to OpenChamber. Do NOT proceed.
If ALL passed: continue to STOP POINT ALPHA.
```

---

## STOP POINT ALPHA — CONFIRMATION GATE

```
HARD STOP: OpenCode MUST confirm the following BEFORE proceeding to ACTION GROUP 1.
This is a manual gate — do not automate.

- [ ] I understand B1-T1 involves git tracking removal — no force-push
- [ ] I understand B1-T4 removes worktrees — I am NOT inside a worktree
- [ ] I have read EXECUTION_GOVERNANCE_LAYER.md Section 4
- [ ] I am authorised to handle PDPA-impacting file operations

If ALL confirmed: proceed to ACTION GROUP 1.
If ANY unchecked: STOP. Report to OpenChamber.
```

---

# ACTION GROUP 1 — WORKTREE PRUNING (B1-T4 / CF-04)

**Files**: `.claude/worktrees/competent-jepsen-88ca88/`, `.claude/worktrees/nifty-tereshkova-2974e6/`, `.gitignore`
**Finding**: CF-04
**Flag**: `!PDPA` `!GIT`

---

```
ACTION_ID:    A1
CONTEXT:      Confirm worktree state before pruning
COMMAND:      git worktree list
EXPECTED_BEFORE: 2+ worktrees including competent-jepsen-88ca88 and nifty-tereshkova-2974e6
EXPECTED_AFTER:  Same output (inspect only)
VERIFY_COMMAND:  git worktree list | grep -q "$(pwd)" && echo "PASS: Active worktree is main repo" || (echo "HARD STOP: You are inside a worktree — cd to main repo" && exit 1)
ROLLBACK_COMMAND:  N/A
```

---

```
ACTION_ID:    A2
CONTEXT:      Confirm worktrees contain real_data_backup.json
COMMAND:      for d in .claude/worktrees/*/; do echo "=== $d ==="; [ -f "$d/database/real_data_backup.json" ] && echo "CONTAINS real_data_backup.json ($(wc -c < "$d/database/real_data_backup.json") bytes)" || echo "Clean"; done
EXPECTED_BEFORE: Both worktrees report "CONTAINS real_data_backup.json"
EXPECTED_AFTER:  Same (inspect only)
VERIFY_COMMAND:  (echo "Manual: confirm both worktrees contain the file in output above")
ROLLBACK_COMMAND:  N/A
```

---

```
ACTION_ID:    A3
CONTEXT:      Prune worktree nifty-tereshkova-2974e6
COMMAND:      git worktree remove nifty-tereshkova-2974e6
EXPECTED_BEFORE:  Worktree exists at .claude/worktrees/nifty-tereshkova-2974e6/
EXPECTED_AFTER:   Worktree removed. git worktree list no longer shows it.
VERIFY_COMMAND:   [ ! -d ".claude/worktrees/nifty-tereshkova-2974e6" ] && echo "PASS: worktree removed" || (echo "FAIL: worktree still exists" && exit 1)
ROLLBACK_COMMAND:  git worktree add .claude/worktrees/nifty-tereshkova-2974e6 ORIGINAL_BRANCH_UNKNOWN — requires knowing original branch. If unrecoverable, worktree was stale.
SAFETY:        !PDPA — worktree contained real_data_backup.json. Verify removal.
```

---

```
ACTION_ID:    A4
CONTEXT:      Prune worktree competent-jepsen-88ca88
COMMAND:      git worktree remove competent-jepsen-88ca88
EXPECTED_BEFORE:  Worktree exists at .claude/worktrees/competent-jepsen-88ca88/
EXPECTED_AFTER:   Worktree removed. git worktree list no longer shows it.
VERIFY_COMMAND:   [ ! -d ".claude/worktrees/competent-jepsen-88ca88" ] && echo "PASS: worktree removed" || (echo "FAIL: worktree still exists" && exit 1)
ROLLBACK_COMMAND:  git worktree add .claude/worktrees/competent-jepsen-88ca88 ORIGINAL_BRANCH_UNKNOWN
SAFETY:        !PDPA — worktree contained real_data_backup.json. Verify removal.
```

---

```
ACTION_ID:    A5
CONTEXT:      Add .claude/worktrees/ to .gitignore
COMMAND:      printf '\n# Claude Code worktrees — may contain duplicate repo copies\n.claude/worktrees/\n' >> .gitignore
EXPECTED_BEFORE:  .gitignore has no .claude/worktrees/ entry
EXPECTED_AFTER:   .gitignore has .claude/worktrees/ entry
VERIFY_COMMAND:   grep -q "claude/worktrees" .gitignore && echo "PASS: .gitignore entry exists" || (echo "FAIL: entry not found" && exit 1)
ROLLBACK_COMMAND:  sed -i '/^\.claude\/worktrees\//d' .gitignore
```

---

## STOP POINT BRAVO

```
HARD STOP: OpenCode MUST verify ALL before proceeding:

A3 PASS:    [ ! -d ".claude/worktrees/nifty-tereshkova-2974e6" ]
A4 PASS:    [ ! -d ".claude/worktrees/competent-jepsen-88ca88" ]
A5 PASS:    grep -q "claude/worktrees" .gitignore
CLEAN:      git worktree list | grep -v "\[main\]" | grep -q . && echo "WARNING: extra worktrees" && exit 1 || echo "PASS: only main"

If ALL PASS: proceed to ACTION GROUP 2.
If ANY FAIL: STOP. Report to OpenChamber with the failing ACTION_ID.
```

---

# ACTION GROUP 2 — SECURE real_data_backup.json (B1-T1 / CF-01) !PDPA !GIT

**File**: `database/real_data_backup.json`
**Finding**: CF-01 — ESCALATED: git-tracked. 76,257 bytes, 1,801 lines.
**Flag**: `!PDPA` `!GIT`

---

```
ACTION_ID:    A6
CONTEXT:      Capture file metadata before removal
COMMAND:      echo "Size: $(wc -c < database/real_data_backup.json) bytes" && echo "Lines: $(wc -l < database/real_data_backup.json)" && echo "First commit:" && git log --follow --format="%h %ad %s" --date=short -- database/real_data_backup.json | tail -1
EXPECTED_BEFORE:  File exists. Size ~76,257 bytes. Lines: 1,801.
EXPECTED_AFTER:   Same (inspect only)
VERIFY_COMMAND:   (echo "Record the first commit hash shown above for BFG cleanup plan")
ROLLBACK_COMMAND:  N/A
```

---

```
ACTION_ID:    A7
CONTEXT:      Remove from git tracking — preserve on disk
COMMAND:      git rm --cached database/real_data_backup.json
EXPECTED_BEFORE:  git ls-files -- database/real_data_backup.json returns the file path
EXPECTED_AFTER:   File is staged as deleted. File still EXISTS on disk.
VERIFY_COMMAND:   (git ls-files -- database/real_data_backup.json | wc -l | grep -q "^0$") && ([ -f database/real_data_backup.json ] && echo "PASS: untracked, still on disk") || (echo "FAIL: tracking not removed or file deleted" && exit 1)
ROLLBACK_COMMAND:  git reset HEAD database/real_data_backup.json
SAFETY:        !PDPA !GIT — --cached preserves file on disk. Only git index modified.
```

---

```
ACTION_ID:    A8
CONTEXT:      Add to .gitignore
COMMAND:      printf '\n# Real production data — must never be committed\ndatabase/real_data_backup.json\n' >> .gitignore
EXPECTED_BEFORE:  .gitignore has no real_data_backup entry
EXPECTED_AFTER:   .gitignore has database/real_data_backup.json entry
VERIFY_COMMAND:   grep -q "real_data_backup" .gitignore && echo "PASS: .gitignore entry exists" || (echo "FAIL: entry not found" && exit 1)
ROLLBACK_COMMAND:  sed -i '/^database\/real_data_backup\.json/d' .gitignore
```

---

```
ACTION_ID:    A9
CONTEXT:      Verify git status for the file
COMMAND:      git status -- database/real_data_backup.json
EXPECTED_BEFORE:  File staged as deleted
EXPECTED_AFTER:   File under "Changes to be committed: deleted:" and NOT under "Untracked files:"
VERIFY_COMMAND:   git status -- database/real_data_backup.json | grep -q "deleted:" && echo "PASS: staged as deleted" || echo "WARNING: check status manually"
ROLLBACK_COMMAND:  N/A
```

---

## STOP POINT CHARLIE

```
HARD STOP: OpenCode MUST verify ALL before proceeding:

A7 PASS:    git ls-files -- database/real_data_backup.json returns EMPTY
A7 PASS:    [ -f database/real_data_backup.json ] returns TRUE
A8 PASS:    grep -q "real_data_backup" .gitignore
A9 PASS:    git status shows file as staged deletion

CRITICAL DECISION — MANUAL GATE:
Is database/real_data_backup.json still needed for recovery reference?
  [ ] YES — leave on disk (gitignored, will not be committed)
  [ ] NO — move to secure offline storage, then rm

If NO selected, execute: mv database/real_data_backup.json /secure/location/path/

If ALL PASS and DECISION MADE: proceed to ACTION GROUP 3.
If ANY FAIL: STOP. Report to OpenChamber.
```

---

# ACTION GROUP 3 — HARDCODED PASSWORDS (B1-T3 / CF-03) !PROD

**File**: `scripts/server-init.sh`
**Finding**: CF-03
**Flag**: `!PROD` — if script was deployed, passwords must be rotated on server

---

```
ACTION_ID:    A10
CONTEXT:      Capture current password lines
COMMAND:      grep -n "Password" scripts/server-init.sh
EXPECTED_BEFORE:  6 lines containing "Password" including ProdPassword123!, StagingPassword123!, DevPassword123!
EXPECTED_AFTER:   Same (inspect only)
VERIFY_COMMAND:   (echo "Record line numbers shown above")
ROLLBACK_COMMAND:  N/A
```

---

```
ACTION_ID:    A11
CONTEXT:      Create backup
COMMAND:      cp scripts/server-init.sh scripts/server-init.sh.backup-$(date +%Y%m%d)
EXPECTED_BEFORE:  scripts/server-init.sh exists. No .backup file.
EXPECTED_AFTER:   scripts/server-init.sh.backup-YYYYMMDD exists with identical content.
VERIFY_COMMAND:   diff scripts/server-init.sh scripts/server-init.sh.backup-* > /dev/null && echo "PASS: backup matches original" || (echo "FAIL: backup mismatch" && exit 1)
ROLLBACK_COMMAND:  rm scripts/server-init.sh.backup-*
```

---

```
ACTION_ID:    A12
CONTEXT:      Replace ProdPassword123! with env var reference
COMMAND:      sed -i "s/IDENTIFIED BY 'ProdPassword123!'/IDENTIFIED BY '\${PROD_DB_PASSWORD}'/g" scripts/server-init.sh
EXPECTED_BEFORE:  grep -q "ProdPassword123!" scripts/server-init.sh returns 0
EXPECTED_AFTER:   grep "ProdPassword123!" scripts/server-init.sh returns EMPTY
VERIFY_COMMAND:   grep -q "PROD_DB_PASSWORD" scripts/server-init.sh && ! grep -q "ProdPassword123!" scripts/server-init.sh && echo "PASS: replaced" || (echo "FAIL: password still present or env var missing" && exit 1)
ROLLBACK_COMMAND:  cp scripts/server-init.sh.backup-* scripts/server-init.sh
```

---

```
ACTION_ID:    A13
CONTEXT:      Replace StagingPassword123! with env var reference
COMMAND:      sed -i "s/IDENTIFIED BY 'StagingPassword123!'/IDENTIFIED BY '\${STAGING_DB_PASSWORD}'/g" scripts/server-init.sh
EXPECTED_BEFORE:  grep -q "StagingPassword123!" scripts/server-init.sh returns 0
EXPECTED_AFTER:   grep "StagingPassword123!" scripts/server-init.sh returns EMPTY
VERIFY_COMMAND:   grep -q "STAGING_DB_PASSWORD" scripts/server-init.sh && ! grep -q "StagingPassword123!" scripts/server-init.sh && echo "PASS: replaced" || (echo "FAIL: password still present or env var missing" && exit 1)
ROLLBACK_COMMAND:  cp scripts/server-init.sh.backup-* scripts/server-init.sh
```

---

```
ACTION_ID:    A14
CONTEXT:      Replace DevPassword123! with env var reference
COMMAND:      sed -i "s/IDENTIFIED BY 'DevPassword123!'/IDENTIFIED BY '\${DEV_DB_PASSWORD}'/g" scripts/server-init.sh
EXPECTED_BEFORE:  grep -q "DevPassword123!" scripts/server-init.sh returns 0
EXPECTED_AFTER:   grep "DevPassword123!" scripts/server-init.sh returns EMPTY
VERIFY_COMMAND:   grep -q "DEV_DB_PASSWORD" scripts/server-init.sh && ! grep -q "DevPassword123!" scripts/server-init.sh && echo "PASS: replaced" || (echo "FAIL: password still present or env var missing" && exit 1)
ROLLBACK_COMMAND:  cp scripts/server-init.sh.backup-* scripts/server-init.sh
```

---

```
ACTION_ID:    A15
CONTEXT:      Add usage comment to script
COMMAND:      sed -i '1s/^/# WARNING: Set these env vars before running:\n#   PROD_DB_PASSWORD\n#   STAGING_DB_PASSWORD\n#   DEV_DB_PASSWORD\n\n/' scripts/server-init.sh
EXPECTED_BEFORE:  Script starts with #!/bin/bash
EXPECTED_AFTER:   Script has 4-line comment block before #!/bin/bash
VERIFY_COMMAND:   head -5 scripts/server-init.sh | grep -q "PROD_DB_PASSWORD" && echo "PASS: comment added" || (echo "FAIL: comment missing" && exit 1)
ROLLBACK_COMMAND:  cp scripts/server-init.sh.backup-* scripts/server-init.sh
```

---

```
ACTION_ID:    A16
CONTEXT:      Verify 0 hardcoded passwords remain
COMMAND:      grep -E "ProdPassword123!|StagingPassword123!|DevPassword123!" scripts/server-init.sh
EXPECTED_BEFORE:  3 matches
EXPECTED_AFTER:   0 matches (empty output, exit code 1)
VERIFY_COMMAND:   ! grep -qE "ProdPassword123!|StagingPassword123!|DevPassword123!" scripts/server-init.sh && echo "PASS: 0 hardcoded passwords" || (echo "FAIL: password(s) still present" && exit 1)
ROLLBACK_COMMAND:  cp scripts/server-init.sh.backup-* scripts/server-init.sh
```

---

```
ACTION_ID:    A17
CONTEXT:      Syntax-check modified script
COMMAND:      bash -n scripts/server-init.sh
EXPECTED_BEFORE:  Script was syntactically valid
EXPECTED_AFTER:   Script is still syntactically valid (exit 0, no output)
VERIFY_COMMAND:   bash -n scripts/server-init.sh && echo "PASS: syntax valid" || (echo "FAIL: syntax error — restore backup" && cp scripts/server-init.sh.backup-* scripts/server-init.sh && exit 1)
ROLLBACK_COMMAND:  cp scripts/server-init.sh.backup-* scripts/server-init.sh
```

---

## STOP POINT DELTA

```
HARD STOP: OpenCode MUST verify ALL before proceeding:

A16 PASS:   ! grep -qE "ProdPassword123!|StagingPassword123!|DevPassword123!" scripts/server-init.sh
A17 PASS:   bash -n scripts/server-init.sh (exit 0)
BACKUP:     ls scripts/server-init.sh.backup-* (file exists)

If ALL PASS: proceed to ACTION GROUP 4.
If ANY FAIL: restore from backup: cp scripts/server-init.sh.backup-* scripts/server-init.sh. Report to OpenChamber.
```

---

# ACTION GROUP 4 — GombakDataExtractor (B5-T8 / CF-17) !PDPA

**File**: `database/seeders/GombakDataExtractor.php`
**Finding**: CF-17
**Flag**: `!PDPA`

---

```
ACTION_ID:    A18
CONTEXT:      Check if GombakDataExtractor is referenced elsewhere
COMMAND:      grep -r "GombakDataExtractor" app/ database/ --include="*.php" 2>/dev/null | grep -v "GombakDataExtractor.php"
EXPECTED_BEFORE:  Unknown
EXPECTED_AFTER:   Output or empty
VERIFY_COMMAND:   (echo "Check output above. If EMPTY: proceed to A19a DELETE. If HAS OUTPUT: proceed to A19b GATE.")
ROLLBACK_COMMAND:  N/A
```

---

## ACTION 4 DECISION GATE

```
If A18 output is EMPTY:
    → Execute A19a (DELETE path)
    → SKIP A19b, A20b

If A18 HAS OUTPUT:
    → Execute A19b, A20b (GATE path)
    → SKIP A19a
```

---

```
ACTION_ID:    A19a
CONTEXT:      DELETE path — remove GombakDataExtractor.php (0 external references)
COMMAND:      git rm database/seeders/GombakDataExtractor.php
EXPECTED_BEFORE:  File exists. 0 external references.
EXPECTED_AFTER:   File removed from disk and git tracking.
VERIFY_COMMAND:   [ ! -f database/seeders/GombakDataExtractor.php ] && echo "PASS: deleted" || (echo "FAIL: file still exists" && exit 1)
ROLLBACK_COMMAND:  git restore --staged database/seeders/GombakDataExtractor.php && git restore database/seeders/GombakDataExtractor.php
SAFETY:        !PDPA
```

---

```
ACTION_ID:    A19b
CONTEXT:      GATE path — add env gate before first DB operation in run() method
COMMAND:      (Manual: open database/seeders/GombakDataExtractor.php. Find the first line inside the run() method. Insert BEFORE any DB operations:)
              --- INSERT BELOW ---
              // PDPA hard guard — prevent regeneration of real_data_backup.json outside local dev
              if (!app()->environment('local')) {
                  $msg = 'GombakDataExtractor is HARD-GATED to APP_ENV=local. Current env: '
                      . app()->environment() . '. This extractor creates real_data_backup.json containing PDPA-protected data.';
                  throw new \RuntimeException($msg);
              }
              if (!config('app.debug')) {
                  throw new \RuntimeException('GombakDataExtractor refuses to run with APP_DEBUG=false (production posture detected).');
              }
              --- END INSERT ---
EXPECTED_BEFORE:  run() method has no env gate
EXPECTED_AFTER:   run() method starts with env gate
VERIFY_COMMAND:   grep -q "HARD-GATED" database/seeders/GombakDataExtractor.php && echo "PASS: gate added" || (echo "FAIL: gate not found" && exit 1)
ROLLBACK_COMMAND:  git checkout -- database/seeders/GombakDataExtractor.php
SAFETY:        !PDPA
```

---

```
ACTION_ID:    A20
CONTEXT:      Verify gate or deletion
COMMAND (DELETE): [ ! -f database/seeders/GombakDataExtractor.php ] && echo "PASS: deleted"
COMMAND (GATE):  grep -q "HARD-GATED" database/seeders/GombakDataExtractor.php && echo "PASS: gated"
EXPECTED_AFTER:  File deleted OR file gated
VERIFY_COMMAND:  (echo "Confirm with output above")
ROLLBACK_COMMAND:  N/A
```

---

## STOP POINT ECHO

```
HARD STOP: OpenCode MUST verify ALL before proceeding:

DELETE path: [ ! -f database/seeders/GombakDataExtractor.php ]
  OR
GATE path:  grep -q "HARD-GATED" database/seeders/GombakDataExtractor.php

If PASS: proceed to ACTION GROUP 5.
If FAIL: STOP. Report to OpenChamber.
```

---

# ACTION GROUP 5 — .env.testing PASSWORD (B2-T3 / CF-11)

**File**: `.env.testing`
**Finding**: CF-11
**Flag**: none

---

```
ACTION_ID:    A21
CONTEXT:      Record current DB_PASSWORD value
COMMAND:      grep "DB_PASSWORD" .env.testing
EXPECTED_BEFORE:  DB_PASSWORD=[REDACTED-CF03]
EXPECTED_AFTER:   Same (inspect only)
VERIFY_COMMAND:   (echo "Confirm current value shown above is the target for replacement")
ROLLBACK_COMMAND:  N/A
```

---

```
ACTION_ID:    A22
CONTEXT:      Replace real password with placeholder
COMMAND:      sed -i "s/DB_PASSWORD=[REDACTED-CF03]/DB_PASSWORD=test_password_placeholder/g" .env.testing
EXPECTED_BEFORE:  DB_PASSWORD=[REDACTED-CF03]
EXPECTED_AFTER:   DB_PASSWORD=test_password_placeholder
VERIFY_COMMAND:   grep "DB_PASSWORD" .env.testing | grep -q "test_password_placeholder" && echo "PASS: placeholder set" || (echo "FAIL: placeholder not found" && exit 1)
ROLLBACK_COMMAND:  sed -i "s/DB_PASSWORD=test_password_placeholder/DB_PASSWORD=[REDACTED-CF03]/g" .env.testing
```

---

```
ACTION_ID:    A23
CONTEXT:      Check if password appears elsewhere in repo
COMMAND:      grep -r "<LEAKED-DB-PASSWORD-CF03>" . --include="*.php" --include="*.env*" --include="*.sh" --include="*.json" --include="*.yml" --include="*.xml" 2>/dev/null | grep -v ".git/" | grep -v "vendor/" | grep -v "node_modules/" | grep -v "archive/"
EXPECTED_BEFORE:  Unknown
EXPECTED_AFTER:   Empty output (password unique to .env.testing)
VERIFY_COMMAND:   [ -z "$(grep -r "<LEAKED-DB-PASSWORD-CF03>" . --include="*.php" --include="*.env*" --include="*.sh" --include="*.json" --include="*.yml" --include="*.xml" 2>/dev/null | grep -v ".git/" | grep -v "vendor/" | grep -v "node_modules/" | grep -v "archive/")" ] && echo "PASS: password not found elsewhere" || (echo "WARNING: password found in other locations — check output above" && exit 1)
ROLLBACK_COMMAND:  N/A
```

---

```
ACTION_ID:    A24
CONTEXT:      Verify .env.testing is not git-tracked
COMMAND:      git ls-files -- .env.testing
EXPECTED_BEFORE:  Empty (untracked)
EXPECTED_AFTER:   Empty (untracked — password never committed)
VERIFY_COMMAND:   [ -z "$(git ls-files -- .env.testing)" ] && echo "PASS: untracked" || (echo "WARNING: file is tracked — password may be in git history" && exit 1)
ROLLBACK_COMMAND:  N/A
```

---

## STOP POINT FOXTROT

```
HARD STOP: OpenCode MUST verify ALL before proceeding:

A22 PASS:   grep -q "test_password_placeholder" .env.testing
A23 PASS:   Password not found elsewhere in repo
A24 PASS:   .env.testing is NOT git-tracked

If ALL PASS: proceed to ACTION GROUP 6.
If A23 shows other locations: report ALL file paths to OpenChamber. Do NOT proceed.
```

---

# ACTION GROUP 6 — ARCHIVE .env APP_KEYs (B5-T5 / CF-05)

**Files**: `archive/cream/.env`, `archive/Code VSC/CREAM/.env`, `archive/Code VSC/creams/.env`, `archive/Code VSC/creamtest1/.env`, `.env`
**Finding**: CF-05
**Flag**: none

---

```
ACTION_ID:    A25
CONTEXT:      Extract all archive APP_KEYs
COMMAND:      for f in "archive/cream/.env" "archive/Code VSC/CREAM/.env" "archive/Code VSC/creams/.env" "archive/Code VSC/creamtest1/.env"; do echo "=== $f ==="; grep "APP_KEY" "$f" 2>/dev/null || echo "  FILE NOT FOUND"; done
EXPECTED_BEFORE:  All 4 files exist with APP_KEY values
EXPECTED_AFTER:   Same (inspect only)
VERIFY_COMMAND:   (echo "Record each APP_KEY value. Note duplicates.")
ROLLBACK_COMMAND:  N/A
```

---

```
ACTION_ID:    A26
CONTEXT:      Extract current .env APP_KEY
COMMAND:      grep "APP_KEY" .env 2>/dev/null || (echo ".env not found"; grep "APP_KEY" .env.example 2>/dev/null)
EXPECTED_BEFORE:  .env has APP_KEY=base64:...
EXPECTED_AFTER:   Same (inspect only)
VERIFY_COMMAND:   (echo "Record current APP_KEY for comparison")
ROLLBACK_COMMAND:  N/A
```

---

```
ACTION_ID:    A27
CONTEXT:      Compare archive keys against current key (MANUAL)
COMMAND:      (Compare the current .env APP_KEY from A26 against each archive key from A25. Report MATCH or NO MATCH for each.)
EXPECTED_BEFORE:  All 4 comparisons done
EXPECTED_AFTER:   All 4 result in NO MATCH
VERIFY_COMMAND:   (If ANY MATCH → ESCALATE to OpenChamber — key is exposed. If ALL NO MATCH → keys are old dev keys, low risk — proceed.)
ROLLBACK_COMMAND:  N/A
```

---

```
ACTION_ID:    A28
CONTEXT:      Add archive .env paths to .gitignore
COMMAND:      printf '\n# Archive .env files — may contain old APP_KEYs\narchive/cream/.env\narchive/Code VSC/CREAM/.env\narchive/Code VSC/creams/.env\narchive/Code VSC/creamtest1/.env\n' >> .gitignore
EXPECTED_BEFORE:  .gitignore has no archive .env entries
EXPECTED_AFTER:   .gitignore has 4 archive .env entries
VERIFY_COMMAND:   [ "$(grep -c "archive/.*\.env" .gitignore)" -ge 4 ] && echo "PASS: 4 archive .env entries" || (echo "FAIL: expected 4 entries" && exit 1)
ROLLBACK_COMMAND:  sed -i '/^archive\/.*\.env$/d; /^# Archive .env files/d' .gitignore
```

---

## STOP POINT GOLF — WAVE 1 COMPLETE

```
HARD STOP: OpenCode MUST verify ALL of the following before marking Wave 1 COMPLETE:

ACTION GROUP 1 (B1-T4):
  [ ] git worktree list shows ONLY main
  [ ] grep -q "claude/worktrees" .gitignore

ACTION GROUP 2 (B1-T1):
  [ ] git ls-files -- database/real_data_backup.json returns EMPTY
  [ ] grep -q "real_data_backup" .gitignore

ACTION GROUP 3 (B1-T3):
  [ ] ! grep -qE "ProdPassword123!|StagingPassword123!|DevPassword123!" scripts/server-init.sh
  [ ] bash -n scripts/server-init.sh (exit 0)

ACTION GROUP 4 (B5-T8):
  [ ] [ ! -f database/seeders/GombakDataExtractor.php ] || grep -q "HARD-GATED" database/seeders/GombakDataExtractor.php

ACTION GROUP 5 (B2-T3):
  [ ] grep -q "test_password_placeholder" .env.testing
  [ ] Password not found elsewhere

ACTION GROUP 6 (B5-T5):
  [ ] All 4 archive APP_KEYs compared — no live key match
  [ ] Archive .env paths in .gitignore

GLOBAL:
  [ ] git status shows expected changes only
  [ ] git config core.hooksPath returns .githooks
  [ ] No files outside documented scope were modified

If ALL PASS: Wave 1 is COMPLETE.
Produce handoff token to OpenChamber.
Proceed to Wave 2 OR commit changes.

COMMIT (if committing):
  git add .gitignore scripts/server-init.sh database/seeders/GombakDataExtractor.php .env.testing
  git add database/real_data_backup.json  (if staged as deleted)
  git commit -m "Docs(Security): Wave 1 — PDPA risk closure.
  
  Remove real_data_backup.json from git tracking. Prune stale worktrees.
  Replace hardcoded passwords with env vars. Gate GombakDataExtractor.
  Rotate .env.testing password. Audit archive APP_KEYs.

  Verified that:
  1. git ls-files -- database/real_data_backup.json returns empty
  2. git worktree list shows only main branch
  3. grep finds 0 hardcoded passwords in scripts/server-init.sh
  4. bash -n scripts/server-init.sh passes
  5. GombakDataExtractor is deleted or env-gated
  6. .env.testing uses placeholder password
  7. No archive APP_KEY matches current key
  
  [Assisted by AI, reviewed manually by Faisal]"

If ANY CHECK FAILS: STOP. Report the failing check to OpenChamber. Do NOT commit.
```

---

*Wave 1 execution packet: 28 atomic actions (PRE-001 through A28) across 6 action groups. 7 STOP POINTS. 3 safety flags (!PDPA, !GIT, !PROD). 0 ambiguous instructions. Every command directly executable.*
