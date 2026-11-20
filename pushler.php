<?php
/**
 * Plugin Name:       Pushler – Web Push for Klaviyo
 * Plugin URI:        https://pushler.com
 * Description:       Adds Pushler web push notifications to your site: the Pushler script on every page and the service worker at your domain root, with no files to upload.
 * Version:           1.0.0
 * Requires at least: 6.3
 * Requires PHP:      7.4
 * Author:            Pushler
 * Author URI:        https://pushler.com
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       pushler
 *
 * @package Pushler
 */

defined( 'ABSPATH' ) || exit;

define( 'PUSHLER_VERSION', '1.0.0' );
define( 'PUSHLER_FILE', __FILE__ );
define( 'PUSHLER_DIR', plugin_dir_path( __FILE__ ) );

/** The option that holds the site's Pushler public key. */
const PUSHLER_OPTION = 'pushler_public_key';

/** The service worker's file name, at the site's root. It must match the path set in Pushler. */
const PUSHLER_WORKER = 'pushler-sw.js';

require_once PUSHLER_DIR . 'includes/functions.php';
require_once PUSHLER_DIR . 'includes/class-pushler-service-worker.php';
require_once PUSHLER_DIR . 'includes/class-pushler-frontend.php';
require_once PUSHLER_DIR . 'includes/class-pushler-settings.php';

Pushler_Service_Worker::register();
Pushler_Frontend::register();

if ( is_admin() ) {
	Pushler_Settings::register();
}
