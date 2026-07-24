#!/usr/bin/env bash
# =============================================================================
# Tibb House — WordPress Development Environment Setup
#
# Idempotent: safe to run multiple times — skips steps already complete.
# Designed to run automatically every time the project opens in Replit,
# including on first import from GitHub.
#
# What it does:
#   1. Downloads WordPress core if missing
#   2. Installs the SQLite Database Integration drop-in if missing
#   3. Symlinks tibbhouse-theme/ and tibbhouse-core/ into wp-content
#      (single source of truth — edit either location, changes are instant)
#   4. Runs the WordPress installer if the database doesn't exist yet
#   5. Activates the Tibb House theme and plugin
# =============================================================================
set -euo pipefail

REPO_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
WP_DIR="$REPO_ROOT/wordpress"
THEME_SRC="$REPO_ROOT/tibbhouse-theme"
PLUGIN_SRC="$REPO_ROOT/tibbhouse-core"
SETUP_PORT=7778   # temporary port for the installer (separate from dev port 6000)

# ── Helpers ───────────────────────────────────────────────────────────────────
ok()   { echo "  ✓ $*"; }
step() { echo ""; echo "▸ $*"; }
warn() { echo "  ⚠ $*"; }
die()  { echo ""; echo "✗ ERROR: $*" >&2; exit 1; }

echo ""
echo "╔══════════════════════════════════════════════════╗"
echo "║  Tibb House — WordPress Dev Environment Setup   ║"
echo "╚══════════════════════════════════════════════════╝"

# ── 1. WordPress Core ─────────────────────────────────────────────────────────
step "WordPress Core"
if [ ! -f "$WP_DIR/wp-load.php" ]; then
  echo "  Downloading latest WordPress…"
  mkdir -p "$WP_DIR"
  curl -fsSL "https://wordpress.org/latest.tar.gz" -o /tmp/wp-latest.tar.gz \
    || die "Failed to download WordPress. Check your internet connection."
  tar -xzf /tmp/wp-latest.tar.gz -C /tmp/
  # Merge core files; preserve wp-config.php and router.php that are already tracked
  # cp -n = no-clobber (skip existing files)
  cp -rn /tmp/wordpress/. "$WP_DIR/"
  rm -rf /tmp/wp-latest.tar.gz /tmp/wordpress
  ok "WordPress core downloaded and extracted"
else
  WP_VER=$(php -r "include '$WP_DIR/wp-includes/version.php'; echo \$wp_version;" 2>/dev/null || echo "unknown")
  ok "WordPress $WP_VER already present"
fi

# ── 2. SQLite Drop-in ─────────────────────────────────────────────────────────
step "SQLite Database Integration"
SQLITE_PLUGIN_DIR="$WP_DIR/wp-content/plugins/sqlite-database-integration"
if [ ! -f "$WP_DIR/wp-content/db.php" ] || [ ! -d "$SQLITE_PLUGIN_DIR" ]; then
  echo "  Downloading SQLite integration plugin…"
  curl -fsSL "https://downloads.wordpress.org/plugin/sqlite-database-integration.zip" \
    -o /tmp/sqlite-integration.zip \
    || die "Failed to download SQLite integration."
  mkdir -p /tmp/sqlite-integration
  unzip -q /tmp/sqlite-integration.zip -d /tmp/sqlite-integration
  # Copy the db.php drop-in
  cp /tmp/sqlite-integration/sqlite-database-integration/db.copy \
     "$WP_DIR/wp-content/db.php"
  # Keep the full plugin directory so db.php can find its SQLite implementation
  mkdir -p "$WP_DIR/wp-content/plugins"
  rm -rf "$SQLITE_PLUGIN_DIR"
  cp -r /tmp/sqlite-integration/sqlite-database-integration "$SQLITE_PLUGIN_DIR"
  rm -rf /tmp/sqlite-integration.zip /tmp/sqlite-integration
  ok "SQLite drop-in and plugin installed"
else
  ok "SQLite drop-in already present"
fi

# ── 3. Required Directories ───────────────────────────────────────────────────
step "Content Directories"
mkdir -p "$WP_DIR/wp-content/database"
mkdir -p "$WP_DIR/wp-content/uploads"
mkdir -p "$WP_DIR/wp-content/themes"
mkdir -p "$WP_DIR/wp-content/plugins"
chmod 755 "$WP_DIR/wp-content/database" \
          "$WP_DIR/wp-content/uploads" \
          "$WP_DIR/wp-content/themes" \
          "$WP_DIR/wp-content/plugins"
ok "Directories ready"

# ── 4. Symlink Theme (single source of truth = tibbhouse-theme/) ─────────────
step "Theme Symlink  [tibbhouse-theme/ ↔ wordpress/wp-content/themes/tibbhouse-theme]"
THEME_LINK="$WP_DIR/wp-content/themes/tibbhouse-theme"

if [ -L "$THEME_LINK" ] && [ "$(readlink "$THEME_LINK")" = "$THEME_SRC" ]; then
  ok "Theme symlink already correct"
elif [ -L "$THEME_LINK" ]; then
  # Stale symlink pointing elsewhere — fix it
  rm "$THEME_LINK"
  ln -s "$THEME_SRC" "$THEME_LINK"
  ok "Theme symlink updated → tibbhouse-theme/"
elif [ -d "$THEME_LINK" ]; then
  # Real directory from an old copy-based setup — replace with symlink.
  # Copy any files that exist in runtime but not in source (preserves manual edits).
  cp -rn "$THEME_LINK/." "$THEME_SRC/" 2>/dev/null || true
  rm -rf "$THEME_LINK"
  ln -s "$THEME_SRC" "$THEME_LINK"
  ok "Replaced theme directory with symlink → tibbhouse-theme/"
else
  ln -s "$THEME_SRC" "$THEME_LINK"
  ok "Theme symlink created → tibbhouse-theme/"
fi

# ── 5. Symlink Plugin (single source of truth = tibbhouse-core/) ─────────────
step "Plugin Symlink  [tibbhouse-core/ ↔ wordpress/wp-content/plugins/tibbhouse-core]"
PLUGIN_LINK="$WP_DIR/wp-content/plugins/tibbhouse-core"

if [ -L "$PLUGIN_LINK" ] && [ "$(readlink "$PLUGIN_LINK")" = "$PLUGIN_SRC" ]; then
  ok "Plugin symlink already correct"
elif [ -L "$PLUGIN_LINK" ]; then
  rm "$PLUGIN_LINK"
  ln -s "$PLUGIN_SRC" "$PLUGIN_LINK"
  ok "Plugin symlink updated → tibbhouse-core/"
elif [ -d "$PLUGIN_LINK" ]; then
  cp -rn "$PLUGIN_LINK/." "$PLUGIN_SRC/" 2>/dev/null || true
  rm -rf "$PLUGIN_LINK"
  ln -s "$PLUGIN_SRC" "$PLUGIN_LINK"
  ok "Replaced plugin directory with symlink → tibbhouse-core/"
else
  ln -s "$PLUGIN_SRC" "$PLUGIN_LINK"
  ok "Plugin symlink created → tibbhouse-core/"
fi

# ── 6. WordPress Installation ─────────────────────────────────────────────────
step "WordPress Installation"
DB_FILE="$WP_DIR/wp-content/database/.ht.sqlite"

if [ -f "$DB_FILE" ]; then
  ok "WordPress already installed (database found)"
else
  echo "  Running WordPress installer (first time only)…"

  # Start a temporary PHP server for the installer
  php -S "127.0.0.1:$SETUP_PORT" -t "$WP_DIR" "$WP_DIR/router.php" \
    &>/tmp/wp-setup-server.log &
  SETUP_PID=$!

  # Wait up to 10 s for the server to be ready
  for i in $(seq 1 20); do
    if curl -sf "http://127.0.0.1:$SETUP_PORT/wp-admin/install.php" &>/dev/null; then
      break
    fi
    sleep 0.5
  done

  # POST the install form
  HTTP_CODE=$(curl -sf -o /tmp/wp-install-response.html -w "%{http_code}" \
    -X POST "http://127.0.0.1:$SETUP_PORT/wp-admin/install.php?step=2" \
    --data-urlencode "weblog_title=Tibb House" \
    --data-urlencode "user_name=admin" \
    --data-urlencode "admin_email=admin@tibbhouse.local" \
    --data-urlencode "admin_password=tibbhouse2024!" \
    --data-urlencode "admin_password2=tibbhouse2024!" \
    --data-urlencode "pw_weak=1" \
    2>/dev/null || echo "000")

  kill "$SETUP_PID" 2>/dev/null || true
  wait "$SETUP_PID" 2>/dev/null || true

  if [ -f "$DB_FILE" ]; then
    ok "WordPress installed (HTTP $HTTP_CODE)"
  else
    warn "Installer HTTP status: $HTTP_CODE"
    warn "Server log: /tmp/wp-setup-server.log"
    warn "Response:   /tmp/wp-install-response.html"
    die "WordPress installation failed. See logs above."
  fi
fi

# ── 7. Activate Theme & Plugin ────────────────────────────────────────────────
step "Theme & Plugin Activation"
DB_FILE="$WP_DIR/wp-content/database/.ht.sqlite"

php -r "
\$db = new SQLite3('$DB_FILE');
\$prefix = 'wp_';

// ── Activate plugin ──────────────────────────────────────────────────────────
\$plugin_file = 'tibbhouse-core/tibbhouse-core.php';
\$row = \$db->querySingle(
    \"SELECT option_value FROM {\$prefix}options WHERE option_name = 'active_plugins'\"
);
\$active = \$row ? unserialize(\$row) : [];
if (is_array(\$active) && !in_array(\$plugin_file, \$active)) {
    \$active[] = \$plugin_file;
    sort(\$active);
    \$val = serialize(\$active);
    \$stmt = \$db->prepare(
        \"UPDATE {\$prefix}options SET option_value = :v WHERE option_name = 'active_plugins'\"
    );
    \$stmt->bindValue(':v', \$val);
    \$stmt->execute();
    echo '  ✓ Plugin activated' . PHP_EOL;
} else {
    echo '  ✓ Plugin already active' . PHP_EOL;
}

// ── Activate theme ───────────────────────────────────────────────────────────
\$theme = 'tibbhouse-theme';
\$current = \$db->querySingle(
    \"SELECT option_value FROM {\$prefix}options WHERE option_name = 'template'\"
);
if (\$current !== \$theme) {
    \$db->exec(\"
        UPDATE {\$prefix}options
        SET option_value = '\$theme'
        WHERE option_name IN ('template', 'stylesheet')
    \");
    echo '  ✓ Theme activated' . PHP_EOL;
} else {
    echo '  ✓ Theme already active' . PHP_EOL;
}

\$db->close();
" || die "Failed to activate theme/plugin via SQLite."

# ── 8. Seed pages, menus & reading settings ───────────────────────────────────
# Creates: Home, About Us, Contact Us, Blog, Patient Forms, Secure Patient Intake
# Sets:    static front page → Home, posts page → Blog
# Builds:  Primary Navigation & Footer Navigation menus
# Safe to run multiple times — checks for existing pages/menus before creating.
step "Pages, Menus & Front-Page Settings"
php "$REPO_ROOT/scripts/seed-pages-menus.php" \
  || die "seed-pages-menus.php failed — see output above."

# ── 9. Seed starter CPT content ───────────────────────────────────────────────
# Creates: Treatments, Conditions, Knowledge, Practitioners, Locations
# Attaches bundled images as featured images.
# Safe to run multiple times — flag-guarded so each seeder runs only once.
step "Starter Content (Treatments, Conditions, Practitioners, etc.)"
php "$REPO_ROOT/scripts/seed-starter-content.php" \
  || die "seed-starter-content.php failed — see output above."

# ── Done ──────────────────────────────────────────────────────────────────────
echo ""
echo "╔══════════════════════════════════════════════════╗"
echo "║  ✓  Setup complete — starting development…      ║"
echo "║                                                  ║"
echo "║  Edit:   tibbhouse-theme/   (theme)              ║"
echo "║  Edit:   tibbhouse-core/    (plugin)             ║"
echo "║  Export: scripts/export-packages.sh             ║"
echo "╚══════════════════════════════════════════════════╝"
echo ""
