<?php
/**
 * Plugin Name: GoDaddy Launch
 * Plugin URI: https://godaddy.com/
 * Description: GoDaddy Launch Description
 * Version: 2.11.0
 * Requires at least: 5.9
 * Requires PHP: 8.0
 * Author: GoDaddy
 * Author URI: https://godaddy.com
 * Text Domain: godaddy-launch
 * Domain Path: /languages
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, see <http://www.gnu.org/licenses/>.
 *
 * @package GoDaddy_Launch
 */

namespace GoDaddy\WordPress\Plugins\Launch;

defined( 'ABSPATH' ) || exit;

// Guard the plugin from initializing more than once.
if ( class_exists( Application::class ) ) {
	return;
}

require_once dirname( __FILE__ ) . '/vendor/autoload.php';

/**
 * Create and retrieve the main applicaiton container instance.
 *
 * @return Application The application container.
 */
function gdl() {
	return Application::getInstance();
}

gdl()->setBasePath( __FILE__ );

/**
 * Autoloaded Service Providers.
 */
$gdl_providers = array(
	GoDaddyStylesServiceProvider::class,
	LiveSiteControl\LiveSiteControlProvider::class,
	PublishGuide\PublishGuideServiceProvider::class,
);

foreach ( $gdl_providers as $gdl_provider ) {
	gdl()->register( $gdl_provider );
}

register_deactivation_hook( __FILE__, array( gdl(), 'deactivation' ) );

// Boot the plugin.
add_action( 'plugins_loaded', array( gdl(), 'boot' ) );
add_action( 'plugins_loaded', array( gdl(), 'loadTextDomain' ) );
add_filter( 'load_textdomain_mofile', array( gdl(), 'loadMoFiles' ), 10, 2 );
