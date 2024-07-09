<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ){
	die('Un-authorized access!');
}


//Define contact form action which is call before mail is trigger
add_action('wpcf7_before_send_mail','vsz_cf7_before_send_email');
//Call this function when mail is trigger then get all form fields related information
function vsz_cf7_before_send_email($contact_form){
	global $wpdb;
	$contact_form_ids = array();
	//Define this action for customize form data before insert in DB
	do_action('vsz_cf7_before_insert_db', $contact_form);

	//Get submitted contact form id
	$cf7_id = $contact_form->id();

	/*
	 * Restriction of the whole form submission to the database
	 * Filter provided to get all the un-necessary form data to be submitted
	 */

	$arr_contact_form_ids = (array) apply_filters('vsz_cf7_unwanted_form_data_submission', $contact_form_ids);

	if(!empty($arr_contact_form_ids) && is_array($arr_contact_form_ids) && in_array($cf7_id, $arr_contact_form_ids)) return;

	$contact_form = vsz_cf7_get_posted_data($contact_form);

	//for database installion
    $contact_form = vsz_cf7_add_extra_fields($contact_form);

	$contact_form = (object) apply_filters('vsz_cf7_modify_form_before_insert_data', $contact_form);

	//Type's $contact_form->posted_data is array
	// Define filter for customize posted data
	/* Below filter will be provided to the Users to modify the data
	 * Below is the process that can be performed
	 * 1) Add new data to the CF7 Form
	 * 2) Can modify the existing form submitted data
	 * 3) Can unset or remove the existing form data of CF7
	 *
	 */

    $contact_form->posted_data = (array) apply_filters('vsz_cf7_posted_data', $contact_form->posted_data);

    //Get table name for data entry
	$data_table_name = sanitize_text_field(VSZ_CF7_DATA_TABLE_NAME);
	$data_entry_table_name = sanitize_text_field(VSZ_CF7_DATA_ENTRY_TABLE_NAME);

	//Insert current form submission time in database
	$time = date('Y-m-d H:i:s');
    $wpdb->query($wpdb->prepare("INSERT INTO {$data_table_name}(`created`) VALUES (%s)", $time));
    //Get last inserted id
	$data_id = $wpdb->insert_id;

	//Insert form values in custom data entry table
	if(!empty($cf7_id) && !empty($data_id)){
		//Get not inserted fields value list
		$cf7d_no_save_fields = vsz_cf7_no_save_fields();
		foreach ($contact_form->posted_data as $k => $v) {

			//Check not inserted fields name in array or not
			if(in_array($k, $cf7d_no_save_fields)) {
				continue;
			}
			else{
				//If value is check box and radio button value then creaye single string
				if(is_array($v)){
					$v = implode("\n", $v);
				}
				$k = htmlspecialchars($k);
				//It is prevent JS injection
				$v = sanitize_textarea_field($v);
				//$v = htmlspecialchars($v);
				$wpdb->query($wpdb->prepare("INSERT INTO {$data_entry_table_name}(`cf7_id`, `data_id`, `name`, `value`) VALUES (%d,%d,%s,%s)", $cf7_id, $data_id, $k, $v));
			}
		}
		//exit;
		//Add action for customize process after insert value in data base
		do_action('vsz_cf7_after_insert_db', $contact_form, $cf7_id, $data_id);
	}

}

/*
 * Support CF7 functions
 */
function vsz_cf7_get_posted_data($cf7){

    if (!isset($cf7->posted_data) && class_exists('WPCF7_Submission')) {
        // Contact Form 7 version 3.9 removed $cf7->posted_data and now
        // we have to retrieve it from an API
        $submission = WPCF7_Submission::get_instance();
		if ($submission){
            $data = array();
            $data['title'] = $cf7->title();
            $data['posted_data'] = $submission->get_posted_data();
            $data['uploaded_files'] = $submission->uploaded_files();
            $data['WPCF7_ContactForm'] = $cf7;
            $cf7 = (object) $data;
        }
    }
    return $cf7;
}

/*
 * Add additional value with form data
 */
function vsz_cf7_add_extra_fields($cf7){

    $submission = WPCF7_Submission::get_instance();

    //Get time stamp value in valid date format
    //$cf7->posted_data['submit_time'] = date_i18n('Y-m-d H:i:s', $submission->get_meta('timestamp'));
    $cf7->posted_data['submit_time'] = current_time("Y-m-d H:i:s");

	if(!defined('vsz_cf7_display_ip')){
		//Get submitted ip address
		$ip_address = (isset($_SERVER['X_FORWARDED_FOR']) && !empty(rest_is_ip_address(sanitize_text_field($_SERVER['X_FORWARDED_FOR'])))) ?  sanitize_text_field($_SERVER['X_FORWARDED_FOR']) : "";

		if(empty($ip_address)){
			$ip_address = (isset($_SERVER['REMOTE_ADDR']) && !empty(rest_is_ip_address(sanitize_text_field($_SERVER['REMOTE_ADDR'])))) ?  sanitize_text_field($_SERVER['REMOTE_ADDR']) : "";
		}

		if(!empty($ip_address)){
			$cf7->posted_data['submit_ip'] = $ip_address;
		}
		//$cf7->posted_data['submit_ip'] = (isset($_SERVER['X_FORWARDED_FOR']) && !empty(rest_is_ip_address($_SERVER['X_FORWARDED_FOR']))) ? $_SERVER['X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];
	}
	return $cf7;
}

//save files uploaded by user and modify data before inserting to database
add_filter('vsz_cf7_modify_form_before_insert_data', 'vsz_cf7_modify_form_before_insert_in_cf7_vdata_entry');
if (!function_exists('vsz_cf7_modify_form_before_insert_in_cf7_vdata_entry')) {
    function vsz_cf7_modify_form_before_insert_in_cf7_vdata_entry($cf7){
        //if it has at lest 1 file uploaded
    	if (count($cf7->uploaded_files) > 0) {

	        //Get upload dir URL
    		$upload_dir = wp_upload_dir();
	        //Create custom upload folder
    		$cf7d_upload_folder = VSZ_CF7_UPLOAD_FOLDER;
    		$dir_upload = $upload_dir['basedir'] . '/' . $cf7d_upload_folder;
    		wp_mkdir_p($dir_upload);
	        //Get all uploaded files information
    		foreach ($cf7->uploaded_files as $k => $v) {


				//to check if CF7 version due to changes $cf7->uploaded_files return value
    			if(defined('WPCF7_VERSION') && WPCF7_VERSION > '5.3.2'){
    				$val=$v[0];
    			}else{
    				$val=$v;
    			}

    			if(!empty($val)){
    				//Get file name
 					// $file_name = basename($val);
					$file = pathinfo($val, PATHINFO_FILENAME);
					$ext = pathinfo($val, PATHINFO_EXTENSION);
					$file_name = $file . date("Ymdhis") . '.' . $ext;
					
					//Create unique file name
	    			$file_name = wp_unique_filename($dir_upload, $file_name);
					//Setup filoe path
	    			$dst_file = $dir_upload . '/' . $file_name;
					//Copy file information in destination variable
	    			if (@copy($val, $dst_file)){
						//Setup customize file information in array
	    				$cf7->posted_data[$k] = esc_url_raw($upload_dir['baseurl'] . '/' . $cf7d_upload_folder . '/' . $file_name);
	    			}
    			}
	        }//Close foreach
	    }//Close if

        return $cf7;
    }//Close function
}//Close if for check function exist or not

//Define function for which field value not insert in table
function vsz_cf7_no_save_fields(){
    $cf7d_no_save_fields = array('_wpcf7', '_wpcf7_version', '_wpcf7_locale', '_wpcf7_unit_tag', '_wpcf7_is_ajax_call','_wpcf7_container_post');
	//Add filter for customize values
    return apply_filters('vsz_cf7_no_save_fields', $cf7d_no_save_fields);
}//Close function

//Get all contact form list here
function vsz_cf7_get_the_form_list($fid = ''){

	//Get All form information
	$forms = WPCF7_ContactForm::find();
	$form = array();
	//fetch each form information

	foreach ($forms as $k => $v){
		//Check if form id not empty then get specific form related information
		if(!empty($fid)){
			if($v->id() == $fid){
				$form[] = $v;
				return $form;
			}
		}
		else{
			$form[] = $v;
		}
    }

	if(count($form)>1){
		// New function Added to sort the array by CF7 Name
		usort($form, "cmp_sort_form_name");
	}

	return $form;
}//Close function
/*
 * Sorting the contact forms to asc order by CF7 name
 */
function cmp_sort_form_name($a, $b)
{
    //return $a->name > $b->name;
    return strcmp($a->name(), $b->name());

}



/*
 * $data: rows from database
 * $fid: form id
 */
function vsz_cf7_sortdata($data){
    $data_sorted = array();
	//Set submitted id wise form information
    foreach ($data as $k => $v) {
        if(!isset($data_sorted[$v->data_id])){
            $data_sorted[$v->data_id] = array();
        }
        $data_sorted[$v->data_id][$v->name] = (string) apply_filters('cf7d_entry_value', trim(wp_unslash($v->value)), $v->name);
    }

    return $data_sorted;
}

//Get form id related fields information from DB
function vsz_cf7_get_db_fields($fid, $filter = true){

    global $wpdb;
	$fid = (int)$fid;
	$data_entry_table_name = sanitize_text_field(VSZ_CF7_DATA_ENTRY_TABLE_NAME);

    $sql = $wpdb->prepare("SELECT `name` FROM `{$data_entry_table_name}` WHERE cf7_id = %d GROUP BY `name`", $fid);
    $data = $wpdb->get_results($sql);

	//Set each field value in array
    $fields = array();
	if(!empty($data)){

		foreach ($data as $k => $v) {
			if(defined('vsz_cf7_display_ip')){
				if($v->name != 'submit_ip'){
					$fields[$v->name] = htmlspecialchars_decode($v->name);
				}
			}
			else{
				$fields[$v->name] = htmlspecialchars_decode($v->name);
			}
		}
	}

	//Check if filter is true or not
    if ($filter) {
		//Get all fields information as per Setting screen
        $fields = (array) apply_filters('vsz_cf7_admin_fields', $fields, $fid);
    }

    return $fields;
}//Close function

//Add option value in specific select box request
if(!function_exists('vsz_cf7_arr_to_option')){
    function vsz_cf7_arr_to_option($arr){
        $html = '';
        if(!empty($arr) && is_array($arr)){
			foreach($arr as $k => $v) {
				$html .= '<option value="'.esc_html($k).'">'.esc_html($v).'</option>';
			}
		}
		return $html;
    }
}

//get speicfic fields related information
function vsz_cf7_admin_get_field_name($field){
    return esc_html($field);
}

//Get form id and specific entry related keys information
function get_entry_related_fields_info($fid,$entryId){
	$fields = array();
	if(!empty($fid) && !empty($entryId)){

		global $wpdb;
		$fid = intval($fid);
		$entryId = intval($entryId);
		$data_entry_table_name = sanitize_text_field(VSZ_CF7_DATA_ENTRY_TABLE_NAME);

		$sql = $wpdb->prepare("SELECT `name` FROM `{$data_entry_table_name}` WHERE `cf7_id` = %d AND `data_id` = %d GROUP BY `name`", $fid, $entryId);
		$data = $wpdb->get_results($sql);
		if(!empty($data)){
			foreach ($data as $k => $v) {
				$fields[$v->name] = htmlspecialchars_decode($v->name);
			}
		}
	}
	return $fields;
}

//Check current action
function vsz_cf7_current_action(){

	$current_action = false;
	if (isset($_POST['action']) && -1 != $_POST['action'] && isset($_POST['btn_apply'])) {
        $current_action = sanitize_text_field($_POST['action']);
        return (string) apply_filters('vsz_cf7_get_current_action', $current_action);
    }

    if (isset($_POST['action2']) && -1 != $_POST['action2'] && isset($_POST['btn_apply2'])) {
        $current_action = sanitize_text_field($_POST['action2']);
        return (string) apply_filters('vsz_cf7_get_current_action', $current_action);
    }
    $current_action = (string) apply_filters('vsz_cf7_get_current_action', $current_action);
    return false;
}


//Display field type related values here
function vsz_display_field_type_value($type,$arr_field_type,$k,$v){

	$type = esc_html($type);
	$k = esc_html($k);
	if($type == 'checkbox'){

		if(is_array($v)){
			$label = esc_html($v['label']);
		}
		else{
			$label = esc_html($v);
		}
		$loading = __('Loading...');
		//added in 1.8.4
		$fieldName = "field[".$k."]";
		$className = "field-$k";
		//Display Text box design here
		?><li class="clearfix">
			<span class="label"><?php print esc_html($label." (".$type.").");?></span>
			<textarea name="<?php print esc_html($fieldName);?>" rows="3" cols="20" class="<?php print esc_html($className);?>" ><?php print esc_html($loading);?></textarea>
			<span class="margin_left"><?php esc_html_e('(Multiple entry start from new line)',VSZ_CF7_TEXT_DOMAIN); ?></span>
			<div class="clear"></div>
		</li><?php

	}
	else if($type == 'radio'){

		if(is_array($v)){
			$label = esc_html($v['label']);
		}
		else{
			$label = esc_html($v);
		}
		$loading = __('Loading...');
		//added in 1.8.4
		$fieldName = "field[".$k."]";
		$className = "field-$k";
		//Display Text box design here
		?><li class="clearfix">
			<span class="label"><?php print esc_html($label." (".$type.").");?></span>
			<textarea name="<?php print esc_html($fieldName);?>" rows="3" cols="20" class="<?php print esc_html($className);?>" ><?php print esc_html($loading);?></textarea>
			<div class="clear"></div>
		</li><?php

	}
	else if($type == 'select'){
		if(is_array($v)){
			$label = esc_html($v['label']);
		}
		else{
			$label = esc_html($v);
		}
		$loading = __('Loading...');
		//added in 1.8.4
		$fieldName = "field[".$k."]";
		$className = "field-$k";
		//Display Text box design here
		?><li class="clearfix">
			<span class="label"><?php print esc_html($label." (".$type.").");?></span>
			<textarea name="<?php print esc_html($fieldName);?>" rows="3" cols="20" class="<?php print esc_html($className);?>" ><?php print esc_html($loading);?></textarea>
			<div class="clear"></div>
		</li><?php
	}
	else if($type == 'textarea'){
		if(is_array($v)){
			$label = esc_html($v['label']);
		}
		else{
			$label = esc_html($v);
		}
		$loading = __('Loading...');
		//added in 1.8.4
		$fieldName = "field[".$k."]";
		$className = "field-$k";
		//Display Text box design here
		?><li class="clearfix">
			<span class="label"><?php print esc_html($label);?></span>
			<textarea name="<?php print esc_html($fieldName);?>" rows="3" cols="20" class="<?php print esc_html($className);?>" ><?php print esc_html($loading);?></textarea>
			<div class="clear"></div>
		</li><?php

	}
	else if($type == 'file'){
		if(is_array($v)){
			$label = esc_html($v['label']);
		}
		else{
			$label = esc_html($v);
		}
		$loading = __('Loading...');
		$disable = 'readonly';
		//added in 1.8.4
		$fieldName = "field[".$k."]";
		$className = "field-$k";
		//Display Text box design here
		?><li class="clearfix">
			<span class="label"><?php print esc_html($label);?></span>
			<input class="<?php print esc_html($className);?>" type="text" name="<?php print esc_html($fieldName);?>" value="<?php print esc_html($loading);?>"  <?php print esc_html($disable);?> />
			<div class="clear"></div>
		</li><?php
	}
	else if($type == 'tel'){

		if(is_array($v)){
			$label = esc_html($v['label']);
		}
		else{
			$label = esc_html($v);
		}
		$loading = __('Loading...');
		//added in 1.8.4
		$fieldName = "field[".$k."]";
		$className = "field-$k";
		//Display Text box design here
		?><li class="clearfix">
			<span class="label"><?php print esc_html($label);?></span>
			<input class="<?php print esc_html($className);?>" type="text" name="<?php print esc_html($fieldName);?>" value="<?php print esc_html($loading);?>" />
			<div class="clear"></div>
		</li><?php

	}
	else if($type == 'dynamictext'|| $type == 'dynamichidden'|| $type == 'url' || $type == 'number' || $type == 'date' || $type == 'acceptance' || $type == 'quiz'){
		if(is_array($v)){
			$label = esc_html($v['label']);
		}
		else{
			$label = esc_html($v);
		}
		$loading = __('Loading...');
		//added in 1.8.4
		$fieldName = "field[".$k."]";
		$className = "field-$k";
		//Display Text box design here
		?><li class="clearfix">
			<span class="label"><?php print esc_html($label);?></span>
			<input class="<?php print esc_html($className);?>" type="text" name="<?php print esc_html($fieldName);?>" value="<?php print esc_html($loading);?>" />
			<div class="clear"></div>
		</li><?php

	}
	else{
		if(is_array($v)){
			$label = esc_html($v['label']);
		}
		else{
			$label = esc_html($v);
		}
		$loading = __('Loading...');
		//added in 1.8.4
		$fieldName = "field[".$k."]";
		$className = "field-$k";
		//Display Text box design here
		?><li class="clearfix">
			<span class="label"><?php print esc_html($label);?></span>
			<textarea name="<?php print esc_html($fieldName);?>" rows="3" cols="20" class="<?php print esc_html($className);?>" ><?php print esc_html($loading);?></textarea>
			<div class="clear"></div>
		</li><?php

	}

}

//Define sheet related date formates
function vsz_cf7_import_date_format_callback(){

	$arr_dates = array('Y-m-d H:i:s P' => date('Y-m-d H:i:s P'),
						'Y-m-d H:i:s' => date('Y-m-d H:i:s'),
						'Y-m-d' => date('Y-m-d'),
						'Y/m/d' => date('Y/m/d'),
						'jS F, Y' => date('jS F, Y'),
						'F j, Y' => date('F j, Y'),
						'd/m/Y' => date('d/m/Y'),
						'd-m-Y' => date('d-m-Y')
					);

	return $arr_dates;

}

//Get field name related type information
function vsz_field_type_info($fid){

	if(empty($fid) || !intval($fid)) return ;

	$fid = intval($fid);
	$obj_form = vsz_cf7_get_the_form_list($fid);
	
	if( empty($obj_form) ) return ;
	
	//get pre define fields information
	$arr_form_tag = $obj_form[0]->scan_form_tags();
	$arr_field_type = array();
	if(!empty($arr_form_tag)){
		//Get all fields related information
		foreach($arr_form_tag as $key => $arr_type){
			//Check if tag type is submit then ignore tag info
			if($arr_type['basetype'] == 'submit') continue;
			//get field type information
			$arr_field_type[$arr_type['name']] = $arr_type['basetype'];
		}
	}

	return $arr_field_type;
}