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
		$this -> hasOne('District as Parent_District', array('local' => 'district_id', 'foreign' => 'id'));
		$this -> hasOne('Counties as County', array('local' => 'county_id', 'foreign' => 'id'));
		$this -> hasMany('Adt_Sites as adt', array('local' => 'id', 'foreign' => 'facility_id'));

	}

	public function getAll() {
		$query = Doctrine_Query::create() -> select("f.*,d.name as district,c.county as county_name,IF(sites.facility_id is null,'NO','YES') as adt_installed") -> from("sync_facility f") -> leftJoin('f.Parent_District d, f.County c,f.adt sites');
		$sync_facility = $query -> execute(array(), Doctrine::HYDRATE_ARRAY);
		return $sync_facility;
	}

	public function getAllNotHydrated() {
		$query = Doctrine_Query::create() -> select("*") -> from("sync_facility") -> orderBy("name asc");
		$sync_facility = $query -> execute();
		return $sync_facility;
	}

	public function getAllHydrated() {
		$query = Doctrine_Query::create() -> select("*") -> from("sync_facility")->orderBy("name asc");
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
	
	public function getTotalNumberCounty($county) {
		$query = Doctrine_Query::create() -> select("COUNT(*) as Total_Facilities") -> from("sync_facility") -> where("county_id = '$county'");
		$count = $query -> execute();
		return $count[0] -> Total_Facilities;
	}

	public function getCode($facility_id, $status_code = 0) {
		if ($status_code == 0) {
			$conditions = "id='$facility_id' and ordering='1'";
		} else if ($status_code == 3) {
			$conditions = "id='$facility_id' and category like '%standalone%'";
		} else {
			$conditions = "id='$facility_id' and service_point='1'";
		}

		$query = Doctrine_Query::create() -> select("code") -> from("sync_facility") -> where("$conditions");
		$sync_facility = $query -> execute(array(), Doctrine::HYDRATE_ARRAY);
		return @$sync_facility[0];
	}
	
	public function getFacilityId($mfl_code) {
		$sql = "SELECT id FROM sync_facility WHERE code ='$mfl_code'";
		$query = $this ->db ->query($sql);
		$result = $query ->result_array();
		$facility_id = $result[0]['id'];
		return $facility_id;
	}

}
?>

