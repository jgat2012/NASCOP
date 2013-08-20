<?php
if (!defined('BASEPATH'))
	exit('No direct script access allowed');

class Synchronization_Management extends MY_Controller {
	function __construct() {
		parent::__construct();
	}

	public function download_to_adt($facility) {
		//Variables
		$main_array = array();
		$temp_array = array();
		$middle_array = array();
		$table_array = array("cdrr_item", "maps_item", "order_comment");
		$sql = "";
		$unique_column = "";
		$order_number = "";

		foreach ($table_array as $table) {
			$sql = "select * from facility_order where is_uploaded='1' and code >='1' and central_facility='$facility'";
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
						$middle_array[] = $temp_array;
						$sql = "update facility_order set is_uploaded='0' where unique_id='$order_number' ";
						$this -> db -> query($sql);
					}
					$main_array[$table] = $middle_array;
					unset($middle_array);
				}
			}
		}
		echo json_encode($main_array);
	}

	public function synchronize() {
		//Variables
		$sql = '';
		$order_number = '';
		$unique_column = 'unique_id';
		$table_array = array();
		$data_array = array();
		$data_array = $_POST;
		$table_array = $data_array['data'];
		$table_array = json_decode($table_array, TRUE);
		foreach ($table_array as $table => $table_contents) {
			if ($table == "facility_order") {
				foreach ($table_contents as $contents) {
					$order_number = $contents['unique_id'];
					$sql = "select is_uploaded as available from facility_order where unique_id='$order_number'";
					$query = $this -> db -> query($sql);
					$results = $query -> result_array();
					unset($contents['id']);
					if ($results) {
						if ($results[0]['available'] == 0) {
							//Record has not been downloaded(Hence Update)
							$this -> db -> where($unique_column, $order_number);
							$this -> db -> update($table, $contents);
						}
					} else {
						//No record Hence Insert
						$this -> db -> insert($table, $contents);
					}
				}
			} else {
				foreach ($table_contents as $contents) {
					$unique_id = $contents['unique_id'];
					$sql = "select * from $table where $unique_column='$unique_id'";
					$query = $this -> db -> query($sql);
					$results = $query -> result_array();
					unset($contents['id']);
					if ($results) {
						$order_number = $contents['unique_id'];
						$this -> db -> where($unique_column, $unique_id);
						$this -> db -> update($table, $contents);
					} else {
						$this -> db -> insert($table, $contents);
					}
				}
			}
		}
	}

	public function base_params($data) {
		$data['title'] = "System Synchronization";
		$data['banner_text'] = "System Synchronization";
		$data['link'] = "synchronization_management";
		$this -> load -> view("template", $data);
	}

}
