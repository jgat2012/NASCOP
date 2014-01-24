<div class="center-content">
	<?php
	if ($this -> session -> flashdata('order_delete')) {
		echo '<p class="message error">' . $this -> session -> flashdata('order_delete') . '</p>';
	} else if ($this -> session -> flashdata('order_message')) {
		echo '<p class="message info">' . $this -> session -> flashdata('order_message') . '</p>';
	}
	?>
	<div>
		<?php echo $order_table;?>
	</div>
</div>
<script type="text/javascript">
	$(document).ready(function() {
		var oTable = $('.dataTables').dataTable();
		oTable.fnSort([[0, 'desc']]);
	});

</script>