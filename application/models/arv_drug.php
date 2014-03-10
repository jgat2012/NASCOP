<?php
class Arv_Drug extends Doctrine_Record {

	public function setTableDefinition() {
		$this -> hasColumn('facility', 'int', 11);
		$this -> hasColumn('pipeline', 'int', 2);
	}

	public function setUp() {
		$this -> setTableName('arv_drug');
	}

	public function getAll() {
		$query = Doctrine_Query::create() -> select("*") -> from("arv_drug");
		$arv = $query -> execute();
		return $arv;
	}

}
