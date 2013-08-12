<script type="text/javascript">
		var url = "";
	$(function() {
	$("#confirm_delete").dialog( {
	height: 150,
	width: 500,
	modal: true,
	autoOpen: false,
	buttons: {
	"Delete List": function() {
	delete_record();
	},
	Cancel: function() {
	$( this ).dialog( "close" );
	}
	}

	} );1				

	$(".delete").click(function(){ 
	url = "<?php echo base_url().'picking_list_management/delete_list/'?>
		" +$(this).attr("list");
		$("#confirm_delete").dialog('open');
		});
		});
		function delete_record(){
		window.location = url;
		}
</script>
<div class="center-content">
<?php $this->load->view("picking_list_sub_menu"); ?>
<table class="table table-striped table-bordered dataTables">
	<thead>
		<tr>
			<th width="50px">List No</th>
			<th>Name</th>
			<th>Created By</th>
			<th>Created On</th>
			<th width="50px">Orders</th>
			<th >Action</th>
		</tr>
	</thead>
	<tbody>
		<?php 
		foreach($lists as $list){?>
			<tr>
				<td><?php echo $list->id;?></td>
				<td><?php echo $list->Name;?></td>
				<td><?php echo $list->User_Object->Name;?></td>
				<td><?php echo date('Y-m-d h:i:s',$list->Timestamp);?></td>
				<td><?php echo count($list->Order_Objects);?></td>
				<td style="text-align: center"><button class="btn btn-small btn-info"><a href="<?php echo base_url()."picking_list_management/view_orders/".$list->id;?>" >View Orders</a></button> | <button class="btn btn-small btn-info"><a href="<?php echo base_url()."picking_list_management/close_list/".$list->id;?>" >Close</a></button> | <button class="btn btn-small btn-info"><a list="<?php echo $list->id;?>" class="link delete">Delete</a></button></td>
				
			</tr>
		<?php }
		?>
	</tbody>
</table>
</div>
<div title="Confirm Delete!" id="confirm_delete" style="width: 300px; height: 150px; margin: 5px auto 5px auto;">
	Are you sure you want to delete this picking list? Individual orders will <b>Not</b> be deleted
</div>
<?php if (isset($pagination)):
?>
<div style="width:450px; margin:0 auto 40px auto"> 
	<?php echo $pagination;?>
</div>
<?php endif;?>