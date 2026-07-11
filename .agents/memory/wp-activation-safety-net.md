---
name: WordPress activation hooks vs in-place file updates
description: Why WP setup logic (seeding, page/menu creation) must also run on admin_init, not just activation hooks.
---

`register_activation_hook()` and `after_switch_theme` only fire when a user actually clicks Activate/Deactivate in wp-admin. If a user re-uploads plugin/theme files in place (e.g. via FTP/zip overwrite) without deactivating first, these hooks never fire again — so new setup logic (seeding starter content, creating pages, building nav menus) silently never runs, and the site looks unchanged after an update.

**Why:** A user reported "no change" after uploading updated plugin/theme files; the root cause was relying solely on activation hooks for idempotent setup logic.

**How to apply:** Pair every activation-hook setup routine with the same idempotent function also hooked to `admin_init` as a safety net, guarded so it's cheap/no-op after the first successful run (e.g. `get_page_by_title()` / option-flag checks before creating anything). This repo's pattern lives in `tibbhouse-theme/inc/homepage-install.php` and `tibbhouse-theme/inc/menu-install.php`.
