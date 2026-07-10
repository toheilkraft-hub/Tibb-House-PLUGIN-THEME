<?php
/**
 * Frontend asset enqueue + preloader injection.
 *
 * Loads the Tibb House design-system CSS/JS on all pages where our
 * custom post types or taxonomies are being displayed.
 *
 * @package Tibbhouse_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles front-end asset registration and the global preloader markup.
 */
class Tibbhouse_Frontend {

	/**
	 * Singleton instance.
	 *
	 * @var Tibbhouse_Frontend|null
	 */
	private static $instance = null;

	/**
	 * Get the singleton instance.
	 *
	 * @return Tibbhouse_Frontend
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Hook into WordPress.
	 */
	private function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_action( 'wp_body_open',       array( $this, 'render_preloader' ) );

		// Fallback for themes that don't call wp_body_open.
		add_action( 'wp_footer', array( $this, 'maybe_render_preloader_fallback' ) );
	}

	/**
	 * Whether we are on a Tibb House page (single CPT or archive/taxonomy).
	 *
	 * @return bool
	 */
	private function is_tibbhouse_page() {
		$cpts       = Tibbhouse_Helpers::post_types();
		$taxonomies = array( 'constitutional_type', 'vital_area', 'knowledge_type', 'evidence_level', 'patient_profile', 'remedies' );

		return is_singular( $cpts )
			|| is_post_type_archive( $cpts )
			|| is_tax( $taxonomies );
	}

	/**
	 * Enqueue front-end styles and scripts.
	 */
	public function enqueue_assets() {
		if ( ! $this->is_tibbhouse_page() ) {
			return;
		}

		wp_enqueue_style(
			'tibbhouse-frontend',
			TIBBHOUSE_CORE_URL . 'assets/css/frontend.css',
			array(),
			TIBBHOUSE_CORE_VERSION
		);

		// Keep the original blocks.css too
		wp_enqueue_style(
			'tibbhouse-blocks',
			TIBBHOUSE_CORE_URL . 'assets/css/blocks.css',
			array( 'tibbhouse-frontend' ),
			TIBBHOUSE_CORE_VERSION
		);

		wp_enqueue_script(
			'tibbhouse-frontend',
			TIBBHOUSE_CORE_URL . 'assets/js/frontend.js',
			array(),
			TIBBHOUSE_CORE_VERSION,
			true
		);
	}

	/**
	 * Render the preloader markup immediately after <body>.
	 */
	public function render_preloader() {
		if ( ! $this->is_tibbhouse_page() ) {
			return;
		}
		$this->preloader_html();
		$this->preloader_rendered = true;
	}

	/**
	 * Tracks whether the preloader was already rendered via wp_body_open.
	 *
	 * @var bool
	 */
	private $preloader_rendered = false;

	/**
	 * Fallback: inject preloader at footer start for themes without wp_body_open.
	 */
	public function maybe_render_preloader_fallback() {
		if ( $this->preloader_rendered ) {
			return;
		}
		if ( ! $this->is_tibbhouse_page() ) {
			return;
		}
		// Move preloader to body start via inline JS
		echo '<script>
(function(){
  var d=document.getElementById("tibbhouse-preloader");
  if(!d){
    var el=document.createElement("div");
    el.id="tibbhouse-preloader";
    el.innerHTML=' . wp_json_encode( $this->preloader_inner() ) . ';
    document.body.insertBefore(el,document.body.firstChild);
  }
})();
</script>';
	}

	/**
	 * The inner HTML of the preloader.
	 *
	 * @return string
	 */
	private function preloader_inner() {
		$logo_url = TIBBHOUSE_CORE_URL . 'assets/img/logo.svg';
		return '
<img class="th-preloader-logo" src="' . esc_url( $logo_url ) . '" alt="Tibb House" width="120" height="120">
<div class="th-preloader-name">Tibb House</div>
<div class="th-preloader-tagline">Natural &amp; Islamic Medicine</div>
<div class="th-preloader-bar"></div>';
	}

	/**
	 * Print the full preloader markup.
	 */
	private function preloader_html() {
		echo '<div id="tibbhouse-preloader">' . $this->preloader_inner() . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}
