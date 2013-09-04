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

	<div>
		<ul class="breadcrumb">
		  <li><a href="<?php echo site_url().'picking_list_management' ?>">Picking Lists</a> <span class="divider">/</span></li>
		 
		  	<?php
		  	if(isset($page_title)){
		  		?>
		  		 <li class="active" id="actual_page"><?php echo $page_title;?> </li>
		  		<?php
		  	}
		  	?>
		 
		</ul>
	</div>
	<div>
	<?php
  	if($this->session->userdata("msg_success")){
  		?>
  		<span class="message success"><?php echo $this->session->userdata("msg_success")  ?></span>
  	<?php
  	$this->session->unset_userdata("msg_success");
	}
  		
  	elseif($this->session->userdata("msg_error")){
  		?>
  		<span class="message error"><?php echo $this->session->userdata("msg_error")  ?></span>
  	<?php
  	$this->session->unset_userdata("msg_error");
  	}
	?>
	</div>
<?php $this->load->view("picking_list_sub_menu"); ?>
<table class="table table-striped table-bordered dataTables">
	<thead>
		<tr>
			<th width="50px">List No</th>
			<th>Pipeline</th>
			<th>Name</th>
			<th>Created By</th>
			<th>Created On</th>
			<th width="50px">No. of Orders</th>
			<th >Action</th>
		</tr>
	</thead>
	<tbody>
		<?php 
		foreach($lists as $list){?>
			<tr>
				<td><?php echo $list->id;?></td>
				<td><?php echo $list->Pipeline;?></td>
				<td><?php echo $list->Name;?></td>
				<td><?php echo $list->User_Object->Name;?></td>
				<td><?php echo date('d-M-Y h:i:s a',$list->Timestamp);?></td>
				<td><?php echo count($list->Order_Objects);?></td>
				<td style="text-align: center"><a href="<?php echo base_url()."picking_list_management/view_orders/".$list->id;?>" >View Orders</a> | <a href="<?php echo base_url()."picking_list_management/close_list/".$list->id;?>" >Close</a> | <a list="<?php echo $list->id;?>" class="link delete">Delete</a></td>
				
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
</div>
