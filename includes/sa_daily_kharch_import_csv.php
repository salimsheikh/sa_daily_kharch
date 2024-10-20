<?php  
// Prevent direct access to the file
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Check if the class 'sa_daily_kharch_import_csv' already exists to prevent redeclaration  
if(!class_exists('sa_daily_kharch_import_csv')){
	
	class sa_daily_kharch_import_csv{
		
		// Property to store constants (table name, etc.)
		var $constants = array();
		
		// Constructor to initialize the constants
		function __construct($constants = array()){
			$this->constants = $constants;
		}
		
		// Function to render the admin menu page for CSV import
		function admin_menu_page()
		{
			$page = isset($_GET['page']) ? $_GET['page'] : '';
			$admin_url = 'admin.php?page='.$page; // Constructing admin URL for form action
			
			// HTML form for CSV upload with Bootstrap styling
			?>
            	<h2>Import CSV</h2>
				<!-- Load Bootstrap CSS -->
				<link rel="stylesheet" href="//cdn.jsdelivr.net/npm/bootstrap@4.4.1/dist/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">

				<!-- Form for CSV upload -->
				<div class="container">
					<form class="form-inline" action="<?php echo admin_url($admin_url)?>" method="post" name="upload_csv" enctype="multipart/form-data">
						<div class="form-group mx-sm-3 mb-2">
							<input type="file" class="form-control-file" id="dkh-import-csv" name="dkh-import-csv"> <!-- CSV file input -->
						</div>
						<input type="hidden" name="admin_page" value="<?php echo $page;?>" />
						<input type="hidden" name="call_action" value="<?php echo $page;?>" />
						<input type="hidden" name="sub_action" value="import-csv" />
						<button type="submit" class="btn btn-primary mb-2">Import</button> <!-- Styled submit button -->
					</form>
				</div>
            <?php
			
			// Set the table name for daily kharch records
			$table_name = $this->get_sample_table_name();
			$this->constants['daily_table_name'] = $table_name;
		}	
		
		// Function to handle admin initialization, including CSV import logic
		function admin_init(){
			$call_action = isset($_POST['call_action']) ? $_POST['call_action'] : '';
			if($call_action == 'dkh-import-csv') // Check if the call action is related to CSV import
			{
				// Get the uploaded file's temporary path
				$filename = isset($_FILES["dkh-import-csv"]["tmp_name"]) ? $_FILES["dkh-import-csv"]["tmp_name"] : '';

				// If no file is uploaded or file size is 0, return false
				if(empty($filename) || $_FILES["dkh-import-csv"]["size"] <= 0) {
				 	return false;
				 }	
				 
				// Import the CSV data into the database
				$this->import_csv_to_database($filename);
			}
		}
		
		// Function to import CSV data into the database
		function import_csv_to_database($filename = ''){
			global $wpdb;
			  
			// Open the CSV file for reading
			if(($file = fopen($filename, "r")) !== FALSE) {
				$row_number = 0;
				$table_name = $this->get_sample_table_name();
				$fields = array();
				$header_sql = "";
				$today_date = date_i18n("Y-m-d H:i:s");
				$user_id = get_current_user_id(); // Get the current user ID
				
				// Loop through the CSV rows
				while (($getData = fgetcsv($file, 10000, ",")) !== FALSE) {
					if($row_number <= 0){
						// Define the columns for the table (only for the first row/header)
						$fields = array('date','name','category','sub_category','type','amount','status','created_date','modified_date','created_by','modified_by','imported_by');
						$header_sql = "INSERT INTO `{$table_name}` (".implode(",",$fields).") values ";
					} else {
						// Skip empty rows
						if(empty($getData[0]) || empty($getData[1]) || empty($getData[2])) {
							continue;
						}

						// Sanitize the input data
						$n = array();
						foreach($getData as $key => $d) {
							$n[] = addslashes($d);
						}

						// Convert the date format
						$n[0] = date("Y-m-d", strtotime($n[0]));
						
						// Prepare the data array for insertion
						$data = array(
							'date' => $n[0],
							'name' => $n[1],
							'category' => $n[2],
							'sub_category' => $n[3],
							'type' => isset($n[4]) ? $n[4] : '',
							'amount' => number_format((float)(isset($n[5]) ? $n[5] : ''), 2, '.', ''),
							'status' => 'publish'
						);

						// Check if the record already exists in the table
						$sql = "SELECT id FROM `{$table_name}` WHERE 1*1 ";
						foreach($data as $f => $v) {
							$sql .= " AND {$f} IN ('{$v}')";
						}
						
						$id = $wpdb->get_var($sql); // Execute the query to get the ID
						
						// If the record does not exist, insert it
						if($id <= 0) {														
							try {								
								$data['created_date'] = $today_date;
								$data['modified_date'] = $today_date;
								$data['created_by'] = $user_id;
								$data['modified_by'] = $user_id;
								$data['imported_by'] = $user_id;
								
								// Insert the data into the table
								$wpdb->insert($table_name, $data);
								
								// Error handling
								if($wpdb->last_error) {
									error_log($wpdb->last_error);
									error_log($wpdb->last_query);
								}
							} catch(Exception $e) {
								error_log($e->getMessage());
							}
						}
					}
					$row_number++;
				}
				fclose($file); // Close the file after reading
			}
		
			/*
			DELETE FROM `wp_sa_daily_kharch` WHERE `date`  BETWEEN '2023-12-01' AND '2023-12-31'*/
			
			/*
			global $wpdb;
			$table_name = $this->constants['daily_table_name'];
			$wpdb->query("UPDATE {$table_name} SET type = 'Cash' WHERE type = 'cash'");
			$wpdb->query("UPDATE {$table_name} SET type = 'Cash' WHERE type = 'Offline'");
			$wpdb->query("DELETE FROM {$table_name} WHERE date = '2023-09-16' AND name = 'Rent' AND category = 'Ghar Kharch' AND sub_category = 'Pending Rent Amount'");
			$wpdb->query("UPDATE {$table_name} SET name = 'Ghar', category = 'Rent' WHERE sub_category = 'Part Rent Payment'");
			
			$sql = "UPDATE {$table_name} SET category = 'Personal Kharch' WHERE 1*1";
			$sql .= " AND name NOT IN ('Amina GK', 'Ghar')";
			$sql .= " AND category IN ('Ghar Kharch')";
			$wpdb->query($sql);
			
			$wpdb->query("UPDATE {$table_name} SET name = 'Husain' WHERE name = 'Husan'");
			$wpdb->query("UPDATE {$table_name} SET name = 'Husain' WHERE name = 'Hsain'");
			$wpdb->query("UPDATE {$table_name} SET name = 'Yusuf Bhai' WHERE name = 'Yusuf'");
			$wpdb->query("UPDATE {$table_name} SET name = 'Sadaka' WHERE name = 'Zakaat'");
			$wpdb->query("UPDATE {$table_name} SET name = 'Fatima' WHERE name = 'fatima'");			
			$wpdb->query("UPDATE {$table_name} SET name = 'Sadaka', category = 'Zakaat' WHERE date = '2023-11-18' AND name = 'Ghar' AND  category = 'Sadaka' AND  amount = '200'");			
			$wpdb->query("UPDATE {$table_name} SET name = 'Sadaka', category = 'Zakaat' WHERE date = '2023-11-20' AND name = 'Ghar' AND  category = 'Sadaka' AND  amount = '10'");
			
			*/
		}
		
		// Function to get or create the daily kharch table
		function get_sample_table_name()
		{
			global $wpdb;
			
			// Table name with the WordPress table prefix
			$table_name  = $wpdb->prefix."sa_daily_kharch";
			
			// Create the table if it doesn't exist
			if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
				$charset_collate = $wpdb->get_charset_collate();
				
				// SQL query to create the table with the specified structure
				$sql = "CREATE TABLE IF NOT EXISTS `{$table_name}`(
					`id` int(11) NOT NULL AUTO_INCREMENT,
					`date` date DEFAULT NULL,
					`name` varchar(50) NOT NULL,
					`category` varchar(50) NOT NULL,
					`sub_category` varchar(50) NOT NULL,
					`type` varchar(50) NOT NULL DEFAULT 'cash',
					`amount` double NOT NULL,
					`status` varchar(25) NOT NULL DEFAULT 'publish',
					`created_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
					`modified_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
					`created_by` int(11) NOT NULL DEFAULT '0',
					`modified_by` int(11) NOT NULL DEFAULT '0',
					`imported_by` int(11) NOT NULL DEFAULT '0',
					PRIMARY KEY (`id`),
					UNIQUE KEY `unique_column` (`date`,`name`,`category`,`sub_category`,`type`,`amount`,`status`)
				) ENGINE=MyISAM AUTO_INCREMENT=0 {$charset_collate};";
				
				// Include WordPress upgrade script for table creation
				if(!file_exists('dbDelta')) {
					require_once(ABSPATH.'wp-admin/includes/upgrade.php');
				}
				dbDelta($sql); // Execute the table creation
			}
			return $table_name; // Return the table name
		}
		
		// Debug function to print an array (for testing purposes)
		function print_array($array = array()){
			print("<pre>");
			print_r($array);
			print("</pre>");
		}
		
		// AJAX handler function (empty placeholder for now)
		function ajax(){
			die;
		}
	}
}