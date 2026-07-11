#!/usr/bin/env bash
# =============================================================================
# Post-merge hook — runs automatically after every GitHub import / task merge.
# Ensures the WordPress environment is always ready after pulling changes.
# =============================================================================
set -e

REPO_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"

echo ""
echo "── Post-merge: syncing development environment…"

# Install/update Node dependencies
pnpm install --frozen-lockfile

# Re-run WordPress setup (idempotent — skips steps already complete,
# syncs any new theme/plugin files from the pulled changes)
bash "$REPO_ROOT/scripts/setup-wordpress.sh"
