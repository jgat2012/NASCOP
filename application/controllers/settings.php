<?php
class settings extends MY_Controller {
	function __construct() {
		parent::__construct();
		$this -> load -> library('encrypt');
		date_default_timezone_set('Africa/Nairobi');
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
			$columns = array("id", "name", "abbreviation", "strength", "packsize", "formulation", "unit", "weight");
		} else if ($type == "sync_facility") {
			$columns = array("id", "code", "name", "category", "services");
		} else if ($type == "sync_regimen") {
			$columns = array("id", "code", "name", "description");
		} else if ($type == "sync_user") {
			$columns = array("id", "name", "email", "role", "profile_id");
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
			foreach ($row as $i => $v) {
				if ($i != "id") {
					$myrow[] = $v;
				} else {
					$id = $v;
				}
			}
			$links = "<a href='" . site_url("settings/modal") . "/" . $type . "' item_id='" . $id . "' class='edit_item' role='button' data-toggle='modal' data-mydata='" . json_encode($row) . "'><i class='icon-pencil'></i></a>";
			$links .= "  ";
			$links .= anchor("settings/delete/" . $id, "<i class='icon-trash'></i>");
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
			$inputs = array("name" => "name", "abbreviation" => "abbreviation", "strength" => "strength", "packsize" => "packsize", "formulation" => "formulation", "unit" => "unit", "weight" => "weight");
		} else if ($type == "sync_facility") {
			$inputs = array("code" => "code", "name" => "name", "category" => "category", "services" => "services");
		} else if ($type == "sync_regimen") {
			$inputs = array("code" => "code", "name" => "name", "description" => "description");
		} else if ($type == "sync_user") {
			$inputs = array("name" => "name", "email" => "email", "role" => "role", "phone" => "profile_id");
		}
		foreach ($inputs as $text => $input) {
			$content .= $group_div;
			$label = "<label class='control-label'>" . $text . "</label>";
			$content .= $label;
			$content .= $control_div;
			$textfield = "<input type='text' id='" . $type . "_" . $input . "' name='" . $input . "'/>";
			if ($input == "profile_id") {
				$textfield = "<input type='text' id='" . $type . "_" . $input . "' name='" . $text . "'/>";
			}
			$content .= $textfield;
			$content .= $close_div;
			$content .= $close_div;
		}

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
			$inputs = array("code" => "code", "name" => "name", "category" => "category", "services" => "services");
		} else if ($type == "sync_regimen") {
			$inputs = array("code" => "code", "name" => "name", "description" => "description");
		} else if ($type == "sync_user") {
			$inputs = array("name" => "name", "email" => "email", "role" => "role", "profile_id" => "phone");
		}
		foreach ($inputs as $index => $input) {
			$save_data[$index] = $this -> input -> post($input);
		}

		//insert or update
		if ($id == null) {
			$this -> db -> insert($type, $save_data);
			$message = "<b>Saved " . $type . "!</b>  You successfully saved.";
			$content = $success_class;
		} else {
			$this -> db -> where('id', $id);
			$this -> db -> update($type, $save_data);
			$message = "<b>Updated " . $type . "!</b> You successfully updated.";
			$content = $info_class;
		}

		$content .= $close_btn_div;
		$content .= $message;
		$content .= $close_div;
		$this -> session -> set_flashdata("alert_message", $content);
		$this -> session -> set_userdata("current_nav", $type);
		redirect("settings");

	}

	public function base_params($data) {
		$data['content_view'] = "settings/settings_v";
		$data['title'] = "webADT | API Settings";
		$data['banner_text'] = "API Settings";
		$this -> load -> view('template', $data);
	}

}
