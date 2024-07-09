<?php

namespace Wpsec\twofa\Core;

use Wpsec\twofa\Config\Services;
use Wpsec\twofa\Controllers\EmailTwoFactorController;
use Wpsec\twofa\Controllers\GoogleAuthenticatorAppController;
use Wpsec\twofa\Controllers\TwoFactorAuthController;
use Wpsec\twofa\Controllers\UserController;
use Wpsec\twofa\Controllers\YubikeyAuthController;
use Wpsec\twofa\Services\AjaxProvider;
use Wpsec\twofa\Services\container\ServiceContainer;

class  WPJsonAPI {

	/**
	 * Register all controllers routes.
	 *
	 * @return ServiceContainer
	 */
	public static function boot() {

		$container = self::boot_container();

		foreach ( self::registered_controllers( $container ) as $controller ) {

			if ( $controller instanceof \WP_REST_Controller ) {
				add_action( 'rest_api_init', array( $controller, 'register_routes' ) );
			}
		}

		return $container;
	}

	/**
	 * Return array of all registered controllers.
	 *
	 * @return array
	 */
	private static function registered_controllers( $container ) {

		return array(
			new TwoFactorAuthController( $container ),
			new GoogleAuthenticatorAppController( $container ),
			new EmailTwoFactorController( $container ),
			new YubikeyAuthController( $container ),
			new UserController( $container ),
		);
	}

	/**
	 * Booting service container, with defined services in config/service.php.
	 *
	 * @return ServiceContainer
	 */
	private static function boot_container() {

		$services = Services::get_services();

		$container = new ServiceContainer( $services );

		do_action( '2fa_container_booted', $container );

		return $container;
	}
}
