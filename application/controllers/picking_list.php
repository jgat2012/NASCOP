<?php
class Picking_List extends MY_Controller {
	function __construct() {
		parent::__construct();
	}

	public function index() {
		//$lists = Picking_List_Details::getPagedLists($offset, $items_per_page, $status);
		$data['open_table'] = $this -> get_lists(0);
		$data['closed_table'] = $this -> get_lists(1);
		$data['content_view'] = "picking_lists/picking_v";
		$data['banner_text'] = "Picking Lists";
		$this -> base_params($data);
	}

	public function create_list() {
		$list_name = $this -> input -> post("list_name", TRUE);
		$picking_array = array();
		$picking_array['name'] = $list_name;
		$picking_array['timestamp'] = date('U');
		$picking_array['created_by'] = $this -> session -> userdata("user_id");
		$picking_array['status'] = 0;
		$this -> db -> insert("picking_list_details", $picking_array);
		$this -> session -> set_flashdata('list_message', "List Created Successfully");
		redirect("picking_list");
	}

	public function close_list($list_id) {
		$sql = "UPDATE picking_list_details p 
				SET p.status='1'
				WHERE p.id='$list_id'";
		$query = $this -> db -> query($sql);
		$this -> session -> set_flashdata('list_message', "List Closed Successfully");
		redirect("picking_list");
	}

	public function delete_list($list_id) {
		$sql = "UPDATE picking_list_details p 
				SET p.active='0'
				WHERE p.id='$list_id'";
		$query = $this -> db -> query($sql);
		$this -> session -> set_flashdata('list_message', "List Deleted Successfully");
		redirect("picking_list");
	}

	public function get_lists($type = 0) {
		$sql = "SELECT p.id,CONCAT('P-LIST#',p.id) as list_label,p.name,u.Name as full_name,p.timestamp,COUNT(c.order_id) as order_no
		        FROM picking_list_details p
		        LEFT JOIN users u ON u.id=p.created_by
		        LEFT JOIN cdrr c ON c.order_id=p.id
		        WHERE p.status='$type'
		        AND p.active='1'
		        GROUP BY p.id";
		$query = $this -> db -> query($sql);
		$results = $query -> result_array();
		$columns = array('#', '#ID', 'List Name', 'Created By', 'Created On', 'No. of Orders', 'Options');
		if ($type == 0) {
			$links = array("picking_list/view_order" => "view orders", "picking_list/assign_orders" => "assign orders", "picking_list/update_list" => "update", "picking_list/close_list" => "close", "picking_list/delete_list" => "delete");
		} else if ($type == 1) {
			$links = array("picking_list/view_order" => "view orders", "picking_list/print_list" => "print list");
		}
		return $this -> showTable($columns, $results, $links);
	}

	public function showTable($columns, $data = array(), $links = array()) {
		$this -> load -> library('table');
		$tmpl = array('table_open' => '<table class="dataTables">');
		$this -> table -> set_template($tmpl);
		$this -> table -> set_heading($columns);
		$link_values = "";
		foreach ($data as $mydata) {
			if ($mydata['timestamp']) {
				$mydata['timestamp'] = date('d-M-Y h:i:s a', $mydata['timestamp']);
			}
			//Set Up links
			foreach ($links as $i => $link) {
				if ($link == "delete") {
					$link_values .= "<a href='" . site_url($i . '/' . $mydata['id']) . "' class='delete link'>$link</a> | ";
				} else if ($link == "print list") {
					$link_values .= "<a href='" . site_url($i . '/' . $mydata['id']) . "' class='link'>$link</a> | ";
				} else {
					$link_values .= "<a href='" . site_url($i . '/' . $mydata['id']) . "'>$link</a> | ";
				}
			}
			$mydata['Options'] = rtrim($link_values, " | ");
			$link_values = "";
			unset($mydata['id']);
			$this -> table -> add_row($mydata);
		}
		return $this -> table -> generate();
	}

	public function base_params($data) {
		$data['title'] = "Warehouse Picking Lists";
		$this -> load -> view('template', $data);
	}

}
