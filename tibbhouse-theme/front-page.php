<?php
/**
 * Front page template — Tibb House homepage.
 *
 * Displays a hero banner, then featured grids for Treatments,
 * Conditions, and Practitioners pulled live from the plugin's CPTs.
 *
 * The actual layout lives in inc/homepage-render.php so it can be shared
 * with the auto-generated "TIBB FRONT PAGE REPLIT" page template — both
 * render an identical result.
 *
 * @package Tibbhouse
 */

get_header();
tibbhouse_render_homepage();
get_footer();
