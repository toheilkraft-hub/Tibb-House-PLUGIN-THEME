---
name: WP auto-setup pipeline
description: Self-configuring WordPress dev environment — scripts, symlinks, export flow.
---

## Setup Script: scripts/setup-wordpress.sh

Idempotent bootstrap run automatically by the Project workflow (sequential mode) before PHP starts.

Steps:
1. Downloads `latest.tar.gz` from wordpress.org if `wordpress/wp-load.php` missing
2. Downloads `sqlite-database-integration.zip`; copies `db.copy` → `wp-content/db.php`
3. Creates symlinks: `wordpress/wp-content/themes/tibbhouse-theme` → absolute path to `tibbhouse-theme/`
4. Creates symlinks: `wordpress/wp-content/plugins/tibbhouse-core` → absolute path to `tibbhouse-core/`
5. Runs WordPress installer via HTTP POST to temporary PHP server on port 7778
6. Activates plugin + theme via direct SQLite3 PHP calls

**Why symlinks:** Single source of truth — edit `tibbhouse-theme/` or `tibbhouse-core/` directly and changes are immediately live in WordPress. No copy/sync step needed.

## Key constraint: no rsync

`rsync` is NOT on PATH in this Nix environment. Use `cp -rn` (no-clobber) for merge-copy operations.

## Export Script: scripts/export-packages.sh

Zips `tibbhouse-theme/` and `tibbhouse-core/` from repo root → installable WordPress packages.

## .replit Workflow

Project runs **sequential**: setup-wordpress.sh → WordPress Server → artifacts/api-server: web

## post-merge.sh

Runs `pnpm install --frozen-lockfile` then `bash scripts/setup-wordpress.sh` after every GitHub import.

## Page and menu content seed

The bootstrap script installs WordPress, activates the theme/plugin, and prepares the runtime, but it does not create the user-facing Home, About Us, Contact Us, Blog, Patient Forms, or navigation records. Those are maintained by the separate idempotent `scripts/seed-pages-menus.php` step.

**Why:** A fresh runtime can be technically healthy while still showing only WordPress defaults and incomplete navigation.

**How to apply:** Run the seeder after first install or import when the preview must contain the complete Tibb House page/menu set; it safely reuses existing pages and rebuilds the seeded menus.
