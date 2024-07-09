<?php
/**
 * The SEO class.
 *
 * @package GoDaddy
 */

namespace GoDaddy\WordPress\Plugins\Launch\PublishGuide\GuideItems;

/**
 * The SEO class.
 */
class SEO extends GuideItemAbstract {

	/**
	 * SEO class constructor.
	 */
	public function __construct() {
		if ( ! $this->is_enabled() ) {
			return;
		}

		add_action( 'admin_init', array( $this, 'disable_yoast_seo_configuration_notice' ) );
	}

	/**
	 * Set the Yoast dismiss_configuration_workout_notice option to true to disable the admin notice.
	 */
	public function disable_yoast_seo_configuration_notice() {
		$options = get_option( 'wpseo', array() );

		if ( isset( $options['dismiss_configuration_workout_notice'] ) && true === $options['dismiss_configuration_workout_notice'] ) {
			return;
		}

		$options['dismiss_configuration_workout_notice'] = true;

		update_option( 'wpseo', $options );
	}

	/**
	 * Determines if the guide item should be enabled.
	 *
	 * @return bool
	 */
	public function is_enabled() {
		return ( is_plugin_active( 'wordpress-seo/wp-seo.php' ) || is_plugin_active( 'wordpress-seo-premium/wp-seo-premium.php' ) );
	}

	/**
	 * Return if the guide item has been completed.
	 *
	 * @return bool
	 */
	public function is_complete() {
		if ( get_option( $this->option_name() ) ) {
			return true;
		}

		$conditions = array(
			$this->is_yoast_seo_configured(),
		);

		$has_incomplete = array_filter(
			$conditions,
			function( $val ) {
				return empty( $val );
			}
		);

		return empty( $has_incomplete );
	}

	/**
	 * Returns the option_name of the GuideItem used in the wp_options table.
	 *
	 * @return string
	 */
	public function option_name() {
		return 'gdl_pgi_site_seo';
	}

	/**
	 * Returns the milestone name of the GuideItem used in the nux api.
	 *
	 * @return string
	 */
	public function milestone_name() {
		return 'site-seo';
	}

	/**
	 * Determines if Yoas SEO has been configured.
	 *
	 * @return bool
	 */
	private function is_yoast_seo_configured() {
		$yoast_options = get_option( 'wpseo', array() );

		if ( ! isset( $yoast_options['configuration_finished_steps'] ) ) {
			return false;
		}

		return 3 === count( $yoast_options['configuration_finished_steps'] );
	}
}
