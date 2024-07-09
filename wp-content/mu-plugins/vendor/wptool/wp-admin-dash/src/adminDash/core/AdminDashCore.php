<?php

/**
* The file that defines the core plugin class
*
* A class definition that includes attributes and functions used across both the
* public-facing side of the site.
*
* @since      1.0.0
*
* @package    Wptool
* @subpackage Wptool\adminDash\core
*/

namespace Wptool\adminDash\core;

use Wptool\adminDash\constants\PageConstants;
use Wptool\adminDash\constants\ToggleStatus;
use Wptool\adminDash\constants\UserRole;
use Wptool\adminDash\constants\WhitelabelConstants;
use Wptool\adminDash\exceptions\AdminDashException;
use Wptool\adminDash\constants\APIConstants;
use Wptool\adminDash\utils\BundlesPath;


/**
* The core plugin class.
*
* This is used to define internationalization and
* public-facing site hooks.
*
* Also maintains the unique identifier of this plugin as well as the current
* version of the plugin.
*
* @since      1.0.0
* @package    Wptool
* @subpackage Wptool\adminDash\core
*/
class AdminDashCore {


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

		$this->version     = defined( 'WPTOOL_WP_AD_VERSION' ) ? WPTOOL_WP_AD_VERSION : '1.0.0';
		$this->plugin_name = $plugin_name;

		add_action( 'init', array( $this, 'init' ) );
		add_action( 'login_init', array( $this, 'init_gd_login' ) );
	}

	public function init() {

		if ( ! $this->is_admin() ) {
			return;
		}

		try {
			WPJsonAPI::boot();
			$this->add_menu_page_hooks();
		} catch ( AdminDashException $e ) {
			// Boot process is unsuccessful, in this case plugin will be loaded silently without any functionality
		}

	}

	/**
	 * Check if logged user is administrator
	 * @return bool
	 */
	private function is_admin() {

		$user = wp_get_current_user();

		if ( in_array( UserRole::ADMINISTRATOR, $user->roles, true ) ) {
			return true;
		}

			return false;
	}

	/**
	 * Adding hooks for menu page creation.
	 *
	 * @return void
	 */
	private function add_menu_page_hooks() {
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_style' ), PHP_INT_MAX, 0 );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_script' ), PHP_INT_MAX, 0 );
	}

	/**
	* Retrieve the version number of the plugin.
	*
	* @return  string    The version number of the plugin.
	* @since   1.0.0
	*
	*/
	public function get_version() {
		return $this->version;
	}

	/**
	 * Adding admin menu page and setting root container.
	 *
	 * @return void
	 */
	function admin_menu() {
		$data       = $this->whitelabel_data();
		$logo       = isset( $data['logo'] ) ? $data['logo'] : '';
		$menu_title = isset( $data['menu_title'] ) ? $data['menu_title'] : __( 'Hosting Overview', 'wptool-admin-dashboard' );

		add_menu_page( $menu_title, $menu_title, 'read', PageConstants::PAGE_NAME, array( $this, 'set_root_container' ), $logo, 0 );

	}

	/**
	 * Returns GoDaddy logo.
	 *
	 * @return string
	 */
	public function get_svg_logo_content() {

		$file = file_get_contents( plugin_dir_path( __FILE__ ) . '../../../assets/img/logo.svg' );

		if ( $file ) {
			return $file;
		}

		return '';
	}

	/**
	 * Returns logo and menu title for current user.
	 *
	 * @return array
	 */
	private function whitelabel_data() {

		$data               = array();
		$data['logo']       = '';
		$data['menu_title'] = __( 'Hosting Overview', 'wptool-admin-dashboard' );

		if ( defined( 'GD_RESELLER' ) ) {

			if ( GD_RESELLER === 1 ) {
				$data['logo'] = 'data:image/svg+xml;base64,' . base64_encode( $this->get_svg_logo_content() );

				$data['menu_title'] = WhitelabelConstants::GODADDY;
			} else {
				$data['logo']       = '';
				$data['menu_title'] = __( 'Hosting Overview', 'wptool-admin-dashboard' );
			}
		}

		return $data;
	}

	/**
	 * Setting root container with id mwp_dashboard_root.
	 *
	 * @return void
	 */
	function set_root_container() {
		?>
			<div id="wp-ad-wptool-admin">
			</div>
		<?php
	}

	/**
	 * Adding MPW Dashboard css bundle.
	 *
	 * @return void
	 */
	function admin_style() {
		if ( ! array_key_exists( 'page', $_GET ) || PageConstants::PAGE_NAME !== $_GET['page'] ) {
			return;
		}
		wp_enqueue_style( 'wp-ad-bundle-css', BundlesPath::resolve_bundle_path_css() );

		// growl styles taken from gd-system-plugin
		wp_enqueue_style( 'wpaas-gritter', \WPaaS\Plugin::assets_url( 'css/jquery-gritter.min.css' ), array(), \WPaaS\Plugin::version() );

	}

	/**
	 * Adding MPW Dashboard js bundle.
	 *
	 * @return void
	 */
	function admin_script() {
		if ( ! array_key_exists( 'page', $_GET ) || PageConstants::PAGE_NAME !== $_GET['page'] ) {
			return;
		}
		wp_enqueue_script( 'wp-ad-bundle-js', BundlesPath::resolve_bundle_path_js(), '', mt_rand( 10, 1000 ), true );

		$root_url = esc_url_raw( rest_url() );
		/* @see https://developer.wordpress.org/rest-api/using-the-rest-api/authentication/ */
		wp_localize_script(
			'wp-ad-bundle-js',
			'AdminDashboardAPIParams',
			array(
				'root'      => $root_url,
				'root_type' => stripos( $root_url, 'wp-json' ) !== false ? APIConstants::WP_JSON_URL : APIConstants::REST_ROUTE_URL,
				'nonce'     => wp_create_nonce( 'wp_rest' ),
				'env'       => BundlesPath::resolve_env(),
			)
		);
	}

	function init_gd_login() {
		add_filter(
			'wpaas_gd_sso_button_enabled',
			function() {
				return get_option( 'wpaas_gd_sso_button_enabled', ToggleStatus::ENABLED ) === ToggleStatus::ENABLED;
			}
		);

	}
}
