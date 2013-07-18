<?php

class Dashboard_Paggregate extends Doctrine_Record {
	public function setTableDefinition() {
		$this -> hasColumn('id', 'int', 11);
		$this -> hasColumn('month', 'varchar',50);
		$this -> hasColumn('year', 'varchar', 50);
		$this -> hasColumn('sites_in_art', 'varchar',100);
		$this -> hasColumn('sites_in_pmtct', 'varchar',100);
		$this -> hasColumn('sites_in_pep', 'varchar',100);
		$this -> hasColumn('total_art_adults', 'varchar',100);
		$this -> hasColumn('total_art_children', 'varchar',100);
		$this -> hasColumn('total_males_new', 'varchar',100);
		$this -> hasColumn('total_males_revisit', 'varchar',100);
		$this -> hasColumn('total_females_new', 'varchar',100);
		$this -> hasColumn('total_females_revisit', 'varchar',100);
		$this -> hasColumn('total_pmtct_new', 'varchar',100);
		$this -> hasColumn('total_pmtct_revisit', 'varchar',100);
		$this -> hasColumn('total_infants_pmtct', 'varchar',100);
		$this -> hasColumn('total_pep_adults', 'varchar',100);
		$this -> hasColumn('total_pep_children', 'varchar',100);
		$this -> hasColumn('total_oi_adult', 'varchar',100);
		$this -> hasColumn('total_oi_children', 'varchar',100);
		$this -> hasColumn('total_diflucan_adults', 'varchar',100);
		$this -> hasColumn('total_diflucan_children', 'varchar',100);
		$this -> hasColumn('cm_new', 'varchar',100);
		$this -> hasColumn('cm_revisit', 'varchar',100);
		$this -> hasColumn('oc_new', 'varchar',100);
		$this -> hasColumn('oc_revisit', 'varchar',100);
		$this -> hasColumn('pipeline', 'varchar',20);
	}

	public function setUp() {
		$this -> setTableName('dashboard_paggregate');
	}

	public function getAll() {
		$query = Doctrine_Query::create() -> select("*") -> from("dashboard_paggregate");
		$types = $query -> execute();
		return $types;
	}

	public function checkValid($pipeline,$month,$year) {
		$query = Doctrine_Query::create() -> select("*") -> from("dashboard_paggregate")->where("pipeline='$pipeline' and month='$month' and year='$year'");
		$types = $query ->execute(array(), Doctrine::HYDRATE_ARRAY);
		return $types;

	}

}
?>