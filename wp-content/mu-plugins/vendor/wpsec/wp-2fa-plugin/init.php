<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @since             1.0.0
 * @package           Wpsec
 *
 * @wordpress-plugin
 * Plugin Name:       WPSEC - WP 2FA Plugin
 * Plugin URI:        godaddy.com
 * Description:       This is a short description of what the plugin does. It's displayed in the WordPress admin area.
 * Version:           1.0.6
 * Author:            GoDaddy
 * Text Domain:       wpsec-wp-2fa
 * Domain Path:       /languages
 */

// If this file is called directly, abort.

if ( ! defined( 'WPINC' ) ) {
	die;
}

require_once __DIR__ . '/vendor/autoload.php';

use Wpsec\twofa\Core\TwoFactorAuthCore;

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define("WPSEC_WP_2FA_VERSION", "1.0.6");

$core = new TwoFactorAuthCore( 'wpsec-wp-2fa' );
