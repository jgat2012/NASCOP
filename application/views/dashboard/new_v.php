
<script type="text/javascript">
	$(document).ready(function() {
		if($("#patient_listing").is(":visible")){
			var oTable = $('#patient_listing').dataTable({
				"sDom" : '<"H"Tfr>t<"F"ip>',
				"oTableTools" : {
					"sSwfPath" : base_url + "scripts/datatable/copy_csv_xls_pdf.swf",
					"aButtons" : ["copy", "print", "xls", "pdf"]
				},
				"sScrollX" : "100%",
				"sScrollY" : "250px",
				"bScrollCollapse" : true,
				"bPaginate" : false,
				"bJQueryUI" : true,
				"bAutoWidth" : false,
				"aoColumnDefs" : [{
					"sWidth" : "10%",
					"aTargets" : [-1]
				}],
				"bDestroy":true
			});
			new FixedColumns(oTable);
		}
		else{
			$('#facility_analysis').dataTable({
			"sDom" : '<"H"Tfr>t<"F"ip>',
			"oTableTools" : {
				"sSwfPath" : base_url + "scripts/datatable/copy_csv_xls_pdf.swf",
				"aButtons" : ["copy", "print", "xls", "pdf"]
			},
			"sScrollY" : "250px",
			"bScrollCollapse" : true,
			"bPaginate" : false,
			"bJQueryUI" : true,
			"bAutoWidth" : false
		},function(){
			alert("Ok")
		});
		}
		
		

	});

</script>
<div style="width:100%">
	<?php echo $dyn_table;?>
</div>