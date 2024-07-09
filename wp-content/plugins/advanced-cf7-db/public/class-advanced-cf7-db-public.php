<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://www.vsourz.com
 * @since      1.0.0
 *
 * @package    Advanced_Cf7_Db
 * @subpackage Advanced_Cf7_Db/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Advanced_Cf7_Db
 * @subpackage Advanced_Cf7_Db/public
 * @author     vsourz Digital <mehul@vsourz.com>
 */
class Advanced_Cf7_Db_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Advanced_Cf7_Db_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Advanced_Cf7_Db_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_register_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/advanced-cf7-db-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Advanced_Cf7_Db_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Advanced_Cf7_Db_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_register_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/advanced-cf7-db-public.js', array( 'jquery' ), $this->version, false );

	}
	
	public function vsz_acf7_db_register_shortcode(){
		
		//Add short code for display ticket related information
		add_shortcode( 'acf7db', array( $this, 'vsz_acf7_db_shortcode_display_front' ));
	}
	
	//Display ticket related information 
	public function vsz_acf7_db_shortcode_display_front($atts, $content, $name){
		return require plugin_dir_path( __FILE__ ) . 'partials/vsz_acf7_db_shortcode_display_front.php';
	}
	
}
