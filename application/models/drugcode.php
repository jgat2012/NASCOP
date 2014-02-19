<?php
class Drugcode extends Doctrine_Record {

	public function setTableDefinition() {
		$this -> hasColumn('name', 'varchar', 150);
		$this -> hasColumn('unit', 'varchar', 30);
		$this -> hasColumn('pack_size', 'varchar', 20);
		$this -> hasColumn('category_id', 'int', 11);
		$this -> hasColumn('arv_drug', 'int', 11);
		$this -> hasColumn('active', 'int', 11);
		$this -> hasColumn('n_map', 'int', 11);
		$this -> hasColumn('e_map', 'int', 11);
	}

	public function setUp() {
		$this -> setTableName('drugcode');
		$this -> hasOne('Sync_Drug as S_Drug', array('local' => 'n_map', 'foreign' => 'id'));
		$this -> hasOne('Escm_Drug as E_Drug', array('local' => 'e_map', 'foreign' => 'id'));
	}

	public function getAll() {
		$query = Doctrine_Query::create() -> select("*") -> from("drugcode");
		$drugs = $query -> execute();
		return $drugs;
	}

}
