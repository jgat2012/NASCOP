<div class="center-content">
	<?php
	if ($this -> session -> flashdata('order_delete')) {
		echo '<p class="message error">' . $this -> session -> flashdata('order_delete') . '</p>';
	} else if ($this -> session -> flashdata('order_message')) {
		echo '<p class="message info">' . $this -> session -> flashdata('order_message') . '</p>';
	}
	?>
	<p></p>
	<?php //echo $order_table;?>
	<!--<a href="<?php echo base_url().'order/clear_all' ?>" class="btn btn-danger" style="float:right;"><i class="icon-remove"></i> Remove All</a>-->
	<div class="tabbable"> <!-- Only required for left/right tabs -->
	  <ul class="nav nav-tabs pills">
	    <li class="active order_tab"><a href="#kemsa_tab" data-toggle="tab">Kemsa Orders</a></li>
	    <li class="order_tab"><a href="#kp_tab" data-toggle="tab">KP Orders</a></li>
	  </ul>
	  <div class="tab-content">
	    <div class="tab-pane active" id="kemsa_tab">
	      <?php //echo $order_table;?>
	      <table class="table table-bordered table-condensed table-hover" id="order_listing_kemsa">
	      	<thead>
	      		<tr>
					<th>#CDRR-ID</th>
					<th>#MAPS-ID</th>
					<th>Period Beginning</th>
					<th>Status</th>
					<th>Facility Name</th>
					<th>Options</th>
				</tr>
            </thead>
	      </table>
	    </div>
	    <div class="tab-pane" id="kp_tab">
	      <table class="table table-bordered table-condensed table-hover" id="order_listing_kp">
	      	<thead>
	      		<tr>
					<th>#CDRR-ID</th>
					<th>#MAPS-ID</th>
					<th>Period Beginning</th>
					<th>Status</th>
					<th>Facility Name</th>
					<th>Options</th>
				</tr>
            </thead>
	      </table>
	    </div>
	  </div>
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
		
		//Patient Listing DataTables
		var oTable = $('#order_listing_kemsa').dataTable({
				        "bProcessing": true,
				        "sAjaxSource": 'order/getorders/kemsa',
				        "bJQueryUI" : true,
						"sPaginationType" : "full_numbers",
						"bStateSave" : true,
						"sDom" : '<"H"T<"clear">lfr>t<"F"ip>',
						"bAutoWidth" : false,
						"bDeferRender" : true,
						"bInfo" : true
				  });
		$('#order_listing_kp').dataTable({
	        "bProcessing": true,
	        "sAjaxSource": 'order/getorders/kp',
	        "bJQueryUI" : true,
			"sPaginationType" : "full_numbers",
			"bStateSave" : true,
			"sDom" : '<"H"T<"clear">lfr>t<"F"ip>',
			"bAutoWidth" : false,
			"bDeferRender" : true,
			"bInfo" : true
	    });
	
		
	});

</script>