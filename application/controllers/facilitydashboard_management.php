<?php
if (!defined('BASEPATH'))
	exit('No direct script access allowed');

class Facilitydashboard_Management extends MY_Controller {

	var $drug_array = array();
	var $drug_count = 0;
	var $counter = 0;

	function __construct() {
		parent::__construct();
		$this -> load -> database();
	}

	public function index() {

	}

	public function order_notification() {
		$facility_code = $this -> session -> userdata("facility");
		$sql = "SELECT status ,COUNT(*) AS total FROM `facility_order` WHERE facility_id ='$facility_code' GROUP BY STATUS";
		$query = $this -> db -> query($sql);
		$results = $query -> result_array();
		$status = "";
		$status_total = 0;
		$total = 0;
		$order_link = "";
		$dyn_table = "";
		if ($results) {
			foreach ($results as $result) {
				if ($result['status'] != '') {
					if ($result['status'] == 0) {
						$status = "Pending Orders";
						$order_link = site_url('order_management/submitted_orders/0');
					} else if ($result['status'] == 1) {
						$status = "Approved Orders";
						$order_link = site_url('order_management/submitted_orders/1');
					} else if ($result['status'] == 2) {
						$status = "Declined Orders";
						$order_link = site_url('order_management/submitted_orders/2');
					} else if ($result['status'] == 3) {
						$status = "Dispatched Orders";
						$order_link = site_url('order_management/submitted_orders/3');
					}
					$status_total = $result['total'];
					$total += $status_total;
					$dyn_table .= "<li><a id='inactive_users' href='$order_link'><i class='icon-th'></i>$status <div class='badge badge-important'>$status_total</div></a></li>";
				}
			}
		} else {
			$dyn_table .= "<li>No Data Available</li>";
		}
		$access_level = $this -> session -> userdata('user_indicator');
		if ($access_level == "facility_administrator") {
			$dyn_table .= $this -> inactive_users();
		}

		echo $dyn_table;
	}

	public function inactive_users() {
		$facility_code = $this -> session -> userdata("facility");
		$sql = "select count(*) as total from users where Facility_Code='$facility_code' and Active='0' and access_level='2'";
		$query = $this -> db -> query($sql);
		$results = $query -> result_array();
		$total = 0;
		$temp = "";
		$order_link = site_url('settings_management');
		if ($results) {
			foreach ($results as $result) {
				$total = $result['total'];
			}
		}
		$temp = "<li class='divider'></li><li><a href='$order_link'><i class='icon-th'></i>Deactivated Users <div class='badge badge-important'>$total</div></a></li>";
		return $temp;
	}

	public function stock_notification($stock_type = "2") {
		$drugs_array = array();
		$counter = 0;

		//Get the facility_code
		$facility_code = $this -> session -> userdata("facility");
		$strDATA = "";
		$strcat = "";
		$strLEVEL = "";
		$strXML = "<chart caption='Drugs Below Safety Stock' useroundedges='1' >";
		//Store
		if ($stock_type == '1') {
			$stock_param = " AND (source='" . $facility_code . "' OR destination='" . $facility_code . "') AND source!=destination ";
		}
		//Pharmacy
		else if ($stock_type == '2') {
			$stock_param = " AND (source=destination) AND(source='" . $facility_code . "') ";
		}

		//Get all drugs that are active
		$drugs_query = "select d.id as id,drug, pack_size, u.name from drugcode d left join drug_unit u on d.unit = u.id  where d.Enabled=1  ";
		$drugs = $this -> db -> query($drugs_query);
		$drugs_results = $drugs -> result_array();
		foreach ($drugs_results as $drugs_result) {
			//Get Drug
			$drug = $drugs_result['id'];
			$drug_name = $drugs_result['drug'];
			$drug_unit = $drugs_result['name'];
			$drug_packsize = $drugs_result['pack_size'];
			$stock_level = 0;
			$today = date("Y-m-d");

			//Get all batches not expired
			$allbatches_query = "SELECT SUM(balance) as total FROM drug_stock_balance d WHERE d.drug_id =  '$drug' AND d.expiry_date > '$today' AND facility_code='$facility_code' AND stock_type='$stock_type'";
			$batches = $this -> db -> query($allbatches_query);
			$batches_results = $batches -> result_array();
			//Get stock balance for a drug

			foreach ($batches_results as $stock_balance) {
				$stock_level = $stock_balance['total'];
			}

			//Get consumption for the past three months
			$safetystock_query = "SELECT SUM(d.quantity_out) AS TOTAL FROM drug_stock_movement d WHERE d.drug ='$drug' AND DATEDIFF(CURDATE(),d.transaction_date)<= 90 and facility='$facility_code' $stock_param";
			$safetystocks = $this -> db -> query($safetystock_query);
			$safetystocks_results = $safetystocks -> result_array();
			$three_monthly_consumption = 0;
			foreach ($safetystocks_results as $safetystocks_result) {
				$three_monthly_consumption = $safetystocks_result['TOTAL'];
				//Calculating Monthly Consumption hence Max-Min Inventory
				$monthly_consumption = ($three_monthly_consumption) / 3;
				$monthly_consumption = number_format($monthly_consumption, 2);

				//Therefore Maximum Consumption
				$maximum_consumption = $monthly_consumption * 3;
				$maximum_consumption = number_format($maximum_consumption, 2);

				//Therefore Minimum Consumption
				$minimum_consumption = $monthly_consumption * 1.5;
				//$minimum_consumption = number_format($monthly_consumption, 2);

				//If current stock balance is less than minimum consumption
				if ($stock_level < $minimum_consumption) {

					if ($minimum_consumption < 0) {
						$minimum_consumption = 0;
					}
					if ($stock_level < 0 or $stock_level == "NULL") {
						$stock_level = 0;
					}
					$drugs_array[$counter]['drug_name'] = $drug_name;
					$drugs_array[$counter]['drug_unit'] = $drug_unit;
					$drugs_array[$counter]['stock_level'] = number_format($stock_level);
					$drugs_array[$counter]['minimum_consumption'] = ceil($minimum_consumption);

					$strDATA .= "<set label='$drug_name' value='$stock_level' />";
					$strLEVEL .= "<set label='$drug_name' value='$minimum_consumption' />";
					$strcat .= "<category label='$drug_name'/>";
				}
			}
			$counter++;

		}

		//Create table to store data
		$tmpl = array('table_open' => '<table id="stock_level" class="table table-striped table-condensed">');
		$this -> table -> set_template($tmpl);
		$this -> table -> set_heading('No', 'Drug', 'Unit', 'Qty (Units)', 'Threshold Qty (Units)', 'Priority');
		$data = "";
		$x = 1;
		$priority = "";
		foreach ($drugs_array as $drugs) {
			if ($drugs['minimum_consumption'] == 0 and $drugs['stock_level'] == 0) {
				$priority = 100;
			} else {
				$priority = ($drugs['stock_level'] / $drugs['minimum_consumption']) * 100;
			}
			//Check for priority
			if ($priority >= 50) {
				$priority_level = "<span class='low_priority'>LOW</span>";
			} else {
				$priority_level = "<span class='high_priority'>HIGH</span>";
			}

			$this -> table -> add_row($x, $drugs['drug_name'], $drugs['drug_unit'], $drugs['stock_level'], $drugs['minimum_consumption'], $priority_level);
			$x++;
		}
		$drug_display = $this -> table -> generate();
		echo $drug_display;

	}

	public function showChart() {
		$this -> load -> view("drug_below_safety_v");
	}

	public function getExpiringDrugs($period = 30, $stock_type = 1) {
		$expiryArray = array();
		$stockArray = array();
		$resultArraySize = 0;
		$count = 0;
		$facility_code = $this -> session -> userdata('facility');
		//$drugs_sql = "SELECT s.id AS id,s.drug AS Drug_Id,d.drug AS Drug_Name,d.pack_size AS pack_size, u.name AS Unit, s.batch_number AS Batch,s.expiry_date AS Date_Expired,DATEDIFF(s.expiry_date,CURDATE()) AS Days_Since_Expiry FROM drugcode d LEFT JOIN drug_unit u ON d.unit = u.id LEFT JOIN drug_stock_movement s ON d.id = s.drug LEFT JOIN transaction_type t ON t.id=s.transaction_type WHERE t.effect=1 AND DATEDIFF(s.expiry_date,CURDATE()) <='$period' AND DATEDIFF(s.expiry_date,CURDATE())>=0 AND d.enabled=1 AND s.facility ='" . $facility_code . "' GROUP BY Batch ORDER BY Days_Since_Expiry asc";
		$drugs_sql="SELECT d.drug as drug_name,d.pack_size,u.name as drug_unit,dsb.batch_number as batch,dsb.balance as stocks_display,dsb.expiry_date,DATEDIFF(dsb.expiry_date,CURDATE()) as expired_days_display FROM drugcode d LEFT JOIN drug_unit u ON d.unit=u.id LEFT JOIN drug_stock_balance dsb ON d.id=dsb.drug_id WHERE DATEDIFF(dsb.expiry_date,CURDATE()) <='$period' AND DATEDIFF(dsb.expiry_date,CURDATE())>=0 AND d.enabled=1 AND dsb.facility_code ='" . $facility_code . "' AND dsb.stock_type='".$stock_type."' AND dsb.balance>0 ORDER BY expired_days_display asc";
		$drugs = $this -> db -> query($drugs_sql);
		$results = $drugs -> result_array();
		//Get all expiring drugs
		//foreach ($results as $result => $value) {
			//$count = 1;
			//$this -> getBatchInfo($value['Drug_Id'], $value['Batch'], $value['Unit'], $value['Drug_Name'], $value['Date_Expired'], $value['Days_Since_Expiry'], $value['id'], $value['pack_size'], $stock_type, $facility_code);
		//}
		
		$d = 0;
		$drugs_array =$results;

		$nameArray = array();
		$dataArray = array();
		foreach ($drugs_array as $drug) {
			$nameArray[] = $drug['drug_name'] . '(' . $drug['batch'] . ')';
			$expiryArray[] = (int)$drug['expired_days_display'];
			$stockArray[] = (int)$drug['stocks_display'];
			$resultArraySize++;
		}
		$resultArray = array( array('name' => 'Expiry', 'data' => $expiryArray), array('name' => 'Stock', 'data' => $stockArray));
		$resultArray = json_encode($resultArray);
		$categories = $nameArray;
		$categories = json_encode($categories);
		//Load Data Variables
		$data['resultArraySize'] = $resultArraySize;
		$data['container'] = 'chart_expiry';
		$data['chartType'] = 'bar';
		$data['title'] = 'Chart';
		$data['chartTitle'] = 'Expired Drugs';
		$data['categories'] = $categories;
		$data['yAxix'] = 'Drugs';
		$data['resultArray'] = $resultArray;
		$this -> load -> view('chart_v', $data);

	}

	public function getBatchInfo($drug, $batch, $drug_unit, $drug_name, $expiry_date, $expired_days, $drug_id, $pack_size, $stock_type, $facility_code) {
		$stock_status = 0;
		$stock_param = "";

		//Store
		if ($stock_type == '1') {
			$stock_param = " AND (source='" . $facility_code . "' OR destination='" . $facility_code . "') AND source!=destination ";
		}
		//Pharmacy
		else if ($stock_type == '2') {
			$stock_param = " AND (source=destination) AND(source='" . $facility_code . "') ";
		}
		$initial_stock_sql = "SELECT SUM( d.quantity ) AS Initial_stock, d.transaction_date AS transaction_date, '" . $batch . "' AS batch FROM drug_stock_movement d WHERE d.drug =  '" . $drug . "' AND facility='" . $facility_code . "' " . $stock_param . " AND transaction_type =  '11' AND d.batch_number =  '" . $batch . "'";
		$batches = $this -> db -> query($initial_stock_sql);
		$batch_results = $batches -> result_array();
		foreach ($batch_results as $batch_result => $value) {
			$initial_stock = $value['Initial_stock'];
			//Check if initial stock is present meaning physical count done
			if ($initial_stock != null) {
				$batch_stock_sql = "SELECT (SUM( ds.quantity ) - SUM( ds.quantity_out )) AS stock_levels, ds.batch_number FROM drug_stock_movement ds WHERE ds.transaction_date BETWEEN  '" . $value['transaction_date'] . "' AND curdate() AND facility='" . $facility_code . "' " . $stock_param . " AND ds.drug ='" . $drug . "'  AND ds.batch_number ='" . $value['batch'] . "'";
				$second_row = $this -> db -> query($batch_stock_sql);
				$second_rows = $second_row -> result_array();

				foreach ($second_rows as $second_row => $value) {
					if ($value['stock_levels'] > 0) {
						$batch_balance = $value['stock_levels'];
						$ed = substr($expired_days, 0, 1);
						if ($ed == "-") {
							$expired_days = $expired_days;
						}

						$batch_stock = $batch_balance / $pack_size;
						$expired_days_display = number_format($expired_days);
						$stocks_display = ceil(number_format($batch_stock, 1));

						$this -> drug_array[$this -> counter]['drug_name'] = $drug_name;
						$this -> drug_array[$this -> counter]['batch'] = $batch;
						$this -> drug_array[$this -> counter]['stocks_display'] = $stocks_display;
						$this -> drug_array[$this -> counter]['expired_days_display'] = $expired_days_display;
						$this -> counter++;
					}
				}

			} else {

				$batch_stock_sql = "SELECT (SUM( ds.quantity ) - SUM( ds.quantity_out ) ) AS stock_levels, ds.batch_number FROM drug_stock_movement ds WHERE ds.drug =  '" . $drug . "' AND facility='" . $facility_code . "' " . $stock_param . " AND ds.expiry_date > curdate() AND ds.batch_number='" . $value['batch'] . "'";
				$second_row = $this -> db -> query($batch_stock_sql);
				$second_rows = $second_row -> result_array();

				foreach ($second_rows as $second_row => $value) {

					if ($value['stock_levels'] > 0) {
						$batch_balance = $value['stock_levels'];
						$ed = substr($expired_days, 0, 1);
						if ($ed == "-") {

							$expired_days = $expired_days;
						}
						@$batch_stock = $batch_balance / @$pack_size;
						$expired_days_display = number_format($expired_days);

						$stocks_display = number_format($batch_stock, 1);

						$this -> drug_array[$this -> counter]['drug_name'] = $drug_name;
						$this -> drug_array[$this -> counter]['batch'] = $batch;
						$this -> drug_array[$this -> counter]['stocks_display'] = $stocks_display;
						$this -> drug_array[$this -> counter]['expired_days_display'] = $expired_days_display;
						$this -> counter++;
					}
				}
			}

		}
	}

	//Get patients enrolled
	public function getPatientEnrolled($startdate = "", $enddate = "") {
		$maleAdult= array();
			$femaleAdult= array();
			$maleChild = array();
			$femaleChild=array();
		$facility_code = $this -> session -> userdata('facility');
		$timestamp = time();
		$edate = date('Y-m-d', $timestamp);
		$dates = array();
		$x = 7;
		$y = 0;
		$resultArraySize = 0;

		//If no parameters are passed, get enrolled patients for the past 7 days
		if ($startdate == "" || $enddate == "") {
			for ($i = 0; $i < $x; $i++) {
				if (date("D", $timestamp) != "Sun") {
					$sdate = date('Y-m-d', $timestamp);
					//Store the days in an array
					$dates[$y] = $sdate;
					$y++;
				}
				//If sunday is included, add one more day
				else {$x = 8;
				}
				$timestamp -= 24 * 3600;
			}
			$start_date = $sdate;
			$end_date = $edate;
		} else {
			$start_date = $startdate;
			$end_date = $enddate;
		}
		$get_patient_sql = "SELECT p.gender, dob , date_enrolled FROM patient p WHERE p.date_enrolled
							BETWEEN  '" . $start_date . "' AND  '" . $end_date . "' AND p.facility_code='" . $facility_code . "' ORDER BY p.date_enrolled  ";
		$res = $this -> db -> query($get_patient_sql);
		$x = 0;
		$y = 0;
		$count_patient_date = 0;
		$date_enrolled = "";
		$counter = 0;
		$total_male_adult = 0;
		$total_female_adult = 0;
		$total_male_child = 0;
		$total_female_child = 0;
		$patients_array = array();

		$results = $res -> result_array();

		//Loop through the array to get totals for each category
		foreach ($results as $key => $value) {
			$count_patient_date++;
			if ($x == 0) {
				$x = 1;
				$date_enrolled = $value['date_enrolled'];
			}
			//If enrollement date changes
			if ($value['date_enrolled'] != $date_enrolled) {
				$count_patient_date = 1;
				$y = 0;
				$total_male_adult = 0;
				$total_female_adult = 0;
				$total_male_child = 0;
				$total_female_child = 0;
				$counter++;
				$patients_array[$counter]['date_enrolled'] = $value['date_enrolled'];
				$patients_array[$counter]['total_day'] = $count_patient_date;
				$date_enrolled = $value['date_enrolled'];

			} else if ($value['date_enrolled'] == $date_enrolled) {

				if ($y != 1) {
					//Initialise totals
					$patients_array[$counter]['date_enrolled'] = $value['date_enrolled'];
					$patients_array[$counter]['total_male_adult'] = 0;
					$patients_array[$counter]['total_female_adult'] = 0;
					$patients_array[$counter]['total_male_child'] = 0;
					$patients_array[$counter]['total_female_child'] = 0;
				}
				$patients_array[$counter]['total_day'] = $count_patient_date;
				$y = 1;

			}

			$birthDate = $value['dob'];
			//get age from date or birthdate
			$age = $this -> age_from_dob($birthDate);
			//If patient is male, check if he is an adult or child
			if ($value['gender'] == 1) {
				//Check if adult
				if ($age >= 15) {
					$total_male_adult++;
					$patients_array[$counter]['total_male_adult'] = $total_male_adult;
					$patients_array[$counter]['total_male_child'] = $total_male_child;
					$patients_array[$counter]['total_female_adult'] = $total_female_adult;
					$patients_array[$counter]['total_female_child'] = $total_female_child;

				} else {
					$total_male_child++;
					$patients_array[$counter]['total_male_adult'] = $total_male_adult;
					$patients_array[$counter]['total_male_child'] = $total_male_child;
					$patients_array[$counter]['total_female_adult'] = $total_female_adult;
					$patients_array[$counter]['total_female_child'] = $total_female_child;
				}
			}
			//If patient is female, check if he is an adult or child
			else if ($value['gender'] == 2) {
				//Check if adult
				if ($age >= 15) {
					$total_female_adult++;
					$patients_array[$counter]['total_male_adult'] = $total_male_adult;
					$patients_array[$counter]['total_male_child'] = $total_male_child;
					$patients_array[$counter]['total_female_adult'] = $total_female_adult;
					$patients_array[$counter]['total_female_child'] = $total_female_child;
				} else {
					$total_female_child++;
					$patients_array[$counter]['total_male_adult'] = $total_male_adult;
					$patients_array[$counter]['total_male_child'] = $total_male_child;
					$patients_array[$counter]['total_female_adult'] = $total_female_adult;
					$patients_array[$counter]['total_female_child'] = $total_female_child;
				}
			}

		}
		$resultArraySize = 6;
		$categories = array('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday');
		foreach ($patients_array as $key => $value) {
			$maleAdult[] = (int)$value['total_male_adult'];
			$femaleAdult[] = (int)$value['total_female_adult'];
			$maleChild[] = (int)$value['total_male_child'];
			$femaleChild[] = (int)$value['total_female_child'];
		}
		$resultArray = array( array('name' => 'Male Adult', 'data' => $maleAdult), array('name' => 'Female Adult', 'data' => $femaleAdult), array('name' => 'Male Child', 'data' => $maleChild), array('name' => 'Female Child', 'data' => $femaleChild));
		$resultArray = json_encode($resultArray);
		$categories = json_encode($categories);

		$data['resultArraySize'] = $resultArraySize;
		$data['container'] = "chart_enrollment";
		$data['chartType'] = 'bar';
		$data['chartTitle'] = 'Patients Enrollment';
		$data['yAxix'] = 'Patients';
		$data['categories'] = $categories;
		$data['resultArray'] = $resultArray;
		$this -> load -> view('chart_stacked_v', $data);

	}

	//Get patients expected for appointment

	public function getExpectedPatients($startdate = "", $enddate = "") {

		$facility_code = $this -> session -> userdata('facility');
		$timestamp = time();
		$edate = date('Y-m-d', $timestamp);
		$dates = array();
		$x = 7;
		$y = 0;
		$missed=array();
		$visited=array();

		//If no parameters are passed, get enrolled patients for the past 7 days
		if ($startdate == "" || $enddate == "") {
			for ($i = 0; $i < $x; $i++) {
				if (date("D", $timestamp) != "Sun") {
					$sdate = date('Y-m-d', $timestamp);
					//Store the days in an array
					$dates[$y] = $sdate;
					$y++;
				}
				//If sunday is included, add one more day
				else {$x = 8;
				}
				$timestamp -= 24 * 3600;
			}
			$start_date = $sdate;
			$end_date = $edate;
		} else {
			$start_date = $startdate;
			$end_date = $enddate;
		}
		//Get patients who are expected
		$patients_expected_sql = "select distinct pa.patient,pa.appointment,UPPER(p.first_name) as first_name from patient_appointment pa, patient p where pa.appointment between '" . $start_date . "' and '" . $end_date . "'  and pa.patient = p.patient_number_ccc and p.facility_code='" . $facility_code . "' AND pa.facility=p.facility_code GROUP BY pa.patient,pa.appointment ORDER BY pa.appointment";
		$res = $this -> db -> query($patients_expected_sql);
		$results = $res -> result_array();
		$resultArraySize = 0;
		$counter = 0;
		$x = 0;
		$y = 0;
		$v = 0;
		$n = 0;
		$count_patient_date = 0;
		$date_appointment = "";
		$patients_array[$counter]['total_patient'] = count($results);
		//Array to store dates and count of patients
		$patients_array = array();
		foreach ($results as $key => $value) {
			$count_patient_date++;
			if ($x == 0) {
				$x = 1;
				$date_appointment = $value['appointment'];
			}
			//If appointment date changes
			if ($value['appointment'] != $date_appointment) {
				//Initialise patients visited and not visited count
				//echo $count_patient_date;
				$count_patient_date = 1;
				$v = 0;
				$n = 0;
				$y = 0;
				$counter++;
				$patients_array[$counter]['date_appointment'] = $value['appointment'];
				$patients_array[$counter]['total_day'] = $count_patient_date;
				$date_appointment = $value['appointment'];
			} else if ($value['appointment'] == $date_appointment) {

				if ($y != 1) {
					//Initialise patients visited and not visited count
					$patients_array[$counter]['date_appointment'] = $value['appointment'];
					$patients_array[$counter]['patient_visited'] = 0;
					$patients_array[$counter]['patient_not_visited'] = 0;
				}
				$patients_array[$counter]['total_day'] = $count_patient_date;
				$y = 1;

			}
			//Check if patient came for appointment
			$visited_patients_sql = "select patient_id from patient_visit pv left join patient p on p.patient_number_ccc=pv.patient_id where pv.dispensing_date='" . $value['appointment'] . "' and pv.patient_id='" . $value['patient'] . "' and pv.facility='" . $facility_code . "' and pv.facility=p.facility_code ";
			$res = $this -> db -> query($visited_patients_sql);
			$results = $res -> result_array();
			if (count($results) != 0) {
				$v++;
				$patients_array[$counter]['patient_visited'] = $v;
				$patients_array[$counter]['patient_not_visited'] = $n;
			} else {
				$n++;
				$patients_array[$counter]['patient_not_visited'] = $n;
				$patients_array[$counter]['patient_visited'] = $v;
			}

		}

		$categories = array('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday');
		foreach ($patients_array as $key => $value) {
			$visited[] = (int)$value['patient_visited'];
			$missed[] = (int)$value['patient_not_visited'];
			$resultArraySize++;
		}
		$resultArray = array( array('name' => 'Visited', 'data' => $visited), array('name' => 'Missed', 'data' => $missed));
		$resultArray = json_encode($resultArray);
		$categories = json_encode($categories);
		$data['resultArraySize'] = $resultArraySize;
		$data['container'] = "chart_appointments";
		$data['chartType'] = 'bar';
		$data['chartTitle'] = 'Patients Expected';
		$data['yAxix'] = 'Patients';
		$data['categories'] = $categories;
		$data['resultArray'] = $resultArray;
		$this -> load -> view('chart_v', $data);

	}

	function age_from_dob($dob) {
		list($y, $m, $d) = explode('-', $dob);
		if (($m = (date('m') - $m)) < 0) {
			$y++;
		} elseif ($m == 0 && date('d') - $d < 0) {
			$y++;
		}
		return date('Y') - $y;

	}

	public function base_params($data) {
		$data['content_view'] = "settings_v";
		$data['quick_link'] = "client_sources";
		$this -> load -> view("template", $data);
	}

	/**
	 * Highchart Test Charts
	 */
	//Get patients enrolled
	public function getPatHC($startdate = "", $enddate = "") {
		$facility_code = $this -> session -> userdata('facility');
		$timestamp = time();
		$edate = date('Y-m-d', $timestamp);
		$dates = array();
		$x = 7;
		$y = 0;

		//If no parameters are passed, get enrolled patients for the past 7 days
		if ($startdate == "" || $enddate == "") {
			for ($i = 0; $i < $x; $i++) {
				if (date("D", $timestamp) != "Sun") {
					$sdate = date('Y-m-d', $timestamp);
					//Store the days in an array
					$dates[$y] = $sdate;
					$y++;
				}
				//If sunday is included, add one more day
				else {$x = 8;
				}
				$timestamp -= 24 * 3600;
			}
			$start_date = $sdate;
			$end_date = $edate;
		} else {
			$start_date = $startdate;
			$end_date = $enddate;
		}
		$get_patient_sql = "SELECT p.gender, dob , date_enrolled FROM patient p WHERE p.date_enrolled
							BETWEEN  '" . $start_date . "' AND  '" . $end_date . "' AND p.facility_code='" . $facility_code . "' ORDER BY p.date_enrolled  ";
		$res = $this -> db -> query($get_patient_sql);
		$x = 0;
		$y = 0;
		$count_patient_date = 0;
		$date_enrolled = "";
		$counter = 0;
		$total_male_adult = 0;
		$total_female_adult = 0;
		$total_male_child = 0;
		$total_female_child = 0;
		$patients_array = array();

		$results = $res -> result_array();

		$adult = array();
		$child = array();
		//Split Gender
		foreach ($results as $key => $value) {
			$age = $value['dob'];
			$age = $this -> age_from_dob($age);
			if ($value['gender'] == 1) {
				if ($age > 15) {
					$total_male_adult += 1;
				} elseif ($age < 15) {
					$total_male_child += 1;
				}

			} elseif ($value['gender'] == 2) {
				if ($age > 15) {
					$total_female_adult += 1;
				} elseif ($age < 15) {
					$total_female_child += 1;
				}
			}

		}
		/*
		 //Loop through the array to get totals for each category
		 foreach ($results as $key => $value) {
		 $count_patient_date++;
		 if ($x == 0) {
		 $x = 1;
		 $date_enrolled = $value['date_enrolled'];
		 }
		 //If enrollement date changes
		 if ($value['date_enrolled'] != $date_enrolled) {
		 $count_patient_date = 1;
		 $y = 0;
		 $total_male_adult = 0;
		 $total_female_adult = 0;
		 $total_male_child = 0;
		 $total_female_child = 0;
		 $counter++;
		 $patients_array[$counter]['date_enrolled'] = $value['date_enrolled'];
		 $patients_array[$counter]['total_day'] = $count_patient_date;
		 $date_enrolled = $value['date_enrolled'];

		 } else if ($value['date_enrolled'] == $date_enrolled) {

		 if ($y != 1) {
		 //Initialise totals
		 $patients_array[$counter]['date_enrolled'] = $value['date_enrolled'];
		 $patients_array[$counter]['total_male_adult'] = 0;
		 $patients_array[$counter]['total_female_adult'] = 0;
		 $patients_array[$counter]['total_male_child'] = 0;
		 $patients_array[$counter]['total_female_child'] = 0;
		 }
		 $patients_array[$counter]['total_day'] = $count_patient_date;
		 $y = 1;

		 }

		 $birthDate = $value['dob'];
		 //get age from date or birthdate
		 $age = $this -> age_from_dob($birthDate);
		 //If patient is male, check if he is an adult or child
		 if ($value['gender'] == 1) {
		 //Check if adult
		 if ($age >= 15) {
		 $total_male_adult++;
		 $patients_array[$counter]['total_male_adult'] = $total_male_adult;
		 $patients_array[$counter]['total_male_child'] = $total_male_child;
		 $patients_array[$counter]['total_female_adult'] = $total_female_adult;
		 $patients_array[$counter]['total_female_child'] = $total_female_child;
		 } else {
		 $total_male_child++;
		 $patients_array[$counter]['total_male_adult'] = $total_male_adult;
		 $patients_array[$counter]['total_male_child'] = $total_male_child;
		 $patients_array[$counter]['total_female_adult'] = $total_female_adult;
		 $patients_array[$counter]['total_female_child'] = $total_female_child;
		 }
		 }
		 //If patient is female, check if he is an adult or child
		 else if ($value['gender'] == 2) {
		 //Check if adult
		 if ($age >= 15) {
		 $total_female_adult++;
		 $patients_array[$counter]['total_male_adult'] = $total_male_adult;
		 $patients_array[$counter]['total_male_child'] = $total_male_child;
		 $patients_array[$counter]['total_female_adult'] = $total_female_adult;
		 $patients_array[$counter]['total_female_child'] = $total_female_child;
		 } else {
		 $total_female_child++;
		 $patients_array[$counter]['total_male_adult'] = $total_male_adult;
		 $patients_array[$counter]['total_male_child'] = $total_male_child;
		 $patients_array[$counter]['total_female_adult'] = $total_female_adult;
		 $patients_array[$counter]['total_female_child'] = $total_female_child;
		 }
		 }

		 }

		 $strXML = "<chart useroundedges='1' bgcolor='ffffff' showborder='0' yAxisName='Enrollments' showvalues='1' showsum='1' areaOverColumns='0' showPercentValues='1' baseFont='Arial' baseFontSize='9' palette='2' rotateNames='1' animation='1'  labelDisplay='Rotate' slantLabels='1' exportEnabled='1' exportHandler='" . base_url() . "Scripts/FusionCharts/ExportHandlers/PHP/FCExporter.php' exportAtClient='0' exportAction='download'>";

		 $stradultmale = "<dataset seriesName='Adult Male' showValues= '0' >";
		 $stradultfemale = "<dataset seriesName='Adult Female' showValues= '0' >";
		 $strchildmale = "<dataset seriesName='Child Male' showValues= '0' >";
		 $strchildfemale = "<dataset seriesName='Child Female' showValues= '0' >";
		 $strCAT = "<categories>";
		 foreach ($patients_array as $patients) {

		 $strCAT .= "<category label='" . date('D M d,Y', strtotime($patients['date_enrolled'])) . "'/>";

		 $stradultmale .= "<set value='" . $patients['total_male_adult'] . "' />";
		 $stradultfemale .= "<set value='" . $patients['total_female_adult'] . "' />";
		 $strchildmale .= "<set value='" . $patients['total_male_child'] . "' />";
		 $strchildfemale .= "<set value='" . $patients['total_female_child'] . "' />";
		 }
		 $strCAT .= "</categories>";
		 $stradultmale .= "</dataset>";
		 $stradultfemale .= "</dataset>";
		 $strchildmale .= "</dataset>";
		 $strchildfemale .= "</dataset>";
		 $strXML .= $strCAT . $stradultmale . $stradultfemale . $strchildmale . $strchildfemale;

		 header('Content-type: text/xml');
		 echo $strXML .= "</chart>";*/

	}

}
