---
name: WordPress template filter resolution order
description: Why hooking only single_template/archive_template/taxonomy_template is not enough to override a theme's default template; template_include runs last and can silently discard the earlier choice.
---

WordPress resolves the template file for a request in stages: `get_single_template()` /
`get_archive_template()` / `get_taxonomy_template()` run first and apply the
`single_template` / `archive_template` / `taxonomy_template` filters respectively. The
result of that stage is then passed through the `template_include` filter, which is the
true final say on what file gets `include`d.

Themes (Astra observed doing this, likely common among page-builder-oriented themes) hook
`template_include` at a high priority to enforce their own layout system. If a plugin only
hooks `single_template` etc., the theme's `template_include` callback can silently replace
the plugin's chosen template later in the chain — the plugin's filter appears to "not work"
even though it fires and returns the right path.

**Why:** Discovered when a custom plugin's `single_template` override (correctly scoped,
correct file paths) was still losing to Astra's default single-post layout on the frontend.
Adding a second, redundant-looking filter fixed it.

**How to apply:** When building a WP plugin template loader, always add a final
`template_include` filter at `PHP_INT_MAX` priority that re-asserts the same routing
decision, in addition to (not instead of) the more specific `single_template` /
`archive_template` / `taxonomy_template` filters. Keep the specific filters too — they
preserve compatibility with theme override conventions (e.g. `locate_template()` chains)
that inspect those earlier stages.
