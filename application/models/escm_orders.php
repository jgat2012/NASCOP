<?php
class Escm_Orders extends Doctrine_Record {

	public function setTableDefinition() {
		$this -> hasColumn('cdrr_id', 'int', 11);
	}

	public function setUp() {
		$this -> setTableName('escm_orders');
		$this -> hasOne('Cdrr as Cdrr', array('local' => 'cdrr_id', 'foreign' => 'id'));
	}

	public function getAll() {
		$query = Doctrine_Query::create() -> select("*") -> from("escm_orders")->groupBy("cdrr_id");
		$cdrrs = $query -> execute();
		return $cdrrs;
	}

	public function getOrder($cdrr_id) {
		$query = Doctrine_Query::create() -> select("*") -> from("escm_orders") -> where("cdrr_id='$cdrr_id'");
		$cdrrs = $query -> execute();
		return $cdrrs;
	}

}
?>