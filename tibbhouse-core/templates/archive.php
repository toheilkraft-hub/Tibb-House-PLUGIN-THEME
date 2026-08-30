<?php
/**
 * Archive template for all Tibb House post types and taxonomies.
 *
 * @package Tibbhouse_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

// Determine post-type icon and accent per CPT.
$post_type    = get_query_var( 'post_type' );
if ( is_array( $post_type ) ) {
	$post_type = reset( $post_type );
}
if ( ! $post_type && is_tax() ) {
	$tax_obj   = get_queried_object();
	$post_type = '';
}

$type_labels = array(
	'treatments'   => esc_html__( 'Treatments', 'tibbhouse-core' ),
	'conditions'   => esc_html__( 'Conditions', 'tibbhouse-core' ),
	'knowledge'    => esc_html__( 'Knowledge', 'tibbhouse-core' ),
	'practitioners'=> esc_html__( 'Practitioners', 'tibbhouse-core' ),
	'locations'    => esc_html__( 'Locations', 'tibbhouse-core' ),
);

$is_filterable = is_post_type_archive( array( 'treatments', 'knowledge' ) );
$age           = isset( $_GET['th_age'] ) ? sanitize_title( wp_unslash( $_GET['th_age'] ) ) : '';
$price_filter  = isset( $_GET['th_price'] ) ? sanitize_key( wp_unslash( $_GET['th_price'] ) ) : '';
$archive_posts = isset( $GLOBALS['wp_query']->posts ) && is_array( $GLOBALS['wp_query']->posts ) ? $GLOBALS['wp_query']->posts : array();

// Price is stored as an intentionally flexible human-readable field
// ("From $60", "$60-$90", etc.), so the lightweight range filter is applied
// to the already-filtered main query in PHP.
if ( 'treatments' === $post_type && $price_filter ) {
	$archive_posts = array_values(
		array_filter(
			$archive_posts,
			function ( $archive_post ) use ( $price_filter ) {
				return Tibbhouse_Helpers::price_matches_range( get_post_meta( $archive_post->ID, 'th_price', true ), $price_filter );
			}
		)
	);
}

$age_terms      = get_terms( array( 'taxonomy' => 'patient_profile', 'hide_empty' => false ) );
$category_terms = 'treatments' === $post_type ? get_terms( array( 'taxonomy' => 'vital_area', 'hide_empty' => true ) ) : array();
$knowledge_types = 'knowledge' === $post_type ? get_terms( array( 'taxonomy' => 'knowledge_type', 'hide_empty' => true ) ) : array();
$related_treatments = 'knowledge' === $post_type ? get_posts( array( 'post_type' => 'treatments', 'post_status' => 'publish', 'posts_per_page' => -1, 'orderby' => 'title', 'order' => 'ASC' ) ) : array();
$related_conditions = 'knowledge' === $post_type ? get_posts( array( 'post_type' => 'conditions', 'post_status' => 'publish', 'posts_per_page' => -1, 'orderby' => 'title', 'order' => 'ASC' ) ) : array();
?>

<div class="tibbhouse-archive-wrap">

	<!-- ── Archive Header ── -->
	<div class="tibbhouse-archive-header-band">
		<div style="max-width:1200px;margin:0 auto;padding:0 5%;position:relative;z-index:2;">
			<nav class="tibbhouse-breadcrumbs">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'tibbhouse-core' ); ?></a>
				<span class="sep">›</span>
				<span><?php the_archive_title(); ?></span>
			</nav>
			<h1><?php the_archive_title(); ?></h1>
			<?php the_archive_description( '<p class="archive-desc">', '</p>' ); ?>
		</div>
	</div>

	<?php if ( $is_filterable ) : ?>
	<section class="th-archive-filter-panel" aria-labelledby="th-filter-title">
		<div class="th-archive-filter-inner">
			<div class="th-filter-intro">
				<div>
					<span class="th-filter-kicker"><?php esc_html_e( 'Refine your view', 'tibbhouse-core' ); ?></span>
					<h2 id="th-filter-title"><?php echo 'treatments' === $post_type ? esc_html__( 'Find the right treatment', 'tibbhouse-core' ) : esc_html__( 'Explore our knowledge', 'tibbhouse-core' ); ?></h2>
				</div>
				<span class="th-filter-count"><?php echo esc_html( sprintf( _n( '%d result', '%d results', count( $archive_posts ), 'tibbhouse-core' ), count( $archive_posts ) ) ); ?></span>
			</div>

			<form class="th-archive-filters" method="get" action="<?php echo esc_url( get_post_type_archive_link( $post_type ) ); ?>">
				<div class="th-filter-fields">
					<label>
						<span><?php esc_html_e( 'Age group', 'tibbhouse-core' ); ?></span>
						<select name="th_age">
							<option value=""><?php esc_html_e( 'All age groups', 'tibbhouse-core' ); ?></option>
							<?php foreach ( $age_terms as $term ) : ?>
								<option value="<?php echo esc_attr( $term->slug ); ?>" <?php selected( $age, $term->slug ); ?>><?php echo esc_html( $term->name ); ?></option>
							<?php endforeach; ?>
						</select>
					</label>

					<?php if ( 'treatments' === $post_type ) : ?>
					<label>
						<span><?php esc_html_e( 'Category / discipline', 'tibbhouse-core' ); ?></span>
						<select name="th_category">
							<option value=""><?php esc_html_e( 'All categories', 'tibbhouse-core' ); ?></option>
							<?php foreach ( $category_terms as $term ) : ?>
								<option value="<?php echo esc_attr( $term->slug ); ?>" <?php selected( isset( $_GET['th_category'] ) ? sanitize_title( wp_unslash( $_GET['th_category'] ) ) : '', $term->slug ); ?>><?php echo esc_html( $term->name ); ?></option>
							<?php endforeach; ?>
						</select>
					</label>
					<label>
						<span><?php esc_html_e( 'Price range', 'tibbhouse-core' ); ?></span>
						<select name="th_price">
							<option value=""><?php esc_html_e( 'Any price', 'tibbhouse-core' ); ?></option>
							<option value="under-50" <?php selected( $price_filter, 'under-50' ); ?>><?php esc_html_e( 'Under $50', 'tibbhouse-core' ); ?></option>
							<option value="50-100" <?php selected( $price_filter, '50-100' ); ?>><?php esc_html_e( '$50 – $100', 'tibbhouse-core' ); ?></option>
							<option value="100-plus" <?php selected( $price_filter, '100-plus' ); ?>><?php esc_html_e( 'Over $100', 'tibbhouse-core' ); ?></option>
						</select>
					</label>
					<?php else : ?>
					<label>
						<span><?php esc_html_e( 'Related treatment', 'tibbhouse-core' ); ?></span>
						<select name="th_related_treatment">
							<option value=""><?php esc_html_e( 'All treatments', 'tibbhouse-core' ); ?></option>
							<?php foreach ( $related_treatments as $related_treatment ) : ?>
								<option value="<?php echo esc_attr( $related_treatment->ID ); ?>" <?php selected( isset( $_GET['th_related_treatment'] ) ? absint( $_GET['th_related_treatment'] ) : 0, $related_treatment->ID ); ?>><?php echo esc_html( $related_treatment->post_title ); ?></option>
							<?php endforeach; ?>
						</select>
					</label>
					<label>
						<span><?php esc_html_e( 'Related condition', 'tibbhouse-core' ); ?></span>
						<select name="th_related_condition">
							<option value=""><?php esc_html_e( 'All conditions', 'tibbhouse-core' ); ?></option>
							<?php foreach ( $related_conditions as $related_condition ) : ?>
								<option value="<?php echo esc_attr( $related_condition->ID ); ?>" <?php selected( isset( $_GET['th_related_condition'] ) ? absint( $_GET['th_related_condition'] ) : 0, $related_condition->ID ); ?>><?php echo esc_html( $related_condition->post_title ); ?></option>
							<?php endforeach; ?>
						</select>
					</label>
					<label>
						<span><?php esc_html_e( 'Content type', 'tibbhouse-core' ); ?></span>
						<select name="th_type">
							<option value=""><?php esc_html_e( 'All content types', 'tibbhouse-core' ); ?></option>
							<?php foreach ( $knowledge_types as $term ) : ?>
								<option value="<?php echo esc_attr( $term->slug ); ?>" <?php selected( isset( $_GET['th_type'] ) ? sanitize_title( wp_unslash( $_GET['th_type'] ) ) : '', $term->slug ); ?>><?php echo esc_html( $term->name ); ?></option>
							<?php endforeach; ?>
						</select>
					</label>
					<?php endif; ?>

					<label>
						<span><?php esc_html_e( 'Sort by', 'tibbhouse-core' ); ?></span>
						<select name="th_sort">
							<option value="newest" <?php selected( isset( $_GET['th_sort'] ) ? sanitize_key( wp_unslash( $_GET['th_sort'] ) ) : 'newest', 'newest' ); ?>><?php esc_html_e( 'Newest', 'tibbhouse-core' ); ?></option>
							<option value="oldest" <?php selected( isset( $_GET['th_sort'] ) ? sanitize_key( wp_unslash( $_GET['th_sort'] ) ) : '', 'oldest' ); ?>><?php esc_html_e( 'Oldest', 'tibbhouse-core' ); ?></option>
							<option value="title" <?php selected( isset( $_GET['th_sort'] ) ? sanitize_key( wp_unslash( $_GET['th_sort'] ) ) : '', 'title' ); ?>><?php esc_html_e( 'A–Z', 'tibbhouse-core' ); ?></option>
							<option value="recommended" <?php selected( isset( $_GET['th_sort'] ) ? sanitize_key( wp_unslash( $_GET['th_sort'] ) ) : '', 'recommended' ); ?>><?php esc_html_e( 'Recommended', 'tibbhouse-core' ); ?></option>
						</select>
					</label>
				</div>
				<div class="th-filter-actions">
					<button class="th-btn th-btn-primary" type="submit"><?php esc_html_e( 'Apply filters', 'tibbhouse-core' ); ?></button>
					<a class="th-filter-reset" href="<?php echo esc_url( get_post_type_archive_link( $post_type ) ); ?>"><?php esc_html_e( 'Clear all', 'tibbhouse-core' ); ?></a>
				</div>
			</form>
		</div>
	</section>
	<?php endif; ?>

	<!-- ── Grid ── -->
	<div class="tibbhouse-archive-grid-wrap">
		<?php if ( ! empty( $archive_posts ) ) : ?>
		<div class="tibbhouse-archive-grid">
			<?php foreach ( $archive_posts as $archive_post ) : $GLOBALS['post'] = $archive_post; setup_postdata( $archive_post ); ?>
			<?php
			$card_post_type = get_post_type();
			$type_label     = isset( $type_labels[ $card_post_type ] ) ? rtrim( $type_labels[ $card_post_type ], 's' ) : $card_post_type;
			// Single label
			$single_labels = array(
				'treatments'    => esc_html__( 'Treatment', 'tibbhouse-core' ),
				'conditions'    => esc_html__( 'Condition', 'tibbhouse-core' ),
				'knowledge'     => esc_html__( 'Article', 'tibbhouse-core' ),
				'practitioners' => esc_html__( 'Practitioner', 'tibbhouse-core' ),
				'locations'     => esc_html__( 'Location', 'tibbhouse-core' ),
			);
			$label = isset( $single_labels[ $card_post_type ] ) ? $single_labels[ $card_post_type ] : $card_post_type;
			$card_meta = array();
			if ( 'treatments' === $card_post_type ) {
				$card_age_terms = get_the_terms( get_the_ID(), 'patient_profile' );
				$card_category_terms = get_the_terms( get_the_ID(), 'vital_area' );
				if ( $card_age_terms && ! is_wp_error( $card_age_terms ) ) {
					$card_meta[] = implode( ', ', wp_list_pluck( $card_age_terms, 'name' ) );
				}
				if ( $card_category_terms && ! is_wp_error( $card_category_terms ) ) {
					$card_meta[] = implode( ', ', wp_list_pluck( $card_category_terms, 'name' ) );
				}
			} elseif ( 'knowledge' === $card_post_type ) {
				$card_age_terms = get_the_terms( get_the_ID(), 'patient_profile' );
				$card_type_terms = get_the_terms( get_the_ID(), 'knowledge_type' );
				$card_author = get_post_meta( get_the_ID(), 'th_author', true );
				$card_reviewer = get_post_meta( get_the_ID(), 'th_medical_reviewer', true );
				$card_reviewed = get_post_meta( get_the_ID(), 'th_last_reviewed', true );
				$card_published = get_the_date();
				$card_related_treatment_ids = get_post_meta( get_the_ID(), 'th_related_treatments', true );
				$card_related_condition_ids = get_post_meta( get_the_ID(), 'th_related_conditions', true );
				$card_related_treatment_ids = is_array( $card_related_treatment_ids ) ? array_filter( array_map( 'absint', $card_related_treatment_ids ) ) : array();
				$card_related_condition_ids = is_array( $card_related_condition_ids ) ? array_filter( array_map( 'absint', $card_related_condition_ids ) ) : array();

				if ( $card_age_terms && ! is_wp_error( $card_age_terms ) ) {
					$card_meta[] = implode( ', ', wp_list_pluck( $card_age_terms, 'name' ) );
				}
				if ( $card_type_terms && ! is_wp_error( $card_type_terms ) ) {
					$card_meta[] = implode( ', ', wp_list_pluck( $card_type_terms, 'name' ) );
				}
				if ( $card_related_treatment_ids ) {
					$card_meta[] = sprintf( __( 'Treatment: %s', 'tibbhouse-core' ), implode( ', ', wp_list_pluck( get_posts( array( 'post_type' => 'treatments', 'post__in' => $card_related_treatment_ids, 'posts_per_page' => -1, 'orderby' => 'post__in' ) ), 'post_title' ) ) );
				}
				if ( $card_related_condition_ids ) {
					$card_meta[] = sprintf( __( 'Condition: %s', 'tibbhouse-core' ), implode( ', ', wp_list_pluck( get_posts( array( 'post_type' => 'conditions', 'post__in' => $card_related_condition_ids, 'posts_per_page' => -1, 'orderby' => 'post__in' ) ), 'post_title' ) ) );
				}
				if ( $card_reviewed ) {
					$card_meta[] = sprintf( __( 'Reviewed %s', 'tibbhouse-core' ), $card_reviewed );
				}
				if ( $card_published ) {
					$card_meta[] = sprintf( __( 'Published %s', 'tibbhouse-core' ), $card_published );
				}
				if ( $card_author || $card_reviewer ) {
					$card_meta[] = trim( $card_author . ( $card_author && $card_reviewer ? ' · ' : '' ) . $card_reviewer );
				}
			}
			$card_price = 'treatments' === $card_post_type ? get_post_meta( get_the_ID(), 'th_price', true ) : '';
			?>
			<a class="th-archive-card" href="<?php the_permalink(); ?>">

				<div class="th-archive-card-thumb">
					<?php if ( has_post_thumbnail() ) : ?>
						<?php the_post_thumbnail( 'medium_large' ); ?>
					<?php else : ?>
						<div class="th-archive-card-thumb-empty">
							<svg viewBox="0 0 24 24"><path d="M12 2a10 10 0 1 0 0 20A10 10 0 0 0 12 2zm1 14H11v-4h2v4zm0-6H11V8h2v2z"/></svg>
						</div>
					<?php endif; ?>
					<span class="th-archive-card-type-badge"><?php echo esc_html( $label ); ?></span>
				</div>

				<div class="th-archive-card-body">
					<h2><?php the_title(); ?></h2>
					<?php $excerpt = get_the_excerpt(); if ( $excerpt ) : ?>
					<p class="th-archive-card-excerpt"><?php echo esc_html( $excerpt ); ?></p>
					<?php endif; ?>
					<?php if ( ! empty( $card_meta ) || $card_price ) : ?>
					<div class="th-archive-card-meta">
						<?php if ( ! empty( $card_meta ) ) : ?><span><?php echo esc_html( implode( ' · ', $card_meta ) ); ?></span><?php endif; ?>
						<?php if ( $card_price ) : ?><strong><?php echo esc_html( $card_price ); ?></strong><?php endif; ?>
					</div>
					<?php endif; ?>
					<div class="th-archive-card-footer">
						<span class="th-read-more">
							<?php echo 'treatments' === $card_post_type ? esc_html__( 'View Treatment', 'tibbhouse-core' ) : esc_html__( 'Read Article', 'tibbhouse-core' ); ?>
							<svg viewBox="0 0 16 16"><path d="M3 8h10M9 4l4 4-4 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" fill="none"/></svg>
						</span>
					</div>
				</div>

			</a>
			<?php endforeach; wp_reset_postdata(); ?>
		</div>

		<div style="margin-top:48px;">
			<?php the_posts_pagination( array(
				'mid_size'  => 2,
				'prev_text' => '← ' . esc_html__( 'Previous', 'tibbhouse-core' ),
				'next_text' => esc_html__( 'Next', 'tibbhouse-core' ) . ' →',
			) ); ?>
		</div>

		<?php else : ?>
		<p style="text-align:center;padding:80px 0;color:var(--th-muted);"><?php esc_html_e( 'Nothing found.', 'tibbhouse-core' ); ?></p>
		<?php endif; ?>
	</div>

</div>

<?php
get_footer();
