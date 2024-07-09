<?php

namespace Wptool\adminDash\config\environments;

class ConfigProd extends Config {

	/**
	 * Gets configuration array for environment.
	 *
	 * @return array
	 */
	static function get_config() {
		return array(
			'support_api_url' => 'https://api.mwc.secureserver.net/v1',
			'ga'              => array(
				'url'            => 'https://www.google-analytics.com/mp/collect',
				'measurement_id' => '',
				'api_secret'     => '',
			),
			'public_api_url'  => 'https://wp-api.wpsecurity.godaddy.com/api/v1',
		);
	}
}
