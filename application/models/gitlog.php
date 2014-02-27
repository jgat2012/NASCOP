<?php
class Gitlog extends Doctrine_Record {

	public function setTableDefinition() {
		$this -> hasColumn('facility_code', 'int', 11);
		$this -> hasColumn('hash_value', 'text');
		$this -> hasColumn('update_time', 'timestamp');
	}

	public function setUp() {
		$this -> setTableName('gitlog');
	}

	public function getAll() {
		$query = Doctrine_Query::create() -> select("*") -> from("gitlog");
		$gitlog = $query -> execute();
		return $gitlog;
	}

}
?>