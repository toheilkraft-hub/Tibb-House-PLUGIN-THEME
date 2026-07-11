# Tibb House / Iheal Clinics

A WordPress site (custom `tibbhouse-core` plugin + `tibbhouse-theme` theme) powering a natural & Islamic medicine platform — Treatments, Conditions, Knowledge articles, Practitioners, and Locations, with a fully designed frontend (preloader, card layouts, taxonomy badges, FAQ accordion, related content grids).

## Run & Operate

- The site is WordPress running on PHP's built-in server, reverse-proxied through the workspace's Express artifact so it's visible in the Replit preview at `/`.
- Workflows:
  - `WordPress Server` — `php -S 0.0.0.0:6000 -t wordpress wordpress/router.php` (internal only, not directly user-facing)
  - `artifacts/api-server: API Server` — Express app; proxies all non-`/api` requests to the WordPress server on port 6000 (see `artifacts/api-server/src/middlewares/wordpressProxy.ts`)
- WordPress core lives in `wordpress/`. The plugin and theme source (tracked at the repo root in `tibbhouse-core/` and `tibbhouse-theme/`) are copied into `wordpress/wp-content/plugins/tibbhouse-core` and `wordpress/wp-content/themes/tibbhouse-theme` — **edit the copies inside `wordpress/wp-content/...`** (or re-copy after editing the root source) since that's what actually runs.
- Database: SQLite via the official "SQLite Database Integration" plugin (drop-in at `wordpress/wp-content/db.php`), file at `wordpress/wp-content/database/.ht.sqlite`. No external DB/Postgres needed for the WordPress site.
- Admin login: `/wp-admin/`, user `admin`, password `TibbHouse2026!` (change this in production).
- `pnpm --filter @workspace/api-server run dev` — run just the proxy/API server directly (needs `WordPress Server` running too).

## Stack

- WordPress (PHP 8.4) + custom plugin/theme, SQLite-backed (no MySQL server required)
- Reverse proxy: Express 5 (`artifacts/api-server`), also owns any future JSON API under `/api`
- Surrounding pnpm workspace (Node 24, TypeScript) scaffold with `lib/db` (Postgres/Drizzle), `lib/api-spec`, mockup-sandbox — currently unused by the WordPress site; only relevant if new non-WordPress features get built alongside it.

## Where things live

- `wordpress/` — WordPress core + `wp-content` (plugins, theme, db.php drop-in, sqlite db file, uploads)
- `tibbhouse-core/`, `tibbhouse-theme/` — the plugin/theme *source of truth* as imported; kept at repo root and copied into `wordpress/wp-content/...` to run
- `artifacts/api-server/src/middlewares/wordpressProxy.ts` — the reverse proxy that makes WordPress reachable through the artifact preview system

## Architecture decisions

- WordPress has no first-class artifact type in this workspace's scaffolding, so it runs as a plain workflow on an internal port and is exposed through the existing Express artifact via a raw reverse proxy (mounted before body-parsing middleware, so uploads/form posts pass through untouched).
- `wp-config.php` derives `WP_HOME`/`WP_SITEURL` from the incoming request's `Host` header instead of hardcoding a domain, so the site works on the dev domain, any preview domain, and a future deployment domain without edits.
- SQLite (not MySQL) backs WordPress to avoid standing up a separate database server in this environment.

## Product

WordPress site for Tibb House: custom post types (Treatments, Conditions, Knowledge, Practitioners, Locations), taxonomies, native meta boxes, resolved cross-content relationships, REST exposure, and Gutenberg blocks (Hero, CTA, FAQ, Testimonials, Booking Form, Card Grid, Related Content, Three Layer, Disclaimer).

## User preferences

_Populate as you build — explicit user instructions worth remembering across sessions._

## Gotchas

- Editing `tibbhouse-core/` or `tibbhouse-theme/` at the repo root does nothing by itself — copy changes into `wordpress/wp-content/plugins/tibbhouse-core` and `wordpress/wp-content/themes/tibbhouse-theme` (or edit them directly there) and the changes appear immediately (no rebuild step).
- After changing PHP code, no restart is needed (PHP is interpreted per-request) — just reload. After changing `wordpressProxy.ts` or other TS/Express code, restart the `artifacts/api-server: API Server` workflow.
- If the preview 404s with "no previewable artifacts", it means the `API Server` artifact/workflow isn't running — restart it; the WordPress workflow alone is not exposed to the preview.

## Pointers

- See the `pnpm-workspace` skill for workspace structure, TypeScript setup, and package details
