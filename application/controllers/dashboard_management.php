<?php
if (!defined('BASEPATH'))
	exit('No direct script access allowed');
class Dashboard_Management extends MY_Controller {
	function __construct() {
		parent::__construct();
		$data = array();
		ini_set("max_execution_time", "100000");
		ini_set('memory_limit', '2048M');
		$this -> load -> library('PHPExcel');
	}

	public function index() {
		$data['content_view'] = "home_v";
		$data['hide_side_menu'] = 1;
		$data['banner_text'] = "National Dashboard";
		$data['title'] = "webADT | National Dashboard";
		$data['supporter'] = Supporter::getThemAll();
		$this -> base_params($data);
	}

	public function download($type = "", $period = "",$pipeline='') {
		if($type=="SOH"){
			$objPHPExcel = new PHPExcel();
			$objPHPExcel -> setActiveSheetIndex(0);
			$i = 1;
			$objPHPExcel -> getActiveSheet() -> SetCellValue('B1', "ART PROGRAM");
			$objPHPExcel -> getActiveSheet() -> SetCellValue('B2', "Pipeline : ".strtoupper($pipeline));
			$objPHPExcel -> getActiveSheet() -> SetCellValue('B3', " MONTHLY COMMODITY CONSUMPTION ");
			$objPHPExcel -> getActiveSheet() -> SetCellValue('B4', "Period : ".$period);
			$objPHPExcel -> getActiveSheet() -> SetCellValue('B8', "Drug Name");
		}
		
		
		else if($type=="CONS"){// Stock consumption
			$period = date('Y-m-d',strtotime($period));
			$drug_table = '';
			$facility_table = '';
			//Get consumption for that period
			if($pipeline == 'kemsa'){
				$drug_table = 'sync_drug';
				$facility_table ='sync_facility';
			}
			else if($pipeline == 'kenya_pharma'){
				$drug_table = ' escm_drug';
				$facility_table ='escm_facility';
			}
			$sql_facility = "
							SELECT f.id,f.name,CAST(f.code AS UNSIGNED) as code FROM $facility_table f
							WHERE f.category != 'satellite'
							AND f.code !='' ORDER BY code ASC
							";
			
			$query_f = $this ->db->query($sql_facility);
			$results_f = $query_f ->result_array();
			
			$sql ="SELECT sd.id as d_id,f.id as facility_id,sd.name as drug_name,CONCAT('(',sd.abbreviation,' ',sd.strength,')') as descr,IF(f.category='standalone',SUM(ci.dispensed_packs),SUM(ci.aggr_consumed)) as tot_consumption FROM $drug_table sd
					LEFT JOIN cdrr_item ci ON ci.drug_id=sd.id
					LEFT JOIN cdrr c ON c.id=ci.cdrr_id
					LEFT JOIN $facility_table f ON f.id =c.facility_id
					LEFT JOIN arv_drug ad ON ad.drug_id = sd.id
					WHERE c.period_begin = '".$period."'
					AND ad.pipeline = '$pipeline'
					GROUP BY sd.name
					";
			$query = $this ->db->query($sql);
			$results = $query ->result_array();
			
			$objPHPExcel = new PHPExcel();
			$objPHPExcel -> setActiveSheetIndex(0);
			
			$objPHPExcel -> getActiveSheet() -> SetCellValue('B1', "ARV PROGRAM");
			$objPHPExcel -> getActiveSheet() -> SetCellValue('B2', "MONTH'S CONSUMPTION BY MEDICINE BY ARV SITE");
			$objPHPExcel -> getActiveSheet() -> SetCellValue('G2', "Data For Reporting Site Only");
			$objPHPExcel -> getActiveSheet() -> SetCellValue('B3', "Period : ".$period);
			$objPHPExcel -> getActiveSheet() -> SetCellValue('B4', "As at : ".date('jS F Y'));
			$objPHPExcel -> getActiveSheet() -> SetCellValue('B5', "Pipeline : ".strtoupper($pipeline));
			$objPHPExcel -> getActiveSheet() -> SetCellValue('C7', "Drug");
			$objPHPExcel -> getActiveSheet() -> SetCellValue('B8', "MFL Code");
			$objPHPExcel -> getActiveSheet() -> SetCellValue('C8', "ARV Ordering Point Name");
			
			$x="D";
			foreach ($results as $value) {
				$drug = $value['drug_name'].$value['descr'];
				$objPHPExcel -> getActiveSheet() -> SetCellValue($x.'7',$drug);
				$x++;
				
			}
			$y=1;
			$p = 9;
			foreach ($results_f as $value) {
				$id = $value['id'];
				$code = $value['code'];
				$name = $value['name'];
				$objPHPExcel -> getActiveSheet() -> SetCellValue('A'.$p,$y);
				$objPHPExcel -> getActiveSheet() -> SetCellValue('B'.$p,$code);
				$objPHPExcel -> getActiveSheet() -> SetCellValue('C'.$p,$name);
			
				$x="D";
				foreach ($results as $value) {
					$facility_id = $value['facility_id'];
					$total_cons = $value['tot_consumption'];
					if($facility_id ==$id){//Check if there is data for this facility
						$objPHPExcel -> getActiveSheet() -> SetCellValue($x.$p,$total_cons);
					}
					else{
						$objPHPExcel -> getActiveSheet() -> SetCellValue($x.$p,0);
					}
					
					$x++;
					
				}
				
				$p++;
				$y++;
			}
			
			$filename = "Facility Consumption by ARV Medicine for " . $period . ".csv";
			header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
			header("Cache-Control: no-store, no-cache, must-revalidate");
			header("Cache-Control: post-check=0, pre-check=0", false);
			header("Pragma: no-cache");
			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			header('Content-Disposition: attachment;filename=' . $filename);
	
			$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'CSV');
	
			$objWriter -> save('php://output');
	
			$objPHPExcel -> disconnectWorksheets();
			unset($objPHPExcel);
			
		}
		
		//Patients BY ART Sites
		else if($type=='ART_PATIENT'){
			$period = date('Y-m-d',strtotime($period));
			$facility_table = '';
			$regimen_table = '';
			if($pipeline == 'kemsa'){
				$facility_table ='sync_facility';
				$regimen_table ='regimen';
			}
			else if($pipeline == 'kenya_pharma'){
				$facility_table ='escm_facility';
				$regimen_table ='escm_regimen';
			}
			//Get ART Facilities
			$sql_facility = "
							SELECT f.id,f.name,CAST(f.code AS UNSIGNED) as code,f.services FROM $facility_table f
							WHERE f.services LIKE '%ART%'
							AND f.code !='' ORDER BY code ASC
							";
			$query_f = $this ->db->query($sql_facility);
			$results_f = $query_f ->result_array();
			
			//Get regimen list
			$sql_regimen = "
							SELECT r.id,r.regimen_code,r.regimen_desc,SUM(mi.total) as tot_patient,f.code,m.facility_id  
							FROM $regimen_table r 
							LEFT JOIN maps_item mi ON mi.regimen_id=r.id
							LEFT JOIN maps m ON m.id=mi.maps_id
							LEFT JOIN $facility_table f ON f.id =m.facility_id
							WHERE enabled ='1'
							GROUP BY r.id
							";
			$query = $this ->db->query($sql_regimen);
			$results = $query ->result_array();
			
			//GEnerate excel start here
			$period =date('F-Y',strtotime($period));
			$objPHPExcel = new PHPExcel();
			$objPHPExcel -> setActiveSheetIndex(0);
;			$objPHPExcel -> getActiveSheet() -> SetCellValue('A1', "ART PROGRAM");
			$objPHPExcel -> getActiveSheet() -> SetCellValue('A2', "FACILITIES: CURRENT ART PATIENTS BY REGIMEN ");
			$objPHPExcel -> getActiveSheet() -> SetCellValue('A3', "Period : ".$period);
			$objPHPExcel -> getActiveSheet() -> SetCellValue('A4', "Pipeline : ".strtoupper($pipeline));
			$objPHPExcel -> getActiveSheet() -> SetCellValue('B7', "Regimen ");
			$objPHPExcel -> getActiveSheet() -> SetCellValue('B8', "Site Name ");
			$objPHPExcel -> getActiveSheet() -> SetCellValue('D6', "New Code ");
			$objPHPExcel -> getActiveSheet() -> SetCellValue('D7', "Previous Code ");
			$objPHPExcel -> getActiveSheet() -> SetCellValue('D8', "Site Total ");
			
			$x="E";
			foreach ($results as $value) {
				$drug = $value['regimen_code'];
				$objPHPExcel -> getActiveSheet() -> SetCellValue($x.'6',$drug);
				$x++;
				
			}
			
			$y=1;
			$p = 9;
			foreach ($results_f as $value) {
				$id = $value['id'];
				$code = $value['code'];
				$name = $value['name'];
				$objPHPExcel -> getActiveSheet() -> SetCellValue('A'.$p,$y);
				$objPHPExcel -> getActiveSheet() -> SetCellValue('B'.$p,$code);
				$objPHPExcel -> getActiveSheet() -> SetCellValue('C'.$p,$name);
				
				$x="E";
				$sub_total =0;
				foreach ($results as $value) {
					$facility_id = $value['facility_id'];
					$total_pat = $value['tot_patient'];
					if($facility_id ==$id){//Check if there is data for this facility
						$objPHPExcel -> getActiveSheet() -> SetCellValue($x.$p,$total_pat);
						$sub_total+=$total_pat;
					}
					else{
						$objPHPExcel -> getActiveSheet() -> SetCellValue($x.$p,0);
					}
					
					$x++;
					
				}
				$objPHPExcel -> getActiveSheet() -> SetCellValue("D".$p,$sub_total);
				
				$p++;
				$y++;
			}
			
			
			$filename = "Current Patients By ART Sites for " . $period . ".csv";
			header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
			header("Cache-Control: no-store, no-cache, must-revalidate");
			header("Cache-Control: post-check=0, pre-check=0", false);
			header("Pragma: no-cache");
			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			header('Content-Disposition: attachment;filename=' . $filename);
	
			$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'CSV');
	
			$objWriter -> save('php://output');
	
			$objPHPExcel -> disconnectWorksheets();
			unset($objPHPExcel);
						
		}

		elseif ($type=='PATIENT_REGIMEN') {
			$period = date('Y-m-d',strtotime($period));
			$facility_table = '';
			$regimen_table = '';
			if($pipeline == 'kemsa'){
				$regimen_table ='regimen';
			}
			else if($pipeline == 'kenya_pharma'){
				$regimen_table ='escm_regimen';
			}
		}

	}

	public function getCommodity($type = "SOH") {
		$columns = array('#', 'Reporting Period', 'Pipeline', 'Action');
		$links = array('dashboard_management/download/' . $type => 'download');
		//Get eSCM orders
		$escm_orders = Escm_Orders::getAll();
		$list = "";
		$order_list = array();
		$cdrr_nascop = array();
		$cdrr_escm = array();
		$table_name = $type;
		$result =array();

		if ($escm_orders) {
			foreach ($escm_orders as $order_id) {
				array_push($order_list, $order_id -> cdrr_id);
			}
			$list = "'" . implode("','", $order_list) . "'";
		}
		$cdrr_nascop = Cdrr::getNascopPeriod($list);
		$counter = 0;
		foreach ($cdrr_nascop as $nascop) {
			$result[$counter]['period'] = date('F-Y', strtotime($nascop['period_begin']));
			$result[$counter]['pipeline'] = "Kemsa";
			$counter++;
		}
		$cdrr_escm = Cdrr::getEscmPeriod($list);
		foreach ($cdrr_escm as $esm) {
			$result[$counter]['period'] = date('F-Y', strtotime($esm['period_begin']));
			$result[$counter]['pipeline'] = "Kenya Pharma";
			$counter++;
		}
		echo $this -> showTable($columns, $result, $links, $table_name);
	}

	public function getPatients($type="ART_PATIENT"){
		$columns = array('#', 'Reporting Period', 'Pipeline', 'Action');
		$links = array('dashboard_management/download/' . $type => 'download');
		//Get eSCM orders
		$escm_patients = Escm_Maps::getAll();
		$list = "";
		$order_list = array();
		$maps_nascop = array();
		$maps_escm = array();
		$table_name = $type;
		$result =array();
		if ($escm_patients) {
			foreach ($escm_patients as $order_id) {
				array_push($order_list, $order_id -> maps_id);
			}
			$list = "'" . implode("','", $order_list) . "'";
		}
		$maps_nascop = Maps::getNascopPeriod($list);
		$counter = 0;
		foreach ($maps_nascop as $nascop) {
			$result[$counter]['period'] = date('F-Y', strtotime($nascop['period_begin']));
			$result[$counter]['pipeline'] = "Kemsa";
			$counter++;
		}
		$maps_escm = Maps::getEscmPeriod($list);
		foreach ($maps_escm as $esm) {
			$result[$counter]['period'] = date('F-Y', strtotime($esm['period_begin']));
			$result[$counter]['pipeline'] = "Kenya Pharma";
			$counter++;
		}
		echo $this -> showTable($columns, $result, $links, $table_name);
		
	}


	public function showTable($columns, $data = array(), $links = array(), $table_name = "") {
		$this -> load -> library('table');
		$tmpl = array('table_open' => '<table id=' . $table_name . '_listing class="table table-bordered table-striped tbl_nat_dashboard">');
		$this -> table -> set_template($tmpl);
		$this -> table -> set_heading($columns);
		$link_values = "";
		$pipeline = "";
		foreach ($data as $mydata) {
			if($mydata['pipeline']=='Kenya Pharma'){
				$pipeline = 'kenya_pharma';
			}
			else if($mydata['pipeline']=='Kemsa'){
				
				$pipeline = 'kemsa';
			}
			//Set Up links
			foreach ($links as $i => $link) {
				$link_values .= "<a href='" . site_url($i . '/' . $mydata['period'].'/'.$pipeline)."'>$link</a> | ";
			}
			$mydata['Options'] = rtrim($link_values, " | ");
			$link_values = "";
			$this -> table -> add_row($mydata);
		}
		return $this -> table -> generate();
	}

	public function base_params($data) {
		$this -> load -> view("template_national", $data);
	}

}
