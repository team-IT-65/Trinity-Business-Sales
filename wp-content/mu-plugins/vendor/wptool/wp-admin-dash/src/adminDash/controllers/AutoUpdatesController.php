<?php

namespace Wptool\adminDash\controllers;

use Wptool\adminDash\services\AutoUpdateService;
use Wptool\adminDash\constants\ErrorCodes;
use Wptool\adminDash\services\container\ServiceContainer;

class AutoUpdatesController extends BaseController {

	/** @var $auto_updates_service AutoUpdateService */
	private $auto_updates_service;

	/**
	 * @param ServiceContainer $container
	 */
	public function __construct( $container ) {

		parent::__construct( $container );

		$this->auto_updates_service = $this->container->get( 'auto_updates_service' );
	}

	/**
	 * Register routes for AutoUpdates controller.
	 *
	 * @return void
	 */
	public function register_routes() {

		register_rest_route(
			$this->namespace,
			'auto-updates',
			array(
				array(
					'methods'             => 'PUT',
					'callback'            => array( $this, 'toggle_auto_update_handler' ),
					'args'                => array(),
					'permission_callback' => array( $this, 'is_authenticated' ),
				),
			)
		);
	}

	/**
	 * Toggle auto updates handler.
	 *
	 * @return \WP_REST_Response | \WP_Error
	 */
	public function toggle_auto_update_handler() {

		$result = $this->auto_updates_service->toggle_auto_update();

		if ( null !== $result ) {
			return new \WP_REST_Response(
				array(
					'data' => array(
						'mwp_auto_updates_status_enabled' => $result,
					),
				),
				200
			);
		}

		return new \WP_REST_Response(
			array(
				'code'    => ErrorCodes::SERVER_ERROR,
				'message' => 'Auto-update status failed to update.',
			),
			500
		);

	}

}
