<?php

class Dashboard_Orderpoints extends Doctrine_Record {
	public function setTableDefinition() {
		$this -> hasColumn('id', 'int', 11);
		$this -> hasColumn('pipeline', 'varchar',50);
		$this -> hasColumn('month', 'varchar', 50);
		$this -> hasColumn('year', 'varchar', 50);
		$this -> hasColumn('mfl_code', 'varchar',50);
		$this -> hasColumn('facility_name', 'varchar',200);
		$this -> hasColumn('district', 'varchar',50);
		$this -> hasColumn('province', 'varchar',50);
		$this -> hasColumn('central', 'varchar',50);
		$this -> hasColumn('standalone', 'varchar', 50);
		$this -> hasColumn('store', 'varchar', 50);
	}

	public function setUp() {
		$this -> setTableName('dashboard_orderpoints');
	}

	public function getAll() {
		$query = Doctrine_Query::create() -> select("*") -> from("dashboard_orderpoints");
		$types = $query -> execute();
		return $types;
	}
	
	public function getMonthList($pipeline, $month, $year) {
		$query = Doctrine_Query::create() -> select("*") -> from("dashboard_orderpoints")->where("pipeline='$pipeline' and month='$month' and year='$year'");
		$types = $query -> execute(array(), Doctrine::HYDRATE_ARRAY);
		return $types;
	}

	public function checkValid($pipeline,$month,$year,$mfl_code) {
		$query = Doctrine_Query::create() -> select("*") -> from("dashboard_orderpoints")->where("pipeline='$pipeline' and month='$month' and year='$year' and mfl_code='$mfl_code'");
		$types = $query ->execute(array(), Doctrine::HYDRATE_ARRAY);
		return $types;

	}

}
?>