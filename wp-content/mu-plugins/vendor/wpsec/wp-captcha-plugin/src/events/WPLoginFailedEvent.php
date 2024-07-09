<?php

namespace Wpsec\captcha\events;

use Wpsec\captcha\handlers\WPLoginEventHandler;

class WPLoginFailedEvent {

	/**
	 *  Hook action name defined by WordPress
	 *  @see add_action()
	 */
	const NAME = 'wp_login_failed';

	/**
	 *  Function from Callback Class that will handle Hook action
	 */
	const CALLBACK_FUNCTION = 'handle_login_hook';

	/**
	 *  Hook priority defined by WordPress
	 *  @see add_action()
	 */
	const PRIORITY = 1;

	/**
	 *  Hook allowed arguments defined by WordPress
	 *  @see add_action()
	 */
	const ACCEPTED_ARGS = 2;

	/**
	 * Load the action hook for WP Login Failed Event
	 *
	 * @since    1.0.0
	 */
	public static function load_event() {
		add_action( self::NAME, array( new WPLoginEventHandler(), self::CALLBACK_FUNCTION ), self::PRIORITY, self::ACCEPTED_ARGS );
	}
}
