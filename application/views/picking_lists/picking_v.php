<div class="center-content">
	<div>
		<ul class="nav nav-tabs">
			<li id="open_btn" class="active">
				<a  href="#open_lists"><i class="icon-folder-open"></i> Open Lists</a>
			</li>
			<li id="closed_btn">
				<a  href="#closed_lists"> <i class="icon-folder-close"></i> Closed Lists</a>
			</li>
		</ul>
	</div>
	<div class="tab-content">
		<div id="open_lists" class="tab-pane active">
			<div class="menu_container">
				<a data-toggle='modal' href='#add_list' class='btn'><i class="icon-plus"></i> Create List</a>
				<?php echo $this -> session -> flashdata('list_message');?>
			</div>
			<?php echo $open_table;?>
		</div>
		<div id="closed_lists" class="tab-pane">
			<div class="menu_container"></div>
			<?php echo $closed_table;?>
		</div>
	</div>
</div>




<!-- Modal to Add Picking List -->
<div id="add_list" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<form id="fmFillOrderForm" action="<?php echo base_url().'picking_list/create_list'?>" method="post" style="margin:0 auto;">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">
				×
			</button>
			<h3 id="myModalLabel">Create Picking List</h3>
		</div>
		<div class="modal-body">
			<table  cellpadding="5">
				<tr>
					<td colspan='2'>
						<label for="list_name">List Name</label>
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

<script type="text/javascript">
	$(document).ready(function() {
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
	});

</script>
