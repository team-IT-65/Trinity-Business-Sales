<?php

namespace Wptool\adminDash\utils;

class ClassUtils {

	/**
	 * Checks if all provided methods are part of the class
	 *
	 * @param string $class_name Class name to check against
	 * @param array $methods Methods to check for
	 * @return bool True/false
	 */
	public static function check_methods_exsist( $class_name, $methods ) {

		if ( ! class_exists( $class_name ) ) {
			return false;
		}

		foreach ( $methods as $method ) {
			if ( ! method_exists( $class_name, $method ) ) {
				return false;
			}
		}

		return true;
	}
}
