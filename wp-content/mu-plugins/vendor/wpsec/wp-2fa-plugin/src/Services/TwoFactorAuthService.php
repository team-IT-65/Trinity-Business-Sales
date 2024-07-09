<?php

namespace Wpsec\twofa\Services;

use Wpsec\twofa\Constants\ToggleStatus;
use Wpsec\twofa\Constants\TwoFactorConstants;

class TwoFactorAuthService {

	/**
	 *  Enable 2-Factor Auth for site
	 * @return bool
	 */
	public function toggle_2fa() {
		$two_fa = get_option( 'wpsec_two_fa_status' );
		$status = ToggleStatus::ENABLED;

		if ( ToggleStatus::ENABLED === $two_fa ) {
			$status = ToggleStatus::DISABLED;
		}

		update_option( 'wpsec_two_fa_status', $status );

		return ToggleStatus::ENABLED === $status;
	}

	/**
	 * Check if 2-Factor Auth enabled
	 * If not set, we will assume it's disabled
	 * @return bool
	 */
	public function is_2fa_enabled() {
		return get_option( 'wpsec_two_fa_status', ToggleStatus::DISABLED ) === ToggleStatus::ENABLED;
	}

	/**
	 * Activates given 2fa method for given user.
	 *
	 * @param int $user_id user ID.
	 * @param string $method_to_activate Method to activate.
	 * @return boolean Is new method enabled.
	 * @since 1.0.0
	 */
	public function add_new_2fa_auth_method( $user_id, $method_to_activate ) {
		if ( ! $this->is_2fa_enabled() ) {
			return false;
		}

		$available_2fa_methods = get_user_meta( $user_id, TwoFactorConstants::WPSEC_2FA_ACTIVE, true );
		if ( empty( $available_2fa_methods ) ) {
			$available_2fa_methods = array();
		} else {
			$available_2fa_methods = json_decode( $available_2fa_methods, true );
		}

		if ( ! in_array( $method_to_activate, $available_2fa_methods, true ) ) {
			$available_2fa_methods[] = $method_to_activate;
			update_user_meta( $user_id, TwoFactorConstants::WPSEC_2FA_ACTIVE, json_encode( $available_2fa_methods ) );

			return true;
		}

		return false;
	}

	/**
	 * Gets array of active 2fa methods.
	 *
	 * @param int $user_id user ID.
	 * @return array
	 * @since 1.0.0
	 */
	public function get_active_2fa_methods( $user_id ) {
		$provider = get_user_meta( $user_id, TwoFactorConstants::WPSEC_2FA_ACTIVE, true );
		if ( empty( $provider ) ) {
			return array();
		}

		return json_decode( $provider, true );
	}

	/**
	 * Activates given 2fa method for given user.
	 *
	 * @param int $user_id Current user.
	 * @param string $method_to_deactivate Method to deactivate.
	 * @return bool
	 * @since 1.0.0
	 */
	public function deactivate_2fa_method( $user_id, $method_to_deactivate ) {
		$available_2fa_methods = get_user_meta( $user_id, TwoFactorConstants::WPSEC_2FA_ACTIVE, true );
		$available_2fa_methods = json_decode( $available_2fa_methods, true );

		if ( in_array( $method_to_deactivate, $available_2fa_methods, true ) ) {
			$index = array_search( $method_to_deactivate, $available_2fa_methods, true );
			unset( $available_2fa_methods[ $index ] );

			update_user_meta( $user_id, TwoFactorConstants::WPSEC_2FA_ACTIVE, json_encode( $available_2fa_methods ) );
			return true;
		}

		return false;
	}

	public function deactivate_all_methods( $user_id ) {
		update_user_meta( $user_id, TwoFactorConstants::WPSEC_2FA_ACTIVE, json_encode( array() ) );
	}
}
