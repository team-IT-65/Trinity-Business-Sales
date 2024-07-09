<?php

namespace Wpsec\twofa\Services;

use Wpsec\twofa\utils\NonceUtils;
use Wpsec\twofa\utils\RequestUtils;

/**
 * Wpsec 2FA Ajax provider.
 *
 * @package Wpsec
 * @subpackage Wpsec/Services
 */
class AjaxProvider {

	/**
	 * Registers all ajax actions
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public static function register_ajax_actions( $container ) {
		add_action( 'wp_ajax_nopriv_wpsec_2fa_send_email', array( $container->get( 'mail_auth_service' ), 'send_mail_ajax' ), PHP_INT_MAX, 0 );
		add_action( 'wp_ajax_nopriv_wpsec_2fa_verify_login_mail_code', array( $container->get( 'login_service' ), 'login_form_validate_2fa' ), PHP_INT_MAX, 0 );
	}
}
