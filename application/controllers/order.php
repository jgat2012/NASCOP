<?php
error_reporting(1);
class Order extends MY_Controller {
	function __construct() {
		parent::__construct();
	}
	
	public $objPHPExcel;
	protected $cellValues;
	
	public function index() {
		$data['content_view'] = "orders/order_v";
		$data['page_title'] = "my Orders";
		$data['banner_text'] = "Facility Orders";
		$data['order_table'] = $this -> get_orders();
		$this -> base_params($data);
	}

	public function get_orders() {
		$columns = array('#', '#CDRR-ID', '#MAPS-ID', 'Period Beginning', 'Status', 'Facility Name', 'Options');
		$sql = "SELECT c.id,m.id as map,IF(c.code='D-CDRR',CONCAT('D-CDRR#',c.id),CONCAT('F-CDRR#',c.id)) as cdrr_id,IF(m.code='D-MAPS',CONCAT('D-MAPS#',m.id),CONCAT('F-MAPS#',m.id)) as maps_id,c.period_begin,c.status as status_name,sf.name as facility_name
					FROM cdrr c
					LEFT JOIN sync_facility sf ON sf.id=c.facility_id
				    LEFT JOIN facilities f ON f.facilitycode=sf.code,maps m
					WHERE c.facility_id = m.facility_id
					AND c.period_begin = m.period_begin
					AND c.period_end = m.period_end
					AND (c.code =  'D-CDRR' OR c.code =  'F-CDRR_packs')
					AND c.status !=  'prepared'
					AND c.status !=  'review'
					AND c.id NOT IN (SELECT cdrr_id FROM escm_orders GROUP BY cdrr_id)
					GROUP BY c.id";
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

		$mylog = new Maps_Log();
		$mylog -> description = $status;
		$mylog -> created = date('Y-m-d H:i:s');
		$mylog -> user_id = $this -> session -> userdata("user_id");
		$mylog -> maps_id = $maps_id;
		$mylog -> save();

		$sql = "UPDATE cdrr SET status='$status' WHERE id='$cdrr_id'";
		$this -> db -> query($sql);
		$sql = "UPDATE maps SET status='$status' WHERE id='$maps_id'";
		$this -> db -> query($sql);

		$this -> session -> set_flashdata('order_message', "Order " . $status . " Successfully");
		redirect("order/view_order/" . $cdrr_id . "/" . $maps_id);
		
	}

	public function view_order($cdrr_id, $maps_id) {
		$order_array = array();
		$and = "";
		$sql = "SELECT c.*,ci.*,f.*,co.county as county_name,d.name as district_name,IF(c.code='D-CDRR',CONCAT('D-CDRR#',c.id),CONCAT('F-CDRR#',c.id)) as cdrr_label,c.status as status_name,sf.name as facility_name,rc.resupply as old_resupply
				FROM cdrr c
				LEFT JOIN cdrr_item ci ON ci.cdrr_id=c.id
				LEFT JOIN sync_facility sf ON sf.id=c.facility_id
				LEFT JOIN facilities f ON f.facilitycode=sf.code
				LEFT JOIN counties co ON co.id=f.county
				LEFT JOIN district d ON d.id=f.district
				LEFT JOIN resupply_change rc ON rc.cdrr_id=ci.cdrr_id AND ci.drug_id=rc.drug_id
				WHERE c.id='$cdrr_id'";
		$query = $this -> db -> query($sql);
		$order_array = $query -> result_array();
		$data['order_array'] = $order_array;
		$data['order_code'] = $order_array[0]['code'];

		$data['amc'] = $this -> getAMC($order_array[0]['facility_id'], $order_array[0]['code'], $order_array[0]['period_begin']);

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
		$sql = "SELECT c.*,ci.*,f.*,co.county as county_name,d.name as district_name,IF(c.code='D-CDRR',CONCAT('D-CDRR#',c.id),CONCAT('F-CDRR#',c.id)) as cdrr_label,c.status as status_name,sf.name as facility_name
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
		if ($cdrr_array[0]['code'] == "D-CDRR") {
			$code = 0;
		} else if ($cdrr_array[0]['code'] == "F-CDRR_units") {
			$facility_code = $this -> session -> userdata("facility");
			if ($cdrr_array[0]['facility_code'] == $facility_code) {
				$code = 1;
			} else {
				$code = 2;
			}
		} else if ($cdrr_array[0]['code'] == "F-CDRR_packs") {
			$code = 3;
		}
		$this -> set_cdrr($code, $data);
	}

	public function set_cdrr($type, $cdrr_array = array()) {
		$this -> session -> set_userdata("order_go_back", "cdrr");
		$data['hide_generate'] = 0;
		$data['hide_side_menu'] = 0;
		$data['hide_save'] = 0;
		$data['hide_btn'] = 0;
		$data['options'] = "none";
		$data['stand_alone'] = 0;
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
		} else if ($type == 3) {
			$data['page_title'] = "Stand-alone(F-CDRR)";
			$data['banner_text'] = "Stand-alone(F-CDRR)";
			$facility = $this -> session -> userdata("facility");
			$data['stand_alone'] = 1;
			$data['content_view'] = "orders/cdrr_template";
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
		$data['report_type'] = $this -> getActualCode($type, "cdrr");
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
		if ($cdrr_array[0]['code'] == "D-CDRR") {
			$code = 0;
		} else if ($cdrr_array[0]['code'] == "F-CDRR_units") {
			$facility_code = $this -> session -> userdata("facility");
			if ($cdrr_array[0]['facility_code'] == $facility_code) {
				$code = 1;
			} else {
				$code = 2;
			}
		} else if ($cdrr_array[0]['code'] == "F-CDRR_packs") {
			$code = 3;
		}

		if ($cdrr_array[0]['status'] == "dispatched") {
			$this -> session -> set_flashdata('order_message', "You cannot update order after it has been dispatched!");
			redirect("order");
		}

		$this -> set_cdrr($code, $data);
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
				if ($code == "F-CDRR_packs") {
					$quantities_dispensed_packs = $this -> input -> post('quantity_dispensed_packs');
				}
				$losses = $this -> input -> post('losses');
				$adjustments = $this -> input -> post('adjustments');
				$physical_count = $this -> input -> post('physical_count');
				$expiry_quantity = $this -> input -> post('expire_qty');
				$expiry_date = $this -> input -> post('expire_period');
				$out_of_stock = $this -> input -> post('out_of_stock');
				$resupply = $this -> input -> post('resupply');
				$old_resupply = $this -> input -> post('old_resupply');
				$comments = $this -> input -> post('comments');
				if ($code == "D-CDRR") {
					$aggr_consumed = $this -> input -> post('aggregated_qty');
					$aggr_on_hand = $this -> input -> post('aggregated_physical_qty');
				}

				//insert cdrr
				$main_array = array();
				$main_array['updated'] = date('Y-m-d H:i:s');
				$main_array['period_begin'] = $period_begin;
				$main_array['period_end'] = $period_end;
				$main_array['comments'] = $comments;
				if ($code == "D-CDRR") {//Aggregated
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
						if ($code == "F-CDRR_packs") {
							$cdrr_array[$commodity_counter]['dispensed_packs'] = $quantities_dispensed_packs[$commodity_counter];
						} else {
							$cdrr_array[$commodity_counter]['dispensed_packs'] = ceil(@$quantities_dispensed[$commodity_counter] / @$pack_size[$commodity_counter]);
						}
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
						if ($code == "D-CDRR") {
							$cdrr_array[$commodity_counter]['aggr_consumed'] = $aggr_consumed[$commodity_counter];
							$cdrr_array[$commodity_counter]['aggr_on_hand'] = $aggr_on_hand[$commodity_counter];
						}
						$cdrr_array[$commodity_counter]['cdrr_id'] = $cdrr_id;
						$cdrr_array[$commodity_counter]['drug_id'] = $commodity;

						if (((int)$resupply[$commodity_counter] - (int)$old_resupply[$commodity_counter]) != 0) {
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

	public function import_order($type = "") {
		$ret = array();
		if ($type == "") {
			$code = $this -> input -> post("upload_type");
			$status = "approved";

			if ($code == "D-CDRR") {
				$status_code = 0;
				$resupply = "N";
			} else if ($code == "F-CDRR_packs") {
				$status_code = 3;
				$resupply = "M";
			} else if ($code == "D-MAPS") {
				$status_code = 0;
			} else if ($code == "F-MAPS") {
				$status_code = 3;
			}

			$this -> load -> library('PHPExcel');
			$objReader = new PHPExcel_Reader_Excel5();

			if (isset($_FILES["file"])) {
				$fileCount = count($_FILES["file"]["tmp_name"]);
				for ($q = 0; $q < $fileCount; $q++) {
					$file_name = $_FILES["file"]["tmp_name"][$q];
					$objPHPExcel = $objReader -> load($_FILES["file"]["tmp_name"][$q]);
					$arr = $objPHPExcel -> getActiveSheet() -> toArray(null, true, true, true);
					$highestColumm = $objPHPExcel -> setActiveSheetIndex(0) -> getHighestColumn();
					$highestRow = $objPHPExcel -> setActiveSheetIndex(0) -> getHighestRow();
					$text = $arr[2]['A'];

					if ($code == "D-CDRR" || $code == "F-CDRR_packs") {
						$first_row = 4;
						$facility_name = trim($arr[$first_row]['C'] . $arr[$first_row]['D'] . $arr[$first_row]['E']);
						$facility_code = trim($arr[$first_row]['G'] . $arr[$first_row]['H'] . $arr[$first_row]['I']);

						$second_row = 5;
						$province = trim($arr[$second_row]['C'] . $arr[$second_row]['D'] . $arr[$second_row]['E']);
						$district = trim($arr[$second_row]['G'] . $arr[$second_row]['H'] . $arr[$second_row]['I']);

						$third_row = 7;
						$period_begin = $this -> clean_date(trim($arr[$third_row]['D'] . $arr[$third_row]['E']));
						$period_end = $this -> clean_date(trim($arr[$third_row]['G'] . $arr[$third_row]['H']));

						$file_type = $this -> checkFileType($code, $text);
						$facilities = Sync_Facility::getId($facility_code, $status_code);
						$facility_id = $facilities['id'];
						$duplicate = $this -> check_duplicate($code, $period_begin, $period_end, $facility_id);
						if ($facilities == "") {
							$ret[] = "Your facility Code in '" . $_FILES["file"]["name"][$q]. "' file does not match any facility.  Kindly cross check the MFL code and / or check if the facility uploading is an ordering point.";
						} else if ($period_begin != date('Y-m-01', strtotime(date('F-Y') . "-1 month")) || $period_end != date('Y-m-t', strtotime(date('F-Y') . "-1 month"))) {
							$ret[] = "You can only report for ".date('F-Y', strtotime(date('F-Y') . "-1 month")).". Kindly check the period fields !-" . $_FILES["file"]["name"][$q].' The format should be dd/mm/yyyy';
						} else if ($file_type == false) {
							$ret[] = "Incorrect File Selected-" . $_FILES["file"]["name"][$q];
						} else if ($duplicate == true) {
							$ret[] = "A cdrr report already exists for this month !-" . $_FILES["file"]["name"][$q];
						} else {
							$fourth_row = 9;
							$sponsor_gok = trim($arr[$fourth_row]['D']);
							$sponsor_pepfar = trim($arr[$fourth_row]['F']);
							$sponsor_msf = trim($arr[$fourth_row]['H']);
							if ($sponsor_gok) {
								$sponsors = "GOK";
							}
							if ($sponsor_pepfar) {
								$sponsors = "PEPFAR";
							}
							if ($sponsor_msf) {
								$sponsors = "MSF";
							}

							$fifth_row = 11;
							$service = array();
							$service_art = trim($arr[$fifth_row]['D']);
							$service_pmtct = trim($arr[$fifth_row]['F']);
							$service_pep = trim($arr[$fifth_row]['H']);
							if ($service_art) {
								$service[] = "ART";
							}
							if ($service_pmtct) {
								$service[] = "PMTCT";
							}
							if ($service_pep) {
								$service[] = "PEP";
							}

							$services = implode(",", $service);

							$seventh_row = 95;

							$comments = trim($arr[$seventh_row]['A']);
							$comments .= trim($arr[$seventh_row]['B']);
							$comments .= trim($arr[$seventh_row]['C']);
							$comments .= trim($arr[$seventh_row]['D']);
							$comments .= trim($arr[$seventh_row]['E']);
							$comments .= trim($arr[$seventh_row]['F']);
							$comments .= trim($arr[$seventh_row]['G']);
							$comments .= trim($arr[$seventh_row]['H']);
							$comments .= trim($arr[$seventh_row]['I']);
							$comments .= trim($arr[$seventh_row]['J']);
							$comments .= trim($arr[$seventh_row]['K']);
							$comments .= trim($arr[$seventh_row]['L']);

							$main_array = array();
							$main_array['id'] = "";
							$main_array['status'] = $status;
							$main_array['created'] = date('Y-m-d H:i:s');
							$main_array['updated'] = "";
							$main_array['code'] = $code;
							$main_array['period_begin'] = $period_begin;
							$main_array['period_end'] = $period_end;
							$main_array['comments'] = $comments;
							$main_array['reports_expected'] = null;
							$main_array['reports_actual'] = null;
							$main_array['services'] = $services;
							$main_array['sponsors'] = $sponsors;
							$main_array['non_arv'] = 0;
							$main_array['delivery_note'] = null;
							$main_array['order_id'] = 0;
							$main_array['facility_id'] = $facility_id;

							$sixth_row = 18;
							$cdrr_array = array();
							$commodity_counter = 0;

							for ($i = $sixth_row; $sixth_row, $i <= 89; $i++) {
								if ($i != 34 || $i != 57) {
									if (trim($arr[$i][$resupply]) != 0) {
										$drug_name = trim($arr[$i]['A']);
										$pack_size = trim($arr[$i]['B']);
										$commodity = $this -> getMappedDrug($drug_name, $pack_size);
										if ($commodity != null) {
											$cdrr_array[$commodity_counter]['id'] = "";
											if ($code == "D-CDRR") {
												$cdrr_array[$commodity_counter]['balance'] = str_replace(',', '', trim($arr[$i]['C']));
												$cdrr_array[$commodity_counter]['received'] = str_replace(',', '', trim($arr[$i]['D']));
												$cdrr_array[$commodity_counter]['dispensed_units'] = ceil(@str_replace(',', '', trim($arr[$i]['E'])) * @$pack_size);
												$cdrr_array[$commodity_counter]['dispensed_packs'] = str_replace(',', '', trim($arr[$i]['E']));
												$cdrr_array[$commodity_counter]['losses'] = str_replace(',', '', trim($arr[$i]['F']));
												$cdrr_array[$commodity_counter]['adjustments'] =  str_replace(',', '', trim($arr[$i]['G']));
												$cdrr_array[$commodity_counter]['count'] =  str_replace(',', '', trim($arr[$i]['H']));
												$cdrr_array[$commodity_counter]['expiry_quant'] =  str_replace(',', '', trim($arr[$i]['K']));
												$expiry_date = trim($arr[$i]['L']);
												if ($expiry_date != "-" || $expiry_date != "" || $expiry_date != null) {
													$cdrr_array[$commodity_counter]['expiry_date'] = $this -> clean_date($expiry_date);
												} else {
													$cdrr_array[$commodity_counter]['expiry_date'] = "";
												}
												$cdrr_array[$commodity_counter]['out_of_stock'] = str_replace(',', '', trim($arr[$i]['M']));
												$cdrr_array[$commodity_counter]['resupply'] = str_replace(",", "", $arr[$i]['N']);
												$cdrr_array[$commodity_counter]['aggr_consumed'] = str_replace(",", "", $arr[$i]['I']);
												$cdrr_array[$commodity_counter]['aggr_on_hand'] = str_replace(",", "", $arr[$i]['J']);
											} else if ($code == "F-CDRR_packs") {
												$cdrr_array[$commodity_counter]['balance'] = str_replace(",", "", $arr[$i]['C']); 
												$cdrr_array[$commodity_counter]['received'] = str_replace(",", "", $arr[$i]['D']);
												$cdrr_array[$commodity_counter]['dispensed_units'] = str_replace(",", "", $arr[$i]['E']);
												$cdrr_array[$commodity_counter]['dispensed_packs'] = str_replace(",", "", $arr[$i]['F']);
												$cdrr_array[$commodity_counter]['losses'] = str_replace(",", "", $arr[$i]['G']);
												$cdrr_array[$commodity_counter]['adjustments'] = str_replace(",", "", $arr[$i]['H']);
												$cdrr_array[$commodity_counter]['count'] = str_replace(",", "", $arr[$i]['I']);
												$cdrr_array[$commodity_counter]['expiry_quant'] = str_replace(",", "", $arr[$i]['J']);
												$expiry_date = trim($arr[$i]['K']);
												if ($expiry_date != "-" || $expiry_date != "" || $expiry_date != null) {
													$cdrr_array[$commodity_counter]['expiry_date'] = $this -> clean_date($expiry_date);
												} else {
													$cdrr_array[$commodity_counter]['expiry_date'] = "";
												}
												$cdrr_array[$commodity_counter]['out_of_stock'] =str_replace(",", "", $arr[$i]['L']);
												$cdrr_array[$commodity_counter]['resupply'] = str_replace(",", "", $arr[$i]['M']);
												$cdrr_array[$commodity_counter]['aggr_consumed'] = null;
												$cdrr_array[$commodity_counter]['aggr_on_hand'] = null;
											}
											$cdrr_array[$commodity_counter]['publish'] = 0;
											$cdrr_array[$commodity_counter]['cdrr_id'] = "";
											$cdrr_array[$commodity_counter]['drug_id'] = $commodity;
											$commodity_counter++;
										}
									}
								}
							}
							$main_array['ownCdrr_item'] = $cdrr_array;

							$log_array[0]['id'] = "";
							$log_array[0]['description'] = "prepared";
							if ($code == "D-CDRR") {
								$log_array[0]['created'] = $this -> clean_date(trim($arr[113]['G']));
								$log_array[0]['user_id'] = $this -> getUser(trim($arr[111]['C']));
							} else {
								$log_array[0]['created'] = $this -> clean_date(trim($arr[109]['G']));
								$log_array[0]['user_id'] = $this -> getUser(trim($arr[107]['C']));
							}
							$log_array[0]['cdrr_id'] = "";

							$log_array[1]['id'] = "";
							$log_array[1]['description'] = "approved";
							if ($code == "D-CDRR") {
								$log_array[1]['created'] = $this -> clean_date(trim($arr[117]['G']));
								$log_array[1]['user_id'] = $this -> getUser(trim($arr[115]['C']));
							} else {
								$log_array[1]['created'] = $this -> clean_date(trim($arr[113]['G']));
								$log_array[1]['user_id'] = $this -> getUser(trim($arr[111]['C']));
							}
							$log_array[1]['cdrr_id'] = "";

							$main_array['ownCdrr_log'] = $log_array;
							$type = "cdrr";

							$main_array = array($main_array);
							$this -> prepare_order($type, $main_array);
							$content = "Order was successfully saved. ";

							$dir = "Export";

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

							//move the file
							$file_location = $dir . "/" . $_FILES['file']['name'][$q];
							move_uploaded_file($_FILES['file']['tmp_name'][$q], $file_location);
							//send excel file to email
							$content .= $this -> send_file($file_location);
							$ret[] = $content . $_FILES["file"]["name"][$q];
						}

					} else if ($code == "D-MAPS" || $code == "F-MAPS") {
						
						$first_row = 4;
						$facility_name = trim($arr[$first_row]['B'] . $arr[$first_row]['C'] . $arr[$first_row]['D']);
						$facility_code = trim($arr[$first_row]['F'] . $arr[$first_row]['G'] . $arr[$first_row]['H']);
						$second_row = 5;
						$province = trim($arr[$first_row]['B'] . $arr[$first_row]['C'] . $arr[$first_row]['D']);
						$district = trim($arr[$first_row]['F'] . $arr[$first_row]['G'] . $arr[$first_row]['H']);
						$third_row = 7;
						$period_begin = $this -> clean_date(trim($arr[$third_row]['D'] . $arr[$third_row]['E']));
						$period_end = $this -> clean_date(trim($arr[$third_row]['G'] . $arr[$third_row]['H']));
						$file_type = $this -> checkFileType($code, $text);
						$facilities = Sync_Facility::getId($facility_code, $status_code);
						$facility_id = $facilities['id'];
						$duplicate = $this -> check_duplicate($code, $period_begin, $period_end, $facility_id, "maps");

						if ($facilities == "") {
							$ret[] = "Your facility Code  in '" . $_FILES["file"]["name"][$q]. "' file does not match any facility.  Kindly cross check the MFL code and / or check if the facility uploading is an ordering point.";
						} else if ($period_begin != date('Y-m-01', strtotime(date('F-Y') . "-1 month")) || $period_end != date('Y-m-t', strtotime(date('F-Y') . "-1 month"))) {
							$ret[] = "You can only report for ".date('F-Y', strtotime(date('F-Y') . "-1 month"))." . Kindly check the period fields !-" . $_FILES["file"]["name"][$q].' The format should be dd/mm/yyyy';
						} else if ($file_type == false) {
							$ret[] = "Incorrect File Selected - " . $_FILES["file"]["name"][$q];
						} else if ($duplicate == true) {
							$ret[] = "A MAPS report already exists for this month !-" . $_FILES["file"]["name"][$i];
						} else {
							$fourth_row = 9;
							$sponsors = "";
							$sponsor_gok = trim($arr[$fourth_row]['D']);
							$sponsor_pepfar = trim($arr[$fourth_row]['F']);
							$sponsor_msf = trim($arr[$fourth_row]['H']);
							if ($sponsor_gok) {
								$sponsors = "GOK";
							}
							if ($sponsor_pepfar) {
								$sponsors = "PEPFAR";
							}
							if ($sponsor_msf) {
								$sponsors = "MSF";
							}

							$fifth_row = 11;
							$service = array();
							$service_art = trim($arr[$fifth_row]['D']);
							$service_pmtct = trim($arr[$fifth_row]['F']);
							$service_pep = trim($arr[$fifth_row]['H']);
							if ($service_art) {
								$service[] = "ART";
							}
							if ($service_pmtct) {
								$service[] = "PMTCT";
							}
							if ($service_pep) {
								$service[] = "PEP";
							}

							$services = implode(",", $service);
							$art_adult_cell =(int)substr($this -> getCellByValue("Total Number of Patients on ART ONLY", $file_name), 1) ;
							$art_adult = str_replace(",", "", $arr[$art_adult_cell]["D"]);
							$art_child = str_replace(",", "", $arr[$art_adult_cell]["F"]);
							$new_male = str_replace(",", "", $arr[$art_adult_cell+4]["D"]);
							$new_female = str_replace(",", "", $arr[$art_adult_cell+4]["F"]);
							$revisit_male = str_replace(",", "", $arr[$art_adult_cell+4]["E"]);
							$revisit_female = str_replace(",", "", $arr[$art_adult_cell+4]["G"]);
							//Get cells for PMTCT
							$pmtcts_cell =(int)substr($this -> getCellByValue("Totals for PMTCT Clients", $file_name), 1) ;
							$new_pmtct = str_replace(",", "", $arr[$pmtcts_cell+1]["H"]);
							$revisit_pmtct = str_replace(",", "", $arr[$pmtcts_cell+2]["H"]);
							//Get cells for Prophylaxis
							$prophylaxis_cell =(int)substr($this -> getCellByValue("Total No. of Infants receiving ARV", $file_name), 1) ;
							$total_infant = str_replace(",", "", $arr[$prophylaxis_cell+1]["H"]);
							//Cells for pep
							$peps_cell =(int)substr($this -> getCellByValue("Totals for PEP Clients ONLY", $file_name), 1) ;
							$pep_adult = str_replace(",", "", $arr[$peps_cell+1]["H"]);
							$pep_child = str_replace(",", "", $arr[$peps_cell+2]["H"]);
							//Cotrimo Cells
							$cotrimos_cell =(int)substr($this -> getCellByValue("Totals for Patients / Clients (ART plus Non-ART)", $file_name), 1) ;
							$total_adult = str_replace(",", "", $arr[$cotrimos_cell]["E"]);
							$total_child = str_replace(",", "", $arr[$cotrimos_cell]["G"]);
							//Diflucan Cells
							$diflucans_cell =(int)substr($this -> getCellByValue("Totals for Patients / Clients on Diflucan", $file_name), 1) ;
							$diflucan_adult = str_replace(",", "", $arr[$diflucans_cell]["E"]);
							$diflucan_child = str_replace(",", "", $arr[$diflucans_cell]["G"]);
							$new_cm = str_replace(",", "", $arr[$diflucans_cell+6]["D"]);
							$revisit_cm = str_replace(",", "", $arr[$diflucans_cell+6]["E"]);
							$new_oc = str_replace(",", "", $arr[$diflucans_cell+6]["F"]);
							$revisit_oc = str_replace(",", "", $arr[$diflucans_cell+6]["G"]);

							//Save Import Values

							$created = date('Y-m-d H:i:s');
							$main_array = array();
							$main_array['id'] = "";
							$main_array['status'] = $status;
							$main_array['created'] = $created;
							$main_array['updated'] = "";
							$main_array['code'] = $code;
							$main_array['period_begin'] = $period_begin;
							$main_array['period_end'] = $period_end;
							$main_array['reports_expected'] = "";
							$main_array['reports_actual'] = "";
							$main_array['services'] = $services;
							$main_array['sponsors'] = $sponsors;
							$main_array['art_adult'] = $art_adult;
							$main_array['art_child'] = $art_child;
							$main_array['new_male'] = $new_male;
							$main_array['revisit_male'] = $revisit_male;
							$main_array['new_female'] = $new_female;
							$main_array['revisit_female'] = $revisit_female;
							$main_array['new_pmtct'] = $new_pmtct;
							$main_array['revisit_pmtct'] = $revisit_pmtct;
							$main_array['total_infant'] = $total_infant;
							$main_array['pep_adult'] = $pep_adult;
							$main_array['pep_child'] = $pep_child;
							$main_array['total_adult'] = $total_adult;
							$main_array['total_child'] = $total_child;
							$main_array['diflucan_adult'] = $diflucan_adult;
							$main_array['diflucan_child'] = $diflucan_child;
							$main_array['new_cm'] = $new_cm;
							$main_array['revisit_cm'] = $revisit_cm;
							$main_array['new_oc'] = $new_oc;
							$main_array['revisit_oc'] = $revisit_oc;
							$main_array['comments'] = "";
							$main_array['report_id'] = "";
							$main_array['facility_id'] = $facility_id;

							//Insert Maps items
							$sixth_row = 25;
							$maps_array = array();
							$nonstandard_maps_array = array();
							$regimen_counter = 0;
							
							/*
							 *Check for regimen categories indexes , only return row numbers
							 */
							$pmtct_preg_women_cell =(int)substr($this -> getCellByValue("PMTCT Regimens for Pregnant Women", $file_name), 1) ;
							$pmtct_infant_cell = (int)substr($this -> getCellByValue("PMTCT Regimens for Infants", $file_name), 1) ;
							$art_adult_first_cell = (int)substr($this -> getCellByValue("ADULT ART First-Line Regimens", $file_name), 1) ;
							$art_adult_second_cell = (int)substr($this -> getCellByValue("ADULT ART Second-Line Regimens", $file_name), 1) ;
							$art_adult_other_cell = (int)substr($this -> getCellByValue("Other ADULT ART Regimens", $file_name), 1) ;
							$paed_first_cell = (int)substr($this -> getCellByValue("PAEDIATRIC ART First-Line Regimens", $file_name), 1) ;
							$paed_second_cell = (int)substr($this -> getCellByValue("PAEDIATRIC ART Second-Line Regimens", $file_name), 1) ;
							$paed_other_cell = (int)substr($this -> getCellByValue("Other PAEDIATRIC ART Regimens", $file_name), 1) ;
							$pep_adult_cell = (int)substr($this -> getCellByValue("Post Exposure Prophylaxis (PEP) for Adults", $file_name), 1) ;
							$pep_child_cell = (int)substr($this -> getCellByValue("Post Exposure Prophylaxis (PEP) for Children", $file_name), 1) ;
							
							$oi_cell = (int)substr($this -> getCellByValue("Opportunistic Infections", $file_name), 1) ;
							
							//Get where list of regimens end
							$end_list = $oi_cell - 2;
							$reg_category = "";
							for ($i = $sixth_row; $sixth_row, $i <= $end_list; $i++) {
								
								//Get regimen category names, names are meant to be the same as the ones in the sync_category table
								
								if($i == $pmtct_preg_women_cell){
									$reg_category = "PMTCT Regimens for Pregnant Women";
								}else if($i == $pmtct_infant_cell){
									$reg_category = "PMTCT Regimens for Infants";
								}else if($i == $art_adult_first_cell){
									$reg_category = "ADULT ART First Line";
								}else if($i == $art_adult_second_cell){
									$reg_category = "Adult ART Second Line";
								}else if($i == $art_adult_other_cell){
									$reg_category = "Other Adult ART Regimen";
								}else if($i == $paed_first_cell){
									$reg_category = "Paediatric First Line";
								}else if($i == $paed_second_cell){
									$reg_category = "Paediatric Second Line";
								}else if($i == $paed_other_cell){
									$reg_category = "Other Paediatric ART Regimen";
								}else if($i == $pep_adult_cell){
									$reg_category = "PEP Adult";
								}else if($i == $pep_child_cell){
									$reg_category = "PEP Child";
								}
								
								if ($i != $pmtct_preg_women_cell || $i != $pmtct_infant_cell || $i != $art_adult_first_cell || $i != $art_adult_second_cell || $i != $art_adult_other_cell || $i != $paed_first_cell || $i != $paed_second_cell || $i != $paed_other_cell || $i != $pep_adult_cell || $i != $pep_child_cell) {
									if ($arr[$i]['E'] != 0 || trim($arr[$i]['A']) != "") {
										$regimen_code = $arr[$i]['A'];
										$regimen_desc = $arr[$i]['B'];
										$total = $arr[$i]['E'];
										
										$regimen_id = $this -> getMappedRegimen($regimen_code, $regimen_desc);
										//echo $regimen_id.' - '.$regimen_code.' - '.$regimen_desc.'<br>';
										
										if ($regimen_id != null && $total != null) {
											$maps_array[$regimen_counter]["id"] = "";
											$maps_array[$regimen_counter]["regimen_id"] = $regimen_id;
											$maps_array[$regimen_counter]["total"] = str_replace(",", "", $total);
											$maps_array[$regimen_counter]["maps_id"] = "";
										}
										else if($regimen_id == null && $total != null){// If regimen is not found, check in non standard regimens
											$regimen_id = $this -> getNonStandardRegimen($regimen_code, $regimen_desc);
											
											//If regimen exists, insert it in Non standard Maps Item table
											if($regimen_id!=null){
												$nonstandard_maps_array[$regimen_counter]["id"] = "";
												$nonstandard_maps_array[$regimen_counter]["regimen_id"] = $regimen_id;
												$nonstandard_maps_array[$regimen_counter]["total"] = str_replace(",", "", $total);
												$nonstandard_maps_array[$regimen_counter]["maps_id"] = "";
											}
											else{//Is regimen still not found in non standard regimen, insert it into non standard regimen
												//Get regimen category
												
												$category_id = Sync_category::getId($reg_category);
												$cat_id = $category_id['id'];
												$nonstandard_regimen = new Nonstandard_regimen();
												$nonstandard_regimen ->regimen_code = $regimen_code;
												$nonstandard_regimen ->regimen_desc = $regimen_desc;
												$nonstandard_regimen ->category = $cat_id;
												$nonstandard_regimen ->line = '';
												$nonstandard_regimen ->type_Of_service = '';
												$nonstandard_regimen ->active = 1;
												$nonstandard_regimen ->n_map = '';
												$nonstandard_regimen ->e_map = '';
												
												$nonstandard_regimen ->save();
												$regimen_id = Nonstandard_regimen::getMaxId();
												$regimen_id = $regimen_id['max_id'];
												
												//Insert into nonstandard maps item
												$nonstandard_maps_array[$regimen_counter]["id"] = "";
												$nonstandard_maps_array[$regimen_counter]["regimen_id"] = $regimen_id;
												$nonstandard_maps_array[$regimen_counter]["total"] = str_replace(",", "", $total);
												$nonstandard_maps_array[$regimen_counter]["maps_id"] = "";
												
											}
											
											
										}
										
										
										$regimen_counter++;
									}
								}
							}
							//die();
							$main_array['ownMaps_item'] = $maps_array;
							$main_array['ownNonStandardMaps_item'] = $nonstandard_maps_array;

							$log_array[0]['id'] = "";
							$log_array[0]['description'] = "prepared";
							if ($code == "D-MAPS") {
								$log_array[0]['created'] = $this -> clean_date(trim($arr[$oi_cell+21]['E']));
								$log_array[0]['user_id'] = $this -> getUser(trim($arr[$oi_cell+19]['B']));
							} else {
								$log_array[0]['created'] = $this -> clean_date(trim($arr[$oi_cell+17]['E']));
								$log_array[0]['user_id'] = $this -> getUser(trim($arr[$oi_cell+15]['B']));
							}
							$log_array[0]['maps_id'] = "";

							$log_array[1]['id'] = "";
							$log_array[1]['description'] = "approved";
							if ($code == "D-MAPS") {
								$log_array[1]['created'] = $this -> clean_date(trim($arr[$oi_cell+25]['E']));
								$log_array[1]['user_id'] = $this -> getUser(trim($arr[$oi_cell+23]['B']));
							} else {
								$log_array[1]['created'] = $this -> clean_date(trim($arr[$oi_cell+21]['E']));
								$log_array[1]['user_id'] = $this -> getUser(trim($arr[$oi_cell+19]['B']));
							}
							$log_array[1]['maps_id'] = "";

							$main_array['ownMaps_log'] = $log_array;
							$type = "maps";

							$main_array = array($main_array);
							$this -> prepare_order($type, $main_array);
							$content = "Order was successfully saved. ";

							$dir = "Export";

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

							//move the file
							$file_location = $dir . "/" . $_FILES['file']['name'][$q];
							move_uploaded_file($_FILES['file']['tmp_name'][$q], $file_location);
							//send excel file to email
							$content .= $this -> send_file($file_location);
							$ret[] = $content . $_FILES["file"]["name"][$q];
						}
					}
					//end of maps
				}//end of loops
				$ret = implode("<br/>", $ret);
				$this -> session -> set_flashdata('login_message', $ret);
			}//end of if check for file
			redirect("dashboard_management");

		} else if ($type == "pipeline_upload") {//Upload Central medical stores and pending orders data
			$this -> load -> library('PHPExcel');
			$objReader = new PHPExcel_Reader_Excel5();
			if ($_FILES['cms_file']['tmp_name']) {
				$objPHPExcel = $objReader -> load($_FILES['cms_file']['tmp_name']);
			} else {
				$this -> session -> set_flashdata('order_message', "No file found !");
				$this -> session -> set_flashdata('pipeline_upload', 1);
				redirect("order/pipeline_upload");
			}

			$arr = $objPHPExcel -> getActiveSheet() -> toArray(null, true, true, true);
			$highestColumm = $objPHPExcel -> setActiveSheetIndex(0) -> getHighestColumn();
			$highestRow = $objPHPExcel -> setActiveSheetIndex(0) -> getHighestRow();

			$period = $arr[2]['C'];
			$period = trim($period);
			$period = date("Y-m-01", strtotime($period));
			$pipeline = $arr[3]['C'];
			$pipeline = trim($pipeline);
			$data = array();
			$z = 0;
			//Check if period and pipeline are entered
			if ($period == "" || $pipeline == "") {
				$this -> session -> set_flashdata('order_message', "Please make sure you fill the period and the pipeline name!");
				$this -> session -> set_flashdata('pipeline_upload', 1);
				redirect("order/pipeline_upload");
				die();
			} else {
				$text = $arr[2]['A'];
				$x = 7;
				$y = $highestRow;
				$sql = "";
				$pipeline = strtolower($pipeline);
				if ($pipeline == "kenya pharma") {
					$pipeline = "kp";
				}
				while ($x <= $y) {
					//get drug details
					$drug = $arr[$x]["A"];
					$drug_array = explode("-", $drug);
					$drug_name = trim($drug_array[0]);
					$drug_abbrev = trim($drug_array[1]);
					$drug_strength = trim($drug_array[2]);

					if (isset($drug_abbrev[3]) and $drug_abbrev[3] != null) {
						$drug_packsize = trim($drug_array[3]);
					} else {
						$drug_packsize = "";
					}
					//echo $drug_name." : ".$drug_abbrev."(".$drug_strength.")".$drug_packsize."<br>";
					//Get drugs ids
					if (strtolower($pipeline) == "kemsa") {
						$drug_id = sync_drug::getDrugId($drug_name, $drug_abbrev, $drug_strength, $drug_packsize);
					} else {//Kenya Pharma Pipeline
						$drug_id = escm_drug::getDrugId($drug_name, $drug_abbrev, $drug_strength, $drug_packsize);
					}
					//echo var_dump($drug_id)."<br>";
					if ($drug_id != "") {
						$drug_id = $drug_id[0]['id'];
						$cms = $arr[$x]["B"];
						$pending = $arr[$x]["C"];
						//echo $cms.'<br>';
						if (trim($cms) != "" or trim($pending) != "") {//Only populate if cms or pending has data
							$data[$z] = array("cms" => "$cms", "pending" => "$pending", "period_begin" => "$period", "pipeline" => "$pipeline", "drug_id" => "$drug_id");
							$z++;
						}

					}
					$x++;

				}
				//die();
				//Run batch
				if ($data[0] != "") {
					$query = $this -> db -> insert_batch('facility_soh', $data);
				}
				$this -> session -> set_flashdata('login_message', "You data have been successfully imported!");
				$this -> session -> set_flashdata('pipeline_upload', 1);
				redirect("order/pipeline_upload");
				die();

			}

		}

	}

	public function getCellValues($filename,$force = false){
		if ( !is_null($this->cellValues) && $force === false ){
			return $this->cellValues;
		}
		
		$this->objPHPExcel = PHPExcel_IOFactory::load($filename);
		$currentIndex = $this->objPHPExcel->getActiveSheetIndex();
		$this->objPHPExcel->setActiveSheetIndex(0);


		$sheet = $this->objPHPExcel->getActiveSheet();
		$highestColumn = $sheet->getHighestColumn(); //e.g., 'G'
		$highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn); //e.g., 6
		$highestRow= $sheet->getHighestRow();

		$this->cellValues = array();
		for ( $i =0 ; $i < $highestColumnIndex; $i++ ){
			$column = PHPExcel_Cell::stringFromColumnIndex($i);
			for ( $j = 1; $j <= $highestRow; $j++ ){
				$this->cellValues[$column . $j] = $sheet->getCellByColumnAndRow($i, $j)->getValue();
			}
		}
		$this->objPHPExcel->setActiveSheetIndex($currentIndex);
		return $this->cellValues;
	}
	
	/**
	 * returns cell by value. Be carefull, could be ambigous, only use
	 * if you really know what you are doing
	 */
	public function getCellByValue($search,$filename) {
		$nonPrintableChars = array("\n", "\r", "\t", "\s");
		$search = str_replace($nonPrintableChars, '', $search);
		foreach ( $this->getCellValues($filename) as $cell => $value ){
			if (stripos(str_replace($nonPrintableChars, '', $value), $search) === 0){
				return $cell;
			}
		}
		return false;
	}

	public function send_file($excel_file) {
		$email_user = stripslashes('webadt.chai@gmail.com');
		$email_password = stripslashes('WebAdt_052013');
		$emails = Users::getPharmacistEmail();
		$mail_list = array();
		foreach ($emails as $email) {
			$mail_list[] = $email['email'];
		}
		$email_address = implode(",", $mail_list);
		$subject = "NASCOP Order Upload";
		$email_sender_title = "NASCOP SYSTEM";

		$message = "Hello, <br/><br/>
		                An order was just uploaded to the $email_sender_title </b><br/>
						Please find the specific order attached.<br/><br/>
						Regards,<br/>
						$email_sender_title team.";

		$config['mailtype'] = "html";
		$config['protocol'] = 'smtp';
		$config['smtp_host'] = 'ssl://smtp.googlemail.com';
		$config['smtp_port'] = 465;
		$config['smtp_user'] = $email_user;
		$config['smtp_pass'] = $email_password;
		ini_set("SMTP", "ssl://smtp.gmail.com");
		ini_set("smtp_port", "465");

		$this -> load -> library('email', $config);
		$this -> email -> set_newline("\r\n");
		$this -> email -> from('webadt.chai@gmail.com', $email_sender_title);
		$this -> email -> to("$email_address");
		$this -> email -> subject($subject);
		$this -> email -> message($message);
		$this -> email -> attach($excel_file);

		if ($this -> email -> send()) {
			$this -> email -> clear(TRUE);
			$error_message = 'Email was sent to <b>' . $email_address . '</b> <br/>';
		} else {
			$error_message='Cannot Connect to Mail Server';
			//$error_message = $this -> email -> print_debugger();
		}

		echo $error_message;
	}

	public function prepare_order($type = "cdrr", $responses = array()) {
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
				foreach ($log as $counter => $items) {
					foreach ($items as $ind => $item) {
						if ($ind == "cdrr_id") {
							$temp_log[$counter]['cdrr_id'] = $cdrr_id;
						} else {
							$temp_log[$counter][$ind] = $item;
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
		} else if ($type == "maps") {
			$maps = array();
			$temp_items = array();
			$temp_nonstandard_items = array();
			$temp_log = array();
			$maps_log = array();
			$maps_items = array();
			$nonstandard_maps_items = array();
			foreach ($responses as $response) {
				foreach ($response as $index => $main) {
					if ($index == "ownMaps_item") {
						$temp_items['maps_item'] = $main;
					} else if ($index == "ownMaps_log") {
						$temp_log['maps_log'] = $main;
					} else if ($index == "ownNonStandardMaps_item"){
						$temp_nonstandard_items['nonstandard_maps_item'] = $main;
						
					} else {
						$maps[$index] = $main;
					}
				}
			}
			$this -> db -> insert("maps", $maps);
			$maps_id = $this -> db -> insert_id();

			//attach maps id to maps_log
			foreach ($temp_log as $logs) {
				foreach ($logs as $counter => $items) {
					foreach ($items as $ind => $item) {
						if ($ind == "maps_id") {
							$maps_log[$counter]['maps_id'] = $maps_id;
						} else {
							$maps_log[$counter][$ind] = $item;
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
			
			//Attach maps id to non standard maps item
			foreach ($temp_nonstandard_items as $temp_nonstandard_item) {
				foreach ($temp_nonstandard_item as $counter => $items) {
					foreach ($items as $ind => $item) {
						if ($ind == "maps_id") {
							$nonstandard_maps_items[$counter]['maps_id'] = $maps_id;
						} else {
							$nonstandard_maps_items[$counter][$ind] = $item;
						}
					}
				}
			}
			//var_dump($nonstandard_maps_items);die();
			$this -> db -> insert_batch('nonstandard_maps_item', $nonstandard_maps_items);
		}
	}

	public function download_cdrr($cdrr_id) {
		$this -> load -> library('PHPExcel');
		$cdrr_array = array();
		$dir = "Export";
		$drug_name = "CONCAT_WS('] ',CONCAT_WS(' [',sd.name,sd.abbreviation),CONCAT_WS(' ',sd.strength,sd.formulation)) as drug_map";

		$sql = "SELECT c.*,ci.*,cl.*,f.*,co.county as county_name,d.name as district_name,u.*,al.level_name,IF(c.code='D-CDRR',CONCAT('D-CDRR#',c.id),CONCAT('F-CDRR#',c.id)) as cdrr_label,c.status as status_name,sf.name as facility_name,$drug_name
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
		$template = "";

		if ($report_type == "D-CDRR") {
			$template = "cdrr_aggregate";
		} else if ($report_type == "F-CDRR_packs") {
			$template = "cdrr_stand_alone";
		} else if ($report_type == "F-CDRR_units") {
			$template = "cdrr_satellite";
		}

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

		if ($cdrr_array[0]['sponsors'] != "") {
			if (strtoupper($cdrr_array[0]['sponsors']) == "GOK" || strtoupper($cdrr_array[0]['sponsors']) == "KEMSA") {
				$loc = "D";
			} else if (strtoupper($cdrr_array[0]['sponsors']) == "PEPFAR" || strtoupper($cdrr_array[0]['sponsors']) == "KENYA PHARMA") {
				$loc = "F";
			} else if (strtoupper($cdrr_array[0]['sponsors']) == "MSF") {
				$loc = "H";
			}
			$objPHPExcel -> getActiveSheet() -> SetCellValue($loc . '9', "X");
		}

		$services = explode(",", $cdrr_array[0]['services']);
		if ($services != "") {
			foreach ($services as $service) {
				if (strtoupper($service) == "ART") {
					$objPHPExcel -> getActiveSheet() -> SetCellValue('D11', "X");
				} else if (strtoupper($service) == "PMTCT") {
					$objPHPExcel -> getActiveSheet() -> SetCellValue('F11', "X");
				} else if (strtoupper($service) == "PEP") {
					$objPHPExcel -> getActiveSheet() -> SetCellValue('H11', "X");
				}
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
							if ($cdrr_array[0]['code'] == "F-CDRR_packs") {
								$objPHPExcel -> getActiveSheet() -> SetCellValue('C' . $i, $cdrr_item['balance']);
								$objPHPExcel -> getActiveSheet() -> SetCellValue('D' . $i, $cdrr_item['received']);
								$objPHPExcel -> getActiveSheet() -> SetCellValue('E' . $i, $cdrr_item['dispensed_units']);
								$objPHPExcel -> getActiveSheet() -> SetCellValue('F' . $i, $cdrr_item['dispensed_packs']);
								$objPHPExcel -> getActiveSheet() -> SetCellValue('G' . $i, $cdrr_item['losses']);
								$objPHPExcel -> getActiveSheet() -> SetCellValue('H' . $i, $cdrr_item['adjustments']);
								$objPHPExcel -> getActiveSheet() -> SetCellValue('I' . $i, $cdrr_item['count']);
								$objPHPExcel -> getActiveSheet() -> SetCellValue('J' . $i, $cdrr_item['expiry_quant']);
								$objPHPExcel -> getActiveSheet() -> SetCellValue('K' . $i, $cdrr_item['expiry_date']);
								$objPHPExcel -> getActiveSheet() -> SetCellValue('L' . $i, $cdrr_item['out_of_stock']);
								$objPHPExcel -> getActiveSheet() -> SetCellValue('M' . $i, $cdrr_item['resupply']);
							} else {
								$objPHPExcel -> getActiveSheet() -> SetCellValue('C' . $i, $cdrr_item['balance']);
								$objPHPExcel -> getActiveSheet() -> SetCellValue('D' . $i, $cdrr_item['received']);
								$objPHPExcel -> getActiveSheet() -> SetCellValue('E' . $i, $cdrr_item['dispensed_units']);
								$objPHPExcel -> getActiveSheet() -> SetCellValue('F' . $i, $cdrr_item['losses']);
								$objPHPExcel -> getActiveSheet() -> SetCellValue('G' . $i, $cdrr_item['adjustments']);
								$objPHPExcel -> getActiveSheet() -> SetCellValue('H' . $i, $cdrr_item['count']);
								if ($cdrr_array[0]['code'] == "D-CDRR") {
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
		}

		if ($cdrr_array[0]['code'] == 'D-CDRR') {
			$objPHPExcel -> getActiveSheet() -> SetCellValue('E108', $cdrr_array[0]['reports_expected']);
			$objPHPExcel -> getActiveSheet() -> SetCellValue('H108', $cdrr_array[0]['reports_actual']);

			$logs = Cdrr_Log::getObjectLogs($cdrr_id);
			foreach ($logs as $log) {
				if ($log -> description == "prepared") {
					$objPHPExcel -> getActiveSheet() -> SetCellValue('C111', $log -> user -> name);
					$objPHPExcel -> getActiveSheet() -> SetCellValue('C113', $log -> user -> username);
					$objPHPExcel -> getActiveSheet() -> SetCellValue('K111', $log -> user -> role);
					$objPHPExcel -> getActiveSheet() -> SetCellValue('G113', $log -> created);
				} else if ($log -> description == "approved") {
					$objPHPExcel -> getActiveSheet() -> SetCellValue('C115', $log -> user -> name);
					$objPHPExcel -> getActiveSheet() -> SetCellValue('C117', $log -> user -> username);
					$objPHPExcel -> getActiveSheet() -> SetCellValue('K115', $log -> user -> role);
					$objPHPExcel -> getActiveSheet() -> SetCellValue('G117', $log -> created);
				}
			}

		} else {
			$logs = Cdrr_Log::getObjectLogs($cdrr_id);
			foreach ($logs as $log) {
				if ($log -> description == "prepared") {
					$objPHPExcel -> getActiveSheet() -> SetCellValue('C107', $log -> user -> name);
					$objPHPExcel -> getActiveSheet() -> SetCellValue('C109', $log -> user -> username);
					$objPHPExcel -> getActiveSheet() -> SetCellValue('K107', $log -> user -> role);
					$objPHPExcel -> getActiveSheet() -> SetCellValue('G109', $log -> created);
				} else if ($log -> description == "approved") {
					$objPHPExcel -> getActiveSheet() -> SetCellValue('C111', $log -> user -> name);
					$objPHPExcel -> getActiveSheet() -> SetCellValue('C113', $log -> user -> username);
					$objPHPExcel -> getActiveSheet() -> SetCellValue('K111', $log -> user -> role);
					$objPHPExcel -> getActiveSheet() -> SetCellValue('G113', $log -> created);
				}
			}

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

	public function check_duplicate($code, $period_start, $period_end, $facility, $table = "cdrr") {
		$response = false;
		$sql = "select * from $table where period_begin='$period_start' and period_end='$period_end' and code='$code' and facility_id='$facility'";
		$query = $this -> db -> query($sql);
		$results = $query -> result_array();
		if ($results) {
			$response = true;
			$this -> session -> set_flashdata('order_message', strtoupper($table) . ' report already exists for this month !');

		}
		return $response;
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
		$tmpl = array('table_open' => '<table id="my_orders">');
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

	public function clean_date($base_date) {
		$clean_date = "";
		$date_array = explode("/", @$base_date);
		$clean_date = @$date_array[2] . "-" . @$date_array[1] . "-" . @$date_array[0];
		return $clean_date;
	}

	public function checkFileType($type, $text) {

		if ($type == "D-CDRR") {
			$match = trim("CENTRAL SITE  / DISTRICT STORE CONSUMPTION DATA REPORT AND REQUEST (D-CDRR) FOR ANTIRETROVIRAL AND OPPORTUNISTIC INFECTION MEDICINES");
		} else if ($type == "D-MAPS") {
			$match = trim("CENTRAL SITE  / DISTRICT STORE MONTHLY ARV PATIENT SUMMARY (D-MAPS) REPORT");
		} else if ($type == "F-CDRR_packs" || $type == "F-CDRR_units") {
			$match = trim("FACILITY CONSUMPTION DATA REPORT AND REQUEST (F-CDRR) FOR ANTIRETROVIRAL AND OPPORTUNISTIC INFECTION MEDICINES");
		} else if ($type == "F-MAPS") {
			$match = trim("FACILITY MONTHLY ARV PATIENT SUMMARY (F-MAPS) REPORT");
		}

		//Test
		if (trim($text) === $match) {
			return true;
		} else {
			return false;
		}
	}

	public function getUser($name) {
		$user_id = Sync_User::getId($name);
		if ($user_id) {
			return $user_id['id'];
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
			$list_array = array_count_values($drug_list);
			if (is_array($list_array)) {
				if (!empty($list_array)) {
					return $key = array_search(max(array_count_values($drug_list)), array_count_values($drug_list));
				}
			}
		}
		return null;
	}

	public function getMappedRegimen($regimen_code = "", $regimen_desc = "") {
		if ($regimen_code != "") {
			$sql = "SELECT r.n_map
				    FROM regimen r
				    WHERE(r.regimen_code='$regimen_code')";
			$query = $this -> db -> query($sql);
			$results = $query -> result_array();
			
			if ($results) {
				return $results[0]['n_map'];
			} else {
				return null;
			}
		}
		return null;
	}

	public function getNonStandardRegimen($regimen_code="",$regimen_desc = ""){
		$sql = "SELECT r.id
				    FROM nonstandard_regimen r
				    WHERE(r.regimen_code='$regimen_code'
				    AND r.regimen_desc='$regimen_desc')";
					
		$query = $this -> db -> query($sql);
		$results = $query -> result_array();
		if ($results) {
			return $results[0]['id'];
		} else {
			return null;
		}
	}

	public function getActualCode($code, $type) {
		if ($type == "cdrr") {
			if ($code == 0) {
				$code = "D-CDRR";
			} else if ($code == 3) {
				$code = "F-CDRR_packs";
			} else {
				$code = "F-CDRR_units";
			}
		} else if ($type == "maps") {
			if ($code == 0) {
				$code = "D-MAPS";
			} else {
				$code = "F-MAPS";
			}
		}
		return $code;
	}

	public function clear_all() {
		$sql_array = array();
		$sql_array[] = "TRUNCATE cdrr";
		$sql_array[] = "TRUNCATE cdrr_item";
		$sql_array[] = "TRUNCATE cdrr_log";
		$sql_array[] = "TRUNCATE maps";
		$sql_array[] = "TRUNCATE maps_item";
		$sql_array[] = "TRUNCATE maps_log";
		foreach ($sql_array as $sql) {
			$query = $this -> db -> query($sql);
		}
		redirect("order");
	}

	public function getAMC($facility_id, $code, $period_begin = "") {
		$earlier_begin = date('Y-m-d', strtotime($period_begin . "-3 months"));
		$total = Cdrr::getAMC($facility_id, $code, $earlier_begin, $period_begin);
		return $total;
	}

	public function upload_authenticate() {
		$email_address = $this -> input -> post("email", TRUE);
		$password = md5($this -> input -> post("password", TRUE));
		$user = Sync_User::getAuthenticUser($email_address, $password);
		if ($user) {
			if ($user['status'] != 'A') {
				$this -> session -> set_flashdata('login_message', "Account has been deactivated!<br/>Contact the Administrator.");
			} else {
				$this -> session -> set_userdata('upload_valid', $user['id']);
				$this -> session -> set_userdata("order_user", $user['name']);
			}
		} else {
			$this -> session -> set_flashdata('login_message', "Login Failed!");
		}
		redirect("dashboard_management");

	}

	public function upload_logout() {
		$this -> session -> unset_userdata("upload_valid");
		$this -> session -> unset_userdata("order_pipeline");
		redirect("dashboard_management");
	}

	public function upload_forgot() {
		//forgot password
		$email_address = $this -> input -> post("email");
		$user = Sync_User::getUser($email_address);
		$password = "";
		if ($user) {
			$user_id = $user['id'];
			$characters = strtoupper("abcdefghijklmnopqrstuvwxyz");
			$characters = $characters . 'abcdefghijklmnopqrstuvwxyz0123456789';
			$password_length = 6;
			$string = '';
			for ($i = 0; $i < $password_length; $i++) {
				$password .= $characters[rand(0, strlen($characters) - 1)];
			}
			$this -> db -> where('id', $user_id);
			$this -> db -> update("sync_user", array("password" => md5($password)));
			$message = $this -> send_password($email_address, $password);
			$this -> session -> set_flashdata('login_message', $message);

		} else {
			$this -> session -> set_flashdata('login_message', "not a valid user email!");
		}
		redirect("dashboard_management");
	}

	public function upload_password() {
		//change password
		$current_password = $this -> input -> post("current_password");
		$new_password = $this -> input -> post("new_password");
		$confirm_password = $this -> input -> post("confirm_password");
		$user_id = $this -> session -> userdata('upload_valid');
		$db_password = Sync_User::getCurrentPassword($user_id);
		if ($db_password == md5($current_password)) {
			if ($new_password == $confirm_password) {
				$this -> db -> where('id', $user_id);
				$this -> db -> update("sync_user", array("password" => md5($new_password)));
				$this -> session -> set_flashdata('login_message', "password updated successfully");
			} else {
				$this -> session -> set_flashdata('login_message', "new password and confirm password are mismatch");
			}
		} else {
			$this -> session -> set_flashdata('login_message', "current password incorrect!");
		}
		redirect("dashboard_management");
	}

	public function send_password($email_address, $password) {
		$email_user = stripslashes('webadt.chai@gmail.com');
		$email_password = stripslashes('WebAdt_052013');
		$subject = "NASCOP User Password Reset";
		$email_sender_title = "NASCOP SYSTEM";

		$message = "Hello NASCOP USER, <br/><br/>
		                Your account for the $email_sender_title was reset</b><br/>
						The new password is <b> $password </b><br/><br/>
						Regards,<br/>
						$email_sender_title team.";

		$config['mailtype'] = "html";
		$config['protocol'] = 'smtp';
		$config['smtp_host'] = 'ssl://smtp.googlemail.com';
		$config['smtp_port'] = 465;
		$config['smtp_user'] = $email_user;
		$config['smtp_pass'] = $email_password;
		ini_set("SMTP", "ssl://smtp.gmail.com");
		ini_set("smtp_port", "465");

		$this -> load -> library('email', $config);
		$this -> email -> set_newline("\r\n");
		$this -> email -> from('webadt.chai@gmail.com', $email_sender_title);
		$this -> email -> to("$email_address");
		$this -> email -> subject($subject);
		$this -> email -> message($message);

		if ($this -> email -> send()) {
			$this -> email -> clear(TRUE);
			$error_message = 'Email was sent to <b>' . $email_address . '</b> <br/>';
		} else {
			$error_message = $this -> email -> print_debugger();
		}

		return $error_message;
	}

	public function pipeline_upload() {
		$data['title'] = 'webADT | Pipeline Upload';
		$data['banner_text'] = 'Pipeline Upload';
		$data['content_view'] = 'pipeline_v';
		$data['page_title'] = 'Pipeline Upload';
		$this -> base_params($data);
	}
	public function twopager_upload($type="",$delete="") {
		if($type==""){
			$data['title'] = 'webADT | 2 Pager Upload';
			$data['banner_text'] = '2 Pager Upload';
			$data['content_view'] = 'twopager_v';
			$data['page_title'] = '2 Pager Upload';
			$data['files']  = Two_pager::getAllHydrated();
			$this -> base_params($data);
		}
		else if($type=="upload"){
			$period = $this->input->post("period_selected");
			if ($_FILES['cms_file']['tmp_name']) {
				//Check if period was selected
				if($period=='0'){
					$this -> session -> set_flashdata('order_message', "Please select a period !");
					$this -> session -> set_flashdata('twopager_upload', 1);
					redirect("order/twopager_upload");
				}
				else{
					$dir = "uploads/2pager";
					/*Delete all files in export folder*/
					if (is_dir($dir)) {
						
					} else {
						mkdir($dir);
					}

					//move the file
					$file_name = $_FILES['cms_file']['name'];
					$ext = pathinfo($file_name, PATHINFO_EXTENSION);
					$name = $period.'.'.$ext;
					$file_location = $dir . "/" . $name;
					try{
						move_uploaded_file($_FILES['cms_file']['tmp_name'], $file_location);
						$this -> session -> set_flashdata('order_message', "File successfully uploaded ! ");
						//Save location to the database
						$check_if_exist = count(Two_pager::checkIfExist($period));
						if($check_if_exist==0){
							$data =array(
										"period" =>$period,
										"link" =>$file_location,
										"uploaded_by" =>$user = $this -> session -> userdata('user_id')
										);
							$this -> db -> insert("two_pager",$data);
						}
						else{//file already exists, nothing changes in the db
							$this -> session -> set_flashdata('order_message', "File successfully updated ! ");
						}
						
					}catch(exception $e){
						$this -> session -> set_flashdata('order_message', "An error occured while uploading the file ! ".$e);
						printf($e);die();
					}
					redirect("order/twopager_upload");
					
					
				}
			} else {
				$this -> session -> set_flashdata('order_message', "No file found !");
				$this -> session -> set_flashdata('twopager_upload', 1);
				redirect("order/twopager_upload");
			}
		}
		else if($type=="delete"){
			$id = $delete;
			$this -> db -> where('id', $id);
			$this -> db -> delete('two_pager');
			$this -> session -> set_flashdata('order_message', "File succesfully deleted !");
			$this -> session -> set_flashdata('twopager_upload', 1);
			redirect("order/twopager_upload");
		}
		
		
	}

	public function rationalize_cdrr($cdrr_id, $maps_id) {
		$commodities = $this -> input -> post('commodity');
		$resupply = $this -> input -> post('resupply');
		$old_resupply = $this -> input -> post('new_resupply');
		$commodity_counter = 0;
		$cdrr_array = array();
		foreach ($commodities as $commodity) {
			if ($resupply[$commodity_counter] != null || $resupply[$commodity_counter] != "") {
				if (((int)$resupply[$commodity_counter] - (int)$old_resupply[$commodity_counter]) != 0) {
					$sql = "UPDATE cdrr_item SET resupply='" . $resupply[$commodity_counter] . "' WHERE cdrr_id='$cdrr_id' and drug_id='" . $commodity . "'";
					$this -> db -> query($sql);
					$change = new Resupply_Change();
					$change -> cdrr_id = $cdrr_id;
					$change -> drug_id = $commodity;
					$change -> resupply = $old_resupply[$commodity_counter];
					$change -> save();
				}
			}
			$commodity_counter++;
		}
		$this -> session -> set_flashdata('order_message', "Order Updated Successfully");
		redirect("order/view_order/" . $cdrr_id . "/" . $maps_id);

	}

	public function base_params($data) {
		$data['title'] = "Order Reporting";
		$data['link'] = "order_management";
		$this -> load -> view('template', $data);
	}

}
