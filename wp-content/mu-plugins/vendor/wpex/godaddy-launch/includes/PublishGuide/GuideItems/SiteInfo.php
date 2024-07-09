<?php
/**
 * The SiteInfo class.
 *
 * @package GoDaddy
 */

namespace GoDaddy\WordPress\Plugins\Launch\PublishGuide\GuideItems;

/**
 * The SiteInfo class.
 */
class SiteInfo extends GuideItemAbstract {
	/**
	 * Determins if the guide item should be enabled.
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
			$this->has_site_title(),
			$this->has_site_description(),
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
		return 'gdl_pgi_site_info';
	}

	/**
	 * Returns the milestone name of the GuideItem used in the nux api.
	 *
	 * @return string
	 */
	public function milestone_name() {
		return 'site-info';
	}

	/**
	 * Determine if the site has a title.
	 *
	 * @return bool
	 */
	private function has_site_title() {
		$blogname = get_option( 'blogname' );
		return ! empty( $blogname ) && ! in_array( $blogname, $this->default_strings(), true );
	}

	/**
	 * Determine if the site has a description.
	 *
	 * @return bool
	 */
	private function has_site_description() {
		$blogdescription = get_option( 'blogdescription' );
		return ! empty( $blogdescription ) && ! in_array( $blogdescription, $this->default_strings(), true );
	}

	/**
	 * Returns an array of default strings possible in site info options.
	 *
	 * We need to use a function so we can utilize tranlation functions.
	 *
	 * @return array
	 */
	private function default_strings() {
		return array(
			__( 'Just another WordPress site', 'godaddy-launch' ),
			__( 'A WordPress Site', 'godaddy-launch' ),
		);
	}
}
