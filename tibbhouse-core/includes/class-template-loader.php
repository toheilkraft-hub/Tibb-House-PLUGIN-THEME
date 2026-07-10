<?php
/**
 * Native template loader.
 *
 * Routes single/archive views for the plugin's CPTs to templates in
 * /templates, unless the active theme provides an override at
 * {theme}/tibbhouse/{template}.
 *
 * @package Tibbhouse_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Hooks into `template_include` to serve plugin templates.
 */
class Tibbhouse_Template_Loader {

	/**
	 * Singleton instance.
	 *
	 * @var Tibbhouse_Template_Loader|null
	 */
	private static $instance = null;

	/**
	 * Get the singleton instance.
	 *
	 * @return Tibbhouse_Template_Loader
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Hook into template_include.
	 */
	private function __construct() {
		add_filter( 'template_include', array( $this, 'load_template' ) );
	}

	/**
	 * Decide which template file to serve for the current request.
	 *
	 * @param string $template Default resolved template path.
	 * @return string
	 */
	public function load_template( $template ) {
		$post_types = Tibbhouse_Helpers::post_types();

		if ( is_singular( $post_types ) ) {
			$file = Tibbhouse_Helpers::locate_template( 'single-' . get_post_type() . '.php' );
			if ( file_exists( $file ) ) {
				return $file;
			}
		}

		if ( is_post_type_archive( $post_types ) || is_tax( array( 'constitutional_type', 'vital_area', 'knowledge_type', 'evidence_level', 'patient_profile', 'remedies' ) ) ) {
			$file = Tibbhouse_Helpers::locate_template( 'archive.php' );
			if ( file_exists( $file ) ) {
				return $file;
			}
		}

		return $template;
	}
}
