<?php
if (!defined('BASEPATH'))
	exit('No direct script access allowed');

class Synchronization_Management extends MY_Controller {
	function __construct() {
		parent::__construct();
	}

	public function index() {

	}

	public function synchronize_orders() {
		$mainstrSQl = "";
		$table_lists = array("facility_order", "cdrr_item", "maps_item", "order_comment");
		foreach ($table_lists as $table_list) {
			$strSQl = "";
			$table_name = $table_list;
			$sql = "select * from  $table_name";
			$query = $this -> db -> query($sql);
			$results = $query -> result_array();
			if ($results) {
				foreach ($results as $val => $value_array) {
					$fields = "";
					$values = "";
					$temp_val="";
					$strSQl .= "INSERT INTO $table_list (";
					foreach ($value_array as $col => $value) {
                        $temp_val.=",".$col."="."'".$value."'";
						$fields .= "," . $col;
						$values .= "," . $value;
					}
					$fields = substr($fields, 1);
					$values = substr($values, 1);
					$temp_val = substr($temp_val, 1);
					$strSQl .= $fields . ")VALUES(" . $values . ") ON DUPLICATE KEY UPDATE $temp_val ;<br/>";
				}
			}
			$mainstrSQl .= $strSQl;
		}
		 echo $mainstrSQl;
		 
	}
	public function getSQL(){
		if($this->input->post("sql")){
			echo "Marete";
		}
	}

	public function base_params($data) {
		$data['title'] = "System Synchronization";
		$data['banner_text'] = "System Synchronization";
		$data['link'] = "synchronization_management";
		$this -> load -> view("template", $data);
	}

}
