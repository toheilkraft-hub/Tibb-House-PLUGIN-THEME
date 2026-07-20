<?php
/**
 * Auto-creates the "TIBB FRONT PAGE REPLIT" Page on theme activation.
 *
 * The page uses the "Homepage (Replit Design)" page template
 * (template-front-replit.php), which renders the same live homepage
 * layout as front-page.php. This lets the site owner immediately assign
 * it as the static homepage from Settings → Reading without having to
 * rebuild the layout by hand.
 *
 * @package Tibbhouse
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'TIBBHOUSE_REPLIT_PAGE_TITLE', 'TIBB FRONT PAGE REPLIT' );

/**
 * Create the "TIBB FRONT PAGE REPLIT" page if it doesn't already exist.
 *
 * Runs on theme activation (`after_switch_theme`). Content is left blank
 * because the layout is rendered entirely by the page template — the
 * template already pulls Treatments, Conditions, Practitioners, etc. live
 * from the Tibb House Core plugin, so there is nothing to duplicate into
 * post_content.
 */
function tibbhouse_maybe_create_replit_front_page() {
	$existing_q = new WP_Query( array( 'post_type' => 'page', 'title' => TIBBHOUSE_REPLIT_PAGE_TITLE, 'posts_per_page' => 1, 'no_found_rows' => true, 'update_post_meta_cache' => false, 'update_post_term_cache' => false ) );
	$existing   = $existing_q->have_posts() ? $existing_q->posts[0] : null;
	if ( $existing ) {
		// Page already exists: make sure it still uses the homepage template
		// (in case it was created before the template file existed).
		if ( 'template-front-replit.php' !== get_page_template_slug( $existing->ID ) ) {
			update_post_meta( $existing->ID, '_wp_page_template', 'template-front-replit.php' );
		}
		return;
	}

	$page_id = wp_insert_post(
		array(
			'post_title'   => TIBBHOUSE_REPLIT_PAGE_TITLE,
			'post_name'    => sanitize_title( TIBBHOUSE_REPLIT_PAGE_TITLE ),
			'post_status'  => 'publish',
			'post_type'    => 'page',
			'post_content' => '',
			'post_author'  => get_current_user_id() ? get_current_user_id() : 1,
			'comment_status' => 'closed',
			'ping_status'    => 'closed',
		),
		true
	);

	if ( is_wp_error( $page_id ) || ! $page_id ) {
		return;
	}

	update_post_meta( $page_id, '_wp_page_template', 'template-front-replit.php' );
}
add_action( 'after_switch_theme', 'tibbhouse_maybe_create_replit_front_page' );

/**
 * Safety net: also run the check on admin_init.
 *
 * `after_switch_theme` only fires when the theme is freshly activated. If
 * the theme files are instead updated in place (FTP/zip re-upload) while
 * already active, that hook never runs again. This runs the same
 * idempotent check (get_page_by_title guard) on every wp-admin load so the
 * "TIBB FRONT PAGE REPLIT" page still gets created after an in-place
 * update, without ever creating duplicates.
 */
add_action( 'admin_init', 'tibbhouse_maybe_create_replit_front_page' );
