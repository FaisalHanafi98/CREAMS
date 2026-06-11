# docs/archive/ — Historical Documentation

Files under this tree are **historical only**. They are preserved for traceability but must not guide current work.

If a new session's behavior disagrees with an archived document, the archived document is wrong by default. Verify current state from code, tests, and the active files listed in [`docs/SOURCE_OF_TRUTH.md`](../SOURCE_OF_TRUTH.md).

## Categories

| Folder | Contents |
|--------|----------|
| `prompts/` | Superseded session prompts, old Claude prompts, re-evaluation prompts whose work is complete |
| `deployment/` | Deployment guides for targets that are not current (Vercel, ECS/Fargate migration) |
| `audits/` | Old status reports, completed audit outputs, pre-close-out progress logs |
| `uat/` | UAT cycle artefacts from past test rounds |
| `reference/` | Point-in-time architecture/schema snapshots that have been superseded |
| `duplicates/` | Byte-identical copies of files kept elsewhere |
| `logs/` | Build/test/log output captured as files |
| `misc/` | Personal notes and non-CREAMS content that ended up in docs/ |
| `quarantine/` | Malformed filenames, corrupt files, filesystem artefacts — do not open inline |

## Rules

1. Do not delete from this tree without review in a dedicated pruning session.
2. Do not resurrect a file from here without first checking it against the active source-of-truth set.
3. Do not cite archived files as authoritative in new documents.
