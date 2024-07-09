jQuery( document ).ready(
	function ($) {
		let all_table_tab                        = $( "#wpsec_2fa_2fa_table_all_tab" );
		let not_set_up_2fa_table_tab             = $( "#wpsec_2fa_2fa_not_set_up_tab" );
		let search_button                        = $( "#wpsec_2fa_site_table_search" );
		let apply_top                            = $( "#wpsec_2fa_bulk_action_top_apply" );
		let apply_bottom                         = $( "#wpsec_2fa_bulk_action_bottom_apply" );
		let restart_2fa_submit                   = $( "#wpsec_2fa_restart_button" );
		let admin_2fa_dialog_wrapper             = $( "#wpsec_2fa_admin_dialogs" );
		let admin_2fa_dialogue_background        = $( "#wpsec_2fa_email_template_background" );
		let admin_2fa_restart_2fa_dialog         = $( "#wpsec_2fa_admin_restart_method_dialog_content" );
		let admin_2fa_restart_title              = $( "#wpsec_2fa_admin_restart_dialog_title" );
		let admin_restart_validation_method      = $( "#wpsec_2fa_restart_validation_method" );
		let admin_restart_validation_code        = $( "#wpsec_2fa_restart_validation_code" );
		let admin_restart_method_name_holder     = $( "#wpsec_2fa_restart_method_name" );
		let admin_restart_validation_code_holder = $( "#wpsec_2fa_restart_validation_code_value" );
		let admin_restart_method_single_user_id  = $( "#wpsec_2fa_restart_single_user_id" );

		let single_restart_actions = $( 'a[name="wpsec_2fa_table_single_restart_action[]"]' )
		for (let i = 0; i < single_restart_actions.length; i++ ) {
			let id = single_restart_actions[i].id;
			let a  = $( "#" + id );
			a.on(
				'click',
				function(){
					let data = [];
					data.push( a.attr( 'data' ) );
					admin_restart_method_single_user_id.val( JSON.stringify( data ) );
					open_admin_2fa_restart_dialog();
				}
			)
		}

		all_table_tab.on(
			'click',
			function() {
				set_action( 'wpsec_2fa_all_users' );
			}
		)

		not_set_up_2fa_table_tab.on(
			'click',
			function() {
				set_action( 'wpsec_2fa_not_set_up_users' );
			}
		)

		search_button.on(
			'click',
			function() {
				let input = $( "#wpsec_2fa_user_search_input" );
				set_action( 'wpsec_2fa_search_user', input.val() );
			}
		)

		apply_top.on(
			'click',
			function() {
				open_admin_2fa_restart_dialog();
			}
		)

		apply_bottom.on(
			'click',
			function() {
				open_admin_2fa_restart_dialog();
			}
		)

		restart_2fa_submit.on(
			'click',
			function (){
				admin_restart_method_name_holder.val( admin_restart_validation_method.val() );
				admin_restart_validation_code_holder.val( admin_restart_validation_code.val() );
				set_bulk_action();
			}
		)

		admin_restart_validation_code.on(
			'keypress',
			function (e){
				if (e.which === 13) {
					admin_restart_method_name_holder.val( admin_restart_validation_method.val() );
					admin_restart_validation_code_holder.val( admin_restart_validation_code.val() );
					set_bulk_action();
				}
			}
		)

		/**
		 * Sets given action and data
		 */
		function set_action( action, data='' ) {
			let action_input = $( "#wpsec_2fa_site_table_action" );
			let data_input   = $( "#wpsec_2fa_site_table_data" );
			action_input.val( action );
			data_input.val( data );
			$( "#wpsec_2fa_site_table_submit" ).click();
		}

		/**
		 * Sets bulk action and data
		 */
		function set_bulk_action() {
			if (admin_restart_method_single_user_id.val() ) {
				set_action( 'wpsec_2fa_site_table_bulk_action', admin_restart_method_single_user_id.val() );
				return;
			}

			let bulk_action = $( '#bulk-action-selector-top' ).find( ":selected" ).val();
			if (bulk_action === '-1') {
				return;
			}

			let checkboxes = $( 'input[name="wpsec_2fa_users[]"]' );
			let ids        = checkboxes.filter( ":checked" ).map(
				function () {
					return this.value;
				}
			).get();

			if (ids) {
				set_action( 'wpsec_2fa_site_table_bulk_action', JSON.stringify( ids ) );
			}
		}

		/**
		 * Toggles provided admin dialog
		 */
		function open_admin_2fa_restart_dialog() {
			admin_2fa_dialog_wrapper.hasClass( 'wpsec_2fa_hidden' ) ? admin_2fa_dialog_wrapper.removeClass( 'wpsec_2fa_hidden' ) : '';
			admin_2fa_restart_2fa_dialog.hasClass( 'wpsec_2fa_hidden' ) ? admin_2fa_restart_2fa_dialog.removeClass( 'wpsec_2fa_hidden' ) : '';
			admin_2fa_dialogue_background.hasClass( 'wpsec_2fa_hidden' ) ? admin_2fa_dialogue_background.removeClass( 'wpsec_2fa_hidden' ) : '';
			admin_2fa_restart_title.hasClass( 'wpsec_2fa_hidden' ) ? admin_2fa_restart_title.removeClass( 'wpsec_2fa_hidden' ) : '';
		}
	}
);
