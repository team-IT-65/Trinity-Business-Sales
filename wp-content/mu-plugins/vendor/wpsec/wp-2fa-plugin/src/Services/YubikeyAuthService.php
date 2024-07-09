<?php

namespace Wpsec\twofa\Services;


use \Exception;
use Wpsec\twofa\API\TwoFactorApiClient;
use Wpsec\twofa\Constants\ToggleStatus;
use Wpsec\twofa\Constants\YubikeyAuthConstants;
use Wpsec\twofa\utils\SiteUtils;
use Wpsec\twofa\utils\UserUtils;

class YubikeyAuthService {
	/**
	 * TwoFactorAuthService instance .
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      TwoFactorApiClient $tfa_api_client.
	 */
	private $tfa_api_client;

	/**
	 * TwoFactorAuthService instance .
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      TwoFactorAuthService $tfa_auth_service.
	 */
	private $tfa_auth_service;

	public function __construct( $tfa_auth_service, $tfa_api_client ) {
		$this->tfa_api_client   = $tfa_api_client;
		$this->tfa_auth_service = $tfa_auth_service;
	}

	public function toggle_google_authenticator_app() {
		$current_status = get_option( YubikeyAuthConstants::WPSEC_2FA_YUBIKEY );
		$status         = ToggleStatus::ENABLED;

		if ( ToggleStatus::ENABLED === $current_status ) {
			$status = ToggleStatus::DISABLED;
		}

		update_option( YubikeyAuthConstants::WPSEC_2FA_YUBIKEY, $status );
		return ToggleStatus::ENABLED === $status;
	}

	/**
	 * Check if yubikey authentication method is enabled.
	 * @return bool
	 */
	public function is_yubikey_enabled() {
		return get_option( YubikeyAuthConstants::WPSEC_2FA_YUBIKEY ) === ToggleStatus::ENABLED;
	}

	/**
	 * Validate and save user OTP for further verification.
	 * @param $code
	 * @return bool
	 */
	public function setup_yubikey( $otp, $current_user = null ) {
		try {
			if ( null === $current_user ) {
				$current_user = UserUtils::get_current_user();
			}

			$res = $this->tfa_api_client->save_otp_code( SiteUtils::get_site_origin(), $current_user->ID, $otp );

			if ( 204 === wp_remote_retrieve_response_code( $res ) ) {
				$this->tfa_auth_service->add_new_2fa_auth_method( $current_user->ID, YubikeyAuthConstants::AUTH_ACTIVE );
				return true;
			}

			return false;
		} catch ( Exception $e ) {
			return false;
		}
	}

	/**
	 * Validate user OTP.
	 * @param $otp
	 * @return bool
	 */
	public function validate_yubikey( $otp, $current_user = null ) {
		try {
			if ( null === $current_user ) {
				$current_user = UserUtils::get_current_user();
			}

			$active_methods = $this->tfa_auth_service->get_active_2fa_methods( $current_user->ID );

			if ( ! in_array( YubikeyAuthConstants::AUTH_ACTIVE, $active_methods, true ) ) {
				return false;
			}

			$res = $this->tfa_api_client->validate_otp_code( SiteUtils::get_site_origin(), $current_user->ID, $otp );

			return 204 === wp_remote_retrieve_response_code( $res );

		} catch ( Exception $e ) {
			return false;
		}
	}

	/**
	 *  This function disable yubikey for current user.
	 *  @param $code
	 *  @return bool
	 */
	public function disable_yubikey( $otp ) {
		$current_user = UserUtils::get_current_user();
		$res          = $this->tfa_api_client->remove_method( SiteUtils::get_site_origin(), $current_user->ID, YubikeyAuthConstants::VALIDATION_METHOD, array(), $otp );

		if ( 204 === wp_remote_retrieve_response_code( $res ) ) {
			$this->tfa_auth_service->deactivate_2fa_method( $current_user->ID, YubikeyAuthConstants::AUTH_ACTIVE );
			return true;
		}

		return false;
	}
}
