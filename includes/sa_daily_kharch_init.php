<?php  
// Prevent direct access to the file
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Check if the class 'sa_daily_kharch_init' already exists to prevent redeclaration  
if(!class_exists('sa_daily_kharch_init')){  
	  
	// Main class that handles the Daily Kharch plugin initialization
	class sa_daily_kharch_init{  
		  
		// Array to hold plugin constants
		var $constants = array();  
		  
		// Constructor that accepts an optional array of constants
		function __construct($constants = array()){  
			$this->constants = $constants; // Store the constants
			
			// Get the AJAX action name from the constants if set
			$ajax_action = isset($this->constants['ajax_action']) ? $this->constants['ajax_action'] : '';  
			  
			// Hook to add admin menu
			add_action('admin_menu', array($this, 'admin_menu'));  
			// Hook to enqueue admin scripts and styles
			add_filter('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'), 11);  
			// Hook to handle the AJAX action with dynamic action name
			add_action('wp_ajax_'.$ajax_action, array($this, 'ajax_action'), 101, 2);	  
			// Hook to initialize admin settings
			add_filter('admin_init', array($this, 'admin_init'), 11);  
		}  
		  
		// Function to add menu items in the WordPress admin menu
		function admin_menu()  
		{  
			// Callback for the menu pages
			$callback  = array($this, 'add_menu_page');  
			
			// Get capability, slug, icon, and position from constants or set default values
			$capability = isset($this->constants['menu_capability']) ? $this->constants['menu_capability'] : 'manage_options';  
			$menu_slug = isset($this->constants['menu_slug']) ? $this->constants['menu_slug'] : '';  
			$menu_icon_url = isset($this->constants['menu_icon_url']) ? $this->constants['menu_icon_url'] : 'dashicons-media-document';  
			$menu_position = isset($this->constants['menu_position']) ? $this->constants['menu_position'] : 50;  
			$plugin_key = isset($this->constants['plugin_key']) ? $this->constants['plugin_key'] : $menu_slug;  
			  
			// Add the main menu page for Daily Kharch plugin
			add_menu_page(esc_html__('Daily Kharch', 'niwcca'), esc_html__('Daily Kharch', 'niwcca'), $capability, $menu_slug, $callback, $menu_icon_url, $menu_position);  
			
			// Add submenu pages under the main menu
			add_submenu_page($menu_slug, esc_html__('Dashboard', 'niwcca'), esc_html__('Dashboard', 'niwcca'), $capability, $menu_slug, $callback);  
			add_submenu_page($menu_slug, esc_html__('Report', 'niwcca'), esc_html__('Report', 'niwcca'), $capability, $plugin_key . '-report', $callback);  
			add_submenu_page($menu_slug, esc_html__('Monthly Report', 'niwcca'), esc_html__('Monthly Report', 'niwcca'), $capability, $plugin_key . '-montly-report', $callback);  
			add_submenu_page($menu_slug, esc_html__('Summary Report', 'niwcca'), esc_html__('Summary Report', 'niwcca'), $capability, $plugin_key . '-summary-report', $callback);  
			add_submenu_page($menu_slug, esc_html__('Received Amount', 'niwcca'), esc_html__('Received Amount', 'niwcca'), $capability, $plugin_key . '-received-amount', $callback);  
			add_submenu_page($menu_slug, esc_html__('Manage Kharch', 'niwcca'), esc_html__('Manage Kharch', 'niwcca'), $capability, $plugin_key . '-manage-kharch', $callback);  
			add_submenu_page($menu_slug, esc_html__('Import', 'niwcca'), esc_html__('Import', 'niwcca'), $capability, $plugin_key . '-import-csv', $callback);  
			
			// Remove the duplicate "Dashboard" menu item
			remove_submenu_page($menu_slug, $menu_slug);  
		}

		// Function to get the list of admin pages created by the plugin
		function get_admin_pages() {
			global $submenu;
			$menu_slug = $this->constants['menu_slug'];

			$admin_page = array();
			// If the submenu exists for the plugin, retrieve the slug of each submenu page
			if (isset($submenu[$menu_slug])) {
				foreach ($submenu[$menu_slug] as $key => $m) {
					$admin_page[] = $m[2];
				}
			}

			return $admin_page;
		}

		// Function that outputs the content of the selected admin page
		function add_menu_page() 
		{
			$plugin_key = isset($this->constants['plugin_key']) ? $this->constants['plugin_key'] : '';
			$admin_page = isset($_GET['page']) ? $_GET['page'] : '';
			
			// Get the appropriate object based on the current admin page
			$obj = $this->get_intensity($admin_page);
			
			// Display the plugin's admin content
			echo "<div class=\"wrap wrap_{$plugin_key}\">";
				if($obj != NULL){
					$obj->admin_menu_page(); // Call the respective object's method to display the page content
				}
			echo "</div>";
		}
		
		// Function to get the object related to the current admin page based on the admin_page slug
		function get_intensity($admin_page = '')
		{
			$obj = NULL;
			$plugin_key = isset($this->constants['plugin_key']) ? $this->constants['plugin_key'] : '';
			
			// Load and instantiate the relevant class based on the admin page being accessed
			switch($admin_page){
				case $plugin_key."-import-csv":
					require_once('sa_daily_kharch_import_csv.php');
					$obj = new sa_daily_kharch_import_csv($this->constants);
					break;
				case $plugin_key."-received-amount":
					require_once('sa_daily_kharch_received_amount.php');
					$obj = new sa_daily_kharch_received_amount($this->constants);
					break;					
				case $plugin_key."-report":
				case $plugin_key."-montly-report":
					require_once('sa_daily_kharch_raports.php');
					$obj = new sa_daily_kharch_reports($this->constants);
					break;
				case $plugin_key."-summary-report":
					require_once('sa_daily_kharch_summary_raports.php');
					$obj = new sa_daily_kharch_summary_reports($this->constants);
					break;
			}			
			
			// Return the object to be used for displaying the page content
			return $obj;
		}

		// Function to enqueue necessary admin scripts and styles
		function admin_enqueue_scripts()
		{
			$page = isset($_GET['page']) ? $_GET['page'] : '';
            
			// Get the list of admin pages for which scripts should be loaded
			$admin_pages = $this->get_admin_pages();

			// Enqueue scripts and styles only on the plugin's admin pages
			if (in_array($page, $admin_pages)) {
				
				// Get plugin version and paths for script and style enqueueing
				$ver = isset($this->constants['ver']) ? $this->constants['ver'] : date("YmdHis");				
				$plugin_path = isset($this->constants['plugin_path']) ? $this->constants['plugin_path'] : '';
				$default_tab = isset($this->constants['default_tab']) ? $this->constants['default_tab'] : '';
				$ajax_action = isset($this->constants['ajax_action']) ? $this->constants['ajax_action'] : '';				
				$tab 		 = isset($_GET['tab']) ? $_GET['tab'] : $default_tab;
				
				// Enqueue jQuery and necessary UI libraries
				wp_enqueue_script('jquery');
				wp_enqueue_script('jquery-ui-autocomplete' );
				wp_enqueue_script('jquery-ui-datepicker');
				
				// Get the plugin URL for loading scripts and styles
				$plugin_url = plugins_url("",$plugin_path);
				$this->constants['plugin_url'] = $plugin_url;
				
				// Load jQuery UI styles
				global $wp_scripts;				
				$jquery_version = isset($wp_scripts->registered['jquery-ui-core']->ver) ? $wp_scripts->registered['jquery-ui-core']->ver : '1.9.2';
				wp_enqueue_style( 'jquery-ui-style', '//ajax.googleapis.com/ajax/libs/jqueryui/'.$jquery_version.'/themes/smoothness/jquery-ui.css' );
				
				// Prepare localized script data for the plugin's JavaScript
				$localize_script = array();
				$localize_script['ajaxurl']      = admin_url('admin-ajax.php');
				$localize_script['ajax_action']  = $ajax_action;
				$localize_script['admin_page']   = $page;
				$localize_script['current_time'] = date_i18n("Y-m-d H:i");
				$localize_script['please_wait']  = esc_html__('Please Wait!','niwcca');
				
				// Enqueue DataTables and Bootstrap scripts/styles for specific admin pages
				if($page == $plugin_key."-report" || $page == $plugin_key."-montly-report" || $page == $plugin_key."-summary-report"
				|| $page == $plugin_key."-received-amount"){
					
					wp_enqueue_script($plugin_key.'_bootstrap',$plugin_url.'/assets/js/bootstrap.min.js',array());
					wp_enqueue_style($plugin_key.'_bootstrap',$plugin_url.'/assets/css/bootstrap.min.css',array());
					
					wp_enqueue_script($plugin_key.'_dataTables',$plugin_url.'/assets/js/jquery.dataTables.min.js',array());
					wp_enqueue_style($plugin_key.'_dataTables',$plugin_url.'/assets/css/jquery.dataTables.min.css',array());
					
					wp_enqueue_script($plugin_key.'_report_scripts',$plugin_url.'/assets/js/report_scripts.js',array(),$ver);
				}
				
				// Enqueue the main scripts and styles for the plugin
				wp_enqueue_script($plugin_key.'_scripts',$plugin_url.'/assets/js/scripts.js',array(),$ver);
				wp_enqueue_style($plugin_key.'_style',$plugin_url.'/assets/css/style.css',array(),$ver);
				
				// Localize the main script with necessary data
				wp_localize_script($plugin_key.'_scripts', 'kharch_object', $localize_script);
				
			}
		}

		// Function to handle admin initialization (e.g., importing CSVs)
		function admin_init()
		{
			$admin_page = isset($_POST['admin_page']) ? $_POST['admin_page'] : '';
			if($admin_page == 'dkh-import-csv'){
				$obj = $this->get_intensity($admin_page);
				$obj->admin_init();
			}
		}

		// Function to handle the AJAX request
		function ajax_action()
		{
			// Check the AJAX nonce for security
			// check_ajax_referer('daily_kharch_nonce', 'security');

			// Ensure the user has the required capability to perform the action
			if (!current_user_can('edit_posts')) {
				wp_die('Unauthorized user');
			}
			
			// Determine which admin page the AJAX request is for
			$admin_page = isset($_POST['admin_page']) ? $_POST['admin_page'] : '';
			$obj = $this->get_intensity($admin_page);
			
			// If the object exists, execute its AJAX handler
			if($obj){
				$obj->ajax();
			}			
			die;
		}
	}
}
?>
