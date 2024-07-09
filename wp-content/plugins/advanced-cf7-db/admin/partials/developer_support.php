<?php

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	die;
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
	// Add css & JS
	wp_enqueue_style('vsz-cf7-db-admin-css');
	wp_enqueue_style('jquery-datetimepicker-css');
	wp_enqueue_script('jquery-ui-sortable');
	
	//wp_enqueue_script('wp_enqueue_style');
	
	//Get all existing contact form list
	$form_list = vsz_cf7_get_the_form_list();

	//Get all form names which entry store in DB
	global $wpdb;
	$sql = "SELECT `cf7_id` FROM `".VSZ_CF7_DATA_ENTRY_TABLE_NAME."` GROUP BY `cf7_id`";
	$data = $wpdb->get_results($sql,ARRAY_N);
	$arr_form_id = array();
	if(!empty($data)){
		foreach($data as $arrVal){
			$arr_form_id[] = (int)$arrVal[0];
		}
	}


	?>
	<div class="wrap">
		<h2>Developer Support</h2>
	</div>
	<div id="acf7db-support-page" class="wrap select-specific shortcodes">
		<div class="tab">
			<span class="tablinks active" onclick="openTab(event, 'acf7db_display_shortcode')">Display Enquiry</span>
			<span class="tablinks" onclick="openTab(event, 'acf7db_other_shortcode')">Ban IP</span>
			<span class="tablinks" onclick="openTab(event, 'acf7db_hooks')">Actions & Filters</span>
		</div>
		<div id="acf7db_display_shortcode" class="tabcontent active acf7db-shortcode-info" style="display:block;">
			<h2>Display Enquiry</h2>
			<p>You can display all contact form submission data on front end side of website to place the short codes.</p>
			<p>You can place these short codes in any page OR use to "do_shortcode" function to execute from php files.</p>
			<p>Ex. <code>do_shortcode( ‘[acf7db form_id='30f46fb']’ );</code></p>
			<p>You can use below options in short codes:</p>
			<table class="form-table bordered widefat fixed display_enquiry_table_class" cellspacing="15px" >
				<thead>
					<th class="table-head-type" >Parameter</th>
					<th class="table-head-type">Description</th>
					<th class="table-head-type">Example</th>
				</thead>
				<tbody>
					<tr class="form-field">
						<th rowspan="3" >FORM ID</th>
						<td>
							<p>You can add form id to display the form data.</p>
						</td>
						<td>
							<p><code>[acf7db form_id='30f46fb']</code></p>
						</td>
					</tr>
					<tr>
						<td>
							<p>You need to add multiple form ids to display multiple form data.</p>
						</td>
						<td>
							<p><code>[acf7db form_id='30f46fb,30f47yz']</code></p>
						</td>
					</tr>
					<tr>
						<td colspan="2" >
							<p>
								<span class="description">
									<b><u>Note:-</u></b>
									<span>If you don't pass id in it or keep empty than output will have all forms data.</span>
								</span>
							</p>
						</td>
					</tr>
					<tr class="form-field">
						<th rowspan="2" >SHOW</th>
						<td>
							<p>You need to add the columns names with form ids to display on front end side.</p>
							<p>This will display only 1 column for form 30f46fb data, and all columns for form 30f47yz data.</p>
						</td>
						<td>
							<p><code>[acf7db form_id='30f46fb,30f47yz' show='30f46fb.your-name']</code></p>
						</td>
					</tr>
					<tr>
						<td>
							<p>You can also add multiple columns to display multiple form data.</p>
							<p>This will display only 1 column for form 30f46fb and form 30f47yz data.</p>
						</td>
						<td>
							<p><code>[acf7db form_id='30f46fb,30f47yz' show='30f46fb.your-name, 30f47yz.your-email']</code></p>
						</td>
					</tr>
					<tr class="form-field">
						<th rowspan="2" >HIDE</th>
						<td>
							<p>You can specify particular columns which you won't like to display.</p>
							<p>It means that to display all columns except 1 column ("your-name") for form 30f46fb data, and all columns for form 30f47yz data.</p>
						</td>
						<td>
							<p><code>[acf7db form_id='30f46fb,30f47yz' hide='30f46fb.your-name']</code></p>
						</td>
					</tr>
					<tr>
						<td>
							<p>You can also add multiple columns to don't display multiple form data.</p>
							<p>This will display all columns except 1 column ("your-name") for form 30f46fb data, and all columns except 1 column ("your-email") for form 30f47yz data.</p>
						</td>
						<td>
							<p><code>[acf7db form_id='30f46fb,30f47yz' hide='30f46fb.your-name, 30f47yz.your-email']</code></p>
						</td>
					</tr>
					<tr class="form-field">
						<th>SEARCH</th>
						<td>
							<p>You can search by keyword.</p>
							<p>It will display all submitted data with value like "test@gmail.com".</p>
						</td>
						<td>
							<p><code>[acf7db form_id='30f46fb,30f47yz' search='test@gmail.com']</code></p>
						</td>
					</tr>
					<tr class="form-field">
						<th rowspan="3">DATE</th>
						<td colspan="2">
							<p>You can search by date using date parameters.</p>
							<p>It is required to pass "start-end date". If any one date will be mentioned then it doesn't work.</p>
						</td>
					</tr>
					<tr>
						<td>
							<p>It's means that to display all submitted data with submit time in between "01/09/2017" and "01/10/2018" .</p>
						</td>
						<td>
							<p><code>[acf7db form_id='30f46fb,30f47yz' start-date="01/09/2017" end-date="01/10/2018"]</code></p>
						</td>
					</tr>
					<tr>
						<td colspan="2">
							<p>
								<span class="description">
									<b><u>Note:-</u></b>
									<span>This parameter will have effect if start date and end date both given with proper format.</span>
									<span>Both date should be in "dd/mm/yyyy" format.</span>
								</span>
							</p>
						</td>
					</tr>
					<tr class="form-field">
						<th rowspan="2" >ID</th>
						<td >
							<p>You can add id to the table tag of output data.</p>
							<p>This will add id to the table.</p>
						</td>
						<td>
							<p><code>[acf7db form_id='30f46fb,30f47yz' id='my-table-id']</code></p>
						</td>
					</tr>
					<tr>
						<td colspan="2">
							<p>
								<span class="description">
									<b><u>Note:-</u></b>
									<span>This parameter will have effect only when format "Table" is given.</span>
								</span>
							</p>
						</td>
					</tr>
					<tr class="form-field">
						<th rowspan="2">CLASS</th>
						<td>
							<p>You can add classes to the table tag of output data.</p>
							<p>This will add classes to the table.</p>
						</td>
						<td>
							<p><code>[acf7db form_id='30f46fb,30f47yz' class='form-table-class1 form-table-class2']</code></p>
						</td>
					</tr>
					<tr>
						<td colspan="2">
							<p>
								<span class="description">
									<b><u>Note:-</u></b>
									<span>This parameter will have effect only when format "Table" is given.</span>
								</span>
							</p>
						</td>
					</tr>
					<tr class="form-field">
						<th rowspan="2">STYLE</th>
						<td>
							<p>You can add style to the table tag of output data.</p>
							<p>This will add style to the table.</p>
						</td>
						<td>
							<p><code>[acf7db form_id='30f46fb,30f47yz' style='max-width=400px;']</code></p>
						</td>
					</tr>
					<tr>
						<td colspan="2">
							<p>
								<span class="description">
									<b><u>Note:-</u></b>
									<span>This parameter will have effect only when format "Table" is given.</span>
									<span>Style will be added as inline style.</span>
								</span>
							</p>
						</td>
					</tr>
					<tr class="form-field">
						<th rowspan="3">HEADER</th>
						<td>
							<p>You can add custom header which will be displayed as title for every table. Headers must be used with form id to to affect the output data.</p>
							<p>This will display "Form Header Text 1" as title for form 30f46fb data. Form 30f47yz data title will be form name.</p>
						</td>
						<td>
							<p><code>[acf7db form_id='30f46fb,30f47yz' header='30f46fb.Form Header Text 1']</code></p>
						</td>
					</tr>
					<tr>
						<td>
							<p>You can add multiple columns which will be displayed for multiple form data.</p>
							<p>This will display "Form Header Text 1" as title for form 30f46fb data and "Form Header Text 2" as title for Form 30f47yz data.</p>
						</td>
						<td>
							<p><code>[acf7db form_id='30f46fb,30f47yz' header='30f46fb.Form Header Text 1, 30f47yz.Form Header Text 2']</code></p>
						</td>
					</tr>
					<tr>
						<td colspan="2">
							<p>
								<span class="description">
									<b><u>Note:-</u></b>
									<span>This parameter will have effect only when format "Table" is given.</span>
									<span>If header is not passed than it will use form title.</span>
								</span>	
							</p>
						</td>
					</tr>
					<tr class="form-field">
						<th rowspan="3">DISPLAY</th>
						<td colspan="2">
							<p>You can select the output type from following types :</p>
						</td>
					</tr>
					<tr>
						<td>
							<p>This will output the short code as a <b>table</b> structure.</p>
						</td>
						<td>
							<p><code>[acf7db form_id='30f46fb,30f47yz' display='table']</code></p>
						</td>
					</tr>
					<tr>
						<td>
							<p>This will output the short code as a data <b>count</b> only.</p>
						</td>
						<td>
							<p><code>[acf7db form_id='30f46fb,30f47yz' display='count']</code></p>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
		<div id="acf7db_other_shortcode" class="tabcontent">
			<h2>Ban IP</h2>
			<p>You can use below short code to skip saving of IP address :</p>
			<table class="form-table striped bordered widefat fixed" cellspacing="15px" >
				<tr class="form-field">
					<th>Skip Saving Of IP Address</th>
					<td>
						<p><code>[cf7-db-display-ip]</code></p>
						<p>OR <br> <code>do_shortcode( ‘[cf7-db-display-ip]’ );</code></p>
						<p><span class="description"><b><u>Note</u></b><br>You need to add this code in function file to skip saving IP address.</span></p>
					</td>
				</tr>
			</table>
		</div>
		<div id="acf7db_hooks" class="tabcontent">
			<h2>Actions & Filters</h2>
			<p>Here a list of actions and filters added is given.</p>
			<p>You can use below hooks as per your requirement at your own risk.</p>
			<h3>Actions</h3>
			<table class="form-table striped bordered widefat fixed action-tbl" cellspacing="15px" >
				<tr class="form-field">
					<th>vsz_cf7_display_settings_btn</th>
					<td>
						<p><strong>Parameters : </strong>$fid</p>
						<p>You can change "Display Settings" using this action.</p>
						<div class='code-php'>
							<div class="action-filter-name">
								add_action<span class="function-bracket text-green">(</span><span class="action-name text-blue">"vsz_cf7_display_settings_btn"</span><span class="funciton-separater text-green">,</span><span class="action-name text-blue">"vsz_cf7_display_settings_btn_callback"</span><span class="funciton-separater text-green">,</span><span class="function-piority text-pink">10</span><span class="funciton-separater text-green">,</span>
								<span class="function-param text-pink">1</span><span class="function-bracket text-green">)</span>;
							</div>
							<div class="function-def">
								<strong class="text-black">function</strong> <span class="function-defined">vsz_cf7_display_settings_btn_callback</span><span class="function-bracket text-green">(</span><span class="function-val text-blue">$fid</span><span class="function-bracket text-green">)</span><span class="function-bracket text-green">{</span>
									<p>&nbsp;&nbsp;&nbsp;// Your custom coding here</p>
								<span class="function-bracket text-green">}</span>
							</div>
						</div>
					</td>
				</tr>
				<tr class="form-field">
					<th>vsz_cf7_after_datesection_btn</th>
					<td>
						<p><strong>Parameters : </strong>$fid</p>
						<p>Using this action,you can add custom coding after date section in listing screen.</p>
						<div class='code-php'>
							<div class="action-filter-name">
								add_action<span class="function-bracket text-green">(</span><span class="action-name text-blue">"vsz_cf7_after_datesection_btn"</span><span class="funciton-separater text-green">,</span><span class="action-name text-blue">"vsz_cf7_after_datesection_btn_callback"</span><span class="funciton-separater text-green">,</span><span class="function-piority text-pink">10</span><span class="funciton-separater text-green">,</span>
								<span class="function-param text-pink">1</span><span class="function-bracket text-green">)</span>;
							</div>
							<div class="function-def">
								<strong class="text-black">function</strong> <span class="function-defined">vsz_cf7_after_datesection_btn_callback</span><span class="function-bracket text-green">(</span><span class="function-val text-blue">$fid</span><span class="function-bracket text-green">)</span><span class="function-bracket text-green">{</span>
									<p>&nbsp;&nbsp;&nbsp;// Your custom coding here</p>
								<span class="function-bracket text-green">}</span>
							</div>
						</div>
					</td>
				</tr>
				<tr class="form-field">
					<th>vsz_cf7_after_bulkaction_btn</th>
					<td>
						<p><strong>Parameters : </strong>$fid</p>
						<p>Using this action,you can add custom coding after bulk action section in listing screen.</p>
						<div class='code-php'>
							<div class="action-filter-name">
								add_action<span class="function-bracket text-green">(</span><span class="action-name text-blue">"vsz_cf7_after_bulkaction_btn"</span><span class="funciton-separater text-green">,</span><span class="action-name text-blue">"vsz_cf7_after_bulkaction_btn_callback"</span><span class="funciton-separater text-green">,</span><span class="function-piority text-pink">10</span><span class="funciton-separater text-green">,</span>
								<span class="function-param text-pink">1</span><span class="function-bracket text-green">)</span>;
							</div>
							<div class="function-def">
								<strong class="text-black">function</strong> <span class="function-defined">vsz_cf7_after_bulkaction_btn_callback</span><span class="function-bracket text-green">(</span><span class="function-val text-blue">$fid</span><span class="function-bracket text-green">)</span><span class="function-bracket text-green">{</span>
									<p>&nbsp;&nbsp;&nbsp;// Your custom coding here</p>
								<span class="function-bracket text-green">}</span>
							</div>
						</div>
					</td>
				</tr>
				<tr class="form-field">
					<th>vsz_cf7_admin_after_heading_field</th>
					<td>
						<p><strong>Parameters : </strong>-</p>
						<p>Using this action,you can display table header in edit column.</p>
						<div class='code-php'>
							<div class="action-filter-name">
								add_action<span class="function-bracket text-green">(</span><span class="action-name text-blue">"vsz_cf7_admin_after_heading_field"</span><span class="funciton-separater text-green">,</span><span class="action-name text-blue">"vsz_cf7_admin_after_heading_field_callback"</span><span class="funciton-separater text-green">,</span><span class="function-piority text-pink">10</span><span class="function-bracket text-green">)</span>;
							</div>
							<div class="function-def">
								<strong class="text-black">function</strong> <span class="function-defined">vsz_cf7_admin_after_heading_field_callback</span><span class="function-bracket text-green">(</span><span class="function-bracket text-green">)</span><span class="function-bracket text-green">{</span>
									<p>&nbsp;&nbsp;&nbsp;// Your custom coding here</p>
								<span class="function-bracket text-green">}</span>
							</div>
						</div>
					</td>
				</tr>
				<tr class="form-field">
					<th>vsz_cf7_admin_after_body_field</th>
					<td>
						<p><strong>Parameters : </strong>$fid, $row_id</p>
						<p>Using this action, you can add custom coding before edit icon.</p>
						<div class='code-php'>
							<div class="action-filter-name">
								add_action<span class="function-bracket text-green">(</span><span class="action-name text-blue">"vsz_cf7_admin_after_body_field"</span><span class="funciton-separater text-green">,</span><span class="action-name text-blue">"vsz_cf7_admin_after_body_field_callback"</span><span class="funciton-separater text-green">,</span><span class="function-piority text-pink">10</span><span class="funciton-separater text-green">,</span>
								<span class="function-param text-pink">2</span><span class="function-bracket text-green">)</span>;
							</div>
							<div class="function-def">
								<strong class="text-black">function</strong> <span class="function-defined">vsz_cf7_admin_after_body_field_callback</span><span class="function-bracket text-green">(</span><span class="function-val text-blue">$fid</span><span class="funciton-separater text-green">,</span><span class="function-val text-blue">$row_id</span><span class="function-bracket text-green">)</span><span class="function-bracket text-green">{</span>
									<p>&nbsp;&nbsp;&nbsp;// Your custom coding here</p>
								<span class="function-bracket text-green">}</span>
							</div>
						</div>
					</td>
				</tr>
				<tr class="form-field">
					<th>vsz_cf7_after_admin_form</th>
					<td>
						<p><strong>Parameters : </strong>$fid</p>
						<p>Using this action, you can add custom coding after whole form.</p>
						<div class='code-php'>
							<div class="action-filter-name">
								add_action<span class="function-bracket text-green">(</span><span class="action-name text-blue">"vsz_cf7_after_admin_form"</span><span class="funciton-separater text-green">,</span><span class="action-name text-blue">"vsz_cf7_after_admin_form_callback"</span><span class="funciton-separater text-green">,</span><span class="function-piority text-pink">10</span><span class="funciton-separater text-green">,</span>
								<span class="function-param text-pink">1</span><span class="function-bracket text-green">)</span>;
							</div>
							<div class="function-def">
								<strong class="text-black">function</strong> <span class="function-defined">vsz_cf7_after_admin_form_callback</span><span class="function-bracket text-green">(</span><span class="function-val text-blue">$fid</span><span class="function-bracket text-green">)</span><span class="function-bracket text-green">{</span>
									<p>&nbsp;&nbsp;&nbsp;// Your custom coding here</p>
								<span class="function-bracket text-green">}</span>
							</div>
						</div>
					</td>
				</tr>
				<tr class="form-field">
					<th>vsz_cf7_before_insert_db</th>
					<td>
						<p><strong>Parameters : </strong>$contact_form</p>
						<p>Using this action, you can customize form data before insert in data base.</p>
						<div class='code-php'>
							<div class="action-filter-name">
								add_action<span class="function-bracket text-green">(</span><span class="action-name text-blue">"vsz_cf7_before_insert_db"</span><span class="funciton-separater text-green">,</span><span class="action-name text-blue">"vsz_cf7_before_insert_db_callback"</span><span class="funciton-separater text-green">,</span><span class="function-piority text-pink">10</span><span class="funciton-separater text-green">,</span><span class="function-param text-pink">1</span><span class="function-bracket text-green">)</span>;
							</div>
							<div class="function-def">
								<strong class="text-black">function</strong> <span class="function-defined">vsz_cf7_before_insert_db_callback</span><span class="function-bracket text-green">(</span><span class="function-val text-blue">$contact_form</span><span class="function-bracket text-green">)</span><span class="function-bracket text-green">{</span>
									<p>&nbsp;&nbsp;&nbsp;// Your custom coding here</p>
								<span class="function-bracket text-green">}</span>
							</div>
						</div>
					</td>
				</tr>
				<tr class="form-field">
					<th>vsz_cf7_after_insert_db</th>
					<td>
						<p><strong>Parameters : </strong>$contact_form, $cf7_id, $data_id</p>
						<p>Using this action, you can customize process after insert value in data base.</p>
						<div class='code-php'>
							<div class="action-filter-name">
								add_action<span class="function-bracket text-green">(</span><span class="action-name text-blue">"vsz_cf7_after_insert_db"</span><span class="funciton-separater text-green">,</span><span class="action-name text-blue">"vsz_cf7_after_insert_db_callback"</span><span class="funciton-separater text-green">,</span><span class="function-piority text-pink">10</span><span class="funciton-separater text-green">,</span>
								<span class="function-param text-pink">3</span><span class="function-bracket text-green">)</span>;
							</div>
							<div class="function-def">
								<strong class="text-black">function</strong> <span class="function-defined">vsz_cf7_after_insert_db_callback</span><span class="function-bracket text-green">(</span><span class="function-val text-blue">$fid</span><span class="funciton-separater text-green">,</span><span class="function-val text-blue">$cf7_id</span><span class="funciton-separater text-green">,</span><span class="function-val text-blue">$data_id</span><span class="function-bracket text-green">)</span><span class="function-bracket text-green">{</span>
									<p>&nbsp;&nbsp;&nbsp;// Your custom coding here</p>
								<span class="function-bracket text-green">}</span>
							</div>
						</div>
					</td>
				</tr>
			</table>
			<h3>Filters</h3>
			<table class="form-table striped bordered widefat fixed action-tbl" cellspacing="15px" >
				<tr class="form-field">
					<th>vsz_cf7_entry_order_by</th>
					<td>
						<p><strong>Parameters : </strong>String</p>
						<p>You can change the order of fields using this filter.</p>
						<div class='code-php'>
							<div class="action-filter-name">
								add_filter<span class="function-bracket text-green">(</span><span class="action-name text-blue">"vsz_cf7_entry_order_by"</span><span class="funciton-separater text-green">,</span><span class="action-name text-blue">"vsz_cf7_entry_order_by_callback"</span><span class="funciton-separater text-green">,</span><span class="function-piority text-pink">10</span><span class="funciton-separater text-green">,</span><span class="function-param text-pink">1</span><span class="function-bracket text-green">)</span>;
							</div>
							<div class="function-def">
								<strong class="text-black">function</strong> <span class="function-defined">vsz_cf7_entry_order_by_callback</span><span class="function-bracket text-green">(</span><span class="function-val text-blue">$order</span><span class="function-bracket text-green">)</span><span class="function-bracket text-green">{</span>
									<p>&nbsp;&nbsp;&nbsp;// Your custom coding here<br/>&nbsp;&nbsp;&nbsp;return $order;</p>
								<span class="function-bracket text-green">}</span>
							</div>
						</div>
					</td>
				</tr>
				<tr class="form-field">
					<th>vsz_cf7_entry_per_page</th>
					<td>
						<p><strong>Parameters : </strong>Integer (Default: 10)</p>
						<p>You can change the number of entries per page using this filter.</p>
						<div class='code-php'>
							<div class="action-filter-name">
								add_filter<span class="function-bracket text-green">(</span><span class="action-name text-blue">"vsz_cf7_entry_per_page"</span><span class="funciton-separater text-green">,</span><span class="action-name text-blue">"vsz_cf7_entry_per_page_callback"</span><span class="funciton-separater text-green">,</span><span class="function-piority text-pink">10</span><span class="funciton-separater text-green">,</span><span class="function-param text-pink">1</span><span class="function-bracket text-green">)</span>;
							</div>
							<div class="function-def">
								<strong class="text-black">function</strong> <span class="function-defined">vsz_cf7_entry_per_page_callback</span><span class="function-bracket text-green">(</span><span class="function-val text-blue">$num</span><span class="function-bracket text-green">)</span><span class="function-bracket text-green">{</span>
									<p>&nbsp;&nbsp;&nbsp;// Your custom coding here<br/>&nbsp;&nbsp;&nbsp;return $num;</p>
								<span class="function-bracket text-green">}</span>
							</div>
						</div>
					</td>
				</tr>
				<tr class="form-field">
					<th>vsz_display_character_count</th>
					<td>
						<p><strong>Parameters : </strong>Integer (Default: 30)</p>
						<p>This filter defines how many characters will be displayed in listing screen. You can change the number of characters displayed using this filter.</p>
						<div class='code-php'>
							<div class="action-filter-name">
								add_filter<span class="function-bracket text-green">(</span><span class="action-name text-blue">"vsz_display_character_count"</span><span class="funciton-separater text-green">,</span><span class="action-name text-blue">"vsz_display_character_count_callback"</span><span class="funciton-separater text-green">,</span><span class="function-piority text-pink">10</span><span class="funciton-separater text-green">,</span><span class="function-param text-pink">1</span><span class="function-bracket text-green">)</span>;
							</div>
							<div class="function-def">
								<strong class="text-black">function</strong> <span class="function-defined">vsz_display_character_count_callback</span><span class="function-bracket text-green">(</span><span class="function-val text-blue">$count</span><span class="function-bracket text-green">)</span><span class="function-bracket text-green">{</span>
									<p>&nbsp;&nbsp;&nbsp;// Your custom coding here<br/>&nbsp;&nbsp;&nbsp;return $count;</p>
								<span class="function-bracket text-green">}</span>
							</div>
						</div>
					</td>
				</tr>
				<tr class="form-field">
					<th>vsz_cf7_not_editable_fields</th>
					<td>
						<p><strong>Parameters : </strong>Array</p>
						<p>You can change non editable fields list using this filter.</p>
						<div class='code-php'>
							<div class="action-filter-name">
								add_filter<span class="function-bracket text-green">(</span><span class="action-name text-blue">"vsz_cf7_not_editable_fields"</span><span class="funciton-separater text-green">,</span><span class="action-name text-blue">"vsz_cf7_not_editable_fields_callback"</span><span class="funciton-separater text-green">,</span><span class="function-piority text-pink">10</span><span class="funciton-separater text-green">,</span><span class="function-param text-pink">1</span><span class="function-bracket text-green">)</span>;
							</div>
							<div class="function-def">
								<strong class="text-black">function</strong> <span class="function-defined">vsz_cf7_not_editable_fields_callback</span><span class="function-bracket text-green">(</span><span class="function-val text-blue">$arr</span><span class="function-bracket text-green">)</span><span class="function-bracket text-green">{</span>
									<p>&nbsp;&nbsp;&nbsp;// Your custom coding here<br/>&nbsp;&nbsp;&nbsp;return $arr;</p>
								<span class="function-bracket text-green">}</span>
							</div>
						</div>
					</td>
				</tr>
				<tr class="form-field">
					<th>vsz_cf7_entry_actions</th>
					<td>
						<p><strong>Parameters : </strong>Array</p>
						<p>You can add/remove any option for "Bulk Action" in listing screen.</p>
						<div class='code-php'>
							<div class="action-filter-name">
								add_filter<span class="function-bracket text-green">(</span><span class="action-name text-blue">"vsz_cf7_entry_actions"</span><span class="funciton-separater text-green">,</span><span class="action-name text-blue">"vsz_cf7_entry_actions_callback"</span><span class="funciton-separater text-green">,</span><span class="function-piority text-pink">10</span><span class="funciton-separater text-green">,</span><span class="function-param text-pink">1</span><span class="function-bracket text-green">)</span>;
							</div>
							<div class="function-def">
								<strong class="text-black">function</strong> <span class="function-defined">vsz_cf7_entry_actions_callback</span><span class="function-bracket text-green">(</span><span class="function-val text-blue">$arr</span><span class="function-bracket text-green">)</span><span class="function-bracket text-green">{</span>
									<p>&nbsp;&nbsp;&nbsp;// Your custom coding here<br/>&nbsp;&nbsp;&nbsp;return $arr;</p>
								<span class="function-bracket text-green">}</span>
							</div>
						</div>
					</td>
				</tr>
				<tr class="form-field">
					<th>vsz_cf7_import_date_format</th>
					<td>
						<p><strong>Parameters : </strong>-</p>
						<p>You can change the date format to import functionality with this filter.</p>
						<div class='code-php'>
							<div class="action-filter-name">
								add_filter<span class="function-bracket text-green">(</span><span class="action-name text-blue">"vsz_cf7_import_date_format"</span><span class="funciton-separater text-green">,</span><span class="action-name text-blue">"vsz_cf7_import_date_format_callback"</span><span class="funciton-separater text-green">,</span><span class="function-piority text-pink">10</span><span class="funciton-separater text-green"><span class="function-bracket text-green">)</span>;
							</div>
							<div class="function-def">
								<strong class="text-black">function</strong> <span class="function-defined">vsz_cf7_import_date_format_callback</span><span class="function-bracket text-green">(</span><span class="function-bracket text-green">)</span><span class="function-bracket text-green">{</span>
									<p>&nbsp;&nbsp;&nbsp;// Your custom coding here<br/>&nbsp;&nbsp;&nbsp;$format = "Y-m-d";<br/>&nbsp;&nbsp;&nbsp;return $format;</p>
								<span class="function-bracket text-green">}</span>
							</div>
						</div>
					</td>
				</tr>
				<tr class="form-field">
					<th>vsz_cf7_unwanted_form_data_submission</th>
					<td>
						<p><strong>Parameters : </strong>Array</p>
						<p>You can exclude contact form ids using this filter and those form entries won't be inserted in advanced contact form database.</p>
						<div class='code-php'>
							<div class="action-filter-name">
								add_filter<span class="function-bracket text-green">(</span><span class="action-name text-blue">"vsz_cf7_unwanted_form_data_submission"</span><span class="funciton-separater text-green">,</span><span class="action-name text-blue">"vsz_cf7_unwanted_form_data_submission_callback"</span><span class="funciton-separater text-green">,</span><span class="function-piority text-pink">10</span><span class="funciton-separater text-green">,</span><span class="function-piority text-pink">1</span><span class="function-bracket text-green">)</span>;
							</div>
							<div class="function-def">
								<strong class="text-black">function</strong> <span class="function-defined">vsz_cf7_unwanted_form_data_submission_callback</span><span class="function-bracket text-green">(</span><span class="function-val text-blue">$arr</span><span class="function-bracket text-green">)</span><span class="function-bracket text-green">{</span>
									<p>&nbsp;&nbsp;&nbsp;// Your custom coding here<br/>&nbsp;&nbsp;&nbsp;return $arr;</p>
								<span class="function-bracket text-green">}</span>
							</div>
						</div>
					</td>
				</tr>
				<tr class="form-field">
					<th>vsz_cf7_modify_form_before_insert_data</th>
					<td>
						<p><strong>Parameters : </strong>$contact_form</p>
						<p>You can change the entry data before it saved to database.</p>
						<div class='code-php'>
							<div class="action-filter-name">
								add_filter<span class="function-bracket text-green">(</span><span class="action-name text-blue">"vsz_cf7_modify_form_before_insert_data"</span><span class="funciton-separater text-green">,</span><span class="action-name text-blue">"vsz_cf7_modify_form_before_insert_data_callback"</span><span class="funciton-separater text-green">,</span><span class="function-piority text-pink">10</span><span class="funciton-separater text-green">,</span><span class="function-piority text-pink">1</span><span class="function-bracket text-green">)</span>;
							</div>
							<div class="function-def">
								<strong class="text-black">function</strong> <span class="function-defined">vsz_cf7_modify_form_before_insert_data_callback</span><span class="function-bracket text-green">(</span><span class="function-val text-blue">$contact_form</span><span class="function-bracket text-green">)</span><span class="function-bracket text-green">{</span>
									<p>&nbsp;&nbsp;&nbsp;// Your custom coding here<br/>&nbsp;&nbsp;&nbsp;return $contact_form;</p>
								<span class="function-bracket text-green">}</span>
							</div>
						</div>
					</td>
				</tr>
				<tr class="form-field">
					<th>vsz_cf7_posted_data</th>
					<td>
						<p><strong>Parameters : </strong>$posted_data</p>
						<p>This filter provided to the users to modify the data.Below is the process that can be performed</p>
						<p>1) Add new data to the CF7 Form<br>
							2) Can modify the existing form submitted data<br>
							3) Can unset or remove the existing form data of CF7</p>
						<div class='code-php'>
							<div class="action-filter-name">
								add_filter<span class="function-bracket text-green">(</span><span class="action-name text-blue">"vsz_cf7_posted_data"</span><span class="funciton-separater text-green">,</span><span class="action-name text-blue">"vsz_cf7_posted_data_callback"</span><span class="funciton-separater text-green">,</span><span class="function-piority text-pink">10</span><span class="funciton-separater text-green">,</span><span class="function-piority text-pink">1</span><span class="function-bracket text-green">)</span>;
							</div>
							<div class="function-def">
								<strong class="text-black">function</strong> <span class="function-defined">vsz_cf7_posted_data_callback</span><span class="function-bracket text-green">(</span><span class="function-val text-blue">$posted_data</span><span class="function-bracket text-green">)</span><span class="function-bracket text-green">{</span>
									<p>&nbsp;&nbsp;&nbsp;// Your custom coding here<br/>&nbsp;&nbsp;&nbsp;return $posted_data;</p>
								<span class="function-bracket text-green">}</span>
							</div>
						</div>
					</td>
				</tr>
				<tr class="form-field">
					<th>vsz_cf7_no_save_fields</th>
					<td>
						<p><strong>Parameters : </strong>Array</p>
						<p>You can exclude contact form ids using this filter and those form field's entry won't be inserted in advanced contact form database.</p>
						<div class='code-php'>
							<div class="action-filter-name">
								add_filter<span class="function-bracket text-green">(</span><span class="action-name text-blue">"vsz_cf7_no_save_fields"</span><span class="funciton-separater text-green">,</span><span class="action-name text-blue">"vsz_cf7_no_save_fields_callback"</span><span class="funciton-separater text-green">,</span><span class="function-piority text-pink">10</span><span class="funciton-separater text-green">,</span><span class="function-piority text-pink">1</span><span class="function-bracket text-green">)</span>;
							</div>
							<div class="function-def">
								<strong class="text-black">function</strong> <span class="function-defined">vsz_cf7_no_save_fields_callback</span><span class="function-bracket text-green">(</span><span class="function-val text-blue">$arr</span><span class="function-bracket text-green">)</span><span class="function-bracket text-green">{</span>
									<p>&nbsp;&nbsp;&nbsp;// Your custom coding here<br/>&nbsp;&nbsp;&nbsp;return $arr;</p>
								<span class="function-bracket text-green">}</span>
							</div>
						</div>
					</td>
				</tr>
				<tr class="form-field">
					<th>cf7d_entry_value</th>
					<td>
						<p><strong>Parameters : </strong>Value,Key</p>
						<p>You can modify specific field value using this filter.</p>
						<div class='code-php'>
							<div class="action-filter-name">
								add_filter<span class="function-bracket text-green">(</span><span class="action-name text-blue">"cf7d_entry_value"</span><span class="funciton-separater text-green">,</span><span class="action-name text-blue">"cf7d_entry_value_callback"</span><span class="funciton-separater text-green">,</span><span class="function-piority text-pink">10</span><span class="funciton-separater text-green">,</span><span class="function-piority text-pink">2</span><span class="function-bracket text-green">)</span>;
							</div>
							<div class="function-def">
								<strong class="text-black">function</strong> <span class="function-defined">cf7d_entry_value_callback</span><span class="function-bracket text-green">(</span><span class="function-val text-blue">$val</span><span class="function-val text-pink">,</span><span class="function-val text-blue">$key</span><span class="function-bracket text-green">)</span><span class="function-bracket text-green">{</span>
									<p>&nbsp;&nbsp;&nbsp;// Your custom coding here<br/>&nbsp;&nbsp;&nbsp;return $val;</p>
								<span class="function-bracket text-green">}</span>
							</div>
						</div>
					</td>
				</tr>
				<tr class="form-field">
					<th>vsz_cf7_admin_fields</th>
					<td>
						<p><strong>Parameters : </strong>Fields, fid</p>
						<p>You can modify fields displaying in listing screen using this filter.</p>
						<div class='code-php'>
							<div class="action-filter-name">
								add_filter<span class="function-bracket text-green">(</span><span class="action-name text-blue">"vsz_cf7_admin_fields"</span><span class="funciton-separater text-green">,</span><span class="action-name text-blue">"vsz_cf7_admin_fields_callback"</span><span class="funciton-separater text-green">,</span><span class="function-piority text-pink">10</span><span class="funciton-separater text-green">,</span><span class="function-piority text-pink">2</span><span class="function-bracket text-green">)</span>;
							</div>
							<div class="function-def">
								<strong class="text-black">function</strong> <span class="function-defined">vsz_cf7_admin_fields_callback</span><span class="function-bracket text-green">(</span><span class="function-val text-blue">$fields</span><span class="function-val text-pink">,</span><span class="function-val text-blue">$fid</span><span class="function-bracket text-green">)</span><span class="function-bracket text-green">{</span>
									<p>&nbsp;&nbsp;&nbsp;// Your custom coding here<br/>&nbsp;&nbsp;&nbsp;return $fields;</p>
								<span class="function-bracket text-green">}</span>
							</div>
						</div>
						
					</td>
				</tr>
				<tr class="form-field">
					<th>vsz_cf7_get_current_action</th>
					<td>
						<p><strong>Parameters : </strong>$current_action</p>
						<p>If you have added additional option in "Bulk Actions", then you need to do custom coding for that additional option over here.</p>
						<div class='code-php'>
							<div class="action-filter-name">
								add_filter<span class="function-bracket text-green">(</span><span class="action-name text-blue">"vsz_cf7_get_current_action"</span><span class="funciton-separater text-green">,</span><span class="action-name text-blue">"vsz_cf7_get_current_action_callback"</span><span class="funciton-separater text-green">,</span><span class="function-piority text-pink">10</span><span class="funciton-separater text-green">,</span><span class="function-piority text-pink">1</span><span class="function-bracket text-green">)</span>;
							</div>
							<div class="function-def">
								<strong class="text-black">function</strong> <span class="function-defined">vsz_cf7_get_current_action_callback</span><span class="function-bracket text-green">(</span><span class="function-val text-blue">$current_action</span><span class="function-bracket text-green">)</span><span class="function-bracket text-green">{</span>
									<p>&nbsp;&nbsp;&nbsp;// Your custom coding here<br/>&nbsp;&nbsp;&nbsp;return $current_action;</p>
								<span class="function-bracket text-green">}</span>
							</div>
						</div>
						
					</td>
				</tr>
			</table>
		</div>

	</div>
	<script>
		function openTab(evt, cityName) {
			// Declare all variables
			var i, tabcontent, tablinks;

			// Get all elements with class="tabcontent" and hide them
			tabcontent = document.getElementsByClassName("tabcontent");
			for (i = 0; i < tabcontent.length; i++) {
				tabcontent[i].style.display = "none";
			}

			// Get all elements with class="tablinks" and remove the class "active"
			tablinks = document.getElementsByClassName("tablinks");
			for (i = 0; i < tablinks.length; i++) {
				tablinks[i].className = tablinks[i].className.replace(" active", "");
			}

			// Show the current tab, and add an "active" class to the button that opened the tab
			document.getElementById(cityName).style.display = "block";
			evt.currentTarget.className += " active";
		}
	</script>
	<script type="text/javascript">//<![CDATA[
	(function () {
	  function htmlEscape(s) {
		return s
		  .replace(/&/g, '&amp;')
		  .replace(/</g, '&lt;')
		  .replace(/>/g, '&gt;');
	  }

	  // this page's own source code
	  var quineHtml = htmlEscape(
		'<!DOCTYPE html>\n<html>\n' +
		document.documentElement.innerHTML +
		'\n<\/html>\n');



	  // insert into PRE
	  jQuery("pre").each(function(){
		   // Highlight the operative parts:
			var htmlTag = jQuery(this).html().replace(
				/&lt;script src[\s\S]*?&gt;&lt;\/script&gt;|&lt;!--\?[\s\S]*?--&gt;|&lt;pre\b[\s\S]*?&lt;\/pre&gt;/g,
				'<span class="operative">$&<\/span>');
			jQuery(this).html = htmlTag;
	  });

	  // document.getElementById("quine").innerHTML = quineHtml;
	})();
	//]]>
	</script><?php
}