<?php
if(!class_exists('sa_daily_kharch_init')){
	
	class sa_daily_kharch_init{
		
		var $constants = array();
		
		function __construct($constants = array()){
			$this->constants = $constants;
			$ajax_action = isset($this->constants['ajax_action']) ? $this->constants['ajax_action'] : '';
			
			add_action('admin_menu', 								array($this, 'admin_menu'));
			add_filter('admin_enqueue_scripts', 					array($this, 'admin_enqueue_scripts'),11);
			add_action('wp_ajax_'.$ajax_action,						array($this, 'ajax_action'),101,2);	
			add_filter('admin_init', 								array($this, 'admin_init'),11);
		}
		
		function admin_menu()
		{
			$callback  = array($this,'add_menu_page');
			$capability = isset($this->constants['menu_capability']) ? $this->constants['menu_capability'] : 'manage_options';
			$menu_slug = isset($this->constants['menu_slug']) ? $this->constants['menu_slug'] : '';
			$menu_icon_url = isset($this->constants['menu_icon_url']) ? $this->constants['menu_icon_url'] : 'dashicons-media-document';
			$menu_position = isset($this->constants['menu_position']) ? $this->constants['menu_position'] : 50;
			$plugin_key = isset($this->constants['plugin_key']) ? $this->constants['plugin_key'] : $menu_slug;
			
			add_menu_page(esc_html__('Daily Kharch','niwcca'),esc_html__('Daily Kharch','niwcca'),$capability,$menu_slug,$callback,$menu_icon_url,$menu_position);
			add_submenu_page($menu_slug,esc_html__('Dashboard','niwcca'),esc_html__('Dashboard','niwcca'),$capability,$menu_slug,$callback);
			add_submenu_page($menu_slug,esc_html__('Report','niwcca'),esc_html__('Report','niwcca'),$capability,$plugin_key.'-report',$callback);
			add_submenu_page($menu_slug,esc_html__('Montly Report','niwcca'),esc_html__('Montly Report','niwcca'),$capability,$plugin_key.'-montly-report',$callback);
			add_submenu_page($menu_slug,esc_html__('Manage kharch','niwcca'),esc_html__('Manage kharch','niwcca'),$capability,$plugin_key.'-manage-kharch',$callback);
			add_submenu_page($menu_slug,esc_html__('Import','niwcca'),esc_html__('Import','niwcca'),$capability,$plugin_key.'-import-csv',$callback);
			
			/*Dashbaord menu removed*/
			remove_submenu_page($menu_slug,$menu_slug);
		}
		
		function add_menu_page()
		{
			$plugin_key = isset($this->constants['plugin_key']) ? $this->constants['plugin_key'] : '';
			$admin_page = isset($_GET['page']) ? $_GET['page'] : '';
			$obj = $this->get_intensity($admin_page);
			echo "<div class=\"wrap wrap_{$plugin_key}\">";
				if($obj!= NULL){
					$obj->admin_menu_page();
				}
			echo "</div>";
		}
		
		function get_intensity($admin_page = '')
		{
			$obj = NULL;
			$plugin_key = isset($this->constants['plugin_key']) ? $this->constants['plugin_key'] : '';
			switch($admin_page){
				case $plugin_key."-import-csv":
					require_once('sa_daily_kharch_import_csv.php');
					$obj = new sa_daily_kharch_import_csv($this->constants);
					break;
					
				case $plugin_key."-report":
				case $plugin_key."-montly-report":
					require_once('sa_daily_kharch_raports.php');
					$obj = new sa_daily_kharch_reports($this->constants);
					break;
			}			
			return $obj;
		}
		
		function admin_enqueue_scripts()
		{
			$page = isset($_GET['page']) ? $_GET['page'] : '';
			$plugin_key = isset($this->constants['ajax_action']) ? $this->constants['ajax_action'] : '';
			if(
				  $page == $plugin_key."-transactions"
				  || $page == $plugin_key."-manage-kharch"
				  || $page == $plugin_key."-import-csv"
				  || $page == $plugin_key."-report"
				  || $page == $plugin_key."-montly-report"
			){
				$ver = isset($this->constants['ver']) ? $this->constants['ver'] : date("YmdHis");
				$plugin_key  = isset($this->constants['ajax_action']) ? $this->constants['ajax_action'] : '';
				$plugin_path = isset($this->constants['plugin_path']) ? $this->constants['plugin_path'] : '';
				$default_tab = isset($this->constants['default_tab']) ? $this->constants['default_tab'] : '';
				$ajax_action = isset($this->constants['ajax_action']) ? $this->constants['ajax_action'] : '';				
				$tab 		 = isset($_GET['tab']) ? $_GET['tab'] : $default_tab;
				
				wp_enqueue_script('jquery');
				wp_enqueue_script('jquery-ui-autocomplete' );
				wp_enqueue_script('jquery-ui-datepicker');
				
				$plugin_url = plugins_url("",$plugin_path);
				$this->constants['plugin_url'] = $plugin_url;
				
				global $wp_scripts;				
				$jquery_version = isset($wp_scripts->registered['jquery-ui-core']->ver) ? $wp_scripts->registered['jquery-ui-core']->ver : '1.9.2';
				wp_enqueue_style( 'jquery-ui-style', '//ajax.googleapis.com/ajax/libs/jqueryui/'.$jquery_version.'/themes/smoothness/jquery-ui.css' );
				
				$localize_script = array();
				$localize_script['ajaxurl']      = admin_url('admin-ajax.php');
				$localize_script['ajax_action']  = $ajax_action;
				$localize_script['admin_page']   = $page;
				$localize_script['current_time'] = date_i18n("Y-m-d H:i");
				
				$localize_script['please_wait'] = esc_html__('Please Wait!','niwcca');
				
				if($page == $plugin_key."-report" || $page == $plugin_key."-montly-report"){
					//wp_enqueue_script($plugin_key.'_dataTables','//cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js',array());
					//wp_enqueue_style($plugin_key.'_dataTables','//cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css',array());
					
					wp_enqueue_script($plugin_key.'_bootstrap',$plugin_url.'/assets/js/bootstrap.min.js',array());
					wp_enqueue_style($plugin_key.'_bootstrap',$plugin_url.'/assets/css/bootstrap.min.css',array());
					
					wp_enqueue_script($plugin_key.'_dataTables',$plugin_url.'/assets/js/jquery.dataTables.min.js',array());
					wp_enqueue_style($plugin_key.'_dataTables',$plugin_url.'/assets/css/jquery.dataTables.min.css',array());
					
					wp_enqueue_script($plugin_key.'_report_scripts',$plugin_url.'/assets/js/report_scripts.js',array(),$ver);
				}
				
				wp_enqueue_script($plugin_key.'_scripts',$plugin_url.'/assets/js/scripts.js',array(),$ver);
				wp_enqueue_style($plugin_key.'_style',$plugin_url.'/assets/css/style.css',array(),$ver);
				wp_localize_script($plugin_key.'_scripts', 'kharch_object', $localize_script);
				
			}
		}
		
		function admin_init()
		{
			$admin_page = isset($_POST['admin_page']) ? $_POST['admin_page'] : '';
			if($admin_page == 'dkh-import-csv'){
				$obj = $this->get_intensity($admin_page);
				$obj->admin_init();
			}
		}
		
		function ajax_action()
		{
			$sub_action = isset($_POST['sub_action']) ? $_POST['sub_action'] : '';
			$admin_page = isset($_POST['admin_page']) ? $_POST['admin_page'] : '';
			$obj = $this->get_intensity($admin_page);
			if($obj){
				$obj->ajax();
			}			
			die;
		}
	}
}