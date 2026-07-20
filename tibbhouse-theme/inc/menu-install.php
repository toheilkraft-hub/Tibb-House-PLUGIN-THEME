<?php
/**
 * Automatic navigation menu setup.
 *
 * On theme activation: creates the standard site pages (About Us, Contact
 * Us, Privacy Policy, Terms & Conditions), builds a "Tibb House Menu"
 * containing Home + those pages + every Tibb House Core content section,
 * and assigns it to the Primary Navigation location — so a fresh install
 * has a complete, working nav with zero manual menu configuration.
 *
 * @package Tibbhouse
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'TIBBHOUSE_MENU_NAME', 'Tibb House Menu' );

/**
 * Create (or reuse) a Page by title, with real starter content.
 *
 * @param string $title   Page title.
 * @param string $content Block content for the page body.
 * @return int Page ID (0 on failure).
 */
function tibbhouse_menu_get_or_create_page( $title, $content ) {
	$existing = get_page_by_title( $title, OBJECT, 'page' );
	if ( $existing ) {
		return (int) $existing->ID;
	}

	$page_id = wp_insert_post(
		array(
			'post_title'   => $title,
			'post_name'    => sanitize_title( $title ),
			'post_status'  => 'publish',
			'post_type'    => 'page',
			'post_content' => $content,
			'post_author'  => get_current_user_id() ? get_current_user_id() : 1,
		),
		true
	);

	return ( is_wp_error( $page_id ) || ! $page_id ) ? 0 : (int) $page_id;
}

/**
 * Build the block content for the "About Us" page.
 *
 * @return string
 */
function tibbhouse_menu_about_content() {
	return implode(
		"\n\n",
		array(
			'<!-- wp:heading --><h2>' . esc_html__( 'Rooted in Tradition, Guided by Care', 'tibbhouse' ) . '</h2><!-- /wp:heading -->',
			'<!-- wp:paragraph --><p>' . esc_html__( 'Tibb House brings together natural and Islamic medicine — Hijama cupping, herbal remedies, and Prophetic dietary guidance — delivered by qualified practitioners in a calm, welcoming setting.', 'tibbhouse' ) . '</p><!-- /wp:paragraph -->',
			'<!-- wp:heading --><h2>' . esc_html__( 'Our Approach', 'tibbhouse' ) . '</h2><!-- /wp:heading -->',
			'<!-- wp:paragraph --><p>' . esc_html__( 'Every treatment plan starts with understanding your constitutional type and vital areas of concern, so recommendations are personal rather than generic.', 'tibbhouse' ) . '</p><!-- /wp:paragraph -->',
			'<!-- wp:list --><ul><li>' . esc_html__( 'Qualified, experienced practitioners', 'tibbhouse' ) . '</li><li>' . esc_html__( 'Evidence-informed traditional remedies', 'tibbhouse' ) . '</li><li>' . esc_html__( 'A calm, respectful clinic environment', 'tibbhouse' ) . '</li></ul><!-- /wp:list -->',
		)
	);
}

/**
 * Build the block content for the "Contact Us" page.
 *
 * @return string
 */
function tibbhouse_menu_contact_content() {
	return implode(
		"\n\n",
		array(
			'<!-- wp:heading --><h2>' . esc_html__( "We'd Love to Hear From You", 'tibbhouse' ) . '</h2><!-- /wp:heading -->',
			'<!-- wp:paragraph --><p>' . esc_html__( 'Have a question about a treatment, or want to book an appointment? Reach out and our team will get back to you.', 'tibbhouse' ) . '</p><!-- /wp:paragraph -->',
			'<!-- wp:paragraph --><p>' . sprintf(
				/* translators: %s: contact email address */
				esc_html__( 'Email us at %s and we will respond within one business day.', 'tibbhouse' ),
				esc_html( get_option( 'admin_email' ) )
			) . '</p><!-- /wp:paragraph -->',
		)
	);
}

/**
 * Build the block content for the "Privacy Policy" page.
 *
 * @return string
 */
function tibbhouse_menu_privacy_content() {
	return implode(
		"\n\n",
		array(
			'<!-- wp:heading --><h2>' . esc_html__( 'Privacy Policy', 'tibbhouse' ) . '</h2><!-- /wp:heading -->',
			'<!-- wp:paragraph --><p>' . esc_html__( 'This page describes how Tibb House collects, uses, and protects the personal information you share with us, such as contact details submitted through booking or contact forms.', 'tibbhouse' ) . '</p><!-- /wp:paragraph -->',
			'<!-- wp:paragraph --><p>' . esc_html__( 'Replace this placeholder with your own privacy policy before launch, or use the WordPress Privacy Policy guide (Settings → Privacy) to generate one.', 'tibbhouse' ) . '</p><!-- /wp:paragraph -->',
		)
	);
}

/**
 * Build the block content for the "Terms & Conditions" page.
 *
 * @return string
 */
function tibbhouse_menu_terms_content() {
	return implode(
		"\n\n",
		array(
			'<!-- wp:heading --><h2>' . esc_html__( 'Terms & Conditions', 'tibbhouse' ) . '</h2><!-- /wp:heading -->',
			'<!-- wp:paragraph --><p>' . esc_html__( 'By using the Tibb House website and booking treatments with us, you agree to the following terms.', 'tibbhouse' ) . '</p><!-- /wp:paragraph -->',
			'<!-- wp:paragraph --><p>' . esc_html__( 'Replace this placeholder with your clinic\'s own terms of service, cancellation policy, and liability disclaimers before launch.', 'tibbhouse' ) . '</p><!-- /wp:paragraph -->',
		)
	);
}

/**
 * Add a custom-URL menu item to a nav menu, avoiding duplicate items by title.
 *
 * @param int    $menu_id  Menu term ID.
 * @param string $title    Item label.
 * @param string $url      Item URL.
 * @param int    $position Menu order.
 */
function tibbhouse_menu_add_item( $menu_id, $title, $url, $position ) {
	if ( ! $url ) {
		return;
	}

	$existing_items = wp_get_nav_menu_items( $menu_id );
	if ( $existing_items ) {
		foreach ( $existing_items as $item ) {
			if ( $item->title === $title ) {
				return; // Already present, don't duplicate.
			}
		}
	}

	wp_update_nav_menu_item(
		$menu_id,
		0,
		array(
			'menu-item-title'    => $title,
			'menu-item-url'      => $url,
			'menu-item-status'   => 'publish',
			'menu-item-position' => $position,
			'menu-item-type'     => 'custom',
		)
	);
}

/**
 * Add a post-type-archive menu item (URL resolved dynamically by WordPress).
 *
 * Using type=post_type_archive means WordPress calls get_post_type_archive_link()
 * at render time, so the URL is always correct regardless of what domain
 * was active when the menu was first created.
 *
 * @param int    $menu_id   Menu term ID.
 * @param string $label     Visible label.
 * @param string $post_type Post type slug.
 * @param int    $position  Menu order.
 */
function tibbhouse_menu_add_archive_item( $menu_id, $label, $post_type, $position ) {
	$existing_items = wp_get_nav_menu_items( $menu_id );
	if ( $existing_items ) {
		foreach ( $existing_items as $item ) {
			if ( $item->title === $label ) {
				return;
			}
		}
	}

	wp_update_nav_menu_item(
		$menu_id,
		0,
		array(
			'menu-item-title'    => $label,
			'menu-item-type'     => 'post_type_archive',
			'menu-item-object'   => $post_type,
			'menu-item-status'   => 'publish',
			'menu-item-position' => $position,
		)
	);
}

/**
 * Add a page/post menu item (URL resolved dynamically from the object ID).
 *
 * @param int    $menu_id   Menu term ID.
 * @param string $label     Visible label.
 * @param int    $page_id   Page/post ID.
 * @param string $obj_type  Post type (default 'page').
 * @param int    $position  Menu order.
 */
function tibbhouse_menu_add_page_item( $menu_id, $label, $page_id, $obj_type, $position ) {
	if ( ! $page_id ) {
		return;
	}

	$existing_items = wp_get_nav_menu_items( $menu_id );
	if ( $existing_items ) {
		foreach ( $existing_items as $item ) {
			if ( $item->title === $label ) {
				return;
			}
		}
	}

	wp_update_nav_menu_item(
		$menu_id,
		0,
		array(
			'menu-item-title'     => $label,
			'menu-item-type'      => 'post_type',
			'menu-item-object'    => $obj_type,
			'menu-item-object-id' => $page_id,
			'menu-item-status'    => 'publish',
			'menu-item-position'  => $position,
		)
	);
}

/**
 * Create the standard pages, build "Tibb House Menu", and assign it as the
 * Primary Navigation — if a primary menu isn't already assigned.
 *
 * Runs on theme activation and, as a safety net, on every admin_init call
 * (see the note in homepage-install.php for why the safety net exists).
 * Idempotent: skips page/menu creation when they already exist by name,
 * and never overwrites a menu the site owner has already assigned.
 */
function tibbhouse_maybe_setup_menu() {
	// Create the standard pages (idempotent by title).
	$about_id   = tibbhouse_menu_get_or_create_page( 'TIBB HOUSE – About Us', tibbhouse_menu_about_content() );
	$contact_id = tibbhouse_menu_get_or_create_page( 'TIBB HOUSE – Contact Us', tibbhouse_menu_contact_content() );
	$privacy_id = tibbhouse_menu_get_or_create_page( 'Privacy Policy', tibbhouse_menu_privacy_content() );
	$terms_id   = tibbhouse_menu_get_or_create_page( 'Terms & Conditions', tibbhouse_menu_terms_content() );

	// Register the Privacy Policy page with WordPress if none is set yet.
	if ( $privacy_id && ! get_option( 'wp_page_for_privacy_policy' ) ) {
		update_option( 'wp_page_for_privacy_policy', $privacy_id );
	}

	// Find or create "Tibb House Menu".
	$menu = get_term_by( 'name', TIBBHOUSE_MENU_NAME, 'nav_menu' );
	$menu_id = $menu ? (int) $menu->term_id : 0;
	if ( ! $menu_id ) {
		$created = wp_create_nav_menu( TIBBHOUSE_MENU_NAME );
		if ( ! is_wp_error( $created ) ) {
			$menu_id = (int) $created;
		}
	}

	if ( ! $menu_id ) {
		return;
	}

	// Populate menu items (skipped individually if already present).
	// Use post_type_archive / post_type item types so WordPress resolves URLs
	// dynamically at render time — avoids stale localhost URLs after migration.
	$position = 1;
	tibbhouse_menu_add_item( $menu_id, __( 'Home', 'tibbhouse' ), home_url( '/' ), $position++ );

	$sections = array(
		'treatments'    => __( 'Treatments', 'tibbhouse' ),
		'conditions'    => __( 'Conditions', 'tibbhouse' ),
		'knowledge'     => __( 'Knowledge', 'tibbhouse' ),
		'practitioners' => __( 'Practitioners', 'tibbhouse' ),
		'locations'     => __( 'Locations', 'tibbhouse' ),
	);
	foreach ( $sections as $post_type => $label ) {
		if ( post_type_exists( $post_type ) ) {
			tibbhouse_menu_add_archive_item( $menu_id, $label, $post_type, $position++ );
		}
	}

	if ( $about_id ) {
		tibbhouse_menu_add_page_item( $menu_id, __( 'About Us', 'tibbhouse' ), $about_id, 'page', $position++ );
	}
	if ( $contact_id ) {
		tibbhouse_menu_add_page_item( $menu_id, __( 'Contact Us', 'tibbhouse' ), $contact_id, 'page', $position++ );
	}

	// Assign as Primary Navigation only if nothing is assigned there yet,
	// so we never clobber a menu the site owner has customized.
	$locations = get_nav_menu_locations();
	if ( empty( $locations['primary'] ) ) {
		$locations['primary'] = $menu_id;
		set_theme_mod( 'nav_menu_locations', $locations );
	}
	if ( empty( $locations['footer'] ) ) {
		$locations['footer'] = $menu_id;
		set_theme_mod( 'nav_menu_locations', $locations );
	}
}
add_action( 'after_switch_theme', 'tibbhouse_maybe_setup_menu' );

/**
 * Safety net: also run on admin_init (see homepage-install.php for why).
 */
add_action( 'admin_init', 'tibbhouse_maybe_setup_menu' );
