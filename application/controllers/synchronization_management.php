<?php
if (!defined('BASEPATH'))
	exit('No direct script access allowed');

class Synchronization_Management extends MY_Controller {
	function __construct() {
		parent::__construct();
	}

	public function index() {

	}

	public function synchronize_orders($facility) {
		$mainstrSQl = "";
		$id_str = "";
		$temp_str = "";
		$table_lists = array("facility_order", "cdrr_item", "maps_item", "order_comment");
		$id_array = array();
		foreach ($table_lists as $table_list) {
			$strSQl = "";
			$table_name = $table_list;
			if ($table_list == "facility_order") {
				$sql = "select * from $table_name where code='1' and facility_id='$facility' or central_facility='$facility'";
			} else if ($table_list == "cdrr_item") {
				$sql = "select * from  $table_name where cdrr_id IN($id_str)";
				$temp_str = $id_str;
			} else if ($table_list == "maps_item") {
				$sql = "select * from  $table_name where maps_id IN($temp_str)";
			} else if ($table_list == "order_comment") {
				$sql = "select * from  $table_name where order_number IN($temp_str)";
			}

			$query = $this -> db -> query($sql);
			$results = $query -> result_array();
			if ($results) {
				foreach ($results as $val => $value_array) {
					$fields = "";
					$values = "";
					$temp_val = "";
					$id_str = "";
					$strSQl .= "INSERT INTO $table_list (";
					foreach ($value_array as $col => $value) {
						$temp_val .= "," . $col . "=" . "\"" . trim($value) . "\"";
						$fields .= "," . $col;
						$values .= ",\"" . trim($value) . "\"";
						if ($col == "id" && $table_list == "facility_order") {
							$id_array[] = $value;
							foreach ($id_array as $temp_id) {
								$id_str .= "," . $temp_id;
							}
							$id_str = substr($id_str, 1);
						}
					}
					$fields = substr($fields, 1);
					$values = substr($values, 1);
					$temp_val = substr($temp_val, 1);
					$strSQl .= $fields . ")VALUES(" . $values . ") ON DUPLICATE KEY UPDATE $temp_val ;";
				}
			}

			$mainstrSQl .= $strSQl;
		}
		return $mainstrSQl;

	}

	public function getSQL($facility) {
		$sql = "";
		if ($this -> input -> post("sql")) {
			$sql = $this -> input -> post("sql");
			$queries = explode(";", $sql);
			foreach ($queries as $query) {
				if (strlen($query) > 0) {
					$this -> db -> query($query);
				}
			}
		}
		$sql = $this -> synchronize_orders($facility);
		echo $sql;
	}

	public function base_params($data) {
		$data['title'] = "System Synchronization";
		$data['banner_text'] = "System Synchronization";
		$data['link'] = "synchronization_management";
		$this -> load -> view("template", $data);
	}

}
