<?php
// Exit if accessed directly
if(!defined( 'ABSPATH' ) ) {
	exit;
}

//print "calling shortcode";


global $wpdb;
$html = "";
// get id for banner shortcode if define
$arrInfo = 	shortcode_atts(
				array( 'form_id' => '', 'show' => '', 'hide' => '', 'display' => '', 'search' => '', 'id' => '', 'class' => '',
					'header' => '', 'style' => '', 'max_entries' => '', 'start-date' => '', 'end-date' => '' ),
				$atts
			);
$formIds = sanitize_text_field($arrInfo['form_id']);
$display = sanitize_text_field($arrInfo['display']);
$show = sanitize_text_field($arrInfo['show']);
$hide = sanitize_text_field($arrInfo['hide']);
$search = sanitize_text_field($arrInfo['search']);
$table_id = sanitize_text_field($arrInfo['id']);
$table_class = sanitize_text_field($arrInfo['class']);
$header = sanitize_text_field($arrInfo['header']);
$table_style = sanitize_text_field($arrInfo['style']);
$posts_per_page = sanitize_text_field($arrInfo['max_entries']);
$start_date = sanitize_text_field($arrInfo['start-date']);
$end_date = sanitize_text_field($arrInfo['end-date']);

$search = str_replace('"', '\\&quot;', $search);
$search = addslashes(addslashes(sanitize_text_field($search)));

$formArr = explode(",", $formIds);

foreach($formArr as $key=>$val){
	
	//added in 2.0.2
	if(defined('WPCF7_VERSION') && WPCF7_VERSION >= '5.8'){
		
		$objForm = wpcf7_get_contact_form_by_hash(trim($val));	
		if(empty($objForm) || $objForm == NULL) unset($formArr[$key]);
	}
	else{
		$val = (int) $objForm->id;
		if(empty($val)) unset($formArr[$key]);
	}
}

//Get all existing contact form list
$form_list = vsz_cf7_get_the_form_list();

if(empty($formArr)){
	$formArr = $form_list;
	$allForms = true;
}


if(!empty($formArr)){

	$count = array();

	foreach($formArr as $fid){
		
		if(isset($allForms) && $allForms){
			$form = $fid;
			unset($fid);
			$fid = $form->id();
		}
		//added in 2.0.2
		else if(defined('WPCF7_VERSION') && WPCF7_VERSION >= '5.8'){
			
			$objForm = wpcf7_get_contact_form_by_hash($fid);	
			$fid = $objForm->id();
		}
		
		$form = vsz_cf7_get_the_form_list($fid);	
		
		
		if(!empty($form)){

			$form = $form[0];
			$data_sorted = "";
			$showArrFinal = array();
			$hideArrFinal = array();
			$headerForForm = "";
			//Get form Id related fields information
			$fields = vsz_cf7_get_db_fields($fid);

			// Show Hide Fields
			if(!empty($show)){
				$showArr = explode(",",$show);
				foreach($showArr as $key => $val){
					$val = trim($val);

					$valArr = explode(".",$val);

					if(count($valArr) > 1){
						//updated in 2.0.2
						if($valArr[0] == $fid || ( function_exists('hash') && $form->hash() == $valArr[0]) ){
							if(count($valArr)>2){
								unset($valArr[0]);

								$fieldVal = implode(".",$valArr);
							}
							else{
								$fieldVal = $valArr[1];
							}

							$showArrFinal[] = $fieldVal;
						}
					}
				}
			}
			if(!empty($hide)){
				$hideArr = explode(",",$hide);
				
				foreach($hideArr as $key => $val){
					$val = trim($val);

					$valArr = explode(".",$val);

					if(count($valArr) > 1){
						//updated in 2.0.2
						if($valArr[0] == $fid || ( function_exists('hash') && $form->hash() == $valArr[0]) ){
							if(count($valArr)>2){
								unset($valArr[0]);

								$fieldVal = implode(".",$valArr);
							}
							else{
								$fieldVal = $valArr[1];
							}

							$hideArrFinal[] = $fieldVal;
						}
					}
				}
			}
			if(!empty($header)){
				$headerArr = explode(",",$header);
				foreach($headerArr as $key => $val){
					$val = trim($val);
					$valArr = explode(".",$val);

					if(count($valArr) > 1){
						
						//updated in 2.0.2
						if($valArr[0] == $fid || ( function_exists('hash') && $form->hash() == $valArr[0]) ){
						
							if(count($valArr)>2){
								unset($valArr[0]);

								$fieldVal = implode(".",$valArr);
							}
							else{
								$fieldVal = $valArr[1];
							}

							$headerForForm = $fieldVal;
						}
					}
				}
			}

			// Show Field Applied Here
			if(!empty($showArrFinal)){
				foreach($fields as $key => $val){
					if(!in_array($val, $showArrFinal)){
						unset($fields[$key]);
					}
				}
			}

			// Hide Field Applied Here
			if(!empty($hideArrFinal)){
				foreach($fields as $key => $val){
					if(in_array($val, $hideArrFinal)){
						unset($fields[$key]);
					}
				}
			}

			//Check contact form id set or not
			if (!empty($fid) && !empty($fields)){
				//Add filter for ordering in entry
				$cf7d_entry_order_by = (string) apply_filters('vsz_cf7_entry_order_by', '`data_id` DESC');
				$cf7d_entry_order_by = sanitize_sql_orderby($cf7d_entry_order_by);

				$items_per_page = (int)apply_filters('vsz_cf7_entry_per_page', (!empty($posts_per_page) ? $posts_per_page : 20));

				$page = isset($_POST['cpage']) && !empty($_POST['cpage']) ? abs((int)sanitize_text_field($_POST['cpage'])) : 1;
				//Setup offset related value here
				$offset = (int)( $page * $items_per_page ) - $items_per_page;

				//Get table name for data entry
				$table_name = sanitize_text_field(VSZ_CF7_DATA_ENTRY_TABLE_NAME);

				// Make sure that $fid will contain integer value only.
				$fid = (int) $fid;

				// If date search exists
				$data_ids = '';
				if(!empty($start_date) && !empty($end_date)){
					$s_date = date_create_from_format("d/m/Y",sanitize_text_field($start_date));
					$e_date = date_create_from_format("d/m/Y",sanitize_text_field($end_date));
					//Get start date information
					$start_date_str =  date_format($s_date,"Y-m-d");

					//Get end date information
					$end_date_str =  date_format($e_date,"Y-m-d").' 23:59:59';

					if($start_date_str && $end_date_str){
						//Setup date parameter value in query
						$date_query = $wpdb->prepare("SELECT data_id FROM `{$table_name}` WHERE 1 = 1 AND `cf7_id` = %d AND `name` = 'submit_time' AND value between %s and %s GROUP BY `data_id` ORDER BY `data_id` DESC", $fid, $start_date_str, $end_date_str);
						$rs_date = $wpdb->get_results($date_query);

						if(!empty($rs_date)){
							foreach($rs_date as $objdata_id){
								if(!empty($ids_export)){
									$arr_ids = array_map('intval',explode(',',$ids_export));
									if(!empty($arr_ids) && in_array($objdata_id->data_id,$arr_ids)){
										$data_ids .= $objdata_id->data_id .',';
									}
								}
								else{
									$data_ids .= $objdata_id->data_id .',';
								}
							}
							$data_ids = rtrim($data_ids,',');
						}
					}
				}

				$query = "";
				$arr_total = array();
				if(!empty($search) && !empty($start_date) && !empty($end_date) && !empty($data_ids)){

					$query = $wpdb->prepare("SELECT * FROM `{$table_name}` WHERE `cf7_id` = %d AND data_id IN(SELECT * FROM (SELECT data_id FROM `{$table_name}` WHERE 1 = 1 AND `cf7_id` = %d AND `value` LIKE '%%"."%s"."%%' AND data_id IN ({$data_ids}) GROUP BY `data_id` ORDER BY {$cf7d_entry_order_by} LIMIT %d,%d) temp_table) ORDER BY {$cf7d_entry_order_by}", $fid, $fid, $search, $offset, $items_per_page);

					$arr_total = $wpdb->get_results($wpdb->prepare("SELECT data_id FROM `{$table_name}` WHERE `cf7_id` = %d AND `value` LIKE '%%"."%s"."%%' AND data_id IN ({$data_ids}) GROUP BY `data_id`", $fid, $search));

				}else if(!empty($search) && empty($start_date) && empty($end_date)){

					$query = $wpdb->prepare("SELECT * FROM `{$table_name}` WHERE `cf7_id` = %d AND data_id IN(SELECT * FROM (SELECT data_id FROM `{$table_name}` WHERE 1 = 1 AND `cf7_id` = %d AND `value` LIKE '%%"."%s"."%%' GROUP BY `data_id` ORDER BY {$cf7d_entry_order_by} LIMIT %d,%d) temp_table) ORDER BY {$cf7d_entry_order_by}", $fid, $fid, $search, $offset, $items_per_page);

					$arr_total = $wpdb->get_results($wpdb->prepare("SELECT data_id FROM `{$table_name}` WHERE `cf7_id` = %d AND `value` LIKE '%%"."%s"."%%' GROUP BY `data_id`", $fid, $search));

				}else if(empty($search) && !empty($start_date) && !empty($end_date) && !empty($data_ids)){

					$query = $wpdb->prepare("SELECT * FROM `{$table_name}` WHERE `cf7_id` = %d AND data_id IN(SELECT * FROM (SELECT data_id FROM `{$table_name}` WHERE 1 = 1 AND `cf7_id` = %d AND data_id IN ({$data_ids}) GROUP BY `data_id` ORDER BY {$cf7d_entry_order_by} LIMIT %d,%d) temp_table) ORDER BY {$cf7d_entry_order_by}", $fid, $fid, $offset, $items_per_page);

					$arr_total = $wpdb->get_results($wpdb->prepare("SELECT data_id FROM `{$table_name}` WHERE `cf7_id` = %d AND data_id IN ({$data_ids}) GROUP BY `data_id`", $fid));

				}else if(empty($search) && empty($start_date) && empty($end_date)){

					$query = $wpdb->prepare("SELECT * FROM `{$table_name}` WHERE `cf7_id` = %d AND data_id IN(SELECT * FROM (SELECT data_id FROM `{$table_name}` WHERE 1 = 1 AND `cf7_id` = %d GROUP BY `data_id` ORDER BY {$cf7d_entry_order_by} LIMIT %d,%d) temp_table) ORDER BY {$cf7d_entry_order_by}", $fid, $fid, $offset, $items_per_page);

					$arr_total = $wpdb->get_results($wpdb->prepare("SELECT data_id FROM `{$table_name}` WHERE `cf7_id` = %d  GROUP BY `data_id`", $fid));

				}

				//Execute query here
				$data = $wpdb->get_results($query);

				//Get entry wise all fields information
				$data_sorted = vsz_cf7_sortdata($data);

				//get total count
				$total = count($arr_total);

				$display_character = (int) apply_filters('vsz_display_character_count',30);

				switch($display){

					case "count" :
						$count[] = $total;
						break;

					case "table" :
					default :

						//Get all fields related information
						if(!empty($data_sorted)){

							// If title passed in attribute use that otherwise use form title
							$html .= !empty($headerForForm) ? '<h2>'.esc_html($headerForForm).'</h2>' : '<h2>'.esc_html($form->title()).'</h2>';
							$html .= '<table id="'.esc_html($table_id).'" class="'.esc_html($table_class).'" style="overflow: auto; '.esc_html($table_style).'">
										<thead>
											<tr>';

							//Define table header section here
							foreach ($fields as $k => $v){
								$html .= '<th class="manage-column" data-key="'.esc_html($v).'">'.vsz_cf7_admin_get_field_name($v).'</th>';
							}

							$html .= '</tr>
									</thead>
									<tbody>';

							foreach ($data_sorted as $k => $v) {
								$k = (int)$k;
								$html .= '<tr>';
								foreach ($fields as $k2 => $v2) {
									//Get fields related values
									$_value = ((isset($v[$k2])) ? $v[$k2] : '&nbsp;');
									$_value1 = filter_var($_value, FILTER_SANITIZE_URL);

									//Check value is URL or not
									if (!filter_var($_value1, FILTER_VALIDATE_URL) === false) {
										$_value = esc_url($_value);
										//If value is url then setup anchor tag with value
										if(!empty($arr_field_type_info) && array_key_exists($k2,$arr_field_type_info) && $arr_field_type_info[$k2] == 'file'){
											//Add download attributes in tag if field type is attachement
											$_value = '<a href="'. esc_url($_value).'" target="_blank" title="'. esc_url($_value).'" download >'.esc_html(basename($_value)).'</a>';

											//Changed dated 13-06-2023
											$html .= '<td data-head="'.vsz_cf7_admin_get_field_name($v2).'">'. $_value.'</td>';

										}
										else{
											$_value = '<a href="'. esc_url($_value).'" target="_blank" title="'. esc_url($_value).'" >'.esc_html(basename($_value)).'</a>';

											//Changed dated 13-06-2023
											$html .= '<td data-head="'.vsz_cf7_admin_get_field_name($v2).'">'. $_value.'</td>';
										}
									}
									else{
										$_value = esc_html(html_entity_decode($_value));
										//var_dump(($_value)); var_dump(strlen($_value)); exit;
										if(strlen($_value) > $display_character){

											$html .= '<td data-head="'.vsz_cf7_admin_get_field_name($v2).'">'. esc_html(substr($_value, 0, $display_character)).'...</td>';
										}else{
											$html .= '<td data-head="'.vsz_cf7_admin_get_field_name($v2).'">'.esc_html($_value).'</td>';
										}
									}
								}

								$html .='</tr>';
							}

							$html .='</tbody>
								</table>';
						}
						else{
							$colSpan = count($fields) + 2;
							$html .= '<table>
										<tr>
											<td colspan="'.esc_html($colSpan).'">
												No records found.
											</td>
										</tr>
									</table>';
						}

						break;
				}

			}
		}
	}
}

if(!empty($count)){
	if(count($count)>1){
		$totalVal = 0;
		foreach($count as $val){
			$totalVal += $val;
		}

		return $totalVal;
	}
	else{
		return implode(",",$count);
	}
}
else{
	return $html;
}