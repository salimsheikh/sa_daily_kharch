<?php
if(!class_exists('sa_daily_kharch_reports')){
	
	class sa_daily_kharch_reports{
		
		var $constants = array();
		
		function __construct($constants = array()){
			$this->constants = $constants;
			global $wpdb;			
			$wpdb->daily_kharch  = $wpdb->prefix."sa_daily_kharch";
			
			$page = isset($_GET['page']) ? $_GET['page'] : '';
			$this->constants['daily_table_name'] = $wpdb->daily_kharch;
			
			if($page == 'dkh-montly-report'){
				$this->constants['default_report_type'] = 'monthly';
			}else{
				$this->constants['default_report_type'] = '';
			}
		}
		
		function admin_menu_page()
		{
			$report_type = isset($_GET['report_type']) ? $_GET['report_type'] : $this->constants['default_report_type'];
			$page = isset($_GET['page']) ? $_GET['page'] : '';
			$admin_url = 'admin.php?page='.$page;
			
			$columns = $this->get_columns('');
			
			$items = $this->get_months_items('grid');
			$output = $this->get_grid($items,'datatable');
			
			$output .= "<br /><br />";
			
			$items = $this->get_months_items('totaltable');
			$output .= $this->get_grid($items,'totaltable');
			
			//$this->print_array($items);
			
			$start_date = date_i18n("Y-m-01");
			$end_date = date_i18n("Y-m-t");
			
			?>
            	 <style type="text/css">
                	.top_title_row{
						display: flex;
						justify-content: space-between;
						align-items: center;
						width:100%;
					}
                </style>
                <!--<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">-->
            	<div class="top_title_row">
                	<h1>Report</h1>
                    <div>
                    	<button type="button" class="button_dispaly_modal button button-primary" data-id="0"><?php esc_html_e('New','textdomain');?></button>
                    </div>
                </div>
                <form id="search-modal-form" name="search-modal-form" method="post" action="">
                    <div class="row  g-3">
                        <div class="col-auto">                                            
                            <input type="text" name="start_date" id="start_date" class="form-control" value="<?php echo esc_attr($start_date);?>" />                            
                         </div>
                         <div class="col-auto">                            
                            <input type="text" name="end_date" id="end_date" class="form-control" value="<?php echo esc_attr($end_date);?>" />
                         </div>
                         <div class="col-auto">
                            <button type="submit" class="btn btn-primary mb-3">Search</button>
                          </div>
                    </div>
                    <input type="hidden" name="action" value="<?php print($this->constants['ajax_action'])?>" />
                    <input type="hidden" name="sub_action" value="<?php print($page)?>" />
                    <input type="hidden" name="admin_page" value="<?php print($page)?>" />
                    <input type="hidden" name="report_type" value="<?php print($report_type)?>" />
                    <input type="hidden" name="call_action" value="search_kharcha" />
                </form>
                <div class="custom_diynamic_filters form-row"></div>
                
                <div class="search_results">
                	<?php print($output);?>
                </div>
                <!--<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>-->
                <!-- The Modal -->
                <div id="myModal" class="modal">                
                  <!-- Modal content -->
                  <div class="modal-content">
                  	<form id="modal-form" name="modal-form" method="post" action="">
                    <div class="modal-header">
                      <h2><?php esc_html_e('Create New Entry','textdomain');?></h2>
                      <span class="modal-close button_close_modal">&times;</span>
                    </div>
                    <div class="modal-body">
                      <div class="alert alert-danger" role="alert"></div>
                      <?php
                      	
						
						$output = "";
						$date = date_i18n("Y-m-d");
						foreach($columns as $field_name => $column_label){
							switch($field_name){
								case "action":
									break;
								default:
									$input_value = '';
									$output .= '<div class="form-group row">';
										$output .= '<label for="'.esc_html($field_name).'" class="col-sm-3 col-form-label">'.esc_html($column_label).'</label>';
										$output .= '<div class="col-sm-9">';
											switch($field_name){
												case "date":
													$input_value = $date;
													break;
												case "type":
													$input_value = 'Cash';
													break;
												default:
													$input_value = 'Ghar Kharch';
													break;
											}
											$output .= '<input type="text" class="form-control required_filed edit_filed '.esc_html($field_name).'" name="update_row['.esc_html($field_name).']" id="'.esc_html($field_name).'" placeholder="'.esc_html($column_label).'" value="'.$input_value.'">';
										$output .= '</div>';
									$output .= '</div>';								
									break;
							}
							
						}						
						print($output);
					  ?>
                    </div>
                    <div class="modal-footer">
                      <div>
                      	<div class="modal-footer-message"></div>
                      </div>
                      <div>
                      		<button type="button" class="button_close_modal button button-primary"><?php esc_html_e('Close')?></button>
                            <button type="submit" class="button_create_row button button-primary "><?php esc_html_e('Create')?></button>
                      </div>
                    </div>
                    <input type="hidden" name="action" value="<?php print($this->constants['ajax_action'])?>" />
                    <input type="hidden" name="sub_action" value="<?php print($page)?>" />
                    <input type="hidden" name="admin_page" value="<?php print($page)?>" />
                    <input type="hidden" id="entery_id" name="id" value="0" />
                    <input type="hidden" name="call_action" value="save_kharcha" />
                    </form>
                  </div>                
                </div>
                <style type="text/css">
                	/* The Modal (background) */
					.modal {
					  display: none; /* Hidden by default */
					  position: fixed; /* Stay in place */
					  z-index: 1; /* Sit on top */
					  padding-top: 60px; /* Location of the box */
					  left: 0;
					  top: 0;
					  width: 100%; /* Full width */
					  height: 100%; /* Full height */
					  overflow: auto; /* Enable scroll if needed */
					  background-color: rgb(0,0,0); /* Fallback color */
					  background-color: rgba(0,0,0,0.4); /* Black w/ opacity */
					}
					
					/* Modal Content */
					.modal-content {
					  position: relative;
					  background-color: #fefefe;
					  margin: auto;
					  padding: 0;
					  border: 1px solid #888;
					  width: 600px;
					  box-shadow: 0 4px 8px 0 rgba(0,0,0,0.2),0 6px 20px 0 rgba(0,0,0,0.19);
					  -webkit-animation-name: animatetop;
					  -webkit-animation-duration: 0.4s;
					  animation-name: animatetop;
					  animation-duration: 0.4s;
					}
					
					@media screen and (max-width: 700px) {
						.modal-content {
							width:90%;
						}
					}
					
					/* Add Animation */
					@-webkit-keyframes animatetop {
					  from {top:-300px; opacity:0} 
					  to {top:0; opacity:1}
					}
					
					@keyframes animatetop {
					  from {top:-300px; opacity:0}
					  to {top:0; opacity:1}
					}
					
					/* The Close Button */
					.modal-close {
					  color: rgba(0, 0, 0, 0.5);					  
					  font-weight: bold;
					}
					
					.modal-close:hover,
					.modal-close:focus {
					  color: #000;
					  text-decoration: none;
					  cursor: pointer;
					}
					
					.modal-header {
						height: 52px;
						width: 100%;
						background-color: #e8e9eb;
						border-bottom: 1px solid rgba(0, 0, 0, 0.15);
						padding: 0px 10px 0px 16px;
						font-size: 1.3em;
						box-sizing: border-box;
						
						display: flex;
						justify-content: space-between;
						align-items: center;
					}
					
					.modal-header h2{
						font-size:15px;
						margin:auto;
						text-align:left;
						margin-left:0;
					}
					
					.modal-body {padding: 25px 25px 10px 25px;}
					
					.modal-footer {
						height: 52px;
						width: 100%;
						background-color: #e8e9eb;
						border-top: 1px solid rgba(0, 0, 0, 0.15);
						padding: 0px 10px 0px 16px;
						box-sizing: border-box;
						
						display: flex;
						justify-content: space-between;
						align-items: center;
					}
					.ui-autocomplete {
						max-height: 200px;
						overflow-y: auto;
						/* prevent horizontal scrollbar */
						overflow-x: hidden;
						/* add padding to account for vertical scrollbar */
						padding-right: 20px;
					} 
					.ui-menu .ui-menu-item-wrapper {
						position: relative;
						padding: 2px;
					}
					.ui-autocomplete li {
						padding: 2px 10px;
						font-size:12px;
					}
					
					input[type=text].invalid_field,
					select.invalid_field{
						border-color:red;
					}
					
					.modal-footer.alert-primary{
						color: #004085;
						background-color: #cce5ff;
						border-color: #b8daff;
					}
					
					.modal-footer.alert-danger{
						color: #721c24;
						background-color: #f8d7da;
						border-color: #f5c6cb;
					}
					
					.modal-footer.alert-success{
						color: #155724;
						background-color: #d4edda;
						border-color: #c3e6cb;
					}
                </style>                
                <script type="text/javascript">
                	var json_data = <?php print(json_encode($this->get_autofill_data()));?>;
					var alert_interval = null;
					if(jQuery.isFunction(jQuery.fn.autocomplete) ) {
						jQuery("input#name").autocomplete({source: json_data.name,minLength:1,max:10,scroll:true});
						jQuery("input#category").autocomplete({source: json_data.category,minLength:1,max:10,scroll:true});
						jQuery("input#sub_category").autocomplete({source: json_data.sub_category,minLength:1,max:10,scroll:true});
						jQuery("input#type").autocomplete({source: json_data.type,minLength:1,max:10,scroll:true});
					}
					if(jQuery.isFunction(jQuery.fn.datepicker) ) {
						jQuery("input#date, input#start_date, input#end_date").datepicker({
							'dateFormat' : 'yy-mm-dd'
						});
					}
					jQuery(".alert").fadeOut();
					
					jQuery("#search-modal-form").submit(function(e){
						e.preventDefault();						
						var request = jQuery("#search-modal-form").serialize();						
						jQuery.post(kharch_object.ajaxurl, request, function( data, status, xhr ) {
							data = JSON.parse(data);							
							jQuery('.search_results').html(data.output);
						}).fail(function() { 
							console.log("failed");
						})
					});
						
					jQuery("#modal-form").submit(function(e){
						e.preventDefault();
						
						var alert_box = jQuery(".modal-footer");
						alert_box.removeClass("alert-primary alert-danger alert-success").find(".modal-footer-message").html("Please enter the required field.");
						
						jQuery("input.required_filed").removeClass("invalid_field");
						
						var valid_field = true;
						jQuery(".required_filed").each(function(index, element){
							var v = jQuery(element).val();
							if(v === "" || v === 0){
								valid_field = false;
								jQuery(element).addClass('invalid_field');
							}
						});
						
						alert_box.fadeIn();
						
						if(!valid_field){
							alert_box.addClass("alert-danger").find(".modal-footer-message").html("Please enter the required field.");
							return false;
						}
						
						alert_box.addClass("alert-primary").find(".modal-footer-message").html("Please Wait!");
						
						var request = jQuery(this).serialize();
						
						jQuery("button").attr('disabled',true).addClass('disabled');
						jQuery.post(kharch_object.ajaxurl, request, function( data, status, xhr ) {
							
							data = JSON.parse(data);
							
							alert_box.removeClass("alert-primary");
							
							jQuery('.required_filed').removeClass('invalid_field');
							
							if(data.status == 1){
								alert_box.addClass("alert-success").find(".modal-footer-message").html(data.message);
							}else{
								alert_box.addClass("alert-danger").find(".modal-footer-message").html(data.message);
							}
							
							alert_interval = setInterval(function () {
								//alert_box.fadeOut();
								alert_box.removeClass("alert-primary alert-danger alert-success");
								alert_box.find(".modal-footer-message").html("");
								jQuery("button").attr('disabled',false).removeClass('disabled');
								clearInterval(alert_interval);
							}, 1000);
							
						}).fail(function() { 
							console.log("Found some issue.");
							alert_interval = setInterval(function () {
								//alert_box.fadeOut();
								alert_box.find(".modal-footer-message").html("");
								jQuery("button").attr('disabled',false).removeClass('disabled');
								clearInterval(alert_interval);
							}, 5000);
						})
					});
                </script>
            <?php
		}
		
		function get_autofill_data(){
			$json_data = array();
			$json_data['name'] = $this->get_json_data('name');
			$json_data['category'] = $this->get_json_data('category');
			$json_data['sub_category'] = $this->get_json_data('sub_category');
			$json_data['type'] = $this->get_json_data('type');			
			return $json_data;
		}
		
		function get_json_data($field = 'date'){
			global $wpdb;
			$table_name = $this->constants['daily_table_name'];
			
			$sql = " SELECT {$field} AS v";
			$sql .= " FROM {$table_name} AS d";
			$sql .= " WHERE 1*1";
			$sql .= " GROUP BY {$field}";
			$sql .= " ORDER BY {$field} ASC";
			$items = $wpdb->get_results($sql);
			$data = array();
			foreach($items as $key => $item){
				$data[] = $item->v;
			}
			return $data;
		}
				
		function get_months_items($type = 'grid', $datatable = 'grid'){
			global $wpdb;
			$table_name = $this->constants['daily_table_name'];
			$report_type = isset($_POST['report_type']) ? $_POST['report_type'] : $this->constants['default_report_type'];
			if($report_type == 'monthly'){
				
				$sql = " SELECT date,name,category,sub_category,type";
				if($datatable == 'totaltable'){
					echo $datatable;
					$sql .= " ,COUNT(*) AS count";
				}				
				$sql .= " ,SUM(ROUND(amount,2)) AS amount";
				$sql .= " ,DATE_FORMAT(date, '%Y-%m') AS  month";
				$sql .= " FROM {$table_name} AS d";
				$sql .= " WHERE 1*1";
				$sql .= $this->get_where_query();
				//$sql .= " AND name IN ('Amina')";
				if($type == 'grid'){
					$sql .= " GROUP BY month";
				}				
				$sql .= " ORDER BY month DESC";
				$items = $wpdb->get_results($sql);
			}else{
				$sql = " SELECT id,date,name,category,sub_category,type";	
				if($datatable == 'totaltable'){
					$sql .= " ,SUM(ROUND(amount,2)) AS amount";
					$sql .= " ,COUNT(*) AS count";					
				}else{
					$sql .= " ,amount";
				}		
				$sql .= " FROM {$table_name} AS d";
				$sql .= " WHERE 1*1";
				//$sql .= " AND name NOT IN ('Amina GK', 'Ghar')";
				//$sql .= " AND category IN ('Ghar Kharch')";
				$sql .= $this->get_where_query();
				$sql .= " ORDER BY date DESC";
			}
			
			$items = $wpdb->get_results($sql);		
				
			return($items);
		}
		
		function get_where_query(){
			
			$report_type = isset($_POST['report_type']) ? $_POST['report_type'] : $this->constants['default_report_type'];
			$start_date = isset($_POST['start_date']) ? $_POST['start_date'] : '';
			$end_date = isset($_POST['end_date']) ? $_POST['end_date'] : '';
			$sql = "";			
			if($start_date != "" and $end_date != ""){
				$sql .= " AND date BETWEEN '{$start_date}' AND '{$end_date}'";
			}
			
			return($sql);
		}
		
		function get_columns($report_type = '')
		{
			$columns = array();
			if($report_type == 'monthly'){
				$columns['month'] = esc_html__('Month', 'textdomain');				
				$columns['amount'] = esc_html__('Amount', 'textdomain');
			}else{
				$columns['date'] = esc_html__('Date', 'textdomain');
				$columns['name'] = esc_html__('Name', 'textdomain');
				$columns['category'] = esc_html__('Categroy', 'textdomain');
				$columns['sub_category'] = esc_html__('Sub Category', 'textdomain');
				$columns['type'] = esc_html__('Type', 'textdomain');
				$columns['amount'] = esc_html__('Amount', 'textdomain');
			}
			
			return $columns;
		}
		
		function get_grid($items = array(),$datatable = 'datatable')
		{
			$report_type = isset($_POST['report_type']) ? $_POST['report_type'] : $this->constants['default_report_type'];
			$columns = $this->get_columns($report_type);
			
			if($report_type != "monthly"){
				$columns['action'] = esc_html__('Action','textdomain');
			}
			
			if($report_type == "monthly"){
				$columns['income'] = esc_html__('Income','textdomain');
				$columns['saving'] = esc_html__('Saving','textdomain');
				
				if($datatable == "totaltable"){
					$this->print_array($columns);
					$this->print_array($datatable);
					$this->print_array($items);
					foreach($items as $key => $item){
						$items[$key]->income = 66800;
					}
				}else{
					foreach($items as $key => $item){
						$items[$key]->income = 66800;
					}
				}
			}
			
			$output = "".$datatable;
			$output .= "<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" class=\"_widefat {$datatable} daily_kharch_report display compact\" style=\"width:100%\">";				
			$output .= "<thead>";				
				$output .= "<tr>";
					foreach($columns as $field_name => $column_label){
						$field_class = $field_name;
						switch($field_name){
							case "amount":
							case "income":
							case "saving":
								$field_class .= " amount right_align";
								break;
							case "action":
								$field_class .= " amount right_align";
								break;
						}
						$output .= "<th class=\"{$field_class}\">".esc_html($column_label)."</th>";
					}					
				$output .= "</tr>";
			$output .= "</thead>";
			$output .= "<tbody>";					
					foreach($items as $key => $item){
							$id = isset($item->id) ? $item->id : 0;
							$amount = isset($item->amount) ? $item->amount : 0;
							$income = isset($item->income) ? $item->income : 0;
							$output .= "<tr>";							
							foreach($columns as $field_name => $column_label){
									$field_class = $field_name;
									$field_value = isset($item->$field_name) ? $item->$field_name : '';
									switch($field_name){
										case "amount":
										case "income":
											$field_value = number_format($field_value,2,".","");
											$field_class .= " amount right_align";
											break;
										case "saving":
											$field_value = $income - $amount;
											$field_value = number_format($field_value,2,".","");
											$field_class .= " amount right_align";
											break;
										case "action":
											$field_value = "<a class=\"button button_dispaly_modal\" data-id=\"{$id}\">Edit</a>";
											$field_class .= " amount right_align";
											break;
									}
									$output .= "<td class=\"{$field_class}\">{$field_value}</td>";
							}
							$output .= "</tr>";
					}				
			$output .= "</tbody>";
			$output .= "</table>";
					
			return($output);
		}
		
		function print_array($array = array()){
			print("<pre>");
			print_r($array);
			print("</pre>");
		}
		
		function save_kharcha($return){
			global $wpdb;
			
			error_log(print_r($_POST,true));
			
			$table_name = $this->constants['daily_table_name'];
			
			$data = array();
			$data['date'] = $update_row['date'];
			$data['name'] = $update_row['name'];
			$data['category'] = $update_row['category'];
			$data['sub_category'] = $update_row['sub_category'];
			$data['type'] = $update_row['type'];
			$data['amount'] = $update_row['amount'];
			$data['status'] = 'publish';
			
			$sql = "SELECT  id ";
			$sql .= " FROM `{$table_name}`";			
			$sql .= " WHERE 1*1 ";
			foreach($data as $f => $v){
				$sql .= " AND {$f} IN ('{$v}')";
			}
			
			$id = $wpdb->get_var($sql);
			
			if($wpdb->last_error){
				error_log($wpdb->last_error);
				error_log($wpdb->last_query);
			}
			
			$wpdb->show_errors = 0;	
			
			$today_date = date_i18n("Y-m-d H:i:s");
			$user_id = get_current_user_id(); 
									
			if($id<=0){														
				try{
					
					$data['created_date'] = $today_date;
					$data['modified_date'] = $today_date;
					$data['created_by'] = $user_id;
					$data['modified_by'] = $user_id;
					$data['imported_by'] = $user_id;
					$wpdb->insert($table_name,$data);
					if($wpdb->last_error){
						error_log($wpdb->last_error);
						error_log($wpdb->last_query);
					}else{
						$return['status'] = 1;
						$return['message'] = esc_html__('Saved Successfully.','textdomain');
					}
					
				}catch(Exception $e){
					error_log($e->getMessage());
				}								
			}else{
				error_log($id);
				error_log($sql);				
				$return['message'] = esc_html__('Found the duplicate entry.','textdomain');
			}
			
			return $return;
		}
		
		function search_kharcha($return = array()){			
			$items = $this->get_months_items();
			$output = $this->get_grid($items);
			
			$return['output'] = $output;
			return $return;
		}
		
		function ajax()
		{
			global $wpdb;
			$return = array();
			$return['status'] = 0;
			$return['message'] = esc_html__('Found some issue.','textdomain');
			
			$call_action = isset($_POST['call_action']) ? $_POST['call_action'] : '';
			$update_row = isset($_POST['update_row']) ? $_POST['update_row'] : '';
			
			
			//error_log(print_r($_POST,true));
			if($call_action == 'save_kharcha'){
				$return = $this->save_kharcha($return);
			}
			
			if($call_action == 'search_kharcha'){
				$return = $this->search_kharcha($return);
			}
			
			echo json_encode($return);
			die;
		}/*AJAX END*/
	}/*Class End*/
}