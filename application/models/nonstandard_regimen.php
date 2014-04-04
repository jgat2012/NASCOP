<?php
class Nonstandard_Regimen extends Doctrine_Record {

	public function setTableDefinition() {
		$this -> hasColumn('regimen_code', 'varchar', 20);
		$this -> hasColumn('regimen_desc', 'varchar', 150);
		$this -> hasColumn('category', 'varchar', 30);
		$this -> hasColumn('line', 'varchar', 4);
		$this -> hasColumn('type_Of_service', 'varchar', 20);
		$this -> hasColumn('active', 'int', 11);
		$this -> hasColumn('n_map', 'int', 11);
		$this -> hasColumn('e_map', 'int', 11);
	}

	public function setUp() {
		$this -> setTableName('nonstandard_regimen');
		$this -> hasOne('Sync_category as Regimen_Category', array('local' => 'Category', 'foreign' => 'id'));
		$this -> hasOne('Regimen_Service_Type as Regimen_Service_Type', array('local' => 'Type_Of_Service', 'foreign' => 'id'));
		$this -> hasMany('Regimen_Drug as Drugs', array('local' => 'id', 'foreign' => 'Regimen'));
		$this -> hasOne('Sync_Regimen as S_Regimen', array('local' => 'n_map', 'foreign' => 'id'));
		$this -> hasOne('Escm_Regimen as E_Regimen', array('local' => 'e_map', 'foreign' => 'id'));
	}

	public function getAll($source = 0) {
		$query = Doctrine_Query::create() -> select("*") -> from("nonstandard_regimen") -> orderBy("regimen_code asc");
		$regimens = $query -> execute();
		return $regimens;
	}

	public function getAllHydrate() {
		$query = Doctrine_Query::create() -> select("*") -> from("nonstandard_regimen") -> orderBy("regimen_code asc");
		$regimens = $query -> execute(array(), Doctrine::HYDRATE_ARRAY);
		return $regimens;
	}
	
	public function getMaxId(){
		$query = Doctrine_Query::create() -> select("MAX(id) as max_id") -> from("nonstandard_regimen") ->limit('1');
		$category = $query -> execute(array(), Doctrine::HYDRATE_ARRAY);
		return $category[0];
	}


}
?>