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
		/*
<<<<<<< HEAD
		$jsArray = array( array('jquery-1.10.2.js'), array('jquery-migrate-1.2.1.js'), array('jquery.form.js'), array('jquery-ui.js'), array('sorttable.js'), array('datatable/jquery.dataTables.min.js'), array('bootstrap/bootstrap.min.js'), array('bootstrap/paging.js'), array('Merged_JS.js'), array('highcharts/highcharts.js'), array('highcharts/modules/exporting.js'));

		
=======
		 $jsArray = array( array('jquery-1.10.2.js'), array('jquery-migrate-1.2.1.js'), array('jquery.form.js'), array('jquery-ui.js'), array('sorttable.js'), array('datatable/jquery.dataTables.min.js'), array('bootstrap/bootstrap.min.js'), array('bootstrap/paging.js'), array('Merged_JS.js'), array('highcharts/highcharts.js'), array('highcharts/modules/exporting.js'));

>>>>>>> b7f10e3b71f611031750a3ea7c28aa07cddcb2d2
		 $cssArray = array(array('bootstrap.css'),
		 array('bootstrap-responsive.min.css'),array('datatable/jquery.dataTables.css'),
		 array('datatable/jquery.dataTables_themeroller.css'),
		 array('datatable/demo_table.css'),array('style.css')
		 ,array('jquery-ui.css'),array('style_report.css'),array('jquery.dataTables_themeroller.css')
		 ,array('demo_table.css'),array('jquery.multiselect.css'),array('jquery.multiselect.filter.css'),
		 array('prettify.css'),array('style_report.css'),array('validator.css')
		 );
		 *
		 */
<<<<<<< HEAD
		$jsArray = array( array('jquery-1.10.2.js'), array('jquery-1.7.2.min.js'), array('jquery-migrate-1.2.1.js'), array('jquery.form.js'), array('jquery.gritter.js'), array('jquery-ui.js'), array('sorttable.js'), array('datatable/jquery.dataTables.min.js'),array('bootstrap/bootstrap.min.js'), array('bootstrap/paging.js'), array('Merged_JS.js'), array('jquery.multiselect.js'), array('jquery.multiselect.filter.js'), array('validator.js'), array('validationEngine-en.js'), array('menus.js'), array('jquery.blockUI.js'), array('amcharts/amcharts.js'), array('highcharts/highcharts.js'), array('highcharts/highcharts-more.js'), array('highcharts/modules/exporting.js'));
=======
		$jsArray = array( array('jquery-1.10.2.js'), array('jquery-1.7.2.min.js'), array('jquery-migrate-1.2.1.js'), array('jquery.form.js'), array('jquery.gritter.js'), array('jquery-ui.js'), array('sorttable.js'), array('datatable/jquery.dataTables.min.js'),array('datatable/FixedColumns.js'), array('bootstrap/bootstrap.min.js'), array('bootstrap/paging.js'), array('Merged_JS.js'), array('jquery.multiselect.js'), array('jquery.multiselect.filter.js'), array('validator.js'), array('validationEngine-en.js'), array('menus.js'), array('jquery.blockUI.js'), array('amcharts/amcharts.js'), array('highcharts/highcharts.js'), array('highcharts/highcharts-more.js'), array('highcharts/modules/exporting.js'));
>>>>>>> b7f10e3b71f611031750a3ea7c28aa07cddcb2d2

		$cssArray = array( array('amcharts/style.css'), array('bootstrap.css'), array('bootstrap.min.css'), array('bootstrap-responsive.min.css'), array('datatable/jquery.dataTables.css'), array('datatable/jquery.dataTables_themeroller.css'), array('datatable/demo_table.css'), array('jquery-ui.css'), array('style.css'), array('assets/jquery.multiselect.css'), array('assets/jquery.multiselect.filter.css'), array('assets/prettify.css'), array('style_report.css'), array('validator.css'), array('jquery.gritter.css'));

		// add a css file
		$this -> carabiner -> css($cssArray);

		$this -> carabiner -> js($jsArray);
		$assets = $this -> carabiner -> display_string();
		$this -> session -> set_userdata('assets', $assets);

		redirect('dashboard_management');
	}

}
