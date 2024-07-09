<?php

namespace Wptool\adminDash\exceptions;

class MinificationRequestFailedException extends AdminDashException {

	/**
	 * @param $message
	 * @param $code
	 * @param \Exception|null $previous
	 */
	public function __construct( $message = '', $code = 0, \Exception $previous = null ) {
		parent::__construct( $message, $code, $previous );

		$this->reason = 'Minification request failed.';
	}
}
