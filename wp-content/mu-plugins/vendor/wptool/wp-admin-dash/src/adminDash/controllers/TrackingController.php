<?php

namespace Wptool\adminDash\controllers;

use Wptool\adminDash\constants\ErrorCodes;
use Wptool\adminDash\constants\TrackingConstants;
use Wptool\adminDash\services\container\ServiceContainer;
use Wptool\adminDash\services\TrackingService;

class TrackingController extends BaseController {

	/** @var TrackingService */
	private $tracking_service;

	/**
	 * @param ServiceContainer $container
	 */
	public function __construct( $container ) {

		parent::__construct( $container );

		$this->tracking_service = $this->container->get( 'tracking_service' );
	}

	public function register_routes() {
		register_rest_route(
			$this->namespace,
			'load',
			array(
				array(
					'methods'             => 'POST',
					'callback'            => array( $this, 'tracking_handler' ),
					'args'                => array(
						'page'    => array(
							'required'          => true,
							'description'       => 'Current user page.',
							'type'              => 'string',
							'validate_callback' => function( $param, $request, $key ) {
								return is_string( $param );
							},
						),
						'type'    => array(
							'required'          => true,
							'description'       => 'Metric type pageview or click.',
							'type'              => 'string',
							'validate_callback' => function( $param, $request, $key ) {
								return in_array( $param, TrackingService::TYPES, true );
							},
						),
						'section' => array(
							'description' => 'User interacted section on website. Exists only for type Event.',
							'type'        => 'string',
						),
					),
					'permission_callback' => array( $this, 'is_authenticated' ),
				),
			)
		);
	}

	/**
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_REST_Response
	 */
	public function tracking_handler( $request ) {

		try {
			$data   = $request->get_params();
			$status = $this->tracking_service->process_load_data( $data );
		} catch ( \Exception $exception ) {
			return new \WP_REST_Response(
				array(
					'code'    => ErrorCodes::SERVER_ERROR,
					'message' => 'Failed to process.',
					'reason'  => $exception->getMessage(),
				),
				500
			);
		}

		if ( ! $status ) {
			return new \WP_REST_Response(
				array(
					'code'   => ErrorCodes::UNABLE_TO_PERFORM_OPERATION,
					'status' => false,
				),
				400
			);
		}

		return new \WP_REST_Response(
			array(
				'status' => true,
			),
			200
		);

	}
}
