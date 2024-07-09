<?php

namespace Wpsec\captcha\events;

use Wpsec\captcha\handlers\CommentFromAfterFieldsEventHandler;

class CommentFormAfterFieldsEvent {

	/**
	 *  Hook action name defined by WordPress
	 *  @see add_action()
	 */
	const NAME = 'comment_form_after_fields';

	/**
	 *  Function from Callback Class that will handle Hook action
	 */
	const CALLBACK_FUNCTION = 'handle_comment_form_hook';

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
	 * Load the action hook for Comment from after fields
	 *
	 * @since    1.0.0
	 */
	public static function load_event() {
		add_action( self::NAME, array( new CommentFromAfterFieldsEventHandler(), self::CALLBACK_FUNCTION ), self::PRIORITY, self::ACCEPTED_ARGS );
	}
}
