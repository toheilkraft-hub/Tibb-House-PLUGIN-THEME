<?php
/**
 * Tibb House — Page & Menu Seeder
 *
 * Run once to create all public pages, set the static front page,
 * and wire up the Primary Navigation menu with every required item.
 *
 * Usage:
 *   php scripts/seed-pages-menus.php
 */

// ── Bootstrap WordPress ────────────────────────────────────────────────────
define( 'ABSPATH_SEED', true );

$_SERVER['HTTP_HOST']            = 'localhost';
$_SERVER['HTTP_X_FORWARDED_HOST']  = 'localhost';
$_SERVER['HTTP_X_FORWARDED_PROTO'] = 'https';
$_SERVER['REQUEST_URI']          = '/';
$_SERVER['REQUEST_METHOD']       = 'GET';

$wp_root = dirname( __DIR__ ) . '/wordpress';
if ( ! file_exists( $wp_root . '/wp-load.php' ) ) {
    die( "ERROR: WordPress not found at $wp_root\n" );
}

require_once $wp_root . '/wp-load.php';

echo "WordPress loaded. Site URL: " . get_option( 'siteurl' ) . "\n";

// ── Helper: create or fetch a page ────────────────────────────────────────
function th_ensure_page( $args ) {
    $slug = $args['post_name'];

    // Check by slug first.
    $existing = get_page_by_path( $slug, OBJECT, 'page' );
    if ( $existing ) {
        // Update template if supplied and different.
        if ( ! empty( $args['template'] ) ) {
            $current_tpl = get_post_meta( $existing->ID, '_wp_page_template', true );
            if ( $current_tpl !== $args['template'] ) {
                update_post_meta( $existing->ID, '_wp_page_template', $args['template'] );
                echo "  ↑ Updated template on existing page: {$existing->post_title}\n";
            }
        }
        echo "  ✓ Page already exists: {$existing->post_title} (ID {$existing->ID})\n";
        return $existing->ID;
    }

    $insert = array(
        'post_title'   => $args['post_title'],
        'post_name'    => $slug,
        'post_status'  => 'publish',
        'post_type'    => 'page',
        'post_content' => $args['post_content'] ?? '',
        'post_author'  => 1,
    );

    $id = wp_insert_post( $insert, true );
    if ( is_wp_error( $id ) ) {
        echo "  ✗ Failed to create page '{$args['post_title']}': " . $id->get_error_message() . "\n";
        return null;
    }

    if ( ! empty( $args['template'] ) ) {
        update_post_meta( $id, '_wp_page_template', $args['template'] );
    }

    echo "  + Created page: {$args['post_title']} (ID $id)\n";
    return $id;
}

// ── 1. Create pages ────────────────────────────────────────────────────────
echo "\n=== Creating Pages ===\n";

$home_id = th_ensure_page( array(
    'post_title'   => 'Home',
    'post_name'    => 'home',
    'post_content' => '',
) );

$about_id = th_ensure_page( array(
    'post_title'   => 'About Us',
    'post_name'    => 'about-us',
    'post_content' => '<!-- wp:paragraph --><p>Tibb House is a natural and Islamic medicine clinic dedicated to holistic wellbeing. Rooted in the prophetic traditions of Tibb Nabawi and the wisdom of classical Islamic medicine, we blend time-honoured healing principles with modern evidence-based practice.</p><!-- /wp:paragraph -->

<!-- wp:paragraph --><p>Our team of qualified practitioners offers personalised consultations, treatments, and guidance to support your physical, mental, and spiritual health — in person and remotely.</p><!-- /wp:paragraph -->',
) );

$contact_id = th_ensure_page( array(
    'post_title'   => 'Contact Us',
    'post_name'    => 'contact-us',
    'post_content' => '',
    'template'     => 'page-contact.php',
) );

$blog_id = th_ensure_page( array(
    'post_title'   => 'Blog',
    'post_name'    => 'blog',
    'post_content' => '',
) );

$forms_id = th_ensure_page( array(
    'post_title'   => 'Patient Forms',
    'post_name'    => 'patient-forms',
    'post_content' => '<!-- wp:paragraph --><p>Please complete the appropriate intake form before your first consultation. All forms are handled securely and confidentially.</p><!-- /wp:paragraph -->

<!-- wp:paragraph --><p>For sensitive medical information, use our <a href="/private-data-hipaa/">Secure Patient Intake</a> portal — encrypted and HIPAA-aligned.</p><!-- /wp:paragraph -->',
) );

$hipaa_id = th_ensure_page( array(
    'post_title'   => 'Secure Patient Intake',
    'post_name'    => 'private-data-hipaa',
    'post_content' => '',
    'template'     => 'page-hipaa.php',
) );

// ── 2. Reading settings: static front page ─────────────────────────────────
echo "\n=== Reading Settings ===\n";

if ( $home_id ) {
    update_option( 'show_on_front', 'page' );
    update_option( 'page_on_front', $home_id );
    echo "  ✓ Front page set to: Home (ID $home_id)\n";
}

if ( $blog_id ) {
    update_option( 'page_for_posts', $blog_id );
    echo "  ✓ Posts page set to: Blog (ID $blog_id)\n";
}

// ── 3. Primary Navigation Menu ────────────────────────────────────────────
echo "\n=== Primary Navigation Menu ===\n";

$menu_name     = 'Primary Navigation';
$menu_location = 'primary';

$menu_obj = wp_get_nav_menu_object( $menu_name );
if ( $menu_obj ) {
    $menu_id = $menu_obj->term_id;
    echo "  ✓ Menu already exists: $menu_name (ID $menu_id)\n";
} else {
    $menu_id = wp_create_nav_menu( $menu_name );
    if ( is_wp_error( $menu_id ) ) {
        die( "  ✗ Could not create menu: " . $menu_id->get_error_message() . "\n" );
    }
    echo "  + Created menu: $menu_name (ID $menu_id)\n";
}

// Remove all existing items so we can rebuild cleanly.
$existing_items = wp_get_nav_menu_items( $menu_id, array( 'post_status' => 'publish,draft' ) );
if ( $existing_items ) {
    foreach ( $existing_items as $item ) {
        wp_delete_post( $item->ID, true );
    }
    echo "  ↺ Cleared " . count( $existing_items ) . " existing menu item(s)\n";
}

// Helper to add a page link item.
function th_add_menu_page( $menu_id, $page_id, $title, $order ) {
    if ( ! $page_id ) return null;
    $item_id = wp_update_nav_menu_item( $menu_id, 0, array(
        'menu-item-title'     => $title,
        'menu-item-object'    => 'page',
        'menu-item-object-id' => $page_id,
        'menu-item-type'      => 'post_type',
        'menu-item-status'    => 'publish',
        'menu-item-position'  => $order,
    ) );
    if ( is_wp_error( $item_id ) ) {
        echo "  ✗ Failed to add '$title': " . $item_id->get_error_message() . "\n";
        return null;
    }
    echo "  + Menu item: $title\n";
    return $item_id;
}

// Helper to add a custom URL item.
function th_add_menu_custom( $menu_id, $url, $title, $order, $classes = '' ) {
    $item_id = wp_update_nav_menu_item( $menu_id, 0, array(
        'menu-item-title'    => $title,
        'menu-item-url'      => $url,
        'menu-item-type'     => 'custom',
        'menu-item-status'   => 'publish',
        'menu-item-position' => $order,
        'menu-item-classes'  => $classes,
    ) );
    if ( is_wp_error( $item_id ) ) {
        echo "  ✗ Failed to add custom '$title': " . $item_id->get_error_message() . "\n";
        return null;
    }
    echo "  + Menu item (custom): $title\n";
    return $item_id;
}

th_add_menu_page(   $menu_id, $home_id,    'Home',           1 );
th_add_menu_page(   $menu_id, $about_id,   'About Us',       2 );
th_add_menu_custom( $menu_id, home_url( '/?post_type=treatments' ),  'Treatments',  3 );
th_add_menu_custom( $menu_id, home_url( '/?post_type=conditions' ),  'Conditions',  4 );
th_add_menu_custom( $menu_id, home_url( '/?post_type=practitioners' ), 'Practitioners', 5 );
th_add_menu_page(   $menu_id, $blog_id,    'Blog',           6 );
th_add_menu_page(   $menu_id, $contact_id, 'Contact Us',     7 );
th_add_menu_page(   $menu_id, $forms_id,   'Patient Forms',  8 );

// Secure CTA — custom URL pointing to the HIPAA page.
$hipaa_url = $hipaa_id ? get_permalink( $hipaa_id ) : home_url( '/private-data-hipaa/' );
th_add_menu_custom( $menu_id, $hipaa_url, 'Secure Patient Intake', 9, 'th-nav-cta-btn' );

// ── 4. Assign menu to theme location ──────────────────────────────────────
echo "\n=== Assigning Menu to Theme Location ===\n";

$locations = get_theme_mod( 'nav_menu_locations', array() );
$locations[ $menu_location ] = $menu_id;
set_theme_mod( 'nav_menu_locations', $locations );
echo "  ✓ Assigned '$menu_name' to location '$menu_location'\n";

// ── 5. Footer menu ────────────────────────────────────────────────────────
echo "\n=== Footer Navigation Menu ===\n";

$footer_name = 'Footer Navigation';
$footer_obj  = wp_get_nav_menu_object( $footer_name );
if ( $footer_obj ) {
    $footer_id = $footer_obj->term_id;
    echo "  ✓ Footer menu already exists (ID $footer_id)\n";
} else {
    $footer_id = wp_create_nav_menu( $footer_name );
    echo "  + Created footer menu (ID $footer_id)\n";
}

$footer_existing = wp_get_nav_menu_items( $footer_id, array( 'post_status' => 'publish,draft' ) );
if ( $footer_existing ) {
    foreach ( $footer_existing as $item ) {
        wp_delete_post( $item->ID, true );
    }
}

th_add_menu_page(   $footer_id, $home_id,    'Home',          1 );
th_add_menu_page(   $footer_id, $about_id,   'About Us',      2 );
th_add_menu_page(   $footer_id, $contact_id, 'Contact Us',    3 );
th_add_menu_page(   $footer_id, $blog_id,    'Blog',          4 );
th_add_menu_page(   $footer_id, $forms_id,   'Patient Forms', 5 );

$footer_locations = get_theme_mod( 'nav_menu_locations', array() );
$footer_locations['footer'] = $footer_id;
set_theme_mod( 'nav_menu_locations', $footer_locations );
echo "  ✓ Assigned footer menu to location 'footer'\n";

// ── 6. Flush rewrite rules ────────────────────────────────────────────────
echo "\n=== Flushing Rewrite Rules ===\n";
flush_rewrite_rules( true );
echo "  ✓ Rewrite rules flushed\n";

// ── Done ──────────────────────────────────────────────────────────────────
echo "\n╔══════════════════════════════════════════════╗\n";
echo "║  ✓  Pages & menus seeded successfully        ║\n";
echo "╚══════════════════════════════════════════════╝\n";
echo "\nPages created:\n";
echo "  Home          → " . get_permalink( $home_id ) . "\n";
echo "  About Us      → " . get_permalink( $about_id ) . "\n";
echo "  Contact Us    → " . get_permalink( $contact_id ) . "\n";
echo "  Blog          → " . get_permalink( $blog_id ) . "\n";
echo "  Patient Forms → " . get_permalink( $forms_id ) . "\n";
echo "  Secure Intake → " . get_permalink( $hipaa_id ) . "\n";
