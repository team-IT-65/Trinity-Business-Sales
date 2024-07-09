<?php
/**
 * The LiveSiteUnexpectedResponse class.
 *
 * @package GoDaddy
 */

namespace GoDaddy\WordPress\Plugins\Launch\Tests;

use GoDaddy\WordPress\Plugins\Launch\LiveSiteControl\LiveSiteControlProvider;

/**
 * The LiveSiteUnexpectedResponse class.
 */
class LiveSiteUnexpectedResponse extends LiveSiteControlProvider {
	/**
	 * The LiveSiteSuccessResponse class.
	 *
	 * Typical response:
	 * {
	 *   ["headers"]=> array(0) {}
	 *   ["body"]=> string(0) ""
	 *   ["response"]=> array(4) {
	 *     ["code"]=> bool(false)
	 *     ["message"] => bool(false)
	 *     ["success"] => bool(false)
	 *     ["data"]    => array(...)
	 *   }
	 *   ["cookies"]=> array(0) {}
	 *   ["http_response"]=> NULL
	 * }
	 *
	 * @param String $url String representing the external URI of the NUX API.
	 * @param Array  $body Contains the content sent to NUX API.
	 * @return Array Raw response object from NUX API.
	 */
	public function perform_remote_api_post( $url = '', $body = '' ) { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter
		return wp_json_encode( array( 'body' => 'Test response.' ) );
	}
};
