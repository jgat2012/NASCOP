<?php
class Aggregated_Order extends Doctrine_Record {

	public function setTableDefinition() {
		$this -> hasColumn('aggregated_order_id', 'varchar', 10);
		$this -> hasColumn('child_order_id', 'varchar', 10);
	}//end setTableDefinition

	public function setUp() {
		$this -> setTableName('aggregated_order');
		$this -> hasOne('facility_order as Facility_Order_Object', array('local' => 'aggregated_order_id', 'foreign' => 'id'));
	}//end setUp

	public static function getAggregatedOrder($order) {
		$query = Doctrine_Query::create() -> select("*") -> from("aggregated_order") -> where("child_order_id = '$order'");
		$orders = $query -> execute(array(), Doctrine::HYDRATE_ARRAY);
		return $orders;
	}
	public static function getOrder($order) {
		$query = Doctrine_Query::create() -> select("*") -> from("aggregated_order") -> where("aggregated_order_id = '$order'");
		$order_object = $query -> execute();
		return $order_object;
	}

}//end class
?>