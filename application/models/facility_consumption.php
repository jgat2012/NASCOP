<?php

class Facility_Consumption extends Doctrine_Record {
	public function setTableDefinition() {
		$this -> hasColumn('id', 'int', 11);
		$this -> hasColumn('facilityname', 'varchar', 200);
		$this -> hasColumn('facilitycode', 'varchar', 100);
		$this -> hasColumn('drugname', 'varchar', 200);
		$this -> hasColumn('month', 'varchar', 50);
		$this -> hasColumn('year', 'varchar', 50);
		$this -> hasColumn('total', 'varchar', 150);
		$this -> hasColumn('pipeline', 'varchar', 50);
	}

	public function setUp() {
		$this -> setTableName('facility_consumption');
	}

	public function getAll() {
		$query = Doctrine_Query::create() -> select("*") -> from("facility_consumption");
		$types = $query -> execute();
		return $types;
	}

	public function checkValid($pipeline, $month, $year) {
		$query = Doctrine_Query::create() -> select("*") -> from("facility_consumption") -> where("pipeline='$pipeline' and month='$month' and year='$year'");
		$types = $query -> execute(array(), Doctrine::HYDRATE_ARRAY);
		return $types;
	}

	public function getTotals($pipeline, $month, $year) {
		$query = Doctrine_Query::create() -> select("*") -> from("facility_soh") -> where("pipeline='$pipeline' and month='$month' and year='$year'");
		$types = $query -> execute(array(), Doctrine::HYDRATE_ARRAY);
		return $types;
	}

	public function getFacilities($pipeline, $month, $year) {
		$query = Doctrine_Query::create() -> select("distinct(facilityname) as facilityname") -> from("facility_soh") -> where("pipeline='$pipeline' and month='$month' and year='$year'");
		$types = $query -> execute(array(), Doctrine::HYDRATE_ARRAY);
		return $types;
	}

	public function getDrugs($pipeline, $month, $year) {
		$query = Doctrine_Query::create() -> select("distinct(drugname) as drugname") -> from("facility_soh") -> where("pipeline='$pipeline' and month='$month' and year='$year'");
		$types = $query -> execute(array(), Doctrine::HYDRATE_ARRAY);
		return $types;
	}

}
?>