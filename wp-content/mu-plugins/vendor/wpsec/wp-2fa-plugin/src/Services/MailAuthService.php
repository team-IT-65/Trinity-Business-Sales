<?php

namespace Wpsec\twofa\Services;

use WP_User;
use Exception;
use Wpsec\twofa\API\TwoFactorApiClient;
use Wpsec\twofa\Constants\MailAuthConstants;
use Wpsec\twofa\utils\SiteUtils;
use Wpsec\twofa\utils\UserUtils;
use Wpsec\twofa\utils\RequestUtils;

class MailAuthService {
	/**
	 * TwoFactorAuthService instance .
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      TwoFactorAuthService $tfa_auth_service.
	 */
	private $tfa_auth_service;

	/**
	 * TwoFactorAuthService instance .
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      TwoFactorApiClient $tfa_api_service.
	 */
	private $tfa_api_service;

	public function __construct( $tfa_auth_service, $tfa_api_service ) {
		$this->tfa_auth_service = $tfa_auth_service;
		$this->tfa_api_service  = $tfa_api_service;
	}

	/**
	 * Send auth mail
	 *
	 * @return bool
	 * @since 1.0.0
	 */
	public function send_mail() {
		$user    = UserUtils::get_current_user();
		$message = get_option( MailAuthConstants::MAIL_BODY_DATABASE, $this->get_default_mail_message() );
		$subject = get_option( MailAuthConstants::MAIL_SUBJECT_DATABASE, $this->get_default_mail_subject() );
		$code    = $this->generate_code();
		$this->set_code_expiration_time( $user, $code );
		// TODO: send headers( From: ) once Two Factor is live
		return $this->send_wp_mail( $user->user_email, $this->build_mail_message( $user, $code, $message ), $this->build_mail_message( $user, $code, $subject ) );
	}

	/**
	 * Send auth mail
	 *
	 * @return bool
	 * @since 1.0.0
	 */
	public function send_mail_ajax() {
		$user    = RequestUtils::check_post_param( 'data' ) ? get_user_by( 'id', $_POST['data'] ) : UserUtils::get_current_user();
		$message = get_option( MailAuthConstants::MAIL_BODY_DATABASE, $this->get_default_mail_message() );
		$subject = get_option( MailAuthConstants::MAIL_SUBJECT_DATABASE, $this->get_default_mail_subject() );
		$code    = $this->generate_code();
		$this->set_code_expiration_time( $user, $code );
		// TODO: send headers( From: ) once Two Factor is live
		$success = $this->send_wp_mail( $user->user_email, $this->build_mail_message( $user, $code, $message ), $this->build_mail_message( $user, $code, $subject ) );

		echo $success ? 'admin_mail_sent' : 'admin_mail_not_sent';
		wp_die();
	}

	/**
	 * Validate mail auth
	 *
	 * @return bool Returns if secret is good or not.
	 * @param $data array
	 * @param $user_id string
	 * @since 1.0.0
	 */
	public function validate_mail_auth( $code, $current_user = null ) {

		if ( null === $current_user ) {
			$current_user = UserUtils::get_current_user();
		}

		$secret = get_transient( $this->get_mail_code_transient_name( $current_user->user_login ) );

		if ( ! $secret ) {
			return false;
		}

		if ( $code !== $secret ) {
			return false;
		}

		$res = $this->tfa_api_service->save_mail_code( SiteUtils::get_site_origin(), $current_user->ID, $secret );

		if ( 204 !== wp_remote_retrieve_response_code( $res ) ) {
			return false;
		}

		$this->tfa_auth_service->add_new_2fa_auth_method( $current_user->ID, MailAuthConstants::AUTH_ACTIVE );
		delete_transient( $this->get_mail_code_transient_name( $current_user->user_login ) );
		return true;
	}

	/**
	 * This function saves custom email
	 * @param $from string
	 * @param $subject string
	 * @param $body string
	 * @return bool
	 */
	public function set_custom_template( $from, $subject, $body ) {
		try {
			update_option( MailAuthConstants::MAIL_FROM_DATABASE, $from );
			update_option( MailAuthConstants::MAIL_SUBJECT_DATABASE, $subject );
			update_option( MailAuthConstants::MAIL_BODY_DATABASE, $body );
			return true;
		} catch ( Exception $e ) {
			return false;
		}
	}

	/**
	 * This function gets custom email
	 * @return array
	 */
	public function get_custom_template() {

		$mail_template_data = array();

		$mail_template_data[ MailAuthConstants::MAIL_FROM_DATABASE ]    = get_option( MailAuthConstants::MAIL_FROM_DATABASE, '' );
		$mail_template_data[ MailAuthConstants::MAIL_SUBJECT_DATABASE ] = get_option( MailAuthConstants::MAIL_SUBJECT_DATABASE, '' );
		$mail_template_data[ MailAuthConstants::MAIL_BODY_DATABASE ]    = get_option( MailAuthConstants::MAIL_BODY_DATABASE, '' );

		return $mail_template_data;
	}

	/**
	 * Send test mail based on users parameters.
	 * @param $from
	 * @param $subject
	 * @param $body
	 * @return bool
	 */
	public function send_test_email( $from, $subject, $body ) {
		try {
			$headers = 'From: ' . $from;
			$user    = UserUtils::get_current_user();
			$code    = $this->generate_code();
			$this->set_code_expiration_time( $user, $code );
			return $this->send_wp_mail(
				$user->user_email,
				$this->build_mail_message( $user, $code, $body ),
				$this->build_mail_message( $user, $code, $subject ),
				$headers
			);
		} catch ( Exception $e ) {
			return false;
		}
	}
	/**
	 * Generate a random six-digit string to send out as an auth code.
	 *
	 * @since 1.0.0
	 *
	 * @param int          $length The code length.
	 * @param string|array $chars Valid auth code characters.
	 * @return string
	 */
	private function generate_code( $length = 6, $chars = '1234567890' ) {
		$code = '';
		if ( is_array( $chars ) ) {
			$chars = implode( '', $chars );
		}
		for ( $i = 0; $i < $length; $i++ ) {
			$code .= substr( $chars, wp_rand( 0, strlen( $chars ) - 1 ), 1 );
		}

		return $code;
	}

	/**
	 * Returns default mail message
	 *
	 * @return string
	 * @since 1.0.0
	 */
	private function get_default_mail_message() {
		return __( 'Your one-time code is: [code].', 'wpsec-wp-2fa' ) . "\n" . __( 'Site: [site-url]', 'wpsec-wp-2fa' ) . "\n" . __( "Please verify you're really you by entering this 6-digit code when you sign in.", 'wpsec-wp-2fa' ) . "\n" . __( 'Just a heads up, this code will expire in 15 minutes for security reasons.', 'wpsec-wp-2fa' );
	}

	/**
	 * Builds email message
	 *
	 * @param WP_User $user WP user.
	 * @param string $message Message.
	 * @param string $code Code.
	 * @return string
	 * @since 1.0.0
	 */
	private function build_mail_message( $user, $code, $message = '' ) {
		$message = str_replace( MailAuthConstants::MAIL_TEMPLATE_CODE, $code, $message );
		$message = str_replace( MailAuthConstants::MAIL_TEMPLATE_SITE_NAME, get_bloginfo( 'name' ), $message );
		$message = str_replace( MailAuthConstants::MAIL_TEMPLATE_SITE_URL, get_bloginfo( 'url' ), $message );

		return str_replace( MailAuthConstants::MAIL_TEMPLATE_USER_LOGIN, $user->user_login, $message );
	}
	/**
	 * Returns default mail message
	 *
	 * @return string
	 * @since 1.0.0
	 */
	private function get_default_mail_subject() {
		return __( 'Your one-time sign in code is [code]', 'wpsec-wp-2fa' );
	}

	/**
	 * Set expiration time for mail code.
	 *
	 * @param WP_User $user
	 * @param integer $code - generated 6 digit code
	 * @param integer $expiration - Time until expiration in seconds. Default is 15min.
	 * @since 1.0.0
	 */
	private function set_code_expiration_time( $user, $code, $expiration = MailAuthConstants::DEFAULT_CODE_EXPIRATION_TIME ) {
		set_transient( $this->get_mail_code_transient_name( $user->user_login ), $code, $expiration );
	}

	/**
	 * Generates mail code transient name
	 *
	 * @param string $username
	 * @return string
	 * @since 1.0.0
	 */
	private function get_mail_code_transient_name( $username ) {
		return sprintf( '%s_%s', MailAuthConstants::MAIL_SECRET_DATABASE, $username );
	}

	/**
	 * Handles sending of an email. It sets necessary header such as content type.
	 *
	 * @param string $recipient_email Email address to send message to.
	 * @param string $message Message contents.
	 * @param string $subject Message subject.
	 *
	 * @return bool Whether the email contents were sent successfully.
	 */
	private function send_wp_mail( $recipient_email, $message, $subject ) {
		// TODO: accept and send headers when Two Factor go live
		return wp_mail( $recipient_email, $subject, $message );
	}
}
