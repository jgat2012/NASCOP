<?php
class Eid_Info extends Doctrine_Record {

	public function setTableDefinition() {
		$this -> hasColumn('patient_no', 'varchar', 150);
		$this -> hasColumn('facility_code', 'varchar', 30);
		$this -> hasColumn('gender', 'varchar', 10);
		$this -> hasColumn('birth_date', 'varchar', 50);
		$this -> hasColumn('service', 'varchar', 50);
		$this -> hasColumn('regimen', 'varchar', 200);
		$this -> hasColumn('enrollment_date', 'varchar', 50);
		$this -> hasColumn('source', 'varchar', 100);
	}

	public function setUp() {
		$this -> setTableName('eid_info');
	}

	public function getAll() {
		$query = Doctrine_Query::create() -> select("*") -> from("eid_info");
		$eid_info = $query -> execute();
		return $eid_info;
	}

}
