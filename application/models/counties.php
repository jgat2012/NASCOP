<?php
class Counties extends Doctrine_Record {

	public function setTableDefinition() {
		$this -> hasColumn('county', 'varchar', 100);
	}//end setTableDefinition

	public function setUp() {
		$this -> setTableName('counties');
	}//end setUp

	public function getTotalNumber() {
		$query = Doctrine_Query::create() -> select("count(*) as Total_Districts") -> from("Counties");
		$total = $query -> execute();
		return $total[0]['Total_Counties'];
	}

	public function getPagedDistricts($offset, $items) {
		$query = Doctrine_Query::create() -> select("*") -> from("Counties") -> offset($offset) -> limit($items);
		$districts = $query -> execute();
		return $districts;
	}
	public function getAll() {
		$query = Doctrine_Query::create() -> select("*") -> from("Counties");
		$districts = $query -> execute();
		return $districts;
	}

}//end class
?>