<?php

namespace Wpsec\twofa\utils;

use WP_User;

/**
 * User utils.
 *
 * @package Wpsec
 * @subpackage Wpsec/utils
 */
class UserUtils {
	/**
	 * Gets current user
	 *
	 * @return WP_User|null
	 * @since 1.0.0
	 */
	public static function get_current_user() {
		$current_user = wp_get_current_user();
		if ( ! $current_user ) {
			return null;
		}

		return $current_user;
	}
}
