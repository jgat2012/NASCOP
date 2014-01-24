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
		$data['supporter'] = Supporter::getThemAll();
		$this -> base_params($data);
	}

	public function download($type = "", $period = "") {

	}

	public function getCommodity($type = "SOH") {
		$columns = array('#', 'Reporting Period', 'Pipeline', 'Action');
		$links = array('dashboard_management/download/' . $type => 'download');
		//Get eSCM orders
		$escm_orders = Escm_Orders::getAll();
		$list = "";
		$order_list = array();
		$cdrr_nascop = array();
		$cdrr_escm = array();
		$table_name = $type;

		if ($escm_orders) {
			foreach ($escm_orders as $order_id) {
				array_push($order_list, $order_id -> cdrr_id);
			}
			$list = "'" . implode("','", $order_list) . "'";
		}
		$cdrr_nascop = Cdrr::getNascopPeriod($list);
		$counter = 0;
		foreach ($cdrr_nascop as $nascop) {
			$result[$counter]['period'] = date('F-Y', strtotime($nascop['period_begin']));
			$result[$counter]['pipeline'] = "Kemsa";
			$counter++;
		}
		$cdrr_escm = Cdrr::getEscmPeriod($list);
		foreach ($cdrr_escm as $esm) {
			$result[$counter]['period'] = date('F-Y', strtotime($esm['period_begin']));
			$result[$counter]['pipeline'] = "Kenya Pharma";
			$counter++;
		}
		echo $this -> showTable($columns, $result, $links, $table_name);
	}

	public function showTable($columns, $data = array(), $links = array(), $table_name = "") {
		$this -> load -> library('table');
		$tmpl = array('table_open' => '<table id=' . $table_name . '_listing class="table table-bordered table-striped tbl_nat_dashboard">');
		$this -> table -> set_template($tmpl);
		$this -> table -> set_heading($columns);
		$link_values = "";
		foreach ($data as $mydata) {
			//Set Up links
			foreach ($links as $i => $link) {
				$link_values .= "<a href='" . site_url($i . '/' . $mydata['period']) . "'>$link</a> | ";
			}
			$mydata['Options'] = rtrim($link_values, " | ");
			$link_values = "";
			$this -> table -> add_row($mydata);
		}
		return $this -> table -> generate();
	}

	public function base_params($data) {
		$this -> load -> view("template_national", $data);
	}

}
