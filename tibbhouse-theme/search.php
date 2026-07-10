<?php
/**
 * Search results template.
 *
 * @package Tibbhouse
 */

get_header();
?>

<div class="tibbhouse-page-hero">
	<h1>
		<?php
		/* translators: %s: search query */
		printf( esc_html__( 'Results for: %s', 'tibbhouse' ), '<em>' . esc_html( get_search_query() ) . '</em>' );
		?>
	</h1>
</div>

<div class="tibbhouse-main">
	<form role="search" method="get" class="th-search-form" style="max-width:560px;margin-bottom:40px;" action="<?php echo esc_url( home_url( '/' ) ); ?>">
		<input type="search" name="s" placeholder="<?php esc_attr_e( 'Search again&hellip;', 'tibbhouse' ); ?>" value="<?php echo esc_attr( get_search_query() ); ?>">
		<button type="submit"><?php esc_html_e( 'Search', 'tibbhouse' ); ?></button>
	</form>

	<?php if ( have_posts() ) : ?>

		<p style="color:var(--th-muted);margin-bottom:32px;font-size:.9rem;">
			<?php
			/* translators: %d: number of results */
			printf( esc_html( _n( '%d result found', '%d results found', (int) $wp_query->found_posts, 'tibbhouse' ) ), (int) $wp_query->found_posts );
			?>
		</p>

		<div class="tibbhouse-posts-grid">
			<?php while ( have_posts() ) : the_post(); ?>
			<article id="post-<?php the_ID(); ?>" <?php post_class( 'tibbhouse-post-card' ); ?>>
				<?php if ( has_post_thumbnail() ) : ?>
				<div class="tibbhouse-post-card-thumb">
					<a href="<?php the_permalink(); ?>"><?php the_post_thumbnail( 'tibbhouse-card' ); ?></a>
				</div>
				<?php endif; ?>
				<div class="tibbhouse-post-card-body">
					<div class="tibbhouse-post-card-meta"><?php echo esc_html( get_post_type_object( get_post_type() )->labels->singular_name ?? get_post_type() ); ?></div>
					<a class="tibbhouse-post-card-title" href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
					<p class="tibbhouse-post-card-excerpt"><?php the_excerpt(); ?></p>
					<a class="tibbhouse-post-card-footer" href="<?php the_permalink(); ?>"><?php esc_html_e( 'View', 'tibbhouse' ); ?> &rarr;</a>
				</div>
			</article>
			<?php endwhile; ?>
		</div>

		<?php the_posts_pagination(); ?>

	<?php else : ?>
		<p style="color:var(--th-muted);padding:40px 0;"><?php esc_html_e( 'No results found. Try a different search term.', 'tibbhouse' ); ?></p>
	<?php endif; ?>
</div>

<?php get_footer(); ?>
