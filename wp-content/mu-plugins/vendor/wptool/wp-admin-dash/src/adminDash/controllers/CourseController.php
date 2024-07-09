<?php

namespace Wptool\adminDash\controllers;

use Exception;
use Wptool\adminDash\constants\ErrorCodes;
use Wptool\adminDash\exceptions\AdminDashException;
use Wptool\adminDash\services\CacheService;
use Wptool\adminDash\services\container\ServiceContainer;
use Wptool\adminDash\services\CourseService;

class CourseController extends BaseController {


	/** @var $course_service CourseService */
	private $course_service;

	/**
	 * @param ServiceContainer $container
	 */
	public function __construct( $container ) {

		parent::__construct( $container );

		$this->course_service = $this->container->get( 'course_service' );
	}

	/**
	 * Register routes for CourseController.
	 *
	 * @return void
	 */
	public function register_routes() {
		register_rest_route(
			$this->namespace,
			'course-progress',
			array(
				array(
					'methods'             => 'GET',
					'callback'            => function () {
						return new \WP_REST_Response(
							array(
								'data' => $this->course_service->get_course_progress(),
							),
							200
						);
					},
					'permission_callback' => array( $this, 'is_authenticated' ),
				),
			)
		);
		register_rest_route(
			$this->namespace,
			'course-progress',
			array(
				array(
					'methods'             => 'POST',
					'callback'            => array( $this, 'course_progress_handler' ),
					'args'                => array(
						'video_id' => array(
							'required'    => true,
							'description' => 'Video ID of finished course',
							'type'        => 'string',
						),
					),
					'permission_callback' => array( $this, 'is_authenticated' ),
				),
			)
		);
	}

	/**
	 * Course progress handler handler function.
	 *
	 * @param \WP_REST_Request $request
	 *
	 * @return \WP_REST_Response
	 */
	public function course_progress_handler( \WP_REST_Request $request ) {

		$params = $request->get_params();

		try {

			$data = $this->course_service->update_course_progress( $params['video_id'] );
		} catch ( Exception $exception ) {
			return new \WP_REST_Response(
				array(
					'code'    => ErrorCodes::SERVER_ERROR,
					'message' => 'Course progress failed to update',
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
