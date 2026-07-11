<?php
/**
 * WordPress configuration — Replit / SQLite edition.
 *
 * Site URL is derived from the incoming request Host header so the site
 * works on the dev domain, any preview domain, and a future deployment
 * domain without any edits here.
 */

// ─── SQLite Database Integration ─────────────────────────────────────────────
// Tells the db.php drop-in where to store the SQLite file.
define( 'DB_DIR', __DIR__ . '/wp-content/database/' );
define( 'DB_FILE', '.ht.sqlite' );

// These are required by WordPress core even when SQLite replaces MySQL.
define( 'DB_NAME',     'local' );
define( 'DB_USER',     'root' );
define( 'DB_PASSWORD', '' );
define( 'DB_HOST',     'localhost' );
define( 'DB_CHARSET',  'utf8' );
define( 'DB_COLLATE',  '' );

// ─── Security Keys & Salts ───────────────────────────────────────────────────
// Generated for this dev install. Change these before going to production.
define( 'AUTH_KEY',         'a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6q7r8s9t0u1v2w3x4y5z6' );
define( 'SECURE_AUTH_KEY',  'z6y5x4w3v2u1t0s9r8q7p6o5n4m3l2k1j0i9h8g7f6e5d4c3b2a1' );
define( 'LOGGED_IN_KEY',    'a9b8c7d6e5f4g3h2i1j0k9l8m7n6o5p4q3r2s1t0u9v8w7x6y5z4' );
define( 'NONCE_KEY',        'z4y3x2w1v0u9t8s7r6q5p4o3n2m1l0k9j8i7h6g5f4e3d2c1b0a9' );
define( 'AUTH_SALT',        'p1l2a3n4t5b6a7s8e9d0e1v2k3e4y5f6o7r8t9h10e11f12u13t14' );
define( 'SECURE_AUTH_SALT', 'q1w2e3r4t5y6u7i8o9p0a1s2d3f4g5h6j7k8l9z0x1c2v3b4n5m6' );
define( 'LOGGED_IN_SALT',   'm6n5b4v3c2x1z0l9k8j7h6g5f4d3s2a1p0o9i8u7y6t5r4e3w2q1' );
define( 'NONCE_SALT',       'i1u2h3s4f5e6t7b8a9d0e1f2g3h4i5j6k7l8m9n0o1p2q3r4s5t6' );

// ─── Table Prefix ────────────────────────────────────────────────────────────
$table_prefix = 'wp_';

// ─── Dynamic Site URL ────────────────────────────────────────────────────────
// Derive the public URL from the incoming Host header so the site works
// on any Replit preview domain without hardcoding.
if ( ! defined( 'WP_HOME' ) ) {
    $protocol = ( ! empty( $_SERVER['HTTP_X_FORWARDED_PROTO'] ) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https' )
        ? 'https'
        : ( ( isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] !== 'off' ) ? 'https' : 'http' );
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    define( 'WP_HOME',    $protocol . '://' . $host );
    define( 'WP_SITEURL', $protocol . '://' . $host );
}

// ─── Paths ───────────────────────────────────────────────────────────────────
define( 'WP_CONTENT_DIR', __DIR__ . '/wp-content' );
define( 'WP_CONTENT_URL', WP_HOME . '/wp-content' );

// ─── Environment ─────────────────────────────────────────────────────────────
define( 'WP_DEBUG',         true );
define( 'WP_DEBUG_LOG',     true );
define( 'WP_DEBUG_DISPLAY', false );
define( 'SCRIPT_DEBUG',     false );

// Increase memory limit for heavy admin operations.
define( 'WP_MEMORY_LIMIT', '256M' );

// Disable cron via HTTP requests; we'll rely on real cron or skip it.
define( 'DISABLE_WP_CRON', true );

// ─── Bootstrap ───────────────────────────────────────────────────────────────
if ( ! defined( 'ABSPATH' ) ) {
    define( 'ABSPATH', __DIR__ . '/' );
}

require_once ABSPATH . 'wp-settings.php';
