<?php

namespace Wpsec\twofa\utils;

/**
 * Request helper.
 *
 * @package Wpsec
 * @subpackage Wpsec/utils
 */
class RequestUtils {


	/**
	 * Checks if POST parameter exist and is it empty
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public static function check_post_param( $param ) {
		return array_key_exists( $param, $_POST ) && ! empty( $_POST[ $param ] );
	}
}
