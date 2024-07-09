<?php

namespace WPaaS;

/**
 * Class Plugin to mock behavior of GD system plugin
 *
 * @package WPaaS
 */
class Plugin {

	/**
	 * Mocker to return config for jenkins build
	 *
	 * @param $name
	 *
	 * @return mixed
	 */
	public static function config( $name ) {

		$config = [
			'd3.token'      => '53dacdceba099a43ed4fb45b491b16c4afb37d48',
			'd3.categories' => json_decode( file_get_contents( __DIR__ . '/d3-categories.json' ) ),
			'imageApi.url'  => 'http://isteam.wsimg.com/stock/',
		];

		return $config[ $name ];

	}

	/**
	 * Mocker to let jenkins know we are on the platform
	 *
	 * @return bool
	 */
	public static function is_wpaas() {

		return true;

	}

}
