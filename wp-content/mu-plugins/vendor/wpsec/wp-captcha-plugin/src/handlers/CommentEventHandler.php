<?php

namespace Wpsec\captcha\handlers;

use WP_Comment;
use WP_Error;
use Wpsec\captcha\events\PreCommentApprovedEvent;
use Wpsec\captcha\events\SpamCommentEvent;
use Wpsec\captcha\events\UnSpamCommentEvent;
use Wpsec\captcha\service\CaptchaService;
use Wpsec\captcha\utils\SiteUtil;

class CommentEventHandler extends EventHandler {

	private static $event_status_map = array(
		SpamCommentEvent::NAME   => 'marked_as_spam',
		UnSpamCommentEvent::NAME => 'unmarked_as_spam',
	);

	private static $event_approve_map = array(
		PreCommentApprovedEvent::NAME => 'comment',
	);

	/**
	 * Handles comment hooks
	 *
	 * @param   int         $comment_id The comment ID
	 * @param   WP_Comment  $comment    Comment object
	 * @since   1.0.0
	 */
	public function handle_comment_hook( $comment_id, $comment ) {
		$current_hook_name = current_action();

		if ( ! $current_hook_name || ! isset( self::$event_status_map[ $current_hook_name ] ) || empty( $comment->comment_author_IP ) ) {
			return;
		}

		$event_meta = array(
			'comment_content'    => $comment->comment_content,
			'comment_author'     => $comment->comment_author,
			'comment_author_url' => $comment->comment_author_url,
		);
		$this->send_event( self::$event_status_map[ $current_hook_name ], array( 'comment_author_ip' => $comment->comment_author_IP ), $event_meta );
	}

	/**
	 * Handles comment approve hook
	 *
	 * @param   int|string|WP_Error     $approved Is it comment approved for storing into DB
	 * @param   array  $comment_data    Comment data
	 * @since   1.0.0
	 */
	public function handle_comment_approve_hook( $approved, $comment_data ) {
		$current_hook_name = current_action();

		if ( ! $current_hook_name || ! isset( self::$event_approve_map[ $current_hook_name ] ) || empty( $comment_data['comment_author_IP'] ) ) {
			return $approved;
		}

		$captcha_answer = isset( $_POST['wpsec_captcha_answer'] ) ? $_POST['wpsec_captcha_answer'] : '';
		$captcha_id     = isset( $_POST['wpsec_captcha_id'] ) ? $_POST['wpsec_captcha_id'] : '';

		$event_meta = array(
			'comment_content'    => $comment_data['comment_content'],
			'comment_author'     => $comment_data['comment_author'],
			'comment_author_url' => $comment_data['comment_author_url'],
			'captcha_id'         => $captcha_id,
			'captcha_answer'     => $captcha_answer,
		);

		$response = $this->send_event( self::$event_approve_map[ $current_hook_name ], array( 'comment_author_ip' => $comment_data['comment_author_IP'] ), $event_meta );

		$captcha_service = new CaptchaService();

		$status_code = wp_remote_retrieve_response_code( $response );
		if ( 204 !== $status_code && $status_code < 500 && $captcha_service->is_wpsec_comment_captcha_enabled() ) {
			/* translators: %s: search term */
			$error_message = sprintf( esc_html__( '%1$s%2$sError:%3$s CAPTCHA verification failed. Please go back and try again.%4$s', 'wpsec-wp-cp' ), '<p>', '<strong>', '</strong>', '</p>' );
			return new WP_Error( PreCommentApprovedEvent::WP_ERROR_CODE, $error_message, 401 );
		}

		return $approved;
	}
}
