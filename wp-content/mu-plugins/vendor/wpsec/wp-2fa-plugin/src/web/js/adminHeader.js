jQuery( document ).ready(
	function ($) {
		let site_settings_tab   = $( "#wpsec_2fa_settings_site_tab" );
		let my_2fa_tab          = $( "#wpsec_2fa_my_2fa_tab" );
		let site_settings_page  = $( "#wpsec_2fa_site" );
		let site_settings_table = $( "#wpsec_2fa_site_user_table" );
		let my_2fa_page         = $( "#wpsec_2fa_my_2fa_page" );
		var tabs                = [];
		tabs.push( site_settings_tab, my_2fa_tab );
		check_for_active_tab();

		site_settings_tab.on(
			'click',
			function() {
				activate_tab( site_settings_tab );
				my_2fa_page.hasClass( 'wpsec_2fa_hidden' ) ? '' : my_2fa_page.addClass( 'wpsec_2fa_hidden' );
				site_settings_page.hasClass( 'wpsec_2fa_hidden' ) ? site_settings_page.removeClass( 'wpsec_2fa_hidden' ) : '';
				site_settings_table.hasClass( 'wpsec_2fa_hidden' ) ? site_settings_table.removeClass( 'wpsec_2fa_hidden' ) : '';
			}
		)

		my_2fa_tab.on(
			'click',
			function() {
				activate_my_2fa_tab();
			}
		)

		/**
		 * Activates given tab
		 */
		function activate_tab( tab_to_activate ) {
			let tabs_to_deactivate = remove_elem_from_array( tabs, tab_to_activate );
			add_active_class( tab_to_activate, tabs_to_deactivate );
			tabs.push( tab_to_activate );
		}

		/**
		 * Adds "active" class to tab
		 */
		function add_active_class( tab_to_active, tabs_to_deactivate ) {
			tabs_to_deactivate.forEach(
				function( tab ) {
					tab.hasClass( 'active' ) ? tab.removeClass( 'active' ) : '';
				}
			);

			tab_to_active.hasClass( 'active' ) ? '' : tab_to_active.addClass( 'active' );
		}

		/**
		 * Removes given elem from given array
		 */
		function remove_elem_from_array( array, elem ) {
			const index = array.indexOf( elem );
			if (index > -1) {
				array.splice( index, 1 );
			}

			return array;
		}

		/**
		 * Checks for previous opened tab
		 */
		function check_for_active_tab() {
			if (wpsec_2fa_current_admin_tab.current_tab === 'wpsec_2fa_my2fa_page') {
				activate_my_2fa_tab();
			}
		}

		/**
		 * Activates My 2FA tab
		 */
		function activate_my_2fa_tab() {
			activate_tab( my_2fa_tab );
			my_2fa_page.hasClass( 'wpsec_2fa_hidden' ) ? my_2fa_page.removeClass( 'wpsec_2fa_hidden' ) : '';
			site_settings_page.hasClass( 'wpsec_2fa_hidden' ) ? '' : site_settings_page.addClass( 'wpsec_2fa_hidden' );
			site_settings_table.hasClass( 'wpsec_2fa_hidden' ) ? '' : site_settings_table.addClass( 'wpsec_2fa_hidden' );
		}
	}
);
