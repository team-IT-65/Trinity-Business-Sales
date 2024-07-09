<?php

namespace Wpsec\twofa\utils;

class SiteUtils {

	/**
	 * Get website origin, giving advantage to GD_TEMP_DOMAIN constant or falling back to WP function `home_url()`
	 *
	 * @return  string Website origin
	 * @since   1.0.0
	 *
	 */
	public static function get_site_origin() {
		if ( defined( 'GD_TEMP_DOMAIN' ) && ! empty( GD_TEMP_DOMAIN ) ) {
			return GD_TEMP_DOMAIN;
		}

		return parse_url( home_url(), PHP_URL_HOST );
	}
}
