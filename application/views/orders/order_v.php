<div class="center-content">
	<div>
		<?php echo $order_table;?>
	</div>
</div>
<script type="text/javascript">
	$(document).ready(function() {
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