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
 * @package           Wptool
 *
 * @wordpress-plugin
 * Plugin Name:       WPTOOL - WP Admin Dash Plugin
 * Plugin URI:        godaddy.com
 * Description:       This is a short description of what the plugin does. It's displayed in the WordPress admin area.
 * Version:           1.0.14
 * Author:            GoDaddy
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wptool-admin-dashboard
 * Domain Path:       /languages
 */

// If this file is called directly, abort.

if (!defined('WPINC')) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define("WPTOOL_WP_AD_VERSION", "1.0.14");

require_once __DIR__ . '/vendor/autoload.php';

use Wptool\adminDash\core\AdminDashCore;

$core = new AdminDashCore("wptool-admin-dashboard");
