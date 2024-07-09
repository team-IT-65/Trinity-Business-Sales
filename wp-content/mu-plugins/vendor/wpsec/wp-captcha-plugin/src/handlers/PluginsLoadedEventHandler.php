<?php

namespace Wpsec\captcha\handlers;

class PluginsLoadedEventHandler extends EventHandler {

	/**
	 * Unique identifier for retrieving translated strings
	 */
	const DOMAIN = 'wpsec-wp-cp';

	/**
	 * Handles plugins loaded hook
	 *
	 * @since   1.0.0
	 */
	public function handle_plugins_loaded_hook() {
		load_plugin_textdomain( self::DOMAIN, false, dirname( plugin_basename( dirname( __DIR__ ) ) ) . '/languages/' );
	}
}
