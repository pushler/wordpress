<?php
/**
 * Removes the plugin's one saved setting when the plugin is deleted.
 *
 * @package Pushler
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

delete_option( 'pushler_public_key' );
