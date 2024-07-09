<?php

namespace Wptool\adminDash\services;

use Wptool\adminDash\exceptions\SiteTrafficRequestFailedException;
use Wptool\adminDash\utils\Configuration;
use WPaaS\Plugin;

class SiteTrafficDataService {

	private $api_url;

	public function __construct() {
		$this->api_url = Configuration::get( 'public_api_url' );
	}

	/**
	 * This function returns site traffic data from rum API
	 *
	 * @return array
	 * @throws SiteTrafficRequestFailedException
	 */
	public function get_site_traffic_data( $from_date, $to_date ) {
		if ( ! $GLOBALS['wpaas_feature_flag']->get_feature_flag_value( 'traffic_data_admin', false ) ) {
			return null;
		}

		$account_uid = defined( 'GD_ACCOUNT_UID' ) ? GD_ACCOUNT_UID : null;
		$response    = wp_remote_get(
			$this->api_url . '/siteTraffic/' . $account_uid . '?' . http_build_query(
				array(
					'from' => $from_date,
					'to'   => $to_date,
				)
			),
			array( 'headers' => Plugin::sign_http_request( wp_json_encode( array() ) ) )
		);

		if ( is_wp_error( $response ) ) {
			throw new SiteTrafficRequestFailedException( $response->get_error_message() );
		}

		if ( 200 !== wp_remote_retrieve_response_code( $response ) ) {
			return null;
		}

		return json_decode( wp_remote_retrieve_body( $response ) )->siteTrafficData;
	}
}
