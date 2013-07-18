<?php

class Reporting_Rates extends Doctrine_Record {
	public function setTableDefinition() {
		$this -> hasColumn('id', 'int', 11);
		$this -> hasColumn('pipeline', 'varchar',50);
		$this -> hasColumn('month', 'varchar', 50);
		$this -> hasColumn('year', 'varchar', 50);
		$this -> hasColumn('rate', 'varchar',50);
		$this -> hasColumn('comments','text');
		$this -> hasColumn('by_5th', 'varchar',150);
		$this -> hasColumn('by_10th', 'varchar',150);
	}

	public function setUp() {
		$this -> setTableName('reporting_rates');
	}

	public function getAll() {
		$query = Doctrine_Query::create() -> select("*") -> from("reporting_rates");
		$types = $query -> execute();
		return $types;
	}

	public function checkValid($pipeline,$month,$year) {
		$query = Doctrine_Query::create() -> select("*") -> from("reporting_rates")->where("pipeline='$pipeline' and month='$month' and year='$year'");
		$types = $query ->execute(array(), Doctrine::HYDRATE_ARRAY);
		return $types;

	}

}
?>