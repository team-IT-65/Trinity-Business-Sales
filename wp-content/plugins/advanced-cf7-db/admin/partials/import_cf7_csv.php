<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) {
	die('Un-authorized access!');
}

/**
 * Detect plugin. For use in Admin area only.
 */
include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

//Check contact form class exist or not
if(!is_plugin_active('contact-form-7/wp-contact-form-7.php')){
	?><div class="notice error is-dismissible">
		<p><?php esc_html_e('Please activate Contact Form plugin first.',VSZ_CF7_TEXT_DOMAIN); ?></p>
	</div><?php
}
else if(defined('WPCF7_VERSION') && WPCF7_VERSION < '4.6'){
	?><div class="notice error is-dismissible">
		<p><?php esc_html_e('Please update latest version for Contact Form plugin first.',VSZ_CF7_TEXT_DOMAIN); ?></p>
	</div><?php
}
else{
	//Enqueue plugin CSS in file
	wp_enqueue_style('vsz-cf7-db-admin-css');

	wp_enqueue_script('advanced_cf7_db_admin_js');
	wp_enqueue_script('datepicker_min_js');

	//Get all existing contact form list
	$form_list = vsz_cf7_get_the_form_list();
	$url = '';
	$fid = '';

	//Get selected form Id value
	if(isset($_GET['import_cf7_id']) && !empty($_GET['import_cf7_id'])){

		$fid = intval(sanitize_text_field($_GET['import_cf7_id']));
		if (!cf7_check_capability('cf7_db_form_view'.$fid) && !cf7_check_capability('cf7_db_form_edit_'.$fid)){
			wp_die( __('You do not have sufficient permissions to access this page.') );
		}
		$menu_url = menu_page_url('import_cf7_csv',false);
		$url = $menu_url.'&import_cf7_id='.$fid;
	}

	// Call When import CSV sheet.
	if(isset($_POST['submit']) && isset($_FILES['importFormList']) && !empty($_FILES['importFormList']['name']) && isset($_POST['wp_entry_nonce']) && isset($_POST['import_cf7_id']) && !empty($_POST['import_cf7_id']) && isset($_POST['form_match_key'])){
		include(dirname(__FILE__).'/import_cf7_entry.class.php');
	}

	$msg = '';
	/************* Save CSV file related key names ******************/

	//Define nonce values here
	$entry_nonce = wp_create_nonce('import-cf7-save-entry-nonce');

	$arr_form_match_key = '';
	//Get form related option values
	$arr_form_match_key = get_option('import_sheet_form_key_'.$fid);
	if(!empty($msg)){
		echo '<div class="notice error is-dismissible"><p>'.esc_html($msg).'</p></div>';
	}
	?><div class="wrap">
		<h2><?php esc_html_e('Import Setting',VSZ_CF7_TEXT_DOMAIN); ?></h2>
	</div>
	<div class="wrap">
		<div class="tabs import-csv">
			<ul class="tab-links wrap">
				<li class="active"><a href="#tab1"><?php esc_html_e('Import Settings',VSZ_CF7_TEXT_DOMAIN); ?></a></li>
			</ul>
			<table class="form-table inner-row wrap">
				<tr class="form-field form-required select-form">
					<th><?php esc_html_e('Select Form name',VSZ_CF7_TEXT_DOMAIN); ?></th>
					<td>
						<select name="import_cf7_id" id="import_cf7_id" onchange="import_submit_cf7()">
							<option value=""><?php esc_html_e('Select Form name',VSZ_CF7_TEXT_DOMAIN); ?></option><?php
							//Display all existing form list here
							if(!empty($form_list)){
								foreach($form_list as $objForm){
									if (cf7_check_capability('cf7_db_form_view'.$objForm->id()) || cf7_check_capability('cf7_db_form_edit_'.$objForm->id())){
										if(!empty($fid) && $fid === $objForm->id())
											print '<option value="'.$objForm->id().'" selected>'.esc_html($objForm->title()).'</option>';
										else
											print '<option value="'.$objForm->id().'" >'.esc_html($objForm->title()).'</option>';
									}
								}
							}
						?></select>
					</td>
				</tr>
			</table>
			<div class="tab-content">
				<div id="tab1" class="tab active">

					<form name="importEntryForm" action="<?php print esc_url($url);?>" method="post" enctype="multipart/form-data">
						<input type="hidden" name="base_url" id="base_url" value="<?php menu_page_url('import_cf7_csv');?>">
						<table class="form-table abc"><?php
							if(!empty($fid)){
								//Get id related form details
								$obj_form = vsz_cf7_get_the_form_list(intval($fid));
								//Get form related fields information
								$arr_form_tag = $obj_form[0]->scan_form_tags();
								$arr_option_type = array('checkbox','radio','select');
								if(!empty($arr_form_tag)){
									?><h3 class="advanced"><?php esc_html_e('Field Setting',VSZ_CF7_TEXT_DOMAIN); ?></h3>
									<tr><table class="form-table field-setting">
										<thead>
											<tr class="form-field form-required">
												<th><?php esc_html_e('Field name',VSZ_CF7_TEXT_DOMAIN); ?></th>
												<th><?php esc_html_e('Type',VSZ_CF7_TEXT_DOMAIN); ?></th>
												<th><?php esc_html_e('Match CSV Column',VSZ_CF7_TEXT_DOMAIN); ?></th>
											</tr>
										</thead>
										<tbody><?php
											foreach($arr_form_tag as $key => $arr_type){
												if($arr_type['basetype'] == 'submit' || $arr_type['basetype'] == 'recaptcha') continue;
												if(isset($arr_type['basetype']) && in_array($arr_type['basetype'],$arr_option_type)){
													?><tr class="form-field form-required">
														<td><?php print esc_html($arr_type['name']); ?></td>
														<td><?php print esc_html($arr_type['basetype']); ?></td>
														<td>
															<input class="match-key" type="text" name="form_match_key[<?php print esc_html($arr_type['name']);?>]" value="<?php print esc_html($arr_type['name']); ?>">
															<!-- Define fields key related field type value -->
															<input type="hidden" name="vsz_cf7_field_type[<?php print esc_html($arr_type['name']); ?>]" value="<?php print esc_html($arr_type['basetype']); ?>">
														</td>
													</tr><?php
												}
												else{
													?><tr class="form-field form-required">
														<td><?php print esc_html($arr_type['name']); ?></td>
														<td><?php print esc_html($arr_type['basetype']); ?></td>
														<!--<td></td>-->
														<td><input class="match-key" type="text" name="form_match_key[<?php print esc_html($arr_type['name']);?>]" value="<?php print esc_html($arr_type['name']); ?>">
														<!-- Define fields key related field type value -->
														<input type="hidden" name="vsz_cf7_field_type[<?php print esc_html($arr_type['name']); ?>]" value="<?php print esc_html($arr_type['basetype']); ?>">
														</td>
													</tr><?php
												}
											}
											?><tr class="form-field form-required">
												<td><?php esc_html_e('submit_ip',VSZ_CF7_TEXT_DOMAIN); ?></td>
												<td><?php esc_html_e('text',VSZ_CF7_TEXT_DOMAIN); ?></td>
												<td><input class="match-key" type="text" name="form_match_key[submit_ip]" value="Submitted From"></td>
											</tr>
											<tr class="form-field form-required">
												<td><?php esc_html_e('submit_time',VSZ_CF7_TEXT_DOMAIN); ?></td>
												<td><?php esc_html_e('text',VSZ_CF7_TEXT_DOMAIN); ?></td>
												<td><input class="match-key regular-text code" type="text" name="form_match_key[submit_time]" value="Submitted" >
													<select class="widefat" id="sheet_date_format" name="sheet_date_format"><?php
														//Add filter for customize date option values
														$arr_date_format = (array) apply_filters('vsz_cf7_import_date_format', vsz_cf7_import_date_format_callback());
															//Get all date format options
														echo vsz_cf7_arr_to_option($arr_date_format);
													?></select>
													<br><?php esc_html_e('Note:',VSZ_CF7_TEXT_DOMAIN); ?><br/><span><?php esc_html_e('If selected date format isn\'t matched with import sheet entry then consider today date.',VSZ_CF7_TEXT_DOMAIN); ?></span>
												</td>
											</tr>
										</tbody>
									</table></tr><?php
								}
								?><table class="form-table">
									<tr>
										<th><!--<h3 class="advanced">Import CSV</h3>--></th>
										<td>
										</td>
									</tr>
									<tr class="form-field form-required">
										<th><label for="importFormList"><?php esc_html_e('Upload CSV ',VSZ_CF7_TEXT_DOMAIN); ?>:</label></th>
										<td>
											<input type="file" name="importFormList" id="importFormList" accept=".csv" onchange="checkfile(this);"/>
										</td>
									</tr>
									<tr class="form-field form-required">
										<th></th>
										<td>
											<input type="submit" id="import_sheet" name="submit" value="<?php esc_html_e('Import Data',VSZ_CF7_TEXT_DOMAIN); ?>" class="button button-primary"/>
										</td>
									</tr>
								</table>
								<input type="hidden" name="wp_entry_nonce" value="<?php print esc_html($entry_nonce); ?>" />
								<input type="hidden" name="import_cf7_id" value="<?php print esc_html($fid); ?>" /><?php
							}
						?></table><!--Close main Table-->
					</form>
				</div>
			</div>
		</div>
	</div><?php
}
