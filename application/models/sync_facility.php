<?php
class Sync_Facility extends Doctrine_Record {

	public function setTableDefinition() {
		$this -> hasColumn('name', 'varchar', 255);
		$this -> hasColumn('code', 'varchar', 15);
		$this -> hasColumn('category', 'varchar', 15);
		$this -> hasColumn('sponsors', 'varchar', 255);
		$this -> hasColumn('services', 'varchar', 255);
		$this -> hasColumn('manager_id', 'int', 11);
		$this -> hasColumn('district_id', 'int', 11);
		$this -> hasColumn('address_id', 'int', 11);
		$this -> hasColumn('parent_id', 'int', 11);
		$this -> hasColumn('ordering', 'tinyint', 1);
		$this -> hasColumn('affiliation', 'varchar', 255);
		$this -> hasColumn('service_point', 'tinyint', 1);
		$this -> hasColumn('county_id', 'int', 11);
		$this -> hasColumn('hcsm_id', 'int', 11);
		$this -> hasColumn('location', 'varchar', 255);
		$this -> hasColumn('affiliate_organization_id', 'int', 11);

	}

	public function setUp() {
		$this -> setTableName('sync_facility');
	}

	public function getAll() {
		$query = Doctrine_Query::create() -> select("*") -> from("sync_facility");
		$sync_facility = $query -> execute(array(), Doctrine::HYDRATE_ARRAY);
		return $sync_facility;
	}

	public function getId($facility_code, $status_code = 0) {
		if ($status_code == 0) {
			$conditions = "code='$facility_code' and ordering='1'";
		} else if ($status_code == 3) {
			$conditions = "code='$facility_code' and category like '%standalone%'";
		} else {
			$conditions = "code='$facility_code' and service_point='1'";
		}

		$query = Doctrine_Query::create() -> select("id") -> from("sync_facility") -> where("$conditions");
		$sync_facility = $query -> execute(array(), Doctrine::HYDRATE_ARRAY);
		return @$sync_facility[0];
	}

	public function getCode($facility_id, $status_code = 0) {
		if ($status_code == 0) {
			$conditions = "id='$facility_id' and ordering='1'";
		} else {
			$conditions = "id='$facility_id' and service_point='1'";
		}

		$query = Doctrine_Query::create() -> select("code") -> from("sync_facility") -> where("$conditions");
		$sync_facility = $query -> execute(array(), Doctrine::HYDRATE_ARRAY);
		return @$sync_facility[0];
	}

}
?>

