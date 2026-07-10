=== Tibb House Core ===
Contributors: tibbhouse
Tags: healthcare, custom post types, gutenberg, content management
Requires at least: 6.0
Tested up to: 6.6
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Production-ready core plugin powering the Tibb House content platform.

== Description ==

Tibb House Core is a reusable, scalable content management system built for
large healthcare content websites, similar to how enterprise WordPress
agencies structure this kind of site.

It works with any modern block theme (Astra, Kadence, Blocksy, etc.), uses
the native Gutenberg editor for long-form content, and stores only structured
metadata as custom fields. No ACF, no Carbon Fields, no Meta Box plugin, and
no third-party CPT plugins are used anywhere - everything is built on native
WordPress APIs (`register_post_type()`, `register_taxonomy()`,
`register_meta()`, `add_meta_box()`, `register_block_type()`).

= Content types =

* Treatments
* Conditions
* Knowledge
* Practitioners
* Locations

Each supports the title, Gutenberg editor, featured image, excerpt,
revisions, REST API, and archives.

= Taxonomies =

* Constitutional Type - Treatments, Conditions, Knowledge, Practitioners, Locations
* Vital Area - Treatments, Conditions, Knowledge
* Knowledge Type - Knowledge only
* Evidence Level - Treatments, Knowledge
* Patient Profile - Conditions, Knowledge
* Remedies - Treatments, Conditions, Knowledge

= Structured meta fields =

Every content type has a native "Tibb House Details" meta box with
purpose-built fields (price, duration, booking URL, FAQ repeater, hero
image, symptoms, causes, qualifications, address, opening hours, and more -
see the full field list in the plugin header comment of each `includes/`
class). All fields are sanitized on save (nonce + capability checked) and
exposed automatically through `register_meta()`'s REST support.

= Relationships =

Dynamic, mostly one-directional relationships that are resolved in both
directions automatically and rendered as "Related Content" sections:

* Treatment <-> Condition
* Condition <-> Knowledge
* Condition <-> Remedy (via shared Remedies taxonomy terms)
* Knowledge <-> Remedy (via shared Remedies taxonomy terms)
* Practitioner <-> Treatment
* Practitioner <-> Location

= Gutenberg blocks =

Hero, CTA, FAQ, Testimonials, Booking Form, Card Grid, Related Content,
Three Layer, and Disclaimer - all server-rendered via PHP `render_callback`s
with a lightweight JS editor preview (no build step required).

= Templates =

Native `single-*.php` templates for each content type plus `archive.php` and
`page.php`, loaded automatically via a `template_include` filter. Themes can
override any template by placing a same-named file inside a `tibbhouse/`
subfolder in the active theme.

= REST API =

All custom fields are exposed via `register_meta()`. Two convenience fields
are added on top for headless clients:

* `tibbhouse_related` - resolved relationships grouped by label
* `tibbhouse_taxonomies` - all taxonomy terms grouped by taxonomy slug

= Performance & standards =

Transient caching for expensive reverse-relationship lookups, proper
escaping and sanitization throughout, nonce verification and capability
checks on every save, translation-ready strings, and adherence to WordPress
Coding Standards.

== Installation ==

1. Upload the `tibbhouse-core` folder to `/wp-content/plugins/`.
2. Activate the plugin through the "Plugins" screen in WordPress.
3. Visit any of the new "Treatments", "Conditions", "Knowledge",
   "Practitioners" or "Locations" menu items to start adding content.
4. (Optional) Copy any `templates/*.php` file into a `tibbhouse/` folder in
   your active theme to customize markup while keeping the plugin logic.

== Changelog ==

= 1.0.0 =
* Initial production-ready release: CPTs, taxonomies, native meta boxes,
  relationships, REST exposure, Gutenberg blocks, and template loader.
