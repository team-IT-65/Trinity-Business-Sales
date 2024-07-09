<?php

namespace Wpsec\twofa\Controllers;

use Exception;
use Wpsec\twofa\Constants\ErrorCodes;
use Wpsec\twofa\Services\GoogleAuthenticatorService;
use Wpsec\twofa\Services\TwoFactorAuthService;

class GoogleAuthenticatorAppController extends BaseController {

	/** @var $google_auth_service GoogleAuthenticatorService */
	private $google_auth_service;

	/** @var $two_factor_auth_service TwoFactorAuthService */
	private $two_factor_auth_service;

	public function __construct( $container ) {
		parent::__construct( $container );

		$this->two_factor_auth_service = $this->container->get( 'two_factor_auth_service' );
		$this->google_auth_service     = $this->container->get( 'google_auth_service' );
	}

	public function register_routes() {
		register_rest_route(
			$this->namespace,
			'google-authenticator-toggle',
			array(
				array(
					'methods'             => 'POST',
					'callback'            => array( $this, 'google_authenticator_toggle_handler' ),
					'permission_callback' => array( $this, 'is_authenticated_administrator' ),
				),
			)
		);
		register_rest_route(
			$this->namespace,
			'generate-app-code',
			array(
				array(
					'methods'             => 'GET',
					'callback'            => array( $this, 'generate_google_auth_code' ),
					'permission_callback' => array( $this, 'is_authenticated' ),
				),
			)
		);
		register_rest_route(
			$this->namespace,
			'validate-app-code',
			array(
				array(
					'methods'             => 'POST',
					'callback'            => array( $this, 'validate_google_auth_code' ),
					'permission_callback' => array( $this, 'is_authenticated' ),
					'args'                => array(
						'code' => array(
							'required'          => true,
							'description'       => 'The validation code from google authenticator app.',
							'type'              => 'string',
							'validate_callback' => function( $param, $request, $key ) {
								return is_string( $param );
							},
						),
					),
				),
			)
		);
		register_rest_route(
			$this->namespace,
			'disable-authenticator-app',
			array(
				array(
					'methods'             => 'POST',
					'callback'            => array( $this, 'disable_authenticator_app' ),
					'permission_callback' => array( $this, 'is_authenticated' ),
					'args'                => array(
						'code' => array(
							'required'          => true,
							'description'       => 'The validation code from google authenticator app.',
							'type'              => 'string',
							'validate_callback' => function( $param, $request, $key ) {
								return is_string( $param );
							},
						),
					),
				),
			)
		);
	}

	/**
	 * Google authenticator toggle handler.
	 *
	 * @param $request \WP_REST_Request Full data about the request.
	 * @return \WP_REST_Response
	 */
	public function google_authenticator_toggle_handler( $request ) {
		if ( ! $this->two_factor_auth_service->is_2fa_enabled() ) {
			return new \WP_REST_Response(
				array(
					'code'    => ErrorCodes::FORBIDDEN,
					'message' => 'Two-Factor Auth is not enabled.',
				),
				403
			);
		}

		try {
			$result = $this->google_auth_service->toggle_google_authenticator_app();
			return new \WP_REST_Response(
				array(
					'data' => array(
						'active' => $result,
					),
				),
				200
			);
		} catch ( Exception $e ) {
			return new \WP_REST_Response(
				array(
					'code'    => ErrorCodes::SERVER_ERROR,
					'message' => 'Failed to enable authenticator app.',
				),
				500
			);
		}
	}

	/**
	 * Generate google code handler.
	 *
	 * @param $request \WP_REST_Request Full data about the request.
	 * @return \WP_REST_Response
	 */
	public function generate_google_auth_code( $request ) {

		if ( ! $this->google_auth_service->is_google_authenticator_enabled() ) {
			return new \WP_REST_Response(
				array(
					'code'    => ErrorCodes::FORBIDDEN,
					'message' => 'Google authenticator is not enabled.',
				),
				403
			);
		}

		try {
			$qr_code = $this->google_auth_service->generate_authenticator_app_qr_code();
			return new \WP_REST_Response(
				array(
					'data' => array(
						'qr_code' => $qr_code,
					),
				),
				200
			);
		} catch ( Exception $e ) {
			return new \WP_REST_Response(
				array(
					'code'    => ErrorCodes::SERVER_ERROR,
					'message' => 'Failed to generate QR code.',
				),
				500
			);
		}
	}

	/**
	 * Validate google authenticator code.
	 *
	 * @param $request \WP_REST_Request Full data about the request.
	 * @return \WP_REST_Response
	 */
	public function validate_google_auth_code( $request ) {
		try {
			$code   = $request->get_param( 'code' );
			$result = $this->google_auth_service->validate_authenticator_app_code( $code );

			return new \WP_REST_Response(
				array(
					'data' => array(
						'code_validated' => $result,
					),
				),
				200
			);
		} catch ( Exception $e ) {
			return new \WP_REST_Response(
				array(
					'code'    => ErrorCodes::SERVER_ERROR,
					'message' => 'Failed validate app code.',
				),
				500
			);
		}
	}


	/**
	 * Disable google authenticator auth method.
	 *
	 * @param $request \WP_REST_Request Full data about the request.
	 * @return \WP_REST_Response
	 */
	public function disable_authenticator_app( $request ) {
		try {
			$code   = $request->get_param( 'code' );
			$result = $this->google_auth_service->disable_authenticator_app( $code );
			return new \WP_REST_Response(
				array(
					'data' => array(
						'disabled' => $result,
					),
				),
				200
			);
		} catch ( Exception $e ) {
			return new \WP_REST_Response(
				array(
					'code'    => ErrorCodes::SERVER_ERROR,
					'message' => 'Failed disable authenticator app.',
				),
				500
			);
		}
	}
}
