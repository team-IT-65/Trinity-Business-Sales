<?php

namespace Wpsec\twofa\Controllers;

use Exception;
use Wpsec\twofa\Constants\ErrorCodes;
use Wpsec\twofa\Services\MailAuthService;
use Wpsec\twofa\Services\TwoFactorAuthService;

class EmailTwoFactorController extends BaseController {
	/** @var $two_factor_auth_service TwoFactorAuthService */
	private $two_factor_auth_service;

	/** @var $mail_auth_service MailAuthService */
	private $mail_auth_service;

	public function __construct( $container ) {
		parent::__construct( $container );

		$this->two_factor_auth_service = $this->container->get( 'two_factor_auth_service' );
		$this->mail_auth_service       = $this->container->get( 'mail_auth_service' );
	}

	public function register_routes() {

		register_rest_route(
			$this->namespace,
			'send-verification-email',
			array(
				array(
					'methods'             => 'POST',
					'callback'            => array( $this, 'send_email_2fa_code' ),
					'permission_callback' => array( $this, 'is_authenticated' ),
				),
			)
		);
		register_rest_route(
			$this->namespace,
			'verify-email-code',
			array(
				array(
					'methods'             => 'POST',
					'callback'            => array( $this, 'verify_email_2fa_code' ),
					'permission_callback' => array( $this, 'is_authenticated' ),
					'args'                => array(
						'code' => array(
							'required'          => true,
							'description'       => 'The verification code from email.',
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
			'set-email-template',
			array(
				array(
					'methods'             => 'POST',
					'callback'            => array( $this, 'set_email_template_handler' ),
					'permission_callback' => array( $this, 'is_authenticated_administrator' ),
					'args'                => array(
						'from'    => array(
							'required'          => true,
							'description'       => 'Custom email from section.',
							'type'              => 'string',
							'validate_callback' => function( $param, $request, $key ) {
								return is_string( $param ) && is_email( $param );
							},
						),
						'subject' => array(
							'required'          => true,
							'description'       => 'Custom subject from section.',
							'type'              => 'string',
							'validate_callback' => function( $param, $request, $key ) {
								return is_string( $param );
							},
						),
						'body'    => array(
							'required'          => true,
							'description'       => 'Custom body from section.',
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
			'test-email-template',
			array(
				array(
					'methods'             => 'POST',
					'callback'            => array( $this, 'test_email_template_handler' ),
					'permission_callback' => array( $this, 'is_authenticated_administrator' ),
					'args'                => array(
						'from'    => array(
							'required'          => true,
							'description'       => 'Custom email from section.',
							'type'              => 'string',
							'validate_callback' => function( $param, $request, $key ) {
								return is_string( $param ) && is_email( $param );
							},
						),
						'subject' => array(
							'required'          => true,
							'description'       => 'Custom subject from section.',
							'type'              => 'string',
							'validate_callback' => function( $param, $request, $key ) {
								return is_string( $param );
							},
						),
						'body'    => array(
							'required'          => true,
							'description'       => 'Custom body from section.',
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
			'get-email-template',
			array(
				array(
					'methods'             => 'GET',
					'callback'            => array( $this, 'get_email_template_handler' ),
					'permission_callback' => array( $this, 'is_authenticated_administrator' ),
				),
			)
		);
	}

	/**
	 * Send email handler.
	 *
	 * @param $request \WP_REST_Request Full data about the request.
	 * @return \WP_REST_Response
	 */
	public function send_email_2fa_code( $request ) {
		if ( ! $this->two_factor_auth_service->is_2fa_enabled() ) {
			return new \WP_REST_Response(
				array(
					'code'    => ErrorCodes::FORBIDDEN,
					'message' => 'Two-Factor Auth is not enabled.',
				),
				403
			);
		}

		try {
			$result = $this->mail_auth_service->send_mail();
			return new \WP_REST_Response(
				array(
					'data' => array(
						'status' => $result,
					),
				),
				200
			);
		} catch ( Exception $e ) {
			return new \WP_REST_Response(
				array(
					'code'    => ErrorCodes::SERVER_ERROR,
					'message' => 'Failed to setup email two-factor',
				),
				500
			);
		}
	}

	/**
	 * Verify email handler.
	 *
	 * @param $request \WP_REST_Request Full data about the request.
	 * @return \WP_REST_Response
	 */
	public function verify_email_2fa_code( $request ) {
		try {
			$code   = $request->get_param( 'code' );
			$result = $this->mail_auth_service->validate_mail_auth( $code );

			return new \WP_REST_Response(
				array(
					'data' => array(
						'code_validated' => $result,
					),
				),
				200
			);
		} catch ( Exception $e ) {
			return new \WP_REST_Response(
				array(
					'code'    => ErrorCodes::SERVER_ERROR,
					'message' => 'Failed verify email code.',
				),
				500
			);
		}
	}

	/**
	 * Set custom email template handler.
	 *
	 * @param $request \WP_REST_Request Full data about the request.
	 * @return \WP_REST_Response
	 */
	public function set_email_template_handler( $request ) {
		try {
			$from    = $request->get_param( 'from' );
			$subject = $request->get_param( 'subject' );
			$body    = $request->get_param( 'body' );
			$result  = $this->mail_auth_service->set_custom_template( $from, $subject, $body );

			return new \WP_REST_Response(
				array(
					'data' => array(
						'set' => $result,
					),
				),
				200
			);
		} catch ( Exception $e ) {
			return new \WP_REST_Response(
				array(
					'code'    => ErrorCodes::SERVER_ERROR,
					'message' => 'Failed to set custom email template.',
				),
				500
			);
		}
	}

	/**
	 * Send test email handler.
	 *
	 * @param $request \WP_REST_Request Full data about the request.
	 * @return \WP_REST_Response
	 */
	public function test_email_template_handler( $request ) {
		try {
			$from    = $request->get_param( 'from' );
			$subject = $request->get_param( 'subject' );
			$body    = $request->get_param( 'body' );
			$result  = $this->mail_auth_service->send_test_email( $from, $subject, $body );

			return new \WP_REST_Response(
				array(
					'data' => array(
						'sent' => $result,
					),
				),
				200
			);
		} catch ( Exception $e ) {
			return new \WP_REST_Response(
				array(
					'code'    => ErrorCodes::SERVER_ERROR,
					'message' => 'Failed to sent test email.',
				),
				500
			);
		}
	}

	/**
	 * Get custom email template handler.
	 *
	 * @param $request \WP_REST_Request Full data about the request.
	 * @return \WP_REST_Response
	 */
	public function get_email_template_handler( $request ) {
		try {
			$result = $this->mail_auth_service->get_custom_template();

			return new \WP_REST_Response(
				array(
					'data' => $result,
				),
				200
			);
		} catch ( Exception $e ) {
			return new \WP_REST_Response(
				array(
					'code'    => ErrorCodes::SERVER_ERROR,
					'message' => 'Failed to get custom email template.',
				),
				500
			);
		}
	}

}
