<?php

namespace Wptool\adminDash\services\tracking;

use Wptool\adminDash\clients\ga\GARequest;
use Wptool\adminDash\constants\TrackingConstants;

class PageviewProcessor extends TrackingProcessor {

	/**
	 * Process and formasts data for Pageview and return GoogleAnalytics request.
	 *
	 * @param $data
	 *
	 * @return GARequest
	 */
	public function process( $data ) {

		$request = new GARequest( $this->genereate_client_id() );

		$request->add_event(
			array(
				'name'  => TrackingConstants::PAGE_VIEW,
				'value' => $data['page'],
			)
		);

		return $request;
	}
}
