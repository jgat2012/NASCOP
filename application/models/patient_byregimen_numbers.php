<?php

class Patient_Byregimen_Numbers extends Doctrine_Record {
	public function setTableDefinition() {
		$this -> hasColumn('id', 'int', 11);
		$this -> hasColumn('facilityname', 'varchar', 100);
		$this -> hasColumn('comments', 'varchar', 100);
		$this -> hasColumn('regimen_desc', 'varchar', 100);
		$this -> hasColumn('regimen_code', 'varchar', 100);
		$this -> hasColumn('previous_code', 'varchar', 100);
		$this -> hasColumn('month', 'varchar', 20);
		$this -> hasColumn('year', 'varchar', 20);
		$this -> hasColumn('total', 'varchar', 100);
		$this -> hasColumn('pipeline', 'varchar', 20);
	}

	public function setUp() {
		$this -> setTableName('patient_byregimen_numbers');
	}

	public function getAll() {
		$query = Doctrine_Query::create() -> select("*") -> from("patient_byregimen_numbers");
		$types = $query -> execute();
		return $types;
	}

	public function checkValid($pipeline, $month, $year) {
		$query = Doctrine_Query::create() -> select("*") -> from("patient_byregimen_numbers") -> where("pipeline='$pipeline' and month='$month' and year='$year'");
		$types = $query -> execute(array(), Doctrine::HYDRATE_ARRAY);
		return $types;
	}

	public function getTotals($pipeline, $month, $year) {
		$query = Doctrine_Query::create() -> select("*") -> from("patient_byregimen_numbers") -> where("pipeline='$pipeline' and month='$month' and year='$year'");
		$types = $query -> execute(array(), Doctrine::HYDRATE_ARRAY);
		return $types;
	}

	public function getFacilities($pipeline, $month, $year) {
		$query = Doctrine_Query::create() -> select("distinct(facilityname) as facilityname") -> from("patient_byregimen_numbers") -> where("pipeline='$pipeline' and month='$month' and year='$year'");
		$types = $query -> execute(array(), Doctrine::HYDRATE_ARRAY);
		return $types;
	}

	public function getRegimens($pipeline, $month, $year) {
		$query = Doctrine_Query::create() -> select("regimen_desc") -> from("patient_byregimen_numbers") -> where("pipeline='$pipeline' and month='$month' and year='$year' and facilityname ='AIC Litein Mission Hospital'");
		$types = $query -> execute(array(), Doctrine::HYDRATE_ARRAY);
		return $types;
	}

	public function getSpecificTotals($pipeline, $month, $year, $facility_name) {
		$query = Doctrine_Query::create() -> select("regimen_desc,total") -> from("patient_byregimen_numbers") -> where("pipeline='$pipeline' and month='$month' and year='$year' and facilityname like \"%$facility_name%\"");
		$types = $query -> execute(array(), Doctrine::HYDRATE_ARRAY);
		return $types;
	}

}
?>