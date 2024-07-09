<?php

namespace Wpsec\captcha\events;

use Wpsec\captcha\handlers\AuthenticateEventHandler;

class AuthenticateEvent {

	/**
	 *  Hook action name defined by WordPress
	 *  @see add_action()
	 */
	const NAME = 'authenticate';

	/**
	 *  Function from Callback Class that will handle Hook action
	 */
	const CALLBACK_FUNCTION = 'handle_authenticate_hook';

	/**
	 *  Hook priority defined by WordPress
	 *  @see add_action()
	 */
	const PRIORITY = 99;

	/**
	 *  Hook allowed arguments defined by WordPress
	 *  @see add_action()
	 */
	const ACCEPTED_ARGS = 1;

	/**
	 * WP error code - flag to identify that was fired from our plugin so we can filter it if where needed
	 */
	const WP_ERROR_CODE = 'wpsec_authentication_failed';

	/**
	 * Load the action hook for Comment from after fields
	 *
	 * @since    1.0.0
	 */
	public static function load_event() {
		add_action( self::NAME, array( new AuthenticateEventHandler(), self::CALLBACK_FUNCTION ), self::PRIORITY, self::ACCEPTED_ARGS );
	}
}
