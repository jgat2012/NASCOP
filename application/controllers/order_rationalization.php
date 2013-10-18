<?php
class Order_Rationalization extends MY_Controller {
	function __construct() {
		parent::__construct();
		$this -> load -> library('pagination');
		date_default_timezone_set('Africa/Nairobi');
	}

	public function index() {
		$this -> submitted_orders();
	}

	public function submitted_orders($status = 0, $offset = 0) {
		if ($status == 0) {
			$data['page_title'] = "Pending Orders";
			$data['days_pending'] = "Approval";
		} elseif ($status == 1) {
			$data['page_title'] = "Approved Orders";
			$data['days_pending'] = "Dispatched";
		} elseif ($status == 2) {
			$data['page_title'] = "Declined Orders";
			$data['days_pending'] = "Resubmission";
		} elseif ($status == 3) {
			$data['page_title'] = "Dispatched Orders";
			$data['days_pending'] = "Delivery";
		}
		$items_per_page = 20;
		$number_of_orders = Facility_Order::getTotalNumber($status);
		$orders = Facility_Order::getPagedOrders($offset, $items_per_page, $status);
		if ($number_of_orders > $items_per_page) {
			$config['base_url'] = base_url() . "order_rationalization/submitted_orders/" . $status . "/";
			$config['total_rows'] = $number_of_orders;
			$config['per_page'] = $items_per_page;
			$config['uri_segment'] = 4;
			$config['num_links'] = 5;
			$this -> pagination -> initialize($config);
			$data['pagination'] = $this -> pagination -> create_links();
		}

		$data['orders'] = $orders;
		$data['quick_link'] = $status;
		$data['content_view'] = "view_orders_rat_v";
		//If the orders being viewed are approved, display the view for generating the picking list
		if ($status == 1) {
			$data['content_view'] = "view_approved_orders_rat_v";
		}
		$data['banner_text'] = "Submitted Orders";
		//get all submitted orders that have not been rationalized (fresh orders)
		$this -> base_params($data);
	}

	public function rationalize_order($order) {
		//First retrieve the order and its particulars from the database
		$data = array();
		$data['order_no'] = $order;
		$data['hide_side_menu'] = 1;
		$data['order_details_page'] = 'edit_order';
		$data['order_details'] = Facility_Order::getOrder($order);
		$order = $data['order_details'] -> Unique_Id;
		$data['commodities'] = Cdrr_Item::getOrderItems($order);
		$data['regimens'] = Maps_Item::getOrderItems($order);
		$data['comments'] = Order_Comment::getOrderComments($order);
		$data['content_view'] = "rationalize_order_v";
		$data['banner_text'] = "Order Particulars";
		$order_type = $data['order_details']['Code'];
		$data['order_type'] = $order_type;
		//get all submitted orders that have not been rationalized (fresh orders)
		$this -> base_params($data);
	}

	public function save() {
		//save the changes made and change the status
		$updated_on = date("U");
		$user_id = $this -> session -> userdata('full_name');
		$opening_balances = $this -> input -> post('opening_balance');
		$quantities_received = $this -> input -> post('quantity_received');
		$quantities_dispensed = $this -> input -> post('quantity_dispensed');
		$losses = $this -> input -> post('losses');
		$adjustments = $this -> input -> post('adjustments');
		$physical_count = $this -> input -> post('physical_count');
		$expiry_quantity = $this -> input -> post('expiry_quantity');
		$expiry_date = $this -> input -> post('expiry_date');
		$out_of_stock = $this -> input -> post('out_of_stock');
		$resupply = $this -> input -> post('resupply');
		$newresupply = $this -> input -> post('newresupply');
		$commodities = $this -> input -> post('commodity');
		$regimens = $this -> input -> post('patient_regimens');
		$patient_numbers = $this -> input -> post('patient_numbers');
		$mos = $this -> input -> post('mos');
		$comments = $this -> input -> post('comments');
		$o_comments = $this -> input -> post('o_comments');
		$transaction_type = $this -> input -> post('transaction_type');
		$unique_id = $this -> input -> post("unique_id");
		//$approve_order = $this -> input -> post('approve_order');
		//$decline_order = $this -> input -> post('decline_order');
		$order_number = $this -> input -> post('order_number');
		$commodity_counter = 0;
		$regimen_counter = 0;
		//url to redirect after saving the record
		$url = "";
		//retrieve the order that is being edited
		$order_object = Facility_Order::getOrder($order_number);
		$status = 0;
		//Check which button was pressed

		if (isset($transaction_type)) {
			if ($transaction_type == 'approved') {
				$url = "order_rationalization/submitted_orders/1";
				$status = 1;
				$this -> session -> set_userdata('msg_success', 'Order was Approved');
			} elseif ($transaction_type == 'declined') {
				$url = "order_rationalization/submitted_orders/2";
				$status = 2;
				$this -> session -> set_userdata('msg_error', 'Order was Declined');
			}

			$order_object -> Status = $status;
			$order_object -> Updated = $updated_on;
			$order_object -> Is_Uploaded =0;
			//code = 0 i.e. fcdrr
			$order_object -> Code = 1;
			//Only for dcdrrs
			/*$order_object->Reports_Expected = 0;
			 $order_object->Reports_Actual = 0;*/
			$order_object -> save();
			//$order_id = $order_object -> id;
			//Now save the comment that has been made
			$facility = "nascop";
			if ($comments != null) {
				if ($o_comments == $comments) {
					$old_comments = Order_Comment::getOrderComments($unique_id);
					foreach ($old_comments as $old_comment) {
						$old_comment -> delete();
					}
					$sql = "select max(id)as last from order_comment";
					$query = $this -> db -> query($sql);
					$results = $query -> result_array();
					$last_id = $results[0]['last'];
					$last_id++;
				}

				$order_comment = new Order_Comment();
				$order_comment -> Order_Number = $unique_id;
				$order_comment -> Timestamp = date('U');
				$order_comment -> User = $user_id;
				$order_comment -> Comment = $comments;
				$order_comment -> Unique_Id = md5($last_id . $facility);
				$order_comment -> save();
			}
			//Now save the cdrr items
			$commodity_counter = 0;
			if ($commodities != null) {
				foreach ($commodities as $commodity) {
					//First check if any quantitites are required for resupply to avoid empty entriesv
					$cdrr_item = Cdrr_Item::getItem($commodity);
					$cdrr_item -> Balance = $opening_balances[$commodity_counter];
					$cdrr_item -> Received = $quantities_received[$commodity_counter];
					$cdrr_item -> Dispensed_Units = $quantities_dispensed[$commodity_counter];
					//For fcdrr, packs are not used.
					//$cdrr_item->Dispensed_Packs = $opening_balances[$commodity_counter];
					$cdrr_item -> Losses = $losses[$commodity_counter];
					$cdrr_item -> Adjustments = $adjustments[$commodity_counter];
					$cdrr_item -> Count = $physical_count[$commodity_counter];
					$cdrr_item -> Resupply = $newresupply[$commodity_counter];
					$cdrr_item -> Newresupply = $resupply[$commodity_counter];
					//The following not required for fcdrrs
					/*$cdrr_item->Aggr_Consumed = $opening_balances[$commodity_counter];
					 $cdrr_item->Aggr_On_Hand = $opening_balances[$commodity_counter];
					 $cdrr_item->Publish = $opening_balances[$commodity_counter];*/
					$cdrr_item -> Cdrr_Id = $unique_id;
					$cdrr_item -> save();
					$commodity_counter++;
				}
			}
			//Save the maps details
			$maps_id = $order_object -> id;
			if ($regimens != null) {
				foreach ($regimens as $regimen) {
					//Check if any patient numbers have been reported for this regimen
					if ($patient_numbers[$regimen_counter] > 0) {
						$maps_item = Maps_Item::getItem($regimen);
						$maps_item -> Total = $patient_numbers[$regimen_counter];
						$maps_item -> Maps_Id = $unique_id;
						$maps_item -> save();
					}
					$regimen_counter++;
				}
			}
			redirect($url);
		}

	}

	public function base_params($data) {
		$data['title'] = "Commodity Orders";
		$data['_type'] = 'order';
		$data['link'] = "order_management";
		$this -> load -> view('template', $data);
	}

}
?>