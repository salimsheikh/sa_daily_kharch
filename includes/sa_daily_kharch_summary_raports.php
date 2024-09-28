<?php
if(!class_exists('sa_daily_kharch_summary_reports')){
	
	class sa_daily_kharch_summary_reports{
		
		var $constants = array();
		
		function __construct($constants = array()){
			$this->constants = $constants;
            global $wpdb;			
			$wpdb->daily_kharch  = $wpdb->prefix."sa_daily_kharch";
			$this->constants['daily_table_name'] = $wpdb->daily_kharch;
            $this->constants['default_report_type'] = '';
		}

        function get_month_dropdown_data($group_by = 'month'){
            global $wpdb;
            $table_name = $this->constants['daily_table_name'];

            $sql = " SELECT ";
            switch($group_by){
                case "month":
                    $sql .= " DATE_FORMAT(date, '%Y-%m') AS value, DATE_FORMAT(date, '%Y %M') AS label";
                    break;
                default:
                    $sql .= " {$group_by} AS value, $group_by AS label";
                    break;                    
            }            
            $sql .= " FROM {$table_name} AS d";
            $sql .= " WHERE 1*1";            
            $sql .= " GROUP BY value";
            switch($group_by){
                case "month":
                    $sql .= " ORDER BY value DESC";
                    break;
                default:
                    $sql .= " ORDER BY value ASC";
                    break;                    
            }
            $items = $wpdb->get_results($sql);

            return $items;
        }

        function get_month_dropdown_fields(){
            $dropdowns = array(
                'month' => esc_html__('Month','textdomain'),
                'name' => esc_html__('Name','textdomain'),
                'category' => esc_html__('Category','textdomain'),
                'sub_category ' => esc_html__('Sub Category ','textdomain'),
                'type' => esc_html__('Type  ','textdomain')
            );

            

            $output = "";
            foreach($dropdowns as $fn => $fl){
                $items = $this->get_month_dropdown_data($fn);
                $output .= '<div class="col-md-3 mb-3">';
                $output .= '    <label for="'.$fn.'" class="form-label">Select '.$fl.'</label>';
                $output .= '    <select class="form-select" id="'.$fn.'" name="'.$fn.'">';
                $output .= '        <option value="" selected>Select '.$fl.'</option>';                
                                    foreach($items as $key => $item) {
                                        $output .= '<option value="'.$item->value.'">'.$item->label.'</option>';
                                    }                        
                $output .= '    </select>';
                $output .= '</div>';
            }
            return $output;
        }

        function update_data(){
            global $wpdb;
            $table_name = $this->constants['daily_table_name'];
            $wpdb->update($table_name, array('type'=>'Cash'), array('type'=>'Case'));
            $wpdb->update($table_name, array('type'=>'Cash'), array('type'=>'Offline'));
            $wpdb->update($table_name, array('type'=>'Online'), array('type'=>'online'));

            $wpdb->update($table_name, array('name'=>'Alfiya-Fatima'), array('name'=>'AlfiyaFatima'));
            $wpdb->update($table_name, array('name'=>'Alfiya-Fatima'), array('name'=>'AlfiyFatima'));
            $wpdb->update($table_name, array('name'=>'Alfiya-Fatima'), array('name'=>'FatimaAlfiya'));

            $wpdb->update($table_name, array('name'=>'Alfiya'), array('name'=>'Alifiya'));
            $wpdb->update($table_name, array('name'=>'Alfiya'), array('name'=>'Alifya'));


            $wpdb->update($table_name, array('name'=>'Saima'), array('name'=>'Sima'));
            $wpdb->update($table_name, array('name'=>'Husain'), array('name'=>'Husan'));
            $wpdb->update($table_name, array('name'=>'Husain'), array('name'=>'Hsain'));
            $wpdb->update($table_name, array('name'=>'Sadaka'), array('name'=>'Sadka'));

            
        }
		
		function admin_menu_page()
		{
            $report_type = isset($_GET['report_type']) ? $_GET['report_type'] : $this->constants['default_report_type'];
			$page = isset($_GET['page']) ? $_GET['page'] : '';
			$admin_url = 'admin.php?page='.$page;
            $start_date = date_i18n("Y-01-01");
			$end_date = date_i18n("Y-m-t");

            //$this->update_data();
            ?>
            
            <h1>Search Form</h1>           

            <form id="modal-form" name="modal-form" method="post" action="" class="row g-3">
                <div class="col-md-3 mb-3">
                    <label for="start_date" class="form-label">Start Date</label>
                    <input type="text" class="form-control start_date" id="start_date" name="start_date" value="<?php echo esc_attr($start_date);?>" >
                </div>
                <div class="col-md-3 mb-3">
                    <label for="end_date" class="form-label">End Date</label>
                    <input type="text" class="form-control end_date" id="end_date" name="end_date" value="<?php echo esc_attr($end_date);?>" >
                </div>
                <?php echo $this->get_month_dropdown_fields();?>
                <div class="col-md-3 mb-3 align-self-end">
                    <button type="submit" class="btn btn-primary">Search</button>
                </div>

                <input type="hidden" name="action" value="<?php print($this->constants['ajax_action'])?>" />
                <input type="hidden" name="sub_action" value="<?php print($page)?>" />
                <input type="hidden" name="admin_page" value="<?php print($page)?>" />
                <input type="hidden" name="report_type" value="<?php print($report_type)?>" />
                <input type="hidden" name="call_action" value="search_kharcha" />
            </form>
    
            <hr>
    
            <div class="search-alert alert" style="display:none;">Please Wait</div>
            <div class="search_results"></div>

            <style>
                .search_result{
                    margin-bottom:35px;
                }
                .right_align{ text-align:right;}
                .wp-core-ui select.form-select{
                    padding: 4px 8px;
                }
            </style>

            <script>
                var alert_interval = null;

                jQuery(document).on("html_loaded",function(){
                        new DataTable('._widefat.daily', {
                            searching: false,
                            lengthChange: false,
                            info: false,
                            stateSave: false,
                        });
                });

                jQuery(".search-alert").fadeOut();

                jQuery("#modal-form").submit(function(e){
						e.preventDefault();
						
						var alert_box = jQuery(".search-alert");
						alert_box.removeClass("alert-primary alert-danger alert-success").find(".modal-footer-message").html("Please enter the required field.");						
						alert_box.fadeIn();

                       
						alert_box.addClass("alert-primary").html("Please Wait!");
						
						var request = jQuery(this).serialize();
						
						jQuery("button").attr('disabled',true).addClass('disabled');
						jQuery.post(kharch_object.ajaxurl, request, function( data, status, xhr ) {
							
							data = JSON.parse(data);
							
							alert_box.removeClass("alert-primary");

                            jQuery("div.search_results").html(data.output);
							
							if(data.status == 1){
								alert_box.addClass("alert-success").html(data.message);
							}else{
								alert_box.addClass("alert-danger").html(data.message);
							}

                            alert_box.hide();

                            jQuery(document).trigger("html_loaded");
							
							alert_interval = setInterval(function () {
								// alert_box.removeClass("alert-primary alert-danger alert-success");
                                alert_box.fadeOut();
								jQuery("button").attr('disabled',false).removeClass('disabled');
								clearInterval(alert_interval);
							}, 1000);
							
						}).fail(function() { 
							alert_interval = setInterval(function () {								
								alert_box.removeClass("alert-primary alert-danger alert-success");								
                                alert_box.fadeOut();
								jQuery("button").attr('disabled',false).removeClass('disabled');
								clearInterval(alert_interval);
							}, 5000);
						})
                });
            </script>
        <?php
		}

        function get_columns($report_type){
            $columns = array();
            switch($report_type){
                case "daily":
                    $columns['date'] = esc_html__('Date', 'textdomain');
                    $columns['name'] = esc_html__('Name', 'textdomain');
                    $columns['category'] = esc_html__('Categroy', 'textdomain');
                    $columns['sub_category'] = esc_html__('Sub Category', 'textdomain');
                    $columns['type'] = esc_html__('Type', 'textdomain');
                    $columns['amount'] = esc_html__('Amount', 'textdomain');
                    break;
                case "monthly":
                    $columns['month'] = esc_html__('Month', 'textdomain');
                    $columns['amount'] = esc_html__('Amount', 'textdomain');
                    break;
            }
            return $columns;
        }

        function get_where_query(){
			
			$report_type = isset($_POST['report_type']) ? $_POST['report_type'] : $this->constants['default_report_type'];
			$start_date = isset($_POST['start_date']) ? $_POST['start_date'] : '';
			$end_date = isset($_POST['end_date']) ? $_POST['end_date'] : '';
            $month = isset($_POST['month']) ? $_POST['month'] : '';

            $fields = array('month','name','category','sub_category','type');

            $sql = "";
            foreach($fields as $fn){
                $fv = isset($_POST[$fn]) ? $_POST[$fn] : '';
                if($fv != ""){
                    switch($fn){
                        case "month":
                            $sql .= " AND DATE_FORMAT(date, '%Y-%m') = '{$fv}'";
                            break;
                        default:
                            $sql .= " AND {$fn} = '{$fv}'";
                            break;
                    }
                }
            }			
			if($start_date != "" and $end_date != ""){
				$sql .= " AND date BETWEEN '{$start_date}' AND '{$end_date}'";
			}
			
			return($sql);
		}

        function get_daily_report($report_type = ''){
            global $wpdb;

            $table_name = $this->constants['daily_table_name'];

            $sql = " SELECT date,name,category,sub_category,type, amount";
            $sql .= " FROM {$table_name} AS d";
            $sql .= " WHERE 1*1";
            $sql .= $this->get_where_query();
            $sql .= " GROUP BY date";
            $sql .= " ORDER BY date ASC";
            
            $items = $wpdb->get_results($sql);

            return $this->get_grid($items,'datatable',$report_type);
        }

        function get_montly_report($report_type = ''){
            global $wpdb;

            $table_name = $this->constants['daily_table_name'];

            $sql = " SELECT ";							
            $sql .= " SUM(ROUND(amount,2)) AS amount";
            $sql .= " ,DATE_FORMAT(date, '%Y-%m') AS  month";
            $sql .= " FROM {$table_name} AS d";
            $sql .= " WHERE 1*1";
            $sql .= $this->get_where_query();
            $sql .= " GROUP BY month";
            $sql .= " ORDER BY month ASC";
            
            $items = $wpdb->get_results($sql);

            return $this->get_grid($items,'',$report_type);
        }

        function get_items(){
           
            $output = "";
            $output .= $this->get_daily_report('daily');
            $output .= $this->get_montly_report('monthly');

            return $output;
        }

        function get_grid($items = array(),$datatable = 'datatable', $report_type = '')
		{
			$columns = $this->get_columns($report_type);
            $output  =  "";
			
            $output .= "<div class=\"search_result {$report_type}\">";
			$output .= "<table border=\"0\" cellpadding=\"0\" cellspacing=\"0\" class=\"_widefat {$report_type} display compact table table-bordered table-striped\" style=\"width:100%\">";				
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
											$field_value = $field_value == "" ? 0 : number_format($field_value,2,".","");
											$field_class .= " amount right_align";
											break;
										case "saving":
											$field_value = $income - $amount;
											$field_value = $field_value == "" ? 0 : number_format($field_value,2,".","");
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
            $output .= "</div>";
					
			return($output);
		}
		
		function print_array($array = array()){
			print("<pre>");
			print_r($array);
			print("</pre>");
		}
		
		function ajax()
		{
            $return = array();
            $return['status'] = 1;
            $return['message'] = "Successfully found.";
            $return['output'] = $this->get_items();

            echo json_encode($return);
			die;
		}
	}
}


