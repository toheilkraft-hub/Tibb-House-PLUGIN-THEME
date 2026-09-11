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

		// Ensure CPT archives always show all posts, not just the WP
		// "Blog pages show at most N" reading setting (which defaults to 10
		// and confuses users who expect to see every seeded entry).
		add_action( 'pre_get_posts', array( $this, 'set_cpt_archive_posts_per_page' ) );
	}

	/**
	 * Remove the reading-settings post limit on Tibb House CPT archives.
	 *
	 * @param WP_Query $query The current query.
	 */
	public function set_cpt_archive_posts_per_page( $query ) {
		if ( is_admin() || ! $query->is_main_query() ) {
			return;
		}
		$cpts = array( 'treatments', 'conditions', 'knowledge', 'practitioners', 'locations' );
		if ( $query->is_post_type_archive( $cpts ) ) {
			$query->set( 'posts_per_page', -1 );

			$post_type = $query->get( 'post_type' );
			if ( is_array( $post_type ) ) {
				$post_type = reset( $post_type );
			}

			if ( in_array( $post_type, array( 'treatments', 'conditions', 'knowledge' ), true ) ) {
				$this->apply_archive_filters( $query, $post_type );
			}
		}
	}

	/**
	 * Apply the public Treatments / Conditions / Knowledge archive controls to the main
	 * query. Filters are intentionally GET-based so they remain bookmarkable
	 * and work without an extra AJAX endpoint.
	 *
	 * @param WP_Query $query     The main archive query.
	 * @param string   $post_type Archive post type.
	 */
	private function apply_archive_filters( $query, $post_type ) {
		$tax_query  = (array) $query->get( 'tax_query' );
		$meta_query = (array) $query->get( 'meta_query' );
		$age        = isset( $_GET['th_age'] ) ? sanitize_title( wp_unslash( $_GET['th_age'] ) ) : '';
		$sort       = isset( $_GET['th_sort'] ) ? sanitize_key( wp_unslash( $_GET['th_sort'] ) ) : '';

		if ( $age && term_exists( $age, 'patient_profile' ) ) {
			$tax_query[] = array(
				'taxonomy' => 'patient_profile',
				'field'    => 'slug',
				'terms'    => $age,
			);
		}

		if ( in_array( $post_type, array( 'treatments', 'conditions' ), true ) ) {
			$category = isset( $_GET['th_category'] ) ? sanitize_title( wp_unslash( $_GET['th_category'] ) ) : '';
			if ( $category && term_exists( $category, 'vital_area' ) ) {
				$tax_query[] = array(
					'taxonomy' => 'vital_area',
					'field'    => 'slug',
					'terms'    => $category,
				);
			}
		}

		if ( 'conditions' === $post_type ) {
			$remedy = isset( $_GET['th_remedy'] ) ? sanitize_title( wp_unslash( $_GET['th_remedy'] ) ) : '';
			if ( $remedy && term_exists( $remedy, 'remedies' ) ) {
				$tax_query[] = array(
					'taxonomy' => 'remedies',
					'field'    => 'slug',
					'terms'    => $remedy,
				);
			}
		}

		if ( 'knowledge' === $post_type ) {
			$type = isset( $_GET['th_type'] ) ? sanitize_title( wp_unslash( $_GET['th_type'] ) ) : '';
			if ( $type && term_exists( $type, 'knowledge_type' ) ) {
				$tax_query[] = array(
					'taxonomy' => 'knowledge_type',
					'field'    => 'slug',
					'terms'    => $type,
				);
			}
		}

		if ( count( $tax_query ) > 1 ) {
			$tax_query['relation'] = 'AND';
			$query->set( 'tax_query', $tax_query );
		} elseif ( ! empty( $tax_query ) ) {
			$query->set( 'tax_query', $tax_query );
		}

		if ( 'knowledge' === $post_type ) {
			$related_treatment = isset( $_GET['th_related_treatment'] ) ? absint( $_GET['th_related_treatment'] ) : 0;
			$related_condition = isset( $_GET['th_related_condition'] ) ? absint( $_GET['th_related_condition'] ) : 0;

			if ( $related_treatment ) {
				$meta_query[] = array(
					'key'     => 'th_related_treatments',
					'value'   => sprintf( ':%d;', $related_treatment ),
					'compare' => 'LIKE',
				);
			}

			if ( $related_condition ) {
				$knowledge_ids = get_posts(
					array(
						'post_type'      => 'knowledge',
						'post_status'    => 'publish',
						'posts_per_page' => -1,
						'fields'         => 'ids',
						'meta_query'     => array(
							array(
								'key'     => 'th_related_conditions',
								'value'   => sprintf( ':%d;', $related_condition ),
								'compare' => 'LIKE',
							),
						),
					)
				);

				// Include the pre-existing Condition → Knowledge relationship
				// while new articles use the direct Knowledge field above.
				$legacy_ids = get_post_meta( $related_condition, 'th_knowledge_relationships', true );
				if ( is_array( $legacy_ids ) ) {
					$knowledge_ids = array_merge( $knowledge_ids, array_map( 'absint', $legacy_ids ) );
				}

				$query->set( 'post__in', array_values( array_unique( array_filter( array_map( 'absint', $knowledge_ids ) ) ) ) ?: array( 0 ) );
			}
		}

		if ( ! empty( $meta_query ) ) {
			$query->set( 'meta_query', $meta_query );
		}

		switch ( $sort ) {
			case 'oldest':
				$query->set( 'orderby', 'date' );
				$query->set( 'order', 'ASC' );
				break;
			case 'title':
				$query->set( 'orderby', 'title' );
				$query->set( 'order', 'ASC' );
				break;
			case 'recommended':
				$query->set( 'meta_key', 'th_priority' );
				$query->set( 'orderby', 'meta_value_num' );
				$query->set( 'order', 'DESC' );
				break;
			case 'newest':
			default:
				$query->set( 'orderby', 'date' );
				$query->set( 'order', 'DESC' );
				break;
		}
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
	 *
	 * Loaded on every page (not just Tibb House CPT views) because the
	 * sitewide preloader, and its logo animation, live in this stylesheet
	 * and need to be present everywhere. The rest of the design system is
	 * scoped with specific classes (.tibbhouse-archive-wrap, .th-hero-wrap,
	 * etc.) so loading it globally is safe.
	 */
	public function enqueue_assets() {
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
		$logo_url = TIBBHOUSE_CORE_URL . 'assets/img/logo-mark.png';
		return '
<div class="th-preloader-ring">
	<img class="th-preloader-logo" src="' . esc_url( $logo_url ) . '" alt="Tibb House" width="120" height="120">
</div>
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
