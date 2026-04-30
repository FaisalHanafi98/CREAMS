#!/usr/bin/env bash
set -e
cd "$(dirname "$0")/.."
python scripts/save_checkpoint.py --mode session_end
