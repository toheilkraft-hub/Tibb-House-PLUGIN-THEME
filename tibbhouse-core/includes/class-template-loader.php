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

		// Final safety net: `template_include` runs *after* single_template /
		// archive_template / taxonomy_template in WordPress's resolution
		// order, and themes such as Astra hook it at a very high priority
		// to force their own layout, silently discarding whatever those
		// earlier filters returned. Re-assert our template here, last,
		// with the maximum possible priority so nothing downstream of us
		// can override it again.
		add_filter( 'template_include', array( $this, 'load_template_include' ), PHP_INT_MAX );
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

	/**
	 * Filter callback for `template_include`.
	 *
	 * Runs last in WordPress's template resolution chain. Re-applies the
	 * same single/archive/taxonomy routing so that a theme (e.g. Astra)
	 * hooking `template_include` at a high priority cannot silently
	 * discard the template chosen above.
	 *
	 * @param string $template Template path resolved so far.
	 * @return string
	 */
	public function load_template_include( $template ) {
		if ( is_singular( array_keys( $this->get_single_template_map() ) ) ) {
			$file = $this->load_single_template( $template );
			if ( $file !== $template && file_exists( $file ) ) {
				return $file;
			}
		}

		$post_types = Tibbhouse_Helpers::post_types();
		$taxonomies = array( 'constitutional_type', 'vital_area', 'knowledge_type', 'evidence_level', 'patient_profile', 'remedies' );

		if ( is_post_type_archive( $post_types ) || is_tax( $taxonomies ) ) {
			$file = $this->load_archive_template( $template );
			if ( $file !== $template && file_exists( $file ) ) {
				return $file;
			}
		}

		return $template;
	}
}
