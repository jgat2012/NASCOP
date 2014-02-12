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
		$series = array();
		$order_array = Cdrr::getOrders($start_date, $end_date);
		if ($order_array) {
			foreach ($order_array as $order_id) {
				array_push($order_list, $order_id -> id);
			}
			$list = "'" . implode("','", $order_list) . "'";
			$qty_array = Cdrr_Item::getOrderItems($list, $limit);
			if ($qty_array) {
				foreach ($qty_array as $qty) {
					$resupply = $qty -> resupply;
					$dataArray[] = (int)$resupply;
					$drug_name = $qty -> S_Drug -> name . " [" . $qty -> S_Drug -> abbreviation . "] " . $qty -> S_Drug -> strength . " " . $qty -> S_Drug -> strength;
					array_push($columns, $drug_name);
					$series = array('name' => "Commodity", 'data' => $dataArray);
				}
				$total_series[] = $series;
			}

		}
		$chart_title = "Top Commodity Orders";
		$yaxis = "Packs";
		$container = "chart_expiry";
		$this -> loadChart($columns, $total_series, $chart_title, $yaxis, $container);
	}

	public function getFacilitiesUsing($start_date = "", $end_date = "") {
		/*
		 *Get all aggregated orders in the selected month
		 *Get all facility information about those orders
		 *Create table for this information
		 */
		$escm_orders = Escm_Orders::getAll();
		$list = "";
		$order_list = array();

		if ($escm_orders) {
			foreach ($escm_orders as $order_id) {
				array_push($order_list, $order_id -> cdrr_id);
			}
			$list = "'" . implode("','", $order_list) . "'";
		}
		$cdrrs = Cdrr::getFacilities($start_date, $end_date, $list);
		$dyn_table = "<table class='dataTables' border='1' id='patient_listing'  cellpadding='5'>";
		$dyn_table .= "<thead><tr><th>Facility Code</th><th>Facility Name</th><th>Facility Type</th><th>County</th></tr></thead>";
		$dyn_table .= "<tbody>";
		if ($cdrrs) {
			foreach ($cdrrs as $cdrr) {
				$dyn_table .= "<tr><td>" . $cdrr -> Facility -> code . "</td>";
				$dyn_table .= "<td>" . $cdrr -> Facility -> name . "</td>";
				$dyn_table .= "<td>" . $cdrr -> Facility -> category . "</td>";
				$dyn_table .= "<td>" . $cdrr -> Facility -> County -> county . "</td></tr>";
			}
		}
		$dyn_table .= "</tbody>";
		$dyn_table .= "</table>";
		echo $dyn_table;
	}

	public function getPickingList($start_date = "", $end_date = "") {
		/*
		 *Get all Picking Lists made in selected period
		 *Group by Pipeline and activity status
		 */
		$dataArray = array();
		$columns = array();
		$total_series = array();
		$series = array();
		$start_date = date('U', strtotime($start_date. "-1 day"));
		$end_date = date('U', strtotime($end_date));
		$open_total = 0;
		$closed_total = 0;
		$lists = Picking_List_Details::getListGroup($start_date, $end_date);
		foreach ($lists as $list) {
			if ($list -> Status == 0) {
				$status = "Open Lists";
				$open_total = $list -> total;
			} else {
				$status = "Closed Lists";
				$closed_total = $list -> total;
			}
		}
		array_push($columns, "Open Lists");
		array_push($columns, "Closed Lists");
		$dataArray[] = (int)$open_total;
		$dataArray[] = (int)$closed_total;
		$series = array('name' => "Picking Lists", 'data' => $dataArray);

		$total_series[] = $series;
		$chart_title = "Picking Lists";
		$yaxis = "No. of Lists";
		$container = "chart_appointments";
		$this -> loadChart($columns, $total_series, $chart_title, $yaxis, $container);
	}

	public function getFacilitiesDelay($start_date = "", $end_date = "") {
		/*
		 *Get all aggregated orders in the selected month that delayed by the 20th of the previous month
		 *Get all facility information about those orders
		 *Calculate delay by dates
		 *Create table for this information
		 */
		$escm_orders = Escm_Orders::getAll();
		$list = "";
		$order_list = array();

		if ($escm_orders) {
			foreach ($escm_orders as $order_id) {
				array_push($order_list, $order_id -> cdrr_id);
			}
			$list = "'" . implode("','", $order_list) . "'";
		}
		$deadline_date = date("Y-m-10", strtotime($start_date));
		$cdrrs = Cdrr::getFacilities($start_date, $end_date, $list);

		$dyn_table = "<table class='dataTables' border='1' id='facility_listing'  cellpadding='5'>";
		$dyn_table .= "<thead><tr><th>Facility Code</th><th>Facility Name</th><th>Order Date</th><th>Delayed By <sup>(" . date('d-M-Y', strtotime($deadline_date)) . ")</sup></th></tr></thead>";
		$dyn_table .= "<tbody>";
		if ($cdrrs) {
			foreach ($cdrrs as $cdrr) {
				$order_date = $cdrr -> created;
				if ($order_date > $deadline_date) {
					$date1 = new DateTime($deadline_date);
					$date2 = new DateTime($order_date);
					$interval = $date1 -> diff($date2);
					$dyn_table .= "<tr><td>" . $cdrr -> Facility -> code . "</td>";
					$dyn_table .= "<td>" . $cdrr -> Facility -> name . "</td>";
					$dyn_table .= "<td>" . date('d-M-Y', strtotime($cdrr -> created)) . "</td>";
					$dyn_table .= "<td>" . $interval -> d . " Day(s)</td></tr>";
				}
			}
		}
		$dyn_table .= "</tbody>";
		$dyn_table .= "</table>";
		echo $dyn_table;
	}

	public function loadChart($columns, $total_series, $chart_title, $yaxis, $container) {
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

	public function order_notification() {
		$dyn_table = "<li>No Data Available</li>";
		echo $dyn_table;
	}

}
?>