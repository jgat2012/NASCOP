<?php
class Escm_Facility extends Doctrine_Record {

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
		$this -> setTableName('escm_facility');
		$this -> hasOne('District as Parent_District', array('local' => 'district_id', 'foreign' => 'id'));
		$this -> hasOne('Counties as County', array('local' => 'county_id', 'foreign' => 'id'));
	}

	public function getAll() {
		$query = Doctrine_Query::create() -> select("f.*,d.name as district,c.county as county_name") -> from("escm_facility f") -> leftJoin('f.Parent_District d, f.County c');
		$escm_facility = $query -> execute(array(), Doctrine::HYDRATE_ARRAY);
		return $escm_facility;
	}

	public function getAllNotHydrated() {
		$query = Doctrine_Query::create() -> select("*") -> from("escm_facility") -> orderBy("name asc");
		$escm_facility = $query -> execute();
		return $escm_facility;
	}
	public function getTotalNumberCounty($county) {
		$query = Doctrine_Query::create() -> select("COUNT(*) as Total_Facilities") -> from("escm_facility") -> where("county_id = '$county'");
		$count = $query -> execute();
		return $count[0] -> Total_Facilities;
	}
	

	public function getAllHydrated() {
		$query = Doctrine_Query::create() -> select("*") -> from("escm_facility") -> orderBy("name asc");;
		$escm_facility = $query -> execute(array(), Doctrine::HYDRATE_ARRAY);
		return $escm_facility;
	}
	
	public function getOrderingPoint(){
		$query = Doctrine_Query::create() -> select("*") -> from("escm_facility")-> where("ordering='1'") -> orderBy("name asc");;
		$escm_facility = $query -> execute(array(), Doctrine::HYDRATE_ARRAY);
		return $escm_facility;
	}

	public function getFacilityCode($facility_id) {
		$query = Doctrine_Query::create() -> select("code") -> from("escm_facility") -> where("id ='$facility_id'");
		$escm_facility = $query -> execute();
		return $escm_facility[0];
	}
	
	public function getFacilityId($facility_name) {
		$query = Doctrine_Query::create() -> select("id") -> from("escm_facility") -> where("name LIKE  '%$facility_name%'");
		$escm_facility = $query -> execute();
		return $escm_facility[0];
	}
	
	public function getCode($facility_id, $status_code = 0) {
		if ($status_code == 0) {
			$conditions = "id='$facility_id' and ordering='1'";
		} else if ($status_code == 3) {
			$conditions = "id='$facility_id' and category like '%standalone%'";
		} else {
			$conditions = "id='$facility_id' and service_point='1'";
		}

		$query = Doctrine_Query::create() -> select("code") -> from("escm_facility") -> where("$conditions");
		$sync_facility = $query -> execute(array(), Doctrine::HYDRATE_ARRAY);
		return @$sync_facility[0];
	}
}
?>

