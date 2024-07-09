function ajax_call_no_response( action , data, nonce) {
	jQuery.ajax(
		{
			type:"POST",
			url: ajaxurl,
			data: {
				action: action,
				data: data,
				nonce: nonce,
			}
		}
	);
}

function ajax_call_with_response( action , data, nonce) {
	jQuery.ajax(
		{
			type:"POST",
			url: ajaxurl,
			data: {
				action: action,
				data: data,
				nonce: nonce,
			},
			success:function(data){
				jQuery( document ).ready(
					function ($) {
						if (data === 'failed_mail') {
							$( "#wpsec_2fa_mail_code_error" ).removeClass( 'wpsec_2fa_hidden' );
						} else if ( data === 'failed_app' || data === 'failed_yubikey' ) {
							$( "#wpsec_2fa_app_code_error" ).removeClass( 'wpsec_2fa_hidden' );
						} else if (data === 'failed_mail_template') {
							$( "#wpsec_2fa_mail_template_code_error_message" ).removeClass( 'wpsec_2fa_hidden' );
						} else if (data === 'success_email_template_save') {
							$( "#wpsec_2fa_mail_template_success_save_message" ).removeClass( 'wpsec_2fa_hidden' );
						} else if (data === 'email_template_mail_sent') {
							$( "#wpsec_2fa_mail_template_success_mail_message" ).removeClass( 'wpsec_2fa_hidden' );
						} else if (data === 'email_template_mail_not_sent') {
							$( "#wpsec_2fa_mail_template_mail_error_message" ).removeClass( 'wpsec_2fa_hidden' );
						} else if (data === 'admin_mail_sent') {
							$( "#wpsec_2fa_mail_code_info" ).removeClass( 'wpsec_2fa_hidden' );
						} else if (data === 'admin_mail_not_sent') {
							$( "#wpsec_2fa_mail_not_sent" ).removeClass( 'wpsec_2fa_hidden' );
						} else if (data === 'expired_code_failed_mail') {
							$( "#wpsec_2fa_mail_code_error_expired" ).removeClass( 'wpsec_2fa_hidden' );
						} else {
							location.reload();
						}
					}
				);

			}
		}
	);
}
