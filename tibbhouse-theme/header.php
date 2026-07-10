<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="https://gmpg.org/xfn/11">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<!-- ── Navigation ── -->
<nav id="tibbhouse-nav" role="navigation" aria-label="<?php esc_attr_e( 'Primary navigation', 'tibbhouse' ); ?>">
	<div class="th-nav-inner">

		<!-- Logo -->
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="th-nav-logo" rel="home">
			<?php if ( has_custom_logo() ) : ?>
				<?php the_custom_logo(); ?>
			<?php else : ?>
				<?php echo tibbhouse_default_logo_svg(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<?php endif; ?>
		</a>

		<!-- Desktop + Mobile Menu Wrapper -->
		<div id="th-nav-menu-wrap" class="th-nav-menu-wrap">
			<?php
			wp_nav_menu( array(
				'theme_location' => 'primary',
				'menu_id'        => 'primary-menu',
				'menu_class'     => 'th-nav-menu',
				'container'      => false,
				'fallback_cb'    => 'tibbhouse_nav_fallback',
			) );
			?>
		</div>

		<!-- Hamburger toggle -->
		<button id="th-nav-toggle" class="th-nav-toggle" aria-controls="th-nav-menu-wrap" aria-expanded="false">
			<span class="sr-only"><?php esc_html_e( 'Toggle menu', 'tibbhouse' ); ?></span>
			<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
				<line x1="3" y1="6"  x2="21" y2="6"/>
				<line x1="3" y1="12" x2="21" y2="12"/>
				<line x1="3" y1="18" x2="21" y2="18"/>
			</svg>
		</button>

	</div>
</nav>

<?php
/**
 * Fallback nav: show a plain link to every page when no menu is assigned.
 */
function tibbhouse_nav_fallback() {
	echo '<ul class="th-nav-menu">';
	wp_list_pages( array(
		'title_li'    => '',
		'echo'        => true,
		'depth'       => 1,
	) );
	echo '</ul>';
}
?>
