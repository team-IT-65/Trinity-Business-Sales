<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @since 1.0.0
 *
 * @package Wpsec
 * @subpackage Wpsec/core
 */

namespace Wpsec\twofa\Core;

use Wpsec\twofa\Services\AjaxProvider;

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package Wpsec
 * @subpackage Wpsec/core
 */
class TwoFactorAuthCore {

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string plugin_name The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string version The current version of the plugin.
	 */
	protected $version;

	/**
	 * The config parameters for capthca plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      array $config The config parameters for capthca plugin.
	 */
	protected $config;

	private $container;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale and
	 * the public-facing side of the site.
	 *
	 * @param string $plugin_name The name of the plugin.
	 * @since   1.0.0
	 */
	public function __construct( $plugin_name ) {

		if ( defined( 'WPSEC_WP_2FA_VERSION' ) ) {
			$this->version = WPSEC_WP_2FA_VERSION;
		} else {
			$this->version = '1.0.0';
		}

		$this->plugin_name = $plugin_name;

		$this->set_locale();

		add_action( 'login_init', array( $this, 'init_login_page_actions' ) );
		add_action( '2fa_container_booted', array( AjaxProvider::class, 'register_ajax_actions' ), PHP_INT_MIN, 1 );

		$this->container = WPJsonAPI::boot();

	}

	/**
	 * Register hook actions for login page
	 *
	 * @since   1.0.0
	 */
	public function init_login_page_actions() {
		add_action( 'login_enqueue_scripts', array( $this, 'enqueue_login_scripts' ) );
		$two_fa_form = new TwoFAForm( $this->version, $this->container );
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @return  string    The name of the plugin.
	 * @since   1.0.0
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @return  string    The version number of the plugin.
	 * @since   1.0.0
	 */
	public function get_version() {
		return $this->version;
	}

	/**
	 * Loads login scripts and styles
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function enqueue_login_scripts() {
		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'wpsec_2fa_login_header', plugin_dir_url( __FILE__ ) . '../web/js/login/loginHeader.js', false );
		wp_enqueue_script( 'wpsec_2fa_login_ajax', plugin_dir_url( __FILE__ ) . '../web/js/login/loginAjax.js', false );
		wp_enqueue_style( 'wpsec_2fa_login_style', plugin_dir_url( __FILE__ ) . '../web/css/login/wpsec_2fa_login.css', false );
		wp_enqueue_style( 'admin_styles', plugin_dir_url( __FILE__ ) . '../web/css/admin2fa.css', array(), $this->version );
		wp_localize_script(
			'wpsec_2fa_login_header',
			'wpsec_2fa_login_header_submit_value',
			array(
				'verify' => __( 'Verify', 'wpsec-wp-2fa' ),
			)
		);
		wp_localize_script(
			'wpsec_2fa_login_ajax',
			'ajax',
			array(
				'url' => admin_url( 'admin-ajax.php' ),
			)
		);
		wp_localize_script(
			'wpsec_2fa_login_header',
			'admin',
			array(
				'url' => admin_url(),
			)
		);
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the 2FATranslateEngine class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since   1.0.0
	 * @access  private
	 */
	private function set_locale() {
		add_action( 'plugins_loaded', array( TwoFactorTranslationEngine::class, 'load_plugin_textdomain' ) );
	}
}
