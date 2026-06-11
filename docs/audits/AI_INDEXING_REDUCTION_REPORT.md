# AI Indexing Reduction Report — CREAMS

> **Date**: 2026-06-11
> **Context**: OpenCode out-of-memory crashes, slow indexing, AI context pollution
> **Companion**: `REPOSITORY_GOVERNANCE_AUDIT.md`, `ARCHIVE_EXECUTION_REPORT.md`

---

## 1. Root-cause analysis of AI tooling problems

### 1.1 Out-of-memory / indexing load

AI coding tools that walk the working tree (with or without honoring `.gitignore`) encountered:

| Rank | Source | Size | Files | Why it hurts indexers |
|------|--------|------|-------|----------------------|
| 1 | `tests/Browser/test-results.json/` | 1.49 GB | 469 | ~75 binary `trace.zip` files of ~20 MB each; the directory's `.json` suffix makes naive indexers treat it as a JSON file/folder worth parsing |
| 2 | `storage/logs/` | 583 MB | ~30 | Single 40 MB plaintext log files get tokenized whole by context-grabbing tools |
| 3 | `docs/archive/audit_screenshots/` | 505 MB | 550 | PNG corpus inside `docs/`, the folder AI tools most eagerly index for context |
| 4 | `tests/Browser/playwright-report/` | 205 MB | 679 | Generated HTML with embedded base64 assets |
| 5 | `public/assets/` + `public/fonts/` | 92 MB | 7,098 | Vendored libraries and icon fonts — 7k small files dominate file-count-based indexers |
| 6 | `docs/archive/irl_reference_materials/` + `IRL Files/` | 128 MB | 92 | The same 63 MB of photos/PDFs present **twice** |
| 7 | `node_modules/` (root + `tests/Browser/` + `tmp-pw-harness/`) | ~98 MB | 4,400+ | Standard, but three separate copies |
| 8 | `.git/` | 1.8 GB | — | Tools that scan pack files or run `git log -p` style context gathering pay this cost |

### 1.2 Context pollution (wrong-information load)

Distinct from volume, these sources actively fed AI sessions **wrong** information:

1. **`docs/CLAUDE.md` (Feb 2026, stale)** — auto-injected by Claude Code whenever a file under `docs/` is read. Claimed wrong auth stack (Breeze+Sanctum), wrong roles (Admin/Manager/Staff/Caretaker), wrong coverage numbers. **Archived this session** → injection stops.
2. **`docs/archive/prompts/`** (12 superseded AI session prompts) — repeatedly resurfaced in searches. **Archived.**
3. **Five conflicting test-count claims** across docs (per SOURCE_OF_TRUTH drift watchlist) — partially mitigated; stale docs that remain are cataloged as do-not-trust.
4. **Duplicate user manuals & UAT reports** — duplicate copy archived.

---

## 2. Reductions achieved by this session's archive execution

All figures are working-tree scope visible to a repo-root file walker that **skips `Archive/`** (see §3).

| Metric | Before | After (indexer skipping `Archive/`) | Reduction |
|--------|--------|-------------------------------------|-----------|
| Working-tree size (excl. `.git`) | ~5.3 GB | ~2.4 GB on disk; **~480 MB** in indexer scope with recommended ignores (Archive + node_modules + vendor + storage) | **~91% of indexable bytes** |
| File count (excl. `.git`) | ~16,000 | ~14,100 on disk; ~9,600 in indexer scope | ~1,900 files relocated; ~40% scope cut with ignores |
| `docs/` size | 591 MB | ~3 MB | **99.5%** |
| `tests/` size | 1.78 GB | ~43 MB (sources + node_modules) | **97.6%** |
| `storage/` size | 610 MB | ~27 MB | **95.6%** |
| Largest file in indexer scope | 40 MB log | 26 MB referenced video (`public/videos/`) | — |
| Stale AI-instruction injection (`docs/CLAUDE.md`) | active | removed from docs/ | eliminated |

**Estimated OpenCode impact**: the OOM driver was almost certainly the 1.49 GB trace corpus plus 40 MB single-file logs. With both out of `tests/` and `storage/` scope and `Archive/` excluded from indexing, peak memory during a full index should drop by an order of magnitude, and incremental indexing will no longer churn on regenerated Playwright output.

---

## 3. Required follow-up: ignore configuration (recommended, not yet applied)

Add to whatever indexer/ignore mechanism each tool supports (e.g. `.opencodeignore` / tool settings). Patterns:

```
Archive/
vendor/
node_modules/
storage/logs/
storage/framework/
public/assets/
public/fonts/
public/build/
tests/Browser/test-results*/
tests/Browser/playwright-report/
*.zip
*.log
```

`.gitignore` was already updated this session so that newly regenerated Playwright/log artifacts stay ignored and the moved untracked artifact sets under `Archive/` do not flood `git status` (see execution report §4).

---

## 4. Remaining bloat sources (not actionable this session)

1. **`.git/` (1.8 GB)** — history contains previously-committed binaries. Remedy is `git filter-repo` (TIER 4, history rewrite, owner approval required).
2. **`public/assets/` + `public/fonts/` (7,098 tracked files)** — served by the application; cannot be archived. Mitigate via indexer ignores only.
3. **`public/letters/` (110 tracked generated PDFs)** — Manual Review queue (possible DB references).
4. **Log regrowth** — without `LOG_CHANNEL=daily` + retention days, `storage/logs/` will regrow 40 MB files.

---

*No deletions were performed. All reductions are relocations into `Archive/` plus recommended ignore rules.*
