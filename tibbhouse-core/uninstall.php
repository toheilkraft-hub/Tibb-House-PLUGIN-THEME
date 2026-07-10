<?php
/**
 * Uninstall handler.
 *
 * Runs only when the plugin is deleted from the Plugins screen (not on
 * deactivation). Removes the plugin's post types' content, taxonomy terms,
 * and any transients it created, so a full uninstall leaves no residue.
 *
 * @package Tibbhouse_Core
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

global $wpdb;

$post_types = array( 'treatments', 'conditions', 'knowledge', 'practitioners', 'locations' );

foreach ( $post_types as $post_type ) {
	$posts = get_posts(
		array(
			'post_type'      => $post_type,
			'posts_per_page' => -1,
			'post_status'    => 'any',
			'fields'         => 'ids',
		)
	);

	foreach ( $posts as $post_id ) {
		wp_delete_post( $post_id, true );
	}
}

$taxonomies = array( 'constitutional_type', 'vital_area', 'knowledge_type', 'evidence_level', 'patient_profile', 'remedies' );

foreach ( $taxonomies as $taxonomy ) {
	$terms = get_terms(
		array(
			'taxonomy'   => $taxonomy,
			'hide_empty' => false,
			'fields'     => 'ids',
		)
	);

	if ( ! is_wp_error( $terms ) ) {
		foreach ( $terms as $term_id ) {
			wp_delete_term( $term_id, $taxonomy );
		}
	}
}

// Remove any transients created by the relationship cache.
// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_tibbhouse_%' OR option_name LIKE '_transient_timeout_tibbhouse_%'" );
