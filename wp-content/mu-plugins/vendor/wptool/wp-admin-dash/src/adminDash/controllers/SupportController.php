<?php

namespace Wptool\adminDash\controllers;

use Wptool\adminDash\constants\ErrorCodes;
use Wptool\adminDash\exceptions\AdminDashException;
use Wptool\adminDash\services\CacheService;
use Wptool\adminDash\services\container\ServiceContainer;
use Wptool\adminDash\services\SupportService;

class SupportController extends BaseController {

	/** @var $support_service SupportService */
	private $support_service;

	/**
	 * @param ServiceContainer $container
	 */
	public function __construct( $container ) {

		parent::__construct( $container );

		$this->support_service = $this->container->get( 'support_service' );
	}

	/**
	 * Register routes for SupportController.
	 *
	 * @return void
	 */
	public function register_routes() {
		register_rest_route(
			$this->namespace,
			'support-request',
			array(
				array(
					'methods'             => 'POST',
					'callback'            => array( $this, 'contact_form_handler' ),
					'args'                => array(
						'reply_to' => array(
							'required'          => true,
							'description'       => 'The e-mail address the support team will reply to',
							'type'              => 'string',
							'sanitize_callback' => 'sanitize_email',
							'validate_callback' => function( $param, $request, $key ) {
								return is_email( $param );
							},
						),
						'subject'  => array(
							'required'    => true,
							'description' => 'The subject',
							'type'        => 'string',
						),
						'message'  => array(
							'required'    => true,
							'description' => 'The message',
							'type'        => 'string',
						),
						'reason'   => array(
							'required'    => true,
							'description' => 'The reason field',
							'type'        => 'string',
						),
					),
					'permission_callback' => array( $this, 'is_authenticated' ),
				),
			)
		);
	}

	/**
	 * Contact form handler function.
	 *
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_REST_Response
	 */
	public function contact_form_handler( \WP_REST_Request $request ) {

		$params = $request->get_params();

		try {

			$data = $this->support_service->send_support_request( $params );

		} catch ( AdminDashException $exception ) {
				return new \WP_REST_Response(
					array(
						'code'    => ErrorCodes::SERVER_ERROR,
						'message' => 'Support request failed to create.',
						'detail'  => array(
							'message' => $exception->getMessage(),
							'reason'  => $exception->getReason(),
						),
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
