<?php
class Adt_Sites extends Doctrine_Record {

	public function setTableDefinition() {
		$this -> hasColumn('facility_id', 'int', 11);
		$this -> hasColumn('pipeline', 'int', 2);
	}

	public function setUp() {
		$this -> setTableName('adt_sites');
		$this -> hasOne('Sync_Facility as S_Facility', array('local' => 'facility_id', 'foreign' => 'id'));
		$this -> hasOne('Escm_Facility as E_Facility', array('local' => 'facility_id', 'foreign' => 'id'));
		$this -> hasOne('Satellites as Satellite', array('local' => 'facility_id', 'foreign' => 'id'));
	}

	public function getAll() {
		$query = Doctrine_Query::create() -> select("*") -> from("adt_sites");
		$sites = $query -> execute();
		return $sites;
	}

	public function getSite($facility_id) {
		$query = Doctrine_Query::create() -> select("*") -> from("adt_sites") -> where("facility_id='$facility_id'");
		$sites = $query -> execute(array(), Doctrine::HYDRATE_ARRAY);
		return $sites;
	}

}
