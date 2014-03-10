<?php
class Facility_Soh extends Doctrine_Record {

	public function setTableDefinition() {
		$this -> hasColumn('cms', 'int', 11);
		$this -> hasColumn('pending', 'int', 11);
		$this -> hasColumn('period_begin', 'varchar', 100);
		$this -> hasColumn('pipeline', 'varchar', 100);
		$this -> hasColumn('drug_id', 'int', 11);
	}

	public function setUp() {
		$this -> setTableName('facility_soh');
	}

	public function getAll() {
		$query = Doctrine_Query::create() -> select("*") -> from("facility_soh");
		$facility = $query -> execute();
		return $facility;
	}

}
