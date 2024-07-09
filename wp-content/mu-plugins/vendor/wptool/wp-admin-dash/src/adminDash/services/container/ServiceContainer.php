<?php

namespace Wptool\adminDash\services\container;

class ServiceContainer {

	private $container;

	/**
	 * @param array $values
	 */
	public function __construct( $values ) {
		$this->container = new Container( $values );
	}

	/**
	 * Gets service from container for gien id.
	 *
	 * @param $id
	 *
	 * @return mixed
	 */
	public function get( $id ) {
		return $this->container[ $id ];
	}

	/**
	 * Checks if service with given id exists.
	 *
	 * @param $id
	 *
	 * @return bool
	 */
	public function has( $id ) {
		return isset( $this->container[ $id ] );
	}

}
