<?php
class Cdrr_Item extends Doctrine_Record {

	public function setTableDefinition() {
		$this -> hasColumn('balance', 'int', 11);
		$this -> hasColumn('received', 'int', 11);
		$this -> hasColumn('dispensed_units', 'int', 11);
		$this -> hasColumn('dispensed_packs', 'int', 11);
		$this -> hasColumn('losses', 'int', 11);
		$this -> hasColumn('adjustments', 'int', 11);
		$this -> hasColumn('count', 'int', 11);
		$this -> hasColumn('expiry_quant', 'int', 11);
		$this -> hasColumn('expiry_date', 'date');
		$this -> hasColumn('out_of_stock', 'int', 11);
		$this -> hasColumn('aggr_consumed', 'int', 11);
		$this -> hasColumn('aggr_on_hand', 'int', 11);
		$this -> hasColumn('publish', 'tinyint', 1);
		$this -> hasColumn('cdrr_id', 'int', 11);
		$this -> hasColumn('drug_id', 'int', 11);
	}

	public function setUp() {
		$this -> setTableName('cdrr_item');
		$this -> hasOne('Cdrr as Cdrr', array('local' => 'cdrr_id', 'foreign' => 'id'));
		$this -> hasOne('Sync_Drug as S_Drug', array('local' => 'drug_id', 'foreign' => 'id'));
	}

	public static function getOrderItems($cdrr) {
		$query = Doctrine_Query::create() -> select("*") -> from("cdrr_item") -> where("cdrr_id = '$cdrr'");
		$items = $query -> execute();
		return $items;
	}

}
?>