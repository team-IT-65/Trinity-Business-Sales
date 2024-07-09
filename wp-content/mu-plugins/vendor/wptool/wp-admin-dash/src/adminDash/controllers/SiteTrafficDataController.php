<?php

namespace Wptool\adminDash\controllers;

use Wptool\adminDash\constants\ErrorCodes;
use Wptool\adminDash\services\SiteTrafficDataService;
use Wptool\adminDash\services\container\ServiceContainer;

class SiteTrafficDataController extends BaseController {
	/** @var $site_traffic_service SiteTrafficDataService */
	private $site_traffic_service;

	/**
	 * @param ServiceContainer $container
	 */
	public function __construct( $container ) {

		parent::__construct( $container );

		$this->site_traffic_service = $this->container->get( 'site_traffic_data_service' );
	}

	public function register_routes() {
		register_rest_route(
			$this->namespace,
			'site-traffic',
			array(
				array(
					'methods'             => 'GET',
					'callback'            => array( $this, 'site_traffic_data' ),
					'permission_callback' => array( $this, 'is_authenticated' ),
				),
			)
		);
	}

	public function site_traffic_data( $request ) {

		$from = $request->get_param( 'from' );
		$to   = $request->get_param( 'to' );
		try {
			$result = $this->site_traffic_service->get_site_traffic_data( $from, $to );

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
					'message' => 'Failed to fetch site traffic data.',
				),
				500
			);
		}
	}
}
