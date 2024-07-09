<?php
/**
 * Tests for PublishGuide\GuideItems\SiteContent.
 *
 * @package GoDaddy_Launch
 */

namespace GoDaddy\WordPress\Plugins\Launch\Tests;

use function GoDaddy\WordPress\Plugins\Launch\gdl;

/**
 * Tests for PublishGuide\GuideItems\SiteContent.
 */
class PublishGuideServiceProvider_Test extends \WP_UnitTestCase {

	/**
	 * Instance SiteContent to test.
	 *
	 * @var SiteContent
	 */
	protected $instance;

	/**
	 * Setup each test.
	 */
	public function set_up() {
		parent::set_up();
		$this->instance = new \GoDaddy\WordPress\Plugins\Launch\PublishGuide\PublishGuideServiceProvider( gdl() );

		// Reset queued scripts.
		global $wp_scripts;
		$wp_scripts = new \WP_Scripts();

		update_option( 'wpnux_export_uid', '1234' );
	}

	/**
	 * Test return early for non-admin.
	 */
	public function test_boot_non_admin() {
		wp_set_current_user( 0 );

		$this->instance->boot();
		do_action( 'wp_enqueue_scripts' );

		global $wp_scripts;
		$this->assertFalse( $wp_scripts->query( 'GoDaddy\WordPress\Plugins\Launch\PublishGuidepublish-guide-script', 'enqueued' ) );
	}

	/**
	 * Test boot runs for admins on non-protected pages.
	 */
	public function test_boot_admin() {
		wp_set_current_user( 1 );

		$this->instance->boot();
		do_action( 'wp_enqueue_scripts' );

		global $wp_scripts;
		$this->assertTrue( $wp_scripts->query( 'GoDaddy\WordPress\Plugins\Launch\PublishGuidepublish-guide-script', 'enqueued' ) );
	}

	/**
	 * Test only boot with export uid.
	 */
	public function test_boot_with_export_uid() {
		global $wp_scripts;
		wp_set_current_user( 1 );

		delete_option( 'wpnux_export_uid' );
		$this->assertFalse( $this->instance->has_export_uid() );

		update_option( 'wpnux_export_uid', '' );
		$this->assertFalse( $this->instance->has_export_uid() );

		$this->instance->boot();
		do_action( 'wp_enqueue_scripts' );
		$this->assertFalse( $wp_scripts->query( 'GoDaddy\WordPress\Plugins\Launch\PublishGuidepublish-guide-script', 'enqueued' ) );

		update_option( 'wpnux_export_uid', '1234' );
		$this->assertTrue( $this->instance->has_export_uid() );

		$this->instance->boot();
		do_action( 'wp_enqueue_scripts' );
		$this->assertTrue( $wp_scripts->query( 'GoDaddy\WordPress\Plugins\Launch\PublishGuidepublish-guide-script', 'enqueued' ) );
	}

	/**
	 * Test method to verify the boot process for migrated sites.
	 *
	 * This method defines the GD_MIGRATED_SITE constant and an export uid to simulate a migrated site.
	 * It then sets the current user to an admin and boots the PublishGuideServiceProvider instance.
	 * Finally, it fires the 'wp_enqueue_scripts' action and verifies that the 'publish-guide-script' script is enqueued.
	 */
	public function test_boot_migrated() {
		// Migrated sites have the GD_MIGRATED_SITE constant defined.
		define( 'GD_MIGRATED_SITE', true );

		// Migrated sites have an export uid.
		update_option( 'wpnux_export_uid', '1234' );

		wp_set_current_user( 1 );

		$this->instance->boot();
		do_action( 'wp_enqueue_scripts' );

		global $wp_scripts;
		$this->assertTrue( $wp_scripts->query( 'GoDaddy\WordPress\Plugins\Launch\PublishGuidepublish-guide-script', 'enqueued' ) );
	}
}
