<?php
/**
 * Tests for PublishGuide\GuideItems\SiteDesign.
 *
 * @package GoDaddy_Launch
 */

namespace GoDaddy\WordPress\Plugins\Launch\Tests;

use GoDaddy\WordPress\Plugins\Launch\PublishGuide\GuideItems\SiteDesign;

/**
 * Tests for PublishGuide\GuideItems\SiteDesign.
 */
class SiteDesign_Test extends \WP_UnitTestCase {
	/**
	 * Instance SiteDesign to test.
	 *
	 * @var SiteDesign
	 */
	protected $instance;

	/**
	 * Default theme_mods that may be applied by WordPress.
	 *
	 * @var array
	 */
	protected $default_theme_mods = array(
		0                    => false,
		'nav_menu_locations' => array(),
		'custom_css_post_id' => -1,
	);

	/**
	 * Setup each test.
	 */
	public function set_up() {
		parent::set_up();

		$this->instance = new SiteDesign();
		switch_theme( 'go' );
		update_option( 'coblocks_site_design_controls_enabled', true );

		foreach ( $this->default_theme_mods as $name => $value ) {
			set_theme_mod( $name, $value );
		}
	}

	/**
	 * Test that SiteDesign is enabled by default.
	 */
	public function test_is_enabled_by_default() {
		$this->assertTrue( get_option( 'coblocks_site_design_controls_enabled' ) );
		$this->assertTrue( $this->instance->is_enabled() );
	}

	/**
	 * Test that SiteDesign requires Go theme to be active.
	 */
	public function test_is_enabled_requires_go_theme() {
		$this->assertEquals( 'go', wp_get_theme()->stylesheet );
		$this->assertTrue( $this->instance->is_enabled() );

		switch_theme( 'not-go' );

		$this->assertNotEquals( 'go', wp_get_theme()->stylesheet );
		$this->assertFalse( $this->instance->is_enabled() );
	}

	/**
	 * Test that SiteDesign is disabed if feature in CoBlocks is disabled.
	 */
	public function test_is_enabled_requires_coblocks_option_enabled() {
		$this->assertTrue( get_option( 'coblocks_site_design_controls_enabled' ) );
		$this->assertTrue( $this->instance->is_enabled() );

		delete_option( 'coblocks_site_design_controls_enabled' );

		$this->assertFalse( get_option( 'coblocks_site_design_controls_enabled' ) );
		$this->assertFalse( $this->instance->is_enabled() );
	}

	/**
	 * Test that SiteDesign is not completed on a fresh WordPress install.
	 */
	public function test_not_is_complete_on_fresh_install() {
		remove_theme_mods();
		$this->assertFalse( $this->instance->is_complete() );
	}

	/**
	 * Test that automatically added theme_mods upon switching a theme do not trigger is_complete.
	 */
	public function test_not_is_complete_with_default_theme_mods() {
		$this->assertEquals( $this->default_theme_mods, get_theme_mods() );
		$this->assertFalse( $this->instance->is_complete() );

		set_theme_mod( 'newMod', 'newValue' );
		$this->assertEquals(
			array_merge( $this->default_theme_mods, array( 'newMod' => 'newValue' ) ),
			get_theme_mods()
		);
		$this->assertTrue( $this->instance->is_complete() );
	}

	/**
	 * Test that SiteDesign is completed when site is customized beyond the default setup from WPNUX onboarding.
	 */
	public function test_is_complete_with_modified_wpnux_data() {
		$this->assertEquals( $this->default_theme_mods, get_theme_mods() );

		$wpnux_export_data = array(
			'content' => array(
				'theme_mods' => array(
					'modification_one' => 'value',
					// Using an array to ensure we don't receive an "array to string conversion" error.
					'modification_two' => array(),
				),
			),
		);
		add_option( 'wpnux_export_data', json_encode( $wpnux_export_data ) );

		foreach ( $wpnux_export_data['content']['theme_mods'] as $name => $value ) {
			set_theme_mod( $name, $value );
		}

		$this->assertFalse( $this->instance->is_complete() );

		set_theme_mod( 'modification_one', 'newValue' );

		$this->assertTrue( $this->instance->is_complete() );
	}
}
