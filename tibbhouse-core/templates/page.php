<?php
/**
 * Generic page template used as a fallback wrapper by the plugin
 * (theme templates for standard `page` post type still take priority
 * unless the theme opts into this override under /tibbhouse/page.php).
 *
 * @package Tibbhouse_Core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();
	?>
	<article <?php post_class( 'tibbhouse-page' ); ?>>
		<header class="tibbhouse-hero">
			<h1><?php the_title(); ?></h1>
		</header>
		<div class="tibbhouse-content">
			<?php the_content(); ?>
		</div>
	</article>
	<?php
endwhile;

get_footer();
