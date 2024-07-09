<?php

namespace Wpsec\twofa\Core;

use Wpsec\twofa\Constants\GoogleAuthenticatorConstants;
use Wpsec\twofa\Constants\ToggleStatus;
use Wpsec\twofa\Constants\UserRole;
use Wpsec\twofa\Constants\YubikeyAuthConstants;
use Wpsec\twofa\Services\container\ServiceContainer;
use Wpsec\twofa\Services\GoogleAuthenticatorService;
use Wpsec\twofa\Services\TwoFactorAuthService;
use Wpsec\twofa\utils\NonceUtils;
use Wpsec\twofa\utils\PhpUtils;
use Wpsec\twofa\web\html\login\LoginTemplate;
use WP_User;

class TwoFAForm {

	/**
	 * Google authenticator service.
	 *
	 * @package Wpsec
	 * @subpackage Wpsec/services
	 */

	/**
	 * The config parameters for 2fa plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      array $config The config parameters for capthca plugin.
	 */
	protected $version;

	/**
	 * Keep track of all the password-based authentication sessions that
	 * need to invalidated before the second factor authentication.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	private static $password_auth_tokens = array();

	/** @var ServiceContainer */
	private $container;

	/** @var GoogleAuthenticatorService */
	private $google_service;

	/** @var TwoFactorAuthService */
	private $two_factor_service;

	/**
	 * Constructor
	 *
	 * @param string $version Version.
	 *
	 * @since 1.0.0
	 */
	public function __construct( $version, $container ) {
		$this->version   = $version;
		$this->container = $container;

		$this->google_service     = $this->container->get( 'google_auth_service' );
		$this->two_factor_service = $this->container->get( 'two_factor_auth_service' );
		/**
		 * Keep track of all the user sessions for which we need to invalidate the
		 * authentication cookies set during the initial password check.
		 */
		add_action( 'set_auth_cookie', array( $this, 'collect_auth_cookie_tokens' ) );
		add_action( 'set_logged_in_cookie', array( $this, 'collect_auth_cookie_tokens' ) );
		add_action( 'wp_login', array( $this, 'wp_login' ), 20, 2 );
	}

	/**
	 * Generate the 2FA login form URL.
	 *
	 * @param array $params List of query argument pairs to add to the URL.
	 * @param string $scheme URL scheme context.
	 *
	 * @return string
	 * @since 1.0.0
	 */
	public static function login_url( $params = array(), $scheme = 'login' ) {
		if ( ! is_array( $params ) ) {
			$params = array();
		}

		$params = urlencode_deep( $params );

		return add_query_arg( $params, site_url( 'wp-login.php', $scheme ) );
	}

	/**
	 * Shows form for checking 2fa
	 *
	 * @param WP_User $user WP user.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function show_2fa_form( $user ) {

		if ( ! $user ) {
			$user = wp_get_current_user();
		}

		$login_nonce_array = NonceUtils::create_login_nonce( $user->ID );
		if ( ! $login_nonce_array ) {
			wp_die( esc_html__( 'Failed to create a login nonce.', 'wp-2fa' ) );
		}

		$redirect_to = isset( $_REQUEST['redirect_to'] ) ? esc_url_raw( wp_unslash( $_REQUEST['redirect_to'] ) ) : admin_url();
		$login_nonce = $login_nonce_array['key'];

		$active_2fa = $this->two_factor_service->get_active_2fa_methods( $user->ID );

		$admin_active_2fa = array();

		if ( get_option( GoogleAuthenticatorConstants::WPSEC_2FA_AUTHENTICATOR_APP, ToggleStatus::DISABLED ) === ToggleStatus::ENABLED ) {
			$admin_active_2fa[] = GoogleAuthenticatorConstants::AUTH_ACTIVE;
		}

		if ( get_option( YubikeyAuthConstants::WPSEC_2FA_YUBIKEY, ToggleStatus::DISABLED ) === ToggleStatus::ENABLED ) {
			$admin_active_2fa[] = YubikeyAuthConstants::AUTH_ACTIVE;
		}

		login_header();

		$qr_callback = function( $user_id ) {
			return $this->google_service->generate_authenticator_app_qr_code( $user_id );
		};

		LoginTemplate::render( get_bloginfo( 'url' ), get_bloginfo( 'name' ), $user->ID, $login_nonce, $redirect_to, $active_2fa, $admin_active_2fa, $qr_callback );

	}

	/**
	 * Keep track of all the authentication cookies that need to be
	 * invalidated before the second factor authentication.
	 *
	 * @param string $cookie Cookie string.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function collect_auth_cookie_tokens( $cookie ) {
		$parsed = wp_parse_auth_cookie( $cookie );

		if ( ! empty( $parsed['token'] ) ) {
			self::$password_auth_tokens[] = $parsed['token'];
		}
	}

	/**
	 * Handle the browser-based login.
	 *
	 * @param string $user_login Username.
	 * @param WP_User $user WP_User object of the logged-in user.
	 *
	 * @since 1.0.0
	 *
	 */
	public function wp_login( $user_login, $user ) {
		if ( ! ( $this->enabled_2fa() && $this->is_admin( $user ) ) ) {
			return;
		}

		$this->clear_session_and_show_2fa_form( $user );
	}

	/**
	 * Checks if 2FA is enabled
	 *
	 * @return bool
	 * @since 1.0.0
	 */
	private function enabled_2fa() {
		return get_option( 'wpsec_two_fa_status', ToggleStatus::DISABLED ) === ToggleStatus::ENABLED;
	}

	/**
	 * Check if user is administrator
	 *
	 * @param $user
	 *
	 * @return bool
	 */
	private function is_admin( $user ) {
		if ( in_array( UserRole::ADMINISTRATOR, $user->roles, true ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Clears current user session and displays a "clone" of login screen with form to capture 2FA code.
	 *
	 * It also terminates current web request.
	 *
	 * @param \WP_User $user WordPress user object.
	 *
	 * @since 1.0.0
	 */
	protected function clear_session_and_show_2fa_form( $user ) {

		// Invalidate the current login session to prevent from being re-used.
		$this->destroy_current_session_for_user( $user );

		// Also clear the cookies which are no longer valid.
		wp_clear_auth_cookie();

		$this->show_2fa_form( $user );
		PhpUtils::exit_script();
	}

	/**
	 * Destroy the known password-based authentication sessions for the current user.
	 *
	 * Is there a better way of finding the current session token without
	 * having access to the authentication cookies which are just being set
	 * on the first password-based authentication request.
	 *
	 * @param WP_User $user User object.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	private function destroy_current_session_for_user( $user ) {
		$session_manager = \WP_Session_Tokens::get_instance( $user->ID );

		foreach ( self::$password_auth_tokens as $auth_token ) {
			$session_manager->destroy( $auth_token );
		}
	}


}
