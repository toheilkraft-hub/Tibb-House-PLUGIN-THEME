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
