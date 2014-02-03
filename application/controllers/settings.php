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

	public function get($type = "nascop_drugs") {
		$jtStartIndex = $this -> input -> get("jtStartIndex");
		$jtPageSize = $this -> input -> get("jtPageSize");
		$jtSorting = urldecode($this -> input -> get("jtSorting"));
		$search_value = $this -> input -> post("name");
		if ($type == "nascop_drugs") {
			$rows = Sync_Drug::getAllSettings($jtStartIndex, $jtPageSize, $jtSorting, $search_value);
			$total_rows = Sync_Drug::getAll();
		}
		$jTableResult = array();
		$jTableResult['Result'] = "OK";
		$jTableResult['Records'] = $rows;
		$jTableResult['TotalRecordCount'] = count($total_rows);
		print json_encode($jTableResult);

	}

	public function base_params($data) {
		$data['content_view'] = "settings/settings_param";
		$data['title'] = "webADT | System Admin";
		$data['banner_text'] = "System Admin";
		$this -> load -> view('settings/settings_template', $data);
	}

}
