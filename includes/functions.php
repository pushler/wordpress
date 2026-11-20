<?php
/**
 * Shared helpers.
 *
 * @package Pushler
 */

defined( 'ABSPATH' ) || exit;

/**
 * The address of the Pushler app, without a trailing slash.
 *
 * Define PUSHLER_APP_URL in wp-config.php, or use the pushler_app_url filter,
 * to point the plugin at another Pushler address.
 */
function pushler_app_url(): string {
	$url = defined( 'PUSHLER_APP_URL' ) ? (string) PUSHLER_APP_URL : 'https://app.pushler.com';

	return untrailingslashit( (string) apply_filters( 'pushler_app_url', $url ) );
}

/** The site's Pushler public key, or an empty string when none is saved. */
function pushler_public_key(): string {
	return (string) get_option( PUSHLER_OPTION, '' );
}

/**
 * Cleans a public key typed or pasted into the settings page.
 *
 * Accepts the key alone, or the whole script tag copied from Pushler, and
 * returns the key ("pk_" and letters or digits), or an empty string.
 *
 * @param mixed $value What was submitted.
 */
function pushler_sanitize_key( $value ): string {
	$value = is_string( $value ) ? trim( wp_unslash( $value ) ) : '';

	if ( preg_match( '/pk_[A-Za-z0-9]{8,64}/', $value, $match ) ) {
		return $match[0];
	}

	return '';
}

/** The address the service worker is served from. */
function pushler_worker_url(): string {
	return home_url( '/' . PUSHLER_WORKER );
}

/** The service worker file's contents: it loads the real worker from Pushler. */
function pushler_worker_source(): string {
	return 'importScripts(' . wp_json_encode( pushler_app_url() . '/sdk/sw.js', JSON_UNESCAPED_SLASHES ) . ");\n";
}

/** True when WordPress runs in a folder (example.com/blog) and not at the domain root. */
function pushler_in_subfolder(): bool {
	$path = (string) wp_parse_url( home_url( '/' ), PHP_URL_PATH );

	return '' !== trim( $path, '/' );
}
