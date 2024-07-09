<?php

namespace Wpsec\twofa\Controllers;

use Wpsec\twofa\Constants\ErrorCodes;
use Wpsec\twofa\Constants\GoogleAuthenticatorConstants;
use Wpsec\twofa\Constants\MailAuthConstants;
use Wpsec\twofa\Constants\UserConstants;
use Exception;
use Wpsec\twofa\Constants\YubikeyAuthConstants;
use Wpsec\twofa\Services\UserService;
use Wpsec\twofa\utils\UserUtils;

class UserController extends BaseController {

	/** @var $user_service UserService */
	private $user_service;

	public function __construct( $container ) {
		parent::__construct( $container );
		$this->user_service = $this->container->get( 'user_service' );
	}

	public function register_routes() {
		register_rest_route(
			$this->namespace,
			'user-filter',
			array(
				array(
					'methods'             => 'GET',
					'callback'            => array( $this, 'filter_users_handler' ),
					'permission_callback' => array( $this, 'is_authenticated_administrator' ),
					'args'                => array(
						'filter_by' => array(
							'required'          => true,
							'description'       => 'Filter criteria.',
							'type'              => 'string',
							'validate_callback' => function( $param, $request, $key ) {
								if ( UserConstants::FILTER_BY_ALL !== $param &&
									UserConstants::FILTER_BY_2FA_NOT_SET_UP !== $param &&
									UserConstants::FILTER_BY_2FA_SET_UP !== $param ) {

									return false;
								}

								return true;
							},
						),
						'roles'     => array(
							'required'          => false,
							'description'       => 'User roles',
							'type'              => 'array',
							'validate_callback' => function( $param, $request, $key ) {
								return is_array( $param );
							},
						),
					),
				),
			)
		);
		register_rest_route(
			$this->namespace,
			'bulk-action',
			array(
				array(
					'methods'             => 'POST',
					'callback'            => array( $this, 'bulk_actions_handler' ),
					'permission_callback' => array( $this, 'is_authenticated_administrator' ),
					'args'                => array(
						'action'              => array(
							'required'          => true,
							'description'       => 'Action admin want to perform',
							'type'              => 'string',
							'validate_callback' => function( $param, $request, $key ) {
								if ( UserConstants::BULK_ACTION_RESTART_2FA !== $param ) {
									return false;
								}

								return true;
							},
						),
						'users'               => array(
							'required'          => true,
							'description'       => 'Users on whom the action will be performed',
							'type'              => 'array',
							'validate_callback' => function( $param, $request, $key ) {
								return is_array( $param );
							},
						),
						'verification_method' => array(
							'required'          => true,
							'description'       => 'Method chosen by admin to verify identity.',
							'type'              => 'string',
							'validate_callback' => function( $param, $request, $key ) {
								if ( GoogleAuthenticatorConstants::VALIDATION_METHOD !== $param &&
									YubikeyAuthConstants::VALIDATION_METHOD !== $param &&
									MailAuthConstants::VALIDATION_METHOD !== $param
								) {
									return false;
								}
								return true;
							},
						),
						'code'                => array(
							'required'          => true,
							'description'       => 'Two-factor code to verify.',
							'type'              => 'string',
							'validate_callback' => function( $param, $request, $key ) {
								return is_string( $param );
							},
						),
					),
				),
			)
		);
		register_rest_route(
			$this->namespace,
			'user-search',
			array(
				array(
					'methods'             => 'GET',
					'callback'            => array( $this, 'user_search_handler' ),
					'permission_callback' => array( $this, 'is_authenticated_administrator' ),
					'args'                => array(
						'username' => array(
							'required'          => true,
							'description'       => 'Action admin want to perform',
							'type'              => 'string',
							'validate_callback' => function( $param, $request, $key ) {
								return is_string( $param );
							},
						),
						'roles'    => array(
							'required'          => false,
							'description'       => 'User roles',
							'type'              => 'array',
							'validate_callback' => function( $param, $request, $key ) {
								return is_array( $param );
							},
						),
					),
				),
			)
		);
		register_rest_route(
			$this->namespace,
			'user-roles',
			array(
				array(
					'methods'             => 'GET',
					'callback'            => array( $this, 'get_user_roles_handler' ),
					'permission_callback' => array( $this, 'is_authenticated_administrator' ),
				),
			)
		);
		register_rest_route(
			$this->namespace,
			'user-roles',
			array(
				array(
					'methods'             => 'PUT',
					'callback'            => array( $this, 'updated_selected_roles_handler' ),
					'permission_callback' => array( $this, 'is_authenticated_administrator' ),
					'args'                => array(
						'selected_roles' => array(
							'required'          => true,
							'description'       => 'User roles that are forced to use Two-Factor Auth.',
							'type'              => 'array',
							'validate_callback' => function( $param, $request, $key ) {
								return is_array( $param );
							},
						),
					),
				),
			)
		);
		register_rest_route(
			$this->namespace,
			'user-info',
			array(
				array(
					'methods'             => 'GET',
					'callback'            => array( $this, 'user_info_handler' ),
					'permission_callback' => array( $this, 'is_authenticated' ),
				),
			)
		);
	}

	/**
	 * Filter users handler.
	 *
	 * @param $request \WP_REST_Request Full data about the request.
	 * @return \WP_REST_Response
	 */
	public function filter_users_handler( $request ) {
		try {
			$filter_by = $request->get_param( 'filter_by' );
			$roles     = $request->get_param( 'roles' ) ? $request->get_param( 'roles' ) : array();
			$users     = $this->user_service->filter_users( $filter_by, $roles );

			return new \WP_REST_Response(
				array(
					'data' => array(
						'users' => $users,
					),
				),
				200
			);

		} catch ( Exception $e ) {
			return new \WP_REST_Response(
				array(
					'code'    => ErrorCodes::SERVER_ERROR,
					'message' => 'Failed to filter users.',
				),
				500
			);
		}
	}

	/**
	 * Bulk actions handler.
	 *
	 * @param $request \WP_REST_Request Full data about the request.
	 * @return \WP_REST_Response
	 */
	public function bulk_actions_handler( $request ) {
		try {
			$action              = $request->get_param( 'action' );
			$users               = $request->get_param( 'users' );
			$verification_method = $request->get_param( 'verification_method' );
			$code                = $request->get_param( 'code' );
			$result              = $this->user_service->bulk_action( $action, $users, $verification_method, $code );

			return new \WP_REST_Response(
				array(
					'data' => array(
						'success' => $result,
					),
				),
				200
			);
		} catch ( Exception $e ) {
			return new \WP_REST_Response(
				array(
					'code'    => ErrorCodes::SERVER_ERROR,
					'message' => 'Failed perform bulk action.',
				),
				500
			);
		}
	}

	/**
	 * USer search handler.
	 *
	 * @param $request \WP_REST_Request Full data about the request.
	 * @return \WP_REST_Response
	 */
	public function user_search_handler( $request ) {
		try {
			$username = $request->get_param( 'username' );
			$roles    = $request->get_param( 'roles' ) ? $request->get_param( 'roles' ) : array();
			$user     = $this->user_service->search_by_username( $username, $roles );

			return new \WP_REST_Response(
				array(
					'data' => array(
						'user' => $user,
					),
				),
				200
			);
		} catch ( Exception $e ) {
			return new \WP_REST_Response(
				array(
					'code'    => ErrorCodes::SERVER_ERROR,
					'message' => 'Failed perform user search.',
				),
				500
			);
		}
	}

	/**
	 * Get user roles handler.
	 *
	 * @param $request \WP_REST_Request Full data about the request.
	 * @return \WP_REST_Response
	 */
	public function get_user_roles_handler( $request ) {
		try {
			$roles = $this->user_service->get_roles();

			return new \WP_REST_Response(
				array(
					'data' => array(
						'roles' => $roles,
					),
				),
				200
			);
		} catch ( Exception $e ) {
			return new \WP_REST_Response(
				array(
					'code'    => ErrorCodes::SERVER_ERROR,
					'message' => 'Failed retrieve roles.',
				),
				500
			);
		}
	}

	/**
	 * Update user roles handler.
	 *
	 * @param $request \WP_REST_Request Full data about the request.
	 * @return \WP_REST_Response
	 */
	public function updated_selected_roles_handler( $request ) {
		try {
			$selected_roles = $request->get_param( 'selected_roles' );
			$result         = $this->user_service->select_roles( $selected_roles );

			return new \WP_REST_Response(
				array(
					'data' => array(
						'success' => $result,
					),
				),
				200
			);
		} catch ( Exception $e ) {
			return new \WP_REST_Response(
				array(
					'code'    => ErrorCodes::SERVER_ERROR,
					'message' => 'Failed update selected roles.',
				),
				500
			);
		}
	}

	/**
	 * User info handler.
	 *
	 * @param $request \WP_REST_Request Full data about the request.
	 * @return \WP_REST_Response
	 */
	public function user_info_handler( $request ) {
		try {
			$user   = UserUtils::get_current_user();
			$result = $this->user_service->transform_one( $user );

			return new \WP_REST_Response(
				array(
					'data' => array(
						'user' => $result,
					),
				),
				200
			);
		} catch ( Exception $e ) {
			return new \WP_REST_Response(
				array(
					'code'    => ErrorCodes::SERVER_ERROR,
					'message' => 'Failed to retrieve user info.',
				),
				500
			);
		}
	}
}

