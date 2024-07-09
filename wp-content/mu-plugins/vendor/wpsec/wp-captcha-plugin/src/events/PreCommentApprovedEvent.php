<?php

namespace Wpsec\captcha\events;

class PreCommentApprovedEvent extends AbstractCommentEvent {

	/**
	 *  Hook action name defined by WordPress
	 *  @see add_action()
	 */
	const NAME = 'pre_comment_approved';

	/**
	 *  Function from Callback Class that will handle Hook action
	 */
	const CALLBACK_FUNCTION = 'handle_comment_approve_hook';

	/**
	 *  Hook priority defined by WordPress
	 *  @see add_action()
	 */
	const PRIORITY = 10;

	/**
	 *  Hook allowed arguments defined by WordPress
	 *  @see add_action()
	 */
	const ACCEPTED_ARGS = 2;

	/**
	 * WP error code - flag to identify that was fired from our plugin so we can filter it if where needed
	 */
	const WP_ERROR_CODE = 'wpsec_comment_authentication_failed';

	/**
	 * Load the action hook for Pre Comment approved Event
	 *
	 * @since    1.0.0
	 */
	public static function load_event() {
		add_action( self::NAME, array( self::get_callback_handler(), self::CALLBACK_FUNCTION ), self::PRIORITY, self::ACCEPTED_ARGS );
	}
}
