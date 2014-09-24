<?php
class Maps extends Doctrine_Record {
	public function setTableDefinition() {
		$this -> hasColumn('status', 'varchar', 10);
		$this -> hasColumn('created', 'datetime');
		$this -> hasColumn('updated', 'datetime');
		$this -> hasColumn('code', 'datetime');
		$this -> hasColumn('period_begin', 'date');
		$this -> hasColumn('period_end', 'date');
		$this -> hasColumn('reports_expected', 'int', 11);
		$this -> hasColumn('reports_actual', 'int', 11);
		$this -> hasColumn('services', 'varchar', 255);
		$this -> hasColumn('sponsors', 'varchar', 255);
		$this -> hasColumn('art_adult', 'int', 11);
		$this -> hasColumn('art_child', 'int', 11);
		$this -> hasColumn('new_male', 'int', 11);
		$this -> hasColumn('new_female', 'int', 11);
		$this -> hasColumn('revisit_male', 'int', 11);
		$this -> hasColumn('revisit_female', 'int', 11);
		$this -> hasColumn('new_pmtct', 'int', 11);
		$this -> hasColumn('revisit_pmtct', 'int', 11);
		$this -> hasColumn('total_infant', 'int', 11);
		$this -> hasColumn('pep_adult', 'int', 11);
		$this -> hasColumn('pep_child', 'int', 11);
		$this -> hasColumn('total_adult', 'int', 11);
		$this -> hasColumn('total_child', 'int', 11);
		$this -> hasColumn('diflucan_adult', 'int', 11);
		$this -> hasColumn('diflucan_child', 'int', 11);
		$this -> hasColumn('new_cm', 'int', 11);
		$this -> hasColumn('revisit_cm', 'int', 11);
		$this -> hasColumn('new_oc', 'int', 11);
		$this -> hasColumn('revisit_oc', 'int', 11);
		$this -> hasColumn('comments', 'text');
		$this -> hasColumn('report_id', 'int', 11);
		$this -> hasColumn('facility_id', 'int', 11);
	}

	public function setUp() {
		$this -> setTableName('maps');
		$this -> hasOne('Sync_Facility as S_Facility', array('local' => 'facility_id', 'foreign' => 'id'));
	}

	public function getMap($map_id) {
		$query = Doctrine_Query::create() -> select("*") -> from("maps") -> where("id='$map_id'");
		$items = $query -> execute(array(), Doctrine::HYDRATE_ARRAY);
		return $items[0];
	}

	public function getFacilityMap($facility_id, $period_begin = "") {
		$query = Doctrine_Query::create() -> select("*") -> from("maps") -> where("facility_id='$facility_id'");
		if ($period_begin != "") {
			$query = Doctrine_Query::create() -> select("*") -> from("maps") -> where("facility_id='$facility_id' and period_begin='$period_begin'");
		}
		$items = $query -> execute(array(), Doctrine::HYDRATE_ARRAY);
		return $items;
	}

	public function getNascopPeriod($id_list) {
		$and = "";
		if ($id_list != "") {
			$and = "and id NOT IN($id_list)";
		}
		$query = Doctrine_Query::create() -> select("period_begin") -> from("maps") -> where("code !='F-MAPS' $and") ->orderBy("period_begin DESC") -> groupBy("period_begin");
		$items = $query -> execute(array(), Doctrine::HYDRATE_ARRAY);
		return $items;
	}

	public function getEscmPeriod($id_list) {
		$and = "";
		if ($id_list != "") {
			$and = "and id IN($id_list)";
		}
		$query = Doctrine_Query::create() -> select("period_begin") -> from("maps") -> where("code !='F-MAPS' $and") ->orderBy("period_begin DESC") -> groupBy("period_begin");
		$items = $query -> execute(array(), Doctrine::HYDRATE_ARRAY);
		return $items;
	}

	public function getReportPeriods() {//Only get aggregated maps
		$query = Doctrine_Query::create() -> select("period_begin") -> from("maps") -> where("code ='D-MAPS'") -> groupBy("period_begin")->OrderBy("period_begin desc");
		$items = $query -> execute(array(), Doctrine::HYDRATE_ARRAY);
		return $items;
	}

}
?>
