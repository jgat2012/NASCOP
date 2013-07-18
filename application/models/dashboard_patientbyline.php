<?php

class Dashboard_Patientbyline extends Doctrine_Record {
	public function setTableDefinition() {
		$this -> hasColumn('id', 'int', 11);
		$this -> hasColumn('pipeline', 'varchar',50);
		$this -> hasColumn('month', 'varchar', 50);
		$this -> hasColumn('year', 'varchar', 50);
		$this -> hasColumn('category', 'varchar',150);
		$this -> hasColumn('total', 'varchar',150);
	}

	public function setUp() {
		$this -> setTableName('dashboard_patientbyline');
	}

	public function getAll() {
		$query = Doctrine_Query::create() -> select("*") -> from("dashboard_patientbyline");
		$types = $query -> execute();
		return $types;
	}

	public function checkValid($pipeline,$month,$year,$category) {
		$query = Doctrine_Query::create() -> select("*") -> from("dashboard_patientbyline")->where("pipeline='$pipeline' and month='$month' and year='$year' and category='$category'");
		$types = $query ->execute(array(), Doctrine::HYDRATE_ARRAY);
		return $types;

	}

}
?>