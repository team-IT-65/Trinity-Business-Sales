<?php

namespace Wptool\adminDash\utils;

use Wptool\adminDash\config\environments\ConfigDev;
use Wptool\adminDash\config\environments\ConfigProd;
use Wptool\adminDash\config\environments\ConfigLocal;
use Wptool\adminDash\config\environments\ConfigTest;
use Wptool\adminDash\exceptions\AdminDashException;
use Wptool\adminDash\exceptions\ConfigurationFailedToLoadException;

/**
 * Main configuration handler.
 */
class Configuration {

	/** @var string[] configuration map by env */
	protected static $config_envs = array(
		'prod'     => ConfigProd::class,
		'dev'      => ConfigDev::class,
		'test'     => ConfigTest::class,
		'myh.test' => ConfigTest::class,
		'local'    => ConfigLocal::class,
	);

	/** @var string[] configuration for current env */
	protected static $config = array();

	/** @var bool state of configuration*/
	private static $initialized = false;

	/** @var string state of configuration*/
	private static $env = null;

	/**
	 * Initializes the configuration.
	 * @throws AdminDashException
	 */
	public static function initialize() {
		self::$env = BundlesPath::resolve_env();

		self::load_config();

		self::$initialized = true;
	}

	/**
	 * Load config for environment.
	 *
	 * @return void
	 * @throws AdminDashException
	 */
	private static function load_config() {

		if ( empty( $env ) ) {
			self::$env = BundlesPath::resolve_env();
		}

		if ( isset( self::$config_envs[ self::$env ] ) && method_exists( self::$config_envs[ self::$env ], 'get_config' ) ) {

			self::$config = self::$config_envs[ self::$env ]::get_config();

		} elseif ( isset( self::$config_envs['prod'] ) && method_exists( self::$config_envs['prod'], 'get_config' ) ) {

			self::$config = self::$config_envs['prod']::get_config();

		} else {
			throw new ConfigurationFailedToLoadException( 'Configuration failed to boot for ' . self::$env );
		}
	}

	/**
	 * Gets the configuration value in key doted notation.
	 *
	 * @param string $key
	 * @param mixed|null $default default value to return
	 *
	 * @return mixed|null
	 */
	public static function get( $key, $default = null ) {

		if ( ! self::$initialized ) {
			self::initialize();
		}

		$array = self::$config;

		foreach ( explode( '.', $key ) as $segment ) {
			if ( ! array_key_exists( $segment, $array ) ) {
				return $default;
			}

			$array = $array[ $segment ];
		}

		return $array;
	}


	/**
	 * Checks if the key exists in the current configurations.
	 *
	 * @param string $key
	 *
	 * @return bool
	 */
	public static function has_key( $key ) {
		if ( ! self::$initialized ) {
			self::initialize();
		}

		return isset( self::$config[ $key ] );
	}
}
