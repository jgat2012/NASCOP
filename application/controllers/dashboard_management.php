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
		$data['report_period'] = $this -> fix_bug(cdrr::getOrderPeriods());
		//Includes facilities that reported for both maps and cdrrs
		$data['maps_report_period'] = $this -> fix_bug(maps::getReportPeriods());
		$data['county_period'] = $this -> getCountyList();
		$data['facility_period'] = $this -> getFacilityList();
		$data['eid_period'] = $this -> fix_bug($this -> getEidPeriod());
		$data['eid_county'] = $this -> getEidCounty();
		$data['eid_facility'] = $this -> getEidFacility();
		$data['eid_adt_period'] = $this -> fix_bug($this -> getEidADTPeriod());
		$data['eid_adt_county'] = $this -> getEidADTCounty();
		$data['eid_adt_facility'] = $this -> getEidADTFacility();
		$this -> base_params($data);
	}

	public function getEidADTPeriod() {
		$sql = "SELECT LAST_DAY(dateinitiatedontreatment) as period_begin FROM eid_master WHERE dateinitiatedontreatment!='' AND dateinitiatedontreatment<=CURDATE() AND LAST_DAY( dateinitiatedontreatment ) IS NOT NULL  AND dateinitiatedontreatment !='1970-01-01' GROUP BY YEAR(dateinitiatedontreatment),MONTH(dateinitiatedontreatment) ORDER BY dateinitiatedontreatment desc";
		$query = $this -> db -> query($sql);
		return $results = $query -> result_array();
	}

	public function getEidPeriod() {
		$sql = "SELECT LAST_DAY(enrollment_date) as period_begin FROM eid_info WHERE enrollment_date !='1970-01-01' AND LAST_DAY( enrollment_date ) IS NOT NULL  GROUP BY YEAR(enrollment_date),MONTH(enrollment_date) ORDER BY enrollment_date desc";
		$query = $this -> db -> query($sql);
		return $results = $query -> result_array();
	}

	public function getEidADTCounty() {
		$sql = "SELECT c.id,c.county
			    FROM eid_master em
			    LEFT JOIN facilities f ON f.facilitycode=em.facilitycode
			    LEFT JOIN counties c ON c.id=f.county
			    WHERE c.id !=''
			    GROUP BY c.id";
		$query = $this -> db -> query($sql);
		$results = $query -> result_array();
		$counties = array();
		foreach ($results as $result) {
			$counties[$result['id']] = $result['county'];
		}
		$counties = array_unique($counties);
		asort($counties);
		return $counties;
	}

	public function getEidCounty() {
		$sql = "SELECT c.id,c.county
			    FROM eid_info ei
			    LEFT JOIN facilities f ON f.facilitycode=ei.facility_code
			    LEFT JOIN counties c ON c.id=f.county
			    GROUP BY c.id";
		$query = $this -> db -> query($sql);
		$results = $query -> result_array();
		foreach ($results as $result) {
			$counties[$result['id']] = $result['county'];
		}
		return @$counties;
	}

	public function getEidADTFacility() {
		$sql = "SELECT f.facilitycode,f.name
			    FROM eid_master em
			    LEFT JOIN facilities f ON f.facilitycode=em.facilitycode
			    WHERE f.name !=''
			    GROUP BY em.facilitycode";
		$query = $this -> db -> query($sql);
		$results = $query -> result_array();
		$facilities = array();
		foreach ($results as $result) {
			$facilities[$result['facilitycode']] = $result['name'];
		}
		$facilities = array_unique($facilities);
		asort($facilities);
		return $facilities;
	}

	public function getEidFacility() {
		$sql = "SELECT f.facilitycode,f.name
			    FROM eid_info ei
			    LEFT JOIN facilities f ON f.facilitycode=ei.facility_code
			    GROUP BY ei.facility_code";
		$query = $this -> db -> query($sql);
		$results = $query -> result_array();
		foreach ($results as $result) {
			$facilities[$result['facilitycode']] = $result['name'];
		}
		return @$facilities;
	}

	public function getCountyList() {
		$sql1 = "SELECT c.county,c.id
				FROM maps m
				LEFT JOIN sync_facility sf ON sf.id=m.facility_id
				LEFT JOIN counties c ON c.id=sf.county_id
				WHERE c.county IS NOT NULL
				GROUP BY c.id
				ORDER BY c.county";

		$sql2 = "SELECT c.county,c.id
				FROM maps m
				LEFT JOIN escm_facility ef ON ef.id=m.facility_id
				LEFT JOIN counties c ON c.id=ef.county_id
				WHERE c.county IS NOT NULL
				GROUP BY c.id
				ORDER BY c.county";

		$query1 = $this -> db -> query($sql1);
		$query2 = $this -> db -> query($sql2);

		$results1 = $query1 -> result_array();
		$results2 = $query2 -> result_array();

		$results = array_merge($results1, $results2);
		$counties = array();
		foreach ($results as $result) {
			$counties[$result['id']] = $result['county'];
		}
		$counties = array_unique($counties);
		asort($counties);
		return $counties;
	}

	public function getFacilityList() {
		$sql1 = "SELECT sf.name,sf.id
				FROM maps m
				LEFT JOIN sync_facility sf ON sf.id=m.facility_id
				WHERE sf.name IS NOT NULL
				GROUP BY sf.id
				ORDER BY sf.name";

		$sql2 = "SELECT ef.name,ef.id
				FROM maps m
				LEFT JOIN escm_facility ef ON ef.id=m.facility_id
				WHERE ef.name IS NOT NULL
				GROUP BY ef.id
				ORDER BY ef.name";

		$query1 = $this -> db -> query($sql1);
		$query2 = $this -> db -> query($sql2);

		$results1 = $query1 -> result_array();
		$results2 = $query2 -> result_array();

		$results = array_merge($results1, $results2);
		$facilities = array();
		foreach ($results as $result) {
			$facilities[$result['id']] = $result['name'];
		}
		$facilities = array_unique($facilities);
		asort($facilities);
		return $facilities;
	}

	public function download($type = "", $period = "", $pipeline = '') {
		$and_check_maps = " AND m.status NOT LIKE '%delete%' AND m.status NOT LIKE '%prepare%' AND m.status NOT LIKE '%receive%' ";
		$and_check_cdrr = " AND c.status NOT LIKE '%receive%' AND c.status NOT LIKE '%delete%' AND c.status NOT LIKE '%prepare%' ";
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
			//$objPHPExcel -> getActiveSheet() -> SetCellValue('AH3', "MOS at Facilities");
			//$objPHPExcel -> getActiveSheet() -> SetCellValue('AI3', "MOS at Central Stores");
			//$objPHPExcel -> getActiveSheet() -> SetCellValue('AJ3', "MOS - Pending With Suppliers");
			//$objPHPExcel -> getActiveSheet() -> SetCellValue('AL3', "MOS at Facilities");
			//$objPHPExcel -> getActiveSheet() -> SetCellValue('AM3', "MOS at Central Stores");
			//$objPHPExcel -> getActiveSheet() -> SetCellValue('AN3', "MOS - Pending With Suppliers");

			$result_drugs = sync_drug::getAll();
			$period = date("Y-m-01", strtotime($period));
			$cur_cons_kp = '-';
			$cur_cons_kemsa = '-';
			$avg_cons_kemsa = '-';
			$avg_cons_kp = '-';
			$x = 5;
			foreach ($result_drugs as $value) {
				$drug_id = $value['id'];
				$drug_name = $value['name'];
				$drug_abbr = $value['abbreviation'];
				$drug_str = $value['strength'];
				$drug_pack = $value['packsize'];
				$drug_category = $value['category_id'];
				//Adults or paeds
				$three_month_back = date('Y-m-d', strtotime(date($period, mktime()) . " - 2 months"));

				/*
				 * Loop through Kenya Pharma orders to get MOS
				 */

				//Count months back so as to get the average consumption
				$sql_months = "SELECT COUNT(DISTINCT(c.period_begin)) as total_months FROM cdrr c
								LEFT JOIN cdrr_item ci ON c.id=ci.cdrr_id
								INNER JOIN escm_orders eo ON eo.cdrr_id=c.id
								WHERE c.period_begin BETWEEN '$three_month_back' AND '$period'
								AND (c.code='D-CDRR' OR c.code ='F-CDRR_Packs')
								AND ci.drug_id = '$drug_id'
								";
				$query = $this -> db -> query($sql_months);
				$result = $query -> result_array();
				$no_months_kp = $result[0]['total_months'];
				//If drug has not been reported
				if ($no_months_kp == 0) {
					$avg_cons_kp = 0;
					$aggr_on_hand_kp = 0;
					$pending_kp = 0;
					$cms_kp = 0;
					$cur_cons_kp = 0;
				} else {
					//Get current consumption for the drug
					$sql_cur_cons = "SELECT SUM(aggr_consumed) as cur_cons FROM cdrr_item ci
								LEFT JOIN cdrr c ON c.id=ci.cdrr_id
								INNER JOIN escm_orders eo ON eo.cdrr_id=c.id
								WHERE c.period_begin='$period'
								AND (c.code='D-CDRR' OR c.code ='F-CDRR_Packs')
								AND ci.drug_id = '$drug_id'";

					$query = $this -> db -> query($sql_cur_cons);
					$result = $query -> result_array();
					$count = count($result);
					if ($count > 0) {
						$cur_cons_kp = $result[0]['cur_cons'];
					}

					$sql_kp = "
									SELECT $drug_id as c_drug_id, ROUND((SUM(aggr_consumed)/$no_months_kp)) as avg_cons,s.aggr_on_hand,soh.pending,soh.cms FROM cdrr_item ci
									LEFT JOIN cdrr c ON c.id=ci.cdrr_id
									INNER JOIN escm_orders eo ON eo.cdrr_id=c.id
									LEFT JOIN  
									(
										SELECT $drug_id as drug_id,SUM(fs.pending) as pending,SUM(fs.cms) as cms FROM facility_soh fs
										WHERE fs.period_begin='$period'
										AND fs.drug_id='$drug_id'
										AND fs.pipeline ='kp'
									) as soh ON soh.drug_id=ci.drug_id
									LEFT JOIN  
									(
										SELECT $drug_id as drug_id, SUM(ci.aggr_on_hand) as aggr_on_hand FROM cdrr_item ci
										LEFT JOIN cdrr c ON c.id=ci.cdrr_id
										INNER JOIN escm_orders eo ON eo.cdrr_id=c.id
										WHERE c.period_begin='$period'
										AND (c.code='D-CDRR' OR c.code ='F-CDRR_Packs')
										AND ci.drug_id = '$drug_id'
										GROUP BY ci.drug_id
									) as s ON s.drug_id=ci.drug_id	
									
									WHERE c.period_begin BETWEEN '$three_month_back' AND '$period'
									AND (c.code='D-CDRR' OR c.code ='F-CDRR_Packs')
									AND ci.drug_id = '$drug_id'
									";
					$query = $this -> db -> query($sql_kp);
					$result = $query -> result_array();
					$count = count($result);
					if ($count > 0) {
						$avg_cons_kp = (int)$result[0]['avg_cons'];
						$aggr_on_hand_kp = (int)$result[0]['aggr_on_hand'];
						$pending_kp = (int)$result[0]['pending'];
						$cms_kp = (int)$result[0]['cms'];
					}

				}

				/*
				 * Kemsa details
				 */
				$sql_months = "SELECT COUNT(DISTINCT(c.period_begin)) as total_months FROM cdrr c
								LEFT JOIN cdrr_item ci ON c.id=ci.cdrr_id
								LEFT JOIN escm_orders eo ON eo.cdrr_id=c.id
								WHERE c.period_begin BETWEEN '$three_month_back' AND '$period'
								AND (c.code='D-CDRR' OR c.code ='F-CDRR_Packs')
								AND ci.drug_id = '$drug_id'
								AND eo.cdrr_id IS NULL
								";
				$query = $this -> db -> query($sql_months);
				$result = $query -> result_array();
				$no_months_kemsa = $result[0]['total_months'];
				if ($no_months_kemsa == 0) {
					$avg_cons_kemsa = 0;
					$aggr_on_hand_kemsa = 0;
					$pending_kemsa = 0;
					$cms_kemsa = 0;
					$cur_cons_kemsa = 0;
				} else {
					//Get current consumption for the drug
					$sql_cur_cons = "SELECT SUM(aggr_consumed) as cur_cons FROM cdrr_item ci
								LEFT JOIN cdrr c ON c.id=ci.cdrr_id
								LEFT JOIN escm_orders eo ON eo.cdrr_id=c.id
								WHERE c.period_begin='$period'
								AND (c.code='D-CDRR' OR c.code ='F-CDRR_Packs')
								AND ci.drug_id = '$drug_id'
								AND eo.cdrr_id IS NULL";
					$query = $this -> db -> query($sql_cur_cons);
					$result = $query -> result_array();
					$count = count($result);
					if ($count > 0) {
						$cur_cons_kemsa = $result[0]['cur_cons'];
					}
					$sql_kp_kemsa = "
									SELECT $drug_id as c_drug_id, ROUND((SUM(aggr_consumed)/$no_months_kp)) as avg_cons,s.aggr_on_hand,soh.pending,soh.cms FROM cdrr_item ci
									LEFT JOIN cdrr c ON c.id=ci.cdrr_id
									LEFT JOIN  
									(
										SELECT $drug_id as drug_id,SUM(fs.pending) as pending,SUM(fs.cms) as cms FROM facility_soh fs
										WHERE fs.period_begin='$period'
										AND fs.drug_id='$drug_id'
										AND fs.pipeline ='kemsa'
									) as soh ON soh.drug_id=ci.drug_id
									LEFT JOIN  
									(
										SELECT $drug_id as drug_id, SUM(ci.aggr_on_hand) as aggr_on_hand FROM cdrr_item ci
										LEFT JOIN cdrr c ON c.id=ci.cdrr_id
										WHERE c.period_begin='$period'
										AND (c.code='D-CDRR' OR c.code ='F-CDRR_Packs')
										AND ci.drug_id = '$drug_id'
										AND c.id NOT IN(SELECT cdrr_id FROM escm_orders)
									) as s ON s.drug_id=ci.drug_id	
									
									WHERE c.period_begin BETWEEN '$three_month_back' AND '$period'
									AND (c.code='D-CDRR' OR c.code ='F-CDRR_Packs')
									AND ci.drug_id = '$drug_id'
									AND c.id NOT IN(SELECT cdrr_id FROM escm_orders)
									";

					$query = $this -> db -> query($sql_kp_kemsa);
					$result = $query -> result_array();
					$count = count($result);

					if ($count > 0) {
						$avg_cons_kemsa = (int)$result[0]['avg_cons'];
						$aggr_on_hand_kemsa = (int)$result[0]['aggr_on_hand'];
						$pending_kemsa = (int)$result[0]['pending'];
						$cms_kemsa = (int)$result[0]['cms'];
					}

				}

				//Populating drugs in the table
				$objPHPExcel -> getActiveSheet() -> SetCellValue('A' . $x, $drug_name . '(' . $drug_abbr . ')');

				$avg_cons = (int)$avg_cons_kemsa + (int)$avg_cons_kp;
				$objPHPExcel -> getActiveSheet() -> SetCellValue('C' . $x, $cur_cons_kemsa);
				$objPHPExcel -> getActiveSheet() -> SetCellValue('D' . $x, $cur_cons_kp);
				$objPHPExcel -> getActiveSheet() -> SetCellValue('E' . $x, $avg_cons);
				//KEMSA
				$objPHPExcel -> getActiveSheet() -> SetCellValue('H' . $x, $aggr_on_hand_kemsa);
				$objPHPExcel -> getActiveSheet() -> SetCellValue('I' . $x, $cms_kemsa);
				$objPHPExcel -> getActiveSheet() -> SetCellValue('J' . $x, $pending_kemsa);
				//KP
				$objPHPExcel -> getActiveSheet() -> SetCellValue('L' . $x, $aggr_on_hand_kp);
				$objPHPExcel -> getActiveSheet() -> SetCellValue('M' . $x, $cms_kp);
				$objPHPExcel -> getActiveSheet() -> SetCellValue('N' . $x, $pending_kp);
				//National Level
				$objPHPExcel -> getActiveSheet() -> SetCellValue('P' . $x, (int)$aggr_on_hand_kemsa + (int)$aggr_on_hand_kp);
				//National Facililties SOH
				$objPHPExcel -> getActiveSheet() -> SetCellValue('Q' . $x, (int)$cms_kemsa + (int)$cms_kp);
				//National Central Medical Store
				$objPHPExcel -> getActiveSheet() -> SetCellValue('R' . $x, (int)$pending_kemsa + (int)$pending_kp);
				//National Pending

				//MOS
				if ($avg_cons != 0) {
					$national_mos = number_format((((int)$aggr_on_hand_kemsa + (int)$aggr_on_hand_kp) + ((int)$cms_kemsa + (int)$cms_kp) + ((int)$pending_kemsa + (int)$pending_kp)) / $avg_cons, 1);
					$facility_mos = number_format(((int)$aggr_on_hand_kemsa + (int)$aggr_on_hand_kp) / $avg_cons, 1);
					$cms_mos = number_format(((int)$cms_kemsa + (int)$cms_kp) / $avg_cons, 1);
					$pending_mos = number_format(((int)$pending_kemsa + (int)$pending_kp) / $avg_cons, 1);
				} else {
					$national_mos = ((int)$aggr_on_hand_kemsa + (int)$aggr_on_hand_kp) + ((int)$cms_kemsa + (int)$cms_kp) + ((int)$pending_kemsa + (int)$pending_kp);
					$facility_mos = (int)$aggr_on_hand_kemsa + (int)$aggr_on_hand_kp;
					$cms_mos = (int)$cms_kemsa + (int)$cms_kp;
					$pending_mos = (int)$pending_kemsa + (int)$pending_kp;
				}
				$objPHPExcel -> getActiveSheet() -> SetCellValue('T' . $x, $national_mos);
				//National MOS =(Fac SOH+CMS+Pending)/agg avg consumption
				$objPHPExcel -> getActiveSheet() -> SetCellValue('V' . $x, $facility_mos);
				//MoS at facilities
				$objPHPExcel -> getActiveSheet() -> SetCellValue('W' . $x, $cms_mos);
				//Mos at central stores
				$objPHPExcel -> getActiveSheet() -> SetCellValue('X' . $x, $pending_mos);

				$x++;
			}

			$this -> generateExcel($filename, $dir, $objPHPExcel);
		} else if ($type == "CONS") {// Stock consumption
			$period = date('Y-m-01', strtotime($period));
			$drug_table = '';
			$facility_table = '';
			$and = '';
			//Get consumption for that period
			if ($pipeline == 'kemsa') {
				$drug_table = 'sync_drug';
				$facility_table = 'sync_facility';
				$results_f = Sync_Facility::getAllHydrated();
				$and .= ' and c.id NOT IN (SELECT cdrr_id FROM escm_orders)';
			} else if ($pipeline == 'kenya_pharma') {
				$drug_table = ' escm_drug';
				$facility_table = 'escm_facility';
				$results_f = Escm_Facility::getAllHydrated();
				$and .= ' and c.id IN (SELECT cdrr_id FROM escm_orders)';
			}
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

			$y = 1;
			$p = 9;
			foreach ($results_f as $value) {//Loop through each facility
				$facility_id = $value['id'];
				$code = $value['code'];
				$name = $value['name'];

				$objPHPExcel -> getActiveSheet() -> SetCellValue('A' . $p, $y);
				$objPHPExcel -> getActiveSheet() -> SetCellValue('B' . $p, $code);
				$objPHPExcel -> getActiveSheet() -> SetCellValue('C' . $p, $name);

				// Get drugs details for each facility
				$sql = "
						SELECT dc.id, dc.name,dc.abbreviation,dc.strength, dc.formulation,IFNULL(tabl.total,0) as tot_consumption   FROM  $drug_table dc
							LEFT JOIN 
							(
							SELECT c.facility_id,IF(c.code='F-CDRR_packs',ci.dispensed_packs,ci.aggr_consumed) as total, ci.drug_id, dc.id as dr_id FROM cdrr_item ci
							LEFT JOIN cdrr c ON c.id = ci.cdrr_id
							LEFT JOIN $drug_table dc ON dc.id = ci.drug_id
							LEFT JOIN $facility_table f ON f.id = c.facility_id
							WHERE c.period_begin = '$period'
							AND c.facility_id = $facility_id
							$and
							$and_check_cdrr
							) tabl ON tabl.dr_id=dc.id
							GROUP BY dc.id
							ORDER BY dc.name
						";
				//echo $sql;die();
				$query = $this -> db -> query($sql);
				$results = $query -> result_array();

				$x = "D";
				foreach ($results as $value) {
					$drug = $value['name'] . '( ' . $value['abbreviation'] . ' ) ' . $value['strength'] . ' ' . $value['formulation'];
					$total_cons = $value['tot_consumption'];
					if ($y == '1') {//Append Drug names for first row only
						$objPHPExcel -> getActiveSheet() -> SetCellValue($x . '7', $drug);
					}
					$objPHPExcel -> getActiveSheet() -> SetCellValue($x . $p, $total_cons);
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

			$period_begin = date('Y-m-01', strtotime($period));
			$period_end = date('Y-m-t', strtotime($period));

			$facility_table = '';
			$regimen_table = '';
			$cols = '';
			$and = ' ';
			//Choose regimen table depending on the pipeline
			if ($pipeline == 'kemsa') {
				$facility_table = 'sync_facility';
				$regimen_table = 'sync_regimen';
				$cols = 'r.id,r.code,r.description';
				//Check for maps that came from Kemsa
				$and .= ' and m.id NOT IN (SELECT maps_id FROM escm_maps)';
				$results_f = Sync_Facility::getAllHydrated();
			} else if ($pipeline == 'kenya_pharma') {
				$facility_table = 'escm_facility';
				$regimen_table = 'escm_regimen';
				$cols = 'r.id,r.code,r.description';
				//Check for maps that came from kenya Pharma
				$and .= ' and m.id IN (SELECT maps_id FROM escm_maps)';
				$results_f = Escm_Facility::getAllHydrated();
			}

			//Generate excel start here
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
			$objPHPExcel -> getActiveSheet() -> SetCellValue('B8', "MFL Code ");
			$objPHPExcel -> getActiveSheet() -> SetCellValue('C8', "Site Name ");
			$objPHPExcel -> getActiveSheet() -> SetCellValue('D5', "Name ");
			$objPHPExcel -> getActiveSheet() -> SetCellValue('D6', "New Code ");
			$objPHPExcel -> getActiveSheet() -> SetCellValue('D7', "Previous Code ");
			$objPHPExcel -> getActiveSheet() -> SetCellValue('D8', "Site Total ");
			//Styling
			$objPHPExcel -> getActiveSheet() -> getStyle('A1:D8') -> getFont() -> setBold(true);

			//start looping through each facility
			$y = 1;
			$p = 9;
			foreach ($results_f as $value) {
				$id = $value['id'];
				$code = $value['code'];
				$name = $value['name'];
				$objPHPExcel -> getActiveSheet() -> SetCellValue('A' . $p, $y);
				$objPHPExcel -> getActiveSheet() -> SetCellValue('B' . $p, $code);
				$objPHPExcel -> getActiveSheet() -> SetCellValue('C' . $p, $name);

				//Get regimen list
				$sql_regimen = "
								SELECT r.id,r.code, r.old_code, r.name,IFNULL(tabl.total,0) as total  FROM $regimen_table r
								LEFT JOIN 
								(
								SELECT m.facility_id, f.name as facility_name, mi.total, mi.regimen_id, r.id as reg_id
								FROM maps_item mi
								LEFT JOIN maps m ON m.id = mi.maps_id
								LEFT JOIN $regimen_table r ON r.id = mi.regimen_id
								LEFT JOIN $facility_table f ON f.id = m.facility_id
								WHERE m.period_begin =  '$period_begin'
								$and_check_maps
								AND m.facility_id=$id
								$and
								) tabl ON tabl.reg_id=r.id
								ORDER BY r.id
								";
				$query = $this -> db -> query($sql_regimen);
				$results = $query -> result_array();

				//Get totals for each regimen, for the selected facility and append it to the sheet
				$x = "E";
				$total = 0;
				//Total for all the regimens for a facility
				foreach ($results as $value) {
					$r_id = $value['id'];
					$code = $value['code'];
					$old_code = $value['old_code'];
					$name = $value['name'];
					$t = $value['total'];
					$total = $total + $t;

					if ($y == 1) {//Append regimen names, old code, new code only once when start looping
						$objPHPExcel -> getActiveSheet() -> mergeCells($x . '1:' . $x . '5');
						$objPHPExcel -> getActiveSheet() -> SetCellValue($x . '1', $name);
						$objPHPExcel -> getActiveSheet() -> SetCellValue($x . '6', $code);
						$objPHPExcel -> getActiveSheet() -> SetCellValue($x . '7', $old_code);
					}
					$objPHPExcel -> getActiveSheet() -> SetCellValue($x . $p, $t);
					//Total for a  regimen

					$x++;

				}
				$objPHPExcel -> getActiveSheet() -> SetCellValue('D' . $p, $total);
				//Total for all regimen for a facility

				$p++;
				$y++;
			}

			$objPHPExcel -> getActiveSheet() -> getStyle('D1:D' . $p) -> getFont() -> setBold(true);
			$this -> generateExcel($filename, $dir, $objPHPExcel);

		}

		//Patients By Regimen
		elseif ($type == 'BYREG_PATIENT') {
			$period = date('Y-m-01', strtotime($period));
			$facility_table = '';
			$regimen_table = '';
			$and = '';
			if ($pipeline == 'kemsa') {
				$regimen_table = 'sync_regimen';
				$facility_table = 'sync_facility';
				//Check for maps that came from kemsa
				$and .= ' and m.id NOT IN (SELECT maps_id FROM escm_maps)';
			} else if ($pipeline == 'kenya_pharma') {
				$regimen_table = 'escm_regimen';
				$facility_table = 'escm_facility';
				//Check for maps that came from kenya Pharma
				$and .= ' and m.id IN (SELECT maps_id FROM escm_maps)';
			}

			$sql_regimen = "
						SELECT c.id as cat_id,c.name as cat_name,tabl.reg_name as regimen_name,tabl.code as regimen_code,tabl.old_code,tabl.total as total FROM sync_category c
						LEFT JOIN 
						(
						SELECT  SUM(mi.total) as total, mi.regimen_id,r.code,r.old_code,r.name as reg_name, r.id as reg_id,c.id as cat_id 
						FROM maps_item mi 
						LEFT JOIN maps m ON m.id = mi.maps_id 
						LEFT JOIN $regimen_table r ON r.id = mi.regimen_id 
						LEFT JOIN escm_facility f ON f.id = m.facility_id
						LEFT JOIN sync_category c ON c.id=r.category_id
						WHERE m.period_begin = '$period' 
						$and
						$and_check_maps
						GROUP BY mi.regimen_id ORDER BY r.code,reg_name ) tabl ON tabl.cat_id=c.id
						WHERE c.name NOT LIKE '%delete%'
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
			$objPHPExcel -> getActiveSheet() -> getStyle('A1:D7') -> getFont() -> setBold(true);

			$x = 8;
			$a = 0;
			$n = 0;
			$cat_total = 0;
			$count = count($results);
			foreach ($results as $value) {

				$cat_id = $results[$a]['cat_id'];
				$cat_name = $results[$a]['cat_name'];
				$code = $value['regimen_code'];
				$regimen_desc = $value['regimen_name'];
				$total = $value['total'];

				if ($a == 0) {//Append Regimen Category when looping for the first time
					$objPHPExcel -> getActiveSheet() -> SetCellValue('B' . $x, $cat_name);
					$objPHPExcel -> getActiveSheet() -> getStyle('B' . $x) -> getFont() -> setBold(true);
					$x++;
					$n++;
					$objPHPExcel -> getActiveSheet() -> SetCellValue('A' . $x, $n);
					$objPHPExcel -> getActiveSheet() -> SetCellValue('B' . $x, $code);
					$objPHPExcel -> getActiveSheet() -> SetCellValue('C' . $x, $regimen_desc);
					$objPHPExcel -> getActiveSheet() -> SetCellValue('D' . $x, $total);
					$cat_total += $total;
				} elseif ($a > 0) {
					$prev = $a - 1;
					if ($results[$prev]['cat_id'] != $results[$a]['cat_id']) {//Check if this regimen is different from the previous one
						$n = 0;
						$objPHPExcel -> getActiveSheet() -> getStyle('C' . $x) -> getFont() -> setBold(true);
						$objPHPExcel -> getActiveSheet() -> getStyle('D' . $x) -> getFont() -> setBold(true);
						$objPHPExcel -> getActiveSheet() -> SetCellValue('C' . $x, "Category Total ");
						$objPHPExcel -> getActiveSheet() -> SetCellValue('D' . $x, $cat_total);
						$cat_total = 0;

						$x++;
						$objPHPExcel -> getActiveSheet() -> SetCellValue('B' . $x, $cat_name);
						$objPHPExcel -> getActiveSheet() -> getStyle('B' . $x) -> getFont() -> setBold(true);
						$x++;
						$n++;
						$objPHPExcel -> getActiveSheet() -> SetCellValue('A' . $x, $n);
						$objPHPExcel -> getActiveSheet() -> SetCellValue('B' . $x, $code);
						$objPHPExcel -> getActiveSheet() -> SetCellValue('C' . $x, $regimen_desc);
						$objPHPExcel -> getActiveSheet() -> SetCellValue('D' . $x, $total);
						$cat_total += $total;
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
				if ($a == $count) {//If last row, display category total for last row
					$objPHPExcel -> getActiveSheet() -> getStyle('C' . $x) -> getFont() -> setBold(true);
					$objPHPExcel -> getActiveSheet() -> getStyle('D' . $x) -> getFont() -> setBold(true);
					$objPHPExcel -> getActiveSheet() -> SetCellValue('C' . $x, "Category Total ");
					$objPHPExcel -> getActiveSheet() -> SetCellValue('D' . $x, $cat_total);
				}
			}
			$this -> generateExcel($filename, $dir, $objPHPExcel);
		}

		//Patients Scale Up
		elseif ($type == "PATIENT_SCALE") {
			$period = date('Y-m-01', strtotime($period));
			$facility_table = '';
			$regimen_table = '';
			$where = ' ';
			if ($pipeline == 'kemsa') {
				$regimen_table = 'sync_regimen';
				$facility_table = 'sync_facility';
				$where .= ' where m.id NOT IN (SELECT maps_id FROM escm_maps)';
			} else if ($pipeline == 'kenya_pharma') {
				$regimen_table = 'escm_regimen';
				$facility_table = 'escm_facility';
				$where .= ' where m.id IN (SELECT maps_id FROM escm_maps)';
			}
			$sql = "
			
			SELECT c.id,c.name,tabl.period_begin,SUM(tabl.total) as total FROM sync_category c
			LEFT JOIN 
			(
			SELECT mi.total,m.period_begin, mi.regimen_id,r.code,r.old_code,r.name as reg_name, r.id as reg_id,c.id as cat_id 
			FROM maps_item mi 
			LEFT JOIN maps m ON m.id = mi.maps_id 
			LEFT JOIN $regimen_table r ON r.id = mi.regimen_id 
			LEFT JOIN $facility_table f ON f.id = m.facility_id
			LEFT JOIN sync_category c ON c.id=r.category_id
			$where
			$and_check_maps
			ORDER BY r.code,reg_name ) tabl ON tabl.cat_id=c.id
			WHERE c.name NOT LIKE '%delete%'
			GROUP BY  c.name,tabl.period_begin ORDER BY tabl.period_begin,c.name
			
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
			$objPHPExcel -> getActiveSheet() -> getStyle('B6') -> getAlignment() -> setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			$objPHPExcel -> getActiveSheet() -> getStyle('E6') -> getAlignment() -> setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			$objPHPExcel -> getActiveSheet() -> getStyle('A7:I7') -> getFont() -> setBold(true);
			$objPHPExcel -> getActiveSheet() -> getStyle('D') -> getFont() -> setBold(true);
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
				$cat_name = strtolower($value['name']);
				//Regimen category

				$patient_category = '';
				if ($cat_name == 'adult art first line' || $cat_name == 'adult art second line' || $cat_name == 'other adult regimen') {
					$patient_category = 'adult art patients';
				} else if ($cat_name == 'paediatric first line' || $cat_name == 'paediatric second line' || $cat_name == 'other paediatric art regimen') {
					$patient_category = 'paediatric art patients';
				} else if ($cat_name == 'pep adult') {
					$patient_category = 'pep adults';
				} else if ($cat_name == 'pep child') {
					$patient_category = 'pep children';
				} else if ($cat_name == 'pmtct regimens for infants') {
					$patient_category = 'pmtct infants';
				} else if ($cat_name == 'pmtct regimens for pregnant women') {
					$patient_category = 'pmtct mothers';
				}

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
			$objPHPExcel -> getActiveSheet() -> getStyle('I8:I' . $y) -> getFont() -> setBold(true);
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

	public function getPatients($type = "ART_PATIENT", $period = "", $county = "", $facility = "") {
		$and_check_maps = " AND m.status NOT LIKE '%delete%' AND m.status NOT LIKE '%prepare%' ";
		if ($period == '') {
			$current_period = date('Y-m-01', strtotime("-1 month"));
			$join_maps = "INNER JOIN maps m ON m.id=mi.maps_id";
			$and = "AND m.period_begin='$current_period'";
		} else {
			$current_period = date('Y-m-01', strtotime($period));
			$join_maps = "INNER JOIN maps m ON m.id=mi.maps_id";
			$and = "AND m.period_begin='$current_period'";
		}

		$data = array();
		if ($type == "BYPIPELINE_ART") {//Number of Patients on ART By Pipeline
			$data['container'] = 'report_by_pipeline';
			$data['title'] = 'Total Patients By Pipeline';
			$data['chartTitle'] = 'No of Patients on ART By Pipeline';

			//kenya pharma
			//Adults patients from Kenya Pharma
			$sql_adult_kp = "SELECT SUM( mi.total ) AS total_adult_kp
							FROM maps_item mi
							LEFT JOIN maps m ON m.id = mi.maps_id
							LEFT JOIN escm_maps em ON em.maps_id = m.id
							LEFT JOIN escm_regimen er ON er.id = mi.regimen_id
							LEFT JOIN sync_category sc ON sc.id = er.category_id
							WHERE m.period_begin =  '" . $current_period . "'
							$and_check_maps
							AND em.maps_id IS NOT NULL 
							AND sc.name LIKE  '%adult%'
							AND sc.name NOT LIKE  '%pep%'
							AND sc.name NOT LIKE '%delete%'";
			//Non standard regimens
			$sql_adult_kp_nr = "SELECT SUM( mi.total ) AS total_adult_kp
							FROM nonstandard_maps_item mi
							LEFT JOIN maps m ON m.id = mi.maps_id
							LEFT JOIN escm_maps em ON em.maps_id = m.id
							LEFT JOIN nonstandard_regimen er ON er.id = mi.regimen_id
							LEFT JOIN sync_category sc ON sc.id = er.category
							WHERE m.period_begin =  '" . $current_period . "'
							$and_check_maps
							AND em.maps_id IS NOT NULL 
							AND sc.name LIKE  '%adult%'
							AND sc.name NOT LIKE  '%pep%'
							AND sc.name NOT LIKE '%delete%'";

			$sql_paed_kp = "SELECT SUM( mi.total ) AS total_paed_kp
							FROM maps_item mi
							LEFT JOIN maps m ON m.id = mi.maps_id
							LEFT JOIN escm_maps em ON em.maps_id = m.id
							LEFT JOIN escm_regimen er ON er.id = mi.regimen_id
							LEFT JOIN sync_category sc ON sc.id = er.category_id
							WHERE m.period_begin =  '" . $current_period . "'
							$and_check_maps
							AND em.maps_id IS NOT NULL 
							AND (sc.name LIKE  '%paediatric%' OR sc.name LIKE  '%pediatric%')
							AND sc.name NOT LIKE '%delete%'";

			//Non standard regimens pead
			$sql_paed_kp_nr = "SELECT SUM( mi.total ) AS total_paed_kp
							FROM nonstandard_maps_item mi
							LEFT JOIN maps m ON m.id = mi.maps_id
							LEFT JOIN escm_maps em ON em.maps_id = m.id
							LEFT JOIN nonstandard_regimen er ON er.id = mi.regimen_id
							LEFT JOIN sync_category sc ON sc.id = er.category
							WHERE m.period_begin =  '" . $current_period . "'
							$and_check_maps
							AND em.maps_id IS NOT NULL 
							AND (sc.name LIKE  '%paediatric%' OR sc.name LIKE  '%pediatric%')
							AND sc.name NOT LIKE '%delete%'";

			$query1 = $this -> db -> query($sql_adult_kp);
			$query2 = $this -> db -> query($sql_paed_kp);
			$query3 = $this -> db -> query($sql_adult_kp_nr);
			$query4 = $this -> db -> query($sql_paed_kp_nr);

			$result1 = $query1 -> result_array();
			$result2 = $query2 -> result_array();
			$result3 = $query3 -> result_array();
			$result4 = $query4 -> result_array();

			$tot_adult_kp = 0;
			$tot_paed_kp = 0;
			$total_kp = 0;
			if (count($result1) > 0) {
				$tot_adult_kp = (int)$result1[0]['total_adult_kp'];
				// Check if non standard regimens exist
				if (count($result3) > 0) {
					$tot_adult_kp += (int)$result3[0]['total_adult_kp'];
				}
			}
			if (count($result2) > 0) {
				$tot_paed_kp = (int)$result2[0]['total_paed_kp'];
				// Check if non standard regimens exist
				if (count($result4) > 0) {
					$tot_paed_kp += (int)$result4[0]['total_paed_kp'];
				}
			}
			$total_kp = $tot_adult_kp + $tot_paed_kp;

			//kemsa
			$sql_adult_kem_nr = "SELECT SUM( mi.total ) AS total_adult_kemsa
							FROM nonstandard_maps_item mi
							LEFT JOIN maps m ON m.id = mi.maps_id
							LEFT JOIN escm_maps em ON em.maps_id = m.id
							LEFT JOIN nonstandard_regimen sr ON sr.id = mi.regimen_id
							LEFT JOIN sync_category sc ON sc.id = sr.category
							WHERE m.period_begin =  '" . $current_period . "'
							$and_check_maps
							AND em.maps_id IS NULL 
							AND sc.name LIKE  '%adult%'
							AND sc.name NOT LIKE  '%pep%'
							AND sc.name NOT LIKE '%delete%'";

			$sql_adult_kem = "SELECT SUM( mi.total ) AS total_adult_kemsa
							FROM maps_item mi
							LEFT JOIN maps m ON m.id = mi.maps_id
							LEFT JOIN escm_maps em ON em.maps_id = m.id
							LEFT JOIN sync_regimen sr ON sr.id = mi.regimen_id
							LEFT JOIN sync_category sc ON sc.id = sr.category_id
							WHERE m.period_begin =  '" . $current_period . "'
							$and_check_maps
							AND em.maps_id IS NULL 
							AND sc.name LIKE  '%adult%'
							AND sc.name NOT LIKE  '%pep%'
							AND sc.name NOT LIKE '%delete%'";

			$sql_paed_kem_nr = "SELECT SUM( mi.total ) AS total_paed_kemsa
							FROM nonstandard_maps_item mi
							LEFT JOIN maps m ON m.id = mi.maps_id
							LEFT JOIN escm_maps em ON em.maps_id = m.id
							LEFT JOIN nonstandard_regimen sr ON sr.id = mi.regimen_id
							LEFT JOIN sync_category sc ON sc.id = sr.category
							WHERE m.period_begin =  '" . $current_period . "'
							$and_check_maps
							AND em.maps_id IS NULL 
							AND (sc.name LIKE  '%paediatric%' OR sc.name LIKE  '%pediatric%')
							AND sc.name NOT LIKE '%delete%'
							";

			$sql_paed_kem = "SELECT SUM( mi.total ) AS total_paed_kemsa
							FROM maps_item mi
							LEFT JOIN maps m ON m.id = mi.maps_id
							LEFT JOIN escm_maps em ON em.maps_id = m.id
							LEFT JOIN sync_regimen sr ON sr.id = mi.regimen_id
							LEFT JOIN sync_category sc ON sc.id = sr.category_id
							WHERE m.period_begin =  '" . $current_period . "'
							$and_check_maps
							AND em.maps_id IS NULL 
							AND (sc.name LIKE  '%paediatric%' OR sc.name LIKE  '%pediatric%')
							AND sc.name NOT LIKE '%delete%'
							";

			$query1 = $this -> db -> query($sql_adult_kem);
			$query2 = $this -> db -> query($sql_paed_kem);
			$query3 = $this -> db -> query($sql_adult_kem_nr);
			$query4 = $this -> db -> query($sql_paed_kem_nr);

			$result1 = $query1 -> result_array();
			$result2 = $query2 -> result_array();
			$result3 = $query3 -> result_array();
			$result4 = $query4 -> result_array();

			$tot_adult_kemsa = 0;
			$tot_paed_kemsa = 0;
			$total_kemsa = 0;

			if (count($result1) > 0) {
				$tot_adult_kemsa = (int)$result1[0]['total_adult_kemsa'];
				// Check if non standard regimens exist
				if (count($result3) > 0) {
					$tot_adult_kemsa += (int)$result3[0]['total_adult_kemsa'];
				}

			}
			if (count($result2) > 0) {
				$tot_paed_kemsa = (int)$result2[0]['total_paed_kemsa'];
				// Check if non standard regimens exist
				if (count($result4) > 0) {
					$tot_paed_kemsa += (int)$result4[0]['total_paed_kemsa'];
				}
			}
			$total_kemsa = $tot_adult_kemsa + $tot_paed_kemsa;

			$total_adults = $tot_adult_kemsa + $tot_adult_kp;
			$total_paeds = $tot_paed_kemsa + $tot_paed_kp;
			$grand_total = $total_adults + $total_paeds;
			//Generate table for Number of Patients on ART
			$tmpl = array('table_open' => '<table id="" class="table table-bordered table-striped dash_tables">');
			$this -> table -> set_template($tmpl);
			$this -> table -> set_heading('', 'Pipeline', '<center>Kemsa</center>', '<center>Kenya Pharma</center>', '<center>Line Total</center>');
			$this -> table -> add_row('', '<h5>Adults</h5>', '<center>' . number_format($tot_adult_kemsa) . '</center>', '<center>' . number_format($tot_adult_kp) . '</center>', '<center>' . number_format($total_adults) . '</center>');
			$this -> table -> add_row('', '<h5>Paeds</h5>', '<center>' . number_format($tot_paed_kemsa) . '</center>', '<center>' . number_format($tot_paed_kp) . '</center>', '<center>' . number_format($total_paeds) . '</center>');
			$this -> table -> add_row('', '<h5>TOTAL</h5>', '<b><center>' . number_format($total_kemsa) . '</center></b>', '<b><center>' . number_format($total_kp) . '</center></b>', '<b><center>' . number_format($grand_total) . '</center></b>');
			$table_display = $this -> table -> generate();
			echo $table_display;
		} elseif ($type == "ADULT_ART") {//Current Adult Patients on ART
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
								$join_maps
								WHERE(r.code IN ('AF1A',  'AF1B',  'AF2A',  'AF2B',  'AF3A',  'AF3B')
								AND mi.maps_id IN(SELECT maps_id FROM escm_maps))
								$and
								$and_check_maps
								GROUP BY r.code) as test ON mr.id=test.regimen_id
								WHERE mr.code IN ('AF1A',  'AF1B',  'AF2A',  'AF2B',  'AF3A',  'AF3B')
								GROUP BY mr.code";

					$join1_kp = "SELECT mr.name as regimen_desc,test.total
                                FROM escm_regimen mr
                                LEFT JOIN 
								(SELECT r.id AS regimen_id, SUM( mi.total ) AS total
								FROM escm_regimen r
								LEFT JOIN maps_item mi ON mi.regimen_id = r.id
								$join_maps
								WHERE(r.code IN('AS1A','AS1B','AS2A','AS2B','AS3A','AS3B','AS4A','AS4B')
								AND mi.maps_id IN(SELECT maps_id FROM escm_maps))
								$and
								$and_check_maps
								GROUP BY r.code) as test ON mr.id=test.regimen_id
								WHERE mr.code IN('AS1A','AS1B','AS2A','AS2B','AS3A','AS3B','AS4A','AS4B')
								GROUP BY mr.code";

					$join2_kp = "SELECT mr.name as regimen_desc,test.total
                                FROM escm_regimen mr
                                LEFT JOIN 
								(SELECT r.id AS regimen_id, SUM( mi.total ) AS total,r.category_id
								FROM escm_regimen r
								LEFT JOIN maps_item mi ON mi.regimen_id = r.id
								LEFT JOIN sync_category sc ON sc.id=r.category_id
								$join_maps
								WHERE sc.name LIKE '%Other Adult Regimen%'
								AND mi.maps_id IN(SELECT maps_id FROM escm_maps)
								$and
								$and_check_maps
								GROUP BY r.code) as test ON mr.id=test.regimen_id
								LEFT JOIN sync_category sc1 ON sc1.id=test.category_id
								WHERE sc1.name LIKE '%Other Adult Regimen%'
								GROUP BY mr.code";
					$regimen_table = "escm_regimen";
				}
				$regimens = array("D4T+3TC+NVP", "D4T+3TC+EFV", "AZT+3TC+NVP", "AZT+3TC+EFV", "TDF+3TC+NVP", "TDF+3TC+EFV", "2ND LINE", "OTHER REGIMENS");

				$regimen_lines['first_line'] = "WHERE r.code IN('AF1A','AF1B','AF2A','AF2B','AF3A','AF3B') GROUP BY r.code";
				$regimen_lines['second_line'] = "WHERE r.code IN('AS1A','AS1B','AS2A','AS2B','AS3A','AS3B','AS4A','AS4B')";
				$regimen_lines['other_lines'] = "LEFT JOIN sync_category sc ON sc.id=r.category_id WHERE sc.name LIKE '%Other Adult Regimen%'";

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

			echo "<pre>";
			print_r($values);
			echo "</pre>";

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
								$join_maps
								WHERE(r.code IN ('CF1A',  'CF1B', 'CF1C'  ,'CF2A',  'CF2B','CF2C', 'CF2D' ,'CF3A',  'CF3B')
								AND mi.maps_id IN(SELECT maps_id FROM escm_maps))
								$and
								$and_check_maps
								GROUP BY r.code) as test ON mr.id=test.regimen_id
								WHERE mr.code IN ('CF1A',  'CF1B', 'CF1C' , 'CF2A',  'CF2B' ,'CF2C',  'CF2D', 'CF3A',  'CF3B')
								GROUP BY mr.code";

					$join1_kp = "SELECT mr.name as regimen_desc,test.total
                                FROM escm_regimen mr
                                LEFT JOIN 
								(SELECT r.id AS regimen_id, SUM( mi.total ) AS total
								FROM escm_regimen r
								LEFT JOIN maps_item mi ON mi.regimen_id = r.id
								$join_maps
								WHERE(r.code IN('CS1A',  'CS1B', 'CS1C'  ,'CS2A', 'CS2B','CS2C', 'CS2D' ,'CS3A',  'CS3B')
								AND mi.maps_id IN(SELECT maps_id FROM escm_maps))
								$and
								$and_check_maps
								GROUP BY r.code) as test ON mr.id=test.regimen_id
								WHERE mr.code IN('CS1A',  'CS1B', 'CS1C'  ,'CS2A', 'CS2B','CS2C', 'CS2D' ,'CS3A',  'CS3B')
								GROUP BY mr.code";

					$join2_kp = "SELECT mr.name as regimen_desc,test.total
                                FROM escm_regimen mr
                                LEFT JOIN 
								(SELECT r.id AS regimen_id, SUM( mi.total ) AS total,r.category_id
								FROM escm_regimen r
								LEFT JOIN maps_item mi ON mi.regimen_id = r.id
								LEFT JOIN sync_category sc ON sc.id=r.category_id
								$join_maps
								WHERE sc.name LIKE '%Other Paediatric ART Regimen%'
								AND mi.maps_id IN(SELECT maps_id FROM escm_maps)
								$and
								$and_check_maps
								GROUP BY r.code) as test ON mr.id=test.regimen_id
								LEFT JOIN sync_category sc1 ON sc1.id=test.category_id
								WHERE sc1.name LIKE '%Other Paediatric ART Regimen%'
								GROUP BY mr.code";
					$regimen_table = "escm_regimen";
				}
				$regimens = array("D4T+3TC+NVP", "D4T+3TC+EFV", "AZT+3TC+NVP", "AZT+3TC+EFV", "TDF+3TC+NVP", "TDF+3TC+EFV", "2ND LINE", "OTHER REGIMENS");

				$regimen_lines['first_line'] = "WHERE r.code IN('CF1A',  'CF1B', 'CF1C' , 'CF2A',  'CF2B' ,'CF2C',  'CF2D', 'CF3A',  'CF3B') GROUP BY r.code";
				$regimen_lines['second_line'] = "WHERE r.code IN('CS1A',  'CS1B', 'CS1C'  ,'CS2A', 'CS2B','CS2C', 'CS2D' ,'CS3A',  'CS3B')";
				$regimen_lines['other_lines'] = "LEFT JOIN sync_category sc ON sc.id=r.category_id WHERE sc.name LIKE '%Other Paediatric ART Regimen%'";

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
							WHERE c.created BETWEEN '" . $first . "' AND  '" . $last_day . "'
							AND(c.code='D-CDRR' OR c.code='F-CDRR_packs')
							AND (c.id NOT IN(SELECT ec.cdrr_id FROM escm_orders ec))
							) as kemsa_count,
							(SELECT COUNT(DISTINCT(c.facility_id)) as kenya_pharma FROM cdrr c
							INNER JOIN escm_orders ec ON ec.cdrr_id=c.id
							WHERE c.created BETWEEN '" . $first . "' AND  '" . $last_day . "'
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
		$resultArray = array( array('name' => 'Kemsa/LMU - Reporting timeliness', 'data' => $kemsaArray), array('name' => 'Kenya Pharma - Reporting timeliness', 'data' => $kpArray), array('name' => 'National Reporting Rate', 'data' => $nationalArray));
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

	public function reportSummary($type = "", $period = '') {

		if ($type == 'table') {//Reporting Ordering Sites Rate Summary
			//echo $period;die();
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
			$sql = "SELECT (nascop.nascop_total+escm.escm_total) as total
					FROM
			        (SELECT COUNT(*)as nascop_total
			        FROM sync_facility sf
			        WHERE sf.id IN(SELECT facility_id FROM adt_sites)) as nascop,
			        (SELECT COUNT(*)as escm_total
			        FROM escm_facility ef
			        WHERE ef.id IN(SELECT facility_id FROM adt_sites)) as escm";
			$query = $this -> db -> query($sql);
			$results = $query -> result_array();
			$tot_adtsites = $results[0]['total'];

			//Sites reported by 10th

			if ($period != "") {
				$tenth = date('Y-m-10', strtotime($period . "+1 month"));
				$first = date('Y-m-01', strtotime($period . "+1 month"));
				$last_day = date('Y-m-t', strtotime($period . "+1 month"));
			} else {
				$period = date('F-Y');
				$tenth = date('Y-m-10', strtotime($period));
				$first = date('Y-m-01', strtotime($period));
				$last_day = date('Y-m-t', strtotime($period));
				$period.="-1 month";
			}
			$period_begin= date('Y-m-01', strtotime($period));
			$period_end= date('Y-m-t', strtotime($period));

			$sql_tenth = "SELECT COUNT(DISTINCT(c.facility_id)) as total 
			              FROM cdrr c
						  INNER JOIN maps m ON m.facility_id=c.facility_id
						  WHERE c.created 
						  BETWEEN '$first' 
						  AND '$tenth'
						  AND c.period_begin='$period_begin'
						  AND m.period_begin='$period_begin'
						  AND c.period_end='$period_end'
						  AND m.period_end='$period_end'";

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
							INNER JOIN maps m ON m.facility_id=c.facility_id
							WHERE c.created 
							BETWEEN '$first' 
							AND  '$last_day'
							AND c.period_begin='$period_begin'
						    AND m.period_begin='$period_begin'
						    AND c.period_end='$period_end'
						    AND m.period_end='$period_end'";
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
			if ($period == '') {
				$tenth = date('Y-m-10');
				$first = date('Y-m-01');
				$last_day = date('Y-m-t');
			} else {
				$tenth = date('Y-m-10', strtotime($period));
				$first = date('Y-m-01', strtotime($period));
				$last_day = date('Y-m-t', strtotime($period));
			}
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
			$sql = "SELECT (nascop.nascop_total+escm.escm_total) as total
					FROM
			        (SELECT COUNT(*)as nascop_total
			        FROM sync_facility sf
			        WHERE sf.id IN(SELECT facility_id FROM adt_sites)) as nascop,
			        (SELECT COUNT(*)as escm_total
			        FROM escm_facility ef
			        WHERE ef.id IN(SELECT facility_id FROM adt_sites)) as escm";
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
			if (@$mydata['pipeline'] == 'Kenya Pharma') {
				$pipeline = 'kenya_pharma';
			} else if (@$mydata['pipeline'] == 'Kemsa') {

				$pipeline = 'kemsa';
			}
			//Set Up links
			if (!empty($links)) {
				foreach ($links as $i => $link) {
					$link_values .= "<a href='" . site_url($i . '/' . $mydata['period'] . '/' . $pipeline) . "'>$link</a> | ";
				}
				$mydata['Options'] = rtrim($link_values, " | ");
				$link_values = "";
			}
			$this -> table -> add_row($mydata);
		}
		return $this -> table -> generate();
	}

	public function set_tab_session() {
		$tab_id = $this -> input -> post("tab_id");
		$this -> session -> set_userdata("tab_session", $tab_id);
		echo "#" . $tab_id;
	}

	public function eid($type = "gender", $period = "", $facility = 0, $county = 0) {
		$conditions = "";
		$conditions_adt = "";
		$conditions_eid = "";

		if ($period != "") {
			$period_start = date('Y-m-01', strtotime($period));
			$period_end = date('Y-m-t', strtotime($period));
		} else {
			$sql = "SELECT enrollment_date as period_begin FROM eid_info GROUP BY YEAR(enrollment_date),MONTH(enrollment_date) ORDER BY enrollment_date desc LIMIT 1";
			$query = $this -> db -> query($sql);
			$results = $query -> result_array();
			if ($results) {
				$period = $results[0]['period_begin'];
			}
			$period_start = date('Y-m-01', strtotime($period));
			$period_end = date('Y-m-t', strtotime($period));
		}

		if ($facility != 0) {
			if ($type == "retention") {
               $conditions .= "AND ei.facility_code='$facility'";
			}else{
               $conditions .= "AND em.facilitycode='$facility'";
			}	 
		}
		if ($county != 0) {
			if ($type != "retention") {
			   $conditions .= "AND c.id='$county'";
			}
		}

		if ($type == "gender") {
			$column = "gender";
			$container = "chart_area_eid_gender";
		} else if ($type == "line") {
			$column = "service";
			$container = "chart_area_eid_line";
		} else if ($type == "regimen") {
			$column = "regimen";
			$container = "chart_area_eid_regimen";
		} else if ($type == "source") {
			$column = "source";
			$container = "chart_area_eid_source";
		}else if ($type == "retention") {
			$column = "status";
			$container = "chart_area_eid_retention";
		}

		if ($type != "comparison" && $type != "summary") {
				if ($type == "retention") {
				 	if($county==0){
				 		$county=90;
				 	}
					$sql = "SELECT ei.$column as label,COUNT( ei.$column ) AS total 
							FROM eid_info ei 
							LEFT JOIN facilities f ON f.facilitycode=ei.facility_code
							WHERE ei.enrollment_date
							BETWEEN '$period_start'
							AND '$period_end'
							AND DATEDIFF(CURDATE(),ei.enrollment_date)>=$county
							$conditions
							GROUP BY ei.$column";
				}else{
					$sql = "SELECT ei.$column as label,COUNT( ei.$column ) AS total 
							FROM eid_info ei 
							LEFT JOIN facilities f ON f.facilitycode=ei.facility_code
							LEFT JOIN counties c ON c.id=f.county
							WHERE ei.enrollment_date
							BETWEEN '$period_start'
							AND '$period_end'
							$conditions
							GROUP BY ei.$column";
						}
		} else if ($type == "summary") {
			$tbody = "";
			$months = array("January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December");
			$size = count($months);
			$tbody .= "<tr><td rowspan='$size'>National</td>";
			foreach ($months as $month) {
				$tbody .= "<td>$month</td>";
				$tbody .= "<td>-</td>";
				$tbody .= "<td>-</td>";
				$tbody .= "<td>-</td>";
				$tbody .= "<td>-</td>";
				$tbody .= "<td>-</td>";
				$tbody .= "<td>-</td>";
				$tbody .= "<td>-</td></tr>";
			}
			echo $tbody;
			exit();

		}  else {
			if ($facility != 0) {
				$conditions_adt .= "AND ei.facility_code='$facility'";
				$conditions_eid .= "AND em.facilitycode='$facility'";
			}
			if ($county != 0) {
				$conditions_adt .= "AND c.id='$county'";
				$conditions_eid .= "AND c.id='$county'";
			}

			$sql = "
			SELECT eid_total.total_eid,adt_total.total_adt,both_eid_adt.both_eid_adt,only_eid.only_eid,only_adt.only_adt
			FROM
			(SELECT COUNT(*)as total_eid
			        FROM eid_master em
			        LEFT JOIN facilities f ON f.facilitycode=em.facilitycode
			        LEFT JOIN counties c ON c.id=f.county
			        WHERE em.dateinitiatedontreatment
				    BETWEEN '$period_start'
				    AND '$period_end'
					$conditions_eid) as eid_total,
		    (SELECT COUNT(*)as total_adt
			        FROM eid_info ei
			        LEFT JOIN facilities f ON f.facilitycode=ei.facility_code
			        LEFT JOIN counties c ON c.id=f.county
			        WHERE ei.enrollment_date
				    BETWEEN '$period_start'
				    AND '$period_end'
					$conditions_adt) as adt_total,
			(SELECT COUNT(*) as both_eid_adt
			        FROM eid_master em,eid_info ei
			        LEFT JOIN facilities f ON f.facilitycode=ei.facility_code
			        LEFT JOIN counties c ON c.id=f.county
			        WHERE ei.enrollment_date
				    BETWEEN '$period_start'
				    AND '$period_end'
				    AND em.facilitycode=ei.facility_code
			        AND em.enrollmentcccno=ei.patient_no
					$conditions_adt)as both_eid_adt,
			 (SELECT COUNT(*) as only_eid
			        FROM eid_master em
			        LEFT JOIN facilities f ON f.facilitycode=em.facilitycode
			        LEFT JOIN counties c ON c.id=f.county
			        WHERE em.dateinitiatedontreatment
				    BETWEEN '$period_start'
				    AND '$period_end'
			        AND em.enrollmentcccno NOT IN(SELECT patient_no FROM eid_info WHERE enrollment_date BETWEEN '$period_start' AND '$period_end')
					$conditions_eid) as only_eid,
			  (SELECT COUNT(*) as only_adt
			        FROM eid_info ei
			        LEFT JOIN facilities f ON f.facilitycode=ei.facility_code
			        LEFT JOIN counties c ON c.id=f.county
			        WHERE ei.enrollment_date
				    BETWEEN '$period_start'
				    AND '$period_end'
			        AND ei.patient_no NOT IN(SELECT enrollmentcccno FROM eid_master WHERE dateinitiatedontreatment BETWEEN '$period_start' AND '$period_end')
					$conditions_adt) as only_adt";
			$query = $this -> db -> query($sql);
			$results = $query -> result_array();
			if ($results) {
				$total_eid = $results[0]['total_eid'];
				if ($total_eid > 0) {
					$total_adt = $results[0]['total_adt'];
					$total_adt_percent = number_format(($total_adt / $total_eid) * 100, 1);
					$both_eid_adt = $results[0]['both_eid_adt'];
					$both_eid_adt_percent = number_format(($both_eid_adt / $total_eid) * 100, 1);
					$on_eid_only = $results[0]['only_eid'];
					$on_eid_only_percent = number_format(($on_eid_only / $total_eid) * 100, 1);
					$on_adt_only = $results[0]['only_adt'];
					$on_adt_only_percent = number_format(($on_adt_only / $total_eid) * 100, 1);
				} else {
					$total_adt = 0;
					$total_adt = 0;
					$both_eid_adt = 0;
					$on_eid_only = 0;
					$on_adt_only = 0;
					$total_adt_percent = number_format(0 * 100, 1);
					$both_eid_adt_percent = number_format(0 * 100, 1);
					$on_eid_only_percent = number_format(0 * 100, 1);
					$on_adt_only_percent = number_format(0 * 100, 1);
				}
			}

			$tmpl = array('table_open' => '<table id="" class="table table-bordered table-striped">');
			$this -> table -> set_template($tmpl);
			$this -> table -> set_heading('', 'Description', 'Total', 'Rate');
			$this -> table -> add_row('', 'Total Enrolled on EID', number_format($total_eid), '100.0%');
			$this -> table -> add_row('', 'Total Enrolled on webADT', number_format($total_adt), $total_adt_percent . '%');
			$this -> table -> add_row('', 'Total Enrolled on both EID and webADT', number_format($both_eid_adt), $both_eid_adt_percent . '%');
			$this -> table -> add_row('', 'Total Enrolled on EID and not on webADT', number_format($on_eid_only), $on_eid_only_percent . '%');
			$this -> table -> add_row('', 'Total Enrolled on webADT and not on EID', number_format($on_adt_only), $on_adt_only_percent . '%');
			$table_display = $this -> table -> generate();
			echo $table_display;
			exit();
		}
		$query = $this -> db -> query($sql);
		$results = $query -> result_array();
		$inner_array = array();
		$resultArray = array();
		$categories = array('Label', 'Total');
		$inner_array['type'] = 'pie';
		$inner_array['name'] = 'eid_analysis';
		$lower_array = array();
		foreach ($results as $result) {
			if ($type == "regimen") {
				$label = explode("|", $result['label']);
				$lower_array[] = array($label[0], (int)$result['total']);
			} else {
				$lower_array[] = array($result['label'], (int)$result['total']);
			}
		}
		$inner_array['data'] = $lower_array;
		$resultArray[] = $inner_array;
		$resultArray = json_encode($resultArray);
		$categories = json_encode($categories);
		$data['container'] = $container;
		$data['chartType'] = 'pie';
		$data['resultArraySize'] = 6;
		$data['title'] = 'EID ' . $column;
		$data['chartTitle'] = 'EID Analysis By ' . $type;
		$data['categories'] = $categories;
		$data['yAxix'] = '% Total';
		$data['myData'] = $resultArray;
		$this -> load -> view('dashboard/chart_pie_v', $data);

	}

	public function reportingSatellites($period = "") {
		if ($period == "") {
			$period_begin = date('Y-m-01', strtotime("-1 month"));
			$period_end = date('Y-m-t', strtotime("-1 month"));

			$first = date('Y-m-01');
			$tenth = date('Y-m-10');
			$last_day = date('Y-m-t');
		} else {
			$period_begin = date('Y-m-01', strtotime($period));
			$period_end = date('Y-m-t', strtotime($period));

			$first = date('Y-m-01', strtotime($period));
			$tenth = date('Y-m-10', strtotime($period));
			$last_day = date('Y-m-t', strtotime($period));
		}

		$total_data = array();
		$sites_with_adt = 0;

		$columns = array("#", "Description", "Total No", "Rate");
		$expected_total = 0;
		$actual_total = 0;

		//get total arv satellites
		$satellite_summary = Cdrr::getSatelliteSummary($period_begin, $period_end);
		$expected_total = $satellite_summary['expected_total'];
		$actual_total = $satellite_summary['actual_total'];

		$total_data[0]['description'] = 'Total No of Satellite ARV Sites';
		$total_data[0]['total'] = $expected_total;
		$total_data[0]['rate'] = '-';

		//get satellites with webADT
		$sites_with_adt = Facilities::getSatellitesADTTotal();
		$total_data[1]['description'] = 'No of Satellite Sites with Web ADT Installed';
		$total_data[1]['total'] = $sites_with_adt;
		$total_data[1]['rate'] = '-';
		//get reported satellites
		$total_data[3]['description'] = 'Total No of Satellite Sites That Have Reported this month';
		$total_data[3]['total'] = $actual_total;
		if ($expected_total == 0) {
			$total_data[3]['rate'] = "0%";
		} else {
			$total_data[3]['rate'] = number_format(($actual_total / $expected_total), 2) . "%";
		}
		echo $this -> showTable($columns, $total_data, $links = array(), $table_name = "satellites");
	}

	public function adult_patients($period = "", $facility = 0, $county = 0) {
		$and_check_maps = " AND m.status NOT LIKE '%delete%' AND m.status NOT LIKE '%prepare%' ";
		$conditions = "";
		$regimens = array();

		if ($period == "") {
			$period = date('Y-m-01', strtotime("-1 month"));
		} else {
			$period = date('Y-m-01', strtotime($period));
		}

		$regimens["D4T+3TC+NVP"] = 0;
		$regimens["D4T+3TC+EFV"] = 0;
		$regimens["AZT+3TC+NVP"] = 0;
		$regimens["AZT+3TC+EFV"] = 0;
		$regimens["TDF+3TC+NVP"] = 0;
		$regimens["TDF+3TC+EFV"] = 0;
		$regimens["2ND LINE"] = 0;
		$regimens["OTHER REGIMENS"] = 0;

		if ($facility != 0) {
			$conditions .= "AND m.facility_id='$facility'";
		}
		if ($county != 0) {
			$conditions .= "AND c.id='$county'";
		}
		//scripts
		$sql1_kp = "SELECT er.name,SUM(mi.total) as total
						 FROM maps_item mi 
						 LEFT JOIN maps m ON m.id=mi.maps_id
						 LEFT JOIN escm_maps em ON em.maps_id = m.id
						 LEFT JOIN escm_regimen er ON er.id=mi.regimen_id
						 LEFT JOIN escm_facility ef ON ef.id=m.facility_id
						 LEFT JOIN counties c ON c.id=ef.county_id
						 WHERE m.period_begin='$period'
						 $and_check_maps
						 AND em.maps_id IS NOT NULL 
						 AND er.code IN ('AF1A',  'AF1B',  'AF2A',  'AF2B',  'AF3A',  'AF3B')
						 $conditions
						 GROUP BY er.code";

		$sql2_kp = "SELECT '2ND LINE' as name,SUM(mi.total) as total
						 FROM maps_item mi 
						 LEFT JOIN maps m ON m.id=mi.maps_id
						 LEFT JOIN escm_maps em ON em.maps_id = m.id
						 LEFT JOIN escm_regimen er ON er.id=mi.regimen_id
						 LEFT JOIN escm_facility ef ON ef.id=m.facility_id
						 LEFT JOIN counties c ON c.id=ef.county_id
						 WHERE m.period_begin='$period'
						 $and_check_maps
						 AND em.maps_id IS NOT NULL 
						 AND er.code IN ('AS1A','AS1B','AS2A','AS2B','AS2C','AS3A','AS4A')
						 $conditions";

		$sql3_kp = "SELECT 'OTHER REGIMENS' as name,SUM(mi.total) as total
						 FROM maps_item mi 
						 LEFT JOIN maps m ON m.id=mi.maps_id
						 LEFT JOIN escm_maps em ON em.maps_id = m.id
						 LEFT JOIN escm_regimen er ON er.id=mi.regimen_id
						 LEFT JOIN escm_facility ef ON ef.id=m.facility_id
						 LEFT JOIN counties c ON c.id=ef.county_id
						 LEFT JOIN sync_category sr ON sr.id=er.category_id
						 WHERE m.period_begin='$period'
						 $and_check_maps
						 AND em.maps_id IS NOT NULL 
						 AND sr.name LIKE '%Other Adult Regimen%'
						 $conditions";

		$sql1_ke = "SELECT er.name,SUM(mi.total) as total
						 FROM maps_item mi 
						 LEFT JOIN maps m ON m.id=mi.maps_id
						 LEFT JOIN escm_maps em ON em.maps_id = m.id
						 LEFT JOIN sync_regimen er ON er.id=mi.regimen_id
						 LEFT JOIN sync_facility ef ON ef.id=m.facility_id
						 LEFT JOIN counties c ON c.id=ef.county_id
						 WHERE m.period_begin='$period'
						 $and_check_maps
						 AND em.maps_id IS NULL 
						 AND er.code IN ('AF1A',  'AF1B',  'AF2A',  'AF2B',  'AF3A',  'AF3B')
						 $conditions
						 GROUP BY er.code";

		$sql2_ke = "SELECT '2ND LINE' as name,SUM(mi.total) as total
						 FROM maps_item mi 
						 LEFT JOIN maps m ON m.id=mi.maps_id
						 LEFT JOIN escm_maps em ON em.maps_id = m.id
						 LEFT JOIN sync_regimen er ON er.id=mi.regimen_id
						 LEFT JOIN sync_facility ef ON ef.id=m.facility_id
						 LEFT JOIN counties c ON c.id=ef.county_id
						 WHERE m.period_begin='$period'
						 $and_check_maps
						 AND em.maps_id IS NULL 
						 AND er.code IN ('AS1A','AS1B','AS2A','AS2B','AS2C','AS3A','AS4A')
						 $conditions";

		$sql3_ke = "SELECT 'OTHER REGIMENS' as name,SUM(mi.total) as total
						 FROM maps_item mi 
						 LEFT JOIN maps m ON m.id=mi.maps_id
						 LEFT JOIN escm_maps em ON em.maps_id = m.id
						 LEFT JOIN sync_regimen er ON er.id=mi.regimen_id
						 LEFT JOIN sync_facility ef ON ef.id=m.facility_id
						 LEFT JOIN counties c ON c.id=ef.county_id
						 LEFT JOIN sync_category sr ON sr.id=er.category_id
						 WHERE m.period_begin='$period'
						 $and_check_maps
						 AND em.maps_id IS NULL 
						 AND sr.name LIKE '%Other Adult Regimen%'
						 $conditions";

		//execute first lines of both
		$query1 = $this -> db -> query($sql1_kp);
		$query2 = $this -> db -> query($sql1_ke);
		$results1 = $query1 -> result_array();
		$results2 = $query2 -> result_array();
		if ($results1) {
			foreach ($results1 as $value) {
				$label = strtoupper(str_replace(" ", "", $value['name']));
				$regimens[$label] = $value['total'];
			}
		}

		if ($results2) {
			foreach ($results2 as $value) {
				$label = strtoupper(str_replace(" ", "", $value['name']));
				$regimens[$label] = $regimens[$label] + $value['total'];
			}
		}

		//execute second line
		$query1 = $this -> db -> query($sql2_kp);
		$query2 = $this -> db -> query($sql2_ke);
		$results1 = $query1 -> result_array();
		$results2 = $query2 -> result_array();
		if ($results1) {
			foreach ($results1 as $value) {
				$label = $value['name'];
				$regimens[$label] = $value['total'];
			}
		}

		if ($results2) {
			foreach ($results2 as $value) {
				$label = $value['name'];
				$regimens[$label] = $regimens[$label] + $value['total'];
			}
		}

		//execute others
		$query1 = $this -> db -> query($sql3_kp);
		$query2 = $this -> db -> query($sql3_ke);
		$results1 = $query1 -> result_array();
		$results2 = $query2 -> result_array();
		if ($results1) {
			foreach ($results1 as $value) {
				$label = $value['name'];
				$regimens[$label] = $value['total'];
			}
		}

		if ($results2) {
			foreach ($results2 as $value) {
				$label = $value['name'];
				$regimens[$label] = $regimens[$label] + $value['total'];
			}
		}

		$categories = array("D4T+3TC+NVP", "D4T+3TC+EFV", "AZT+3TC+NVP", "AZT+3TC+EFV", "TDF+3TC+NVP", "TDF+3TC+EFV", "2ND LINE", "OTHER REGIMENS");

		foreach ($regimens as $regimen) {
			$values[] = (int)$regimen;
		}
		$resultArray = array( array('name' => 'Number of Patients', 'data' => $values));
		$resultArray = json_encode($resultArray);
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
	}

	public function paed_patients($period = "", $facility = 0, $county = 0) {
		$and_check_maps = " AND m.status NOT LIKE '%delete%' AND m.status NOT LIKE '%prepare%' ";
		$conditions = "";
		$regimens = array();

		if ($period == "") {
			$period = date('Y-m-01', strtotime("-1 month"));
		} else {
			$period = date('Y-m-01', strtotime($period));
		}

		$regimens["AZT+3TC+NVP"] = 0;
		$regimens["AZT+3TC+EFV"] = 0;
		$regimens["ABC+3TC+NVP"] = 0;
		$regimens["ABC+3TC+EFV"] = 0;
		$regimens["ABC+3TC+LPV/R"] = 0;
		$regimens["AZT+3TC+LPV/R"] = 0;
		$regimens["2ND LINE"] = 0;
		$regimens["OTHER REGIMENS"] = 0;

		if ($facility != 0) {
			$conditions .= "AND m.facility_id='$facility'";
		}
		if ($county != 0) {
			$conditions .= "AND c.id='$county'";
		}
		//scripts
		$sql1_kp = "SELECT er.name,SUM(mi.total) as total
						 FROM maps_item mi 
						 LEFT JOIN maps m ON m.id=mi.maps_id
						 LEFT JOIN escm_maps em ON em.maps_id = m.id
						 LEFT JOIN escm_regimen er ON er.id=mi.regimen_id
						 LEFT JOIN escm_facility ef ON ef.id=m.facility_id
						 LEFT JOIN counties c ON c.id=ef.county_id
						 WHERE m.period_begin='$period'
						 $and_check_maps
						 AND em.maps_id IS NOT NULL 
						 AND er.code IN ('CF1A',  'CF1B', 'CF1C' , 'CF2A',  'CF2B' ,'CF2C',  'CF2D', 'CF3A',  'CF3B')
						 $conditions
						 GROUP BY er.code";

		$sql2_kp = "SELECT '2ND LINE' as name,SUM(mi.total) as total
						 FROM maps_item mi 
						 LEFT JOIN maps m ON m.id=mi.maps_id
						 LEFT JOIN escm_maps em ON em.maps_id = m.id
						 LEFT JOIN escm_regimen er ON er.id=mi.regimen_id
						 LEFT JOIN escm_facility ef ON ef.id=m.facility_id
						 LEFT JOIN counties c ON c.id=ef.county_id
						 WHERE m.period_begin='$period'
						 $and_check_maps
						 AND em.maps_id IS NOT NULL 
						 AND er.code IN ('CS1A',  'CS1B', 'CS1C'  ,'CS2A', 'CS2B','CS2C', 'CS2D' ,'CS3A',  'CS3B')
						 $conditions";

		$sql3_kp = "SELECT 'OTHER REGIMENS' as name,SUM(mi.total) as total
						 FROM maps_item mi 
						 LEFT JOIN maps m ON m.id=mi.maps_id
						 LEFT JOIN escm_maps em ON em.maps_id = m.id
						 LEFT JOIN escm_regimen er ON er.id=mi.regimen_id
						 LEFT JOIN escm_facility ef ON ef.id=m.facility_id
						 LEFT JOIN counties c ON c.id=ef.county_id
						 LEFT JOIN sync_category sr ON sr.id=er.category_id
						 WHERE m.period_begin='$period'
						 $and_check_maps
						 AND em.maps_id IS NOT NULL 
						 AND sr.name LIKE '%Other Paediatric ART Regimen%'
						 $conditions";

		$sql1_ke = "SELECT er.name,SUM(mi.total) as total
						 FROM maps_item mi 
						 LEFT JOIN maps m ON m.id=mi.maps_id
						 LEFT JOIN escm_maps em ON em.maps_id = m.id
						 LEFT JOIN sync_regimen er ON er.id=mi.regimen_id
						 LEFT JOIN sync_facility ef ON ef.id=m.facility_id
						 LEFT JOIN counties c ON c.id=ef.county_id
						 WHERE m.period_begin='$period'
						 $and_check_maps
						 AND em.maps_id IS NULL 
						 AND er.code IN ('CF1A',  'CF1B', 'CF1C' , 'CF2A',  'CF2B' ,'CF2C',  'CF2D', 'CF3A',  'CF3B')
						 $conditions
						 GROUP BY er.code";

		$sql2_ke = "SELECT '2ND LINE' as name,SUM(mi.total) as total
						 FROM maps_item mi 
						 LEFT JOIN maps m ON m.id=mi.maps_id
						 LEFT JOIN escm_maps em ON em.maps_id = m.id
						 LEFT JOIN sync_regimen er ON er.id=mi.regimen_id
						 LEFT JOIN sync_facility ef ON ef.id=m.facility_id
						 LEFT JOIN counties c ON c.id=ef.county_id
						 WHERE m.period_begin='$period'
						 $and_check_maps
						 AND em.maps_id IS NULL 
						 AND er.code IN ('CS1A',  'CS1B', 'CS1C'  ,'CS2A', 'CS2B','CS2C', 'CS2D' ,'CS3A',  'CS3B')
						 $conditions";

		$sql3_ke = "SELECT 'OTHER REGIMENS' as name,SUM(mi.total) as total
						 FROM maps_item mi 
						 LEFT JOIN maps m ON m.id=mi.maps_id
						 LEFT JOIN escm_maps em ON em.maps_id = m.id
						 LEFT JOIN sync_regimen er ON er.id=mi.regimen_id
						 LEFT JOIN sync_facility ef ON ef.id=m.facility_id
						 LEFT JOIN counties c ON c.id=ef.county_id
						 LEFT JOIN sync_category sr ON sr.id=er.category_id
						 WHERE m.period_begin='$period'
						 $and_check_maps
						 AND em.maps_id IS NULL 
						 AND sr.name LIKE '%Other Paediatric ART Regimen%'
						 $conditions";

		//execute first lines of both
		$query1 = $this -> db -> query($sql1_kp);
		$query2 = $this -> db -> query($sql1_ke);
		$results1 = $query1 -> result_array();
		$results2 = $query2 -> result_array();
		if ($results1) {
			foreach ($results1 as $value) {
				$label = strtoupper(str_replace(" ", "", $value['name']));
				$regimens[$label] = $value['total'];
			}
		}

		if ($results2) {
			foreach ($results2 as $value) {
				$label = strtoupper(str_replace(" ", "", $value['name']));
				$regimens[$label] = @$regimens[$label] + $value['total'];
			}
		}

		//execute second line
		$query1 = $this -> db -> query($sql2_kp);
		$query2 = $this -> db -> query($sql2_ke);
		$results1 = $query1 -> result_array();
		$results2 = $query2 -> result_array();
		if ($results1) {
			foreach ($results1 as $value) {
				$label = $value['name'];
				$regimens[$label] = $value['total'];
			}
		}

		if ($results2) {
			foreach ($results2 as $value) {
				$label = $value['name'];
				$regimens[$label] = $regimens[$label] + $value['total'];
			}
		}

		//execute others
		$query1 = $this -> db -> query($sql3_kp);
		$query2 = $this -> db -> query($sql3_ke);
		$results1 = $query1 -> result_array();
		$results2 = $query2 -> result_array();
		if ($results1) {
			foreach ($results1 as $value) {
				$label = $value['name'];
				$regimens[$label] = $value['total'];
			}
		}

		if ($results2) {
			foreach ($results2 as $value) {
				$label = $value['name'];
				$regimens[$label] = $regimens[$label] + $value['total'];
			}
		}
		$categories = array("AZT+3TC+NVP", "AZT+3TC+EFV", "ABC+3TC+NVP", "ABC+3TC+EFV", "ABC+3TC+LPV/R", "AZT+3TC+LPV/R", "2ND LINE", "OTHER REGIMENS");

		foreach ($regimens as $regimen) {
			$values[] = (int)$regimen;
		}
		$resultArray = array( array('name' => 'Number of Patients', 'data' => $values));
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
	}

	public function two_pager() {

		$files = Two_pager::getAllHydrated();
		$list = '<table class="table table-bordered table-striped tbl_nat_dashboard" id="TWO_PAGER">
	    			<thead>
	    				<tr><th>No</th><th> Period</th><th>Action</th></tr>
	    			</thead>
	    			<tbody>';

		$x = 1;
		foreach ($files as $value) {
			$list .= '<tr><td>' . $x . '</td><td>Stock Situation ' . $value['period'] . '</td><td><a href="' . base_url() . $value['link'] . '">Download</a></td></tr>';
			$x++;
		}
		$list .= '				
	    			</tbody>
			    </table>';
		echo $list;
	}

	public function fix_bug($periods = array()) {
		$current_selection = date('Y-m-01', strtotime("-1 month"));
		$end_selection = date('Y-m-t', strtotime("-1 month"));
		$count = 0;
		foreach ($periods as $period) {
			if (in_array($current_selection, $period)) {
				$count++;
			}
			if (in_array($end_selection, $period)) {
				$count++;
			}
		}
		//if count is equal to zero then no current selection period exists and should be added
		if ($count == 0) {
			array_unshift($periods, array('id' => $current_selection, 'period_begin' => $current_selection));
		}
		return $periods;
	}

	public function base_params($data) {
		$this -> load -> view("template_national", $data);
	}

}
