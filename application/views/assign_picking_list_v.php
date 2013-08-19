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
<form method="post" action="<?php echo site_url('picking_list_management/save_list')?>">
<?php
	$order_list="";
	$count=0;
	if($orders) {
	foreach($orders as $order){
		if($count==0){
			$order_list.=$order;
		}
		else{
			$order_list.=','.$order;
			$count==1;
		}
		?>
		<input type="hidden" name="orders[]" value="<?php echo $order; ?>" />
<?php }}?>
<div class="alert-bootstrap alert-info">Assign order(s) No <span class="_green"> <?php echo $order_list; ?></span> to a picking list.</div>
<div >
	<table class="table" style="width:60%;margin:0 auto">
		<tbody>
			<tr>
				<th>New Picking List Name</th><td><input name="picking_list_name" id="picking_list_name" type="text" class="input-xlarge"></td>
			</tr>
			<tr>
				<th><span class="_green">or</span> Select an Open List</th>
				<td>
					<select name="selected_picking_list" class="input-xlarge">
						<option value="0">--Select One--</option>
						<?php foreach($picking_lists as $list){?>
							<option value="<?php echo $list->id; ?>"><?php echo $list->Name; ?></option> 
						<?php }?> 
					</select>
				</td> 
			</tr>
		</tbody>
		<tfoot>
			<tr>
				<td colspan="2"><input name="generate" id="generate" class="btn btn-success" style="padding-left: 30px;padding-right: 30px" value="Save" type="submit"></td>
			</tr>
		</tfoot>
	</table>
</div>
			
</form>
</div>