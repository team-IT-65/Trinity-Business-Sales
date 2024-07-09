<?php

namespace Wpsec\twofa\API;

use WP_Error;
use WPaaS\Plugin;

class TwoFactorApiClient {

	/** @var string host */
	private $host = '';

	/** @var string qr_endpoint_path */
	private $qr_code_endpoint_path = '/api/v1/twoFactors/authenticatorApps/';

	/** @var string otp_endpoint_path */
	private $otp_endpoint_path = '/api/v1/twoFactors/yubikeys/';

	/** @var string email_endpoint_path */
	private $email_endpoint_path = '/api/v1/twoFactors/emails/';

	/** @var string remove_method_endpoint_path */
	private $remove_endpoint_path = '/api/v1/twoFactors/removeMethod/';

	/** @var array $host_per_env */
	private $host_per_env = array(
		'local'    => 'http://host.docker.internal:8080',
		'dev'      => 'https://wp-api.wpsecurity.dev-godaddy.com',
		'test'     => 'https://wp-api.wpsecurity.test-godaddy.com',
		'myh.test' => 'https://wp-api.wpsecurity.test-godaddy.com',
		'prod'     => 'https://wp-api.wpsecurity.godaddy.com',
	);

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
		'cURL error 6', // CURL_COULDNT_RESOLVE_HOST
		'cURL error 7', // CURL_COULDNT_CONNECT
		'cURL error 28', // CURL_OPERATION_TIMEDOUT
	);

	/**
	 * Define the Two Factor Api Client.
	 *
	 * @since   1.0.0
	 */
	public function __construct() {
		$current_env = class_exists( '\WPaaS\Plugin' ) ? Plugin::get_env() : 'prod';

		if ( ! array_key_exists( $current_env, $this->host_per_env ) ) {
			$current_env = 'prod';
		}

		$this->host = $this->host_per_env[ $current_env ];
	}

	/**
	 * Generate QR code API request
	 *
	 * @param   string $origin
	 * @param   integer $user_id
	 * @param   string $username
	 *
	 * @return array|WP_Error
	 *
	 * @since   1.0.0
	 */
	public function generate_qr_code( $origin, $user_id, $username ) {
		$endpoint        = sprintf( '%s%s%s', $this->host, $this->qr_code_endpoint_path, 'generateCode' );
		$options         = $this->default_request_options;
		$options['body'] = wp_json_encode(
			array(
				'origin'   => $origin,
				'userId'   => $user_id,
				'username' => $username,
			)
		);

		return $this->sign_and_send_request( $options, $endpoint );
	}

	/**
	 * Validate QR code api request
	 *
	 * @param   string $origin
	 * @param   integer $user_id
	 * @param   string $code
	 *
	 * @return array|WP_Error
	 *
	 * @since   1.0.0
	 */
	public function validate_qr_code( $origin, $user_id, $code ) {
		$endpoint        = sprintf( '%s%s%s', $this->host, $this->qr_code_endpoint_path, 'verify' );
		$options         = $this->default_request_options;
		$options['body'] = wp_json_encode(
			array(
				'origin' => $origin,
				'userId' => $user_id,
				'code'   => $code,
			)
		);

		return $this->sign_and_send_request( $options, $endpoint );
	}

	/**
	 * Save yubikey code API request
	 *
	 * @param   string $origin
	 * @param   integer $user_id
	 * @param   string $otp
	 *
	 * @return array|WP_Error
	 *
	 * @since   1.0.0
	 */
	public function save_otp_code( $origin, $user_id, $otp ) {
		$endpoint = sprintf( '%s%s', $this->host, $this->otp_endpoint_path );

		$options         = $this->default_request_options;
		$options['body'] = wp_json_encode(
			array(
				'origin'      => $origin,
				'userId'      => $user_id,
				'yubikeyCode' => $otp,
			)
		);
		return $this->sign_and_send_request( $options, $endpoint );
	}

	/**
	 * Validate yubikey code API request
	 *
	 * @param   string $origin
	 * @param   integer $user_id
	 * @param   string $otp
	 *
	 * @return array|WP_Error
	 *
	 * @since   1.0.0
	 */
	public function validate_otp_code( $origin, $user_id, $otp ) {
		$endpoint = sprintf( '%s%s%s', $this->host, $this->otp_endpoint_path, 'verify' );

		$options         = $this->default_request_options;
		$options['body'] = wp_json_encode(
			array(
				'origin'      => $origin,
				'userId'      => $user_id,
				'yubikeyCode' => $otp,
			)
		);

		return $this->sign_and_send_request( $options, $endpoint );
	}

	/**
		 * Send email verification code to API with metadata
		 *
		 * @param   string $origin
		 * @param   integer $user_id
		 * @param   string $secret
		 *
		 * @return array|WP_Error
		 *
		 * @since   1.0.0
		 */
	public function save_mail_code( $origin, $user_id, $secret ) {
		$endpoint        = sprintf( '%s%s', $this->host, $this->email_endpoint_path );
		$options         = $this->default_request_options;
		$options['body'] = wp_json_encode(
			array(
				'origin'    => $origin,
				'userId'    => $user_id,
				'emailCode' => $secret,
			)
		);

		return $this->sign_and_send_request( $options, $endpoint );
	}

	/**
	 * Remove Two Factor method API request
	 *
	 * @param   string $origin
	 * @param   integer $user_id
	 * @param   string $method_name
	 * @param   integer[] $user_ids
	 * @param   string $code
	 *
	 * @return array|WP_Error
	 *
	 * @since   1.0.0
	 */
	public function remove_method( $origin, $user_id, $method_name, $user_ids, $code ) {
		$endpoint        = sprintf( '%s%s', $this->host, $this->remove_endpoint_path );
		$options         = $this->default_request_options;
		$options['body'] = wp_json_encode(
			array(
				'origin'  => $origin,
				'adminId' => $user_id,
				'method'  => $method_name,
				'code'    => $code,
				'userIds' => $user_ids,
			)
		);

		return $this->sign_and_send_request( $options, $endpoint );
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
				return true;
			}
		}

		return false;
	}

	/**
	 * Signs API request with needed headers
	 * @param array $options
	 * @param string $endpoint
	 *
	 * @return array|WP_Error
	 */
	private function sign_and_send_request( $options, $endpoint ) {
		$signed_request_headers = Plugin::sign_http_request( $options['body'] );

		if ( empty( $signed_request_headers ) ) {
			return array( 'response' => array( 'code' => 504 ) );
		}

		$options['headers'] = array_merge( $options['headers'], $signed_request_headers );
		$response           = wp_remote_post( $endpoint, $options );

		if ( $this->is_timeout( $response ) ) {
			return array( 'response' => array( 'code' => 504 ) );
		}

		return $response;
	}
}
