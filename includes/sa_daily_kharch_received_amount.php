<?php
if(!class_exists('sa_daily_kharch_received_amount')){
	
	class sa_daily_kharch_received_amount{
		
		var $constants = array();
		
		function __construct($constants = array()){
			$this->constants = $constants;
            global $wpdb;			
			$wpdb->daily_kharch  = $wpdb->prefix."sa_daily_kharch";
			$this->constants['daily_table_name'] = $wpdb->daily_kharch;
            $this->constants['default_report_type'] = '';
		}

        
		
		function admin_menu_page()
		{
            $report_type = isset($_GET['report_type']) ? $_GET['report_type'] : $this->constants['default_report_type'];
			$page = isset($_GET['page']) ? $_GET['page'] : '';
			$admin_url = 'admin.php?page='.$page;
            $start_date = date_i18n("Y-01-01");
			$today_date = date_i18n("Y-m-d");

            $recieved_amount = isset($_POST['recieved_amount']) ? $_POST['recieved_amount'] : '10000';
            $recieved_by = isset($_POST['recieved_by']) ? $_POST['recieved_by'] : 'InfoSoft Salery';
            ?>            
            <h1>Received Amount</h1>
            <form method="post" action="<?php echo admin_url("admin.php?page={$page}"); ?>">
                <div class="form-group">
                    <label for="recieved_date">Received Date</label>
                    <input type="text" class="form-control recieved_date" id="recieved_date" name="recieved_date" value="<?php echo $today_date;?>" required>
                </div>

                <div class="form-group">
                    <label for="recieved_amount">Amount</label>
                    <input type="text" class="form-control" id="recieved_amount" name="recieved_amount"  value="<?php echo $recieved_amount;?>" pattern="^\d+(\.\d{1})?$" title="Only numbers with one decimal place are allowed" required>
                </div>

                <div class="form-group">
                    <label for="amount_type">Type of Amount</label>
                    <select class="form-control" id="amount_type" name="amount_type" required>
                        <option value="Salery">Salery</option>
                        <option value="Udhari">Udhari</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="recieved_by">Received By</label>
                    <input type="text" class="form-control" id="recieved_by" name="recieved_by" required autocomplete="off" value="<?php echo $recieved_by;?>">
                    <ul id="autocomplete-list" class="list-group"></ul>
                </div>

                <input type="hidden" name="action" value="submit_received_amount">
                <button type="submit" class="btn btn-primary">Submit</button>
            </form>          
            <script>
                document.getElementById('recieved_by').addEventListener('input', function() {
                    let input = this.value.toLowerCase();
                    let suggestions = ["deepak", "saima"];
                    let filtered = suggestions.filter(name => name.toLowerCase().includes(input));

                    let list = document.getElementById('autocomplete-list');
                    list.innerHTML = '';
                    filtered.forEach(item => {
                        let listItem = document.createElement('li');
                        listItem.classList.add('list-group-item');
                        listItem.innerText = item;
                        listItem.addEventListener('click', function() {
                            document.getElementById('recieved_by').value = item;
                            list.innerHTML = '';
                        });
                        list.appendChild(listItem);
                    });
                });
            </script>
        <?php
		}

         // Method to handle form submission
    public function handle_form_submission() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'submit_received_amount') {
            global $wpdb;

            // Sanitize and validate form inputs
            $recieved_date = sanitize_text_field($_POST['recieved_date']);
            $recieved_amount = floatval($_POST['recieved_amount']);
            $amount_type = sanitize_text_field($_POST['amount_type']);
            $recieved_by = sanitize_text_field($_POST['recieved_by']);
            $status = 'Pending'; // Default status
            $created_by = get_current_user_id();
            $updated_by = get_current_user_id();
            $current_date = current_time('mysql');

            // Insert data into the Payments table
            $wpdb->insert(
                $wpdb->prefix . 'payments',
                [
                    'recieved_date' => $recieved_date,
                    'recieved_amount' => $recieved_amount,
                    'recieved_by' => $recieved_by,
                    'amount_type' => $amount_type,
                    'status' => $status,
                    'created_by' => $created_by,
                    'updated_by' => $updated_by,
                    'created_date' => $current_date,
                    'updated_date' => $current_date
                ]
            );

            // Redirect after submission
            wp_redirect(admin_url('admin.php?page=received-amount'));
            exit;
        }
    }

    // Method to create the database table
    public function create_payments_table() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'payments';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            recieved_date date NOT NULL,
            recieved_amount decimal(10,1) NOT NULL,
            recieved_by varchar(100) NOT NULL,
            amount_type varchar(50) NOT NULL,
            status varchar(20) NOT NULL DEFAULT 'Pending',
            created_by bigint(20) NOT NULL,
            updated_by bigint(20) NOT NULL,
            created_date datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            updated_date datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
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