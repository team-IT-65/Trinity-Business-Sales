<?php
/**
 * The GuideItemAbstract class.
 *
 * @package GoDaddy
 */

namespace GoDaddy\WordPress\Plugins\Launch\PublishGuide\GuideItems;

/**
 * Describes the repeated logic on all instances of GuideItemInterface
 */
abstract class GuideItemAbstract implements GuideItemInterface {
	/**
	 * Determins if the guide item has been skipped.
	 *
	 * @return bool
	 */
	public function is_skipped() {
		return get_option( $this->option_name() ) === 'skipped';
	}
}
