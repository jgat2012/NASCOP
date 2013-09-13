<?php $this -> load -> view('sections/head');?>
<script type="text/javascript">
	$(document).ready(function() {
		var oTable = $('#patient_listing').dataTable({
			"sScrollX" : "100%",
			"sScrollY" : "300px",
			"bScrollCollapse" : true,
			"bPaginate" : false,
			"bJQueryUI" : true,
			"bAutoWidth" : false,
			"aoColumnDefs" : [{
				"sWidth" : "10%",
				"aTargets" : [-1]
			}]
		});
		new FixedColumns(oTable);

	});

</script>
<div style="width:700px;">
	<?php echo $dyn_table;?>
</div>