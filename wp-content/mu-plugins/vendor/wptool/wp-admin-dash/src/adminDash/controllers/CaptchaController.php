<?php

namespace Wptool\adminDash\controllers;

use Exception;
use Wptool\adminDash\exceptions\CaptchaDisabledException;
use Wptool\adminDash\services\CaptchaService;
use Wptool\adminDash\services\container\ServiceContainer;
use Wptool\adminDash\constants\ErrorCodes;

class CaptchaController extends BaseController {

	/** @var $captcha_service CaptchaService */
	private $captcha_service;

	/**
	 * @param ServiceContainer $container
	 */
	public function __construct( $container ) {

		parent::__construct( $container );

		$this->captcha_service = $this->container->get( 'captcha_service' );
	}

	public function register_routes() {
		register_rest_route(
			$this->namespace,
			'captcha-toggle',
			array(
				array(
					'methods'             => 'PUT',
					'callback'            => array( $this, 'captcha_toggle_handler' ),
					'permission_callback' => array( $this, 'is_authenticated' ),
				),
			)
		);

		register_rest_route(
			$this->namespace,
			'captcha-comment-toggle',
			array(
				array(
					'methods'             => 'PUT',
					'callback'            => array( $this, 'captcha_comment_toggle_handler' ),
					'permission_callback' => array( $this, 'is_authenticated' ),
				),
			)
		);

		register_rest_route(
			$this->namespace,
			'captcha-login-toggle',
			array(
				array(
					'methods'             => 'PUT',
					'callback'            => array( $this, 'captcha_login_toggle_handler' ),
					'permission_callback' => array( $this, 'is_authenticated' ),
				),
			)
		);
	}

	public function captcha_toggle_handler( $request ) {
		try {
			$result = $this->captcha_service->toggle_wpsec_captcha();

			return new \WP_REST_Response(
				array(
					'data' => array(
						'wpsec_captcha_enabled' => $result,
					),
				),
				200
			);
		} catch ( Exception $e ) {
			return new \WP_REST_Response(
				array(
					'code'    => ErrorCodes::SERVER_ERROR,
					'message' => 'Captcha failed to update.',
				),
				500
			);
		}
	}

	public function captcha_comment_toggle_handler( $request ) {
		try {
			$result = $this->captcha_service->toggle_wpsec_captcha_comment();

			return new \WP_REST_Response(
				array(
					'data' => array(
						'wpsec_captcha_comment_enabled' => $result,
					),
				),
				200
			);
		} catch ( CaptchaDisabledException $e ) {
			return new \WP_REST_Response(
				array(
					'code'    => ErrorCodes::UNABLE_TO_PERFORM_OPERATION,
					'message' => $e->getReason(),
				),
				400
			);
		} catch ( Exception $e ) {
			return new \WP_REST_Response(
				array(
					'code'    => ErrorCodes::SERVER_ERROR,
					'message' => 'Captcha comment toggle failed to update.',
				),
				500
			);
		}
	}

	public function captcha_login_toggle_handler( $request ) {
		try {
			$result = $this->captcha_service->toggle_wpsec_captcha_login();

			return new \WP_REST_Response(
				array(
					'data' => array(
						'wpsec_captcha_login_enabled' => $result,
					),
				),
				200
			);
		} catch ( CaptchaDisabledException $e ) {
			return new \WP_REST_Response(
				array(
					'code'    => ErrorCodes::UNABLE_TO_PERFORM_OPERATION,
					'message' => $e->getReason(),
				),
				400
			);
		} catch ( Exception $e ) {
			return new \WP_REST_Response(
				array(
					'code'    => ErrorCodes::SERVER_ERROR,
					'message' => 'Captcha login toggle failed to update.',
				),
				500
			);
		}
	}
}
