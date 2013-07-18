<?php
class Patientbyregimen_Management extends MY_Controller {
	function __construct() {
		parent::__construct();
		$this -> load -> helper('fusioncharts');
	}

	public function index() {
		//$this->drawgraph();
		$this -> load -> view("patient_regnos_v");
	}

	public function plotgraph($year, $month, $pipeline) {
		$date = '2013-' . $month . '-02';
		$display_month = date('F', strtotime($date));
		if ($pipeline == '1') {
			$pipeline_display = "Kemsa";
		}
		if ($pipeline == '2') {
			$pipeline_display = "Kenya Pharma";
		}
		$strXML = "<chart caption='Patient By regimen Summary for $display_month-$year(" . $pipeline_display . ")'  pieSliceDepth='30' showBorder='0' formatNumberScale='0' showValues='1' showPercentageInLabel='1'  showPercentageValues='1' >";
		$this -> load -> database();
		$query = $this -> db -> query("SELECT t.category,SUM(t.total) AS total FROM(SELECT pr.regimen_desc, dr.category, SUM( total ) AS total FROM  `dashboard_regimen` dr LEFT JOIN  `patient_byregimen_numbers` pr ON pr.regimen_desc = dr.name WHERE YEAR ='$year' AND MONTH ='$month' AND pipeline = '$pipeline' AND dr.category IS NOT NULL GROUP BY pr.regimen_desc)t GROUP BY t.category");
		$results = $query -> result_array();
		if ($results) {
			foreach ($results as $result) {
				if ($result['category'] == 1) {
					$strXML .= "<set label='Adult ART Patients' value='" . $result['total'] . "' />";
				}
				if ($result['category'] == 2) {
					$strXML .= "<set label='Pediatric ART Patients' value='" . $result['total'] . "' />";
				}
				if ($result['category'] == 3) {
					$strXML .= "<set label='PEP Children' value='" . $result['total'] . "' />";
				}
				if ($result['category'] == 4) {
					$strXML .= "<set label='PEP Adults' value='" . $result['total'] . "' />";
				}
				if ($result['category'] == 5) {
					$strXML .= "<set label='PMTCT Patients Infants' value='" . $result['total'] . "' />";
				}
				if ($result['category'] == 6) {
					$strXML .= "<set label='PMTCT Patients Mothers' value='" . $result['total'] . "' />";
				}
			}
		}
		header('Content-type: text/xml');
		echo $strXML .= "</chart>";
	}

	public function drawgraph() {
		$strDataURL = $this -> plotgraph();
		$chart = Fusioncharts(base_url() . "Scripts/FusionCharts/Column3D.swf", $strDataURL, "", "Patients_By_Line", 650, 300, false, false, false);
		$data['graph'] = $chart;
		$this -> load -> view("patient_nos_v", $data);
	}

}
?>