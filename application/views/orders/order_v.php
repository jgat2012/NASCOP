<div class="center-content">
	<?php
	if ($this -> session -> flashdata('order_delete')) {
		echo '<p class="message error">' . $this -> session -> flashdata('order_delete') . '</p>';
	} else if ($this -> session -> flashdata('order_message')) {
		echo '<p class="message info">' . $this -> session -> flashdata('order_message') . '</p>';
	}
	?>
	<p></p>
	<a href="<?php echo base_url().'order/clear_all' ?>" class="btn btn-danger" style="float:right;"><i class="icon-remove"></i> Remove All</a>
	<?php echo $order_table;?>
</div>
<div id="select_satellite" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div id="excel_upload" style="text-align:center;">
		<form id='fmImportData' name="frm" method="post" enctype="multipart/form-data" id="frm" action="<?php echo base_url()."order/import_order"?>">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">
					×
				</button>
				<h3 id="myModalLabel">Order Upload</h3>
			</div>
			<div class="modal-body">
				<input type="hidden"  name="upload_type" id="upload_type" />
				<input type="file"  name="file" size="30"  required="required" accept="application/vnd.ms-excel"/>
			</div>
			<div class="modal-footer">
				<button class="btn" data-dismiss="modal" aria-hidden="true">
					Cancel
				</button>
				<input name="btn_save" class="btn btn-primary" type="submit"  value="Save" />
			</div>
		</form>
	</div>
</div>
<script type="text/javascript">
	$(document).ready(function() {
		var oTable = $('#my_orders').dataTable({
			"bJQueryUI" : true,
			"sPaginationType" : "full_numbers",
			"bAutoWidth" : false,
			"bDeferRender" : true,
			"bInfo" : true,
			"bProcessing" : true,
			"bSort" : true,
			"bSortClasses" : true,
			"bStateSave" : true,
			"sScrollX" : "100%",
			"bScrollCollapse" : true,
			"sScrollY" : "200px",
			"sCookiePrefix" :"nascop_orders_"
			});
		oTable.fnSort([[0, 'desc']]);
		$(".upload").click(function() {
			var upload_type = $(this).attr("id");
			$("#myModalLabel").text(upload_type + " Upload");
			$("#upload_type").val(upload_type);
		});
	});

</script>