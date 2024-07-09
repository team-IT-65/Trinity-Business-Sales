<?php

namespace Wptool\adminDash\controllers;

use Wptool\adminDash\constants\ErrorCodes;
use Wptool\adminDash\services\AutoUpdateService;
use Wptool\adminDash\services\CacheService;
use Wptool\adminDash\services\container\ServiceContainer;
use Wptool\adminDash\services\SiteHealthService;

class SiteHealthController extends BaseController {

	/** @var $site_health_service SiteHealthService */
	private $site_health_service;

	/**
	 * @param ServiceContainer $container
	 */
	public function __construct( $container ) {
		parent::__construct( $container );

		$this->site_health_service = $this->container->get( 'site_health_service' );
	}

	/**
	 * Register routes for AutoUpdates controller.
	 *
	 * @return void
	 */
	public function register_routes() {

		register_rest_route(
			$this->namespace,
			'site-health-status',
			array(
				array(
					'methods'             => 'GET',
					'callback'            => array( $this, 'site_health_status_handler' ),
					'args'                => array(),
					'permission_callback' => array( $this, 'is_authenticated' ),
				),
			)
		);

		register_rest_route(
			$this->namespace,
			'site-health-async-test',
			array(
				array(
					'methods'             => 'GET',
					'callback'            => array( $this, 'site_health_status_async_handler' ),
					'args'                => array(
						'test_name' => array(
							'description'       => 'Name of the async test.',
							'type'              => 'string',
							'required'          => true,
							'validate_callback' => function( $param, $request, $key ) {
								$valid_tests = array_keys( \WP_Site_Health::get_tests()['async'] );
								$valid_tests = array_merge( $valid_tests, array( 'authorization_header' ) );

								if ( in_array( $param, $valid_tests, true ) ) {
									return true;
								}

								return false;
							},
						),
					),
					'permission_callback' => array( $this, 'is_authenticated' ),
				),
			)
		);

		register_rest_route(
			$this->namespace,
			'site-health-info',
			array(
				array(
					'methods'             => 'GET',
					'callback'            => array( $this, 'site_health_info_handler' ),
					'args'                => array(),
					'permission_callback' => array( $this, 'is_authenticated' ),
				),
			)
		);

		register_rest_route(
			$this->namespace,
			'site-health-directory-sizes',
			array(
				array(
					'methods'             => 'GET',
					'callback'            => array( $this, 'directory_sizes_handler' ),
					'args'                => array(),
					'permission_callback' => array( $this, 'is_authenticated' ),
				),
			)
		);

		register_rest_route(
			$this->namespace,
			'site-health-async-tests',
			array(
				array(
					'methods'             => 'GET',
					'callback'            => array( $this, 'available_async_tests_handler' ),
					'args'                => array(),
					'permission_callback' => array( $this, 'is_authenticated' ),
				),
			)
		);
	}

	/**
	 * Site health status handler for sync tests.
	 *
	 * @return \WP_REST_Response
	 */
	public function site_health_status_handler() {

		$data = $this->site_health_service->get_status_tab_data_sync();

		return new \WP_REST_Response(
			array(
				'data' => $data,
			),
			200
		);
	}

	/**
	 * Site health status handler for async tests.
	 *
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_REST_Response
	 */
	public function site_health_status_async_handler( \WP_REST_Request $request ) {

		$test_name = $request->get_param( 'test_name' );

		$data = $this->site_health_service->get_status_tab_data_async( $test_name );

		if ( null === $data ) {
			return new \WP_REST_Response(
				array(
					'code'    => ErrorCodes::SERVER_ERROR,
					'message' => 'Test failed to execute.',
				),
				500
			);
		}
		return new \WP_REST_Response(
			array(
				'data' => $data,
			),
			200
		);
	}

	/**
	 * Directory siezes handler.
	 *
	 * @return \WP_REST_Response
	 */
	public function directory_sizes_handler() {

		$data = $this->site_health_service->get_directory_sizes();

		if ( null === $data ) {
			return new \WP_REST_Response(
				array(
					'code'    => ErrorCodes::SERVER_ERROR,
					'message' => 'Test failed to execute.',
				),
				500
			);
		}
		return new \WP_REST_Response(
			array(
				'data' => $data,
			),
			200
		);
	}

	/**
	 * Available async tests handler.
	 *
	 * @return \WP_REST_Response
	 */
	public function available_async_tests_handler() {

		$data = $this->site_health_service->get_available_async_tests();

		return new \WP_REST_Response(
			array(
				'data' => $data,
			),
			200
		);
	}

	/**
	 * Site health info handler.
	 *
	 * @return \WP_REST_Response
	 */
	function site_health_info_handler() {

		try {
			$data = $this->site_health_service->get_info_tab_data();
		} catch ( \ImagickException $exception ) {
			return new \WP_REST_Response(
				array(
					'code'    => ErrorCodes::SERVER_ERROR,
					'message' => 'Tests failed to execute.',
					'reason'  => $exception->getMessage(),
				),
				500
			);
		}

		return new \WP_REST_Response(
			array(
				'data' => $data,
			),
			200
		);

	}

}
