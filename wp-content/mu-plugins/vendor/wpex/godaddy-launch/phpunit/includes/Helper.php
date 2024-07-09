<?php
/**
 * Tests for Helper.
 *
 * @package GoDaddy_Launch
 */

namespace GoDaddy\WordPress\Plugins\Launch\Tests;

use GoDaddy\WordPress\Plugins\Launch\Helper;

/**
 * Tests for Helper.
 */
class Helper_Test extends \WP_UnitTestCase {
	/**
	 * Ensure option is converted to timestamp when saved as boolean or string literal of true.
	 */
	public function test_update_option_convert_true_to_timestamp() {
		$this->assertEquals( time(), Helper::update_option_convert_true_to_timestamp( true ) );
		$this->assertEquals( time(), Helper::update_option_convert_true_to_timestamp( 'true' ) );
		$this->assertEquals( 'skipped', Helper::update_option_convert_true_to_timestamp( 'skipped' ) );
		$this->assertEquals( '', Helper::update_option_convert_true_to_timestamp( '' ) );
	}

	/**
	 * Ensure option is retrieved as skipped or boolean as string.
	 */
	public function test_get_skipped_or_boolean_as_string() {
		$this->assertEquals( 'skipped', Helper::get_skipped_or_boolean_as_string( 'skipped' ) );
		$this->assertEquals( '1', Helper::get_skipped_or_boolean_as_string( 'true' ) );
		$this->assertEquals( '', Helper::get_skipped_or_boolean_as_string( '' ) );
	}

	/**
	 * Ensuer option is retrieved as boolean.
	 */
	public function test_get_option_convert_timestamp_to_true() {
		$this->assertEquals( true, Helper::get_option_convert_timestamp_to_true( time() ) );
		$this->assertEquals( true, Helper::get_option_convert_timestamp_to_true( 'skipped' ) );
		$this->assertEquals( false, Helper::get_option_convert_timestamp_to_true( '' ) );
	}
}
