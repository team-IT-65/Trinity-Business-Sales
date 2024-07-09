<?php

namespace Wptool\adminDash\core;

use Wptool\adminDash\config\Services;
use Wptool\adminDash\controllers\AutoUpdatesController;
use Wptool\adminDash\controllers\CaptchaController;
use Wptool\adminDash\controllers\CourseController;
use Wptool\adminDash\controllers\GDLoginController;
use Wptool\adminDash\controllers\GlobalInfoController;
use Wptool\adminDash\controllers\MinificationController;
use Wptool\adminDash\controllers\OnboardingController;
use Wptool\adminDash\controllers\SiteTrafficDataController;
use Wptool\adminDash\controllers\SupportController;
use Wptool\adminDash\controllers\TrackingController;
use Wptool\adminDash\services\container\ServiceContainer;
use Wptool\adminDash\controllers\SiteHealthController;
use Wptool\adminDash\utils\Configuration;

class WPJsonAPI {

	/**
	 * Register all controllers routes.
	 *
	 * @return void
	 */
	public static function boot() {

		foreach ( self::registered_controllers() as $controller ) {

			if ( $controller instanceof \WP_REST_Controller ) {
				add_action( 'rest_api_init', array( $controller, 'register_routes' ) );
			}
		}
	}

	/**
	 * Return array of all registered controllers.
	 *
	 * @return array
	 */
	private static function registered_controllers() {

		$container = self::boot_container();

		return array(
			new AutoUpdatesController( $container ),
			new GlobalInfoController( $container ),
			new SiteHealthController( $container ),
			new SupportController( $container ),
			new CourseController( $container ),
			new CaptchaController( $container ),
			new TrackingController( $container ),
			new OnboardingController( $container ),
			new GDLoginController( $container ),
			new SiteTrafficDataController( $container ),
			new MinificationController( $container ),
		);
	}

	/**
	 * Booting service container, with defined services in config/service.php.
	 *
	 * @return ServiceContainer
	 */
	private static function boot_container() {

		Configuration::initialize();

		$services = Services::get_services();

		return new ServiceContainer( $services );
	}
}
