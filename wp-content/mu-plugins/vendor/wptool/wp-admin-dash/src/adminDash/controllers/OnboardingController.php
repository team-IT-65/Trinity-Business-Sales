<?php

namespace Wptool\adminDash\controllers;

use Exception;
use Wptool\adminDash\constants\ErrorCodes;
use Wptool\adminDash\services\container\ServiceContainer;
use Wptool\adminDash\services\OnboardingService;

class OnboardingController extends BaseController {

	/** @var $onboarding_service OnboardingService */
	private $onboarding_service;

	/**
	 * @param ServiceContainer $container
	 */
	public function __construct( $container ) {

		parent::__construct( $container );

		$this->onboarding_service = $this->container->get( 'onboarding_service' );
	}

	public function register_routes() {
		register_rest_route(
			$this->namespace,
			'onboarding',
			array(
				array(
					'methods'             => 'PUT',
					'callback'            => array( $this, 'onboarding_handler' ),
					'permission_callback' => array( $this, 'is_authenticated' ),
				),
			)
		);
	}

	public function onboarding_handler() {
		try {
			$result = $this->onboarding_service->complete_onboarding();

			return new \WP_REST_Response(
				array(
					'data' => array(
						'wpsec_user_onboarded' => $result,
					),
				),
				200
			);
		} catch ( Exception $e ) {
			return new \WP_REST_Response(
				array(
					'code'    => ErrorCodes::SERVER_ERROR,
					'message' => 'Onboarding failed to update.',
				),
				500
			);
		}
	}
}
