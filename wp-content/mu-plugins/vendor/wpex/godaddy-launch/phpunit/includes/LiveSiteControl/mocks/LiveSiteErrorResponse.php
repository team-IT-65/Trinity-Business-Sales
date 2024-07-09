<?php
/**
 * The LiveSiteErrorResponse class.
 *
 * @package GoDaddy
 */

namespace GoDaddy\WordPress\Plugins\Launch\Tests;

use GoDaddy\WordPress\Plugins\Launch\LiveSiteControl\LiveSiteControlProvider;

/**
 * The LiveSiteErrorResponse class.
 */
class LiveSiteErrorResponse extends LiveSiteControlProvider {

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
		return wp_json_encode(
			array(
				'headers'  => array(),
				'body'     => array(),
				'response' => array(
					'success' => false,
					'code'    => 429,
					'message' => 'Too Many Requests',
					'data'    => array(
						'can_submit_milestone'      => false,
						'errors'                    => array(
							0 => 'Too many submissions. Please try again in 1 second.',
						),
						'last_milestone_at'         => '2022-11-30T16:19:02.000000Z',
						'limit'                     => 1,
						'limit_for'                 => 'localhost.com',
						'limit_per'                 => null,
						'next_milestone_allowed_at' => null,
						'next_milestone_allowed_in' => '1 second',
						'time'                      => 1669825168,
						'type'                      => 'site-publish',
					),
				),
			)
		);
	}
};
