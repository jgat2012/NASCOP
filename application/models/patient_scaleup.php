<?php

class Patient_Scaleup extends Doctrine_Record {
	public function setTableDefinition() {
		$this -> hasColumn('id', 'int', 11);
		$this -> hasColumn('pipeline', 'varchar', 50);
		$this -> hasColumn('month', 'varchar', 50);
		$this -> hasColumn('year', 'varchar', 50);
		$this -> hasColumn('adult_art', 'varchar', 50);
		$this -> hasColumn('paed_art', 'varchar', 50);
		$this -> hasColumn('paed_pep', 'varchar', 50);
		$this -> hasColumn('adult_pep', 'varchar', 50);
		$this -> hasColumn('mothers_pmtct', 'varchar', 200);
		$this -> hasColumn('infant_pmtct', 'varchar', 150);
	}

	public function setUp() {
		$this -> setTableName('patient_scaleup');
	}

	public function getAll() {
		$query = Doctrine_Query::create() -> select("*") -> from("patient_scaleup");
		$types = $query -> execute();
		return $types;
	}

	public function checkValid($pipeline, $month, $year) {
		$query = Doctrine_Query::create() -> select("*") -> from("patient_scaleup") -> where("pipeline='$pipeline' and month='$month' and year='$year'");
		$types = $query -> execute(array(), Doctrine::HYDRATE_ARRAY);
		return $types;
	}

	public function getTotals($pipeline, $month, $year) {
		$query = Doctrine_Query::create() -> select("adult_art,paed_art,year,month") -> from("patient_scaleup") -> where("concat(year,month) <='$year$month' and `pipeline`='$pipeline'")->orderBy("year desc,month desc");
		$types = $query -> execute(array(), Doctrine::HYDRATE_ARRAY);
		return $types;
	}

}
?>