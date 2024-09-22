<?php
/*
Plugin Name: Daily Kharch
Author: Salim Shaikh
Version: 1.0.0
*/

if(!class_exists('sa_daily_kharch')){
	
	class sa_daily_kharch{
		
		var $constants = array();
		
		function __construct(){
			$constants = array();
			$this->constants = $constants;
			
			$this->constants = $constants;
				
			$this->constants['plugin_path']  = __FILE__;
		
			$this->constants['plugin_key']   = 'dkh';
			
			$this->constants['ver']           = "1.0.0";
			
			$this->constants['ver']           = date("YmdHis");
			
			$this->constants['ajax_action']   = $this->constants['plugin_key'];
			
			$this->constants['menu_capability'] = 'manage_options';
			
			$this->constants['menu_slug']     = $this->constants['plugin_key'];
			
			$this->constants['menu_icon_url'] = 'dashicons-media-document';
			
			$this->constants['menu_position'] = 50;
			
			if(!defined('sa_daily_kharch_path')){
				define('sa_daily_kharch_path',__DIR__."".DIRECTORY_SEPARATOR);
			}
			
			add_action('plugins_loaded', array($this, 'plugins_loaded'));
			
			add_action('init', array($this, 'load_plugin_textdomain'));
			
			add_filter('plugin_action_links', array( $this, 'plugin_action_links' ), 10, 2);
		}
		
		function plugins_loaded()
		{
			if(!class_exists('sa_daily_kharch_init')){
				require_once('includes/sa_daily_kharch_init.php');
				$obj = new sa_daily_kharch_init($this->constants);
			}			
		}
		
		function load_plugin_textdomain()
		{
			$plugin_key = $this->constants['plugin_key'];
			load_plugin_textdomain($plugin_key,false,dirname(plugin_basename(__FILE__)).'/languages'); 
		}
		
		function plugin_action_links($actions = array(), $plugin_file = ''){
		 	static $plugin;

			if(!isset($plugin)){
				$plugin = plugin_basename(__FILE__);
			}
				
			if($plugin == $plugin_file) {
				$plugin_key = $this->constants['plugin_key'];
				$admin_url = admin_url("admin.php?page={$plugin_key}-dashboard&tab=today_kharch");
				$actions[] = '<a href="'.$admin_url.'" target="_blank">'.esc_html__('Dashbaord','niwcca').'</a>';
				
				$admin_url = admin_url("admin.php?page={$plugin_key}-manage-kharch");
				$actions[] = '<a href="'.$admin_url.'" target="_blank">'.esc_html__('Manage Kharch','niwcca').'</a>';
			}
				
			return $actions;
		}
		
		static function activation()
		{
			$obj = new sa_daily_kharch();
			require_once('includes/sa_daily_kharch_init.php');
			$obj = new sa_daily_kharch_init($this->constants);
			
			
			$plugin_key = $obj->constants['plugin_key'];
			$option_name = $plugin_key."_settings";
			
			$settings = get_option($option_name,array());
			$settings = is_array($settings) ? $settings : array();
			
			$receipt_company_name = $obj->constants['receipt_company_name'];
			$receipt_company_address = $obj->constants['receipt_company_address'];
			
			$settings['prefix_payment_number']     = isset($settings['prefix_payment_number']) 		? $settings['prefix_payment_number'] 	 : 'PA';
			
			update_option($option_name,$settings);
		}
		
		static function deactivation(){}
	}
	
	$obj = new sa_daily_kharch();
}