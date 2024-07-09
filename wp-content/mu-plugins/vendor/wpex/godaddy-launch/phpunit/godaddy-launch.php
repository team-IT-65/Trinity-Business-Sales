<?php
/**
 * Tests the plugin bootstrap file.
 *
 * @package GoDaddy_Launch
 */

namespace GoDaddy\WordPress\Plugins\Launch\Tests;

use GoDaddy\WordPress\Plugins\Launch\Application;

use function GoDaddy\WordPress\Plugins\Launch\gdl;

/**
 * Tests the plugin bootstrap file.
 */
class Bootstrap_Test extends \WP_UnitTestCase {

	/**
	 * Test that the plugin has been successfully loaded into the test suite.
	 */
	public function test_gdl_loaded() {
		$this->assertTrue( class_exists( Application::class ) );
	}

	/**
	 * Test that the gdl() helper function returns an instance of the Container.
	 */
	public function test_gdl_helper_returns_container_instance() {
		$container_instance = gdl();
		$this->assertTrue( $container_instance instanceof Application );
	}
}
