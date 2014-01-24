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
	}

	public function getAll() {
		$query = Doctrine_Query::create() -> select("*") -> from("escm_facility");
		$sync_facility = $query -> execute(array(), Doctrine::HYDRATE_ARRAY);
		return $sync_facility;
	}

}
?>

