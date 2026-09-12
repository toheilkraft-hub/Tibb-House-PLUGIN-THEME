---
name: WordPress setup hooks and public migrations
description: Why WordPress setup logic needs both admin and public-request safety nets.
---

`register_activation_hook()` and `after_switch_theme` only fire when a user actually clicks Activate/Deactivate in wp-admin. If a user re-uploads plugin/theme files in place (e.g. via FTP/zip overwrite) without deactivating first, these hooks never fire again. Public content migrations also cannot rely on `admin_init`, because visitors do not load wp-admin; a flag-guarded public `init` safety net is required when the migration must affect the first live page request.

**Why:** A user reported "no change" after uploading updated plugin/theme files; the root cause was relying solely on activation hooks for idempotent setup logic.

**How to apply:** Pair activation-hook setup with an idempotent `admin_init` safety net. If a production content cleanup must run for visitors, also attach the guarded migration to `init` after CPT registration; use a versioned option so an older completed flag cannot suppress a newly expanded cleanup. This repo's theme setup pattern lives in `tibbhouse-theme/inc/homepage-install.php` and `tibbhouse-theme/inc/menu-install.php`.
