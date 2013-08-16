<?php
if (!defined('BASEPATH'))
	exit('No direct script access allowed');

class Synchronization_Management extends MY_Controller {
	function __construct() {
		parent::__construct();
	}

	public function synchronize_orders($facility) {
		$mainstrSQl = "";
		$id_str = "";
		$temp_str = "";
		$sql = "";
		$table_lists = array("facility_order", "cdrr_item", "maps_item", "order_comment");
		$id_array = array();
		foreach ($table_lists as $table_list) {
			$strSQl = "";
			$table_name = $table_list;
			if ($table_list == "facility_order") {
				$sql = "select * from $table_name where code >='1' and central_facility='$facility'";
			} else if ($table_list == "cdrr_item") {
				if ($id_str) {
					$sql = "select * from  $table_name where cdrr_id IN($id_str)";
				}
				$temp_str = $id_str;
			} else if ($table_list == "maps_item") {
				if ($temp_str) {
					$sql = "select * from  $table_name where maps_id IN($temp_str)";
				}
			} else if ($table_list == "order_comment") {
				if ($temp_str) {
					$sql = "select * from  $table_name where order_number IN($temp_str)";
				}
			}
			if ($sql) {
				$query = $this -> db -> query($sql);
				$results = $query -> result_array();
				if ($results) {
					foreach ($results as $val => $value_array) {
						$fields = "";
						$values = "";
						$temp_val = "";
						$id_str = "";
						$strSQl .= "<br/>INSERT INTO $table_list (";
						foreach ($value_array as $col => $value) {
							if ($col != 'id') {
								$temp_val .= "," . $col . "=" . "\"" . trim($value) . "\"";
								$fields .= "," . $col;
								$values .= ",\"" . trim($value) . "\"";
							}
							if ($col == "unique_id" && $table_list == "facility_order") {
								$id_array[] = $value;
								foreach ($id_array as $temp_id) {
									$id_str .= ",\"" . $temp_id . "\"";
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
			}
			$mainstrSQl .= $strSQl;
		}
		echo $mainstrSQl;

	}

	public function getSQL($facility) {
		$sql = "";
		if ($this -> input -> post("sql")) {
			$sql = $this -> input -> post("sql");
			if ($sql != '') {
				$sql = base64_decode($sql);
				$queries = explode(";", $sql);
				foreach ($queries as $query) {
					if (strlen($query) > 0) {
						$this -> db -> query($query);
					}
				}
			}
		}
		$sql = $this -> synchronize_orders($facility);
		if ($sql != '') {
			echo $sql = base64_encode($sql);
		} else {
			echo $sql = "";
		}
	}

	public function download_to_adt($facility) {
		//Variables
		$main_array = array();
		$temp_array = array();
		$table_array = array("cdrr_item", "maps_item", "order_comment");
		$sql = "";
		$unique_column = "";
		$order_number = "";

		foreach ($table_array as $table) {
			$sql = "select * from facility_order where is_downloaded='0' and code >='1' and central_facility='$facility'";
			$query = $this -> db -> query($sql);
			$order_array = $query -> result_array();
			if ($order_array) {
				$main_array["facility_order"] = $order_array;
				foreach ($table_array as $table) {
					if ($table == "cdrr_item") {
						$unique_column = "cdrr_id";
					} else if ($table == "maps_item") {
						$unique_column = "maps_id";
					} else if ($table == "order_comment") {
						$unique_column = "order_number";
					}
					foreach ($order_array as $order) {
						$order_number = $order['unique_id'];
						$sql = "select * from $table where $unique_column='$order_number'";
						$query = $this -> db -> query($sql);
						$temp_array = $query -> result_array();
						//$sql = "update facility_order set is_downloaded='1' where unique_id='$order_number' ";
						//$this -> db -> query($sql);
					}
					$main_array[$table] = $temp_array;
					unset($temp_array);
				}
			}
		}
		header('Content-type: application/json');
		return json_encode($main_array);

	}

	public function synchronize() {
		$data_array = $this -> download_to_adt("13050");
		$table_array = json_decode($data_array, TRUE);
		foreach ($table_array as $table => $table_contents) {
			//echo $table."\n";
			foreach ($table_contents as $contents) {
                     echo $contents['unique_id']."\n";
			}
			die();
		}
	}

	public function base_params($data) {
		$data['title'] = "System Synchronization";
		$data['banner_text'] = "System Synchronization";
		$data['link'] = "synchronization_management";
		$this -> load -> view("template", $data);
	}

}
