<?php
if (!defined('BASEPATH'))
	exit('No direct script access allowed');
class Patientscaleup_Management extends MY_Controller {
	function __construct() {
		parent::__construct();
		$data = array();
		ini_set("max_execution_time", "10000");
	}

	public function index() {
		$this -> load -> view("patient_scaleup_v");
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
		$strXML = "<chart useroundedges='1' caption='Patient Scale-Up Summary Upto $display_month-$year(" . $pipeline_display . ")'>";
		$this -> load -> database();
		$query = $this -> db -> query("SELECT `adult_art`,`paed_art`,`year`,`month` from patient_scaleup where concat(`year`,`month`) <='$year$month' and `pipeline`='$pipeline'");
		$results = $query -> result_array();

		if ($results) {
			$strXML .= "<categories>";
			foreach ($results as $result) {
				$year=$result["year"];
				$date = $result["year"]."-". $result["month"].'-02';
		        $display_month = date('M', strtotime($date));
				$strXML .= "<category label='$display_month-$year'/>";
			}
		}
		$strXML .= "</categories>";
		
		
		$strXML.="<dataset><dataset seriesName='Adult ART Patients' color='AFD8F8' showValues= '0'>";
		foreach ($results as $result) {
			     $strXML.="<set value='".$result['adult_art']."' />";    
		}
		$strXML.="</dataset></dataset>";
		
		$strXML.="<dataset><dataset seriesName='Paediatric ART Patients'  showValues= '0'>";
		foreach ($results as $result) {
			     $strXML.="<set value='".$result['paed_art']."' />";    
		}
		$strXML.="</dataset></dataset>";
		
		$strXML.="<lineset seriesname='Total ART Patients' showValues= '1' lineThickness='4' >";
		foreach ($results as $result) {
			     $strXML.="<set value='".($result['adult_art']+$result['paed_art'])."' />";    
		}
        $strXML.="</lineset>";
		header('Content-type: text/xml');
		echo $strXML .= "</chart>";
	}
}
?>