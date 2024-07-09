<?php

namespace Wpsec\twofa\Controllers;

use Exception;
use Wpsec\twofa\Constants\ErrorCodes;
use Wpsec\twofa\Services\GoogleAuthenticatorService;
use Wpsec\twofa\Services\TwoFactorAuthService;
use Wpsec\twofa\Services\YubikeyAuthService;

class TwoFactorAuthController extends BaseController {

	/** @var $two_factor_auth_service TwoFactorAuthService */
	private $two_factor_auth_service;

	/**
	 * GoogleAuthenticatorService instance.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      GoogleAuthenticatorService $google_auth_service.
	 */
	private $google_auth_service;

	/** @var $yubikey_auth_service YubikeyAuthService */
	private $yubikey_auth_service;

	public function __construct( $container ) {
		parent::__construct( $container );

		$this->two_factor_auth_service = $this->container->get( 'two_factor_auth_service' );
		$this->google_auth_service     = $this->container->get( 'google_auth_service' );
		$this->yubikey_auth_service    = $this->container->get( 'yubikey_auth_service' );
	}

	public function register_routes() {
		register_rest_route(
			$this->namespace,
			'toggle',
			array(
				array(
					'methods'             => 'PUT',
					'callback'            => array( $this, 'two_factor_auth_toggle_handler' ),
					'permission_callback' => array( $this, 'is_authenticated_administrator' ),
				),
			)
		);
		register_rest_route(
			$this->namespace,
			'status',
			array(
				array(
					'methods'             => 'GET',
					'callback'            => array( $this, 'get_two_factor_status' ),
					'permission_callback' => array( $this, 'is_authenticated_administrator' ),
				),
			)
		);
		register_rest_route(
			$this->namespace,
			'methods-status',
			array(
				array(
					'methods'             => 'GET',
					'callback'            => array( $this, 'get_two_factor_methods_status' ),
					'permission_callback' => array( $this, 'is_authenticated_administrator' ),
				),
			)
		);
	}

	/**
	 * Two-Factor toggle handler.
	 *
	 * @param $request \WP_REST_Request Full data about the request.
	 * @return \WP_REST_Response
	 */
	public function two_factor_auth_toggle_handler( $request ) {
		try {
			$result = $this->two_factor_auth_service->toggle_2fa();

			return new \WP_REST_Response(
				array(
					'data' => array(
						'wpsec_two_fa_status' => $result,
					),
				),
				200
			);
		} catch ( Exception $e ) {
			return new \WP_REST_Response(
				array(
					'code'    => ErrorCodes::SERVER_ERROR,
					'message' => 'Two-Factor Auth failed to update.',
				),
				500
			);
		}
	}

	/**
	 * Get two-factor status handler.
	 *
	 * @param $request \WP_REST_Request Full data about the request.
	 * @return \WP_REST_Response
	 */
	public function get_two_factor_status( $request ) {
		try {
			$result = $this->two_factor_auth_service->is_2fa_enabled();

			return new \WP_REST_Response(
				array(
					'data' => array(
						'wpsec_two_fa_status' => $result,
					),
				),
				200
			);
		} catch ( Exception $e ) {
			return new \WP_REST_Response(
				array(
					'code'    => ErrorCodes::SERVER_ERROR,
					'message' => 'Failed to get Two-Factor Auth status.',
				),
				500
			);
		}
	}

	/**
	 * Get status od all two-factor method.
	 *
	 * @param $request \WP_REST_Request Full data about the request.
	 * @return \WP_REST_Response
	 */
	public function get_two_factor_methods_status( $request ) {
		try {
			return new \WP_REST_Response(
				array(
					'data' => array(
						'authenticator_app' => $this->google_auth_service->is_google_authenticator_enabled(),
						'yubikey'           => $this->yubikey_auth_service->is_yubikey_enabled(),
					),
				),
				200
			);
		} catch ( Exception $e ) {
			return new \WP_REST_Response(
				array(
					'code'    => ErrorCodes::SERVER_ERROR,
					'message' => 'Failed to get Two-Factor Auth status.',
				),
				500
			);
		}
	}
}
