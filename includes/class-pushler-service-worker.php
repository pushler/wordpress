<?php
/**
 * Serves the service worker from the site's root, so nothing has to be uploaded.
 *
 * @package Pushler
 */

defined( 'ABSPATH' ) || exit;

/**
 * Browsers only accept a service worker from the site's own domain. WordPress
 * answers /pushler-sw.js itself: a one-line file that loads the worker from
 * Pushler, so the worker stays up to date without a plugin update.
 */
class Pushler_Service_Worker {

	public static function register(): void {
		// Early, before WordPress looks for a page with this address.
		add_action( 'init', array( __CLASS__, 'maybe_serve' ), 0 );
		// Stop WordPress redirecting /pushler-sw.js to /pushler-sw.js/.
		add_filter( 'redirect_canonical', array( __CLASS__, 'no_trailing_slash' ), 10, 2 );
	}

	/** Whether this request asks for the worker file. */
	public static function is_worker_request(): bool {
		if ( empty( $_SERVER['REQUEST_URI'] ) ) {
			return false;
		}

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Only compared, never output.
		$requested = wp_parse_url( wp_unslash( $_SERVER['REQUEST_URI'] ), PHP_URL_PATH );
		$worker    = wp_parse_url( pushler_worker_url(), PHP_URL_PATH );

		return is_string( $requested ) && $requested === $worker;
	}

	public static function maybe_serve(): void {
		if ( ! self::is_worker_request() ) {
			return;
		}

		status_header( 200 );
		header( 'Content-Type: application/javascript; charset=utf-8' );
		header( 'Service-Worker-Allowed: /' );
		header( 'Cache-Control: public, max-age=3600' );
		header( 'X-Robots-Tag: noindex' );

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- JavaScript, built with wp_json_encode().
		echo pushler_worker_source();
		exit;
	}

	/**
	 * @param string|false $redirect  Where WordPress wants to redirect to.
	 * @param string       $requested The address that was asked for.
	 * @return string|false
	 */
	public static function no_trailing_slash( $redirect, $requested ) {
		return self::is_worker_request() ? false : $redirect;
	}
}
