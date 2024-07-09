<?php
/**
 * The GuideItemInterface class.
 *
 * @package GoDaddy
 */

namespace GoDaddy\WordPress\Plugins\Launch\PublishGuide\GuideItems;

/**
 * Describes the interface of a guide item that exposes methods to read its current state.
 */
interface GuideItemInterface {

	/**
	 * Determins if the guide item has been skipped.
	 *
	 * @return bool
	 */
	public function is_skipped();

	/**
	 * Determins if the guide item should be enabled.
	 *
	 * @return bool
	 */
	public function is_enabled();

	/**
	 * Determins if the guide item has been completed.
	 *
	 * @return bool
	 */
	public function is_complete();

	/**
	 * Returns the option_name of the GuideItem used in the wp_options table.
	 *
	 * @return string
	 */
	public function option_name();

	/**
	 * Returns the milestone name of the GuideItem used in the nux api.
	 *
	 * @return string
	 */
	public function milestone_name();
}
