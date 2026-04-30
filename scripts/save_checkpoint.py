import argparse
import datetime
from pathlib import Path

PROJECT_NAME = "CREAMS"

STUB_TEMPLATE = """
## CHECKPOINT — {project} — {timestamp} — {mode}

### Current objective
_[to be filled by Claude]_

### Completed this session
_[to be filled by Claude]_

### Files changed
_[to be filled by Claude]_

### Commands/tests run
_[to be filled by Claude]_

### Current system state
_[to be filled by Claude]_

### Open issues
_[to be filled by Claude]_

### Next best action
_[to be filled by Claude]_

### Do not repeat
_[to be filled by Claude]_
"""


def main() -> None:
    parser = argparse.ArgumentParser()
    parser.add_argument(
        "--mode",
        required=True,
        choices=["pre_compaction", "session_end", "prompt_interval"],
    )
    args = parser.parse_args()
    project_root = Path(__file__).resolve().parent.parent
    memory_dir = project_root / ".memsearch" / "memory"
    memory_dir.mkdir(parents=True, exist_ok=True)
    today = datetime.date.today().isoformat()
    target = memory_dir / f"{today}.md"
    timestamp = datetime.datetime.now().isoformat(timespec="seconds")
    stub = STUB_TEMPLATE.format(project=PROJECT_NAME, timestamp=timestamp, mode=args.mode)
    with target.open("a", encoding="utf-8") as f:
        f.write(stub)
    print(f"Checkpoint stub appended: {target}")


if __name__ == "__main__":
    main()
