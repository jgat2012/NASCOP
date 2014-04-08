<?php
class Two_pager extends Doctrine_Record {
	public function setTableDefinition() {
		$this -> hasColumn('period', 'text');
		$this -> hasColumn('link', 'varchar', 100);
		$this -> hasColumn('active', 'int', 1);
	}

	public function setUp() {
		$this -> setTableName('Two_pager');
	}

	public static function getAllHydrated() {
		$query = Doctrine_Query::create() -> select("id,link,period") -> from("Two_pager")->orderBy('period desc');
		$menus = $query -> execute(array(), Doctrine::HYDRATE_ARRAY);
		return $menus;
	}
	public function getAll() {
		$query = Doctrine_Query::create() -> select("*") -> from("Two_pager");
		$menus = $query -> execute();
		return $menus;
	}
	
	public function getAllActive() {
		$query = Doctrine_Query::create() -> select("*") -> from("Two_pager")->where("active='1'");
		$menus = $query -> execute(array(), Doctrine::HYDRATE_ARRAY);
		return $menus;
	}
	public function checkIfExist($period="") {
		$query = Doctrine_Query::create() -> select("*") -> from("Two_pager")->where("period='".$period."'");
		$menus = $query -> execute(array(), Doctrine::HYDRATE_ARRAY);
		return $menus;
	}

}
