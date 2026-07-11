<?php
/**
 * PHP built-in server router for WordPress.
 *
 * Routes static files in wp-content directly; everything else goes
 * through WordPress's front controller (index.php).
 */

$uri = urldecode( parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH ) );

// Serve real static files directly (wp-content assets, wp-admin JS/CSS, etc.)
if ( $uri !== '/' && file_exists( __DIR__ . $uri ) && ! is_dir( __DIR__ . $uri ) ) {
    return false; // Let the built-in server handle it.
}

// Map /wp-admin/ and everything else through WordPress.
if ( preg_match( '#^/wp-(admin|includes)/#', $uri ) ) {
    // Let built-in server look for index.php inside those directories.
    return false;
}

// Everything else: WordPress front controller.
require __DIR__ . '/index.php';
