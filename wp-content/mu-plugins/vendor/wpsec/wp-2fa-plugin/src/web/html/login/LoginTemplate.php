<?php

namespace Wpsec\twofa\web\html\login;

use Wpsec\twofa\Constants\GoogleAuthenticatorConstants;
use Wpsec\twofa\Constants\MailAuthConstants;
use Wpsec\twofa\Constants\YubikeyAuthConstants;
use Wpsec\twofa\Core\TwoFAForm;

/**
 * Login Template.
 *
 * @package Wpsec
 * @subpackage Wpsec/web/html/login
 */
class LoginTemplate {

	/**
	 * Render 2fa login template.
	 *
	 * @param string $site_url
	 * @param string $site_name
	 * @param string $user_id
	 * @param string $login_nonce
	 * @param string $redirect_to
	 * @param array $user_active_2fa
	 * @param array $admin_active_2fa
	 *
	 * @since 1.0.0
	 */
	public static function render( $site_url, $site_name, $user_id, $login_nonce, $redirect_to, $user_active_2fa, $admin_active_2fa, $qr_callback ) {
		?>
			<?php $app_method_activated_by_admin = in_array( GoogleAuthenticatorConstants::AUTH_ACTIVE, $admin_active_2fa, true ); ?>
			<?php $yubikey_method_activated_by_admin = in_array( YubikeyAuthConstants::AUTH_ACTIVE, $admin_active_2fa, true ); ?>

		<?php

		if ( in_array( MailAuthConstants::AUTH_ACTIVE, $user_active_2fa, true ) ) {
			$key = array_search( MailAuthConstants::AUTH_ACTIVE, $user_active_2fa, true );
			unset( $user_active_2fa[ $key ] );
		}

		if ( empty( $admin_active_2fa ) ) {

			//Admin did not enable anything, render just mail
			$render_app_icon     = false;
			$render_yubikey_icon = false;

			$render_app_body     = false;
			$render_yubikey_body = false;

		} elseif ( empty( $user_active_2fa ) ) {

			//User did not activate anything, render admin enabled methods in order: Yubikey,App
			$render_app_icon     = in_array( GoogleAuthenticatorConstants::AUTH_ACTIVE, $admin_active_2fa, true );
			$render_yubikey_icon = in_array( YubikeyAuthConstants::AUTH_ACTIVE, $admin_active_2fa, true );

			if ( $render_yubikey_icon ) {
				$render_app_body     = false;
				$render_yubikey_body = true;
			} else {
				$render_app_body     = true;
				$render_yubikey_body = false;
			}
		} else {

			//Admin has enabled Yubikey or App, User has active Yubikey or App
			$active_intersect = array_intersect( $admin_active_2fa, $user_active_2fa );

			if ( empty( $active_intersect ) ) {

				if ( in_array( GoogleAuthenticatorConstants::AUTH_ACTIVE, $admin_active_2fa, true ) ) {
					$render_app_body     = true;
					$render_app_icon     = true;
					$render_yubikey_icon = false;
					$render_yubikey_body = false;
				} else {
					$render_app_body     = false;
					$render_app_icon     = false;
					$render_yubikey_body = true;
					$render_yubikey_icon = true;
				}
			} else {

				if ( in_array( YubikeyAuthConstants::AUTH_ACTIVE, $active_intersect, true ) && in_array( GoogleAuthenticatorConstants::AUTH_ACTIVE, $active_intersect, true ) ) {
					$render_app_body     = false;
					$render_yubikey_icon = true;
					$render_yubikey_body = true;
					$render_app_icon     = true;
				} elseif ( in_array( YubikeyAuthConstants::AUTH_ACTIVE, $active_intersect, true ) ) {
					$render_app_body     = false;
					$render_yubikey_icon = true;
					$render_yubikey_body = true;
					$render_app_icon     = false;
				} else {
					$render_app_body     = true;
					$render_yubikey_icon = false;
					$render_yubikey_body = false;
					$render_app_icon     = true;
				}
			}
		}
		?>
		<div id="login-wrapper">
			<div id='wpsec_2fa_2sv_login_code_error_message' class="error wpsec_2fa_hidden">
				<p>
					<?php
					/* translators: %s: search term */
					printf( __( '%1$sError%2$s : 2SV code is incorrect', 'wpsec-wp-2fa' ), '<strong>', '</strong>' );
					?>
				</p>
			</div>
			<div id='wpsec_2fa_2sv_login_code_expired_message' class="error wpsec_2fa_hidden">
				<p>
					<?php
					/* translators: %s: search term */
					printf( __( '%1$sError%2$s : 2SV code has expired.', 'wpsec-wp-2fa' ), '<strong>', '</strong>' );
					?>
				</p>
			</div>
			<form name="validate_2fa_form" id="wpsec_2fa_login_form"
				action="<?php echo esc_url( TwoFAForm::login_url( array( 'action' => 'wpsec_2fa_validate_2fa' ), 'login_post' ) ); ?>"
				method="post" autocomplete="off">
				<p class="wpsec_2fa_login_text">
					<strong><?php echo empty( $user_active_2fa ) ? __( 'Set up 2-step verification (2SV) code', 'wpsec-wp-2fa' ) : __( 'Enter 2-step verification (2SV) code', 'wpsec-wp-2fa' ); ?></strong>
				</p>
				<p class="wpsec_2fa_login_text" id="wpsec_2fa_login_check_title">
					<?php echo __( 'Choose the 2SV option you prefer.', 'wpsec-wp-2fa' ); ?>
				</p>
				<p class="wpsec_2fa_login_text wpsec-2fa-hidden" id="wpsec_2fa_login_check_mail_title">
					<?php echo __( "Check your WordPress user's email address for a 6-digit verification code. Enter the code below, then select Verify.", 'wpsec-wp-2fa' ); ?>
					<a href="#" id="wpsec_2fa_login_resend_mail"><?php echo __( 'Resend email', 'wpsec-wp-2fa' ); ?></a>
				</p>

				<div id="wpsec_2fa_login_icons">
					<p id="wpsec_2fa_login_yubikey_auth_icon" class="wpsec_2fa_login_icon
						<?php
						if ( ! $render_yubikey_icon ) {
							echo 'wpsec-2fa-hidden';
						}
						?>
						">
						<svg width="45" height="51" viewBox="0 0 45 51"
							fill="#2271B2"
							xmlns="http://www.w3.org/2000/svg">
							<path d="M3.48047 39.8184V43H4.48242V39.8184L7.62305 34.7148H6.45117L3.98438 38.8867L1.51758 34.7148H0.339844L3.48047 39.8184ZM13.1895 43V37.1406H12.2637V40.5684C12.2637 41.6816 11.6543 42.2734 10.6992 42.2734C9.85547 42.2734 9.32812 41.7461 9.32812 40.709V37.1406H8.40234V40.832C8.40234 42.291 9.19336 43.0996 10.4473 43.0996C11.2148 43.0996 11.8828 42.7305 12.2637 42.0801V43H13.1895ZM15.1758 43H16.1016V41.9043C16.4707 42.666 17.2148 43.0996 18.0996 43.0996C19.6875 43.0996 20.6133 41.7812 20.6133 40.0703C20.6133 38.3535 19.6875 37.041 18.0879 37.041C17.2031 37.041 16.4707 37.4746 16.1016 38.2305V34.7148H15.1758V43ZM17.8711 37.8613C19.0371 37.8613 19.6406 38.8281 19.6406 40.0703C19.6406 41.3125 19.0371 42.2793 17.8711 42.2793C16.7051 42.2793 16.0254 41.3242 16.0254 40.0645C16.0254 38.8164 16.7051 37.8613 17.8711 37.8613ZM22.6113 35.8398C22.9395 35.8398 23.2031 35.5762 23.2031 35.2539C23.2031 34.9316 22.9395 34.6621 22.6113 34.6621C22.2832 34.6621 22.0254 34.9316 22.0254 35.2539C22.0254 35.5762 22.2832 35.8398 22.6113 35.8398ZM22.1484 37.1406V43H23.0742V37.1406H22.1484ZM26.291 34.7148H25.3184V43H26.291V40.6797L28.2715 38.6582L31.0664 43H32.2734L28.9277 37.9844L32.127 34.7148H30.8496L26.291 39.4609V34.7148ZM35.9414 43.0996C36.9023 43.0996 37.5938 42.7949 38.2441 42.1035L37.5996 41.5293C37.1133 42.0566 36.6328 42.2617 35.9648 42.2617C34.9219 42.2617 34.2129 41.6289 34.125 40.3105H38.3613C38.3906 40.1465 38.4082 39.9883 38.4082 39.7949C38.4082 38.3594 37.5703 37.041 35.8477 37.041C34.2422 37.041 33.1465 38.2773 33.1465 40.0996C33.1465 41.916 34.2305 43.0996 35.9414 43.0996ZM35.8301 37.8379C36.8379 37.8379 37.4648 38.5352 37.4883 39.6191H34.1367C34.2598 38.4414 34.957 37.8379 35.8301 37.8379ZM40.4238 45.5078H41.4434L44.6895 37.1406H43.7051L41.9297 41.8047L40.0547 37.1406H39L41.4609 42.9824L40.4238 45.5078Z"/>
							<path d="M26 18.9999C26.1978 18.9999 26.3911 18.9413 26.5556 18.8314C26.72 18.7215 26.8482 18.5653 26.9239 18.3826C26.9996 18.1999 27.0194 17.9988 26.9808 17.8048C26.9422 17.6109 26.847 17.4327 26.7071 17.2928C26.5673 17.153 26.3891 17.0577 26.1951 17.0192C26.0011 16.9806 25.8 17.0004 25.6173 17.0761C25.4346 17.1517 25.2784 17.2799 25.1685 17.4444C25.0586 17.6088 25 17.8022 25 17.9999C25 18.2652 25.1054 18.5195 25.2929 18.707C25.4804 18.8946 25.7348 18.9999 26 18.9999ZM27 4.99994H25V3.87494C24.9671 3.10139 24.6366 2.37047 24.0776 1.83477C23.5186 1.29907 22.7742 1 22 1C21.2258 1 20.4814 1.29907 19.9224 1.83477C19.3634 2.37047 19.0329 3.10139 19 3.87494V4.99994H17V12.9999H27V4.99994ZM21 3.87494C21.0324 3.63272 21.1517 3.41051 21.3356 3.24958C21.5195 3.08865 21.7556 2.99995 22 2.99995C22.2444 2.99995 22.4805 3.08865 22.6644 3.24958C22.8483 3.41051 22.9676 3.63272 23 3.87494V4.99994H21V3.87494ZM25 10.9999H19V6.99994H25V10.9999ZM13 13.9999V21.9999H31V13.9999H13ZM29 19.9999H15V15.9999H29V19.9999ZM18 18.9999C18.1978 18.9999 18.3911 18.9413 18.5556 18.8314C18.72 18.7215 18.8482 18.5653 18.9239 18.3826C18.9996 18.1999 19.0194 17.9988 18.9808 17.8048C18.9422 17.6109 18.847 17.4327 18.7071 17.2928C18.5673 17.153 18.3891 17.0577 18.1951 17.0192C18.0011 16.9806 17.8 17.0004 17.6173 17.0761C17.4346 17.1517 17.2784 17.2799 17.1685 17.4444C17.0586 17.6088 17 17.8022 17 17.9999C17 18.2652 17.1054 18.5195 17.2929 18.707C17.4804 18.8946 17.7348 18.9999 18 18.9999ZM23.3333 18.9999C23.5311 18.9999 23.7244 18.9413 23.8889 18.8314C24.0533 18.7215 24.1815 18.5653 24.2572 18.3826C24.3329 18.1999 24.3527 17.9988 24.3141 17.8048C24.2755 17.6109 24.1803 17.4327 24.0404 17.2928C23.9006 17.153 23.7224 17.0577 23.5284 17.0192C23.3344 16.9806 23.1334 17.0004 22.9506 17.0761C22.7679 17.1517 22.6117 17.2799 22.5018 17.4444C22.392 17.6088 22.3333 17.8022 22.3333 17.9999C22.3333 18.2652 22.4387 18.5195 22.6262 18.707C22.8137 18.8946 23.0681 18.9999 23.3333 18.9999ZM20.6667 18.9999C20.8645 18.9999 21.0578 18.9413 21.2223 18.8314C21.3867 18.7215 21.5149 18.5653 21.5906 18.3826C21.6663 18.1999 21.6861 17.9988 21.6475 17.8048C21.6089 17.6109 21.5136 17.4327 21.3738 17.2928C21.2339 17.153 21.0558 17.0577 20.8618 17.0192C20.6678 16.9806 20.4667 17.0004 20.284 17.0761C20.1013 17.1517 19.9451 17.2799 19.8352 17.4444C19.7253 17.6088 19.6667 17.8022 19.6667 17.9999C19.6667 18.2652 19.7721 18.5195 19.9596 18.707C20.1471 18.8946 20.4015 18.9999 20.6667 18.9999Z"/>
						</svg>
					</p>
					<p id="wpsec_2fa_login_app_auth_icon" class="wpsec_2fa_login_icon
						<?php
						if ( ! $render_app_icon ) {
							echo 'wpsec-2fa-hidden';
						}
						?>
						">
						<svg width="50" height="51" viewBox="0 0 50 51"
							opacity="<?php echo $render_yubikey_icon ? '0.5' : '1'; ?>"
							fill="<?php echo $render_yubikey_icon ? 'black' : '#2271B2'; ?>"
							xmlns="http://www.w3.org/2000/svg">
							<path d="M4.8457 34.7148H3.76758L0.46875 43H2.03906L2.72461 41.2012H5.85938L6.53906 43H8.14453L4.8457 34.7148ZM4.28906 36.8184L5.43164 39.9297H3.1582L4.28906 36.8184ZM14.1445 43V37.1406H12.7148V40.4043C12.7148 41.4004 12.2285 41.8574 11.4141 41.8574C10.752 41.8574 10.2773 41.3945 10.2773 40.4805V37.1406H8.84766V40.6562C8.84766 42.2441 9.69727 43.1172 10.9863 43.1172C11.6543 43.1172 12.2754 42.8242 12.7148 42.3145V43H14.1445ZM17.8945 43.1172C18.2988 43.1172 18.7324 43.0234 18.9492 42.9414V41.6465C18.7559 41.7402 18.4336 41.8574 18.1348 41.8574C17.7598 41.8574 17.4727 41.6934 17.4727 41.1309V38.3125H18.9492V37.1406H17.4727V34.7207H16.0781V37.1406H15.1113V38.3125H16.0547V41.3828C16.0547 42.4492 16.7402 43.1172 17.8945 43.1172ZM20.0508 34.7148V43H21.4805V39.7363C21.4805 38.7402 21.9668 38.2832 22.7812 38.2832C23.4434 38.2832 23.918 38.7461 23.918 39.6602V43H25.3477V39.4844C25.3477 37.8965 24.498 37.0234 23.209 37.0234C22.541 37.0234 21.9199 37.3164 21.4805 37.8262V34.7148H20.0508ZM33.4277 34.7148H32.3496L29.0508 43H30.6211L31.3066 41.2012H34.4414L35.1211 43H36.7266L33.4277 34.7148ZM32.8711 36.8184L34.0137 39.9297H31.7402L32.8711 36.8184ZM37.6055 45.4258H39.0352V42.4082C39.4395 42.9121 40.0957 43.1172 40.7109 43.1172C42.3047 43.1172 43.248 41.8164 43.248 40.0703C43.248 38.3066 42.3047 37.0234 40.6875 37.0234C40.0605 37.0234 39.4395 37.2285 39.0352 37.7207V37.1406H37.6055V45.4258ZM40.3301 38.2773C41.3145 38.2773 41.7598 39.0625 41.7598 40.0703C41.7598 41.0664 41.3145 41.8633 40.3301 41.8633C39.3457 41.8633 38.9121 41.0781 38.9121 40.0703C38.9121 39.0508 39.3457 38.2773 40.3301 38.2773ZM44.5078 45.4258H45.9375V42.4082C46.3418 42.9121 46.998 43.1172 47.6133 43.1172C49.207 43.1172 50.1504 41.8164 50.1504 40.0703C50.1504 38.3066 49.207 37.0234 47.5898 37.0234C46.9629 37.0234 46.3418 37.2285 45.9375 37.7207V37.1406H44.5078V45.4258ZM47.2324 38.2773C48.2168 38.2773 48.6621 39.0625 48.6621 40.0703C48.6621 41.0664 48.2168 41.8633 47.2324 41.8633C46.248 41.8633 45.8145 41.0781 45.8145 40.0703C45.8145 39.0508 46.248 38.2773 47.2324 38.2773Z"/>
							<path fill-rule="evenodd" clip-rule="evenodd"
								d="M27 7H23V5H27V7ZM32 2V22H18V2H32ZM30 4H20V20H30V4ZM25 16.96C24.45 16.96 24 17.41 24 17.96C24 18.51 24.45 18.96 25 18.96C25.55 18.96 26 18.51 26 17.96C26 17.41 25.55 16.96 25 16.96Z"/>
						</svg>
					</p>
					<p id="wpsec_2fa_login_mail_auth_icon" class="wpsec_2fa_login_icon">
						<svg width="40" height="51" viewBox="0 0 40 51"
							opacity="<?php echo empty( $admin_active_2fa ) ? '1' : '0.5'; ?>"
							fill="<?php echo empty( $admin_active_2fa ) ? '#2271B2' : 'black'; ?>"
							xmlns="http://www.w3.org/2000/svg">
								<path d="M1.33594 34.7148V43H6.77344V42.0977H2.30859V39.2617H6.35742V38.3828H2.30859V35.5938H6.77344V34.7148H1.33594ZM8.47852 37.1406V43H9.4043V39.5664C9.4043 38.459 9.99023 37.8672 10.8984 37.8672C11.7305 37.8672 12.2051 38.3945 12.2051 39.4316V43H13.1367V39.5195C13.1367 38.4121 13.7344 37.8672 14.6309 37.8672C15.457 37.8672 15.9375 38.3945 15.9375 39.4316V43H16.8633V39.3086C16.8633 37.8379 16.084 37.041 14.8477 37.041C14.0039 37.041 13.3711 37.3691 12.9141 38.1602C12.6035 37.4336 11.9883 37.041 11.1797 37.041C10.4121 37.041 9.77344 37.4043 9.4043 38.0605V37.1406H8.47852ZM22.3711 41.8867L22.4355 43H23.209V39.5312C23.209 37.8965 22.3711 37.041 20.8887 37.041C19.8516 37.041 19.0957 37.4277 18.5039 38.2715L19.2188 38.793C19.5996 38.1777 20.1387 37.8613 20.8477 37.8613C21.8027 37.8613 22.3301 38.3711 22.3301 39.5898H20.7539C19.1074 39.5898 18.2754 40.2637 18.2754 41.3535C18.2754 42.4375 19.0488 43.0996 20.2793 43.0996C21.1875 43.0996 21.9141 42.6836 22.3711 41.8867ZM20.4609 42.3027C19.6582 42.3027 19.2246 41.9102 19.2246 41.3066C19.2246 40.6914 19.6934 40.2871 20.7598 40.2871H22.3301V40.5508C22.3301 41.3359 21.627 42.3027 20.4609 42.3027ZM25.5586 35.8398C25.8867 35.8398 26.1504 35.5762 26.1504 35.2539C26.1504 34.9316 25.8867 34.6621 25.5586 34.6621C25.2305 34.6621 24.9727 34.9316 24.9727 35.2539C24.9727 35.5762 25.2305 35.8398 25.5586 35.8398ZM25.0957 37.1406V43H26.0215V37.1406H25.0957ZM28.002 43H28.9277V34.7148H28.002V43Z"/>
								<path fill-rule="evenodd" clip-rule="evenodd" d="M5 4V20H25V4H5ZM23 14.06L19.93 12.01L23 9.99V14.06ZM23 6V7.6L15.05 12.83L7 7.32V6H23ZM10.3 12L7 14.2V9.75L10.3 12ZM7 18V16.61L12.08 13.23L15.03 15.25L18.12 13.22L23 16.46V18H7Z"/>

						</svg>
					</p>
				</div>

				<div id="wpsec_2fa_email_login" class="<?php echo empty( $admin_active_2fa ) ? '' : 'wpsec-2fa-hidden'; ?>">
					<?php LoginMailTemplate::render(); ?>
				</div>
				<div id="wpsec_2fa_app_login" class="<?php echo ! $render_app_body ? 'wpsec-2fa-hidden' : ''; ?>">
					<?php
					if ( $app_method_activated_by_admin && ! in_array( GoogleAuthenticatorConstants::AUTH_ACTIVE, $user_active_2fa, true ) ) {
						LoginAppNotActivatedTemplate::render( $user_id, $qr_callback );
					} elseif ( $app_method_activated_by_admin && in_array( GoogleAuthenticatorConstants::AUTH_ACTIVE, $user_active_2fa, true ) ) {
						LoginAppTemplate::render();
					}
					?>
				</div>
				<div id="wpsec_2fa_yubikey_login" class="<?php echo ! $render_yubikey_body ? 'wpsec-2fa-hidden' : ''; ?>">
					<?php
					if ( $yubikey_method_activated_by_admin && ! in_array( YubikeyAuthConstants::AUTH_ACTIVE, $user_active_2fa, true ) ) {
						LoginYubikeyNotActivatedTemplate::render();
					} elseif ( $yubikey_method_activated_by_admin && in_array( YubikeyAuthConstants::AUTH_ACTIVE, $user_active_2fa, true ) ) {
						LoginYubikeyTemplate::render();
					}
					?>
				</div>
				<input type='hidden' name='wpsec_2fa_auth_id' id='wpsec_2fa_auth_id'
					value='<?php echo esc_attr( $user_id ); ?>'/>
				<input type='hidden' name='wpsec_2fa_auth_nonce' id='wpsec_2fa_auth_nonce'
					value='<?php echo esc_attr( $login_nonce ); ?>'/>
				<input type='hidden' name='redirect_to' value='<?php echo esc_attr( $redirect_to ); ?>'/>
				<input type="hidden" name="wpsec_2fa_login_provider" id="wpsec_2fa_login_provider"
					value="<?php echo ( in_array( YubikeyAuthConstants::AUTH_ACTIVE, $admin_active_2fa, true ) ? YubikeyAuthConstants::AUTH_ACTIVE : ( in_array( GoogleAuthenticatorConstants::AUTH_ACTIVE, $admin_active_2fa, true ) ? GoogleAuthenticatorConstants::AUTH_ACTIVE : MailAuthConstants::AUTH_ACTIVE ) ); ?>">
				<input type="submit" name="swp_wpsec_2fa_login_submit" id="wp_wpsec_2fa_login_submit"
					class="button button-primary button-large wpsec-2fa-hidden">
				<input type='hidden' name='wpsec_2fa_login_user_id' id='wpsec_2fa_login_user_id'
					value='<?php echo $user_id; ?>'/>
			</form>

			<p id="backtoblog">
				<a href="<?php echo $site_url; ?>">
					<?php
					/* translators: %s: search term */
					printf( __( '← Go to %s', 'wpsec-wp-2fa' ), $site_name );
					?>
				</a>
			</p>
		</div>
		<?php
	}
}
