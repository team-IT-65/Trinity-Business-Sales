<?php

namespace Wptool\adminDash\exceptions;


abstract class AdminDashException extends \Exception {

	/**
	 * @var string
	 */
	protected $reason;

	/**
	 * @param $message
	 * @param $code
	 * @param \Exception|null $previous
	 */
	public function __construct( $message = '', $code = 0, \Exception $previous = null ) {
		parent::__construct( $message, $code, $previous );
	}

	/**
	 * Retrieves reason of the exception.
	 *
	 * @return string
	 */
	public function getReason() {
		return $this->reason;
	}
}
