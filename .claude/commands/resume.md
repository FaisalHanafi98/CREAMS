Follow these steps exactly. Do not skip any step. Do not modify files until step 5.

## Step 1 — Read today's memory file (or most recent)
Run this and read the full output:
```
ls -t .memsearch/memory/*.md 2>/dev/null | head -1
```
Then read that file completely end-to-end.

## Step 2 — Search for the last checkpoint (best-effort)
First, try the vector search command:
```
uvx --from "memsearch[onnx]" memsearch search "last checkpoint objective next action open issues" --top-k 5 --collection ms_creams_91bc6b96
```
If any result has a chunk hash, expand the most relevant one:
```
uvx --from "memsearch[onnx]" memsearch expand <chunk_hash> --collection ms_creams_91bc6b96
```

**If the command errors (expected on Windows — Milvus Lite has no Windows wheels):**
Fall back to reading the three most recent memory files directly:
```
ls -t .memsearch/memory/*.md 2>/dev/null | head -3
```
Read each one end-to-end. The markdown files are always the source of truth.

## Step 3 — Read CLAUDE.md
Read `CLAUDE.md` in the project root.

## Step 4 — Summarize in this exact format

**Date of last session:** YYYY-MM-DD
**What was worked on:** [one sentence]
**Status:** [done / in progress / blocked]
**Open issues:** [bullet list]
**Next best action:** [specific enough to act on immediately]
**Do not repeat:** [failed approaches already ruled out]

## Step 5 — Wait for confirmation
Say: "Context reconstructed. Ready to continue — confirm to proceed."
Do not read, edit, or run anything else until the user says yes.
