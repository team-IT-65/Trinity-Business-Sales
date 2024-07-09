<?php

namespace Wptool\adminDash\utils;

use WPaaS\Plugin;

class BundlesPath {

	/** @var array $js_bundle_per_env */
	private static $js_bundle_per_env = array(
		'local'    => 'http://localhost:3000/static/js/bundle.js',
		'dev'      => 'https://d3rkwznpdz0oz2.cloudfront.net/main.js',
		'test'     => 'https://d2mt2uivr8ua2g.cloudfront.net/main.js',
		'myh.test' => 'https://d2mt2uivr8ua2g.cloudfront.net/main.js',
		'prod'     => 'https://ds9ywulh7jrls.cloudfront.net/main.js',
	);

	/** @var array $css_bundle_per_env */
	private static $css_bundle_per_env = array(
		'local'    => '',
		'dev'      => 'https://d3rkwznpdz0oz2.cloudfront.net/main.css',
		'test'     => 'https://d2mt2uivr8ua2g.cloudfront.net/main.css',
		'myh.test' => 'https://d2mt2uivr8ua2g.cloudfront.net/main.css',
		'prod'     => 'https://ds9ywulh7jrls.cloudfront.net/main.css',
	);

	/**
	 * Resolving current env.
	 *
	 * @return string
	 */
	public static function resolve_env() {
		return class_exists( '\WPaaS\Plugin' ) ? Plugin::get_env() : 'prod';
	}

	/**
	 * Returns URL or path to js bundle file.
	 *
	 * @return string
	 */
	public static function resolve_bundle_path_js() {

		if ( isset( self::$js_bundle_per_env[ self::resolve_env() ] ) ) {
			return self::$js_bundle_per_env[ self::resolve_env() ];
		}

		return self::$js_bundle_per_env['test'];
	}

	/**
	 * Returns URL or path to css bundle file.
	 *
	 * @return string
	 */
	public static function resolve_bundle_path_css() {

		if ( isset( self::$css_bundle_per_env[ self::resolve_env() ] ) ) {
			return self::$css_bundle_per_env[ self::resolve_env() ];
		}

		return self::$css_bundle_per_env['test'];
	}

}
