<?php

namespace Wptool\adminDash\services;

use Wptool\adminDash\clients\ga\GAClient;
use Wptool\adminDash\constants\TrackingConstants;
use Wptool\adminDash\services\tracking\ClickProcessor;
use Wptool\adminDash\services\tracking\PageviewProcessor;
use Wptool\adminDash\services\tracking\TrackingProcessor;

class TrackingService {

	/** @var GAClient */
	private $ga_client;

	/**
	 * @param GAClient $ga_client
	 */
	public function __construct( $ga_client ) {

		$this->ga_client = $ga_client;
	}

	const TYPES = array(
		TrackingConstants::CLICK,
		TrackingConstants::PAGE_VIEW,
	);

	const PROCESSORS = array(
		TrackingConstants::PAGE_VIEW => PageviewProcessor::class,
		TrackingConstants::CLICK     => ClickProcessor::class,
	);

	/**
	 * Process load data and send it to GoogleAnalytics.
	 *
	 * @param $data
	 *
	 * @return bool
	 * @throws \Exception
	 */
	public function process_load_data( $data ) {

		$type = $data['type'];

		$processor = $this->get_tracking_processor( $type );

		if ( ! $processor instanceof TrackingProcessor ) {
			throw new \Exception( 'Tracking data cannot be processed.' );
		}

		$tracking_request = $processor->process( $data );

		return $this->ga_client->send( $tracking_request );
	}

	/**
	 * Fetch processor depending on event type.
	 *
	 * @param $type
	 *
	 * @return mixed
	 * @throws \Exception
	 */
	private function get_tracking_processor( $type ) {

		if ( isset( self::PROCESSORS[ $type ] ) ) {
			$processor = self::PROCESSORS[ $type ];
			return new $processor;
		}

		throw new \Exception( 'Tracking data cannot be processed. Processor not found.' );
	}
}
