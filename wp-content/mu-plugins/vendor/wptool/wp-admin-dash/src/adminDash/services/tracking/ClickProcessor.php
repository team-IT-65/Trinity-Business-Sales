<?php

namespace Wptool\adminDash\services\tracking;

use Wptool\adminDash\clients\ga\GARequest;
use Wptool\adminDash\constants\TrackingConstants;

class ClickProcessor extends TrackingProcessor {

	/**
	 * Process and formasts data for Event and return GoogleAnalytics request.
	 *
	 * @param $data
	 *
	 * @return GARequest
	 */
	public function process( $data ) {

		$request = new GARequest( $this->genereate_client_id() );

		if ( empty( $data['section'] ) ) {
			throw new \Exception( 'section is required for type click event' );
		}

		$request->add_event(
			array(
				'name'  => TrackingConstants::CLICK,
				'value' => $data['section'],
			)
		);

		return $request;
	}
}
