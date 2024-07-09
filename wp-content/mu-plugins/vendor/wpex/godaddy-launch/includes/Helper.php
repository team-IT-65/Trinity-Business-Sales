<?php
/**
 * The Helper class.
 *
 * @package GoDaddy
 */

namespace GoDaddy\WordPress\Plugins\Launch;

defined( 'ABSPATH' ) || exit;

/**
 * The Helper class.
 */
class Helper {
	/**
	 * Retrieve the correct URL of the NUX API depending on the environment
	 *
	 * @return string
	 */
	public static function wpnux_api_base() {
		$api_urls = array(
			'local' => 'https://wpnux.test/v3/api',
			'dev'   => 'https://wpnux.dev-godaddy.com/v3/api',
			'test'  => 'https://wpnux.test-godaddy.com/v3/api',
			'prod'  => 'https://wpnux.godaddy.com/v3/api',
		);

		$env = getenv( 'SERVER_ENV', true );

		$api_url = ! empty( $api_urls[ $env ] ) ? $api_urls[ $env ] : $api_urls['dev'];

		return untrailingslashit( (string) apply_filters( 'godaddy_launch_wpnux_api_url', $api_url ) );
	}

	/**
	 * Determine if the RUM (Real User Metrics) is enabled
	 *
	 * @return bool
	 */
	public static function is_rum_enabled() {
		return (bool) apply_filters( 'wpaas_rum_enabled', defined( 'GD_RUM_ENABLED' ) ? GD_RUM_ENABLED : true );
	}

	/**
	 * Return the site domain.
	 *
	 * @return string
	 */
	public static function domain() {
		return wp_parse_url( home_url(), PHP_URL_HOST );
	}

	/**
	 * Convert option boolean value to timestamp when saved.
	 *
	 * @param string $value passed from filter.
	 *
	 * @return mixed
	 */
	public static function update_option_convert_true_to_timestamp( $value ) {
		return ( 'true' === $value || true === $value ) ? time() : $value;
	}

	/**
	 * Get skipped or boolean as string.
	 *
	 * @param string $value passed from filter.
	 *
	 * @return bool
	 */
	public static function get_skipped_or_boolean_as_string( $value ) {
		return ( 'skipped' === $value ) ? $value : (string) self::get_option_convert_timestamp_to_true( $value );
	}

	/**
	 * Convert option timestamp value to boolean.
	 *
	 * @param string $value passed from filter.
	 *
	 * @return bool
	 */
	public static function get_option_convert_timestamp_to_true( $value ) {
		return ! empty( $value );
	}

	/**
	 * Get the default request body payload for the NUX API.
	 *
	 * @return array
	 */
	public static function get_default_nux_api_request_body() {
		$parent_theme = get_option( 'template' );
		$parent_theme = is_string( $parent_theme ) ? strtolower( $parent_theme ) : null;

		return array(
			'coblocks_version'      => defined( 'COBLOCKS_VERSION' ) ? COBLOCKS_VERSION : null,
			'current_theme'         => $parent_theme,
			'customer_id'           => defined( 'GD_CUSTOMER_ID' ) ? GD_CUSTOMER_ID : null,
			'gdl_version'           => gdl()->version(),
			'go_theme_version'      => defined( 'GO_VERSION' ) ? GO_VERSION : null,
			'hostname'              => gethostname(),
			'is_coblocks_active'    => is_plugin_active( 'coblocks/class-coblocks.php' ),
			'is_go_theme_active'    => 'go' === $parent_theme,
			'language'              => get_user_locale(),
			'pl_id'                 => defined( 'GD_RESELLER' ) ? (int) GD_RESELLER : null,
			'site_created_at'       => defined( 'GD_SITE_CREATED' ) ? GD_SITE_CREATED : null,
			'site_domain'           => parse_url( home_url(), PHP_URL_HOST ),
			'system_plugin_version' => is_callable( array( '\WPaaS\Plugin', 'version' ) ) ? \WPaaS\Plugin::version() : null,
			'website_id'            => defined( 'GD_ACCOUNT_UID' ) ? GD_ACCOUNT_UID : null,
			'wp_user_id'            => get_current_user_id(),
			'wp_version'            => get_bloginfo( 'version' ),
		);
	}
}
