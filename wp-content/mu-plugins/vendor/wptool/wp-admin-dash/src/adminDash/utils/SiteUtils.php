<?php

namespace Wptool\adminDash\utils;

class SiteUtils {

	public static function get_site_title() {
		return empty( get_bloginfo( 'name' ) ) ? str_replace( array( 'http://', 'http://www.', 'www.' ), '', get_bloginfo( 'wpurl' ) ) : get_bloginfo( 'name' );
	}
}
