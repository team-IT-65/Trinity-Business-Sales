<?php

namespace Wptool\adminDash\controllers;

use Exception;
use Wptool\adminDash\constants\ErrorCodes;
use Wptool\adminDash\services\container\ServiceContainer;
use Wptool\adminDash\services\GDLoginService;

class GDLoginController extends BaseController {

	/** @var $onboarding_service GDLoginService */
	private $gd_login_service;

	/**
	 * @param ServiceContainer $container
	 */
	public function __construct( $container ) {

		parent::__construct( $container );

		$this->gd_login_service = $this->container->get( 'gd_login_service' );
	}

	public function register_routes() {
		register_rest_route(
			$this->namespace,
			'toggle-gd-login',
			array(
				array(
					'methods'             => 'PUT',
					'callback'            => array( $this, 'login_handler' ),
					'permission_callback' => array( $this, 'is_authenticated_administrator' ),
				),
			)
		);
	}

	public function login_handler() {
		try {
			$result = $this->gd_login_service->toggle_gd_login();

			return new \WP_REST_Response(
				array(
					'data' => array(
						'wpsec_gd_login' => $result,
					),
				),
				200
			);
		} catch ( Exception $e ) {
			return new \WP_REST_Response(
				array(
					'code'    => ErrorCodes::SERVER_ERROR,
					'message' => 'Login with GoDaddy failed to update.',
				),
				500
			);
		}
	}
}
