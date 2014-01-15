<?php
class Order extends MY_Controller {
	function __construct() {
		parent::__construct();
	}

	public function download($facility) {
		$sql = "SELECT facilitycode FROM facilities WHERE parent='$facility'";
		$query = $this -> db -> query($sql);
		$facilities = $query -> result_array();
		$facility_list = array();
		foreach ($facilities as $facility) {
			$facility_list[] = $facility['facilitycode'];
		}
		$facility_list = implode(",", $facility_list);
		$facility_list = rtrim($facility_list, ",");
		//Get Items to upload
		$sql = "SELECT * FROM sync_log WHERE facility IN($facility_list)";
		$query = $this -> db -> query($sql);
		$results = $query -> result_array();
		$cdrr_array = array();
		$main_array = array();
		$cdrr_list = "";
		if ($results) {
			foreach ($results as $result) {
				$item_id = $result['item_id'];
				$item_type = $result['item_type'];
				if ($item_type == "cdrr") {
					$cdrr_array[] = $item_id;
				}
			}
			$cdrr_list = implode(",", $cdrr_array);
			$cdrr_list = rtrim($cdrr_list, ",");
			if ($cdrr_list) {
				$sql_array = array();
				$sql_array['cdrr'] = "SELECT * FROM cdrr WHERE id IN ($cdrr_list)";
				$sql_array['cdrr_item'] = "SELECT * FROM cdrr_item WHERE cdrr_id IN ($cdrr_list)";
				$sql_array['cdrr_log'] = "SELECT * FROM cdrr_log WHERE cdrr_id IN ($cdrr_list)";
				foreach ($sql_array as $table => $sql) {
					$query = $this -> db -> query($sql);
					$results = $query -> result_array();
					if ($results) {
						foreach ($results as $result) {
							foreach ($result as $index => $res) {
								if ($index != "id") {
									$main_array[$table][$counter][$index] = $res;
								} else {
									$res = $this -> check_new($table, $res);
									if ($res != null) {
										$counter = $res;
									} else {
										$counter = md5($res . $facility);
									}
								}
							}
						}
					}
				}
			}
			$sql = "DELETE FROM sync_log WHERE facility IN($facility_list)";
			$query = $this -> db -> query($sql);
			echo json_encode($main_array);
		}
		return null;
	}

	public function upload() {
		$post_array = $_POST;
		$main_array = $post_array['data'];
		$main_array = json_decode($main_array, TRUE);
		if (!empty($main_array)) {
			$order_maps = array();
			foreach ($main_array as $table => $main) {
				foreach ($main as $original_id => $contents) {
					if ($table == "cdrr") {
						$new_id = $this -> check_original($table, $original_id);
						if ($new_id == null) {
							$this -> db -> insert($table, $contents);
							$cdrr_id = $this -> db -> insert_id();
							$new_id = $cdrr_id;
							$order_maps['type'] = $table;
							$order_maps['old_id'] = $original_id;
							$order_maps['new_id'] = $cdrr_id;
							$this -> db -> insert("order_maps", $order_maps);
						} else {
							$this -> db -> where('id', $new_id);
							$this -> db -> update($table, $contents);
						}
					} else {
						$contents['cdrr_id'] = $this -> check_id($contents['cdrr_id']);
						$new_id = $this -> check_original($table, $original_id);
						if ($new_id == null) {
							$this -> db -> insert($table, $contents);
							$new_id = $this -> db -> insert_id();
							$order_maps['type'] = $table;
							$order_maps['old_id'] = $original_id;
							$order_maps['new_id'] = $new_id;
							$this -> db -> insert("order_maps", $order_maps);
						} else {
							$this -> db -> where('id', $new_id);
							$this -> db -> update($table, $contents);
						}
					}
					unset($order_maps);
				}
			}
		}
	}

	public function check_original($type, $original_id) {
		$sql = "SELECT new_id FROM order_maps WHERE type='$type' AND old_id='$original_id' LIMIT 1";
		$query = $this -> db -> query($sql);
		$results = $query -> result_array();
		if ($results) {
			return $results[0]['new_id'];
		}
		return null;
	}

	public function check_new($type, $new_id) {
		$sql = "SELECT old_id FROM order_maps WHERE type='$type' AND new_id='$new_id' LIMIT 1";
		$query = $this -> db -> query($sql);
		$results = $query -> result_array();
		if ($results) {
			return $results[0]['old_id'];
		}
		return null;
	}

	public function check_id($new_id) {
		$sql = "SELECT new_id FROM order_maps WHERE type='cdrr' AND old_id='$new_id' LIMIT 1";
		$query = $this -> db -> query($sql);
		$results = $query -> result_array();
		if ($results) {
			return $results[0]['new_id'];
		}
		return null;
	}

}
