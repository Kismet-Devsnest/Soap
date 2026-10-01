#!/usr/bin/env bash
# Entry point for the hourly Claude cloud scheduled task.
# Installs Python Playwright if missing (Chromium is pre-installed at
# /opt/pw-browsers in the cloud image), then runs the monitor headlessly.
set -uo pipefail
cd "$(dirname "$0")"

if ! python3 -c "import playwright" 2>/dev/null; then
    pip install -q "playwright==1.56.0" >/dev/null 2>&1
fi

export ARTIFACT_DIR="${ARTIFACT_DIR:-$PWD/artifacts}"
rm -rf "$ARTIFACT_DIR"
NUM_RUNS="${NUM_RUNS:-3}" python3 shopify_carrier_monitor_cloud.py
