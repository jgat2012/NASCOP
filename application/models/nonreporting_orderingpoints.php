<?php

class Nonreporting_Orderingpoints extends Doctrine_Record {
	public function setTableDefinition() {
		$this -> hasColumn('id', 'int', 11);
		$this -> hasColumn('pipeline', 'varchar',50);
		$this -> hasColumn('month', 'varchar', 50);
		$this -> hasColumn('year', 'varchar', 50);
		$this -> hasColumn('mfl_code', 'varchar', 100);
		$this -> hasColumn('facility_name', 'varchar',200);
		$this -> hasColumn('district', 'varchar',150);
		$this -> hasColumn('province', 'varchar',150);
		$this -> hasColumn('facility_type', 'varchar', 100);
		$this -> hasColumn('cdrr_maps', 'varchar',150);
		$this -> hasColumn('cdrr_only', 'varchar',150);
		$this -> hasColumn('maps_only', 'varchar',150);

	}

	public function setUp() {
		$this -> setTableName('nonreporting_orderingpoints');
	}

	public function getAll() {
		$query = Doctrine_Query::create() -> select("*") -> from("nonreporting_orderingpoints");
		$types = $query -> execute();
		return $types;
	}

	public function checkValid($pipeline,$month,$year) {
		$query = Doctrine_Query::create() -> select("*") -> from("nonreporting_orderingpoints")->where("pipeline='$pipeline' and month='$month' and year='$year'");
		$types = $query ->execute(array(), Doctrine::HYDRATE_ARRAY);
		return $types;

	}

}
?>