<?php
/**
 * Main template — blog index / search / fallback.
 *
 * @package Tibbhouse
 */

get_header();
?>

<?php if ( is_home() && ! is_front_page() ) : ?>
<div class="tibbhouse-page-hero">
	<h1><?php single_post_title(); ?></h1>
</div>
<?php endif; ?>

<div class="tibbhouse-main">
	<?php tibbhouse_breadcrumbs(); ?>

	<?php if ( have_posts() ) : ?>

		<div class="tibbhouse-posts-grid">
			<?php while ( have_posts() ) : the_post(); ?>

			<article id="post-<?php the_ID(); ?>" <?php post_class( 'tibbhouse-post-card' ); ?>>
				<?php if ( has_post_thumbnail() ) : ?>
				<div class="tibbhouse-post-card-thumb">
					<a href="<?php the_permalink(); ?>"><?php the_post_thumbnail( 'tibbhouse-card' ); ?></a>
				</div>
				<?php endif; ?>

				<div class="tibbhouse-post-card-body">
					<div class="tibbhouse-post-card-meta">
						<?php echo get_the_date(); ?>
						<?php $cats = get_the_category(); if ( $cats ) : ?>
						&nbsp;·&nbsp; <?php echo esc_html( $cats[0]->name ); ?>
						<?php endif; ?>
					</div>

					<a class="tibbhouse-post-card-title" href="<?php the_permalink(); ?>">
						<?php the_title(); ?>
					</a>

					<p class="tibbhouse-post-card-excerpt"><?php the_excerpt(); ?></p>

					<a class="tibbhouse-post-card-footer" href="<?php the_permalink(); ?>">
						<?php esc_html_e( 'Read more', 'tibbhouse' ); ?>
						<svg width="14" height="14" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true">
							<path d="M3 8h10M9 4l4 4-4 4"/>
						</svg>
					</a>
				</div>
			</article>

			<?php endwhile; ?>
		</div>

		<?php the_posts_pagination( array(
			'mid_size'  => 2,
			'prev_text' => '&larr; ' . esc_html__( 'Previous', 'tibbhouse' ),
			'next_text' => esc_html__( 'Next', 'tibbhouse' ) . ' &rarr;',
		) ); ?>

	<?php else : ?>

		<p style="text-align:center;padding:60px 0;color:var(--th-muted);">
			<?php esc_html_e( 'No posts found.', 'tibbhouse' ); ?>
		</p>

	<?php endif; ?>
</div>

<?php get_footer(); ?>
