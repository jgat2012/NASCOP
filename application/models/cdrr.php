<?php
class Cdrr extends Doctrine_Record {

	public function setTableDefinition() {
		$this -> hasColumn('status', 'varchar', 20);
		$this -> hasColumn('created', 'datetime');
		$this -> hasColumn('updated', 'datetime');
		$this -> hasColumn('code', 'varchar', 15);
		$this -> hasColumn('period_begin', 'date');
		$this -> hasColumn('period_end', 'date');
		$this -> hasColumn('comments', 'text');
		$this -> hasColumn('reports_expected', 'int', 11);
		$this -> hasColumn('reports_actual', 'int', 11);
		$this -> hasColumn('services', 'varchar', 255);
		$this -> hasColumn('sponsors', 'varchar', 255);
		$this -> hasColumn('non_arv', 'int', 11);
		$this -> hasColumn('delivery_note', 'varchar', 255);
		$this -> hasColumn('order_id', 'int', 11);
		$this -> hasColumn('facility_id', 'int', 11);
	}

	public function setUp() {
		$this -> setTableName('cdrr');
		$this -> hasOne('Sync_Facility as Facility', array('local' => 'facility_id', 'foreign' => 'id'));
	}

	public function getAll() {
		$query = Doctrine_Query::create() -> select("*") -> from("cdrr");
		$cdrrs = $query -> execute();
		return $cdrrs;
	}

	public function getOrders($start, $end) {
		$query = Doctrine_Query::create() -> select("*") -> from("cdrr") -> where("period_begin='$start' and period_end='$end' and(code='D-CDRR' or code='F-CDRR_packs')");
		$cdrrs = $query -> execute();
		return $cdrrs;
	}

	public function getFacilities($start, $end, $id_list) {
		$and = "";
		if ($id_list != "") {
			$and = "and id NOT IN($id_list)";
		}
		$query = Doctrine_Query::create() -> select("*") -> from("cdrr") -> where("period_begin='$start' and period_end='$end' and(code='D-CDRR' or code='F-CDRR_packs') $and") -> groupBy("facility_id");
		$items = $query -> execute();
		return $items;
	}

	public function getStatus($start, $end, $id_list) {
		$and = "";
		if ($id_list != "") {
			$and = "and id NOT IN($id_list)";
		}
		$query = Doctrine_Query::create() -> select("status,count(status) as total") -> from("cdrr") -> where("period_begin='$start' and period_end='$end' and(code='D-CDRR' or code='F-CDRR_packs') $and") -> groupBy("status");
		$items = $query -> execute();
		return $items;
	}

	public function getNascopPeriod($id_list) {
		$and = "";
		if ($id_list != "") {
			$and = "and id NOT IN($id_list)";
		}
		$query = Doctrine_Query::create() -> select("period_begin") -> from("cdrr") -> where("code !='F-CDRR_units' $and") -> groupBy("period_begin");
		$items = $query -> execute(array(), Doctrine::HYDRATE_ARRAY);
		return $items;
	}

	public function getEscmPeriod($id_list) {
		$and = "";
		if ($id_list != "") {
			$and = "and id IN($id_list)";
		}
		$query = Doctrine_Query::create() -> select("period_begin") -> from("cdrr") -> where("code !='F-CDRR_units' $and") -> groupBy("period_begin");
		$items = $query -> execute(array(), Doctrine::HYDRATE_ARRAY);
		return $items;
	}

	public function getCdrr($cdrr_id) {
		$query = Doctrine_Query::create() -> select("*") -> from("cdrr") -> where("id='$cdrr_id'");
		$items = $query -> execute(array(), Doctrine::HYDRATE_ARRAY);
		return $items[0];
	}

}
?>