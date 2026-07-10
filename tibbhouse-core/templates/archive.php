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

	<!-- ── Grid ── -->
	<div class="tibbhouse-archive-grid-wrap">
		<?php if ( have_posts() ) : ?>
		<div class="tibbhouse-archive-grid">
			<?php while ( have_posts() ) : the_post(); ?>
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
					<div class="th-archive-card-footer">
						<span class="th-read-more">
							<?php esc_html_e( 'Read more', 'tibbhouse-core' ); ?>
							<svg viewBox="0 0 16 16"><path d="M3 8h10M9 4l4 4-4 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" fill="none"/></svg>
						</span>
					</div>
				</div>

			</a>
			<?php endwhile; ?>
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
