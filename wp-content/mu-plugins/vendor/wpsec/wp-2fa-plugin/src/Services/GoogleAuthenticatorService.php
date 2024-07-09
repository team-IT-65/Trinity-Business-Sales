<?php

namespace Wpsec\twofa\Services;

use Wpsec\twofa\API\TwoFactorApiClient;
use Wpsec\twofa\Constants\GoogleAuthenticatorConstants;
use Wpsec\twofa\Constants\ToggleStatus;
use Wpsec\twofa\utils\SiteUtils;
use Wpsec\twofa\utils\UserUtils;

class GoogleAuthenticatorService {

	/**
	 * TwoFactorAuthService instance .
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      TwoFactorAuthService $tfa_auth_service.
	 */
	private $tfa_auth_service;

	/**
	 * TwoFactorAuthService instance.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      TwoFactorApiClient $tfa_api_client.
	 */
	private $tfa_api_client;

	public function __construct( $tfa_auth_service, $tfa_api_client ) {
		$this->tfa_auth_service = $tfa_auth_service;
		$this->tfa_api_client   = $tfa_api_client;

	}

	/**
	 * Generates secret for authenticator app QR code generator
	 *
	 * @return bool|string
	 * @since 1.0.0
	 */
	public function generate_authenticator_app_qr_code( $current_user_id = null ) {
		if ( null === $current_user_id ) {
			$current_user_id = UserUtils::get_current_user()->ID;
		}

		$active_2fa_methods = $this->tfa_auth_service->get_active_2fa_methods( $current_user_id );
		$username           = get_userdata( $current_user_id )->user_login;
		if ( in_array( GoogleAuthenticatorConstants::AUTH_ACTIVE, $active_2fa_methods, true ) ) {
			return null;
		}

		$res = $this->tfa_api_client->generate_qr_code( SiteUtils::get_site_origin(), $current_user_id, $username );

		if ( 200 !== wp_remote_retrieve_response_code( $res ) ) {
			return null;
		}

		return json_decode( wp_remote_retrieve_body( $res ) )->qrCode;
	}

	/**
	 * Validates authenticate app code
	 *
	 * @return bool
	 * @since 1.0.0
	 */
	public function validate_authenticator_app_code( $code, $current_user = null ) {

		$used_code = get_transient( GoogleAuthenticatorConstants::USED_AUTH_APP_CODE );

		if ( ! empty( $used_code ) && $used_code === $code ) {
			return false;
		}

		if ( null === $current_user ) {
			$current_user = UserUtils::get_current_user();
		}

		$current_user_id = $current_user->ID;

		$res = $this->tfa_api_client->validate_qr_code( SiteUtils::get_site_origin(), $current_user_id, $code );

		if ( 204 !== wp_remote_retrieve_response_code( $res ) ) {
			return false;
		}

		set_transient( GoogleAuthenticatorConstants::USED_AUTH_APP_CODE, $code, 31 );

		$this->tfa_auth_service->add_new_2fa_auth_method( $current_user_id, GoogleAuthenticatorConstants::AUTH_ACTIVE );
		return true;
	}

	/**
	 * This function enable/disable authenticator 2 fa method.
	 * @return bool
	 */
	public function toggle_google_authenticator_app() {
		$current_status = get_option( GoogleAuthenticatorConstants::WPSEC_2FA_AUTHENTICATOR_APP );
		$status         = ToggleStatus::ENABLED;

		if ( ToggleStatus::ENABLED === $current_status ) {
			$status = ToggleStatus::DISABLED;
		}

		update_option( GoogleAuthenticatorConstants::WPSEC_2FA_AUTHENTICATOR_APP, $status );
		return ToggleStatus::ENABLED === $status;
	}

	/**
	 * This function checks if google authenticator 2 Fa method is enabled.
	 * @return bool
	 */
	public function is_google_authenticator_enabled() {
		return get_option( GoogleAuthenticatorConstants::WPSEC_2FA_AUTHENTICATOR_APP ) === ToggleStatus::ENABLED;
	}

	/**
	 *  This function disable authenticator app for current user
	 *  @param $code
	 *  @return bool
	 */
	public function disable_authenticator_app( $code ) {
		$current_user = UserUtils::get_current_user();
		$res          = $this->tfa_api_client->remove_method( SiteUtils::get_site_origin(), $current_user->ID, GoogleAuthenticatorConstants::VALIDATION_METHOD, array(), $code );

		if ( 204 === wp_remote_retrieve_response_code( $res ) ) {
			$this->tfa_auth_service->deactivate_2fa_method( $current_user->ID, GoogleAuthenticatorConstants::AUTH_ACTIVE );
			return true;
		}

		return false;
	}
}
