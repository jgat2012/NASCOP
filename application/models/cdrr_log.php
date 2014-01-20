<?php
class Cdrr_Log extends Doctrine_Record {

	public function setTableDefinition() {
		$this -> hasColumn('description', 'varchar', 255);
		$this -> hasColumn('created', 'datetime');
		$this -> hasColumn('user_id', 'int', 11);
		$this -> hasColumn('cdrr_id', 'int', 11);
	}

	public function setUp() {
		$this -> setTableName('cdrr_log');
		$this -> hasOne('Cdrr as Cdrr', array('local' => 'cdrr_id', 'foreign' => 'id'));
		$this -> hasOne('Sync_User as S_User', array('local' => 'user_id', 'foreign' => 'id'));
	}

	public function getAll() {
		$query = Doctrine_Query::create() -> select("*") -> from("cdrr_log");
		$cdrr_log = $query -> execute();
		return $cdrr_log;
	}

}
?>