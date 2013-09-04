<?php
if (!defined('BASEPATH'))
	exit('No direct script access allowed');
class Chart_Management extends MY_Controller {
	function __construct() {
		parent::__construct();
		$data = array();
		ini_set("max_execution_time", "10000");
	}

	public function index() {

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

	public function patientByRegimen($year, $month, $pipeline) {
		$date = '2013-' . $month . '-02';
		$display_month = date('F', strtotime($date));
		if ($pipeline == '1') {
			$pipeline_display = "Kemsa";
		}
		if ($pipeline == '2') {
			$pipeline_display = "Kenya Pharma";
		}
	//	$strXML = "<chart caption='Patient By regimen Summary for $display_month-$year(" . $pipeline_display . ")'  pieSliceDepth='30' showBorder='0' formatNumberScale='0' showValues='1' showPercentageInLabel='1'  showPercentageValues='1' >";
		$this -> load -> database();
		$query = $this -> db -> query("SELECT t.category,SUM(t.total) AS total FROM(SELECT pr.regimen_desc, dr.category, SUM( total ) AS total FROM  `dashboard_regimen` dr LEFT JOIN  `patient_byregimen_numbers` pr ON pr.regimen_desc = dr.name WHERE YEAR ='$year' AND MONTH ='$month' AND pipeline = '$pipeline' AND dr.category IS NOT NULL GROUP BY pr.regimen_desc)t GROUP BY t.category");
		$results = $query -> result_array();
		/*if ($results) {
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
		}*/
		echo json_encode($results);
	}

	public function facilityConsumption($year, $month, $pipeline) {
		//$pipeline = "2";
		//$month = "02";
		//$year = "2013";
		$results = Facility_Consumption::getTotals($pipeline, $month, $year);
		$facility_results = Facility_Consumption::getFacilities($pipeline, $month, $year);
		$drug_results = Facility_Consumption::getDrugs($pipeline, $month, $year);
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

	public function patientScaleUp($year, $month, $pipeline) {
		$date = '2013-' . $month . '-02';
		$display_month = date('F', strtotime($date));
		if ($pipeline == '1') {
			$pipeline_display = "Kemsa";
		}
		if ($pipeline == '2') {
			$pipeline_display = "Kenya Pharma";
		}
		//$strXML = "<chart useroundedges='1' caption='Patient Scale-Up Summary Upto $display_month-$year(" . $pipeline_display . ")'>";
		$this -> load -> database();
		$query = $this -> db -> query("SELECT `adult_art`,`paed_art`,`year`,`month` from patient_scaleup where concat(`year`,`month`) <='$year$month' and `pipeline`='$pipeline'");
		$results = $query -> result_array();
       /*
		if ($results) {
			$strXML .= "<categories>";
			foreach ($results as $result) {
				$year = $result["year"];
				$date = $result["year"] . "-" . $result["month"] . '-02';
				$display_month = date('M', strtotime($date));
				$strXML .= "<category label='$display_month-$year'/>";
			}
		}
		$strXML .= "</categories>";

		$strXML .= "<dataset><dataset seriesName='Adult ART Patients' color='AFD8F8' showValues= '0'>";
		foreach ($results as $result) {
			$strXML .= "<set value='" . $result['adult_art'] . "' />";
		}
		$strXML .= "</dataset></dataset>";

		$strXML .= "<dataset><dataset seriesName='Paediatric ART Patients'  showValues= '0'>";
		foreach ($results as $result) {
			$strXML .= "<set value='" . $result['paed_art'] . "' />";
		}
		$strXML .= "</dataset></dataset>";

		$strXML .= "<lineset seriesname='Total ART Patients' showValues= '1' lineThickness='4' >";
		foreach ($results as $result) {
			$strXML .= "<set value='" . ($result['adult_art'] + $result['paed_art']) . "' />";
		}
		$strXML .= "</lineset>";
		header('Content-type: text/xml');
		echo $strXML .= "</chart>";
		*/
		echo json_encode(;)
	}

	public function pipelineConsumption($year, $month, $pipeline) {
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
				if ($result['consumption'] < 0) {
					$result['consumption'] = 0;
				}
				$strXML .= "<set label='" . $result['drugname'] . "' value='" . $result['consumption'] . "' />";
			}
			header('Content-type: text/xml');
			echo $strXML .= "</chart>";
		}
	}

public function getColumns($table = "") {
		$sql = "desc `$table`";
		$columns = array();
		$query = $this -> db -> query($sql);
		$results = $query -> result_array();
		$del_firstval = "recordID";
		$del_secondval = "parameter";
		foreach ($results as $value) {
			if ($value['Field'] == $del_firstval) {
				unset($value['Field']);
			} else if ($value['Field'] == $del_secondval) {
				unset($value['Field']);
			} else {
				$columns[] = $value['Field'];
			}

		}
		return $columns;
	}

	public function getChart( $result,$chartType = "line") {
		$series = array();
		$total_series = array();
		foreach ($results as $key => $result) {
			foreach ($result as $column => $value) {
				foreach ($columns as $month) {
					if ($column == $month) {
						$table_data[] = (double)$value;
					}
				}
			}
			$series = array('name' => $result['parameter'], 'data' => $table_data);
			$total_series[] = $series;
			unset($table_data);
		}
		if ($chartType == "line" || $chartType == "column" || $chartType == "bar") {
			$openview = 'chart_v';
		} else if ($chartType == "stacked_column") {
			$openview = 'chart_stacked_v';
			$chartType = "column";
		} else if ($chartType == "stacked_bar") {
			$openview = 'chart_stacked_v';
			$chartType = "bar";
		}
		$results = json_encode($total_series);
		$resultArraySize = 10;
		$data['resultArraySize'] = $resultArraySize;
		$data['container'] = 'chart_expiry';
		$data['chartType'] = $chartType;
		$data['chartTitle'] = trim(str_replace('_',' ' ,$table));
		$data['categories'] = json_encode($columns);
		$data['yAxis'] = 'No. of Queries';
		$data['resultArray'] = $results;
		$data['chartTypelist'] = array("line", "column", "bar", "stacked_column", "stacked_bar");
		$data['table_list'] = array("24_hour_query_resolution", "absolute_volume_or_processing_headcount", "activity_volume_by_country", "backlog_and_tat_compliance", "country_hct_by_weighted_volume", "cur_&_productivity_trend", "customer_complaints_vs_accuracy", "employed_vs_unemployed_worker", "interday_volumes_flow", "mandatory_elearning_completion_rate", "overtime_hours_vs_average_working_hour", "pass1_errors_vs_maker_accuracy", "pass2_errors_vs_checker_accuracy", "processors_&_non_processors_total_hct", "rejects_by_country_percentage", "rejects_or_defectives", "staff_turnover", "standard_&_average_working_days", "total_overtime", "volumes", "volumes_vs_weighted_volumes", "weighted_activity_volume_by_country", "weighted_volume_per_processing_hct");

		$data['contentView'] = $openview;
		$this -> loadChart($data);
	}

	public function loadPage($data) {
		$openview = 'index';
		$data['chartTitle'] = 'No chart chosen';
		$data['title'] = 'Stanchart Dashboard';
		$data['chartTypelist'] = array("line", "column", "bar", "stacked_column", "stacked_bar");
		$data['table_list'] = array("24_hour_query_resolution", "absolute_volume_or_processing_headcount", "activity_volume_by_country", "backlog_and_tat_compliance", "country_hct_by_weighted_volume", "cur_&_productivity_trend", "customer_complaints_vs_accuracy", "employed_vs_unemployed_worker", "interday_volumes_flow", "mandatory_elearning_completion_rate", "overtime_hours_vs_average_working_hour", "pass1_errors_vs_maker_accuracy", "pass2_errors_vs_checker_accuracy", "processors_&_non_processors_total_hct", "rejects_by_country_percentage", "rejects_or_defectives", "staff_turnover", "standard_&_average_working_days", "total_overtime", "volumes", "volumes_vs_weighted_volumes", "weighted_activity_volume_by_country", "weighted_volume_per_processing_hct");
		$data['contentView'] = $openview;
		$this -> load -> view('template', $data);
	}

	public function loadChart($data) {
		$this -> load -> view('chartLoader', $data);
	}

}
