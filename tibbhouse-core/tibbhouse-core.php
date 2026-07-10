<?php
/**
 * Plugin Name:       Tibb House Core
 * Plugin URI:        https://tibbhouse.example
 * Description:       Production-ready core plugin powering the Tibb House content platform: Treatments, Conditions, Knowledge, Practitioners and Locations, with native meta boxes, taxonomies, relationships, REST exposure and Gutenberg blocks. Built with native WordPress APIs only (no ACF, no third-party CPT plugins).
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Tibb House
 * Text Domain:       tibbhouse-core
 * Domain Path:       /languages
 * License:           GPL v2 or later
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // No direct access.
}

define( 'TIBBHOUSE_CORE_VERSION', '1.0.0' );
define( 'TIBBHOUSE_CORE_FILE', __FILE__ );
define( 'TIBBHOUSE_CORE_PATH', plugin_dir_path( __FILE__ ) );
define( 'TIBBHOUSE_CORE_URL', plugin_dir_url( __FILE__ ) );
define( 'TIBBHOUSE_CORE_BASENAME', plugin_basename( __FILE__ ) );

/**
 * Main plugin bootstrap class.
 *
 * Loads every subsystem (CPTs, taxonomies, meta boxes, relationships,
 * REST API, Gutenberg blocks, template loader) and wires up shared
 * activation / i18n behaviour.
 */
final class Tibbhouse_Core {

	/**
	 * Singleton instance.
	 *
	 * @var Tibbhouse_Core|null
	 */
	private static $instance = null;

	/**
	 * Get the singleton instance.
	 *
	 * @return Tibbhouse_Core
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor - private, use instance().
	 */
	private function __construct() {
		$this->includes();
		$this->init_hooks();
	}

	/**
	 * Require all subsystem class files.
	 */
	private function includes() {
		require_once TIBBHOUSE_CORE_PATH . 'includes/class-helpers.php';
		require_once TIBBHOUSE_CORE_PATH . 'includes/class-cpts.php';
		require_once TIBBHOUSE_CORE_PATH . 'includes/class-taxonomies.php';
		require_once TIBBHOUSE_CORE_PATH . 'includes/class-fields.php';
		require_once TIBBHOUSE_CORE_PATH . 'includes/class-relationships.php';
		require_once TIBBHOUSE_CORE_PATH . 'includes/class-rest.php';
		require_once TIBBHOUSE_CORE_PATH . 'includes/class-blocks.php';
		require_once TIBBHOUSE_CORE_PATH . 'includes/class-template-loader.php';
	}

	/**
	 * Register global plugin hooks and boot subsystems.
	 */
	private function init_hooks() {
		add_action( 'init', array( $this, 'load_textdomain' ) );

		register_activation_hook( TIBBHOUSE_CORE_FILE, array( $this, 'activate' ) );
		register_deactivation_hook( TIBBHOUSE_CORE_FILE, array( $this, 'deactivate' ) );

		// Boot subsystems. Order matters: CPTs and taxonomies must exist
		// before fields/relationships/REST/blocks try to attach to them.
		Tibbhouse_CPTs::instance();
		Tibbhouse_Taxonomies::instance();
		Tibbhouse_Fields::instance();
		Tibbhouse_Relationships::instance();
		Tibbhouse_Rest::instance();
		Tibbhouse_Blocks::instance();
		Tibbhouse_Template_Loader::instance();
	}

	/**
	 * Load the plugin translation files.
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'tibbhouse-core', false, dirname( TIBBHOUSE_CORE_BASENAME ) . '/languages' );
	}

	/**
	 * Activation callback: register CPTs/taxonomies then flush rewrite rules.
	 */
	public function activate() {
		Tibbhouse_CPTs::instance()->register_post_types();
		Tibbhouse_Taxonomies::instance()->register_taxonomies();
		flush_rewrite_rules();
	}

	/**
	 * Deactivation callback: flush rewrite rules to clean up.
	 */
	public function deactivate() {
		flush_rewrite_rules();
	}
}

/**
 * Boot the plugin.
 *
 * @return Tibbhouse_Core
 */
function tibbhouse_core() {
	return Tibbhouse_Core::instance();
}
tibbhouse_core();
