<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 *
 * @package    Wpsec
 * @subpackage Wpsec/core
 */

namespace Wpsec\twofa\Core;

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Wpsec
 * @subpackage Wpsec/core
 */
class TwoFactorTranslationEngine {

	/**
	 * Unique identifier for retrieving translated strings
	 */
	const DOMAIN = 'wpsec-wp-2fa';

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public static function load_plugin_textdomain() {
		load_plugin_textdomain( self::DOMAIN, false, dirname( plugin_basename( dirname( __DIR__ ) ) ) . '/languages/' );
	}
}
