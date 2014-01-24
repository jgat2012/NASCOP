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
				LEFT JOIN sync_facility sf ON sf.id=c.facility_id
				LEFT JOIN facilities f ON f.facilitycode=sf.code
				LEFT JOIN maps m ON sf.id=m.facility_id
				WHERE c.code='0'
				AND m.code=c.code
				AND m.period_begin=c.period_begin
				AND m.period_end=c.period_end
				AND c.status !='prepared' 
				AND c.status !='review'";
		//AND c.id NOT IN (SELECT cdrr_id FROM escm_orders)
		$query = $this -> db -> query($sql);
		$results = $query -> result_array();
		$links = array("order/view_order" => "view order");
		return $this -> showTable($columns, $results, $links);
	}

	public function move_order($status, $cdrr_id, $maps_id) {
		$log = new Cdrr_Log();
		$log -> description = $status;
		$log -> created = date('Y-m-d H:i:s');
		$log -> user_id = $this -> session -> userdata("user_id");
		$log -> cdrr_id = $cdrr_id;
		$log -> save();

		$sql = "UPDATE cdrr SET status='$status' WHERE id='$cdrr_id'";
		$this -> db -> query($sql);

		$this -> session -> set_flashdata('order_message', "Order " . $status . " Successfully");
		redirect("order/view_order/" . $cdrr_id . "/" . $maps_id);
	}

	public function view_order($cdrr_id, $maps_id) {
		$order_array = array();
		$and = "";
		$sql = "SELECT c.*,ci.*,f.*,co.county as county_name,d.name as district_name,IF(c.code='0',CONCAT('D-CDRR#',c.id),CONCAT('F-CDRR#',c.id)) as cdrr_label,c.status as status_name,IF(c.code='1',CONCAT(f.name,CONCAT(' ','Dispensing Point')),f.name)as facility_name
				FROM cdrr c
				LEFT JOIN cdrr_item ci ON ci.cdrr_id=c.id
				LEFT JOIN sync_facility sf ON sf.id=c.facility_id
				LEFT JOIN facilities f ON f.facilitycode=sf.code
				LEFT JOIN counties co ON co.id=f.county
				LEFT JOIN district d ON d.id=f.district
				WHERE c.id='$cdrr_id'
				AND ci.resupply !='0'";
		$query = $this -> db -> query($sql);
		$order_array = $query -> result_array();
		$data['order_array'] = $order_array;

		if ($order_array[0]['status_name'] == "received" || $order_array[0]['status_name'] == "rationalized") {
			$data['option_links'] = "<li class='active'><a href='" . site_url("order/view_cdrr/" . $cdrr_id) . "'>view</a></li><li><a href='" . site_url("order/update_cdrr/" . $cdrr_id) . "'>update</a></li><li></li>";
		} else {
			$data['option_links'] = "<li class='active'><a href='" . site_url("order/view_cdrr/" . $cdrr_id) . "'>view</a></li>";
		}

		//maps
		$sql = "SELECT mi.regimen_id,mi.total
			 	FROM maps m
			 	LEFT JOIN maps_item mi ON mi.maps_id=m.id
				WHERE m.id='$maps_id'";
		$query = $this -> db -> query($sql);
		$fmaps_array = $query -> result_array();
		$data['fmaps_array'] = $fmaps_array;
		$data['page_title'] = "Central Aggregate(D-CDRR)";
		$data['content_view'] = "orders/order_template";
		$data['banner_text'] = "Central Aggregate(D-CDRR)";
		$data['hide_side_menu'] = 1;
		$data['cdrr_id'] = $cdrr_id;
		$data['regimens'] = Regimen::getAllObjects();
		$data['regimen_categories'] = Regimen_Category::getAll();
		$sql = "SELECT sd.id,CONCAT_WS('] ',CONCAT_WS(' [',name,abbreviation),CONCAT_WS(' ',strength,formulation)) as Drug,unit as Unit_Name,packsize as Pack_Size,category_id as Category
			        FROM cdrr_item ci
			        LEFT JOIN sync_drug sd ON sd.id=ci.drug_id
			        WHERE ci.cdrr_id='$cdrr_id'
			        AND ci.resupply !='0'
			        AND(sd.category_id='1' OR sd.category_id='2' OR sd.category_id='3')
			        $and";
		$query = $this -> db -> query($sql);
		$data['commodities'] = $query -> result();
		$data['logs'] = Cdrr_Log::getObjectLogs($cdrr_id);
		$data['maps_id'] = $maps_id;
		$this -> base_params($data);
	}

	public function view_cdrr($cdrr_id) {
		$cdrr_array = array();
		$sql = "SELECT c.*,ci.*,f.*,co.county as county_name,d.name as district_name,IF(c.code='0',CONCAT('D-CDRR#',c.id),CONCAT('F-CDRR#',c.id)) as cdrr_label,c.status as status_name,IF(c.code='1',CONCAT(f.name,CONCAT(' ','Dispensing Point')),f.name)as facility_name
				FROM cdrr c
				LEFT JOIN cdrr_item ci ON ci.cdrr_id=c.id
				LEFT JOIN sync_facility sf ON sf.id=c.facility_id
				LEFT JOIN facilities f ON f.facilitycode=sf.code
				LEFT JOIN counties co ON co.id=f.county
				LEFT JOIN district d ON d.id=f.district
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
			$facility_id = $cdrr_array['cdrr_array'][0]['facility_id'];
			$data['facility_id'] = $facility_id;
			$facilities = Sync_Facility::getCode($facility_id, $type);
			$facility = $facilities['code'];
			$code = $cdrr_array['cdrr_array'][0]['code'];
			$data['options'] = $cdrr_array['options'];
			if ($data['options'] == "view") {
				$data['hide_save'] = 1;
			}
			$data['hide_btn'] = 1;
			$cdrr_id = $cdrr_array['cdrr_array'][0]['cdrr_id'];
			$data['cdrr_id'] = $cdrr_id;
			$data['logs'] = Cdrr_Log::getObjectLogs($cdrr_id);
			if ($data['options'] == "view") {
				if ($data['status_name'] == "received" || $data['status_name'] == "rationalized") {
					$data['option_links'] = "<li class='active'><a href='" . site_url("order/view_cdrr/" . $cdrr_id) . "'>view</a></li><li><a href='" . site_url("order/update_cdrr/" . $cdrr_id) . "'>update</a></li><li><a class='delete' href='" . site_url("order/delete_cdrr/" . $cdrr_id) . "'>delete</a></li>";
				} else {
					$data['option_links'] = "<li class='active'><a href='" . site_url("order/view_cdrr/" . $cdrr_id) . "'>view</a></li>";
				}
			} else if ($data['options'] == "update") {
				if ($data['status_name'] == "received" || $data['status_name'] == "rationalized") {
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
				$data['commodities'] = Sync_Drug::getActiveList();
			} else {
				$sql = "SELECT sd.id,CONCAT_WS('] ',CONCAT_WS(' [',name,abbreviation),CONCAT_WS(' ',strength,formulation)) as Drug,unit as Unit_Name,packsize as Pack_Size,category_id as Category
			        FROM cdrr_item ci
			        LEFT JOIN sync_drug sd ON sd.id=ci.drug_id
			        WHERE ci.cdrr_id='$cdrr_id'
			        AND(sd.category_id='1' OR sd.category_id='2' OR sd.category_id='3')
			        $and";
				$query = $this -> db -> query($sql);
				$data['commodities'] = $query -> result();
			}
		} else {
			$period_start = date('Y-m-01');
			$period_end = date('Y-m-t');
			$duplicate = $this -> checkDuplicate($type, $period_start, $period_end, $facility);
			$supplier = Facilities::getSupplier($facility);
			$data['commodities'] = Sync_Drug::getActiveList();
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
		$sql = "SELECT c.*,ci.*,f.*,co.county as county_name,d.name as district_name,IF(c.code='0',CONCAT('D-CDRR#',c.id),CONCAT('F-CDRR#',c.id)) as cdrr_label,c.status as status_name,IF(c.code='1',CONCAT(f.name,CONCAT(' ','Dispensing Point')),f.name)as facility_name
				FROM cdrr c
				LEFT JOIN cdrr_item ci ON ci.cdrr_id=c.id
				LEFT JOIN sync_facility sf ON sf.id=c.facility_id
				LEFT JOIN facilities f ON f.facilitycode=sf.code
				LEFT JOIN counties co ON co.id=f.county
				LEFT JOIN district d ON d.id=f.district
				WHERE c.id='$cdrr_id'";
		$query = $this -> db -> query($sql);
		$cdrr_array = $query -> result_array();
		$data['cdrr_array'] = $cdrr_array;
		$data['options'] = "update";
		$this -> set_cdrr($cdrr_array[0]['code'], $data);
	}

	public function update($type = "cdrr", $cdrr_id) {
		//If reporting for cdrr
		if ($type == 'cdrr') {
			$save = $this -> input -> post("save");
			if ($save) {
				$facility_id = $this -> input -> post("facility_id");
				$non_arv = $this -> input -> post("non_arv");
				$delivery_note = $this -> input -> post("delivery_note");
				$updated = date('Y-m-d H:i:s');
				$code = $this -> input -> post("report_type");
				$period_begin = $this -> input -> post("period_start");
				$period_end = $this -> input -> post("period_end");
				$comments = $this -> input -> post("comments");
				$services = $this -> input -> post("type_of_service");
				$sponsors = $this -> input -> post("sponsor");
				$commodities = $this -> input -> post('commodity');
				$pack_size = $this -> input -> post('pack_size');
				$opening_balances = $this -> input -> post('opening_balance');
				$quantities_received = $this -> input -> post('quantity_received');
				$quantities_dispensed = $this -> input -> post('quantity_dispensed');
				$losses = $this -> input -> post('losses');
				$adjustments = $this -> input -> post('adjustments');
				$physical_count = $this -> input -> post('physical_count');
				$expiry_quantity = $this -> input -> post('expire_qty');
				$expiry_date = $this -> input -> post('expire_period');
				$out_of_stock = $this -> input -> post('out_of_stock');
				$resupply = $this -> input -> post('resupply');
				$old_resupply = $this -> input -> post('old_resupply');
				$comments = $this -> input -> post('comments');
				if ($code == 0) {
					$aggr_consumed = $this -> input -> post('aggregated_qty');
					$aggr_on_hand = $this -> input -> post('aggregated_physical_qty');
				}

				//insert cdrr
				$main_array = array();
				$main_array['updated'] = date('Y-m-d H:i:s');
				$main_array['code'] = $code;
				$main_array['period_begin'] = $period_begin;
				$main_array['period_end'] = $period_end;
				$main_array['comments'] = $comments;
				if ($code == 0) {//Aggregated
					$reports_expected = $this -> input -> post('central_rate');
					$reports_actual = $this -> input -> post('actual_report');
					$main_array['reports_expected'] = $reports_expected;
					$main_array['reports_actual'] = $reports_actual;
				}
				$main_array['services'] = $services;
				$main_array['sponsors'] = $sponsors;
				$main_array['facility_id'] = $facility_id;
				$main_array['delivery_note'] = $delivery_note;
				$main_array['non_arv'] = $non_arv;

				$this -> db -> where('id', $cdrr_id);
				$this -> db -> update('cdrr', $main_array);

				//insert cdrr_log
				$log_array = array();
				$log_array['description'] = "updated";
				$log_array['created'] = date('Y-m-d H:i:s');
				$log_array['user_id'] = $this -> session -> userdata("user_id");
				$log_array['cdrr_id'] = $cdrr_id;
				$this -> db -> insert('cdrr_log', $log_array);

				//insert cdrr_items
				$sql = "DELETE FROM cdrr_item where cdrr_id='$cdrr_id'";
				$query = $this -> db -> query($sql);
				$commodity_counter = 0;
				$cdrr_array = array();
				foreach ($commodities as $commodity) {
					if ($resupply[$commodity_counter] != null || $resupply[$commodity_counter] != "") {
						$cdrr_array[$commodity_counter]['balance'] = $opening_balances[$commodity_counter];
						$cdrr_array[$commodity_counter]['received'] = $quantities_received[$commodity_counter];
						$cdrr_array[$commodity_counter]['dispensed_units'] = $quantities_dispensed[$commodity_counter];
						$cdrr_array[$commodity_counter]['dispensed_packs'] = ceil(@$quantities_dispensed[$commodity_counter] / @$pack_size[$commodity_counter]);
						$cdrr_array[$commodity_counter]['losses'] = $losses[$commodity_counter];
						$cdrr_array[$commodity_counter]['adjustments'] = $adjustments[$commodity_counter];
						$cdrr_array[$commodity_counter]['count'] = $physical_count[$commodity_counter];
						$cdrr_array[$commodity_counter]['expiry_quant'] = $expiry_quantity[$commodity_counter];
						if ($expiry_date[$commodity_counter] != "-" || $expiry_date[$commodity_counter] == "") {
							$cdrr_array[$commodity_counter]['expiry_date'] = date('Y-m-d', strtotime($expiry_date[$commodity_counter]));
						} else {
							$cdrr_array[$commodity_counter]['expiry_date'] = "";
						}
						$cdrr_array[$commodity_counter]['out_of_stock'] = $out_of_stock[$commodity_counter];
						$cdrr_array[$commodity_counter]['resupply'] = $resupply[$commodity_counter];
						if ($code == 0) {
							$cdrr_array[$commodity_counter]['aggr_consumed'] = $aggr_consumed[$commodity_counter];
							$cdrr_array[$commodity_counter]['aggr_on_hand'] = $aggr_on_hand[$commodity_counter];
						}
						$cdrr_array[$commodity_counter]['cdrr_id'] = $cdrr_id;
						$cdrr_array[$commodity_counter]['drug_id'] = $commodity;

						if ($resupply[$commodity_counter] != $old_resupply[$commodity_counter]) {
							$change = new Resupply_Change();
							$change -> cdrr_id = $cdrr_id;
							$change -> drug_id = $commodity;
							$change -> resupply = $old_resupply[$commodity_counter];
							$change -> save();
						}
					}
					$commodity_counter++;
				}
				$this -> db -> insert_batch('cdrr_item', $cdrr_array);
				$this -> session -> set_flashdata('order_message', "Cdrr report successfully updated !");
			}
		}
		redirect("order");

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

	public function download_cdrr($cdrr_id) {
		$this -> load -> library('PHPExcel');
		$cdrr_array = array();
		$dir = "Export";
		$drug_name = "CONCAT_WS('] ',CONCAT_WS(' [',sd.name,sd.abbreviation),CONCAT_WS(' ',sd.strength,sd.formulation)) as drug_map";

		$sql = "SELECT c.*,ci.*,cl.*,f.*,co.county as county_name,d.name as district_name,u.*,al.level_name,IF(c.code='0',CONCAT('D-CDRR#',c.id),CONCAT('F-CDRR#',c.id)) as cdrr_label,c.status as status_name,IF(c.code='1',CONCAT(f.name,CONCAT(' ','Dispensing Point')),f.name)as facility_name,$drug_name
				FROM cdrr c
				LEFT JOIN cdrr_item ci ON ci.cdrr_id=c.id
				LEFT JOIN cdrr_log cl ON cl.cdrr_id=c.id
				LEFT JOIN sync_facility sf ON sf.id=c.facility_id
				LEFT JOIN facilities f ON f.facilitycode=sf.code
				LEFT JOIN counties co ON co.id=f.county
				LEFT JOIN district d ON d.id=f.district
				LEFT JOIN sync_user su ON su.id=cl.user_id
				LEFT JOIN users u ON su.id=u.map
				LEFT JOIN access_level al ON al.id=u.Access_Level
				LEFT JOIN sync_drug sd ON sd.id=ci.drug_id
				WHERE c.id='$cdrr_id'";
		$query = $this -> db -> query($sql);
		$cdrr_array = $query -> result_array();
		$report_type = $cdrr_array[0]['code'];
		$template = "cdrr_aggregate";

		$inputFileType = 'Excel5';
		$inputFileName = $_SERVER['DOCUMENT_ROOT'] . '/NASCOP/assets/' . $template . '.xls';
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

		if (strtoupper($cdrr_array[0]['sponsors']) == "GOK" || strtoupper($cdrr_array[0]['sponsors']) == "KEMSA") {
			$loc = "D";
		} else if (strtoupper($cdrr_array[0]['sponsors']) == "PEPFAR" || strtoupper($cdrr_array[0]['sponsors']) == "KENYA PHARMA") {
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
			$pack_size = $arr[$i]['B'];
			if ($drug) {
				$key = $this -> getMappedDrug($drug, $pack_size);
				if ($key !== null) {
					foreach ($cdrr_array as $cdrr_item) {
						if ($key == $cdrr_item['drug_id']) {
							$objPHPExcel -> getActiveSheet() -> SetCellValue('C' . $i, $cdrr_item['balance']);
							$objPHPExcel -> getActiveSheet() -> SetCellValue('D' . $i, $cdrr_item['received']);
							$objPHPExcel -> getActiveSheet() -> SetCellValue('E' . $i, $cdrr_item['dispensed_units']);
							$objPHPExcel -> getActiveSheet() -> SetCellValue('F' . $i, $cdrr_item['losses']);
							$objPHPExcel -> getActiveSheet() -> SetCellValue('G' . $i, $cdrr_item['adjustments']);
							$objPHPExcel -> getActiveSheet() -> SetCellValue('H' . $i, $cdrr_item['count']);
							if ($cdrr_array[0]['code'] == 0) {
								$objPHPExcel -> getActiveSheet() -> SetCellValue('I' . $i, $cdrr_item['aggr_consumed']);
								$objPHPExcel -> getActiveSheet() -> SetCellValue('J' . $i, $cdrr_item['aggr_on_hand']);
								$objPHPExcel -> getActiveSheet() -> SetCellValue('K' . $i, $cdrr_item['expiry_quant']);
								$objPHPExcel -> getActiveSheet() -> SetCellValue('L' . $i, $cdrr_item['expiry_date']);
								$objPHPExcel -> getActiveSheet() -> SetCellValue('M' . $i, $cdrr_item['out_of_stock']);
								$objPHPExcel -> getActiveSheet() -> SetCellValue('N' . $i, $cdrr_item['resupply']);
							} else {
								$objPHPExcel -> getActiveSheet() -> SetCellValue('I' . $i, $cdrr_item['expiry_quant']);
								$objPHPExcel -> getActiveSheet() -> SetCellValue('J' . $i, $cdrr_item['expiry_date']);
								$objPHPExcel -> getActiveSheet() -> SetCellValue('K' . $i, $cdrr_item['out_of_stock']);
								$objPHPExcel -> getActiveSheet() -> SetCellValue('L' . $i, $cdrr_item['resupply']);
							}
						}
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
				//$mydata['maps_id'] = "<a href='" . site_url("order/view_maps/" . $mydata['map']) . "'>" . $mydata['maps_id'] . "</a>";
			}
			//Set Up links
			foreach ($links as $i => $link) {
				if ($link == "delete") {
					$link_values .= "<a href='" . site_url($i . '/' . $mydata['id']) . "' class='delete'>$link</a> | ";
				} else if ($link == "view order") {
					$link_values .= "<a href='" . site_url($i . '/' . $mydata['id'] . '/' . $mydata['map']) . "'>$link</a> | ";
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

	public function searchForId($id, $array) {

		foreach ($array as $key => $val) {
			$val = array_map('strtolower', $val);
			if (in_array($id, $val)) {
				return $key;
			}
		}
		return null;
	}
	public function getMappedDrug($drug_name = "", $packsize = "") {
		if ($drug_name != "") {
			$drugs = explode(" ", trim($drug_name));
			$drug_list = array();
			foreach ($drugs as $drug) {
				if ($drug != null) {
					$sql = "SELECT sd.id 
		      FROM sync_drug sd
		      WHERE (sd.name like '%$drug%'
		      OR sd.abbreviation like '%$drug%'
		      OR sd.strength = '$drug'
		      OR sd.formulation = '$drug')
		      AND sd.packsize='$packsize'";
					$query = $this -> db -> query($sql);
					$results = $query -> result_array();
					if ($results) {
						foreach ($results as $result) {
							$drug_list[] = $result['id'];
						}
					}
				}
			}
			return $key = array_search(max(array_count_values($drug_list)), array_count_values($drug_list));
		}
		return null;
	}

	public function base_params($data) {
		$data['title'] = "Order Reporting";
		$data['link'] = "order_management";
		$this -> load -> view('template', $data);
	}

}
