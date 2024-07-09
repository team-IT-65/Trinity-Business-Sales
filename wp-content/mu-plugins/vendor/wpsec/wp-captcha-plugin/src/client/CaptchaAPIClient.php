<?php

namespace Wpsec\captcha\client;

use WP_Error;
use WPaaS\Plugin;
use Wpsec\captcha\constants\CookieConstants;
use Wpsec\captcha\utils\Logger;

class CaptchaAPIClient {

	/** @var string host */
	private $host = '';

	/** @var string events_endpoint_path */
	private $events_endpoint_path = '/api/v1/events';

	/** @var array $default_request_options */
	private $default_request_options = array(
		'body'     => '',
		'headers'  => array(
			'Content-Type' => 'application/json',
		),
		'method'   => 'POST',
		'timeout'  => 3,
		'blocking' => true,
	);

	/**
	 * Documentation for cURL errors: https://curl.se/libcurl/c/libcurl-errors.html
	 *
	 * @var array $timeout_curl_errors
	 */
	private static $timeout_curl_errors = array(
		'cURL error 6', // CURLE_COULDNT_RESOLVE_HOST
		'cURL error 7', // CURLE_COULDNT_CONNECT
		'cURL error 28', // CURLE_OPERATION_TIMEDOUT
	);

	/**
	 * Define the Captcha Stats Client.
	 *
	 * @since   1.0.0
	 */
	public function __construct( $captcha_api_host ) {
		$this->host = $captcha_api_host;
	}

	/**
	 * Sends event type to API with meta data
	 *
	 * @param   string  $event_type
	 * @param   array   $client_ips
	 * @param   array   $event_meta
	 *
	 * @return array|WP_Error
	 *
	 * @since   1.0.0
	 */
	public function send_event_type( $event_type, $client_ips, $event_meta = array() ) {
		$endpoint        = sprintf( '%s%s', $this->host, $this->events_endpoint_path );
		$options         = $this->default_request_options;
		$cp_challenge    = isset( $_COOKIE[ CookieConstants::CP_CHALLENGE ] ) ? $_COOKIE[ CookieConstants::CP_CHALLENGE ] : '';
		$options['body'] = wp_json_encode(
			array(
				'event_type'   => $event_type,
				'ips'          => $client_ips,
				'event_meta'   => $event_meta,
				'cp_challenge' => $cp_challenge,
			)
		);

		$signed_request_headers = Plugin::sign_http_request( $options['body'] );

		if ( empty( $signed_request_headers ) ) {
			return array( 'response' => array( 'code' => 504 ) );
		}

		$options['headers'] = array_merge( $options['headers'], $signed_request_headers );

		$response = wp_remote_post( $endpoint, $options );

		if ( $this->is_timeout( $response ) ) {
			return array( 'response' => array( 'code' => 504 ) );
		}

		return $response;
	}

	/**
	 * Check if request response is timeout
	 * Since WordPress does not have any built in functionality, we are checking this manually
	 *
	 * @param   object $response
	 *
	 * @return  bool
	 * @since   1.0.0
	 *
	 */
	private function is_timeout( $response ) {
		if ( ! is_wp_error( $response ) || ! isset( $response->errors['http_request_failed'] ) ) {
			return false;
		}

		$response_error = substr( $response->errors['http_request_failed'][0], 0, 13 );

		foreach ( self::$timeout_curl_errors as $curl_error ) {
			if ( false !== strpos( $response_error, $curl_error ) ) {
				Logger::log(
					'Request Timeout',
					array(
						'captcha_id' => isset( $_POST['wpsec_captcha_id'] ) ? $_POST['wpsec_captcha_id'] : '',
						'error'      => $curl_error,
					)
				);
				return true;
			}
		}

		return false;
	}
}
