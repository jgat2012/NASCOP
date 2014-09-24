<?php
class Casco_List extends Doctrine_Record {

	public function setTableDefinition() {
		$this -> hasColumn('name', 'varchar', 150);
		$this -> hasColumn('county_id', 'int', 11);
		$this -> hasColumn('active', 'int', 2);
	}//end setTableDefinition

	public function setUp() {
		$this -> setTableName('casco_list');
		$this -> hasOne('Counties as county', array('local' => 'county_id', 'foreign' => 'id'));
	}//end setUp

	public function getActive() {
		$query = Doctrine_Query::create() -> select("*") -> from("casco_list")->where("active='1'")->orderBy("name asc");
		$counties = $query -> execute(array(), Doctrine::HYDRATE_ARRAY);
		return $counties;
	}

}//end class
?>