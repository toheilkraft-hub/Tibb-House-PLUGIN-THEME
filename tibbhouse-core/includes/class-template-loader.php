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
 * Hooks into `single_template` (and `archive_template` /
 * `taxonomy_template`) to serve plugin templates.
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
	 * Hook into single_template (with a late priority so it wins over
	 * themes such as Astra that also filter single_template) and into
	 * archive_template / taxonomy_template for archive views.
	 */
	private function __construct() {
		add_filter( 'single_template', array( $this, 'load_single_template' ), 99 );
		add_filter( 'archive_template', array( $this, 'load_archive_template' ), 99 );
		add_filter( 'taxonomy_template', array( $this, 'load_archive_template' ), 99 );
	}

	/**
	 * Map of post type => template filename, single source of truth so
	 * new CPTs only need an entry here to get their own template.
	 *
	 * @return array<string, string>
	 */
	protected function get_single_template_map() {
		return array(
			'treatments'    => 'single-treatments.php',
			'conditions'    => 'single-conditions.php',
			'knowledge'     => 'single-knowledge.php',
			'practitioners' => 'single-practitioners.php',
			'locations'     => 'single-locations.php',
		);
	}

	/**
	 * Filter callback for `single_template`.
	 *
	 * Intercepts single-post views for the plugin's CPTs and swaps in the
	 * matching template from /templates, unless the active theme provides
	 * an override at {theme}/tibbhouse/{template}.
	 *
	 * @param string $template Template path resolved so far.
	 * @return string
	 */
	public function load_single_template( $template ) {
		$post_type = get_post_type();

		if ( ! $post_type ) {
			return $template;
		}

		$map = $this->get_single_template_map();

		if ( ! isset( $map[ $post_type ] ) ) {
			return $template;
		}

		$file = Tibbhouse_Helpers::locate_template( $map[ $post_type ] );

		if ( file_exists( $file ) ) {
			return $file;
		}

		return $template;
	}

	/**
	 * Filter callback for `archive_template` / `taxonomy_template`.
	 *
	 * Routes CPT archives and the plugin's taxonomy term pages to the
	 * shared archive.php template.
	 *
	 * @param string $template Template path resolved so far.
	 * @return string
	 */
	public function load_archive_template( $template ) {
		$post_types = Tibbhouse_Helpers::post_types();
		$taxonomies = array( 'constitutional_type', 'vital_area', 'knowledge_type', 'evidence_level', 'patient_profile', 'remedies' );

		if ( ! is_post_type_archive( $post_types ) && ! is_tax( $taxonomies ) ) {
			return $template;
		}

		$file = Tibbhouse_Helpers::locate_template( 'archive.php' );

		if ( file_exists( $file ) ) {
			return $file;
		}

		return $template;
	}
}
