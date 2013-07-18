<?php

class Dashboard_Servicepoints extends Doctrine_Record {
	public function setTableDefinition() {
		$this -> hasColumn('id', 'int', 11);
		$this -> hasColumn('pipeline', 'varchar',50);
		$this -> hasColumn('month', 'varchar', 50);
		$this -> hasColumn('year', 'varchar', 50);
		$this -> hasColumn('mfl_code', 'varchar',50);
		$this -> hasColumn('facility_name', 'varchar',200);
		$this -> hasColumn('centralsite_name', 'varchar',200);
		$this -> hasColumn('district', 'varchar',50);
		$this -> hasColumn('province', 'varchar',50);
		$this -> hasColumn('dispensing', 'varchar',50);
		$this -> hasColumn('standalone', 'varchar', 50);
		$this -> hasColumn('satellite', 'varchar', 50);
	}

	public function setUp() {
		$this -> setTableName('dashboard_servicepoints');
	}

	public function getAll() {
		$query = Doctrine_Query::create() -> select("*") -> from("dashboard_servicepoints");
		$types = $query -> execute();
		return $types;
	}

	public function checkValid($pipeline,$month,$year,$facility_name,$mfl_code) {
		
		if($mfl_code){
			$display_code="and mfl_code='$mfl_code'";
		}else{
			$display_code="and `facility_name`=\"".$facility_name."\"";
		}
		
		$query = Doctrine_Query::create() -> select("*") -> from("dashboard_servicepoints")->where("pipeline='$pipeline' and month='$month' and year='$year' $display_code");
		//echo $query->getSQL();
		$types = $query ->execute(array(), Doctrine::HYDRATE_ARRAY);
		return $types;
	}

}
?>