<?php
if (!defined('BASEPATH'))

	exit('No direct script access allowed');

class Sync extends MY_Controller {
	function __construct() {
		parent::__construct();
	}

	public function user($email = "") {
		$email = urldecode($email);
		$email = "kevomarete@gmail.com";
		$users = Sync_User::getUser($email);
		$users = array($users);
		if ($users) {
			foreach ($users as $user) {
				$user_id = $user['id'];
			}
			$facilities = User_Facilities::getHydratedFacilityList($user_id);
			if ($facilities) {
				$user['ownUser_facility'] = $facilities['facility'];
			}
		}
		echo json_encode($user);

	}

	public function drugs() {
		$user = Sync_Drug::getAll();
		echo json_encode($user);
	}

	public function facilities() {
		$user = Sync_Facility::getAllHydrated();
		echo json_encode($user);
	}

	public function regimen() {
		$user = Sync_Regimen::getAllHydrated();
		echo json_encode($user);
	}

	public function facility($facility_id, $type, $period_begin = "") {
		$total_array = array();
		if ($type == "cdrr") {
			$cdrrs = Cdrr::getFacilityCdrr($facility_id);
			if ($period_begin != "") {
				$cdrrs = Cdrr::getFacilityCdrr($facility_id, $period_begin);
			}
			foreach ($cdrrs as $cdrr) {
				$id = $cdrr['id'];
				$items = Cdrr_Item::getItems($id);
				$logs = Cdrr_Log::getLogs($id);
				$main_array = $cdrr;
				$main_array["ownCdrr_item"] = $items;
				$main_array["ownCdrr_log"] = $logs;
				$total_array[] = $main_array;
			}

		} else if ($type == "maps") {
			$maps = Maps::getFacilityMap($facility_id);
			if ($period_begin != "") {
				$maps = Maps::getFacilityMap($facility_id, $period_begin);
			}
			foreach ($maps as $map) {
				$id = $map['id'];
				$items = Maps_Item::getItems($id);
				$logs = Maps_Log::getLogs($id);
				$main_array["ownMaps_item"] = $items;
				$main_array["ownMaps_log"] = $logs;
				$total_array[] = $main_array;
			}

		}
		echo json_encode($total_array, JSON_PRETTY_PRINT);
	}

	public function eid($facility_code) {
		$post_array = $_POST;
		$main_array = $post_array['json_data'];
		$responses = json_decode($main_array, TRUE);
		$sql = "DELETE FROM eid_info WHERE facility_code='$facility_code'";
		$this -> db -> query($sql);
		$this -> db -> insert_batch("eid_info", $responses);
		echo json_encode(array('EID Sync Success'));
	}

	public function gitlog() {
		$post_array = $_POST;
		$main_array = $post_array['json_data'];
		$responses = json_decode($main_array, TRUE);
		$sql = "DELETE FROM gitlog WHERE facility_code='$facility_code'";
		$this -> db -> query($sql);
		$this -> db -> insert_batch("gitlog", $responses);
		echo json_encode(array('GITLOG Sync Success'));
	}

	public function save($link = "nascop", $type = "cdrr", $id = "") {
		$post_array = $_POST;
		$main_array = $post_array['json_data'];
		$responses = json_decode($main_array, TRUE);

		if ($id != "") {
			$this -> delete($type, $id);
		}
		if ($link == "escm") {
			$pipeline = 'kenya pharma';
			$facility_id = '';
			if ($type == "cdrr") {
				$cdrr = array();
				foreach ($responses as $response) {
					foreach ($response as $index => $main) {
						if ($index == "ownCdrr_item") {
							$this -> db -> insert_batch("cdrr_item", $main);
						} else if ($index == "ownCdrr_log") {
							$this -> db -> insert_batch("cdrr_log", $main);
						} else {
							$cdrr[$index] = $main;
							//Get facility id
							if ($index == 'facility_id') {
								$facility_id = $main;
							}
						}
					}
				}
				$this -> db -> insert("cdrr", $cdrr);
				$cdrr_id = $this -> db -> insert_id();
				$this -> db -> insert("escm_orders", array("cdrr_id" => $cdrr_id));
			} else if ($type == "maps") {
				$maps = array();
				foreach ($responses as $response) {
					foreach ($response as $index => $main) {
						if ($index == "ownMaps_item") {
							$this -> db -> insert_batch("maps_item", $main);
						} else if ($index == "ownMaps_log") {
							$this -> db -> insert_batch("maps_log", $main);
						} else {
							$maps[$index] = $main;
							//Get facility id
							if ($index == 'facility_id') {
								$facility_id = $main;
							}
						}
					}
				}
				$this -> db -> insert("maps", $maps);
				$maps_id = $this -> db -> insert_id();
				$this -> db -> insert("escm_maps", array("maps_id" => $maps_id));

			}

			//Check if facility already uses adt
			$sql = "SELECT * FROM adt_sites WHERE facility_id='$facility_id' AND pipeline='$pipeline'";
			$query = $this -> db -> query($sql);
			$result = $query -> result_array($query);
			$count = count($result);
			if ($count == 0 && $facility_id != '') {//If facility not found
				$data_facility = array("facility_id " => $facility_id, "pipeline" => $pipeline);
				$this -> db -> insert('adt_sites', $data_facility);
			}

		} else {
			$pipeline = 'kemsa';
			$my_array = array();
			if ($type == "cdrr") {
				$cdrr = array();
				$cdrr_items = array();
				$cdrr_log = array();
				$temp_items = array();
				$temp_log = array();
				foreach ($responses as $response) {
					foreach ($response as $index => $main) {
						if ($index == "ownCdrr_item") {
							$cdrr_items[$index] = $main;
						} else if ($index == "ownCdrr_log") {
							$cdrr_log[$index] = $main;
						} else {
							$cdrr[$index] = $main;
							//Get facility id
							if ($index == 'facility_id') {
								$facility_id = $main;
							}
						}
					}
				}
				//Insert the cdrr and retrieve the auto_id assigned to it,this will be the cdrr_id
				$this -> db -> insert('cdrr', $cdrr);
				$cdrr_id = $this -> db -> insert_id();

				//Loop through cdrr_log and add cdrr_id
				foreach ($cdrr_log as $index => $logs) {
					foreach ($logs as $counter => $log) {
						foreach ($log as $ind => $lg) {
							if ($ind == "cdrr_id") {
								$temp_log[$counter]['cdrr_id'] = $cdrr_id;
							} else {
								$temp_log[$counter][$index] = $lg;
							}
						}
					}
				}
				$this -> db -> insert_batch('cdrr_log', $temp_log);

				//Loop through cdrr_item and add cdrr_id
				foreach ($cdrr_items as $index => $cdrr_item) {
					foreach ($cdrr_item as $counter => $items) {
						foreach ($items as $ind => $item) {
							if ($ind == "cdrr_id") {
								$temp_items[$counter]['cdrr_id'] = $cdrr_id;
							} else {
								$temp_items[$counter][$ind] = $item;
							}
						}
					}
				}
				$this -> db -> insert_batch('cdrr_item', $temp_items);
				$my_array = $this -> read($type, $cdrr_id);

			} else if ($type == "maps") {
				$maps = array();
				$temp_items = array();
				$temp_log = array();
				$maps_log = array();
				$maps_items = array();
				foreach ($responses as $response) {
					foreach ($response as $index => $main) {
						if ($index == "ownMaps_item") {
							$temp_items['maps_item'] = $main;
						} else if ($index == "ownMaps_log") {
							$temp_log['maps_log'] = $main;
						} else {
							$maps[$index] = $main;
							//Get facility id
							if ($index == 'facility_id') {
								$facility_id = $main;
							}
						}
					}
				}
				$this -> db -> insert("maps", $maps);
				$maps_id = $this -> db -> insert_id();

				//attach maps id to maps_log
				foreach ($temp_log as $logs) {
					foreach ($logs as $counter => $log) {
						foreach ($log as $index => $lg) {
							if ($index == "maps_id") {
								$maps_log[$counter]["maps_id"] = $maps_id;
							} else {
								$maps_log[$counter][$index] = $lg;
							}
						}
					}
				}
				$this -> db -> insert_batch('maps_log', $maps_log);

				//attach maps id to maps_item
				foreach ($temp_items as $temp_item) {
					foreach ($temp_item as $counter => $items) {
						foreach ($items as $ind => $item) {
							if ($ind == "maps_id") {
								$maps_items[$counter]['maps_id'] = $maps_id;
							} else {
								$maps_items[$counter][$ind] = $item;
							}
						}
					}
				}
				$this -> db -> insert_batch('maps_item', $maps_items);
				$my_array = $this -> read($type, $maps_id);
			}
			//Check if facility already uses adt
			$sql = "SELECT * FROM adt_sites WHERE facility_id='$facility_id' AND pipeline='$pipeline'";
			$query = $this -> db -> query($sql);
			$result = $query -> result_array($query);
			$count = count($result);
			if ($count == 0 && $facility_id != '') {//If facility not found
				$data_facility = array("facility_id " => $facility_id, "pipeline" => $pipeline);
				$this -> db -> insert('adt_sites', $data_facility);
			}

			echo json_encode($my_array);
		}
	}

	public function read($type = "cdrr", $id) {
		$main_array = array();
		if ($type == "cdrr") {
			$main_array = Cdrr::getCdrr($id);
			$main_array["ownCdrr_item"] = Cdrr_Item::getItems($id);
			$main_array["ownCdrr_log"] = Cdrr_Log::getLogs($id);
		} else if ($type == "maps") {
			$main_array = Maps::getMap($id);
			$main_array["ownMaps_item"] = Maps_Item::getItems($id);
			$main_array["ownMaps_log"] = Maps_Log::getLogs($id);
		}
		$main_array = array($main_array);
		return $main_array;
	}

	public function delete($type = "cdrr", $id) {
		$sql_array = array();
		if ($type == "cdrr") {
			$sql_array[] = "DELETE FROM cdrr where id='$id'";
			$sql_array[] = "DELETE FROM cdrr_item where cdrr_id='$id'";
			$sql_array[] = "DELETE FROM cdrr_log where cdrr_id='$id'";
		} else if ($type == "maps") {
			$sql_array[] = "DELETE FROM maps where id='$id'";
			$sql_array[] = "DELETE FROM maps_item where maps_id='$id'";
			$sql_array[] = "DELETE FROM maps_log where maps_id='$id'";
		}
		foreach ($sql_array as $sql) {
			$query = $this -> db -> query($sql);
		}
	}

}
