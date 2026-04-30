#!/usr/bin/env bash
set -e
cd "$(dirname "$0")/.."
python scripts/save_checkpoint.py --mode pre_compaction
