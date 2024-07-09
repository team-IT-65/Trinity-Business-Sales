<?php

namespace Wptool\adminDash\services;

use Wptool\adminDash\constants\MinificationConstants;
use Wptool\adminDash\controllers\MinificationController;
use Wptool\adminDash\utils\Configuration;
use WPaaS\Plugin;
use Wptool\adminDash\utils\PlansMapping;

class MinificationService {

	private $api_url;

	public function __construct() {
		$this->api_url = Configuration::get( 'public_api_url' );
	}

	/**
	 * This function returns minified flag values.
	 *
	 * @return array
	 */
	public function get_minified_flags() {

		$account_uid = defined( 'GD_ACCOUNT_UID' ) ? GD_ACCOUNT_UID : null;
		$response    = wp_remote_get(
			$this->api_url . '/cdn/' . $account_uid . '/minification',
			array( 'headers' => Plugin::sign_http_request( wp_json_encode( array() ) ) )
		);

		if ( is_wp_error( $response ) ) {
			return array(
				'status' => MinificationConstants::ERROR,
				'data'   => null,
			);
		}

		if ( 200 !== wp_remote_retrieve_response_code( $response ) ) {
			return array(
				'status' => MinificationConstants::ERROR,
				'data'   => null,
			);
		}

		return array(
			'status' => MinificationConstants::SUCCESS,
			'data'   => json_decode( wp_remote_retrieve_body( $response ) ),
		);
	}

	/**
	 * This function returns minified flag values.
	 * If Account does not have any minified flags set, response is:
	 * [
	 *  'status' => 'success',
	 *  'data' => [
	 *    'js' => false,
	 *    'css' => false,
	 *    'html' => false
	 *   ]
	 * ]
	 *
	 * @return array
	 */
	public function set_minified_flags( $type ) {

		$account_uid = defined( 'GD_ACCOUNT_UID' ) ? GD_ACCOUNT_UID : null;

		$response = wp_remote_request(
			$this->api_url . '/cdn/' . $account_uid . '/minification',
			array(
				'headers' => array_merge( array( 'Content-Type' => 'application/json' ), Plugin::sign_http_request( wp_json_encode( array() ) ) ),
				'method'  => 'PUT',
				'timeout' => 30,
				'body'    => json_encode(
					array(
						'type' => $type,
					)
				),
			)
		);

		if ( is_wp_error( $response ) ) {
			return array(
				'status' => MinificationConstants::ERROR,
				'data'   => null,
			);
		}

		if ( 200 !== wp_remote_retrieve_response_code( $response ) ) {
			return array(
				'status' => MinificationConstants::ERROR,
				'data'   => null,
			);
		}

		return array(
			'status' => MinificationConstants::SUCCESS,
			'data'   => json_decode( wp_remote_retrieve_body( $response ) ),
		);
	}

	/**
	 * This function returns account plan.
	 *
	 * @return string|null
	 */
	public function get_account_plan() {

		if ( defined( 'GD_PLAN_NAME' ) ) {
			return PlansMapping::get_plan_type( GD_PLAN_NAME );
		}

		return null;
	}
}
