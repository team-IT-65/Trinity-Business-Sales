function ajax_call_no_response( action , data, nonce) {
	jQuery.ajax(
		{
			type:"POST",
			url: ajax.url,
			data: {
				action: action,
				data: data,
				nonce: nonce
			}
		}
	);
}

function ajax_call_with_response( action , data, nonce, user_id) {
	jQuery.ajax(
		{
			type:"POST",
			url: ajax.url,
			data: {
				action: action,
				data: data,
				nonce: nonce,
				user_id: user_id
			},
			success:function( data ) {
				jQuery( document ).ready(
					function ($) {
						if (data === 'login_error') {
							$( "#wpsec_2fa_2sv_login_code_error_message" ).removeClass( 'wpsec_2fa_hidden' );
						} else if (data === 'admin_mail_sent') {
							$( "#wpsec_2fa_mail_code_info" ).removeClass( 'wpsec_2fa_hidden' );
						} else if (data === 'expired_code_login_error') {
							$( "#wpsec_2fa_2sv_login_code_expired_message" ).removeClass( 'wpsec_2fa_hidden' );
						} else {
							top.location.replace( admin.url );
						}
					}
				);

			},
		}
	);
}
