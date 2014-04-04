<?php
class Sync_Category extends Doctrine_Record {

	public function setTableDefinition() {
		$this -> hasColumn('name', 'varchar', 150);
		$this -> hasColumn('active', 'int', 2);
	}

	public function setUp() {
		$this -> setTableName('sync_category');
	}

	public function getAll() {
		$query = Doctrine_Query::create() -> select("*") -> from("sync_category");
		$category = $query -> execute();
		return $category;
	}
	
	public function getId($name="") {
		$query = Doctrine_Query::create() -> select("id") -> from("sync_category") ->where("name LIKE '%".$name."%'")->limit('1');
		$category = $query -> execute(array(), Doctrine::HYDRATE_ARRAY);
		return $category[0];
	}
}
