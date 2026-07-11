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
	return '<img src="' . esc_url( $logo_url ) . '" class="th-nav-logo-img" alt="' . esc_attr__( 'Tibb House — بيت الطب', 'tibbhouse' ) . '" height="52" width="auto">';
}
