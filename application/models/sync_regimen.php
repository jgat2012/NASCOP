<?php
class Sync_Regimen extends Doctrine_Record {

	public function setTableDefinition() {
		$this -> hasColumn('name', 'varchar', 255);
		$this -> hasColumn('code', 'varchar',5);
		$this -> hasColumn('old_code', 'varchar',45);
		$this -> hasColumn('description', 'text');
		$this -> hasColumn('category_id', 'int',11);
	}

	public function setUp() {
		$this -> setTableName('sync_regimen');
	}

	public function getAll() {
		$query = Doctrine_Query::create() -> select("*") -> from("sync_regimen");
		$sync_regimen = $query -> execute();
		return $sync_regimen;
	}
	
	public function getAllSettings() {
		$query = Doctrine_Query::create() -> select("code,name,description,old_code") -> from("sync_regimen");
		$sync_regimen = $query -> execute(array(), Doctrine::HYDRATE_ARRAY);
		return $sync_regimen;
	}

}
?>