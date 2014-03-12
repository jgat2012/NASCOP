<?php
class Eid_Master extends Doctrine_Record {

	public function setTableDefinition() {
		$this -> hasColumn('facilitycode', 'varchar', 30);
		$this -> hasColumn('datetested', 'date');
		$this -> hasColumn('enrollmentcccno', 'varchar', 30);
		$this -> hasColumn('dateinitiatedontreatment', 'date');
	}

	public function setUp() {
		$this -> setTableName('eid_master');
	}

	public function getAll() {
		$query = Doctrine_Query::create() -> select("*") -> from("eid_master");
		$eid = $query -> execute();
		return $eid;
	}

}
