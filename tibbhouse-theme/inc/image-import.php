<?php
/**
 * Bundled image importer.
 *
 * Copies images that ship with the theme (assets/img/bundled/) into the
 * WordPress media library the first time the theme is activated — so the
 * site looks complete out of the box with no manual uploads required.
 *
 * Safe to call multiple times: guards against duplicates by checking whether
 * an attachment with the same filename already exists.
 *
 * @package Tibbhouse
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'TIBBHOUSE_IMG_IMPORT_FLAG', 'tibbhouse_bundled_images_imported_v1' );

/**
 * Import all images from assets/img/bundled/ into the WP media library.
 * Skips any file already present (matched by filename). Runs once per install.
 */
function tibbhouse_import_bundled_images() {
	if ( get_option( TIBBHOUSE_IMG_IMPORT_FLAG ) ) {
		return;
	}

	$source_dir = get_template_directory() . '/assets/img/bundled/';
	if ( ! is_dir( $source_dir ) ) {
		return;
	}

	require_once ABSPATH . 'wp-admin/includes/image.php';
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';

	$files = glob( $source_dir . '*.{jpg,jpeg,png,webp}', GLOB_BRACE );
	if ( ! $files ) {
		return;
	}

	// Mark as imported first so a crash cannot trigger a retry loop.
	update_option( TIBBHOUSE_IMG_IMPORT_FLAG, time() );

	foreach ( $files as $file_path ) {
		$filename = basename( $file_path );

		// Skip if an attachment with this filename already exists.
		$existing = new WP_Query( array(
			'post_type'              => 'attachment',
			'post_status'            => 'inherit',
			'title'                  => pathinfo( $filename, PATHINFO_FILENAME ),
			'posts_per_page'         => 1,
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		) );
		if ( $existing->have_posts() ) {
			continue;
		}

		$filetype = wp_check_filetype( $filename );
		if ( empty( $filetype['type'] ) ) {
			continue;
		}

		$upload = wp_upload_bits( $filename, null, file_get_contents( $file_path ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		if ( ! empty( $upload['error'] ) ) {
			continue;
		}

		$attachment_id = wp_insert_attachment(
			array(
				'post_mime_type' => $filetype['type'],
				'post_title'     => sanitize_file_name( pathinfo( $filename, PATHINFO_FILENAME ) ),
				'post_content'   => '',
				'post_status'    => 'inherit',
			),
			$upload['file']
		);

		if ( is_wp_error( $attachment_id ) || ! $attachment_id ) {
			continue;
		}

		$meta = wp_generate_attachment_metadata( $attachment_id, $upload['file'] );
		wp_update_attachment_metadata( $attachment_id, $meta );
	}
}

// Run on theme activation and on every admin_init as a safety net for
// in-place theme file updates that don't re-trigger after_switch_theme.
add_action( 'after_switch_theme', 'tibbhouse_import_bundled_images' );
add_action( 'admin_init',         'tibbhouse_import_bundled_images' );
