<?php
/**
 * The AddDomain class.
 *
 * @package GoDaddy
 */

namespace GoDaddy\WordPress\Plugins\Launch\PublishGuide\GuideItems;

/**
 * The AddDomain class.
 */
class AddDomain extends GuideItemAbstract {
	/**
	 * Determines if the guide item should be enabled.
	 *
	 * @return bool
	 */
	public function is_enabled() {
		return true;
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
			$this->has_custom_domain(),
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
		return 'gdl_pgi_add_domain';
	}

	/**
	 * Returns the milestone name of the GuideItem used in the nux api.
	 *
	 * @return string
	 */
	public function milestone_name() {
		return 'add-domain';
	}

	/**
	 * Determine if the site has a temp domain.
	 *
	 * @return bool
	 */
	private function has_custom_domain() {
		$temp_domain = defined( 'GD_TEMP_DOMAIN' ) ? GD_TEMP_DOMAIN : false;

		if ( ! $temp_domain ) {
			return false;
		}

		$protocols = array( 'http://', 'https://' );
		return str_replace( $protocols, '', home_url() ) !== str_replace( $protocols, '', $temp_domain );
	}
}
