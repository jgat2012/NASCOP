<?php
class Maps_Log extends Doctrine_Record {

	public function setTableDefinition() {
		$this -> hasColumn('description', 'varchar', 255);
		$this -> hasColumn('created', 'datetime');
		$this -> hasColumn('user_id', 'int', 11);
		$this -> hasColumn('maps_id', 'int', 11);
	}

	public function setUp() {
		$this -> setTableName('maps_log');
		$this -> hasOne('Maps as Maps', array('local' => 'maps_id', 'foreign' => 'id'));
		$this -> hasOne('Sync_User as S_User', array('local' => 'user_id', 'foreign' => 'id'));
	}

	public function getAll() {
		$query = Doctrine_Query::create() -> select("*") -> from("maps_log");
		$cdrr_log = $query -> execute();
		return $cdrr_log;
	}

	public function getMapLogs($map_id) {
		$query = Doctrine_Query::create() -> select("*") -> from("maps_log") -> where("maps_id='$map_id'");
		$map_log = $query -> execute();
		return $map_log;
	}

}
?>