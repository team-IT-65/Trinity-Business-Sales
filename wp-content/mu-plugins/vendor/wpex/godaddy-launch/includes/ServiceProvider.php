<?php
/**
 * The ServiceProvider class.
 *
 * @package GoDaddy
 */

namespace GoDaddy\WordPress\Plugins\Launch;

/**
 * The ServiceProvider class.
 */
abstract class ServiceProvider {

	/**
	 * The application instance.
	 *
	 * @var \GoDaddy\WordPress\Plugins\Launch\Application;
	 */
	protected $app;

	/**
	 * Create a new service provider instance.
	 *
	 * @param  \GoDaddy\WordPress\Plugins\Launch\Application $app The Application.
	 */
	public function __construct( $app ) {
		$this->app = $app;
	}

	/**
	 * Register any application services.
	 */
	public function register() {}

	/**
	 * Take in raw json data from API and format into usable WP response here.
	 *
	 * Example raw response
	 * {{"headers":[],"body":"","response":{"code":true,"message":"sample",},"cookies":[],"http_response":null}}
	 *
	 * @param Array $remote_request The raw remote request data.
	 * @return \WP_Error|\WP_Rest_Response Error if invalid or typical REST response otherwise.
	 */
	protected function format_remote_post_response( $remote_request ) {

		if ( ! is_array( $remote_request ) || is_wp_error( $remote_request ) ) {
			return rest_ensure_response( $remote_request );
		}

		// Retrieve information.
		$response_code    = wp_remote_retrieve_response_code( $remote_request );
		$response_message = wp_remote_retrieve_response_message( $remote_request );
		$response_body    = wp_remote_retrieve_body( $remote_request );

		return new \WP_REST_Response(
			array(
				'status'        => $response_code,
				'response'      => $response_message,
				'body_response' => json_decode( $response_body ),
			),
			$response_code
		);
	}

	/**
	 * Function takes in $url and $body variables and performs wp_remote_post call
	 * to NUX api and return the response.
	 *
	 * @param String $url String representing the external URI of the NUX API.
	 * @param Array  $body Contains the content sent to NUX API.
	 * @return Array Raw response object from NUX API.
	 */
	protected function perform_remote_api_post( $url, $body ) {
		return wp_remote_post(
			$url,
			array(
				'headers' => array( 'Content-Type' => 'application/json' ),
				'body'    => wp_json_encode( $body ),
			)
		);
	}
}
