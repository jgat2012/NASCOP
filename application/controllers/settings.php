<?php
class settings extends MY_Controller {
	var $esm_url = "https://api.kenyapharma.org/";
	function __construct() {
		parent::__construct();
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

	public function api_sync() {
		$log = "";
		$info_class = "<div class='alert alert-info'>";
		$error_class = "<div class='alert alert-error'>";
		$close_btn_div = "<button type='button' class='close' data-dismiss='alert'>&times;</button>";
		//Link array
		$links = array();
		$links['escm_drug'] = "drugs";
		$links['escm_regimen'] = "regimen";

		$curl = new Curl();
		$url = $this -> esm_url;

		$username = "kevinmarete";
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
				if ($i != "id" && $i != "facility" && $i != "category_id" && $i != "status" && $i != "old_code" && $i != "district_id" && $i != "ordering" && $i != "service_point" && $i != "county_id" && $i != "sponsors") {
					$myrow[] = $v;
				} else {
					if ($i == "id") {
						$id = $v;
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
				}
			}
			$links = "";
			if ($action_link == "delete") {
				$links = "<a href='" . site_url("settings/modal") . "/" . $type . "' item_id='" . $id . "' class='edit_item' role='button' data-toggle='modal' data-mydata='" . json_encode($row) . "'><i class='icon-pencil'></i></a>";
				$links .= "  ";
				if ($type != "sync_facility") {
					$links .= anchor("settings/" . $action_link . "/" . $type . "/" . $id, $action_icon, array("class" => "delete"));
				}
			} else {
				if ($type != "sync_facility") {
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
			} else if ($input == "username") {
				$textfield = "<input type='text' id='" . $type . "_" . $input . "' name='" . $input . "' class='phone'/>";
			} else if ($input == "district_id") {
				$textfield = "<select id='" . $type . "_" . $input . "' name='" . $input . "'>";
				$textfield .= "<option value='0' selected='selected'>--Select One--</option>";
				$districts = District::getActive();
				foreach ($districts as $district) {
					$textfield .= "<option value='" . $district['id'] . "'>" . $district['Name'] . "</option>";
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
			} else if ($input == "ordering") {
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
			$inputs = array("name" => "name", "abbreviation" => "abbreviation", "strength" => "strength", "packsize" => "packsize", "formulation" => "formulation", "unit" => "unit", "weight" => "weight");
		} else if ($type == "sync_facility") {
			$inputs = array("code" => "code", "name" => "name", "category" => "category", "sponsors" => "sponsors", "services" => "services", "district_id" => "district_id", "ordering" => "ordering", "service_point" => "service_point", "county_id" => "county_id");
		} else if ($type == "sync_regimen") {
			$inputs = array("code" => "code", "name" => "name", "description" => "description");
		} else if ($type == "sync_user") {
			$inputs = array("name" => "name", "email" => "email", "role" => "role", "username" => "username", "facility_list" => "facilities_holder");
		}
		foreach ($inputs as $index => $input) {
			if ($index == "facility_list") {
				if ($input == null) {
					$facility_list = "";
				} else {
					$facility_list = json_encode($this -> input -> post($input));
				}
			} else {
				$save_data[$index] = $this -> input -> post($input);
			}
		}
		//insert or update
		if ($id == null) {
			$this -> db -> insert($type, $save_data);
			$message = "<b>Saved " . $type . "!</b>  You successfully saved.";
			$content = $success_class;
			if ($type == "sync_user") {
				$user_id = $this -> db -> insert_id();
				$this -> db -> insert("user_facilities", array("user_id" => $user_id, "facility" => $facility_list));
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
			}
		}

		$content .= $close_btn_div;
		$content .= $message;
		$content .= $close_div;
		$this -> session -> set_flashdata("alert_message", $content);
		$this -> session -> set_userdata("nav_link", $type);
		redirect("settings");

	}

	public function base_params($data) {
		$data['content_view'] = "settings/settings_v";
		$data['title'] = "webADT | API Settings";
		$data['banner_text'] = "API Settings";
		$this -> load -> view('template', $data);
	}

}
