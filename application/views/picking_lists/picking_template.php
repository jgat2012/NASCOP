<div class="center-content">
	<div>
		<ul class="breadcrumb">
			<li>
				<a href="<?php echo site_url().'picking_list' ?>">Memos</a><span class="divider">/</span>
			</li>
			<li class="active" id="actual_page">
				<?php echo $page_title;?>
			</li>
		</ul>
	</div>
	<div style="margin-bottom:2em;">
		<table border="1" width="100%" cellpadding="4" style="font-size:14px;background:#c3d9ff;">
			<tbody>
				<tr>
					<td><b>List No:#</b></td>
					<td><?php echo "P-LIST#" . $list -> id;?></td>
					<td><b>Created By</b></td>
					<td><?php echo $list -> full_name;?></td>
				</tr>
				<tr>
					<td><b>Memo Name</b></td>
					<td><?php echo $list -> name;?></td>
					<td><b>Created On</b></td>
					<td><?php echo date('d-M-Y h:i:s a', $list -> timestamp);?></td>
				</tr>
				<tr>
					<td><b>No.of Orders</b></td>
					<td><?php echo $list -> orders_total;?></td>
					<td><b>Status</b></td>
					<td><?php echo $list -> status;?></td>
				</tr>
			</tbody>
		</table>
	</div>
	<div>
		<?php echo $orders_table;?>
	</div>
</div>
<!-- Modal to Show Commodity List -->
<div id="commodity_list" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="width:45%;">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">
				×
			</button>
			<h3 id="myModalLabel">Commodity List for D-CDRR#<span id="hash_cdrr"></span></h3>
		</div>
		<div class="modal-body" id="commodity_body">
			
		</div>
		<div class="modal-footer">
			<button class="btn" data-dismiss="modal" aria-hidden="true">
				Cancel
			</button>
		</div>
</div>

<script type="text/javascript">
	$(document).ready(function() {
		$(".commodity_list").click(function() {
            var base_url="<?php echo base_url();?>";
			var order_id = $(this).attr("commodity_list_id");
			 $("#hash_cdrr").text(order_id);
			var link = base_url + "picking_list/view_commodities/" + order_id;
			$.ajax({
				url : link,
				type : 'POST',
				success : function(data) {
					$("#commodity_body").empty();
                    $("#commodity_body").append(data);
                    $('.dataTables').dataTable();                   
				}
			});
		});
	    var oTable = $('.dataTables').dataTable();
		oTable.fnSort([[0, 'desc']]);
	});
</script>
