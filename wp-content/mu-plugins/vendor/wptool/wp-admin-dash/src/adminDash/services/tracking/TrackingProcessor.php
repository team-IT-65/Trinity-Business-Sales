<?php

namespace Wptool\adminDash\services\tracking;

abstract class TrackingProcessor {

	abstract public function process( $data);

	public function genereate_client_id() {

		$temp_domain = defined( 'GD_TEMP_DOMAIN' ) ? GD_TEMP_DOMAIN : get_site_url();

		return md5( $temp_domain . get_current_user_id() );
	}
}
