<?php
class Picking_List extends MY_Controller {
	function __construct() {
		parent::__construct();
	}

	public function index() {
		$data['open_table'] = $this -> get_lists(0);
		$data['closed_table'] = $this -> get_lists(1);
		$data['assign_table'] = $this -> get_orders();
		$data['mail_lists'] = $this -> getMailList();
		$data['content_view'] = "picking_lists/picking_v";
		$data['banner_text'] = "MEMOs";
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
		$list_orders = Picking_List_Details::getListItemCount($list_id);
		if ($list_orders > 0) {
			$sql = "UPDATE picking_list_details p 
				SET p.status='1'
				WHERE p.id='$list_id'";
			$query = $this -> db -> query($sql);
			$this -> session -> set_flashdata('list_message', "List Closed Successfully");
			$this -> session -> set_userdata("order_go_back", "fmaps");
		} else {
			$this -> session -> set_flashdata('list_message', "List Closing Failed");
			$this -> session -> set_userdata("order_go_back", "cdrr");
		}
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
		$sql = "SELECT sd.id,CONCAT_WS('] ',CONCAT_WS(' [',name,abbreviation),CONCAT_WS(' ',strength,formulation)) as drug,unit as drug_unit,ci.resupply
			        FROM cdrr_item ci
			        LEFT JOIN sync_drug sd ON sd.id=ci.drug_id
			        WHERE ci.cdrr_id='$cdrr_id'
			        AND ci.resupply !='0'
			        AND(sd.category_id='1' OR sd.category_id='2' OR sd.category_id='3')";
		//include resupply >0
		$query = $this -> db -> query($sql);
		$results = $query -> result();
		return $results;
	}

	public function get_lists($type = 0) {
		$sql = "SELECT p.id,CONCAT('MEMO#',p.id) as list_label,p.name,u.Name as full_name,p.timestamp,COUNT(c.order_id) as order_no
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
			$links = array("picking_list/view_orders" => "view orders", "picking_list/print_list" => "print memo", "picking_list/mail_list" => "send memo");
		}
		return $this -> showTable($columns, $results, $links);
	}

	public function get_orders() {
		$columns = array('#', '#CDRR-ID', 'Period Beginning', 'Status', 'Facility Name', 'Options');
		$sql = "SELECT c.id,IF(c.code='D-CDRR',CONCAT('D-CDRR#',c.id),CONCAT('F-CDRR#',c.id)) as cdrr_id,c.period_begin,c.status as status_name,IF(c.code='1',CONCAT(f.name,CONCAT(' ','Dispensing Point')),f.name)as facility_name
				FROM cdrr c
				LEFT JOIN sync_facility sf ON sf.id=c.facility_id
				LEFT JOIN facilities f ON f.facilitycode=sf.code
				LEFT JOIN maps m ON sf.id=m.facility_id
				WHERE m.facility_id=c.facility_id
				AND (c.code='D-CDRR' OR c.code='F-CDRR_packs')
				AND m.period_begin=c.period_begin
				AND m.period_end=c.period_end
				AND c.status='dispatched'
				AND c.order_id='0'";
		$query = $this -> db -> query($sql);
		$results = $query -> result_array();
		$links = array("order/view_order" => "checkbox");
		return $this -> showTable($columns, $results, $links);
	}

	public function get_orders_list($list_id, $status) {
		$columns = array('#', '#CDRR-ID', 'Period Beginning', 'Facility Name', 'Options');
		$sql = "SELECT c.id,IF(c.code='D-CDRR',CONCAT('D-CDRR#',c.id),CONCAT('F-CDRR#',c.id)) as cdrr_id,c.period_begin,sf.name as facility_name
				FROM cdrr c
				LEFT JOIN sync_facility sf ON sf.id=c.facility_id
				LEFT JOIN facilities f ON f.facilitycode=sf.code
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

	public function generatePDF($data, $type = 0) {
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
		$this -> mpdf -> SetFooter('Generated on: {DATE d/m/Y}|{PAGENO}|Warehouse Memo');
		$this -> mpdf -> WriteHTML($html_title);
		$this -> mpdf -> simpleTables = true;
		$this -> mpdf -> WriteHTML($data);
		$this -> mpdf -> WriteHTML($html_footer);
		$dir = "Export/";
		$report_name = $dir . "Warehouse Memo.pdf";
		$html_title . "\n";
		$data . "\n";

		/*Delete all files in export folder*/
		if (is_dir($dir)) {
			$files = scandir($dir);
			foreach ($files as $object) {
				if ($object != "." && $object != "..") {
					unlink($dir . "/" . $object);
				}
			}
		} else {
			mkdir($dir);
		}

		$this -> mpdf -> Output($report_name, 'F');
		if ($type == 0) {
			redirect($report_name);
		} else {
			return $report_name;
		}
	}

	public function print_list($list_id, $type = 0) {
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

		$sql = "SELECT c.id,sf.name as facility_name,IF(c.code='D-CDRR',CONCAT('D-CDRR#',c.id),CONCAT('F-CDRR#',c.id)) as cdrr_id
		        FROM cdrr c
		        LEFT JOIN sync_facility sf ON sf.id=c.facility_id
		        LEFT JOIN facilities f ON f.facilitycode=sf.code
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
		if ($type == 0) {
			$this -> generatePDF($data, $type);
		} else {
			$file_name = $this -> generatePDF($data, $type);
			return $file_name;
		}
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
				} else if ($link == "print memo") {
					$link_values .= "<a href='" . site_url($i . '/' . $mydata['id']) . "' target='_blank' class='link'>$link</a> | ";
				} else if ($link == "update") {
					$link_values .= "<a data-toggle='modal' href='#edit_list' class='update' link_id='" . $mydata['id'] . "' link_name='" . $mydata['name'] . "'>$link</a> | ";
				} else if ($link == "assign orders") {
					$link_values .= "<a data-toggle='modal' href='#assign_orders' assign_id='" . $mydata['id'] . "'  class='assign'>$link</a> | ";
				} else if ($link == "checkbox") {
					$link_values .= "<input type='checkbox' name='orders[]' value='" . $mydata['id'] . "'/>";
				} else if ($link == "view commodities") {
					$link_values .= "<a data-toggle='modal' href='#commodity_list' commodity_list_id='" . $mydata['id'] . "' class='commodity_list'>$link</a> | ";
				} else if ($link == "send memo") {
					$link_values .= "<a data-toggle='modal' href='#email_list' picking_list_id='" . $mydata['id'] . "' class='mail_list'>$link</a> | ";
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
		$this -> session -> set_flashdata('list_message', "Memo Updated Successfully");
		redirect("picking_list");
	}

	public function view_orders($list_id) {
		$sql = "SELECT p.id,u.name as full_name,p.name,p.timestamp,count(c.order_id) as orders_total,IF(p.status=1,'Closed','Open') as status 
		      FROM picking_list_details p
		      LEFT JOIN cdrr c ON c.order_id=p.id
		      LEFT JOIN users u ON u.id=p.created_by
		      WHERE p.id='$list_id'";
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
		$data['banner_text'] = "Memo Details";
		$this -> base_params($data);
	}

	public function view_commodities($cdrr_id) {
		$columns = array('#', 'Commodity', 'Quantity for Resupply', 'Packs/Bottles/Tins');
		$sql = "SELECT sd.id,CONCAT_WS('] ',CONCAT_WS(' [',name,abbreviation),CONCAT_WS(' ',strength,formulation)) as drug,unit as drug_unit,ci.resupply
			        FROM cdrr_item ci
			        LEFT JOIN sync_drug sd ON sd.id=ci.drug_id
			        WHERE ci.cdrr_id='$cdrr_id'
			        AND resupply !='0'
			        AND(sd.category_id='1' OR sd.category_id='2' OR sd.category_id='3')";
		//include resupply >0
		$query = $this -> db -> query($sql);
		$results = $query -> result_array();
		echo $this -> showTable($columns, $results);
	}

	public function getMailList() {
		$options = '';
		$lists = Mail_List::getAll();
		foreach ($lists as $list) {
			$options .= '<optgroup label="' . $list -> name . '">';
			$emails = Mail_User::getMails($list -> id);
			foreach ($emails as $email) {
				$options .= '<option value="' . $email -> Email -> id . '"> ' . $email -> Email -> email_address . ' </option>';
			}
			$options .= '</optgroup>';
		}
		//others
		$options .= '<optgroup label="Others">';
		$sql = "SELECT ue.id,ue.email_address FROM user_emails ue WHERE ue.id NOT IN(SELECT email_id FROM mail_user WHERE list_id !='0')";
		$query = $this -> db -> query($sql);
		$results = $query -> result_array();
		if ($results) {
			foreach ($results as $email) {
				$options .= '<option value="' . $email['id'] . '"> ' . $email['email_address'] . ' </option>';
			}
			$options .= '</optgroup>';
		}
		return $options;
	}

	public function send_list() {
		$mail_list_id = $this -> input -> post("mail_list_id");
		$mail_lists = $this -> input -> post("mail_list_holder");
		$this -> session -> set_flashdata('list_message', "Mail Message Failed(no recipients)");
		if ($mail_lists != null) {
			//get the specific emails
			$email_address = "";
			$lists = explode(",", $mail_lists);
			$lists = array_unique($lists);
			foreach ($lists as $list) {
				$mail = User_Emails::getMail($list);
				$email_array[] = $mail['email_address'];
			}
			$email_address = implode(",", $email_array);

			$file_name = $this -> print_list($mail_list_id, 1);
			$email_user = stripslashes('webadt.chai@gmail.com');
			$email_password = stripslashes('WebAdt_052013');
			$subject = "NASCOP MEMO#" . $mail_list_id;
			$email_sender_title = "NASCOP SYSTEM";

			$message = "Hello, <br/><br/>
		                This Picking List was generated from the $email_sender_title </b><br/>
						Please find the specific list attached.<br/><br/>
						Regards,<br/>
						$email_sender_title team.";

			$config['mailtype'] = "html";
			$config['protocol'] = 'smtp';
			$config['smtp_host'] = 'ssl://smtp.googlemail.com';
			$config['smtp_port'] = 465;
			$config['smtp_user'] = $email_user;
			$config['smtp_pass'] = $email_password;
			ini_set("SMTP", "ssl://smtp.gmail.com");
			ini_set("smtp_port", "465");

			$this -> load -> library('email', $config);
			$this -> email -> set_newline("\r\n");
			$this -> email -> from('webadt.chai@gmail.com', $email_sender_title);
			$this -> email -> to("$email_address");
			$this -> email -> subject($subject);
			$this -> email -> message($message);
			$this -> email -> attach($file_name);

			if ($this -> email -> send() && $email_address != "") {
				$this -> email -> clear(TRUE);
				$error_message = 'Mail Sent Successfully<br/>';
			} else {
				$error_message = $this -> email -> print_debugger();
			}
			$this -> session -> set_flashdata('list_message', $error_message);
		}
		redirect("picking_list");

	}

	public function base_params($data) {
		$data['title'] = "Warehouse Memos";
		$this -> load -> view('template', $data);
	}

}
