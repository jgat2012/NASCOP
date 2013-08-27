<?php

class Pipeline_Consumption extends Doctrine_Record {
	public function setTableDefinition() {
		$this -> hasColumn('id', 'int', 11);
		$this -> hasColumn('pipeline', 'varchar', 50);
		$this -> hasColumn('month', 'varchar', 100);
		$this -> hasColumn('year', 'varchar', 100);
		$this -> hasColumn('drugname', 'varchar', 200);
		$this -> hasColumn('consumption', 'varchar', 150);
	}

	public function setUp() {
		$this -> setTableName('pipeline_consumption');
	}

	public function getAll() {
		$query = Doctrine_Query::create() -> select("*") -> from("pipeline_consumption");
		$types = $query -> execute();
		return $types;
	}

	public function checkValid($pipeline, $month, $year, $drugname_cell) {
		if ($pipeline == 1) {
			$first_drug = md5("Lopinavir/Ritonavir (LPV/r) 200mg/50mg tabs");
			$second_drug = md5("Lopinavir/ritonavir (LPV/r) liquid  80/20mg/ml");
			$third_drug = md5("Lamivudine (3TC) liquid  10mg/ml");
			if (md5($drugname_cell) === $first_drug) {
				$drugname_cell = "200mg/50mg";
			}
			if (md5($drugname_cell) === $second_drug) {
				$drugname_cell = "80/20mg/ml";
			}
			if (md5($drugname_cell) === $third_drug) {
				$drugname_cell = "Lamivudine (3TC) liquid";
			}
		}
		if ($pipeline == 2) {
			$first_drug = md5("Didanosine 200mg tabs DDI 200");
			$second_drug = md5("Triomune 30mg STAVUDINE/LAMIVUDINE/NEVIRAPINE(300/150/200)");
			$third_drug = md5("Triomune junior STAVUDINE/LAMIVUDINE/NEVIRAPINE (12/60/100)");
			if (md5($drugname_cell) === $first_drug) {
				$drugname_cell = "Didanosine 200mg";
			}
			if (md5($drugname_cell) === $second_drug) {
				$drugname_cell = "Triomune 30mg";
			}
			if (md5($drugname_cell) === $third_drug) {
				$drugname_cell = "Triomune junior";
			}

		}
		$query = Doctrine_Query::create() -> select("*") -> from("pipeline_consumption") -> where("pipeline='$pipeline' and month='$month' and year='$year' and drugname like '%$drugname_cell%'");
		$types = $query -> execute(array(), Doctrine::HYDRATE_ARRAY);
		return $types;

	}

	public function getTotals($pipeline, $month, $year) {
		$query = Doctrine_Query::create() -> select("*") -> from("pipeline_consumption") -> where("pipeline='$pipeline' and month='$month' and year='$year'");
		$types = $query -> execute(array(), Doctrine::HYDRATE_ARRAY);
		return $types;
	}

	public function getDrugs($pipeline, $month, $year) {
		$query = Doctrine_Query::create() -> select("distinct(drugname) as drugname") -> from("pipeline_consumption") -> where("pipeline='$pipeline' and month='$month' and year='$year'");
		$types = $query -> execute(array(), Doctrine::HYDRATE_ARRAY);
		return $types;
	}

	public function getConsumption($pipeline, $month, $year) {
		$query = Doctrine_Query::create() -> select("upper(drugname) as drugname, consumption") -> from("pipeline_consumption") -> where("pipeline='$pipeline' and month='$month' and year='$year'") -> groupBy("drugname, consumption") -> orderBy("drugname asc");
		$types = $query -> execute(array(), Doctrine::HYDRATE_ARRAY);
		return $types;
	}

}
?>