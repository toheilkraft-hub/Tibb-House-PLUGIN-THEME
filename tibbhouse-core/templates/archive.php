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
?>

<header class="tibbhouse-archive-header">
	<h1><?php the_archive_title(); ?></h1>
	<?php the_archive_description( '<div class="tibbhouse-archive-description">', '</div>' ); ?>
</header>

<div class="tibbhouse-block tibbhouse-card-grid">
	<?php
	if ( have_posts() ) :
		while ( have_posts() ) :
			the_post();
			printf(
				'<a class="tibbhouse-card" href="%1$s">%2$s<span class="tibbhouse-card-title">%3$s</span></a>',
				esc_url( get_permalink() ),
				get_the_post_thumbnail( get_the_ID(), 'medium' ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				esc_html( get_the_title() )
			);
		endwhile;
	else :
		echo '<p>' . esc_html__( 'Nothing found.', 'tibbhouse-core' ) . '</p>';
	endif;
	?>
</div>

<?php
the_posts_pagination();

get_footer();
