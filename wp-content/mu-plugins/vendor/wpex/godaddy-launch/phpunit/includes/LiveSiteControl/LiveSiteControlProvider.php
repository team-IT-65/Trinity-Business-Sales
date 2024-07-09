<?php
/**
 * Tests for LiveSiteControl\LiveSiteControlProvider.
 *
 * @package GoDaddy_Launch
 */

namespace GoDaddy\WordPress\Plugins\Launch\Tests;

use GoDaddy\WordPress\Plugins\Launch\LiveSiteControl\LiveSiteControlProvider;
use GoDaddy\WordPress\Plugins\Launch\Helper;

require_once __DIR__ . '/mocks/LiveSiteErrorResponse.php';
require_once __DIR__ . '/mocks/LiveSiteSuccessResponse.php';
require_once __DIR__ . '/mocks/LiveSiteUnexpectedResponse.php';


use GoDaddy\WordPress\Plugins\Launch\Tests\LiveSiteSuccessResponse;
use GoDaddy\WordPress\Plugins\Launch\Tests\LiveSiteErrorResponse;
use GoDaddy\WordPress\Plugins\Launch\Tests\LiveSiteUnexpectedResponse;

/**
 * Tests for LiveSiteControl\LiveSiteControlProvider.
 */
class LiveSiteControlProvider_Test extends \WP_UnitTestCase {
	/**
	 * Instance LiveSiteControlProvider to test.
	 *
	 * @var LiveSiteControlProvider
	 */
	protected $instance;

	/**
	 * Setup each test.
	 */
	public function set_up() {
		parent::set_up();

		$this->instance = $this->registerInstance( LiveSiteControlProvider::class );

		remove_all_filters( 'wpaas_rum_enabled' );
	}

	/**
	 * Used to reinitialize the class instance with mocked variants.
	 *
	 * @param String $class_to_register Is string representing class to register.
	 */
	private function registerInstance( $class_to_register ) {
		return \GoDaddy\WordPress\Plugins\Launch\gdl()->register( $class_to_register );
	}

	/**
	 * Test that test_is_rest_endpoint_restricted is restricting the filtered paths.
	 */
	public function test_is_rest_endpoint_restricted() {
		// Test defaults.
		$this->assertTrue( $this->instance->is_rest_endpoint_restricted( '/wp/v2' ) );
		$this->assertFalse( $this->instance->is_rest_endpoint_restricted( '/wpaas/v1' ) );

		// Test all restricted.
		$callback = function() {
			return array();
		};
		add_filter( 'gdl_unrestricted_rest_endpoints', $callback );

		$this->assertTrue( $this->instance->is_rest_endpoint_restricted( '/wp/v2' ) );
		$this->assertTrue( $this->instance->is_rest_endpoint_restricted( '/wpaas/v1' ) );

		remove_filter( 'gdl_unrestricted_rest_endpoints', $callback );

		// Test allow /wp/v2 endpoints.
		$callback = function( $unrestricted_defaults ) {
			$unrestricted_defaults[] = '/wp/v2';
			return $unrestricted_defaults;
		};
		add_filter( 'gdl_unrestricted_rest_endpoints', $callback );

		$this->assertFalse( $this->instance->is_rest_endpoint_restricted( '/wp/v2' ) );
		$this->assertFalse( $this->instance->is_rest_endpoint_restricted( '/wpaas/v1' ) );

		remove_filter( 'gdl_unrestricted_rest_endpoints', $callback );
	}

	/**
	 * Test that milestone_published_nux_api will handle any api response type.
	 *
	 * This test can be considered incomplete in that we do not test any real API response from test or local.
	 * We know how this function will behave but are unable to assert based on actual API responses and
	 * are thus unable to extend these tests to further functionalities or features.
	 *
	 * We need tests to be run off of a real API either local or reliable test API that we can send mock data
	 * with expected responses. Present version of API has restriction of repeated data submissions meaning
	 * mock data cannot be expected to be consistent.
	 */
	public function test_milestone_published_nux_api() {
		// milestone_published_nux_api should return WP_Rest_Response if valid api response exists.
		$instance = $this->registerInstance( LiveSiteSuccessResponse::class );
		$results  = $instance->milestone_published_nux_api();
		$this->assertInstanceOf( \WP_REST_Response::class, $results );

		// milestone_published_nux_api should return error if api error response exists.
		$instance = $this->registerInstance( LiveSiteErrorResponse::class );
		$results  = $instance->milestone_published_nux_api();
		$this->assertInstanceOf( \WP_REST_Response::class, $results );

		// milestone_published_nux_api should return error if unexpected api response exists.
		$instance = $this->registerInstance( LiveSiteUnexpectedResponse::class );
		$results  = $instance->milestone_published_nux_api();
		$this->assertInstanceOf( \WP_REST_Response::class, $results );
	}

	/**
	 * Test that gdl/v1 endpoints are disabled when GD_RUM_ENABLED is false.
	 */
	public function test_milestone_endpoints_inactive_when_rum_is_disabled() {
		add_filter( 'wpaas_rum_enabled', '__return_false' );
		$this->instance->boot();
		do_action( 'rest_api_init' );

		$rest_server = rest_get_server();
		$this->assertNotContains( 'gdl/v3', $rest_server->get_namespaces() );
	}

	/**
	 * Test that gdl/v1 endpoints are enabled when GD_RUM_ENABLED is true.
	 */
	public function test_milestone_endpoints_active_when_rum_is_enabled() {
		add_filter( 'wpaas_rum_enabled', '__return_true' );
		$this->instance->boot();
		do_action( 'rest_api_init' );

		$rest_server = rest_get_server();
		$this->assertContains( 'gdl/v1', $rest_server->get_namespaces() );
	}

	/**
	 * Test site is can be viewed when publish state is true.
	 */
	public function test_is_restricted_publish_state() {
		// Default: not published.
		delete_option( $this->instance::SETTINGS['publishState'] );
		$this->assertTrue( $this->instance->is_restricted() );

		// Site is published.
		update_option( $this->instance::SETTINGS['publishState'], true );
		$this->assertFalse( $this->instance->is_restricted() );

		// Site is unpublished.
		update_option( $this->instance::SETTINGS['publishState'], false );
		$this->assertTrue( $this->instance->is_restricted() );
	}

	/**
	 * Test site can be viewed when publish state is false and query param is set.
	 */
	public function test_is_restricted_export_uid_override() {
		// Site went through onboarding and has wpnux_export_uid set.
		update_option( 'wpnux_export_uid', '99ad3308-3d64-415f-a2f9-5ad28691909c' );
		delete_option( $this->instance::SETTINGS['publishState'] );

		$this->assertTrue( $this->instance->is_restricted() );

		// Allow override with query param.
		$_GET['wpnux_export_uid'] = '99ad3308-3d64-415f-a2f9-5ad28691909c';
		$this->assertFalse( $this->instance->is_restricted() );

		// Ensure query param matches export uid.
		$_GET['wpnux_export_uid'] = '99ad3308-3d64-415f-a2f9-000000000000';
		$this->assertTrue( $this->instance->is_restricted() );

		// Sanity: publish state is absolute.
		update_option( $this->instance::SETTINGS['publishState'], true );
		$this->assertFalse( $this->instance->is_restricted() );

		// Sanity: override ignored because site is published.
		$_GET['wpnux_export_uid'] = '99ad3308-3d64-415f-a2f9-5ad28691909c';
		$this->assertFalse( $this->instance->is_restricted() );

		// Sanity: override ignored because site is published.
		$_GET['wpnux_export_uid'] = '99ad3308-3d64-415f-a2f9-000000000000';
		$this->assertFalse( $this->instance->is_restricted() );
	}

	/**
	 * Test that the NUX API milestone publish event includes all of the expected data.
	 */
	public function test_nux_api_milestone_publish_payload() {
		$domain = defined( 'GD_TEMP_DOMAIN' ) ? GD_TEMP_DOMAIN : Helper::domain();
		$url    = Helper::wpnux_api_base() . '/milestones/site-publish?domain=' . $domain;

		add_filter(
			'pre_http_request',
			function( $preempt, $args, $url ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter
				$payload_body = (array) json_decode( $args['body'] );

				$nux_api_data = array(
					'coblocks_version',
					'current_theme',
					'customer_id',
					'gdl_version',
					'go_theme_version',
					'hostname',
					'is_coblocks_active',
					'is_go_theme_active',
					'language',
					'pl_id',
					'site_created_at',
					'site_domain',
					'system_plugin_version',
					'website_id',
					'wp_user_id',
					'wp_version',

					// additional meta fields.
					'is_launch_now',
					'launch_later_at',
					'guide_items',
					'guide_items_count',
					'guide_items_complete',
					'guide_items_skipped',
					'guide_items_skipped_count',
					'guide_items_complete_count',
					'guide_items_complete_percent',
					'guide_items_incomplete',
					'guide_items_incomplete_count',
					'guide_items_incomplete_percent',
					'guide_items_disabled',
					'guide_items_disabled_count',
					'guide_items_disabled_method',
				);

				foreach ( $nux_api_data as $key ) {
					$this->assertArrayHasKey( $key, $payload_body );
				}
			},
			10,
			3
		);

		$this->instance->milestone_published_nux_api();
	}
}
