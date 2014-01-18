<?php
class Sync_Drug extends Doctrine_Record {

	public function setTableDefinition() {
		$this -> hasColumn('name', 'varchar', 255);
		$this -> hasColumn('abbreviation', 'varchar', 255);
		$this -> hasColumn('strength', 'varchar', 255);
		$this -> hasColumn('packsize', 'int', 7);
		$this -> hasColumn('formulation', 'varchar', 255);
		$this -> hasColumn('unit', 'varchar', 255);
		$this -> hasColumn('note', 'varchar', 255);
		$this -> hasColumn('weight', 'int', 4);
		$this -> hasColumn('category_id', 'int', 11);
		$this -> hasColumn('regimen_id', 'int', 11);
	}

	public function setUp() {
		$this -> setTableName('sync_drug');
	}

	public function getAll() {
		$query = Doctrine_Query::create() -> select("*") -> from("sync_drug");
		$sync_drug = $query -> execute(array(), Doctrine::HYDRATE_ARRAY);
		return $sync_drug;
	}

}
?>