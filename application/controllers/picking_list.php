<?php
class Picking_List extends MY_Controller {
	function __construct() {
		parent::__construct();
	}

	public function index() {
		$data['open_table'] = $this -> get_lists(0);
		$data['closed_table'] = $this -> get_lists(1);
		$data['assign_table'] = $this -> get_orders();
		$data['content_view'] = "picking_lists/picking_v";
		$data['banner_text'] = "Picking Lists";
		$this -> base_params($data);
	}

	public function assign_orders() {
		$list_id = $this -> input -> post("assign_list_id", TRUE);
		$orders = $this -> input -> post("orders", TRUE);
		if (!empty($orders)) {
			foreach ($orders as $order) {
				$sql = "UPDATE cdrr c SET order_id='$list_id' WHERE c.id='$order'";
				$this -> db -> query($sql);
			}
			$this -> session -> set_flashdata('list_message', "Orders Assigned Successfully");
		} else {
			$this -> session -> set_flashdata('list_message', "no orders found");
		}
		redirect("picking_list");
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

	public function get_commodities($cdrr_id) {
		$sql = "SELECT d.id,UPPER(d.drug) as drug,ci.resupply,du.Name as drug_unit 
		        FROM cdrr_item ci
		        LEFT JOIN drugcode d ON d.id=ci.drug_id
		        LEFT JOIN drug_unit du ON du.id=d.unit
		        WHERE ci.cdrr_id='$cdrr_id' 
		        GROUP BY ci.drug_id";
		//include resupply >0
		$query = $this -> db -> query($sql);
		$results = $query -> result();
		return $results;
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
			$links = array("picking_list/view_orders" => "view orders", "picking_list/assign_orders" => "assign orders", "picking_list/update_list" => "update", "picking_list/close_list" => "close", "picking_list/delete_list" => "delete");
		} else if ($type == 1) {
			$links = array("picking_list/view_orders" => "view orders", "picking_list/print_list" => "print list");
		}
		return $this -> showTable($columns, $results, $links);
	}

	public function get_orders() {
		$columns = array('#', '#CDRR-ID', 'Period Beginning', 'Status', 'Facility Name', 'Options');
		$sql = "SELECT c.id,IF(c.code='0',CONCAT('D-CDRR#',c.id),CONCAT('F-CDRR#',c.id)) as cdrr_id,c.period_begin,s.name as status_name,IF(c.code='1',CONCAT(f.name,CONCAT(' ','Dispensing Point')),f.name)as facility_name
				FROM cdrr c
				LEFT JOIN order_status s ON s.id=c.status
				LEFT JOIN facilities f ON f.facilitycode=c.facility_id
				WHERE c.code='0'
				AND s.name LIKE '%approved%'
				AND c.order_id='0'";
		//change to rationalized
		$query = $this -> db -> query($sql);
		$results = $query -> result_array();
		$links = array("order/view_order" => "checkbox");
		return $this -> showTable($columns, $results, $links);
	}

	public function get_orders_list($list_id, $status) {
		$columns = array('#', '#CDRR-ID', 'Period Beginning', 'Facility Name', 'Options');
		$sql = "SELECT c.id,IF(c.code='0',CONCAT('D-CDRR#',c.id),CONCAT('F-CDRR#',c.id)) as cdrr_id,c.period_begin,IF(c.code='1',CONCAT(f.name,CONCAT(' ','Dispensing Point')),f.name)as facility_name
				FROM cdrr c
				LEFT JOIN facilities f ON f.facilitycode=c.facility_id
				WHERE c.order_id='$list_id'
				GROUP BY c.id";
		$query = $this -> db -> query($sql);
		$results = $query -> result_array();
		$links = array("picking_list/view_commodities" => "view commodities", "picking_list/remove_order" => "remove order");
		if ($status == "Closed") {
			$links = array("picking_list/view_commodities" => "view commodities");
		}
		return $this -> showTable($columns, $results, $links);
	}

	public function generatePDF($data) {
		$current_date = date("M d, Y");
		$icon = site_url() . 'assets/img/coat_of_arms-resized.png';
		$html_title = "<div style='width:100px; height:100px; margin:0 auto;'><img src='" . $icon . "' style='width:96px; height:96px;'></img></div>";
		$html_title .= "<h3 style='text-align:center;'>MINISTRY OF HEALTH</h3>";
		$html_title .= "<span style='text-align:left;'>Kenyatta Hospital Grounds, AIDS/TB/Leprosy Division</span><br/>";
		$html_title .= "<span style='text-align:left;'>P.O. Box 19361, Nairobi</span><br/>";
		$html_title .= "<span style='text-align:left;'>Telephone: 020- 2729502/49</span><br/>";
		$html_title .= "<span style='text-align:left;'>Fax: 020 - 2710518</span><br/>";
		$html_title .= "<span style='text-align:left;'>Email: art@lmu.co.ke</span><br/>";
		$html_title .= "<h4 style='text-align:left;'>FROM: ARV Logistics Management Unit, NASCOP</h4>";
		$html_title .= "<h4 style='text-align:left;'>TO: Customer Service, KEMSA</h4>";
		$html_title .= "<h4 style='text-align:left;'>CC: Warehouse manager, KEMSA</h4>";
		$html_title .= "<h4 style='text-align:left;'>Date: $current_date</h4>";
		$html_title .= "<h3 style='text-align:left; text-decoration: underline;'>RESUPPLY OF ARV'S</h3>";
		//Create the footer
		$current_user = $this -> session -> userdata('user_id');
		$user_object = Users::getUserDetail($current_user);
		//retrieve user so as to get their signature
		$html_footer = "<div style='width:100%; position:fixed; bottom:0;'><h4 style='text-align:left;'>Yours Faithfully,</h4><div style='width:160px; height:100px; margin:20px; auto 0 auto;'><img src='" . base_url() . "assets/img/" . $user_object -> Image_Link . "'></img></div>";
		$html_footer .= "<h4 style='text-align:left;'>" . $user_object -> Name . "<br/>" . $user_object -> Access -> Level_Name . "<br/> NASCOP's ARV Logistics Management Unit at Kemsa" . "</h4></div>";
		//echo $html_footer;
		$this -> load -> library('mpdf');
		$this -> mpdf = new mPDF('c', 'A4');
		$this -> mpdf -> SetTitle('Warehouse Picking List');
		$this -> mpdf -> defaultfooterfontsize = 9;
		/* blank, B, I, or BI */
		$this -> mpdf -> defaultfooterline = 1;
		/* 1 to include line below header/above footer */
		$this -> mpdf -> mirrorMargins = 1;
		@$mpdf -> defaultfooterfontstyle = B;
		$this -> mpdf -> SetFooter('Generated on: {DATE d/m/Y}|{PAGENO}|Warehouse Picking List');
		$this -> mpdf -> WriteHTML($html_title);
		$this -> mpdf -> simpleTables = true;
		$this -> mpdf -> WriteHTML($data);
		$this -> mpdf -> WriteHTML($html_footer);
		$report_name = "Warehouse Picking List.pdf";
		$html_title . "\n";
		$data . "\n";
		$this -> mpdf -> Output($report_name, 'D');
	}

	public function print_list($list_id) {
		//style
		$data = "<style>
					table.data-table {
						table-layout: fixed;
						width: 700px;
						border-collapse:collapse;
						border:1px solid black;
					}
					table.data-table td, th {
						width: 100px;
						border: 1px solid black;
					}
					.leftie{
						text-align: left !important;
					}
					.right{
						text-align: right !important;
					}
					.center{
						text-align: center !important;
					}
			    </style>";

		$sql = "SELECT c.id,f.name as facility_name,IF(c.code='0',CONCAT('D-CDRR#',c.id),CONCAT('F-CDRR#',c.id)) as cdrr_id
		        FROM cdrr c
		        LEFT JOIN facilities f ON f.facilitycode=c.facility_id
		        LEFT JOIN picking_list_details p ON p.id=c.order_id
		        WHERE p.id='$list_id'";
		$query = $this -> db -> query($sql);
		$cdrrs = $query -> result();

		foreach ($cdrrs as $cdrr) {
			$data .= '<h5 style="text-align: left">' . $cdrr -> facility_name . ' ' . $cdrr -> cdrr_id . '</h5>';
			$data .= '<table class="data-table"><thead><tr><th>Commodity</th><th>Quantity for Resupply</th><th>Packs/Bottles/Tins</th></tr></thead><tbody>';
			$items = $this -> get_commodities($cdrr -> id);
			foreach ($items as $item) {
				$data .= '<tr><td>' . $item -> drug . '</td><td>' . $item -> resupply . '</td><td>' . $item -> drug_unit . '</td></tr>';
			}
			$data .= '</tbody></table>';
		}

		$this -> generatePDF($data);
	}

	public function remove_order($cdrr_id) {
		$sql = "UPDATE cdrr c 
				SET c.order_id='0'
				WHERE c.id='$cdrr_id'";
		$query = $this -> db -> query($sql);
		$this -> session -> set_flashdata('list_message', "Order removed Successfully");
		redirect("picking_list");
	}

	public function showTable($columns, $data = array(), $links = array()) {
		$this -> load -> library('table');
		$tmpl = array('table_open' => '<table class="dataTables">');
		$this -> table -> set_template($tmpl);
		$this -> table -> set_heading($columns);
		$link_values = "";
		foreach ($data as $mydata) {
			if (@$mydata['timestamp']) {
				$mydata['timestamp'] = date('d-M-Y h:i:s a', $mydata['timestamp']);
			}
			//Set Up links
			foreach ($links as $i => $link) {
				if ($link == "delete") {
					$link_values .= "<a href='" . site_url($i . '/' . $mydata['id']) . "' class='delete link'>$link</a> | ";
				} else if ($link == "remove order") {
					$link_values .= "<a href='" . site_url($i . '/' . $mydata['id']) . "' class='delete link'>$link</a> | ";
				} else if ($link == "print list") {
					$link_values .= "<a href='" . site_url($i . '/' . $mydata['id']) . "' class='link'>$link</a> | ";
				} else if ($link == "update") {
					$link_values .= "<a data-toggle='modal' href='#edit_list' class='update' link_id='" . $mydata['id'] . "' link_name='" . $mydata['name'] . "'>$link</a> | ";
				} else if ($link == "assign orders") {
					$link_values .= "<a data-toggle='modal' href='#assign_orders' assign_id='" . $mydata['id'] . "'  class='assign'>$link</a> | ";
				} else if ($link == "checkbox") {
					$link_values .= "<input type='checkbox' name='orders[]' value='" . $mydata['id'] . "'/>";
				} else if ($link == "view commodities") {
					$link_values .= "<a data-toggle='modal' href='#commodity_list' commodity_list_id='" . $mydata['id'] . "' class='commodity_list'>$link</a> | ";
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

	public function update_list() {
		$list_id = $this -> input -> post("edit_list_id", TRUE);
		$list_name = $this -> input -> post("edit_list_name", TRUE);
		$sql = "UPDATE picking_list_details p 
				SET p.name='$list_name'
				WHERE p.id='$list_id'";
		$query = $this -> db -> query($sql);
		$this -> session -> set_flashdata('list_message', "List Updated Successfully");
		redirect("picking_list");
	}

	public function view_orders($list_id) {
		$sql = "SELECT p.id,u.name as full_name,p.name,p.timestamp,count(c.order_id) as orders_total,iF(p.status=1,'Closed','Open') as status 
		      FROM picking_list_details p
		      LEFT JOIN cdrr c ON c.order_id=p.id
		      LEFT JOIN users u ON u.id=p.created_by
		      WHERE p.id='$list_id'
		      GROUP BY c.id";
		$query = $this -> db -> query($sql);
		$results = $query -> result();
		$data['list'] = $results[0];
		if (@$results[0] -> status == 'Closed') {
			$this -> session -> set_userdata("order_go_back", "fmaps");
		} else {
			$this -> session -> set_userdata("order_go_back", "cdrr");
		}
		$data['orders_table'] = $this -> get_orders_list($list_id, @$results[0] -> status);
		$data['page_title'] = 'Picking List Details';
		$data['content_view'] = "picking_lists/picking_template";
		$data['banner_text'] = "Picking List Details";
		$this -> base_params($data);
	}

	public function view_commodities($cdrr_id) {
		$columns = array('#', 'Commodity', 'Quantity for Resupply', 'Packs/Bottles/Tins');
		$sql = "SELECT d.id,UPPER(d.drug) as drug,ci.resupply,du.Name as drug_unit 
		        FROM cdrr_item ci
		        LEFT JOIN drugcode d ON d.id=ci.drug_id
		        LEFT JOIN drug_unit du ON du.id=d.unit
		        WHERE ci.cdrr_id='$cdrr_id' 
		        GROUP BY ci.drug_id";
		//include resupply >0
		$query = $this -> db -> query($sql);
		$results = $query -> result_array();
		echo $this -> showTable($columns, $results);
	}

	public function base_params($data) {
		$data['title'] = "Warehouse Picking Lists";
		$this -> load -> view('template', $data);
	}

}
