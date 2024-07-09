<?php

namespace Wptool\adminDash\controllers;

use Wptool\adminDash\constants\ErrorCodes;
use Wptool\adminDash\constants\MinificationConstants;
use Wptool\adminDash\services\MinificationService;
use Wptool\adminDash\services\SiteTrafficDataService;
use Wptool\adminDash\services\container\ServiceContainer;

class MinificationController extends BaseController {
	/** @var $minification_service MinificationService */
	private $minification_service;

	/**
	 * @param ServiceContainer $container
	 */
	public function __construct( $container ) {

		parent::__construct( $container );

		$this->minification_service = $this->container->get( 'minification_service' );
	}

	public function register_routes() {
		register_rest_route(
			$this->namespace,
			'minification',
			array(
				array(
					'methods'             => 'PUT',
					'callback'            => array( $this, 'minify' ),
					'permission_callback' => array( $this, 'is_authenticated_administrator' ),
				),
			)
		);

	}

	public function minify( $request ) {

		$type = $request->get_param( 'type' );

		try {
			$result = $this->minification_service->set_minified_flags( $type );

			if ( MinificationConstants::ERROR === $result['status'] ) {
				return new \WP_REST_Response(
					array(
						'code'    => ErrorCodes::SERVER_ERROR,
						'message' => 'Failed to update minification type.',
					),
					500
				);
			}

			return new \WP_REST_Response(
				array(
					'data' => $result,
				),
				200
			);

		} catch ( \Exception $e ) {
			return new \WP_REST_Response(
				array(
					'code'    => ErrorCodes::SERVER_ERROR,
					'message' => 'Failed to update minification type.',
				),
				500
			);
		}
	}



}
