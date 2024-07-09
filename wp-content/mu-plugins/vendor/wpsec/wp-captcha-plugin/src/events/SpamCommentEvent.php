<?php

namespace Wpsec\captcha\events;

class SpamCommentEvent extends AbstractCommentEvent {

	/**
	 *  Hook action name defined by WordPress
	 *  @see add_action()
	 */
	const NAME = 'spammed_comment';

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
	 * Load the action hook for Spam Comment Event
	 *
	 * @since    1.0.0
	 */
	public static function load_event() {
		add_action( self::NAME, array( self::get_callback_handler(), self::CALLBACK_FUNCTION ), self::PRIORITY, self::ACCEPTED_ARGS );
	}
}
