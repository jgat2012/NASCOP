<div class="center-content">
	<div>
		<ul class="nav nav-tabs">
			<li id="open_btn" class="active">
				<a  href="#open_lists"><i class="icon-folder-open"></i> Open Memos</a>
			</li>
			<li id="closed_btn">
				<a  href="#closed_lists"> <i class="icon-folder-close"></i> Closed Memos</a>
			</li>
		</ul>
	</div>
	<div class="tab-content">
		<?php
				if ($this -> session -> flashdata('order_delete')) {
					echo '<p class="message error">' . $this -> session -> flashdata('order_delete') . '</p>';
				} else if ($this -> session -> flashdata('list_message')) {
					echo '<p class="message info">' . $this -> session -> flashdata('list_message') . '</p>';
				}
		?>
		<div id="open_lists" class="tab-pane active">
			<div class="menu_container">
				<a data-toggle='modal' href='#add_list' class='btn'><i class="icon-plus"></i> Create Memo</a>
				<br/>
			</div>
			<?php echo $open_table;?>
		</div>
		<div id="closed_lists" class="tab-pane">
			<div class="menu_container"></div>
			<?php echo $closed_table;?>
		</div>
	</div>
<!-- Modal to Add Picking List -->
<div id="add_list" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<form id="fmFillOrderForm" action="<?php echo base_url().'picking_list/create_list'?>" method="post" style="margin:0 auto;">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">
				×
			</button>
			<h3 id="myModalLabel">Create Memo</h3>
		</div>
		<div class="modal-body">
			<table  cellpadding="5">
				<tr>
					<td colspan='2'><label for="list_name">Memo Name</label>
					<input type="text" name="list_name" id="list_name" value="" required="required"/>
					</td>
				</tr>
			</table>
		</div>
		<div class="modal-footer">
			<button class="btn" data-dismiss="modal" aria-hidden="true">
				Cancel
			</button>
			<input type="submit" class="btn btn-primary" name="proceed" id="proceed" value="Save">
		</div>
	</form>
</div>
<!-- Modal to Edit Picking List -->
<div id="edit_list" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<form id="fmFillOrderForm" action="<?php echo base_url().'picking_list/update_list'?>" method="post" style="margin:0 auto;">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">
				×
			</button>
			<h3 id="myModalLabel">Update Memo</h3>
		</div>
		<div class="modal-body">
			<table  cellpadding="5">
				<tr>
					<td colspan='2'><label for="list_name">Memo Name</label>
					<input type="hidden" name="edit_list_id" id="edit_list_id" value="" required="required"/>
					<input type="text" name="edit_list_name" id="edit_list_name" value="" required="required"/>
					</td>
				</tr>
			</table>
		</div>
		<div class="modal-footer">
			<button class="btn" data-dismiss="modal" aria-hidden="true">
				Cancel
			</button>
			<input type="submit" class="btn btn-primary" name="proceed" id="proceed" value="Save">
		</div>
	</form>
</div>
<!-- Modal to Assign Orders -->
<div id="assign_orders" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="width:45%;">
	<form id="fmFillOrderForm" action="<?php echo base_url().'picking_list/assign_orders'?>" method="post" style="margin:0 auto;">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">
				×
			</button>
			<h3 id="myModalLabel">Assign Orders</h3>
		</div>
		<div class="modal-body">
			<input type="hidden" name="assign_list_id" id="assign_list_id" value="" required="required"/>
			<?php echo $assign_table;?>
		</div>
		<div class="modal-footer">
			<button class="btn" data-dismiss="modal" aria-hidden="true">
				Cancel
			</button>
			<input type="submit" class="btn btn-primary" name="proceed" id="proceed" value="proceed">
		</div>
	</form>
</div>
<!-- Modal to Display Email List -->

			<div id="email_list" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
				<form class="form-horizontal" action="<?php echo base_url() . 'picking_list/send_list';?>" id="modal_action" method="post">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-hidden="true">
							×
						</button>
						<h3 id="myModalLabel"><span id="modal_header">Send Memo</span></h3>
					</div>
					<div class="modal-body">
						<div class='control-group'>
						<label class='control-label'>Email List </label>
						<div class='controls'>
						<input type="hidden" name="mail_list_id" id="mail_list_id" value="" required="required"/>
						    <select id="mail_list_data" class="multiselect" multiple="multiple" style='width:auto;padding:10px;'>
						      <?php echo $mail_lists;?>
							</select>
							</select><input type='hidden' id='mail_list_holder' name='mail_list_holder' />
							</div>
						 </div>
					</div>
					<div class="modal-footer">
						<button class="btn" data-dismiss="modal" aria-hidden="true">
							Close
						</button>
						<button class="btn btn-primary">
							Send
						</button>
					</div>
				</form>
			</div>
			</div>
<script type="text/javascript">
	$(document).ready(function() {
		$(".multiselect").multiselect().multiselectfilter();
		$(".multiselect").multiselect("uncheckAll");
		
		$("#open_btn").click(function() {
			$("#closed_btn").removeClass();
			$(this).addClass("active");
			$("#open_lists").show();
			$("#closed_lists").hide();
		});
		$("#closed_btn").click(function() {
			$("#open_btn").removeClass();
			$(this).addClass("active");
			$("#closed_lists").show();
			$("#open_lists").hide();
		});
		var oTable = $('.dataTables').dataTable();
		oTable.fnSort([[0, 'desc']]);

		$(".delete").click(function() {
			var check = confirm("Are you sure?");
			if(check) {
				return true;
			} else {
				return false;
			}
		});

		$(".update").click(function() {
			var link_id = $(this).attr("link_id");
			var link_name = $(this).attr("link_name");
			$("#edit_list_id").val(link_id);
			$("#edit_list_name").val(link_name);
		});
		$(".assign").click(function() {
			var link_id = $(this).attr("assign_id");
			$("#assign_list_id").val(link_id);
		});
		$(".mail_list").click(function() {
			var link_id = $(this).attr("picking_list_id");
			$("#mail_list_id").val(link_id);
		});
		
		$("#modal_action").submit(function() {
				var facilities = $("select#mail_list_data").multiselect("getChecked").map(function() {
					return this.value;
				}).get();
				$("#mail_list_holder").val(facilities);
		});
	});

</script>
<?php
if($this->session->userdata("order_go_back")){

if($this->session->userdata("order_go_back")=="cdrr"){
?>
<script type="text/javascript">
	$(document).ready(function() {
		$("#closed_btn").removeClass();
		$(this).addClass("active");
		$("#open_lists").show();
		$("#closed_lists").hide();
	});

</script>
<?php
}
else if($this->session->userdata("order_go_back")=="fmaps"){
?>
<script type="text/javascript">
	$(document).ready(function() {
		$("#open_btn").removeClass();
		$("#closed_btn").addClass("active");
		$("#closed_lists").show();
		$("#open_lists").hide();
	});

</script>
<?php
}

}
else{
?>
<script type="text/javascript">
	$(document).ready(function() {
		$("#closed_btn").removeClass();
		$(this).addClass("active");
		$("#open_lists").show();
		$("#closed_lists").hide();
	});

</script>
<?php
}
?>
