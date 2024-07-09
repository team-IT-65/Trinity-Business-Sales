<?php

namespace Wpsec\twofa\web\html\login;

class LoginMailTemplate {



	/**
	 * Render login mail template.
	 *
	 * @since 1.0.0
	 */
	public static function render() {       ?>
		<div id="wpsec_2fa_login_mail_check_configuration">
			<div id="wpsec_2fa_login_vertical_line"></div>

			<p class="wpsec-2fa-text-align-center">
				<?php
				/* translators: %s: search term */
				printf( __( 'Select %1$sNext%2$s to get verification code by email.', 'wpsec-wp-2fa' ), '<strong>', '</strong>' );
				?>
			</p>

			<div id="wpsec_2fa_login_mail_first_check_conf">

				<div class="wpsec-2fa-display-flex one_button_only">
					<div class="wpsec_2fa_admin_progress_bar">
						<span class="wpsec_2fa_dot wpsec_2fa_dot_active wpsec_2fa_login_dot"></span>
						<span class="wpsec_2fa_dot"></span>
					</div>
					<button type="button" id="wpsec_2fa_mail_login_check_next_button"
							class="file-editor-warning-dismiss button button-primary">
						<?php echo __( 'Next', 'wpsec-wp-2fa' ); ?>
					</button>
				</div>
			</div>
		</div>

		<div id="wpsec_2fa_login_mail_check_second_conf" class="wpsec-2fa-hidden">
			<input type="text"
				class="wpsec_2fa_login_code_check"
				id="wpsec_2fa_login_mail_code_check"
				name="wpsec_2fa_login_mail_code_check"
				value="" placeholder="<?php echo __( '2SV Code...', 'wpsec-wp-2fa' ); ?>">

			<div id='wpsec_2fa_mail_code_info' class="wpsec_2fa_hidden">
				<p class="wpsec_2fa_info_icon">
					<?php echo __( '2SV code expires in 15 minutes', 'wpsec-wp-2fa' ); ?>
				</p>
			</div>
			<div class="wpsec-2fa-display-flex">
				<button type="button" id="wpsec_2fa_mail_login_check_back_button"
					class="file-editor-warning-dismiss button button-secondary">
					<?php echo __( 'Back', 'wpsec-wp-2fa' ); ?>
				</button>
				<div class="wpsec_2fa_admin_progress_bar">
					<span class="wpsec_2fa_dot wpsec_2fa_dot_active wpsec_2fa_login_dot"></span>
					<span class="wpsec_2fa_dot wpsec_2fa_dot_active"></span>
				</div>

				<input type="button" name="wp_wpsec_2fa_login_mail_submit" id="wp_wpsec_2fa_login_mail_submit"
					class="button button-primary button-large" value="<?php echo __( 'Verify', 'wpsec-wp-2fa' ); ?>">
			</div>
		</div>
		<?php
	}
}
