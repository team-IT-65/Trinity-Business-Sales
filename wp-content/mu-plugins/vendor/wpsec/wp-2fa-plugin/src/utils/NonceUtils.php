<?php

namespace Wpsec\twofa\utils;

/**
 * Nonce helper.
 *
 * @package Wpsec
 * @subpackage Wpsec/utils
 */
class NonceUtils {

	const USER_META_NONCE_KEY = 'wpsec_wp_2fa_nonce';

	/**
	 * Create the login nonce.
	 *
	 * @since 1.0.0
	 *
	 * @param int $user_id User ID.
	 * @return array|bool
	 */
	public static function create_login_nonce( $user_id ) {
		$login_nonce = array();
		try {
			$login_nonce['key'] = bin2hex( random_bytes( 32 ) );
		} catch ( \Exception $ex ) {
			$login_nonce['key'] = wp_hash( $user_id . mt_rand() . microtime(), 'nonce' );
		}
		$login_nonce['expiration'] = time() + HOUR_IN_SECONDS;

		if ( ! update_user_meta( $user_id, self::USER_META_NONCE_KEY, $login_nonce ) ) {
			return false;
		}

		return $login_nonce;
	}

	/**
	 * Verify the login nonce.
	 *
	 * @since 0.1-dev
	 *
	 * @param int    $user_id User ID.
	 * @param string $nonce Login nonce.
	 * @return bool
	 */
	public static function verify_login_nonce( $user_id, $nonce ) {
		$login_nonce = get_user_meta( $user_id, self::USER_META_NONCE_KEY, true );
		if ( ! $login_nonce ) {
			return false;
		}

		if ( $nonce !== $login_nonce['key'] || time() > $login_nonce['expiration'] ) {
			self::delete_login_nonce( $user_id );
			return false;
		}

		return true;
	}

	/**
	 * Delete the login nonce.
	 *
	 * @since 1.0.0
	 *
	 * @param int $user_id User ID.
	 * @return bool
	 */
	public static function delete_login_nonce( $user_id ) {
		return delete_user_meta( $user_id, self::USER_META_NONCE_KEY );
	}
}
