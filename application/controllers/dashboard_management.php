<?php
if (!defined('BASEPATH'))
	exit('No direct script access allowed');
class Dashboard_Management extends MY_Controller {
	function __construct() {
		parent::__construct();
		$data = array();
		ini_set("max_execution_time", "10000");
	}

	public function index() {
		$data['content_view'] = "home_v";
		$data['hide_side_menu'] = 1;
		$data['banner_text'] = "National Dashboard";
		$data['title'] = "webADT | National Dashboard";
		$data['supporter']=Supporter::getThemAll();
		$this -> base_params($data);
	}

	public function notification($id, $notification, $count, $icon, $link) {
		$note = "<li><a id='$id' href='$link'><i class='$icon'></i>$notification<div class='badge badge-important'>$count</div></a></li>";
		echo $note;
	}

	public function facilitySOH($year, $month, $pipeline) {
		//$pipeline = "1";
		//$month = "12";
		//$year = "2011";
		$results = Facility_Soh::getTotals($pipeline, $month, $year);
		$facility_results = Facility_Soh::getFacilities($pipeline, $month, $year);
		$drug_results = Facility_Soh::getDrugs($pipeline, $month, $year);
		$count = 1;
		$i = 0;
		$dyn_table = "<table border='1'  cellspacing='0.75' cellpadding='1'>";
		$dyn_table .= "<thead><tr><th>Facility Name</th>";
		foreach ($drug_results as $drug_result) {
			$dyn_table .= "<th>" . $drug_result['drugname'] . "</th>";
		}
		$dyn_table .= "</tr></thead>";
		$dyn_table .= "<tbody><tr><td>" . $facility_results[$i]['facilityname'] . "</td>";
		foreach ($results as $result) {
			$dyn_table .= "<td>" . $result['total'] . "</td>";
			$count++;
			if ($count == sizeof($drug_results) + 1) {
				$dyn_table .= "</tr>";
				$count = 1;
				if ($i < sizeof($facility_results) - 1) {
					$i++;
					$dyn_table .= "<tr><td>" . $facility_results[$i]['facilityname'] . "</td>";

				}
			}

		}
		$dyn_table .= "</tbody></table>";
		echo $dyn_table;
	}

	public function base_params($data) {
		$this -> load -> view("template_national", $data);
	}

}
