<div class="center-content">
	<?php
	if ($this -> session -> flashdata('order_delete')) {
		echo '<p class="message error">' . $this -> session -> flashdata('order_delete') . '</p>';
	} else if ($this -> session -> flashdata('order_message')) {
		echo '<p class="message info">' . $this -> session -> flashdata('order_message') . '</p>';
	}
	?>
	<div>
		<a data-toggle='modal' href='#select_central' class='btn check_net btn_central' id='btn_new_cdrr_central'><i class="icon-upload"></i> Upload D-CDRR(<b>Central</b>)</a>
		<a data-toggle='modal' href='#select_stand-alone' class='btn check_net btn_stand_alone' id='btn_new_cdrr_stand_alone'><i class="icon-upload"></i> Upload F-CDRR(<b>Stand-Alone</b>)</a>
		<?php echo $order_table;?>
	</div>
</div>
<script type="text/javascript">
	$(document).ready(function() {
		var oTable = $('.dataTables').dataTable();
		oTable.fnSort([[0, 'desc']]);
	});

</script>