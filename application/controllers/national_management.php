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
			$dyn_table = "<table id='ca_stock_status' border='1' id='patient_listing'  cellpadding='5' class='dataTables'>";
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
	
	//Patients By Regimen
	public function pa_patients_by_regimen($year, $month, $pipeline, $type = 0) {
		$results = Dashboard_Patientbyline::getMonthlyValues($pipeline, $month, $year);
		
		$resultArraySize=0;
		$nameArray = array();
		$totalArray=array();
		foreach ($results as $value) {
			$nameArray[] = str_replace('Regimens','',$value['category']);
			$totalArray[] = (int)$value['total'];
			$resultArraySize++;
		}
		
		$resultArray = array( array('name' => 'Patients', 'data' => $totalArray));
		
		$resultArray = json_encode($resultArray);
		$categories = $nameArray;
		$categories = json_encode($categories);
		//Load Data Variables
		$data['resultArraySize'] = $resultArraySize;
		$data['container'] = 'patient_reg_chart';
		$data['chartType'] = 'bar';
		$data['title'] = 'Chart';
		$data['chartTitle'] = 'Patients By Regimen';
		$data['categories'] = $categories;
		$data['yAxix'] = 'Regimen';
		$data['resultArray'] = $resultArray;
		$this -> load -> view('chart_v', $data);
	}
	
	//Current patient by ART site
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
		$data['label'] = 'Facility';
		$data['table'] = 'facilities';
		$data['actual_page'] = 'View Facilities';
		$data['dyn_table'] = $dyn_table;
		$this -> base_params($data);
	}

	public function pa_patients_scaleup($year, $month, $pipeline, $type = 0) {
		$results = Patient_Scaleup::getTotals($pipeline, $month, $year);
		$resultArraySize=0;
		$nameArray = array();
		$adultTotalArray=array();
		$paedTotalArray=array();
		foreach ($results as $value) {
			$nameArray[] =date('M-Y',strtotime('01-'.$value['month'].'-'.$value['year']));
			$adultTotalArray[] = (int)$value['adult_art'];
			$paedTotalArray[] = (int)$value['paed_art'];
			$resultArraySize++;
		}
		
		$resultArray = array( array('name' => 'Adult', 'data' => $adultTotalArray), array('name' => 'Child', 'data' => $paedTotalArray));
		$resultArray = json_encode($resultArray);
		$categories = $nameArray;
		$categories = json_encode($categories);
		//Load Data Variables
		$data['resultArraySize'] = $resultArraySize;
		$data['container'] = 'patient_scale_chart';
		$data['chartType'] = 'bar';
		$data['title'] = 'Chart';
		$data['chartTitle'] = 'Patient Scale Up';
		$data['categories'] = $categories;
		$data['yAxix'] = 'Period';
		$data['resultArray'] = $resultArray;
		$this -> load -> view('chart_v', $data);
		
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
		$results = Dashboard_Orderpoints::getMonthlySummary($pipeline, $month, $year);
		$summary_array = array("central='1'", "standalone='1'", "central='0' and standalone='0'");
		$central_total = 0;
		$standalone_total = 0;
		$satellite_total = 0;
		$overall_total = 0;
		$dyn_table = "<table border='1' id='patient_listing'  cellpadding='5' class='dataTables'>";
		$dyn_table .= "<thead><tr><th>Provinces</th><th>Central Sites</th><th>Standalone Sites</th><th>Satellite Sites</th><th>Total</th></tr></thead>";
		$dyn_table .= "<tbody>";
		foreach ($results as $result) {
			$province = $result['province'];
			$total = $result['total'];
			$dyn_table .= "<tr><td>" . $province . "</td>";
			$count = 0;
			foreach ($summary_array as $summary) {
				$province_total = Dashboard_Orderpoints::getMonthlyProvinceSummary($pipeline, $month, $year, $province, $summary);
				$dyn_table .= "<td>" . $province_total['total'] . "</td>";
				if ($count == 0) {
					$central_total += $province_total['total'];
				} else if ($count == 1) {
					$standalone_total += $province_total['total'];
				} else if ($count == 2) {
					$satellite_total += $province_total['total'];
				}
				$count++;
			}
			$overall_total += $total;
			$dyn_table .= "<td>" . $total . "</td></tr>";
		}
		$dyn_table .= "</tbody><tfoot><tr><td>Totals</td><td>$central_total</td><td>$standalone_total</td><td>$satellite_total</td><td>$overall_total</td></tr>";
		$dyn_table .= "</tfoot></table>";
		echo $dyn_table;
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
		$summary_array = array("satellite='0' and standalone='0'", "standalone='1'", "satellite='1'");
		$central_total = 0;
		$standalone_total = 0;
		$satellite_total = 0;
		$overall_total = 0;
		$dyn_table = "<table border='1' id='patient_listing'  cellpadding='5' class='dataTables'>";
		$dyn_table .= "<thead><tr><th>Provinces</th><th>Central Sites</th><th>Standalone Sites</th><th>Satellite Sites</th><th>Total</th></tr></thead>";
		$dyn_table .= "<tbody>";
		foreach ($results as $result) {
			$province = $result['province'];
			$total = $result['total'];
			$dyn_table .= "<tr><td>" . $province . "</td>";
			$count = 0;
			foreach ($summary_array as $summary) {
				$province_total = Dashboard_Servicepoints::getMonthlyProvinceSummary($pipeline, $month, $year, $province, $summary);
				$dyn_table .= "<td>" . $province_total['total'] . "</td>";
				if ($count == 0) {
					$central_total += $province_total['total'];
				} else if ($count == 1) {
					$standalone_total += $province_total['total'];
				} else if ($count == 2) {
					$satellite_total += $province_total['total'];
				}
				$count++;
			}
			$overall_total += $total;
			$dyn_table .= "<td>" . $total . "</td></tr>";
		}
		$dyn_table .= "</tbody><tfoot><tr><td>Totals</td><td>$central_total</td><td>$standalone_total</td><td>$satellite_total</td><td>$overall_total</td></tr>";
		$dyn_table .= "</tfoot></table>";
		echo $dyn_table;

	}

	public function oa_orders_by_commodity($year, $month, $pipeline, $type = 0) {
		/*
		 * Convert month and year to period start to period end
		 * Get all Approved/Dispatched Aggregated Orders(Unique Ids) in period for that pipeline
		 * Get all commodities in the cdrr_item table for orders that have the pre-selected unique ids 
		 */


		$period_start=date('Y-m-01',strtotime($year."-".$month."-01"));
		$period_end=date('Y-m-t',strtotime($year."-".$month."-01"));
		$facility_orders=Facility_Order::getOrderCommoditiesByPipeline($pipeline,$period_start,$period_end);
		$id_list=array();

		foreach($facility_orders as $facility_order){
			$unique_id=$facility_order['Unique_Id'];
			$id_list[]=$unique_id;
		}
		$orders = "'".implode("','",$id_list)."'";
		$results=Cdrr_Item::getAllCommodities($orders);
		echo "<pre>";
		print_r($results);
		echo "</pre>";

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