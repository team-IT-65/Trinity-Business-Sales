jQuery( document ).ready(
	function ($) {
		const EMAIL_TEMPLATE_ACTIONS = 'wpsec_2fa_email_template_actions';

		let email_template_background    = $( "#wpsec_2fa_email_template_background" );
		let email_template               = $( "#wpsec_2fa_email_settings_template" );
		let send_test_mail_button        = $( "#wpsec_2fa_send_test_mail_button" );
		let save_email_template_button   = $( "#wpsec_2fa_save_email_template_button" );
		let selected_roles_checkbox      = $( "#wpsec_2fa_roles_selected" );
		let all_roles_checkbox           = $( "#wpsec_2fa_roles_all" );
		let none_roles_checkbox          = $( "#wpsec_2fa_roles_none" );
		let email_template_cancel_button = $( "#wpsec_2fa_cancel_mail_template_button" );
		let admin_2fa_nonce              = $( "#wpsec_2fa_admin_nonce" );

		let email_template_mail_success_message = $( "#wpsec_2fa_mail_template_success_mail_message" );
		let email_template_save_success_message = $( "#wpsec_2fa_mail_template_success_save_message" );
		let email_template_mail_fail_message    = $( "#wpsec_2fa_mail_template_mail_error_message" );
		let email_template_code_fail_message    = $( "#wpsec_2fa_mail_template_code_error_message" );

		add_handlers_for_email_template_settings(
			email_template,
			email_template_background,
			send_test_mail_button,
			save_email_template_button,
			selected_roles_checkbox,
			all_roles_checkbox,
			none_roles_checkbox,
			email_template_cancel_button
		);

		/**
		 * Adds handlers for email template settings
		 */
		function add_handlers_for_email_template_settings(
			email_template,
			email_template_background,
			send_test_mail_button,
			save_email_template_button,
			selected_roles_checkbox,
			all_roles_checkbox,
			none_roles_checkbox,
			email_template_cancel_button
		) {
			if (email_template && email_template_background) {
				$( "#wpsec_2fa_manage_email_template_link" ).on(
					'click',
					function () {
						show_email_template_settings( email_template, email_template_background )
					}
				);

				$( "#wpsec_2fa_email_template_close_button" ).on(
					'click',
					function () {
						hide_email_template_settings( email_template, email_template_background )
					}
				);

				send_test_mail_button.on(
					'click',
					function () {
						set_email_template_action( 'send_test_email' );
					}
				)

				save_email_template_button.on(
					'click',
					function () {
						set_email_template_action( 'save_email_template' );
					}
				)

				selected_roles_checkbox.on(
					'change',
					function ( event ) {
						enable_selected_roles( event );
					}
				)

				all_roles_checkbox.on(
					'change',
					function () {
						disable_selected_roles();
					}
				)

				none_roles_checkbox.on(
					'change',
					function () {
						disable_selected_roles();
					}
				)

				email_template_cancel_button.on(
					'click',
					function () {
						hide_email_template_settings( email_template, email_template_background );
					}
				)

			}
		}

		/**
		 * Shows email template settings
		 */
		function show_email_template_settings(email_template, email_template_background) {
			email_template_background.hasClass( 'wpsec_2fa_hidden' ) ? email_template_background.removeClass( 'wpsec_2fa_hidden' ) : '';
			email_template.hasClass( 'wpsec_2fa_hidden' ) ? email_template.removeClass( 'wpsec_2fa_hidden' ) : '';
		}

		/**
		 * Hides email template settings
		 */
		function hide_email_template_settings(email_template, email_template_background) {
			email_template_background.hasClass( 'wpsec_2fa_hidden' ) ? '' : email_template_background.addClass( 'wpsec_2fa_hidden' );
			email_template.hasClass( 'wpsec_2fa_hidden' ) ? '' : email_template.addClass( 'wpsec_2fa_hidden' );
		}

		/**
		 * Sets email template action in POST request
		 */
		function set_email_template_action( action ) {
			let data = {
				"action" : action,
				"wpsec_2fa_email_from" : $( "#wpsec_2fa_email_from" ).val(),
				"wpsec_2fa_email_subject" : $( "#wpsec_2fa_email_subject" ).val(),
				"wpsec_2fa_email_body" : $( "#wpsec_2fa_email_body" ).val(),
			};

			let nonce     = admin_2fa_nonce.val();
			let json_data = JSON.stringify( data );

			close_email_template_messages();
			ajax_call_with_response( EMAIL_TEMPLATE_ACTIONS, json_data, nonce );
		}

		/**
		 * Enables selected roles div
		 */
		function enable_selected_roles( event ) {
			let list_of_roles_div = $( "#wpsec2fa_list_od_roles_div" );
			if ( event.target.checked ) {
				list_of_roles_div.hasClass( 'wpsec_2fa_disabled_div' ) ? list_of_roles_div.removeClass( 'wpsec_2fa_disabled_div' ) : '';
			}
		}

		/**
		 * Disables selected roles div
		 */
		function disable_selected_roles () {
			let list_of_roles_div = $( "#wpsec2fa_list_od_roles_div" );
			list_of_roles_div.hasClass( 'wpsec_2fa_disabled_div' ) ? '' : list_of_roles_div.addClass( 'wpsec_2fa_disabled_div' );
		}

		/**
		 * Closes all email template messages
		 */
		function close_email_template_messages() {
			! email_template_mail_success_message.hasClass( 'wpsec_2fa_hidden' ) ? email_template_mail_success_message.addClass( 'wpsec_2fa_hidden' ) : '';
			! email_template_save_success_message.hasClass( 'wpsec_2fa_hidden' ) ? email_template_save_success_message.addClass( 'wpsec_2fa_hidden' ) : '';
			! email_template_code_fail_message.hasClass( 'wpsec_2fa_hidden' ) ? email_template_code_fail_message.addClass( 'wpsec_2fa_hidden' ) : '';
			! email_template_mail_fail_message.hasClass( 'wpsec_2fa_hidden' ) ? email_template_mail_fail_message.addClass( 'wpsec_2fa_hidden' ) : '';
		}

	}
);
