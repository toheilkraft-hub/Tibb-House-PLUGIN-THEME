# Tibb House / Iheal Clinics — WordPress Development Environment

A WordPress site (custom `tibbhouse-core` plugin + `tibbhouse-theme` theme) powering a natural & Islamic medicine platform — Treatments, Conditions, Knowledge articles, Practitioners, and Locations — with a fully designed frontend.

This repository is a **permanent, self-configuring WordPress dev environment**. Every import from GitHub sets itself up automatically. No manual conversion or preparation ever required.

---

## 🔄 Development Pipeline

```
GitHub Repository
       ↓
Open in Replit  (run button)
       ↓
Auto-setup runs  (scripts/setup-wordpress.sh)  ← fully automatic, no manual steps
  1. Downloads WordPress core if missing
  2. Installs SQLite drop-in if missing
  3. Creates symlinks  tibbhouse-theme/ ↔ wp-content/themes/tibbhouse-theme
                       tibbhouse-core/  ↔ wp-content/plugins/tibbhouse-core
  4. Runs WordPress installer if first time
  5. Activates theme & plugin
  6. Seeds pages & menus  (scripts/seed-pages-menus.php)
       • Home, About Us, Contact Us, Blog, Patient Forms, Secure Patient Intake
       • Primary Navigation + Footer Navigation menus fully wired
       • Front page set to Home, posts page set to Blog
  7. Seeds all content  (scripts/seed-starter-content.php)
       • 4 Treatments (Hijama, Black Seed, Herbal Steam, Honey & Olive Oil)
       • 4 Conditions (Back Pain, Respiratory, Digestive, Sleep)
       • 4 Knowledge articles
       • 3 Practitioners (Dr. Amina, Imam Bilal, Sister Fatima)
       • 3 Locations (Downtown, East End, Online)
       • Featured images attached from tibbhouse-core/assets/img/starter/
       ↓
Live preview opens with the complete site — no half-baked state
       ↓
Edit  tibbhouse-theme/  or  tibbhouse-core/  — changes appear instantly on reload
       ↓
Commit & push to GitHub
       ↓
Run:  bash scripts/export-packages.sh
  → tibbhouse-theme.zip   (Appearance › Themes)
  → tibbhouse-core.zip    (Plugins › Add New)
```

> **All seed scripts are idempotent.** Running setup again on an existing install is fast — every step checks before creating and skips anything already present. The content seeders are flag-guarded in the database so they never duplicate posts.

---

## 🚀 Running the Project

Hit the **Run** button. The `Project` workflow:
1. Runs `scripts/setup-wordpress.sh` (fast, idempotent — skips completed steps)
2. Starts `WordPress Server` — PHP built-in server on port 6000
3. Starts `artifacts/api-server: web` — Vite proxy that exposes WordPress in the preview pane

To re-run setup manually at any time:
```bash
bash scripts/setup-wordpress.sh
```

---

## ✏️ Editing

**Theme edits** → `tibbhouse-theme/`  
**Plugin edits** → `tibbhouse-core/`

Both are symlinked into `wordpress/wp-content/` so any save is immediately live. No copy step. No rebuild. Just reload the preview.

- PHP changes → instant (no restart needed)
- CSS/JS changes → instant (no restart needed)
- After changing the Vite proxy config → restart `artifacts/api-server: web`

---

## 📦 Exporting Installable Packages

```bash
bash scripts/export-packages.sh
```

Produces:
- `tibbhouse-theme.zip` — install via **Appearance › Themes › Upload Theme**
- `tibbhouse-core.zip` — install via **Plugins › Add New › Upload Plugin**

---

## 🗂 Where Things Live

| Path | Purpose |
|------|---------|
| `tibbhouse-theme/` | Theme source — **edit here** (git-tracked) |
| `tibbhouse-core/` | Plugin source — **edit here** (git-tracked) |
| `wordpress/wp-config.php` | WP config — SQLite, dynamic host URL (git-tracked) |
| `wordpress/router.php` | PHP built-in server router (git-tracked) |
| `wordpress/wp-content/themes/tibbhouse-theme` | Symlink → `tibbhouse-theme/` |
| `wordpress/wp-content/plugins/tibbhouse-core` | Symlink → `tibbhouse-core/` |
| `wordpress/wp-content/database/.ht.sqlite` | SQLite database (runtime, gitignored) |
| `wordpress/wp-content/uploads/` | Media uploads (runtime, gitignored) |
| `scripts/setup-wordpress.sh` | Idempotent environment bootstrap (runs full pipeline) |
| `scripts/seed-pages-menus.php` | Creates all pages and nav menus (called by setup) |
| `scripts/seed-starter-content.php` | Seeds all CPT content + media (called by setup) |
| `scripts/export-packages.sh` | Generates installable ZIPs |
| `scripts/post-merge.sh` | Runs after every GitHub import / task merge |
| `artifacts/api-server/` | Vite reverse-proxy artifact (exposes WP in preview) |

---

## 🔑 WordPress Admin

- URL: `/wp-admin/`
- Username: `admin`
- Password: `tibbhouse2024!`  _(change before going to production)_

---

## ⚙️ Stack

- **WordPress** (PHP 8.4) — custom plugin + theme, SQLite-backed (no MySQL required)
- **Preview proxy**: Vite dev server (`artifacts/api-server`) proxies all requests to the PHP server on port 6000
- **pnpm workspace** (Node 24, TypeScript) — surrounding scaffold; unused by WordPress directly

---

## 🏗 Architecture Notes

- `wp-config.php` derives `WP_HOME`/`WP_SITEURL` from the incoming `Host` header — works on any Replit preview domain or deployment domain without any edits.
- SQLite backs WordPress via the official "SQLite Database Integration" drop-in — no separate database server needed.
- `tibbhouse-theme/` and `tibbhouse-core/` are symlinked (not copied) into `wp-content/` — there is **one copy** of each that is both git-tracked and live in WordPress simultaneously.
- The `wordpress/` directory is gitignored except for the two hand-crafted config files; everything else regenerates from `setup-wordpress.sh`.

---

## 👤 User Preferences

_Populate as you build — explicit user instructions worth remembering across sessions._

- Do not touch the plugin (`tibbhouse-core/`) architecture or duplicate its CPTs/taxonomies.
- Do not convert the project to React/Next/etc — it is and should remain WordPress.
- Theme edits are the primary dev activity; the plugin is the data layer and stays untouched.
