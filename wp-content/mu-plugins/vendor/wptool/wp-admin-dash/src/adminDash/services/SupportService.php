<?php

namespace Wptool\adminDash\services;

use Wptool\adminDash\exceptions\AdminDashException;
use Wptool\adminDash\exceptions\SupportRequestFailedException;
use Wptool\adminDash\utils\Configuration;

class SupportService {

	private $api_url;

	public function __construct() {
		$this->api_url = Configuration::get( 'support_api_url' );
	}
	/**
	 * Sends POST API request in order to create support request.
	 *
	 * @param $params
	 *
	 * @return array
	 * @throws AdminDashException|SupportRequestFailedException
	 */
	public function send_support_request( $params ) {

		$data = $this->format_support_request_data( $params );

		$response = wp_remote_post(
			$this->api_url . '/support/request',
			array(
				'body' => array(
					'from' => $params['reply_to'],
					'data' => $data,
				),
			)
		);

		if ( is_wp_error( $response ) ) {
			throw new SupportRequestFailedException( $response->get_error_message() );
		}

		return array(
			'reason'   => $params['reason'],
			'reply_to' => $params['reply_to'],
			'subject'  => $params['subject'],
			'message'  => $params['message'],
		);

	}


	/**
	 * Formatting support request data.
	 *
	 * @param $params
	 *
	 * @return array
	 * @throws AdminDashException
	 */
	private function format_support_request_data( $params ) {

		return array(
			'ticket'              => array(
				'subject'     => $params['subject'],
				'description' => $params['message'],
			),
			'customer'            => array(
				'name'  => wp_get_current_user()->name,
				'email' => $params['reply_to'],
			),
			'reason'              => $params['reason'],
			'support_bot_context' => $this->is_reseller() ? 'reseller' : 'godaddy',
			//          'system_status_report' => '',
			'mwp'                 => array(
				'is_reseller' => $this->is_reseller(),
				'plan'        => array(
					'type' => defined( 'GD_PLAN_NAME' ) ? GD_PLAN_NAME : null,
				),
			),
		);
	}

	/**
	 * Checks if account is reseller.
	 *
	 * @return bool
	 */
	private function is_reseller() {
		return defined( 'GD_RESELLER' ) && GD_RESELLER > 1;
	}
}
