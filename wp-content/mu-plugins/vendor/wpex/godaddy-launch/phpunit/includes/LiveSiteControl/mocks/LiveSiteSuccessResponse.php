<?php
/**
 * The LiveSiteSuccessResponse class.
 *
 * @package GoDaddy
 */

namespace GoDaddy\WordPress\Plugins\Launch\Tests;

use GoDaddy\WordPress\Plugins\Launch\LiveSiteControl\LiveSiteControlProvider;

/**
 * The LiveSiteSuccessResponse class.
 */
class LiveSiteSuccessResponse extends LiveSiteControlProvider {

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
				array(
					'headers'  => array(),
					'body'     => array(),
					'response' => array(
						'success' => true,
						'code'    => 201,
						'message' => 'Created',
						'data'    => array(
							'can_submit_milestone'      => false,
							'id'                        => '97dea5ee-4ea8-458b-9866-8ddc9558abcc',
							'last_milestone_at'         => '2022-11-30T16:20:23.000000Z',
							'limit'                     => 1,
							'limit_for'                 => 'localhost.org',
							'limit_per'                 => null,
							'next_milestone_allowed_at' => null,
							'next_milestone_allowed_in' => '1 second',
							'time'                      => 1669825224,
							'type'                      => 'site-publish',
						),
					),
				),
			)
		);
	}
};
