<?php

namespace Wpsec\twofa\Controllers;

use Exception;
use Wpsec\twofa\Constants\ErrorCodes;
use Wpsec\twofa\Services\TwoFactorAuthService;
use Wpsec\twofa\Services\YubikeyAuthService;

class YubikeyAuthController extends BaseController {

	/** @var $two_factor_auth_service TwoFactorAuthService */
	private $two_factor_auth_service;

	/** @var $yubikey_auth_service YubikeyAuthService */
	private $yubikey_auth_service;

	public function __construct( $container ) {
		parent::__construct( $container );

		$this->two_factor_auth_service = $this->container->get( 'two_factor_auth_service' );
		$this->yubikey_auth_service    = $this->container->get( 'yubikey_auth_service' );
	}

	public function register_routes() {
		register_rest_route(
			$this->namespace,
			'yubikey-toggle',
			array(
				array(
					'methods'             => 'POST',
					'callback'            => array( $this, 'yubikey_toggle_handler' ),
					'permission_callback' => array( $this, 'is_authenticated_administrator' ),
				),
			)
		);
		register_rest_route(
			$this->namespace,
			'setup-yubikey',
			array(
				array(
					'methods'             => 'POST',
					'callback'            => array( $this, 'setup_yubikey_handler' ),
					'permission_callback' => array( $this, 'is_authenticated' ),
					'args'                => array(
						'otp' => array(
							'required'          => true,
							'description'       => 'The generated yubikey OTP.',
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
			'validate-yubikey',
			array(
				array(
					'methods'             => 'POST',
					'callback'            => array( $this, 'validate_yubikey_handler' ),
					'permission_callback' => array( $this, 'is_authenticated' ),
					'args'                => array(
						'otp' => array(
							'required'          => true,
							'description'       => 'The generated yubikey OTP.',
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
			'disable-yubikey',
			array(
				array(
					'methods'             => 'POST',
					'callback'            => array( $this, 'disable_yubikey_handler' ),
					'permission_callback' => array( $this, 'is_authenticated' ),
					'args'                => array(
						'otp' => array(
							'required'          => true,
							'description'       => 'The generated yubikey OTP.',
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
	 * Yubikey toggle handler.
	 *
	 * @param $request \WP_REST_Request Full data about the request.
	 * @return \WP_REST_Response
	 */
	public function yubikey_toggle_handler( $request ) {
		try {
			if ( ! $this->two_factor_auth_service->is_2fa_enabled() ) {
				return new \WP_REST_Response(
					array(
						'code'    => ErrorCodes::FORBIDDEN,
						'message' => 'Two-Factor Auth is not enabled.',
					),
					403
				);
			}

			$result = $this->yubikey_auth_service->toggle_google_authenticator_app();
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
					'message' => 'Failed to enable Yubikey.',
				),
				500
			);
		}
	}

	/**
	 * Setup Yubikey handler.
	 *
	 * @param $request \WP_REST_Request Full data about the request.
	 * @return \WP_REST_Response
	 */
	public function setup_yubikey_handler( $request ) {
		try {
			if ( ! $this->yubikey_auth_service->is_yubikey_enabled() ) {
				return new \WP_REST_Response(
					array(
						'code'    => ErrorCodes::FORBIDDEN,
						'message' => 'Yubikey authentication is not enabled.',
					),
					403
				);
			}

			$otp    = $request->get_params()['otp'];
			$result = $this->yubikey_auth_service->setup_yubikey( $otp );

			return new \WP_REST_Response(
				array(
					'data' => array(
						'set' => $result,
					),
				),
				200
			);
		} catch ( Exception $e ) {
			return new \WP_REST_Response(
				array(
					'code'    => ErrorCodes::SERVER_ERROR,
					'message' => 'Failed to setup yubikey.',
				),
				500
			);
		}
	}

	/**
	 * Validate yubikey OTP handler.
	 *
	 * @param $request \WP_REST_Request Full data about the request.
	 * @return \WP_REST_Response
	 */
	public function validate_yubikey_handler( $request ) {
		try {
			if ( ! $this->yubikey_auth_service->is_yubikey_enabled() ) {
				return new \WP_REST_Response(
					array(
						'code'    => ErrorCodes::FORBIDDEN,
						'message' => 'Yubikey authentication is not enabled.',
					),
					403
				);
			}

			$otp    = $request->get_param( 'otp' );
			$result = $this->yubikey_auth_service->validate_yubikey( $otp );

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
					'message' => 'Failed to validate yubikey OTP.',
				),
				500
			);
		}
	}

	/**
	 * Disable Yubikey method handler.
	 *
	 * @param $request \WP_REST_Request Full data about the request.
	 * @return \WP_REST_Response
	 */
	public function disable_yubikey_handler( $request ) {
		try {
			$otp    = $request->get_param( 'otp' );
			$result = $this->yubikey_auth_service->disable_yubikey( $otp );

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
					'message' => 'Failed to disable yubikey.',
				),
				500
			);
		}
	}
}
