jQuery( document ).ready(
	function($){

		const WPSEC_2FA_MAIL_ACTIVE        = 'wpsec_2fa_mail';
		const WPSEC_2FA_APP_ACTIVE         = 'wpsec_2fa_enabled_app';
		const WPSEC_2FA_YUBIKEY_ACTIVE     = 'wpsec_2fa_enabled_yubikey';
		const WPSEC_2FA_RESEND_MAIL_ACTION = 'wpsec_2fa_send_email';
		const WPSEC_2FA_VERIFY_LOGIN_CODE  = 'wpsec_2fa_verify_login_mail_code';

		let wpsec_2fa_login_app_check_conf         = $( "#wpsec_2fa_app_login" );
		let wpsec_2fa_mail_login                   = $( "#wpsec_2fa_email_login" );
		let wpsec_2fa_yubikey_login                = $( "#wpsec_2fa_yubikey_login" );
		let wpsec_2fa_login_mail_check_first_conf  = $( "#wpsec_2fa_login_mail_check_configuration" );
		let wpsec_2fa_login_mail_check_second_conf = $( "#wpsec_2fa_login_mail_check_second_conf" );
		let wpsec_2fa_login_app_auth_icon          = $( "#wpsec_2fa_login_app_auth_icon" );
		let wpsec_2fa_login_mail_auth_icon         = $( "#wpsec_2fa_login_mail_auth_icon" );
		let wpsec_2fa_login_yubikey_auth_icon      = $( "#wpsec_2fa_login_yubikey_auth_icon" );
		let wpsec_2fa_login_mail_next_button       = $( "#wpsec_2fa_mail_login_check_next_button" );
		let wpsec_2fa_login_title                  = $( "#wpsec_2fa_login_check_title" );
		let wpsec_2fa_login_mail_title             = $( "#wpsec_2fa_login_check_mail_title" );
		let wpsec_2fa_login_mail_back_button       = $( "#wpsec_2fa_mail_login_check_back_button" );
		let wpsec_2fa_login_icons                  = $( "#wpsec_2fa_login_icons" );
		let wpsec_2fa_login_nonce				   = $( "#wpsec_2fa_auth_nonce" );

		let wpsec_2fa_login_resend_mail           = $( "#wpsec_2fa_login_resend_mail" );
		let wpsec_2fa_login_provider              = $( "#wpsec_2fa_login_provider" );
		let wpsec_2fa_login_mail_submit_button    = $( "#wp_wpsec_2fa_login_mail_submit" );
		let wpsec_2fa_login_app_submit_button     = $( "#wp_wpsec_2fa_login_app_submit" );
		let wpsec_2fa_login_yubikey_submit_button = $( "#wp_wpsec_2fa_login_yubikey_submit" );
		let wpsec_2fa_login_code_error            = $( "#wpsec_2fa_2sv_login_code_error_message" );
		let wpsec_2fa_login_expired_code_error    = $( "#wpsec_2fa_2sv_login_code_expired_message" );
		let wpsec_2fa_login_app_code_check        = $( "#wpsec_2fa_login_app_code_check" );
		let wpsec_2fa_login_mail_code_check       = $( "#wpsec_2fa_login_mail_code_check" );
		let wpsec_2fa_login_yubikey_code_check    = $( "#wpsec_2fa_login_yubikey_code_check" );

		instantiate_slider_buttons( [ wpsec_2fa_login_app_check_conf, wpsec_2fa_yubikey_login ] );

		wpsec_2fa_login_app_auth_icon.on(
			'click',
			function() {
				let app_active = wpsec_2fa_login_provider.val();
				if (  app_active !== WPSEC_2FA_APP_ACTIVE ) {
					deactivate_login_header( wpsec_2fa_login_mail_auth_icon, wpsec_2fa_mail_login );
					deactivate_login_header( wpsec_2fa_login_yubikey_auth_icon, wpsec_2fa_yubikey_login );
					deactivate_mail_configuration( wpsec_2fa_login_mail_check_second_conf );
					activate_login_header( wpsec_2fa_login_app_auth_icon, wpsec_2fa_login_app_check_conf );
					wpsec_2fa_login_provider.val( WPSEC_2FA_APP_ACTIVE );
				}
			}
		)

		wpsec_2fa_login_mail_auth_icon.on(
			'click',
			function() {
				let app_active = wpsec_2fa_login_provider.val();
				if ( app_active !== WPSEC_2FA_MAIL_ACTIVE) {
					deactivate_login_header( wpsec_2fa_login_app_auth_icon, wpsec_2fa_login_app_check_conf );
					deactivate_login_header( wpsec_2fa_login_yubikey_auth_icon, wpsec_2fa_yubikey_login );
					activate_login_header( wpsec_2fa_login_mail_auth_icon, wpsec_2fa_mail_login );
					wpsec_2fa_login_provider.val( WPSEC_2FA_MAIL_ACTIVE );
				}
			}
		)

		wpsec_2fa_login_yubikey_auth_icon.on(
			'click',
			function() {
				let app_active = wpsec_2fa_login_provider.val();
				if ( app_active !== WPSEC_2FA_YUBIKEY_ACTIVE) {
					deactivate_login_header( wpsec_2fa_login_app_auth_icon, wpsec_2fa_login_app_check_conf );
					deactivate_login_header( wpsec_2fa_login_mail_auth_icon, wpsec_2fa_mail_login );
					deactivate_mail_configuration( wpsec_2fa_login_mail_check_second_conf );
					activate_login_header( wpsec_2fa_login_yubikey_auth_icon, wpsec_2fa_yubikey_login );
					wpsec_2fa_login_provider.val( WPSEC_2FA_YUBIKEY_ACTIVE );
				}
			}
		)

		wpsec_2fa_login_mail_next_button.on(
			'click',
			function() {
				deactivate_mail_configuration( wpsec_2fa_login_mail_check_first_conf );
				activate_mail_configuration( wpsec_2fa_login_mail_check_second_conf );
				! wpsec_2fa_login_title.hasClass( 'wpsec-2fa-hidden' ) ? wpsec_2fa_login_title.addClass( 'wpsec-2fa-hidden' ) : '';
				wpsec_2fa_login_mail_title.hasClass( 'wpsec-2fa-hidden' ) ? wpsec_2fa_login_mail_title.removeClass( 'wpsec-2fa-hidden' ) : '';
				! wpsec_2fa_login_icons.hasClass( 'wpsec-2fa-hidden' ) ? wpsec_2fa_login_icons.addClass( 'wpsec-2fa-hidden' ) : '';
				let user_id = $( "#wpsec_2fa_login_user_id" ).val();
				let nonce   = wpsec_2fa_login_nonce.val();
				ajax_call_with_response( WPSEC_2FA_RESEND_MAIL_ACTION, user_id, nonce );
			}
		)

		wpsec_2fa_login_mail_back_button.on(
			'click',
			function() {
				deactivate_mail_configuration( wpsec_2fa_login_mail_check_second_conf );
				activate_mail_configuration( wpsec_2fa_login_mail_check_first_conf );
				wpsec_2fa_login_title.hasClass( 'wpsec-2fa-hidden' ) ? wpsec_2fa_login_title.removeClass( 'wpsec-2fa-hidden' ) : '';
				! wpsec_2fa_login_mail_title.hasClass( 'wpsec-2fa-hidden' ) ? wpsec_2fa_login_mail_title.addClass( 'wpsec-2fa-hidden' ) : '';
				wpsec_2fa_login_icons.hasClass( 'wpsec-2fa-hidden' ) ? wpsec_2fa_login_icons.removeClass( 'wpsec-2fa-hidden' ) : '';
			}
		)

		wpsec_2fa_login_resend_mail.on(
			'click',
			function() {
				let user_id = $( "#wpsec_2fa_login_user_id" ).val();
				let nonce   = wpsec_2fa_login_nonce.val();
				ajax_call_with_response( WPSEC_2FA_RESEND_MAIL_ACTION, user_id, nonce );
			}
		)

		wpsec_2fa_login_mail_submit_button.on(
			'click',
			function() {
				! wpsec_2fa_login_code_error.hasClass( 'wpsec_2fa_hidden' ) ? wpsec_2fa_login_code_error.addClass( 'wpsec_2fa_hidden' ) : '';
				! wpsec_2fa_login_expired_code_error.hasClass( 'wpsec_2fa_hidden' ) ? wpsec_2fa_login_expired_code_error.addClass( 'wpsec_2fa_hidden' ) : '';
				call_2fa_code_check( WPSEC_2FA_MAIL_ACTIVE );
			}
		)

		wpsec_2fa_login_app_submit_button.on(
			'click',
			function() {
				call_2fa_code_check( WPSEC_2FA_APP_ACTIVE );
			}
		)

		wpsec_2fa_login_yubikey_submit_button.on(
			'click',
			function() {
				call_2fa_code_check( WPSEC_2FA_YUBIKEY_ACTIVE );
			}
		)

		wpsec_2fa_login_app_code_check.on(
			'keypress',
			function (e) {
				if (e.which === 13) {
					e.preventDefault();
					call_2fa_code_check( WPSEC_2FA_APP_ACTIVE );
				}
			}
		)

		wpsec_2fa_login_mail_code_check.on(
			'keypress',
			function (e) {
				if (e.which === 13) {
					e.preventDefault();
					! wpsec_2fa_login_code_error.hasClass( 'wpsec_2fa_hidden' ) ? wpsec_2fa_login_code_error.addClass( 'wpsec_2fa_hidden' ) : '';
					! wpsec_2fa_login_expired_code_error.hasClass( 'wpsec_2fa_hidden' ) ? wpsec_2fa_login_expired_code_error.addClass( 'wpsec_2fa_hidden' ) : '';
					call_2fa_code_check( WPSEC_2FA_MAIL_ACTIVE );
				}
			}
		)

		wpsec_2fa_login_yubikey_code_check.on(
			'keypress',
			function (e) {
				if (e.which === 13) {
					e.preventDefault();
					call_2fa_code_check( WPSEC_2FA_YUBIKEY_ACTIVE );
				}
			}
		)

		/**
		 * Activates login auth header
		 */
		function activate_login_header(wrapper, header_config) {
			let svg = wrapper.children()[0];
			svg.setAttribute( 'fill', '#2271B2' );
			svg.setAttribute( 'opacity', '1' );
			header_config.hasClass( 'wpsec-2fa-hidden' ) ? header_config.removeClass( 'wpsec-2fa-hidden' ) : '';
		}

		/**
		 * Deactivates login auth header
		 */
		function deactivate_login_header(wrapper, header_config) {
			let svg = wrapper.children()[0];
			svg.setAttribute( 'fill', 'black' );
			svg.setAttribute( 'opacity', '0.5' );
			! header_config.hasClass( 'wpsec-2fa-hidden' ) ? header_config.addClass( 'wpsec-2fa-hidden' ) : '';
		}

		/**
		 * Activates given mail configuration
		 */
		function activate_mail_configuration( configuration ) {
			configuration.hasClass( 'wpsec-2fa-hidden' ) ? configuration.removeClass( 'wpsec-2fa-hidden' ) : '';
		}

		/**
		 * Deactivates given mail configuration
		 */
		function deactivate_mail_configuration( configuration ) {
			! configuration.hasClass( 'wpsec-2fa-hidden' ) ? configuration.addClass( 'wpsec-2fa-hidden' ) : '';
		}

		/**
		 * Instantiate button functionality for screen slider
		 * @param parents Array of parent containers
		 */
		function instantiate_slider_buttons(parents){
			parents.forEach(
				function (parent) {
					parent.find( "button.wpsec_2fa_login_button_next" ).each(
						function () {
							$( this ).on(
								'click',
								function () {
									slider_buttons( 'next', parent )
								}
							)
						}
					)
					parent.find( "button.wpsec_2fa_login_button_back" ).each(
						function () {
							$( this ).on(
								'click',
								function () {
									slider_buttons( 'previous', parent );
								}
							)
						}
					)
				}
			)
		}

		/**
		 * Screen changing functionality for transitioning between screens with buttons
		 * @param direction Direction to scroll to, 'next' for the next slide, 'previous' for the previous slide
		 * @param parent Parent container
		 */
		function slider_buttons( direction = 'next', parent ) {
			console.log( parent );
			let currentSlide               = parent.find( "#wpsec_2fa_current_slide" );
			let wpsec_2fa_login_app_slides = parent.find( "div.ui-dialog-content" );
			let currentSlideNumber         = Number( currentSlide.val() );

			console.log( currentSlide, wpsec_2fa_login_app_slides );
			hideAllDialogs( wpsec_2fa_login_app_slides );
			if ( direction === 'next' ) {
				currentSlide.val( ! (currentSlideNumber + 1 > wpsec_2fa_login_app_slides.length - 1) ? currentSlideNumber += 1 : currentSlideNumber );
			}

			if ( direction === 'previous' ) {
				currentSlide.val( ! (currentSlideNumber - 1 < 0) ? currentSlideNumber -= 1 : currentSlideNumber );
			}

			if (currentSlideNumber > 0) {
				wpsec_2fa_login_title.addClass( 'wpsec-2fa-hidden' )
			} else {
				wpsec_2fa_login_title.removeClass( 'wpsec-2fa-hidden' )
			}

			$( wpsec_2fa_login_app_slides[currentSlideNumber] ).removeClass( 'wpsec-2fa-hidden' );
			wpsec_2fa_login_icons.hasClass( 'wpsec-2fa-hidden' ) && currentSlideNumber === 0 ? wpsec_2fa_login_icons.removeClass( 'wpsec-2fa-hidden' ) : wpsec_2fa_login_icons.addClass( 'wpsec-2fa-hidden' );
		}

		/**
		 * Hides all dialogs that are supplied in array
		 */
		function hideAllDialogs( dialogs ) {
			dialogs.each( function() { deactivate_mail_configuration( $( this ) )} );
		}

		/**
		 * Sends ajax call for checking 2fa code
		 */
		function call_2fa_code_check( provider ) {
			let mail_input    = wpsec_2fa_login_mail_code_check.val();
			let app_input     = wpsec_2fa_login_app_code_check.val();
			let yubikey_input = wpsec_2fa_login_yubikey_code_check.val();

			if ( (mail_input === "" && provider === WPSEC_2FA_MAIL_ACTIVE) || (app_input === "" && provider === WPSEC_2FA_APP_ACTIVE) || (yubikey_input === "" && provider === WPSEC_2FA_YUBIKEY_ACTIVE) ) {
				wpsec_2fa_login_code_error.removeClass( 'wpsec_2fa_hidden' );
				return;
			}

			let data = {
				"wpsec_2fa_login_provider" : provider,
				"wpsec_2fa_login_app_code_check" : app_input,
				"wpsec_2fa_login_mail_code_check" : mail_input,
				"wpsec_2fa_login_yubikey_code_check" : yubikey_input,
			};

			let user_id   = $( "#wpsec_2fa_login_user_id" ).val();
			let nonce     = wpsec_2fa_login_nonce.val();
			let json_data = JSON.stringify( data );
			ajax_call_with_response( WPSEC_2FA_VERIFY_LOGIN_CODE, json_data, nonce, user_id );
		}

	}
);
