<?php
if (!defined('BASEPATH'))
	exit('No direct script access allowed');

class Pharmacist_Management extends MY_Controller {

	function __construct() {
		parent::__construct();
	}

	public function getTopCommodities($limit, $start_date = "", $end_date = "") {
		$sql="";
	}

	public function getFacilitiesUsing($start_date = "", $end_date = "") {
		
	}

	public function getPickingList($start_date = "", $end_date = "") {
		
	}

	public function getFacilitiesDelay($start_date = "", $end_date = "") {
		
	}

}
?>