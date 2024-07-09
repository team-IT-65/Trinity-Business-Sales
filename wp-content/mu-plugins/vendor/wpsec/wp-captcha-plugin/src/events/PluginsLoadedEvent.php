<?php

namespace Wpsec\captcha\events;

use Wpsec\captcha\handlers\PluginsLoadedEventHandler;

class PluginsLoadedEvent {

	/**
	 *  Hook action name defined by WordPress
	 *  @see add_action()
	 */
	const NAME = 'plugins_loaded';

	/**
	 *  Function from Callback Class that will handle Hook action
	 */
	const CALLBACK_FUNCTION = 'handle_plugins_loaded_hook';

	/**
	 *  Hook priority defined by WordPress
	 *  @see add_action()
	 */
	const PRIORITY = 10;

	/**
	 *  Hook allowed arguments defined by WordPress
	 *  @see add_action()
	 */
	const ACCEPTED_ARGS = 1;

	/**
	 * Load the action hook plugins loaded
	 *
	 * @since    1.0.0
	 */
	public static function load_event() {
		add_action( self::NAME, array( new PluginsLoadedEventHandler(), self::CALLBACK_FUNCTION ), self::PRIORITY, self::ACCEPTED_ARGS );
	}
}
