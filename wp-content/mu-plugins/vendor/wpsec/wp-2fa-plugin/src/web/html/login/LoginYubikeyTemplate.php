<?php

namespace Wpsec\twofa\web\html\login;


/**
 * Login App Template.
 *
 * @package Wpsec
 * @subpackage Wpsec/web/html/login
 */
class LoginYubikeyTemplate {

	/**
	 * Render login yubikey template.
	 *
	 * @since 1.0.0
	 */
	public static function render() {
		?>
		<div id="wpsec_2fa_login_app_check_configuration">
			<input type="password" autocomplete=new-password
				class="wpsec_2fa_login_code_check"
				id="wpsec_2fa_login_yubikey_code_check"
				name="wpsec_2fa_login_yubikey_code_check"
				value="" placeholder="<?php echo __( 'Tap YubiKey...', 'wpsec-wp-2fa' ); ?>">

			<p class="wpsec-2fa-text-align-center">
				<?php
				/* translators: %s: search term */
				printf( __( 'Touch your YubiKey, then select %1$sVerify%2$s.', 'wpsec-wp-2fa' ), '<strong>', '</strong>' );
				?>
			</p>

			<br>
			<p>
				<input type="button" id="wp_wpsec_2fa_login_yubikey_submit" class="button button-primary button-large" value="<?php echo __( 'Verify', 'wpsec-wp-2fa' ); ?>">
			</p>
		</div>
		<?php
	}
}
