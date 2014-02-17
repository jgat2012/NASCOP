<?php
class Escm_Maps extends Doctrine_Record {

	public function setTableDefinition() {
		$this -> hasColumn('maps_id', 'int', 11);
		$this -> hasColumn('escm_id', 'int', 11);
	}

	public function setUp() {
		$this -> setTableName('escm_maps');
		$this -> hasOne('Maps as Map', array('local' => 'maps_id', 'foreign' => 'id'));
	}

	public function getAll() {
		$query = Doctrine_Query::create() -> select("*") -> from("escm_maps");
		$maps = $query -> execute();
		return $maps;
	}

	public function getOrder($maps_id) {
		$query = Doctrine_Query::create() -> select("*") -> from("escm_maps") -> where("maps_id='$maps_id'");
		$maps = $query -> execute();
		return $maps;
	}

	public function getEscm($escm_id) {
		$query = Doctrine_Query::create() -> select("*") -> from("escm_maps") -> where("escm_id='$escm_id'");
		$maps = $query -> execute(array(), Doctrine::HYDRATE_ARRAY);
		return $maps[0];
	}

}
?>