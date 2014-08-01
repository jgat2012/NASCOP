<?php
class settings extends MY_Controller {
	var $esm_url = "https://api.kenyapharma.org/";
	var $eid_url = "http://nascop.org/eid/";
	function __construct() {
		parent::__construct();
		$this -> load -> library('github_updater');
		$this -> load -> library('Unzip');
		$this -> load -> library('encrypt');
		$this -> load -> library('Curl');

	}

	public function index() {
		$data['label'] = 'Facility';
		$data['table'] = 'sync_facility';
		$data['actual_page'] = 'NASCOP Facilities';
		$data['hide_side_menu'] = 1;
		$this -> base_params($data);
	}

	public function get($type = "sync_drug") {
		//Column definitions
		if ($type == "sync_drug") {
			$columns = array("id", "name", "abbreviation", "strength", "packsize", "formulation", "unit", "weight", "category_id");
		} else if ($type == "sync_facility") {
			$columns = array("id", "code", "name", "category", "sponsors", "services", "district_id", "ordering", "service_point", "county_id");
		} else if ($type == "sync_regimen") {
			$columns = array("id", "code", "name", "description", "old_code", "category_id");
		} else if ($type == "sync_user") {
			$columns = array("s.id", "name", "email", "role", "username", "status", "facility");
		} else if ($type == "mail_list") {
			$columns = array("s.id", "s.name", "creator_id", "u.Name as creator", "COUNT(mu.id) as total_users", "s.active");
		} else if ($type == "user_emails") {
			$columns = array("s.id", "s.email_address", "COUNT(ml.id) as total_lists", "s.active");
		} else if ($type == "drugcode") {
			$columns = array("id", "name", "unit", "pack_size", "category_id", "arv_drug", "n_map", "e_map", "active");
		} else if ($type == "facilities") {
			$columns = array("s.id", "facilitycode", "name", "type", "c.county", "s.active", "facilitytype", "district", "s.county as county_id", "supported_by", "service_art", "service_pmtct", "service_pep", "supplied_by", "parent", "map", "IF(a.facility_id !='NULL','1','0') as adt_site");
		} else if ($type == "regimen") {
			$columns = array("s.id", "regimen_code", "regimen_desc", "r.Name", "s.category as regimen_category", "line", "type_of_service", "n_map", "e_map", "s.active");
		} else if ($type == "gitlog") {
			$columns = array("s.id", "f.name", "hash_value", "update_time");
			$hash = $this -> github_updater -> get_hash();
		} else if ($type == "escm_facility") {
			$columns = array("id", "code", "name", "category", "sponsors", "services", "district_id", "ordering", "service_point", "county_id");
		} else if ($type == "eid_mail") {
			$columns = array("s.id", "s.email as email_address", "f.name as facility","s.facility as code", "s.active");
		}

		$iDisplayStart = $this -> input -> get_post('iDisplayStart', true);
		$iDisplayLength = $this -> input -> get_post('iDisplayLength', true);
		$iSortCol_0 = $this -> input -> get_post('iSortCol_0', false);
		$iSortingCols = $this -> input -> get_post('iSortingCols', true);
		$sSearch = $this -> input -> get_post('sSearch', true);
		$sEcho = $this -> input -> get_post('sEcho', true);
		$aColumns = $columns;
		$columns = implode(",", $columns);

		// Paging
		if (isset($iDisplayStart) && $iDisplayLength != '-1') {
			$this -> db -> limit($this -> db -> escape_str($iDisplayLength), $this -> db -> escape_str($iDisplayStart));
		}
		// Ordering
		if (isset($iSortCol_0)) {
			for ($i = 0; $i < intval($iSortingCols); $i++) {
				$iSortCol = $this -> input -> get_post('iSortCol_' . $i, true);
				$bSortable = $this -> input -> get_post('bSortable_' . intval($iSortCol), true);
				$sSortDir = $this -> input -> get_post('sSortDir_' . $i, true);

				if ($bSortable == 'true') {
					$this -> db -> order_by($aColumns[intval($this -> db -> escape_str($iSortCol))], $this -> db -> escape_str($sSortDir));
				}
			}
		}
		/*
		 * Filtering
		 */
		if (isset($sSearch) && !empty($sSearch)) {
			for ($i = 0; $i < count($aColumns); $i++) {
				$bSearchable = $this -> input -> get_post('bSearchable_' . $i, true);
				// Individual column filtering
				if (isset($bSearchable) && $bSearchable == 'true') {
					$this -> db -> or_like($aColumns[$i], $this -> db -> escape_like_str($sSearch));
				}
			}
		}

		// Select Data
		$this -> db -> select('SQL_CALC_FOUND_ROWS ' . str_replace(' , ', ' ', implode(', ', $aColumns)), false);
		$this -> db -> select("$columns");
		$this -> db -> from("$type s");
		if ($type == "sync_user") {
			$this -> db -> join("user_facilities uf", "uf.user_id=s.id", "left");
		} else if ($type == "mail_list") {
			$this -> db -> join("users u", "s.creator_id=u.id", "left");
			$this -> db -> join("mail_user mu", "s.id=mu.list_id", "left");
			$this -> db -> group_by("s.id");
		} else if ($type == "user_emails") {
			$this -> db -> join("mail_user mu", "s.id=mu.email_id", "left");
			$this -> db -> join("mail_list ml", "mu.list_id=ml.id", "left");
			$this -> db -> group_by("s.id");
		} else if ($type == "facilities") {
			$this -> db -> join("counties c", "c.id=s.county", "left");
			$this -> db -> join("adt_sites a", "a.facility_id=s.map", "left");
		} else if ($type == "regimen") {
			$this -> db -> join("regimen_category r", "r.id=s.category", "left");
		} else if ($type == "gitlog") {
			$this -> db -> join("facilities f", "f.facilitycode=s.facility_code", "left");
		} else if ($type == "eid_mail") {
			$this -> db -> join("facilities f", "f.facilitycode=s.facility", "left");
		}
		$rResult = $this -> db -> get();
		// Data set length after filtering
		$this -> db -> select('FOUND_ROWS() AS found_rows');
		$iFilteredTotal = $this -> db -> get() -> row() -> found_rows;
		// Total data set length
		$this -> db -> select("id");
		$this -> db -> from("$type");
		$tot_drugs = $this -> db -> get();
		$iTotal = count($tot_drugs -> result_array());

		$output = array('sEcho' => intval($sEcho), 'iTotalRecords' => $iTotal, 'iTotalDisplayRecords' => (int)$iFilteredTotal, 'aaData' => array());
		foreach ($rResult->result_array() as $row) {
			$myrow = array();
			$action_link = "delete";
			$action_icon = "<i class='icon-remove'></i>";
			foreach ($row as $i => $v) {
				if ($i != "id" && $i != "code" && $i != "regimen_category" && $i != "facilitytype" && $i != "district" && $i != "supported_by" && $i != "service_art" && $i != "service_pmtct" && $i != "service_pep" && $i != "supplied_by" && $i != "parent" && $i != "map" && $i != "adt_site" && $i != "line" && $i != "type_of_service" && $i != "arv_drug" && $i != "n_map" && $i != "e_map" && $i != "map" && $i != "creator_id" && $i != "facility" && $i != "category_id" && $i != "status" && $i != "old_code" && $i != "district_id" && $i != "ordering" && $i != "service_point" && $i != "county_id" && $i != "sponsors" && $i != "active") {
					$myrow[] = $v;
				} else {
					if ($i == "id") {
						$id = $v;
					}
					if($type == "eid_mail" && $i=="facility"){
						$myrow[] = $v;
					}
				}
				//Delete/enable actions
				if ($type == "sync_user" && $i == "status" && $v == "N") {
					$action_link = "enable";
					$action_icon = "<i class='icon-ok'></i>";
				} else if ($type == "sync_drug" && $i == "category_id" && $v == 14) {
					$action_link = "enable";
					$action_icon = "<i class='icon-ok'></i>";
				} else if ($type == "sync_regimen" && $i == "category_id" && $v == 15) {
					$action_link = "enable";
					$action_icon = "<i class='icon-ok'></i>";
				} else if ($type == "mail_list" && $i == "active" && $v == 0) {
					$action_link = "enable";
					$action_icon = "<i class='icon-ok'></i>";
				} else if ($type == "user_emails" && $i == "active" && $v == 0) {
					$action_link = "enable";
					$action_icon = "<i class='icon-ok'></i>";
				} else if ($type == "drugcode" && $i == "active" && $v == 0) {
					$action_link = "enable";
					$action_icon = "<i class='icon-ok'></i>";
				} else if ($type == "facilities" && $i == "active" && $v == 0) {
					$action_link = "enable";
					$action_icon = "<i class='icon-ok'></i>";
				} else if ($type == "regimen" && $i == "active" && $v == 0) {
					$action_link = "enable";
					$action_icon = "<i class='icon-ok'></i>";
				} else if ($type == "gitlog" && $i == "hash_value") {
					if ($hash == "") {
						$status = "<div class='alert-info'>cannot connect to server</div>";
					} else if ($hash == $v) {
						$status = "<div class='alert-success'>up to update</div>";
					} else {
						$status = "<div class='alert-error'>need to update</div>";
					}
					$myrow[] = $status;
				} else if ($type == "eid_mail" && $i == "active" && $v == 0) {
					$action_link = "enable";
					$action_icon = "<i class='icon-ok'></i>";
				}
			}

			if ($type == "user_emails") {
				$lists = Mail_User::getLists($id);
				$mylist = array();
				foreach ($lists as $list) {
					$mylist[] = $list['list_id'];
				}
				$mylist = implode(",", $mylist);
				$row["mail_list"] = json_encode($mylist);
			}
			$links = "";
			if ($action_link == "delete") {
				//for eid_mail replace facility name with code for edit function
				if ($type == "eid_mail") {
                   $row['facility']=$row['code'];
                   unset($row['code']);
				}
				$links = "<a href='" . site_url("settings/modal") . "/" . $type . "' item_id='" . $id . "' class='edit_item' role='button' data-toggle='modal' data-mydata='" . json_encode($row) . "'><i class='icon-pencil'></i></a>";
				$links .= "  ";
				if ($type != "sync_facility" || $type != "gitlog") {
					$links .= anchor("settings/" . $action_link . "/" . $type . "/" . $id, $action_icon, array("class" => "delete"));
				}
			} else {
				if ($type != "sync_facility" || $type != "gitlog") {
					$links .= anchor("settings/" . $action_link . "/" . $type . "/" . $id, $action_icon, array("class" => "delete"));
				}
			}
			$myrow[] = $links;
			$output['aaData'][] = $myrow;
		}
		echo json_encode($output);
	}

	public function modal($type = "sync_drug") {
		$content = "";
		$group_div = "<div class='control-group'>";
		$control_div = "<div class='controls'>";
		$close_div = "</div>";
		if ($type == "sync_drug") {
			$inputs = array("name" => "name", "abbreviation" => "abbreviation", "strength" => "strength", "packsize" => "packsize", "formulation" => "formulation", "unit" => "unit", "weight" => "weight", "Category" => "category_id");
		} else if ($type == "sync_facility") {
			$inputs = array("code" => "code", "name" => "name", "category" => "category", "sponsors" => "sponsors", "services" => "services", "district" => "district_id", "is ordering point?" => "ordering", " is service point?" => "service_point", "county" => "county_id");
		} else if ($type == "sync_regimen") {
			$inputs = array("code" => "code", "name" => "name", "description" => "description", "old_code" => "old_code");
		} else if ($type == "sync_user") {
			$inputs = array("name" => "name", "email" => "email", "role" => "role", "phone" => "username", "User Facilities" => "facilities");
		} else if ($type == "mail_list") {
			$inputs = array("List Name" => "name", " " => "creator_id");
		} else if ($type == "user_emails") {
			$inputs = array("Email Address" => "email_address", "Mailing List" => "mail_list");
		} else if ($type == "drugcode") {
			$inputs = array("name" => "name", "unit" => "unit", "pack size" => "pack_size", "category" => "category_id", "is ARV drug?" => "arv_drug", "nascop drug" => "n_map", "escm drug" => "e_map");
		} else if ($type == "facilities") {
			$inputs = array("code" => "facilitycode", "name" => "name", "category" => "type", "type" => "facilitytype", "is ADT site?" => "adt_site", "county" => "county_id", "district" => "district", "supplier" => "supplied_by", "supporter" => "supported_by", "service ART" => "service_art", "service PEP" => "service_pep", "service PMTCT" => "service_pmtct", "parent" => "parent", "mapped facility" => "map");
		} else if ($type == "regimen") {
			$inputs = array("code" => "regimen_code", "name" => "regimen_desc", "category" => "regimen_category", "Line" => "line", "service" => "type_of_service", "nascop regimen" => "n_map", "escm regimen" => "e_map");
		} else if ($type == "escm_facility") {
			$inputs = array("code" => "code", "name" => "name", "category" => "category", "sponsors" => "sponsors", "services" => "services", "district" => "district_id", "is ordering point?" => "ordering", " is service point?" => "service_point", "county" => "county_id");
		} else if ($type == "eid_mail") {
			$inputs = array("email address" => "email_address", "facility" => "facility");
		}

		foreach ($inputs as $text => $input) {
			$content .= $group_div;
			$label = "<label class='control-label'>" . $text . "</label>";
			$content .= $label;
			$content .= $control_div;
			$textfield = "<input type='text' id='" . $type . "_" . $input . "' name='" . $input . "'/>";
			if ($input == "profile_id") {
				$textfield = "<input type='text' id='" . $type . "_" . $input . "' name='" . $text . "'/>";
			} else if ($input == "category_id") {
				$textfield = "<select id='" . $type . "_" . $input . "' name='" . $text . "'>";
				$textfield .= "<option value='0' selected='selected'>--Select One--</option>";
				$textfield .= "<option value='1'>ART Adults</option>";
				$textfield .= "<option value='2'>ART Paeds</option>";
				$textfield .= "<option value='3'>OI Drugs </option>";
				$textfield .= "</select>";
			} else if ($input == "name") {
				$textfield = "<input type='text' required='required' id='" . $type . "_" . $input . "' name='" . $input . "'/>";
			} else if ($input == "username") {
				$textfield = "<input type='text' id='" . $type . "_" . $input . "' name='" . $input . "' class='phone'/>";
			} else if ($input == "creator_id") {
				$textfield = "<input type='hidden' id='" . $type . "_" . $input . "' name='" . $input . "'/>";
			} else if ($input == "email_address") {
				$textfield = "<input type='email' required='required' id='" . $type . "_" . $input . "' name='" . $input . "'/>";
			} else if ($input == "district_id") {
				$textfield = "<select id='" . $type . "_" . $input . "' name='" . $input . "'>";
				$textfield .= "<option value='0' selected='selected'>--Select One--</option>";
				$districts = District::getActive();
				foreach ($districts as $district) {
					$textfield .= "<option value='" . $district['id'] . "'>" . $district['Name'] . "</option>";
				}
				$textfield .= "</select>";
			} else if ($input == "n_map" && $type == "drugcode") {
				$textfield = "<select id='" . $type . "_" . $input . "' name='" . $input . "'>";
				$textfield .= "<option value='0' selected='selected'>--Select One--</option>";
				$drugs = Sync_Drug::getActive();
				foreach ($drugs as $drug) {
					$textfield .= "<option value='" . $drug['id'] . "'>" . $drug['name'] . "</option>";
				}
				$textfield .= "</select>";
			} else if ($input == "e_map" && $type == "drugcode") {
				$textfield = "<select id='" . $type . "_" . $input . "' name='" . $input . "'>";
				$textfield .= "<option value='0' selected='selected'>--Select One--</option>";
				$drugs = Escm_Drug::getActive();
				foreach ($drugs as $drug) {
					$textfield .= "<option value='" . $drug['id'] . "'>" . $drug['name'] . "</option>";
				}
				$textfield .= "</select>";
			} else if ($input == "n_map" && $type == "regimen") {
				$textfield = "<select id='" . $type . "_" . $input . "' name='" . $input . "' class='span5'>";
				$textfield .= "<option value='0' selected='selected'>--Select One--</option>";
				$regimens = Sync_Regimen::getAllHydrated();
				foreach ($regimens as $regimen) {
					$textfield .= "<option value='" . $regimen['id'] . "'>" . $regimen['code'] . " | " . $regimen['name'] . "</option>";
				}
				$textfield .= "</select>";
			} else if ($input == "e_map" && $type == "regimen") {
				$textfield = "<select id='" . $type . "_" . $input . "' name='" . $input . "' class='span5'>";
				$textfield .= "<option value='0' selected='selected'>--Select One--</option>";
				$regimens = Escm_Regimen::getAllHydrated();
				foreach ($regimens as $regimen) {
					$textfield .= "<option value='" . $regimen['id'] . "'>" . $regimen['code'] . " | " . $regimen['name'] . "</option>";
				}
				$textfield .= "</select>";
			} else if ($input == "regimen_category" && $type == "regimen") {
				$textfield = "<select id='" . $type . "_" . $input . "' name='" . $input . "' class='span5'>";
				$textfield .= "<option value='0' selected='selected'>--Select One--</option>";
				$regimens = Regimen_Category::getAllHydrate();
				foreach ($regimens as $regimen) {
					$textfield .= "<option value='" . $regimen['id'] . "'>" . $regimen['Name'] . "</option>";
				}
				$textfield .= "</select>";
			} else if ($input == "type_of_service" && $type == "regimen") {
				$textfield = "<select id='" . $type . "_" . $input . "' name='" . $input . "' class='span5'>";
				$textfield .= "<option value='0' selected='selected'>--Select One--</option>";
				$regimens = Regimen_Service_Type::getHydratedAll();
				foreach ($regimens as $regimen) {
					$textfield .= "<option value='" . $regimen['id'] . "'>" . $regimen['Name'] . "</option>";
				}
				$textfield .= "</select>";
			} else if ($input == "county_id") {
				$textfield = "<select id='" . $type . "_" . $input . "' name='" . $input . "'>";
				$textfield .= "<option value='0' selected='selected'>--Select One--</option>";
				$counties = Counties::getActive();
				foreach ($counties as $county) {
					$textfield .= "<option value='" . $county['id'] . "'>" . $county['county'] . "</option>";
				}
				$textfield .= "</select>";
			} else if ($input == "facilitytype" && $type == "facilities") {
				$textfield = "<select id='" . $type . "_" . $input . "' name='" . $input . "' class='span5'>";
				$textfield .= "<option value='0' selected='selected'>--Select One--</option>";
				$types = Facility_Types::getActive();
				foreach ($types as $ftype) {
					$textfield .= "<option value='" . $ftype['id'] . "'>" . $ftype['Name'] . "</option>";
				}
				$textfield .= "</select>";
			} else if (($input == "parent" || $input=="facility") && ($type == "facilities" || $type=="eid_mail")) {
				$textfield = "<select id='" . $type . "_" . $input . "' name='" . $input . "' class='span8'>";
				$textfield .= "<option value='0' selected='selected'>--Select One--</option>";
				$facilities = Facilities::getActive();
				foreach ($facilities as $facility) {
					$textfield .= "<option value='" . $facility['facilitycode'] . "'>" . $facility['name'] . "</option>";
				}
				$textfield .= "</select>";
			} else if ($input == "map" && $type == "facilities") {
				$textfield = "<select id='" . $type . "_" . $input . "' name='" . $input . "' class='span10'>";
				$textfield .= "<option value='0' selected='selected'></option>";
				$facilities1 = Sync_Facility::getAllHydrated();
				$facilities2 = Escm_Facility::getAllHydrated();
				$facilities3 = Satellites::getAllHydrated();
				$facilities4 = array_merge($facilities1, $facilities2);
				$facilities = array_merge($facilities3, $facilities4);
				sort($facilities);
				foreach ($facilities as $facility) {
					$textfield .= "<option value='" . $facility['id'] . "'>" . $facility['name'] . "</option>";
				}
				$textfield .= "</select>";
			} else if ($input == "county") {
				$textfield = "<select id='" . $type . "_" . $input . "' name='" . $input . "'>";
				$textfield .= "<option value='0' selected='selected'>--Select One--</option>";
				$counties = Counties::getActive();
				foreach ($counties as $county) {
					$textfield .= "<option value='" . $county['id'] . "'>" . $county['county'] . "</option>";
				}
				$textfield .= "</select>";
			} else if ($input == "district") {
				$textfield = "<select id='" . $type . "_" . $input . "' name='" . $input . "'>";
				$textfield .= "<option value='0' selected='selected'>--Select One--</option>";
				$districts = District::getActive();
				foreach ($districts as $district) {
					$textfield .= "<option value='" . $district['id'] . "'>" . $district['Name'] . "</option>";
				}
				$textfield .= "</select>";
			} else if ($input == "supplied_by") {
				$textfield = "<select id='" . $type . "_" . $input . "' name='" . $input . "'>";
				$textfield .= "<option value='0' selected='selected'>--Select One--</option>";
				$suppliers = Suppliers::getActive();
				foreach ($suppliers as $supplier) {
					$textfield .= "<option value='" . $supplier['id'] . "'>" . $supplier['name'] . "</option>";
				}
				$textfield .= "</select>";
			} else if ($input == "supported_by") {
				$textfield = "<select id='" . $type . "_" . $input . "' name='" . $input . "'>";
				$textfield .= "<option value='0' selected='selected'>--Select One--</option>";
				$supporters = Supporter::getAllActive();
				foreach ($supporters as $supporter) {
					$textfield .= "<option value='" . $supporter['id'] . "'>" . $supporter['Name'] . "</option>";
				}
				$textfield .= "</select>";
			} else if ($input == "service_art") {
				$textfield = "<select id='" . $type . "_" . $input . "' name='" . $input . "'>";
				$textfield .= "<option value='0' selected='selected'>NO</option>";
				$textfield .= "<option value='1'>YES</option>";
				$textfield .= "</select>";
			} else if ($input == "service_pep") {
				$textfield = "<select id='" . $type . "_" . $input . "' name='" . $input . "'>";
				$textfield .= "<option value='0' selected='selected'>NO</option>";
				$textfield .= "<option value='1'>YES</option>";
				$textfield .= "</select>";
			} else if ($input == "service_pmtct") {
				$textfield = "<select id='" . $type . "_" . $input . "' name='" . $input . "'>";
				$textfield .= "<option value='0' selected='selected'>NO</option>";
				$textfield .= "<option value='1'>YES</option>";
				$textfield .= "</select>";
			} else if ($input == "ordering") {
				$textfield = "<select id='" . $type . "_" . $input . "' name='" . $input . "'>";
				$textfield .= "<option value='0' selected='selected'>NO</option>";
				$textfield .= "<option value='1'>YES</option>";
				$textfield .= "</select>";
			} else if ($input == "arv_drug") {
				$textfield = "<select id='" . $type . "_" . $input . "' name='" . $input . "'>";
				$textfield .= "<option value='0' selected='selected'>NO</option>";
				$textfield .= "<option value='1'>YES</option>";
				$textfield .= "</select>";
			} else if ($input == "adt_site") {
				$textfield = "<select id='" . $type . "_" . $input . "' name='" . $input . "'>";
				$textfield .= "<option value='0' selected='selected'>NO</option>";
				$textfield .= "<option value='1'>YES</option>";
				$textfield .= "</select>";
			} else if ($input == "service_point") {
				$textfield = "<select id='" . $type . "_" . $input . "' name='" . $input . "'>";
				$textfield .= "<option value='0' selected='selected'>NO</option>";
				$textfield .= "<option value='1'>YES</option>";
				$textfield .= "</select>";
			} else if ($input == "facilities") {
				$textfield = "<select id='" . $type . "_" . $input . "' name='" . $input . "[]' multiple='multiple' style='width:300px;'>";
				$facilities = Sync_Facility::getAllHydrated();
				foreach ($facilities as $facility) {
					$textfield .= "<option value='" . $facility['id'] . "'>" . " " . $facility['name'] . "</option>";
				}
				$textfield .= "</select><input type='hidden' id='" . $input . "_holder' name='" . $input . "_holder' />";
			} else if ($input == "mail_list") {
				$textfield = "<select id='" . $type . "_" . $input . "' name='" . $input . "[]' multiple='multiple' style='width:300px;'>";
				$lists = Mail_List::getActive();
				foreach ($lists as $list) {
					$textfield .= "<option value='" . $list -> id . "'>" . " " . $list -> name . "</option>";
				}
				$textfield .= "</select><input type='hidden' id='" . $input . "_holder' name='" . $input . "_holder' />";
			}
			$content .= $textfield;
			$content .= $close_div;
			$content .= $close_div;
		}
		$this -> session -> set_userdata("nav_link", $type);
		echo $content;
	}

	public function save($type = "sync_drug", $id = null) {
		$save_data = array();
		$success_class = "<div class='alert alert-success'>";
		$error_class = "<div class='alert alert-error'>";
		$info_class = "<div class='alert alert-info'>";
		$close_btn_div = "<button type='button' class='close' data-dismiss='alert'>&times;</button>";
		$message = "";
		$close_div = "</div>";

		if ($type == "sync_drug") {
			$inputs = array("name" => "name", "abbreviation" => "abbreviation", "strength" => "strength", "packsize" => "packsize", "formulation" => "formulation", "unit" => "unit", "weight" => "weight","category_id" => "Category");
		} else if ($type == "sync_facility") {
			$inputs = array("code" => "code", "name" => "name", "category" => "category", "sponsors" => "sponsors", "services" => "services", "district_id" => "district_id", "ordering" => "ordering", "service_point" => "service_point", "county_id" => "county_id");
		} else if ($type == "sync_regimen") {
			$inputs = array("code" => "code", "name" => "name", "description" => "description");
		} else if ($type == "sync_user") {
			$inputs = array("name" => "name", "email" => "email", "role" => "role", "username" => "username", "facility_list" => "facilities_holder");
		} else if ($type == "mail_list") {
			$inputs = array("name" => "name", "creator_id" => "creator_id");
		} else if ($type == "user_emails") {
			$inputs = array("email_address" => "email_address", "mail_list" => "mail_list_holder");
		} else if ($type == "drugcode") {
			$inputs = array("name" => "name", "unit" => "unit", "pack_size" => "pack_size", "category_id" => "category", "arv_drug" => "arv_drug", "n_map" => "n_map", "e_map" => "e_map");
		} else if ($type == "facilities") {
			$inputs = array("facilitycode" => "facilitycode", "name" => "name", "type" => "type", "facilitytype" => "facilitytype", "adt_site" => "adt_site", "county" => "county_id", "district" => "district", "supplied_by" => "supplied_by", "supported_by" => "supported_by", "service_art" => "service_art", "service_pep" => "service_pep", "service_pmtct" => "service_pmtct", "parent" => "parent", "map" => "map");
		} else if ($type == "regimen") {
			$inputs = array("regimen_code" => "regimen_code", "regimen_desc" => "regimen_desc", "category" => "regimen_category", "line" => "line", "type_of_service" => "type_of_service", "n_map" => "n_map", "e_map" => "e_map");
		} else if ($type == "escm_facility") {
			$inputs = array("code" => "code", "name" => "name", "category" => "category", "sponsors" => "sponsors", "services" => "services", "district_id" => "district_id", "ordering" => "ordering", "service_point" => "service_point", "county_id" => "county_id");
		} else if ($type == "eid_mail") {
			$inputs = array("email" => "email_address", "facility" => "facility");
		} 

		foreach ($inputs as $index => $input) {
			if ($index == "facility_list") {
				if ($input == null) {
					$facility_list = "";
				} else {
					$facility_list = json_encode($this -> input -> post($input));
				}
			} else if ($index == "mail_list") {
				if ($input == null) {
					$mail_list = "";
				} else {
					$mail_list = $this -> input -> post($input);
					$mail_list = explode(",", $mail_list);
				}
			} else if ($index == "email" && $type=="sync_user" && $id == null) {
				$password = "";
				$characters = strtoupper("abcdefghijklmnopqrstuvwxyz");
				$characters = $characters . 'abcdefghijklmnopqrstuvwxyz0123456789';
				$password_length = 6;
				$string = '';
				for ($i = 0; $i < $password_length; $i++) {
					$password .= $characters[rand(0, strlen($characters) - 1)];
				}
				$save_data[$index] = $this -> input -> post($input);
				$save_data["password"] = md5($password);
				$this -> send_password($this -> input -> post($input), $password);

			} else if ($index == "creator_id" && $id == null) {
				$save_data[$index] = $this -> session -> userdata("user_id");
			} else if ($input == "adt_site" && $this -> input -> post("supplied_by") != 0) {
				$facility_id = $this -> input -> post("map");
				$sql = "DELETE FROM adt_sites WHERE facility_id='$facility_id'";
				$this -> db -> query($sql);
				if ($this -> input -> post("adt_site") == 1) {
					$supplied_by = $this -> input -> post("supplied_by");
					$this -> db -> insert("adt_sites", array("facility_id" => $facility_id, "pipeline" => $supplied_by));
				}
			} else {
				$save_data[$index] = $this -> input -> post($input);
			}
		}

		if ($type == "facilities") {
			unset($save_data['adt_site']);
		}

		//insert or update
		if ($id == null) {
			$this -> db -> insert($type, $save_data);
			$message = "<b>Saved " . $type . "!</b>  You successfully saved.";
			$content = $success_class;
			if ($type == "sync_user") {
				$user_id = $this -> db -> insert_id();
				$this -> db -> insert("user_facilities", array("user_id" => $user_id, "facility" => $facility_list));
			} else if ($type == "user_emails") {
				$email_id = $this -> db -> insert_id();
				foreach ($mail_list as $mail) {
					$this -> db -> insert("mail_user", array("email_id" => $user_id, "list_id" => $mail));
				}
			}
		} else {
			$this -> db -> where('id', $id);
			$this -> db -> update($type, $save_data);
			$message = "<b>Updated " . $type . "!</b> You successfully updated.";
			$content = $info_class;
			if ($type == "sync_user") {
				$user_id = $id;
				$results = User_Facilities::getHydratedFacilityList($user_id);
				if ($results) {
					$this -> db -> where('user_id', $user_id);
					$this -> db -> update("user_facilities", array("user_id" => $user_id, "facility" => $facility_list));
				} else {
					$this -> db -> insert("user_facilities", array("user_id" => $user_id, "facility" => $facility_list));
				}
			} else if ($type == "user_emails") {
				$email_id = $id;
				$sql = "DELETE FROM mail_user WHERE email_id='$email_id'";
				$this -> db -> query($sql);
				if (!empty($mail_list) || $mail_list != "") {
					foreach ($mail_list as $mail) {
						$this -> db -> insert("mail_user", array("email_id" => $email_id, "list_id" => $mail));
					}
				}
			}
		}

		$content .= $close_btn_div;
		$content .= $message;
		$content .= $close_div;
		$this -> session -> set_flashdata("alert_message", $content);
		$this -> session -> set_userdata("nav_link", $type);
		redirect("settings");

	}
	public function enable($type = "sync_drug", $id = null) {
		$info_class = "<div class='alert alert-info'>";
		$error_class = "<div class='alert alert-error'>";
		$close_btn_div = "<button type='button' class='close' data-dismiss='alert'>&times;</button>";

		if ($id != null) {
			if ($type == "sync_drug") {
				$columns = array("category_id" => 0);
			} else if ($type == "sync_regimen") {
				$columns = array("category_id" => 0);
			} else if ($type == "sync_user") {
				$columns = array("status" => "A");
			} else if ($type == "mail_list" || $type == "user_emails" || $type == "drugcode" || $type == "facilities" || $type == "regimen" || $type == "eid_mail") {
				$columns = array("active" => "1");
			}
			$this -> db -> where('id', $id);
			$this -> db -> update($type, $columns);
			$message = "<b>Enabled " . $type . "!</b> You successfully enabled.";
			$content = $info_class;
			$content .= $close_btn_div;
			$content .= $message;
			$content .= $close_div;
		} else {
			$message = "<b>Failed " . $type . "!</b> You failed to enable.";
			$content = $error_class;
			$content .= $close_btn_div;
			$content .= $message;
			$content .= $close_div;
		}
		$this -> session -> set_flashdata("alert_message", $content);
		$this -> session -> set_userdata("nav_link", $type);
		redirect("settings");
	}

	public function delete($type = "sync_drug", $id = null) {
		$info_class = "<div class='alert alert-info'>";
		$error_class = "<div class='alert alert-error'>";
		$close_btn_div = "<button type='button' class='close' data-dismiss='alert'>&times;</button>";

		if ($id != null) {
			if ($type == "sync_drug") {
				$columns = array("category_id" => 14);
			} else if ($type == "sync_regimen") {
				$columns = array("category_id" => 15);
			} else if ($type == "sync_user") {
				$columns = array("status" => "N");
			} else if ($type == "mail_list" || $type == "user_emails" || $type == "drugcode" || $type == "facilities" || $type == "regimen" || $type == "eid_mail") {
				$columns = array("active" => "0");
			}
			$this -> db -> where('id', $id);
			$this -> db -> update($type, $columns);
			$message = "<b>Deleted " . $type . "!</b> You successfully deleted.";
			$content = $error_class;
			$content .= $close_btn_div;
			$content .= $message;
			$content .= $close_div;
		} else {
			$message = "<b>Failed " . $type . "!</b> You failed to delete.";
			$content = $info_class;
			$content .= $close_btn_div;
			$content .= $message;
			$content .= $close_div;
		}
		$this -> session -> set_flashdata("alert_message", $content);
		$this -> session -> set_userdata("nav_link", $type);
		redirect("settings");
	}

	public function api_sync() {
		$log = "";
		$info_class = "<div class='alert alert-info'>";
		$error_class = "<div class='alert alert-error'>";
		$close_btn_div = "<button type='button' class='close' data-dismiss='alert'>&times;</button>";
		$close_div = "</div>";

		//Link array
		$links = array();
		$links['escm_drug'] = "drugs";
		$links['escm_regimen'] = "regimen";

		$curl = new Curl();
		$url = $this -> esm_url;

		$username = "kmarete";
		$password = "poltergeist";
		$curl -> setBasicAuthentication($username, $password);
		$curl -> setOpt(CURLOPT_RETURNTRANSFER, TRUE);
		$curl -> setOpt(CURLOPT_SSL_VERIFYPEER, FALSE);

		foreach ($links as $table => $link) {
			$target_url = $url . $link;
			$curl -> get($target_url);
			if ($curl -> error) {
				$curl -> error_code;
				$log .= "Error " . $curl -> error_code . " ! Sync Failed<br/>";
			} else {
				$main_array = json_decode($curl -> response, TRUE);
				$this -> db -> query("TRUNCATE $table");
				$this -> db -> insert_batch($table, $main_array);
				$log .= "Sync " . $table . "! Synched Succesful<br/>";
			}
		}
		$content = $info_class;
		$content .= $close_btn_div;
		$content .= $log;
		$content .= $close_div;
		$this -> session -> set_flashdata('alert_message', $content);
	}

	public function get_updates() {
		ini_set("max_execution_time", "1000000");
		$log = "";
		$info_class = "<div class='alert alert-info'>";
		$error_class = "<div class='alert alert-error'>";
		$close_btn_div = "<button type='button' class='close' data-dismiss='alert'>&times;</button>";
		$close_div = "</div>";

		$curl = new Curl();
		$url = $this -> esm_url;

		$lists = Escm_Facility::getAllNotHydrated();
		foreach ($lists as $list) {
			$facility_id = $list -> id;
			$links[] = "facility/" . $facility_id . "/cdrr";
			$links[] = "facility/" . $facility_id . "/maps";
		}
		$username = "kmarete";
		$password = "poltergeist";
		$curl -> setBasicAuthentication($username, $password);
		$curl -> setOpt(CURLOPT_RETURNTRANSFER, TRUE);
		$curl -> setOpt(CURLOPT_SSL_VERIFYPEER, FALSE);

		foreach ($links as $link) {
			$target_url = $url . $link;
			$curl -> get($target_url);
			if ($curl -> error) {
				$curl -> error_code;
				$log = "Error: " . $curl -> error_code . "<br/>";
			} else {
				$main_array = json_decode($curl -> response, TRUE);
				$clean_data = array();

				$current_month_start = date('Y-m-01');
				$current_month_end = date('Y-m-t');

				$one_current_month_start = date('Y-m-d', strtotime($current_month_start . "-1 month"));
				$one_current_month_end = date('Y-m-d', strtotime($current_month_end . "-1 month"));

				$two_current_month_start = date('Y-m-d', strtotime($current_month_start . "-2 months"));
				$two_current_month_end = date('Y-m-d', strtotime($current_month_end . "-2 months"));

				$three_current_month_start = date('Y-m-d', strtotime($current_month_start . "-3 months"));
				$three_current_month_end = date('Y-m-d', strtotime($current_month_end . "-3 months"));

				$four_current_month_start = date('Y-m-d', strtotime($current_month_start . "-4 months"));
				$five_current_month_start = date('Y-m-d', strtotime($current_month_start . "-5 months"));
				$six_current_month_start = date('Y-m-d', strtotime($current_month_start . "-6 months"));

				foreach ($main_array as $main) {
					if ($main['code'] == "D-CDRR" || $main['code'] == "F-CDRR_units" || $main['code'] == "F-CDRR_packs") {
						$type = "cdrr";
					} else {
						$type = "maps";
					}
					if (is_array($main)) {
						if (!empty($main)) {
							if ($main['period_begin'] == $current_month_start || $main['period_begin'] == $one_current_month_start || $main['period_begin'] == $two_current_month_start || $main['period_begin'] == $three_current_month_start || $main['period_begin'] == $four_current_month_start || $main['period_begin'] == $five_current_month_start || $main['period_begin'] == $six_current_month_start) {
								$this -> extract_order($type, array($main), $main['id']);
							}
						}
					}
				}
				$log = "Sync Complete";
			}
		}
		$content = $info_class;
		$content .= $close_btn_div;
		$content .= $log;
		$content .= $close_div;
		$this -> session -> set_flashdata('alert_message', $content);
	}

	public function eid_sync() {
		$log = "";
		$info_class = "<div class='alert alert-info'>";
		$error_class = "<div class='alert alert-error'>";
		$close_btn_div = "<button type='button' class='close' data-dismiss='alert'>&times;</button>";
		$close_div = "</div>";

		//Link array
		$links = array();
		$links['eid_master'] = "heiapi.php";

		$curl = new Curl();
	    $url = $this -> eid_url;

		foreach ($links as $table => $link) {
			$target_url = $url . $link;
			$curl -> get($target_url);
			if ($curl -> error) {
				$curl -> error_code;
				$log .= "Error " . $curl -> error_code . " ! Sync Failed<br/>";
			} else {
				$main_array = json_decode($curl -> response, TRUE);
				//clear table
				$this -> db -> query("TRUNCATE $table");

				foreach ($main_array as $main) {
					foreach ($main as $post) {
						$this -> db -> insert($table, $post['post']);
					}
				}
				$log .= "Sync " . $table . "! Synched Succesful<br/>";
			}
		}
		$content = $info_class;
		$content .= $close_btn_div;
		$content .= $log;
		$content .= $close_div;
		$this -> session -> set_flashdata('alert_message', $content);
	}

	public function extract_order($type = "cdrr", $responses = array(), $id = "") {
		/*Steps
		 * 1.Check if escm id is mapped to nascop id return mapped id or null if none
		 * 2.Get nascop id if exits ad delete it from cdrr/maps
		 * 3.Clean ids of array response
		 * 4.a)If nascop id is not null Attach nascop id to id column as well as cdrr_id and maps_id
		 *   b)if nascop is null save order as new and user last insert id as cdrr/maps id
		 * 5.Map the escm_id to the nascop id
		 */
		if ($id != "") {
			$escm_id = $id;
			$nascop_id = $this -> check_map($escm_id, $type);
			if ($nascop_id != null) {
				$this -> delete_order($type, $nascop_id, 1);
			}
			$responses = $this -> clean_index($type, $responses);
			$responses = array($responses);
		}
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
			if ($nascop_id != null) {
				$cdrr["id"] = $nascop_id;
				$cdrr_id = $nascop_id;
				//Insert the cdrr and use nascop id as cdrr id
				$this -> db -> insert('cdrr', $cdrr);
			} else {
				//Insert the cdrr and retrieve the auto_id assigned to it,this will be the cdrr_id
				$this -> db -> insert('cdrr', $cdrr);
				$cdrr_id = $this -> db -> insert_id();
			}
			$this -> map_order($escm_id, $cdrr_id, $type);

			//Loop through cdrr_log and add cdrr_id
			foreach ($cdrr_log as $index => $my_log) {
				foreach ($my_log as $counter => $log) {
					foreach ($log as $ind => $lg) {
						if ($ind == "cdrr_id") {
							$temp_log[$counter]['cdrr_id'] = $cdrr_id;
						} else {
							$temp_log[$counter][$ind] = $lg;
						}
					}
				}
			}

			if (is_array($temp_log)) {
				if (!empty($temp_log)) {
					$this -> db -> insert_batch('cdrr_log', $temp_log);
				}
			}

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
			if (is_array($temp_items)) {
				if (!empty($temp_items)) {
					$this -> db -> insert_batch('cdrr_item', $temp_items);
				}
			}
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

			if ($nascop_id != null) {
				$maps["id"] = $nascop_id;
				$maps_id = $nascop_id;
				//Insert the maps and use nascop id as maps id
				$this -> db -> insert('maps', $maps);
			} else {
				//Insert the maps and retrieve the auto_id assigned to it,this will be the cdrr_id
				$this -> db -> insert('maps', $maps);
				$maps_id = $this -> db -> insert_id();
			}
			$this -> map_order($escm_id, $maps_id, $type);

			//attach maps id to maps_log
			foreach ($maps_log as $index => $my_log) {
				foreach ($my_log as $counter => $log) {
					foreach ($log as $ind => $lg) {
						if ($ind == "maps_id") {
							$maps_log[$counter]['maps_id'] = $maps_id;
						} else {
							$maps_log[$counter][$ind] = $lg;
						}
					}
				}
			}
			if (is_array($maps_log)) {
				if (!empty($maps_log)) {
					$this -> db -> insert_batch('maps_log', $maps_log);
				}
			}

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

			if (is_array($maps_items)) {
				if (!empty($maps_items)) {
					$this -> db -> insert_batch('maps_item', $maps_items);
				}
			}
		}
	}

	public function clean_index($type = "cdrr", $responses = array()) {
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
						if ($index == "id") {
							$cdrr[$index] = "";
						} else {
							$cdrr[$index] = $main;
						}
					}
				}
			}
			$my_array = $cdrr;

			//Loop through cdrr_item and add cdrr_id
			foreach ($cdrr_items as $index => $cdrr_item) {
				foreach ($cdrr_item as $counter => $items) {
					foreach ($items as $ind => $item) {
						if ($ind == "id") {
							$temp_items[$counter]['id'] = "";
						} else if ($ind == "cdrr_id") {
							$temp_items[$counter]['cdrr_id'] = "";
						} else {
							$temp_items[$counter][$ind] = $item;
						}
					}
				}
			}
			$my_array['ownCdrr_item'] = $temp_items;

			//Loop through cdrr_log and add cdrr_id
			foreach ($cdrr_log as $index => $my_log) {
				foreach ($my_log as $counter => $log) {
					foreach ($log as $ind => $lg) {
						if ($ind == "id") {
							$temp_log[$counter]['id'] = "";
						} else if ($ind == "cdrr_id") {
							$temp_log[$counter]['cdrr_id'] = "";
						} else {
							$temp_log[$counter][$ind] = $lg;
						}
					}
				}
			}
			$my_array['ownCdrr_log'] = $temp_log;

		} else if ($type == "maps") {
			$map = array();
			$map_items = array();
			$map_log = array();
			$temp_items = array();
			$temp_log = array();
			foreach ($responses as $response) {
				foreach ($response as $index => $main) {
					if ($index == "ownMaps_item") {
						$map_items[$index] = $main;
					} else if ($index == "ownMaps_log") {
						$map_log[$index] = $main;
					} else {
						if ($index == "id") {
							$map[$index] = "";
						} else {
							$map[$index] = $main;
						}
					}
				}
			}
			$my_array = $map;

			//Loop through cdrr_item and add cdrr_id
			foreach ($map_items as $index => $map_item) {
				foreach ($map_item as $counter => $items) {
					foreach ($items as $ind => $item) {
						if ($ind == "id") {
							$temp_items[$counter]['id'] = "";
						} else if ($ind == "maps_id") {
							$temp_items[$counter]['maps_id'] = "";
						} else {
							$temp_items[$counter][$ind] = $item;
						}
					}
				}
			}
			$my_array['ownMaps_item'] = $temp_items;

			//Loop through cdrr_log and add cdrr_id
			foreach ($map_log as $index => $my_log) {
				foreach ($my_log as $counter => $log) {
					foreach ($log as $ind => $lg) {
						if ($ind == "id") {
							$temp_log[$counter]['id'] = "";
						} else if ($ind == "maps_id") {
							$temp_log[$counter]['maps_id'] = "";
						} else {
							$temp_log[$counter][$ind] = $lg;
						}
					}
				}
			}
			$my_array['ownMaps_log'] = $temp_log;
		}
		return $my_array;
	}

	public function check_map($escm_id, $type = "cdrr") {
		$return_column = "";
		if ($type == "cdrr") {
			$order = Escm_Orders::getEscm($escm_id);
			$return_column = "cdrr_id";
		} else if ($type == "maps") {
			$order = Escm_Maps::getEscm($escm_id);
			$return_column = "maps_id";
		}

		if ($order) {
			return $order[$return_column];
		}
		return null;
	}

	public function map_order($escm_id = "", $nascop_id = "", $type = "cdrr") {
		if ($type == "cdrr") {
			$sql = "DELETE FROM escm_orders WHERE cdrr_id='$nascop_id'";
			$this -> db -> query($sql);
			$order = new Escm_Orders();
			$order -> cdrr_id = $nascop_id;
		} else if ($type == "maps") {
			$sql = "DELETE FROM escm_maps WHERE maps_id='$nascop_id'";
			$this -> db -> query($sql);
			$order = new Escm_Maps();
			$order -> maps_id = $nascop_id;
		}
		$order -> escm_id = $escm_id;
		$order -> save();

	}
	public function delete_order($type = "cdrr", $id, $mission = 0) {
		$sql = "SELECT status FROM $type WHERE id='$id'";
		$query = $this -> db -> query($sql);
		$results = $query -> result_array();
		if ($results) {
			$status = $results[0]['status'];
			if (($status != "approved" || $mission == 1)) {
				$sql_array = array();
				if ($type == "cdrr") {
					$this -> session -> set_userdata("order_go_back", "cdrr");
					$sql_array[] = "DELETE FROM cdrr where id='$id'";
					$sql_array[] = "DELETE FROM cdrr_item where cdrr_id='$id'";
					$sql_array[] = "DELETE FROM cdrr_log where cdrr_id='$id'";
				} else if ($type == "maps") {
					$this -> session -> set_userdata("order_go_back", "maps");
					$sql_array[] = "DELETE FROM maps where id='$id'";
					$sql_array[] = "DELETE FROM maps_item where maps_id='$id'";
					$sql_array[] = "DELETE FROM maps_log where maps_id='$id'";
				}
				foreach ($sql_array as $sql) {
					$query = $this -> db -> query($sql);
				}
				if ($mission == 0) {
					$this -> session -> set_flashdata("order_delete", $type . " was deleted successfully.");
				}
			} else {
				if ($mission == 0) {
					$this -> session -> set_flashdata("order_delete", $type . " delete failed!");
				}
			}
		} else {
			$this -> session -> set_flashdata("order_delete", $type . " not found!");
		}
		if ($mission == 0) {
			redirect("order");
		}
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

	public function monthly_eid_update(){
		$period=3;
        $period_days=$period*30;
		$today=date('Y-m-d');
        $last_day_of_month=date('Y-m-t');
        $first_day=date('Y-m-01',strtotime("-".$period."months"));
        $last_day=date('Y-m-t',strtotime("-".$period."months"));
        $facility_emails=array();
        $response="";
 
        //check if last day of month
        if($today != $last_day_of_month){
            //get eid facility list emails
            $sql="SELECT em.email,em.facility
                  FROM eid_mail em
                  LEFT JOIN eid_master e ON em.facility=e.facilitycode
                  WHERE em.active='1'
                  GROUP BY em.email,em.facility";
            $query = $this -> db -> query($sql);
		    $results = $query -> result_array();
		    if($results){
	            foreach($results as $result){
	                $facility=$result['facility'];
	               	$email=$result['email'];
	                $facility_emails[$facility][]=$email;
	            }
		    }

		    foreach($facility_emails as $facility_code=>$emails){
		        $sql="SELECT ei.status as label,COUNT(ei.status) AS total,f.name
			          FROM eid_info ei 
			          LEFT JOIN facilities f ON f.facilitycode=ei.facility_code 
			          WHERE ei.enrollment_date 
			          BETWEEN '$first_day' 
			          AND '$last_day' 
			          AND ei.facility_code='$facility_code' 
			          AND DATEDIFF(CURDATE(),ei.enrollment_date)>=$period_days
			          GROUP BY ei.status";
				$query = $this -> db -> query($sql);
			    $results = $query -> result_array();
			    $table="<table border='1' width='50%' cellspacing='0.5' cellpadding='2'><caption>".strtoupper(@$results[0]['name'])."</caption>";
			    $table.="<thead><tr><th>Retention Status</th><th>Total</th></tr></thead><tbody>";
			    if($results){
		            foreach($results as $result){
                      $table.="<tr>";
                      $table.="<td>".strtoupper($result['label'])."</td>";
                      $table.="<td>".$result['total']."</td>";
                      $table.="</tr>";
		            }
			    }else{
			    	  $table.="<tr>";
                      $table.="<td colspan='2'>no data available!</td>";
                      $table.="</tr>";
			    }
			    $table.="</tbody></table>";
			    $email_list=implode(",", $emails);

			    $message = "Hello, <br/><br/>
                This is the monthly EID Summary for ".@$results[0]['name'].".<br/>
                The data is for patients enrolled in the period between ".date('d-M-Y',strtotime($first_day))." and ".date('d-M-Y',strtotime($last_day))." reporting on retention for  a period of ".$period." months.<br/>
				Find the summary in the table below:<br/><br/>".
				$table."<br/>Regards,<br/>NASCOP SYSTEM team.";
			    $response.=$this->send_notification($email_list,$message);
            }  
        }
        echo $response;
	}

	public function send_notification($email_address,$message){
		$email_user = stripslashes('webadt.chai@gmail.com');
		$email_password = stripslashes('WebAdt_052013');
		$subject = "EID Monthly Retention Summary";
		$email_sender_title = "NASCOP SYSTEM";

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

	public function auto_script(){
		$this -> load -> view('auto_script_v');
	}

	public function base_params($data) {
		$data['content_view'] = "settings/settings_v";
		$data['title'] = "webADT | API Settings";
		$data['banner_text'] = "API Settings";
		$this -> load -> view('template', $data);
	}

}
