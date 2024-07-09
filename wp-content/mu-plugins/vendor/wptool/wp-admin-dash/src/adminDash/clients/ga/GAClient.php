<?php

namespace Wptool\adminDash\clients\ga;

use Wptool\adminDash\utils\Configuration;

class GAClient {

	/** @var string  */
	private $url;

	/** @var string  */
	private $measurement_id;

	/** @var string */
	private $api_secret;

	public function __construct() {

		$this->url            = Configuration::get( 'ga.url' );
		$this->measurement_id = Configuration::get( 'ga.measurement_id' );
		$this->api_secret     = Configuration::get( 'ga.api_secret' );
	}

	/**
	 * Sends request to GA API.
	 *
	 * @param GARequest $request
	 *
	 * @return bool
	 */
	public function send( $request ) {

		$this->url = add_query_arg(
			array(
				'measurement_id' => $this->measurement_id,
				'api_secret'     => $this->api_secret,
			),
			$this->url
		);

		$args = array(
			'body'     => json_encode( $request->get_request_data() ),
			'method'   => 'POST',
			'blocking' => false,
			'headers'  => array(
				'Content-Type' => 'application/json',
			),
		);

		$response = wp_remote_post( $this->url, $args );

		if ( $response instanceof \WP_Error ) {
			return false;
		}

		return true;

	}
}
