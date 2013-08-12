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
		$jsArray = array(array('jquery-1.10.2.min.js'), array('highcharts/highcharts.js'), array('highcharts/modules/exporting.js')
		,array('jquery.form.js'),array('bootstrap/bootstrap.min.js')
		,array('datatable/jquery.dataTables.js'),array('jquery-migrate-1.2.1.js')
		, array('jquery.multiselect.js'),array('Merged_JS.js'));
		$cssArray = array( array('style.css'), array('bootstrap.css'),array('jquery-ui.css'),array('style_report.css'),array('jquery.dataTables_themeroller.css')
		,array('demo_table.css'));

		

		// add a css file
		$this -> carabiner -> css($cssArray);
		
		$this -> carabiner -> js($jsArray);
		$assets = $this -> carabiner -> display_string();
		$this -> session -> set_userdata('assets', $assets);
		
		redirect('User_Management/login');
	}
}