<?php
/**
 * Tibb House theme functions.
 *
 * @package Tibbhouse
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'TIBBHOUSE_THEME_VERSION', '1.1.0' );
define( 'TIBBHOUSE_THEME_URI', get_template_directory_uri() );

require_once get_template_directory() . '/inc/homepage-render.php';
require_once get_template_directory() . '/inc/homepage-install.php';
require_once get_template_directory() . '/inc/menu-install.php';
require_once get_template_directory() . '/inc/image-import.php';

/**
 * Remove the "Archives:" / "Archive" prefix WordPress prepends to CPT archive
 * page titles — e.g. "Treatments Archives" → "Treatments".
 */
add_filter( 'get_the_archive_title', function ( $title ) {
	if ( is_post_type_archive() ) {
		$obj = get_queried_object();
		if ( $obj && isset( $obj->labels->name ) ) {
			return $obj->labels->name;
		}
		// Fallback: strip any "Archives:" / "Archive:" prefix WordPress added.
		return preg_replace( '/^[^:]+:\s*/', '', $title );
	}
	return $title;
} );

/**
 * Rewrite any nav-menu URL whose host differs from WP_HOME so stale
 * localhost/wrong-domain URLs still work after a migration or dev-env move.
 * Runs on wp_nav_menu_objects so it covers ALL menu items at once.
 */
add_filter( 'wp_nav_menu_objects', function ( $items ) {
	$home      = home_url( '/' );
	$home_host = wp_parse_url( $home, PHP_URL_HOST );
	$home_sch  = wp_parse_url( $home, PHP_URL_SCHEME );

	foreach ( $items as $item ) {
		if ( empty( $item->url ) ) {
			continue;
		}
		$parsed = wp_parse_url( $item->url );
		if ( empty( $parsed['host'] ) ) {
			continue; // Relative — leave as-is.
		}
		if ( $parsed['host'] !== $home_host ) {
			// Replace scheme+host with current WP_HOME values.
			$item->url = $home_sch . '://' . $home_host . ( $parsed['path'] ?? '/' ) .
				( ! empty( $parsed['query'] )    ? '?' . $parsed['query']    : '' ) .
				( ! empty( $parsed['fragment'] ) ? '#' . $parsed['fragment'] : '' );
		}
	}
	return $items;
} );

/* -----------------------------------------------------------------------
   Theme Setup
----------------------------------------------------------------------- */
function tibbhouse_setup() {
	load_theme_textdomain( 'tibbhouse', get_template_directory() . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ) );
	add_theme_support( 'custom-logo', array(
		'height'      => 80,
		'width'       => 200,
		'flex-height' => true,
		'flex-width'  => true,
	) );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'customize-selective-refresh-widgets' );
	add_theme_support( 'wp-block-styles' );
	add_theme_support( 'align-wide' );
	add_theme_support( 'responsive-embeds' );

	// Image sizes
	add_image_size( 'tibbhouse-hero',    1600, 700,  true );
	add_image_size( 'tibbhouse-card',     600, 400,  true );
	add_image_size( 'tibbhouse-thumb',    400, 300,  true );
	add_image_size( 'tibbhouse-portrait', 480, 640,  true );

	// Navigation menus
	register_nav_menus( array(
		'primary' => esc_html__( 'Primary Navigation', 'tibbhouse' ),
		'footer'  => esc_html__( 'Footer Navigation',  'tibbhouse' ),
	) );
}
add_action( 'after_setup_theme', 'tibbhouse_setup' );

/* -----------------------------------------------------------------------
   Widget Areas
----------------------------------------------------------------------- */
function tibbhouse_widgets_init() {
	register_sidebar( array(
		'name'          => esc_html__( 'Sidebar', 'tibbhouse' ),
		'id'            => 'sidebar-1',
		'description'   => esc_html__( 'Add widgets here to appear in the sidebar.', 'tibbhouse' ),
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h3 class="widget-title">',
		'after_title'   => '</h3>',
	) );

	register_sidebar( array(
		'name'          => esc_html__( 'Footer Column 1', 'tibbhouse' ),
		'id'            => 'footer-1',
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h4 class="footer-widget-title">',
		'after_title'   => '</h4>',
	) );

	register_sidebar( array(
		'name'          => esc_html__( 'Footer Column 2', 'tibbhouse' ),
		'id'            => 'footer-2',
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h4 class="footer-widget-title">',
		'after_title'   => '</h4>',
	) );
}
add_action( 'widgets_init', 'tibbhouse_widgets_init' );

/* -----------------------------------------------------------------------
   Enqueue Assets
----------------------------------------------------------------------- */
function tibbhouse_enqueue_scripts() {
	// Use filemtime so any file change auto-busts the browser cache.
	$css_ver = filemtime( get_template_directory() . '/assets/css/theme.css' ) ?: TIBBHOUSE_THEME_VERSION;
	$js_ver  = filemtime( get_template_directory() . '/assets/js/theme.js' )  ?: TIBBHOUSE_THEME_VERSION;

	// Theme stylesheet
	wp_enqueue_style(
		'tibbhouse-theme',
		TIBBHOUSE_THEME_URI . '/assets/css/theme.css',
		array(),
		$css_ver
	);

	// Theme JS (mobile menu, etc.)
	wp_enqueue_script(
		'tibbhouse-theme',
		TIBBHOUSE_THEME_URI . '/assets/js/theme.js',
		array(),
		$js_ver,
		true
	);

	// Comment reply
	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'tibbhouse_enqueue_scripts' );

/* -----------------------------------------------------------------------
   Helpers
----------------------------------------------------------------------- */

/**
 * Render breadcrumbs for standard pages/posts.
 */
function tibbhouse_breadcrumbs() {
	if ( is_front_page() ) {
		return;
	}
	echo '<nav class="tibbhouse-theme-breadcrumbs">';
	echo '<a href="' . esc_url( home_url( '/' ) ) . '">' . esc_html__( 'Home', 'tibbhouse' ) . '</a>';
	echo '<span class="sep">›</span>';
	if ( is_category() || is_tag() || is_tax() ) {
		echo '<span>' . get_queried_object()->name . '</span>';
	} elseif ( is_single() ) {
		echo '<span>' . esc_html( get_the_title() ) . '</span>';
	} elseif ( is_page() ) {
		echo '<span>' . esc_html( get_the_title() ) . '</span>';
	} elseif ( is_search() ) {
		/* translators: %s search term */
		echo '<span>' . sprintf( esc_html__( 'Search: %s', 'tibbhouse' ), '<em>' . esc_html( get_search_query() ) . '</em>' ) . '</span>';
	} elseif ( is_404() ) {
		echo '<span>' . esc_html__( 'Page Not Found', 'tibbhouse' ) . '</span>';
	}
	echo '</nav>';
}

/**
 * Return SVG logo markup (used when no custom logo is set).
 *
 * @return string
 */
function tibbhouse_default_logo_svg() {
	$logo_url = get_template_directory_uri() . '/assets/img/logo-full.png';
	return '<img src="' . esc_url( $logo_url ) . '" class="th-nav-logo-img" alt="' . esc_attr__( 'Tibb House — بيت الطب', 'tibbhouse' ) . '">';
}

/* -----------------------------------------------------------------------
   Customizer — Logo Size & Layout Controls
----------------------------------------------------------------------- */

/**
 * Register Customizer settings and controls.
 */
function tibbhouse_customize_register( WP_Customize_Manager $wp_customize ) {

	// ── Section ──────────────────────────────────────────────────────────
	$wp_customize->add_section( 'tibbhouse_layout', array(
		'title'    => esc_html__( 'Tibb House — Layout', 'tibbhouse' ),
		'priority' => 30,
	) );

	// ── Logo height (desktop) ─────────────────────────────────────────────
	$wp_customize->add_setting( 'tibbhouse_logo_height', array(
		'default'           => 88,
		'transport'         => 'postMessage', // live preview without full reload
		'sanitize_callback' => 'absint',
	) );

	$wp_customize->add_control( 'tibbhouse_logo_height', array(
		'label'       => esc_html__( 'Logo height — desktop (px)', 'tibbhouse' ),
		'description' => esc_html__( 'Drag to resize the logo in the navigation bar.', 'tibbhouse' ),
		'section'     => 'tibbhouse_layout',
		'type'        => 'range',
		'input_attrs' => array(
			'min'  => 40,
			'max'  => 160,
			'step' => 2,
		),
	) );

	// ── Logo height (mobile) ──────────────────────────────────────────────
	$wp_customize->add_setting( 'tibbhouse_logo_height_mobile', array(
		'default'           => 56,
		'transport'         => 'postMessage',
		'sanitize_callback' => 'absint',
	) );

	$wp_customize->add_control( 'tibbhouse_logo_height_mobile', array(
		'label'       => esc_html__( 'Logo height — mobile (px)', 'tibbhouse' ),
		'section'     => 'tibbhouse_layout',
		'type'        => 'range',
		'input_attrs' => array(
			'min'  => 32,
			'max'  => 100,
			'step' => 2,
		),
	) );

	// ── Footer logo height ────────────────────────────────────────────────
	$wp_customize->add_setting( 'tibbhouse_footer_logo_height', array(
		'default'           => 100,
		'transport'         => 'postMessage',
		'sanitize_callback' => 'absint',
	) );

	$wp_customize->add_control( 'tibbhouse_footer_logo_height', array(
		'label'       => esc_html__( 'Logo height — footer (px)', 'tibbhouse' ),
		'section'     => 'tibbhouse_layout',
		'type'        => 'range',
		'input_attrs' => array(
			'min'  => 40,
			'max'  => 180,
			'step' => 2,
		),
	) );
}
add_action( 'customize_register', 'tibbhouse_customize_register' );

/**
 * Output inline CSS driven by Customizer values (front-end + Customizer preview).
 */
function tibbhouse_customize_css() {
	$logo_h        = absint( get_theme_mod( 'tibbhouse_logo_height',        88  ) );
	$logo_h_mobile = absint( get_theme_mod( 'tibbhouse_logo_height_mobile', 56  ) );
	$footer_logo_h = absint( get_theme_mod( 'tibbhouse_footer_logo_height', 100 ) );
	?>
	<style id="tibbhouse-customizer-css">
		.th-nav-logo img,
		.th-logo-svg,
		.th-nav-logo-img { height: <?php echo $logo_h; ?>px !important; width: auto !important; }

		.th-footer-brand img.th-logo-svg,
		.th-footer-brand .th-nav-logo img,
		.th-footer-brand img { height: <?php echo $footer_logo_h; ?>px !important; width: auto !important; }

		@media (max-width: 768px) {
			.th-nav-logo img,
			.th-logo-svg,
			.th-nav-logo-img { height: <?php echo $logo_h_mobile; ?>px !important; }
		}
	</style>
	<?php
}
add_action( 'wp_head', 'tibbhouse_customize_css' );

/**
 * Live-preview JS: pushes slider changes to the iframe instantly via postMessage.
 */
function tibbhouse_customize_preview_js() {
	?>
	<script>
	( function( $ ) {
		function applyLogoCSS( desktop, mobile, footer ) {
			var el = document.getElementById( 'tibbhouse-customizer-css' );
			if ( ! el ) { el = document.createElement( 'style' ); el.id = 'tibbhouse-customizer-css'; document.head.appendChild( el ); }
			el.textContent =
				'.th-nav-logo img,.th-logo-svg,.th-nav-logo-img{height:' + desktop + 'px!important;width:auto!important}' +
				'.th-footer-brand img.th-logo-svg,.th-footer-brand .th-nav-logo img,.th-footer-brand img{height:' + footer + 'px!important;width:auto!important}' +
				'@media(max-width:768px){.th-nav-logo img,.th-logo-svg,.th-nav-logo-img{height:' + mobile + 'px!important}}';
		}

		var desktop = <?php echo absint( get_theme_mod( 'tibbhouse_logo_height', 88 ) ); ?>;
		var mobile  = <?php echo absint( get_theme_mod( 'tibbhouse_logo_height_mobile', 56 ) ); ?>;
		var footer  = <?php echo absint( get_theme_mod( 'tibbhouse_footer_logo_height', 100 ) ); ?>;

		wp.customize( 'tibbhouse_logo_height', function( v ) {
			v.bind( function( val ) { desktop = +val; applyLogoCSS( desktop, mobile, footer ); } );
		} );
		wp.customize( 'tibbhouse_logo_height_mobile', function( v ) {
			v.bind( function( val ) { mobile = +val; applyLogoCSS( desktop, mobile, footer ); } );
		} );
		wp.customize( 'tibbhouse_footer_logo_height', function( v ) {
			v.bind( function( val ) { footer = +val; applyLogoCSS( desktop, mobile, footer ); } );
		} );
	} )( jQuery );
	</script>
	<?php
}
add_action( 'customize_preview_init', function() {
	add_action( 'wp_footer', 'tibbhouse_customize_preview_js' );
} );


/**
 * Auto-assign the Contact Page template to the "TIBB HOUSE – Contact Us" page.
 * Runs on admin_init so it applies after the page is created by the menu installer.
 * Idempotent — only writes the meta once.
 */
function tibbhouse_assign_contact_template() {
	$contact = get_page_by_title( 'TIBB HOUSE – Contact Us', OBJECT, 'page' );
	if ( ! $contact ) {
		return;
	}
	$current = get_post_meta( $contact->ID, '_wp_page_template', true );
	if ( 'page-contact.php' !== $current ) {
		update_post_meta( $contact->ID, '_wp_page_template', 'page-contact.php' );
	}
}
add_action( 'admin_init', 'tibbhouse_assign_contact_template' );


/**
 * Create the "PRIVATE DATA HIPAA" ghost page and assign the blank HIPAA template.
 *
 * The page is published (so it has a real URL) but is intentionally excluded from
 * all nav menus and sitemaps — a ghost that only the direct link can reach.
 * Idempotent: creates once, then only updates the template meta if missing.
 */
function tibbhouse_create_hipaa_ghost_page() {
	$existing = get_page_by_title( 'PRIVATE DATA HIPAA', OBJECT, 'page' );

	if ( ! $existing ) {
		$page_id = wp_insert_post( array(
			'post_title'   => 'PRIVATE DATA HIPAA',
			'post_name'    => 'private-data-hipaa',
			'post_status'  => 'publish',
			'post_type'    => 'page',
			'post_content' => '',
			'post_author'  => 1,
			'menu_order'   => 9999,
			'comment_status' => 'closed',
			'ping_status'    => 'closed',
		), true );

		if ( ! is_wp_error( $page_id ) ) {
			update_post_meta( $page_id, '_wp_page_template', 'page-hipaa.php' );
			// Exclude from sitemap plugins (Yoast, RankMath, etc.)
			update_post_meta( $page_id, '_yoast_wpseo_meta-robots-noindex', '1' );
			update_post_meta( $page_id, '_yoast_wpseo_meta-robots-nofollow', '1' );
			update_post_meta( $page_id, 'rank_math_robots', array( 'noindex', 'nofollow' ) );
		}
	} else {
		$tpl = get_post_meta( $existing->ID, '_wp_page_template', true );
		if ( 'page-hipaa.php' !== $tpl ) {
			update_post_meta( $existing->ID, '_wp_page_template', 'page-hipaa.php' );
		}
	}
}
add_action( 'admin_init', 'tibbhouse_create_hipaa_ghost_page' );

/**
 * Helper: return the permalink of the dedicated private HIPAA page.
 *
 * The seeded page uses the stable `private-data-hipaa` slug and is titled
 * "Secure Patient Intake". Keep the title lookup as a compatibility fallback
 * for older installs that used the "PRIVATE DATA HIPAA" ghost-page title.
 */
function tibbhouse_hipaa_url() {
	$page = get_page_by_path( 'private-data-hipaa', OBJECT, 'page' );

	if ( ! $page ) {
		$page = get_page_by_title( 'PRIVATE DATA HIPAA', OBJECT, 'page' );
	}

	return $page ? get_permalink( $page ) : home_url( '/private-data-hipaa/' );
}
