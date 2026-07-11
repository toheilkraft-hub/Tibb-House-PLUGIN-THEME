#!/usr/bin/env bash
# =============================================================================
# Tibb House — Export Installable WordPress Packages
#
# Generates two ready-to-install ZIP files from the source directories:
#
#   tibbhouse-theme.zip  → WordPress Admin › Appearance › Themes
#   tibbhouse-core.zip   → WordPress Admin › Plugins › Add New
#
# Run this after finishing edits to package up the latest version.
# =============================================================================
set -euo pipefail

REPO_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
THEME_SRC="$REPO_ROOT/tibbhouse-theme"
PLUGIN_SRC="$REPO_ROOT/tibbhouse-core"
OUT="$REPO_ROOT"

ok()   { echo "  ✓ $*"; }
step() { echo ""; echo "▸ $*"; }
die()  { echo ""; echo "✗ ERROR: $*" >&2; exit 1; }

echo ""
echo "╔══════════════════════════════════════════════════╗"
echo "║  Tibb House — Export WordPress Packages          ║"
echo "╚══════════════════════════════════════════════════╝"

# Validate sources exist
[ -d "$THEME_SRC" ]  || die "Theme source not found: $THEME_SRC"
[ -d "$PLUGIN_SRC" ] || die "Plugin source not found: $PLUGIN_SRC"

# ── Theme ZIP ─────────────────────────────────────────────────────────────────
step "Packaging Theme"
THEME_ZIP="$OUT/tibbhouse-theme.zip"
rm -f "$THEME_ZIP"
cd "$REPO_ROOT"
zip -r "$THEME_ZIP" tibbhouse-theme/ \
  --exclude "*/.DS_Store" \
  --exclude "*.DS_Store" \
  --exclude "*/.git*" \
  --exclude "*/__MACOSX*" \
  --exclude "*/node_modules/*" \
  --exclude "*.log" \
  > /dev/null
SIZE=$(du -sh "$THEME_ZIP" | cut -f1)
ok "tibbhouse-theme.zip created ($SIZE)"
ok "Install via: WordPress Admin › Appearance › Themes › Add New › Upload Theme"

# ── Plugin ZIP ────────────────────────────────────────────────────────────────
step "Packaging Plugin"
PLUGIN_ZIP="$OUT/tibbhouse-core.zip"
rm -f "$PLUGIN_ZIP"
cd "$REPO_ROOT"
zip -r "$PLUGIN_ZIP" tibbhouse-core/ \
  --exclude "*/.DS_Store" \
  --exclude "*.DS_Store" \
  --exclude "*/.git*" \
  --exclude "*/__MACOSX*" \
  --exclude "*/node_modules/*" \
  --exclude "*.log" \
  > /dev/null
SIZE=$(du -sh "$PLUGIN_ZIP" | cut -f1)
ok "tibbhouse-core.zip created ($SIZE)"
ok "Install via: WordPress Admin › Plugins › Add New › Upload Plugin"

# ── Summary ───────────────────────────────────────────────────────────────────
echo ""
echo "╔══════════════════════════════════════════════════╗"
echo "║  ✓  Packages ready for download / installation  ║"
echo "╠══════════════════════════════════════════════════╣"
printf  "║  %-48s║\n" "tibbhouse-theme.zip  → Appearance › Themes"
printf  "║  %-48s║\n" "tibbhouse-core.zip   → Plugins › Add New"
echo "╚══════════════════════════════════════════════════╝"
echo ""
