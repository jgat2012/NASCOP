<?php
class Access_Log extends Doctrine_Record {

	public function setTableDefinition() {
		$this -> hasColumn('machine_code','varchar',150);
		$this -> hasColumn('ip_address', 'text');
		$this -> hasColumn('location','varchar',150);
		$this -> hasColumn('user_id','varchar',150);
		$this -> hasColumn('timestamp','varchar',150);
		$this -> hasColumn('facility_code','varchar',150);
		$this -> hasColumn('access_type','varchar',150);
	}

	public function setUp() {
		$this -> setTableName('access_log');
	}

	public function getAll() {
		$query = Doctrine_Query::create() -> select("*") -> from("access_log");
		$sync_log = $query -> execute();
		return $sync_log;
	}
	


}
