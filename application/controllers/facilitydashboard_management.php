<?php
if (!defined('BASEPATH'))
	exit('No direct script access allowed');

class Facilitydashboard_Management extends MY_Controller {

	function __construct() {
		parent::__construct();
	}

	public function order_notification() {
		$sql = "SELECT status ,COUNT(*) AS total FROM `facility_order` WHERE code='1' GROUP BY STATUS";
		$query = $this -> db -> query($sql);
		$results = $query -> result_array();
		$status = "";
		$status_total = 0;
		$total = 0;
		$order_link = "";
		$dyn_table = "";
		if ($results) {
			foreach ($results as $result) {
				if ($result['status'] != '') {
					if ($result['status'] == 0) {
						$status = "Pending Orders";
						$order_link = site_url('order_rationalization/submitted_orders/0');
					} else if ($result['status'] == 1) {
						$status = "Approved Orders";
						$order_link = site_url('order_rationalization/submitted_orders/1');
					} else if ($result['status'] == 2) {
						$status = "Declined Orders";
						$order_link = site_url('order_rationalization/submitted_orders/2');
					} else if ($result['status'] == 3) {
						$status = "Dispatched Orders";
						$order_link = site_url('order_rationalization/submitted_orders/3');
					}
					$status_total = $result['total'];
					$total += $status_total;
					$dyn_table .= "<li><a id='inactive_users' href='$order_link'><i class='icon-th'></i>$status <div class='badge badge-important'>$status_total</div></a></li>";
				}
			}
		} else {
			$dyn_table .= "<li>No Data Available</li>";
		}
		$access_level = $this -> session -> userdata('user_indicator');
		if ($access_level == "nascop_administrator") {
			$dyn_table .= $this -> inactive_users();
		}
		$dyn_table = "<li>No Data Available</li>";
		echo $dyn_table;
	}

	public function inactive_users() {
		$facility_code = "NASCOP";
		$sql = "select count(*) as total from users where Facility_Code='$facility_code' and Active='0' and access_level='2'";
		$query = $this -> db -> query($sql);
		$results = $query -> result_array();
		$total = 0;
		$temp = "";
		$order_link = site_url('settings_management');
		if ($results) {
			foreach ($results as $result) {
				$total = $result['total'];
			}
		}
		$temp = "<li class='divider'></li><li><a href='$order_link'><i class='icon-th'></i>Deactivated Users <div class='badge badge-important'>$total</div></a></li>";
		return $temp;
	}

}
