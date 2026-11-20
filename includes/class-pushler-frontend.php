<?php
/**
 * Adds the Pushler script to every page of the site.
 *
 * @package Pushler
 */

defined( 'ABSPATH' ) || exit;

class Pushler_Frontend {

	const HANDLE = 'pushler';

	public static function register(): void {
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue' ) );
		add_filter( 'script_loader_tag', array( __CLASS__, 'add_key' ), 10, 2 );
	}

	public static function enqueue(): void {
		if ( '' === pushler_public_key() ) {
			return;
		}

		// Loaded from Pushler, in the footer and async, so it never holds up the page.
		wp_enqueue_script(
			self::HANDLE,
			pushler_app_url() . '/sdk/v1.js',
			array(),
			PUSHLER_VERSION,
			array(
				'strategy'  => 'async',
				'in_footer' => true,
			)
		);
	}

	/**
	 * The script reads the site's key from its own tag.
	 *
	 * @param string $tag    The script tag.
	 * @param string $handle The script's handle.
	 */
	public static function add_key( $tag, $handle ) {
		if ( self::HANDLE !== $handle || false !== strpos( $tag, 'data-key=' ) ) {
			return $tag;
		}

		// data-cfasync and data-no-optimize keep optimisation plugins from rewriting the tag.
		$attributes = sprintf( ' data-key="%s" data-cfasync="false" data-no-optimize="1"', esc_attr( pushler_public_key() ) );

		// Only the tag with a src: other code may have attached inline scripts to this handle.
		return preg_replace( '/<script\b(?=[^>]*\ssrc=)/', '<script' . $attributes, $tag, 1 );
	}
}
