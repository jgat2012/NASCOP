<?php
if (!defined('BASEPATH'))
	exit('No direct script access allowed');

class Pharmacist_Management extends MY_Controller {

	function __construct() {
		parent::__construct();
	}

	public function getTopCommodities($limit, $start_date = "", $end_date = "") {
		/*
		 *Get all aggregated orders in the selected month
		 *Get the unique_id's of those orders
		 *Check for the top(limit)commodity stocks for those stocks
		 */
		$order_list = array();
		$dataArray = array();
		$columns = array();
		$total_series = array();
		$series=array();
		$order_array = Facility_Order::getAggregateOrders($start_date, $end_date);
		if ($order_array) {
			foreach ($order_array as $order_id) {
				array_push($order_list, $order_id -> Unique_Id);
			}
			$list = "'" . implode("','", $order_list) . "'";
			$qty_array = Cdrr_Item::getTopCommodities($limit, $list);
			if ($qty_array) {
				foreach ($qty_array as $qty) {
					$resupply = $qty -> Resupply;
					$dataArray[] = (int)$resupply;
					array_push($columns, $qty -> Drug_Id);
					$series = array('name' => "Commodity", 'data' => $dataArray);
				}
				$total_series[] = $series;
			}

		}
		$chart_title = "Top Commodity Orders";
		$yaxis = "Packs";
		$container="chart_expiry";
		//$this -> loadChart($columns, $total_series, $chart_title, $yaxis,$container);
	}

	public function getFacilitiesUsing($start_date = "", $end_date = "") {
		/*
		 *Get all aggregated orders in the selected month
		 *Get all facility information about those orders
		 *Create table for this information
		 */
		$facilities = Facility_Order::getFacilitiesUsingADT($start_date, $end_date);
		$dyn_table = "<table class='dataTables' border='1' id='patient_listing'  cellpadding='5'>";
		$dyn_table .= "<thead><tr><th>Facility Code</th><th>Facility Name</th><th>Facility Type</th><th>County</th></tr></thead>";
		$dyn_table .= "<tbody>";
		if ($facilities) {
			foreach ($facilities as $facility) {
				$dyn_table .= "<tr><td>" . $facility -> mflcode . "</td>";
				$dyn_table .= "<td>" . $facility -> FacilityName . "</td>";
				$dyn_table .= "<td>" . $facility -> FacilityType . "</td>";
				$dyn_table .= "<td>" . $facility -> facility_county . "</td></tr>";
			}
		}
		$dyn_table .= "</tbody>";
		$dyn_table .= "</table>";
		//echo $dyn_table;
	}

	public function getPickingList($start_date = "", $end_date = "") {
		/*
		 *Get all Picking Lists made in selected period
		 *Group by Pipeline and activity status
		 */
		/*$dataArray = array();
		$columns = array();
		$total_series = array();
		$series=array();
		$start_date = date('U', strtotime($start_date));
		$end_date = date('U', strtotime($end_date));
		$lists = Picking_List_Details::getListGroup($start_date, $end_date);
		foreach ($lists as $list) {
			if ($list -> Status == 0) {
				$status = "Open Lists";
			} else {
				$status = "Closed Lists";
			}
			$pipeline_type = $list -> Pipeline . " ($status)";
			$total = $list -> total;
			$dataArray[] = (int)$total;
			array_push($columns, $pipeline_type);
			$series = array('name' => "Picking Lists", 'data' => $dataArray);
		}
		$total_series[] = $series;
		$chart_title = "Pipeline Picking Lists";
		$yaxis = "No. of Lists";
		$container="chart_appointments";*/
		//$this -> loadChart($columns, $total_series, $chart_title, $yaxis,$container);
	}

	public function getFacilitiesDelay($start_date = "", $end_date = "") {
		/*
		 *Get all aggregated orders in the selected month that delayed by the 20th of the previous month
		 *Get all facility information about those orders
		 *Calculate delay by dates
		 *Create table for this information
		 */
		$facilities = Facility_Order::getFacilitiesDelayOrders($start_date, $end_date);
		$deadline_date = date("Y-m-20", strtotime($start_date . " -1 month"));
		$dyn_table = "<table class='dataTables' border='1' id='patient_listing'  cellpadding='5'>";
		$dyn_table .= "<thead><tr><th>Facility Code</th><th>Facility Name</th><th>Order Date</th><th>Delayed By <sup>(" . date('d-M-Y', strtotime($deadline_date)) . ")</sup></th></tr></thead>";
		$dyn_table .= "<tbody>";
		if ($facilities) {
			foreach ($facilities as $facility) {
				$order_date = date('d-M-Y', $facility -> CreatedTimestamp);
				if ($order_date > $deadline_date) {
					$date1 = new DateTime($deadline_date);
					$date2 = new DateTime($order_date);
					$interval = $date1 -> diff($date2);
					$dyn_table .= "<tr><td>" . $facility -> mflcode . "</td>";
					$dyn_table .= "<td>" . $facility -> FacilityName . "</td>";
					$dyn_table .= "<td>" . $order_date . "</td>";
					$dyn_table .= "<td>" . $interval -> d . " Day(s)</td></tr>";
				}
			}
		}
		$dyn_table .= "</tbody>";
		$dyn_table .= "</table>";
		//echo $dyn_table;
	}

	public function loadChart($columns, $total_series, $chart_title, $yaxis,$container) {
		$resultArray = json_encode($total_series);
		$categories = json_encode($columns);
		$resultArraySize = 0;
		$data['resultArraySize'] = $resultArraySize;
		$data['container'] = $container;
		$data['chartType'] = 'bar';
		$data['title'] = 'Chart';
		$data['chartTitle'] = $chart_title;
		$data['categories'] = $categories;
		$data['yAxix'] = $yaxis;
		$data['resultArray'] = $resultArray;
		$this -> load -> view('chart_v', $data);
	}

}
?>