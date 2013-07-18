<?php
class Pipeline_Stock extends Doctrine_Record {

	public function setTableDefinition() {
		$this -> hasColumn('id', 'int', 255);
		$this -> hasColumn('commodity_id', 'varchar', 255);
		$this -> hasColumn('total_issued', 'varchar', 255);
		$this -> hasColumn('consumption', 'varchar', 255);
		$this -> hasColumn('stock_on_hand', 'varchar',255);
		$this -> hasColumn('earliest_expiry_date', 'varchar', 255);
		$this -> hasColumn('quantity_of_stock_expiring', 'varchar', 255);
		$this -> hasColumn('central_site_stock_on_hand', 'varchar', 255);
		$this -> hasColumn('total_stock_in_country', 'varchar', 255);
		$this -> hasColumn('mos_on_hand_pipeline', 'varchar', 255);
		$this -> hasColumn('mos_on_hand_central_sites', 'varchar', 255);
		$this -> hasColumn('mos_on_hand_total', 'varchar', 255);
		$this -> hasColumn('quantity_on_order_from_suppliers', 'varchar', 255);
		$this -> hasColumn('source', 'varchar', 255);
		$this -> hasColumn('expected_delivery_date', 'varchar',255);
		$this -> hasColumn('receipts_or_transfers', 'varchar', 255);
		$this -> hasColumn('comments_or_actions', 'varchar', 255);
		$this -> hasColumn('upload_date', 'varchar', 255);
		$this -> hasColumn('pipeline_id', 'varchar', 255);

		
	}

	public function setUp() {
		$this -> setTableName('pipeline_stock');
	}

	public function getAll() {
		$query = Doctrine_Query::create() -> select("*") -> from("pipeline_stock");
		$drugunits = $query -> execute();
		return $pipeline_stock;
	}

	public function getTotalNumber() {
		$query = Doctrine_Query::create() -> select("count(*) as Total") -> from("pipeline_stock");
		$total = $query -> execute();
		return $total[0]['Total'];
	}

	public function getPagedDrugUnits($offset, $items) {
		$query = Doctrine_Query::create() -> select("*") -> from("pipeline_stock") -> offset($offset) -> limit($items);
		$drug_units = $query -> execute();
		return $pipeline_stock;
	}
	/*
	public function add($commodity_id,$total_issued,$consumption,$stock_on_hand,$earliest_expiry_date,$quantity_of_stock_expiring,$central_site_stock_on_hand,$total_stock_in_country,$mos_on_hand_pipeline,$mos_on_hand_central_sites,$mos_on_hand_total,$quantity_on_order_from_suppliers,$source,$expected_delivery_date,$receipts_or_transfers,$comments_or_actions,$upload_date,$pipeline_id){
		
		$new_pipeline = new Pipeline_Stock();
		$data = array("id" => 'NULL', "commodity_id" => $commodity_id,"total_issued"=>$total_issued,"consumption"=>$consumption, "stock_on_hand" => $stock_on_hand, "earliest_expiry_date" => $earliest_expiry_date,"quantity_of_stock_expiring"=>$quantity_of_stock_expiring,"central_site_stock_on_hand"=>$central_site_stock_on_hand, "total_stock_in_country" => $total_stock_in_country, "mos_on_hand_pipeline" => $mos_on_hand_pipeline,"mos_on_hand_central_sites"=>$mos_on_hand_central_sites,"mos_on_hand_total"=>$mos_on_hand_total,"quantity_on_order_from_suppliers"=>$quantity_on_order_from_suppliers,"source"=>$source,"expected_delivery_date"=>$expected_delivery_date,"receipts_or_transfers"=>$receipts_or_transfers,"comments_or_actions"=>$comments_or_actions,"upload_date"=>$upload_date,"pipeline_id"=>$pipeline_id);
		$new_pipeline -> fromArray($data);
		$new_pipeline -> save();

	}
   */

}
