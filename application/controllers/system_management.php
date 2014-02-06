<?php

error_reporting(0);
class System_management extends MY_Controller {
	function __construct() {
		parent::__construct();
		$this -> load -> library('PHPExcel');
		$this -> load -> helper('url');

	}

	public function index() {
		$this -> load -> library('carabiner');
		$this -> carabiner -> empty_cache();
		$jsArray = array( array('jquery-1.10.2.js'), array('jquery-1.7.2.min.js'), array('jquery-migrate-1.2.1.js'), array('jquery.form.js'), array('jquery.gritter.js'), array('jquery-ui.js'), array('sorttable.js'), array('datatable/jquery.dataTables.min.js'), array('datatable/FixedColumns.js'), array('bootstrap/bootstrap.min.js'), array('bootstrap/paging.js'), array('Merged_JS.js'), array('jquery.multiselect.js'), array('jquery.multiselect.filter.js'), array('validator.js'), array('validationEngine-en.js'), array('menus.js'), array('jquery.blockUI.js'), array('amcharts/amcharts.js'), array('highcharts/highcharts.js'), array('highcharts/highcharts-more.js'), array('highcharts/modules/exporting.js'));
		$cssArray = array( array('amcharts/style.css'), array('bootstrap.css'), array('bootstrap.min.css'), array('bootstrap-responsive.min.css'), array('datatable/jquery.dataTables.css'), array('datatable/jquery.dataTables_themeroller.css'), array('datatable/demo_table.css'), array('assets/jquery.multiselect.css'), array('assets/jquery.multiselect.filter.css'), array('assets/prettify.css'), array('style_report.css'), array('validator.css'), array('jquery.gritter.css'), array('jquery-ui.css'), array('style.css'),);
		$this -> carabiner -> css($cssArray);
		$this -> carabiner -> js($jsArray);

		$assets = $this -> carabiner -> display_string();
		$this -> load -> helper('file');
		if (!write_file('application/views/sections/link.php', $assets)) {
			echo 'Unable to write the file';
		} else {
			echo 'File written!';
		}
		redirect('dashboard_management');
	}

	public function updateFacility(){
		
		$sql ='truncate escm_facility1';
		//$sql ="INSERT INTO escm_facility1 SELECT * FROM escm_facility WHERE name LIKE '%St Monica%'";
		$query = $this -> db -> query($sql);
		$sql = "SELECT DISTINCT(f.code) FROM fac f
		INNER JOIN sync_facility ef ON ef.code=f.code
		WHERE ef.code!=''";
		$query = $this -> db -> query($sql);
		$result=$query->result_array();
		foreach ($result as $value) {
			$name = $value['code'];
			//$name =str_replace("'","\'", $name);
			$name =str_replace('"','\'', $name);
			
			//$name =str_replace("'","\'", $name);
			$name =addslashes($name);
			$name_c=str_replace('Central Site',' ', $name);
			echo $name_c."<br>";
			$sql ="INSERT INTO escm_facility1
					SELECT * FROM sync_facility WHERE code='".$name."'";
					
			$query = $this -> db -> query($sql);
					
		}
	}

}
