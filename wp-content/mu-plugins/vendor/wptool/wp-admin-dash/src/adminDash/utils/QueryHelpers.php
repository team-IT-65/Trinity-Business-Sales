<?php

namespace Wptool\adminDash\utils;

class QueryHelpers {

	/**
	 * Filters query string and remove unwanted query args:
	 * _method,rest_route
	 *
	 * @param string $query_string
	 *
	 * @return string
	 */
	public static function filter_query_args( $query_string ) {

		$args_to_remove = array(
			'_method',
			'rest_route',
		);

		$parsed_query = array();
		parse_str( $query_string, $parsed_query );

		foreach ( $args_to_remove as $arg ) {
			if ( isset( $parsed_query[ $arg ] ) ) {
				unset( $parsed_query[ $arg ] );
			}
		}

		return http_build_query( $parsed_query );

	}
}
