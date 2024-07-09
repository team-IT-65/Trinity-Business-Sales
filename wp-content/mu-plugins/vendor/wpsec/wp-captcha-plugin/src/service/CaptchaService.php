<?php

namespace Wpsec\captcha\service;

use WP_Error;
use WPaaS\Plugin;
use Wpsec\captcha\client\CaptchaAPIClient;
use Wpsec\captcha\constants\ToggleStatus;
use Wpsec\captcha\handlers\EventHandler;

class CaptchaService {

	/**
	 * Captcha API Client
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      CaptchaAPIClient captcha_api_client
	 */
	private $captcha_api_client;

	/** @var string captcha_api_host */
	private $captcha_api_host = '';

	/** @var array $host_per_env */
	private $host_per_env = array(
		'local'    => 'http://local.gasket.dev-godaddy.com:8080',
		'dev'      => 'https://captcha.wpsecurity.dev-godaddy.com',
		'test'     => 'https://captcha.wpsecurity.test-godaddy.com',
		'myh.test' => 'https://captcha.wpsecurity.test-godaddy.com',
		'prod'     => 'https://captcha.wpsecurity.godaddy.com',
	);

	/**
	 * Define the Captcha Service functionality of the plugin.
	 *
	 * @since   1.0.0
	 */
	public function __construct() {
		$current_env = class_exists( '\WPaaS\Plugin' ) ? Plugin::get_env() : 'prod';

		if ( ! array_key_exists( $current_env, $this->host_per_env ) ) {
			$current_env = 'prod';
		}

		$this->captcha_api_host   = $this->host_per_env[ $current_env ];
		$this->captcha_api_client = new CaptchaAPIClient( $this->captcha_api_host );
	}

	/**
	 * Send event type with meta data to the Captcha API
	 *
	 * @param   string  $event_type
	 * @param   array   $client_ips
	 * @param   string  $origin
	 * @param   array   $event_meta
	 *
	 * @return array|WP_Error
	 *
	 * @since   1.0.0
	 */
	public function send_event_to_api( $event_type, $client_ips, $event_meta = array() ) {
		return $this->captcha_api_client->send_event_type( $event_type, $client_ips, $event_meta );
	}

	/**
	 * Loads JS Captcha script that will get and render Captcha image & renders captcha HTML wrapper
	 *
	 * @since   1.0.0
	 */
	public function render_captcha_html_wrapper( $trigger = null ) {

		$captcha_script_endpoint_path = sprintf( '%s/api/v1/captcha/script', $this->captcha_api_host );

		if ( ! empty( $trigger ) ) {
			$captcha_script_endpoint_path = add_query_arg( array( 'trigger' => $trigger ), $captcha_script_endpoint_path );
		}
		wp_enqueue_script( 'wpsec_show_captcha', $captcha_script_endpoint_path, array(), null, true );
		?>
		<div hidden class="wpsec_captcha_wrapper">
			<div class="wpsec_captcha_image"></div>
			<label for="wpsec_captcha_answer">
			<?php esc_html_e( 'Type in the text displayed above', 'wpsec-wp-cp' ); ?>
			</label>
			<input type="text" class="wpsec_captcha_answer" name="wpsec_captcha_answer" value=""/>
		</div>
		<?php
	}

	/**
	 * Loads JS Captcha script that will get and render Captcha image & renders captcha HTML wrapper
	 *
	 * @since   1.0.0
	 */
	public function get_captcha_html_wrapper_login_form( $html, $args ) {

		$captcha_script_endpoint_path = add_query_arg( array( 'trigger' => EventHandler::WP_LOGIN_TRIGGER_POINT ), sprintf( '%s/api/v1/captcha/script', $this->captcha_api_host ) );

		add_action(
			'wp_enqueue_scripts',
			function() use ( $captcha_script_endpoint_path ) {
				wp_enqueue_script( 'wpsec_show_captcha', $captcha_script_endpoint_path, array(), null, true );
			}
		);

		$info = __( 'Type in the text displayed above', 'wpsec-wp-cp' );

		return
		$html .
		'<div hidden class="wpsec_captcha_wrapper">
            <div class="wpsec_captcha_image"></div>
            <label for="wpsec_captcha_answer">'
			. $info .
			'</label>
            <input type="text" class="wpsec_captcha_answer" name="wpsec_captcha_answer" value=""/>
        </div>';
	}

	/**
	 * Check if request is made by xmlrpc
	 *
	 * @return bool
	 */
	public function is_xmlrpc_request() {
		return defined( 'XMLRPC_REQUEST' ) && XMLRPC_REQUEST;
	}

	/**
	 * Check if captcha is enabled
	 * @return bool
	 */
	public function is_wpsec_captcha_enabled() {
		$wpsec_captcha_enabled = get_option( 'wpsec_captcha_enabled', ToggleStatus::ENABLED );
		return ToggleStatus::DISABLED !== $wpsec_captcha_enabled;
	}

	/**
	 * Check if captcha is enabled on comments
	 * @return bool
	 */
	public function is_wpsec_comment_captcha_enabled() {
		if ( $this->is_wpsec_captcha_enabled() ) {
			return get_option( 'wpsec_comment_captcha_enabled', ToggleStatus::ENABLED ) === ToggleStatus::ENABLED;
		}
		return false;
	}

	/**
	 * Check if captcha is disabled for login
	 * @return bool
	 */
	public function is_wpsec_login_captcha_enabled() {
		if ( $this->is_wpsec_captcha_enabled() ) {
			return get_option( 'wpsec_login_captcha_enabled', ToggleStatus::ENABLED ) === ToggleStatus::ENABLED;
		}
		return false;
	}
}
