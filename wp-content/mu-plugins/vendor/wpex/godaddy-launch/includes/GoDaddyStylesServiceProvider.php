<?php
/**
 * The GoDaddyStylesServiceProvider class.
 *
 * @package GoDaddy
 */

namespace GoDaddy\WordPress\Plugins\Launch;

use GoDaddy\WordPress\Plugins\Launch\Dependencies\GoDaddy\Styles\StylesLoader;
use GoDaddy\WordPress\Plugins\Launch\ServiceProvider;

/**
 * GoDaddyStylesServiceProvider class.
 */
class GoDaddyStylesServiceProvider extends ServiceProvider {
	/**
	 * This method will be used for hooking into WordPress with actions/filters.
	 *
	 * @return void
	 */
	public function boot() {
		StylesLoader::getInstance()->setBasePath(
			$this->app->basePath( 'includes/Dependencies/GoDaddy/Styles/' )
		);

		StylesLoader::getInstance()->setBaseUrl(
			$this->app->baseUrl( 'includes/Dependencies/GoDaddy/Styles/' )
		);

		StylesLoader::getInstance()->boot();
	}

	/**
	 * This method will be used to bind things to the container.
	 *
	 * @return void
	 */
	public function register() {}
}
