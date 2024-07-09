jQuery( document ).ready(
	function ($) {
		const WPSEC_2FA_RESEND_MAIL_ACTION         = 'wpsec_2fa_send_email';
		const WPSEC_2FA_VERIFY_MAIL_CODE_ACTION    = 'wpsec_2fa_verify_email_code';
		const WPSEC_2FA_VERIFY_APP_CODE_ACTION     = 'wpsec_2fa_verify_app_code';
		const WPSEC_2FA_VERIFY_YUBIKEY_CODE_ACTION = 'wpsec_2fa_verify_yubikey_code';
		const WPSEC_2FA_DISABLED_DIALOGUE          = 'wpsec_2fa_disabled_dialogue';

		let admin_2fa_dialog                   = $( "#wpsec_2fa_admin_dialogs" );
		let admin_2fa_dialogue_background      = $( "#wpsec_2fa_email_template_background" );
		let admin_2fa_dialogue_success_message = $( "#gritter-notice-wrapper" );
		let admin_2fa_dialogue_close           = $( "#wpsec_2fa_admin_dialogue_close" );
		let admin_2fa_disabled_first_text      = $( "#wpsec_2fa_disable_first_text" );
		let admin_2fa_disabled_second_text     = $( "#wpsec_2fa_disable_second_text" );
		let admin_2fa_is_disabled_dialogue     = $( "#wpsec_2fa_is_disabled_dialogue" );
		let admin_2fa_disable_cancel           = $( "#wpsec_2fa_disable_close_button" );

		let admin_2fa_mail_disable_dialogue_button = $( "#wpsec_2fa_disable_mail" );
		let admin_2fa_disable_content              = $( "#wpsec_2fa_admin_disable_dialog_content" );
		let admin_2fa_disable_title                = $( "#wpsec_2fa_admin_email_dialog_disable_title" );
		let admin_2fa_app_disable_title            = $( "#wpsec_2fa_admin_app_dialog_disable_title" );
		let admin_2fa_yubikey_disable_title        = $( "#wpsec_2fa_admin_yubikey_dialog_disable_title" );
		let admin_2fa_disable_which                = $( "#wpsec_2fa_disable_which" );

		let admin_2fa_email_enable_button = $( "#wpsec_2fa_enable_mail" );
		let admin_2fa_mail_title          = $( "#wpsec_2fa_admin_email_dialog_title" );
		let admin_2fa_mail_content        = $( "#wpsec_2fa_admin_email_dialog_content" );
		let admin_2fa_mail_close_button   = $( "#wpsec_2fa_email_close_button" );
		let admin_2fa_resend_mail         = $( "#wpsec_2fa_admin_email_resend" );

		let admin_2fa_app_enable_button          = $( "#wpsec_2fa_enable_app" );
		let admin_2fa_yubikey_enable_button      = $( "#wpsec_2fa_enable_yubikey" );
		let admin_2fa_app_scan_content           = $( "#wpsec_2fa_admin_app_scan_dialog_content" );
		let admin_2fa_app_title                  = $( "#wpsec_2fa_admin_app_dialog_title" );
		let admin_2fa_yubikey_title              = $( "#wpsec_2fa_admin_yubikey_dialog_title" );
		let admin_2fa_app_cancel_button          = $( "#wpsec_2fa_app_scan_close_button" );
		let admin_2fa_yubikey_cancel_button      = $( "#wpsec_2fa_yubikey_close_button" );
		let admin_restart_2fa_cancel_button      = $( "#wpsec_2fa_restart_methods_cancel_button" );
		let admin_2fa_app_next_button            = $( "#wpsec_2fa_app_next_button" );
		let admin_2fa_app_verify_content         = $( "#wpsec_2fa_admin_app_check_dialog_content" );
		let admin_2fa_app_back_button            = $( "#wpsec_2fa_app_back_button" );
		let admin_2fa_app_wrapper_content        = $( "#wpsec_2fa_admin_app_wrapper_content" );
		let admin_2fa_yubikey_wrapper_content    = $( "#wpsec_2fa_admin_yubikey_wrapper_content" );
		let admin_2fa_verify_mail_code_button    = $( "#wpsec_2fa_admin_verify_mail_code" );
		let admin_2fa_verify_app_code_button     = $( "#wpsec_2fa_admin_verify_app_code" );
		let admin_2fa_verify_yubikey_code_button = $( "#wpsec_2fa_admin_verify_yubikey_code" );
		let wpsec_2fa_admin_slides               = $( ".wpsec-yubikey-dialog" );
		let admin_2fa_nonce                      = $( "#wpsec_2fa_admin_nonce" );
		let email_code_sent                      = $( "#wpsec_2fa_mail_code_info" );
		let email_error_expired_code             = $( "#wpsec_2fa_mail_code_error_expired" );
		let email_error_code                     = $( "#wpsec_2fa_mail_code_error" );
		let admin_2fa_restart_dialog_content     = $( "#wpsec_2fa_admin_restart_method_dialog_content" );
		let admin_2fa_restart_title              = $( "#wpsec_2fa_admin_restart_dialog_title" );
		let admin_restart_method_single_user_id  = $( "#wpsec_2fa_restart_single_user_id" );

		let wpsec_2fa_admin_button_next = $( ".wpsec_2fa_admin_button_next" );
		let wpsec_2fa_admin_button_back = $( ".wpsec_2fa_admin_button_back" );
		let currentSlide                = $( "#wpsec_2fa_current_slide" );

		close_success_wrapper();

		set_up_mail_listeners();

		set_up_app_listeners();

		admin_2fa_dialogue_close.on(
			'click',
			function() {
				close_admin_2fa_setup_dialogue( admin_2fa_mail_content, admin_2fa_mail_title );
				close_admin_2fa_setup_dialogue( admin_2fa_disable_content, admin_2fa_disable_title );
				hideTitles( [admin_2fa_yubikey_disable_title, admin_2fa_app_disable_title] );
				close_admin_2fa_setup_dialogue( admin_2fa_app_wrapper_content, admin_2fa_app_title );
				close_admin_2fa_setup_dialogue( admin_2fa_yubikey_wrapper_content, admin_2fa_yubikey_title );
				close_admin_2fa_setup_dialogue( admin_2fa_restart_dialog_content, admin_2fa_restart_title );
				admin_restart_method_single_user_id.val( "" );
			}
		)

		admin_2fa_disable_cancel.on(
			'click',
			function (){
				close_admin_2fa_setup_dialogue( admin_2fa_disable_content, admin_2fa_disable_title );
				hideTitles( [admin_2fa_yubikey_disable_title, admin_2fa_app_disable_title] );
			}
		)

		admin_2fa_verify_mail_code_button.on(
			'click',
			function() {
				let code = $( "#wpsec_2fa_mail_code_check" ).val();
				if (code === "") {
					let mail_code_error = $( "#wpsec_2fa_mail_code_error" );
					mail_code_error.hasClass( 'wpsec_2fa_hidden' ) ? mail_code_error.removeClass( 'wpsec_2fa_hidden' ) : '';
					return;
				}
				! email_error_expired_code.hasClass( 'wpsec_2fa_hidden' ) ? email_error_expired_code.addClass( 'wpsec_2fa_hidden' ) : '';
				! email_error_code.hasClass( 'wpsec_2fa_hidden' ) ? email_error_code.addClass( 'wpsec_2fa_hidden' ) : '';
				ajax_call_with_response( WPSEC_2FA_VERIFY_MAIL_CODE_ACTION, code, admin_2fa_nonce.val() );
			}
		)

		admin_2fa_verify_app_code_button.on(
			'click',
			function() {
				let code = $( "#wpsec_2fa_app_code_check" ).val();
				ajax_call_with_response( WPSEC_2FA_VERIFY_APP_CODE_ACTION, code, admin_2fa_nonce.val() );
			}
		)

		admin_2fa_verify_yubikey_code_button.on(
			'click',
			function() {
				let code = $( "#wpsec_2fa_yubikey_code_check" ).val();
				ajax_call_with_response( WPSEC_2FA_VERIFY_YUBIKEY_CODE_ACTION, code, admin_2fa_nonce.val() );
			}
		)

		/**
		 * Adds listeners for mail set up actions
		 */
		function set_up_mail_listeners() {
			admin_2fa_email_enable_button.on(
				'click',
				function() {
					open_admin_2fa_setup_dialog( admin_2fa_mail_content, admin_2fa_mail_title, false , 'email' );
					! email_code_sent.hasClass( 'wpsec_2fa_hidden' ) ? email_code_sent.addClass( 'wpsec_2fa_hidden' ) : '';
					ajax_call_with_response( WPSEC_2FA_RESEND_MAIL_ACTION, '' , admin_2fa_nonce.val() );
				}
			)

			admin_2fa_mail_close_button.on(
				'click',
				function() {
					close_admin_2fa_setup_dialogue( admin_2fa_mail_content, admin_2fa_mail_title, null, null );
				}
			)

			admin_2fa_resend_mail.on(
				'click',
				function () {
					! email_code_sent.hasClass( 'wpsec_2fa_hidden' ) ? email_code_sent.addClass( 'wpsec_2fa_hidden' ) : '';
					ajax_call_with_response( WPSEC_2FA_RESEND_MAIL_ACTION, '', admin_2fa_nonce.val() );
				}
			)

			admin_2fa_mail_disable_dialogue_button.on(
				'click',
				function() {
					open_admin_2fa_setup_dialog(
						admin_2fa_disable_content,
						admin_2fa_disable_title,
						true,
						'email'
					);
				}
			)
		}

		/**
		 * Adds listeners for authenticator app set up actions
		 */
		function set_up_app_listeners() {
			admin_2fa_app_enable_button.on(
				'click',
				function () {
					let app_enabled = $( "#wpsec_2fa_is_app_enabled" ).val();
					if (Boolean( app_enabled )) {
						open_admin_2fa_setup_dialog(
							admin_2fa_disable_content,
							admin_2fa_app_disable_title,
							true,
							'app'
						);
					} else {
						open_admin_2fa_setup_dialog( admin_2fa_app_wrapper_content, admin_2fa_app_title, false, 'app' );
					}
				}
			)

			admin_2fa_yubikey_enable_button.on(
				'click',
				function () {
					let yubikey_enabled = $( "#wpsec_2fa_is_yubikey_enabled" ).val();
					if (Boolean( yubikey_enabled )) {
						open_admin_2fa_setup_dialog(
							admin_2fa_disable_content,
							admin_2fa_yubikey_disable_title,
							true,
							'yubikey'
						);
					} else {
						open_admin_2fa_setup_dialog( admin_2fa_yubikey_wrapper_content, admin_2fa_yubikey_title, false, 'yubikey' );
					}
				}
			)

			admin_2fa_app_cancel_button.on(
				'click',
				function() {
					close_admin_2fa_setup_dialogue( admin_2fa_app_wrapper_content, admin_2fa_app_title );
				}
			)

			admin_2fa_yubikey_cancel_button.on(
				'click',
				function() {
					close_admin_2fa_setup_dialogue( admin_2fa_yubikey_wrapper_content, admin_2fa_yubikey_title );
				}
			)

			admin_restart_2fa_cancel_button.on(
				'click',
				function() {
					close_admin_2fa_setup_dialogue( admin_2fa_restart_dialog_content, admin_2fa_restart_title );
					admin_restart_method_single_user_id.val( "" );
				}
			)

			admin_2fa_app_next_button.on(
				'click',
				function() {
					close_admin_2fa_setup_dialogue( admin_2fa_app_scan_content, admin_2fa_app_title );
					open_admin_2fa_setup_dialog( admin_2fa_app_verify_content, admin_2fa_app_title, false, 'app' );
				}
			)

			admin_2fa_app_back_button.on(
				'click',
				function() {
					close_admin_2fa_setup_dialogue( admin_2fa_app_verify_content, admin_2fa_app_title );
					open_admin_2fa_setup_dialog( admin_2fa_app_scan_content, admin_2fa_app_title, false, 'app' );
				}
			)

			wpsec_2fa_admin_button_next.each(
				function()
				{$( this ).on(
					'click',
					function() {
						slider_buttons( 'next' )
					}
				)
				}
			)
			wpsec_2fa_admin_button_back.each(
				function()
				{$( this ).on(
					'click',
					function(){
						slider_buttons( 'previous' );
					}
				)
				}
			)
		}

		/**
		 * Opens provided admin dialog
		 */
		function open_admin_2fa_setup_dialog(dialog_content, dialog_title, disabled_dialogue, disabled_dialogue_type) {
			admin_2fa_dialog.hasClass( 'wpsec_2fa_hidden' ) ? admin_2fa_dialog.removeClass( 'wpsec_2fa_hidden' ) : '';
			dialog_title.hasClass( 'wpsec_2fa_hidden' ) ? dialog_title.removeClass( 'wpsec_2fa_hidden' ) : '';
			if (disabled_dialogue) {
				admin_2fa_is_disabled_dialogue.val( WPSEC_2FA_DISABLED_DIALOGUE );
				admin_2fa_disabled_first_text.hasClass( 'wpsec_2fa_hidden' ) ? admin_2fa_disabled_first_text.removeClass( 'wpsec_2fa_hidden' ) : '';
				admin_2fa_disabled_second_text.hasClass( 'wpsec_2fa_hidden' ) ? admin_2fa_disabled_second_text.removeClass( 'wpsec_2fa_hidden' ) : '';
				switch (disabled_dialogue_type) {
					case 'email' :
						admin_2fa_disabled_first_text.html( wpsec_2fa_disable_texts.email_first_text );
						admin_2fa_disabled_second_text.html( wpsec_2fa_disable_texts.email_second_text );
						admin_2fa_disable_which.val( 'email' );
						break;
					case 'app':
						admin_2fa_disabled_first_text.html( wpsec_2fa_disable_texts.app_first_text );
						admin_2fa_disabled_second_text.html( wpsec_2fa_disable_texts.app_second_text );
						admin_2fa_disable_which.val( 'app' );
						break;
					case 'yubikey':
						admin_2fa_disabled_first_text.html( wpsec_2fa_disable_texts.yubikey_first_text );
						admin_2fa_disabled_second_text.html( wpsec_2fa_disable_texts.yubikey_second_text );
						admin_2fa_disable_which.val( 'yubikey' );
				}
			}

			dialog_content.hasClass( 'wpsec_2fa_hidden' ) ? dialog_content.removeClass( 'wpsec_2fa_hidden' ) : '';
			admin_2fa_dialogue_background.hasClass( 'wpsec_2fa_hidden' ) ? admin_2fa_dialogue_background.removeClass( 'wpsec_2fa_hidden' ) : '';
		}

		/**
		 * Closes provided admin dialog
		 */
		function close_admin_2fa_setup_dialogue(dialog_content, dialog_title) {
			! admin_2fa_dialog.hasClass( 'wpsec_2fa_hidden' ) ? admin_2fa_dialog.addClass( 'wpsec_2fa_hidden' ) : '';
			! dialog_title.hasClass( 'wpsec_2fa_hidden' ) ? dialog_title.addClass( 'wpsec_2fa_hidden' ) : '';
			! dialog_content.hasClass( 'wpsec_2fa_hidden' ) ? dialog_content.addClass( 'wpsec_2fa_hidden' ) : '';
			if (admin_2fa_is_disabled_dialogue.val() === WPSEC_2FA_DISABLED_DIALOGUE) {
				! admin_2fa_disabled_first_text.hasClass( 'wpsec_2fa_hidden' ) ? admin_2fa_disabled_first_text.removeClass( 'wpsec_2fa_hidden' ) : '';
				! admin_2fa_disabled_second_text.hasClass( 'wpsec_2fa_hidden' ) ? admin_2fa_disabled_second_text.removeClass( 'wpsec_2fa_hidden' ) : '';
			}

			! admin_2fa_dialogue_background.hasClass( 'wpsec_2fa_hidden' ) ? admin_2fa_dialogue_background.addClass( 'wpsec_2fa_hidden' ) : '';
		}

		/**
		 * Closes success message after 5 seconds
		 */
		function close_success_wrapper() {
			if ( ! admin_2fa_dialogue_success_message.hasClass( 'wpsec_2fa_hidden' ) ) {
				setTimeout(
					function() {
						admin_2fa_dialogue_success_message.addClass( 'wpsec_2fa_hidden' );
					},
					5000
				)
			}
		}

		/**
		 * Screen changing functionality for transitioning between screens with buttons
		 * @param direction Direction to scroll to, 'next' for the next slide, 'previous' for the previous slide
		 */
		function slider_buttons( direction = 'next' ) {
			let currentSlideNumber = Number( currentSlide.val() );
			hideAllDialogs( wpsec_2fa_admin_slides );
			if ( direction === 'next' ) {
				currentSlide.val( ! (currentSlideNumber + 1 > wpsec_2fa_admin_slides.length - 1) ? currentSlideNumber += 1 : currentSlideNumber );
			}

			if ( direction === 'previous' ) {
				currentSlide.val( ! (currentSlideNumber - 1 < 0) ? currentSlideNumber -= 1 : currentSlideNumber );
			}

			$( wpsec_2fa_admin_slides[currentSlideNumber] ).removeClass( 'wpsec_2fa_hidden' );
		}

		/**
		 * Hides all dialogs that are supplied in array
		 */
		function hideAllDialogs( dialogs ) {
			dialogs.each( function() { deactivate_configuration( $( this ) )} );
		}

		/**
		 * Deactivates given mail configuration
		 */
		function deactivate_configuration( configuration ) {
			! configuration.hasClass( 'wpsec_2fa_hidden' ) ? configuration.addClass( 'wpsec_2fa_hidden' ) : '';
		}

		function hideTitles( titles ) {
			titles.forEach(
				function (title){
					! title.hasClass( 'wpsec_2fa_hidden' ) ? title.addClass( 'wpsec_2fa_hidden' ) : '';
				}
			)
		}

	}
);
