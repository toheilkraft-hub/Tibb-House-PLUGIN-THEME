---
name: Tibb House theme/plugin CSS split
description: Where design tokens and the sitewide preloader live across the theme and plugin, and enqueue scoping.
---

The Tibb House theme (`tibbhouse-theme/`) and the `tibbhouse-core` plugin each define their own `:root` CSS token block (theme.css and plugin's assets/css/frontend.css) that must be kept in sync — a palette change in one without the other leaves half the site on stale colors.

The sitewide preloader (`#tibbhouse-preloader`, logo + spinner) is rendered and styled entirely from the plugin (`class-frontend.php` + `frontend.css` + `frontend.js`), not the theme, even though it appears on every page. Its CSS/JS/markup enqueue was originally gated behind `is_tibbhouse_page()` (only CPT/taxonomy views) — this was changed to load sitewide since the preloader and other lightly-scoped rules need to be present everywhere; safe because the rest of frontend.css's rules are scoped to specific classes.

**Why:** the pre-existing token/section duplication is not obvious from reading either file alone, and it's easy to "fix" only the theme file and think the rebrand is done.

**How to apply:** any future palette/branding change must touch both `:root` blocks and re-check large solid-background sections (hero/archive-header bands, CTA bands, footer) in both files for hardcoded light/dark text colors that assumed the old background. After editing the live `wordpress/wp-content/...` copies, copy changes back to the root `tibbhouse-theme/` and `tibbhouse-core/` source dirs (project convention).
