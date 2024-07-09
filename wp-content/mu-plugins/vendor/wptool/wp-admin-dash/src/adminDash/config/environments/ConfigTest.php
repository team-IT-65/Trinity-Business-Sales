<?php

namespace Wptool\adminDash\config\environments;

class ConfigTest extends Config {

	/**
	 * Gets configuration array for environment.
	 *
	 * @return array
	 */
	static function get_config() {
		return array(
			'support_api_url' => 'https://api-dev.mwc.secureserver.net/v1',
			'ga'              => array(
				'url'            => 'https://www.google-analytics.com/mp/collect',
				'measurement_id' => 'G-8P206XBG4B',
				'api_secret'     => 'Vte7UP_QS1CKMXlrpu5pBQ',
			),
			'public_api_url'  => 'https://wp-api.wpsecurity.test-godaddy.com/api/v1',
		);
	}
}
