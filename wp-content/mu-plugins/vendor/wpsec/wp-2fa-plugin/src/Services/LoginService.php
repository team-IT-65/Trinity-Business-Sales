<?php

namespace Wpsec\twofa\Services;

use WP_User;
use Wpsec\twofa\Constants\GoogleAuthenticatorConstants;
use Wpsec\twofa\Constants\MailAuthConstants;
use Wpsec\twofa\Constants\YubikeyAuthConstants;
use Wpsec\twofa\utils\NonceUtils;
use Wpsec\twofa\utils\PhpUtils;

/**
 * Google authenticator service.
 *
 * @package Wpsec
 * @subpackage Wpsec/services
 */
class LoginService {

	const WPSEC_2FA_USED_CODE = 'wpsec_2fa_used_code';

	/**
	 * The config parameters for 2fa plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      array $config The config parameters for capthca plugin.
	 */
	protected $version;

	/** @var YubikeyAuthService */
	private $yubikey_service;

	/** @var GoogleAuthenticatorService */
	private $google_auth_service;

	/** @var MailAuthService */
	private $mail_service;

	/** @var TwoFactorAuthService */
	private $two_factor_service;


	public function __construct( $yubikey_service, $google_auth_service, $mail_service, $two_factor_service, $version ) {
		$this->yubikey_service     = $yubikey_service;
		$this->google_auth_service = $google_auth_service;
		$this->mail_service        = $mail_service;
		$this->two_factor_service  = $two_factor_service;
		$this->version             = $version;
	}
	/**
	 * Login form validation.
	 *
	 * @since 1.0.0
	 */
	public function login_form_validate_2fa() {

		$user          = self::validate_post_params_and_nonce();
		$success_login = false;
		if ( ! $user ) {
			echo 'login_error';
			wp_die();
		}

		$data     = json_decode( stripslashes( $_POST['data'] ), true );
		$provider = $data['wpsec_2fa_login_provider'];

		if ( GoogleAuthenticatorConstants::AUTH_ACTIVE === $provider && $this->google_auth_service->validate_authenticator_app_code( $data['wpsec_2fa_login_app_code_check'], $user ) ) {

			$transient_name = self::build_transient_name( $user );
			set_transient( $transient_name, $data['wpsec_2fa_login_app_code_check'], 31 );
			$success_login = true;

		} elseif ( MailAuthConstants::AUTH_ACTIVE === $provider && $this->mail_service->validate_mail_auth( $data['wpsec_2fa_login_mail_code_check'], $user ) ) {

			$success_login = true;

		} elseif ( YubikeyAuthConstants::AUTH_ACTIVE === $provider && $this->setup_or_validate_yubikey( $data['wpsec_2fa_login_yubikey_code_check'], $user ) ) {

			$transient_name = self::build_transient_name( $user );
			set_transient( $transient_name, $data['wpsec_2fa_login_yubikey_code_check'], 31 );
			$success_login = true;

		} else {
			echo 'login_error';
			wp_die();
		}

		if ( $success_login ) {
			wp_set_auth_cookie( $user->ID );
		}

		wp_die();
	}

	public function setup_or_validate_yubikey( $otp, $user ) {

		if ( in_array( YubikeyAuthConstants::AUTH_ACTIVE, $this->two_factor_service->get_active_2fa_methods( $user->ID ), true ) ) {
			return $this->yubikey_service->validate_yubikey( $otp, $user );
		} else {
			return $this->yubikey_service->setup_yubikey( $otp, $user );
		}

	}


	/**
	 * Gets current user.
	 *
	 * @param int $user_id User ID.
	 * @return WP_User|bool
	 * @since 1.0.0
	 */
	private function get_user( $user_id ) {
		global $current_user;

		$userdata = WP_User::get_data_by( 'id', $user_id );

		if ( ! $userdata ) {
			return false;
		}

		if ( $current_user instanceof WP_User && $current_user->ID === (int) $userdata->ID ) {
			return $current_user;
		}

		$user = new WP_User();
		$user->init( $userdata );

		return $user;
	}

	/**
	 * Builds transient name
	 *
	 * @param WP_User $user Current user.
	 * @return string
	 * @since 1.0.0
	 */
	private function build_transient_name( $user ) {
		$transient_name = '%s_%s';

		return sprintf( $transient_name, self::WPSEC_2FA_USED_CODE, $user->user_login );
	}

	/**
	 * Validates nonce, post params.
	 *
	 * @return WP_User|bool
	 * @since 1.0.0
	 */
	private function validate_post_params_and_nonce() {
		if ( ! isset( $_POST['data'], $_POST['nonce'], $_POST['user_id'] ) ) {
			return false;
		}

		$auth_id = (int) $_POST['user_id'];
		$user    = self::get_user( $auth_id );
		if ( ! $user ) {
			return false;
		}

		$nonce = ( ! empty( $_POST['nonce'] ) ) ? sanitize_textarea_field( wp_unslash( $_POST['nonce'] ) ) : '';
		if ( true !== NonceUtils::verify_login_nonce( $user->ID, $nonce ) ) {
			wp_safe_redirect( get_bloginfo( 'url' ) );
			PhpUtils::exit_script();
		}

		return $user;
	}

}
