<?php
if (!defined('BASEPATH'))
	exit('No direct script access allowed');
class National_Management extends MY_Controller {
	function __construct() {
		parent::__construct();
		/*
		 * ca->Commodity Analysis
		 * pa->Patient Analysis
		 * fa->Facility Analysis
		 * oa->Order Analysis
		 * ra->Reporting Analysis
		 */
	}

	public function ca_stock_status($year, $month, $pipeline, $type = 0) {
		/*
		 * Check if (type==0) then it is facility else national stock_status
		 */
		if ($type == 1) {

		} else {
			$results = Facility_Soh::getTotals($pipeline, $month, $year);
			$facility_results = Facility_Soh::getFacilities($pipeline, $month, $year);
			$drug_results = Facility_Soh::getDrugs($pipeline, $month, $year);
			$count = 1;
			$i = 0;
			$dyn_table = "<table border='1' id='patient_listing'  cellpadding='5' class='dataTables'>";
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
			//echo $dyn_table;
			$data['label'] = 'Facility';
			$data['table'] = 'facilities';
			$data['actual_page'] = 'View Facilities';
			$data['dyn_table'] = $dyn_table;
			$this -> base_params($data);
		}
	}

	public function ca_consumption($year, $month, $pipeline, $type = 0) {
		/*
		 * Check if (type==0) then it is facility else pipeline consumption
		 */
		if ($type == 1) {
			$results = Pipeline_Consumption::getConsumption($pipeline, $month, $year);
			echo "<pre>";
			print_r($results);
			echo "</pre>";

		} else {
			$results = Facility_Consumption::getTotals($pipeline, $month, $year);
			$facility_results = Facility_Consumption::getFacilities($pipeline, $month, $year);
			$drug_results = Facility_Consumption::getDrugs($pipeline, $month, $year);
			$count = 1;
			$i = 0;
			$dyn_table = "<table border='1' id='patient_listing'  cellpadding='5' class='dataTables'>";
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
	}

	public function pa_patients_by_regimen($year, $month, $pipeline, $type = 0) {
		$results = Dashboard_Patientbyline::getMonthlyValues($pipeline, $month, $year);
		echo "<pre>";
		print_r($results);
		echo "</pre>";
	}

	public function pa_patients_by_artsite($year, $month, $pipeline, $type = 0) {
		$facility_results = Patient_Byregimen_Numbers::getFacilities($pipeline, $month, $year);
		$regimen_results = Patient_Byregimen_Numbers::getRegimens($pipeline, $month, $year);
		$count = 1;
		$i = 0;
		$dyn_table = "<table border='1' id='patient_listing'  cellpadding='5' class='dataTables'>";
		$dyn_table .= "<thead><tr><th>Facility Name</th>";
		foreach ($regimen_results as $regimen_result) {
			$dyn_table .= "<th>" . $regimen_result['regimen_desc'] . "</th>";
		}
		$dyn_table .= "</tr></thead>";
		$dyn_table .= "<tbody>";

		/*
		 * Outer Loop check through facility array
		 * Check array keys for the current facility in loop
		 * Inner Loop check through regimen array
		 * Within inner loop check for facility and regimen in result array
		 */

		foreach ($facility_results as $facility) {
			$facility_name = trim(str_replace(array('\'', '"', ',', ';', '<', '>', '.'), ' ', $facility['facilityname']));
			$results = Patient_Byregimen_Numbers::getSpecificTotals($pipeline, $month, $year, $facility_name);
			$dyn_table .= "<tr><td>" . $facility_name . "</td>";
			if ($results) {
				foreach ($results as $result) {
					$dyn_table .= "<td>" . $result['total'] . "</td>";
				}
			}
			$dyn_table .= "</tr>";
		}
		$dyn_table .= "</tbody></table>";
		echo $dyn_table;
	}

	public function pa_patients_scaleup($year, $month, $pipeline, $type = 0) {
		$results = Patient_Scaleup::getTotals($pipeline, $month, $year);
		echo "<pre>";
		print_r($results);
		echo "</pre>";
	}

	public function fa_ordering_sites_list($year, $month, $pipeline, $type = 0) {
		$results = Dashboard_Orderpoints::getMonthList($pipeline, $month, $year);
		$ftype = "";
		$dyn_table = "<table border='1' id='patient_listing'  cellpadding='5' class='dataTables'>";
		$dyn_table .= "<thead><tr><th>MFL Code</th><th>Facility Name</th><th>District</th><th>Province</th><th>Facility Type</th></tr></thead>";
		$dyn_table .= "<tbody>";
		foreach ($results as $result) {
			if ($result['central'] == 1) {
				$ftype = "Central Site";
			} else if ($result['standalone'] == 1) {
				$ftype = "Standalone Site";
			} else if ($result['store'] == 1) {
				$ftype = "District Store";
			}
			$dyn_table .= "<tr><td>" . $result['mfl_code'] . "</td><td>" . $result['facility_name'] . "</td><td>" . $result['district'] . "</td><td>" . $result['province'] . "</td><td>$ftype</td></tr>";
		}
		$dyn_table .= "</tbody></table>";
		echo $dyn_table;
	}

	public function fa_ordering_sites_summary($year, $month, $pipeline, $type = 0) {

	}

	public function fa_service_points_list($year, $month, $pipeline, $type = 0) {
		$results = Dashboard_Servicepoints::getMonthList($pipeline, $month, $year);
		$ftype = "";
		$dispense = "";
		$dyn_table = "<table border='1' id='patient_listing'  cellpadding='5' class='dataTables'>";
		$dyn_table .= "<thead><tr><th>MFL Code</th><th>Satellite Site</th><th>Central Site</th><th>District</th><th>Province</th><th>Dispensing Point</th><th>Facility Type</th></tr></thead>";
		$dyn_table .= "<tbody>";
		foreach ($results as $result) {
			if ($result['dispensing'] == 1) {
				$dispense = "Yes";
			} else {
				$dispense = "No";
			}
			if ($result['satellite'] == 0) {
				$ftype = "Central Site";
			} else if ($result['satellite'] == 1) {
				$ftype = "Satellite Site";
			} else if ($result['standalone'] == 1) {
				$ftype = "Standalone Site";
			}
			$dyn_table .= "<tr><td>" . $result['mfl_code'] . "</td><td>" . $result['facility_name'] . "</td><td>" . $result['centralsite_name'] . "</td><td>" . $result['district'] . "</td><td>" . $result['province'] . "</td><td>$dispense</td><td>$ftype</td></tr>";
		}
		$dyn_table .= "</tbody></table>";
		echo $dyn_table;
	}

	public function fa_service_points_summary($year, $month, $pipeline, $type = 0) {
		$results = Dashboard_Servicepoints::getMonthlySummary($pipeline, $month, $year);
		$dyn_table = "<table border='1' id='patient_listing'  cellpadding='5' class='dataTables'>";
		$dyn_table .= "<thead><tr><th>Provinces</th><th>Standalone Sites</th><th>Satellite Sites</th><th>Total</th></tr></thead>";
		$dyn_table .= "<tbody>";
		foreach($results as $result){
			$dyn_table .= "<tr><td>" . $result['province'] . "</td><td>" . $result['Standalone'] . "</td><td>" . $result['Satellite'] . "</td><td>" . $result['Total'] . "</td></tr>";
		}
		$dyn_table .= "</tbody></table>";
		echo $dyn_table;
		
	}

	public function oa_orders_by_commodity() {

	}

	public function oa_orders_reporting_rate() {

	}

	public function oa_fmaps_statistics() {

	}

	public function oa_pipeline_order_deliveryrates() {

	}

	public function ra_reporting_rates_per_facilitytype() {

	}

	public function ra_reporting_rates_per_facility() {

	}

	public function ra_non_reporting_facility_rates() {
		$data['label'] = 'Facility';
		$data['table'] = 'facilities';
		$data['actual_page'] = 'View Facilities';
		$data['dyn_table'] = $dyn_table;
		$this -> base_params($data);
	}

	public function base_params($data) {
		$data['content_view'] = "admin/add_param_a";
		$data['title'] = "webADT | System Admin";
		$data['banner_text'] = "System Admin";
		$this -> load -> view('admin/admin_template', $data);
	}

}
?>