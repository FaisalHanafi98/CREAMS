#!/usr/bin/env bash
set -e
cd "$(dirname "$0")/.."

COUNTER_FILE=".memsearch/.prompt_count"
mkdir -p .memsearch

COUNT=0
if [ -f "$COUNTER_FILE" ]; then
  COUNT=$(cat "$COUNTER_FILE" 2>/dev/null || echo 0)
  [[ "$COUNT" =~ ^[0-9]+$ ]] || COUNT=0
fi

COUNT=$((COUNT + 1))
echo "$COUNT" > "$COUNTER_FILE"

if [ $((COUNT % 10)) -eq 0 ]; then
  python scripts/save_checkpoint.py --mode prompt_interval 2>/dev/null || true
  TODAY=$(date +%Y-%m-%d)
  echo "{\"systemMessage\": \"[memsearch] CHECKPOINT DUE (prompt #${COUNT}): A checkpoint stub has been appended to .memsearch/memory/${TODAY}.md. Fill all 8 sections from live context NOW before answering the user.\"}"
else
  echo '{}'
fi
