<?php
/**
 * Tibb House — Starter Content Seeder (CLI)
 *
 * Bootstraps WordPress and runs all starter-content seeders so that
 * Treatments, Conditions, Knowledge, Practitioners, and Locations are
 * populated on every fresh import — without needing an admin-panel visit.
 *
 * Safe to run multiple times: every seeder is flag-guarded and will no-op
 * if its content was already created.
 *
 * Usage: php scripts/seed-starter-content.php
 */

// ── Bootstrap ──────────────────────────────────────────────────────────────
$_SERVER['HTTP_HOST']              = 'localhost';
$_SERVER['HTTP_X_FORWARDED_HOST']  = 'localhost';
$_SERVER['HTTP_X_FORWARDED_PROTO'] = 'https';
$_SERVER['REQUEST_URI']            = '/';
$_SERVER['REQUEST_METHOD']         = 'GET';

$wp_root = dirname( __DIR__ ) . '/wordpress';
if ( ! file_exists( $wp_root . '/wp-load.php' ) ) {
    fwrite( STDERR, "ERROR: WordPress not found at $wp_root\n" );
    exit( 1 );
}

require_once $wp_root . '/wp-load.php';
echo "WordPress loaded. Site URL: " . get_option( 'siteurl' ) . "\n";

// ── Sanity checks ──────────────────────────────────────────────────────────
if ( ! class_exists( 'Tibbhouse_Starter_Content' ) ) {
    fwrite( STDERR, "ERROR: Tibbhouse_Starter_Content class not found.\n" );
    fwrite( STDERR, "       Is the tibbhouse-core plugin active?\n" );
    exit( 1 );
}

if ( ! post_type_exists( 'treatments' ) ) {
    fwrite( STDERR, "ERROR: Custom post types not registered.\n" );
    fwrite( STDERR, "       The plugin may not have loaded correctly.\n" );
    exit( 1 );
}

// ── Run all seeders in order ───────────────────────────────────────────────
$seeder = Tibbhouse_Starter_Content::instance();

echo "\n=== v1: Treatments, Conditions, Knowledge, Practitioners, Locations ===\n";
$seeder->maybe_seed();
echo "  ✓ v1 seeder complete\n";

echo "\n=== v2: Extra Practitioner & Locations ===\n";
$seeder->maybe_seed_v2();
echo "  ✓ v2 seeder complete\n";

echo "\n=== v3: 4th Items + About/Contact Page Content ===\n";
$seeder->maybe_seed_v3();
echo "  ✓ v3 seeder complete\n";

echo "\n=== Repair: Gap-filler (re-seeds any missing content) ===\n";
$seeder->maybe_repair();
echo "  ✓ repair seeder complete\n";

// ── Flush rewrite rules so CPT archives resolve correctly ─────────────────
flush_rewrite_rules( true );
echo "\n  ✓ Rewrite rules flushed\n";

// ── Report final counts ───────────────────────────────────────────────────
echo "\n=== Content Summary ===\n";
$all_ok = true;
$minimums = array(
    'treatments'   => 3,
    'conditions'   => 3,
    'knowledge'    => 3,
    'practitioners'=> 2,
    'locations'    => 1,
);
foreach ( $minimums as $pt => $min ) {
    $q     = new WP_Query( array(
        'post_type'      => $pt,
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'no_found_rows'  => false,
        'fields'         => 'ids',
    ) );
    $count = (int) $q->found_posts;
    $ok    = $count >= $min;
    if ( ! $ok ) {
        $all_ok = false;
    }
    echo sprintf( "  %-16s %d post(s)%s\n", $pt . ':', $count, $ok ? '' : "  ← WARNING: expected at least $min" );
}

echo "\n";
if ( $all_ok ) {
    echo "╔══════════════════════════════════════════════╗\n";
    echo "║  ✓  Starter content seeded successfully      ║\n";
    echo "╚══════════════════════════════════════════════╝\n";
} else {
    fwrite( STDERR, "WARNING: Some post types have fewer posts than expected.\n" );
    fwrite( STDERR, "         The repair seeder will retry on the next run.\n" );
    // Exit 0 — partial content is better than blocking startup entirely.
}

echo "\n";
