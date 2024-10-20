<?php
/*
Plugin Name: Daily Kharch
Author: Salim Shaikh
Version: 1.0.0
*/

// Prevent direct access to the file
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Check if the class doesn't exist to avoid redeclaration issues
if(!class_exists('sa_daily_kharch')){
	
	class sa_daily_kharch{
		
		// Class variable to hold plugin constants
		var $constants = array();
		
		// Constructor function - initializes the plugin
		function __construct(){
			
			// Define the plugin directory path constant if it's not already defined
			if(!defined('sa_daily_kharch_path')){
				define('sa_daily_kharch_path',__DIR__."".DIRECTORY_SEPARATOR);
			}
			
			// Hook to execute the `plugins_loaded` function after all plugins are loaded
			add_action('plugins_loaded', array($this, 'plugins_loaded'));
			
			// Hook to load the plugin text domain (for translations)
			add_action('init', array($this, 'load_plugin_textdomain'));
			
			// Filter to add custom plugin action links in the plugin list page
			add_filter('plugin_action_links', array( $this, 'plugin_action_links' ), 10, 2);
		}

		// get the constants array with settings
		function get_constants(){
			// Initialize the constants array
			$constants = array();
						
			// Define the plugin path constant (main plugin file path)
			$constants['plugin_path']  = __FILE__;
		
			// Define a unique plugin key (used for various settings)
			$constants['plugin_key']   = 'dkh';
			
			// Plugin version
			$constants['ver']           = "1.0.0";
			
			// Overwriting the previous version with the current timestamp (useful for cache busting)
			// $constants['ver']           = date("YmdHis");

			// Generate a unique version string based on the current date and time            
			if(defined('WP_ENVIRONMENT_TYPE')){
				if(WP_ENVIRONMENT_TYPE == 'development' || WP_ENVIRONMENT_TYPE == 'staging'){
					$constants['version'] = date("YmdHis");
				}				
			}
			
			// Define the action name for AJAX requests
			$constants['ajax_action']   = $this->constants['plugin_key'];
			
			// Capability required to access the plugin's admin menu (manage_options)
			$constants['menu_capability'] = 'manage_options';
			
			// Define the menu slug for the admin menu
			$constants['menu_slug']     = $this->constants['plugin_key']."-kharch";
			
			// Define the icon URL for the admin menu
			$constants['menu_icon_url'] = 'dashicons-media-document';
			
			// Define the menu position in the admin dashboard (50)
			$constants['menu_position'] = 50;

			return $constants;
		}
		
		// Function executed after plugins are loaded
		function plugins_loaded()
		{
			// Store the initialized array in the class variable for later use
            $this->constants = $this->get_constants();

			// Check if the `sa_daily_kharch_init` class exists
			if(!class_exists('sa_daily_kharch_init')){
				// If not, require the file that contains this class
				require_once('includes/sa_daily_kharch_init.php');
				
				// Create a new instance of `sa_daily_kharch_init` class with constants
				$obj = new sa_daily_kharch_init($this->constants);
			}			
		}
		
		// Load plugin text domain for translation (localization)
		function load_plugin_textdomain()
		{
			$plugin_key = $this->constants['plugin_key'];

			// Load the language files for translation from the `/languages` directory
			load_plugin_textdomain($plugin_key, false, dirname(plugin_basename(__FILE__)).'/languages'); 
		}
		
		// Add custom action links to the plugin page (e.g., Dashboard, Manage Kharch)
		function plugin_action_links($actions = array(), $plugin_file = ''){
		 	static $plugin;

			// Get the plugin basename if not already set
			if(!isset($plugin)){
				$plugin = plugin_basename(__FILE__);
			}
				
			// Check if the current plugin matches the plugin file
			if($plugin == $plugin_file) {
				
				$plugin_key = $this->constants['plugin_key'];
				
				// Add a Dashboard link in the plugin action list
				$admin_url = admin_url("admin.php?page={$plugin_key}-dashboard&tab=today_kharch");
				$actions[] = '<a href="'.$admin_url.'" target="_blank">'.esc_html__('Dashboard', 'niwcca').'</a>';
				
				// Add a Manage Kharch link in the plugin action list
				$admin_url = admin_url("admin.php?page={$plugin_key}-manage-kharch");
				$actions[] = '<a href="'.$admin_url.'" target="_blank">'.esc_html__('Manage Kharch', 'niwcca').'</a>';
			}
				
			// Return the modified actions array
			return $actions;
		}
		
		// Plugin activation hook (executed when the plugin is activated)
		static function activation()
		{
			// Create a new instance of the plugin class
			$obj = new sa_daily_kharch();
			
			// Include the initialization file
			require_once('includes/sa_daily_kharch_init.php');
			
			// Instantiate the `sa_daily_kharch_init` class with constants
			$obj = new sa_daily_kharch_init($this->constants);
			
			// Get the plugin key and use it to create an option name
			$plugin_key = $obj->constants['plugin_key'];
			$option_name = $plugin_key."_settings";
			
			// Retrieve the existing settings or set as an empty array if not found
			$settings = get_option($option_name, array());

			// is_array() check the return value is array if not return array
			$settings = is_array($settings) ? $settings : array();
			
			// Set default values for settings (e.g., prefix for payment numbers)
			$settings['prefix_payment_number'] = isset($settings['prefix_payment_number']) ? $settings['prefix_payment_number'] : 'PA';
			
			// Save the updated settings in the database
			update_option($option_name, $settings);
		}
		
		// Plugin deactivation hook (executed when the plugin is deactivated)
		static function deactivation(){}
	}
	
	// Create a new instance of the plugin class
	$obj = new sa_daily_kharch();	
}

// Hook for plugin activation
register_activation_hook(__FILE__, array('sa_daily_kharch', 'activation'));

// Hook for plugin deactivation
register_deactivation_hook(__FILE__, array('sa_daily_kharch', 'deactivation'));