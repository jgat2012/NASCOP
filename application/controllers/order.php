<?php
class Order extends MY_Controller {
	function __construct() {
		parent::__construct();
	}

	public function index() {
		$data['content_view'] = "orders/order_v";
		$data['page_title'] = "my Orders";
		$data['banner_text'] = "Facility Orders";
		$data['order_table'] = $this -> get_orders();
		$this -> base_params($data);
	}

	public function get_orders() {
		$columns = array('#', '#CDRR-ID', '#MAPS-ID', 'Period Beginning', 'Status', 'Facility Name', 'Options');
		$sql = "SELECT c.id,m.id as map,IF(c.code='0',CONCAT('D-CDRR#',c.id),CONCAT('F-CDRR#',c.id)) as cdrr_id,IF(m.code='0',CONCAT('D-MAPS#',m.id),CONCAT('F-MAPS#',m.id)) as maps_id,c.period_begin,c.status as status_name,IF(c.code='1',CONCAT(f.name,CONCAT(' ','Dispensing Point')),f.name)as facility_name
				FROM cdrr c
				LEFT JOIN facilities f ON f.id=c.facility_id
				LEFT JOIN maps m ON f.id=m.facility_id
				WHERE c.code='0'
				AND m.code=c.code
				AND m.period_begin=c.period_begin
				AND m.period_end=c.period_end";
		$query = $this -> db -> query($sql);
		$results = $query -> result_array();
		$links = array("order/view_order" => "view order");
		return $this -> showTable($columns, $results, $links);
	}

	public function view_order($cdrr_id) {
		$order_array = array();
		$sql = "SELECT c.*,ci.*,cl.*,f.*,co.county as county_name,d.name as district_name,u.*,al.level_name,IF(c.code='0',CONCAT('D-CDRR#',c.id),CONCAT('F-CDRR#',c.id)) as cdrr_label,os.name as status_name,IF(c.code='1',CONCAT(f.name,CONCAT(' ','Dispensing Point')),f.name)as facility_name
				FROM cdrr c
				LEFT JOIN cdrr_item ci ON ci.cdrr_id=c.id
				LEFT JOIN cdrr_log cl ON cl.cdrr_id=c.id
				LEFT JOIN facilities f ON f.facilitycode=c.facility_id
				LEFT JOIN counties co ON co.id=f.county
				LEFT JOIN district d ON d.id=f.district
				LEFT JOIN users u ON u.id=cl.user_id
				LEFT JOIN access_level al ON al.id=u.Access_Level
				LEFT JOIN order_status os ON os.id=c.status
				WHERE c.id='$cdrr_id'";
		$query = $this -> db -> query($sql);
		$order_array = $query -> result_array();
		$data['order_array'] = $order_array;
		$data['page_title'] = "Central Aggregate(D-CDRR)";
		$data['content_view'] = "orders/order_template";
		$data['banner_text'] = "Central Aggregate(D-CDRR)";
		$data['hide_side_menu'] = 1;
		$data['cdrr_id'] = $cdrr_id;
		$data['regimens'] = Regimen::getAllObjects("13050");
		$data['regimen_categories'] = Regimen_Category::getAll();

		$sql = "SELECT d.id,UPPER(d.Drug) As Drug,d.Pack_Size,d.Safety_Quantity,d.Quantity,d.Duration
			        FROM cdrr_item ci
			        LEFT JOIN drugcode d ON d.id=ci.drug_id
			        WHERE ci.cdrr_id='$cdrr_id'
			        AND d.Enabled='1'
			        ORDER BY d.id asc";

		$query = $this -> db -> query($sql);
		$data['commodities'] = $query -> result();
		$this -> base_params($data);
	}

	public function view_cdrr($cdrr_id) {
		$cdrr_array = array();
		$sql = "SELECT c.*,ci.*,cl.*,f.*,co.county as county_name,d.name as district_name,u.*,al.level_name,IF(c.code='0',CONCAT('D-CDRR#',c.id),CONCAT('F-CDRR#',c.id)) as cdrr_label,os.name as status_name,IF(c.code='1',CONCAT(f.name,CONCAT(' ','Dispensing Point')),f.name)as facility_name
				FROM cdrr c
				LEFT JOIN cdrr_item ci ON ci.cdrr_id=c.id
				LEFT JOIN cdrr_log cl ON cl.cdrr_id=c.id
				LEFT JOIN facilities f ON f.facilitycode=c.facility_id
				LEFT JOIN counties co ON co.id=f.county
				LEFT JOIN district d ON d.id=f.district
				LEFT JOIN users u ON u.id=cl.user_id
				LEFT JOIN access_level al ON al.id=u.Access_Level
				LEFT JOIN order_status os ON os.id=c.status
				WHERE c.id='$cdrr_id'";
		$query = $this -> db -> query($sql);
		$cdrr_array = $query -> result_array();
		$data['cdrr_array'] = $cdrr_array;
		$data['options'] = "view";
		$this -> set_cdrr($cdrr_array[0]['code'], $data);
	}

	public function set_cdrr($type, $cdrr_array = array()) {
		$this -> session -> set_userdata("order_go_back", "cdrr");
		$data['hide_generate'] = 0;
		$data['hide_side_menu'] = 0;
		$data['hide_save'] = 0;
		$data['hide_btn'] = 0;
		$data['options'] = "none";
		if ($type == 1) {
			$data['page_title'] = "Central Dispensing Point(F-CDRR)";
			$data['content_view'] = "orders/cdrr_template";
			$data['banner_text'] = "Central Dispensing Point(F-CDRR)";
			$facility = $this -> session -> userdata("facility");
		} else if ($type == 2) {//Satellite cdrr
			$data['page_title'] = "Satellite Facility(F-CDRR)";
			$data['content_view'] = "orders/cdrr_template";
			$data['banner_text'] = "Satellite Facility(F-CDRR)";
			$facility = $this -> input -> post("satellite_facility", TRUE);
			if ($facility == null) {
				$facility = $this -> session -> userdata("facility");
			} else {
				$data['hide_generate'] = 1;
			}

		} else {
			$data['page_title'] = "Central Aggregate(D-CDRR)";
			$data['content_view'] = "orders/cdrr_template";
			$data['banner_text'] = "Central Aggregate(D-CDRR)";
			$data['hide_generate'] = 2;
			$facility = $this -> session -> userdata("facility");
		}

		if (!empty($cdrr_array)) {
			$data['cdrr_array'] = $cdrr_array['cdrr_array'];
			$data['status_name'] = $cdrr_array['cdrr_array'][0]['status_name'];
			$facility = $cdrr_array['cdrr_array'][0]['facility_id'];
			$code = $cdrr_array['cdrr_array'][0]['code'];
			$data['options'] = $cdrr_array['options'];
			if ($data['options'] == "view") {
				$data['hide_save'] = 1;
			}
			$data['hide_btn'] = 1;
			$cdrr_id = $cdrr_array['cdrr_array'][0]['cdrr_id'];
			$data['cdrr_id'] = $cdrr_id;
			if ($data['options'] == "view") {
				if ($data['status_name'] != "received" || $data['status_name'] != "dispatched") {
					$data['option_links'] = "<li class='active'><a href='" . site_url("order/view_cdrr/" . $cdrr_id) . "'>view</a></li><li><a href='" . site_url("order/update_cdrr/" . $cdrr_id) . "'>update</a></li><li><a class='delete' href='" . site_url("order/delete_cdrr/" . $cdrr_id) . "'>delete</a></li>";
				} else {
					$data['option_links'] = "<li class='active'><a href='" . site_url("order/view_cdrr/" . $cdrr_id) . "'>view</a></li>";
				}
			} else if ($data['options'] == "update") {
				if ($data['status_name'] != "received" || $data['status_name'] != "dispatched") {
					$data['option_links'] = "<li><a href='" . site_url("order/view_cdrr/" . $cdrr_id) . "'>view</a></li><li class='active'><a href='" . site_url("order/update_cdrr/" . $cdrr_id) . "'>update</a></li><li><a class='delete' href='" . site_url("order/delete_cdrr/" . $cdrr_id) . "'>delete</a></li>";
				} else {
					$data['option_links'] = "";
				}
			}

			if ($code == 0) {
				$and = "";
			} else {
				$and = "AND ci.resupply !='0'";
			}
			if ($cdrr_array['options'] == "update") {
				$supplier = Facilities::getSupplier($facility);
				$data['commodities'] = Drugcode::getAllObjects($supplier);
			} else {
				$sql = "SELECT d.id,UPPER(d.Drug) As Drug,du.Name as Unit_Name,d.Pack_Size,d.Safety_Quantity,d.Quantity,d.Duration
			        FROM cdrr_item ci
			        LEFT JOIN drugcode d ON d.id=ci.drug_id
			        LEFT JOIN drug_unit du ON du.id=d.unit
			        WHERE ci.cdrr_id='$cdrr_id'
			        AND d.Enabled='1'
			        $and
			        ORDER BY d.id asc";
				$query = $this -> db -> query($sql);
				$data['commodities'] = $query -> result();
			}
		} else {
			$period_start = date('Y-m-01');
			$period_end = date('Y-m-t');
			$duplicate = $this -> checkDuplicate($type, $period_start, $period_end, $facility);
			$supplier = Facilities::getSupplier($facility);
			$data['commodities'] = Drugcode::getAllObjects($supplier);
			if ($duplicate == true) {
				redirect("order");
			}
		}
		$data['facility_object'] = Facilities::getCodeFacility($facility);
		$data['report_type'] = $type;
		$this -> base_params($data);
	}

	public function update_cdrr($cdrr_id) {
		$cdrr_array = array();
		$sql = "SELECT c.*,ci.*,cl.*,f.*,co.county as county_name,d.name as district_name,u.*,al.level_name,IF(c.code='0',CONCAT('D-CDRR#',c.id),CONCAT('F-CDRR#',c.id)) as cdrr_label,os.name as status_name,IF(c.code='1',CONCAT(f.name,CONCAT(' ','Dispensing Point')),f.name)as facility_name,ci.id as item_id
				FROM cdrr c
				LEFT JOIN cdrr_item ci ON ci.cdrr_id=c.id
				LEFT JOIN cdrr_log cl ON cl.cdrr_id=c.id
				LEFT JOIN facilities f ON f.facilitycode=c.facility_id
				LEFT JOIN counties co ON co.id=f.county
				LEFT JOIN district d ON d.id=f.district
				LEFT JOIN users u ON u.id=cl.user_id
				LEFT JOIN access_level al ON al.id=u.Access_Level
				LEFT JOIN order_status os ON os.id=c.status
				WHERE c.id='$cdrr_id'";
		$query = $this -> db -> query($sql);
		$cdrr_array = $query -> result_array();
		$data['cdrr_array'] = $cdrr_array;
		$data['options'] = "update";
		$this -> set_cdrr($cdrr_array[0]['code'], $data);
	}

	public function delete_cdrr($cdrr_id) {
		$access_level = $this -> session -> userdata("user_indicator");
		if ($access_level == "facility_administrator") {
			$sql_array = array();
			$sql_array[] = "DELETE FROM cdrr where id='$cdrr_id'";
			$sql_array[] = "DELETE FROM cdrr_item where cdrr_id='$cdrr_id'";
			$sql_array[] = "DELETE FROM cdrr_log where cdrr_id='$cdrr_id'";
			foreach ($sql_array as $sql) {
				$query = $this -> db -> query($sql);
			}
		}
		redirect("order");
	}

	public function approve_cdrr($cdrr_id) {
		$sql = "SELECT * 
		      FROM cdrr c,maps m 
		      WHERE c.id='$cdrr_id'
		      AND c.code=m.code
		      AND c.period_begin=m.period_begin
			  AND c.period_end=m.period_end";
		$query = $this -> db -> query($sql);
		$results = $query -> result_array();
		$this -> session -> set_flashdata('order_message', "Cdrr Cannot be Approved because corresponding maps is missing");
		if ($results) {
			$sql = "UPDATE cdrr c,(SELECT id
		 FROM order_status os
		 WHERE os.name LIKE '%approved%') as s
		 SET c.status=s.id
		 WHERE c.id='$cdrr_id'";
			$query = $this -> db -> query($sql);
			$this -> session -> set_flashdata('order_message', "Cdrr Approved Successfully");
			$sync_log = array();
			$sync_log['item_id'] = $cdrr_id;
			$sync_log['item_type'] = "cdrr";
			$this -> db -> insert("sync_log", $sync_log);
		}
		redirect("order/view_cdrr/" . $cdrr_id);
	}

	public function archive_cdrr($cdrr_id) {
		$sql = "UPDATE cdrr c,(SELECT id
		        FROM order_status os
		        WHERE os.name LIKE '%archived%') as s
		        SET c.status=s.id
		        WHERE c.id='$cdrr_id'";
		$query = $this -> db -> query($sql);
		$this -> session -> set_flashdata('order_message', "Cdrr Archived Successfully");
		$sync_log = array();
		$sync_log['item_id'] = $cdrr_id;
		$sync_log['item_type'] = "cdrr";
		$this -> db -> insert("sync_log", $sync_log);
		redirect("order/view_cdrr/" . $cdrr_id);
	}

	public function download_cdrr($cdrr_id) {
		$this -> load -> library('PHPExcel');
		$cdrr_array = array();
		$dir = "Export";

		$sql = "SELECT c.*,ci.*,cl.*,f.*,co.county as county_name,d.name as district_name,u.*,al.level_name,IF(c.code='0',CONCAT('D-CDRR#',c.id),CONCAT('F-CDRR#',c.id)) as cdrr_label,os.name as status_name,IF(c.code='1',CONCAT(f.name,CONCAT(' ','Dispensing Point')),f.name)as facility_name,dm.name as drug_map
				FROM cdrr c
				LEFT JOIN cdrr_item ci ON ci.cdrr_id=c.id
				LEFT JOIN cdrr_log cl ON cl.cdrr_id=c.id
				LEFT JOIN facilities f ON f.facilitycode=c.facility_id
				LEFT JOIN counties co ON co.id=f.county
				LEFT JOIN district d ON d.id=f.district
				LEFT JOIN users u ON u.id=cl.user_id
				LEFT JOIN access_level al ON al.id=u.Access_Level
				LEFT JOIN order_status os ON os.id=c.status
				LEFT JOIN drugcode dc ON dc.id=ci.drug_id
				LEFT JOIN drug_mapping dm ON dm.id=dc.map
				WHERE c.id='$cdrr_id'";
		$query = $this -> db -> query($sql);
		$cdrr_array = $query -> result_array();
		$report_type = $cdrr_array[0]['code'];
		$template = "";

		if ($report_type == 0) {
			$template = "cdrr_aggregate";
		} else {
			$template = "cdrr_satellite";
		}

		$inputFileType = 'Excel5';
		$inputFileName = $_SERVER['DOCUMENT_ROOT'] . '/ADT/assets/' . $template . '.xls';
		$objReader = PHPExcel_IOFactory::createReader($inputFileType);
		$objPHPExcel = $objReader -> load($inputFileName);

		/*Delete all files in export folder*/
		if (is_dir($dir)) {
			$files = scandir($dir);
			foreach ($files as $object) {
				if ($object != "." && $object != "..") {
					unlink($dir . "/" . $object);
				}
			}
		} else {
			mkdir($dir);
		}

		$objPHPExcel -> getActiveSheet() -> SetCellValue('C4', $cdrr_array[0]['name']);
		$objPHPExcel -> getActiveSheet() -> SetCellValue('G4', $cdrr_array[0]['facilitycode']);

		$objPHPExcel -> getActiveSheet() -> SetCellValue('C5', $cdrr_array[0]['county_name']);
		$objPHPExcel -> getActiveSheet() -> SetCellValue('G5', $cdrr_array[0]['district_name']);

		$objPHPExcel -> getActiveSheet() -> SetCellValue('D7', date('d/m/y', strtotime($cdrr_array[0]['period_begin'])));
		$objPHPExcel -> getActiveSheet() -> SetCellValue('G7', date('d/m/y', strtotime($cdrr_array[0]['period_end'])));

		if (strtoupper($cdrr_array[0]['sponsors']) == "GOK") {
			$loc = "D";
		} else if (strtoupper($cdrr_array[0]['sponsors']) == "PEPFAR") {
			$loc = "F";
		} else if (strtoupper($cdrr_array[0]['sponsors']) == "MSF") {
			$loc = "H";
		}
		$objPHPExcel -> getActiveSheet() -> SetCellValue($loc . '9', "X");

		$services = explode(",", $cdrr_array[0]['services']);
		foreach ($services as $service) {
			if (strtoupper($service) == "ART") {
				$objPHPExcel -> getActiveSheet() -> SetCellValue('D11', "X");
			} else if (strtoupper($service) == "PMTCT") {
				$objPHPExcel -> getActiveSheet() -> SetCellValue('F11', "X");
			} else if (strtoupper($service) == "PEP") {
				$objPHPExcel -> getActiveSheet() -> SetCellValue('H11', "X");
			}
		}

		$objPHPExcel -> getActiveSheet() -> SetCellValue('A95', $cdrr_array[0]['comments']);
		$arr = $objPHPExcel -> getActiveSheet() -> toArray(null, true, true, true);
		for ($i = 18; $i <= 93; $i++) {
			$drug = $arr[$i]['A'];
			if ($drug) {
				$key = $this -> searchForId($drug, $cdrr_array);
				if ($key !== null) {
					$objPHPExcel -> getActiveSheet() -> SetCellValue('C' . $i, $cdrr_array[$key]['balance']);
					$objPHPExcel -> getActiveSheet() -> SetCellValue('D' . $i, $cdrr_array[$key]['received']);
					$objPHPExcel -> getActiveSheet() -> SetCellValue('E' . $i, $cdrr_array[$key]['dispensed_units']);
					$objPHPExcel -> getActiveSheet() -> SetCellValue('F' . $i, $cdrr_array[$key]['losses']);
					$objPHPExcel -> getActiveSheet() -> SetCellValue('G' . $i, $cdrr_array[$key]['adjustments']);
					$objPHPExcel -> getActiveSheet() -> SetCellValue('H' . $i, $cdrr_array[$key]['count']);
					if ($cdrr_array[0]['code'] == 0) {
						$objPHPExcel -> getActiveSheet() -> SetCellValue('I' . $i, $cdrr_array[$key]['aggr_consumed']);
						$objPHPExcel -> getActiveSheet() -> SetCellValue('J' . $i, $cdrr_array[$key]['aggr_on_hand']);
						$objPHPExcel -> getActiveSheet() -> SetCellValue('K' . $i, $cdrr_array[$key]['expiry_quant']);
						$objPHPExcel -> getActiveSheet() -> SetCellValue('L' . $i, $cdrr_array[$key]['expiry_date']);
						$objPHPExcel -> getActiveSheet() -> SetCellValue('M' . $i, $cdrr_array[$key]['out_of_stock']);
						$objPHPExcel -> getActiveSheet() -> SetCellValue('N' . $i, $cdrr_array[$key]['resupply']);
					} else {
						$objPHPExcel -> getActiveSheet() -> SetCellValue('I' . $i, $cdrr_array[$key]['expiry_quant']);
						$objPHPExcel -> getActiveSheet() -> SetCellValue('J' . $i, $cdrr_array[$key]['expiry_date']);
						$objPHPExcel -> getActiveSheet() -> SetCellValue('K' . $i, $cdrr_array[$key]['out_of_stock']);
						$objPHPExcel -> getActiveSheet() -> SetCellValue('L' . $i, $cdrr_array[$key]['resupply']);
					}
				}
			}
		}

		if ($cdrr_array[0]['code'] == 0) {
			$objPHPExcel -> getActiveSheet() -> SetCellValue('E108', $cdrr_array[0]['reports_expected']);
			$objPHPExcel -> getActiveSheet() -> SetCellValue('H108', $cdrr_array[0]['reports_actual']);

			$objPHPExcel -> getActiveSheet() -> SetCellValue('C111', $cdrr_array[0]['Name']);
			$objPHPExcel -> getActiveSheet() -> SetCellValue('C113', $cdrr_array[0]['Phone_Number']);
			$objPHPExcel -> getActiveSheet() -> SetCellValue('K111', $cdrr_array[0]['level_name']);
			$objPHPExcel -> getActiveSheet() -> SetCellValue('G113', $cdrr_array[0]['created']);
		} else {
			$objPHPExcel -> getActiveSheet() -> SetCellValue('C107', $cdrr_array[0]['Name']);
			$objPHPExcel -> getActiveSheet() -> SetCellValue('C109', $cdrr_array[0]['Phone_Number']);
			$objPHPExcel -> getActiveSheet() -> SetCellValue('K107', $cdrr_array[0]['level_name']);
			$objPHPExcel -> getActiveSheet() -> SetCellValue('G109', $cdrr_array[0]['created']);
		}

		//Generate file
		ob_start();
		$original_filename = $cdrr_array[0]['cdrr_label'] . " " . $cdrr_array[0]['facility_name'] . " " . $cdrr_array[0]['period_begin'] . " to " . $cdrr_array[0]['period_end'] . ".xls";
		$filename = $dir . "/" . urldecode($original_filename);
		$objWriter = new PHPExcel_Writer_Excel5($objPHPExcel);
		$objWriter -> save($filename);
		$objPHPExcel -> disconnectWorksheets();
		unset($objPHPExcel);
		if (file_exists($filename)) {
			$filename = str_replace("#", "%23", $filename);
			redirect($filename);
		}

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
					if ($table == "cdrr" || $table == "maps") {
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
						$check = substr($table, 0, 4);
						//Check if table is in relation with maps or cdrr table
						if ($check == 'cdrr') {
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
						} else if ($check == 'maps') {
							$contents['maps_id'] = $this -> check_id($contents['maps_id'], 'maps');
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

	public function check_id($new_id, $type = 'cdrr') {
		$sql = "SELECT new_id FROM order_maps WHERE type='$type' AND old_id='$new_id' LIMIT 1";
		$query = $this -> db -> query($sql);
		$results = $query -> result_array();
		if ($results) {
			return $results[0]['new_id'];
		}
		return null;
	}

	public function showTable($columns, $data = array(), $links = array()) {
		$this -> load -> library('table');
		$tmpl = array('table_open' => '<table class="dataTables">');
		$this -> table -> set_template($tmpl);
		$this -> table -> set_heading($columns);
		$link_values = "";
		foreach ($data as $mydata) {
			if ($mydata['id']) {
				$mydata['cdrr_id'] = "<a href='" . site_url("order/view_cdrr/" . $mydata['id']) . "'>" . $mydata['cdrr_id'] . "</a>";
				$mydata['maps_id'] = "<a href='" . site_url("order/view_maps/" . $mydata['map']) . "'>" . $mydata['maps_id'] . "</a>";
			}
			//Set Up links
			foreach ($links as $i => $link) {
				if ($link == "delete") {
					$link_values .= "<a href='" . site_url($i . '/' . $mydata['id']) . "' class='delete'>$link</a> | ";
				} else {
					$link_values .= "<a href='" . site_url($i . '/' . $mydata['id']) . "'>$link</a> | ";
				}
			}
			$mydata['Options'] = rtrim($link_values, " | ");
			$link_values = "";
			unset($mydata['id']);
			unset($mydata['map']);
			$this -> table -> add_row($mydata);
		}
		return $this -> table -> generate();
	}

	public function base_params($data) {
		$data['title'] = "Order Reporting";
		$data['link'] = "order_management";
		$this -> load -> view('template', $data);
	}

}
