<?php
if (!defined('BASEPATH'))
	exit('No direct script access allowed');

class Pipeline_Management extends MY_Controller {
	function __construct() {
		parent::__construct();
		$data = array();
		$this -> load -> library('PHPExcel');
		ini_set("max_execution_time", "10000");
	}

	public function index() {

		$data['content_view'] = "pipeline_upload";
		$data['hide_side_menu'] = 1;
		$this -> base_params($data);
	}

	public function upload() {
		$pipeline = $_POST['pipeline_name'];
		$upload_period = $_POST['upload_date'];
		$report_type = $_POST['test_type'];
		$book_type = $_POST['book_type'];
		$period = explode('-', $upload_period);
		$year = $period[1];
		$month = date('m', strtotime($period[0]));
		$this -> session -> set_userdata('upload_counter', '2');
		if ($book_type == 0) {
			//Upload was worksheet
			$this -> workbook($_FILES['file']['tmp_name'], $pipeline, $year, $month);
		} else if ($book_type == 1) {
			//Upload was single sheet
			$this -> worksheet($_FILES['file']['tmp_name'], $pipeline, $year, $month, $report_type);
		} else {
			$this -> session -> set_userdata('upload_counter', '1');
		}
		redirect("pipeline_management/index");

	}

	public function workbook($uploaded_file, $pipeline, $year, $month) {
		if ($pipeline == 1) {
			//Pipeline is Kemsa
			$this -> workbook_kemsa($uploaded_file, $pipeline, $year, $month);
		} else if ($pipeline == 2) {
			//Pipeline is Kenya Pharma
			$this -> workbook_kp($uploaded_file, $pipeline, $year, $month);
		}

	}

	public function workbook_kemsa($uploaded_file, $pipeline, $year, $month) {
		//Load file into memory
		$objPHPExcel = PHPExcel_IOFactory::load($uploaded_file);
		$CurrentWorkSheetIndex = 0;
		//Iterate through an unknown structure
		foreach ($objPHPExcel->getWorksheetIterator() as $worksheet) {
			$CurrentWorkSheetIndex++;
			$worksheetTitle = $worksheet -> getTitle();
			if ($worksheetTitle == "Ordering Points" || $CurrentWorkSheetIndex == 3) {
				//Check for ordering point sheet
				$this -> workbook_kemsa_op($worksheet, $pipeline, $year, $month);
			} else if ($worksheetTitle == "Service Points" || $CurrentWorkSheetIndex == 4) {
				//Check for service point sheet
				$this -> workbook_kemsa_sp($worksheet, $pipeline, $year, $month);
			} else if ($worksheetTitle == "Patients By Regimen" || $CurrentWorkSheetIndex == 6) {
				//Check if Patient By Regimen Sheet
				$this -> workbook_kemsa_pbyregimen($worksheet, $pipeline, $year, $month);
			} else if ($worksheetTitle == "Aggregate Patient data" || $CurrentWorkSheetIndex == 7) {
				//Check if Aggregate Patient Data sheet
				$this -> workbook_kemsa_paggregate($worksheet, $pipeline, $year, $month);
			} else if ($worksheetTitle == "ART Patient Scaleup Trends" || $CurrentWorkSheetIndex == 8) {
				//Check if Patient Scaleup Sheet
				$this -> workbook_kemsa_pscaleup($worksheet, $pipeline, $year, $month);
			} else if ($worksheetTitle == "Current patients By ART Site" || $CurrentWorkSheetIndex == 9) {
				//Check if Current Patients By ART  Sheet
				$this -> workbook_kemsa_pcurrent($worksheet, $pipeline, $year, $month);
			} else if ($worksheetTitle == "Pipeline Commodity Consumption" || $CurrentWorkSheetIndex == 10) {
				//Check if Pipeline Commodity Consumption Sheet
				$this -> workbook_kemsa_piconsumption($worksheet, $pipeline, $year, $month);
			} else if ($worksheetTitle == "Facility Cons BY ARV Medicine" || $CurrentWorkSheetIndex == 12) {
				//Check if Facility Cons BY ARV Sheet
				$this -> workbook_kemsa_faconsumption($worksheet, $pipeline, $year, $month);
			} else if ($worksheetTitle == "Facility SOH BY ARV Medicine" || $CurrentWorkSheetIndex == 13) {
				//Check if Facility SOH BY ARV Sheet
				$this -> workbook_kemsa_faSOH($worksheet, $pipeline, $year, $month);
			}
		}
	}

	public function workbook_kemsa_op($worksheet, $pipeline, $year, $month) {
		$highestRow = $worksheet -> getHighestRow();
		$highestColumn = $worksheet -> getHighestColumn();
		$highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);
		for ($row = 8; $row <= $highestRow; ++$row) {
			for ($col = 11; $col < $highestColumnIndex; ++$col) {
				//Get facility codes
				$mflcode_cell = $worksheet -> getCellByColumnAndRow(11, $row);
				$facilityname_cell = $worksheet -> getCellByColumnAndRow(12, $row);
				$district_cell = $worksheet -> getCellByColumnAndRow(13, $row);
				$province_cell = $worksheet -> getCellByColumnAndRow(14, $row);
				$central_cell = $worksheet -> getCellByColumnAndRow(15, $row);
				$standalone_cell = $worksheet -> getCellByColumnAndRow(16, $row);
				$store_cell = $worksheet -> getCellByColumnAndRow(17, $row);
				$mfl_code = $mflcode_cell -> getValue();
				$facility_name = $facilityname_cell -> getValue();
				$district = $district_cell -> getValue();
				$province = $province_cell -> getValue();
				$central = 0;
				$standalone = 0;
				$store = 0;
				if ($central_cell -> getValue()) {
					$central = 1;
				}
				if ($standalone_cell -> getValue()) {
					$standalone = 1;
				}
				if ($store_cell -> getValue()) {
					$store = 1;
				}
			}
			$validity = Dashboard_Orderpoints::checkValid($pipeline, $month, $year, $mfl_code);
			if (!$validity) {

				$orderpoints_report = new Dashboard_Orderpoints();
				$orderpoints_report -> pipeline = $pipeline;
				$orderpoints_report -> month = $month;
				$orderpoints_report -> year = $year;
				$orderpoints_report -> mfl_code = $mfl_code;
				$orderpoints_report -> facility_name = $facility_name;
				$orderpoints_report -> district = $district;
				$orderpoints_report -> province = $province;
				$orderpoints_report -> central = $central;
				$orderpoints_report -> standalone = $standalone;
				$orderpoints_report -> store = $store;
				$orderpoints_report -> save();
			}
		}

	}

	public function workbook_kemsa_sp($worksheet, $pipeline, $year, $month) {
		$highestRow = $worksheet -> getHighestRow();
		$highestColumn = $worksheet -> getHighestColumn();
		$highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);
		$arr = $worksheet -> toArray(null, true, true, true);
		for ($row = 4; $row <= $highestRow; ++$row) {
			for ($col = 12; $col < $highestColumnIndex; ++$col) {
				$id_cell = $worksheet -> getCellByColumnAndRow(10, $row);
				$mflcode_cell = $worksheet -> getCellByColumnAndRow(11, $row);
				$facilityname_cell = $worksheet -> getCellByColumnAndRow(12, $row);
				$centralsite_cell = $worksheet -> getCellByColumnAndRow(13, $row);
				$district_cell = $worksheet -> getCellByColumnAndRow(14, $row);
				$province_cell = $worksheet -> getCellByColumnAndRow(15, $row);
				$dispensing_cell = $worksheet -> getCellByColumnAndRow(16, $row);
				$standalone_cell = $worksheet -> getCellByColumnAndRow(17, $row);
				$satelite_cell = $worksheet -> getCellByColumnAndRow(18, $row);
				$mfl_code = $mflcode_cell -> getValue();
				$facility_name = $arr[$row]['M'];
				$centralsite_name = $centralsite_cell -> getValue();
				$district = $district_cell -> getValue();
				$province = $province_cell -> getValue();
				$dispensing = 0;
				$standalone = 0;
				$satelite = 0;
				if ($dispensing_cell -> getValue()) {
					$dispensing = 1;
				}
				if ($standalone_cell -> getValue()) {
					$standalone = 1;
				}
				if ($satelite_cell -> getValue()) {
					$satelite = 1;
				}
			}
			if ($id_cell -> getValue()) {
				$facility_name = str_replace(".", "", $facility_name);
				$validity = Dashboard_Servicepoints::checkValid($pipeline, $month, $year, $facility_name, $mfl_code);
				if (!$validity) {
					$servicepoints_report = new Dashboard_Servicepoints();
					$servicepoints_report -> pipeline = $pipeline;
					$servicepoints_report -> month = $month;
					$servicepoints_report -> year = $year;
					$servicepoints_report -> mfl_code = $mfl_code;
					$servicepoints_report -> facility_name = $facility_name;
					$servicepoints_report -> centralsite_name = $centralsite_name;
					$servicepoints_report -> district = $district;
					$servicepoints_report -> province = $province;
					$servicepoints_report -> dispensing = $dispensing;
					$servicepoints_report -> standalone = $standalone;
					$servicepoints_report -> satellite = $satelite;
					$servicepoints_report -> save();
				}
			}

		}

	}

	public function workbook_kemsa_pbyregimen($worksheet, $pipeline, $year, $month) {
		$highestRow = $worksheet -> getHighestRow();
		$highestColumn = $worksheet -> getHighestColumn();
		$highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);
		$arr = $worksheet -> toArray(null, true, true, true);
		$label[0] = $arr[6]['H'];
		$value[0] = $arr[6]['I'];
		$label[1] = $arr[7]['H'];
		$value[1] = $arr[7]['I'];
		$label[2] = $arr[9]['H'];
		$value[2] = $arr[9]['I'];
		$label[3] = $arr[8]['H'];
		$value[3] = $arr[8]['I'];
		$label[4] = $arr[10]['H'];
		$value[4] = $arr[10]['I'];
		$label[5] = $arr[11]['H'];
		$value[5] = $arr[11]['I'];

		for ($i = 0; $i < 6; $i++) {
			$validity = Dashboard_Patientbyline::checkValid($pipeline, $month, $year, $label[$i]);
			if (!$validity) {
				$new_patentbyline = new Dashboard_Patientbyline();
				$new_patentbyline -> pipeline = $pipeline;
				$new_patentbyline -> month = $month;
				$new_patentbyline -> year = $year;
				$new_patentbyline -> category = $label[$i];
				$new_patentbyline -> total = $value[$i];
				$new_patentbyline -> save();
			}
		}

	}

	public function workbook_kemsa_paggregate($worksheet, $pipeline, $year, $month) {
		$highestRow = $worksheet -> getHighestRow();
		$highestColumn = $worksheet -> getHighestColumn();
		$highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);
		$arr = $worksheet -> toArray(null, true, true, true);
		$site_in_art = $arr[7]['C'];
		$site_in_pmtct = $arr[7]['E'];
		$site_in_pep = $arr[7]['G'];
		$total_art_adults = $arr[11]['C'];
		$total_art_children = $arr[11]['G'];
		$total_males_new = $arr[15]['C'];
		$total_males_revisit = $arr[15]['D'];
		$total_females_new = $arr[15]['E'];
		$total_females_revisit = $arr[15]['F'];
		$total_pmtct_new = $arr[18]['D'];
		$total_pmtct_revisit = $arr[18]['G'];
		$total_infants_pmtct = $arr[20]['D'];
		$total_pep_adults = $arr[23]['D'];
		$total_pep_children = $arr[23]['G'];
		$total_oi_adult = $arr[28]['E'];
		$total_oi_children = $arr[28]['G'];
		$total_diflucan_adults = $arr[31]['E'];
		$total_diflucan_children = $arr[31]['E'];
		$cm_new = $arr[35]['C'];
		$cm_revisit = $arr[35]['D'];
		$oc_new = $arr[35]['E'];
		$oc_revisit = $arr[35]['F'];

		$validity = Dashboard_Paggregate::checkValid($pipeline, $month, $year);
		if (!$validity) {
			$paggregate_report = new Dashboard_Paggregate();
			$paggregate_report -> month = $month;
			$paggregate_report -> year = $year;
			$paggregate_report -> sites_in_art = $site_in_art;
			$paggregate_report -> sites_in_pmtct = $site_in_pmtct;
			$paggregate_report -> sites_in_pep = $site_in_pep;
			$paggregate_report -> total_art_adults = $total_art_adults;
			$paggregate_report -> total_art_children = $total_art_children;
			$paggregate_report -> total_males_new = $total_males_new;
			$paggregate_report -> total_males_revisit = $total_males_revisit;
			$paggregate_report -> total_females_new = $total_females_new;
			$paggregate_report -> total_females_revisit = $total_females_revisit;
			$paggregate_report -> total_pmtct_new = $total_pmtct_new;
			$paggregate_report -> total_pmtct_revisit = $total_pmtct_revisit;
			$paggregate_report -> total_infants_pmtct = $total_infants_pmtct;
			$paggregate_report -> total_pep_adults = $total_pep_adults;
			$paggregate_report -> total_pep_children = $total_pep_children;
			$paggregate_report -> total_oi_adult = $total_oi_adult;
			$paggregate_report -> total_oi_children = $total_oi_children;
			$paggregate_report -> total_diflucan_adults = $total_diflucan_adults;
			$paggregate_report -> total_diflucan_children = $total_diflucan_children;
			$paggregate_report -> cm_new = $cm_new;
			$paggregate_report -> cm_revisit = $cm_revisit;
			$paggregate_report -> oc_new = $oc_new;
			$paggregate_report -> oc_revisit = $oc_revisit;
			$paggregate_report -> pipeline = $pipeline;
			$paggregate_report -> save();
		}
	}

	public function workbook_kemsa_pscaleup($worksheet, $pipeline, $year, $month) {
		$highestRow = $worksheet -> getHighestRow();
		$highestColumn = $worksheet -> getHighestColumn();
		$highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);
		$arr = $worksheet -> toArray(null, true, true, true);
		for ($row = 8; $row <= $highestRow; ++$row) {
			if ($arr[$row]["A"] != "") {
				$facility_cell = $worksheet -> getCellByColumnAndRow(0, $row);
				$period_name = $facility_cell -> getValue();
				for ($col = 0; $col < $highestColumnIndex; ++$col) {

				}
				$month = date('m', strtotime($arr[$row]["A"]));
				$year = "20" . date('d', strtotime($arr[$row]["A"]));
				$adult_art = $arr[$row]["B"];
				$paed_art = $arr[$row]["C"];
				$paed_pep = $arr[$row]["E"];
				$adult_pep = $arr[$row]["F"];
				$mother_pmtct = $arr[$row]["G"];
				$infant_pmtct = $arr[$row]["H"];
				$validity = Patient_Scaleup::checkValid($pipeline, $month, $year);
				if (!$validity) {
					$ps_report = new Patient_Scaleup();
					$ps_report -> pipeline = $pipeline;
					$ps_report -> month = $month;
					$ps_report -> year = $year;
					$ps_report -> adult_art = $adult_art;
					$ps_report -> paed_art = $paed_art;
					$ps_report -> paed_pep = $paed_pep;
					$ps_report -> adult_pep = $adult_pep;
					$ps_report -> mothers_pmtct = $mother_pmtct;
					$ps_report -> infant_pmtct = $infant_pmtct;
					$ps_report -> save();
				}
			} else {
				exit ;
			}
		}
	}

	public function workbook_kemsa_pcurrent($worksheet, $pipeline, $year, $month) {
		$validity = Patient_Byregimen_Numbers::checkValid($pipeline, $month, $year);
		if (!$validity) {
			$highestRow = $worksheet -> getHighestRow();
			$highestColumn = $worksheet -> getHighestColumn();
			$highestColumnIndex = PHPExcel_Cell::columnIndexFromString("EC");
			for ($row = 10; $row <= ($highestRow - 1); ++$row) {
				$facility_cell = $worksheet -> getCellByColumnAndRow(1, $row);
				$comments_cell = $worksheet -> getCellByColumnAndRow(2, $row);
				$facility_name = $facility_cell -> getValue();
				$facility_name = trim(str_replace(array('\'', '"', ',', ';', '<', '>', '.'), ' ', $facility_name));
				$comments = $comments_cell -> getValue();
				for ($col = 4; $col < ($highestColumnIndex - 1); ++$col) {
					$cell = $worksheet -> getCellByColumnAndRow($col, $row);
					$regimen_desc_cell = $worksheet -> getCellByColumnAndRow($col, 1);
					$regimen_code_cell = $worksheet -> getCellByColumnAndRow($col, 6);
					$prev_regimen_code_cell = $worksheet -> getCellByColumnAndRow($col, 7);
					$val = $cell -> getValue();
					if ($val == null) {
						$val = 0;
					}
					if ($facility_name) {
						$pipeline_report = new Patient_Byregimen_Numbers();
						$pipeline_report -> facilityname = $facility_name;
						$pipeline_report -> comments = $comments;
						$pipeline_report -> regimen_desc = $regimen_desc_cell;
						$pipeline_report -> regimen_code = $regimen_code_cell;
						$pipeline_report -> previous_code = $prev_regimen_code_cell;
						$pipeline_report -> month = $month;
						$pipeline_report -> year = $year;
						$pipeline_report -> total = $val;
						$pipeline_report -> pipeline = $pipeline;
						$pipeline_report -> save();
					} else if ($facility_name == "Category") {
						exit();
					}
				}
			}
		}
	}

	public function workbook_kemsa_piconsumption($worksheet, $pipeline, $year, $month) {
		$highestRow = $worksheet -> getHighestRow();
		$highestColumn = $worksheet -> getHighestColumn();
		$highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);
		$arr = $worksheet -> toArray(null, true, true, true);
		for ($row = 10; $row <= $highestRow; ++$row) {
			for ($col = 2; $col < $highestColumnIndex; ++$col) {
				$cell = $worksheet -> getCellByColumnAndRow($col, $row);
				$drugname_cell = $arr[$row]['B'];
				$month = date('m', strtotime($worksheet -> getCellByColumnAndRow($col, 7)));
				if ($worksheet -> getCellByColumnAndRow($col, 7) == "February") {
					$month = "02";
				}
				$year = $worksheet -> getCellByColumnAndRow($col, 6);
				$val = $cell -> getValue();
				if ($drugname_cell != null) {
					if (is_string($val) || $val == null) {
						$val = 0;
					}
					$validity = Pipeline_Consumption::checkValid($pipeline, $month, $year, $drugname_cell);

					if (!$validity) {
						if ($drugname_cell !== "Drugs for OIs") {
							$pc_report = new Pipeline_Consumption();
							$pc_report -> pipeline = $pipeline;
							$pc_report -> month = $month;
							$pc_report -> year = $year;
							$pc_report -> drugname = $drugname_cell;
							$pc_report -> consumption = $val;
							$pc_report -> save();
						}
					}
				}

			}
		}
	}

	public function workbook_kemsa_faconsumption($worksheet, $pipeline, $year, $month) {
		$validity = Facility_Consumption::checkValid($pipeline, $month, $year);
		if (!$validity) {
			$highestRow = $worksheet -> getHighestRow();
			$highestColumn = $worksheet -> getHighestColumn();
			$highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);
			for ($row = 9; $row <= ($highestRow); ++$row) {
				$facility_cell = $worksheet -> getCellByColumnAndRow(2, $row);
				$facility_name = $facility_cell -> getValue();
				$facilityname_cell = $worksheet -> getCellByColumnAndRow(1, $row);
				$facility_code = $facilityname_cell -> getValue();
				for ($col = 3; $col < ($highestColumnIndex); ++$col) {
					$cell = $worksheet -> getCellByColumnAndRow($col, $row);
					$drugname_cell = $worksheet -> getCellByColumnAndRow($col, 7);
					$val = $cell -> getValue();
					if ($facility_name != "") {
						if ($val == null) {
							$val = 0;
						}
						if ($facility_name !== "Grand Total") {
							$fconsumption_report = new Facility_Consumption();
							$fconsumption_report -> facilityname = $facility_name;
							$fconsumption_report -> facilitycode = $facility_code;
							$fconsumption_report -> drugname = $drugname_cell;
							$fconsumption_report -> month = $month;
							$fconsumption_report -> year = $year;
							$fconsumption_report -> total = $val;
							$fconsumption_report -> pipeline = $pipeline;
							$fconsumption_report -> save();
						}
					}
				}
			}
		}
	}

	public function workbook_kemsa_faSOH($worksheet, $pipeline, $year, $month) {
		$validity = Facility_Soh::checkValid($pipeline, $month, $year);
		if (!$validity) {
			$highestRow = $worksheet -> getHighestRow();
			$highestColumn = $worksheet -> getHighestColumn();
			$highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);
			$arr = $worksheet -> toArray(null, true, true, true);
			for ($row = 9; $row <= ($highestRow - 1); ++$row) {
				$facility_cell = $worksheet -> getCellByColumnAndRow(2, $row);
				$facilitycode_cell = $worksheet -> getCellByColumnAndRow(1, $row);
				$facility_name = $facility_cell -> getValue();
				$facility_code = $facilitycode_cell -> getValue();
				for ($col = 3; $col < ($highestColumnIndex); ++$col) {
					$cell = $worksheet -> getCellByColumnAndRow($col, $row);
					$drugname_cell = $worksheet -> getCellByColumnAndRow($col, 7);
					$val = $cell -> getValue();
					if ($facility_name != "") {
						if ($val == null) {
							$val = 0;
						}
						if ($facility_name !== "Grand Total") {
							$fsoh_report = new Facility_Soh();
							$fsoh_report -> facilityname = $facility_name;
							$fsoh_report -> facilitycode = $facility_code;
							$fsoh_report -> drugname = $drugname_cell;
							$fsoh_report -> month = $month;
							$fsoh_report -> year = $year;
							$fsoh_report -> total = $val;
							$fsoh_report -> pipeline = $pipeline;
							$fsoh_report -> save();
						}
					}

				}
			}
		}
	}

	public function workbook_kp($uploaded_file, $pipeline, $year, $month) {
		//Load file into memory
		$objPHPExcel = PHPExcel_IOFactory::load($uploaded_file);
		$CurrentWorkSheetIndex = 0;
		//Iterate through an unknown structure
		foreach ($objPHPExcel->getWorksheetIterator() as $worksheet) {
			$CurrentWorkSheetIndex++;
			$worksheetTitle = $worksheet -> getTitle();
			if ($worksheetTitle == "ART Ordering Points" || $CurrentWorkSheetIndex == 3) {
				//Check for ordering point sheet
				//$this -> workbook_kp_op($worksheet, $pipeline, $year, $month);
			} else if ($worksheetTitle == "ART Service Points" || $CurrentWorkSheetIndex == 4) {
				//Check for service point sheet
				$this -> workbook_kp_sp($worksheet, $pipeline, $year, $month);
			} else if ($worksheetTitle == "Aggregate Patient data" || $CurrentWorkSheetIndex == 6) {
				//Check if Aggregate Patient Data sheet
				//$this -> workbook_kp_paggregate($worksheet, $pipeline, $year, $month);
			} else if ($worksheetTitle == "Patients by Regimen" || $CurrentWorkSheetIndex == 7) {
				//Check if Patient By Regimen Sheet
				//$this -> workbook_kp_pbyregimen($worksheet, $pipeline, $year, $month);
			} else if ($worksheetTitle == "ART Patients Scaleup Trends" || $CurrentWorkSheetIndex == 8) {
				//Check if Patient Scaleup Sheet
				//$this -> workbook_kp_pscaleup($worksheet, $pipeline, $year, $month);
			} else if ($worksheetTitle == "Current Patients By ART Site" || $CurrentWorkSheetIndex == 9) {
				//Check if Current Patients By ART  Sheet
				$this -> workbook_kp_pcurrent($worksheet, $pipeline, $year, $month);
			} else if ($worksheetTitle == "Pipeline Commodity Consumption" || $CurrentWorkSheetIndex == 10) {
				//Check if Pipeline Commodity Consumption Sheet
				//$this -> workbook_kp_piconsumption($worksheet, $pipeline, $year, $month);
			} else if ($worksheetTitle == "Facility Cons BY ARV medicine" || $CurrentWorkSheetIndex == 12) {
				//Check if Facility Cons BY ARV Sheet
				//$this -> workbook_kp_faconsumption($worksheet, $pipeline, $year, $month);
			} else if ($worksheetTitle == "Facility SOH BY ARV Medicine" || $CurrentWorkSheetIndex == 13) {
				//Check if Facility SOH BY ARV Sheet
				//$this -> workbook_kp_faSOH($worksheet, $pipeline, $year, $month);
			}
		}
	}

	public function workbook_kp_op($worksheet, $pipeline, $year, $month) {
		$highestRow = $worksheet -> getHighestRow();
		$highestColumn = $worksheet -> getHighestColumn();
		$highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);
		for ($row = 9; $row <= $highestRow; ++$row) {
			for ($col = 11; $col < $highestColumnIndex; ++$col) {
				$mflcode_cell = $worksheet -> getCellByColumnAndRow(11, $row);
				$facilityname_cell = $worksheet -> getCellByColumnAndRow(12, $row);
				$centralsite_cell = $worksheet -> getCellByColumnAndRow(13, $row);
				$district_cell = $worksheet -> getCellByColumnAndRow(14, $row);
				$province_cell = $worksheet -> getCellByColumnAndRow(15, $row);
				$facilitytype_cell = $worksheet -> getCellByColumnAndRow(16, $row);
				$mfl_code = $mflcode_cell -> getValue();
				$facility_name = $facilityname_cell -> getValue();
				$district = $district_cell -> getValue();
				$province = $province_cell -> getValue();
				$central = 0;
				$standalone = 0;
				$store = 0;
				if ($facilitytype_cell -> getValue() == "Central Site") {
					$central = 1;
					$standalone = 0;
					$store = 0;
				}
				if ($facilitytype_cell -> getValue() == "Standalone Site") {
					$central = 0;
					$standalone = 1;
					$store = 0;
				}
				if ($facilitytype_cell -> getValue() == "Satellite Site") {
					$central = 0;
					$standalone = 0;
					$store = $centralsite_cell -> getValue();
				}
			}
			$validity = Dashboard_Orderpoints::checkValid($pipeline, $month, $year, $mfl_code);
			if (!$validity) {
				$orderpoints_report = new Dashboard_Orderpoints();
				$orderpoints_report -> pipeline = $pipeline;
				$orderpoints_report -> month = $month;
				$orderpoints_report -> year = $year;
				$orderpoints_report -> mfl_code = $mfl_code;
				$orderpoints_report -> facility_name = $facility_name;
				$orderpoints_report -> district = $district;
				$orderpoints_report -> province = $province;
				$orderpoints_report -> central = $central;
				$orderpoints_report -> standalone = $standalone;
				$orderpoints_report -> store = $store;
				$orderpoints_report -> save();
			}
		}
	}

	public function workbook_kp_sp($worksheet, $pipeline, $year, $month) {
		$highestRow = $worksheet -> getHighestRow();
		$highestColumn = $worksheet -> getHighestColumn();
		$highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);
		$arr = $worksheet -> toArray(null, true, true, true);
		for ($row = 6; $row <= $highestRow; ++$row) {
			for ($col = 7; $col < $highestColumnIndex; ++$col) {
				$id_cell = $worksheet -> getCellByColumnAndRow(7, $row);
				$mflcode_cell = $worksheet -> getCellByColumnAndRow(8, $row);
				$facilityname_cell = $worksheet -> getCellByColumnAndRow(9, $row);
				$centralsite_cell = $worksheet -> getCellByColumnAndRow(10, $row);
				$district_cell = $worksheet -> getCellByColumnAndRow(11, $row);
				$province_cell = $worksheet -> getCellByColumnAndRow(12, $row);
				$facilitytype_cell = $worksheet -> getCellByColumnAndRow(13, $row);
				$mfl_code = $mflcode_cell -> getValue();
				$facility_name = $facilityname_cell -> getValue();
				$centralsite_name = $centralsite_cell -> getValue();
				$district = $district_cell -> getValue();
				$province = $province_cell -> getValue();
				$dispensing = 0;
				$standalone = 0;
				$satelite = 0;

				if ($facilitytype_cell -> getValue() == "Standalone Site") {
					$dispensing = 0;
					$standalone = 1;
					$satelite = 0;
				}
				if ($facilitytype_cell -> getValue() == "Satellite Site") {
					$dispensing = 0;
					$standalone = 0;
					$satelite = 1;
				}
				if ($facilitytype_cell -> getValue() == "Dispensing Point") {
					$dispensing = 1;
					$standalone = 0;
					$satelite = 0;
				}
			}
			if ($id_cell -> getValue()) {
				$facility_name = trim(str_replace(array('\'', '"', ',', ';', '<', '>', '.'), ' ', $facility_name));
				$validity = Dashboard_Servicepoints::checkValid($pipeline, $month, $year, $facility_name, $mfl_code);
				if (!$validity) {
					$servicepoints_report = new Dashboard_Servicepoints();
					$servicepoints_report -> pipeline = $pipeline;
					$servicepoints_report -> month = $month;
					$servicepoints_report -> year = $year;
					$servicepoints_report -> mfl_code = $mfl_code;
					$servicepoints_report -> facility_name = $facility_name;
					$servicepoints_report -> centralsite_name = $centralsite_name;
					$servicepoints_report -> district = $district;
					$servicepoints_report -> province = $province;
					$servicepoints_report -> dispensing = $dispensing;
					$servicepoints_report -> standalone = $standalone;
					$servicepoints_report -> satellite = $satelite;
					$servicepoints_report -> save();
				}
			}

		}

	}

	public function workbook_kp_paggregate($worksheet, $pipeline, $year, $month) {
		$highestRow = $worksheet -> getHighestRow();
		$highestColumn = $worksheet -> getHighestColumn();
		$highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);
		$arr = $worksheet -> toArray(null, true, true, true);
		$site_in_art = $arr[7]['C'];
		$site_in_pmtct = $arr[7]['E'];
		$site_in_pep = $arr[7]['G'];
		$total_art_adults = $arr[11]['C'];
		$total_art_children = $arr[11]['G'];
		$total_males_new = $arr[15]['C'];
		$total_males_revisit = $arr[15]['D'];
		$total_females_new = $arr[15]['E'];
		$total_females_revisit = $arr[15]['F'];
		$total_pmtct_new = $arr[18]['D'];
		$total_pmtct_revisit = $arr[18]['G'];
		$total_infants_pmtct = $arr[20]['D'];
		$total_pep_adults = $arr[23]['D'];
		$total_pep_children = $arr[23]['G'];
		$total_oi_adult = $arr[28]['E'];
		$total_oi_children = $arr[28]['G'];
		$total_diflucan_adults = $arr[31]['E'];
		$total_diflucan_children = $arr[31]['E'];
		$cm_new = $arr[35]['C'];
		$cm_revisit = $arr[35]['D'];
		$oc_new = $arr[35]['E'];
		$oc_revisit = $arr[35]['F'];

		$validity = Dashboard_Paggregate::checkValid($pipeline, $month, $year);
		if (!$validity) {
			$paggregate_report = new Dashboard_Paggregate();
			$paggregate_report -> month = $month;
			$paggregate_report -> year = $year;
			$paggregate_report -> sites_in_art = $site_in_art;
			$paggregate_report -> sites_in_pmtct = $site_in_pmtct;
			$paggregate_report -> sites_in_pep = $site_in_pep;
			$paggregate_report -> total_art_adults = $total_art_adults;
			$paggregate_report -> total_art_children = $total_art_children;
			$paggregate_report -> total_males_new = $total_males_new;
			$paggregate_report -> total_males_revisit = $total_males_revisit;
			$paggregate_report -> total_females_new = $total_females_new;
			$paggregate_report -> total_females_revisit = $total_females_revisit;
			$paggregate_report -> total_pmtct_new = $total_pmtct_new;
			$paggregate_report -> total_pmtct_revisit = $total_pmtct_revisit;
			$paggregate_report -> total_infants_pmtct = $total_infants_pmtct;
			$paggregate_report -> total_pep_adults = $total_pep_adults;
			$paggregate_report -> total_pep_children = $total_pep_children;
			$paggregate_report -> total_oi_adult = $total_oi_adult;
			$paggregate_report -> total_oi_children = $total_oi_children;
			$paggregate_report -> total_diflucan_adults = $total_diflucan_adults;
			$paggregate_report -> total_diflucan_children = $total_diflucan_children;
			$paggregate_report -> cm_new = $cm_new;
			$paggregate_report -> cm_revisit = $cm_revisit;
			$paggregate_report -> oc_new = $oc_new;
			$paggregate_report -> oc_revisit = $oc_revisit;
			$paggregate_report -> pipeline = $pipeline;
			$paggregate_report -> save();
		}

	}

	public function workbook_kp_pbyregimen($worksheet, $pipeline, $year, $month) {
		$highestRow = $worksheet -> getHighestRow();
		$highestColumn = $worksheet -> getHighestColumn();
		$highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);
		$arr = $worksheet -> toArray(null, true, true, true);
		$label[0] = $arr[6]['G'];
		$value[0] = $arr[6]['H'];
		$label[1] = $arr[7]['G'];
		$value[1] = $arr[7]['H'];
		$label[2] = $arr[9]['G'];
		$value[2] = $arr[9]['H'];
		$label[3] = $arr[8]['G'];
		$value[3] = $arr[8]['H'];
		$label[4] = $arr[10]['G'];
		$value[4] = $arr[10]['H'];
		$label[5] = $arr[11]['G'];
		$value[5] = $arr[11]['H'];

		$label[6] = $arr[12]['G'];
		$value[6] = $arr[12]['H'];
		$label[7] = $arr[13]['G'];
		$value[7] = $arr[13]['H'];
		$label[8] = $arr[14]['G'];
		$value[8] = $arr[14]['H'];
		$label[9] = $arr[15]['G'];
		$value[9] = $arr[15]['H'];

		for ($i = 0; $i < 10; $i++) {
			$validity = Dashboard_Patientbyline::checkValid($pipeline, $month, $year, $label[$i]);
			if (!$validity && $label[$i] != '') {
				$new_patentbyline = new Dashboard_Patientbyline();
				$new_patentbyline -> pipeline = $pipeline;
				$new_patentbyline -> month = $month;
				$new_patentbyline -> year = $year;
				$new_patentbyline -> category = $label[$i];
				$new_patentbyline -> total = $value[$i];
				$new_patentbyline -> save();
			}
		}

	}

	public function workbook_kp_pscaleup($worksheet, $pipeline, $year, $month) {
		$highestRow = $worksheet -> getHighestRow();
		$highestColumn = $worksheet -> getHighestColumn();
		$highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);
		$arr = $worksheet -> toArray(null, true, true, true);
		$rws1 = 0;
		for ($row = 8; $row <= $highestRow; ++$row) {
			if ($arr[$row]["A"] != "") {
				$facility_cell = $worksheet -> getCellByColumnAndRow(0, $row);
				$period_name = $facility_cell -> getValue();
				for ($col = 0; $col < $highestColumnIndex; ++$col) {

				}

				$month = date('m', strtotime($arr[$row]["A"]));
				$year = "20" . date('d', strtotime($arr[$row]["A"]));
				$adult_art = $arr[$row]["B"];
				$paed_art = $arr[$row]["C"];
				$paed_pep = $arr[$row]["E"];
				$adult_pep = $arr[$row]["F"];
				$mother_pmtct = $arr[$row]["G"];
				$infant_pmtct = $arr[$row]["H"];
				$validity = Patient_Scaleup::checkValid($pipeline, $month, $year);
				if (!$validity) {
					$ps_report = new Patient_Scaleup();
					$ps_report -> pipeline = $pipeline;
					$ps_report -> month = $month;
					$ps_report -> year = $year;
					$ps_report -> adult_art = $adult_art;
					$ps_report -> paed_art = $paed_art;
					$ps_report -> paed_pep = $paed_pep;
					$ps_report -> adult_pep = $adult_pep;
					$ps_report -> mothers_pmtct = $mother_pmtct;
					$ps_report -> infant_pmtct = $infant_pmtct;
					$ps_report -> save();
				}

			} else {
				exit ;

			}
		}

	}

	public function workbook_kp_pcurrent($worksheet, $pipeline, $year, $month) {
		$validity = Patient_Byregimen_Numbers::checkValid($pipeline, $month, $year);
		if (!$validity) {
			$highestRow = $worksheet -> getHighestRow();
			$highestColumn = $worksheet -> getHighestColumn();
			$highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);
			$arr = $worksheet -> toArray(null, true, true, true);
			for ($row = 9; $row <= ($highestRow - 1); ++$row) {
				$facility_id_cell = $worksheet -> getCellByColumnAndRow(0, $row);
				$facility_cell = $worksheet -> getCellByColumnAndRow(1, $row);
				$facility_name = $facility_cell -> getValue();
				$facility_name = trim(str_replace(array('\'', '"', ',', ';', '<', '>', '.'), ' ', $facility_name));
				$comments_cell = $worksheet -> getCellByColumnAndRow(2, $row);
				$comments = $comments_cell -> getValue();
				for ($col = 4; $col < ($highestColumnIndex - 1); ++$col) {
					$cell = $worksheet -> getCellByColumnAndRow($col, $row);
					$regimen_desc_cell = $worksheet -> getCellByColumnAndRow($col, 1);
					$regimen_code_cell = $worksheet -> getCellByColumnAndRow($col, 6);
					$prev_regimen_code_cell = $worksheet -> getCellByColumnAndRow($col, 7);
					$val = $cell -> getValue();
					if ($val == null) {
						$val = 0;
					}
					if ($regimen_desc_cell != '' && $facility_id_cell != '') {
						$pipeline_report = new Patient_Byregimen_Numbers();
						$pipeline_report -> facilityname = $facility_name;
						$pipeline_report -> comments = $comments;
						$pipeline_report -> regimen_desc = $regimen_desc_cell;
						$pipeline_report -> regimen_code = $regimen_code_cell;
						$pipeline_report -> previous_code = $prev_regimen_code_cell;
						$pipeline_report -> month = $month;
						$pipeline_report -> year = $year;
						$pipeline_report -> total = $val;
						$pipeline_report -> pipeline = $pipeline;
						$pipeline_report -> save();
					}
				}
			}

		}

	}

	public function workbook_kp_piconsumption($worksheet, $pipeline, $year, $month) {

		$highestRow = $worksheet -> getHighestRow();
		$highestColumn = $worksheet -> getHighestColumn();
		$highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);
		$arr = $worksheet -> toArray(null, true, true, true);
		for ($row = 10; $row <= $highestRow; ++$row) {
			for ($col = 2; $col < $highestColumnIndex; ++$col) {
				$year = $worksheet -> getCellByColumnAndRow($col, 6);
				if ($year != "") {
					$cell = $worksheet -> getCellByColumnAndRow($col, $row);
					$drugname_cell = $worksheet -> getCellByColumnAndRow(1, $row);
					$month = date('m', strtotime($worksheet -> getCellByColumnAndRow($col, 7)));
					if ($worksheet -> getCellByColumnAndRow($col, 7) == "February") {
						$month = "02";
					}

					$val = $cell -> getValue();
					if ($drugname_cell != null && $drugname_cell != "") {
						if (is_string($val) || $val == null) {
							$val = 0;
						}
						$validity = Pipeline_Consumption::checkValid($pipeline, $month, $year, $drugname_cell);
						if (!$validity) {
							if ($drugname_cell !== "Drugs for OIs") {
								$pc_report = new Pipeline_Consumption();
								$pc_report -> pipeline = $pipeline;
								$pc_report -> month = $month;
								$pc_report -> year = $year;
								$pc_report -> drugname = $drugname_cell;
								$pc_report -> consumption = $val;
								$pc_report -> save();
							}
						}
					}
				}
			}

		}

	}

	public function workbook_kp_faconsumption($worksheet, $pipeline, $year, $month) {
		$highestRow = $worksheet -> getHighestRow();
		$highestColumn = $worksheet -> getHighestColumn();
		$highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);
		$arr = $worksheet -> toArray(null, true, true, true);
		for ($row = 9; $row <= ($highestRow - 1); ++$row) {
			$facility_cell = $worksheet -> getCellByColumnAndRow(1, $row);
			$facility_name = $facility_cell -> getValue();
			for ($col = 3; $col < ($highestColumnIndex); ++$col) {
				$cell = $worksheet -> getCellByColumnAndRow($col, $row);
				$drugname_cell = $worksheet -> getCellByColumnAndRow($col, 7);
				$val = $cell -> getValue();
				if ($drugname_cell != "") {
					if ($val == null) {
						$val = 0;
					}
					$fconsumption_report = new Facility_Consumption();
					$fconsumption_report -> facilityname = $facility_name;
					$fconsumption_report -> drugname = $drugname_cell;
					$fconsumption_report -> month = $month;
					$fconsumption_report -> year = $year;
					$fconsumption_report -> total = $val;
					$fconsumption_report -> pipeline = $pipeline;
					$fconsumption_report -> save();

				}

			}
		}

	}

	public function workbook_kp_faSOH($worksheet, $pipeline, $year, $month) {
		$highestRow = $worksheet -> getHighestRow();
		$highestColumn = $worksheet -> getHighestColumn();
		$highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);
		$arr = $worksheet -> toArray(null, true, true, true);
		for ($row = 9; $row <= ($highestRow - 2); ++$row) {
			$facility_cell = $worksheet -> getCellByColumnAndRow(1, $row);
			$facility_name = $facility_cell -> getValue();
			for ($col = 3; $col < ($highestColumnIndex); ++$col) {
				$cell = $worksheet -> getCellByColumnAndRow($col, $row);
				$drugname_cell = $worksheet -> getCellByColumnAndRow($col, 7);
				$val = $cell -> getValue();
				if ($drugname_cell != "") {
					if ($val == null) {
						$val = 0;
					}
					$fsoh_report = new Facility_Soh();
					$fsoh_report -> facilityname = $facility_name;
					$fsoh_report -> drugname = $drugname_cell;
					$fsoh_report -> month = $month;
					$fsoh_report -> year = $year;
					$fsoh_report -> total = $val;
					$fsoh_report -> pipeline = $pipeline;
					$fsoh_report -> save();

				}

			}
		}

	}

	public function worksheet($uploaded_file, $pipeline, $year, $month, $report_type) {
		$objPHPExcel = PHPExcel_IOFactory::load($uploaded_file);
		if ($report_type == 1) {
			//Check If report is  patient by regimen
			if ($pipeline == 1) {
				$this -> worksheet_kemsa_pregimen($objPHPExcel, $pipeline, $year, $month);
			} else if ($pipeline == 2) {
				$this -> worksheet_kp_pregimen($objPHPExcel, $pipeline, $year, $month);
			}

		} else if ($report_type == 2) {
			//Check If Facility Consumption
			if ($pipeline == 1) {
				$this -> worksheet_kemsa_fconsumption($objPHPExcel, $pipeline, $year, $month);
			} else if ($pipeline == 2) {
				$this -> worksheet_kp_fconsumption($objPHPExcel, $pipeline, $year, $month);
			}

		} else if ($report_type == 3) {
			//Check If Facility Stock on Hand
			if ($pipeline == 1) {
				$this -> worksheet_kemsa_fsoh($objPHPExcel, $pipeline, $year, $month);
			} else if ($pipeline == 2) {
				$this -> worksheet_kp_fsoh($objPHPExcel, $pipeline, $year, $month);
			}
		} else if ($report_type == 4) {
			//Check If Pipeline Commodity Consumption
			if ($pipeline == 1) {
				$this -> worksheet_kemsa_pconsumption($objPHPExcel, $pipeline, $year, $month);
			} else if ($pipeline == 2) {
				$this -> worksheet_kp_pconsumption($objPHPExcel, $pipeline, $year, $month);
			}
		} else if ($report_type == 5) {
			//Check If Patient Scaleup
			if ($pipeline == 1) {
				$this -> worksheet_kemsa_pscaleup($objPHPExcel, $pipeline, $year, $month);
			} else if ($pipeline == 2) {
				$this -> worksheet_kp_pscaleup($objPHPExcel, $pipeline, $year, $month);
			}
		}

	}

	public function worksheet_kemsa_pregimen($objPHPExcel, $pipeline, $year, $month) {
		foreach ($objPHPExcel->getWorksheetIterator() as $worksheet) {
			$highestRow = $worksheet -> getHighestRow();
			$highestColumn = $worksheet -> getHighestColumn();
			$highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);
			$arr = $objPHPExcel -> getActiveSheet() -> toArray(null, true, true, true);
			for ($row = 10; $row <= ($highestRow - 1); ++$row) {
				$facility_cell = $worksheet -> getCellByColumnAndRow(1, $row);
				$facility_name = $facility_cell -> getValue();
				for ($col = 4; $col < ($highestColumnIndex - 1); ++$col) {
					$cell = $worksheet -> getCellByColumnAndRow($col, $row);
					$regimen_desc_cell = $worksheet -> getCellByColumnAndRow($col, 1);
					$regimen_code_cell = $worksheet -> getCellByColumnAndRow($col, 6);
					$prev_regimen_code_cell = $worksheet -> getCellByColumnAndRow($col, 7);
					$val = $cell -> getValue();
					if ($val == null) {
						$val = 0;
					}
					$pipeline_report = new Patient_Byregimen_Numbers();
					$pipeline_report -> facilityname = $facility_name;
					$pipeline_report -> comments = $comments;
					$pipeline_report -> regimen_desc = $regimen_desc_cell;
					$pipeline_report -> regimen_code = $regimen_code_cell;
					$pipeline_report -> previous_code = $prev_regimen_code_cell;
					$pipeline_report -> month = $month;
					$pipeline_report -> year = $year;
					$pipeline_report -> total = $val;
					$pipeline_report -> pipeline = $pipeline;
					$pipeline_report -> save();
				}
			}
		}

	}

	public function worksheet_kp_pregimen($objPHPExcel, $pipeline, $year, $month) {
		foreach ($objPHPExcel->getWorksheetIterator() as $worksheet) {
			$worksheetTitle = $worksheet -> getTitle();
			if ($worksheetTitle == "Current Patients by ART Site") {
				$highestRow = $worksheet -> getHighestRow();
				$highestColumn = $worksheet -> getHighestColumn();
				$highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);
				$arr = $objPHPExcel -> getActiveSheet() -> toArray(null, true, true, true);
				for ($row = 9; $row <= $highestRow; ++$row) {
					if ($row < 176) {
						$facility_cell = $worksheet -> getCellByColumnAndRow(1, $row);
						$facility_name = $facility_cell -> getValue();
						$facility_name = trim(str_replace(array('\'', '"', ',', ';', '<', '>', '.'), ' ', $facility_name));
						for ($col = 4; $col < $highestColumnIndex; ++$col) {
							$cell = $worksheet -> getCellByColumnAndRow($col, $row);
							$regimen_desc_cell = $worksheet -> getCellByColumnAndRow($col, 1);
							$regimen_code_cell = $worksheet -> getCellByColumnAndRow($col, 6);
							$prev_regimen_code_cell = $worksheet -> getCellByColumnAndRow($col, 7);
							$val = $cell -> getValue();
							if ($val == null) {
								$val = 0;
							}
							$pipeline_report = new Patient_Byregimen_Numbers();
							$pipeline_report -> facilityname = $facility_name;
							$pipeline_report -> comments = $comments;
							$pipeline_report -> regimen_desc = $regimen_desc_cell;
							$pipeline_report -> regimen_code = $regimen_code_cell;
							$pipeline_report -> previous_code = $prev_regimen_code_cell;
							$pipeline_report -> month = $month;
							$pipeline_report -> year = $year;
							$pipeline_report -> total = $val;
							$pipeline_report -> pipeline = $pipeline;
							$pipeline_report -> save();

						}
					}

				}
			}
		}
	}

	public function worksheet_kemsa_fconsumption($objPHPExcel, $pipeline, $year, $month) {
		foreach ($objPHPExcel->getWorksheetIterator() as $worksheet) {
			$highestRow = $worksheet -> getHighestRow();
			$highestColumn = $worksheet -> getHighestColumn();
			$highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);
			$arr = $objPHPExcel -> getActiveSheet() -> toArray(null, true, true, true);
			for ($row = 3; $row <= ($highestRow - 1); ++$row) {
				$facility_cell = $worksheet -> getCellByColumnAndRow(1, $row);
				$facility_name = $facility_cell -> getValue();
				for ($col = 2; $col < ($highestColumnIndex); ++$col) {
					$cell = $worksheet -> getCellByColumnAndRow($col, $row);
					$drugname_cell = $worksheet -> getCellByColumnAndRow($col, 1);
					$val = $cell -> getValue();
					if ($facility_name != "") {
						if ($val == null) {
							$val = 0;
						}
						$fconsumption_report = new Facility_Consumption();
						$fconsumption_report -> facilityname = $facility_name;
						$fconsumption_report -> drugname = $drugname_cell;
						$fconsumption_report -> month = $month;
						$fconsumption_report -> year = $year;
						$fconsumption_report -> total = $val;
						$fconsumption_report -> pipeline = $pipeline;
						$fconsumption_report -> save();
					}

				}
			}
		}
	}

	public function worksheet_kp_fconsumption($objPHPExcel, $pipeline, $year, $month) {
		foreach ($objPHPExcel->getWorksheetIterator() as $worksheet) {
			$highestRow = $worksheet -> getHighestRow();
			$highestColumn = $worksheet -> getHighestColumn();
			$highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);
			$arr = $objPHPExcel -> getActiveSheet() -> toArray(null, true, true, true);
			for ($row = 9; $row <= ($highestRow - 1); ++$row) {
				$facility_cell = $worksheet -> getCellByColumnAndRow(1, $row);
				$facility_name = $facility_cell -> getValue();
				for ($col = 3; $col < ($highestColumnIndex); ++$col) {
					$cell = $worksheet -> getCellByColumnAndRow($col, $row);
					$drugname_cell = $worksheet -> getCellByColumnAndRow($col, 7);
					$val = $cell -> getValue();
					if ($drugname_cell != "") {
						if ($val == null) {
							$val = 0;
						}
						$fconsumption_report = new Facility_Consumption();
						$fconsumption_report -> facilityname = $facility_name;
						$fconsumption_report -> drugname = $drugname_cell;
						$fconsumption_report -> month = $month;
						$fconsumption_report -> year = $year;
						$fconsumption_report -> total = $val;
						$fconsumption_report -> pipeline = $pipeline;
						$fconsumption_report -> save();

					}

				}
			}
		}

	}

	public function worksheet_kemsa_fsoh($objPHPExcel, $pipeline, $year, $month) {
		foreach ($objPHPExcel->getWorksheetIterator() as $worksheet) {
			$highestRow = $worksheet -> getHighestRow();
			$highestColumn = $worksheet -> getHighestColumn();
			$highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);
			$arr = $objPHPExcel -> getActiveSheet() -> toArray(null, true, true, true);
			for ($row = 9; $row <= ($highestRow - 1); ++$row) {
				$facility_cell = $worksheet -> getCellByColumnAndRow(1, $row);
				$facility_name = $facility_cell -> getValue();
				for ($col = 2; $col < ($highestColumnIndex); ++$col) {
					$cell = $worksheet -> getCellByColumnAndRow($col, $row);
					$drugname_cell = $worksheet -> getCellByColumnAndRow($col, 7);
					$val = $cell -> getValue();
					if ($facility_name != "") {
						if ($val == null) {
							$val = 0;
						}
						$fsoh_report = new Facility_Soh();
						$fsoh_report -> facilityname = $facility_name;
						$fsoh_report -> drugname = $drugname_cell;
						$fsoh_report -> month = $month;
						$fsoh_report -> year = $year;
						$fsoh_report -> total = $val;
						$fsoh_report -> pipeline = $pipeline;
						$fsoh_report -> save();

					}

				}
			}
		}

	}

	public function worksheet_kp_fsoh($objPHPExcel, $pipeline, $year, $month) {
		foreach ($objPHPExcel->getWorksheetIterator() as $worksheet) {
			$highestRow = $worksheet -> getHighestRow();
			$highestColumn = $worksheet -> getHighestColumn();
			$highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);
			$arr = $objPHPExcel -> getActiveSheet() -> toArray(null, true, true, true);
			for ($row = 9; $row <= ($highestRow - 2); ++$row) {
				$facility_cell = $worksheet -> getCellByColumnAndRow(1, $row);
				$facility_name = $facility_cell -> getValue();
				for ($col = 3; $col < ($highestColumnIndex); ++$col) {
					$cell = $worksheet -> getCellByColumnAndRow($col, $row);
					$drugname_cell = $worksheet -> getCellByColumnAndRow($col, 7);
					$val = $cell -> getValue();
					if ($drugname_cell != "") {
						if ($val == null) {
							$val = 0;
						}
						$fsoh_report = new Facility_Soh();
						$fsoh_report -> facilityname = $facility_name;
						$fsoh_report -> drugname = $drugname_cell;
						$fsoh_report -> month = $month;
						$fsoh_report -> year = $year;
						$fsoh_report -> total = $val;
						$fsoh_report -> pipeline = $pipeline;
						$fsoh_report -> save();

					}

				}
			}
		}
	}

	public function worksheet_kemsa_pconsumption($objPHPExcel, $pipeline, $year, $month) {

	}

	public function worksheet_kp_pconsumption($objPHPExcel, $pipeline, $year, $month) {
		foreach ($objPHPExcel->getWorksheetIterator() as $worksheet) {
			$highestRow = $worksheet -> getHighestRow();
			$highestColumn = $worksheet -> getHighestColumn();
			$highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);
			$arr = $objPHPExcel -> getActiveSheet() -> toArray(null, true, true, true);
			for ($row = 10; $row <= $highestRow; ++$row) {
				for ($col = 1; $col < $highestColumnIndex; ++$col) {
					$cell = $worksheet -> getCellByColumnAndRow($col, $row);
					$drugname_cell = $worksheet -> getCellByColumnAndRow(0, $row);
					$month = date('m', strtotime($worksheet -> getCellByColumnAndRow($col, 7)));
					$year = $worksheet -> getCellByColumnAndRow($col, 6);
					$val = $cell -> getValue();
					if ($drugname_cell != null) {
						if (is_string($val) || $val == null) {
							$val = 0;
						}
						$validity = Pipeline_Consumption::checkValid($pipeline, $month, $year, $drugname_cell);
						if (!$validity) {
							$pc_report = new Pipeline_Consumption();
							$pc_report -> pipeline = $pipeline;
							$pc_report -> month = $month;
							$pc_report -> year = $year;
							$pc_report -> drugname = $drugname_cell;
							$pc_report -> consumption = $val;
							$pc_report -> save();
						}
					}

				}
			}
		}
	}

	public function worksheet_kemsa_pscaleup($objPHPExcel, $pipeline, $year, $month) {

	}

	public function worksheet_kp_pscaleup($objPHPExcel, $pipeline, $year, $month) {
		foreach ($objPHPExcel->getWorksheetIterator() as $worksheet) {
			$highestRow = $worksheet -> getHighestRow();
			$highestColumn = $worksheet -> getHighestColumn();
			$highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);
			$arr = $objPHPExcel -> getActiveSheet() -> toArray(null, true, true, true);
			for ($row = 8; $row <= $highestRow; ++$row) {
				$facility_cell = $worksheet -> getCellByColumnAndRow(0, $row);
				$period_name = $facility_cell -> getValue();
				for ($col = 0; $col < $highestColumnIndex; ++$col) {

				}

				$month = date('m', strtotime($arr[$row]["A"]));
				$year = "20" . date('d', strtotime($arr[$row]["A"]));
				$adult_art = $arr[$row]["B"];
				$paed_art = $arr[$row]["C"];
				$paed_pep = $arr[$row]["E"];
				$adult_pep = $arr[$row]["F"];
				$mother_pmtct = $arr[$row]["G"];
				$infant_pmtct = $arr[$row]["H"];
				$validity = Patient_Scaleup::checkValid($pipeline, $month, $year);
				if (!$validity) {
					$ps_report = new Patient_Scaleup();
					$ps_report -> pipeline = $pipeline;
					$ps_report -> month = $month;
					$ps_report -> year = $year;
					$ps_report -> adult_art = $adult_art;
					$ps_report -> paed_art = $paed_art;
					$ps_report -> paed_pep = $paed_pep;
					$ps_report -> adult_pep = $adult_pep;
					$ps_report -> mothers_pmtct = $mother_pmtct;
					$ps_report -> infant_pmtct = $infant_pmtct;
					$ps_report -> save();

				}

			}
		}
	}

	public function base_params($data) {
		$data['title'] = "Pipleline Stock Data";

		$data['banner_text'] = "Monthly Pipeline Upload";
		$data['quick_link'] = "pipeline";
		//$this -> load -> view('template', $data);
	}

}
?>