<?php
if (!defined('BASEPATH'))
	exit('No direct script access allowed');
class Pipelineconsumption_Management extends MY_Controller {
	function __construct() {
		parent::__construct();
		$data = array();
		ini_set("max_execution_time", "10000");
	}

	public function index() {
		$this -> load -> view("pipeline_nos_v");
	}

	public function plotgraph($year, $month, $pipeline) {
		$display_month = date('F', strtotime($date));
		if ($pipeline == '1') {
			$pipeline_display = "Kemsa";
		}
		if ($pipeline == '2') {
			$pipeline_display = "Kenya Pharma";
		}
		$strXML = "<chart useroundedges='1' caption='Pipeline Consumption Summary for $display_month-$year(" . $pipeline_display . ")'   showBorder='0' formatNumberScale='0' showValues='1' showPercentageInLabel='1'  showPercentageValues='1' >";
		$this -> load -> database();
		$query = $this -> db -> query("SELECT UPPER(drugname) as drugname,consumption FROM `pipeline_consumption` where year='$year' and month='$month' and pipeline='$pipeline' order by drugname asc");
		$results = $query -> result_array();

		if ($results) {
			foreach ($results as $result) {
				if($result['consumption']<0){
					$result['consumption']=0;
				}
				$strXML .= "<set label='" . $result['drugname'] . "' value='" . $result['consumption'] . "' />";
			}
			header('Content-type: text/xml');
			echo $strXML .= "</chart>";
		}
	}

}
?>