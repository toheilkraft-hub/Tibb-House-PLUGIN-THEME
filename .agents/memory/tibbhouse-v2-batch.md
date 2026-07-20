---
name: Tibb House v2 Batch Changes
description: Summary of all changes made in the July 2026 batch — what was done and what to know for future work.
---

## What was changed

- **Permalink structure** set to `/%postname%/` in the DB directly; rewrite rules cache cleared. All 5 CPT archives return 200.
- **Admin thumbnail column** (new `class-admin-thumbnails.php`): clickable "Image" column on every CPT list view, opens WP media library, saves via AJAX. Flag: uses nonce `tibbhouse_thumb_<post_id>`.
- **Bundled images importer** (new `tibbhouse-theme/inc/image-import.php`): copies `assets/img/bundled/` to WP media on activation. Flag: `tibbhouse_bundled_images_imported_v1` (delete to re-run).
- **About Us image**: AI-generated `about-clinic.jpg` added to theme; `th-home-about-visual` now has an `<img>` tag; CSS `:has(img)::before { display:none }` hides the dot pattern when image present.
- **V2 seeder** (`maybe_seed_v2()` in `class-starter-content.php`): adds 3rd practitioner + 2 extra locations. Flag: `tibbhouse_starter_content_seeded_v2` (delete to re-run). All 5 CPTs now have 3 published posts.
- **Deprecated `get_page_by_title()`** replaced with `WP_Query` in `homepage-render.php` and `homepage-install.php`.
- **ZIPs re-exported**: `tibbhouse-theme.zip` (3.6M), `tibbhouse-core.zip` (2.8M).

## Tips for future work

- Seeder images must be in BOTH `tibbhouse-core/assets/img/starter/` and `tibbhouse-theme/assets/img/bundled/` — keep both in sync.
- `Tibbhouse_Helpers::post_types()` enumerates CPTs; new CPTs added there get the admin thumb column automatically.
- The `tibbhouse-run-setup.php` trick (drop in wordpress/, curl, self-deletes) is the fastest way to trigger admin_init hooks from the shell without WP-CLI.
