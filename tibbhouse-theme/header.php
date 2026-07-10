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
 * Fallback nav: when no "Primary Navigation" menu has been assigned yet,
 * link straight to the Tibb House content sections (Treatments, Conditions,
 * Knowledge, Practitioners, Locations) so the header matches the intended
 * design instead of listing arbitrary WordPress Pages.
 */
function tibbhouse_nav_fallback() {
	echo '<ul class="th-nav-menu">';
	foreach ( tibbhouse_nav_fallback_links() as $link ) {
		printf(
			'<li><a href="%s">%s</a></li>',
			esc_url( $link['url'] ),
			esc_html( $link['label'] )
		);
	}
	echo '</ul>';
}

/**
 * Build the list of fallback nav links from the Tibb House Core CPTs.
 *
 * Falls back to `wp_list_pages()` output only if the plugin isn't active
 * (no managed post types are registered).
 *
 * @return array[] List of ['url' => ..., 'label' => ...].
 */
function tibbhouse_nav_fallback_links() {
	$sections = array(
		'treatments'    => __( 'Treatments', 'tibbhouse' ),
		'conditions'    => __( 'Conditions', 'tibbhouse' ),
		'knowledge'     => __( 'Knowledge', 'tibbhouse' ),
		'practitioners' => __( 'Practitioners', 'tibbhouse' ),
		'locations'     => __( 'Locations', 'tibbhouse' ),
	);

	$links = array();
	foreach ( $sections as $post_type => $label ) {
		if ( ! post_type_exists( $post_type ) ) {
			continue;
		}
		$archive_link = get_post_type_archive_link( $post_type );
		if ( $archive_link ) {
			$links[] = array( 'url' => $archive_link, 'label' => $label );
		}
	}

	if ( ! empty( $links ) ) {
		return $links;
	}

	// Plugin not active: fall back to listing top-level Pages.
	$pages = get_pages( array( 'sort_column' => 'menu_order' ) );
	foreach ( $pages as $page ) {
		$links[] = array( 'url' => get_permalink( $page ), 'label' => $page->post_title );
	}
	return $links;
}
?>
