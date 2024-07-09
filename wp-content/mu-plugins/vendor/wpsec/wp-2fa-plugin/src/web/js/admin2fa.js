jQuery( document ).ready(
	function($){
		$( "#wpsec_2fa_active_google" ).change(
			function () {
				$( "#wpsec_google_configuration" ).hasClass( 'wpsec_hidden' ) ? $( "#wpsec_google_configuration" ).removeClass( 'wpsec_hidden' ) : '';
				$( "#wpsec_mail_configuration" ).hasClass( "wpsec_hidden" ) ? '' : $( "#wpsec_mail_configuration" ).addClass( "wpsec_hidden" );
			}
		)

		$( "#wpsec_2fa_active_mail" ).change(
			function () {
				$( "#wpsec_google_configuration" ).hasClass( 'wpsec_hidden' ) ? '' : $( "#wpsec_google_configuration" ).addClass( 'wpsec_hidden' );
				$( "#wpsec_mail_configuration" ).hasClass( "wpsec_hidden" ) ? $( "#wpsec_mail_configuration" ).removeClass( "wpsec_hidden" ) : '';
			}
		)
	}
);
