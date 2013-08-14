<?php

error_reporting(0);
class System_management extends MY_Controller {
	function __construct() {
		parent::__construct();
		$this -> load -> library('PHPExcel');
		$this -> load -> helper('url');
		
	}

	public function index() {
		/*$gitUpdate=$this->github_updater->has_update();
		if($gitUpdate==TRUE){
			$this->github_updater->update();
		}
		*/
		$this -> load -> library('carabiner');
		$this -> carabiner -> empty_cache();
		// add a js file
		$jsArray = array(array('jquery-1.10.2.js'),array('jquery-migrate-1.2.1.js'),array('jquery.form.js'),
		 array('jquery-ui.js'),array('sorttable.js'),array('datatable/jquery.dataTables.min.js'),
		 array('bootstrap/bootstrap.min.js'),array('bootstrap/paging.js'),array('Merged_JS.js'),
		 array('highcharts/highcharts.js'),array('highcharts/modules/exporting.js'));

		
		$cssArray = array(array('bootstrap.css'),
		array('bootstrap-responsive.min.css'),array('datatable/jquery.dataTables.css'),
		array('datatable/jquery.dataTables_themeroller.css'),
		array('datatable/demo_table.css'),array('style.css')
		,array('jquery-ui.css'),array('style_report.css'),array('jquery.dataTables_themeroller.css')
		,array('demo_table.css'),array('jquery.multiselect.css'),array('jquery.multiselect.filter.css'),
		array('prettify.css'),array('style_report.css'),array('validator.css')	
		);

		

		// add a css file
		$this -> carabiner -> css($cssArray);
		
		$this -> carabiner -> js($jsArray);
		$assets = $this -> carabiner -> display_string();
		$this -> session -> set_userdata('assets', $assets);
		
		redirect('User_Management/login');
	}
}