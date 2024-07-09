<?php

namespace Wptool\adminDash\clients\ga;

class GARequest {

	private $client_id;

	private $events;

	/**
	 * @param $client_id
	 */
	public function __construct( $client_id ) {
		$this->events    = array();
		$this->client_id = $client_id;
	}

	/**
	 * Adds event to GA request.
	 *
	 * @param array $event
	 *
	 * @return void
	 */
	public function add_event( $event ) {

		$this->events[] = $event;
	}

	/**
	 * Formats GA request data.
	 *
	 * @return array
	 */
	public function get_request_data() {
		$data = array(
			'client_id' => $this->client_id,
			'events'    => array(),
		);

		foreach ( $this->events as $event ) {
			$data['events'][] = array(
				'name'   => $event['name'],
				'params' => array(
					'value' => $event['value'],
				),
			);
		}

		return $data;
	}
}
