<?php

namespace Wpsec\twofa\Config;


use Wpsec\twofa\API\TwoFactorApiClient;
use Wpsec\twofa\Services\GoogleAuthenticatorService;
use Wpsec\twofa\Services\LoginService;
use Wpsec\twofa\Services\MailAuthService;
use Wpsec\twofa\Services\TwoFactorAuthService;
use Wpsec\twofa\Services\UserService;
use Wpsec\twofa\Services\YubikeyAuthService;

class Services {

	public static function get_services() {

		$services = array();

		$services['two_factor_auth_service'] = function () {
			return new TwoFactorAuthService();
		};

		$services['two_factor_api_client'] = function () {
			return new TwoFactorApiClient();
		};

		$services['mail_auth_service'] = function ( $container ) {
			return new MailAuthService( $container['two_factor_auth_service'], $container['two_factor_api_client'] );
		};

		$services['google_auth_service'] = function ( $container ) {
			return new GoogleAuthenticatorService( $container['two_factor_auth_service'], $container['two_factor_api_client'] );
		};

		$services['yubikey_auth_service'] = function ( $container ) {
			return new YubikeyAuthService( $container['two_factor_auth_service'], $container['two_factor_api_client'] );
		};

		$services['user_service'] = function ( $container ) {
			return new UserService( $container['two_factor_auth_service'], $container['two_factor_api_client'] );
		};

		$services['login_service'] = function( $container ) {
			return new LoginService(
				$container['yubikey_auth_service'],
				$container['google_auth_service'],
				$container['mail_auth_service'],
				$container['two_factor_auth_service'],
				WPSEC_WP_2FA_VERSION
			);
		};

		return $services;
	}
}
