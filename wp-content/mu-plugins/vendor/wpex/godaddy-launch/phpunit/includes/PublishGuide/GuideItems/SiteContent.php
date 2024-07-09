<?php
/**
 * Tests for PublishGuide\GuideItems\SiteContent.
 *
 * @package GoDaddy_Launch
 */

namespace GoDaddy\WordPress\Plugins\Launch\Tests;

use GoDaddy\WordPress\Plugins\Launch\PublishGuide\GuideItems\SiteContent;

/**
 * Tests for PublishGuide\GuideItems\SiteContent.
 */
class SiteContent_Test extends \WP_UnitTestCase {
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

		$this->instance = new SiteContent();
		update_option( 'coblocks_site_content_controls_enabled', true );
	}

	/**
	 * Test that SiteContent is enabled by default.
	 */
	public function test_is_enabled_by_default() {
		$this->assertTrue( get_option( 'coblocks_site_content_controls_enabled' ) );
		$this->assertTrue( $this->instance->is_enabled() );
	}

	/**
	 * Test that SiteContent requires CoBlocks option enabled.
	 */
	public function test_is_enabled_requires_coblocks_feature_enabled() {
		$this->assertTrue( get_option( 'coblocks_site_content_controls_enabled' ) );
		$this->assertTrue( $this->instance->is_enabled() );
	}

	/**
	 * Test that the default posts & pages of a fresh WordPress & WooCommerce install do not trigger completion status.
	 */
	public function test_not_is_complete_on_fresh_install() {
		// Clear the database.
		global $wpdb;
		$wpdb->query( "TRUNCATE $wpdb->comments" );
		$wpdb->query( "TRUNCATE $wpdb->commentmeta" );
		$wpdb->query( "TRUNCATE $wpdb->posts" );
		$wpdb->query( "TRUNCATE $wpdb->postmeta" );
		$wpdb->query( "TRUNCATE $wpdb->terms" );
		$wpdb->query( "TRUNCATE $wpdb->term_taxonomy" );
		$wpdb->query( "TRUNCATE $wpdb->term_relationships" );

		// Setup default posts & pages.
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		wp_install_defaults( 1 );

		// Activate & install WooCommerce.
		$all_plugins = array_keys( get_plugins() );
		if ( ! empty( $all_plugins['woocommerce/woocommerce.php'] ) ) {
			activate_plugin( 'woocommerce/woocommerce.php' );
			\WC_Install::install();
		}

		$this->assertFalse( $this->instance->is_complete() );
	}

	/**
	 * Test that a newly published post triggers completion.
	 */
	public function test_is_complete_newly_published_post() {
		$this->assertFalse( $this->instance->is_complete() );

		wp_insert_post(
			array(
				'post_title'  => 'Testing',
				'post_status' => 'publish',
			)
		);

		$this->assertTrue( $this->instance->is_complete() );
	}

	/**
	 * Test that default posts & pages added from WPNUX onboarding do not trigger completion status.
	 */
	public function test_is_complete_excluding_wpnux_data_posts() {
		$wpnux_export_data = array(
			'content' => array(
				'posts' => array(
					'home-page'    => array(
						'post_type'    => 'page',
						'post_title'   => 'Home',
						'post_content' => 'post content',
					),
					'blog-page'    => array(
						'post_type'    => 'page',
						'post_title'   => 'Blog',
						'post_content' => 'post content',
					),
					'contact-page' => array(
						'post_type'    => 'page',
						'post_title'   => 'Contact',
						'post_content' => 'post content',
					),
				),
			),
		);
		add_option( 'wpnux_export_data', json_encode( $wpnux_export_data ) );

		foreach ( $wpnux_export_data['content']['posts'] as $postarr ) {
			wp_insert_post( $postarr );
		}

		$this->assertFalse( $this->instance->is_complete() );

		wp_insert_post(
			array(
				'post_title'  => 'Testing',
				'post_status' => 'publish',
			)
		);

		$this->assertTrue( $this->instance->is_complete() );
	}
}
