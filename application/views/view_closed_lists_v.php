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
				<td style="text-align: center"><button class="btn btn-small btn-info"><a href="<?php echo base_url()."picking_list_management/view_orders/".$list->id;?>" >View Orders</a></button> | <button class="btn btn-small btn-info"><a href="<?php echo base_url()."picking_list_management/print_list/".$list->id;?>" class="link">Print List</a></button></td>
				
			</tr>
		<?php }
		?>
	</tbody>
</table>
</div>
