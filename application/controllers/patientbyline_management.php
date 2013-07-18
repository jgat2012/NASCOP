<?php
class Patientbyline_Management extends MY_Controller {
	function __construct() {
		parent::__construct();
		$this -> load -> helper('fusioncharts');
	}

	public function index() {
		//$this->drawgraph();
		$this->load->view("patient_nos_v");
	}

	public function plotgraph($year,$month,$pipeline) {
		$date= '2013-'.$month.'-02';
		$display_month=date('F',strtotime($date));
		if($pipeline=='1'){
		$pipeline_display="Kemsa";	
		}
		if($pipeline=='2'){
		$pipeline_display="Kenya Pharma";		
		}

		$strXML = "<chart caption='ART Patient Summary for $display_month-$year(".$pipeline_display.")'  pieSliceDepth='30' showBorder='0' formatNumberScale='0' showValues='1' showPercentageInLabel='1'  showPercentageValues='1' >";
		$this -> load -> database();
		$query = $this -> db -> query("SELECT * FROM  `patient_scaleup` WHERE MONTH ='$month' AND YEAR ='$year' AND PIPELINE='$pipeline'");
		$results = $query -> result_array();
		if ($results) {
			foreach ($results as $result) {
				$strXML .= "<set label='Adult ART Patients' value='" . $result['adult_art'] . "' />";
				$strXML .= "<set label='Pediatric ART Patients' value='" . $result['paed_art'] . "' />";
				$strXML .= "<set label='PEP Children' value='" . $result['paed_pep'] . "' />";
				$strXML .= "<set label='PEP Adults' value='" . $result['adult_pep'] . "' />";
				$strXML .= "<set label='PMTCT Patients Infants' value='" . $result['mothers_pmtct'] . "' />";
				$strXML .= "<set label='PMTCT Patients Mothers' value='" . $result['infant_pmtct'] . "' />";
			}
		}
		$strXML .= "</chart>";
	    header('Content-type: text/xml');
		echo $strXML;
		
	}
	
	public function drawgraph(){
		$strDataURL =$this -> plotgraph();
		$chart = Fusioncharts(base_url()."Scripts/FusionCharts/Column3D.swf", $strDataURL, "","Patients_By_Line", 650, 300, false, false,false);
		$data['graph'] = $chart;
		$this->load->view("patient_nos_v",$data);
	}

}
?>