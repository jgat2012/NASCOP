<?php
if (!defined('BASEPATH'))

	exit('No direct script access allowed');

class Sync extends MY_Controller {
	function __construct() {
		parent::__construct();
	}

	public function user($email) {
		$email = urldecode($email);
		//$email ='kevomarete@gmail.com';
		$user = Sync_User::getUser($email);
		echo json_encode($user);
	}

	public function drugs() {
		$user = Sync_Drug::getAll();
		echo json_encode($user);
	}

	public function facilities() {
		$user = Sync_Facility::getAll();
		echo json_encode($user);
	}

	public function regimen() {
		$user = Regimen::getAllRegimens();
		echo json_encode($user);
	}

	public function save($link = "nascop", $type = "cdrr", $id = "") {
		$post_array = $_POST;
		$main_array = $post_array['json_data'];
		$responses = json_decode($main_array, TRUE);

		if ($id != "") {
			$this -> delete($type, $id);
		}
		if ($link == "escm") {
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
						}
					}
				}
				$this -> db -> insert("maps", $maps);
				$maps_id = $this -> db -> insert_id();
				$this -> db -> insert("escm_maps", array("maps_id" => $maps_id));
			}

		} else {
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
						}
					}
				}
				//Insert the cdrr and retrieve the auto_id assigned to it,this will be the cdrr_id
				$this -> db -> insert('cdrr', $cdrr);
				$cdrr_id = $this -> db -> insert_id();

				//Loop through cdrr_log and add cdrr_id
				foreach ($cdrr_log as $index => $log) {
					foreach ($log as $ind => $lg) {
						if ($ind == "cdrr_id") {
							$lg['cdrr_id'] = $cdrr_id;
						}
						$temp_log[] = $lg;
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
						}
					}
				}
				$this -> db -> insert("maps", $maps);
				$maps_id = $this -> db -> insert_id();

				//attach maps id to maps_log
				foreach ($temp_log as $logs) {
					foreach ($logs as $index => $log) {
						if ($index == "maps_id") {
							$log["maps_id"] = $maps_id;
						}
						$maps_log[] = $log;
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
