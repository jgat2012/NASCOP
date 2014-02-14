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

	public function download($type = "", $period = "", $pipeline = '') {
		if ($type == "SOH") {
			$dir = "Export";
			$inputFileType = 'Excel5';
			$inputFileName = $_SERVER['DOCUMENT_ROOT'] . '/NASCOP/assets/template/excel.xls';
			$objReader = PHPExcel_IOFactory::createReader($inputFileType);
			$objPHPExcel = $objReader -> load($inputFileName);

			$filename = "Stock";
			$dir = "Export";
			$objPHPExcel = $this -> generateExcelDefaultStyle($filename);
			$objPHPExcel -> setActiveSheetIndex(0);
			$objPHPExcel -> getDefaultStyle() -> getFont() -> setName('Book Antiqua') -> setSize(10);
			$i = 1;
			$objPHPExcel -> getActiveSheet() -> getColumnDimension("B") -> setAutoSize(true);
			$objPHPExcel -> getActiveSheet() -> getStyle('C3:AN3') -> getAlignment() -> setWrapText(true);
			$objPHPExcel -> getActiveSheet() -> getRowDimension('3') -> setRowHeight(-1);
			foreach (range('C','AN') as $columnID) {

				if ($columnID == "F" || $columnID == "G" || $columnID == "K" || $columnID == "O" || $columnID == "S" || $columnID == "U" || $columnID == "Y" || $columnID == "Z" || $columnID == "AB" || $columnID == "AF" || $columnID == "AG" || $columnID == "AK") {
					$objPHPExcel -> getActiveSheet() -> getColumnDimension($columnID) -> setWidth(5);
				} else {
					$objPHPExcel -> getActiveSheet() -> getColumnDimension($columnID) -> setWidth(15);
				}

			}
			$objPHPExcel -> getActiveSheet() -> mergeCells('H2:J2');
			$objPHPExcel -> getActiveSheet() -> mergeCells('L2:N2');
			$objPHPExcel -> getActiveSheet() -> mergeCells('P2:R2');
			$objPHPExcel -> getActiveSheet() -> mergeCells('T2:X2');
			$objPHPExcel -> getActiveSheet() -> mergeCells('P2:R2');
			$objPHPExcel -> getActiveSheet() -> mergeCells('AA2:AE2');
			$objPHPExcel -> getActiveSheet() -> mergeCells('AH2:AJ2');
			$objPHPExcel -> getActiveSheet() -> mergeCells('AL2:AN2');
			$objPHPExcel -> getActiveSheet() -> SetCellValue('H2', "KEMSA");
			$objPHPExcel -> getActiveSheet() -> SetCellValue('L2', "KENYA PHARMA");
			$objPHPExcel -> getActiveSheet() -> SetCellValue('P2', "NATIONAL");
			$objPHPExcel -> getActiveSheet() -> SetCellValue('T2', "NATIONAL MOS - Without S.up");
			$objPHPExcel -> getActiveSheet() -> SetCellValue('AA2', "NATIONAL MOS - S.up");
			$objPHPExcel -> getActiveSheet() -> SetCellValue('AH2', "KEMSA");
			$objPHPExcel -> getActiveSheet() -> SetCellValue('AL2', "KP");
			$objPHPExcel -> getActiveSheet() -> SetCellValue('A3', "Drug Abbreviation");
			$objPHPExcel -> getActiveSheet() -> SetCellValue('B3', "Drugs Classification");
			$objPHPExcel -> getActiveSheet() -> SetCellValue('C3', "Current Consumption- Kemsa");
			$objPHPExcel -> getActiveSheet() -> SetCellValue('D3', "Current Consumption- KP");
			$objPHPExcel -> getActiveSheet() -> SetCellValue('E3', "Agg. Avg. Monthly Consumption");
			$objPHPExcel -> getActiveSheet() -> SetCellValue('H3', "Facility SOH - KEMSA Pipeline");
			$objPHPExcel -> getActiveSheet() -> SetCellValue('I3', "SOH KEMSA");
			$objPHPExcel -> getActiveSheet() -> SetCellValue('J3', "KEMSA - Pending With Suppliers");
			$objPHPExcel -> getActiveSheet() -> SetCellValue('L3', "Facility SOH - KP");
			$objPHPExcel -> getActiveSheet() -> SetCellValue('M3', "SOH KP");
			$objPHPExcel -> getActiveSheet() -> SetCellValue('N3', "KP - Pending With Suppliers");
			$objPHPExcel -> getActiveSheet() -> SetCellValue('P3', "Facility SOH");
			$objPHPExcel -> getActiveSheet() -> SetCellValue('Q3', "Stocks on hand - Central Stores");
			$objPHPExcel -> getActiveSheet() -> SetCellValue('R3', "Pending With Suppliers");
			$objPHPExcel -> getActiveSheet() -> SetCellValue('T3', "National MOS");
			$objPHPExcel -> getActiveSheet() -> SetCellValue('V3', "MOS at Facilities");
			$objPHPExcel -> getActiveSheet() -> SetCellValue('W3', "MOS at Central Stores");
			$objPHPExcel -> getActiveSheet() -> SetCellValue('X3', "MOS - Pending With Suppliers");
			$objPHPExcel -> getActiveSheet() -> SetCellValue('AA3', "National MOS");
			$objPHPExcel -> getActiveSheet() -> SetCellValue('AC3', "MOS at Facilities");
			$objPHPExcel -> getActiveSheet() -> SetCellValue('AD3', "MOS at Central Stores");
			$objPHPExcel -> getActiveSheet() -> SetCellValue('AE3', "MOS - Pending With Suppliers");
			$objPHPExcel -> getActiveSheet() -> SetCellValue('AH3', "MOS at Facilities");
			$objPHPExcel -> getActiveSheet() -> SetCellValue('AI3', "MOS at Central Stores");
			$objPHPExcel -> getActiveSheet() -> SetCellValue('AJ3', "MOS - Pending With Suppliers");
			$objPHPExcel -> getActiveSheet() -> SetCellValue('AL3', "MOS at Facilities");
			$objPHPExcel -> getActiveSheet() -> SetCellValue('AM3', "MOS at Central Stores");
			$objPHPExcel -> getActiveSheet() -> SetCellValue('AN3', "MOS - Pending With Suppliers");
			$sql = "SELECT d.name,d.abbreviation,d.category_id FROM escm_drug";

			$this -> generateExcel($filename, $dir, $objPHPExcel);
		} else if ($type == "CONS") {// Stock consumption
			$period = date('Y-m-01', strtotime($period));
			$drug_table = '';
			$facility_table = '';
			//Get consumption for that period
			if ($pipeline == 'kemsa') {
				$drug_table = 'sync_drug';
				$facility_table = 'sync_facility';
				$results_f = Sync_Facility::getAllHydrated();
			} else if ($pipeline == 'kenya_pharma') {
				$drug_table = ' escm_drug';
				$facility_table = 'escm_facility';
				$results_f = Escm_Facility::getAllHydrated();
			}

			$sql = "SELECT sd.id as d_id,f.id as facility_id,sd.name as drug_name,CONCAT('(',sd.abbreviation,' ',sd.strength,')') as descr,IF(f.category='standalone',SUM(ci.dispensed_packs),SUM(ci.aggr_consumed)) as tot_consumption FROM $drug_table sd
					LEFT JOIN cdrr_item ci ON ci.drug_id=sd.id
					LEFT JOIN cdrr c ON c.id=ci.cdrr_id
					LEFT JOIN $facility_table f ON f.id =c.facility_id
					LEFT JOIN arv_drug ad ON ad.drug_id = sd.id
					WHERE c.period_begin = '" . $period . "'
					AND ad.pipeline = '$pipeline'
					GROUP BY sd.name
					";
			$query = $this -> db -> query($sql);
			$results = $query -> result_array();
			$filename = "Facility Cons by ARV Medicine";
			$dir = "Export";
			$objPHPExcel = $this -> generateExcelDefaultStyle($filename);
			$objPHPExcel -> setActiveSheetIndex(0);
			foreach (range('A','C') as $columnID) {
				$objPHPExcel -> getActiveSheet() -> getColumnDimension($columnID) -> setAutoSize(true);
			}
			$objPHPExcel -> getActiveSheet() -> mergeCells('B1:C1');
			$objPHPExcel -> getActiveSheet() -> mergeCells('B2:C2');
			$objPHPExcel -> getActiveSheet() -> mergeCells('B3:C3');
			$objPHPExcel -> getActiveSheet() -> mergeCells('B3:C3');
			$objPHPExcel -> getActiveSheet() -> mergeCells('B4:C4');
			$objPHPExcel -> getActiveSheet() -> mergeCells('B5:C5');

			$objPHPExcel -> getActiveSheet() -> SetCellValue('B1', "ARV PROGRAM");
			$objPHPExcel -> getActiveSheet() -> SetCellValue('B2', "MONTH'S CONSUMPTION BY MEDICINE BY ARV SITE");
			$objPHPExcel -> getActiveSheet() -> SetCellValue('G2', "Data For Reporting Site Only");
			$objPHPExcel -> getActiveSheet() -> SetCellValue('B3', "Period : " . $period);
			$objPHPExcel -> getActiveSheet() -> SetCellValue('B4', "As at : " . date('jS F Y'));
			$objPHPExcel -> getActiveSheet() -> SetCellValue('B5', "Pipeline : " . strtoupper($pipeline));
			$objPHPExcel -> getActiveSheet() -> SetCellValue('C7', "Drug");
			$objPHPExcel -> getActiveSheet() -> SetCellValue('B8', "MFL Code");
			$objPHPExcel -> getActiveSheet() -> SetCellValue('C8', "ARV Ordering Point Name");

			$x = "D";
			foreach ($results as $value) {
				$drug = $value['drug_name'] . $value['descr'];
				$objPHPExcel -> getActiveSheet() -> SetCellValue($x . '7', $drug);
				$x++;

			}
			$y = 1;
			$p = 9;
			foreach ($results_f as $value) {
				$id = $value['id'];
				$code = $value['code'];
				$name = $value['name'];
				$objPHPExcel -> getActiveSheet() -> SetCellValue('A' . $p, $y);
				$objPHPExcel -> getActiveSheet() -> SetCellValue('B' . $p, $code);
				$objPHPExcel -> getActiveSheet() -> SetCellValue('C' . $p, $name);

				$x = "D";
				foreach ($results as $value) {
					$facility_id = $value['facility_id'];
					$total_cons = $value['tot_consumption'];
					if ($facility_id == $id) {//Check if there is data for this facility
						$objPHPExcel -> getActiveSheet() -> SetCellValue($x . $p, $total_cons);
					} else {
						$objPHPExcel -> getActiveSheet() -> SetCellValue($x . $p, 0);
					}

					$x++;

				}

				$p++;
				$y++;
			}
			$objPHPExcel -> getActiveSheet() -> getStyle('D7:' . $x . $p) -> getAlignment() -> setWrapText(true);
			$objPHPExcel -> getActiveSheet() -> getRowDimension('7') -> setRowHeight(-1);
			$objPHPExcel -> getActiveSheet() -> freezePane('D8');
			$this -> generateExcel($filename, $dir, $objPHPExcel);

		}
		//Patients BY ART Sites
		else if ($type == 'ART_PATIENT') {
			$period = date('Y-m-01', strtotime($period));
			$facility_table = '';
			$regimen_table = '';
			$cols = '';
			if ($pipeline == 'kemsa') {
				$facility_table = 'sync_facility';
				$regimen_table = 'regimen';
				$cols = 'r.id,r.regimen_code,r.regimen_desc';
			} else if ($pipeline == 'kenya_pharma') {
				$facility_table = 'escm_facility';
				$regimen_table = 'escm_regimen';
				$cols = 'r.id,r.code,r.description';
			}
			//Get ART Facilities
			$sql_facility = "
							SELECT f.id,f.name,CAST(f.code AS UNSIGNED) as code,f.services FROM $facility_table f
							ORDER BY code ASC
							";
			$query_f = $this -> db -> query($sql_facility);
			$results_f = $query_f -> result_array();

			//Get regimen list
			$sql_regimen = "
							SELECT r.id,r.regimen_code,r.regimen_desc,SUM(mi.total) as tot_patient,f.code,m.facility_id  
							FROM regimen r 
							LEFT JOIN maps_item mi ON mi.regimen_id=r.id
							LEFT JOIN maps m ON m.id=mi.maps_id
							LEFT JOIN $facility_table f ON f.id =m.facility_id
							GROUP BY r.id
							";
			$query = $this -> db -> query($sql_regimen);
			$results = $query -> result_array();

			//GEnerate excel start here
			$period = date('F-Y', strtotime($period));
			$filename = "Current Patients By ART Sites";
			$dir = "Export";
			$objPHPExcel = $this -> generateExcelDefaultStyle($filename);
			$objPHPExcel -> getActiveSheet() -> mergeCells('A1:C1');
			$objPHPExcel -> getActiveSheet() -> mergeCells('A2:C2');
			$objPHPExcel -> getActiveSheet() -> mergeCells('A3:C3');
			$objPHPExcel -> getActiveSheet() -> mergeCells('A4:C4');
			$objPHPExcel -> getActiveSheet() -> freezePane('D8');
			foreach (range('A','D') as $columnID) {
				$objPHPExcel -> getActiveSheet() -> getColumnDimension($columnID) -> setAutoSize(true);
			}
			$objPHPExcel -> setActiveSheetIndex(0);
			$objPHPExcel -> getActiveSheet() -> SetCellValue('A1', "ART PROGRAM");
			$objPHPExcel -> getActiveSheet() -> SetCellValue('A2', "FACILITIES: CURRENT ART PATIENTS BY REGIMEN ");
			$objPHPExcel -> getActiveSheet() -> SetCellValue('A3', "Period : " . $period);
			$objPHPExcel -> getActiveSheet() -> SetCellValue('A4', "Pipeline : " . strtoupper($pipeline));
			$objPHPExcel -> getActiveSheet() -> SetCellValue('B7', "Regimen ");
			$objPHPExcel -> getActiveSheet() -> SetCellValue('B8', "Site Name ");
			$objPHPExcel -> getActiveSheet() -> SetCellValue('D6', "New Code ");
			$objPHPExcel -> getActiveSheet() -> SetCellValue('D7', "Previous Code ");
			$objPHPExcel -> getActiveSheet() -> SetCellValue('D8', "Site Total ");

			$x = "E";
			foreach ($results as $value) {
				$drug = $value['regimen_code'];
				$objPHPExcel -> getActiveSheet() -> SetCellValue($x . '6', $drug);
				$x++;

			}

			$y = 1;
			$p = 9;
			foreach ($results_f as $value) {
				$id = $value['id'];
				$code = $value['code'];
				$name = $value['name'];
				$objPHPExcel -> getActiveSheet() -> SetCellValue('A' . $p, $y);
				$objPHPExcel -> getActiveSheet() -> SetCellValue('B' . $p, $code);
				$objPHPExcel -> getActiveSheet() -> SetCellValue('C' . $p, $name);

				$x = "E";
				$sub_total = 0;
				foreach ($results as $value) {
					$facility_id = $value['facility_id'];
					$total_pat = $value['tot_patient'];
					if ($facility_id == $id) {//Check if there is data for this facility
						$objPHPExcel -> getActiveSheet() -> SetCellValue($x . $p, $total_pat);
						$sub_total += $total_pat;
					} else {
						$objPHPExcel -> getActiveSheet() -> SetCellValue($x . $p, 0);
					}

					$x++;

				}
				$objPHPExcel -> getActiveSheet() -> SetCellValue("D" . $p, $sub_total);

				$p++;
				$y++;
			}

			$this -> generateExcel($filename, $dir, $objPHPExcel);

		} elseif ($type == 'BYREG_PATIENT') {
			$period = date('Y-m-01', strtotime($period));
			$facility_table = '';
			$regimen_table = '';
			if ($pipeline == 'kemsa') {
				$regimen_table = 'regimen';
				$facility_table = 'sync_facility';
			} else if ($pipeline == 'kenya_pharma') {
				$regimen_table = 'escm_regimen';
				$facility_table = 'escm_facility';
			}

			$sql_regimen = "
						SELECT r.id,r.regimen_code,r.regimen_desc,rc.id as cat_id, rc.name as cat_name,IF(SUM(reg.total)IS NULL,0,SUM(reg.total)) as tot 
						FROM regimen r
						LEFT JOIN(
							SELECT r.id,r.regimen_code,r.regimen_desc,SUM(mi.total) as total 
							FROM regimen r
							LEFT JOIN maps_item mi ON mi.regimen_id=r.id
							LEFT JOIN maps m ON m.id=mi.maps_id
							LEFT JOIN $facility_table f ON f.id=m.facility_id
							WHERE m.period_begin = '" . $period . "'
							AND f.category !='satellite'
							GROUP BY r.id
						) as reg ON reg.id=r.id
						LEFT JOIN regimen_category rc ON rc.id=r.category
						WHERE rc.name IS NOT NULL
						GROUP BY r.id
						ORDER BY cat_name ASC
						";
			$query = $this -> db -> query($sql_regimen);
			$results = $query -> result_array();

			$period = date('F-Y', strtotime($period));
			$filename = "Patients By Regimen";
			$dir = "Export";
			$objPHPExcel = $this -> generateExcelDefaultStyle($filename);
			$objPHPExcel -> getActiveSheet() -> mergeCells('A1:C1');
			$objPHPExcel -> getActiveSheet() -> mergeCells('A2:C2');
			$objPHPExcel -> getActiveSheet() -> mergeCells('A3:C3');
			$objPHPExcel -> getActiveSheet() -> mergeCells('A4:C4');
			$objPHPExcel -> getActiveSheet() -> mergeCells('A5:C5');
			$objPHPExcel -> getActiveSheet() -> getColumnDimension('C') -> setWidth(60);
			$objPHPExcel -> getActiveSheet() -> getStyle('C') -> getAlignment() -> setWrapText(true);
			foreach (range('A','B') as $columnID) {
				$objPHPExcel -> getActiveSheet() -> getColumnDimension($columnID) -> setAutoSize(true);
			}
			$objPHPExcel -> setActiveSheetIndex(0);
			$objPHPExcel -> getActiveSheet() -> SetCellValue('A1', "ART PROGRAM");
			$objPHPExcel -> getActiveSheet() -> SetCellValue('A2', "Pipeline : " . strtoupper($pipeline));
			$objPHPExcel -> getActiveSheet() -> SetCellValue('A3', "PATIENTS BY REGIMEN ");
			$objPHPExcel -> getActiveSheet() -> SetCellValue('A4', "Period : " . $period);
			$objPHPExcel -> getActiveSheet() -> SetCellValue('A5', "As at : " . date('jS F Y'));
			$objPHPExcel -> getActiveSheet() -> SetCellValue('B7', "Regimen Code ");
			$objPHPExcel -> getActiveSheet() -> SetCellValue('C7', "Regimen Details ");
			$objPHPExcel -> getActiveSheet() -> SetCellValue('D7', "Total Patients ");

			$x = 8;
			$a = 0;
			$n = 0;
			$cat_total = 0;
			foreach ($results as $value) {

				$cat_id = $results[$a]['cat_id'];
				$cat_name = $results[$a]['cat_name'];
				$code = $value['regimen_code'];
				$regimen_desc = $value['regimen_desc'];
				$total = $value['tot'];

				if ($a == 0) {
					$objPHPExcel -> getActiveSheet() -> SetCellValue('B' . $x, $cat_name);
					//Regimen Category
				} elseif ($a > 0) {
					$prev = $a - 1;
					if ($results[$prev]['cat_id'] != $results[$a]['cat_id']) {
						$n = 0;
						$objPHPExcel -> getActiveSheet() -> getStyle('C' . $x) -> getFont() -> setBold(true);
						$objPHPExcel -> getActiveSheet() -> getStyle('D' . $x) -> getFont() -> setBold(true);
						$objPHPExcel -> getActiveSheet() -> SetCellValue('C' . $x, "Category Total ");
						$objPHPExcel -> getActiveSheet() -> SetCellValue('D' . $x, $cat_total);
						$cat_total = 0;
						$x++;
						$objPHPExcel -> getActiveSheet() -> SetCellValue('B' . $x, $cat_name);
					} else {
						$cat_total += $total;
						$n++;
						$objPHPExcel -> getActiveSheet() -> SetCellValue('A' . $x, $n);
						$objPHPExcel -> getActiveSheet() -> SetCellValue('B' . $x, $code);
						$objPHPExcel -> getActiveSheet() -> SetCellValue('C' . $x, $regimen_desc);
						$objPHPExcel -> getActiveSheet() -> SetCellValue('D' . $x, $total);
					}
				}
				$objPHPExcel -> getActiveSheet() -> getRowDimension($x) -> setRowHeight(-1);
				$x++;
				$a++;
			}
			$this -> generateExcel($filename, $dir, $objPHPExcel);
		} elseif ($type == "PATIENT_SCALE") {
			$period = date('Y-m-01', strtotime($period));
			$facility_table = '';
			$regimen_table = '';
			$category_id = '';
			if ($pipeline == 'kemsa') {
				$regimen_table = 'regimen';
				$facility_table = 'sync_facility';
				$category_id = 'category';
			} else if ($pipeline == 'kenya_pharma') {
				$regimen_table = 'escm_regimen';
				$facility_table = 'escm_facility';
				$category_id = 'category_id';
			}
			$sql = "SELECT rc.id,rc.name as r_category,m.period_begin,IF(SUM(mi.total) IS NULL,0,SUM(mi.total)) as total,rs.name,
						IF(rc.name LIKE '%adult%' AND rs.name='art','Adult ART Patients',
						IF(rc.name LIKE '%Paediatric%' AND rs.name='art','Paediatric ART Patients',
						IF(rc.name LIKE '%adult%' AND rs.name='pep','PEP Adults',
						IF(rc.name LIKE '%children%' AND rs.name='pep','PEP Children',
						IF(rc.name LIKE '%mother%' AND rs.name='pmtct','PMTCT Mothers',
						IF(rc.name LIKE '%child%' AND rs.name='pmtct','PMTCT Infants',
						'')))))) as Patient_Category
					FROM regimen_category rc
					LEFT JOIN regimen r ON r.category=rc.id
					LEFT JOIN maps_item mi ON mi.regimen_id=r.id
					LEFT JOIN maps m ON m.id=mi.maps_id
					LEFT JOIN regimen_service_type rs ON rs.id=r.type_of_service
					LEFT JOIN $facility_table f ON f.id = m.facility_id
					WHERE m.period_begin IS NOT NULL AND f.category!='satellite'
					GROUP BY rc.name,m.period_begin,Patient_Category ORDER BY period_begin, rs.name,Patient_Category
					 
					";
			//die($sql);
			$query = $this -> db -> query($sql);
			$results = $query -> result_array();
			$count = count($results);
			if ($count > 0) {
				$first_period = $results[0]['period_begin'];
				$first_period = date('M Y', strtotime($first_period));
				$last_period = $results[$count - 1]['period_begin'];
				$last_period = date('M Y', strtotime($last_period));
				if ($first_period == $last_period) {
					$period_date = $first_period;
				} else {
					$period_date = $first_period . ' - ' . $last_period;
				}

			}

			//Generate excel sheet
			$period = date('F-Y', strtotime($period));
			$filename = "ART Patients Scale Up Trends";
			$dir = "Export";
			$objPHPExcel = $this -> generateExcelDefaultStyle($filename);
			$objPHPExcel -> getActiveSheet() -> mergeCells('A1:C1');
			$objPHPExcel -> getActiveSheet() -> mergeCells('A2:C2');
			$objPHPExcel -> getActiveSheet() -> mergeCells('A3:C3');
			$objPHPExcel -> getActiveSheet() -> mergeCells('A4:C4');
			$objPHPExcel -> getActiveSheet() -> mergeCells('B6:D6');
			$objPHPExcel -> getActiveSheet() -> mergeCells('E6:H6');
			foreach (range('A','I') as $columnID) {
				$objPHPExcel -> getActiveSheet() -> getColumnDimension($columnID) -> setWidth(10);
			};

			$objPHPExcel -> getActiveSheet() -> getStyle('B7:I7') -> getAlignment() -> setWrapText(true);
			$objPHPExcel -> getActiveSheet() -> getRowDimension('7') -> setRowHeight(-1);

			$objPHPExcel -> getActiveSheet() -> SetCellValue('A1', "ART PROGRAM");
			$objPHPExcel -> getActiveSheet() -> SetCellValue('A2', "Pipeline : " . strtoupper($pipeline));
			$objPHPExcel -> getActiveSheet() -> SetCellValue('A3', "ART Patients Scale-up by Month and Category ");
			$objPHPExcel -> getActiveSheet() -> SetCellValue('A4', "Period : " . @$period_date);
			$objPHPExcel -> getActiveSheet() -> SetCellValue('I5', "As at : " . date('d F Y'));
			$objPHPExcel -> getActiveSheet() -> SetCellValue('B6', "Patients on ART");
			$objPHPExcel -> getActiveSheet() -> SetCellValue('E6', "Others");
			$objPHPExcel -> getActiveSheet() -> SetCellValue('A7', "Month");
			$objPHPExcel -> getActiveSheet() -> SetCellValue('B7', "Adult ART Patients");
			$objPHPExcel -> getActiveSheet() -> SetCellValue('C7', "Paediatric ART Patients");
			$objPHPExcel -> getActiveSheet() -> SetCellValue('D7', "Total ART Patients");
			$objPHPExcel -> getActiveSheet() -> SetCellValue('E7', "PEP Children");
			$objPHPExcel -> getActiveSheet() -> SetCellValue('F7', "PEP Adults");
			$objPHPExcel -> getActiveSheet() -> SetCellValue('G7', "PMTCT Infants");
			$objPHPExcel -> getActiveSheet() -> SetCellValue('H7', "PMTCT Mothers");
			$objPHPExcel -> getActiveSheet() -> SetCellValue('I7', "Grand Total");
			$x = 0;
			$y = 8;
			$tot_art_adult = 0;
			$tot_art_child = 0;
			$tot_pep_child = 0;
			$tot_pep_adult = 0;
			$tot_pmtct_mother = 0;
			$tot_pmtct_infant = 0;
			$total = 0;
			foreach ($results as $value) {
				$period = date('M-Y', strtotime($value['period_begin']));
				$patient_category = strtolower($value['Patient_Category']);
				$total = $value['total'];
				if ($x == 0) {
					$objPHPExcel -> getActiveSheet() -> SetCellValue('A' . $y, $period);
					if ($patient_category == 'adult art patients') {$tot_art_adult += $total;
					} else if ($patient_category == 'paediatric art patients') {$tot_art_child += $total;
					} else if ($patient_category == 'pep adults') {$tot_pep_adult += $total;
					} else if ($patient_category == 'pep children') {$tot_pep_child += $total;
					} else if ($patient_category == 'pmtct infants') {$tot_pmtct_infant += $total;
					} else if ($patient_category == 'pmtct mothers') {$tot_pmtct_mother += $total;
					}
				} else if ($x > 0) {
					if ($results[$x]['period_begin'] != $results[$x - 1]['period_begin']) {//Period change
						$y++;
						$z = $y - 1;
						$objPHPExcel -> getActiveSheet() -> SetCellValue('A' . $y, $period);
						$objPHPExcel -> getActiveSheet() -> SetCellValue('B' . $z, $tot_art_adult);
						$objPHPExcel -> getActiveSheet() -> SetCellValue('C' . $z, $tot_art_child);
						$objPHPExcel -> getActiveSheet() -> SetCellValue('D' . $z, ($tot_art_child + $tot_art_adult));
						$objPHPExcel -> getActiveSheet() -> SetCellValue('E' . $z, $tot_pep_child);
						$objPHPExcel -> getActiveSheet() -> SetCellValue('F' . $z, $tot_pep_adult);
						$objPHPExcel -> getActiveSheet() -> SetCellValue('G' . $z, $tot_pmtct_infant);
						$objPHPExcel -> getActiveSheet() -> SetCellValue('H' . $z, $tot_pmtct_mother);
						$objPHPExcel -> getActiveSheet() -> SetCellValue('I' . $z, ($tot_pmtct_mother + $tot_pmtct_infant + $tot_pep_adult + $tot_pep_child + $tot_art_child + $tot_art_adult));
						//Initialize all totals
						$tot_art_adult = 0;
						$tot_art_child = 0;
						$tot_pep_child = 0;
						$tot_pep_adult = 0;
						$tot_pmtct_mother = 0;
						$tot_pmtct_infant = 0;
						if ($patient_category == 'adult art patients') {$tot_art_adult += $total;
						} else if ($patient_category == 'paediatric art patients') {$tot_art_child += $total;
						} else if ($patient_category == 'pep adults') {$tot_pep_adult += $total;
						} else if ($patient_category == 'pep children') {$tot_pep_child += $total;
						} else if ($patient_category == 'pmtct infants') {$tot_pmtct_infant += $total;
						} else if ($patient_category == 'pmtct mothers') {$tot_pmtct_mother += $total;
						}
					} else {
						if ($patient_category == 'adult art patients') {$tot_art_adult += $total;
						} else if ($patient_category == 'paediatric art patients') {$tot_art_child += $total;
						} else if ($patient_category == 'pep adults') {$tot_pep_adult += $total;
						} else if ($patient_category == 'pep children') {$tot_pep_child += $total;
						} else if ($patient_category == 'pmtct infants') {$tot_pmtct_infant += $total;
						} else if ($patient_category == 'pmtct mothers') {$tot_pmtct_mother += $total;
						}
					}
				}
				$x++;
				//If end of loop, append data
				if ($count == $x) {
					$z = $y;
					$objPHPExcel -> getActiveSheet() -> SetCellValue('B' . $z, $tot_art_adult);
					$objPHPExcel -> getActiveSheet() -> SetCellValue('C' . $z, $tot_art_child);
					$objPHPExcel -> getActiveSheet() -> SetCellValue('D' . $z, ($tot_art_child + $tot_art_adult));
					$objPHPExcel -> getActiveSheet() -> SetCellValue('E' . $z, $tot_pep_child);
					$objPHPExcel -> getActiveSheet() -> SetCellValue('F' . $z, $tot_pep_adult);
					$objPHPExcel -> getActiveSheet() -> SetCellValue('G' . $z, $tot_pmtct_infant);
					$objPHPExcel -> getActiveSheet() -> SetCellValue('H' . $z, $tot_pmtct_mother);
					$objPHPExcel -> getActiveSheet() -> SetCellValue('I' . $z, ($tot_pmtct_mother + $tot_pmtct_infant + $tot_pep_adult + $tot_pep_child + $tot_art_child + $tot_art_adult));
				};
			}
			$this -> generateExcel($filename, $dir, $objPHPExcel);

		}

	}

	public function generateExcelDefaultStyle($title = "") {
		$dir = "Export";
		$inputFileType = 'Excel5';
		$inputFileName = $_SERVER['DOCUMENT_ROOT'] . '/NASCOP/assets/template/excel.xls';
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
		//$objPHPExcel = new PHPExcel();
		//$objPHPExcel -> setActiveSheetIndex(0);
		$objPHPExcel -> getDefaultStyle() -> getFont() -> setName('Book Antiqua') -> setSize(10);
		$objPHPExcel -> getActiveSheet() -> setTitle($title);
		return $objPHPExcel;
	}

	public function generateExcel($filename = "", $dir = "", $objPHPExcel = "") {
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
		header("Cache-Control: no-store, no-cache, must-revalidate");
		header("Cache-Control: post-check=0, pre-check=0", false);
		header("Pragma: no-cache");
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment;filename=' . $filename);
		$filename = $dir . '/' . $filename . '.xls';
		$objWriter = new PHPExcel_Writer_Excel5($objPHPExcel);
		$objWriter -> save($filename);
		$objPHPExcel -> disconnectWorksheets();
		unset($objPHPExcel);
		if (file_exists($filename)) {
			$filename = str_replace("#", "%23", $filename);
			redirect($filename);
		}
	}

	public function getCommodity($type = "SOH") {
		$columns = array('#', 'Reporting Period', 'Pipeline', 'Action');
		$links = array('dashboard_management/download/' . $type => '<i class="icon-download-alt"></i>download');
		$table_name = $type;

		if ($type == "CONS") {
			//Get eSCM orders
			$escm_orders = Escm_Orders::getAll();
			$list = "";
			$order_list = array();
			$cdrr_nascop = array();
			$cdrr_escm = array();
			$result = array();
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
		} elseif ($type == "SOH") {
			//Get orders from cdrr which have distinct period
			$cdrr_data = Cdrr::getCdrrPeriod();
			$counter = 0;
			foreach ($cdrr_data as $cdrr) {
				$result[$counter]['period'] = date('F-Y', strtotime($cdrr['period_begin']));
				$result[$counter]['pipeline'] = "National Level";
				$counter++;
			}
		}

		echo $this -> showTable($columns, $result, $links, $table_name);
	}

	public function getPatients($type = "ART_PATIENT") {
		$curren_period = date('Y-m-01');
		$data = array();
		if ($type == "BYPIPELINE_ART") {//Number of ART Patients BY Pipeline
			$data['container'] = 'report_by_pipeline';
			$data['title'] = 'Total Patients By Pipeline';
			$data['chartTitle'] = 'No of Patients on ART By Pipeline';
			//Get total patients for Kenya Pharma
			$sql_kp = "
					SELECT SUM(m.art_adult) as total_adult_kp,SUM(m.art_child) as total_paed_kp 
					FROM maps m 
					LEFT JOIN escm_maps em ON em.maps_id = m.id 
					WHERE STR_TO_DATE(m.period_begin,'%Y-%m-%d')  ='" . $curren_period . "' 
					AND em.maps_id IS NOT NULL
					";
			$query = $this -> db -> query($sql_kp);
			$result = $query -> result_array();
			$tot_adult_kp = 0;
			$tot_paed_kp = 0;
			$total_kp = 0;
			if (count($result) > 0) {
				$tot_adult_kp = (int)$result[0]['total_adult_kp'];
				$tot_paed_kp = (int)$result[0]['total_paed_kp'];
				$total_kp = $tot_adult_kp + $tot_paed_kp;
			}
			//Get totals for Kemsa
			$sql_kemsa = "
					SELECT SUM(m.art_adult) as total_adult_kemsa,SUM(m.art_child) as total_paed_kemsa 
					FROM maps m 
					LEFT JOIN escm_maps em ON em.maps_id = m.id 
					WHERE STR_TO_DATE(m.period_begin,'%Y-%m-%d')  ='" . $curren_period . "' 
					AND em.maps_id IS NULL
					";
			$query = $this -> db -> query($sql_kemsa);
			$result = $query -> result_array();
			$tot_adult_kemsa = 0;
			$tot_paed_kemsa = 0;
			$total_kemsa = 0;
			if (count($result) > 0) {
				$tot_adult_kemsa = (int)$result[0]['total_adult_kemsa'];
				$tot_paed_kemsa = (int)$result[0]['total_paed_kemsa'];
				$total_kemsa = $tot_adult_kemsa + $tot_paed_kemsa;
			}

			$total_adults = $tot_adult_kemsa + $tot_adult_kp;
			$total_paeds = $tot_paed_kemsa + $tot_paed_kp;
			$grand_total = $total_adults + $total_paeds;
			/*
			 if($total_kemsa==0 && $total_kp==0){
			 echo "<div style='text-align:center' class=''> No data reported yet for this period </div>";die();
			 }
			 $colors = array('#66aaf7','#f66c6f','#8bbc21','#910000','#1aadce','#492970','#f28f43','#77a1e5','#c42525','#a6c96a');

			 $myData = array(
			 array(
			 'y'=>$total_kp,
			 'color'=>$colors[1],
			 'drilldown'=>array(
			 'name'=>'Kenya Pharma Categories',
			 'categories'=>array('Adults', 'Peads'),
			 'data'=> array($tot_adult_kp,$tot_paed_kp),
			 'color'=> $colors[0]
			 )
			 ),
			 array(
			 'y'=>$total_kemsa,
			 'color'=>$colors[4],
			 'drilldown'=>array(
			 'name'=>'Kemsa Categories',
			 'categories'=>array('Adults', 'Peads'),
			 'data'=> array($tot_adult_kemsa,$tot_paed_kemsa),
			 'color'=> $colors[6]
			 )
			 ),

			 );

			 $myData =json_encode($myData);
			 //echo $myData;die();
			 $categories = array('Kenya Pharma','Kemsa');
			 $categories =json_encode($categories);

			 $data['categories'] = $categories;
			 $data['myData'] = $myData;
			 //$this -> load -> view('dashboard/chart_report_pie_2l_v', $data);
			 */

			//Generate table for Number of Patients on ART
			$tmpl = array('table_open' => '<table id="" class="table table-bordered table-striped dash_tables">');
			$this -> table -> set_template($tmpl);
			$this -> table -> set_heading('', 'Pipeline', '<center>Kemsa</center>', '<center>Kenya Pharma</center>', '<center>Line Total</center>');
			$this -> table -> add_row('', '<h5>Adults</h5>', '<center>' . $tot_adult_kemsa . '</center>', '<center>' . $tot_adult_kp . '</center>', '<center>' . $total_adults . '</center>');
			$this -> table -> add_row('', '<h5>Paeds</h5>', '<center>' . $tot_paed_kemsa . '</center>', '<center>' . $tot_paed_kp . '</center>', '<center>' . $total_paeds . '</center>');
			$this -> table -> add_row('', '<h5>TOTAL</h5>', '<b><center>' . $total_kemsa . '</center></b>', '<b><center>' . $total_kp . '</center></b>', '<b><center>' . $grand_total . '</center></b>');
			$table_display = $this -> table -> generate();
			echo $table_display;
		} elseif ($type == "ADULT_ART") {
			//Bar Chart
			$data = array();
			$list = array();
			$dataArray = array();
			$columns = array();
			$total_series = array();
			$series = array();
			$categories = array();
			$pipelines = array("kemsa", "kenya_pharma");
			$values1 = array(0, 0, 0, 0, 0, 0, 0, 0);
			$values2 = array(0, 0, 0, 0, 0, 0, 0, 0);

			foreach ($pipelines as $pipeline) {
				if ($pipeline == "kemsa") {
					$regimen_table = "sync_regimen";
				} else if ($pipeline == "kenya_pharma") {
					$join_kp = "SELECT mr.name as regimen_desc,test.total
                                FROM escm_regimen mr
                                LEFT JOIN 
								(SELECT r.id AS regimen_id, SUM( mi.total ) AS total
								FROM escm_regimen r
								LEFT JOIN maps_item mi ON mi.regimen_id = r.id
								WHERE(r.code IN ('AF1A',  'AF1B',  'AF2A',  'AF2B',  'AF3A',  'AF3B')
								AND mi.maps_id IN(SELECT maps_id FROM escm_maps))
								GROUP BY r.code) as test ON mr.id=test.regimen_id
								WHERE mr.code IN ('AF1A',  'AF1B',  'AF2A',  'AF2B',  'AF3A',  'AF3B')
								GROUP BY mr.code";

					$join1_kp = "SELECT mr.name as regimen_desc,test.total
                                FROM escm_regimen mr
                                LEFT JOIN 
								(SELECT r.id AS regimen_id, SUM( mi.total ) AS total
								FROM escm_regimen r
								LEFT JOIN maps_item mi ON mi.regimen_id = r.id
								WHERE(r.code IN('AS1A','AS1B','AS2A','AS2B','AS3A','AS3B','AS4A','AS4B')
								AND mi.maps_id IN(SELECT maps_id FROM escm_maps))
								GROUP BY r.code) as test ON mr.id=test.regimen_id
								WHERE mr.code IN('AS1A','AS1B','AS2A','AS2B','AS3A','AS3B','AS4A','AS4B')
								GROUP BY mr.code";

					$join2_kp = "SELECT mr.name as regimen_desc,test.total
                                FROM escm_regimen mr
                                LEFT JOIN 
								(SELECT r.id AS regimen_id, SUM( mi.total ) AS total
								FROM escm_regimen r
								LEFT JOIN maps_item mi ON mi.regimen_id = r.id
								WHERE (r.code NOT IN('AF1A','AF1B','AF2A','AF2B','AF3A','AF3B','AS1A','AS1B','AS2A','AS2B','AS3A','AS3B','AS4A','AS4B')
								AND mi.maps_id IN(SELECT maps_id FROM escm_maps))
								GROUP BY r.code) as test ON mr.id=test.regimen_id
								WHERE mr.code NOT IN('AF1A','AF1B','AF2A','AF2B','AF3A','AF3B','AS1A','AS1B','AS2A','AS2B','AS3A','AS3B','AS4A','AS4B')
								GROUP BY mr.code";
					$regimen_table = "escm_regimen";
				}
				$regimens = array("D4T+3TC+NVP", "D4T+3TC+EFV", "AZT+3TC+NVP", "AZT+3TC+EFV", "TDF+3TC+NVP", "TDF+3TC+EFV", "2ND LINE", "OTHER REGIMENS");

				$regimen_lines['first_line'] = "WHERE r.code IN('AF1A','AF1B','AF2A','AF2B','AF3A','AF3B') GROUP BY r.code";
				$regimen_lines['second_line'] = "WHERE r.code IN('AS1A','AS1B','AS2A','AS2B','AS3A','AS3B','AS4A','AS4B')";
				$regimen_lines['other_lines'] = "WHERE r.code NOT IN('AF1A','AF1B','AF2A','AF2B','AF3A','AF3B','AS1A','AS1B','AS2A','AS2B','AS3A','AS3B','AS4A','AS4B')";

				foreach ($regimen_lines as $index => $regimen_list) {
					//Query to retrieve patients in this regimens
					$sql = "SELECT r.name as regimen_desc,SUM(mi.total)as total
					        FROM $regimen_table r
					        LEFT JOIN maps_item mi ON mi.regimen_id=r.id
					        $regimen_list";

					if ($pipeline == "kenya_pharma" && $index == "first_line") {
						$sql = $join_kp;
					} else if ($pipeline == "kenya_pharma" && $index == "second_line") {
						$sql = $join1_kp;
					} else if ($pipeline == "kenya_pharma" && $index == "other_lines") {
						$sql = $join2_kp;
					}
					$query = $this -> db -> query($sql);
					$results = $query -> result_array();
					if ($results) {
						foreach ($results as $value) {
							if ($pipeline == "kemsa") {
								$total = $value['total'];
								if ($index == "first_line") {
									$value = strtoupper(str_replace(" ", "", $value['regimen_desc']));
								} else if ($index == "second_line") {
									$value = "2ND LINE";
								} else if ($index == "other_lines") {
									$value = "OTHER REGIMENS";
								}
								$key = array_search($value, $regimens);
								$values1[$key] = @(int)$total;
							} else if ($pipeline == "kenya_pharma") {
								$total = $value['total'];
								if ($index == "first_line") {
									$value = strtoupper(str_replace(" ", "", $value['regimen_desc']));
								} else if ($index == "second_line") {
									$value = "2ND LINE";
								} else if ($index == "other_lines") {
									$value = "OTHER REGIMENS";
								}
								$key = array_search($value, $regimens);
								$values2[$key] = @(int)$total;
							}
						}
					}
				}
			}

			$add = function($a, $b) {
				return $a + $b;
			};

			$values = array_map($add, $values1, $values2);

			$resultArray = array( array('name' => 'Number of Patients', 'data' => $values));
			foreach ($regimens as $key => $value) {
				$categories[$key] = $value;
			}

			$resultArray = json_encode($resultArray);
			//var_dump($resultArray);die();
			$categories = json_encode($categories);
			$data['resultArraySize'] = 7;
			$data['container'] = 'report_adult_chart';
			$data['chartType'] = 'bar';
			$data['chartTitle'] = 'Adult Patients on ART';
			$data['categories'] = $categories;
			$data['yAxix'] = 'No of Patients';
			$data['name'] = 'Adult Patients';
			$data['resultArray'] = $resultArray;
			$this -> load -> view('dashboard/chart_report_bar_v', $data);
		} elseif ($type == "PAED_ART") {//Bar Chart
			$data = array();
			$list = array();
			$dataArray = array();
			$columns = array();
			$total_series = array();
			$series = array();
			$categories = array();
			$pipelines = array("kemsa", "kenya_pharma");
			$values1 = array(0, 0, 0, 0, 0, 0, 0, 0);
			$values2 = array(0, 0, 0, 0, 0, 0, 0, 0);

			foreach ($pipelines as $pipeline) {
				if ($pipeline == "kemsa") {
					$regimen_table = "sync_regimen";
				} else if ($pipeline == "kenya_pharma") {
					$join_kp = "SELECT mr.name as regimen_desc,test.total
                                FROM escm_regimen mr
                                LEFT JOIN 
								(SELECT r.id AS regimen_id, SUM( mi.total ) AS total
								FROM escm_regimen r
								LEFT JOIN maps_item mi ON mi.regimen_id = r.id
								WHERE(r.code IN ('CF1A',  'CF1B', 'CF1C'  ,'CF2A',  'CF2B','CF2C', 'CF2D' ,'CF3A',  'CF3B')
								AND mi.maps_id IN(SELECT maps_id FROM escm_maps))
								GROUP BY r.code) as test ON mr.id=test.regimen_id
								WHERE mr.code IN ('CF1A',  'CF1B', 'CF1C' , 'CF2A',  'CF2B' ,'CF2C',  'CF2D', 'CF3A',  'CF3B')
								GROUP BY mr.code";

					$join1_kp = "SELECT mr.name as regimen_desc,test.total
                                FROM escm_regimen mr
                                LEFT JOIN 
								(SELECT r.id AS regimen_id, SUM( mi.total ) AS total
								FROM escm_regimen r
								LEFT JOIN maps_item mi ON mi.regimen_id = r.id
								WHERE(r.code IN('CS1A',  'CS1B', 'CS1C'  ,'CS2A', 'CS2B','CS2C', 'CS2D' ,'CS3A',  'CS3B')
								AND mi.maps_id IN(SELECT maps_id FROM escm_maps))
								GROUP BY r.code) as test ON mr.id=test.regimen_id
								WHERE mr.code IN('CS1A',  'CS1B', 'CS1C'  ,'CS2A', 'CS2B','CS2C', 'CS2D' ,'CS3A',  'CS3B')
								GROUP BY mr.code";

					$join2_kp = "SELECT mr.name as regimen_desc,test.total
                                FROM escm_regimen mr
                                LEFT JOIN 
								(SELECT r.id AS regimen_id, SUM( mi.total ) AS total
								FROM escm_regimen r
								LEFT JOIN maps_item mi ON mi.regimen_id = r.id
								WHERE (r.code NOT IN('CF1A',  'CF1B', 'CF1C' , 'CF2A',  'CF2B' ,'CF2C',  'CF2D', 'CF3A',  'CF3B','CS1A',  'CS1B', 'CS1C'  ,'CS2A', 'CS2B','CS2C', 'CS2D' ,'CS3A',  'CS3B')
								AND mi.maps_id IN(SELECT maps_id FROM escm_maps))
								GROUP BY r.code) as test ON mr.id=test.regimen_id
								WHERE mr.code NOT IN('CF1A',  'CF1B', 'CF1C' , 'CF2A',  'CF2B' ,'CF2C',  'CF2D', 'CF3A',  'CF3B','CS1A',  'CS1B', 'CS1C'  ,'CS2A', 'CS2B','CS2C', 'CS2D' ,'CS3A',  'CS3B')
								GROUP BY mr.code";
					$regimen_table = "escm_regimen";
				}
				$regimens = array("D4T+3TC+NVP", "D4T+3TC+EFV", "AZT+3TC+NVP", "AZT+3TC+EFV", "TDF+3TC+NVP", "TDF+3TC+EFV", "2ND LINE", "OTHER REGIMENS");

				$regimen_lines['first_line'] = "WHERE r.code IN('CF1A',  'CF1B', 'CF1C' , 'CF2A',  'CF2B' ,'CF2C',  'CF2D', 'CF3A',  'CF3B') GROUP BY r.code";
				$regimen_lines['second_line'] = "WHERE r.code IN('CS1A',  'CS1B', 'CS1C'  ,'CS2A', 'CS2B','CS2C', 'CS2D' ,'CS3A',  'CS3B')";
				$regimen_lines['other_lines'] = "WHERE r.code NOT IN('CF1A',  'CF1B', 'CF1C' , 'CF2A',  'CF2B' ,'CF2C',  'CF2D', 'CF3A',  'CF3B','CS1A',  'CS1B', 'CS1C'  ,'CS2A', 'CS2B','CS2C', 'CS2D' ,'CS3A',  'CS3B')";

				foreach ($regimen_lines as $index => $regimen_list) {
					//Query to retrieve patients in this regimens
					$sql = "SELECT r.name as regimen_desc,SUM(mi.total)as total
					        FROM $regimen_table r
					        LEFT JOIN maps_item mi ON mi.regimen_id=r.id
					        $regimen_list";

					if ($pipeline == "kenya_pharma" && $index == "first_line") {
						$sql = $join_kp;
					} else if ($pipeline == "kenya_pharma" && $index == "second_line") {
						$sql = $join1_kp;
					} else if ($pipeline == "kenya_pharma" && $index == "other_lines") {
						$sql = $join2_kp;
					}
					$query = $this -> db -> query($sql);
					$results = $query -> result_array();
					if ($results) {
						foreach ($results as $value) {
							if ($pipeline == "kemsa") {
								$total = $value['total'];
								if ($index == "first_line") {
									$value = strtoupper(str_replace(" ", "", $value['regimen_desc']));
								} else if ($index == "second_line") {
									$value = "2ND LINE";
								} else if ($index == "other_lines") {
									$value = "OTHER REGIMENS";
								}
								$key = array_search($value, $regimens);
								$values1[$key] = @(int)$total;
							} else if ($pipeline == "kenya_pharma") {
								$total = $value['total'];
								if ($index == "first_line") {
									$value = strtoupper(str_replace(" ", "", $value['regimen_desc']));
								} else if ($index == "second_line") {
									$value = "2ND LINE";
								} else if ($index == "other_lines") {
									$value = "OTHER REGIMENS";
								}
								$key = array_search($value, $regimens);
								$values2[$key] = @(int)$total;
							}
						}
					}
				}
			}

			$add = function($a, $b) {
				return $a + $b;
			};

			$values = array_map($add, $values1, $values2);

			$regimens = array("AZT+3TC+NVP", "AZT+3TC+EFV", "ABC+3TC+NVP", "ABC+3TC+EFV", "ABC+3TC+LPv/r", "AZT+3TC+LPv/r", "2ND LINE", "OTHER REGIMENS");

			$resultArray = array( array('name' => 'Number of Patients', 'data' => $values));

			foreach ($regimens as $key => $value) {
				$categories[$key] = $value;
			}
			$resultArray = json_encode($resultArray);
			$categories = json_encode($categories);
			$data['resultArraySize'] = 7;
			$data['container'] = 'report_paed_chart';
			$data['chartType'] = 'bar';
			$data['title'] = 'Reporting Analysis';
			$data['chartTitle'] = 'Paediatric Patients on ART';
			$data['categories'] = $categories;
			$data['yAxix'] = 'No of Patients';
			$data['name'] = 'Paediatric Patients';
			$data['resultArray'] = $resultArray;
			$this -> load -> view('dashboard/chart_report_bar_v', $data);
		} else {
			$columns = array('#', 'Reporting Period', 'Pipeline', 'Action');
			$links = array('dashboard_management/download/' . $type => '<i class="icon-download-alt"></i>download');
			//Get eSCM orders
			$escm_patients = Escm_Maps::getAll();
			$list = "";
			$order_list = array();
			$maps_nascop = array();
			$maps_escm = array();
			$table_name = $type;
			$result = array();
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

	}

	public function getReport($type = '') {
		$data = array();
		$list = array();
		$dataArray = array();
		$columns = array();
		$total_series = array();
		$series = array();
		$categories = array();

		$sixmonthback = date('F-Y', strtotime(date("F-Y", mktime()) . " - 182 day"));
		$x = 0;
		$kemsaArray = array();
		$kpArray = array();
		$nationalArray = array();
		while ($x <= 5) {
			$period = date("F-Y", strtotime(date("Y-m-d", strtotime($sixmonthback)) . " +" . ($x + 1) . " month"));
			$first = date('Y-m-01', strtotime($period));
			$tenth = date('Y-m-10', strtotime($period));
			$last_day = date('Y-m-t', strtotime($period));
			$sql = "SELECT (
							SELECT COUNT(DISTINCT(c.facility_id)) as kemsa FROM cdrr c
							LEFT JOIN escm_orders ec ON ec.cdrr_id=c.id          
							WHERE c.created BETWEEN '" . $first . "' AND  '" . $tenth . "
							WHERE c.id NOT IN ec.cdrr_id'
							AND(c.code='D-CDRR' OR c.code='F-CDRR_packs')
							) as kemsa_count,
							(SELECT COUNT(DISTINCT(c.facility_id)) as kenya_pharma FROM cdrr c
							INNER JOIN escm_orders ec ON ec.cdrr_id=c.id
							WHERE c.created BETWEEN '" . $first . "' AND  '" . $tenth . "'
							AND(c.code='D-CDRR' OR c.code='F-CDRR_packs')) as kenya_pharma_count,
							(
							SELECT COUNT(DISTINCT(c.facility_id)) as national_total FROM cdrr c
							WHERE c.created BETWEEN '" . $first . "' AND  '" . $last_day . "'
							AND(c.code='D-CDRR' OR c.code='F-CDRR_packs')
							) as national_count,
							(
							SELECT COUNT(DISTINCT(id)) as total_kp FROM escm_facility
							) as total_arv_kp,
							(
							SELECT COUNT(DISTINCT(id)) as total_kemsa FROM sync_facility
							) as total_arv_kemsa
							
							FROM cdrr LIMIT 1
							";

			//INNER JOIN maps m ON m.period_begin=c.period_begin

			$query = $this -> db -> query($sql);
			$result = $query -> result_array();
			$total_arv_kp = 0;
			$total_arv_kemsa = 0;
			$national_total = 0;
			if (count($result) > 0) {
				$total_arv_kp = (int)$result[0]['total_arv_kp'];
				$total_arv_kemsa = (int)$result[0]['total_arv_kemsa'];
				$national_total = $total_arv_kemsa + $total_arv_kp;
			}

			if ($total_arv_kemsa == 0) {
				$kemsa_rate = 0;
			} else {
				$kemsa_rate = ((int)$result[0]['kemsa_count'] / $total_arv_kemsa) * 100;
			}
			if ($total_arv_kp == 0) {
				$kp_rate = 0;
			} else {
				$kp_rate = ((int)$result[0]['kenya_pharma_count'] / $total_arv_kp) * 100;
			}
			if ($national_total == 0) {
				$national_rate = 0;
			} else {
				$national_rate = ((int)$result[0]['national_count'] / $national_total) * 100;
				$national_rate = round($national_rate, 2);
			}

			$kemsaArray[] = round($kemsa_rate, 2);
			$kpArray[] = round($kp_rate, 2);
			$nationalArray[] = round($national_rate, 2);
			$categories[$x] = $period;
			$x++;
		}
		$resultArray = array( array('name' => 'Kemsa/LMU - Reporting timeliness (By 10th)', 'data' => $kemsaArray), array('name' => 'Kenya Pharma - Reporting timeliness (By 10th)', 'data' => $kpArray), array('name' => 'National Reporting Rate', 'data' => $nationalArray));
		//echo var_dump($resultArray);die();
		$resultArray = json_encode($resultArray);
		$categories = json_encode($categories);
		$data['container'] = 'report_sum_chart';
		$data['chartType'] = 'bar';
		$data['title'] = 'Reporting Analysis';
		$data['chartTitle'] = 'Reporting rates for ARV Ordering points for KP and KEMSA pipelines';
		$data['categories'] = $categories;
		$data['yAxix'] = '% Reporting Rate';
		$data['resultArray'] = $resultArray;
		$this -> load -> view('dashboard/chart_report_line_v', $data);

	}

	public function reportSummary($type = "") {

		if ($type == 'table') {//Reporting site summary
			//Total Number of ARV Sites
			$sql_kemsa = "SELECT COUNT(f.code) as total FROM sync_facility f";
			$query = $this -> db -> query($sql_kemsa);
			$results = $query -> result_array();
			$total_kemsa = $results[0]['total'];
			$sql_kenyap = "SELECT COUNT(f.code) as total FROM escm_facility f";
			$query = $this -> db -> query($sql_kenyap);
			$results = $query -> result_array();
			$total_kenya_pharma = $results[0]['total'];
			$total_arv_sites = $total_kemsa + $total_kenya_pharma;

			//Sites using ADT
			$sql = "SELECT COUNT(id) as total FROM adt_sites";
			$query = $this -> db -> query($sql);
			$results = $query -> result_array();
			$tot_adtsites = $results[0]['total'];

			//Sites reported by 10th
			$tenth = date('Y-m-10');
			$first = date('Y-m-01');
			$last_day = date('Y-m-t');
			$sql_tenth = "SELECT COUNT(DISTINCT(c.facility_id)) as total FROM cdrr c
							WHERE c.created BETWEEN '" . $first . "' AND  '" . $tenth . "'";
			//INNER JOIN maps m ON m.period_begin=c.period_begin

			$query = $this -> db -> query($sql_tenth);
			$results = $query -> result_array();
			$tot_tenth = $results[0]['total'];
			if ($tot_adtsites == 0) {
				$x = 0;
			} else {
				$x = number_format(($tot_tenth / $total_arv_sites) * 100, 2);
			}

			//Sites that have reported this month
			$sql_report = "SELECT COUNT(DISTINCT(c.facility_id)) as total FROM cdrr c
							WHERE c.created BETWEEN '" . $first . "' AND  '" . $last_day . "'";
			//INNER JOIN maps m ON m.period_begin=c.period_begin
			$query = $this -> db -> query($sql_report);
			$results = $query -> result_array();
			$tot_reportsites = $results[0]['total'];
			if ($tot_adtsites == 0) {
				$y = 0;
			} else {
				$y = number_format(($tot_reportsites / $total_arv_sites) * 100, 2);
			}
			$tmpl = array('table_open' => '<table id="" class="table table-bordered table-striped">');
			$this -> table -> set_template($tmpl);
			$this -> table -> set_heading('', 'Description', 'Total No', 'Rate');
			$this -> table -> add_row('', 'Total No of ARV Sites', $total_arv_sites, ' - ');
			$this -> table -> add_row('', 'No of Sites with Web ADT Installed', $tot_adtsites, ' - ');
			$this -> table -> add_row('', 'No of Sites That Have Reported this month (By the 10th)', $tot_tenth, $x . ' %');
			$this -> table -> add_row('', 'Total No of Sites That Have Reported this month', $tot_reportsites, $y . ' %');
			$table_display = $this -> table -> generate();
			echo $table_display;
		} else if ($type == 'site_reporting') {//Reporting site Analysis
			$data = array();
			//Sites reported by 10th
			$tenth = date('Y-m-10');
			$first = date('Y-m-01');
			$last_day = date('Y-m-t');
			$sql_tenth = "SELECT COUNT(DISTINCT(c.facility_id)) as total FROM cdrr c
							WHERE c.created BETWEEN '" . $first . "' AND  '" . $tenth . "'";
			//INNER JOIN maps m ON m.period_begin=c.period_begin

			$query = $this -> db -> query($sql_tenth);
			$results = $query -> result_array();
			$tot_tenth = $results[0]['total'];
			//$x=($tot_tenth/$tot_adtsites)*100;

			//Sites that have reported
			$sql_report = "SELECT COUNT(DISTINCT(c.facility_id)) as total FROM cdrr c						
							WHERE c.created BETWEEN '" . $first . "' AND  '" . $last_day . "'";
			//INNER JOIN maps m ON m.period_begin=c.period_begin

			$query = $this -> db -> query($sql_report);
			$results = $query -> result_array();
			$tot_reportsites = $results[0]['total'];
			//$y=($tot_reportsites/$tot_adtsites)*100;

			$data['tot_reportsites'] = $tot_reportsites;
			$data['tot_tenth'] = $tot_tenth;
			$data['container'] = 'reporting_site_summary';
			$data['chartType'] = 'pie';
			$data['title'] = 'Reporting Analysis Summary';
			$this -> load -> view('dashboard/chart_report_site_v', $data);
		} else {
			$data = array();
			//Total Number of ARV Sites
			$sql_kemsa = "SELECT COUNT(f.code) as total FROM sync_facility f";
			$query = $this -> db -> query($sql_kemsa);
			$results = $query -> result_array();
			$total_kemsa = $results[0]['total'];
			$sql_kenyap = "SELECT COUNT(f.code) as total FROM escm_facility f";
			$query = $this -> db -> query($sql_kenyap);
			$results = $query -> result_array();
			$total_kenya_pharma = $results[0]['total'];
			$total_arv_sites = $total_kemsa + $total_kenya_pharma;

			//Sites using ADT
			$sql = "SELECT COUNT(id) as total FROM adt_sites";
			$query = $this -> db -> query($sql);
			$results = $query -> result_array();
			$tot_adt_sites = $results[0]['total'];

			$data['total_arv_sites'] = $total_arv_sites;
			$data['total_adt_sites'] = $tot_adt_sites;
			$data['container'] = 'report_summary';
			$data['chartType'] = 'pie';
			$data['title'] = 'Reporting Analysis Summary';
			$this -> load -> view('dashboard/chart_report_summary_v', $data);
		}

	}

	public function showTable($columns, $data = array(), $links = array(), $table_name = "") {
		$this -> load -> library('table');
		$tmpl = array('table_open' => '<table id=' . $table_name . '_listing class="table table-bordered table-striped tbl_nat_dashboard">');
		$this -> table -> set_template($tmpl);
		$this -> table -> set_heading($columns);
		$link_values = "";
		$pipeline = "";
		foreach ($data as $mydata) {
			if ($mydata['pipeline'] == 'Kenya Pharma') {
				$pipeline = 'kenya_pharma';
			} else if ($mydata['pipeline'] == 'Kemsa') {

				$pipeline = 'kemsa';
			}
			//Set Up links
			foreach ($links as $i => $link) {
				$link_values .= "<a href='" . site_url($i . '/' . $mydata['period'] . '/' . $pipeline) . "'>$link</a> | ";
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
