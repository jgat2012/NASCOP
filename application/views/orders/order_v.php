<div class="center-content">
	<?php
	if ($this -> session -> flashdata('order_delete')) {
		echo '<p class="message error">' . $this -> session -> flashdata('order_delete') . '</p>';
	} else if ($this -> session -> flashdata('order_message')) {
		echo '<p class="message info">' . $this -> session -> flashdata('order_message') . '</p>';
	}
	?>
	<div>
		<a data-toggle='modal' href='#select_satellite' class='btn upload' id='D-CDRR'><i class="icon-upload"></i> Upload D-CDRR(<b>Central</b>)</a>
		<a data-toggle='modal' href='#select_satellite' class='btn upload' id='F-CDRR_packs'><i class="icon-upload"></i> Upload F-CDRR(<b>Stand-Alone</b>)</a>
		<a data-toggle='modal' href='#select_satellite' class='btn upload' id='D-MAPS'><i class="icon-upload"></i> Upload D-MAPS(<b>Central</b>)</a>
		<a data-toggle='modal' href='#select_satellite' class='btn upload' id='F-MAPS'><i class="icon-upload"></i> Upload F-MAPS(<b>Stand-Alone</b>)</a>
		<?php echo $order_table;?>
	</div>
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
		var oTable = $('.dataTables').dataTable();
		oTable.fnSort([[0, 'desc']]);
		$(".upload").click(function() {
			var upload_type = $(this).attr("id");
			$("#myModalLabel").text(upload_type + " Upload");
			$("#upload_type").val(upload_type);
		});
	});

</script>