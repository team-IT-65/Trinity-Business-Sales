<?php

namespace Wpsec\captcha\events;

use Wpsec\captcha\handlers\CommentEventHandler;

abstract class AbstractCommentEvent {

	/**
	 *  Function from Callback Class that will handle Hook action
	 */
	const CALLBACK_FUNCTION = 'handle_comment_hook';

	/**
	 * Init and return comment event handler
	 *
	 * @since   1.0.0
	 */
	public static function get_callback_handler() {
		return new CommentEventHandler();
	}
}
