<?php
/**
 * 404 template.
 *
 * @package Tibbhouse
 */

get_header();
?>

<div class="tibbhouse-404-wrap">
	<h1>404</h1>
	<h2><?php esc_html_e( 'Page Not Found', 'tibbhouse' ); ?></h2>
	<p><?php esc_html_e( 'The page you are looking for may have moved or no longer exists.', 'tibbhouse' ); ?></p>

	<form role="search" method="get" class="th-search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
		<input type="search" name="s" placeholder="<?php esc_attr_e( 'Search&hellip;', 'tibbhouse' ); ?>" value="<?php echo esc_attr( get_search_query() ); ?>" required>
		<button type="submit"><?php esc_html_e( 'Search', 'tibbhouse' ); ?></button>
	</form>

	<a class="th-back-home" href="<?php echo esc_url( home_url( '/' ) ); ?>">
		&larr; <?php esc_html_e( 'Back to Home', 'tibbhouse' ); ?>
	</a>
</div>

<?php get_footer(); ?>
