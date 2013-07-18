<?php

class Satellite_Rates extends Doctrine_Record {
	public function setTableDefinition() {
		$this -> hasColumn('id', 'int', 11);
		$this -> hasColumn('pipeline', 'varchar',50);
		$this -> hasColumn('month', 'varchar', 50);
		$this -> hasColumn('year', 'varchar', 50);
		$this -> hasColumn('province', 'varchar', 100);
		$this -> hasColumn('district', 'varchar',100);
		$this -> hasColumn('expected', 'varchar',150);
		$this -> hasColumn('actual', 'varchar',150);

	}

	public function setUp() {
		$this -> setTableName('satellite_rates');
	}

	public function getAll() {
		$query = Doctrine_Query::create() -> select("*") -> from("satellite_rates");
		$types = $query -> execute();
		return $types;
	}

	public function checkValid($pipeline,$month,$year) {
		$query = Doctrine_Query::create() -> select("*") -> from("satellite_rates")->where("pipeline='$pipeline' and month='$month' and year='$year'");
		$types = $query ->execute(array(), Doctrine::HYDRATE_ARRAY);
		return $types;

	}

}
?>