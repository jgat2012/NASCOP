<?php
class Resupply_Change extends Doctrine_Record {

	public function setTableDefinition() {
		$this -> hasColumn('cdrr_id', 'int', 11);
		$this -> hasColumn('drug_id', 'int', 11);
		$this -> hasColumn('resupply', 'int', 11);
	}

	public function setUp() {
		$this -> setTableName('resupply_change');
	}

	public function getAll() {
		$query = Doctrine_Query::create() -> select("*") -> from("resupply_change");
		$resupply = $query -> execute(array(), Doctrine::HYDRATE_ARRAY);
		return $resupply;
	}

}
?>