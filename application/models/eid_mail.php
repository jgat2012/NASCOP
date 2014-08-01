<?php
class Eid_Mail extends Doctrine_Record {

	public function setTableDefinition() {
		$this -> hasColumn('email', 'varchar', 150);
		$this -> hasColumn('facility', 'varchar', 50);
		$this -> hasColumn('active', 'int', 11);
	}

	public function setUp() {
		$this -> setTableName('eid_mail');
	}

	public function getAll() {
		$query = Doctrine_Query::create() -> select("*") -> from("eid_mail");
		$eid_mail = $query -> execute();
		return $eid_mail;
	}

}
