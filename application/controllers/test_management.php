<?php
class test_management extends MY_Controller {
	function __construct() {
		parent::__construct();
		ini_set("max_execution_time", "100000");
		$this -> load -> helper('fusioncharts');
		$this -> load -> database();
		
	}

	public function index() {
		$this -> load -> view('test_v');	
	}

	public function enrolled_in_care($selected_period = "", $facility_code = "") {
		//Check if Facility selected or not
		if ($facility_code) {
			$added_sql = "and facility_code='$facility_code'";
		} else {
			$added_sql = "";
		}
		//Variables
		$selected_period = "March-2013";
		$period = explode('-', $selected_period);
		$year = $period[1];
		$month = date('m', strtotime($period[0]));
		$today = date('Y-m-d', strtotime("01-$selected_period"));
		$below_one_year = 0;
		$male_below_fifteen_years = 0;
		$female_below_fifteen_years = 0;
		$male_above_fifteen_years = 0;
		$female_above_fifteen_years = 0;

		//Get patients enrolled in care below 1 year
		$sql = "select count(*) AS total from patient where MONTH(date_enrolled)='$month' and YEAR(date_enrolled)='$year' and DATEDIFF('$today',dob)<=360 $added_sql";
		$query = $this -> db -> query($sql);
		$results = $query -> result_array();
		if ($results) {
			$below_one_year = $results[0]['total'];
		}

		//Get Patients enrolled in care below 15 years
		$sql = "select count(*) AS total,gender from patient where MONTH(date_enrolled)='$month' and YEAR(date_enrolled)='$year' and DATEDIFF('$today',dob)>360 and DATEDIFF('$today',dob)<=(360*15) $added_sql group by gender";
		$query = $this -> db -> query($sql);
		$results = $query -> result_array();
		if ($results) {
			if ($results[0]['gender'] == 1) {
				$male_below_fifteen_years = $results[0]['total'];
			} else if ($results[0]['gender'] == 2) {
				$female_below_fifteen_years = $results[0]['total'];
			}
		}

		//Get Patients enrolled in care above 15 years
		$sql = "select count(*) AS total,gender from patient where MONTH(date_enrolled)='$month' and YEAR(date_enrolled)='$year' and DATEDIFF('$today',dob)>(360*15) $added_sql group by gender";
		$query = $this -> db -> query($sql);
		$results = $query -> result_array();
		if ($results) {
			if ($results[0]['gender'] == 1) {
				$male_above_fifteen_years = $results[0]['total'];
			} else if ($results[0]['gender'] == 2) {
				$female_above_fifteen_years = $results[0]['total'];
			}
			$above_fifteen_years = $results[0]['total'];
		}

		$data = array();
		$data['<1'] = $below_one_year;
		$data['<15_male'] = $male_below_fifteen_years;
		$data['<15_female'] = $female_below_fifteen_years;
		$data['15>_male'] = $male_above_fifteen_years;
		$data['15>_female'] = $female_above_fifteen_years;
		$data['enrolled_total'] = $below_one_year + $male_below_fifteen_years + $female_below_fifteen_years + $male_above_fifteen_years + $female_above_fifteen_years;

		return $data;

	}

	public function currently_in_care($selected_period = "", $facility_code = "") {
		//Check if Facility selected or not
		if ($facility_code) {
			$added_sql = "and facility_code='$facility_code'";
		} else {
			$added_sql = "";
		}
		//Variables
		$selected_period = "March-2013";
		$period = explode('-', $selected_period);
		$year = $period[1];
		$month = date('m', strtotime($period[0]));
		$today = date('Y-m-t', strtotime("$selected_period"));
		$below_one_year = 0;
		$male_below_fifteen_years = 0;
		$female_below_fifteen_years = 0;
		$male_above_fifteen_years = 0;
		$female_above_fifteen_years = 0;

		//Get patients currently in care below 1 year
		$sql = "select count(*) AS total from patient where start_regimen_date <='$today' and DATEDIFF('$today',dob)<=360 and current_status='1' $added_sql";
		$query = $this -> db -> query($sql);
		$results = $query -> result_array();
		if ($results) {
			$below_one_year = $results[0]['total'];
		}

		//Get Patients currently in care below 15 years
		$sql = "select count(*) AS total,gender from patient where start_regimen_date <='$today' and DATEDIFF('$today',dob)>360 and DATEDIFF('$today',dob)<=(360*15) and current_status='1' $added_sql group by gender";
		$query = $this -> db -> query($sql);
		$results = $query -> result_array();
		if ($results) {
			if ($results[0]['gender'] == 1) {
				$male_below_fifteen_years = $results[0]['total'];
			} else if ($results[0]['gender'] == 2) {
				$female_below_fifteen_years = $results[0]['total'];
			}
		}

		//Get Patients currently in care above 15 years
		$sql = "select count(*) AS total,gender from patient where start_regimen_date <='$today' and DATEDIFF('$today',dob)>(360*15) and current_status='1' $added_sql group by gender";
		$query = $this -> db -> query($sql);
		$results = $query -> result_array();
		if ($results) {
			if ($results[0]['gender'] == 1) {
				$male_above_fifteen_years = $results[0]['total'];
			} else if ($results[0]['gender'] == 2) {
				$female_above_fifteen_years = $results[0]['total'];
			}
			$above_fifteen_years = $results[0]['total'];
		}
		$data = array();
		$data['<1'] = $below_one_year;
		$data['<15_male'] = $male_below_fifteen_years;
		$data['<15_female'] = $female_below_fifteen_years;
		$data['15>_male'] = $male_above_fifteen_years;
		$data['15>_female'] = $female_above_fifteen_years;
		$data['currently_in_care_total'] = $below_one_year + $male_below_fifteen_years + $female_below_fifteen_years + $male_above_fifteen_years + $female_above_fifteen_years;

		return $data;

	}

	public function starting_art($selected_period = "", $facility_code = "") {
		//Check if Facility selected or not
		if ($facility_code) {
			$added_sql = "and facility_code='$facility_code'";
		} else {
			$added_sql = "";
		}
		//Variables
		$selected_period = "March-2013";
		$period = explode('-', $selected_period);
		$year = $period[1];
		$month = date('m', strtotime($period[0]));
		$today = date('Y-m-t', strtotime("$selected_period"));
		$below_one_year = 0;
		$male_below_fifteen_years = 0;
		$female_below_fifteen_years = 0;
		$male_above_fifteen_years = 0;
		$female_above_fifteen_years = 0;
		$female_pregnant = 0;
		$female_not_pregnant = 0;
		$no_tb = 0;
		$yes_tb = 0;

		//Get patients starting on ART  below 1 year
		$sql = "select count(*) AS total from patient where start_regimen_date <='$today' and DATEDIFF('$today',dob)<=360 and service='1' $added_sql";
		$query = $this -> db -> query($sql);
		$results = $query -> result_array();
		if ($results) {
			$below_one_year = $results[0]['total'];
		}

		//Get Patients starting on ART  below 15 years
		$sql = "select count(*) AS total,gender from patient where start_regimen_date <='$today' and DATEDIFF('$today',dob)>360 and DATEDIFF('$today',dob)<=(360*15) and service='1' $added_sql group by gender";
		$query = $this -> db -> query($sql);
		$results = $query -> result_array();
		if ($results) {
			if ($results[0]['gender'] == 1) {
				$male_below_fifteen_years = $results[0]['total'];
			} else if ($results[0]['gender'] == 2) {
				$female_below_fifteen_years = $results[0]['total'];
			}
		}

		//Get Patients starting on ART  above 15 years
		$sql = "select count(*) AS total,gender from patient where start_regimen_date <='$today' and DATEDIFF('$today',dob)>(360*15) and service='1' $added_sql group by gender";
		$query = $this -> db -> query($sql);
		$results = $query -> result_array();
		if ($results) {
			if ($results[0]['gender'] == 1) {
				$male_above_fifteen_years = $results[0]['total'];
			} else if ($results[0]['gender'] == 2) {
				$female_above_fifteen_years = $results[0]['total'];
			}
			$above_fifteen_years = $results[0]['total'];
		}

		//Get Patients starting on ART and are pregnant
		$sql = "select count(*) AS total,pregnant from patient where start_regimen_date <='$today' and gender='2' $added_sql group by pregnant";
		$query = $this -> db -> query($sql);
		$results = $query -> result_array();
		if ($results) {

			foreach ($results as $result) {
				if ($result['pregnant'] == 0) {
					$female_not_pregnant = $result['total'];
				} else if ($result['pregnant'] == 1) {
					$female_pregnant = $result['total'];
				}
			}
		}

		//Get Patients starting on ART and have TB
		$sql = "select count(*) AS total,tb from patient where start_regimen_date <='$today'  $added_sql group by tb";
		$query = $this -> db -> query($sql);
		$results = $query -> result_array();
		if ($results) {

			foreach ($results as $result) {
				if ($result['tb'] == 0) {
					$no_tb = $result['total'];
				} else if ($result['tb'] == 1) {
					$yes_tb = $result['total'];
				}
			}
		}

		$data = array();
		$data['<1'] = $below_one_year;
		$data['<15_male'] = $male_below_fifteen_years;
		$data['<15_female'] = $female_below_fifteen_years;
		$data['15>_male'] = $male_above_fifteen_years;
		$data['15>_female'] = $female_above_fifteen_years;
		$data['female_pregnant'] = $female_pregnant;
		$data['female_not_pregnant'] = $female_not_pregnant;
		$data['yes_tb'] = $yes_tb;
		$data['no_tb'] = $no_tb;
		$data['starting_art_total'] = $below_one_year + $male_below_fifteen_years + $female_below_fifteen_years + $male_above_fifteen_years + $female_above_fifteen_years;

		return $data;

	}

	public function prevention_with_positives($selected_period = "", $facility_code = "") {
		//Check if Facility selected or not
		if ($facility_code) {
			$added_sql = "where facility_code='$facility_code'";
		} else {
			$added_sql = "";
		}
		//Variables
		$selected_period = "March-2013";
		$period = explode('-', $selected_period);
		$year = $period[1];
		$month = date('m', strtotime($period[0]));
		$today = date('Y-m-t', strtotime("$selected_period"));
		$condoms = 0;
		$modern_contraceptives = 0;

		//Get patients using modern contraceptives
		$sql = "select count(*) as total from (select fplan from patient $added_sql) as fplan_data where '-1-' not in(fplan_data.fplan)";
		$query = $this -> db -> query($sql);
		$results = $query -> result_array();
		if ($results) {
			$modern_contraceptives = $results[0]['total'];
		}

		//Get patients using condoms
		$sql = "select count(*) as total from (select fplan from patient $added_sql) as fplan_data where '-1-' in(fplan_data.fplan) ";
		$query = $this -> db -> query($sql);
		$results = $query -> result_array();
		if ($results) {
			$condoms = $results[0]['total'];
		}
		$data = array();
		$data['condoms'] = $condoms;
		$data['modern_contraceptives'] = $modern_contraceptives;

		return $data;
	}

	public function hiv_care_visits($selected_period = "", $facility_code = "") {
		//Check if Facility selected or not
		if ($facility_code) {
			$added_sql = "and facility='$facility_code'";
		} else {
			$added_sql = "";
		}
		//Variables
		$selected_period = "March-2013";
		$period = explode('-', $selected_period);
		$year = $period[1];
		$month = date('m', strtotime($period[0]));
		$from = date('Y-m-01', strtotime("$selected_period"));
		$to = date('Y-m-t', strtotime("$selected_period"));
		$female_18 = 0;
		$scheduled_visits = 0;
		$unscheduled_visits = 0;

		//Get female patients in hiv care visits
		$sql = "select count(patient_number_ccc) as total from patient p left join patient_visit pv on pv.patient_id=p.patient_number_ccc where dispensing_date between '$from' and '$to' and gender='2' and DATEDIFF('$from',dob)>=(360*18) group by facility_code";
		$query = $this -> db -> query($sql);
		$results = $query -> result_array();
		if ($results) {
			$female_18 = $results[0]['total'];
		}

		//Scheduled Care visits
		$sql = "select count(*) as total from (select patient,facility from  patient_appointment where appointment between '$from' and '$to' $added_sql group by patient,facility) as pa,(select patient_id,facility from patient_visit where dispensing_date between '$from' and '$to' $added_sql group by patient_id,facility) as pv where pv.patient_id=pa.patient and pv.facility=pa.facility";
		$query = $this -> db -> query($sql);
		$results = $query -> result_array();
		if ($results) {
			$scheduled_visits = $results[0]['total'];
		}

		//Unscheduled Care visits
		$sql = "select count(*)as total from (select patient,facility from  patient_appointment where appointment between '$from' and '$to' $added_sql group by patient,facility) as pa,(select patient_id,facility from patient_visit where dispensing_date between '$from' and '$to' $added_sql group by patient_id,facility) as pv where pv.patient_id NOT IN(pa.patient)group by pv.patient_id,pv.facility";
		$query = $this -> db -> query($sql);
		$results = $query -> result_array();
		if ($results) {
			$unscheduled_visits = $results[0]['total'];
		}

		$data = array();
		$data['female>18'] = $female_18;
		$data['scheduled_visits'] = $scheduled_visits;
		$data['unscheduled_visits'] = $unscheduled_visits;
		$data['total'] = $scheduled_visits + $unscheduled_visits;

		return $data;
	}

	public function revisits_on_art($selected_period = "", $facility_code = "") {
		//Check if Facility selected or not
		if ($facility_code) {
			$added_sql = "where facility_code='$facility_code'";
		} else {
			$added_sql = "";
		}
		//Variables
		$selected_period = "March-2013";
		$period = explode('-', $selected_period);
		$year = $period[1];
		$month = date('m', strtotime($period[0]));
		$today = date('Y-m-t', strtotime("$selected_period"));

		$from_2months = "";
		$to_1month = "";

		$revisit_on_art_below_1 = 0;
		$revisit_on_art_below_15 = 0;
		$revisit_on_art_above_15 = 0;

		//Get Patients who revisited in last 2 months
		$sql = "select count(patient_number_ccc) as total from patient p left join patient_visit pv on pv.patient_id=p.patient_number_ccc where dispensing_date between '$from' and '$to' and gender='2' and DATEDIFF('$from',dob)>=(360*18) group by facility_code";
		$query = $this -> db -> query($sql);
		$results = $query -> result_array();
		if ($results) {
			$female_18 = $results[0]['total'];
		}

	}

}
?>	