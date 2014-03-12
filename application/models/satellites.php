<?php
class Satellites extends Doctrine_Record {

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
		$this -> setTableName('satellites');
		$this -> hasOne('District as Parent_District', array('local' => 'district_id', 'foreign' => 'id'));
		$this -> hasOne('Counties as County', array('local' => 'county_id', 'foreign' => 'id'));
		$this -> hasMany('Adt_Sites as adt', array('local' => 'id', 'foreign' => 'facility_id'));

	}

	public function getAll() {
		$query = Doctrine_Query::create() -> select("*") -> from("satellites") -> orderBy("name asc");
		$facility = $query -> execute();
		return $facility;
	}

	public function getAllHydrated() {
		$query = Doctrine_Query::create() -> select("*") -> from("satellites") -> orderBy("name asc");
		$facility = $query -> execute(array(), Doctrine::HYDRATE_ARRAY);
		return $facility;
	}
	
	public function getTotal() {
		$query = Doctrine_Query::create() -> select("COUNT(*) as total") -> from("satellites") -> orderBy("name asc");
		$facility = $query -> execute(array(), Doctrine::HYDRATE_ARRAY);
		return $facility[0]['total'];
	}

}
?>

