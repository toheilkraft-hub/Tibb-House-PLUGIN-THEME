<?php
/**
 * Static page template.
 *
 * @package Tibbhouse
 */

get_header();

while ( have_posts() ) :
	the_post();
	?>

<div class="tibbhouse-page-hero">
	<h1><?php the_title(); ?></h1>
</div>

<div class="tibbhouse-main">
	<?php tibbhouse_breadcrumbs(); ?>

	<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
		<?php if ( has_post_thumbnail() ) : ?>
		<div style="margin-bottom:40px;border-radius:var(--th-radius);overflow:hidden;">
			<?php the_post_thumbnail( 'tibbhouse-hero' ); ?>
		</div>
		<?php endif; ?>

		<div class="entry-content">
			<?php the_content(); ?>
		</div>
	</article>
</div>

<?php
endwhile;
get_footer();
