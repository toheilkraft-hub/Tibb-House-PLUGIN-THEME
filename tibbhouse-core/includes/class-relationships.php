<?php
/**
 * Dynamic bidirectional relationships between content types.
 *
 * The plugin stores relationships as one-directional post-meta arrays
 * (defined in class-fields.php), then resolves the reverse direction here
 * on demand so authors never manage both sides manually.
 *
 * Relationship pairs implemented:
 *   Treatment   <-> Condition     (th_related_conditions / th_treatment_relationships)
 *   Condition   <-> Knowledge     (th_knowledge_relationships)
 *   Condition   <-> Remedy        (via `remedies` taxonomy - shared terms)
 *   Knowledge   <-> Remedy        (via `remedies` taxonomy - shared terms)
 *   Practitioner<-> Treatment     (via th_treatments_offered on Locations + practitioner clinic)
 *   Practitioner<-> Location      (th_clinic_location / th_practitioners)
 *
 * @package Tibbhouse_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Resolves and renders dynamic related-content sections.
 */
class Tibbhouse_Relationships {

	/**
	 * Singleton instance.
	 *
	 * @var Tibbhouse_Relationships|null
	 */
	private static $instance = null;

	/**
	 * Get the singleton instance.
	 *
	 * @return Tibbhouse_Relationships
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Hook the automatic content injection.
	 */
	private function __construct() {
		add_filter( 'the_content', array( $this, 'maybe_append_related_content' ) );
	}

	/**
	 * Get posts explicitly related via a meta key that stores an array of post IDs.
	 *
	 * @param int    $post_id  Source post ID.
	 * @param string $meta_key Meta key holding related post IDs.
	 * @return WP_Post[]
	 */
	public function get_related_by_meta( $post_id, $meta_key ) {
		$ids = get_post_meta( $post_id, $meta_key, true );
		$ids = is_array( $ids ) ? array_filter( array_map( 'absint', $ids ) ) : array();

		if ( empty( $ids ) ) {
			return array();
		}

		return get_posts(
			array(
				'post_type'      => 'any',
				'post__in'       => $ids,
				'orderby'        => 'post__in',
				'posts_per_page' => -1,
				'post_status'    => 'publish',
			)
		);
	}

	/**
	 * Reverse-lookup: find posts of $post_type whose $meta_key array contains $post_id.
	 *
	 * Cached via transient because meta_value LIKE scans do not use an index.
	 *
	 * @param int    $post_id   Target post ID being searched for.
	 * @param string $post_type Post type to search within.
	 * @param string $meta_key  Meta key that stores the relationship array.
	 * @return WP_Post[]
	 */
	public function get_reverse_related( $post_id, $post_type, $meta_key ) {
		$cache_key = "tibbhouse_reverse_{$meta_key}_{$post_id}";

		return Tibbhouse_Helpers::cached(
			$cache_key,
			function () use ( $post_id, $post_type, $meta_key ) {
				$query = new WP_Query(
					array(
						'post_type'      => $post_type,
						'posts_per_page' => -1,
						'post_status'    => 'publish',
						'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
							array(
								'key'     => $meta_key,
								'value'   => sprintf( ':%d;', $post_id ),
								'compare' => 'LIKE',
							),
						),
						'fields'         => 'all',
					)
				);
				return $query->posts;
			},
			HOUR_IN_SECONDS
		);
	}

	/**
	 * Get posts of $post_type sharing at least one term with $post_id on $taxonomy.
	 *
	 * Used for the taxonomy-driven Condition<->Remedy and Knowledge<->Remedy links.
	 *
	 * @param int    $post_id   Source post ID.
	 * @param string $taxonomy  Shared taxonomy, e.g. 'remedies'.
	 * @param string $post_type Post type to look for matches in.
	 * @return WP_Post[]
	 */
	public function get_related_by_taxonomy( $post_id, $taxonomy, $post_type ) {
		$terms = wp_get_post_terms( $post_id, $taxonomy, array( 'fields' => 'ids' ) );

		if ( is_wp_error( $terms ) || empty( $terms ) ) {
			return array();
		}

		return get_posts(
			array(
				'post_type'      => $post_type,
				'post__not_in'   => array( $post_id ),
				'posts_per_page' => 6,
				'post_status'    => 'publish',
				'tax_query'      => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
					array(
						'taxonomy' => $taxonomy,
						'field'    => 'term_id',
						'terms'    => $terms,
					),
				),
			)
		);
	}

	/**
	 * Build the full set of related-content groups for the given post.
	 *
	 * @param int    $post_id   Post ID.
	 * @param string $post_type Post type.
	 * @return array<string, WP_Post[]> Map of section label => posts.
	 */
	public function get_related_groups( $post_id, $post_type ) {
		$groups = array();

		switch ( $post_type ) {
			case 'treatments':
				$groups[ __( 'Related Conditions', 'tibbhouse-core' ) ] = $this->get_related_by_meta( $post_id, 'th_related_conditions' );
				$groups[ __( 'Practitioners Offering This', 'tibbhouse-core' ) ] = $this->get_reverse_related( $post_id, 'locations', 'th_treatments_offered' );
				break;

			case 'conditions':
				$groups[ __( 'Related Treatments', 'tibbhouse-core' ) ]       = $this->get_reverse_related( $post_id, 'treatments', 'th_related_conditions' );
				$groups[ __( 'Related Knowledge', 'tibbhouse-core' ) ]        = $this->get_related_by_meta( $post_id, 'th_knowledge_relationships' );
				$groups[ __( 'Related Remedies', 'tibbhouse-core' ) ]         = $this->get_related_by_taxonomy( $post_id, 'remedies', 'treatments' );
				break;

			case 'knowledge':
				$groups[ __( 'Related Conditions', 'tibbhouse-core' ) ] = $this->get_reverse_related( $post_id, 'conditions', 'th_knowledge_relationships' );
				$groups[ __( 'Related Remedies', 'tibbhouse-core' ) ]   = $this->get_related_by_taxonomy( $post_id, 'remedies', 'treatments' );
				break;

			case 'practitioners':
				$groups[ __( 'Clinic Location', 'tibbhouse-core' ) ]   = $this->get_related_by_meta( $post_id, 'th_clinic_location' );
				$groups[ __( 'Locations Offering', 'tibbhouse-core' ) ] = $this->get_reverse_related( $post_id, 'locations', 'th_practitioners' );
				break;

			case 'locations':
				$groups[ __( 'Treatments Offered', 'tibbhouse-core' ) ] = $this->get_related_by_meta( $post_id, 'th_treatments_offered' );
				$groups[ __( 'Practitioners', 'tibbhouse-core' ) ]      = $this->get_related_by_meta( $post_id, 'th_practitioners' );
				break;
		}

		return array_filter( $groups );
	}

	/**
	 * Render a related-content section as a simple card grid of links.
	 *
	 * @param int    $post_id   Post ID.
	 * @param string $post_type Post type.
	 * @return string HTML.
	 */
	public function render_related_content( $post_id, $post_type ) {
		$groups = $this->get_related_groups( $post_id, $post_type );

		if ( empty( $groups ) ) {
			return '';
		}

		// Determine whether any group has posts.
		$has_any = false;
		foreach ( $groups as $posts ) {
			if ( ! empty( $posts ) ) {
				$has_any = true;
				break;
			}
		}
		if ( ! $has_any ) {
			return '';
		}

		// Map post type slugs to readable single labels.
		$type_labels = array(
			'treatments'    => _x( 'Treatment', 'related card label', 'tibbhouse-core' ),
			'conditions'    => _x( 'Condition', 'related card label', 'tibbhouse-core' ),
			'knowledge'     => _x( 'Article', 'related card label', 'tibbhouse-core' ),
			'practitioners' => _x( 'Practitioner', 'related card label', 'tibbhouse-core' ),
			'locations'     => _x( 'Location', 'related card label', 'tibbhouse-core' ),
		);

		ob_start();
		?>
<section class="tibbhouse-related">
	<div class="tibbhouse-related-inner">
		<?php foreach ( $groups as $label => $posts ) : ?>
			<?php if ( empty( $posts ) ) { continue; } ?>
			<h2><?php echo esc_html( $label ); ?></h2>
			<div class="th-related-grid">
				<?php foreach ( $posts as $related_post ) : ?>
				<?php
				$rtype  = get_post_type( $related_post );
				$rlabel = isset( $type_labels[ $rtype ] ) ? $type_labels[ $rtype ] : $rtype;
				$thumb  = get_the_post_thumbnail( $related_post, 'medium' );
				?>
				<a class="th-related-card" href="<?php echo esc_url( get_permalink( $related_post ) ); ?>">
					<div class="th-related-card-thumb">
						<?php if ( $thumb ) : ?>
							<?php echo $thumb; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<?php else : ?>
							<div class="th-related-card-thumb-placeholder">
								<svg viewBox="0 0 24 24"><path d="M12 2a10 10 0 1 0 0 20A10 10 0 0 0 12 2zm1 14H11v-4h2v4zm0-6H11V8h2v2z"/></svg>
							</div>
						<?php endif; ?>
					</div>
					<div class="th-related-card-body">
						<div class="th-related-card-type"><?php echo esc_html( $rlabel ); ?></div>
						<div class="th-related-card-title"><?php echo esc_html( get_the_title( $related_post ) ); ?></div>
					</div>
				</a>
				<?php endforeach; ?>
			</div>
		<?php endforeach; ?>
	</div>
</section>
		<?php
		return ob_get_clean();
	}

	/**
	 * Auto-append the related content block to the_content on singular Tibb House CPTs,
	 * so it works even for themes/templates that don't use our template files directly.
	 *
	 * @param string $content Post content.
	 * @return string
	 */
	public function maybe_append_related_content( $content ) {
		if ( ! is_singular( Tibbhouse_Helpers::post_types() ) || ! in_the_loop() || ! is_main_query() ) {
			return $content;
		}

		return $content . $this->render_related_content( get_the_ID(), get_post_type() );
	}
}
